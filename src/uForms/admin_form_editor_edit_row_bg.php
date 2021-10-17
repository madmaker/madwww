<?php
class uForms_admin_form_editor_edit_row_bg {
    private $uCore,$form_id,$row_id,$row_header,$row_descr,$row_pos;
    private function check_data() {
        if(!isset($_POST['form_id'],$_POST['row_id'],$_POST['row_header'],$_POST['row_descr'],$_POST['row_pos'])) $this->uCore->error(1);
        $this->form_id=$_POST['form_id'];
        $this->row_id=$_POST['row_id'];
        $this->row_header=$_POST['row_header'];
        $this->row_descr=$_POST['row_descr'];
        $this->row_pos=$_POST['row_pos'];

        if(!uString::isDigits($this->form_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->row_id)) $this->uCore->error(3);
        if(!uString::isDigits($this->row_pos)) $this->uCore->error(4);
    }
    private function update_row() {
        if(!$this->uCore->query("uForms","UPDATE
        `u235_rows`
        SET
        `row_header`='".uString::text2sql($this->row_header)."',
        `row_descr`='".uString::text2sql($this->row_descr)."',
        `row_pos`='".$this->row_pos."'
        WHERE
        `form_id`='".$this->form_id."' AND
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_row();

        //clear cache
        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $uForms->clear_cache($this->form_id);

        echo "{'status' : 'done', 'row_id' : '".$this->row_id."', 'row_header' : '".rawurlencode($this->row_header)."', 'row_descr' : '".rawurlencode($this->row_descr)."', 'row_pos' : '".$this->row_pos."'}";
    }
}
$newClass=new uForms_admin_form_editor_edit_row_bg ($this);
