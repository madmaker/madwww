<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_request_log_time_bg {
    public $uFunc;
    public $uSes;
    private $uCore,
        $tic_id,$time_spent,$timestamp,$comment,$user_id,$user_name;

    private function check_data() {
        if(!isset($_POST['tic_id'],$_POST['minutes'],$_POST['hours'],$_POST['date'],$_POST['time'],$_POST['comment'],$_POST['user_id'])) $this->uFunc->error(10);
        $this->tic_id=$_POST['tic_id'];
        if(!uString::isDigits($this->tic_id)) $this->uFunc->error(20);
        //check if this tic_id exists
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(30);
        if(!mysqli_num_rows($query)) $this->uFunc->error(40);

        $min=$_POST['minutes'];
        $hour=$_POST['hours'];
        if(!uString::isDigits($min)) die("{'status' : 'error', 'msg' : 'spenttime'}");
        if(!uString::isDigits($hour)) die("{'status' : 'error', 'msg' : 'spenttime'}");
        $this->time_spent=$min+$hour*60;


        $date=$_POST['date'];
        $time=$_POST['time'];
        if(!uString::isDate($date)) die("{'status' : 'error','msg':'date'}");
        $dateAr=explode('.',$date);

        if(!uString::isTime($time)) die("{'status' : 'error','msg':'time'}");
        $timeAr=explode(':',$time);

        $this->timestamp=mktime($timeAr[0],$timeAr[1],0,$dateAr[1],$dateAr[0],$dateAr[2]);



        $this->comment=uString::text2sql($_POST['comment']);

        if($this->uCore->access(8)) {
            $this->user_id=$_POST['user_id'];
            if(!uString::isDigits($this->user_id)) $this->uFunc->error(50);
        }
        else $this->user_id=$this->uSes->get_val("user_id");

        //check if this user_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id AND
            u235_users.status=u235_usersinfo.status
            WHERE
            u235_users.user_id=:user_id AND
            u235_users.status='active' AND
            site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        if(!$user=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(70);
        $this->user_name=$user->firstname.' '.$user->secondname.' '.$user->lastname;
    }
    private function log_time() {
        //get new rec_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `rec_id`
        FROM
        `u235_requests_time`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `rec_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(80);
        if(mysqli_num_rows($query)) {
            $rec=$query->fetch_object();
            $rec_id=$rec->rec_id+1;
        }
        else $rec_id=1;

        //write new rec to db
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_requests_time` (
        `rec_id`,
        `tic_id`,
        `time_spent`,
        `comment`,
        `user_id`,
        `timestamp`,
        `site_id`
        ) VALUES (
        '".$rec_id."',
        '".$this->tic_id."',
        '".$this->time_spent."',
        '".$this->comment."',
        '".$this->user_id."',
        '".$this->timestamp."',
        '".site_id."'
        )
        ")) $this->uFunc->error(90);

        //count how much hours is spent for this task total
        if(!$query=$this->uCore->query("uSup","SELECT
        SUM(time_spent)
        FROM
        `u235_requests_time`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(100);
        $qr=$query->fetch_assoc();
        $time_total=$qr['SUM(time_spent)'];

        //update request's time total
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_time_spent`='".$time_total."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(110);

        $hours=floor($this->time_spent/60);
        $minutes=$this->time_spent-$hours*60;

        $hours_total=floor($time_total/60);
        $minutes_total=$time_total-$hours_total*60;

        die("{
        'status' : 'done',
        'rec_id' : '".$rec_id."',
        'tic_id' : '".$this->tic_id."',
        'time_spent' : '".$hours.':'.$minutes."',
        'comment' : '".rawurlencode($_POST['comment'])."',
        'user_id' : '".$this->user_id."',
        'user_name' : '".rawurlencode($this->user_name)."',
        'timestamp' : '".date('d.m.Y H:i:s',$this->timestamp)."',
        'total_time' : '".$hours_total.':'.$minutes_total."'
        }");
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(9)&&$this->uCore->access(8)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->log_time();
    }
}
$uSup=new uSup_request_log_time_bg($this);
