<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_delete_record {
    public $uFunc;
    public $uSes;
    private $uCore,$rec_id;
    private function check_data() {
        if(!isset($_POST['rec_id'])) $this->uCore->error(1);
        $this->rec_id=$_POST['rec_id'];
        if(!uString::isDigits($this->rec_id)) $this->uCore->error(2);
    }
    private function check_access() {
        if($this->uCore->access(38)) return true;
        if($this->uCore->access(33)) {
            //check if user is owner of this record
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_id`
            FROM
            `u235_records`
            WHERE
            `rec_id`='".$this->rec_id."' AND
            `user_id`='".$this->uSes->get_val("user_id")."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(3);
            if(mysqli_num_rows($query)) return true;
        }
        return false;
    }
    private function delete_record() {
        //delete record's files
        @uFunc::rmdir($this->uCore->mod.'/'.site_id.'/'.$this->rec_id);
        if(!$this->uCore->query("uKnowbase","DELETE FROM
        `u235_records_files`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        //delete record
        if(!$this->uCore->query("uKnowbase","DELETE FROM
        `u235_records`
        WHERE
        `rec_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        //delete request-solution link
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_uKnowbase_solutions_requests`
        WHERE
        `sol_id`='".$this->rec_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->check_data();
        if(!$this->check_access()) die("{'status' : 'forbidden'}");

        $this->delete_record();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{'status' : 'done'}";
    }
}
$uKnowbase=new uKnowbase_delete_record($this);
