<?php
use processors\uFunc;
require_once 'processors/classes/uFunc.php';
class uAuth_cron_session_cleaner {
    private $uCore,$secret,$uFunc;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uFunc->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uFunc->error(2);
    }
    private function clean() {
        try {
            $stm = $this->uFunc->pdo("uSes")->prepare("DELETE 
            FROM
            u235_list
            WHERE
            `time`<:time_search
            ");

            $time_search = time()-$this->ses_lifetime;
            $stm->bindParam(':time_search', $time_search, PDO::PARAM_INT);
            $stm->execute();

            return true;
        }
        catch(PDOException $e) {$this->uFunc->error(3/*.$e->getMessage()*/);}

        return false;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='77PoP6InbmiMnF';
        $this->ses_lifetime=1209600;//14 days
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->clean();
    }
}
$uAuth=new uAuth_cron_session_cleaner ($this);