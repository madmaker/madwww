<?php

use processors\uFunc;

require_once "processors/classes/uFunc.php";

class uForms_admin_form_editor_delete_field_bg {
    private $uCore,$field_id;

    private function check_data() {
        if(!isset($_POST['field_id'])) $this->uCore->error(1);
        $this->field_id=$_POST['field_id'];
        if(!uString::isDigits($this->field_id)) $this->uCore->error(2);
    }
    private function del_field() {
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_fields`
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_selectbox_values`
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }
    private function del_field_results() {
        try {
            $site_id = site_id;
            $del_fields = $this->uFunc->pdo("uForms")->prepare("DELETE
            FROM
            u235_form_results
            WHERE
            field_id=:field_id AND 
            site_id=:site_id
            ");

            /** @noinspection PhpUndefinedMethodInspection */
            $del_fields->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $del_fields->bindParam(':field_id', $this->field_id, PDO::PARAM_INT);
            $del_fields->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");
        $this->uFunc = new uFunc($this->uCore);

        $this->check_data();

        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->field_id2form_id($this->field_id);

        $this->del_field();
        $this->del_field_results();

        //clear cache
        $uForms->clear_cache($form_id);

        echo "{'status' : 'done', 'field_id' : '".$this->field_id."'}";
    }
}
$newClass=new uForms_admin_form_editor_delete_field_bg($this);
