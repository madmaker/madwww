<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uEvents_admin_event_delete {
    private $uCore,$uFunc,$event_id,$type_id;
    private function check_data() {
        if(!isset($_POST['event_id'])) $this->uCore->error(10);
        $this->event_id=$_POST['event_id'];
        if(!uString::isDigits($this->event_id)) $this->uCore->error(20);

        //check if event_id exists and btw get type_id
        $this->get_type_id();
    }
    private function delete_event() {
        uFunc::rmdir($this->uCore->mod.'/events_files/'.site_id.'/'.$this->event_id);

        if(!$this->uCore->query("uEvents","DELETE FROM
        `u235_events_list`
        WHERE
        `event_id`='".$this->event_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);

        if(!$this->uCore->query("uEvents","DELETE FROM
        `u235_events_files`
        WHERE
        `event_id`='".$this->event_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);

        if(!$this->uCore->query("uEvents","DELETE FROM
        `u235_events_dates`
        WHERE
        `event_id`='".$this->event_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(50);

        if(!$this->uCore->query("uPage","DELETE FROM
        `u235_cols_els`
        WHERE
        `el_id`='".$this->event_id."' AND
        `el_type`='uEvents_dates' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
    }
    private function get_type_id() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_type_id`
        FROM
        `u235_events_list`
        WHERE
        `event_id`='".$this->event_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(70);
        if(!mysqli_num_rows($query)) $this->uCore->error(80);
        $qr=$query->fetch_object();
        $this->type_id=$qr->event_type_id;
    }
    private function clear_cache() {
        //delete cache
        //for current event cache
        uFunc::rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
        //for event's type
        uFunc::rmdir('uEvents/cache/events/'.site_id.'/'.$this->type_id);
        //for all same type events
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`
        FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(90);
        while($ev=$query->fetch_object()) {
            uFunc::rmdir('uEvents/cache/event/'.site_id.'/'.$ev->event_id);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_event();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'"
        }';

        $this->clear_cache();
    }
}
$uEvents=new uEvents_admin_event_delete($this);
