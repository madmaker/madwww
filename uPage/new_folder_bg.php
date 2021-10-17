<?php
namespace uPage\admin;

use processors\uFunc;
use uPage\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once 'processors/uSes.php';
require_once "uPage/inc/common.php";
class new_folder {
    private $uPage;
    private $uFunc;
    private $uSes;
    private $uCore,$folder_name,$folder_id;
    private function check_data() {
        if(!isset($_POST['folder_name'],$_POST['folder_id'])) $this->uFunc->error(1);
        $this->folder_name=trim($_POST['folder_name']);

        $this->folder_name=trim(uString::sanitize_filename($this->folder_name));
        if(!strlen($this->folder_name)) $this->folder_name='_';

        $this->folder_id=$_POST['folder_id'];
        if(!uString::isDigits($this->folder_id)) $this->uFunc->error(2);
        $this->folder_id=(int)$this->folder_id;
    }
    private function create_folder() {
        $folder_id=$this->uPage->create_folder($this->folder_name,$this->folder_id);


        echo '{
        "status":"done",
        "page_id":"'.$folder_id.'",
        "page_title":"'.rawurlencode($this->folder_name).'",
        "page_alias":"",
        "deleted":"0",
        "page_type":"folder",
        "page_timestamp":"'.time().'",
        "folder_id":"'.$this->folder_id.'"
        }';
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        $this->create_folder();
    }
}
new new_folder($this);