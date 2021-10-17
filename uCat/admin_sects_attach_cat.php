<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";
class uCat_admin_sects_attach_cat {
    public $uCat;
    public $uFunc;
    public $uSes;
    private $uCore;
    private $cat_id,$sect_id;

    private function checkData() {
        if(!isset($_POST['cat_id'],$_POST['sect_id'],$_POST['action'])) $this->uFunc->error(10);
        $this->cat_id=$_POST['cat_id'];
        $this->sect_id=$_POST['sect_id'];
        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->sect_id)) $this->uFunc->error(30);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();

        if($_POST['action']=='attach') $this->uCat->attach_cat2sect($this->sect_id,$this->cat_id);
        else $this->uCat->detach_sectFromCat($this->sect_id,$this->cat_id);

        echo "{
        'status' : 'success',
        'action' : '".($_POST['action']=='attach'?'attach':'detach')."',
        'sect_id' : '".$this->sect_id."',
        'cat_id':'".$this->cat_id."'
        }";
    }
}
new uCat_admin_sects_attach_cat($this);