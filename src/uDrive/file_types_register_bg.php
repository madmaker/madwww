<?php
class uDrive_file_types_register {
    private $uCore,$type_id;
    private function check_data() {
        if(!isset($_POST['type_id'])) $this->uCore->error(10);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uCore->error(20);
    }
    private function register_type() {
        if(!$this->uCore->query("uDrive","UPDATE
        `u235_file_types`
        SET
        `known`='1'
        WHERE
        `type_id`='".$this->type_id."'
        ")) $this->uCore->error(30);
        echo '{"status":"done"}';
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(1901)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->register_type();
    }
}
$uDrive=new uDrive_file_types_register($this);
