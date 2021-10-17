<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_arts_delete_bg{
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore;
    private $art_id;

    private function checkData() {
        if(!isset($_POST['art_id'])) $this->uFunc->error(10);
        $this->art_id=$_POST['art_id'];
        if(!uString::isDigits($this->art_id)) $this->uFunc->error(20);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->uCat->delete_article($this->art_id);
        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo "{'status' : 'done'}";
    }
}
new admin_arts_delete_bg($this);