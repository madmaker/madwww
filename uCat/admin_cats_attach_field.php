<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_cats_attach_field{
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore;
    private $cat_id,$item_id,$field_id;
    private function checkData() {
        if(!isset($_POST['field_id'],$_POST['action'])) $this->uFunc->error(10);
        if(!isset($_POST['cat_id'])&&!isset($_POST['item_id'])) $this->uFunc->error(20);
        if(isset($_POST['cat_id'])) {
            $this->cat_id=$_POST['cat_id'];
            $this->item_id=false;
            if(!uString::isDigits($this->cat_id)) $this->uFunc->error(30);
        }
        else {
            $this->cat_id=false;
            $this->item_id=$_POST['item_id'];
            if(!uString::isDigits($this->item_id)) $this->uFunc->error(40);

            $q_cats=$this->uCat->get_item_cats($this->item_id);
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $cat_obj=$q_cats->fetch(\PDO::FETCH_OBJ); $i++) $this->cat_id[$i]=$cat_obj->cat_id;
        }

        $this->field_id=$_POST['field_id'];
        if(!uString::isDigits($this->field_id)) $this->uFunc->error(50);
    }
    private function attach() {
        if(is_array($this->cat_id)) {
            for($i=0;$i<count($this->cat_id);$i++) {
                $cat_id=$this->cat_id[$i];
                //attach or detach
                if($_POST['action']=='attach') $this->uCat->attach_field2cat($this->field_id,$cat_id);
                else $this->uCat->detach_field_from_cat($this->field_id,$cat_id);
            }
        }
        else {
            if($_POST['action']=='attach') $this->uCat->attach_field2cat($this->field_id,$this->cat_id);
            else $this->uCat->detach_field_from_cat($this->field_id,$this->cat_id);
        }
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");


        $this->checkData();

        $this->attach();

        echo "{
        'status' : 'success',
        'action' : '".($_POST['action']=='attach'?'attach':'detach')."',
        }";
    }
}
new admin_cats_attach_field($this);