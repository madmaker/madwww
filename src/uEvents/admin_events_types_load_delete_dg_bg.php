<?php
class uEvents_admin_events_types_load_delete_dg {
    private $uCore,$type_id;
    private function check_data() {
        if(!isset($_POST['type_id'])) $this->uCore->error(10);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uCore->error(20);
    }
    public function get_events_types() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`,
        `type_title`
        FROM
        `u235_events_types`
        WHERE
        `type_id`!='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);
        return $query;
    }

    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_events_types_load_delete_dg_bg'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(300)) die("forbidden");

        $this->check_data();
    }
}
$uEvents=new uEvents_admin_events_types_load_delete_dg($this);?>

<div class="bs-callout bs-callout-danger">
    <?=$uEvents->text('Alert.Delete events type. Events will be deleted'/*<p>Если удалить тип событий, то все прикрепленные события пропадут</p>
    <p>Они либо будут удалены, либо нужно указать, к какому другому типу событий их нужно прикрепить</p>*/);?>
</div>

<div class="form-group">
    <label><?=$uEvents->text('Info. Specify events type for events'/*Укажите, к какому типу событий прикрепить события удаляемого типа*/);?></label>
    <select id="uEvents_delete_type_types" class="form-control">
        <?$q_types=$uEvents->get_events_types();
        while($type=$q_types->fetch_object()) {?>
            <option value="<?=$type->type_id?>"><?=uString::sql2text($type->type_title)?></option>
        <?}
    ?></select>
</div>
