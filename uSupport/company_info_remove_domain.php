<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_remove_domain {
    public $uFunc;
    public $uSes;
    private $uCore, $domain_id, $com_id;
    private function check_data() {
        if(!isset($_POST['domain_id'],$_POST['com_id'])) $this->uFunc->error(10);
        $this->domain_id=&$_POST['domain_id'];
        $this->com_id=&$_POST['com_id'];
        if(!uString::isDigits($this->domain_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(30);
    }
    private function check_access() {
        //operator
        if($this->uSes->access(201)) return true;

        //check if current user is admin of this company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            com_id=:com_id AND
            admin=1 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        return false;
    }
    private function delDomain() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("DELETE FROM
            u235_com_email_domains
            WHERE
            domain_id=:domain_id AND
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':domain_id', $this->domain_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->delDomain();
        exit('{"status":"done"}');
    }
}
new company_info_remove_domain ($this);