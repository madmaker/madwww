<?php
class uSubscr_edit_title {
    private $uCore,$gr_id,$gr_title;
    private function check_data() {
        if(!isset($_POST['gr_id'],$_POST['gr_title'])) $this->uCore->error(1);
        if(!uString::isDigits($_POST['gr_id'])) $this->uCore->error(2);
        $this->gr_id=$_POST['gr_id'];

        $this->gr_title=$_POST['gr_title'];
    }
    private function update_rec() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_groups`
        SET
        `gr_title`='".uString::text2sql($this->gr_title)."'
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_rec();
        echo "{
        'status' : 'done',
        'gr_id' : '".$this->gr_id."',
        'gr_title' : '".rawurlencode($this->gr_title)."'
        }";
    }
}
$uSubscr=new uSubscr_edit_title($this);
