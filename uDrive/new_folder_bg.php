<?php
use processors\uFunc;
use uDrive\common;

require_once "uDrive/classes/common.php";
require_once "processors/classes/uFunc.php";
require_once 'processors/uSes.php';
class uDrive_new_folder {
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
        $folder_id=$this->uDrive->create_folder($this->folder_name,$this->folder_id);


        echo '{
        "status":"done",
        "file_id":"'.$folder_id.'",
        "file_name":"'.rawurlencode($this->folder_name).'",
        "file_size":"0",
        "file_mime":"folder",
        "file_ext_icon":"icon-folder",
        "file_timestamp":"'.time().'",
        "folder_id":"'.$this->folder_id.'"
        }';
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uDrive=new common($this->uCore);

        if(!$this->uSes->access(1900)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->create_folder();
    }
}
$uDrive=new uDrive_new_folder($this);