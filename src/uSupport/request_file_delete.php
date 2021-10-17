<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_request_file_delete {
    public $uFunc;
    public $uSes;
    private $uCore,$msg_id2found,$tic_id2found;

    private function check_access($file_id) {
        //get msg_id of this file
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`,
        `tic_id`
        FROM
        `u235_msgs_files`
        WHERE
        `file_id`='".$file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
        if(!mysqli_num_rows($query)) return false;//file is not found in db
        $file=$query->fetch_object();

        //we can delete file only from user's tmp request or tmp msg
        if($file->msg_id!='0') {
            if(!isset($this->msg_id2found[$file->msg_id])) {
                $this->msg_id2found[$file->msg_id]=false;

                if(!$query=$this->uCore->query("uSup","SELECT
                `msg_id`
                FROM
                `u235_msgs`
                WHERE
                `msg_id`='".$file->msg_id."' AND
                `msg_status`='0' AND
                `msg_sender`='".$this->uSes->get_val("user_id")."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(2);
                if(mysqli_num_rows($query)) $this->msg_id2found[$file->msg_id]=true;
            }
            return $this->msg_id2found[$file->msg_id];
        }
        else {
            if(!isset($this->msg_id2found[$file->msg_id])) {
                $this->tic_id2found[$file->tic_id]=false;
                if(!$query=$this->uCore->query("uSup","SELECT
                `tic_id`
                FROM
                `u235_requests`
                WHERE
                `tic_id`='".$file->tic_id."' AND
                `tic_status`='new' AND
                `user_id`='".$this->uSes->get_val("user_id")."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(3);
                if(mysqli_num_rows($query)) $this->tic_id2found[$file->tic_id]=true;
            }
            return $this->tic_id2found[$file->tic_id];
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        for($i=0;isset($_POST['file_'.$i.'_id']);$i++) {
            if(uString::isDigits ($_POST['file_'.$i.'_id'])) {
                $file_id=&$_POST['file_'.$i.'_id'];

                if($tic_id=$this->check_access($file_id)) {

                    if(!$this->uCore->query("uSup","DELETE FROM
                    `u235_msgs_files`
                    WHERE
                    `file_id`='".$file_id."' AND
                    `site_id`='".site_id."'
                    ")) $this->uCore->error(4);

                    @uFunc::rmdir("uSupport/msgs_files/".site_id.'/'.$tic_id.'/'.$file_id);
                }
            }
        }

        echo "{'status':'done'}";
    }
}
$uSup=new uSup_request_file_delete($this);
