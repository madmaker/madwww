<?php
namespace uCat\yandexmarket;
use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use uCat\common;
use item_avatar;
use uString;
use XLSXWriter;

ini_set("memory_limit","256M");
set_time_limit(600);

require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
require_once "uCat/inc/item_avatar.php";

class marketYandexRu {
    public $returnHtml;
    private $avatar;
    private $item_types_ar;
    private $items;
    private $availabilities_ar;
    private $fields_ar;
    private $uCat;
    private $uFunc;
    private $uCore;

    private function return_error($heading, $text) {
        $this->returnHtml='<div class="jumbotron">
        <h1 class="page-header">'.$heading.'</h1>
        <p>'.$text.'</p>
        </div>';
        return false;
    }

    private function get_site_fields() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_id,
            field_title,
            field_units,
            field_type_id,
            field_pos,
            field_place_id
            FROM 
            u235_fields 
            WHERE 
            site_id=:site_id
            ORDER BY 
            field_place_id , 
            field_pos
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $q_fields=$stm->fetchAll(PDO::FETCH_OBJ);
            unset($stm);
            return $q_fields;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_allowed_availabilities() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            avail_id
            FROM 
            u235_items_avail_values
            JOIN
            u235_items_avail_types
            ON
            u235_items_avail_types.avail_type_id=u235_items_avail_values.avail_type_id
            WHERE
            (
              u235_items_avail_types.avail_type_id=1 OR /*В наличии*/ 
              u235_items_avail_types.avail_type_id=4 OR /*Под заказ*/
              u235_items_avail_types.avail_type_id=5 /*Мало*/
            ) AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $q_avails=$stm->fetchAll(PDO::FETCH_OBJ);
            unset($stm);
            return $q_avails;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_allowed_item_types() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            type_id
            FROM 
            items_types
            WHERE
            base_type_id=0 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_items_without_variants($q_fields,$q_quantity_where,$q_availabilities,$q_item_types) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id,
            item_img_time,
            item_title,
            item_descr,
            item_url,
            item_price,
            prev_price,
            item_article_number,
            primary_cat_id,
            item_avail,
            yandex_description,
            manufactured_in,
            manufacturer_warranty,
            manufacturer,
            buy_without_order_on,
            pickup_on,
            delivery_time,
            delivery_cost,
            delivery_on,
            quantity
            ".$q_fields."
            FROM 
            u235_items 
            WHERE
            parts_autoadd=0 AND
            ".$q_availabilities."
            ".$q_quantity_where."
            ".$q_item_types."
            request_price=0 AND
            inaccurate_price=0 AND
            item_status=1 AND
            has_variants=0 AND
            item_price>0 AND
            item_img_time>0 AND
            upload_to_yandex_market=1 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_items_with_variants($q_fields) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id,
            item_title,
            item_descr,
            item_url,
            primary_cat_id,
            yandex_description,
            manufactured_in,
            manufacturer_warranty,
            manufacturer,
            buy_without_order_on,
            pickup_on,
            delivery_time,
            delivery_cost,
            delivery_on,
            quantity
            ".$q_fields."
            FROM 
            u235_items 
            WHERE
            parts_autoadd=0 AND
            item_status=1 AND
            has_variants=1 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_item_variants($item_id,$q_availabilities_for_variants,$q_variant_quantity_where,$q_item_types_for_variants) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id,
            var_id,
            img_time,
            price,
            prev_price,
            item_article_number,
            var_type_title,
            avail_id,
            var_quantity AS quantity
            FROM
            items_variants
            JOIN
            items_variants_types
            ON
            items_variants_types.var_type_id=items_variants.var_type_id AND
            items_variants.site_id=items_variants_types.site_id
            WHERE
            item_id=:item_id AND
            ".$q_item_types_for_variants."
            ".$q_availabilities_for_variants."
            ".$q_variant_quantity_where."
            request_price=0 AND
            inaccurate_price=0 AND
            hidden=0 AND
            price>0 AND
            img_time>0 AND
            items_variants.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        return array();
    }
    private function get_items() {
        //FIELDS
        $fields_count=count($this->fields_ar);
        $q_fields="";
        for($i=0;$i<$fields_count;$i++) {
            $q_fields.=", field_".$this->fields_ar[$i]->field_id;
        }

        //QUANTITY
        if((int)$this->uFunc->getConf("item_quantity_show","uCat")) {
            $q_quantity_where=" quantity>0 AND";
            $q_variant_quantity_where=" var_quantity>0 AND";
        }
        else $q_quantity_where=$q_variant_quantity_where="";

        //AVAILABILITIES
        $q_availabilities=$q_availabilities_for_variants="(";
        $availabilities_count=count($this->availabilities_ar);
        for($i=0;$i<$availabilities_count;$i++) {
            $q_availabilities.=" item_avail=".$this->availabilities_ar[$i]->avail_id;
            $q_availabilities_for_variants.=" avail_id=".$this->availabilities_ar[$i]->avail_id;
            if($i+1<$availabilities_count) {
                $q_availabilities.=" OR ";
                $q_availabilities_for_variants.=" OR ";
            }
        }
        $q_availabilities.=") AND ";
        $q_availabilities_for_variants.=") AND ";

        //ITEM TYPES
        $q_item_types=$q_item_types_for_variants="(";
        $types_count=count($this->item_types_ar);
        for($i=0;$i<$types_count;$i++) {
            $q_item_types.=" item_type=".$this->item_types_ar[$i]->type_id;
            $q_item_types_for_variants.=" item_type_id=".$this->item_types_ar[$i]->type_id;
            if($i+1<$types_count) {
                $q_item_types.=" OR ";
                $q_item_types_for_variants.=" OR ";
            }
        }
        $q_item_types.=") AND ";
        $q_item_types_for_variants.=") AND ";

        $items=$this->get_items_without_variants($q_fields,$q_quantity_where,$q_availabilities,$q_item_types);

        $items_with_variants=$this->get_items_with_variants($q_fields);
        $items_with_variants_count=count($items_with_variants);
        $variants=array();
        for($i=0;$i<$items_with_variants_count;$i++) {
            $variants_ar=$this->get_item_variants($items_with_variants[$i]->item_id,$q_availabilities_for_variants,$q_variant_quantity_where,$q_item_types_for_variants);
            $variants_count=count($variants_ar);
            for($j=0;$j<$variants_count;$j++) {
                $k=count($variants);
                $variants[$k]=new stdClass();
//                $variants[$k]=$items_with_variants[$i];

                $variants[$k]->item_id=$items_with_variants[$i]->item_id;
                $variants[$k]->item_title=$items_with_variants[$i]->item_title;
                $variants[$k]->item_descr=$items_with_variants[$i]->item_descr;
                $variants[$k]->item_url=$items_with_variants[$i]->item_url;
                $variants[$k]->primary_cat_id=$items_with_variants[$i]->primary_cat_id;
                $variants[$k]->yandex_description=$items_with_variants[$i]->yandex_description;
                $variants[$k]->manufactured_in=$items_with_variants[$i]->manufactured_in;
                $variants[$k]->manufacturer_warranty=$items_with_variants[$i]->manufacturer_warranty;
                $variants[$k]->manufacturer=$items_with_variants[$i]->manufacturer;
                $variants[$k]->buy_without_order_on=$items_with_variants[$i]->buy_without_order_on;
                $variants[$k]->pickup_on=$items_with_variants[$i]->pickup_on;
                $variants[$k]->delivery_time=$items_with_variants[$i]->delivery_time;
                $variants[$k]->delivery_cost=$items_with_variants[$i]->delivery_cost;
                $variants[$k]->delivery_on=$items_with_variants[$i]->delivery_on;

                $fields_count=count($this->fields_ar);
                for($m=0;$m<$fields_count;$m++) {
                    $field_id="field_".$this->fields_ar[$m]->field_id;
                    $variants[$k]->$field_id= $items_with_variants[$m]->$field_id;
                };
                $variants[$k]->item_price=$variants_ar[$j]->price;
                $variants[$k]->prev_price=$variants_ar[$j]->prev_price;
                $variants[$k]->item_article_number=$variants_ar[$j]->item_article_number;
                $variants[$k]->item_avail=$variants_ar[$j]->avail_id;
                $variants[$k]->item_img_time=$variants_ar[$j]->img_time;
                $variants[$k]->var_id=$variants_ar[$j]->var_id;
                $variants[$k]->var_type_title=$variants_ar[$j]->var_type_title;
            }
        }

        return array($items,$variants);
    }
    private $avail_id2avail_value_ar;
    private function avail_id2avail_value($avail_id) {
        if(!isset($this->avail_id2avail_value_ar[$avail_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                u235_items_avail_types.avail_type_id
                FROM 
                u235_items_avail_types
                JOIN
                u235_items_avail_values
                ON
                u235_items_avail_types.avail_type_id=u235_items_avail_values.avail_type_id
                WHERE 
                avail_id=:avail_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_id', $avail_id ,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                /** @noinspection PhpUndefinedMethodInspection */
                $res=$stm->fetch(PDO::FETCH_OBJ);
                $avail_type_id=(int)$res->avail_type_id;
                if(!$res) $this->avail_id2avail_value_ar[$avail_id]="На заказ";
                elseif($avail_type_id===1||$avail_type_id===5) $this->avail_id2avail_value_ar[$avail_id]="В наличии";
                else $this->avail_id2avail_value_ar[$avail_id]="На заказ";
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        return $this->avail_id2avail_value_ar[$avail_id];
    }
    private $cat_id2cat_title_ar;
    private function cat_id2cat_title($cat_id,$item_id) {
        if(!(int)$cat_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                cat_id 
                FROM 
                u235_cats_items 
                WHERE 
                item_id=:item_id AND
                site_id=:site_id
                ORDER BY cat_id DESC 
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($res=$stm->fetch(PDO::FETCH_OBJ)) $cat_id=$res->cat_id;
            }
            catch(PDOException $e) {$this->uFunc->error('75'/*.$e->getMessage()*/);}
        }
        if(!isset($this->cat_id2cat_title_ar[$cat_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                yandex_cat_id,
                cat_title
                FROM 
                u235_cats
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $res=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedVariableInspection */
            if(!$res) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                    cat_id 
                    FROM 
                    u235_cats_items
                    WHERE 
                    item_id=:item_id AND
                    site_id=:site_id
                    ORDER BY cat_id DESC
                    LIMIT 1
                    ");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    $res = $stm->fetch(PDO::FETCH_OBJ);
                    if (!$res) return $this->cat_id2cat_title_ar[$cat_id] = false;
                    else return $this->cat_id2cat_title($res->cat_id, $item_id);
                } catch (PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
            }

            $yandex_cat_id=(int)$res->yandex_cat_id;
            $cat_title= uString::sql2text($res->cat_title,1);
            if($yandex_cat_id) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                    cat_title
                    FROM 
                    yandex_cats
                    WHERE 
                    cat_id=:cat_id
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $res->yandex_cat_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    $res=$stm->fetch(PDO::FETCH_OBJ);
                    if(!$res) return $this->cat_id2cat_title_ar[$cat_id]=$cat_title;
                    return $this->cat_id2cat_title_ar[$cat_id]=$res->cat_title;
                }
                catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
            }
            return $this->cat_id2cat_title_ar[$cat_id]=$cat_title;
        }
        return $this->cat_id2cat_title_ar[$cat_id];
    }
    private function item2fields_values($item) {
        //FIELDS
        $fields_count=count($this->fields_ar);
        $field_val="";
        for($i=0;$i<$fields_count;$i++) {
            $field_id="field_".$this->fields_ar[$i]->field_id;
            if($item->$field_id!="") {
                $field_val .= uString::sql2text($this->fields_ar[$i]->field_title, 1);
                $field_val .= "|";
                $field_val .= $item->$field_id;
                if ($this->fields_ar[$i]->field_units != "") {
                    $field_val .= "|";
                    $field_val .= uString::sql2text($this->fields_ar[$i]->field_units, 1);
                }
                $field_val .= ";";
            }
        }
        return $field_val;
    }

    private function make_file($items_and_variants_ar) {
        require_once "lib/PHP_XLSXWriter/xlsxwriter.class.php";

        $writer = new XLSXWriter();
        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/uCat/tmp")) mkdir($_SERVER['DOCUMENT_ROOT']."/uCat/tmp",0777);
        $writer->setTempDir($_SERVER['DOCUMENT_ROOT']."/uCat/tmp");

        $writer->writeSheetHeader('Инструкция', array('ЯМ102017О'=>'string'));
        $writer->writeSheetHeader('Описание полей', array('ЯМ102017О'=>'string'));

        $table_header=array(
            "id*",/**/
            "Статус товара",
            "Доставка",
            "Стоимость доставки",
            "Срок доставки",
            "Самовывоз",
            "Купить в магазине без заказа",
            "Ссылка на товар на сайте магазина*",/**/
            "Производитель",
            "Название*",/**/
            "Категория*",/**/
            "Цена",/**/
            "Цена без скидки",
            "Валюта*",/**/
            "Ссылка на картинку*",/**/
            "Описание",
            "Характеристики товара",
            "Условия продажи",
            "Гарантия производителя",
            "Страна происхождения",
            "Штрихкод",
            "bid",
            "Count"
        );


        $writer->writeSheetRow('Товары',array(""));
//        $writer->writeSheetHeader('Товары', $table_header );
        $writer->writeSheetRow('Товары', $table_header );

        $items_ar=$items_and_variants_ar[0];
        $variants_ar=$items_and_variants_ar[1];

        $item_existing_ids_ar=[];
        $item_existing_titles_ar=[];

        $items_count=count($items_ar);
        for($i=0;$i<$items_count;$i++) {
//            if(site_id==54) {
//                if($i>4000) break;
//            }
            $item=$items_ar[$i];

            $item_avail=$this->avail_id2avail_value($item->item_avail);

            $delivery_on=(int)$item->delivery_on;
            if(!$delivery_on) {
                $delivery_on=$this->uCat->site_has_delivery_type_1()?1:0;
            }
            if($delivery_on) $delivery_on="Есть";
            else $delivery_on="Нет";

            $delivery_cost=(float)$item->delivery_cost;
            if(!$delivery_cost) {
                $delivery_cost=(float)$this->uFunc->getConf("local_delivery_price","uCat");
            }

            $delivery_time=$item->delivery_time;
            if($delivery_time=="") {
                $delivery_time=$this->uFunc->getConf("local_delivery_time","uCat");
            }

            $pickup_on=(int)$item->pickup_on;
            if(!$pickup_on) {
                $pickup_on=$this->uCat->site_has_delivery_type_0()?1:0;
            }
            if($pickup_on) $pickup_on="Есть";
            else $pickup_on="Нет";

            $buy_without_order_on=(int)$item->buy_without_order_on;
            if(!$buy_without_order_on) {
                $buy_without_order_on=(int)$this->uFunc->getConf("buy_without_order_is_on","uCat");
            }
            if($buy_without_order_on) $buy_without_order_on="Можно";
            else $buy_without_order_on="Нельзя";

            $manufacturer=$item->manufacturer;

            $manufactured_in=$item->manufactured_in;

            $cat_title=$this->cat_id2cat_title($item->primary_cat_id,$item->item_id);

            $manufacturer_warranty=(int)$item->manufacturer_warranty;
            if($manufacturer_warranty) $manufacturer_warranty="Есть";
            else $manufacturer_warranty="Нет";

            if(trim($item->yandex_description)!="") $description=trim($item->yandex_description);
            else $description=strip_tags(uString::sql2text($item->item_descr,1));

            $fields=$this->item2fields_values($item);

            $item_url=u_sroot."uCat/item/".$item->item_id;
            if($item->item_url!="") $item_url=u_sroot."uCat/item/".$item->item_url;

            //DEFINE item_title
            $item_title= uString::sql2text($item->item_title,1);

            $item_title_tmp=$item_title;
            for($j=0;array_key_exists($item_title_tmp,$item_existing_titles_ar)&&$j<200;$j++) $item_title_tmp=$item_title." v".$j;
            $item_title=$item_title_tmp;

            $item_existing_titles_ar[$item_title]=1;
            //--DEFINE item_title

            //DEFINE item_article_number
            $item->item_article_number= uString::rus2eng($item->item_article_number);
            if(preg_match("#^[\d\w]+$#i",$item->item_article_number)) $item_article_number=$item->item_article_number;
            else $item_article_number=$item->item_id;

            $item_article_number_tmp=$item_article_number;
            for($j=0;array_key_exists($item_article_number_tmp,$item_existing_ids_ar)&&$j<200;$j++) $item_article_number_tmp=$item->item_id."XX".$j;
            $item_article_number=$item_article_number_tmp;

            $item_existing_ids_ar[$item_article_number]=1;
            //--DEFINE item_article_number

            $item_price=str_replace(".",",",$item->item_price);

            $prev_price="";
            if($item->prev_price>$item->item_price) {
                if(
                    ($item->item_price*0.95+$item->item_price)>$item->prev_price&&
                    ($item->item_price*0.05+$item->item_price)<$item->prev_price
                ) $prev_price=str_replace(".",",",$item->prev_price);
            }

            $img_url=$this->avatar->get_avatar('orig',$item->item_id,$item->item_img_time);
            if($img_url=="images/uCat/item_def_avatar.jpg") $img_url="";

            $img_size=filesize("uCat/item_avatars/".site_id."/".$item->item_id."/orig.jpg");
            if($img_size==20039) $this->uCat->reset_img_time_for_item($item->item_id);

            $row=array(
                $item_article_number,/**/
                $item_avail,/*Статус товара: В наличии / На заказ*/
                $delivery_on,/*Доставка: Есть/Нет*/
                $delivery_cost/*Стоимость доставки*/,
                $delivery_time/*Срок доставки*/,
                $pickup_on/*Самовывоз: Есть/Нет*/,
                $buy_without_order_on,/*Купить в магазине без заказа: Можно/Нельзя*/
                $item_url,/*Ссылка на товар*/
                $manufacturer,/*Производитель*/
                $item_title,/*Название*/
                $cat_title,/*Категория*/
                $item_price,/*Цена - через запятую*/
                $prev_price/*Цена без скидки*/,
                "RUR",/*Валюта*/
                ($img_size!==20039?$img_url:""),/*Ссылка на кантинку*/
                $description,
                $fields,/*Характеристики в формате название | значение;*/
                (site_id==54?$img_size:"")/*"Необходима предоплата."*//*Условия продажи*/,//Убрал, потому что Яндекс выебывался на балсера
                $manufacturer_warranty,/*Гарантия производителя: Есть/Нет*/
                $manufactured_in/*Страна просихождения*/,
                "",/*Штрихкод*/
                ""/*bid*/,
                $item->quantity
            );
            $writer->writeSheetRow('Товары', $row );
        }

        $variants_count=count($variants_ar);
        for($i=0;$i<$variants_count;$i++) {
//            if(site_id==54) {
//                if($i>4000) break;
//            }
            $item=$variants_ar[$i];

            $item_avail=$this->avail_id2avail_value($item->item_avail);

            $delivery_on=(int)$item->delivery_on;
            if(!$delivery_on) {
                $delivery_on=$this->uCat->site_has_delivery_type_1()?1:0;
            }
            if($delivery_on) $delivery_on="Есть";
            else $delivery_on="Нет";

            $delivery_cost=(float)$item->delivery_cost;
            if(!$delivery_cost) {
                $delivery_cost=(float)$this->uFunc->getConf("local_delivery_price","uCat");
            }

            $delivery_time=$item->delivery_time;
            if($delivery_time=="") {
                $delivery_time=$this->uFunc->getConf("local_delivery_time","uCat");
            }

            $pickup_on=(int)$item->pickup_on;
            if(!$pickup_on) {
                $pickup_on=$this->uCat->site_has_delivery_type_0()?1:0;
            }
            if($pickup_on) $pickup_on="Есть";
            else $pickup_on="Нет";

            $buy_without_order_on=(int)$item->buy_without_order_on;
            if(!$buy_without_order_on) {
                $buy_without_order_on=(int)$this->uFunc->getConf("buy_without_order_is_on","uCat");
            }
            if($buy_without_order_on) $buy_without_order_on="Можно";
            else $buy_without_order_on="Нельзя";

            $manufacturer=$item->manufacturer;

            $manufactured_in=$item->manufactured_in;

            $cat_title=$this->cat_id2cat_title($item->primary_cat_id,$item->item_id);

            $manufacturer_warranty=(int)$item->manufacturer_warranty;
            if($manufacturer_warranty) $manufacturer_warranty="Есть";
            else $manufacturer_warranty="Нет";

            if(trim($item->yandex_description)!="") $description=trim($item->yandex_description);
            else $description=strip_tags(uString::sql2text($item->item_descr,1));

            $fields=$this->item2fields_values($item);

            $item_url=u_sroot."uCat/item/".$item->item_id."?var_id=".$item->var_id;
            if($item->item_url!="") $item_url=u_sroot."uCat/item/".$item->item_url."?var_id=".$item->var_id;


            //DEFINE item_title
            $item_title= uString::sql2text($item->var_type_title,1);

            $item_title_tmp=$item_title;
            for($j=0;array_key_exists($item_title_tmp,$item_existing_titles_ar)&&$j<200;$j++) $item_title_tmp=$item_title." v".$j;
            $item_title=$item_title_tmp;

            $item_existing_titles_ar[$item_title]=1;
            //--DEFINE item_title

            //DEFINE item_article_number
            $item->item_article_number= uString::rus2eng($item->item_article_number);
            if(preg_match("#^[\d\w]+$#i",$item->item_article_number)) $item_article_number=$item->item_article_number;
            else $item_article_number=$item->item_id."V".$item->var_id;

            $item_article_number_tmp=$item_article_number;
            for($j=0;array_key_exists($item_article_number_tmp,$item_existing_ids_ar)&&$j<200;$j++) {
                $item_article_number_tmp=$item->item_id."X".$j;
            }
            $item_article_number=$item_article_number_tmp;

            $item_existing_ids_ar[$item_article_number]=1;
            //--DEFINE item_article_number


            $item_price=str_replace(".",",",$item->item_price);

            $prev_price="";
            if($item->prev_price>$item->item_price) {
                if(
                    ($item->item_price*0.95+$item->item_price)<$item->prev_price||
                    ($item->item_price*0.05+$item->item_price)>$item->prev_price
                ) $prev_price=str_replace(".",",",$item->prev_price);
            }

            $img_url=$this->avatar->get_avatar('orig',$item->item_id,$item->item_img_time);
            if($img_url=="images/uCat/item_def_avatar.jpg") $img_url="";

            $img_size=filesize("uCat/item_avatars/".site_id."/".$item->item_id."/orig.jpg");
            if($img_size==20039) $this->uCat->reset_img_time_for_item($item->item_id);

            $row=array(
                $item_article_number,/**/
                $item_avail,/*Статус товара: В наличии / На заказ*/
                $delivery_on,/*Доставка: Есть/Нет*/
                $delivery_cost/*Стоимость доставки*/,
                $delivery_time/*Срок доставки*/,
                $pickup_on/*Самовывоз: Есть/Нет*/,
                $buy_without_order_on,/*Купить в магазине без заказа: Можно/Нельзя*/
                $item_url,/*Ссылка на товар*/
                $manufacturer,/*Производитель*/
                $item_title,/*Название*/
                $cat_title,/*Категория*/
                $item_price,/*Цена - через запятую*/
                $prev_price/*Цена без скидки*/,
                "RUR",/*Валюта*/
                ($img_size!==20039?$img_url:""),/*Ссылка на кантинку*/
                $description,
                $fields,/*Характеристики в формате название | значение;*/
                (site_id==54?$img_size:"")/*"Необходима предоплата."*//*Условия продажи*/,//Убрал, потому что Яндекс выебывался на балсера
                $manufacturer_warranty,/*Гарантия производителя: Есть/Нет*/
                $manufactured_in/*Страна просихождения*/,
                "",/*Штрихкод*/
                ""/*bid*/
            );
            $writer->writeSheetRow('Товары', $row );
        }

        $writer->writeToFile('uDrive/files/yandexmarket/'.site_id.'.xlsx');
    }
    private function return_file($file_addr) {
        if (!file_exists($file_addr)) return $this->return_error("File is not found", "this file is not found");

//        if(strpos(u_sroot,'.local/')&&$_SERVER['REMOTE_ADDR']=='127.0.0.1') {//Закомментить этот блок в Production
//            if (!file_exists("uDrive/files/yandexmarket/".site_id.'.xlsx')) die('50');
//            header('Content-Description: File Transfer');
//            header('Content-Type: application/vnd.ms-excel');
//            header('Content-Disposition: attachment; filename="'.site_id.'.xlsx');//to open download window
//            flush();
//            readfile($file_addr);
//            exit;
//        }

        header('X-Accel-Redirect: /files/yandexmarket/'.site_id.'.xlsx');
        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.site_id.'.xlsx"');

        return true;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $files_dir=/*$_SERVER['DOCUMENT_ROOT'].*/"uDrive/files";
        $yandexmarket_files_dir=$files_dir."/yandexmarket";
        $file_addr=$yandexmarket_files_dir."/".site_id.'.xlsx';

        if(!file_exists($file_addr)||true) {
            $this->uCat=new common($this->uCore);
            $this->avatar=new item_avatar($this->uCore);

            if (!file_exists($files_dir)) mkdir($files_dir,0755,true);
            if (!file_exists($yandexmarket_files_dir)) mkdir($yandexmarket_files_dir,0755,true);

            $this->fields_ar=$this->get_site_fields();
            $this->availabilities_ar=$this->get_allowed_availabilities();
            $this->item_types_ar=$this->get_allowed_item_types();
            $items_and_variants_ar=$this->items=$this->get_items();

            $this->make_file($items_and_variants_ar);
        }

        $this->return_file($file_addr);
    }
}
$uCat=new marketYandexRu($this);

$this->uFunc->incJs(u_sroot.'js/u235/uString.js');
$this->page_content=$uCat->returnHtml;
/** @noinspection PhpIncludeInspection */
include "templates/template.php";
