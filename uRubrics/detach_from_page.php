<?php
namespace uRubrics\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once "uPage/inc/common.php";

class detach_from_page {
    private $uCore;
    private $page_id;
    private $rubric_id;

    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['rubric_id'])) $this->uFunc->error(10);
        $this->page_id=$_POST['page_id'];
        $this->rubric_id=$_POST['rubric_id'];
        if(!uString::isDigits($this->page_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->rubric_id)) $this->uFunc->error(30);
    }
    private function detach() {
        //DELETE connection
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("DELETE FROM
            u235_urubrics_pages
            WHERE
            page_id=:page_id AND
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uPage=new common($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->detach();

        $this->uPage->clean_cache4uRubrics($this->rubric_id);

        echo "{
        'status' : 'success', 
        'rubric_id' : '".$this->rubric_id."',
        'page_id' : '".$this->page_id."'
        }";
    }
}
/*$newClass=*/new detach_from_page($this);