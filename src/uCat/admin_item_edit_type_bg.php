<?php
class uCat_admin_item_edit_type {
    private $uCore;
    public $type_id,$base_type_id,$type_title;
    private function check_data() {
        if(!isset($_POST['type_id'])) $this->uCore->error(10);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uCore->error(20);
    }
    private function get_type_data() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `base_type_id`,
        `type_title`
        FROM
        `items_types`
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);
        if(!mysqli_num_rows($query)) $this->uCore->error(30);
        $type=$query->fetch_object();
        $this->base_type_id=(int)$type->base_type_id;
        $this->type_title=uString::sql2text($type->type_title,1);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die("forbidden");

        $this->check_data();
        $this->get_type_data();
    }
}
$uCat=new uCat_admin_item_edit_type($this);?>
<input type="hidden" id="uCat_edit_item_type_id" value="<?=$uCat->type_id?>">
<div class="form-group">
    <label for="uCat_edit_item_type_base_type_id">Базовый тип</label>
    <select id="uCat_edit_item_type_base_type_id" class="form-control">
        <option value="0" <?=$uCat->base_type_id==0?'selected':''?>>Обычный товар</option>
        <option value="1" <?=$uCat->base_type_id==1?'selected':''?>>Ссылка для скачивания</option>
    </select>
</div>
<div class="form-group">
    <label for="uCat_edit_item_type_title">Название типа товаров</label>
    <input id="uCat_edit_item_type_title" class="form-control" value="<?=htmlspecialchars($uCat->type_title)?>">
</div>
<div class="bs-callout bs-callout-warning">
    <p>Если вы измените базовый тип, то это коснется всех товаров и всех вариантов такого типа.</p>
    <p class="small text-muted">Например, если была обычная книга, она может стать электронной и вместо книги сайт будет продавать электронную.</p>
</div>