<?php
namespace uForms\admin;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor_add_field_bg {
    public $uFunc;
    public $uSes;
    public $uForms;
    private $uCore,$col_id,$form_id,$field_id;

    private function check_data() {
        if(!isset($_POST['col_id'])) $this->uFunc->error(10);
        $this->col_id=$_POST['col_id'];
        if(!uString::isDigits($this->col_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);
        
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $field=$this->uForms->create_field($this->col_id,site_id);
        if(!is_array($field)) $this->uFunc->error(40);

        //clear cache
        $this->form_id=$this->uForms->col_id2form_id($this->col_id);
        $this->uForms->clear_cache($this->form_id);
        $this->field_id=$field['field_id'];

        echo "{
        'status' : 'done',
        'field_id' : '".$this->field_id."',
        'field_pos' : '".$field['field_pos']."',
        'col_id' : '".$this->col_id."'
        }";
    }
}
new admin_form_editor_add_field_bg ($this);