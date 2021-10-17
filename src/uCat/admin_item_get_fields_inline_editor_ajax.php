<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "lib/simple_html_dom.php";
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class admin_item_get_fields_inline_editor_ajax{
    private $fields_ar;
    private $uFunc;
    private $uSes;
    private $uCore,$place_id,$item_id,$items_fields_q_select,$item;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['place_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        $this->place_id=$_POST['place_id'];
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->place_id)) $this->uFunc->error(30);
    }
    private function get_item_fields() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_fields.field_id,
            field_title,
            field_pos,
            field_units,
            field_style,
            field_sql_type,
            field_place_id,
            field_effect_id,
            label_style_id
            FROM
            u235_fields
            JOIN 
            u235_fields_types
            ON
            u235_fields.field_type_id=u235_fields_types.field_type_id
            JOIN 
            u235_cats_fields
            ON
            u235_cats_fields.field_id=u235_fields.field_id AND
            u235_cats_fields.site_id=u235_fields.site_id
            JOIN
            u235_cats_items
            ON
            u235_cats_items.cat_id=u235_cats_fields.cat_id AND
            u235_cats_items.site_id=u235_fields.site_id
            WHERE
            u235_cats_items.item_id=:item_id AND
            (u235_fields.field_place_id=:field_place_id OR u235_fields.field_place_id='1') AND
            u235_fields.site_id=:site_id
            ORDER BY
            field_pos ASC,
            field_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_place_id', $this->place_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->fields_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        $this->items_fields_q_select='';
        $fields_ar_count=count($this->fields_ar);
        for($i=0;$i<$fields_ar_count;$i++) {
            $field=$this->fields_ar[$i];
            $this->items_fields_q_select.="field_".$field->field_id.",";
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
            upload_to_yandex_market,
            yandex_description,
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
            if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(50);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    private function print_fields() {?>
        <div class="fields">
            <div role="tabpanel">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#uCat_field_common" aria-controls="home" role="tab" data-toggle="tab"><?=site_domain?></a></li>
                    <li role="presentation"><a href="#uCat_field_yandex_market" aria-controls="uCat_field_yandex_market" role="tab" data-toggle="tab">Яндекс Маркет</a></li>
                    <li role="presentation"><a href="#uCat_field_parts" aria-controls="uCat_field_parts" role="tab" data-toggle="tab">Запчасти</a></li>
                </ul>

                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="uCat_field_common">
                        <?
                        $fields_count=count($this->fields_ar);
                        for($first=true,$i=0;$i<$fields_count;$i++) {
                            $field=$this->fields_ar[$i];
                            if($field->field_place_id==$this->place_id) {
                                $field_units=uString::sql2text($field->field_units);
                                $item_field_id='field_'.$field->field_id;
                                $first=false;
                                ?>
                                <div class="row"><?
                                    if($field->field_style=='html text') {$label_cols=12;}
                                    else {$label_cols=4;}?>
                                    <div class="col-md-<?=$label_cols?> field_title">
                                        <button class="btn btn-default btn-sm uTooltip" title="Редактировать настройки характеристики" onclick="uCat.edit_field(<?=$field->field_id?>)"><span class="icon-pencil"></span></button>
                                        <?=uString::sql2text($field->field_title)?>
                                        <?if(!empty($field_units)&&
                                            (
                                                $field->field_style=='multiline'||
                                                $field->field_style=='link'||
                                                $field->field_style=='html text'
                                            )
                                        ) {?>
                                            , <?=uString::sql2text($field->field_units)?>
                                        <?}
                                        if(
                                                $field->field_style!='link'&&
                                                $field->field_style!='html text'&&
                                                $field->field_style!='datetime'&&
                                                $field->field_style!='multiline'
                                        ) {?>
                                        <button class="btn btn-default btn-sm uTooltip" title="Посмотреть значения харарактеристики" onclick="uCat.show_field_values(<?=$field->field_id?>)"><span class="icon-doc-text"></span></button>
                                        <?}?>
                                    </div>
                                    <div class="col-md-<?=($cols=12-$label_cols)?$cols:12?> field_val">

                                        <?$value=$this->item->$item_field_id;
                                        if(!empty($field_units)&&
                                        $field->field_style!='link'&&
                                        $field->field_style!='datetime'&&
                                        $field->field_style!='html text'&&
                                        $field->field_style!='multiline'
                                        ){?>
                                        <div class="input-group">
                                            <?}
                                            if($field->field_sql_type=='INT'&&$field->field_style=='text line') {?>
                                            <input id="uCat_field_value_editor_<?=$field->field_id?>" data-field_id="<?=$field->field_id?>" data-field_type="integer" class="form-control" type="text" value="<?=$value?>" onchange="uCat_item_admin.field_val_save(this)">
                                            <?}
                                            elseif($field->field_sql_type=='DOUBLE'&&$field->field_style=='text line') {?>
                                            <input id="uCat_field_value_editor_<?=$field->field_id?>" data-field_id="<?=$field->field_id?>" data-field_type="double" class="form-control" type="text" value="<?=$value?>" onblur="uCat_item_admin.field_val_save(this)">
                                            <?}
                                            elseif($field->field_sql_type=='TINYTEXT'&&$field->field_style=='text line') {?>
                                            <input id="uCat_field_value_editor_<?=$field->field_id?>" data-field_id="<?=$field->field_id?>" data-field_type="" class="form-control" type="text" value="<?=uString::sql2text($value,true)?>" onblur="uCat_item_admin.field_val_save(this)">
                                            <?}
                                            elseif($field->field_sql_type=='TEXT'&&$field->field_style=='html text') {
                                            $txt=uString::sql2text($value,true);?>
                                                <div id="uCat_item_field_editor_field_html_text_<?=$field->field_id?>"><?=$txt?></div>
                                                <!--suppress ES6ModulesDependencies -->
                                                <script type="text/javascript">
                                                    // tinymce.remove();//из-за этого отваливается второй редактор
                                                    $(".tinymce_editable").removeClass('tinymce_editable');
                                                    if (!parseInt($("#uCat_item_field_editor_field_html_text_<?=$field->field_id?>").prop("isContentEditable"))) {
                                                        tinymce.remove('#uCat_item_field_editor_field_html_text_<?=$field->field_id?>');
                                                        uTinymce_vars.init_tinymce("uCat_item_field_editor_field_html_text_<?=$field->field_id?>", 'uCat_item_admin.field_val_save("","<?=$field->field_id?>","html text",html)', 'uCat_item_admin.showFiles(field_name)');
                                                        $("#uCat_item_field_editor_field_html_text_<?=$field->field_id?>").addClass('tinymce_editable');
                                                    }
                                                </script>
                                            <?}
                                            elseif($field->field_sql_type=='TEXT'&&$field->field_style=='multiline') {?>
                                                <textarea data-field_id="<?=$field->field_id?>" data-field_type="" class="form-control" rows="4" onchange="uCat_item_admin.field_val_save(this)"><?=uString::sql2text($value,true)?></textarea>
                                            <?}
                                            elseif($field->field_sql_type=='INT'&&$field->field_style=='date') {?>
                                            <input id="uCat_field_value_editor_<?=$field->field_id?>" data-field_id="<?=$field->field_id?>" data-field_type="date" class="uCate_field_editor_datepicker form-control" type="text" value="<?=!empty($value)?date('d.m.Y',$value):''?>" style="position: relative; z-index: 10000" onchange="uCat_item_admin.field_val_save(this)">
                                            <?}
                                            elseif($field->field_sql_type=='INT'&&$field->field_style=='datetime') {?>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <input data-field_id="<?=$field->field_id?>" data-field_type="datetime" id="uCat_item_field_editor_field_date_<?=$field->field_id?>" class="uCate_field_editor_datepicker form-control" type="text" value="<?=!empty($value)?date('d.m.Y',$value):''?>" style="position: relative; z-index: 10000" placeholder="Дата" onchange="uCat_item_admin.field_val_save(this)">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <?if(!empty($field_units)){?>
                                                        <div class="input-group">
                                                            <?}?>
                                                            <input data-field_id="<?=$field->field_id?>" data-field_type="datetime" id="uCat_item_field_editor_field_time_<?=$field->field_id?>" class="uCat_item_field_datetime_time form-control" type="text" value="<?=!empty($value)?date('H:i',$value):''?>" placeholder="Время" onchange="uCat_item_admin.field_val_save(this)">
                                                            <?if(!empty($field_units)){?>
                                                            <span class="input-group-addon" id="sizing-addon3"><?=uString::sql2text($field->field_units)?></span>
                                                        </div>
                                                    <?}?>
                                                    </div>
                                                </div>
                                            <?}
                                            elseif($field->field_sql_type=='TEXT'&&$field->field_style=='link') {
                                            $href=$val='';
                                            $target='_self';
                                            if(!empty($value)) {
                                                $html=str_get_html(uString::sql2text($value,true));
                                                foreach($html->find('a') as $element) {$val=$element->innertext; $href=$element->href; $target=$element->target;}
                                            }
                                            ?>
                                                <div class="input-group">
                                                    <input data-field_id="<?=$field->field_id?>" data-field_type="link" id="uCat_item_field_editor_field_link_href_<?=$field->field_id?>" class="form-control" type="text" value="<?=$href?>" placeholder="url" onchange="uCat_item_admin.field_val_save(this)">
                                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="uCat_item_admin.showFiles4field('<?=$field->field_id?>')"><span class="icon-upload-cloud"></span></button>
                                    </span>
                                                </div><!-- /input-group -->
                                            <input data-field_id="<?=$field->field_id?>" data-field_type="link" id="uCat_item_field_editor_field_link_label_<?=$field->field_id?>" class="form-control" type="text" value="<?=$val?>" placeholder="Отображаемый текст" onchange="uCat_item_admin.field_val_save(this)">
                                                <select data-field_id="<?=$field->field_id?>" data-field_type="link" id="uCat_item_field_editor_field_link_target_<?=$field->field_id?>" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                                    <option value="_self" <?=($target=='_self')?'selected':''?>>В текущем окне</option>
                                                    <option value="_blank" <?=($target=='_blank')?'selected':''?>>В новом окне</option>
                                                </select>
                                                <? unset($html,$href,$val);
                                            }

                                            if(!empty($field_units)&&
                                            $field->field_style!='link'&&
                                            $field->field_style!='datetime'&&
                                            $field->field_style!='html text'&&
                                            $field->field_style!='multiline'
                                            ){?>
                                            <span class="input-group-addon" id="sizing-addon3"><?=uString::sql2text($field->field_units)?></span>
                                        </div>
                                    <?}?>
                                    </div>
                                </div>
                                <?//}
                            }
                        }
                        if($first) {?><p>В это место пока не добавлено характеристик.</p><?}?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="uCat_field_yandex_market">
                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div>Доставка</div>
                                    <?$this->item->delivery_on=(int)$this->item->delivery_on;?>
                                    <div class="field_val">
                                        <select data-field_id="delivery_on" data-field_type="integer" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                            <option value="0" <?=$this->item->delivery_on===0?"selected":""?>>По умолчанию</option>
                                            <option value="1" <?=$this->item->delivery_on===1?"selected":""?>>Есть</option>
                                            <option value="2" <?=$this->item->delivery_on===2?"selected":""?>>Нет</option>
                                        </select>
                                    </div>
                                    <div class="help-block"><small>Возможность курьерской доставки по региону магазина.<br>Внимание! Обязательно выберите значение "Нет", если товар запрещено продавать дистанционно (ювелирные изделия, лекарственные средства).</small></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <?php
                                    $currency='р';
                                    if(site_id==54) {
                                        $currency='Eur';
                                    }
                                    ?>
                                    <div class="field_title">Стоимость доставки</div>
                                    <?$this->item->delivery_cost=(float)$this->item->delivery_cost;?>
                                    <div class="field_val">
                                        <div class="input-group">
                                            <input data-field_id="delivery_cost" data-field_type="double" class="form-control" type="text" value="<?=$this->item->delivery_cost?>" onchange="uCat_item_admin.field_val_save(this)">
                                            <span class="input-group-addon" id="sizing-addon3"><?=$currency?></span>
                                        </div>
                                    </div>
                                    <div class="help-block"><small>Стоимость курьерское доставки по региону магазина<br>Нельзя писать валюту, например "руб." Пример: 300<br>0 - система будет использовать значение по умолчанию из настроек сайта.</small></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Срок доставки</div>
                                    <div class="field_val">
                                        <div class="input-group">
                                            <input data-field_id="delivery_time" data-field_type="" class="form-control" type="text" value="<?=$this->item->delivery_time?>" onchange="uCat_item_admin.field_val_save(this)">
                                            <span class="input-group-addon" id="sizing-addon3">Дней</span>
                                        </div>
                                    </div>
                                    <div class="help-block"><small>Срок курьерской доставки товара по региону магазина.<br>Укажите количество дней. Пример: 2, 2-4<br>0 - система будет использовать значение по умолчанию из настроек сайта.</small></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Самовывоз</div>
                                    <?$this->item->pickup_on=(int)$this->item->pickup_on;?>
                                    <div class="field_val">
                                        <select data-field_id="pickup_on" data-field_type="integer" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                            <option value="0" <?=$this->item->pickup_on===0?"selected":""?>>По умолчанию</option>
                                            <option value="1" <?=$this->item->pickup_on===1?"selected":""?>>Есть</option>
                                            <option value="2" <?=$this->item->pickup_on===2?"selected":""?>>Нет</option>
                                        </select>
                                    </div>
                                    <div class="help-block"><small>Возможность самовывоза из пунктов выдачи.</small></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Купить в магазине без заказа</div>
                                    <?$this->item->buy_without_order_on=(int)$this->item->buy_without_order_on;?>
                                    <div class="field_val">
                                        <select data-field_id="buy_without_order_on" data-field_type="integer" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                            <option value="0" <?=$this->item->buy_without_order_on===0?"selected":""?>>По умолчанию</option>
                                            <option value="1" <?=$this->item->buy_without_order_on===1?"selected":""?>>Можно</option>
                                            <option value="2" <?=$this->item->buy_without_order_on===2?"selected":""?>>Нельзя</option>
                                        </select>
                                    </div>
                                    <div class="help-block"><small>Возможность купить товар сразу на месте, без оформления предварительного заказа</small></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Производитель</div>
                                    <div class="field_val">
                                        <input data-field_id="manufacturer" data-field_type="" class="form-control" type="text" value="<?=addslashes($this->item->manufacturer)?>" onchange="uCat_item_admin.field_val_save(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Гарантия производителя</div>
                                    <?$this->item->manufacturer_warranty=(int)$this->item->manufacturer_warranty;?>
                                    <div class="field_val">
                                        <select data-field_id="manufacturer_warranty" data-field_type="integer" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                            <option value="1" <?=$this->item->manufacturer_warranty===1?"selected":""?>>Есть</option>
                                            <option value="0" <?=$this->item->manufacturer_warranty===0?"selected":""?>>Нет</option>
                                        </select>
                                    </div>
                                    <div class="help-block"><small>Укажите, есть ли официальная гарантия производителя</small></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Страна происхождения</div>
                                    <div class="field_val">
                                        <input data-field_id="manufactured_in" data-field_type="" class="form-control" type="text" value="<?=addslashes($this->item->manufactured_in)?>" onchange="uCat_item_admin.field_val_save(this)">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Описание для Яндекс Маркета</div>
                                    <div class="field_val">
                                        <textarea data-field_id="yandex_description" data-field_type="" class="form-control" onchange="uCat_item_admin.field_val_save(this)"><?=$this->item->yandex_description?></textarea>
                                    </div>
                                    <div class="help-block"><small>Можно оставить пустым.<br>Тогда на Яндекс Маркете будет использоваться описание со страницы товара</small></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Выгружать товар на Яндекс Маркет</div>
                                    <?$this->item->upload_to_yandex_market=(int)$this->item->upload_to_yandex_market;?>
                                    <div class="field_val">
                                        <select data-field_id="upload_to_yandex_market" data-field_type="integer" class="form-control" onchange="uCat_item_admin.field_val_save(this)">
                                            <option value="1" <?=$this->item->upload_to_yandex_market===1?"selected":""?>>Да</option>
                                            <option value="0" <?=$this->item->upload_to_yandex_market===0?"selected":""?>>Нет</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div role="tabpanel" class="tab-pane" id="uCat_field_parts">
                        <div class="row">
                            <div class="col-md-6">
                                <div>
                                    <div>Заводской номер детали</div>
                                    <div class="field_val">
                                        <input data-field_id="manufacturer_part_number" data-field_type="" class="form-control" type="text" value="<?=$this->item->manufacturer_part_number?>" onchange="uCat_item_admin.field_val_save(this)">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div>
                                    <div class="field_title">Номер детали для поиска</div>
                                    <div class="field_val">
                                        <input data-field_id="search_part_number" data-field_type="" class="form-control" type="text" value="<?=$this->item->search_part_number?>" onchange="uCat_item_admin.field_val_save(this)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    <?}
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->get_item_fields();
        $this->get_item_data();
        $this->print_fields();
    }
}
new admin_item_get_fields_inline_editor_ajax($this);
