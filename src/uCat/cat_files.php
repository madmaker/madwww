<?php
// !!!!!!
//СЕССИЯ В ЭТОМ ФАЙЛЕ НЕ ПРОВЕРЯЕТСЯ. ЗАДАНО В БД
// !!!!!!

class uCat_cat_files {
    private $uCore,
        //from user
        $cat_id,$file_id,
        //used in script
        $file_addr,$file_mime,$file_name,$file_size;
    public $returnHtml;
    private function check_data() {
        //uCat/cat_files/site_id/cat_id/file_id/file_name
        //$this->uCore->url_prop[1]
        //1 - site_id  - не используется. Просто в старых адресах было
        //2 - cat_id - key
        //2 - file_id - key. В БД искать нужно по where cat_id and file_id. Они связаны
        //3 - file_name - не используется. Просто в старых адресах было

        uFunc::journal('request: '.$_SERVER['REQUEST_URI'].' ---- referrer: '.(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:''),'uCat_old_cat_files_requests');

        if(!isset($this->uCore->url_prop[2])) return $this->return_error('Файл не найден','Такой файл не найден',1);
        if(!isset($this->uCore->url_prop[3])) return $this->return_error('Файл не найден','Такой файл не найден',2);

        $this->cat_id=$this->uCore->url_prop[2];
        if(!uString::isDigits($this->cat_id)) return $this->return_error('Файл не найден','Такой файл не найден',3);
        $this->cat_id=(int)$this->cat_id;

        $this->file_id=$this->uCore->url_prop[3];
        if(!uString::isDigits($this->file_id)) return $this->return_error('Файл не найден','Такой файл не найден',3);
        $this->file_id=(int)$this->file_id;

        return true;
    }
    private function return_error($heading,$text,$code=0) {
        //if($code) die('Код '.$code);
        /*$this->returnHtml='<div class="jumbotron">
        <h1 class="page-header">'.$heading.'</h1>
        <p>'.$text.'</p>
        </div>';*/
        return false;
    }
    private function get_file() {
        //get uDrive file id
        if(!$query=$this->uCore->query("uCat","SELECT
        `uDrive_file_id`
        FROM
        `u235_cats_files`
        WHERE
        `cat_id`='".$this->cat_id."' AND
        `file_id`='".$this->file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->return_error('Файл не найден','Такой файл не найден',5);
        $qr=$query->fetch_object();
        $file_id=(int)$qr->uDrive_file_id;

        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_name`,
        `file_mime`,
        `file_size`,
        `file_hashname`
        FROM
        `u235_files`
        WHERE
        `file_id`='".$file_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) return $this->return_error('Файл не найден','Такой файл не найден',5);

        $file=$query->fetch_object();

        $this->file_addr= $_SERVER['DOCUMENT_ROOT'].'/uDrive/files/'.site_id.'/'.$file_id.'/'.$file->file_hashname;
        $this->file_mime=$file->file_mime;
        $this->file_name=uString::sql2text($file->file_name);
        $this->file_size=$file->file_size;

        return true;
    }
    private function file_output() {
        if (!file_exists($this->file_addr)) return $this->return_error('Файл не найден','Такой файл не найден','6 '.$this->file_addr);

        header('Content-Description: File Transfer');
        if(isset($_GET['download'])) header('Content-Type: application/octet-stream');//if we want to open download window
        else header('Content-Type: '.$this->file_mime);
        //header('Cache-Control: public, must-revalidate, max-age=0');
        //header('Pragma: no-cache');
        header('Accept-Ranges: bytes');
        if(isset($_GET['download'])) header('Content-Disposition: attachment; filename='.$this->file_name);//to open download window
        else header('Content-Disposition: '.($this->file_mime=='application/octet-stream'?'attachment':'inline').'; filename="'.$this->file_name.'"');
        //header('Content-Transfer-Encoding: binary');
        //header('Expires: 0');
        /*if(!$this->thumb)*/ header('Content-Length: ' . $this->file_size);
        flush();
        readfile($this->file_addr);

        return true;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        if(!$this->check_data()) return false;
        if(!$this->get_file()) return false;
        if($this->file_output()) exit;
        else return false;
    }
}
$uCat=new uCat_cat_files($this);
