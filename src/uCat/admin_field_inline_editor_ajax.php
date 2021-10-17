<?
namespace uCat\admin;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

use PDO;
use PDOException;
use processors\uFunc;
use uString;

class admin_field_inline_editor_ajax{
    public $fields_types_ar;
    public $fields_places_ar;
    public $fields_label_styles_ar;
    public $fields_effects_ar;
    public $field_filters_ar;
    private $uFunc;
    private $uSes;
    private $uCore;
    public $field_id,$hash, $field;

    private function check_data() {
        if(!isset($_POST['field_id'])) $this->uFunc->error(10);
        $this->field_id=$_POST['field_id'];
        if(!uString::isDigits($this->field_id)) $this->uFunc->error(20);
    }

    private function getField(){
        //Fields list
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_fields.field_id,
            field_title,
            field_comment,
            field_units,
            field_type_id,
            field_effect_id,
            field_pos,
            field_place_id,
            u235_fields.filter_type_id,
            filter_type_val,
            search_use,
            label_style_id,
            tablelist_show,
            planelist_show,
            tileslist_show,
            tileslist_show_on_card,
            sort_show,
            merge
            FROM
            u235_fields
            JOIN 
            u235_cats_fields
            ON
            u235_cats_fields.field_id=u235_fields.field_id AND
            u235_cats_fields.site_id=u235_fields.site_id
            JOIN
            u235_fields_filter_types
            ON
            u235_fields.filter_type_id=u235_fields_filter_types.filter_type_id
            WHERE
            u235_fields.field_id=:field_id AND
            u235_fields.field_type_id!='0' AND
            u235_fields.site_id=:site_id
            ORDER BY field_id ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->field=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    private function get_fields_types() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_type_id,
            field_type_title,
            field_sql_type
            FROM
            u235_fields_types
            WHERE
            field_type_id!='0'
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->fields_types_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    private function get_fields_places() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            place_id,
            place_title
            FROM
            u235_fields_places
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->fields_places_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }
    private function get_fields_label_styles() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            label_style_id,
            label_style_title
            FROM
            u235_fields_label_styles
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->fields_label_styles_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    private function get_fields_effects() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            effect_id,
            effect_title
            FROM
            u235_fields_effects
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->fields_effects_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }
    private function get_fields_filter_types() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            filter_type_id,
            filter_type_sql,
            filter_type_val,
            filter_type_title
            FROM
            u235_fields_filter_types
            JOIN 
            u235_fields_types
            ON
            field_sql_type=filter_type_sql
            WHERE
            filter_type_id!='0' AND
            field_type_id=:field_type_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_type_id', $this->field->field_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->field_filters_ar=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->getField();
        $this->get_fields_types();
        $this->get_fields_filter_types();
        $this->get_fields_places();
        $this->get_fields_label_styles();
        $this->get_fields_effects();

        $this->hash=$this->uCore->uFunc->sesHack();
    }
}
$uCat=new admin_field_inline_editor_ajax($this);

ob_start();

?>
<div role="tabpanel">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active"><a href="#uCat_field_inline_editor_main" aria-controls="home" role="tab" data-toggle="tab">Основные</a></li>
        <li role="presentation"><a href="#uCat_field_inline_editor_view" aria-controls="uCat_field_inline_editor_view" role="tab" data-toggle="tab">Расширенные</a></li>
    </ul>
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="uCat_field_inline_editor_main">
            <div class="row">
                <div class="col-md-6">

                    <div class="form-group">
                        <label for="uCat_item_field_editor_field_title" class="control-label">Название</label>
                        <input id="uCat_item_field_editor_field_title" class="form-control" type="text" value="<?=addslashes(uString::sql2text($uCat->field->field_title,true))?>">
                    </div>

                    <div class="form-group">
                        <label for="uCat_item_field_editor_field_units" class="control-label">Единицы измерения</label>
                        <input id="uCat_item_field_editor_field_units" class="form-control" type="text" value="<?=addslashes(uString::sql2text($uCat->field->field_units))?>">
                        <span class="help-block">Отображается в каталоге и фильтре</span>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_search_use" type="checkbox" <?=$uCat->field->search_use=='1'?'checked':''?>> <span >Использовать при поиске?</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_tablelist_show" type="checkbox" <?=$uCat->field->tablelist_show=='1'?'checked':''?>> <span >Показывать в табличном отображении?</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_planelist_show" type="checkbox" <?=$uCat->field->planelist_show=='1'?'checked':''?>> <span >Показывать в отображении списком и корзине?</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_tileslist_show" type="checkbox" <?=$uCat->field->tileslist_show=='1'?'checked':''?>> <span >Показывать в плиточном отображении при наведении?</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_tileslist_show_on_card" type="checkbox" <?=$uCat->field->tileslist_show_on_card=='1'?'checked':''?>> <span >Показывать в плиточном отображении сразу в карточке?</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label>
                                <input id="uCat_item_field_editor_sort_show" type="checkbox" <?=$uCat->field->sort_show=='1'?'checked':''?>> <span >Использовать для сортировки?</span>
                            </label>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">

                    <div class="form-group">
                        <label for="uCat_item_field_editor_field_type_id" class="control-label">Тип</label>
                        <div class="input-group">
                        <select id="uCat_item_field_editor_field_type_id" class="form-control" onchange="uCat.field_change_type()">
                            <?
                            $fields_types_ar_count=count($uCat->fields_types_ar);
                            for($i=0;$i<$fields_types_ar_count;$i++) {
                                $type=$uCat->fields_types_ar[$i];?>
                                <option value="<?=$type->field_type_id?>" <?=$uCat->field->field_type_id==$type->field_type_id?'selected':''?>><?=uString::sql2text($type->field_type_title)?></option>
                            <?}?>
                        </select>
                            <span class="input-group-btn">
                                <button class="btn btn-default uTooltip" title="Посмотреть разъяснение по типам характеристик" type="button" onclick="jQuery('#uCat_edit_field_types_explanation_dg').modal('show').css('z-index',9999)"><span class="glyphicon glyphicon-question-sign"></span></button>
                            </span>
                        </div>
                        <span class="help-block">Как хранить в базе данных, как отображать в товаре, как будет работать в фильтре. Влияет на скорость работы сайта!</span>
                        <span class="help-block text-danger">Если поменять тип (например с текста на число), то уже записанные данные в этой характеристике могут повредиться!<br>Меняйте тип на свой страх и риск и только на такой же: числа на числа, текст на текст</span>
                    </div>

                    <div class="form-group">
                        <label for="uCat_item_field_editor_filter_type_id" class="control-label">Как отображать в фильтре?</label>
                        <div class="input-group">
                            <select id="uCat_item_field_editor_filter_type_id" class="form-control">
                                <?
                                $field_filters_ar_count=count($uCat->field_filters_ar);
                                for($i=0;$i<$field_filters_ar_count;$i++) {
                                    $filter=$uCat->field_filters_ar[$i];?>
                                    <option value="<?=$filter->filter_type_id?>" <?=$uCat->field->filter_type_val==$filter->filter_type_val?'selected':''?>><?=uString::sql2text($filter->filter_type_title,true)?></option>
                                <?}?>
                            </select>
                            <span class="input-group-btn">
                                <button class="btn btn-default uTooltip" title="Посмотреть примеры отображения фильтров" type="button" onclick="jQuery('#uCat_edit_field_filter_examples_dg').modal('show').css('z-index',9999)"><span class="glyphicon glyphicon-question-sign"></span></button>
                            </span>
                        </div>
                        <span class="help-block">Как отображать характеристику в фильтре?</span>
                    </div>

                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="uCat_field_inline_editor_view">

            <div class="row">
                <div class="col-md-6">

                    <div class="form-group">
                        <label for="uCat_item_field_editor_field_pos" class="control-label">Позиция относительно других характеристик</label>
                        <input id="uCat_item_field_editor_field_pos" type="text" class="form-control" value="<?=addslashes(uString::sql2text($uCat->field->field_pos,true))?>">
                        <span class="help-block">Если число больше, чем у других, то будет отображаться ниже, если меньше - выше</span>
                    </div>

                    <div class="form-group">
                    <label for="uCat_item_field_editor_field_place_id" class="control-label">Где отображать на странице товара?</label>
                    <select id="uCat_item_field_editor_field_place_id" class="form-control"><?
                        $fields_places_ar_count=count($uCat->fields_places_ar);
                        for($i=0;$i<$fields_places_ar_count;$i++){
                            $place=$uCat->fields_places_ar[$i];?>
                            <option value="<?=$place->place_id?>" <?=$uCat->field->field_place_id==$place->place_id?'selected':''?>><?=uString::sql2text($place->place_title)?></option>
                        <?}?></select>
                </div>

                <div class="form-group">
                    <label for="uCat_item_field_editor_label_style_id" class="control-label">Где отображать название характеристики?</label>
                    <select id="uCat_item_field_editor_label_style_id" class="form-control">
                        <?
                        $fields_label_styles_ar_count=count($uCat->fields_label_styles_ar);
                        for($i=0;$i<$fields_label_styles_ar_count;$i++) {
                            $label_place=$uCat->fields_label_styles_ar[$i];?>
                            <option value="<?=$label_place->label_style_id?>" <?=$uCat->field->label_style_id==$label_place->label_style_id?'selected':''?>><?=uString::sql2text($label_place->label_style_title,true)?></option>
                        <?}?>
                    </select>
                    <span class="help-block">Слева от значения, над текстом и т.п.</span>
                </div>


                </div>

                    <div class="col-md-6">

                        <div class="form-group">
                            <label for="uCat_item_field_editor_field_comment" class="control-label">Комментарий</label>
                            <textarea id="uCat_item_field_editor_field_comment" class="form-control"><?=uString::sql2text($uCat->field->field_comment,1)?></textarea>
                            <span class="help-block">Комментарий для себя. Нигде не отображается</span>
                        </div>


                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input id="uCat_item_field_editor_merge" type="checkbox" <?=$uCat->field->merge=='1'?'checked':''?>> <span >Склеивать?</span>
                                </label>
                            </div>
                            <span class="help-block">Все характеристики с одинаковыми заголовками и позициями будут отображаться под одним общим заголовком (без заголовка для каждой)</span>
                        </div>

                        <div class="form-group">
                            <label for="uCat_item_field_editor_field_effect_id" class="control-label">Эффект</label>
                            <div class="input-group">
                            <select id="uCat_item_field_editor_field_effect_id" class="form-control">
                                <?
                                $fields_effects_ar_count=count($uCat->fields_effects_ar);
                                for($i=0;$i<$fields_effects_ar_count;$i++) {
                                    $effect=$uCat->fields_effects_ar[$i];?>
                                    <option value="<?=$effect->effect_id?>" <?=$uCat->field->field_effect_id==$effect->effect_id?'selected':''?>><?=uString::sql2text($effect->effect_title,true)?></option>
                                <?}?>
                            </select>
                                <span class="input-group-btn">
                                    <button class="btn btn-default uTooltip" title="Посмотреть разъяснение по эффектам характеристик" type="button" onclick="jQuery('#uCat_edit_field_effects_explanation_dg').modal('show').css('z-index',9999)"><span class="glyphicon glyphicon-question-sign"></span></button>
                                </span>
                            </div>
                            <span class="help-block">Какой эффект применять к характеристике при отображении на странице товара?</span>
                        </div>

                    </div>

            </div>

        </div>
    </div>

</div>