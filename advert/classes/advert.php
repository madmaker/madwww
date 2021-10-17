<?php
namespace advert\common;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class advert {
    private $uSes;
    private $uFunc;
    private $uCore;
    public function book_new_ad_id($site_id=site_id) {
        if(!$this->uSes->access(2)) $this->uFunc->error("advert/10");
        $user_id=$this->uSes->get_val("user_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("advert")->prepare("INSERT INTO 
            adverts (
            site_id, user_id
            )
            VALUES (
            :site_id, 
            :user_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return (int)$this->uFunc->pdo("advert")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('advert/20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_ad_data_of_current_user($ad_id,$select_data='ad_id',$status_query='status=0',$site_id=site_id) {
        if(!$user_id=$this->uSes->get_val("user_id")) $this->uFunc->error('advert/30');
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("advert")->prepare("SELECT 
            ".$select_data." 
            FROM 
            adverts 
            WHERE
            ad_id=:ad_id AND
            user_id=:user_id AND 
            site_id=:site_id AND
            (".$status_query.")
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ad_id', $ad_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('advert/40'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
    }
}