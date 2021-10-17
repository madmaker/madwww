<?php
class uForms_admin_form_editor_delete_col_bg {
    private $uCore,$col_id;

    private function check_data() {
        if(!isset($_POST['col_id'])) $this->uCore->error(1);
        $this->col_id=$_POST['col_id'];
        if(!uString::isDigits($this->col_id)) $this->uCore->error(2);
    }
    private function del_col() {
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_columns`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);

        //get all fields dependent on this column
        if(!$query1=$this->uCore->query("uForms","SELECT
        `field_id`
        FROM
        `u235_fields`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        while($field=$query1->fetch_object()) {
            //delete all values dependent on every field dependent on this column
            if(!$this->uCore->query("uForms","DELETE FROM
            `u235_selectbox_values`
            WHERE
            `field_id`='".$field->field_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(5);
        }
        //delete all fields dependent on this column
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_fields`
        WHERE
        `col_id`='".$this->col_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();

        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->col_id2form_id($this->col_id);

        $this->del_col();

        //clear cache
        $uForms->clear_cache($form_id);

        echo "{'status' : 'done', 'col_id' : '".$this->col_id."'}";
    }
}
$newClass=new uForms_admin_form_editor_delete_col_bg($this);
