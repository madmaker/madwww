<?php
namespace uCat\admin;
use PDO;
use PDOException;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_items_create_bg{
    public $uSes;
    public $uFunc;
    public $uCat;
    public $unit_id;
    private $uCore,
        $item_title,$cat_id,$art_id,$item_id;

    private function check_data() {
        if(!isset($_POST['item_title'],$_POST['cat_id'])) $this->uFunc->error(10);
        $this->item_title=trim($_POST['item_title']);
        if(empty($this->item_title)) die("{'status' : 'error', 'msg' : 'title_empty'}");

        $this->cat_id=$_POST['cat_id'];
        if(!uString::isDigits($this->cat_id)&&$this->cat_id!='no_cat') $this->uFunc->error(20);
        $this->art_id=$_POST['art_id'];
        if(!uString::isDigits($this->art_id)&&$this->art_id!='no_art') $this->uFunc->error(30);
    }
    private function attach_item2cat() {
        if($this->cat_id=='no_cat') return 0;
        $this->uCat->attach_item2cat($this->cat_id,$this->item_id);
        return 1;
    }
    private function attach_item2art() {
        if($this->art_id=='no_art') return 0;
        $this->uCat->attach_art2item($this->item_id,$this->art_id);
        return 1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->item_id = $this->uCat->create_new_item($this->item_title);
        $this->attach_item2cat();
        $this->attach_item2art();
        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo "{
        'status' : 'done',
        'item_id' : '".$this->item_id."',
        'unit_id':'".$this->unit_id."'
        }";
    }
}
new admin_items_create_bg($this);