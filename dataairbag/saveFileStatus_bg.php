<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "dataairbag/classes/dataairbag.php";

class saveFileStatus_bg {
    private $newStatus;
    /**
     * @var dataairbag
     */
    private $dataairbag;
    /**
     * @var int
     */
    private $file_id;
    /**
     * @var int
     */
    private $host_id;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;
    private function checkData() {
        if(isset($_POST["host_id"],$_POST["file_id"],$_POST["newStatus"])) {
            if(!$this->uSes->access(2)) die("{'status' : 'forbidden'}");

            $this->host_id=(int)$_POST["host_id"];
            $this->file_id=(int)$_POST["file_id"];

            $user_id=$this->uSes->get_val("user_id");

            if(!$this->dataairbag->hostBelongs2User($this->host_id,$user_id)) {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"host is not found"
                ));
                exit;
            }

            if(!$this->dataairbag->file_id2data($this->file_id,$this->host_id)) {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"file is not found"
                ));
                exit;
            }

            if($_POST["newStatus"]=="backup") $this->newStatus=1;
            elseif($_POST["newStatus"]=="cancel") $this->newStatus=4;
            else {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"wrong new status"
                ));
                exit;
            }
        }
        elseif(isset($_POST["hash"],$_POST["key"],$_POST["inode"],$_POST["status"])) {
            if(!$this->host_id=$this->dataairbag->hostHashKey2hostId($_POST["hash"],$_POST["key"])) {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"haven't received data requested 1"
                ));
                exit;
            }
            if(!$file=$this->dataairbag->file_inode2data($_POST["inode"],$this->host_id)) {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"haven't received data requested 2"
                ));
                exit;
            }
            $this->file_id=(int)$file->file_id;

            $newStatus=(int)$_POST["status"];
            if($newStatus===3||$newStatus===6) $this->newStatus=$newStatus;
            else {
                print json_encode(array(
                    "status"=>"error",
                    "msg"=>"wrong new status"
                ));
                exit;
            }
        }
        else {
            print json_encode(array(
                "status"=>"error",
                "msg"=>"haven't received data requested 3"
            ));
            exit;
        }
    }

    private function updateFileStatus($host_id,$file_id,$status) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE 
            files
            SET
            status=:status
            WHERE
            file_id=:file_id AND
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->dataairbag=new dataairbag($this->uCore);

        $this->checkData();
        $this->updateFileStatus($this->host_id,$this->file_id,$this->newStatus);
        $this->dataairbag->sendNewFileStatuses($this->host_id);

        print json_encode(array(
            "status"=>"done",
            "file_id"=>$this->file_id,
            "newStatus"=>$this->newStatus
        ));
    }
}
new saveFileStatus_bg($this);
