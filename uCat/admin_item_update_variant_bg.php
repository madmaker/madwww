<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

require_once "uDrive/classes/common.php";
require_once "uCat/classes/common.php";

class admin_item_update_variant {
    private $uSes;
    private $uFunc;
    private $uCore,$uCat,$uDrive_common,$var_id,$item_id;
    private function check_data() {
        if(!isset($_POST['var_id'],$_POST['item_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(10);
        $this->var_id=$_POST['var_id'];
        if(!uString::isDigits($this->var_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(20);

        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(21);
    }
    private function update_price($price) {
        $this->uCat->update_variant($this->var_id,"`price`='".$price."'");
        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('item_price',$price,PDO::PARAM_STR)
            ));
            echo "{
            'status':'done',
            'default_var':'1',
            'item_price':'".$price."'
            }";
            exit;
        }
    }
    private function update_prev_price($prev_price) {
        $this->uCat->update_variant($this->var_id,"`prev_price`='".$prev_price."'");
        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('prev_price',$prev_price,PDO::PARAM_STR)
            ));
            echo "{
            'status':'done',
            'default_var':'1',
            'prev_price':'".$prev_price."'
            }";
            exit;
        }
    }
    private function update_item_article_number($item_article_number) {
        $default_var=0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            items_variants
            SET 
            item_article_number=:item_article_number
            WHERE
            var_id=:var_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $this->var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('item_article_number',$item_article_number,PDO::PARAM_STR)
            ));
            $default_var=1;
        }
        echo "{
            'status':'done',
            'default_var':'".$default_var."',
            'item_article_number':'".$item_article_number."'
            }";
        exit;
    }
    private function update_quantity($quantity) {
        $this->uCat->update_variant($this->var_id,"`var_quantity`='".$quantity."'");

        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('quantity',$quantity,PDO::PARAM_INT)
            ));
            echo "{
            'status':'done',
            'default_var':'1',
            'quantity':'".$quantity."'
            }";
            exit;
        }
    }
    private function update_inaccurate_price($inaccurate_price) {
        $this->uCat->update_variant($this->var_id,"`inaccurate_price`='".$inaccurate_price."'");
        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('inaccurate_price',$inaccurate_price,PDO::PARAM_INT)
            ));

            echo "{
            'status':'done',
            'default_var':'1',
            'inaccurate_price':'".$inaccurate_price."'
            }";
            exit;
        }
    }
    private function update_request_price($request_price) {
        $this->uCat->update_variant($this->var_id,"`request_price`='".$request_price."'");
        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('request_price',$request_price,PDO::PARAM_INT)
            ));

            echo "{
            'status':'done',
            'default_var':'1',
            'request_price':'".$request_price."'
            }";
            exit;
        }
    }
    private function update_avail_id($avail_id) {
        $this->uCat->update_variant($this->var_id,"`avail_id`='".$avail_id."'");
        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            $this->uCat->item_update($this->item_id,array(
                array('item_avail',$avail_id,PDO::PARAM_INT)
            ));
        }

        $avail_type_id2class_ar=array(
            '0'=>'',
            '1'=>'text-success',
            '2'=>'text-muted',
            '3'=>'text-danger',
            '4'=>'text-warning',
            '5'=>'text-info'
        );

        echo "{
        'status':'done',
        'default_var':'".($this->uCat->is_default_item_variant($this->item_id,$this->var_id)?"1":"0")."',
        'var_id':'".$this->var_id."',
        'avail_label':'".rawurlencode($this->uCat->avail_id2avail_data($avail_id)->avail_label)."',
        'avail_class':'".$avail_type_id2class_ar[$this->uCat->avail_id2avail_data($avail_id)->avail_type_id]."',
        'avails_json':'".rawurlencode($this->uCat->get_avails_json($avail_id))."',
        'item_avail':'".$avail_id."'
        }";
        exit;
    }
    private function update_var_type_id($var_type_id) {
        $this->uCat->update_variant($this->var_id,"`var_type_id`='".$var_type_id."'");
        $base_type_id=(int)$this->uCat->item_type_id2data($this->uCat->var_type_id2data($var_type_id)->item_type_id)->base_type_id;
        $var=$this->uCat->var_id2data($this->var_id);

        if($base_type_id==1) {
            if((int)$var->file_id) {//set avail_type_id=1
                $avail_id=$this->uCat->get_any_available_avail_id();
                $this->uCat->update_variant($this->var_id,"`avail_id`='".$avail_id."'");
            }
            else {//set avail_type_id=2
                $avail_id=$this->uCat->get_any_dontshow_avail_id();
                $this->uCat->update_variant($this->var_id,"`avail_id`='".$avail_id."'");
            }
            $var=$this->uCat->var_id2data($this->var_id);
        }

        //check if this is default var of item
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) {
            if($base_type_id==1) {
                /** @noinspection PhpUndefinedVariableInspection */
                $this->uCat->item_update($this->item_id,array(
                    array('item_type',$this->uCat->var_type_id2data($var->var_type_id)->item_type_id,PDO::PARAM_INT),
                    array('item_avail',$avail_id,PDO::PARAM_INT)
                ));
            }
            else {
                $this->uCat->item_update($this->item_id,array(
                    array('item_type',$this->uCat->var_type_id2data($var->var_type_id)->item_type_id,PDO::PARAM_INT)
                ));
            }
        }
        echo "{
        'status':'done',
        'default_var':'".($this->uCat->is_default_item_variant($this->item_id,$this->var_id)?"1":"0")."',
        'var_id':'".$this->var_id."',
        'avail_id':'".$var->avail_id."',
        'base_type_id':'".$base_type_id."',
        ".($base_type_id==0?
                "'avails_json':'".rawurlencode($this->uCat->get_avails_json($var->avail_id))."',
                'avail_label':'".rawurlencode($this->uCat->avail_id2avail_data($var->avail_id)->avail_label)."',
                'avail_descr':'".rawurlencode($this->uCat->avail_id2avail_data($var->avail_id)->avail_descr)."',
                'avail_class':'".$this->uCat->avail_type_id2class($this->uCat->avail_id2avail_data($var->avail_id)->avail_type_id)."',
                'avail_type_id':'".rawurlencode($this->uCat->avail_id2avail_data($var->avail_id)->avail_type_id)."',
                "
                :
                (
                    "'file_id':'".$var->file_id."',
                ".((int)$var->file_id?"'file_hashname':'".$this->uDrive_common->file_id2data($var->file_id)->file_hashname."',
                'file_name':'".rawurlencode($this->uDrive_common->file_id2data($var->file_id)->file_name)."',":'')
                )
            )."
        'var_type_title':'".rawurlencode($this->uCat->var_type_id2data($var_type_id)->var_type_title)."',
        'item_type':'".$this->uCat->var_type_id2data($var_type_id)->item_type_id."',
        'item_type_title':'".rawurlencode($this->uCat->item_type_id2data($this->uCat->var_type_id2data($var_type_id)->item_type_id)->type_title)."',
        'var_types_json':'".rawurlencode($this->uCat->get_var_types_json($var->var_type_id))."'
        }";
        exit;
    }
    private function delete_variant() {
        //check if this var is default one
        if($this->uCat->is_default_item_variant($this->item_id,$this->var_id)) $default_var=1;
        else $default_var=0;

        //delete variant
        $this->uCat->delete_variant($this->var_id);

        if($default_var) {//get new item data
            $get_sql="`item_avail`,
                `item_price`,
                `inaccurate_price`,
                `request_price`,
                `item_type`,
                `file_id`";
            $item=$this->uCat->item_id2data($this->item_id,$get_sql);

            //get new default variant
            $new_var_id=$this->uCat->item_id2default_variant_id($this->item_id);
        }

        if(!$this->uCat->has_variants($this->item_id)) {//item hasn't variants anymore
            $variants_left=0;
        }
        else {
            $variants_left=1;
        }

        /** @noinspection PhpUndefinedVariableInspection */
        echo "{
            'status':'done',
            'variants_left':'".$variants_left."',";

        if($default_var) {
            echo "
            'item_avail':'" . $item->item_avail . "',
            'item_price':'" . $item->item_price . "',
            'inaccurate_price':'" . $item->inaccurate_price . "',
            'request_price':'" . $item->request_price . "',
            'item_type':'" . $item->item_type . "',
            'base_type_id':'" . $this->uCat->item_type_id2data($item->item_type)->base_type_id . "',
            'file_id':'" . $item->file_id . "',";

            $file = $this->uDrive_common->file_id2data($item->file_id,"file_hashname,file_name");
            if ($file) echo "
            'file_name':'" . rawurlencode($file->file_name) . "',
            'file_hashname':'" . rawurlencode($file->file_hashname) . "',
            ";

            echo "'var_id':'" . $new_var_id . "',";
        }
        echo "'default_var':'".$default_var."'
            }";
        exit;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uCat=new common($this->uCore);
        $this->uDrive_common=new \uDrive\common($this->uCore);

        $this->check_data();

        if(isset($_POST['price'])) {
            $price=trim($_POST['price']);
            if(!uString::isFloat($price)) die("{
            'status':'error',
            'field':'price',
            'msg':'wrong format'
            }");

            $this->update_price($price);
        }
        elseif(isset($_POST['prev_price'])) {
            $prev_price=trim($_POST['prev_price']);
            if(!uString::isFloat($prev_price)) die("{
            'status':'error',
            'field':'prev_price',
            'msg':'wrong format'
            }");

            $this->update_prev_price($prev_price);
        }
        elseif(isset($_POST['item_article_number'])) {
            $item_article_number=trim($_POST['item_article_number']);
            if(!mb_strlen($item_article_number)) $item_article_number=$this->var_id;
            $this->update_item_article_number($item_article_number);
        }
        elseif(isset($_POST['quantity'])) {
            $quantity=trim($_POST['quantity']);
            $this->update_quantity($quantity);
        }
        elseif(isset($_POST['inaccurate_price'])) {
            $inaccurate_price=$_POST['inaccurate_price']=='1'?1:0;
            $this->update_inaccurate_price($inaccurate_price);
        }
        elseif(isset($_POST['request_price'])) {
            $request_price=$_POST['request_price']=='1'?1:0;
            $this->update_request_price($request_price);
        }
        elseif(isset($_POST['avail_id'])) {
            $avail_id=$_POST['avail_id'];
//            if(!uString::isDigits($avail_id));
            $this->update_avail_id($avail_id);
        }
        elseif(isset($_POST['var_type_id'])) {
            $var_type_id=$_POST['var_type_id'];
//            if(!uString::isDigits($var_type_id));
            $this->update_var_type_id($var_type_id);
        }
        elseif(isset($_POST['delete'])) {
            $this->delete_variant();
        }
        else $this->uFunc->error(50);

        echo "{
        'status':'done',
        'default_var':'0'
        }";
    }
}
new admin_item_update_variant($this);