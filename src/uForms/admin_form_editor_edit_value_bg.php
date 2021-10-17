<?php
class uForms_admin_form_editor_edit_value_bg {
    private $uCore,$value_id,$label,$pos;

    private function check_data() {
        if(!isset($_POST['value_id'],$_POST['label'],$_POST['pos'])) $this->uCore->error(1);
        $this->value_id=$_POST['value_id'];
        $this->label=$_POST['label'];
        $this->pos=$_POST['pos'];
        if(!uString::isDigits($this->value_id)) $this->uCore->error(2);
        if(!uString::isDigits($this->pos)) $this->uCore->error(3);
    }
    private function update_value() {
        if(!$this->uCore->query("uForms","UPDATE
        `u235_selectbox_values`
        SET
        `label`='".uString::text2sql($this->label)."',
        `pos`='".$this->pos."'
        WHERE
        `value_id`='".$this->value_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_value();

        //clear cache
        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->value_id2form_id($this->value_id);
        $uForms->clear_cache($form_id);

        echo "{
        'status' : 'done',
        'value_id' : '".$this->value_id."',
        'label' : '".rawurlencode($this->label)."',
        'pos' : '".$this->pos."'
        }";
    }
}
$newClass=new uForms_admin_form_editor_edit_value_bg($this);
