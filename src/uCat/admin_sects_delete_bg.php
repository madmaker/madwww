<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class admin_sects_delete_bg{
    public $uFunc;
    public $uSes;
    public $uCat;
    private $uCore,
        $sect_id;

    private function checkData() {
        if(!isset($_POST['sect_id'])) $this->uFunc->error(10);
        $this->sect_id=$_POST['sect_id'];
        if(!uString::isDigits($this->sect_id)) $this->uFunc->error(20);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->uCat->delete_sect($this->sect_id);
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done'}";
    }
}
new admin_sects_delete_bg($this);