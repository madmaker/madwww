<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_remove {
    public $uFunc;
    public $uSes;
    private $uCore,
        $com_id;

    private function check_data() {
        if(!isset($_POST['com_id'])) $this->uFunc->error(10);
        $this->com_id=$_POST['com_id'];
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(20);
        //check if company exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_id
            FROM
            u235_comps
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(\PDO::FETCH_OBJ)) return true;

        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        return false;
    }
    private function detach_requests_from_companies() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE 
            u235_requests
            SET
            company_id=0
            WHERE
            company_id=:company_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    private function remove_company() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("DELETE FROM
            u235_comps
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir('uSupport/com_avatars/'.site_id.'/'.$this->com_id);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        if(!$this->uSes->access(201)) die('{"status":"forbidden"}');//only for operator

        $this->check_data();

        $this->detach_requests_from_companies();
        $this->remove_company();

        echo '{"status":"done"}';
        exit;
    }
}
new company_remove ($this);