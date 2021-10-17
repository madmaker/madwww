<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

require_once 'uCat/inc/admin_count_helper.php';
require_once 'uCat/classes/common.php';
require_once 'uDrive/classes/common.php';

class admin_items_edit_bg {
    public $uFunc;
    public $uSes;
    private $uCore,
$item_id,$field,$value,$additional,
$uDrive_common,$uCat;

    private function check_data() {
        if(!isset($_POST['field'],$_POST['value'],$_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=&$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(20);

        $this->field=&$_POST['field'];
        $this->value=&$_POST['value'];
        $this->additional='';
    }
    private function update_uDrive_folder_name() {
        //get uDrive_folder_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            uDrive_folder_id
            FROM
            u235_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $file_name=trim(uString::sanitize_filename($this->value));
            if(!strlen($file_name)) $file_name='Товар '.$this->item_id;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("UPDATE
                u235_files
                SET
                file_name=:file_name
                WHERE
                file_id=:file_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_name', $file_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $qr->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
    }

    private function update_seo() {
        $seo_title=$_POST['value'];
        $seo_descr=$_POST['seo_descr'];

        $seo_title=str_replace('"',"",$seo_title);
        $seo_title=str_replace('\'',"",$seo_title);
        $seo_title=uString::text2sql($seo_title);

        $seo_descr=str_replace('"',"",$seo_descr);
        $seo_descr=str_replace('\'',"",$seo_descr);
        $seo_descr=uString::text2sql($seo_descr);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            seo_title=:seo_title,
            seo_descr=:seo_descr
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':seo_title', $seo_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':seo_descr', $seo_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'seo_title' : '".rawurlencode($seo_title)."',
        'seo_descr' : '".rawurlencode($seo_descr)."'
        }";
    }
    private function update_avail() {
        if(!uString::isDigits($this->value)) $this->uFunc->error(60);
        //get avail_title
        $avail=$this->uCat->avail_id2avail_data($this->value);
        if(!$avail) $this->uFunc->error(70);
        $this->additional=",'avail_type_id':'".$avail->avail_type_id."','avail_label':'".rawurlencode(uString::sql2text($avail->avail_label,true))."','avail_descr':'".rawurlencode(uString::sql2text($avail->avail_descr,true))."'";

        $this->uCat->item_update($this->item_id,array(
            array($this->field,$this->value,PDO::PARAM_INT)
        ));

        //get affected cats
        $q_cats=$this->uCat->get_item_cats($this->item_id);
        //update cat's counters
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$data=$q_cats->fetch(PDO::FETCH_OBJ);$i++) {
            //update cat's item_count
            $this->uCat->calculate_cat_item_count($data->cat_id);
            //update cat's sect_count
            $this->uCat->calculate_cat_sect_count($data->cat_id);
        }

        $this->uCat->calculate_item_parents_item_count($this->item_id);

        //Update default variant
        if($this->uCat->has_variants($this->item_id)) {
            $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
            if($var_id) {
                $this->uCat->update_variant($var_id,"`avail_id`='".$this->value."'");
            }
        }

        echo "{
        'status' : 'done',
        '".$this->field."' : '".rawurlencode($this->value)."'
        ".$this->additional."
        }";
    }
    private function update_price() {
        $this->value=str_replace(',','.',$this->value);
        if(!uString::isFloat($this->value)) die("{'status' : 'error', 'msg' : 'not a price'}");

        $this->value=uString::sql2text($this->value);

        //Update default variant
        if($this->uCat->has_variants($this->item_id)) {
            $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
            if($var_id) {
                $this->uCat->update_variant($var_id,"`price`='".$this->value."'");
            }
        }
    }
    private function update_prev_price() {
        $this->value=str_replace(',','.',$this->value);
        if(!uString::isFloat($this->value)) die("{'status' : 'error', 'msg' : 'not a price'}");

        $this->value=uString::sql2text($this->value);

        //Update default variant
        if($this->uCat->has_variants($this->item_id)) {
            $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
            if($var_id) {
                $this->uCat->update_variant($var_id,"`prev_price`='".$this->value."'");
            }
        }
    }
    private function update_request_price() {
                //Update default variant
        if($this->uCat->has_variants($this->item_id)) {
            $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
            if($var_id) {
                $this->uCat->update_variant($var_id,"`request_price`='".$this->value."'");
            }
        }
    }
    private function update_inaccurate_price() {
        //Update default variant
        if($this->uCat->has_variants($this->item_id)) {
            $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
            if($var_id) {
                $this->uCat->update_variant($var_id,"`inaccurate_price`='".$this->value."'");
            }
        }
    }
    private function update_item_type() {
        if(!uString::isDigits($this->value)) $this->uFunc->error(80);

        //check if selected item type exists
        $item_type = $this->uCat->item_type_id2data($this->value);
        if (!$item_type) $this->uFunc->error(90);

        if ((int)$item_type->base_type_id == 1) {//is file
            if($this->uCat->has_variants($this->item_id)) {//update default var
                $def_var_id=(int)$this->uCat->item_id2default_variant_id($this->item_id);
                if(!$def_var_id) $this->uFunc->error(100);
                if(!$def_var_data=$this->uCat->var_id2data($def_var_id)) $this->uFunc->error(110);

                if ((int)$def_var_data->file_id) {//set availability to available
                    $avail_id = $this->uCat->get_any_available_avail_id();
                    $this->uCat->item_update($this->item_id, array(
                        array('item_avail', $avail_id, PDO::PARAM_INT)
                    ));
                    $this->uCat->update_variant($this->item_id, "`avail_id`='.$avail_id.'");
                }
                else {//set availability to dontshow
                    $avail_id = $this->uCat->get_any_dontshow_avail_id();
                    $this->uCat->item_update($this->item_id, array(
                        array('item_avail', $avail_id, PDO::PARAM_INT)
                    ));
                    $this->uCat->update_variant($this->item_id, "`avail_id`='.$avail_id.'");
                }
            }
            else {
                //check if this item has file attached
                if ((int)$this->uCat->item_id2data($this->item_id, "`file_id`")->file_id) {//set availability to available
                    $avail_id = $this->uCat->get_any_available_avail_id();
                    $this->uCat->item_update($this->item_id, array(
                        array('item_avail', $avail_id, PDO::PARAM_INT)
                    ));
                }
                else {//set availability to dontshow
                    $avail_id = $this->uCat->get_any_dontshow_avail_id();
                    $this->uCat->item_update($this->item_id, array(
                        array('item_avail', $avail_id, PDO::PARAM_INT)
                    ));
                }
            }
        }

        if (!isset($avail_id)) $avail_id = $this->uCat->item_id2data($this->item_id, "`item_avail`")->item_avail;
        $avail_type_id = $this->uCat->avail_id2avail_data($avail_id)->avail_type_id;
        $avail_label = $this->uCat->avail_id2avail_data($avail_id)->avail_label;
        $avail_descr = $this->uCat->avail_id2avail_data($avail_id)->avail_descr;
        $avail_class = $this->uCat->avail_type_id2class($avail_type_id);

        $this->additional=",
        'base_type_id':'".$item_type->base_type_id."',
        'avail_id':'".$avail_id."',
        'avail_type_id':'".$avail_type_id."',
        'avail_label':'".rawurlencode($avail_label)."',
        'avail_descr':'".rawurlencode($avail_descr)."',
        'avail_class':'".$avail_class."'
        ";
    }
    private function update_item_def_var() {
        $var=$this->uCat->set_default_variant($this->item_id,$this->value);
        if(!isset($this->uDrive_common)) $this->uDrive_common=new uDrive\common($this->uCore);

        $avail_type_id=$this->uCat->avail_id2avail_data($var->avail_id)->avail_type_id;
        echo "{
        'status'                : 'done',
        'var_id'                : '".$this->value."',
        'item_article_number'   : '".$var->item_article_number."',
        'item_price'            : '".$var->price."',
        'prev_price'            : '".$var->prev_price."',
        'quantity'            : '".$var->var_quantity."',
        'inaccurate_price'      : '".$var->inaccurate_price."',
        'request_price'         : '".$var->request_price."',
        'item_avail'            : '".$var->avail_id."',
        'avail_class'           : '".$this->uCat->avail_type_id2class($avail_type_id)."',
        'avail_type_id'         : '".$avail_type_id."',
        ".($this->uCat->item_type_id2data($var->item_type_id)->base_type_id=='1'?("
        'file_id'               : '".$var->file_id."',
        ".((int)$var->file_id?("
        'file_name'             : '".rawurlencode($this->uDrive_common->file_id2data($var->file_id)->file_name)."',
        'file_hashname'         : '".rawurlencode($this->uDrive_common->file_id2data($var->file_id)->file_hashname)."',
        "):"")."
        "):"")."
        'item_type'             : '".$var->item_type_id."',
        'base_type_id'          : '".$this->uCat->item_type_id2data($var->item_type_id)->base_type_id."'
        }";
        exit;
    }
    private function update_file_id() {
        if(!isset($_POST['var_id'])) $this->uFunc->error(115);

        $var_id=$_POST['var_id'];
        if(!uString::isDigits($var_id)) $this->uFunc->error(120);
        $var_id=(int)$var_id;

        if(!uString::isDigits($this->value)) $this->uFunc->error(130);

        if(!isset($this->uDrive_common)) $this->uDrive_common=new uDrive\common($this->uCore);
        if($this->value) {
            //check if selected file exists
            if(!$this->uDrive_common->file_exists($this->value)) $this->uFunc->error(140);
            //update file_access
            $this->uDrive_common->update_file_access($this->value,1);
            /*$usage_id=*/$this->uDrive_common->add_file_usage($this->value,'uCat','item',$this->item_id);
        }

        if($var_id) {//we're editing variant
            if((int)$this->value) {//set availability to available
                $avail_id=$this->uCat->get_any_available_avail_id();
            }
            else {//set availability to dontshow
                $avail_id=$this->uCat->get_any_dontshow_avail_id();
            }

            $this->uCat->update_variant($var_id,"`file_id`='".$this->value."', `avail_id`='".$avail_id."'");

            //check if this is default var of item
            if($this->uCat->is_default_item_variant($this->item_id,$var_id)) {
                $this->uCat->item_update($this->item_id,array(
                    array('file_id',$this->value,PDO::PARAM_INT),
                    array('item_avail',$avail_id,PDO::PARAM_INT)
                ));
            }

            echo "{
            'status' : 'done',
            'default_var':'".($this->uCat->is_default_item_variant($this->item_id,$var_id)?"1":"0")."',
            'var_id' : '".$var_id."',
            ".((int)$this->value?
                    "'file_id' : '".rawurlencode($this->value)."',
                    'file_name' : '".rawurlencode($this->uDrive_common->file_id2data($this->value)->file_name)."',
                    'file_hashname' : '".rawurlencode($this->uDrive_common->file_id2data($this->value)->file_hashname)."'"
                    :
                    ""
                )."
            }";
            exit;
        }
        else {//we're editing item directly
            if((int)$this->value) {//set availability to available
                $avail_id=$this->uCat->get_any_available_avail_id();
                $this->uCat->item_update($this->item_id,array(
                    array('item_avail',$avail_id,PDO::PARAM_INT)
                ));
            }
            else {//set availability to dontshow
                $avail_id=$this->uCat->get_any_dontshow_avail_id();
                $this->uCat->item_update($this->item_id,array(
                    array('item_avail',$avail_id,PDO::PARAM_INT)
                ));
            }


            if($this->uCat->has_variants($this->item_id)) {
                $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
                if($var_id) {
                    $this->uCat->update_variant($var_id,"`file_id`='".$this->value."', `avail_id`='".$avail_id."'");
                }
            }
        }
    }
    private function update_widgets($item_id,$site_id=site_id) {
        $widgets=$this->value;


        $q_wgts="";
        for($i=0;$i<8;$i++) $q_wgts.="wgt_".$i."=:wgt_".$i.",";
        $q_wgts=substr($q_wgts,0,-1);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE  
            items_widgets
            SET
            ".$q_wgts."
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            for($i=0;$i<8;$i++) {
                $widgets[$i]=(int)$widgets[$i]?1:0;
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':wgt_'.$i,$widgets[$i],PDO::PARAM_INT);
            }
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_item_article_number($item_article_number) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items 
            SET 
            item_article_number=:item_article_number
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}
    }
    private function update_quantity($quantity) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items 
            SET 
            quantity=:quantity
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':quantity', $quantity,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}
    }
    private function update_unit_id($unit_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items 
            SET 
            unit_id=:unit_id
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':unit_id', $unit_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
    }

    private function update_value() {
        $status = $this->uCat->item_update($this->item_id,array(
            array($this->field,$this->value,PDO::PARAM_INT)
        ));

        if(
            $this->field=="item_title"||
            $this->field=="item_keywords"||
            $this->field=="item_url"||
            $this->field=="item_keywords"||
            $this->field=="item_descr"||
            $this->field=="seo_descr"
        ) $this->value=uString::sql2text($this->value,1);

        if ($status == 0) {
            echo "{
            'status' : 'done',
            '" . $this->field . "' : '" . rawurlencode($this->value) . "'" . $this->additional . "
            }";
        }
        else {
            echo "{
            'status' : 'found',
            '" . $this->field . "' : '" . rawurlencode($this->value) . "'" . $this->additional . "
            }";
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->uCat=new uCat\common($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();

        if($this->field=='item_title') {
            $this->value=trim($this->value);
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'title_empty'}");
            $this->value=uString::text2sql($this->value);
            $this->update_uDrive_folder_name();
        }
        elseif($this->field=='item_article_number') {
            $this->value=trim($this->value);
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'empty'}");
            $this->update_item_article_number($this->value);
            //update def var
            if($this->uCat->has_variants($this->item_id)) {
                $var_id=(int)$this->uCat->item_id2default_variant_id($this->item_id);
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
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $this->value,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}
            }
            echo "{
            'status' : 'done',
            'item_article_number' : '" . rawurlencode($this->value) . "'
            }";
            exit;

        }
        elseif($this->field=='unit_id') {
            $this->value=trim($this->value);
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'empty'}");
            $this->update_unit_id($this->value);
            echo "{
            'status' : 'done',
            'unit_id' : '" . $this->value . "',
            'unit_name' : '" . rawurlencode($this->uCat->unit_id2unit_name($this->value)) . "'
            }";
            exit;

        }
        elseif($this->field=='quantity') {
            $this->value=trim($this->value);
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'empty'}");
            $this->update_quantity($this->value);
            echo "{
            'status' : 'done',
            'quantity' : '" . rawurlencode($this->value) . "'
            }";
            exit;

        }
        elseif($this->field=='item_url') $this->value=uString::text2sql($this->value);
        elseif($this->field=='item_keywords') {
            $this->value=str_replace('"',"",$this->value);
            $this->value=str_replace('\'',"",$this->value);
            $this->value=uString::text2sql($this->value);
        }
        elseif($this->field=='item_descr') $this->value=uString::text2sql($this->value);
        elseif($this->field=='seo_title') {
            $this->update_seo();
            exit;
        }
        elseif($this->field=='seo_descr') $this->value=uString::text2sql($this->value);
        elseif($this->field=='item_avail') {
            $this->update_avail();
            exit;
        }
        elseif($this->field=='inaccurate_price') {
            $this->value=(int)$this->value;
            if($this->value) $this->value=1;
            $this->update_inaccurate_price();
        }
        elseif($this->field=='request_price') {
            $this->value=(int)$this->value;
            if($this->value) $this->value=1;
            $this->update_request_price();
        }
        elseif($this->field=='item_price') $this->update_price();
        elseif($this->field=='prev_price') $this->update_prev_price();
        elseif($this->field=='item_type') $this->update_item_type();
        elseif($this->field=='item_def_var') {
            if(!uString::isDigits($this->value)) $this->uFunc->error(210);
            $this->update_item_def_var();
        }
        elseif($this->field=='file_id') $this->update_file_id();
        elseif($this->field=='delete_avatar') {
            if(!isset($_POST['var_id'])) $this->uFunc->error(220);
            $var_id=(int)$_POST['var_id'];

            if($var_id) {//Update variant avatar timestamp
                $this->uCat->reset_img_time_for_var($var_id,$this->item_id);
                $this->uFunc->rmdir('uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$var_id);

                //check if this is default variant for current item
                if($this->uCat->is_default_item_variant($this->item_id,$var_id)) {
                    $this->uCat->reset_img_time_for_item($this->item_id);
                    $this->uFunc->rmdir('uCat/item_avatars/'.site_id.'/'.$this->item_id);
                }
            }
            else {//Update item avatar timestamp
                $this->uCat->reset_img_time_for_item($this->item_id);
                $this->uFunc->rmdir('uCat/item_avatars/'.site_id.'/'.$this->item_id);

                if($this->uCat->has_variants($this->item_id)) {
                    //update default var avatar
                    $var_id=$this->uCat->item_id2default_variant_id($this->item_id);
                    $this->uCat->reset_img_time_for_var($var_id,$this->item_id);
                    $this->uFunc->rmdir('uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$var_id);
                }
            }

            echo "{
            'status' : 'done',
            'var_id':'".$var_id."'
            }";
            exit;
        }
        elseif($this->field=='widgets') $this->update_widgets($this->item_id,site_id);
        elseif(strpos($this->field,'field_')===0) {//now only for rich text
            $this->field=str_replace('field_','',$this->field);
            if(!uString::isDigits(($this->field))) $this->uFunc->error(230);
            $this->field='field_'.$this->field;

            $this->value=uString::text2sql($this->value);
        }
        else $this->uFunc->error(240);

        $this->update_value();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
}
new admin_items_edit_bg($this);