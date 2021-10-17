<?php
class uPeople_profile_edit_attach_group_bg {
    private $uCore,$attach,$gr_id,$user_id;
    private function check_data() {
        if(!isset($_POST['action'],$_POST['gr_id'],$_POST['user_id'])) $this->uCore->error(1);
        if($_POST['action']=='attach') $this->attach=true;
        else $this->attach=false;
        $this->gr_id=$_POST['gr_id'];
        if(!uString::isDigits($this->gr_id)) $this->uCore->error(2);
        $this->user_id=$_POST['user_id'];
        if(!uString::isDigits($this->user_id)) $this->uCore->error(3);
    }
    private function do_attach() {
        if(!$this->uCore->query("uPeople","DELETE FROM
        `u235_people_groups`
        WHERE
        `user_id`='".$this->user_id."' AND
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        if($this->attach) {
            if(!$this->uCore->query("uPeople","INSERT INTO
            `u235_people_groups` (
            `user_id`,
            `gr_id`,
            `site_id`
            ) VALUES (
            '".$this->user_id."',
            '".$this->gr_id."',
            '".site_id."'
            )
            ")) $this->uCore->error(5);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die('forbidden');

        $this->check_data();
        $this->do_attach();
        echo 'done';
    }
}
$newClass=new uPeople_profile_edit_attach_group_bg($this);
