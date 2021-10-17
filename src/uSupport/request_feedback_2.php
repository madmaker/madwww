<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_requests_feedback_2 {
    public $uFunc;
    public $uSes;
    private $uCore,$tic_id;
    public $allow,$tic_subject,$html,$in_dialog;

    private function error($text) {
        $this->html=$text;

        return false;
    }
    private function save_feedback() {
        for($i=1,$ans_num=0,$ans_sum=0;$i<12;$i++) {
            if(isset($_POST['ans'.$i])) {
                if(uString::isDigits($_POST['ans'.$i])) {
                    $ans[$i]=$_POST['ans'.$i];
                    $ans_num++;
                    $ans_sum+=$ans[$i];
                }
                else $ans[$i]=0;
            }
            else $ans[$i]=0;

            if(isset($_POST['ans'.$i.'_comment'])) {
                $ans_comment[$i]=uString::text2sql(trim($_POST['ans'.$i.'_comment']));
            }
            else $ans_comment[$i]='';
        }

        if($ans_num>0) $ans_type=$ans_sum/$ans_num;
        else $ans_type='neutral';
        if($ans_type>3) $ans_type='positive';
        elseif($ans_type<3) $ans_type='negative';
        else $ans_type='neutral';

        //save feedback data
        $qu="INSERT INTO
        `u235_requests_feedbacks` (
        `tic_id`,";
        for($i=1;$i<12;$i++) {
            $qu.="`ans".$i."`,";
        }
        for($i=1;$i<12;$i++) {
            $qu.="`ans".$i."_comment`,";
        }
        $qu.="`timestamp`,
        `site_id`
        ) VALUES (
        '".$this->tic_id."',";
        for($i=1;$i<12;$i++) {
            $qu.="'".$ans[$i]."',";
        }
        for($i=1;$i<12;$i++) {
            $qu.="'".$ans_comment[$i]."',";
        }
        $qu.="'".time()."',
        '".site_id."'
        )
        ";

        if(!$this->uCore->query("uSup",$qu)) $this->uCore->error(1);

        //update tic - set that feedback isset
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_feedback_info`='".$ans_type."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
    }
    public function check_data() {
        $feedback_time_limit=604800;//7days
        $this->in_dialog=0;

        if(!isset($this->uCore->url_prop[1])) {
            if(!isset($_POST['tic_id'])) {
                if($this->in_dialog) die('{"status":"forbidden"}');
                return $this->error("Такой страницы не существует");
            }
            else $handler=$_POST['tic_id'];
            $this->in_dialog=1;
        }
        else $handler=$this->uCore->url_prop[1];
        if(uString::isDigits($handler)&&$this->uCore->access(2)) {//must be tic_id
            if(!$query=$this->uCore->query("uSup","SELECT
            `tic_id`,
            `tic_subject`
            FROM
            `u235_requests`
            WHERE
            `tic_id`='".$handler."' AND
            `site_id`='".site_id."' AND
            `user_id`='".$this->uSes->get_val("user_id")."'
            ")) $this->uCore->error(3);
            if(!mysqli_num_rows($query)) {
                if($this->in_dialog) die('{"status":"forbidden"}');
                return $this->error("Такой страницы не существует");
            }
            $tic=$query->fetch_object();
        }
        else if(uString::isHash($handler)) {//must be hash link
            if(!$query=$this->uCore->query("uSup","SELECT
            `tic_id`,
            `tic_subject`,
            `tic_changed_timestamp`
            FROM
            `u235_requests`
            WHERE
            `tic_feedback_info`='".$handler."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(!mysqli_num_rows($query)) {
                if($this->in_dialog) die('{"status":"forbidden"}');
                return $this->error("Такой страницы не существует");
            }
            $tic=$query->fetch_object();
            if($tic->tic_changed_timestamp<(time()-$feedback_time_limit)) {
                if($this->in_dialog) die('{"status":"forbidden"}');
                return $this->error("Такой страницы не существует");
            }
        }
        else {
            if($this->in_dialog) die('{"status":"forbidden"}');
            return $this->error("Такой страницы не существует");
        }

        $this->tic_id=$tic->tic_id;
        $this->tic_subject=uString::sql2text($tic->tic_subject);

        //check if there are still no feedback registered for this tic_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`
        FROM
        `u235_requests_feedbacks`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(mysqli_num_rows($query)) {
            if($this->in_dialog) die('{"status":"duplicate"}');
            return $this->error("Мы уже приняли ваш отзыв на этот запрос");
        }

        $this->save_feedback();
        return true;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->allow=$this->check_data();

        if($this->in_dialog) die('{
        "status":"done",
        "tic_id":"'.$this->tic_id.'"
        }');
    }
}
$uSup=new uSup_requests_feedback_2($this);

ob_start();?>
<div class="uSup_request_feedback">
    <h1 class="page-header"><?=$this->page['page_title']?><br><small><?=$uSup->tic_subject?></small></h1>

<?if($uSup->allow) {?>
    <p>Спасибо. Ваш отзыв принят.</p>
<?} else {?>
    <p><?=$uSup->html?></p>
<?}?>

</div>
<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
