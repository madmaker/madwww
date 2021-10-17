<?php
namespace uPage\admin;

use PDO;
use PDOException;
use processors\uFunc;
use stdClass;
use uPage\common;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

require_once 'uPage/inc/common.php';

class admin_page_save_bg {
    private $show_title;
    private $folder_levels;
    private $site_css;
    private $uPage;
    private $uSes;
    private $uFunc;
    private $uCore,$page_id,$page_title,$page_url,$page_css;
    private $page_width;

    private function check_data() {
        if(!isset($_POST['field'],$_POST['page_id'])) $this->uFunc->error(10,1);

        if(!isset($_POST['page_id'])) $this->uFunc->error(20,1);
        if(uString::isDigits($_POST['page_id'])) $this->page_id=(int)$_POST['page_id'];
        else {
            if(!uString::isDigits(str_replace(",","",$_POST['page_id']))) $this->uFunc->error(30,1);
            $this->page_id=$_POST['page_id'];
        }

        if($_POST['field']=='page_title') {
            if(!isset($_POST['page_title'])) $this->uFunc->error(40,1);
            $this->page_title=trim($_POST['page_title']);
            if(!strlen($this->page_title)) die("{
            'status':'error',
            'title is empty'
            }");
        }
        elseif($_POST['field']=='page_css') {
            if(!isset($_POST['page_css'])) $this->uFunc->error(50,1);
            $this->page_css=trim($_POST['page_css']);
            $this->page_css=uString::clean_css($this->page_css);
        }
        elseif($_POST['field']=='site_style') {
            if(!isset($_POST['site_css'])) $this->uFunc->error(50,1);
            $this->site_css=trim($_POST['site_css']);
            $this->site_css=uString::clean_css($this->site_css);
        }
        elseif($_POST['field']=='page_url') {
            if(!isset($_POST['page_url'])) $this->uFunc->error(60,1);
            $this->page_url=trim($_POST['page_url']);
            if(uString::isDigits($this->page_url)) die("{
            'status':'error',
            'not only digits'
            }");
        }
        elseif($_POST['field']=='move2folder') return true;
        elseif($_POST['field']=='page_width') {
            if(!isset($_POST['page_width'])) $this->uFunc->error(70,1);
            $this->page_width=(int)$_POST['page_width'];
            if($this->page_width) $this->page_width=1;
            else $this->page_width=0;
        }
        elseif($_POST['field']=='page_seo') {
            if(!isset($_POST['page_keywords'],$_POST['page_description'])) $this->uFunc->error(80,1);
        }
        elseif($_POST['field']=='preview_text') {
            if(!isset($_POST['preview_text'])) $this->uFunc->error(85,1);
        }
        elseif($_POST['field']=='page_timestamp') {
            if(!isset(
                $_POST["page_date"],
                $_POST["page_time"],
                $_POST["user_timezoneOffset"],
                $_POST["page_timestamp_show"]
            )) $this->uFunc->error(90,1);
        }
        elseif(
            $_POST['field']=='folder_id'||
            $_POST['field']=='cutpaste'||
            $_POST['field']=='copypaste'||
            $_POST['field']=='recycle'||
            $_POST['field']=='delete'||
            $_POST['field']=='restore'||
            $_POST['field']=='clean_recycled'||
            $_POST['field']=='preview_image_delete'
        );
        else $this->uFunc->error(95,1);
        return 0;
    }
    private function save_page_title() {
        $page_data=$this->uPage->page_id2data($this->page_id,"show_title,text_folder_id");
        $page_data=(array)$page_data;
        $text_folder_id=$this->uPage->define_text_folder_id($this->page_id,$this->page_title,$page_data["text_folder_id"]);

        if(!isset($_POST['show_title'])) {
            $this->show_title=(int)$page_data->show_title;
        }
        else $this->show_title=(int)$_POST['show_title'];
        if($this->show_title!=1) $_POST['show_title']=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_title=:page_title,
            show_title=:show_title
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $page_title=uString::text2sql($this->page_title);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $this->show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_title=:page_title
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $page_title=uString::text2sql($this->page_title);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $text_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('115'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        die("{
            'status':'done',
            'page_title':'".rawurlencode($this->page_title)."',
            'show_title':'".$this->show_title."'
            }");
    }
    private function save_preview_text() {
        if(!isset($_POST["preview_text"])) {
            print json_encode(array("status"=>"error","msg"=>"no preview text is received"));
            exit;
        }
        $preview_text=$_POST["preview_text"];
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            preview_text=:preview_text
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_text', $preview_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        print json_encode(array(
            'status'=>'done'/*,
            'preview_text'=>$preview_text*/
        ));
        exit;
    }
    private function preview_image_delete() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            preview_img_timestamp=0
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('125'/*.$e->getMessage()*/,1);}

        $this->clear_cache();

        require_once "uPage/inc/page_preview_img.php";
        $page_preview_img=new \page_preview_img($this->uCore);

        print json_encode(array(
            'status'=>'done',
            'img'=>$page_preview_img->get_img_url(500,$this->page_id,0)
        ));
        exit;
    }
    private function check_if_page_url_is_free($url,$index) {
        if($index===0) $new_url=$url;
        else $new_url=$url.'_'.$index;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_pages
            WHERE
            page_url=:page_url AND
            page_id!=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $new_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private function save_page_url() {
        //make page_url
        $url=uString::text2filename(uString::rus2eng($this->page_url));
        //check if page_url is free
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        for($i=0; $this->check_if_page_url_is_free($url,$i); $i++);
        if($i===0) {
            if(uString::isDigits($url)) {//We can't allow url that contains only digits. It may be an ID of page.
                $url='_'.$url;
            }
            $this->page_url=$url;
        }
        else $this->page_url=$url.'_'.$i;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_url=:page_url
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $this->page_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('135'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        die("{
            'status':'done',
            'page_url':'".rawurlencode($this->page_url)."'
            }");
    }
    private function save_page_page_timestamp() {
        if(!uString::isDate($_POST['page_date'])) {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"page_date is wrong"
            ));
            exit;
        }
        if(!uString::isTime($_POST['page_time'])) {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"page_time is wrong"
            ));
            exit;
        }
        $page_timestamp_show=(int)$_POST["page_timestamp_show"]?1:0;

        $dateAr=explode('.',$_POST['page_date']);
        $timeAr=explode(':',$_POST['page_time']);
        $page_timestamp=mktime($timeAr[0],$timeAr[1],0,($dateAr[1]),$dateAr[0],$dateAr[2])/*+$_POST['user_timezoneOffset']*60*/;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_timestamp=:page_timestamp,
            page_timestamp_show=:page_timestamp_show
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp_show', $page_timestamp_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        echo json_encode(array(
            'status'=>'done'/*,
            'page_date'=>$_POST['page_date'],
            'page_time'=>$_POST['page_time']*/
        ));
        exit;
    }
    private function save_page_seo() {
        $page_keywords=htmlspecialchars(trim(strip_tags(stripslashes($_POST['page_keywords']))));
        $page_description=htmlspecialchars(trim(strip_tags(stripslashes($_POST['page_description']))));

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_keywords=:page_keywords,
            page_description=:page_description
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_keywords', $page_keywords,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_description', $page_description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        die("{
            'status':'done',
            'page_keywords':'".rawurlencode($page_keywords)."',
            'page_description':'".rawurlencode($page_description)."'
            }");
    }
    private function save_page_css() {
        $this->uPage->save_page_css($this->page_id,$this->page_css);
        die('{
        "status":"done",
        "page_css":"'.rawurlencode($this->page_css).'"
        }');
    }
    private function save_site_style() {
        if(!isset($_POST["site_primary_color_highlight"])) $_POST["site_primary_color_highlight"]="";
        if(!isset($_POST["site_primary_color"])) $_POST["site_primary_color"]="";
        if(!isset($_POST["site_primary_over_font_color"])) $_POST["site_primary_over_font_color"]="";
        if(!isset($_POST["site_font_color"])) $_POST["site_font_color"]="";
        if(!isset($_POST["sliders_dots_style"])) $_POST["sliders_dots_style"]=4;

        $this->uPage->save_site_css(array(
            "site_css"=>$this->site_css,
            "site_primary_color_highlight"=>$_POST["site_primary_color_highlight"],
            "site_primary_color"=>$_POST["site_primary_color"],
            "site_primary_over_font_color"=>$_POST["site_primary_over_font_color"],
            "site_font_color"=>$_POST["site_font_color"],
            "sliders_dots_style"=>$_POST["sliders_dots_style"]
        ));
        die(json_encode(array(
        "status"=>"done",
        "site_css"=>$this->site_css
        )));
    }
    private function move_page() {
        if(!isset($_POST['folder_id'])) $this->uFunc->error(160,1);
        if(!uString::isDigits($_POST['folder_id'])) $this->uFunc->error(170,1);
        $folder_id=(int)$_POST['folder_id'];

        if($folder_id!=0) $this->move_page_check4no_recursion($folder_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
             SET
            folder_id=:folder_id
             WHERE
             page_id=:page_id AND
             site_id=:site_id
             ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/,1);}

        echo "{
        'status' : 'done'
        }";
    }
    private function move_page_check4no_recursion($folder_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            folder_id
            FROM
            u235_pages
            WHERE
            page_id=:page_id AND
            page_type='folder' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(190,1);
            $parent_folder_id=(int)$qr->folder_id;
            if($parent_folder_id==$this->page_id) $this->uFunc->error(200,1);
            if($parent_folder_id!=0) $this->move_page_check4no_recursion($parent_folder_id);
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/,1);}
    }

    private function check_if_folder_exists($folder_id) {
        //check if folder exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_pages
            WHERE
            deleted=0 AND
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
            else return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('220'/*.$e->getMessage()*/,1);}

        return 0;
    }
    private function recycle_pages_from_folder($folder_id,$action) {
        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') $q_deleted='2';
        elseif($action=='restore') $q_deleted='0';
        else $this->uFunc->error(230,1);

        //get folder's pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id,
            page_type,
            folder_id
            FROM
            u235_pages
            WHERE
            folder_id=:folder_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('240'/*.$e->getMessage()*/,1);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {
            if($action=='recycle'||$action=='delete') {
                if ($page->page_type == 'folder') $this->recycle_pages_from_folder($page->page_id, $action);
            }

            if($action=='delete') {
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("uPage")->prepare("UPDATE
                    u235_pages
                    SET
                    deleted_directly=1,
                    deleted=2
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page->page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('250'/*.$e->getMessage()*/,1);}
            }
            else {
                //check if page's folder exits
                $q_reset_folder_id='';
                if($page->folder_id!='0') {
                    if(!$this->check_if_folder_exists($page->folder_id)) {
                        $q_reset_folder_id=" folder_id=0, ";
                    }
                }
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("uPage")->prepare("UPDATE
                    u235_pages
                    SET
                    ".$q_reset_folder_id.($action=='recycle'?" deleted_directly=0,":'').
                        "deleted=:deleted
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    /** @noinspection PhpUndefinedVariableInspection */$stm1->bindParam(':deleted', $q_deleted,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page->page_id,PDO::PARAM_INT);

                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('260'/*.$e->getMessage()*/,1);}
            }

            if($action=='restore') {
                if ($page->page_type == 'folder') $this->recycle_pages_from_folder($page->page_id, $action);
            }

        }
    }
    public function recycle_pages($action) {
        $pages_ar=explode(',',$this->page_id);

        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') $q_deleted='2';
        elseif($action=='restore') $q_deleted='0';
        else $this->uFunc->error(270,1);


        $folder_id=0;
        for($i=0;$i<(count($pages_ar));$i++) {
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];

                //get page info
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                    page_type,
                    folder_id
                    FROM
                    u235_pages
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('280'/*.$e->getMessage()*/,1);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$page=$stm->fetch(PDO::FETCH_OBJ)) continue;

                $folder_id=(int)$page->folder_id;
                if($action=='recycle'||$action=='delete') {
                    if($page->page_type=='folder') $this->recycle_pages_from_folder($page_id,$action);
                }

                if($action=='delete') {
                    //update page
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                        u235_pages
                        SET
                        deleted_directly=1,
                        deleted=:deleted
                        WHERE
                        page_id=:page_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */
                        /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':deleted', $q_deleted,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('290'/*.$e->getMessage()*/,1);}
                }
                else {
                    //check if page's folder exits
                    $q_reset_folder_id='';
                    if($page->folder_id!='0') {
                        if(!$this->check_if_folder_exists($page->folder_id)) {
                            $q_reset_folder_id=" folder_id=0, ";
                            $folder_id=0;
                        }
                    }
                    //update page
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                        u235_pages
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
                    catch(PDOException $e) {$this->uFunc->error('300'/*.$e->getMessage()*/,1);}
                }

                if($action=='restore') {
                    if($page->page_type=='folder') $this->recycle_pages_from_folder($page_id,$action);
                }
            }
        }

        echo '{
        "status":"done",
        "folder_id":"'.$folder_id.'"
        }';
    }

    private function save_page_width() {

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_width=:page_width
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_width', $this->page_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('310'/*.$e->getMessage()*/,1);}

        $this->clear_cache();
        die("{'status':'done'}");
    }

    private function get_folder_level($folder_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            folder_id
            FROM
            u235_pages
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
        catch(PDOException $e) {$this->uFunc->error('320'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private function set_folder_levels($folder_id) {
        if(!isset($this->folder_levels)) {
            if($folder_id) {
                $folder_id=$this->get_folder_level($folder_id);
                $this->folder_levels[$folder_id]=1;
                if($folder_id) $this->get_folder_level($folder_id);
            }
        }
    }

    private function update_folder_id() {
        if(!isset($_POST['folder_id'])) $this->uFunc->error(400,1);

        $folder_id=$_POST['folder_id'];
        if(!uString::isDigits($folder_id)) $this->uFunc->error(410,1);
        $folder_id=(int)$folder_id;

        $pages_ar=explode(',',$_POST['page_id']);

        if($folder_id) {//check if this page_id exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_id
                FROM
                u235_pages
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(420,1);
            }
            catch(PDOException $e) {$this->uFunc->error('430'/*.$e->getMessage()*/,1);}
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
                        $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                        page_type
                        FROM
                        u235_pages
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

                        if($page->page_type=='folder') {//parent folder can't be dropped to it's child in any level
                            $this->set_folder_levels($folder_id);
                            if(isset($this->folder_levels[$page_id])) {
                                $skipped_pages_list.=$page_id.',';
                                continue;
                            }
                        }
                    }
                    catch(PDOException $e) {$this->uFunc->error('440'/*.$e->getMessage()*/,1);}
                }

                $pages_list.=$page_id.',';
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                    u235_pages
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
                catch(PDOException $e) {$this->uFunc->error('450'/*.$e->getMessage()*/,1);}
            }
        }

        echo '{
        "status":"done",
        "pages":"'.$pages_list.'",
        "skipped_pages_list":"'.$skipped_pages_list.'",
        "folder_id":"'.$folder_id.'"
        }';
    }

    private function copy_folders_content($orig_folder_id,$site_id=site_id) {
        //get folder's content
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            *
            FROM
            u235_pages
            WHERE
            folder_id=:folder_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $orig_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('460'/*.$e->getMessage()*/,1);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->uPage->copy_page($page,$site_id);
            if($page->page_type=='folder') $this->copy_folders_content($page->page_id,$site_id);
        }
        return 0;
    }

    private function copypaste_pages($site_id=site_id) {
        //explode array with pages list
        $pages_ar=explode(',',$this->page_id);

        //Define target folder id
        $target_folder_id=$_POST['folder_id'];
        if(!uString::isDigits($target_folder_id)) $this->uFunc->error(780,1);
        $target_folder_id=(int)$target_folder_id;

        //Define current folder id
        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) $this->uFunc->error(790,1);
        $cur_folder_id=(int)$cur_folder_id;

        //check if this target_folder_id exists
        if($target_folder_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_id
                FROM
                u235_pages
                WHERE
                page_id=:page_id AND
                page_type='folder' AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $target_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(800,1);//Error if target folder id doesn't exists
            }
            catch(PDOException $e) {$this->uFunc->error('810'/*.$e->getMessage()*/,1);}
        }

        $pages_list=$pages_info='';

        for($i=0;$i<count($pages_ar);$i++) {//Go throw pages
            if(uString::isDigits($pages_ar[$i])) {
                $page_id=(int)$pages_ar[$i];

                if($page_data=$this->uPage->page_id2data($page_id,"*",$site_id)) {
                    $new_page_data=$this->uPage->copy_page($page_data,$site_id);

                    if($page_data->page_type=='folder') {//Object for copy is folder
                        $this->set_folder_levels($target_folder_id);//parent folder can't be dropped to it's child in any level
                        if(isset($this->folder_levels[$page_id])) continue;

                        $this->copy_folders_content($page_id,$new_page_data->page_id);
                    }

                    $pages_list.=$page_id.'='.$new_page_data->page_id.',';

                    $pages_info.='
                        "page_title_'.$new_page_data->page_id.'":"'.rawurlencode(uString::sql2text($page_data->page_title,1)).'",
                        "page_type_'.$new_page_data->page_id.'":"'.$page_data->page_type.'",
                        "page_url_'.$new_page_data->page_id.'":"'.rawurlencode(uString::sql2text($page_data->page_url,1)).'",
                        "page_timestamp_'.$new_page_data->page_id.'":"'.$new_page_data->page_timestamp.'",
                        ';
                }
            }
        }

        /** @noinspection PhpUndefinedVariableInspection */
        if(!isset($page->folder_id)) {
            if(!isset($page)) $page=new stdClass();
            $page->folder_id=$cur_folder_id;
        }

        /** @noinspection PhpUndefinedVariableInspection */
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
        if(!uString::isDigits($target_folder_id)) $this->uFunc->error(850,1);
        $target_folder_id=(int)$target_folder_id;

        $cur_folder_id=$_POST['cur_folder_id'];
        if(!uString::isDigits($cur_folder_id)) $this->uFunc->error(860,1);
        $cur_folder_id=(int)$cur_folder_id;

        //check if target_folder exists
        if($target_folder_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                page_id
                FROM
                u235_pages
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $target_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(870,1);
            }
            catch(PDOException $e) {$this->uFunc->error('880'/*.$e->getMessage()*/,1);}
        }

        $pages_list=$pages_info=$skipped_pages_list='';
        if($cur_folder_id!=$target_folder_id) {//we need only page_type
            $q_page_data="
            page_type,
            folder_id
            ";
        }
        else {//we must get all page_info
            $q_page_data="
            page_title,
            page_type,
            page_url,
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
                    $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                    ".$q_page_data."
                    FROM
                    u235_pages
                    WHERE
                    page_id=:page_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $pages_ar[$i],PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('890'/*.$e->getMessage()*/,1);}

                /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
                if(!$page=$stm->fetch(PDO::FETCH_OBJ)) {
                    $skipped_pages_list.=$page_id.',';
                    continue;
                }

                if($cur_folder_id==$target_folder_id) {//data for browser
                    $pages_info.='
                    "page_title_'.$pages_ar[$i].'":"'.rawurlencode(uString::sql2text($page->page_title,1)).'",
                    "page_url'.$pages_ar[$i].'":"'.rawurlencode(uString::sql2text($page->page_url,1)).'",
                    "page_type_'.$pages_ar[$i].'":"'.$page->page_type.'",
                    "page_timestamp_'.$pages_ar[$i].'":"'.$page->page_timestamp.'",
                    ';
                }

                if($page->page_type=='folder') {//parent folder can't be dropped to it's child in any level
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
                    $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                    u235_pages
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
                catch(PDOException $e) {$this->uFunc->error('900'/*.$e->getMessage()*/,1);}
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

    private function clean_recycled_bin() {
        //update page
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
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
        catch(PDOException $e) {$this->uFunc->error('910'/*.$e->getMessage()*/,1);}

        echo '{"status":"done"}';
    }

    private function clear_cache() {
        $this->uPage->clear_cache($this->page_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        if($_POST['field']=='page_title') $this->save_page_title();
        elseif($_POST['field']=='preview_text') $this->save_preview_text();
        elseif($_POST['field']=='preview_image_delete') $this->preview_image_delete();
        elseif($_POST['field']=='page_url') $this->save_page_url();
        elseif($_POST['field']=='page_timestamp') $this->save_page_page_timestamp();
        elseif($_POST['field']=='page_css') $this->save_page_css();
        elseif($_POST['field']=='site_style') $this->save_site_style();
        elseif($_POST['field']=='move2folder') $this->move_page();
        elseif($_POST['field']=='page_width') $this->save_page_width();
        elseif($_POST['field']=='page_seo') $this->save_page_seo();
        elseif($_POST['field']=='copypaste') $this->copypaste_pages();
        elseif($_POST['field']=='cutpaste') $this->cutpaste_pages();
        elseif($_POST['field']=='folder_id') $this->update_folder_id();
        elseif($_POST['field']=='delete') $this->recycle_pages('delete');
        elseif($_POST['field']=='restore') $this->recycle_pages('restore');
        elseif($_POST['field']=='recycle') $this->recycle_pages('recycle');
        elseif($_POST['field']=='clean_recycled') $this->clean_recycled_bin();
        else $this->uFunc->error(920,1);
        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
}
new admin_page_save_bg($this);
