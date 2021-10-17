<?php
class uKnowbase_new_record_load_records_pos{
    private $uCore;
    public $rec_id,$rec,$q_records;
    private function get_records() {
        if(!$this->q_records=$this->uCore->query("uKnowbase","SELECT
            `rec_id`,
            `is_section`,
            `rec_title`,
            `rec_indent`,
            `rec_position`
            FROM
            `u235_records`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `rec_position` ASC
            ")) $this->uCore->error(4);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(33)) die("forbidden");
        $this->get_records();
    }
}
$uKnowbase=new uKnowbase_new_record_load_records_pos($this);?>
<div class="form-group">
    <label>Заголовок записи:</label>
    <input type="text" id="uKnowbase_new_rec_title" class="form-control">
</div>
<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="checkbox" id="uKnowbase_new_rec_is_section">
             <span>Это заголовок, раздел?</span>
        </label>
    </div>
</div>

<div class="form-group">
    <label>Отступ слева</label>
    <select id="uKnowbase_new_rec_indent" class="form-control selectpicker"><?for($i=0;$i<5;$i++) {?>
            <option value="<?=$i?>"><?=$i?></option>
        <?}?></select>
</div>
<div class="form-group">
    <label>После какой записи отображать?</label>
    <select class="form-control selectpicker" id="uKnowbase_new_rec_position">
        <option value="0">На самом верху</option>
        <?while($rec=$uKnowbase->q_records->fetch_object()) {?>
            <option value="<?=$rec->rec_id?>" style="<?=$rec->is_section=='1'?'font-weight: bold;':''?> padding-left:<?=($rec->rec_indent*20)+10?>px;"><?=uString::sql2text($rec->rec_title)?></option>
        <?}?></select>
</div>
