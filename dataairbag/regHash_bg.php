<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class regHash {
    private $hash;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["hash"])) {
            print json_encode(array("status"=>"error","msg"=>"hash is not sent"));
            exit;
        }
        if(!\uString::isHash($_POST["hash"])) {
            print json_encode(array("status"=>"error","msg"=>"hash has wrong format"));
            exit;
        }
        $this->hash=$_POST["hash"];
    }

//    public function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }

    private function checkIfHashExists() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            `key` 
            FROM 
            hosts 
            WHERE 
            hash=:hash
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->key;
        }
        catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage(),1);}
        return false;
    }

    private function registerHash() {
        $key=$this->uFunc->genHash();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("INSERT INTO 
            hosts
            (
             hash,
             `key`,
             arch,
             hostname,
             platform,
             `release`,
             totalmem,
             type
             )
             VALUES (
             :hash,
             :key,
             :arch,
             :hostname,
             :platform,
             :release,
             :totalmem,
             :type
             )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':key', $key,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':arch', $_POST["arch"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hostname', $_POST["hostname"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':platform', $_POST["platform"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':release', $_POST["release"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':totalmem', $_POST["totalmem"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type', $_POST["type"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/,1);}
        return $key;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
//        $this->uSes=new uSes($this->uCore);
        //if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        if($this->checkIfHashExists()) {
            print json_encode(array("status"=>"error","msg"=>"hash exists"));
            exit;
        }

        $key=$this->registerHash();

        print json_encode(array(
            "status"=>"done",
            "key"=>$key
        ));

    }
}
new regHash($this);
