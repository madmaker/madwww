<?php
class uEvents_events_file {
    private $uCore,$file_id,$file_addr,$file_mime,$file_name,$file_size,$thumb;

    public function text($str) {
        return $this->uCore->text(array('uEvents','events_types_file'),$str);
    }

    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) die('');
        $this->file_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->file_id)) die('');
        $this->thumb=false;
        if(isset($this->uCore->url_prop[2])) {
            if($this->uCore->url_prop[2]=='sm') $this->thumb=true;
        }
    }
    private function get_file() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_id`,
        `file_name`,
        `file_name_hash`,
        `file_ext`,
        `file_mime`,
        `file_size`
        FROM
        `u235_events_types_files`
        WHERE
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);
        if(!mysqli_num_rows($query)) die('');
        $file=$query->fetch_object();

        if($this->thumb) {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/events_types_files/'.site_id.'/'.$file->type_id.'/'.$this->file_id.'/'.$this->file_id.'_sm.jpg';
            $this->file_mime='image/jpeg';
            $this->file_name='thumb_'.$file->file_name;
        }
        else {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/events_types_files/'.site_id.'/'.$file->type_id.'/'.$this->file_id.'/'.$file->file_name_hash;
            $this->file_mime=$file->file_mime;
            $this->file_name=$file->file_name;
        }
        $this->file_size=$file->file_size;
    }
    private function file_output() {
        if (!file_exists($this->file_addr)) die('');
        header('Content-Description: File Transfer');
        header('Content-Type: '.$this->file_mime);
        header('Accept-Ranges: bytes');
        header('Content-Disposition: '.($this->file_mime=='application/octet-stream'?'attachment':'inline').'; filename="'.uString::sql2text($this->file_name,1).'"');
        if(!$this->thumb) header('Content-Length: ' . $this->file_size);
        flush();
        readfile($this->file_addr);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->uCore->page['page_title']=$this->text("Page name"/*Файл*/);

        $this->checkData();
        $this->get_file();
        $this->file_output();
        exit;
    }
}
$uEvents=new uEvents_events_file($this);
