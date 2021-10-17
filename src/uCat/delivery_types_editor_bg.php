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

class delivery_types_editor_bg{
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore;


    private function print_delivery_types($site_id=site_id) {
        if(!$stm_delivery_types=$this->uCat->get_delivery_types("del_type_id, 
            del_type_name, 
            del_type_descr, 
            del_type, 
            is_default,
            del_show,
            pos",$site_id)) $this->uFunc->error(0,1);?>

        <h2>Способы вручения заказа
            <small class="pull-right">
                <button class="btn btn-primary" onclick="uCat_cart_admin.create_delivery('delivery_type')"><span class="icon-plus"></span> Добавить еще</button>
            </small>
        </h2>

        <?$found=0;
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=1;$delivery_type=$stm_delivery_types->fetch(PDO::FETCH_OBJ);$i++) {
            $found=1;
            $delivery_type->is_default=(int)$delivery_type->is_default;
            $delivery_type->del_type=(int)$delivery_type->del_type;
            $delivery_type->del_show=(int)$delivery_type->del_show;?>
            <div class="bs-callout container-fluid">
                <div class="row">
                    <h2>Способ #<?=$i?>
                        <small class="pull-right">
                            <button
                                    class="btn btn-danger <?=$delivery_type->is_default?'disabled':''?> uCat_delivery_type_editor_delete_btn"
                                    id="uCat_delivery_type_editor_delete_btn_<?=$delivery_type->del_type_id?>"
                                    data-del_type_id="<?=$delivery_type->del_type_id?>"
                                    onclick="uCat_cart_admin.delete_handler_confirm(this)"
                            ><span class="icon-cancel"></span> Удалить</button>
                        </small>
                    </h2>

                    <div class="form-group col-md-8">
                        <label>Название способа вручения</label>
                        <input data-del_type_id="<?=$delivery_type->del_type_id?>" data-field_name="del_type_name" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($delivery_type->del_type_name)?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Позиция</label>
                        <input data-del_type_id="<?=$delivery_type->del_type_id?>" data-field_name="pos" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($delivery_type->pos)?>">
                    </div>
                    <div class="form-group col-md-12">
                        <label>Описание способа вручения</label>
                        <div id="uCat_delivery_type_editor_descr_<?=$delivery_type->del_type_id?>"><?=$delivery_type->del_type_descr?></div>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Использовать по умолчанию</label>
                        <select data-del_type_id="<?=$delivery_type->del_type_id?>" data-field_name="is_default" <?=$delivery_type->is_default||!$delivery_type->del_show?'disabled':''?> id="uCat_delivery_type_editor_is_default_<?=$delivery_type->del_type_id?>" class="form-control uCat_delivery_type_editor_is_default" onchange="uCat_cart_admin.update_field_value(this)">
                            <option value="0" <?=$delivery_type->is_default?'selected':''?>>Нет</option>
                            <option value="1" <?=$delivery_type->is_default?'selected':''?>>Да</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Способ вручения</label>
                        <select data-del_type_id="<?=$delivery_type->del_type_id?>" data-field_name="del_type" class="form-control" onchange="uCat_cart_admin.update_field_value(this)">
                            <option value="0" <?=$delivery_type->del_type?'selected':''?>>Самовывоз</option>
                            <option value="1" <?=$delivery_type->del_type?'selected':''?>>Доставка</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4">
                        <label>Отображать на сайте</label>
                        <select data-del_type_id="<?=$delivery_type->del_type_id?>" data-field_name="del_show" <?=$delivery_type->is_default?'disabled':''?> id="uCat_delivery_type_editor_del_show_<?=$delivery_type->del_type_id?>" class="form-control uCat_delivery_type_editor_del_show" onchange="uCat_cart_admin.update_field_value(this)">
                            <option value="0" <?=$delivery_type->del_show?'selected':''?>>Скрывать</option>
                            <option value="1" <?=$delivery_type->del_show?'selected':''?>>Отображать</option>
                        </select>
                    </div>
                </div>

<!--                <div>-->
                    <h3>Пункты самовывоза/Районы доставки
                        <small class="pull-right">
                            <button class="btn btn-primary btn-sm" onclick="uCat_cart_admin.create_delivery('point',<?=$delivery_type->del_type_id?>)"><span class="icon-plus"></span> Добавить еще</button>
                        </small>
                    </h3>
                    <?$stm_delivery_points=$this->uCat->get_delivery_points($delivery_type->del_type_id,"point_id,
                    point_name,
                    point_descr,
                    is_default,
                    point_show,
                    pos");
                    /** @noinspection PhpUndefinedMethodInspection */
                    for($j=1;$point=$stm_delivery_points->fetch(PDO::FETCH_OBJ);$j++) {
                        $point->is_default=(int)$point->is_default;
                        $point->point_show=(int)$point->point_show;?>
                        <div class="bs-callout bs-callout-default container-fluid">
                            <div class="row">
                                <h3>Пункт/район #<?=$j?>
                                    <small class="pull-right">
                                        <button
                                                data-del_type_id="<?=$delivery_type->del_type_id?>"
                                                class="btn btn-danger <?=$point->is_default?'disabled':''?> uCat_delivery_point_editor_delete_btn_<?=$delivery_type->del_type_id?>"
                                                id="uCat_delivery_point_editor_delete_btn_<?=$delivery_type->del_type_id?>_<?=$point->point_id?>"
                                                data-point_id="<?=$point->point_id?>"
                                                onclick="uCat_cart_admin.delete_handler_confirm(this)"
                                        ><span class="icon-cancel"></span> Удалить</button>
                                    </small>
                                </h3>
                                <div class="form-group col-md-8">
                                    <label>Название пункта/района</label>
                                    <input data-point_id="<?=$point->point_id?>" data-field_name="point_name" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($point->point_name)?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Позиция</label>
                                    <input data-point_id="<?=$point->point_id?>" data-field_name="pos" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($point->pos)?>">
                                </div>
                                <div class="form-group col-md-12">
                                    <label>Описание пункта/района</label>
                                    <div id="uCat_delivery_point_editor_descr_<?=$point->point_id?>"><?=$point->point_descr?></div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Использовать по умолчанию</label>
                                    <select data-del_type_id="<?=$delivery_type->del_type_id?>" data-point_id="<?=$point->point_id?>" data-field_name="is_default" <?=$point->is_default||!$point->point_show?'disabled':''?> id="uCat_delivery_point_editor_is_default_<?=$delivery_type->del_type_id?>_<?=$point->point_id?>" class="form-control uCat_delivery_point_editor_is_default_<?=$delivery_type->del_type_id?>" onchange="uCat_cart_admin.update_field_value(this)">
                                        <option value="0" <?=$point->is_default?'selected':''?>>Нет</option>
                                        <option value="1" <?=$point->is_default?'selected':''?>>Да</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Отображать на сайте</label>
                                    <select data-del_type_id="<?=$delivery_type->del_type_id?>" data-point_id="<?=$point->point_id?>" data-field_name="point_show" <?=$point->is_default?'disabled':''?> id="uCat_delivery_point_editor_point_show_<?=$delivery_type->del_type_id?>_<?=$point->point_id?>" class="form-control uCat_delivery_point_editor_point_show_<?=$delivery_type->del_type_id?>" onchange="uCat_cart_admin.update_field_value(this)">
                                        <option value="0" <?=$point->point_show?'selected':''?>>Скрывать</option>
                                        <option value="1" <?=$point->point_show?'selected':''?>>Отображать</option>
                                    </select>
                                </div>
                            </div>

                            <script type="text/javascript">
                                tinymce.remove("#uCat_delivery_point_editor_descr_<?=$point->point_id?>");
                                uCat_cart_admin.delivery_point_descr_tinymce_init(<?=$point->point_id?>);
                            </script>

                            <h4>Варианты получения в пункте/районе <small class="pull-right"><button class="btn btn-primary btn-sm" onclick="uCat_cart_admin.create_delivery('var',<?=$point->point_id?>)"><span class="icon-plus"></span> Добавить еще</button></small></h4>
                            <?$stm_delivery_point_variants=$this->uCat->get_delivery_point_variants($point->point_id,"var_id,
                            var_name,
                            var_descr,
                            delivery_price,
                            avail_at_price_since,
                            avail_at_price_till,
                            set_at_price_since,
                            manager_must_confirm,
                            manager_sets_delivery_price,
                            var_show,
                            pos");
                            /** @noinspection PhpUndefinedMethodInspection */
                            for($k=1;$var=$stm_delivery_point_variants->fetch(PDO::FETCH_OBJ);$k++) {
                            $var->var_show=(int)$var->var_show;?>
                                <div class="highlight container-fluid">
                                    <div class="row">
                                        <h4>Вариант #<?=$k?>
                                            <small class="pull-right">
                                                <button
                                                        data-point_id="<?=$point->point_id?>"
                                                        class="btn btn-danger uCat_delivery_point_variant_editor_delete_btn_<?=$point->point_id?>"
                                                        id="uCat_delivery_point_variant_editor_delete_btn_<?=$point->point_id?>_<?=$var->var_id?>"
                                                        data-var_id="<?=$var->var_id?>"
                                                        onclick="uCat_cart_admin.delete_handler_confirm(this)"
                                                ><span class="icon-cancel"></span> Удалить</button>
                                            </small>
                                        </h4>
                                        <div class="form-group col-md-8">
                                            <label>Название варианта</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="var_name" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->var_name)?>">
                                        </div>
                                        <div class="form-group col-md-4">
                                            <label>Позиция</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="pos" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->pos)?>">
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Описание варианта</label>
                                            <div id="uCat_delivery_point_variant_editor_descr_<?=$var->var_id?>"><?=$var->var_descr?></div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label>Отображать на сайте</label>
                                            <select data-point_id="<?=$point->point_id?>" data-var_id="<?=$var->var_id?>" data-field_name="var_show" class="form-control uCat_delivery_point_variant_editor_var_show_<?=$point->point_id?>" id="uCat_delivery_point_variant_editor_var_show_<?=$point->point_id?>_<?=$var->var_id?>" onchange="uCat_cart_admin.update_field_value(this)">
                                                <option value="0" <?=$var->var_show?'selected':''?>>Скрывать</option>
                                                <option value="1" <?=$var->var_show?'selected':''?>>Отображать</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Доступен при сумме заказа от</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="avail_at_price_since" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->avail_at_price_since)?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Доступен при сумме заказа до</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="avail_at_price_till" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->avail_at_price_till)?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Стоимость доставки</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="delivery_price" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->delivery_price)?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Использовать по умолчанию при сумме заказа от</label>
                                            <input data-var_id="<?=$var->var_id?>" data-field_name="set_at_price_since" onblur="uCat_cart_admin.update_field_value(this)" type="text" class="form-control" value="<?=htmlspecialchars($var->set_at_price_since)?>">
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Менеджер вручную устанавливат цену доставки</label>
                                            <select data-var_id="<?=$var->var_id?>" data-field_name="manager_sets_delivery_price" class="form-control" onchange="uCat_cart_admin.update_field_value(this)">
                                                <option value="0" <?=$var->manager_sets_delivery_price?'selected':''?>>Нет</option>
                                                <option value="1" <?=$var->manager_sets_delivery_price?'selected':''?>>Да</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-6">
                                            <label>Менеджер подтверждает заказ вручную</label>
                                            <select data-var_id="<?=$var->var_id?>" data-field_name="manager_must_confirm" class="form-control" onchange="uCat_cart_admin.update_field_value(this)">
                                                <option value="0" <?=$var->manager_must_confirm?'selected':''?>>Нет</option>
                                                <option value="1" <?=$var->manager_must_confirm?'selected':''?>>Да</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <script type="text/javascript">
                                    tinymce.remove("#uCat_delivery_point_variant_editor_descr_<?=$var->var_id?>");
                                    uCat_cart_admin.delivery_point_variant_descr_tinymce_init(<?=$var->var_id?>);
                                </script>
                            <?}?>
                        </div>
                    <?}?>
<!--                </div>-->

            </div>
            <script type="text/javascript">
                tinymce.remove("#uCat_delivery_type_editor_descr_<?=$delivery_type->del_type_id?>");
                uCat_cart_admin.delivery_type_descr_tinymce_init(<?=$delivery_type->del_type_id?>);
            </script>
        <?}
        if(!$found) {?>
           <div>Пока не создано ни одного способа вручения товара. Создайте первый</div>
        <?}?>

        <div class="bs-callout bs-callout-primary">Информация сохраняется автоматически после изменения</div>

    <?}

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("forbidden");
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->print_delivery_types();
    }
}
new delivery_types_editor_bg($this);