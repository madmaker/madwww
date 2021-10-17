<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_file {
    public $uFunc;
    public $uSes;
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
    private function check_access() {
        if($this->uCore->access(33)) return true;
        //get user's comps
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_id`
        FROM
        `u235_com_users`
        WHERE
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);

        $q_comps='(1=0';
        while($com=$query->fetch_object()) {
            $q_comps.=" OR `com_id`='".$com->com_id."' ";
        }
        $q_comps.=')';

        //get rec_id by file_id
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records_files`
        WHERE
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
        if(!mysqli_num_rows($query)) return false;
        $qr=$query->fetch_object();
        $rec_id=$qr->rec_id;

        //check if rec access is limited
        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `access_limited`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(30);
        if(!mysqli_num_rows($query)) return false;
        $rec=$query->fetch_object();
        if($rec->access_limited=='0') return true;

        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records_comps`
        WHERE
        ".$q_comps." AND
        `rec_id`='".$rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(40);

        if(mysqli_num_rows($query)) return true;

        return false;
    }
    private function get_file() {
        if(!$query=$this->uCore->query("uKnowbase","SELECT
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
        ")) $this->uCore->error(50);
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
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->checkData();
        if($this->check_access()) {
            $this->get_file();
            $this->file_output();
        }
        exit;
    }
}
$newClass=new uKnowbase_file($this);
