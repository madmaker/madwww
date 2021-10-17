<?php
namespace uEditor;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class common {
    public $uFunc;
    public $uSes;
    private $uCore;

    public function check_if_folder_exists($folder_id) {
        //check if folder exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
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
        catch(PDOException $e) {$this->uFunc->error('uEditor common 1'/*.$e->getMessage()*/);}

        return 0;
    }
    public function recycle_pages_from_folder($folder_id,$action) {
        if($action=='recycle') $q_deleted='1';
        elseif($action=='delete') $q_deleted='2';
        elseif($action=='restore') $q_deleted='0';
        else $this->uFunc->error("uEditor common 2");

        //get folder's pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id,
            page_category,
            folder_id
            FROM
            u235_pages_html
            WHERE
            folder_id=:folder_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 3'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {
            if($action=='recycle'||$action=='delete') {
                if ($page->page_category == 'folder') $this->recycle_pages_from_folder($page->page_id, $action);
            }

            if($action=='delete') {
                //update page
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("pages")->prepare("UPDATE
                    u235_pages_html
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
                catch(PDOException $e) {$this->uFunc->error('uEditor common 4'/*.$e->getMessage()*/);}
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
                    $stm1=$this->uFunc->pdo("pages")->prepare("UPDATE
                    u235_pages_html
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
                catch(PDOException $e) {$this->uFunc->error('uEditor common 5'/*.$e->getMessage()*/);}
            }

            if($action=='restore') {
                if ($page->page_category == 'folder') $this->recycle_pages_from_folder($page->page_id, $action);
            }

        }
    }

    public function copy_text($text_id,$folder_id,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $src=$this->page_id2info($text_id,"*",$source_site_id);

        $new_page_id=$this->get_new_page_id($dest_site_id);
        $new_page_name=$src->page_name.$new_page_id;
        $new_page_alias=$src->page_alias.$new_page_id;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO u235_pages_html (
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
            :page_alias, 
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $new_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_text', $src->page_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $new_page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $src->page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_avatar_time', $src->page_avatar_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatar', $src->show_avatar,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_access', $src->page_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $src->navi_parent_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_personal_menu', $src->navi_personal_menu,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_category', $src->page_category,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_menu_id', $src->navi_parent_menu_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_alias', $new_page_alias,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_show_title_in_content', $src->page_show_title_in_content,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':meta_description', $src->meta_description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':meta_keywords', $src->meta_keywords,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $src->page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp_show', $src->page_timestamp_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':views_counter', $src->views_counter,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_short_text', $src->page_short_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':uDrive_folder_id', $src->uDrive_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':deleted', $src->deleted,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':deleted_directly', $src->deleted_directly,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 6'/*.$e->getMessage()*/);}

        return $new_page_id;
    }

    public function get_new_page_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
            WHERE
            site_id=:site_id
            ORDER BY
            page_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->page_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 10'/*.$e->getMessage()*/);}

        return 1;
    }
    public function create_page($page_text,$page_timestamp,$page_name,$page_title,$page_id,$folder_id,$site_id=site_id) {
        $page_title=uString::text2sql($page_title);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO
            u235_pages_html (
            page_id,
            folder_id,
            page_title,
            page_text,
            page_name,
            page_timestamp,
            site_id
            ) VALUES (
            :page_id,
            :folder_id,
            :page_title,
            :page_text,
            :page_name,
            :page_timestamp,
            :site_id
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_text', $page_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 20'/*.$e->getMessage()*/);}
    }
    public function page_title2page_name_converter($page_title) {
        $page_name=str_replace(' ','_',strtolower(preg_replace('/[^\w\d_-]*/','',str_replace(' ','_',uString::rus2eng($page_title)))));
        return $this->check_if_name_is_unique($page_name);
    }
    private function check_if_name_is_unique($page_name_arg) {
        $nameIsUnique=false;
        for($i='';!$nameIsUnique;) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                page_name
                FROM
                u235_pages_html
                WHERE
                page_name=:page_name AND
                site_id=:site_id
                ");
                $page_name=$page_name_arg.$i;
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $nameIsUnique=true;
                else {
                    if($i=='') $i=0;
                    $i++;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('uEditor common 30'/*.$e->getMessage()*/);}
        }
        return $page_name_arg.$i;
    }

    public function page_id2info($page_id,$q_select="page_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
                ".$q_select."
                FROM
                u235_pages_html
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 35'/*.$e->getMessage()*/);}
        return 0;
    }

    //Folder
    public function create_folder($folder_name,$parent_folder_id=0) {
        try {//get new folder id
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 40'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $folder_id=$qr->page_id+1;
        else $folder_id=1;


        if($parent_folder_id) {//check if parent folder exists
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
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $parent_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uEditor common 50'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $parent_folder_id=0;
        }

        try {//create folder
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO
            u235_pages_html (
            page_id,
            page_title,
            page_category,
            page_timestamp,
            folder_id,
            site_id
            ) VALUES (
            :page_id,
            :page_title,
            'folder',
            :page_timestamp,
            :folder_id,
            :site_id
            )
            ");
            $page_timestamp=time();
            $page_title=trim($folder_name);
            if(!strlen($page_title)) /** @noinspection PhpUndefinedMethodInspection */$page_title=$this->text("Folder"/*Папка*/)." ".$folder_id;
            $page_title=uString::text2sql($page_title);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $parent_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 60'/*.$e->getMessage()*/);}

        return $folder_id;
    }
    public function get_module_folder_id($module) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
            folder_id 
            FROM 
            mod_folders 
            WHERE 
            module=:module AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':module', $module,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 70'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->folder_id;
        else {//set new folder
            if($module=="uPage") {
                $folder_name=$this->text("uPage module folder title"/*Страницы*/);
                $parent_folder_id=0;
            }

            $folder_id=$this->create_folder($folder_name,$parent_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO
                 mod_folders (
                 module,
                 folder_id,
                 site_id
                 ) VALUES (
                 :module,
                 :folder_id,
                 :site_id
                 )
                 ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':module', $module,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uEditor common 80'/*.$e->getMessage()*/);}

            return $folder_id;
        }
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uEditor','common'),$str);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
    }
}
