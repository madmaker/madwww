<?php
//TODO-nik87 Защитить ключем или хэшем общение между серверами
namespace dataairbag;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class dataairbag {
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uCore;

    public function checkOnline($hash,$key) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"http://node1.madwww.org:3031");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(array(
            "task"=>"checkIfHostIsOnline",
            "hash"=>$hash,
            "key"=>$key
        )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if(!$server_output = curl_exec($ch)) {
            curl_close ($ch);
            return "Server is offline";
        }
        curl_close ($ch);
        $res=json_decode($server_output);

        return $res->msg;
    }
    private function updateFileInDB($file,$parent_inode,$hostId) {
        if(!isset($file->stat)) return 0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            file_id
            FROM
            files
            WHERE
            inode=:inode AND 
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $file->stat->ino,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $hostId,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $fileId=(int)$qr->file_id;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE 
                files 
                SET
                fileName=:fileName,
                size=:size,
                mtime=:mtime,
                ctime=:ctime,
                birthtime=:birthtime,
                parent_inode=:parentInode
                WHERE
                file_id=:file_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':fileName', $file->fileName,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $file->stat->size,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mtime', $file->stat->mtimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ctime', $file->stat->ctimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':birthtime', $file->stat->birthtimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parentInode', $parent_inode,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $fileId,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }

        else {
            if($file->isFile) $type=0;
            elseif($file->isDirectory) $type=1;
            else $type=2;

            $status=0;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("dataairbag")->prepare("INSERT INTO 
                files 
                (
                 host_id, 
                 inode, 
                 parent_inode, 
                 fileName, 
                 type, 
                 status, 
                 size, 
                 mtime, 
                 ctime, 
                 birthtime
                ) VALUES (
                 :host_id, 
                 :inode, 
                 :parent_inode, 
                 :fileName, 
                 :type, 
                 :status, 
                 :size, 
                 :mtime, 
                 :ctime, 
                 :birthtime         
                )");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $hostId,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $file->stat->ino,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parent_inode', $parent_inode,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':fileName', $file->fileName,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type', $type,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $file->stat->size,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mtime', $file->stat->mtimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ctime', $file->stat->ctimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':birthtime', $file->stat->birthtimeMs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'.$e->getMessage());}
        }
        return 1;
    }
    public function getFilesFromFolder($hostId,$parentInode,$hash='',$key='') {
        if($hash===''||$key==='') {
            if(!$qr=$this->hostId2Data($hostId,"hash,`key`")) return "";
            $hash=$qr->hash;
            $key=$qr->key;
        }
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"http://node1.madwww.org:3031");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(array(
            "task"=>"getFilesFromFolder",
            "parentInode"=>$parentInode,
            "hash"=>$hash,
            "key"=>$key
        )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($server_output = curl_exec($ch)) {
            if($res=json_decode($server_output)) {
                if(isset($res->filesAr)) {
                    $filesAr=$res->filesAr;
                    foreach ($filesAr as $i => $file) {
                        $file = $filesAr[$i];
                        $this->updateFileInDB($file, $parentInode, $hostId);
                    }
                    //TODO-nik87 обновлять информацию о файлах, которые есть в базе, но их нет в списке с хоста
                }
            }
        }

        curl_close ($ch);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            file_id,
            inode,
            fileName,
            type,
            status,
            size,
            mtime,
            ctime,
            birthtime,
            lastUploadTimestamp
            FROM 
            files 
            WHERE 
            host_id=:host_id AND
            parent_inode=:parent_inode
            ORDER BY 
            type DESC,
            fileName ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $hostId,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':parent_inode', $parentInode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }
    public function hostBelongs2User($host_id,$user_id,$q_select="host_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            $q_select 
            FROM 
            hosts 
            WHERE 
            host_id=:host_id AND
            user_id=:user_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return 0;
    }
    public function hostId2Data($host_id,$q_request="host_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            $q_request
            FROM 
            hosts
            WHERE 
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        return 0;
    }
    public function hostHashKey2hostId($hash,$key) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            host_id
            FROM 
            hosts
            WHERE 
            hash=:hash AND
            `key`=:key
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hash', $hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':key', $key,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return (int)$qr->host_id;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        return 0;
    }
    public function file_id2data($file_id,$host_id,$q_select="file_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            $q_select 
            FROM 
            files 
            WHERE
            file_id=:file_id AND
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 0;
    }
    public function file_inode2data($inode,$host_id,$q_select="file_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            $q_select 
            FROM 
            files 
            WHERE
            inode=:inode AND
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 0;
    }

    private $getFilesOfInode_path='';
    public function getPathOfInode($host_id,$inode,$links=0,$recursion=0) {
        $pre_link='/dataairbag/fileList/'.$host_id.'/';
        if(!$recursion) $this->getFilesOfInode_path='';
        if($inode===0) {
            if($links) return '<a href="'.$pre_link.'0">/</a>';
            else return '/';
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT fileName,file_id FROM files WHERE inode=:inode AND host_id=:host_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
            if($links) return '<a href="'.$pre_link.'0">/</a>';
            else return '/';
        }
        $link=$pre_link.$qr->file_id;

        if($this->getFilesOfInode_path==='') {
            if($links) $this->getFilesOfInode_path='<a href="'.$link.'">'.$qr->fileName.'</a>';
            else $this->getFilesOfInode_path=$qr->fileName;
        }
        else {
            if($links) $this->getFilesOfInode_path='<a href="'.$link.'">'.$qr->fileName.'</a>/'.$this->getFilesOfInode_path;
            else $this->getFilesOfInode_path=$qr->fileName.'/'.$this->getFilesOfInode_path;
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT parent_inode FROM files WHERE inode=:inode AND host_id=:host_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $inode,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $host_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return $this->getFilesOfInode_path;

        $parentInode=(int)$qr->parent_inode;

        if(!$parentInode) {
            return '<a href="'.$pre_link.'0">/</a>'.$this->getFilesOfInode_path;
        }
        else return $this->getPathOfInode($host_id,$parentInode,$links,1);
}

    public function sendNewFileStatuses($hostId,$hash='',$key='') {
        if($hash===''||$key==='') {
            if(!$qr=$this->hostId2Data($hostId,"hash,`key`")) return 0;
            $hash=$qr->hash;
            $key=$qr->key;
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("dataairbag")->prepare("SELECT 
            inode,
            status
            FROM 
            files 
            WHERE 
            (status=1 OR status=4) AND
            host_id=:host_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $hostId,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$filesAr=$stm->fetchAll(PDO::FETCH_OBJ)) return 0;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,"http://node1.madwww.org:3031");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode(array(
            "task"=>"changeFilesStatus",
            "filesAr"=>$filesAr,
            "hash"=>$hash,
            "key"=>$key
        )));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($server_output = curl_exec($ch)) {
            if($res=json_decode($server_output)) {
                if($res->status==="success") {
                    foreach($filesAr as $i=>$file) {
                        $file->status=(int)$file->status;
                        if($file->status===1) $status=2;
                        elseif($file->status===4) $status=5;
                        else {
                            curl_close ($ch);
                            return 0;
                        }

                        try {
                            /** @noinspection PhpUndefinedMethodInspection */
                            $stm=$this->uFunc->pdo("dataairbag")->prepare("UPDATE 
                            files
                            SET
                            status=:status
                            WHERE
                            inode=:inode AND
                            host_id=:host_id
                            ");
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status,PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':inode', $file->inode,PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':host_id', $hostId,PDO::PARAM_INT);
                            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                        }
                        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
                    }
                }
            }
        }

        curl_close ($ch);
        return 1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
    }
}
