<?php
class uCat_admin_item_types_editor {
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
$uCat=new uCat_admin_item_types_editor($this);?>
<table class="table table-condensed table-striped" id="uCat_item_types_editor">
    <?while($item_type=$uCat->q_items_types->fetch_object()) {?>
        <tr id="uCat_item_types_editor_type_<?=$item_type->type_id?>">
            <td><button class="btn btn-default btn-xs" onclick="uCat_item_admin.item_type_edit(<?=$item_type->type_id?>)"><span class="icon-pencil"></span></button></td>
            <td>#<?=$item_type->type_id?></td>
            <td class="td_type_title"><?=uString::sql2text($item_type->type_title)?></td>
            <td><button class="btn btn-danger btn-xs uCat_item_types_editor_type_delete_btn" onclick="uCat_item_admin.delete_item_type(<?=$item_type->type_id?>)"><span class="icon-cancel"></span></button></td>
        </tr>
    <?}?>
</table>