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

class attach2page {
    private $uPage;
    private $uFunc;
    private $uSes;
    private $mod;
    private $uCore;
    private $page_id;
    private $rubric_id;

    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['rubric_id'])) $this->uFunc->error(10);
        if(isset($_POST["mod"])) $this->mod=1;
        else $this->mod=0;
        $this->page_id=$_POST['page_id'];
        $this->rubric_id=$_POST['rubric_id'];
        if(!uString::isDigits($this->page_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->rubric_id)) $this->uFunc->error(30);

        //check if rubric exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            rubric_id
            FROM
            u235_urubrics_list
            WHERE
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        if($this->mod) {
            //check if page exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uPage")->prepare("SELECT
                page_id
                FROM
                u235_pages
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if (!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
            } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        else {
            //check if page exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("pages")->prepare("SELECT
                page_id
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if (!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
            } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
    }
    private function attach_page2rubric() {
        //make connection
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("REPLACE INTO 
            u235_urubrics_pages (
            page_id,
            rubric_id,
            `mod`,
            site_id
            ) VALUES (
            :page_id,
            :rubric_id,
            :mod,
            :site_id
            )");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod', $this->mod,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        $this->attach_page2rubric();

        $this->uPage->clean_cache4uRubrics($this->rubric_id);

        echo "{
        'status' : 'success', 
        'rubric_id' : '".$this->rubric_id."',
        'page_id' : '".$this->page_id."'
        }";
    }
}
new attach2page($this);