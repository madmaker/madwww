<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class sitemap_admin_page_navi {
    public $page_id;
    public $page_title;
    public $page_individualMenu;
    private $uCore;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) die();
        $this->page_id=$this->uCore->url_prop[1];
        if(!uString::isDigits(str_replace("s","",$this->page_id))) die();
    }

    private function get_cats() {
        //РАЗДЕЛЫ МЕНЮ
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_id,
            cat_title
            FROM
            u235_cats
            WHERE
            site_id=:site_id AND
            status=''
            ORDER BY 
            cat_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }

    private function get_page_info() {
        if(strpos($this->page_id,"s")===0) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                 page_title,
                 navi_personal_menu
                 FROM
                 u235_pages_html
                 WHERE
                 site_id=:site_id AND
                 page_id=:page_id
                 ");
                $page_id=str_replace("s","",$this->page_id);
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                 page_title,
                 navi_personal_menu
                 FROM
                 u235_pages_list
                 WHERE
                 page_id=:page_id
                 ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        }

        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) die();
        $this->page_title=$qr->page_title;
        $this->page_individualMenu=(int)$qr->navi_personal_menu;
    }

    private function get_pages_attached_menus() {
        //ID ПРИКРЕПЛЕННЫХ РАЗДЕЛОВ
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_id
            FROM
            u235_pagemenu
            WHERE
            site_id=:site_id AND
            page_id=:page_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    private function set_panel() {
        $this->uCore->page_panel='<ul class="u235_top_menu">
        <li><a id="uNavi_attach_btn" href="javascript:void(0);">'.$this->text("Attach - btn txt"/*Прикрепить*/).'</a></li>
        <li><a id="uNavi_detach_btn" href="javascript:void(0);">'.$this->text("Detach - btn txt"/*Открепить*/).'</a></li>
        </ul>';
    }

    public function text($str) {
        return $this->uCore->text(array('uNavi','sitemap_admin_page_navi'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Меню, прикрепленное к странице*/);

        $this->uCore->uInt_js('uNavi','sitemap_admin_page_navi');

        $this->check_data();
        $this->cats=$this->get_cats();
        $this->get_page_info();
        $this->menus=$this->get_pages_attached_menus();
        $this->set_panel();
    }
}
$uNavi=new sitemap_admin_page_navi($this);

$this->uFunc->incJs(u_sroot.'uForms/js/'.$this->page_name.'.js');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uNavi.css');

ob_start();?>

<div class="uNavi_sitemap_admin_page_navi uNavi">
    <h1><?=$uNavi->text("Menu for - header"/*Меню для */)?>"<?=$uNavi->page_title;?>"</h1><?
    if(strpos($this->page_id,"s")===0) {?>
        <p>
        <label><?=$uNavi->text("Inherit parent menus on this and child pages - label"/*Наследование меню на этой странице и ее дочерних:*/)?></label>
        <select id="uNavi_individualMenu_sbx">
            <option value="0" <?if(!$uNavi->page_individualMenu) {?> selected <?}?>><?=$uNavi->text("Inherit"/*Наследовать от родительских*/)?></option>
            <option value="1" <?if($uNavi->page_individualMenu) {?> selected <?}?>><?=$uNavi->text("Not inherit"/*Не наследовать*/)?></option>
        </select>
        </p>
    <?}?>

    <div class="list"></div>

</div>

<script type="text/javascript">
page_id="<?=$uNavi->page_id?>";
	 
cat_id=[];
cat_title=[];
cat_attached=[];
cat_sel=[];
i=0;
<? while($menu=$uNavi->menus->fetch(PDO::FETCH_OBJ)) { ?>
	 cat_id[i]=<?=$menu->cat_id?>;
	 cat_title[i++]="<?=rawurlencode($menu->cat_title)?>";
<?}?>
	 
att_id=[];
att_id2i=[];
i=0;
<? while($att=$uNavi->cats->fetch(PDO::FETCH_OBJ)) {?>
	 att_id[i]=<?=$att->cat_id?>;
	 att_id2i[att_id[i]]=i;
	 i++;
<?}?>
</script>

<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>