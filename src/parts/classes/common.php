<?php
namespace parts;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class common {
    private $uFunc;
    private $uSes;
    private $uCore;
    public function get_search_result($search_id) {
        $ses_id=$this->uSes->get_val("ses_id");

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("parts")->prepare("SELECT 
            result 
            FROM 
            searches 
            WHERE
            search_id=:search_id AND
            ses_id=:ses_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':search_id', $search_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->result;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 0;
    }

    private function get_search_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("parts")->prepare("SELECT 
            search_id 
            FROM 
            searches
            order by search_id DESC LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(\PDO::FETCH_OBJ)) return $qr->search_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 1;
    }
    private function delete_old_searches() {
        $old_timestamp=time()-86400;//1day;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("parts")->prepare("DELETE FROM searches
            WHERE
            timestamp<:old_timestamp
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':old_timestamp', $old_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    public function save_result($result,$site_id=site_id) {
//        $result=json_encode($result);
//        $this->delete_old_searches();
//        $search_id=$this->get_search_id();
        if(!isset($_SESSION["parts"])) $_SESSION["parts"]=[];
        /*if(!isset($_SESSION["parts"]["searchResults"])) */$_SESSION["parts"]["searchResults"]=[];
        $search_id=count($_SESSION["parts"]["searchResults"]);
        $_SESSION["parts"]["searchResults"][$search_id]=$result;
//        $ses_id=$this->uSes->get_val("ses_id");
//        $timestamp=time();
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("parts")->prepare("INSERT INTO searches (
//            search_id,
//            site_id,
//            ses_id,
//            result,
//            timestamp
//            ) VALUES (
//            :search_id,
//            :site_id,
//            :ses_id,
//            :result,
//            :timestamp
//            )
//            ");
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':search_id', $search_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':result', $result,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return $search_id;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

    }
}