<?php
namespace uCat;
use PDO;
use PDOException;
use processors\uFunc;
use item_avatar;
use uSes;
use uString;
use XLSXWriter;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";
require_once "lib/PHP_XLSXWriter/xlsxwriter.class.php";
require_once "uCat/inc/item_avatar.php";

class export_bg {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var common
     */
    public $uCat;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;

    private function get_items($site_id=site_id) {
        $fields_ar=$this->uCat->get_site_fields_and_fields_types("u235_fields.field_id,field_title,field_comment,field_type_title,field_type_descr,field_sql_type,field_style",$site_id);

        $q_fields_ar=[];
        foreach ($fields_ar AS $i=>$field) {
            $q_fields_ar[]=$field->field_id;
        }
        $q_fields=implode(",",$q_fields_ar);
        if($q_fields!=="") $q_fields=",".$q_fields;
        else $q_fields="";

        if($has_options=(int)$this->uFunc->getConf("enable_var_options","uCat")) {
            $options_ar=$this->uCat->get_site_options("option_id,option_name",$site_id);
        }
        else $options_ar=[];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT 
            items_variants.var_id,
            items_variants.item_article_number AS variant_article_number,
            items_variants.uuid_variant,
            items_variants.var_type_id,
            items_variants.default_var,
            items_variants.price,
            items_variants.prev_price AS variant_prev_price,
            items_variants.var_quantity,
            items_variants.img_time,
            items_variants.inaccurate_price AS variant_inaccurate_price,
            items_variants.request_price AS variant_request_price,
            items_variants.avail_id AS variant_avail_id,
            items_variants.file_id AS variant_file_id,
       
            item_img_time,
            u235_items.item_id,
            u235_items.item_avail AS avail_id,
            evotor_uuid,
            quantity,
            u235_items.unit_id,
            unit_name,
            `default`,
            item_title,
            u235_items.seo_title,
            item_descr,
            u235_items.seo_descr,
            item_keywords,
            item_url,
            item_price,
            u235_items.prev_price,
            u235_items.item_article_number,
            u235_items.request_price,
            u235_items.inaccurate_price,
            item_type,
            type_title,
            base_type_id,
            primary_cat_id,
            cat_title,
            u235_cats.primary_sect_id,
            sect_title,
            u235_items.file_id,
            upload_to_yandex_market,
            yandex_description,
            manufactured_in,
            manufacturer_warranty,
            manufacturer,
            buy_without_order_on,
            pickup_on,
            delivery_time,
            delivery_cost,
            delivery_on,
            manufacturer_part_number,
            search_part_number,
            orig_replacement
            $q_fields
            FROM 
            u235_items
            LEFT JOIN
            items_types
            ON
            item_type=type_id AND
            u235_items.site_id=items_types.site_id
            LEFT JOIN 
            units
            ON
            u235_items.unit_id=units.unit_id AND
            u235_items.site_id=units.site_id
            LEFT JOIN
            u235_cats
            ON
            primary_cat_id=cat_id AND
            u235_items.site_id=u235_cats.site_id
            LEFT JOIN
            u235_sects
            ON
            u235_cats.primary_sect_id=u235_sects.sect_id AND
            u235_cats.site_id=u235_sects.site_id
            LEFT JOIN
            items_variants
            ON
            u235_items.item_id=items_variants.item_id AND
            u235_items.site_id=items_variants.site_id
            

            WHERE
            parts_autoadd=0 AND
            u235_items.site_id=:site_id
            ORDER BY 
            u235_items.item_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return array($stm,$fields_ar,$options_ar);
        }
        catch(PDOException $e) {$this->uFunc->error('1583227162'/*.$e->getMessage()*/,1);}
        return false;
    }

    private function make_file($site_id=site_id) {
        $items_fields_ar=$this->get_items($site_id);
        $items_stm=$items_fields_ar[0];
        $fields_ar=$items_fields_ar[1];
        $options_ar=$items_fields_ar[2];

        $writer = new XLSXWriter();
        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/uCat/tmp")) mkdir($_SERVER['DOCUMENT_ROOT']."/uCat/tmp",0755);
        $writer->setTempDir($_SERVER['DOCUMENT_ROOT']."/uCat/tmp");

//        $writer->writeSheetHeader('Инструкция', array('ЯМ102017О'=>'string'));

        $table_header_primary_fields=array(
            "ID товара на сайте",//item_id,
            "UUID товара",//evotor_uuid,
            "ID варианта товара",//items_variants.var_id,
            "Вариант по умолчанию",//items_variants.default_var,
            "Название товара",//item_title,
            "Описание товара",//item_descr,
            "Артикул",//item_article_number,
            "Цена товара",//item_price,
            "Перечеркнутая цена товара",//prev_price,
            "Цену нужно запрашивать",//request_price,
            "Цена указана ориентировочно",//inaccurate_price,
            "URL изображения товара",
            "ID основной категории товара",//primary_cat_id,
            "Название основной категории товара",//cat_title
            "ID основного раздела",//primary_sect_id,
            "Название основного раздела",//sect_title
            "URL товара",//item_url,
            "ID файла товара",//file_id,
            "Адрес файла товара",//$this->uCat->item_file_id2url($file_id)
            "Остаток товара",//quantity,
            "ID наличия товара",//avail_id,
            "Название наличия",//avail_label,
            "Описание наличия",//avail_descr,
            "Тип наличия"//avail_type_id,
        );
        $table_header_item_fields=[];
        foreach ($fields_ar AS $i=>$field) {
            $table_header_item_fields[]=$field->field_title;
        }
        $table_header_item_options=[];
        foreach ($options_ar AS $i=>$option) {
            $table_header_item_options[]=$option->option_name;
        }
        $table_header_secondary_fields=array(
            "ID типа товара",//item_type,
            "Название типа товара",//type_title,
            "Базовый тип товара",//base_type_id,
            "ID единицы измерения на сайте",//u235_items.unit_id,
            "Название единицы измерения",//unit_name,
            "Является единицей измерения по умолчанию",//`default`,
            "SEO - Ключевые слова товара",//item_keywords,
            "SEO - Название товара",//u235_items.seo_title,
            "SEO - Описание товара",//u235_items.seo_descr,
            "Загружать на Яндекс Маркет (ЯМ)",//upload_to_yandex_market,
            "ЯМ - Описание",//yandex_description,
            "ЯМ - Страна производства",//manufactured_in,
            "ЯМ - Есть гарантия производителя",//manufacturer_warranty,
            "ЯМ - Производитель",//manufacturer,
            "ЯМ - Товар можно купить без предварительного заказа",//buy_without_order_on,
            "ЯМ - Доступен самовывоз",//pickup_on,
            "ЯМ - Доступна доставка",//delivery_on,
            "ЯМ - Время доставки, сек",//delivery_time,
            "ЯМ - Стоимость доставки",//delivery_cost,
            "Parts - код запчасти по производителю",//manufacturer_part_number,
            "Parts - код запчасти по поиску",//search_part_number,
            "Parts - Оригинал или замена",//orig_replacement
        );
        $table_header=array_merge($table_header_primary_fields,$table_header_item_fields,$table_header_item_options,$table_header_secondary_fields);

        $writer->writeSheetRow('Товары',array(""));
        $writer->writeSheetRow('Товары', $table_header );

        $item_avatar=new item_avatar($this->uCore);

        while($item=$items_stm->fetch(PDO::FETCH_OBJ)) {
            $has_var=!is_null($item->var_id);

            $img_url=$item_avatar->get_avatar('orig',$item->item_id,($has_var?$item->img_time:$item->item_img_time),($has_var?$item->var_id:0));
            if($img_url=="images/uCat/item_def_avatar.jpg") $img_url="";

            $img_size=filesize("uCat/item_avatars/".site_id."/".$item->item_id."/orig.jpg");
            if($img_size==20039) $this->uCat->reset_img_time_for_item($item->item_id);

            $avail_data=$this->uCat->avail_id2avail_data(($has_var?$item->variant_avail_id:$item->avail_id),$site_id);

            $row1=array(
                $item->item_id,
                ($has_var?$item->uuid_variant:$item->evotor_uuid),
                $item->var_id,
                $item->default_var,
                uString::sql2text($item->item_title,1),
                $item->item_descr,
                ($has_var?$item->variant_article_number:$item->item_article_number),
                ($has_var?$item->price:$item->item_price),
                ($has_var?$item->variant_prev_price:$item->prev_price),
                ($has_var?$item->variant_request_price:$item->request_price),
                ($has_var?$item->variant_inaccurate_price:$item->inaccurate_price),
                $img_url,
                $item->primary_cat_id,
                $item->cat_title,
                $item->primary_sect_id,
                $item->sect_title,
                $item->item_url,
                ($has_var?$item->variant_file_id:$item->file_id),
                ($has_var?$this->uCat->item_file_id2url($item->variant_file_id):$this->uCat->item_file_id2url($item->file_id)),
                ($has_var?$item->var_quantity:$item->quantity),
                ($has_var?$item->variant_avail_id:$item->avail_id),
                $avail_data->avail_label,
                $avail_data->avail_descr,
                $avail_data->avail_type_id,
            );
            foreach ($fields_ar AS $i=>$field) {
                $field_id=$field->field_id;
                $row1[]=$item->$field_id;
            }
            foreach ($options_ar AS $i=>$option) {
                $option_value=$this->uCat->variant_id_option_id2option_value($item->var_id,$option->option_id,$site_id);
                $row1[]=$option_value;
            }
            $row2=array(
                $item->item_type,
                $item->type_title,
                $item->base_type_id,
                $item->unit_id,
                $item->unit_name,
                $item->default,
                $item->item_keywords,
                $item->seo_title,
                $item->seo_descr,
                $item->upload_to_yandex_market,
                $item->yandex_description,
                $item->manufactured_in,
                $item->manufacturer_warranty,
                $item->manufacturer,
                $item->buy_without_order_on,
                $item->pickup_on,
                $item->delivery_on,
                $item->delivery_time,
                $item->delivery_cost,
                $item->manufacturer_part_number,
                $item->search_part_number,
                $item->orig_replacement
            );
            $row=array_merge($row1,$row2);
            $writer->writeSheetRow('Товары', $row );
        }

        /*$variants_count=count($variants_ar);
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
                $item_article_number,
                $item_avail,
                $delivery_on,
                $delivery_cos,
                $delivery_tim
            );
            $writer->writeSheetRow('Товары', $row );
        }*/

        uFunc::rmdir("uCat/export_files/".$site_id);

        $hash=$this->uFunc->genHash();
        $dir="uCat/export_files/".$site_id.'/'.$hash;
        if(!file_exists($_SERVER['DOCUMENT_ROOT']."/".$dir)) mkdir($_SERVER['DOCUMENT_ROOT']."/".$dir,0777,1);
        $writer->writeToFile($dir.'/export.xlsx');
        uFunc::create_empty_index($dir);

        return $hash;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) {
            print json_encode(array(
                    "status"=>"forbidden"
            ));
            exit;
        }

        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $hash=$this->make_file();
        print json_encode(array(
                "status"=>"done",
            "hash"=>$hash
        ));
    }
}
new export_bg($this);
