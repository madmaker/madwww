<?php
namespace uCat\admin;

use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_arts_create_bg {
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore,$art_title;
    private function check_data() {
        if(!isset($_POST['art_title'])) $this->uFunc->error(10);
        $this->art_title=trim($_POST['art_title']);
        if(empty($this->art_title)) die("{'status' : 'error', 'msg':'title is empty'}");
    }
    private function attach2item($art_id) {
        //attach art to item
        if(!isset($_POST['item_id'])) return 0;
        $item_id=$_POST['item_id'];
        if(!uString::isDigits($item_id)) return 0;

        //attach art to item
        $this->uCat->attach_art2item($item_id,$art_id);
        return 1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $art_id=$this->uCat->create_new_article($this->art_title);
        $this->attach2item($art_id);
        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo "{
        'status' : 'done',
        'art_id' : '".$art_id."'
        }";
    }
}
new admin_arts_create_bg($this);