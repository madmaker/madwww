<?php
class uSup_requests_cats_admin_edit{
    private $uCore,$cat_id;
    private function check_data() {
        if(!isset($_POST['cat_id'])) $this->uCore->error(1);
        $this->cat_id=$_POST['cat_id'];
        if(!uString::isDigits($this->cat_id)) $this->uCore->error(2);
    }
    private function edit_cat_save() {
        if(!isset($_POST['cat_title'])) $this->uCore->error(3);
        $cat_title=trim($_POST['cat_title']);
        if(!strlen($cat_title)) die("{'status':'error','msg':'title is empty'}");

        if(!$this->uCore->query('uSup',"UPDATE
        `u235_requests_cats`
        SET
        `cat_title`='".uString::text2sql($cat_title)."'
        WHERE
        `cat_id`='".$this->cat_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);

        echo '{
        "status":"done",
        "cat_id":"'.$this->cat_id.'",
        "cat_title":"'.rawurlencode($cat_title).'"
        }';
        exit;
    }
    private function delete_cat() {
        if(!$this->uCore->query('uSup',"DELETE FROM
        `u235_requests_cats`
        WHERE
        `cat_id`='".$this->cat_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);

        //unattach cat_id from requests
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `cat_id`='0'
        WHERE
        `tic_cat`='".$this->cat_id."' AND
        `site_id`='".site_id."'
        "))

        echo '{
        "status":"done"
        }';
        exit;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(8)&&!$this->uCore->access(9)) die('{"status":"forbidden"}');

        $this->check_data();
        if(isset($_POST['edit_cat'])) $this->edit_cat_save();
        elseif(isset($_POST['delete_cat'])) $this->delete_cat();
        else die('{"status":"forbidden"}');
    }
}
$uSup=new uSup_requests_cats_admin_edit($this);

