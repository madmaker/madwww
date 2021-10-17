<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class admin_items_delete_bg{
    private $uCat;
    private $uSes;
    private $uFunc;
    private $uCore,$item_id;

    private function check_data() {
        if(!isset($_POST['item_id'])) 
            $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) 
            $this->uFunc->error(20);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->uCat->delete_item($this->item_id);
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done'}";
    }
}
new admin_items_delete_bg($this);