<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class uEvents_admin_new_event_dg_loader {
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_event_type_selectbox_loader'),$str);
    }


    private function add_first_event_type() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("INSERT INTO
            u235_events_types (
            type_id,
            type_title,
            type_url,
            site_id
            ) VALUES (
            '1',
            :type_title,
            'events',
            :site_id
            )
            ");
            $type_title=$this->text("Events - first event type title"/*События*/);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_title', $type_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            //$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

    }
    public function get_events_types() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`,
        `type_title`
        FROM
        `u235_events_types`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uFunc->error(20);
        if(!mysqli_num_rows($query)) {
            $this->add_first_event_type();
            return $this->get_events_types();
        }
        return $query;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uSes->access(300)) die($this->text("Access denied - msg txt"/*В доступе отказано*/));
    }
}
if(isset($_POST['type_id'])) {
    if(uString::isDigits($_POST['type_id'])) $type_id=$_POST['type_id'];
}
if(!isset($type_id)) $type_id=0;
$uEvents=new uEvents_admin_new_event_dg_loader($this);?>

<div class="form-group">
    <label><?=$uEvents->text("Choose event type - input label"/*Выберите тип события*/)?></label>
    <div class="input-group">
        <select id="uEvents_event_type_id" class="form-control"><?
            $q_events_types=$uEvents->get_events_types();
            while($evt=$q_events_types->fetch_object()) {?>
                <option value="<?=$evt->type_id?>" <?=$type_id==$evt->type_id?' selected ':''?>><?=uString::sql2text($evt->type_title,0)?></option>
            <?}
            ?></select>
        <div class="input-group-btn">
            <button type="button" class="btn btn-default uTooltip" title="<?=$uEvents->text("Add new event type - btn txt"/*Добавить новый тип событий*/)?>" onclick="uEvents_inline_create.new_event_type_init()"><span class="glyphicon glyphicon-plus"></span></button>
        </div><!-- /btn-group -->
    </div><!-- /input-group -->
</div>
