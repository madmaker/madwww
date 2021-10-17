<?php
namespace uCat\item;
use uCat\common;

require_once 'uCat/classes/common.php';
class new_variant_type_dg {
    private $uCore;
    public $uCat_common,$q_item_types;
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->uCat_common=new common($this->uCore);
        $this->q_item_types=$this->uCat_common->get_item_types();
    }
}
$uCat=new new_variant_type_dg ($this);?>

<div class="form-group" id="uCat_item_new_variant_type_dg_item_variant_title_parent">
    <label for="uCat_item_new_variant_type_dg_item_variant_title" class="control-label">Название нового варианта</label>
    <input type="text" class="form-control" id="uCat_item_new_variant_type_dg_item_variant_title" onclick="jQuery('#uCat_item_new_variant_type_dg_item_variant_title_parent').removeClass('has-error')" onkeydown="jQuery('#uCat_item_new_variant_type_dg_item_variant_title_parent').removeClass('has-error')">
</div>
<div class="form-group">
    <label class="control-label">Выберите тип товара</label>
    <div class="input-group">
    <select id="uCat_item_new_variant_type_dg_item_variant_type" class="form-control"><?
        while($item_type=$uCat->q_item_types->fetch_object()) {?>
            <option id="uCat_item_new_variant_type_dg_item_variant_type_option_<?=$item_type->type_id?>" value="<?=$item_type->type_id?>"><?=\uString::sql2text($item_type->type_title)?></option>
        <?}
        ?>
    </select>
        <div class="input-group-btn">
            <button onclick="uCat_item_admin.edit_items_types()" title="Добавить/Изменить типы товаров" class="btn btn-default uTooltip"><span class="icon-pencil"></span></button>
        </div>
    </div>
</div>