<?php
class uForms_cron_rec_cleaner {
    private $uCore,$secret,$form_lifetime;
    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }
    private function clean() {
        if(!$this->uCore->query("uForms","DELETE FROM
        `u235_records`
        WHERE
        `rec_status`='new' AND
        `rec_timestamp`<'".(time()-$this->form_lifetime)."'
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='HJlds9872kljdfglxv65';
        $this->form_lifetime=10800;//3hours

        $this->check_data();
        $this->clean();
        echo 'done';
    }
}
$uForms=new uForms_cron_rec_cleaner($this);
