<?php
namespace uCat\admin;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_sects_create_bg{
    private $uCat;
    private $uSes;
    private $uFunc;
    private $uCore,
        $sect_title,$sect_id;
    private function check_data() {
        if(!isset($_POST['sect_title'])) $this->uFunc->error(10);
        $this->sect_title=uString::text2sql(trim($_POST['sect_title']));
        if(empty($this->sect_title)) die("{'status' : 'error', 'msg' : 'title is empty'}");
    }
    private function attach_cat2sect() {
        if(isset($_POST['cat_id'])) {
            $cat_id=$_POST['cat_id'];
            if($cat_id=='no_cat') return 0;
            if(!uString::isDigits($cat_id)) $this->uFunc->error(20);
            $this->uCat->attach_cat2sect($this->sect_id,$cat_id);
        }
        return 1;
    }
    private function attach_sect2sect() {
        if(isset($_POST['sect_id'])) {
            $sect_id=$_POST['sect_id'];
            if($sect_id=='no_sect') return 0;
            if(!uString::isDigits($sect_id)) $this->uFunc->error(30);
            $this->uCat->attach_sect2sect($sect_id, $this->sect_id);
        }
        return 1;
    }
    private function create_sect() {
        $show_in_menu_new_sects=$this->uCore->uFunc->getConf("show_in_menu_new_sects","uCat");
        if($show_in_menu_new_sects!='1') $show_in_menu_new_sects='0';

        $this->sect_id=$this->uCat->create_new_sect($this->sect_title,$show_in_menu_new_sects);

        //attach current cat to new sect if needed

        if(isset($_POST['sect_id']) && $_POST['sect_id'] !== "no_sect") {
            $this->attach_sect2sect();
        }
        else {
            $this->attach_cat2sect();
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_sect();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done', 'sect_id' : '".$this->sect_id."'}";
    }
}
new admin_sects_create_bg ($this);