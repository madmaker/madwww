<?php
/** @noinspection PhpIncludeInspection */
include_once 'uCat/inc/template_helper.php';
require_once "inc/item_avatar.php";
require_once "inc/art_avatar.php";
require_once "inc/item_img.php";
require_once 'uCat/classes/common.php';

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uDrive/classes/common.php";
class uCat_item {
    public $uFunc;
    public $uSes;
    public $uDrive;
    public $buy_button_show;
    public $item_page_title_pos;
    public $how_to_call_item_images;
    public $arts_label;
    public $inaccurate_price_label;
    public $enable_var_options;
    public $item_img_col_num;
    public $price_is_used;
    public $item_availability_show;
    public $item_quantity_show;
    public $item_prev_price_show;
    public $item_field_title_col_num;
    public $show_link_to_sects_in_bc;
    public $inaccurate_price_descr;
    public $link_item_descr;
    public $link_item_label;
    public $options_number;
    public $selected_var_id;
    private $uCore;
    public $item,$cat_id,$cat_title,$cat_url,$sect_id,$sect_title,$sect_url,$q_fields,$items_fields_q_select,
$helper,$q_item_pics,$avatar,$art_avatar,$item_id,$q_fields_places,$field_place_id2title,$item_img,
$q_fields_filter_types,$q_fields_types,$q_fields_label_styles,$q_fields_effects,$q_avail_types,
$uDrive_folder_id,$base_type_id,$file_hashname,$file_name,$uCat,$enable_item_plus_and_minus;
    private function error(/** @noinspection PhpUnusedParameterInspection */$debug_code) {
        header('Location: '.u_sroot.'uCat/sects');
        exit;
        //die('#'.$debug_code);
    }
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) $this->error(10);
        $this->item_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->item_id)) {
            $item_url=uString::text2sql($this->item_id);
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_id
                FROM
                u235_items
                WHERE
                item_url=:item_url AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_url', $item_url,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedMethodInspection */

            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $item_url=rawurldecode($this->item_id);
                $item_url=uString::text2sql($item_url);

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                    item_id
                    FROM
                    u235_items
                    WHERE
                    item_url=:item_url AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_url', $item_url,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->error(40);
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $this->item_id=$qr->item_id;
        }
    }

    private function increase_views_counter($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            views_counter=views_counter+1
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583242645'/*.$e->getMessage()*/);}
    }

    private function get_uDrive_folder_id() {
        //define uDrive item default folder
        if($this->item->uDrive_folder_id=='0') {
            $item_title=trim(uString::sanitize_filename(uString::sql2text($this->item->item_title)));
            if(!strlen($item_title)) $item_title='Товар '.$this->item_id;

            $uCat_items_folder_id=$this->uDrive->get_module_folder_id("uCat_items");
            $this->item->uDrive_folder_id=$this->uDrive->create_folder($item_title,$uCat_items_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items
                SET
                uDrive_folder_id=:folder_id
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $this->item->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
    }

    private function get_item_fields() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields=$this->uCore->query("uCat","SELECT DISTINCT
        `u235_fields`.`field_id`,
        `field_title`,
        `field_pos`,
        `field_units`,
        `field_style`,
        `field_place_id`,
        `field_effect_id`,
        `label_style_id`
        FROM
        `u235_fields`,
        `u235_fields_types`,
        `u235_cats_fields`,
        `u235_cats_items`
        WHERE
        `u235_cats_items`.`item_id`='".$this->item_id."' AND
        `u235_cats_items`.`site_id`='".site_id."' AND
        `u235_cats_items`.`cat_id`=`u235_cats_fields`.`cat_id` AND
        `u235_cats_fields`.`field_id`=`u235_fields`.`field_id` AND
        `u235_cats_fields`.`site_id`='".site_id."' AND
        `u235_fields`.`field_type_id`=`u235_fields_types`.`field_type_id` AND
        `u235_fields`.`site_id`='".site_id."'
        ORDER BY
        `field_pos` ASC,
        `field_title` ASC
        ")) $this->uFunc->error(60);
        $this->items_fields_q_select='';
        /** @noinspection PhpUndefinedMethodInspection */
        while($field=$this->q_fields->fetch_object()) {
            $this->items_fields_q_select.="`field_".$field->field_id."`,";
        }
    }
    private function check_if_item_type_exists($item_type) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        `base_type_id`
        FROM
        `items_types`
        WHERE
        `type_id`='".$item_type."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(70);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $this->base_type_id=(int)$qr->base_type_id;
            return 1;
        }
        return 0;
    }
    private function set_item_type_default($cur_item_type) {
        if(!$this->check_if_item_type_exists(0)) {
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uCat","INSERT INTO
            `items_types` (
            `base_type_id`,
            `type_id`,
            `type_title`,
            `site_id`
            ) VALUES (
            '0',
            '0',
            'Обычный товар',
            '".site_id."'
            )
            ")) $this->uFunc->error(80);
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            item_type=0
            WHERE
            item_type=:item_type AND
            site_id=:site_id
        ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_type', $cur_item_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }

    private function get_item_data() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            ".$this->items_fields_q_select."
            u235_items.views_counter,
            u235_items.item_id,
            u235_items.item_avail,
            u235_items.item_img_time,
            u235_items.item_title,
            u235_items.item_descr,
            u235_items.item_price,
            u235_items.quantity,
            u235_items.prev_price,
            u235_items.inaccurate_price,
            u235_items.request_price,
            u235_items.item_url,
            u235_items.seo_title,
            u235_items.seo_descr,
            u235_items.item_keywords,
            u235_items_avail_values.avail_label,
            u235_items_avail_values.avail_descr,
            u235_items.item_article_number,
            u235_items.uDrive_folder_id,
            u235_items_avail_values.avail_type_id,
            u235_items.item_type,
            u235_items.file_id,
            u235_items.has_variants,
            u235_items.unit_id,
            manufactured_in,
            manufacturer_warranty,
            manufacturer,
            buy_without_order_on,
            pickup_on,
            delivery_time,
            delivery_cost,
            delivery_on,
            manufacturer_part_number,
            search_part_number
            FROM
            u235_items
            JOIN
            u235_items_avail_values
            ON
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            u235_items_avail_values.site_id=u235_items.site_id
            WHERE
            u235_items.item_id=:item_id AND
            u235_items.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) $this->error(110);
        $this->item->item_avail=(int)$this->item->item_avail;
        $this->item->item_img_time=(int)$this->item->item_img_time;
        $this->item->item_price=(float)$this->item->item_price;
        $this->item->quantity=(float)$this->item->quantity;
        $this->item->prev_price=(float)$this->item->prev_price;
        $this->item->inaccurate_price=(int)$this->item->inaccurate_price;
        $this->item->request_price=(int)$this->item->request_price;
        $this->item->uDrive_folder_id=(int)$this->item->uDrive_folder_id;
        $this->item->avail_type_id=(int)$this->item->avail_type_id;
        $this->item->item_type=(int)$this->item->item_type;
        $this->item->file_id=(int)$this->item->file_id;
        $this->item->has_variants=(int)$this->item->has_variants;
        $this->options_number = $this->uCat->has_options($this->item_id);
        $this->item->unit_id=(int)$this->item->unit_id;

        $this->get_uDrive_folder_id();
        if(!$this->check_if_item_type_exists($this->item->item_type)) {
            $this->set_item_type_default($this->item->item_type);
            $this->item->item_type=0;
            $this->base_type_id=0;
        }

        $this->item->unit_name=$this->uCat->unit_id2unit_name($this->item->unit_id);
    }

    private function get_cat_info() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_cats.cat_id,
            cat_url,
            cat_title
            FROM
            u235_items
            JOIN 
            u235_cats
            ON
            u235_cats.cat_id=u235_items.primary_cat_id AND
            u235_cats.site_id=u235_items.site_id
            WHERE
            u235_items.item_id=:item_id AND
            u235_cats.site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->cat_id = $qr->cat_id;
                $this->cat_url = $qr->cat_url;
                $this->cat_title = uString::sql2text($qr->cat_title);
            }
            else {
                $q_cats=$this->uCat->get_item_cats($this->item_id);
                /** @noinspection PhpUndefinedMethodInspection */
                while($cat=$q_cats->fetch(PDO::FETCH_OBJ)) {
                    if(!(int)$cat->cat_id) continue;
                    if($this->uCat->cat_exists($cat->cat_id)) {
                        $this->uCat->set_certain_primary_cat_id($this->item_id, $cat->cat_id);
                        $this->get_cat_info();
                        break;
                    }
                    else $this->uCat->detach_itemFromCat($cat->cat_id,$this->item_id);
                }
                $this->uCat->set_certain_primary_cat_id($this->item_id, 0);
                $this->cat_id=0;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        return 1;
    }
    private function get_sect_info() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_sects.sect_id,
            sect_url,
            sect_title
            FROM
            u235_sects
            JOIN
            u235_sects_cats
            ON
            u235_sects.sect_id=u235_sects_cats.sect_id AND
            u235_sects.site_id=u235_sects_cats.site_id
            WHERE
            u235_sects_cats.cat_id=:cat_id AND
            u235_sects.site_id=:site_id
            ORDER BY
            item_count DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->sect_id = $qr->sect_id;
                $this->sect_url = $qr->sect_url;
                $this->sect_title = uString::sql2text($qr->sect_title);
            }
            else return $this->sect_id=0;
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
        return 1;
    }
    private function get_item_pictures() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_item_pics=$this->uCore->query("uCat","SELECT
        `img_id`,
        `timestamp`
        FROM
        `u235_items_pictures`
        WHERE
        `item_id`='".$this->item_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(200);
    }
    private function get_fields_places() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_places=$this->uCore->query("uCat","SELECT
        `place_id`,
        `place_title`
        FROM
        `u235_fields_places`
        ")) $this->uFunc->error(210);
    }
    private function get_fields_filter_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_filter_types=$this->uCore->query("uCat","SELECT
        `filter_type_id`,
        `filter_type_sql`,
        `filter_type_title`
        FROM
        `u235_fields_filter_types`
        ")) $this->uFunc->error(220);
    }
    private function get_fields_label_styles() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_label_styles=$this->uCore->query("uCat","SELECT
        `label_style_id`,
        `label_style_title`
        FROM
        `u235_fields_label_styles`
        ")) $this->uFunc->error(230);
    }
    private function get_fields_effects() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_effects=$this->uCore->query("uCat","SELECT
        `effect_id`,
        `effect_title`
        FROM
        `u235_fields_effects`
        ")) $this->uFunc->error(240);
    }
    private function get_fields_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_types=$this->uCore->query("uCat","SELECT
        `field_type_id`,
        `field_sql_type`,
        `field_type_title`
        FROM
        `u235_fields_types`
        ")) $this->uFunc->error(250);
    }
    private function get_avail_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_avail_types=$this->uCore->query("uCat","SELECT
        `avail_type_id`,
        `avail_type_title`
        FROM
        `u235_items_avail_types`
        ORDER BY
        `avail_type_id` ASC
        ")) $this->uFunc->error(260);
    }
    public function print_fields($place_id) {?>
        <div class="fields" id="uCat_item_fields_place_<?=$place_id?>" data-field-place="<?=$place_id?>"><?
            mysqli_data_seek($this->q_fields,0);
                $last_field_title='';
                $last_field_pos=0;
                $first=true;
        /** @noinspection PhpUndefinedMethodInspection */
        while($field=$this->q_fields->fetch_object()) {
                if($field->field_place_id==$place_id) {
                    $item_field_id='field_'.$field->field_id;
                    if(!empty($this->item->$item_field_id)) {
                        ?>
                        <div class="row <?=($last_field_title==$field->field_title&&$last_field_pos==$field->field_pos||$first)?'second':''?>"><?
                            if($field->label_style_id=='1') {?><div class="col-md-<?=$this->item_field_title_col_num?> field_title"><span><?
                                if(!($last_field_title==$field->field_title&&$last_field_pos==$field->field_pos)) echo uString::sql2text($field->field_title)?>
                                </span></div>
                            <div class="col-md-<?=(12-$this->item_field_title_col_num)?> field_val"><?}
                                else {?><div class="col-md-12"><?}

                                if($field->label_style_id=='2') {
                                    if(!($last_field_title==$field->field_title&&$last_field_pos==$field->field_pos)) echo '<h2 class="uCat_item_field_label">'.uString::sql2text($field->field_title).'</h2>';
                                }
                                $first=false;
                                $last_field_title=$field->field_title;
                                $last_field_pos=$field->field_pos;

                                $value=$this->item->$item_field_id;
                                if($field->field_style=='integer'||
                                    $field->field_style=='double') {
                                    echo $value;
                                }
                                elseif($field->field_style=='text line') {
                                    echo uString::sql2text($value,true);
                                }
                                elseif($field->field_style=='html text') {
                                    $txt=uString::sql2text($value,true);
                                    if($field->field_effect_id=='2') {
                                        $txt_ar=explode('<!-- pagebreak -->',$txt);
                                    ?>
                                            <div class="uCat_field_html_text">
                                                <div class="btn-group" style="display: table; float: right;">
                                                    <button class="btn btn-default btn-sm" onclick="//noinspection JSJQueryEfficiency
                                                        jQuery('#flipbook_<?=$field->field_id?>').turn( 'previous' )"><span class="icon-left-open"></span></button>
                                                    <button class="btn btn-default btn-sm" onclick="//noinspection JSJQueryEfficiency
                                                        jQuery('#flipbook_<?=$field->field_id?>').turn( 'next' )"><span class="icon-right-open"></span></button>
                                                </div>
                                                <div class="clearfix"> </div>
                                                <div id="flipbook_<?=$field->field_id?>" class="flipbook"><?
                                                    //$config = HTMLPurifier_Config::createDefault();
                                                    //$purifier = new HTMLPurifier($config);
                                                    $doc = new DOMDocument();
                                                    for($j=0;$j<count($txt_ar);$j++) {
                                                        $txt_ar[$j]=mb_convert_encoding($txt_ar[$j], 'HTML-ENTITIES', 'UTF-8');
                                                        //$txt_ar[$j] = $purifier->purify($txt_ar[$j]);
                                                        //$txt_ar[$j] = tidy_repair_string($txt_ar[$j]);
                                                        @$doc->loadHTML($txt_ar[$j]);
                                                        $txt_ar[$j] = $doc->saveHTML();?>
                                                        <div class="flipbook_items"><?=$txt_ar[$j]?></p></div>
                                                    <?}?>
                                                </div>
                                            </div>

                                        <script type="text/javascript">
                                            var bbitem_ar=$('.flipbook_items');
                                            var max_height=0;
                                            for(var i=0;i<bbitem_ar.length;i++) {
                                                if(max_height<jQuery(bbitem_ar[i]).height()) max_height=jQuery(bbitem_ar[i]).height();
                                                $(bbitem_ar[i]).addClass('flipbook_items');
                                            }
                                            //noinspection JSJQueryEfficiency
                                            $(document).ready(function() {
                                                $("#flipbook_<?=$field->field_id?>").turn({
                                                    //width: 1000,
                                                    display: 'single',
                                                    height: max_height,
                                                    autoCenter: true
                                                });
                                            });
                                        </script>
                                    <?
                                    }
                                    else echo $txt;
                                }
                                elseif($field->field_style=='multiline') {
                                    echo nl2br(uString::sql2text($value,1));
                                }
                                elseif($field->field_style=='date') {
                                    echo date('d.m.Y',$value);
                                }
                                elseif($field->field_style=='datetime') {
                                    echo date('d.m.Y H:i',$value);
                                }
                                elseif($field->field_style=='link') {
                                    $val=uString::sql2text($value,true);
                                    echo $val;
                                }
                                ?> <?=uString::sql2text($field->field_units)?>
                        </div>
                        </div>
                    <?}
                }
            }

        if($place_id===2) {
            if($this->item->manufacturer_part_number!==''&&!is_null($this->item->manufacturer_part_number)) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Заводской номер детали</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->manufacturer_part_number?></div>
                </div>
            <?}
            if($this->item->search_part_number!==''&&!is_null($this->item->search_part_number)) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Номер детали для поиска</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->search_part_number?></div>
                </div>
            <?}

            $this->item->delivery_on=(int)$this->item->delivery_on;
            if($this->item->delivery_on) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Доставка</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->delivery_on?"Есть":"Нет"?></div>
                </div>
            <?}
            $this->item->pickup_on=(int)$this->item->pickup_on;
            if($this->item->pickup_on) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Самовывоз</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->pickup_on?"Возможен":"Нет"?></div>
                </div>
            <?}
            $this->item->buy_without_order_on=(int)$this->item->buy_without_order_on;
            if($this->item->buy_without_order_on) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Покупка без предварительного заказа</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->buy_without_order_on?"Возможна":"Нет"?></div>
                </div>
            <?}
            if($this->item->delivery_time>0) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Срок местной доставки</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->delivery_time?></div>
                </div>
            <?}
            if($this->item->delivery_cost>0) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Стоимость местной доставки</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->delivery_cost?></div>
                </div>
            <?}
            if($this->item->manufacturer!="") {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Производитель</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->manufacturer?></div>
                </div>
            <?}
            if($this->item->manufactured_in!="") {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Страна происхождения</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->manufactured_in?></div>
                </div>
            <?}
            $this->item->manufacturer_warranty=(int)$this->item->manufacturer_warranty;
            if($this->item->manufacturer_warranty) {?>
                <div class="row">
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Гарантия производителя</span></div>
                    <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?=$this->item->manufacturer_warranty?"Есть":"Нет"?></div>
                </div>
            <?}
        }?>
            </div><?
    }
    public function print_fields_tabs() {
        $fields_ar=$this->uCat->get_item_tab_fields($this->item_id);
        $fields_ar_count=count($fields_ar);?>
        <div class="fields" id="uCat_item_fields_place_6" data-field-place="6">

        <ul class="nav nav-tabs" role="tablist"><?
            $first=1;
            for($i=0;$i<$fields_ar_count;$i++) {
                $field = $fields_ar[$i];
                $item_field_id = 'field_' . $field->field_id;


                if (!empty($this->item->$item_field_id)) {?>
                    <li role="presentation" class="<?=$first?'active':''?>">
                        <a
                                href="#<?= $item_field_id ?>"
                                aria-controls="<?= $item_field_id ?>" role="tab"
                                data-toggle="tab"
                        ><?= uString::sql2text($field->field_title, 1) ?></a>
                    </li>
                <?
                    $first=0;
                }
            }?>
        </ul>

        <div class="tab-content"><?
            $first=1;
            for($i=0;$i<$fields_ar_count;$i++) {
                $field = $fields_ar[$i];
                $item_field_id = 'field_' . $field->field_id;

                if(!empty($this->item->$item_field_id)) {
                    $value=$this->item->$item_field_id;
                    ?>
                    <div role="tabpanel" class="tab-pane <?=$first?('active '):''?>" id="<?=$item_field_id?>"><?
                        $first=0;
                        if($field->field_style=='integer'||
                            $field->field_style=='double') {
                            echo $value;
                        }
                        elseif($field->field_style=='text line') {
                            echo uString::sql2text($value,1);
                        }
                        elseif($field->field_style=='html text') {
                            $txt=uString::sql2text($value,1);
                            if($field->field_effect_id=='2') {
                                $txt_ar=explode('<!-- pagebreak -->',$txt);
                                ?>
                                <div class="uCat_field_html_text">
                                    <div class="btn-group" style="display: table; float: right;">
                                        <button class="btn btn-default btn-sm" onclick="//noinspection JSJQueryEfficiency
                                                jQuery('#flipbook_<?=$field->field_id?>').turn( 'previous' )"><span class="icon-left-open"></span></button>
                                        <button class="btn btn-default btn-sm" onclick="//noinspection JSJQueryEfficiency
                                                jQuery('#flipbook_<?=$field->field_id?>').turn( 'next' )"><span class="icon-right-open"></span></button>
                                    </div>
                                    <div class="clearfix"> </div>
                                    <div id="flipbook_<?=$field->field_id?>" class="flipbook"><?
                                        //$config = HTMLPurifier_Config::createDefault();
                                        //$purifier = new HTMLPurifier($config);
                                        $doc = new DOMDocument();
                                        for($j=0;$j<count($txt_ar);$j++) {
                                            $txt_ar[$j]=mb_convert_encoding($txt_ar[$j], 'HTML-ENTITIES', 'UTF-8');
                                            //$txt_ar[$j] = $purifier->purify($txt_ar[$j]);
                                            //$txt_ar[$j] = tidy_repair_string($txt_ar[$j]);
                                            @$doc->loadHTML($txt_ar[$j]);
                                            $txt_ar[$j] = $doc->saveHTML();?>
                                            <div class="flipbook_items"><?=$txt_ar[$j]?></p></div>
                                        <?}?>
                                    </div>
                                </div>

                                <script type="text/javascript">
                                    var bbitem_ar=$('.flipbook_items');
                                    var max_height=0;
                                    for(var i=0;i<bbitem_ar.length;i++) {
                                        if(max_height<jQuery(bbitem_ar[i]).height()) max_height=jQuery(bbitem_ar[i]).height();
                                        $(bbitem_ar[i]).addClass('flipbook_items');
                                    }
                                    //noinspection JSJQueryEfficiency
                                    $(document).ready(function() {
                                        $("#flipbook_<?=$field->field_id?>").turn({
                                            //width: 1000,
                                            display: 'single',
                                            height: max_height,
                                            autoCenter: true
                                        });
                                    });
                                </script>
                                <?
                            }
                            else echo $txt;
                        }
                        elseif($field->field_style=='multiline') {
                            echo nl2br(uString::sql2text($value,1));
                        }
                        elseif($field->field_style=='date') {
                            echo date('d.m.Y',$value);
                        }
                        elseif($field->field_style=='datetime') {
                            echo date('d.m.Y H:i',$value);
                        }
                        elseif($field->field_style=='link') {
                            $val=uString::sql2text($value,true);
                            echo $val;
                        }
                    ?></div>
                <?}
            }?>
        </div>
    </div><?
    }

    private function define_breadcrumb() {
        if($this->show_link_to_sects_in_bc) $this->uCore->uBc->add_info->html='<li><a href="'.u_sroot.'uCat/sects">Каталог</a></li>';
        else $this->uCore->uBc->add_info->html='';

        if($this->sect_id) $this->uCore->uBc->add_info->html.='<li><a href="'.u_sroot.$this->uCore->mod.'/cats/'.(strlen($this->sect_url)?uString::sql2text($this->sect_url):$this->sect_id).'">'.$this->sect_title.'</a></li>';

        if($this->cat_id) $this->uCore->uBc->add_info->html.='<li><a href="'.u_sroot.$this->uCore->mod.'/items/'.(strlen($this->cat_url)?uString::sql2text($this->cat_url):$this->cat_id).'">'.$this->cat_title.'</a></li>';

        $this->uCore->uBc->add_info->html.='<li class="active"><a id="uCat_item_breadcrumb" href="'.u_sroot.$this->uCore->mod.'/item/'.(strlen($this->item->item_url)?uString::sql2text($this->item->item_url,1):$this->item->item_id).'">'.uString::sql2text($this->item->item_title,1).'</a></li>';
    }

    private function get_uDrive_file_hash() {
        if($this->item->file_id!='0') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
                file_hashname,
                file_name
                FROM
                u235_files
                WHERE
                file_id=:file_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $this->item->file_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('270'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {//file is not found. Set file_id to 0
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                    u235_items
                    SET
                    file_id=0
                    WHERE
                    item_id=:item_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('280'/*.$e->getMessage()*/);}

                return $this->item->file_id=0;
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $this->file_hashname=$qr->file_hashname;
            $this->file_name=uString::sql2text($qr->file_name);
        }
        return 1;
    }

    private function tune2var() {
        if(isset($_GET["var_id"])) {
            $this->selected_var_id=$_GET["var_id"];

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                item_article_number,
                var_type_title,
                price AS item_price, 
                prev_price,
                img_time AS item_img_time,
                inaccurate_price,
                request_price,
                items_variants.avail_id AS item_avail,
                u235_items_avail_values.avail_label,
                u235_items_avail_values.avail_descr,
                u235_items_avail_values.avail_type_id,
                item_type_id AS item_type,
                var_quantity AS quantity,
                file_id
                FROM 
                items_variants
                JOIN
                items_variants_types
                ON
                items_variants.var_type_id=items_variants_types.var_type_id AND
                items_variants.site_id=items_variants_types.site_id
                JOIN
                u235_items_avail_values
                ON
                items_variants.avail_id=u235_items_avail_values.avail_id AND
                u235_items_avail_values.site_id=items_variants.site_id
                WHERE 
                var_id=:var_id AND
                items_variants_types.site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $this->selected_var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    $enable_var_options=(int)$this->uFunc->getConf("enable_var_options","uCat");
                    if($enable_var_options) $this->item->item_title=/*". ".*/$res->var_type_title;
                    else $this->item->item_title.=". (".$res->var_type_title.')';

                    $this->item->item_article_number=$res->item_article_number;
                    $this->item->item_price=(float)$res->item_price;
                    $this->item->prev_price=(float)$res->prev_price;
                    $this->item->item_img_time=(int)$res->item_img_time;
                    $this->item->inaccurate_price=(int)$res->inaccurate_price;
                    $this->item->request_price=(int)$res->request_price;
                    $this->item->item_avail=(int)$res->item_avail;
                    $this->item->avail_label=$res->avail_label;
                    $this->item->avail_descr=$res->avail_descr;
                    $this->item->avail_type_id=(int)$res->avail_type_id;
                    $this->item->item_type=(int)$res->item_type;
                    $this->item->quantity=(float)$res->quantity;
                    $this->item->file_id=(int)$res->file_id;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('290'/*.$e->getMessage()*/);}
        }
        else {
            $this->selected_var_id=(int)$this->uCat->item_id2default_variant_id($this->item_id);
        }
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uDrive=new uDrive\common($this->uCore);

        $this->helper=new uCat_helper($this->uCore);

        $this->avatar=new uCat_item_avatar($this->uCore);
        $this->art_avatar=new uCat_art_avatar($this->uCore);

        $this->uCat=new \uCat\common($this->uCore);

        $this->item_page_title_pos          = $this->uFunc->getConf('item_page_title_pos','uCat');
        $this->how_to_call_item_images      = $this->uFunc->getConf('how to call item_images','uCat');
        $this->arts_label                   = $this->uFunc->getConf('arts_label','uCat');
        $this->inaccurate_price_label       = $this->uFunc->getConf('inaccurate_price_label','uCat');

        $this->enable_item_plus_and_minus   = (int) $this->uFunc->getConf('enable_item_plus_and_minus','uCat');
        $this->buy_button_show              = (int) $this->uFunc->getConf('buy_button_show','uCat');
        $this->enable_var_options           = (int) $this->uFunc->getConf("enable_var_options","uCat");
        $this->item_img_col_num             = (int) $this->uFunc->getConf('item_img_col_num','uCat');
        $this->price_is_used                = (int) $this->uFunc->getConf('price_is_used','uCat');
        $this->item_availability_show       = (int) $this->uFunc->getConf('item_availability_show','uCat');
        $this->item_quantity_show           = (int) $this->uFunc->getConf('item_quantity_show','uCat');
        $this->item_prev_price_show         = (int) $this->uFunc->getConf('item_prev_price_show','uCat');
        $this->item_field_title_col_num     = (int) $this->uFunc->getConf('item_field_title_col_num','uCat');
        $this->show_link_to_sects_in_bc     = (int) $this->uFunc->getConf('Show link to sects in bc','uCat');

        $this->inaccurate_price_descr       = htmlspecialchars(strip_tags($this->uFunc->getConf('inaccurate_price_descr','uCat')));
        $this->link_item_descr              = uString::sql2text($this->uFunc->getConf('link_item_descr','uCat'),1);
        $this->link_item_label              = uString::sql2text($this->uFunc->getConf('link_item_label','uCat'),1);

        $this->checkData();
        $this->get_item_fields();
        $this->get_item_data();
        $this->tune2var();
        $this->get_cat_info();
        if(!(int)$this->cat_id) {
            $q_cats = $this->uCat->get_item_cats($this->item_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while ($cat = $q_cats->fetch(PDO::FETCH_OBJ)) {
                if (!(int)$cat->cat_id) continue;
                if ($this->uCat->cat_exists($cat->cat_id)) {
                    $this->uCat->set_certain_primary_cat_id($this->item_id, $cat->cat_id);
                    $this->get_cat_info();
                    break;
                }
                else $this->uCat->detach_itemFromCat($cat->cat_id, $this->item_id);
            }
        }

        $this->get_sect_info();
        $this->get_item_pictures();

        $this->item_img=new uCat_item_img($this->uCore);
        if($this->uSes->access(25)) {
            $this->get_fields_places();
            $this->get_fields_filter_types();
            $this->get_fields_types();
            $this->get_fields_label_styles();
            $this->get_fields_effects();
            $this->get_avail_types();
            $this->get_uDrive_file_hash();

            /** @noinspection PhpUndefinedMethodInspection */
            while($place=$this->q_fields_places->fetch_object())
                $this->field_place_id2title[$place->place_id]=uString::sql2text($place->place_title,true);
        }

        $this->define_breadcrumb();

        //phpjs
        $this->uFunc->incJs(u_sroot."js/phpjs/functions/strings/str_replace.js");

        $this->uFunc->incJs(u_sroot.'js/turnjs/lib/turn.min.js');

//fancybox
        $this->uFunc->incJs(u_sroot.'js/fancybox/jquery.fancybox.pack.js');
        $this->uFunc->incCss(u_sroot.'js/fancybox/jquery.fancybox.css');

//bootstrap-touchspin
        $this->uFunc->incCss('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
        $this->uFunc->incJs('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js');

        $this->uFunc->incJs('/uCat/js/common.js');
        $this->uFunc->incJs('/uCat/js/item.min.js');


        if($this->uSes->access(25)) {
            //phpjs
            $this->uFunc->incJs("/js/phpjs/functions/strings/htmlspecialchars.min.js");
            $this->uFunc->incJs("/js/phpjs/functions/strings/explode.js");
            //popConfirm
            $this->uFunc->incJs("/js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js");
            //eip_element
            $this->uFunc->incJs("/js/u235/eip_element.min.js");

            $this->uFunc->incJs('/uCat/js/item_admin.min.js');

            //tinymce
            $this->uFunc->incJs('/js/tinymce/tinymce.min.js');
        }

        $this->increase_views_counter();
    }
}
$uCat=new uCat_item($this);

$item_title=uString::sql2text($uCat->item->item_title,1);

ob_start();
?>
    <script type="text/javascript">
        var enable_var_options=<?=$uCat->enable_var_options?>

        let options_ar = [];
        let values_ar=[];
        let option_id2i = [];
        let value_id2i=[];
        let j;
        let k;
        let n;

        let variants_options_values=[];
        let variants_data=[];
        let def_var_options_values=[];
    </script>
<?if($uCat->uSes->access(25)) {
/** @noinspection PhpIncludeInspection */
include_once 'uDrive/inc/my_drive_manager.php';?>
    <div id="uDrive_my_drive_uploader_init"></div>
    <script type="text/javascript">
        if(typeof uCat_item_admin==="undefined") uCat_item_admin={};
        if(typeof uDrive_manager==="undefined") uDrive_manager={};

        uCat_item_admin.uDrive_folder_id=<?=$uCat->item->uDrive_folder_id;?>;
        $(document).ready(function() {
            uDrive_manager.init('uDrive_my_drive_uploader',<?=$uCat->item->uDrive_folder_id;?>, 1, "uCat_item_admin.insert_tinymce_url", 'uCat', 'item',<?=$uCat->item_id?>);
        });
    </script>
<?
}


if(!empty($uCat->item->seo_title)) $this->page['page_title']=$uCat->item->seo_title;
else $this->page['page_title']=uString::sql2text($uCat->item->item_title,1).'. '.($uCat->cat_id?$uCat->cat_title:'').'. '.($uCat->sect_id?$uCat->sect_title:'').'. Каталог';
if(!empty($uCat->item->seo_descr)) $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->item->seo_descr))))));
else $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->item->item_descr))))));
if(!empty($uCat->item->item_keywords)) $this->page['meta_keywords']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->item->item_keywords))))));

$this->uFunc->incCss(u_sroot.'uCat/css/item.min.css');
$this->uFunc->incCss(u_sroot.'templates/site_'.site_id.'/css/uCat/uCat.css');

/** @noinspection PhpIncludeInspection */
include 'uCat/inc/request_price_form.php';?>

<div class="uCat_item">
    <div class="cont row"><?if($uCat->item_page_title_pos=='top') {?>
            <div class="col-md-12" style="padding:0;">
                <?if($uCat->uSes->access(25)){?>
                <div class="bs-callout bs-callout-danger uCat_item_no_download_link_alert" style="display: none">
                    Этот товар имеет тип "Ссылка для скачивания", однако файл не прикреплен.<br>Этот товар невозможно будет купить!!!
                </div>
                <?}?>
                <?if($uCat->uFunc->getConf("show_item_article_number","uCat")){?><p><span class="text-primary uCat_item_article_number" id="uCat_item_article_number_<?=$uCat->item_id?>"><?=$uCat->item->item_article_number?></span></p><?}
                if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
                <h1 class="page-header item_title" id="uCat_item_title"><?=uString::sql2text($uCat->item->item_title,1)?></h1>
                <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2) include "uCat/templates/search.php";?>
            </div>
        <?}?>
        <div class="img col-md-<?=$uCat->item_img_col_num?>">
            <div class="img_container">
                <a id="uCat_item_avatar_a_<?=$uCat->item->item_id?>" href="<?=$uCat->avatar->get_avatar('orig',$uCat->item->item_id,$uCat->item->item_img_time)?>" class="fancybox">
                    <img
                        id="uCat_item_avatar_img_<?=$uCat->item->item_id?>"
                        src="<?=$uCat->avatar->get_avatar(640,$uCat->item->item_id,$uCat->item->item_img_time)?>"
                        alt="<?=htmlspecialchars($item_title).'. '.($uCat->cat_id?$uCat->cat_title:'').'. '.($uCat->sect_id?$uCat->sect_title:'')?>"
                        title="<?=htmlspecialchars(uString::sql2text($uCat->item->item_title,1)).'. '.($uCat->cat_id?$uCat->cat_title:'').'. '.($uCat->sect_id?$uCat->sect_title:'')?>"
                        class="img-responsive"
                        >
                </a>
            </div>

            <p class="clearfix"> </p>

            <?#################------VARIANTS------#############?>

            <?
            if($uCat->item->has_variants) {
                $avatar_style="item_page";
                if ($uCat->options_number) include "uCat/inc/options_table.php";
                else include "uCat/inc/variants_table.php";
            }
            else {?>
            <div id="uCat_item_variants"></div>
            <?}
            if($uCat->item_availability_show) {?>
                <div  id="uCat_item_availability" class="availability <?=$uCat->uCat->avail_type_id2class($uCat->item->avail_type_id)?> uTooltip" title="<?=(int)$uCat->base_type_id==1?$uCat->link_item_descr:$uCat->item->avail_descr?>">
                    <p class="clearfix"> </p>
                    <span id="uCat_item_availability_label"><?=(int)$uCat->base_type_id==1?(int)$uCat->base_type_id==1?$uCat->link_item_label:$uCat->item->avail_label:$uCat->item->avail_label?></span>
                </div>
            <?}?>


            <? if($uCat->item_quantity_show) {?>
                <span>
                    <p class="clearfix"> </p>
                    Остаток: <span id="uCat_item_quantity" class="uCat_item_quantity"><?=$uCat->item->quantity?></span> <span id="uCat_item_units"><?=$uCat->item->unit_name?></span>
                </span>
            <?}?>



            <?if($uCat->price_is_used) {
                $currency='р';
                if(site_id==54) {
                    $currency='Eur';
                }?>
                <div class="price">
                    <p class="clearfix"> </p>
                    <?if($uCat->item_prev_price_show) {?>
                    <div id="uCat_prev_price_<?=$uCat->item_id?>" class="prev_price text-primary" style="<?=(int)$uCat->item->prev_price?"":"display:none;"?>">
                        <?
                        print number_format($uCat->item->prev_price, (count(explode('.',$uCat->item->prev_price))>1?2:0),'.', ' ');
                        ?> <?=$currency?>
                    </div>
                    <?}?>
                        <span id="uCat_item_price_<?=$uCat->item_id?>" style="visibility: <?=$uCat->item->request_price?'hidden':'visible'?>">
                        <?

                            print number_format($uCat->item->item_price, (count(explode('.',$uCat->item->item_price))>1?2:0),'.', ' ');
                            ?>
                            <?if(site_id==54) {?><span>Eur</span><?}
                            else {?><span class="icon-rouble"></span><?}?>
                            </span>
                            <span id="item_inaccurate_price_marker_<?=$uCat->item_id?>" style="display:<?=(int)$uCat->item->inaccurate_price?'inline':'none'?>">*</span>

                        <button
                                id="item_inaccurate_price_request_btn_<?=$uCat->item_id?>"
                                class="btn btn-default btn-sm"
                                style="display:<?=(int)$uCat->item->inaccurate_price?'inline':'none'?>"
                                onclick="uCat_request_price_form.openForm(<?=$uCat->item_id?>,0,'<?=rawurlencode(uString::sql2text($uCat->item->item_title,1))?>')"
                        >Уточнить цену</button>
                </div>
            <?}?>
            <div class="clearfix">&nbsp;</div>

            <?if($uCat->buy_button_show) {
                //Условия изменения кнопки:
                //avail_type_id===2 - кнопка не активна
                //avail_type_id===3 - кнопка не активна
                //quantity===0 - кнопка не активна
                //item_quantity_show выкл (0) - кнопка не активна
                //request_price - текст "Запросить цену". На клик - uCat_request_price_form.openForm. Класс btn-default
                //avail_type_id===4 - текст "Заказать". На клик  - uCat_request_price_form.openForm
                //item_price==0 - текст "Получить бесплатно"

                //В остальных случаях: текст - buy_btn_label, на клик uCat_cart.buy, класс btn-primary

                ?>
            <button
                    id="uCat_item_buy_btn"
                    class="pull-left btn
                    <?=$uCat->item->request_price?"btn-default":"btn-primary"?>
                    <?=((int)$uCat->item->has_variants && !$uCat->enable_item_plus_and_minus)?'col-lg-12 col-md-12 col-sm-12 col-xs-12':''?>
                    <?=(
                    ($uCat->item->avail_type_id===2||$uCat->item->avail_type_id===3)||
                    (!$uCat->item->quantity&&$uCat->item_quantity_show)
                    )?'disabled':''?>
                    "
                    onclick="<?
                    if(!$uCat->item->request_price&&(int)$uCat->item->avail_type_id!==4) {
                        if($uCat->item_quantity_show && $uCat->enable_item_plus_and_minus) {?>
                            uCat_cart.buy_indicate_quantity(<?=$uCat->item->item_id?>,uCat.var_selected_price[<?=$uCat->item->item_id?>],uCat.var_selected[<?=$uCat->item->item_id?>]);
                        <?}
                        else {?>
                            uCat_cart.buy(<?=$uCat->item->item_id?>,uCat.var_selected_price[<?=$uCat->item->item_id?>],uCat.var_selected[<?=$uCat->item->item_id?>]);
                        <?}
                    }
                    else {?>
                        uCat_request_price_form.openForm(<?=$uCat->item_id?>,0,'<?=rawurlencode(uString::sql2text($uCat->item->item_title, 1))?>');
                    <?}?>
                    "
            ><?
                if($uCat->item->request_price) echo 'Запросить цену';
                elseif($uCat->item->avail_type_id===4) echo 'Заказать';//Под заказ
                elseif(!$uCat->item->item_price) print $uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1);
                else echo $uCat->uFunc->getConf("buy_btn_label","uCat")?></button>
            <?}?>
            <?if($uCat->enable_item_plus_and_minus) {?>
                <div class="input-group input-group-sm pull-left" style="margin-left: 20px;">
                    <input type="text" data-max="<?=$uCat->item->quantity?>" id="uCat_item_<?=$uCat->item_id?>_count" autocomplete="off"  class="items_count_spinner" value="1">
                </div>
            <?}?>
            <div id="inaccurate_price_label_<?=$uCat->item_id?>" style="clear:both; visibility: <?=(int)$uCat->item->inaccurate_price?'visible':'hidden'?>" onclick="uCat_request_price_form.openForm(<?=$uCat->item_id?>,0,'<?=rawurlencode(uString::sql2text($uCat->item->item_title,1))?>')"><?=$uCat->inaccurate_price_label?></div>
        </div>

        <div class="col-md-<?=12-$uCat->item_img_col_num?> info field_place_2">
            <?if($uCat->item_page_title_pos=='right') {
                if($uCat->uSes->access(25)){?>
                <div class="bs-callout bs-callout-danger uCat_item_no_download_link_alert" style="display: none">
                    Этот товар имеет тип "Ссылка для скачивания", однако файл не прикреплен.<br>Этот товар невозможно будет купить!!!
                </div>
                <?}?>
                <?if($uCat->uFunc->getConf("show_item_article_number","uCat")){?><p><span class="text-primary uCat_item_article_number"  id="uCat_item_article_number_<?=$uCat->item_id?>"><?=$uCat->item->item_article_number?></span></p><?}
                if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
                <h1 class="page-header item_title" id="uCat_item_title"><?=uString::sql2text($uCat->item->item_title,1)?></h1>
            <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2||(int)$uCat->uFunc->getConf("search_field_pos","uCat")===3) include "uCat/templates/search.php";
            }?>
            <?$uCat->print_fields(2)?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12"><?$uCat->print_fields(3)?></div>
    </div>


    <div id="uCat_item_pictures_cnt">
    <?if(mysqli_num_rows($uCat->q_item_pics)) {?>
        <div class="row">
            <div class="col-md-12">
                <div class="item_pictures">
                    <h2><?=$uCat->how_to_call_item_images?></h2>
                    <div class="wrapper-with-margin">
                        <div id="uCat_item_pictures_slider" class="owl-carousel dots_style_4">
                            <?
                            /** @noinspection PhpUndefinedMethodInspection */
                            while($img=$uCat->q_item_pics->fetch_object()) {
                                $orig=$uCat->item_img->get_img('orig',$uCat->item_id,$img->img_id,$img->timestamp);
                                $sm=$uCat->item_img->get_img('item_page',$uCat->item_id,$img->img_id,$img->timestamp);
                                if($orig&&$sm){?>
                                <div style="<?=site_id==54?("background-image:url('$sm'); background-size:cover; height:200px;"):""?>">
                                    <a class="fancybox" rel="item_pictures" href="<?=$orig?>">
                                        <img class="img" src="<?=$sm?>" style="<?=site_id==54?("opacity:0; height:100%; width:100%;"):""?>" alt="<?=htmlspecialchars(strip_tags($item_title))?>"><!--TODO-nik87 сделать настройку - как отображать доп. изображения. - прям в диаоге загрузки изображений показывать ее-->
                                    </a>
                                </div>
                                <?}?>
                            <?}?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?}?>
    </div>


    <div class="row">
        <div class="col-md-12"><?$uCat->print_fields(5)?></div>
    </div>


    <div class="row"><div class="col-md-12">
            <div class="item_descr" id="uCat_item_descr"><?=uString::sql2text($uCat->item->item_descr,true)?></div>
        </div></div>

    <?$uCat->print_fields_tabs()?>

    <div class="row">
        <div class="col-md-12"><?$uCat->print_fields(4)?></div>
    </div>

    <?$query=$uCat->helper->get_item_articles($uCat->item->item_id);?>

    <div class="row">
        <div class="col-md-12">
            <div class="uCat_articles" id="uCat_item_articles" <?=!mysqli_num_rows($query)?'style="display:none"':''?>>
                <?if(mysqli_num_rows($query)) {?>
                <?if(mysqli_num_rows($query)>1) {?>
                <div class="customNavigation">

                </div>
                <?}?>
                <h2 class="title"><?=$uCat->arts_label?></h2>
                <div id="uCat_articles_slider" class="owl-carousel uCat_articles_slider dots_style_4">
                    <?
                    /** @noinspection PhpUndefinedMethodInspection */
                    while($arts=$query->fetch_object()) {?>
                        <div class="item">
                            <a href="<?=u_sroot?>uCat/article/<?=$arts->art_id?>" style="<?if($arts->art_avatar_time!='0') {
                                if($art_avatar=$uCat->art_avatar->get_avatar(640,$arts->art_id,$arts->art_avatar_time)) {
                                }
                                else {
                                    $art_avatar='http://localhost/nofile#';//http://localhost/nofile# потому что блять браузеры дописывают всякую херню к пустому src или к src с #
                                    ?>display:none<?
                                }
                            }
                            else {
                                $art_avatar='http://localhost/nofile#';
                                ?>display:none<?
                            }
                            ?>">
                                <img id="uCat_item_art_avatar_<?=$arts->art_id?>" title="<?=htmlspecialchars(strip_tags(uString::sql2text($arts->art_title)))?>" class="avatar" src="<?=$art_avatar?>" alt="<?=htmlspecialchars(strip_tags(uString::sql2text($arts->art_title)))?>">
                            </a>
                            <h3 class="title"><a href="<?=u_sroot?>uCat/article/<?=$arts->art_id?>"><span id="uCat_item_art_title_<?=$arts->art_id?>"><?=uString::sql2text($arts->art_title)?></span></a>
                        <?if($uCat->uSes->access(25)){?><button class="btn btn-default btn-xs uTooltip pull-right u235_eip" title="Редактировать статью" onclick="uCat.edit_art(<?=$arts->art_id?>)"><span class="glyphicon glyphicon-pencil"></span></button><?}?>
                            </h3>
                            <div id="uCat_item_art_<?=$arts->art_id?>"><?
                            $txt=uString::sql2text($arts->art_text,true);
                            $pos=mb_strpos($txt,'<!-- my page break -->',0, 'UTF-8');
                            if(!$pos) {
                                $pos=mb_strpos($txt,'<!-- pagebreak -->',0, 'UTF-8');
                                if(!$pos) {
                                    echo mb_substr(strip_tags($txt),0,600,'UTF-8').'...';
                                }
                                else echo mb_substr($txt,0,$pos,'UTF-8');
                            }
                            else echo mb_substr($txt,0,$pos,'UTF-8');?>
                            </div>
                        </div>
                    <?}?>
                </div>
                <?}?>
            </div>
        </div>
    </div>

    <div id="item_views_counter" class="text-muted pull-right"><span class="icon-eye"></span> <?=$uCat->item->views_counter ?></div>
</div>

<script type="text/javascript">
    if(typeof uCat==="undefined") uCat={};
    uCat.one_click_add2cart_btn=<?=$uCat->uFunc->getConf("one_click_add2cart_btn","uCat")?>;
    uCat.item_img_time=<?=$uCat->item->item_img_time?>;
    uCat.item_id=<?=$uCat->item_id?>;
    uCat.sect_id=<?=$uCat->sect_id?>;

    uCat.var_selected=[];
    uCat.var_selected[<?=$uCat->item_id?>]=<?=(int)$uCat->item->has_variants?$uCat->selected_var_id:0?>;
    uCat.var_selected_price=[];
    uCat.var_selected_price[<?=$uCat->item_id?>]=<?=$uCat->item->item_price?>;
    uCat.editable=false;
</script>

<?if($uCat->uSes->access(25)){//admin part?>
    <script type="text/javascript">
        if(typeof uCat_item_admin==="undefined") uCat_item_admin={};

        uCat.editable=true;
        uCat_item_admin.item_id=<?=$uCat->item_id?>;
        uCat.item_title="<?=rawurlencode(uString::sql2text($uCat->item->item_title,1))?>";
        uCat_item_admin.item_article_number=decodeURIComponent("<?=rawurlencode($uCat->item->item_article_number)?>");
        uCat_item_admin.quantity=<?=$uCat->item->quantity;?>;
        uCat_item_admin.item_price=<?=$uCat->item->item_price?>;
        uCat_item_admin.prev_price=<?=$uCat->item->prev_price?>;
        uCat_item_admin.inaccurate_price=<?=(int)$uCat->item->inaccurate_price?>;
        uCat_item_admin.inaccurate_price_descr="<?=rawurlencode($uCat->inaccurate_price_descr)?>";
        uCat_item_admin.request_price=<?=$uCat->item->request_price?>;
        uCat_item_admin.item_avail=<?=$uCat->item->item_avail?>;
        uCat_item_admin.widgets=[];
        <? $widgets=$uCat->uCat->get_item_widgets($uCat->item_id);
        for($i=0;$i<8;$i++) {
            $wgt="wgt_".$i?>
        uCat_item_admin.widgets[<?=$i?>]=<?=(int)$widgets->$wgt?>;
        <?}?>
        uCat.item_url="<?=rawurlencode(uString::sql2text($uCat->item->item_url))?>";
        uCat.item_keywords="<?=rawurlencode(uString::sql2text($uCat->item->item_keywords))?>";
        uCat.seo_title="<?=rawurlencode(uString::sql2text($uCat->item->seo_title))?>";
        uCat.seo_descr="<?=rawurlencode(uString::sql2text($uCat->item->seo_descr))?>";
        uCat.site_title="<?=rawurlencode(site_name)?>";
        uCat_item_admin.item_type=<?=(int)$uCat->item->item_type?>;
        uCat_item_admin.base_type_id=<?=$uCat->base_type_id?>;
        uCat_item_admin.file_id=<?=$uCat->item->file_id?>;
        uCat_item_admin.file_name="<?=isset($uCat->file_name)?$uCat->file_name:''?>";
        uCat_item_admin.file_hashname="<?=isset($uCat->file_hashname)?$uCat->file_hashname:''?>";
        uCat_item_admin.has_variants=<?=$uCat->item->has_variants?>;
        uCat_item_admin.unit_id=<?=(int)$uCat->item->unit_id?>;
        uCat_item_admin.unit_name=decodeURIComponent("<?=rawurlencode($uCat->item->unit_name)?>");

        uCat_item_admin.link_item_label="<?=rawurlencode($uCat->link_item_label)?>";
        uCat_item_admin.link_item_descr="<?=rawurlencode($uCat->link_item_descr)?>";

        if(typeof uCat_item_admin.avail_id==="undefined") uCat_item_admin.avail_id=[];
        if(typeof uCat_item_admin.avail_label==="undefined") uCat_item_admin.avail_label=[];
        if(typeof uCat_item_admin.avail_descr==="undefined") uCat_item_admin.avail_descr=[];
        if(typeof uCat_item_admin.avail_class==="undefined") uCat_item_admin.avail_class=[];
        if(typeof uCat_item_admin.avail_id2i==="undefined") uCat_item_admin.avail_id2i=[];

        <?
        $avails=$uCat->uCat->get_avails();
        /** @noinspection PhpUndefinedMethodInspection */
        while($avail=$avails->fetch_object()) {?>
        var i=uCat_item_admin.avail_id.length;
            uCat_item_admin.avail_id[i]=<?=$avail->avail_id?>;
            uCat_item_admin.avail_label[i]="<?=rawurlencode(uString::sql2text($avail->avail_label,true))?>";
            uCat_item_admin.avail_descr[i]="<?=rawurlencode(uString::sql2text($avail->avail_descr,true))?>";
            uCat_item_admin.avail_class[i]="<?=$uCat->uCat->avail_type_id2class($avail->avail_type_id)?>";
            uCat_item_admin.avail_id2i[uCat_item_admin.avail_id[i]]=i;
        <?}?>

        if(typeof uCat.field_place_id2title==="undefined") uCat.field_place_id2title=[];
        if(typeof uCat.filter_type_id==="undefined") uCat.filter_type_id=[];
        if(typeof uCat.filter_type_sql==="undefined") uCat.filter_type_sql=[];
        if(typeof uCat.filter_type_title==="undefined") uCat.filter_type_title=[];
        if(typeof uCat.field_type_id==="undefined") uCat.field_type_id=[];
        if(typeof uCat.field_sql_type==="undefined") uCat.field_sql_type=[];

        <?mysqli_data_seek($uCat->q_fields_places,0);
        /** @noinspection PhpUndefinedMethodInspection */
        while($place=$uCat->q_fields_places->fetch_object()) {?>
        uCat.field_place_id2title[<?=$place->place_id?>]="<?=uString::sql2text($place->place_title,true)?>";
        <?}
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$fields_filter_types=$uCat->q_fields_filter_types->fetch_object();$i++) {?>
        uCat.filter_type_id[<?=$i?>]=<?=$fields_filter_types->filter_type_id?>;
        uCat.filter_type_sql[<?=$i?>]="<?=$fields_filter_types->filter_type_sql?>";
        uCat.filter_type_title[<?=$i?>]="<?=$fields_filter_types->filter_type_title?>";
        <?}
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$q_fields_types=$uCat->q_fields_types->fetch_object();$i++) {?>
        uCat.field_type_id[<?=$i?>]=<?=$q_fields_types->field_type_id?>;
        uCat.field_sql_type[<?=$i?>]="<?=$q_fields_types->field_sql_type?>";
        <?}?>
    </script>
    <!-- Modals -->
    <?include 'dialogs/item_admin.php';?>
<?}?>

<script type="text/javascript">
    var item_quantity_show=<?=$uCat->item_quantity_show?>;
    let item_availability_show=<?=$uCat->item_availability_show?>;
    let item_prev_price_show=<?=$uCat->item_prev_price_show?>;
    let price_is_used=<?=$uCat->price_is_used?>;
    let buy_button_show=<?=$uCat->buy_button_show?>;
    let has_options=<?=$uCat->options_number?>;
    let buy_btn_label=decodeURIComponent("<?=rawurlencode($uCat->uFunc->getConf("buy_btn_label","uCat"))?>");
    let get_item_for_free_btn_txt=decodeURIComponent("<?=rawurlencode($uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1))?>");
    let enable_item_plus_and_minus=<?=$uCat->enable_item_plus_and_minus;?>;
</script>

<?// include 'uCat/buy_form.inc.php';?>
<?$this->page_content=ob_get_contents();
ob_end_clean();

/** @noinspection PhpIncludeInspection */
include "templates/template.php";
