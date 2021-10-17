<?php
class uPeople_delete_group_bg {
    private $uCore,
        $gr_id;

    private function check_data() {
        if(!isset($_POST['gr_id'])) $this->uCore->error(1);
        $this->gr_id=$_POST['gr_id'];
        if(!uString::isDigits($this->gr_id)) $this->uCore->error(2);
    }

    private function delete_group() {
        //delete group
        if(!$this->uCore->query("uPeople","DELETE FROM
        `u235_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        //delete user's group's associations
        if(!$this->uCore->query("uPeople","DELETE FROM
        `u235_people_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_group();

        echo "{'status' : 'done'}";
    }
}
$uPeople=new uPeople_delete_group_bg($this);
