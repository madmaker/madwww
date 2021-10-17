<?php
class uPeople_profile_admin_update_bg {
    private $uCore,
        $user_id,$firstname,$secondname,$lastname;
    private function check_data(){
        if(!isset($_POST['user_id'],$_POST['firstname'],$_POST['secondname'],$_POST['lastname'],$_POST['field_num'])) $this->uCore->error(1);
        $this->user_id=&$_POST['user_id'];
        if(!uString::isDigits($this->user_id))$this->uCore->error(2);
        $this->firstname=&$_POST['firstname'];
        $this->secondname=&$_POST['secondname'];
        $this->lastname=&$_POST['lastname'];
    }
    private function update_user() {
        //update fields
            $field_num=$_POST['field_num'];
            if(!uString::isDigits($field_num)) $this->uCore->error(3);

            $sql="
            `firstname`='".$this->firstname."',
            `secondname`='".$this->secondname."',
            `lastname`='".$this->lastname."'
            ";

            for($i=0;$i<$field_num;$i++) {
                if(!isset($_POST['field_'.$i])) continue;
                if(!isset($_POST['field_ids_'.$i])) continue;

                if(!uString::isDigits($_POST['field_ids_'.$i])) continue;
                $field_val=uString::text2sql($_POST['field_'.$i]);

                $field_id=$_POST['field_ids_'.$i];
                if(!uString::isDigits($field_id)) continue;

                //check if field_type != 3
                if(!$query=$this->uCore->query("uPeople","SELECT
                `field_type`
                FROM
                `u235_fields`
                WHERE
                `site_id`='".site_id."' AND
                `field_id`='".$field_id."' AND
                `field_type`!='3'
                ")) $this->uCore->error(4);
                if(!mysqli_num_rows($query)) continue;

                //if(!empty($sql)) $sql.=',';
                $sql.=", `field_".$field_id."`='".$field_val."'";
            }
            if(!$this->uCore->query('uPeople',"UPDATE `u235_people`
            SET
            ".$sql."
            WHERE
            `user_id`='".$this->user_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(5);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die('forbidden');

        $this->check_data();
        $this->update_user();

        echo 'done';
    }
}

$uPeople=new uPeople_profile_admin_update_bg($this);
