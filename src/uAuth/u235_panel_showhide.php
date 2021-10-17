<?php
namespace uAuth;
use uSes;

require_once "processors/uSes.php";

class u235_panel_showhide {
    public $uSes;
    public $value;
    private $uCore;

    private function check_data() {
        if(!isset($_POST['value'])) exit;
        $this->value=!(int)$_POST['value']?0:1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->check_data();
        $this->uSes->set_val("u235_panel_visible",$this->value);
    }
}
new u235_panel_showhide($this);