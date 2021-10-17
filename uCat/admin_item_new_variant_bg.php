<?php
namespace uCat\item;
use processors\uFunc;
use uCat\common;
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
require_once "uDrive/classes/common.php";

class new_variant {
    public $uSes;
    public $uFunc;
    private $uCore,$item_id,$var_type_id,
$uCat_common,$uDrive_common;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['var_type_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!\uString::isDigits($this->item_id)) $this->uFunc->error(11);
        $this->var_type_id=$_POST['var_type_id'];
        if(!\uString::isDigits($this->var_type_id)) $this->uFunc->error(20);

        //check if this var_type_id exists
        $var_type_ar=$this->uCat_common->var_type_id2data($this->var_type_id);
        if(!$var_type_ar) $this->uFunc->error(30);

        //check if this variant is already attached
        if($this->uCat_common->has_variant($this->item_id,$this->var_type_id)) die("{
        'status':'error',
        'var_id':'".$this->uCat_common->var_type_id2var_id($this->var_type_id,$this->item_id)."',
        'msg':'already has this variant'
        }");
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uCat_common=new common($this->uCore);
        $this->uDrive_common=new \uDrive\common($this->uCore);

        $this->check_data();

        $has_variants=$this->uCat_common->has_variants($this->item_id);
        if(!$has_variants) {//write current item's data to new variant
            $this->uCat_common->setup_item_initial_variant($this->item_id);
        }

        $var_ar=$this->uCat_common->add_new_variant($this->item_id,0,$this->var_type_id);
        $item_type_id=$this->uCat_common->var_type_id2data($this->var_type_id)->item_type_id;

        echo "{
        'status':'done',
        'has_variants':'".$has_variants."',
        'var_id':'".$var_ar['var_id']."',
        'item_article_number':'".rawurlencode($var_ar['item_article_number'])."',
        'uuid_variant':'".$var_ar['uuid_variant']."',
        'var_type_title':'".rawurlencode(\uString::sql2text($this->uCat_common->var_type_id2data($this->var_type_id)->var_type_title,1))."',
        'item_type_title':'".$this->uCat_common->item_type_id2data($item_type_id)->type_title."',
        'price':'".$var_ar['price']."',
        'prev_price':'".$var_ar['prev_price']."',
        'var_quantity':'".$var_ar['var_quantity']."',
        'inaccurate_price':'".$var_ar['inaccurate_price']."',
        'request_price':'".$var_ar['request_price']."',
        'avail_id':'".$var_ar['avail_id']."',
        'avail_type_id':'".$this->uCat_common->avail_id2avail_data($var_ar['avail_id'])->avail_type_id."',
        'avail_label':'".rawurlencode($this->uCat_common->avail_id2avail_data($var_ar['avail_id'])->avail_label)."',
        'var_types_json':'".rawurlencode($this->uCat_common->get_var_types_json($this->var_type_id))."',
        'base_type_id':'".$this->uCat_common->item_type_id2data($this->uCat_common->var_type_id2data($this->var_type_id)->item_type_id)->base_type_id."',
        'avails_json':'".rawurlencode($this->uCat_common->get_avails_json($var_ar['avail_id']))."',
        'file_id':'".$var_ar['file_id']."',
        ".($var_ar['file_id']!='0'?
                ("'file_hashname':'".$this->uDrive_common->file_id2data($var_ar['file_id'])->file_hashname."',
                'file_name':'".rawurlencode($this->uDrive_common->file_id2data($var_ar['file_id'])->file_name)."'")
            :'').
        "}";
    }
}
new new_variant($this);