<?php
class uViblog_admin_list_file_delete_bg {
    private $uCore;
    private $file_id;
    private function checkData() {
        if(!isset($_POST['file_id'])) $this->uCore->error(1);
        $this->file_id=$_POST['file_id'];
        if(!uString::isDigits($this->file_id)&&$this->file_id!='all') $this->uCore->error(3);
    }
    private function delFile() {
        if($this->file_id!='all') {
            if(!$query=$this->uCore->query("uViblog","SELECT
            `file_name`
            FROM
            `u235_descr_files`
            WHERE
            `file_id`='".$this->file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(!mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'file not found', 'file_id':'".$this->file_id."'}");
            $file_name=$query->fetch_object();
            $file_name=$file_name->file_name;
            @unlink($this->uCore->mod.'/descr_files/'.site_id.'/'.$file_name);
            if(!$this->uCore->query("uViblog","DELETE FROM
            `u235_descr_files`
            WHERE
            `file_id`='".$this->file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(5);
        }
        else {
            @uFunc::rmdir($this->uCore->mod.'/descr_files/'.site_id);
            if(!$this->uCore->query("uViblog","DELETE FROM
            `u235_descr_files`
            WHERE
            `site_id`='".site_id."'
            ")) $this->uCore->error(5);
        }
        echo "{'status' : 'success','file_id':'".$this->file_id."'}";
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(4)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->delFile();
    }
}
$uEditor=new uViblog_admin_list_file_delete_bg($this);
