<?php
namespace obooking;
use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class get_clients_bg{
    private $obooking;
    private $uFunc;
    private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
        $this->obooking=new common($this->uCore);

        $show_select_btn=0;
        if(isset($_POST["show_select_btn"])) {
            $show_select_btn=(int)$_POST["show_select_btn"];
            $show_select_btn=(int)!!$show_select_btn;
        }

        $this->obooking->clients_list($show_select_btn);
    }
}
new get_clients_bg($this);