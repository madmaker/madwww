<?php
class uSubscr_edit_user{
    private $uCore,$user_name,$user_email,$user_id;
    private function check() {
        if(!isset($_POST['user_name'],$_POST['user_email'],$_POST['user_id'])) $this->uCore->error(1);
        $this->user_id=$_POST['user_id'];
        if(!uString::isDigits($this->user_id)) $this->uCore->error(2);
        $this->user_name=$_POST['user_name'];
        $this->user_email=trim($_POST['user_email']);

        if(!uString::isEmail($this->user_email)) die("{'status' : 'error', 'msg' : 'email'}");
    }
    private function write2db() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_users`
        SET
        `user_name`='".uString::text2sql($this->user_name)."',
        `user_email`='".uString::text2sql($this->user_email)."'
        WHERE
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."' AND
        `admin_made`='1' AND
        `unsubscribed`='0'
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check();
        $this->write2db();

        echo  "{
        'status' : 'done',
        'user_id' : '".$this->user_id."',
        'user_name':'".rawurlencode($this->user_name)."',
        'user_email':'".rawurlencode($this->user_email)."'
        }";
    }
}
$uSubscr=new uSubscr_edit_user($this);
