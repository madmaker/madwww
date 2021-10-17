<?php
class uForms_form_admin_file_delete {
    private $uCore;
    private $file_id,$form_id;
    private function checkData() {
        if(!isset($_POST['file_id'],$_POST['form_id'])) $this->uCore->error(1);
        $this->file_id=$_POST['file_id'];
        $this->form_id=$_POST['form_id'];
        if(!uString::isDigits($this->file_id)&&$this->file_id!='all') $this->uCore->error(2);
        if(!uString::isDigits($this->form_id)) $this->uCore->error(3);
    }
    private function delFile() {
        if($this->file_id!='all') {
            uFunc::rmdir($this->uCore->mod.'/form_files/'.site_id.'/'.$this->form_id.'/'.$this->file_id);
            if(!$this->uCore->query('uForms',"DELETE FROM
            `u235_forms_files`
            WHERE
            `form_id`='".$this->form_id."' AND
            `file_id`='".$this->file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(5);
        }
        else {
            uFunc::rmdir($this->uCore->mod.'/form_files/'.site_id.'/'.$this->form_id);
            if(!$this->uCore->query('uForms',"DELETE FROM
            `u235_forms_files`
            WHERE
            `form_id`='".$this->form_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(6);
        }
        echo "{'status' : 'done','file_id':'".$this->file_id."'}";
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->delFile();
    }
}
$uForms=new uForms_form_admin_file_delete($this);
