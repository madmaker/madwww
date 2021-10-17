<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_requests_admin_set_no_solution_reason_bg {
    public $uFunc;
    public $uSes;
    private $uCore,
        $tic_id,$reason,$user_id;

    private function check_data() {
        if(!isset($_POST['tic_id'],$_POST['reason'])) $this->uCore->error(10);
        $this->tic_id=&$_POST['tic_id'];
        $this->reason=trim($_POST['reason']);
        if($this->uCore->uFunc->getConf("req_force_write_noreason_sol","uSup")=='1') {
            if(strlen($this->reason)<5) die("{'status' : 'error', 'msg' : 'reason'}");
        }
        if(!uString::isDigits($this->tic_id)) $this->uCore->error(20);
    }
    private function get_user_data() {
        $this->user_id=$this->uSes->get_val("user_id");

        if(!$query=$this->uCore->query('uAuth',"SELECT
        `firstname`,
        `lastname`
        FROM
        `u235_users`
        WHERE
        `u235_users`.`user_id`='".$this->user_id."'
        ")) $this->uCore->error(30);
        if(!mysqli_num_rows($query)) $this->uCore->error(40);
        $user=$query->fetch_object();
        return $user->firstname.' '.$user->lastname;
    }
    private function set_reason() {
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `uknowbase_solution_isset`='0',
        `uknowbase_no_solution_reason`='".uString::text2sql($this->reason)."',
        `uknowbase_no_solution_user_id`='".$this->user_id."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(50);

        if(!$this->uCore->query("uSup","DELETE
        FROM
        `u235_uKnowbase_solutions_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(9)&&!$this->uCore->access(8)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_user_data();
        $this->set_reason();
        echo "{
        'status' : 'done',
        'tic_id' : '".$this->tic_id."',
        'reason' : '".rawurlencode(nl2br($this->reason))."',
        'user_id' : '".$this->user_id."',
        'user_name' : '".rawurlencode($this->get_user_data())."'
        }";
    }
}
$uSup=new uSup_requests_admin_set_no_solution_reason_bg($this);
