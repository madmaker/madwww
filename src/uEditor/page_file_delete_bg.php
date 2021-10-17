<?php
class uEditor_page_file_delete_bg {
    private $uCore;
    private $page_id,$file_id;
    private function checkData() {
        if(!isset($_POST['page_id'],$_POST['file_id'])) $this->uCore->error(1);
        $this->page_id=$_POST['page_id'];
        $this->file_id=$_POST['file_id'];
        if(!uString::isDigits($this->page_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->file_id)&&$this->file_id!='all') $this->uCore->error(3);
    }
    private function delFile() {
        if($this->file_id!='all') {
            if(!$query=$this->uCore->query("pages","SELECT
            `file_name`
            FROM
            `u235_pages_files`
            WHERE
            `file_id`='".$this->file_id."' AND
            `page_id`='".$this->page_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(!mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'file not found', 'file_id':'".$this->file_id."'}");
            $file_name=$query->fetch_object();
            $file_name=$file_name->file_name;
            @unlink($this->uCore->mod.'/files/'.site_id.'/'.$this->page_id.'/'.$file_name);
            if(!$this->uCore->query("pages","DELETE FROM `u235_pages_files` WHERE `file_id`='".$this->file_id."' AND `site_id`='".site_id."'")) $this->uCore->error(5);
        }
        else {
            @uFunc::rmdir($this->uCore->mod.'/files/'.site_id.'/'.$this->page_id);
            if(!$this->uCore->query("pages","DELETE FROM `u235_pages_files` WHERE `page_id`='".$this->page_id."' AND `site_id`='".site_id."'")) $this->uCore->error(6);
        }
        echo "{'status' : 'success','file_id':'".$this->file_id."'}";
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(7)) die("{'status' : 'forbidden'}");
        $this->checkData();
        $this->delFile();
    }
}
$uEditor=new uEditor_page_file_delete_bg($this);
