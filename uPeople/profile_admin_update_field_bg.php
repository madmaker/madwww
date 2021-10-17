<?php
class uPeople_profile_admin_update_field_bg {
    private $uCore,$user_id,$field_id,$field_val;
    private function check_data(){
        if(!isset($_POST['user_id'],$_POST['field_id'],$_POST['field_val'])) $this->uCore->error(1);
        $this->user_id=&$_POST['user_id'];
        if(!uString::isDigits($this->user_id))$this->uCore->error(2);
        $this->field_id=&$_POST['field_id'];
        if(!uString::isDigits($this->field_id))$this->uCore->error(3);
        $this->field_val=uString::text2sql($_POST['field_val']);
    }
    private function update_user() {
            if(!$this->uCore->query('uPeople',"UPDATE
            `u235_people`
            SET
            `field_".$this->field_id."`='".$this->field_val."'
            WHERE
            `user_id`='".$this->user_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die('forbidden');

        $this->check_data();
        $this->update_user();
        echo 'done';
    }
}

$uPeople=new uPeople_profile_admin_update_field_bg ($this);
