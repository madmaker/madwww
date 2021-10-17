<?php
namespace uForms\admin;

use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_create_bg {
    public $uFunc;
    public $uSes;
    public $uForms;
    private $uCore,
        $form_title;

    private function checkData() {
        if(!isset($_POST['form_title'])) $this->uFunc->error(10);
        $this->form_title=uString::text2sql(trim($_POST['form_title']));
        if(empty($this->form_title)) die("{'status' : 'error', 'msg' : 'title is empty'}");
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);
        
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $form_id=$this->uForms->create_form($this->form_title,site_id);
        echo "{'status' : 'done', 'form_id' : '".$form_id."','form_title':'".rawurlencode(uString::sql2text($this->form_title))."'}";;
    }
}
new admin_form_create_bg($this);