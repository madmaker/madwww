<?php
namespace uEditor\admin;

use processors\uFunc;
use PDO;
use PDOException;
use uEditor_setup_article;
use uSes;
use uString;

class drop_article_bg {
    private $uCore,$uFunc,$page_id;
    private function check_data() {
        if(!isset($_POST['page_id'])) $this->uFunc->error(10);
        $this->page_id=$_POST['page_id'];
        if(!uString::isDigits($this->page_id)) $this->uFunc->error(20);
    }
    private function delete_page() {
        //get page's parent page id for navi
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        if(!$page=$stm->fetch(PDO::FETCH_OBJ)) die('forbidden');

        //set for all children pages new parent page id for navi
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            navi_parent_page_id=:navi_parent_page_id
            WHERE
            navi_parent_page_id=:old_navi_parent_page_id AND
            site_id=:site_id
            ");
            $old_navi_parent_page_id="s".$this->page_id;
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $page->navi_parent_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':old_navi_parent_page_id', $old_navi_parent_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        //DELETE page
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("DELETE FROM
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
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        uFunc::rmdir('uEditor/files/'.site_id.'/'.$this->page_id);

        //DELETE page from uPage elements
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM
            u235_cols_els
            WHERE
            el_id=:page_id AND
            el_type='art' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        echo "{'status' : 'done'}";
    }
    private function clear_cache() {
        //clear cache
        include_once "uEditor/inc/setup_article.php";
        $uEditor=new uEditor_setup_article($this,$this->page_id);
        $uEditor->clear_cache($this->page_id);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die('forbidden');
        $this->check_data();

        $this->delete_page();

        $this->clear_cache();

        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
}
/*$uEditor=*/new drop_article_bg ($this);