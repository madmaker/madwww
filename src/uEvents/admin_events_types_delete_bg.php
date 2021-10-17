<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uEvents_admin_events_types_delete {
    private $uCore,$uFunc,$type_id,$action,$new_type_id;
    private function check_data() {
        if(!isset($_POST['type_id'],$_POST['action'],$_POST['new_type_id'])) $this->uCore->error(1);
        $this->type_id=$_POST['type_id'];
        $this->action=$_POST['action'];
        $this->new_type_id=$_POST['new_type_id'];

        if(!uString::isDigits($this->type_id)) $this->uCore->error(2);
        if($this->type_id==$this->new_type_id) die('{
        "status":"error",
        "msg":"same type"
        }');
        if($this->action=='move') {
            if(!uString::isDigits($this->new_type_id)) $this->uCore->error(3);
            if(!$query=$this->uCore->query("uEvents","SELECT
            `type_id`
            FROM
            `u235_events_types`
            WHERE
            `type_id`='".$this->new_type_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(!mysqli_num_rows($query)) die('{
            "status":"error",
            "msg":"new type_id is not found"
            }');
        }
        elseif($this->action=='all') return true;
        else $this->uCore->error(5);
    }
    private function move_events() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`
        FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(7);

        if(!$this->uCore->query("uEvents","UPDATE
        `u235_events_list`
        SET
        `event_type_id`='".$this->new_type_id."'
        WHERE
        `event_type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);

        while($event=$query->fetch_object()) {
            uFunc::rmdir('uEvents/events_files/'.site_id.'/'.$event->event_id);
        }
        uFunc::rmdir('uEvents/cache/events/'.site_id.'/'.$this->new_type_id);
    }
    private function delete_events() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`
        FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(7);
        while($event=$query->fetch_object()) {
            uFunc::rmdir('uEvents/events_files/'.site_id.'/'.$event->event_id);
        }

        if(!$query=$this->uCore->query("uEvents","DELETE FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(8);
    }
    private function delete_type() {
        //delete type from db
        if(!$this->uCore->query("uEvents","DELETE FROM
        `u235_events_types`
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(9);

        //delete type's files
        if(!$this->uCore->query("uEvents","DELETE FROM
        `u235_events_types_files`
        WHERE
        `type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);

        //Delete from uPage
        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols_els`
        WHERE
        `el_id`='".$this->type_id."' AND
        (
        `el_type`='uEvents_list' OR
        `el_type`='uEvents_calendar'
        )AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);

        uFunc::rmdir('uEvents/cache/events/'.site_id.'/'.$this->type_id);
        uFunc::rmdir('uEvents/events_types_files/'.site_id.'/'.$this->type_id);

        if($this->action=='move') $this->move_events();
        else $this->delete_events();
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_type();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo '{
        "status":"done"
        }';
    }
}
$uEvents=new uEvents_admin_events_types_delete($this);
