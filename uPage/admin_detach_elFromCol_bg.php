<?php
require_once 'uPage/inc/common.php';
class uPage_admin_detach_elFromCol {
    private $uCore,$cols_els_id,$page_id;
    private function check_data() {
        if(!isset($_POST['cols_els_id'])) $this->uCore->error(1);
        $this->cols_els_id=$_POST['cols_els_id'];
        if(!uString::isDigits($this->cols_els_id)) $this->uCore->error(2);

        include_once "uPage/inc/common.php";
        $uPage_common=new \uPage\common($this->uCore);
        $this->page_id=$uPage_common->get_page_id('el',$this->cols_els_id);
    }
    private function detach_el() {
        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols_els`
        WHERE
        `cols_els_id`='".$this->cols_els_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);

        echo ('{
        "status":"done",
        "cols_els_id":"'.$this->cols_els_id.'"
        }');
    }
    private function clear_cache() {
        $this->uPage->clear_cache($this->page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uPage=new \uPage\common($this->uCore);

        if(!$this->uCore->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->detach_el();
        $this->clear_cache();
    }
}
$uPage=new uPage_admin_detach_elFromCol($this);
