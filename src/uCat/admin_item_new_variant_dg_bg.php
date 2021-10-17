<?php
namespace uCat\item;
use uCat\common;

require_once 'uCat/classes/common.php';
class new_variant_dg {
    private $uCore;
    public $uCat_common,$q_variants_types,$item_id;

    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uCore->error(10);
        $this->item_id=$_POST['item_id'];
        if(!\uString::isDigits($this->item_id)) $this->uCore->error(20);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");
        $this->check_data();

        $this->uCat_common=new common($this->uCore);
        $this->q_variants_types=$this->uCat_common->get_variants_types();
    }
}
$uCat=new new_variant_dg($this);?>

<div class="form-group">
    <label for="uCat_item_new_variant_dg_item_variant_type">Выберите готовый вариант</label>
        <div class="input-group">
            <select id="uCat_item_new_variant_dg_item_variant_type" class="form-control selectpicker"><?
                while($var_type=$uCat->q_variants_types->fetch_object()) {?>
                    <option value="<?=$var_type->var_type_id?>" <?=$uCat->uCat_common->has_variant($uCat->item_id,$var_type->var_type_id)?'class="bg-success" style="font-weight: bold"':''?>><?=\uString::sql2text($var_type->var_type_title)?></option>
                <?}
                ?></select>
            <span class="input-group-btn">
                <button class="btn btn-default uTooltip" title="Изменить этот вариант" type="button" onclick="uCat_item_admin.variant_edit_open_dg(jQuery('#uCat_item_new_variant_dg_item_variant_type').val())"><span class="icon-pencil"></span></button>
            </span>
            <span class="input-group-btn">
                <button class="btn btn-default uTooltip" title="Создать новый вариант" type="button" onclick="uCat_item_admin.new_variant_type_dg()"><span class="icon-plus"></span></button>
            </span>
        </div><!-- /input-group -->
    <span class="help-block"><b>Выделены</b> варианты, которые уже добавлены в этом товаре</span>
    <span class="help-block">Неиспользуемые варианты удаляются автоматически</span>
</div>