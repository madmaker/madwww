<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_solution_delete_files {
    public $uFunc;
    public $uSes;
    private $uCore,$rec_id,$file_ar,$records_ids;
    private function checkData() {
        if(!isset($_POST['files'])) $this->uCore->error(10);
        $this->file_ar=explode('#',$_POST['files']);
    }
    private function check_access($rec_id) {
        if($this->uCore->access(38)) return true;//have right to edit any record

        if(!$query=$this->uCore->query("uKnowbase","SELECT
        `rec_id`
        FROM
        `u235_records`
        WHERE
        `rec_id`='".$rec_id."' AND
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
        if(mysqli_num_rows($query)) return true;//have rights to edit own record

        return false;
    }
    private function delete_files() {
        $this->records_ids='';
        for($i=1;$i<count($this->file_ar);$i++) {
            $file_id=$this->file_ar[$i];
            if(!uString::isDigits($this->file_ar[$i])) continue;
            //get rec_id of this file
            if(!$query=$this->uCore->query("uKnowbase","SELECT
            `rec_id`
            FROM
            `u235_records_files`
            WHERE
            `file_id`='".$file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(30);
            if(!mysqli_num_rows($query)) continue;
            $rec=$query->fetch_object();
            $this->rec_id=$rec->rec_id;
            if($this->check_access($this->rec_id)) {
                //delete from fs
                //echo $this->uCore->mod.'/'.site_id.'/'.$this->rec_id.'/'.$file_id;
                uFunc::rmdir($this->uCore->mod.'/files/'.site_id.'/'.$this->rec_id.'/'.$file_id);
                //delete from db
                if(!$this->uCore->query("uKnowbase","DELETE FROM
                `u235_records_files`
                WHERE
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(40);

                $this->records_ids.="'file_".$file_id."':'1',";
            }
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uCore->access(33)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->delete_files();
        echo "{".$this->records_ids." 'status' : 'done'}";
    }
}
$uKnowbase_solution_delete_files=new uKnowbase_solution_delete_files($this);
