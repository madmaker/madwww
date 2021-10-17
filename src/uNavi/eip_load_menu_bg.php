<?php
namespace uNavi\admin;

use PDO;
use PDOException;
use processors\uFunc;

require_once "processors/uMenu.php";
require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class eip_load_menu_bg {
    private $uSes;
    private $uFunc;
    private $uMenu;
    private $uCore,
        $cat_id;
    private function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uNavi','eip_load_menu_bg'),$str);
    }
    private function check_data() {
        if(!isset($_POST['cat_id'])) $this->uFunc->error(10);
        $this->cat_id=$_POST['cat_id'];
    }
    private function makeLink($link) {
        $link=trim($link);
        if(empty($link)) return false;
        return $link;
    }
//    private function get($page) {
//        $require=" 1=0 ";
//        if(strpos($page,"s")===0) {
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
//                navi_personal_menu,
//                navi_parent_page_id,
//                page_id
//                FROM
//                u235_pages_html
//                WHERE
//                page_id=:page_id AND
//                site_id=:site_id
//                ");
//                $page_id=str_replace("s","",$page);
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//            }
//            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
//
//            /** @noinspection PhpUndefinedVariableInspection */
//            /** @noinspection PhpUndefinedMethodInspection */
//            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
//                if(!(int)$data->navi_personal_menu) $require.=" OR ".$this->get($data->navi_parent_page_id);
//
//                try {
//                    /** @noinspection PhpUndefinedMethodInspection */
//                    $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
//					cat_id
//					FROM
//					u235_pagemenu
//					WHERE
//					page_id=:page_id AND
//					site_id=:site_id
//                    ");
//                    $page_id="s".$data->page_id;
//                    $site_id=site_id;
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//                }
//                catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                while($category=$stm->fetch(PDO::FETCH_OBJ)) {
//                    $category=$category->cat_id;
//                    $require.=" OR `cat_id`='".$category."' ";
//                }
//            }
//        }
//        if(strpos($page,"p")===0) {
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
//                navi_personal_menu,
//                navi_parent_page_id,
//                page_id
//                FROM
//                u235_pages
//                WHERE
//                page_id=:page_id AND
//                site_id=:site_id
//                ");
//                $page_id=str_replace("p","",$page);
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//            }
//            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
//
//            /** @noinspection PhpUndefinedVariableInspection */
//            /** @noinspection PhpUndefinedMethodInspection */
//            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
//                if(is_null($data->navi_parent_page_id)) $data->navi_parent_page_id=0;
//                if(!(int)$data->navi_personal_menu) $require.=" OR ".$this->get($data->navi_parent_page_id);
//
//                try {
//                    /** @noinspection PhpUndefinedMethodInspection */
//                    $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
//					cat_id
//					FROM
//					u235_pagemenu
//					WHERE
//					page_id=:page_id AND
//					site_id=:site_id
//					");
//                    $page_id="p".$data->page_id;
//                    $site_id=site_id;
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//                }
//                catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                while($category=$stm->fetch(PDO::FETCH_OBJ)) {
//                    $category=$category->cat_id;
//                    $require.=" OR `cat_id`='".$category."' ";
//                }
//            }
//        }
//        else {
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
//                navi_personal_menu,
//                navi_parent_page_id,
//                page_id
//                FROM
//                u235_pages_list
//                WHERE
//                page_id=:page_id
//                ");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//            }
//            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
//
//            /** @noinspection PhpUndefinedVariableInspection */
//            /** @noinspection PhpUndefinedMethodInspection */
//            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
//                if(!$data->navi_personal_menu&&$data->navi_parent_page_id!="mainpage"&&$data->navi_parent_page_id!="") $require.=" OR ".$this->get($data->navi_parent_page_id);
//
//                try {
//                    /** @noinspection PhpUndefinedMethodInspection */
//                    $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
//					cat_id
//					FROM
//					u235_pagemenu
//					WHERE
//					page_id=:page_id AND
//					site_id=:site_id
//					");
//                    $page_id=$data->page_id;
//                    $site_id=site_id;
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//                }
//                catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                while($cat=$stm->fetch(PDO::FETCH_OBJ)) {
//                    $cat_id=$cat->cat_id;
//                    $require.=" OR `cat_id`='".$cat_id."' ";
//                }
//            }
//        }
//        return $require;
//    }
    private function insert() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_type,
            cat_access
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $cat=$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        if(!$cat) {
            echo '{
            "status":"done",
            "dg_cnt":"",
            "menu_cnt":""
            }';
            exit;
        }
        $cat->cat_type=(int)$cat->cat_type;

        echo '{
            "status":"done",
            "dg_cnt":"'.rawurlencode($this->return_cat_type_content($this->cat_id)).'",
            "menu_cnt":"'.rawurlencode($this->uMenu->return_cat_id_content($this->cat_id)).'"
            }';
        exit;
    }
    private function return_cat_type_content($cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            title,
            link,
            access,
            id,
            target,
            position,
            indent
            FROM
            u235_menu
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ORDER BY
            position ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

        $cnt='<input type="hidden" id="uNavi_eip_menu_items_list_cat_id" value="'.$cat_id.'">
        <ul class="list-unstyled">';

        $indent=0;

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        for($first=true;$item=$stm->fetch(PDO::FETCH_OBJ);$first=false) {
            if($this->uSes->access($item->access)) {
                $item->link=$this->makeLink($item->link);

                while($item->indent>$indent) {
                    $indent++;
                    $cnt.='<ul>';
                }
                while($item->indent<$indent) {
                    $indent--;
                    $cnt.='</ul>';
                }
                if(!$first) $cnt.='</li>';

                $cnt.='<li 
                id="uNavi_eip_item_li_'.$item->id.'" 
                class="list-unstyled indent_'.$item->indent.' cur_indent_'.$indent.' '.($first&&$indent?"bg-danger":"").'" '.($first&&$indent?'title="'.$this->text("Menu item without parent item wouldn't be shown - hint"/*Пункт меню без родительского пункта не будет отображен*/).'"':"").'
                >
                <a 
                href="javascript:void(0)" 
                onclick="uNavi_eip.edit_menu_item('.$item->id.')"
                ><span class="text-muted"><small>#'.$item->id.'</small></span> '.$item->title.'</a>
                '.($item->link?('&nbsp;<a href="'.$item->link.'" target="_blank"><span class="icon-link-ext"></span></a>'):'');
            }
        }
        $cnt.='</li>
        </ul>

        <div class="bs-callout bs-callout-primary">'.$this->text("Click the item title to edit or delete - hint"/*Чтобы изменить/удалить пункт меню, нажмите на его название*/).'</div>';
        return $cnt;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die('{"status":"forbidden"}');

        $this->uFunc=new uFunc($this->uCore);
        $this->uMenu=new \uMenu($this->uCore);

        $this->check_data();
        $this->insert();
    }
}
new eip_load_menu_bg($this);