<?php
//СЕССИЯ ЗДЕСЬ НЕ ПРОВЕРЯЕТСЯ, NO_SES_CHECK задано в БД
class uCat_cron_unused_field_columns_cleaner {
    private $uCore,$secret;
    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }
    private function check_field($field_name) {
        if($this->startime+$this->lifetime<time()) exit();
        $field_id=str_replace('field_','',$field_name);
        if(!$query=$this->uCore->query("uCat","SELECT
        `field_id`
        FROM
        `u235_fields`
        WHERE
        `field_id`='".$field_id."'
        LIMIT 1
        ")) $this->uCore->query(3);
        if(!mysqli_num_rows($query)) {
            if(!$this->uCore->query("uCat","ALTER TABLE
            u235_items
            DROP
            field_".$field_id)) $this->uCore->error(4);
        }
    }
    private function get_columns() {
        if(!$query=$this->uCore->query("uCat","DESCRIBE
        u235_items
        ")) $this->uCore->error(5);
        while($col=$query->fetch_object()) {
            if(strpos($col->Field,'field_')===0) $this->check_field($col->Field);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='YUIOiuosf97832o234mnLssdf09871NBMBNbnnbnbz11b34yshk2h3f5vHg234hjjh2nbhjgGHj4nm2jh34299809';
        $this->startime=time();
        $this->lifetime=20;//20 seconds

        $this->check_data();

        $this->get_columns();
    }
}
$uCat=new uCat_cron_unused_field_columns_cleaner($this);
