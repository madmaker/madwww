<?php
class uEvents_admin_event_load_dates {
    private $uCore,$event_id;

    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_event_load_dates_bg'),$str);
    }


    private function check_data() {
        if(!isset($_POST['event_id'])) $this->uCore->error(1);
        $this->event_id=$_POST['event_id'];
        if(!uString::isDigits($this->event_id)) $this->uCore->error(2);
    }
    public function get_event_dates() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `date_id`,
        `date`,
        `duration`,
        `comment`
        FROM
        `u235_events_dates`
        WHERE
        `event_id`='".$this->event_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        return $query;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
    }
}
$uEvent=new uEvents_admin_event_load_dates($this);

$q_dates=$uEvent->get_event_dates();
if(mysqli_num_rows($q_dates)) {?>
    <table class="table table-striped">
        <tr>
            <th><?=$uEvent->text("Event start date - table header cell"/*Дата начала*/)?></th>
            <th colspan="2"><?=$uEvent->text("Event duration - table header cell"/*Продолжительность, дней*/)?></th>
        </tr>
        <?while($date=$q_dates->fetch_object()) {?>
            <tr>
                <td>
                    <div class="btn-group u235_eip">
                        <button class="btn btn-xs btn-danger uTooltip uEvents_event_delete_date_btn" title="<?=$uEvent->text("Delete this date - btn txt"/*Удалить эту дату*/)?>" onclick="uEvents_event_admin.delete_date_do(<?=$date->date_id?>)"><span class="glyphicon glyphicon-remove"></span></button>
                        <button class="btn btn-xs btn-default uTooltip uEvents_event_edit_date_btn" title="<?=$uEvent->text("Edit this date - btn txt"/*Изменить дату*/)?>" onclick="uEvents_event_admin.edit_date_init(<?=$date->date_id?>)"><span class="glyphicon glyphicon-pencil"></span></button>
                    </div>&nbsp;
                    <?=date('d.m.Y',$date->date);?></td>
                <td><?=$date->duration?></td>
                <td><?=$date->comment?></td>
            </tr>
        <?}?>
    </table>
<?}?>
