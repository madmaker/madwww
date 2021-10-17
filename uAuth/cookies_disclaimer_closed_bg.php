<?php
namespace uAuth;
use uSes;

require_once "processors/uSes.php";

class cookies_disclaimer_closed_bg {
    private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->uSes->set_val("cookies_disclaimer_closed",1);
    }
}
new cookies_disclaimer_closed_bg($this);