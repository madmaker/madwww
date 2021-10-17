<?php
class uEvents_cron_events_cleaner {
    private $uCore,$secret;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }
    private function clean() {
        //get expired dates
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`,
        `site_id`
        FROM
        `u235_events_dates`
        WHERE
        (`date`+`duration`*24*60*60)<".(time())."
        ")) $this->uCore->error(3);
        //clear cache for events
        while($ev=$query->fetch_object()) {
            uFunc::rmdir("uEvents/cache/event/".$ev->site_id.'/'.$ev->event_id);
            //get types_id
            if(!$query1=$this->uCore->query("uEvents","SELECT
            `event_type_id`
            FROM
            `u235_events_list`
            WHERE
            `event_id`='".$ev->event_id."' AND
            `site_id`='".$ev->site_id."'
            ")) $this->uCore->error(4);
            //clear cache for events_types
            while($type=$query1->fetch_object()) {
                uFunc::rmdir("uEvents/cache/events/".$ev->site_id.'/'.$type->event_type_id);
            }
        }

        //delete expired dates
        if(!$query=$this->uCore->query("uEvents","DELETE FROM
        `u235_events_dates`
        WHERE
        (`date`+`duration`*24*60*60)<".(time())."
        ")) $this->uCore->error(5);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='Hjksdfsdf798254jilkjo(**(uihksdf';

        //$this->check_data();
        $this->clean();
    }
}
$uEvents=new uEvents_cron_events_cleaner ($this);
