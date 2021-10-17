<?php
namespace uCat\admin;

use DOMDocument;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_item_get_fields_only_ajax{
    private $uFunc;
    private $uSes;
    private $uCore,
        $item_id,
        $item,
        $items_fields_q_select,
        $q_fields;
    public $place_id;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['place_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        $this->place_id=(int)$_POST['place_id'];
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(20);
    }
    private function get_item_fields() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields=$this->uCore->query("uCat","SELECT DISTINCT
        `u235_fields`.`field_id`,
        `field_title`,
        `field_pos`,
        `field_units`,
        `field_style`,
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
        `u235_fields`.`field_place_id`='".$this->place_id."' AND
        `u235_fields`.`site_id`='".site_id."'
        ORDER BY
        `field_pos` ASC,
        `field_title` ASC
        ")) $this->uFunc->error(40);
        $this->items_fields_q_select='';
        /** @noinspection PhpUndefinedMethodInspection */
        while($field=$this->q_fields->fetch_object()) {
            $this->items_fields_q_select.="`field_".$field->field_id."`,";
        }
    }
    private function get_item_data() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            ".$this->items_fields_q_select."
            item_id,
            item_avail,
            item_img_time,
            item_title,
            item_descr,
            item_price,
            inaccurate_price,
            item_url,
            seo_title,
            seo_descr,
            item_keywords,
            avail_label,
            avail_descr,
            avail_id,
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
            item_id=:item_id AND
            u235_items.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }
    public function print_fields() {
        mysqli_data_seek($this->q_fields,0);
        $last_field_title='';
        $last_field_pos=0;
        $first=true;

        /** @noinspection PhpUndefinedMethodInspection */
        while($field=$this->q_fields->fetch_object()) {
            $item_field_id='field_'.$field->field_id;
            if(!empty($this->item->$item_field_id)) {
                ?>
                <div class="row <?=($last_field_title==$field->field_title&&$last_field_pos==$field->field_pos||$first)?'second':''?>"><?
                if($field->label_style_id=='1') {?><div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span><?
                    if(!($last_field_title==$field->field_title&&$last_field_pos==$field->field_pos)) echo uString::sql2text($field->field_title)?>
                                        </span></div>
                <div class="col-md-<?=(12-$this->uCore->uFunc->getConf("item_field_title_col_num","uCat"))?> field_val"><?}
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
                                <button class="btn btn-default btn-sm" onclick="jQuery('#flipbook_<?=$field->field_id?>').turn( 'previous' )"><span class="icon-left-open"></span></button>
                                <button class="btn btn-default btn-sm" onclick="jQuery('#flipbook_<?=$field->field_id?>').turn( 'next' )"><span class="icon-right-open"></span></button>
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
                            var bbitem_ar=jQuery('.flipbook_items');
                            var max_height=0;
                            for(var i=0;i<bbitem_ar.length;i++) {
                                if(max_height<jQuery(bbitem_ar[i]).height()) max_height=jQuery(bbitem_ar[i]).height();
                            }

                            jQuery("#flipbook_<?=$field->field_id?>").turn({
                                display:'single',
                                height: max_height,
                                autoCenter: true
                            });
                        </script> <?
                        }
                        else echo $txt;
                    }
                    elseif($field->field_style=='multiline') {
                        echo nl2br(uString::sql2text($value,true));
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

        if($this->place_id===2) {
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
                    <div class="col-md-<?=$this->uCore->uFunc->getConf("item_field_title_col_num","uCat")?> field_title"><span>Страна произхождения</span></div>
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
        }
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
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();
        $this->get_item_fields();
        $this->get_item_data();
    }
}
$uCat=new admin_item_get_fields_only_ajax($this);
ob_start();
if($uCat->place_id===6) $uCat->print_fields_tabs();
else $uCat->print_fields();
$cnt=ob_get_contents();
ob_end_clean();?>
{
    'status' : 'done',
    'content' : '<?=rawurlencode($cnt)?>',
    'place_id':'<?=$uCat->place_id?>'
}
