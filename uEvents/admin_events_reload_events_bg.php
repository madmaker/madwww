<?php
namespace uEvents\admin;

use processors\uFunc;
use uEvents\events;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once  "uEvents/events.php";
class reload_events {
    private $uSes;
    private $uFunc;
    private $uCore;
    public $type_id;
    private function check_data() {
        if(!isset($_POST['type_id'])) $this->uFunc->error(10);
        $this->type_id=$_POST['type_id'];
        if(!uString::isDigits($this->type_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        if(!$this->uSes->access(300)) die("forbidden");
        $this->check_data();

        $cache_dir="uEvents/cache/events/".site_id."/".$this->type_id;

        if(!file_exists($cache_dir."/events_list.html")) {
            $setup_uEvents=new events($this->uCore);
            $setup_uEvents->type_id=$this->type_id;
            if($setup_uEvents->check_data()) $setup_uEvents->build_events_list_cache();
        }

        echo file_get_contents($cache_dir."/events_list.html");
    }
}
new reload_events($this);