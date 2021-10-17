<?php
namespace uEditor\admin;

use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use uPage\common;
use uString;

require_once "uEditor/inc/setup_article.php";
include_once "inc/page_avatar.php";
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";
require_once "uEditor/classes/common.php";

class page_save_bg {
    public $folder_levels;
    public $page_show_title_in_content;
    private $uEditor;
    private $uSes;
    private $uPage;
    private $uCore,$uFunc,$page_id;
    private function checkData() {
        //check page_id
        if(!isset($_POST['page_id'])) $this->uFunc->error(10);
        if(uString::isDigits($_POST['page_id'])) $this->page_id=(int)$_POST['page_id'];
        else {
            if(!uString::isDigits(str_replace(",","",$_POST['page_id']))) $this->uFunc->error(20);
            $this->page_id=$_POST['page_id'];
        }
    }
    private function save_page_text() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_text=:page_text
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $page_text=uString::text2sql($_POST['page_text'],true);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_text', $page_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        if(isset($_POST['cols_els_id'])) {
            $cols_els_id=$_POST['cols_els_id'];
            if(!uString::isDigits($cols_els_id)) $this->uFunc->error(40);


            echo '{
            "status":"done",
            "el_id":"'.$this->page_id.'",
            "cols_els_id":"'.$cols_els_id.'",
            "page_text":"'.rawurlencode($_POST['page_text']).'"
            }';
        }
        else echo "{'status' : 'done'}";;
    }
    private function save_page_title() {
        if(!isset($_POST['page_show_title_in_content'])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
                page_show_title_in_content
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

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->page_show_title_in_content=(int)$qr->page_show_title_in_content;
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        else $this->page_show_title_in_content=(int)$_POST['page_show_title_in_content'];
        if($this->page_show_title_in_content!=1) $_POST['page_show_title_in_content']=0;

        $_POST['page_title']=trim($_POST['page_title']);

        if(!strlen($_POST['page_title'])) die("{'status' : 'error', 'msg' : 'title is empty'}");

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_title=:page_title,
            page_show_title_in_content=:page_show_title_in_content
            WHERE
            page_id=:page_id AND
            site_id=:site_id
             ");
            $site_id=site_id;
            $page_title=uString::text2sql($_POST['page_title'],true);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_show_title_in_content', $this->page_show_title_in_content,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'page_title':'".rawurlencode($_POST['page_title'])."',
        'page_show_title_in_content':'".$_POST['page_show_title_in_content']."'
        }";
    }
    private function save_page_url() {
        if(!isset($_POST['page_alias'])) $this->uFunc->error(70);

        $_POST['page_name']=urldecode($_POST['page_name']);
        if(!uString::isFilename_rus($_POST['page_name'])) die("{'status' : 'error', 'msg' : 'page_name'}");

        $_POST['page_alias']=trim(urldecode($_POST['page_alias']));
        if(!uString::isUrl_rus($_POST['page_alias'])&&!empty($_POST['page_alias'])) die("{'status' : 'error', 'msg' : 'page_alias'}");

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
            WHERE
            page_name=:page_name AND
            page_id!=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $page_name=$_POST['page_name'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) die("{'status' : 'error', 'msg' : 'page_name_busy'}");
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_name=:page_name,
            page_alias=:page_alias
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $page_name=$_POST['page_name'];
            $page_alias=$_POST['page_alias'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_alias', $page_alias,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'page_name':'".rawurlencode($_POST['page_name'])."',
        'page_alias':'".$_POST['page_alias']."'
        }";
    }
    private function save_page_access() {
        if(!isset($_POST['page_access'])) $this->uFunc->error(100);
        if(!uString::isDigits($_POST['page_access'])) $this->uFunc->error(110);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_access=:page_access
            WHERE
            page_id=:page_id AND
            site_id=:site_id
             ");
            $site_id=site_id;
            $page_access=$_POST['page_access'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_access', $page_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'page_access':'".rawurlencode($_POST['page_access'])."'
        }";
    }
    private function save_page_navi() {
        if(!isset($_POST['navi_parent_menu_id'])) $this->uFunc->error(130);
        if(!uString::isDigits($_POST['navi_parent_menu_id'])) $this->uFunc->error(140);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            navi_parent_menu_id=:navi_parent_menu_id
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $navi_parent_menu_id=$_POST['navi_parent_menu_id'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_menu_id', $navi_parent_menu_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'navi_parent_menu_id':'".rawurlencode($_POST['navi_parent_menu_id'])."'
        }";
    }
    private function save_page_timestamp() {
        if(!isset($_POST['page_date'],$_POST['page_time'],$_POST['page_timestamp_show'])) $this->uFunc->error(160);

        if(!uString::isDate($_POST['page_date'])) die('page_date');
        if(!uString::isTime($_POST['page_time'])) die('page_time');
        if($_POST['page_timestamp_show']!='1') $_POST['page_timestamp_show']='0';

        $dateAr=explode('.',$_POST['page_date']);
        $timeAr=explode(':',$_POST['page_time']);
        $page_timestamp=mktime($timeAr[0],$timeAr[1],0,($dateAr[1]),$dateAr[0],$dateAr[2])/*+$_POST['user_timezoneOffset']*60*/;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_timestamp=:page_timestamp,
            page_timestamp_show=:page_timestamp_show
            WHERE
            page_id=:page_id AND
            site_id=:site_id
             ");
            $site_id=site_id;
            $page_timestamp_show=$_POST['page_timestamp_show'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp_show', $page_timestamp_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'page_timestamp':'".$page_timestamp."',
        'page_datetime':'".date('d.m.Y H:i',$page_timestamp)."',
        'page_timestamp_show':'".$_POST['page_timestamp_show']."',
        }";
    }
    private function save_page_short() {
        $_POST['page_short_text']=urldecode($_POST['page_short_text']);
        $_POST['page_short_text']=uString::text2sql($_POST['page_short_text'],true);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_short_text=:page_short_text
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $page_short_text=$_POST['page_short_text'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_short_text', $page_short_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done'
        }";
    }
    private function save_page_seo() {
        if(!isset($_POST['meta_keywords'])) $this->uFunc->error(190);

        $meta_description=trim(rawurldecode($_POST['meta_description']));
        $meta_keywords=trim(rawurldecode($_POST['meta_keywords']));


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            meta_description=:meta_description,
            meta_keywords=:meta_keywords
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $meta_description=uString::text2sql($meta_description);
            $meta_keywords=uString::text2sql($meta_keywords);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':meta_keywords', $meta_keywords,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':meta_description', $meta_description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}

        echo "{
        'status' : 'done',
        'meta_description' : '".rawurlencode($meta_description)."',
        'meta_keywords' : '".rawurlencode($meta_keywords)."'
        }";
    }
    private function delete_page_avatar() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_avatar_time=0
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir('uEditor/pages_avatars/'.site_id.'/'.$this->page_id);

        echo "{'status' : 'done'}";
    }
    private function show_avatar() {
        if($_POST['show_avatar']!='1') $_POST['show_avatar']='0';

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            show_avatar=:show_avatar
            WHERE
            page_id=:page_id AND
            site_id=:site_id
             ");
            $site_id=site_id;
            $show_avatar=$_POST['show_avatar'];
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatar', $show_avatar,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('220'/*.$e->getMessage()*/);}

        //get page's show_avatar
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            show_avatar
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

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(240);
            $show_avatar=$qr->show_avatar;

            if($show_avatar&&$this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1') {
                $page_avatar=new \uEditor_page_avatar($this->uCore);
                $page_avatar_addr=$page_avatar->get_avatar(450,$this->page_id,time());
            }
            else $page_avatar_addr=false;


            echo "{
            'status' : 'done',
            'avatar_src':'".$page_avatar_addr."',
            'show_avatar':'".$_POST['show_avatar']."'
            }";
        }
        catch(PDOException $e) {$this->uFunc->error('250'/*.$e->getMessage()*/);}
    }
//    private function avatar_page_width() {
//        $avatar_page_width=trim($_POST['avatar_page_width']);
//        if(!uString::isDigits($avatar_page_width)) die("{'status' : 'error', 'msg' : 'not digits'}");
//
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
//            u235_conf
//            SET
//            value=:avatar_page_width
//            WHERE
//            field_id=805 AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avatar_page_width', $avatar_page_width,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('260'/*.$e->getMessage()*/);}
//
//
//        //get page's show_avatar
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
//            show_avatar
//            FROM
//            u235_pages_html
//            WHERE
//            page_id=:page_id AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//            /** @noinspection PhpUndefinedMethodInspection */
//            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(270);
//            $show_avatar=$qr->show_avatar;
//
//            if($show_avatar&&$this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1') {
//                $page_avatar=new \uEditor_page_avatar($this->uCore);
//                $page_avatar_addr=$page_avatar->get_avatar(450,$this->page_id,time());
//            }
//            else $page_avatar_addr=false;
//
//            echo "{
//            'status' : 'done',
//            'avatar_src':'".$page_avatar_addr."'
//            }";
//        }
//        catch(PDOException $e) {$this->uFunc->error('280'/*.$e->getMessage()*/);}
//    }
//    private function avatar_uRubrics_list_width() {
//        $avatar_uRubrics_list_width=trim($_POST['avatar_uRubrics_list_width']);
//        if(!uString::isDigits($avatar_uRubrics_list_width)) die("{'status' : 'error', 'msg' : 'not digits'}");
//
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
//            u235_conf
//            SET
//            value=:avatar_uRubrics_list_width
//            WHERE
//            field_id='806' AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avatar_uRubrics_list_width', $avatar_uRubrics_list_width,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('290'/*.$e->getMessage()*/);}
//
//        echo "{
//        'status' : 'done'
//        }";
//    }
//    private function avatar_uRubrics_widget_width() {
//        $avatar_uRubrics_widget_width=trim($_POST['avatar_uRubrics_widget_width']);
//        if(!uString::isDigits($avatar_uRubrics_widget_width)) die("{'status' : 'error', 'msg' : 'not digits'}");
//
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
//            u235_conf
//            SET
//            value=:avatar_uRubrics_widget_width
//            WHERE
//            field_id='807' AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avatar_uRubrics_widget_width', $avatar_uRubrics_widget_width,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('300'/*.$e->getMessage()*/);}
//
//        echo "{
//        'status' : 'done'
//        }";
//    }
    private function show_avatars_on_pages() {
        if($_POST['show_avatars_on_pages']!='1') $_POST['show_avatars_on_pages']='0';

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_conf
            SET
            value=:show_avatars_on_pages
            WHERE
            field_id='808' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatars_on_pages', $_POST['show_avatars_on_pages'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('310'/*.$e->getMessage()*/);}

        //get page's show_avatar
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            show_avatar
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

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(320);
            $show_avatar=$qr->show_avatar;

            if($show_avatar&&$this->uCore->uFunc->getConf("show_avatars_on_pages","content")=='1') {
                $page_avatar=new \uEditor_page_avatar($this->uCore);
                $page_avatar_addr=$page_avatar->get_avatar(450,$this->page_id,time());
            }
            else $page_avatar_addr=false;

            echo "{
            'status' : 'done',
            'avatar_src':'".$page_avatar_addr."',
            'show_avatars_on_pages':'".$_POST['show_avatars_on_pages']."'
            }";
        }
        catch(PDOException $e) {$this->uFunc->error('330'/*.$e->getMessage()*/);}
    }
    private function clear_cache() {
        $uEditor=new \uEditor_setup_article($this,$this->page_id);

        //clear cache
        $uEditor->clear_cache($this->page_id);


        //clear uPage cache with this art
        try {
            //get page_id

            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            u235_pages.page_id
            FROM
            u235_pages
            JOIN
            u235_rows
            ON
            u235_rows.page_id=u235_pages.page_id AND 
            u235_rows.site_id=u235_pages.site_id
            JOIN
            u235_cols
            ON
            u235_cols.row_id=u235_rows.row_id AND 
            u235_cols.site_id=u235_rows.site_id
            JOIN
            u235_cols_els
            ON
            u235_cols_els.col_id=u235_cols.col_id AND 
            u235_cols_els.site_id=u235_cols.site_id
            WHERE 
            el_id=:el_id AND 
            el_type='art' AND
            u235_pages.site_id=:site_id");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($page=$stm->fetch(PDO::FETCH_OBJ)) {//clear cache for selected pages
                $this->uPage->clear_cache($page->page_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('390'.$e->getMessage());}
    }


    private function get_folder_level($folder_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            folder_id
            FROM
            u235_pages_html
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) die ('{
            "status":"error",
            "msg":"problem with target folder"
            }');

            return (int)$qr->folder_id;
        }
        catch(PDOException $e) {$this->uFunc->error('400'/*.$e->getMessage()*/);}
        return 0;
    }
    private function set_folder_levels($folder_id) {
        if(!isset($this->folder_levels)) {
            $folder_id=$this->get_folder_level($folder_id);
            if($folder_id) {
                $this->folder_levels[$folder_id]=1;
                $this->get_folder_level($folder_id);
            }
        }
    }

    private function update_folder_id() {
        $folder_id=$_POST['folder_id'];
        if(!uString::isDigits($folder_id)) $this->uFunc->error(410);
        $folder_id=(int)$folder_id;

        $pages_ar=explode(',',$_POST['page_id']);

        if($folder_id) {//check if this page_id exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_id
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(420);
            }
            catch(PDOException $e) {$this->uFunc->error('430'/*.$e->getMessage()*/);}
        }

        $pages_list=$skipped_pages_list='';
        for($i=0;$i<(count($pages_ar)-1);$i++) {
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];
                if($folder_id==$page_id) {//folder can't be dropped to itself
                    $skipped_pages_list.=$page_id.',';
                    continue;
                }

                if($folder_id) {
                    //get page info
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                        page_category
                        FROM
                        u235_pages_html
                        WHERE
                        page_id=:page_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                        /** @noinspection PhpUndefinedMethodInspection */
                        if(!$page=$stm->fetch(PDO::FETCH_OBJ)) {
                            $skipped_pages_list.=$page_id.',';
                            continue;
                        }

                        if($page->page_category=='folder') {//parent folder can't be dropped to it's child in any level
                            $this->set_folder_levels($folder_id);
                            if(isset($this->folder_levels[$page_id])) {
                                $skipped_pages_list.=$page_id.',';
                                continue;
                            }
                        }
                    }
                    catch(PDOException $e) {$this->uFunc->error('440'/*.$e->getMessage()*/);}
                }

                $pages_list.=$page_id.',';
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
                    u235_pages_html
                    SET
                    folder_id=:folder_id
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('450'/*.$e->getMessage()*/);}
            }
        }

        echo '{
        "status":"done",
        "pages":"'.$pages_list.'",
        "skipped_pages_list":"'.$skipped_pages_list.'",
        "folder_id":"'.$folder_id.'"
        }';
    }
    private function copy_folders_content($orig_folder_id,$new_folder_id) {
        //get folder's content
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            *
            FROM
            u235_pages_html
            WHERE
            folder_id=:folder_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $orig_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('460'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {
            //get new page_id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("pages")->prepare("SELECT
                page_id
                FROM
                u235_pages_html
                WHERE
                site_id=:site_id
                ORDER BY
                page_id DESC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm1->fetch(PDO::FETCH_OBJ)) $new_page_id=$qr->page_id+1;
                else $new_page_id=1;
            }
            catch(PDOException $e) {$this->uFunc->error('470'/*.$e->getMessage()*/);}

            //insert page
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("pages")->prepare("INSERT INTO
                u235_pages_html (
                page_id,
                site_id,
                page_text,
                page_name,
                page_title,
                page_avatar_time,
                show_avatar,
                page_access,
                navi_parent_page_id,
                navi_personal_menu,
                page_category,
                folder_id,
                navi_parent_menu_id,
                page_alias,
                page_show_title_in_content,
                meta_description,
                meta_keywords,
                page_timestamp,
                page_timestamp_show,
                views_counter,
                page_short_text,
                uDrive_folder_id,
                deleted,
                deleted_directly
                ) VALUES (
                :page_id,
                :site_id,
                :page_text,
                :page_name,
                :page_title,
                :page_avatar_time,
                :show_avatar,
                :page_access,
                :navi_parent_page_id,
                :navi_personal_menu,
                :page_category,
                :folder_id,
                :navi_parent_menu_id,
                :page_category,
                :page_show_title_in_content,
                :meta_description,
                :meta_keywords,
                :page_timestamp,
                :page_timestamp_show,
                :views_counter,
                :page_short_text,
                :uDrive_folder_id,
                :deleted,
                :deleted_directly
                )
                ");
                $site_id=site_id;
                $page->page_name=$page->page_name."_".time();

                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $new_page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_text', $page->page_text,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_name', $page->page_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_title', $page->page_title,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_avatar_time', $page->page_avatar_time,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':show_avatar', $page->show_avatar,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_access', $page->page_access,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_parent_page_id', $page->navi_parent_page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_personal_menu', $page->navi_personal_menu,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_category', $page->page_category,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':folder_id', $new_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_parent_menu_id', $page->navi_parent_menu_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_category', $page->page_category,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_show_title_in_content', $page->page_show_title_in_content,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_description', $page->meta_description,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_keywords', $page->meta_keywords,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_timestamp', $page->page_timestamp,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_timestamp_show', $page->page_timestamp_show,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':views_counter', $page->views_counter,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_short_text', $page->page_short_text,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':uDrive_folder_id', $page->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':deleted', $page->deleted,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':deleted_directly', $page->deleted_directly,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('480'/*.$e->getMessage()*/);}

            if($page->page_category=='folder') /** @noinspection PhpUndefinedVariableInspection */$this->copy_folders_content($page->page_id,$new_page_id);
        }
        return 0;
    }
    private function copypaste_pages() {
        //explode array with pages list
        $pages_ar=explode(',',$this->page_id);

        //Define target folder id
        $target_folder_id=$_POST['folder_id'];
        if(!uString::isDigits($target_folder_id)) $this->uFunc->error(490);
        $target_folder_id=(int)$target_folder_id;

        //Define current folder id
        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) $this->uFunc->error(500);
        $cur_folder_id=(int)$cur_folder_id;

        //check if this target_folder_id exists
        if($target_folder_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_id
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $target_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(510);//Error if target folder id doesn't exists
            }
            catch(PDOException $e) {$this->uFunc->error('520'/*.$e->getMessage()*/);}
        }

        $pages_list=$pages_info='';

        for($i=0;$i<count($pages_ar);$i++) {//Go throw pages
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];

                //get page info
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                    *
                    FROM
                    u235_pages_html
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('530'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if($page=$stm->fetch(PDO::FETCH_OBJ)) {
                    //get new page_id
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm1=$this->uFunc->pdo("pages")->prepare("SELECT
                        page_id
                        FROM
                        u235_pages_html
                        WHERE
                        site_id=:site_id
                        ORDER BY
                        page_id DESC
                        LIMIT 1
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('540'/*.$e->getMessage()*/);}

                    /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
                    if($qr=$stm1->fetch(PDO::FETCH_OBJ)) $new_page_id=$qr->page_id+1;
                    else $new_page_id=1;

                    //insert page
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm1=$this->uFunc->pdo("pages")->prepare("INSERT INTO
                        u235_pages_html (
                        page_id,
                        site_id,
                        page_text,
                        page_name,
                        page_title,
                        page_avatar_time,
                        show_avatar,
                        page_access,
                        navi_parent_page_id,
                        navi_personal_menu,
                        page_category,
                        folder_id,
                        navi_parent_menu_id,
                        page_alias,
                        page_show_title_in_content,
                        meta_description,
                        meta_keywords,
                        page_timestamp,
                        page_timestamp_show,
                        views_counter,
                        page_short_text,
                        uDrive_folder_id
                        ) VALUES (
                        :page_id,
                        :site_id,
                        :page_text,
                        :page_name,
                        :page_title,
                        :page_avatar_time,
                        :show_avatar,
                        :page_access,
                        :navi_parent_page_id,
                        :navi_personal_menu,
                        :page_category,
                        :folder_id,
                        :navi_parent_menu_id,
                        '',
                        :page_show_title_in_content,
                        :meta_description,
                        :meta_keywords,
                        :page_timestamp,
                        :page_timestamp_show,
                        :views_counter,
                        :page_short_text,
                        :uDrive_folder_id
                        )
                        ");
                        $site_id=site_id;

                        $page->page_name=$page->page_name."_".time();
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $new_page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_text', $page->page_text,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_name', $page->page_name,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_title', $page->page_title,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_avatar_time', $page->page_avatar_time,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':show_avatar', $page->show_avatar,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_access', $page->page_access,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_parent_page_id', $page->navi_parent_page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_personal_menu', $page->navi_personal_menu,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_category', $page->page_category,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':folder_id', $target_folder_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':navi_parent_menu_id', $page->navi_parent_menu_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_show_title_in_content', $page->page_show_title_in_content,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_description', $page->meta_description,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_keywords', $page->meta_keywords,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_timestamp', $page->page_timestamp,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_timestamp_show', $page->page_timestamp_show,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':views_counter', $page->views_counter,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_short_text', $page->page_short_text,PDO::PARAM_STR);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':uDrive_folder_id', $page->uDrive_folder_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('550'/*.$e->getMessage()*/);}

                    if($page->page_category=='folder') {
                        $this->set_folder_levels($target_folder_id);//parent folder can't be dropped to it's child in any level
                        if(isset($this->folder_levels[$page_id])) continue;

                        /** @noinspection PhpUndefinedVariableInspection */
                        $this->copy_folders_content($page_id,$new_page_id);
                    }

                    /** @noinspection PhpUndefinedVariableInspection */
                    $pages_list.=$page_id.'='.$new_page_id.',';

                        $pages_info.='
                        "page_title_'.$new_page_id.'":"'.rawurlencode(uString::sql2text($page->page_title,1)).'",
                        "page_name_'.$new_page_id.'":"'.rawurlencode(uString::sql2text($page->page_name,1)).'",
                        "page_category_'.$new_page_id.'":"'.$page->page_category.'",
                        "page_alias_'.$new_page_id.'":"'.rawurlencode(uString::sql2text($page->page_alias,1)).'",
                        "page_timestamp_'.$new_page_id.'":"'.$page->page_timestamp.'",
                        ';
                }
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        if(!isset($page->folder_id)) {
            if(!isset($page)) $page=new stdClass();
            $page->folder_id=$cur_folder_id;
        }

        echo '{
        "status":"done",
        "action":"copypaste",
        "pages":"'.$pages_list.'",
        '.$pages_info.'
        "folder_id":"'.$target_folder_id.'",
        "from_folder_id":"'.$page->folder_id.'"
        }';
    }
    private function cutpaste_pages() {
        $pages_ar=explode(',',$this->page_id);

        $target_folder_id=$_POST['folder_id'];
        if(!uString::isDigits($target_folder_id)) $this->uFunc->error(560);
        $target_folder_id=(int)$target_folder_id;

        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) $this->uFunc->error(570);
        $cur_folder_id=(int)$cur_folder_id;

        //check if target_folder exists
        if($target_folder_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_id
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $target_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(580);
            }
            catch(PDOException $e) {$this->uFunc->error('590'/*.$e->getMessage()*/);}
        }

        $pages_list=$pages_info=$skipped_pages_list='';
        if($cur_folder_id!=$target_folder_id) {//we need only page_category
            $q_page_data="
            page_category,
            folder_id
            ";
        }
        else {//we must get all page_info
            $q_page_data="
            page_title,
            page_name,
            page_category,
            page_alias,
            page_timestamp,
            folder_id
            ";
        }
        //go throw all pages selected
        for($i=0;$i<(count($pages_ar));$i++) {
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];
                if($target_folder_id==$page_id) {//folder can't be cutpasted to itself
                    $skipped_pages_list.=$page_id.',';
                    continue;
                }

                //get page info
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                    ".$q_page_data."
                    FROM
                    u235_pages_html
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $pages_ar[$i],PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('600'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$page=$stm->fetch(PDO::FETCH_OBJ)) {
                    $skipped_pages_list.=$page_id.',';
                    continue;
                }

                if($cur_folder_id==$target_folder_id) {//data for browser
                    $pages_info.='
                    "page_title_'.$pages_ar[$i].'":"'.rawurlencode(uString::sql2text($page->page_title,1)).'",
                    "page_name_'.$pages_ar[$i].'":"'.rawurlencode(uString::sql2text($page->page_name,1)).'",
                    "page_alias_'.$pages_ar[$i].'":"'.rawurlencode(uString::sql2text($page->page_alias,1)).'",
                    "page_category_'.$pages_ar[$i].'":"'.$page->page_category.'",
                    "page_alias_'.$pages_ar[$i].'":"'.$page->page_alias.'",
                    "page_timestamp_'.$pages_ar[$i].'":"'.$page->page_timestamp.'",
                    ';
                }

                if($page->page_category=='folder') {//parent folder can't be dropped to it's child in any level
                    $this->set_folder_levels($target_folder_id);
                    if(isset($this->folder_levels[$page_id])) {
                        $skipped_pages_list.=$page_id.',';
                        continue;
                    }
                }

                $pages_list.=$page_id.',';
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
                    u235_pages_html
                    SET
                    folder_id=:folder_id
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $target_folder_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('610'/*.$e->getMessage()*/);}
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        echo '{
        "status":"done",
        "action":"cutpaste",
        "pages":"'.$pages_list.'",
        '.$pages_info.'
        "skipped_pages_list":"'.$skipped_pages_list.'",
        "folder_id":"'.$target_folder_id.'",
        "cur_folder_id":"'.$cur_folder_id.'",
        "old_folder_id":"'.$page->folder_id.'"
        }';
    }

    public function recycle_pages($action) {
        $pages_ar=explode(',',$this->page_id);

        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') $q_deleted='2';
        elseif($action=='restore') $q_deleted='0';
        else $this->uFunc->error(670);


        $folder_id=0;
        for($i=0;$i<(count($pages_ar));$i++) {
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];

                //get page info
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                    page_category,
                    folder_id
                    FROM
                    u235_pages_html
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('680'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$page=$stm->fetch(PDO::FETCH_OBJ)) continue;

                $folder_id=(int)$page->folder_id;
                if($action=='recycle'||$action=='delete') {
                    if($page->page_category=='folder') $this->uEditor->recycle_pages_from_folder($page_id,$action);
                }

                if($action=='delete') {
                    //update page
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
                        u235_pages_html
                        SET
                        deleted_directly=1,
                        deleted=:deleted
                        WHERE
                        page_id=:page_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':deleted', $q_deleted,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('690'/*.$e->getMessage()*/);}
                }
                else {
                    //check if page's folder exits
                    $q_reset_folder_id='';
                    if($page->folder_id!='0') {
                        if(!$this->uEditor->check_if_folder_exists($page->folder_id)) {
                            $q_reset_folder_id=" folder_id=0, ";
                            $folder_id=0;
                        }
                    }
                    //update page
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
                        u235_pages_html
                        SET
                        ".$q_reset_folder_id.($action=='recycle'?"deleted_directly=1,":'').
                        "deleted=:deleted
                        WHERE
                        page_id=:page_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */
                        /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':deleted', $q_deleted,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('700'/*.$e->getMessage()*/);}
                }

                if($action=='restore') {
                    if($page->page_category=='folder') $this->recycle_pages_from_folder($page_id,$action);
                }
            }
        }

        echo '{
        "status":"done",
        "folder_id":"'.$folder_id.'"
        }';
    }

    private function clean_recycled_bin() {
        //update page
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            deleted=2
            WHERE
            deleted=1 AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('710'/*.$e->getMessage()*/);}

        echo '{"status":"done"}';
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);
        $this->uEditor=new \uEditor\common($this->uCore);

        $this->checkData();


        if(isset($_POST['page_text'])) $this->save_page_text();
        elseif(isset($_POST['page_title'])) $this->save_page_title();
        elseif(isset($_POST['page_name'])) $this->save_page_url();
        elseif(isset($_POST['page_access'])) $this->save_page_access();
        elseif(isset($_POST['navi_parent_menu_id'])) $this->save_page_navi();
        elseif(isset($_POST['page_date'])) $this->save_page_timestamp();
        elseif(isset($_POST['page_short_text'])) $this->save_page_short();
        elseif(isset($_POST['meta_description'])) $this->save_page_seo();
        elseif(isset($_POST['delete_page_avatar'])) $this->delete_page_avatar();
        elseif(isset($_POST['show_avatar'])) $this->show_avatar();
        elseif(isset($_POST['show_avatars_on_pages'])) $this->show_avatars_on_pages();
        elseif(isset($_POST['folder_id'],$_POST['copypaste'])) $this->copypaste_pages();
        elseif(isset($_POST['folder_id'],$_POST['cutpaste'])) $this->cutpaste_pages();
        elseif(isset($_POST['folder_id'])) $this->update_folder_id();
        elseif(isset($_POST['delete'])) $this->recycle_pages('delete');
        elseif(isset($_POST['restore'])) $this->recycle_pages('restore');
        elseif(isset($_POST['recycle'])) $this->recycle_pages('recycle');
        elseif(isset($_POST['clean_recycled'])) $this->clean_recycled_bin();
        else die("{'status' : 'forbidden'}");

        $this->uFunc->set_flag_update_sitemap(1, site_id);
        $this->clear_cache();
    }
}
new page_save_bg($this);
