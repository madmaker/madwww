<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class eip_bc_attach_page {
    public $uFunc;
    public $uSes;
    private $uCore,$navi_parent_page_id,$mod,$page_id,$cur_navi_parent_page_id,$page_title;
    private function check_data() {
        if(!isset($_POST['parent_page_id'],$_POST['mod'],$_POST['page_id'])) $this->uFunc->error(10);
        if($_POST['mod']!='page'&&$_POST['mod']!='uPage') $this->uFunc->error(20);
        $this->mod=$_POST['mod'];

        $this->navi_parent_page_id=$_POST['parent_page_id'];
        $this->page_id=$_POST['page_id'];

        if(strpos($this->navi_parent_page_id,'s')===0) {
            $parent_id=substr($this->navi_parent_page_id,1);
            if(!uString::isDigits($parent_id)) $this->uFunc->error(30);
        }
        elseif(strpos($this->navi_parent_page_id,'p')===0) {
            $parent_id=substr($this->navi_parent_page_id,1);
            if(!uString::isDigits($parent_id)) $this->uFunc->error(40);
        }
        else {
            if(!uString::isDigits($this->navi_parent_page_id)) $this->uFunc->error(50);
        }

        if(!uString::isDigits($this->page_id)) $this->uFunc->error(60);

    }
    private function get_page_info() {
        if($this->mod=='page') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                navi_parent_page_id,
                page_title
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(80);
        }
        elseif($this->mod=='uPage') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                navi_parent_page_id,
                page_title
                FROM
                u235_pages
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(100);
        }
        else $this->uFunc->error(110);

        if(strpos($this->navi_parent_page_id,'s')===0) $parent_id=substr($this->navi_parent_page_id,1);
        elseif(strpos($this->navi_parent_page_id,'p')===0) $parent_id=substr($this->navi_parent_page_id,1);
        else $parent_id=$this->navi_parent_page_id;

        /** @noinspection PhpUndefinedVariableInspection */
        if($qr->navi_parent_page_id==$parent_id) die("{
        'status':'error',
        'msg':'not allowed here',
        'note':'1'
        }");

        $this->cur_navi_parent_page_id=$qr->navi_parent_page_id;
        $this->page_title=$qr->page_title;
    }
    private function check_for_no_recursion() {
        //we must check if new parent is not under current page id
        $parent_id=$this->navi_parent_page_id;
        if($parent_id=='0') return true;
        for($i=0;$parent_id!='0'&&$i<20;$i++) {
            if(strpos($parent_id,'s')===0) {
                $parent_id=substr($parent_id,1);
                if($parent_id==$this->page_id) return false;

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                    navi_parent_page_id
                    FROM
                    u235_pages_html
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $parent_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return false;
                $parent_id=$qr->navi_parent_page_id;
                if($parent_id=='0') return true;
            }
            elseif(strpos($parent_id,'p')===0) {
                $parent_id=substr($parent_id,1);
                if($parent_id==$this->page_id) return false;

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                    navi_parent_page_id
                    FROM
                    u235_pages
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $parent_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return false;
                $parent_id=$qr->navi_parent_page_id;
                if($parent_id=='0') return true;
            }
            else return true;
        }
        return false;
    }
    private function attach() {
        if($this->mod=='page') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
                u235_pages_html
                SET
                navi_parent_page_id=:navi_parent_page_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $this->navi_parent_page_id,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
        }
        elseif($this->mod=='uPage') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                u235_pages
                SET
                navi_parent_page_id=:navi_parent_page_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $this->navi_parent_page_id,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}
        }
        else $this->uFunc->error(160);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if(!$this->check_for_no_recursion()) die("{
        'status':'error',
        'msg':'not allowed here',
        'note':'2'
        }");

        $this->get_page_info();

        $this->attach();

        $this->uCore->mod='page';
        $this->uCore->page['navi_parent_page_id']=$this->navi_parent_page_id;
        $this->uCore->page['page_title']=$this->page_title;

        ob_start();
        $this->uCore->uBc->insert(1);
        $bc_html=ob_get_contents();
        ob_end_clean();

        echo "{
        'status':'done',
        'parent_page_id':'".$this->navi_parent_page_id."',
        'old_page_id':'".$this->cur_navi_parent_page_id."',
        'new_bc_html':'".rawurlencode($bc_html)."',
        }";
        exit;
    }
}
/*$uNavi=*/new eip_bc_attach_page($this);