<?php
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "dataairbag/classes/dataairbag.php";

class fileUploaded_bg {
    /**
     * @var int
     */
    private $status;
    private $timestamp;
    /**
     * @var int
     */
    private $file_id;
    /**
     * @var int
     */
    private $birthtime;
    /**
     * @var int
     */
    private $ctime;
    /**
     * @var int
     */
    private $mtime;
    /**
     * @var int
     */
    private $size;
    /**
     * @var int
     */
    private $type;
    private $fileName;
    /**
     * @var int
     */
    private $parentInode;
    /**
     * @var int
     */
    private $host_id;
    /**
     * @var int
     */
    private $inode;
    /**
     * @var dataairbag
     */
    private $dataairbag;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset(
            $_POST["hash"],
            $_POST["key"],
            $_POST["inode"],
            $_POST["parentInode"],
            $_POST["fileName"],
            $_POST["type"],
            $_POST["size"],
            $_POST["mtime"],
            $_POST["ctime"],
            $_POST["birthtime"]
        )) {
            print json_encode(array(
               "status"=>"error",
               "msg"=>"have not received required data"
            ));
            exit;
        }

        if(!$this->host_id=$this->dataairbag->hostHashKey2hostId($_POST["hash"],$_POST["key"])) {
            print json_encode(array(
                "status"=>"error",
                "msg"=>"wrong credentials"
            ));
            exit;
        }
        $this->inode=(int)$_POST["inode"];
        $this->parentInode=(int)$_POST["parentInode"];
        $this->fileName=$_POST["fileName"];
        $this->type=(int)$_POST["type"];
        if($this->type<0||$this->type>2) {
            print json_encode(array(
                "status"=>"error",
                "msg"=>"wrong type"
            ));
            exit;
        }
        $this->size=(int)$_POST["size"];
        $this->mtime=(int)$_POST["mtime"];
        $this->ctime=(int)$_POST["ctime"];
        $this->birthtime=(int)$_POST["birthtime"];

    }

    private function registerFileVersion() {
        //get last file version
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            version 
            FROM 
            file_versions 
            WHERE 
            inode=:inode AND
            host_id=:host_id
            ORDER BY 
            version DESC LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $this->inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/,1);}

        //register version
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $version=$qr->version+1;
        else $version=0;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("INSERT INTO 
            file_versions (
            host_id, 
            file_id, 
            version,
            timestamp, 
            inode, 
            parent_inode, 
            fileName, 
            size, 
            mtime, 
            ctime, 
            birthtime
            ) VALUES (
            :host_id, 
            :file_id, 
            :version,
            :timestamp, 
            :inode, 
            :parent_inode, 
            :fileName, 
            :size, 
            :mtime, 
            :ctime, 
            :birthtime                                                                                                                  
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $this->file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':version', $version,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $this->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $this->inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parent_inode', $this->parentInode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':fileName', $this->fileName,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $this->size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mtime', $this->mtime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ctime', $this->ctime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':birthtime', $this->birthtime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'.$e->getMessage(),1);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE 
                    files 
                    SET 
                    size=:size,
                    mtime=:mtime,
                    ctime=:ctime,
                    fileName=:fileName,
                    parent_inode=:parent_inode,
                    lastUploadTimestamp=:lastUploadTimestamp
                    WHERE
                    host_id=:host_id AND 
                    inode=:inode
                    ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $this->size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mtime', $this->mtime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ctime', $this->ctime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':fileName', $this->fileName,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parent_inode', $this->parentInode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lastUploadTimestamp', $this->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $this->inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}

        return $version;
    }

    private function registerNewFile() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("INSERT INTO 
                files (
                host_id, 
                inode, 
                parent_inode, 
                fileName, 
                type, 
                status, 
                size, 
                mtime, 
                ctime, 
                birthtime,
                lastUploadTimestamp
                ) VALUES (
                :host_id, 
                :inode, 
                :parent_inode, 
                :fileName, 
                :type, 
                3, 
                :size, 
                :mtime, 
                :ctime, 
                :birthtime,
                :lastUploadTimestamp
                )
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $this->host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $this->inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parent_inode', $this->parentInode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':fileName', $this->fileName,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type', $this->type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $this->size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mtime', $this->mtime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ctime', $this->ctime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':birthtime', $this->birthtime,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lastUploadTimestamp', $this->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
        $this->dataairbag=new dataairbag($this->uCore);

        $this->check_data();
        $this->timestamp=time();

        if(!$file_data=$this->dataairbag->file_inode2data($this->inode,$this->host_id,"file_id,status")) {
            $this->registerNewFile();//записываем новый файл
            $file_data=$this->dataairbag->file_inode2data($this->inode,$this->host_id,"file_id,status");
        }

        $this->file_id=(int)$file_data->file_id;
        $this->status=(int)$file_data->status;

        if ($this->type === 0) {
            //Нужно перекинуть файл из FTP-папки
            if (!$qr = $this->dataairbag->hostId2Data($this->host_id, "ftpusername")) {
                $this->uFunc->error(40, 1);
            }
            $ftpusername = $qr->ftpusername;

            if($this->status>=0&&$this->status<4) {
                $version = $this->registerFileVersion();

                $destPath = 'dataairbag/uploads/' . $this->host_id . '/' . $this->inode;
                if (!file_exists($destPath)) if (!mkdir($destPath, 0755, true)) {
                    print json_encode(array(
                        "status" => "error",
                        "msg" => "could not create dest dir"
                    ));
                    exit;
                }

                if (!rename('ftp/' . $ftpusername . '/' . $this->inode, $destPath . '/' . $version)) {
                    print json_encode(array(
                        "status" => "error",
                        "msg" => "could not move file"
                    ));
                    exit;
                }
            }
            else {
                unlink('ftp/' . $ftpusername . '/' . $this->inode);
            }
        }

        print json_encode(array(
            "status"=>"success"
        ));
    }
}
new fileUploaded_bg($this);
