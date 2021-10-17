<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_add_domain {
    public $uFunc;
    public $uSes;
    private $uCore,$domain, $com_id;
    private function check_data() {
        if(!isset($_POST['domain'],$_POST['com_id'])) $this->uFunc->error(10);
        $this->domain=&$_POST['domain'];
        $this->com_id=&$_POST['com_id'];
        if(!uString::isDomain_name($this->domain)) die('{"status":"error","msg":"check domain name"}');
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(20);

        // check if company exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_id
            FROM
            u235_comps
            WHERE
            com_id=:com_id AND 
            site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    private function check_access() {
        //operator
        if($this->uSes->access(201)) return true;

        //check if client or admin of this company
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
            LIMIT 1
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
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        return false;
    }
    private function addDomain() {
        //check if domain already added to this company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            domain
            FROM
            u235_com_email_domains
            WHERE
            domain=:domain AND
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':domain', $this->domain,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) die('{"status":"done"}');
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        //get id for new domain
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            domain_id
            FROM
            u235_com_email_domains
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ORDER BY
            domain_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($domain=$stm->fetch(PDO::FETCH_OBJ)) $domain_id=$domain->domain_id+1;
            else $domain_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

        //add domain
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_com_email_domains (
            domain_id,
            domain,
            com_id,
            site_id
            ) VALUES (
            :domain_id,
            :domain,
            :com_id,
            :site_id
            )");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */$stm->bindParam(':domain_id', $domain_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':domain', $this->domain,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->check_data();

        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->addDomain();
        exit('{"status":"done"}');
    }
}
new company_info_add_domain ($this);