<?php
class uEvents_event_delete_file {
    private $uCore,$event_id,$file_id;
    private function check_data() {
        if(!isset($_POST['event_id'],$_POST['file_id'])) $this->uCore->error(10);
        $this->event_id=$_POST['event_id'];
        $this->file_id=$_POST['file_id'];

        if(!uString::isDigits($this->event_id)) $this->uCore->error(20);
        if(!uString::isDigits($this->file_id)&&$this->file_id!='all') $this->uCore->error(30);
    }
    private function delete_file() {
        if($this->file_id=='all') {
            uFunc::rmdir($this->uCore->mod.'/events_files/'.site_id.'/'.$this->event_id);
            if(!$this->uCore->query("uEvents","DELETE FROM
            `u235_events_files`
            WHERE
            `event_id`='".$this->event_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(40);
        }
        else {
            uFunc::rmdir($this->uCore->mod.'/events_files/'.site_id.'/'.$this->event_id.'/'.$this->file_id);
            if(!$this->uCore->query("uEvents","DELETE FROM
            `u235_events_files`
            WHERE
            `file_id`='".$this->file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(50);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_file();

        echo '{
        "status":"done",
        "file_id":"'.$this->file_id.'"
        }';
    }
}
$uEvent=new uEvents_event_delete_file($this);
