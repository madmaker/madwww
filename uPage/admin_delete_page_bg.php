<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uPage/inc/common.php';

class uPage_delete_page {
    private $uPage;
    private $uSes;
    private $uCore,$uFunc,$page_id;
    private function check_data() {
        if(!isset($_POST['page_id'])) $this->uCore->error(10);
        $this->page_id=$_POST['page_id'];
        if(!uString::isDigits($this->page_id)) $this->uCore->error(20);
    }
    private function clear_cache() {
        $this->uPage->clear_cache($this->page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();

        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uPage=new \uPage\common($this->uCore);
        $this->uFunc = new uFunc($this->uCore);

        $this->check_data();
        $this->uPage->delete_page($this->page_id,site_id);
        $this->clear_cache();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        die('{
        "status":"done"
        }');
    }
}
$uPage=new uPage_delete_page($this);