<?php
class uSupport_requests_cats_admin_new_cat_bg {
    private $uCore,$cat_id,$cat_title;
    private function checkData() {
        if(!isset($_POST['cat_title'])) $this->uCore->error(1);
        $this->cat_title=uString::text2sql($_POST['cat_title']);
        if(empty($this->cat_title)) die("{'status' : 'error', 'msg' : 'title_empty'}");
    }
    private function addCat() {
        //get new cat_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `cat_id`
        FROM
        `u235_requests_cats`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `cat_id` DESC
        LIMIT 1
        ")) $this->uCore->error(1);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $this->cat_id=$qr->cat_id+1;
        }
        else $this->cat_id=1;
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_requests_cats` (
        `cat_id`,
        `cat_title`,
        `site_id`
        ) VALUES (
        '".$this->cat_id."',
        '".$this->cat_title."',
        '".site_id."'
        )")) $this->uCore->error(2);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(22)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->addCat();
        echo "{'status' : 'done', 'cat_id':'".$this->cat_id."', 'cat_title':'".$this->cat_title."'}";
    }
}
$uSup=new uSupport_requests_cats_admin_new_cat_bg($this);
