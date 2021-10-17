<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class sitemap_admin_page2page {
    private $uCore;
    private $real_parent;
    private $id;
    private $parent;

    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['page_parent'])) $this->uFunc->error(10);
        if(!uString::isDigits($this->id=str_replace('s', '', $_POST['page_id']))) $this->uFunc->error(20);
        $this->parent=$_POST['page_parent'];
        if(!empty($this->parent)&&!uString::isDigits($this->real_parent=str_replace('s', '', $this->parent))) $this->uFunc->error(30);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();


        if(!empty($this->parent)) {
            //Проверяем, чтобы родительский пункт меню существовал и не был тем же самым, что и id страницы

            if(strpos($_POST['page_parent'], 's')===0) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
                    page_id
                    FROM 
                    u235_pages_html
                    WHERE 
                    page_id=:real_parent AND 
                    page_id!=:page_id AND 
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':real_parent', $this->real_parent,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
            }
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("pages")->prepare("SELECT 
                    page_id
                    FROM 
                    u235_pages_list
                    WHERE 
                    page_id=:real_parent
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':real_parent', $this->real_parent, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
            }

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
        }

        if(strpos($_POST['page_id'], 's')!==0) die('done');

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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $this->parent,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        echo 'done';
    }
}
/*$uNavi=*/new sitemap_admin_page2page($this);