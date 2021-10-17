<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class company_info_remove_user{
    public $uFunc;
    public $uSes;
    private $uCore,
        $user_id, $com_id,$admin;
    private function check_data() {
        if(!isset($_POST['type'],$_POST['user_id'],$_POST['com_id'])) $this->uFunc->error(10);
        if($_POST['type']=='admin') $this->admin=1;
        else $this->admin=0;
        $this->user_id=&$_POST['user_id'];
        $this->com_id=&$_POST['com_id'];
        if(!uString::isDigits($this->user_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(30);
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
    private function delUser() {
        if($this->admin) {
            //downgrade admin to regular company's user
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
                u235_com_users
                SET
                admin=0,
                notify_about_new_requests=0
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        else {
            //delete user from com
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("DELETE FROM
                u235_com_users
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->delUser();
        exit('{"status":"done"}');
    }
}
new company_info_remove_user($this);