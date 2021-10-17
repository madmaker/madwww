<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_record_change_pos {
    public $uFunc;
    public $uSes;
    private $uCore;
    public $rec_id,$rec,$q_records;
    private function check_data() {
        if(!$this->uCore->access(33)) return false;

        if(!isset($_POST['rec_id'])) $this->uCore->error(1);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(2);

        //get record info
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_position`,
        `rec_indent`,
        `rec_title`,
        `user_id`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) return false;
        $this->rec=$query->fetch_object();

        //check if user have access to edit this record
        if($this->uCore->access(38)) return true;
        //check if user is owner of this record and
        if($this->rec->user_id==$this->uSes->get_val("user_id")) return true;

        return false;
    }
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
            `site_id`='".site_id."' AND
            `rec_id`!='".$this->rec_id."'
            ORDER BY
            `rec_position` ASC
            ")) $this->uCore->error(4);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);


        if(!$this->check_data()) die("forbidden");
        $this->get_records();
    }
}
$uKnowbase=new uKnowbase_record_change_pos($this);?>

<h3><?=uString::sql2text($uKnowbase->rec->rec_title,1)?></h3>
<input type="hidden" id="uKnowbase_change_pos_rec_id" value="<?=$uKnowbase->rec_id?>">
<div class="form-group">
    <label>Отступ слева</label>
    <select id="uKnowbase_change_pos_rec_indent" class="form-control selectpicker"><?for($i=0;$i<5;$i++) {?>
           <option value="<?=$i?>" <?=($i==$uKnowbase->rec->rec_indent)?' selected ':''?>><?=$i?></option>
        <?}?></select>
</div>
<div class="form-group">
    <label>После какой записи отображать?</label>
    <select class="form-control selectpicker" id="uKnowbase_change_pos_rec_pos">
        <option value="0">На самом верху</option>
        <?while($rec=$uKnowbase->q_records->fetch_object()) {?>
            <option value="<?=$rec->rec_id?>" <?=($rec->rec_position==$uKnowbase->rec->rec_position-1)?' selected ':''?> style="<?=$rec->is_section=='1'?'font-weight: bold;':''?> padding-left:<?=($rec->rec_indent*20)+10?>px;"><?=uString::sql2text($rec->rec_title)?></option>
        <?}?></select>
</div>
<div class="checkbox">
    <label>
        <input type="checkbox" id="uKnowbase_change_pos_apply_for_children" checked>  <span>Применить к дочерним записям <span class="text-muted">- все дочерние записи также будут перемещены</span></span>
    </label>
</div>
