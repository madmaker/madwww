<?php
class uForms_admin_form_editor_field_del_value {
    private $uCore,$value_id;

    private function check_data() {
        if(!isset($_POST['value_id'])) $this->uCore->error(1);
        $this->value_id=$_POST['value_id'];
        if(!uString::isDigits($this->value_id)) $this->uCore->error(2);
    }
    private function del_value() {
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_selectbox_values`
        WHERE
        `value_id`='".$this->value_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();

        //clear cache
        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->value_id2form_id($this->value_id);

        $this->del_value();

        $uForms->clear_cache($form_id);

        echo "{'status' : 'done', 'value_id' : '".$this->value_id."'}";
    }
}
$newClass=new uForms_admin_form_editor_field_del_value($this);
