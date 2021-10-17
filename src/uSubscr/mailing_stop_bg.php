<?php
class uSubscr_mailing_stop {
    private $uCore,$m_id;
    private function check_data() {
        if(!isset($_POST['m_id'])) $this->uCore->error(1);
        $this->m_id=$_POST['m_id'];
        if(!uString::isDigits($this->m_id)) $this->uCore->error(2);
    }
    private function stop_mailing() {
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_mailing`
        SET
        `status`='stopped',
        `timestamp`='".time()."'
        WHERE
        `m_id`='".$this->m_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die('forbidden');

        $this->check_data();

        $this->stop_mailing();
        echo 'done';
    }
}
$uSubscr=new uSubscr_mailing_stop($this);
