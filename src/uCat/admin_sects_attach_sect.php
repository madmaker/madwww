<?php
namespace uCat\admin;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class admin_sects_attach_sect{
    public $uCat;
    public $uFunc;
    public $uSes;
    public $loop_flag;
    private $uCore;
    private $parent_sect_id, $child_sect_id;

    private function checkData() {
        if(!isset($_POST['parent_sect_id'],$_POST['child_sect_id'],$_POST['action'])) $this->uFunc->error(10);
        $this->parent_sect_id=$_POST['parent_sect_id'];
        $this->child_sect_id=$_POST['child_sect_id'];
        $this->loop_flag = false;
        if(!uString::isDigits($this->parent_sect_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->child_sect_id)) $this->uFunc->error(30);
    }


    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->checkData();
        if($_POST['action']=='attach') $this->uCat->attach_sect2sect($this->parent_sect_id,$this->child_sect_id);
        else $this->uCat->detach_sectFromSect($this->parent_sect_id,$this->child_sect_id);

        echo "{
        'status' : 'success',
        'action' : '".($_POST['action']=='attach'?'attach':'detach')."',
        'sect_id' : '".$this->child_sect_id."',
        'parent_sect_id':'".$this->parent_sect_id."'
        }";
    }
}
new admin_sects_attach_sect($this);