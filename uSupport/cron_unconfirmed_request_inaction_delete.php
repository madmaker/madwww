<?php
class uSup_unconfirmed_request_inaction_delete {
    private $uCore,$secret,
        $start_time,$time_limit,$stop_before_limit;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }

    private function delete_old_unconfirmed_requests() {
        //86400 is 24h
        $time=time()-86400;
        //get all tmp_requests
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_id`,
        `site_id`
        FROM
        `u235_requests`
        WHERE
        `tic_opened_timestamp`<".$time." AND
        `tic_confirmed`='0'
        ")) $this->uCore->error(3);

        //delete all unconfirmed requests from db
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_requests`
        WHERE
        `tic_opened_timestamp`<".$time." AND
        `tic_confirmed`='0'
        ")) $this->uCore->error(4);

        while($req=$query->fetch_object()) {//delete every tmp request's folder and msgs_files record
            if(!$this->uCore->query("uSup","DELETE FROM
            `u235_msgs_files`
            WHERE
            `tic_id`='".$req->tic_id."' AND
            `site_id`='".$req->site_id."'
            "));
            @uFunc::rmdir("uSupport/msgs_files/".$req->site_id.'/'.$req->tic_id);

            //get all messages of this request
            if(!$query1=$this->uCore->query("uSup","SELECT
            `msg_id`
            FROM
            `u235_msgs`
            WHERE
            `tic_id`='".$req->tic_id."' AND
            `site_id`='".$req->site_id."'
            ")) $this->uCore->error(5);

            //delete msgs of this request from db
            if(!$this->uCore->query("uSup","DELETE FROM
            `u235_msgs`
            WHERE
            `tic_id`='".$req->tic_id."' AND
            `site_id`='".$req->site_id."'
            ")) $this->uCore->error(6);

            while($msg=$query1->fetch_object()) {//delete every msg's file and msgs_files record of this request
                if(!$query2=$this->uCore->query("uSup","SELECT
                `file_id`
                FROM
                `u235_msgs_files`
                WHERE
                `msg_id`='".$msg->msg_id."' AND
                `tic_id`='".$req->tic_id."' AND
                `site_id`='".$req->site_id."'
                ")) $this->uCore->error(7);
                if(mysqli_num_rows($query2)) {
                    $msg_file=$query2->fetch_object();
                    if(!$this->uCore->query("uSup","DELETE FROM
                    `u235_msgs_files`
                    WHERE
                    `msg_id`='".$msg->msg_id."' AND
                    `tic_id`='".$req->tic_id."' AND
                    `site_id`='".$req->site_id."'
                    "));
                    @uFunc::rmdir("uSupport/msgs_files/".$req->site_id.'/'.$req->tic_id.'/'.$msg_file->file_id);
                }
            }
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='yrcl8Pbb6LvtQymJ$LVKIP6Q';
        $this->time_limit=30;
        $this->stop_before_limit=10;
        $this->start_time=time();

        $this->check_data();

        $this->delete_old_unconfirmed_requests();
    }
}
$uSup=new uSup_unconfirmed_request_inaction_delete($this);
