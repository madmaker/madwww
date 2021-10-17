<?php
class uSubscr_file {
    private $uCore,$file_id,$file_addr,$file_mime,$file_name,$file_size,$thumb;
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
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `rec_id`,
        `file_name`,
        `file_name_hash`,
        `file_ext`,
        `file_mime`,
        `file_size`
        FROM
        `u235_records_files`
        WHERE
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);
        if(!mysqli_num_rows($query)) die('');
        $file=$query->fetch_object();

        if($this->thumb) {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/files/'.site_id.'/'.$file->rec_id.'/'.$this->file_id.'/'.$this->file_id.'_sm.jpg';
            $this->file_mime='image/jpeg';
            $this->file_name='thumb_'.$file->file_name;
        }
        else {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/files/'.site_id.'/'.$file->rec_id.'/'.$this->file_id.'/'.$file->file_name_hash;
            $this->file_mime=$file->file_mime;
            $this->file_name=$file->file_name;
        }
        $this->file_size=$file->file_size;
    }
    private function file_output() {
        if (!file_exists($this->file_addr)) die('');
        header('Content-Description: File Transfer');
        header('Content-Type: '.$this->file_mime);
        //header('Cache-Control: public, must-revalidate, max-age=0');
        //header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: '.($this->file_mime=='application/octet-stream'?'attachment':'inline').'; filename="'.$this->file_name.'"');
        //header('Content-Transfer-Encoding: binary');
        //header('Expires: 0');
        if(!$this->thumb) header('Content-Length: ' . $this->file_size);
        //ob_clean();
        flush();
        readfile($this->file_addr);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->checkData();
        $this->get_file();
        $this->file_output();
        exit;
    }
}
$uSubscr=new uSubscr_file($this);
