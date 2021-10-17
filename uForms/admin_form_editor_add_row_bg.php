<?php
namespace uForms\admin;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor_add_row_bg {
    public $uFunc;
    public $uSes;
    public $uForms;
    private $uCore,$form_id;

    private function check_data() {
        if(!isset($_POST['form_id'])) $this->uFunc->error(10);
        $this->form_id=$_POST['form_id'];
        if(!uString::isDigits($this->form_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);
        
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();

        $row=$this->uForms->create_row($this->form_id);
        if(!is_array($row)) $this->uFunc->error(30);

        //clear cache
        $this->uForms->clear_cache($this->form_id);

        echo "{
        'status' : 'done',
        'row_id' : '".$row['row_id']."',
        'row_pos' : '".$row['row_pos']."'
        }";
    }
}
new admin_form_editor_add_row_bg($this);