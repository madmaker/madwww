<?php
class uSubscr_rec_delete_files {
    private $uCore,$rec_id,$file_ar,$records_ids;
    private function checkData() {
        if(!isset($_POST['files'])) $this->uCore->error(1);
        $this->file_ar=explode('#',$_POST['files']);
    }
    private function delete_files() {
        $this->records_ids='';
        for($i=1;$i<count($this->file_ar);$i++) {
            $file_id=$this->file_ar[$i];
            if(!uString::isDigits($this->file_ar[$i])) continue;
            //get rec_id of this file
            if(!$query=$this->uCore->query("uSubscr","SELECT
            `rec_id`
            FROM
            `u235_records_files`
            WHERE
            `file_id`='".$file_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(2);
            if(!mysqli_num_rows($query)) continue;
            $rec=$query->fetch_object();
            $this->rec_id=$rec->rec_id;
                //delete from fs
                //echo $this->uCore->mod.'/'.site_id.'/'.$this->rec_id.'/'.$file_id;
                uFunc::rmdir($this->uCore->mod.'/files/'.site_id.'/'.$this->rec_id.'/'.$file_id);
                //delete from db
                if(!$this->uCore->query("uSubscr","DELETE FROM
                `u235_records_files`
                WHERE
                `file_id`='".$file_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(3);

                $this->records_ids.="'file_".$file_id."':'1',";
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->delete_files();
        echo "{".$this->records_ids." 'status' : 'done'}";
    }
}
new uSubscr_rec_delete_files($this);
