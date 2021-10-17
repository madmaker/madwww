<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_items_attach_art{
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore;
    private $art_id,$item_id;
    private function checkData() {
        if(!isset($_POST['art_id'],$_POST['item_id'],$_POST['action'])) $this->uFunc->error(10);
        $this->art_id=$_POST['art_id'];
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->art_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(30);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();

        if($_POST['action']=='attach') $this->uCat->attach_art2item($this->item_id,$this->art_id);
        else $this->uCat->detach_art_from_item($this->item_id,$this->art_id);

        echo "{
        'status' : 'success',
        'action' : '".($_POST['action']=='attach'?'attach':'detach')."',
        'art_id' : '".$this->art_id."',
        'item_id':'".$this->item_id."'
        }";
    }
}
new admin_items_attach_art($this);