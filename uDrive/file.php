<?php
namespace uDrive;
// !!!!!!
//СЕССИЯ В ЭТОМ ФАЙЛЕ НЕ ПРОВЕРЯЕТСЯ. ЗАДАНО В БД
// !!!!!!
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'processors/classes/uFunc.php';

class file {
    public $uFunc;
    private $file_site_id;
    private $uCore,
        //from user
        $file_id,$file_hash,
        //used in script
        $file_addr,$file_mime,$file_name,$file_size;
    public $returnHtml;
    private function check_data() {
        //$this->uCore->url_prop[1]
        //1 - file_id
        //2 - file_hashname
        //3 - file_name - не используется. Чисто для красоты адреса

        if(!isset($this->uCore->url_prop[1])) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }
        if(!isset($this->uCore->url_prop[2])) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }

        $this->file_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->file_id)) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }
        $this->file_id=(int)$this->file_id;

        $this->file_hash=$this->uCore->url_prop[2];
        if(!uString::isHash($this->file_hash)) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }

        return true;
    }
    private function return_error($heading, $text) {
        $this->returnHtml='<div class="jumbotron">
        <h1 class="page-header">'.$heading.'</h1>
        <p>'.$text.'</p>
        </div>';
        return false;
    }
    private function get_file() {
        try {
            $stm=$this->uFunc->pdo('uDrive')->prepare('SELECT
            file_name,
            file_mime,
            file_size,
            site_id
            FROM
            u235_files
            WHERE
            file_id=:file_id AND
            file_hashname=:file_hashname 
            ');
//            $site_id=site_id;
            $stm->bindParam(':file_id', $this->file_id,PDO::PARAM_INT);
            $stm->bindParam(':file_hashname', $this->file_hash,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        if(!$file=$stm->fetch(PDO::FETCH_OBJ)) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }

        $this->file_site_id=$file->site_id;
        $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/files/'.$this->file_site_id.'/'.$this->file_id.'/'.$this->file_hash;
        $this->file_mime=$file->file_mime;
        $this->file_name=strip_tags(stripcslashes(uString::sql2text($file->file_name,1)));
        $this->file_size=$file->file_size;


        return true;
    }
    private function file_output() {
        if (!file_exists($this->file_addr)) {
            return $this->return_error($this->text('file is not found'), $this->text('this file is not found'));
        }

        header('X-Accel-Redirect: /files/' . $this->file_site_id . '/' . $this->file_id . '/' . $this->file_hash);
        if (isset($_GET['download']) || $this->file_size > 52428800) {
            header('Content-Type: application/octet-stream');
        }//if we want to open download window
        else {
            header('Content-Type: ' . $this->file_mime);
        }

        if (isset($_GET['download']) || $this->file_size > 52428800) {
            header('Content-Disposition: attachment; filename="' . $this->file_name . '"');
        }//to open download window
        else {
            header('Content-Disposition: ' . ($this->file_mime === 'application/octet-stream' ? 'attachment' : 'inline') . '; filename="' . $this->file_name . '"');
        }

        return true;
    }
    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uDrive','file'),$str);
    }

    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text('Page name'/*Файлы*/);

        if(!$this->check_data()) {
            return false;
        }
        if(!$this->get_file()) {
            return false;
        }
        if($this->file_output()) {
            exit;
        }

        return false;
    }
}
$uDrive=new file($this);


$this->uFunc->incJs(u_sroot.'js/u235/uString.js');
$this->page_content=$uDrive->returnHtml;
/** @noinspection PhpIncludeInspection */
include 'templates/template.php';
