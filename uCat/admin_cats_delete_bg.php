<?php
namespace uCat\admin;
use uString;

require_once 'uCat/inc/admin_count_helper.php';
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class admin_cats_delete_bg {
    public $uFunc;
    public $uSes;
    public $uCat;
    private $uCore,
     $cat_id;

    private function checkData() {
        if(!isset($_POST['cat_id'])) $this->uFunc->error(10);
        $this->cat_id=$_POST['cat_id'];
        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(20);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->uCat->delete_cat($this->cat_id,site_id);
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done'}";
    }
}
new admin_cats_delete_bg($this);