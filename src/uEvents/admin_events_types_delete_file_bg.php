<?php
class uEvents_event_delete_file {
    private $uCore,$type_id,$file_id;
    private function check_data() {
        if(!isset($_POST['type_id'],$_POST['file_id'])) $this->uCore->error(10);
        $this->type_id=$_POST['type_id'];
        $this->file_id=$_POST['file_id'];

        if(!uString::isDigits($this->type_id)) $this->uCore->error(20);
        if(!uString::isDigits($this->file_id)&&$this->file_id!='all') $this->uCore->error(30);
    }
    private function delete_file() {
        if($this->file_id=='all') {
            uFunc::rmdir($this->uCore->mod.'/events_types_files/'.site_id.'/'.$this->type_id);
            if(!$this->uCore->query("uEvents","DELETE FROM
            `u235_events_types_files`
            WHERE
            `type_id`='".$this->type_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(40);
        }
        else {
            uFunc::rmdir($this->uCore->mod.'/events_types_files/'.site_id.'/'.$this->type_id.'/'.$this->file_id);
            if(!$this->uCore->query("uEvents","DELETE FROM
            `u235_events_types_files`
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
