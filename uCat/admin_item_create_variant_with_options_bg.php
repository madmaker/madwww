<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class admin_item_create_variant_with_options_bg {
    private $uCat;
    private $uSes;
    private $uFunc;
    private $item_id;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=(int)$_POST['item_id'];
    }
    private function get_item_options() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_id
            FROM 
            items_options 
            WHERE
            item_id=:item_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function check_if_value_exists($option_id,$value_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            value_id 
            FROM 
            option_values 
            WHERE 
            option_id=:option_id AND
            value_id=:value_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    private function create_option_default_value($option_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            option_values (
            option_id, 
            value, 
            site_id
            ) VALUES (
            :option_id, 
            'Новое значение', 
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $this->uFunc->pdo("uCat")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_option_default_value($option_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            `value` 
            FROM 
            option_values 
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ORDER BY value_id ASC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->value;
            else return $this->create_option_default_value($option_id);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return 0;
    }
    private function save_received_options_values($item_options_obj,$var_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0; $option=$item_options_obj->fetch(PDO::FETCH_OBJ); $i++) {
            $option_id=$option->option_id;
            $option_ar[$i]["option_id"]=$option_id;
            if(isset($_POST['option_val_'.$option_id])) {
                $value_id=$_POST['option_val_'.$option_id];
                //check if this value exists
                if($this->check_if_value_exists($option_id,$value_id)) {
                    //save this value for new item variant
                }
                else {
                    //get the default value
                    $value_id=$this->get_option_default_value($option_id);
                }
            }
            else {//option is not received - let's set default value
                $value_id=$this->get_option_default_value($option_id);
            }

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("REPLACE INTO
                variants_options_values (
                var_id,
                option_id,
                value_id,
                site_id
                ) VALUES (
                :var_id,
                :option_id,
                :value_id,
                :site_id
                )
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
    }
    private function set_new_var_type_title($var_id,$var_type_id) {
        $item_id=$this->uCat->var_id2data($var_id)->item_id;
        $this->uCat->update_all_item_variants_with_options_title($item_id);
//        $options_obj=$this->uCat->get_options_with_values($var_id);
//        $var_title_addition=". ===".$var_id."===(";
//        /** @noinspection PhpUndefinedMethodInspection */
//        while ($option = $options_obj->fetch(PDO::FETCH_OBJ)) {
//            $var_title_addition.=$option->option_name.": ".$option->value.". ";
//        }
//        $var_title_addition.=")";

//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
//            items_variants_types
//            SET
//            var_type_title=concat(var_type_title,:var_type_title)
//            WHERE
//            var_type_id=:var_type_id AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_title', $var_title_addition,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_type_id', $var_type_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

    }
    private function create_new_item_variant() {
        $item_info=$this->uCat->item_id2data($this->item_id,"item_title,item_article_number,quantity,item_type,item_price,prev_price,inaccurate_price,request_price,item_avail");
        $var_uuid=$this->uFunc->generate_uuid();
        if(!$this->uCat->has_variants($this->item_id)) $item_article_number=$item_info->item_article_number;
        else $item_article_number=0;

        $var_type_info=$this->uCat->create_variant_type($item_info->item_title,$item_info->item_type);
        $var_info=$this->uCat->add_new_variant($this->item_id,$item_article_number,$var_type_info[0],$var_uuid,$item_info->item_price,$item_info->prev_price,$item_info->quantity,$item_info->inaccurate_price,$item_info->request_price,$item_info->item_avail);
        return $var_info;
//        }
//        else {
//            $var_info=$this->uCat->add_new_variant($this->item_id,$item_info->item_type,$var_uuid,$item_info->item_price,$item_info->inaccurate_price,$item_info->request_price,$item_info->item_avail);
//            return $var_id=$var_info['var_id'];
//        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uCat=new common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $item_options_obj=$this->get_item_options();
        $var_info=$this->create_new_item_variant();
        $this->save_received_options_values($item_options_obj,$var_info['var_id']);
        $this->set_new_var_type_title($var_info['var_id'],$var_info['var_type_id']);
        echo "{
        'status':'done'
        }";
    }
}
new admin_item_create_variant_with_options_bg($this);