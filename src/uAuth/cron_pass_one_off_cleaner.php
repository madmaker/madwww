<?php
namespace uAuth\cron;
use PDO;
use PDOException;
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

class cron_pass_one_off_cleaner {
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var string
     */
    private $secret;
    private function check_data() {
        if(!isset($_POST['uSecret'])) {
            $this->uFunc->error(10);
        }
        if($this->secret!==$_POST['uSecret']) {
            $this->uFunc->error(20);
        }
    }
    private function clean_passwords() {
        //DELETE Old passwords
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare('DELETE
            FROM
            pass_one_off
            WHERE
            timestamp<:timestamp
            ');
            $timestamp=time()-300;//5 minutes
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);


        /** @noinspection SpellCheckingInspection */
        $this->secret='HKLJS^f78w6874h2kjHY&*^8o2h3jknrk.weyf78YIUh2k.3hrw67es8yHJLHKL298737r9pujLKAJL:SKhs]19[-i';

        $this->check_data();

        $this->clean_passwords();
    }
}
new cron_pass_one_off_cleaner($this);
