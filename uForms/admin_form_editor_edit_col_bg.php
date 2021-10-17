<?php
class uForms_admin_form_editor_edit_col_bg {
    private $uCore,$row_id,$col_id,$col_header,$col_descr,$col_pos;
    private function check_data() {
        if(!isset($_POST['col_id'],$_POST['col_header'],$_POST['col_descr'],$_POST['col_pos'])) $this->uCore->error(1);
        $this->col_id=$_POST['col_id'];
        $this->col_header=$_POST['col_header'];
        $this->col_descr=$_POST['col_descr'];
        $this->col_pos=$_POST['col_pos'];

        if(!uString::isDigits($this->col_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->col_pos)) $this->uCore->error(3);
    }
    private function get_row_id() {
        if(!$query=$this->uCore->query("uForms","SELECT
        `row_id`
        FROM
        `u235_columns`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        if(!mysqli_num_rows($query)) $this->uCore->error(5);
        $col=$query->fetch_object();
        $this->row_id=$col->row_id;
    }
    private function update_col() {
        if(!$this->uCore->query("uForms","UPDATE
        `u235_columns`
        SET
        `col_header`='".uString::text2sql($this->col_header)."',
        `col_descr`='".uString::text2sql($this->col_descr)."',
        `col_pos`='".$this->col_pos."'
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_row_id();
        $this->update_col();

        //clear cache
        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->row_id2form_id($this->row_id);
        $uForms->clear_cache($form_id);

        echo "{
        'status' : 'done',
        'row_id' : '".$this->row_id."',
        'col_id' : '".$this->col_id."',
        'col_header' : '".rawurlencode($this->col_header)."',
        'col_descr' : '".rawurlencode($this->col_descr)."',
        'col_pos' : '".$this->col_pos."'
        }";
    }
}
$newClass=new uForms_admin_form_editor_edit_col_bg  ($this);
