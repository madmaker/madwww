<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class uCat_admin_cats_attach_item {
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore;
    private $cat_id,$item_id;
    private function checkData() {
        if(!isset($_POST['cat_id'],$_POST['item_id'],$_POST['action'])) $this->uFunc->error(10);
        $this->cat_id=$_POST['cat_id'];
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(30);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();

        if($_POST['action']=='attach') $this->uCat->attach_item2cat($this->cat_id,$this->item_id);
        else $this->uCat->detach_itemFromCat($this->cat_id,$this->item_id);

        echo "{
        'status' : 'success',
        'action' : '".($_POST['action']=='attach'?'attach':'detach')."',
        'cat_id' : '".$this->cat_id."',
        'item_id':'".$this->item_id."'
        }";
    }
}
new uCat_admin_cats_attach_item($this);