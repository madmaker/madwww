<?php
class uCat_admin_item_type_editor {
    private $uCore,$item_id;
    public $q_items_types;

    private function check_data() {
        if(!isset($_POST['item_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(10);
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(20);
    }
    private function get_items_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_items_types=$this->uCore->query("uCat","SELECT
        `type_id`,
        `type_title`
        FROM
        `items_types`
        WHERE
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(30);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->access(25)) die("forbidden");

        $this->check_data();
        $this->get_items_types();
    }
}
$uCat=new uCat_admin_item_type_editor($this);?>
<div class="form-group">
    <label for="uCat_item_type_edit_selectbox">Тип товара</label>
    <div class="input-group">
        <select id="uCat_item_type_edit_selectbox" сlass="form-control">
            <?while($item_type=$uCat->q_items_types->fetch_object()) {?>
            <option id="uCat_item_type_edit_option_<?=$item_type->type_id?>" value="<?=$item_type->type_id?>"><?=uString::sql2text($item_type->type_title)?></option>
            <?}?>
        </select>
        <span class="input-group-btn">
        <button onclick="uCat_item_admin.edit_items_types()" class="btn btn-default uTooltip" title="Добавить, удалить, изменить типы товаров" type="button"><span class="icon-pencil"></span></button>
      </span>
    </div>
</div>