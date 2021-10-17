<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uEvents_new_event_bg {
    private $uCore,$uFunc,$event_title,$event_type_id,$event_id,$event_pos;
    private function check_data() {
        if(!isset($_POST['event_title'],$_POST['event_type_id'])) $this->uCore->error(1);
        $this->event_title=trim($_POST['event_title']);
        $this->event_type_id=$_POST['event_type_id'];
        if(!uString::isDigits($this->event_type_id)) $this->uCore->error(2);
        if(!strlen($this->event_title)) die('{
        "status":"error",
        "msg":"title is empty"
        }');
        if(!uString::isDigits($this->event_type_id)) die('{
        "status":"error",
        "msg":"wrong type_id"
        }');

        //check if this type_id exists
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`
        FROM
        `u235_events_types`
        WHERE
        `type_id`='".$this->event_type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) $this->uCore->error(4);
    }
    private function save_event() {
        //get new event_id
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`
        FROM
        `u235_events_list`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `event_id` DESC
        LIMIT 1
        ")) $this->uCore->error(5);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->event_id=$qr->event_id+1;
        }
        else {
            $this->event_id=1;
        }
        //get new event_pos
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_pos`
        FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->event_type_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `event_pos` ASC
        LIMIT 1
        ")) $this->uCore->error(6);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->event_pos=$qr->event_pos-1;
        }
        else {
            $this->event_pos=0;
        }

        $is_header=0;
        if(isset($_POST['is_header'])) $is_header=1;

        if(!$this->uCore->query("uEvents","INSERT INTO
        `u235_events_list` (
        `event_id`,
        `event_type_id`,
        `is_header`,
        `event_title`,
        `event_descr`,
        `event_pos`,
        `form_id`,
        `site_id`
        ) VALUES (
        '".$this->event_id."',
        '".$this->event_type_id."',
        '".$is_header."',
        '".uString::text2sql($this->event_title)."',
        '',
        '".$this->event_pos."',
        '0',
        '".site_id."'
        )
        ")) $this->uCore->error(7);
    }


    private function clear_cache($clear_same_events=true) {
        //delete cache
        //for event's type
        uFunc::rmdir('uEvents/cache/events/'.site_id.'/'.$this->event_type_id);
        if($clear_same_events) {
            //for all same type events
            if(!$query=$this->uCore->query("uEvents","SELECT
            `event_id`
            FROM
            `u235_events_list`
            WHERE
            `event_type_id`='".$this->event_type_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(8);
            while($ev=$query->fetch_object()) {
                uFunc::rmdir('uEvents/cache/event/'.site_id.'/'.$ev->event_id);
            }
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->save_event();
        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_title":"'.rawurlencode($this->event_title).'",
        "event_type_id":"'.$this->event_type_id.'"
        }';

        $this->clear_cache(true);
    }
}
$uEvent=new uEvents_new_event_bg($this);
