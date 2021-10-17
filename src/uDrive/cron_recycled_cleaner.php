<?php
class uDrive_cron_recycled_cleaner {
    private $uCore,$secret,
    $start_time,$lifetime;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }
    private function delete_file($file) {
        if($this->start_time+$this->lifetime<time()) exit;

        if($file->file_mime=='folder') {//check if folder is empty
            $query=$this->get_folder_files($file);
            if(mysqli_num_rows($query)) {//clean folder's files
                $this->clean_folder_files($query);
            }
        }

        //DELETE FILE FROM DB
        if(!$this->uCore->query("uDrive","DELETE FROM
            `u235_files`
            WHERE
            `file_id`='".$file->file_id."' AND
            `site_id`='".$file->site_id."'
            ")) $this->uCore->error(3);

        //DELETE FILE FROM FS
        //check if files with same hashname exists on this site
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`
        FROM
        `u235_files`
        WHERE
        `file_hashname`='".$file->file_hashname."' AND
        `site_id`='".$file->site_id."'
        LIMIT 1
        ")) $this->uCore->error(4);
        if(!mysqli_num_rows($query)) uFunc::rmdir('uDrive/files/'.$file->site_id.'/'.$file->file_id);

    }
    private function clean_folder_files($query) {
        while($file=$query->fetch_object()) {
            $this->delete_file($file);
        }
    }
    private function get_folder_files($file) {
        //get last 100 deleted files of folder
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `site_id`,
        `file_mime`,
        `file_hashname`
        FROM
        `u235_files`
        WHERE
        `folder_id`='".$file->file_id."' AND
        `site_id`='".$file->site_id."'
        LIMIT 100
        ")) $this->uCore->error(5);
        return $query;
    }
    private function clean() {
        //get last 200 deleted files
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`,
        `site_id`,
        `file_mime`,
        `file_hashname`
        FROM
        `u235_files`
        WHERE
        `deleted`='2'
        LIMIT 200
        ")) $this->uCore->error(6);
        while($file=$query->fetch_object()) {
            $this->delete_file($file);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='HNJKsfyisdo2778923423lHJs';
        $this->start_time=time();
        $this->lifetime=20;//20 seconds

        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->check_data();
        $this->clean();
    }
}
$uDrive=new uDrive_cron_recycled_cleaner ($this);
