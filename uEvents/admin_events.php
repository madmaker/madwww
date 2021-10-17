<?php
namespace uEvents;

use PDO;
use PDOException;
use uString;

require_once "processors/classes/uFunc.php";

class admin_events {
    private $uFunc;
    private $uCore;
    public $test;
    public function text($str) {
        return $this->uCore->text(array('uEvents','admin_events'),$str);
    }
    public function get_events_list($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_id,
            event_type_id,
            event_title,
            show_begin_timestamp,
            show_end_timestamp
            FROM
            u235_events_list
            WHERE
            site_id=:site_id
            ORDER BY
            event_id ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_events_types_list($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id,
            type_title,
            type_url
            FROM
            u235_events_types
            WHERE
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*События*/);

        //uInt JS
        $this->uCore->uInt_js('uEvents','admin_events');
        $this->uFunc->incJs("uEvents/js/admin_events.min.js");
    }
}
$uEvents=new admin_events($this);
ob_start();?>

<script type="text/javascript">
    let i;
    if(typeof uEvents_admin_events==="undefined") {
        uEvents_admin_events = {};
        uEvents_admin_events.events = [];
        uEvents_admin_events.type_id2title = [];
        uEvents_admin_events.type_id2url = [];
    }
    <?
    $events=$uEvents->get_events_list();
    /** @noinspection PhpUndefinedMethodInspection */
    while($event=$events->fetch(PDO::FETCH_OBJ)) {
        $event->show_begin_timestamp=(int)$event->show_begin_timestamp;
        $event->show_end_timestamp=(int)$event->show_end_timestamp;
        ?>
    i=uEvents_admin_events.events.length;
    uEvents_admin_events.events[i]=[];
    uEvents_admin_events.events[i]['event_id']=<?=$event->event_id?>;
    uEvents_admin_events.events[i]['show_begin_timestamp']="<?=$event->show_begin_timestamp?date("d.m.Y",$event->show_begin_timestamp):""?>";
    uEvents_admin_events.events[i]['show_end_timestamp']="<?=$event->show_end_timestamp?date("d.m.Y",$event->show_end_timestamp):""?>";
    uEvents_admin_events.events[i]['event_type_id']=<?=$event->event_type_id?>;
    uEvents_admin_events.events[i]['event_title']="<?=rawurlencode(uString::sql2text($event->event_title))?>";
    <?}

    $types=$uEvents->get_events_types_list();
    /** @noinspection PhpUndefinedMethodInspection */
    while($type=$types->fetch(PDO::FETCH_OBJ)) {?>
    uEvents_admin_events.type_id2title[<?=$type->type_id?>]='<?=rawurlencode(uString::sql2text($type->type_title))?>';
    uEvents_admin_events.type_id2url[<?=$type->type_id?>]='<?=rawurlencode(uString::sql2text($type->type_url))?>';
    <?}
 ?>
</script>

<div class="container-fluid" id="uEvents_admin_events_list"></div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";