<?php
namespace uPage\admin;
use processors\uFunc;
use uPage\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";

class delete_row_template_bg {
    private $uPage;
    private $row_template_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["row_template_id"])) $this->uFunc->error(10);
        $this->row_template_id=(int)$_POST["row_template_id"];
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);

        $this->check_data();

        $this->uPage->delete_row_template($this->row_template_id,site_id);

        echo "{'status':'done'}";
        exit;
    }
}
new delete_row_template_bg($this);