<?php
require_once 'uPage/inc/common.php';

class uPage_admin_delete_col {
    private $uCore,$col_id,$page_id;
    private function check_data() {
        if(!isset($_POST['col_id'])) $this->uCore->error(1);
        $this->col_id=$_POST['col_id'];
        if(!uString::isDigits($this->col_id)) $this->uCore->error(2);

        include_once "uPage/inc/common.php";
        $uPage_common=new \uPage\common($this->uCore);
        $this->page_id=$uPage_common->get_page_id('col',$this->col_id);
    }
    private function delete_col() {
        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols_els`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);

        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }
    private function clear_cache() {
        $this->uPage->clear_cache($this->page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uPage=new \uPage\common($this->uCore);

        if(!$this->uCore->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_col();
        $this->clear_cache();
        die('{
        "status":"done",
        "col_id":"'.$this->col_id.'"
        }');
    }
}
$uPage=new uPage_admin_delete_col($this);
