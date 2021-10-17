<?php

namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uString;


require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class file {
    private $uCore,$file_id,$file_addr,$file_mime,$file_name,$file_size,$thumb,$tic_id;
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) die('1');
        $this->file_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->file_id)) die('2');
        $this->thumb=false;
        if(isset($this->uCore->url_prop[2])) {
            if($this->uCore->url_prop[2]=='sm') $this->thumb=true;
        }
    }
    private function check_access() {
        if($this->uSes->access(8)) return true;//operator
        if($this->uSes->access(9)) return true;//consultant

        //get owner and company_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id,
            company_id
            FROM
            u235_requests
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$req=$stm->fetch(PDO::FETCH_OBJ))$this->uFunc->error(10);
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        if($req->user_id==$this->uSes->get_val("user_id")) return true;//owner

        //check if current user is client of request's company
        if($req->company_id!='0') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                user_id
                FROM
                u235_com_users
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $user_id=$this->uSes->get_val('user_id');
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $req->company_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($stm->fetch(PDO::FETCH_OBJ)) return true;
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        }

        //access by file hash for non-authorised user
        if(isset($this->uCore->url_prop[3],$this->uCore->url_prop[4])&&$this->thumb) {
            $msg_id=$this->uCore->url_prop[3];
            $msg_hash=$this->uCore->url_prop[4];

            if(!uString::isDigits($msg_id));
            if(!uString::isHash($msg_hash));

            //check hash
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                msg_id
                FROM
                u235_file_access_hashes
                WHERE
                msg_id=:msg_id AND
                hash=:msg_hash AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_hash', $msg_hash,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($stm->fetch(PDO::FETCH_OBJ)) return true;
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }

        return false;
    }
    private function get_file() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            timestamp,
            filename,
            file_size,
            file_mime,
            hash,
            tic_id
            FROM
            u235_msgs_files
            WHERE
            file_id=:file_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $this->file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$file=$stm->fetch(PDO::FETCH_OBJ)) die('3');
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        $this->tic_id=$file->tic_id;

        if($this->thumb) {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/uSupport/msgs_files/'.site_id.'/'.$file->tic_id.'/'.$this->file_id.'/'.$this->file_id.'_sm.jpg';
            $this->file_mime='image/jpeg';
            $this->file_name='thumb_'.uString::sql2text($file->filename);
        }
        else {
            $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/uSupport/msgs_files/'.site_id.'/'.$file->tic_id.'/'.$this->file_id.'/'.$file->hash;
            $this->file_mime=$file->file_mime;
            $this->file_name=uString::sql2text($file->filename);
        }
        $this->file_size=$file->file_size;
    }
    private function file_output() {
        if (!file_exists($this->file_addr)) die('50');
        header('Content-Description: File Transfer');
        if(isset($_GET['download'])) header('Content-Type: application/octet-stream');//if we want to open download window
        else header('Content-Type: '.$this->file_mime);
        header('Accept-Ranges: bytes');
        if(isset($_GET['download'])) header('Content-Disposition: attachment; filename="'.str_replace('"','\\"',uString::sql2text($this->file_name)).'"');//to open download window
        else header('Content-Disposition: '.($this->file_mime=='application/octet-stream'?'attachment':'inline').'; filename="'.uString::sql2text($this->file_name,1).'"');
        if(!$this->thumb) header('Content-Length: ' . $this->file_size);
        flush();
        readfile($this->file_addr);
        exit;
    }
    private function def_file_output() {
        $file='images/uSup/default_img.jpg';
        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Accept-Ranges: bytes');
        header('Content-Disposition: inline filename="no_access');
        flush();
        readfile($file);
        die();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->checkData();
        $this->get_file();
        if(!$this->check_access()) {
            if($this->thumb) {
                $this->def_file_output();
            }
            return false;
        }
        $this->file_output();
    }
}
/*$newClass=*/new file($this);
ob_start();?>

<?if($this->access(2)){?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>У вас нет доступа к этому файлу</p>
    </div>
<?}
else {?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div><?}
$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
