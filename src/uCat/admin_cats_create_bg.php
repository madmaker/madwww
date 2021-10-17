<?php
use processors\uFunc;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once "uCat/classes/common.php";

class uCat_admin_cats_create {
    private $uCat;
    private $uSes;
    private $uFunc;
    private $uCore,
    $cat_title;
    private function check_data() {
        if(!isset($_POST['cat_title'])) $this->uFunc->error(10);
        $this->cat_title=uString::text2sql(trim($_POST['cat_title']));
        if(empty($this->cat_title)) die("{'status' : 'error', 'msg' : 'title is empty'}");
    }
    private function attach_item2cat($cat_id) {
        if(isset($_POST['item_id'])) {
            $item_id=$_POST['item_id'];
            if($item_id=='no_item') return false;
            if(!uString::isDigits($item_id)) $this->uFunc->error(20);
            $this->uCat->attach_item2cat($cat_id,$item_id);
        }
        return true;
    }
    private function attach_cat2sect($cat_id) {
        if(isset($_POST['sect_id'])) {
            $sect_id=$_POST['sect_id'];
            if($sect_id=='no_sect') return false;
            if(!uString::isDigits($sect_id)) $this->uFunc->error(30);
            $this->uCat->attach_cat2sect($sect_id,$cat_id);
        }
        return true;
    }
    private function create_cat() {
        $cat_id=$this->uCat->create_new_cat($this->cat_title);
        //get new cat_id

        //attach current item to cat if needed
        $this->attach_item2cat($cat_id);

        //attach current sect to cat if needed
        $this->attach_cat2sect($cat_id);

        return $cat_id;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $cat_id=$this->create_cat();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done', 'cat_id' : '".$cat_id."'}";
    }
}
new uCat_admin_cats_create($this);