<?php
class uEditor_cron_page_delete_empty_folders {
    private $uCore;
    private function check_data() {
        if(!isset($_POST['secret'])) $this->uCore->error(10);
        if($_POST['secret']!=$this->secret) $this->uCore->error(20);
    }
    private function delete_empty_folders() {
        //uEditor
        //SELECT all folders
        if(!$query=$this->uCore->query("pages","SELECT
        `page_id`,
        `site_id`
        FROM
        `u235_pages_html`
        WHERE
        `page_category`='folder' AND
        `page_timestamp`<".(time()-10800/*3 hours*/)."
        ")) $this->uCore->error(30);
        while($folder=$query->fetch_object()) {
            //check if this folder has any children
            if(!$query1=$this->uCore->query("pages","SELECT
            `page_id`
            FROM
            `u235_pages_html`
            WHERE
            `folder_id`='".$folder->page_id."' AND
            `site_id`='".$folder->site_id."'
            LIMIT 1
            ")) $this->uCore->error(40);
            if(!mysqli_num_rows($query1)) {//no children
                if(!$this->uCore->query("pages","DELETE FROM
                `u235_pages_html`
                WHERE
                `page_id`='".$folder->page_id."' AND
                `site_id`='".$folder->site_id."'
                ")) $this->uCore->error(50);
            }
            unset($query1);
        }
        unset($query);

        //uPage
        //SELECT all folders
        if(!$query=$this->uCore->query("uPage","SELECT
        `page_id`,
        `site_id`
        FROM
        `u235_pages`
        WHERE
        `page_type`='folder' AND
        `page_timestamp`<".(time()-10800/*3 hours*/)."
        ")) $this->uCore->error(60);
        while($folder=$query->fetch_object()) {
            //check if this folder has any children
            if(!$query1=$this->uCore->query("uPage","SELECT
            `page_id`
            FROM
            `u235_pages`
            WHERE
            `folder_id`='".$folder->page_id."' AND
            `site_id`='".$folder->site_id."'
            LIMIT 1
            ")) $this->uCore->error(70);
            if(!mysqli_num_rows($query1)) {//no children
                if(!$this->uCore->query("uPage","DELETE FROM
                `u235_pages`
                WHERE
                `page_id`='".$folder->page_id."' AND
                `site_id`='".$folder->site_id."'
                ")) $this->uCore->error(80);
            }
            unset($query1);
        }
        unset($query);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret="LKlksjdf8097324534jklk**9uoisjlkdf_)(*^s";

        $this->check_data();
        $this->delete_empty_folders();
    }
}
$uEditor=new uEditor_cron_page_delete_empty_folders($this);
