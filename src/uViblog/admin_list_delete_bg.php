<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uViblog_admin_list_delete_bg {
    private $uCore,$uFunc,$helper;
    private $status,$q_item_count;

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(4)) die('forbidden');

        $this->checkData();
        $this->run();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
    private function checkData() {
        if(!isset($_POST['ids'],$_POST['action'])) $this->uCore->error(1);
        $action=&$_POST['action'];
        if($action=='delete') {
            $this->status="'deleted'";
        }
        else {
            $this->status='NULL';
        }
    }
    private function update_videoStatus($ids_list) {
        if(!$this->uCore->query("uViblog","UPDATE
        `u235_list`
        SET
        `video_status`=".$this->status."
        WHERE
        (".$ids_list.") AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
    }

    private function run() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        for($i=1;$i<$ids_count;$i++) {
            $cat_id=$idsAr[$i];
            if(!uString::isDigits($cat_id)) $this->uCore->error(10);
            $ids_list.=" OR `video_id`='".$cat_id."'";
        }
        //update status to new
        $this->update_videoStatus($ids_list);
        echo 'done';
    }
}
$uCat=new uViblog_admin_list_delete_bg ($this);
