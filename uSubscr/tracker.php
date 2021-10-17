<?php
class uSubscr_tracker {
    private $uCore,$m_id,$user_id,$hash;
    private function check_data() {
        if(!isset($_GET['m_id'],$_GET['user_id'],$_GET['hash'])) return false;
        $this->m_id=$_GET['m_id'];
        $this->user_id=$_GET['user_id'];
        $this->hash=$_GET['hash'];

        if(!uString::isDigits($this->m_id)) return false;
        if(!uString::isDigits($this->user_id)) return false;
        if(!uString::isHash($this->hash)) return false;

        return true;
    }
    private function update_state() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing_results`
        SET
        `result`='read'
        WHERE
        `m_id`='".$this->m_id."' AND
        `user_id`='".$this->user_id."' AND
        `hash`='".$this->hash."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
    }
    private function file_output() {
        $filename='images/common/000000-0.png';
        if (!file_exists($filename)) die('');
        header('Content-Description: File Transfer');
        header('Content-Type: image/png');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline filename="px.png"');
        flush();
        readfile($filename);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if($this->check_data()) {
            $this->update_state();
        }
        $this->file_output();
    }
}
$uSubscr=new uSubscr_tracker($this);
