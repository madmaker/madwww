<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class sitemap_admin {
    public $stm1;
    public $stm2;
    public $stm3;
    private $uCore;

    private function get_pages() {
        //php-scripts
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->stm1=$this->uFunc->pdo("pages")->prepare("SELECT
            page_mod,
            page_title,
            page_id,
            page_name,
            navi_parent_page_id
            FROM
            u235_pages_list
            WHERE
            page_category=''
            ORDER BY
            page_mod,
            page_title");
            /** @noinspection PhpUndefinedMethodInspection */$this->stm1->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        //html-pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->stm2=$this->uFunc->pdo("pages")->prepare("SELECT
            page_title,
            page_id,
            page_name,
            navi_parent_page_id
            FROM
            u235_pages_html
            WHERE
            site_id=:site_id AND
            page_category=''
            ORDER BY
            page_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->stm2->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->stm2->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        //uPage
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->stm3=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_title,
            page_id,
            page_url,
            navi_parent_page_id
            FROM
            u235_pages
            WHERE
            site_id=:site_id
            ORDER BY
            page_title
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->stm3->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->stm3->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    public function text($str) {
        return $this->uCore->text(array('uNavi','sitemap_admin'),$str);
    }

    private function define_panel() {
        $this->uCore->page_panel='
        <ul class="u235_top_menu">
            <li><a id="uNavi_attachBtn" href="javascript:void(0);">'.$this->text("Attach - btn"/*Прикрепить*/).'</a></li>
            <li><a id="uNavi_cancelBtn" href="javascript:void(0);">'.$this->text("Cancel - btn"/*Отмена*/).'</a></li>
            <li><a id="uNavi_pageMenuBtn" href="javascript:void(0);">'.$this->text("Page's menu - btn"/*Меню страницы*/).'</a></li>
        </ul>';
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Администрирование карты сайта*/);

        $this->uFunc->incJs(u_sroot.'uNavi/js/sitemap_admin.min.js');
        $this->uFunc->incCss(u_sroot.'templates/u235/css/uNavi.min.css');

        $GLOBALS['TEMPLATE']['HEADER']=$this->text("pg title"/*Карта сайта*/);

        $this->get_pages();
        $this->define_panel();
    }
}
$uNavi=new sitemap_admin($this);
ob_start();?>

    <div class="uNavi">
        <div class="pageList mapList"><div></div></div>
        <div class="pageList freeList"><div></div></div>
    </div>

    <script type="text/javascript">
        i=0;
        page_module=[];
        page_title=[];
        page_id=[];
        page_name=[];
        page_parent=[];
        childs_added=[];
        item_shown=[];
        item_sel=[];
        item_id2i=[];

        page_module[i]="mainpage";
        page_title[i]="<?=$uNavi->text("Default homepage title"/*Главная страница*/)?>";
        page_id[i]=0;
        page_name[i]="index";
        page_parent[i]="mainpage";
        i++;

        <? while($page=$uNavi->stm1->fetch(PDO::FETCH_OBJ)) {
            if($uNavi->uFunc->mod_installed($page->page_mod)){?>
                page_module[i]="<?=$page->page_mod?>";
                page_title[i]="<?=$page->page_title?>";
                page_id[i]=<?=$page->page_id?>;
                page_name[i]="<?=$page->page_name?>";
                page_parent[i]="<?=$page->navi_parent_page_id?>";
                i++;
            <?}
        }?>
        <? while($page=$uNavi->stm2->fetch(PDO::FETCH_OBJ)) { ?>
            page_module[i]="page";
            page_title[i]="<?=$page->page_title?>";
            page_id[i]='s'+<?=$page->page_id?>;
            page_name[i]="<?=$page->page_name?>";
            page_parent[i]="<?=$page->navi_parent_page_id?>";
            i++;
        <?}?>
        <? while($page=$uNavi->stm3->fetch(PDO::FETCH_OBJ)) { ?>
            page_module[i]="uPage";
            page_title[i]="<?=$page->page_title?>";
            page_id[i]='s'+<?=$page->page_id?>;
            page_name[i]="<?=$page->page_url?>";
            page_parent[i]="<?=$page->navi_parent_page_id?>";
            i++;
        <?}?>
    </script>


    <div style="display:none">

    </div>




<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>