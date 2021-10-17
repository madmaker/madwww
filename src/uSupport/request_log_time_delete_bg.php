<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_request_log_time_delete {
    public $uFunc;
    public $uSes;
    private $uCore,
        $rec_id,$tic_id;

    private function check_data() {
        if(!isset($_POST['rec_id'])) $this->uCore->error(10);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(20);

        if($this->uCore->access(8)) {
            //get rec's tic_id
            if(!$query=$this->uCore->query("uSup","SELECT
            `tic_id`
            FROM
            `u235_requests_time`
            WHERE
            `rec_id`='".$this->rec_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(30);
            if(!mysqli_num_rows($query)) return false;
            $qr=$query->fetch_object();
            $this->tic_id=$qr->tic_id;
            return true;
        }
        //if not operator get rec's tic_id and check if rec's user is it's owner
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests_time`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->tic_id=$qr->tic_id;
            return true;
        }

        return false;
    }
    private function del_time_log() {
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_requests_time`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(50);

        //count how much hours is spent for this task total
        if(!$query=$this->uCore->query("uSup","SELECT
        SUM(time_spent)
        FROM
        `u235_requests_time`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
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
        ")) $this->uCore->error(70);

        $hours_total=floor($time_total/60);
        $minutes_total=$time_total-$hours_total*60;

        echo "{
        'status' : 'done',
        'rec_id' : '".$this->rec_id."',
        'tic_id' : '".$this->tic_id."',
        'total_time' : '".$hours_total.":".$minutes_total."'
        }";
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(9)&&$this->uCore->access(8)) die("{'status' : 'forbidden'}");

        if($this->check_data()) {
            $this->del_time_log();
        }
        else die("{'status' : 'forbidden'}");
    }
}
$uSup=new uSup_request_log_time_delete($this);
