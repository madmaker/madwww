<?php
class uCat_admin_avails_editor{
    private $uCore,$avail_id;
    public $avail,$q_avails;
    private function check_data() {
        if(!isset($_POST['avail_id'])) $this->uCore->error(1);
        $this->avail_id=$_POST['avail_id'];

        if(!uString::isDigits($this->avail_id)) $this->uCore->error(2);
    }
    private function get_avail() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `avail_id`,
        `avail_label`,
        `avail_descr`,
        `avail_type_id`
        FROM
        `u235_items_avail_values`
        WHERE
        `avail_id`='".$this->avail_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) $this->uCore->error(4);
        $this->avail=$query->fetch_object();
    }
    private function get_avails() {
        if(!$this->q_avails=$this->uCore->query("uCat","SELECT
        `avail_type_id`,
        `avail_type_title`
        FROM
        `u235_items_avail_types`
        ORDER BY
        `avail_type_id`
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($this->q_avails)) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->uCore->access(25)) die('forbidden');

        $this->check_data();
        $this->get_avail();
        $this->get_avails();
    }
}
$uCat=new uCat_admin_avails_editor($this);?>

<input type="hidden" id="uCat_avail_settings_edit_avail_id" value="<?=$uCat->avail->avail_id?>">
<div class="form-group">
    <label>Название доступности</label>
    <input type="text" id="uCat_avail_settings_edit_avail_label" class="form-control" placeholder="На складе завались!" value="<?=uString::text2screen(uString::sql2text($uCat->avail->avail_label))?>">
</div>
<div class="form-group">
    <label>Описание</label>
    <input type="text" id="uCat_avail_settings_edit_avail_descr" class="form-control" placeholder="Можно прийти и купить. Все есть в наличии!" value="<?=uString::text2screen(uString::sql2text($uCat->avail->avail_descr))?>">
    <p class="help-block">Дополнительная информация для клиента</p>
</div>
<div class="form-group">
    <label>Тип доступности</label>
    <div class="input-group">
        <select id="uCat_avail_settings_edit_avail_type_id" class="form-control">
            <? while($avail=$uCat->q_avails->fetch_object()) {?>
                <option value="<?=$avail->avail_type_id?>"  <?=$uCat->avail->avail_type_id==$avail->avail_type_id?'selected':''?>><?=$avail->avail_type_title?></option>
            <?}?>
        </select>
        <div class="input-group-btn">
            <button class="btn btn-default uTooltip" title="Для чего нужен каждый тип?" onclick="jQuery('#uCat_item_availability_types_descr_dg').modal('show')"><span class="glyphicon glyphicon-question-sign"></span></button>
        </div>
    </div>
</div>
