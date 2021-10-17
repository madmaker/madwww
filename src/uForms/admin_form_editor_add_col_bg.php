<?php
namespace uForms\admin;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor_add_col_bg {
    public $uFunc;
    public $uSes;
    public $uForms;
    private $uCore,$row_id;

    private function check_data() {
        if(!isset($_POST['row_id'])) $this->uFunc->error(10);
        $this->row_id=$_POST['row_id'];
        if(!uString::isDigits($this->row_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);
        
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $col=$this->uForms->create_col($this->row_id,site_id);
        if(!is_array($col)) $this->uFunc->error(30);

        //clear cache
        $form_id=$this->uForms->row_id2form_id($this->row_id);
        $this->uForms->clear_cache($form_id);

        echo "{
        'status' : 'done',
        'row_id' : '".$this->row_id."',
        'col_id' : '".$col['col_id']."',
        'col_pos' : '".$col['col_pos']."'
        }";
    }
}
new admin_form_editor_add_col_bg($this);