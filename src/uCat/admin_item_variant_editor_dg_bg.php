<?php
namespace uCat\item;

use uCat\common;

class variant_editor_dg {
    private $uCore;
    public $var_type_id,$uCat_common,$var;
    private function check_data() {
        if(!isset($_POST['var_type_id'])) $this->uCore->error(10);
        $this->var_type_id=$_POST['var_type_id'];
        if(!\uString::isDigits($this->var_type_id)) $this->uCore->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("forbidden");

        $this->check_data();

        require_once 'uCat/classes/common.php';
        $this->uCat_common=new common($this->uCore);
    }
}
$uCat=new variant_editor_dg($this);?>

<div class="form-group">
    <label for="admin_item_variant_editor_dg_var_title">Название варианта</label>
    <input id="admin_item_variant_editor_dg_var_title" class="form-control" type="text" value="<?=htmlspecialchars(\uString::sql2text($uCat->uCat_common->var_type_id2data($uCat->var_type_id)->var_type_title))?>">
</div>

<div class="form-group">
    <label for="admin_item_variant_editor_dg_item_type">Тип товара</label>
    <div class="input-group">
        <select class="form-control" id="admin_item_variant_editor_dg_item_type">
            <?
            $item_types=$uCat->uCat_common->get_item_types();
            while($type=$item_types->fetch_object()) {?>
                <option value="<?=$type->type_id?>"><?=htmlspecialchars(\uString::sql2text($type->type_title))?></option>
            <?}
            ?>
        </select>
        <span class="input-group-btn">
        <button onclick="uCat_item_admin.edit_items_types()" class="btn btn-default uTooltip" title="Добавить, удалить, изменить типы товаров" type="button"><span class="icon-pencil"></span></button>
      </span>
    </div>
</div>

<input type="hidden" value="<?=$uCat->var_type_id?>" id="admin_item_variant_editor_dg_var_type_id">

<div class="bs-callout bs-callout-default">
    <p>При смене типа товара для варианта, все товары, у которых этот вариант является основным, также поменяют свой тип</p>
</div>