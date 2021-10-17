<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class checkKey {
    private $ftpuserpass;
    private $ftpusername;
    /**
     * @var int
     */
    private $user_id;
    private $status;
    private $host_id;
    private $key;
    private $hash;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset(
            $_POST["hash"],
            $_POST["key"]
        )) {
            print json_encode(array("status"=>"error","msg"=>"hash or key is not sent"));
            exit;
        }
        if(!\uString::isHash($_POST["hash"])) {
            print json_encode(array("status"=>"error","msg"=>"hash has wrong format"));
            exit;
        }
        if(!\uString::isHash($_POST["key"])) {
            print json_encode(array("status"=>"error","msg"=>"key has wrong format"));
            exit;
        }
        $this->hash=$_POST["hash"];
        $this->key=$_POST["key"];
    }


    private function checkKey() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT
            user_id,
            host_id,
            status,
            ftpusername,
            ftpuserpass
            FROM 
            hosts
            WHERE 
            hash=:hash AND
            `key`=:key
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $this->hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':key', $this->key,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->user_id=(int)$qr->user_id;
                $this->host_id=(int)$qr->host_id;
                $this->status=$qr->status;
                $this->ftpusername=$qr->ftpusername;
                $this->ftpuserpass=$qr->ftpuserpass;
                return 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage(),1);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        if(!$this->checkKey()) {
            print json_encode(array("status"=>"error","msg"=>"wrong hash and key"));
            exit;
        }

        if($this->status==="") {
            print json_encode(array(
                "status" => "done",
                "action" => "key registration needed"
            ));
            exit;
        }
        else if($this->status==="registered") {
            print json_encode(array(
                "status" => "done",
                "action" => "host is registered",
                "ftpusername"=>$this->ftpusername,
                "ftpuserpass"=>$this->ftpuserpass
            ));
            exit;
        }

        print json_encode(array(
            "status" => "error",
            "action" => "wrong key status"
        ));
        exit;

    }
}
new checkKey($this);
