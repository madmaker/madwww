<?php
namespace uCat\item;
use uCat\common;
require_once 'uCat/classes/common.php';

class new_variant_type {
    private $uCore,$item_id,$item_type_id,$var_type_title,
$uCat_common;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['var_type_title'],$_POST['item_type_id'])) $this->uCore->error(10);
        $this->item_id=$_POST['item_id'];
        if(!\uString::isDigits($this->item_id)) $this->uCore->error(11);
        $this->item_type_id=$_POST['item_type_id'];
        if(!\uString::isDigits($this->item_type_id)) $this->uCore->error(20);
        $this->var_type_title=trim($_POST['var_type_title']);
        if(!strlen($this->var_type_title)) {
            die("{
            'status':'error',
            'msg':'title is empty'
            }");
        }

        //check if this type_id exists
        $q_type=$this->uCat_common->item_type_id2data($this->item_type_id);
        if(!$q_type) $this->uCore->error(30);

        //check if this item_id exists
        if(!$this->uCat_common->item_exists($this->item_id)) $this->uCore->error(40);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->uCat_common=new common($this->uCore);
        $this->check_data();
        $var_type_ar=$this->uCat_common->create_variant_type($this->var_type_title,$this->item_type_id);
        $var_type_id=$var_type_ar[0];

        //then let's add this var_type to this item

        echo "{
        'status':'done',
        'var_type_id':'".$var_type_id."',
        'var_type_title':'".rawurlencode($this->var_type_title)."'
        }";
    }
}
new new_variant_type($this);