<?php
class uForms_admin_form_editor_delete_row_bg {
    private $uCore,$form_id,$row_id;

    private function check_data() {
        if(!isset($_POST['form_id'],$_POST['row_id'])) $this->uCore->error(1);
        $this->form_id=$_POST['form_id'];
        $this->row_id=$_POST['row_id'];
        if(!uString::isDigits($this->form_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->row_id)) $this->uCore->error(3);
    }
    private function del_row() {
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_rows`
        WHERE
        `form_id`='".$this->form_id."' AND
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);

        //get all cols dependent on this row
        if(!$query=$this->uCore->query("uForms","SELECT
        `col_id`
        FROM
        `u235_columns`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        while($col=$query->fetch_object()) {
            //get all fields dependent on every column dependent on this row
            if(!$query1=$this->uCore->query("uForms","SELECT
            `field_id`
            FROM
            `u235_fields`
            WHERE
            `col_id`='".$col->col_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(6);

            while($field=$query1->fetch_object()) {
                //delete all values dependent on every field dependent on every column dependent on this row
                if(!$this->uCore->query("uForms","DELETE FROM
                `u235_selectbox_values`
                WHERE
                `field_id`='".$field->field_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(7);
            }

            //delete all fields dependent on every column dependent on this row
            if(!$this->uCore->query("uForms","DELETE FROM
            `u235_fields`
            WHERE
            `col_id`='".$col->col_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(8);
        }
        //delete all cols dependent on this row
        if(!$query=$this->uCore->query("uForms","DELETE FROM
        `u235_columns`
        WHERE
        `row_id`='".$this->row_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(9);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();

        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->row_id2form_id($this->row_id);

        $this->del_row();

        //clear cache
        $uForms->clear_cache($form_id);

        echo "{'status' : 'done', 'row_id' : '".$this->row_id."'}";
    }
}
$newClass=new uForms_admin_form_editor_delete_row_bg($this);
