<?php
namespace uCat;
ini_set("memory_limit","256M");
set_time_limit(50000);
header("Connection: close");
ignore_user_abort(true);
ob_start();
ob_end_flush(); // All output buffers must be flushed here
flush();

use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class import {
    public $site_id, $item_id, $unit_id, $sect_id, $cat_id, $filepath, $datapost, $columns, $extension, $delimiter, $separat, $lines_to_skip, $column_name, $result_obj, $item_action_flag, $cat_action_flag, $sect_action_flag, $unit_action_flag;

    private $uCore, $uFunc, $uCat, $uSes, $error, $error_code;


    public function data_preparation() {
        $this->uCat->save_import_file_to_db($this->filepath,$this->lines_to_skip,$this->extension,json_encode($this->columns),$this->delimiter);
    }

    public function create_obj() {

    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        $this->uSes = new uSes($this->uCore);
        $this->uCat = new common($this->uCore);

        $this->site_id = site_id;
        $this->error = false;
        $this->error_code = 0;
        $this->filepath = $_SESSION['filepath'];
        $_SESSION['filepath'] = "";

        if(isset($_POST["data"])) {
            $this->delimiter = $_POST["data"]["delimiter"];
            $this->lines_to_skip = (int)$_POST["data"]["skip"];
            $this->columns = $_POST["data"]["columns"];
            $this->extension = $_POST["data"]["extension"];
            $this->data_preparation();
        }
        echo json_encode(array("status"=>"done"));
    }
}
new import($this);
