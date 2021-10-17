<?php
class uForms_admin_form_editor_add_value_bg {
    private $uCore,$field_id,$label,$value_id,$pos;

    private function check_data() {
        if(!isset($_POST['field_id'],$_POST['label'])) $this->uCore->error(1);
        $this->field_id=$_POST['field_id'];
        $this->label=uString::text2sql($_POST['label']);
        if(!uString::isDigits($this->field_id)) $this->uCore->error(2);
    }
    private function get_last_pos() {
        if(!$query=$this->uCore->query("uForms","SELECT
        `pos`
        FROM
        `u235_selectbox_values`
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ORDER BY `pos` DESC
        LIMIT 1
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) $this->value_pos=0;
        else {
            $value=$query->fetch_object();
            $this->pos=$value->pos;
        }
    }
    private function add_value() {
        //get id for new value
        if(!$query=$this->uCore->query("uForms","SELECT
        `value_id`
        FROM
        `u235_selectbox_values`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `value_id` DESC
        LIMIT 1
        ")) $this->uCore->error(4);
        if(mysqli_num_rows($query)) {
            $val=$query->fetch_object();
            $this->value_id=$val->value_id+1;
        }
        else $this->value_id=1;

        //ad new value
        if(!$this->uCore->query("uForms","INSERT INTO
        `u235_selectbox_values` (
        `value_id`,
        `field_id`,
        `label`,
        `pos`,
        `site_id`
        ) VALUES (
        '".$this->value_id."',
        '".$this->field_id."',
        '".$this->label."',
        '".$this->pos."',
        '".site_id."'
        )
        ")) $this->uCore->error(5);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_last_pos();
        $this->add_value();

        //clear cache
        include_once 'inc/common.php';
        $uForms=new uForms($this->uCore);
        $form_id=$uForms->field_id2form_id($this->field_id);
        $uForms->clear_cache($form_id);

        echo "{
        'status' : 'done',
        'value_id' : '".$this->value_id."',
        'field_id' : '".$this->field_id."',
        'label' : '".uString::sql2text($this->label)."',
        'pos' : '".$this->pos."'
        }";
    }
}
$newClass=new uForms_admin_form_editor_add_value_bg ($this);
