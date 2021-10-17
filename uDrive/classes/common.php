<?php
namespace uDrive;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class common {
    private $uSes;
    private $uFunc;
    private $uCore;

    //Folder
    public function create_folder($folder_name,$parent_folder_id=0) {
        try {//get new folder id
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
            file_id
            FROM 
            u235_files 
            WHERE 
            site_id=:site_id
            ORDER BY
            file_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $folder_id=$qr->file_id+1;
            else $folder_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('uDrive common 10'/*.$e->getMessage()*/);}

        if($parent_folder_id) {//check if parent folder exists
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
                file_id 
                FROM 
                u235_files 
                WHERE
                file_id=:file_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $parent_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $parent_folder_id=0;
            }
            catch(PDOException $e) {$this->uFunc->error('uDrive common 20'/*.$e->getMessage()*/);}
        }

        try {//create folder
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("INSERT INTO
            u235_files (
            file_id,
            file_name,
            file_mime,
            file_timestamp,
            folder_id,
            owner_id,
            site_id
            ) VALUES (
            :file_id,
            :file_name,
            'folder',
            :file_timestamp,
            :folder_id,
            :owner_id,
            :site_id
            )
            ");
            $owner_id=$this->uSes->get_val("user_id");
            $file_timestamp=time();
            $folder_name=trim($folder_name);
            if(!strlen($folder_name)) $folder_name=$this->text("Folder"/*Папка*/)." ".$folder_id;
            $file_name=uString::text2sql($folder_name);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':owner_id', $owner_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $parent_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_timestamp', $file_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_name', $file_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uDrive common 30'/*.$e->getMessage()*/);}

        return $folder_id;
    }
    public function get_module_folder_id($module) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
            folder_id 
            FROM 
            u235_mod_folders 
            WHERE 
            module=:module AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':module', $module,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uDrive common 40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->folder_id;
        else {//set new folder
            if($module=="uCat") {
                $folder_name=$this->text("uCat module folder title"/*MAD Каталог*/);
                $parent_folder_id=0;
            }
            elseif($module=="uCat_sects") {
                $folder_name=$this->text("uCat sections folder title"/*Разделы каталога*/);
                $parent_folder_id=$this->get_module_folder_id("uCat");
            }
            elseif($module=="uCat_cats") {
                $folder_name=$this->text("uCat cats folder title"/*Категории каталога*/);
                $parent_folder_id=$this->get_module_folder_id("uCat");
            }
            elseif($module=="uCat_items") {
                $folder_name=$this->text("uCat items folder title"/*Товары каталога*/);
                $parent_folder_id=$this->get_module_folder_id("uCat");
            }
            elseif($module=="uCat_arts") {
                $folder_name=$this->text("uCat arts folder title"/*Статьи о товарах каталога*/);
                $parent_folder_id=$this->get_module_folder_id("uCat");
            }
            elseif($module=="uPage") {
                $folder_name=$this->text("uPage module folder title"/*Страницы*/);
                $parent_folder_id=0;
            }
            elseif($module=="uSlider") {
                $folder_name=$this->text("uSlider module folder title"/*Слайдеры*/);
                $parent_folder_id=0;
            }
            elseif($module=="uEditor") {
                $folder_name=$this->text("uEditor module folder title"/*Статьи*/);
                $parent_folder_id=0;
            }
            elseif($module=="configurator") {
                $folder_name=$this->text("configurator module folder name");
                $parent_folder_id=0;
            }
            elseif($module=="configurator_page") {
                $folder_name=$this->text("uPage module folder title");
                $parent_folder_id=$this->get_module_folder_id("configurator");
            }

            $folder_id=$this->create_folder($folder_name,$parent_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("INSERT INTO
                 u235_mod_folders (
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
            catch(PDOException $e) {$this->uFunc->error('uDrive common 50'/*.$e->getMessage()*/);}

            return $folder_id;
        }
    }

    private $file_exists_ar;
    //FILE
    public function file_exists($file_id,$site_id=site_id) {
        if(!isset($this->file_exists_ar[$site_id][$file_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
                file_id
                FROM
                u235_files
                WHERE
                file_id=:file_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                /** @noinspection PhpUndefinedMethodInspection */
                $this->file_exists_ar[$site_id][$file_id]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uDrive common 60'/*.$e->getMessage()*/,1);}
        }
        return $this->file_exists_ar[$site_id][$file_id];
    }
    public function update_file_access($file_id,$file_access,$site_id=site_id) {
        if(!$this->uCore->query("uDrive","UPDATE
        `u235_files`
        SET
        `file_access`='".$file_access."'
        WHERE
        `file_id`='".$file_id."' AND
        `site_id`='".$site_id."'
        ")) $this->uFunc->error('uDrive common 70');
    }
    private $file_id2data_ar;
    public function file_id2data($file_id,$q_select="file_name,
                file_size,
                file_ext,
                file_mime,
                file_hashname,
                file_timestamp,
                deleted,
                deleted_directly,
                owner_id,
                file_is_used,
                file_protected,
                file_access,
                file_access",$site_id=site_id) {
        if(!isset($this->file_id2data_ar[$site_id][$file_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
                ".$q_select."
                FROM 
                u235_files 
                WHERE 
                file_id=:file_id AND 
                site_id=:site_id");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $this->file_id2data_ar[$site_id][$file_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uDrive common 80'/*.$e->getMessage()*/);}
        }
        return $this->file_id2data_ar[$site_id][$file_id][$q_select];
    }
    public function get_new_file_id($site_id=site_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `site_id`='".$site_id."'
            ORDER BY
            `file_id` DESC
            LIMIT 1
            ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(5);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            return $qr->file_id+1;
        }
        return 1;
    }
    //USAGE
    private $check_if_usage_isset_ar;
    public function check_if_usage_isset($file_id,$file_mod,$handler_type,$handler_id,$site_id=site_id) {
        if(!isset($this->check_if_usage_isset_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id])) {
            if(!$query=$this->uCore->query("uDrive","SELECT
                `usage_id`
                FROM
                `u235_files_usage`
                WHERE
                `file_id`='".$file_id."' AND
                `file_mod`='".$file_mod."' AND
                `handler_type`='".$handler_type."' AND
                `handler_id`='".$handler_id."' AND
                `site_id`='".$site_id."'
                ")) $this->uFunc->error('uDrive common 90');
            if(mysqli_num_rows($query)) {
                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$query->fetch_object();
                $this->check_if_usage_isset_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id]=$qr->usage_id;
            }
            else $this->check_if_usage_isset_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id]=0;
        }
        return $this->check_if_usage_isset_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id];
    }
    public function get_new_usage_id($site_id=site_id) {
        if(!$query=$this->uCore->query("uDrive","SELECT
        `usage_id`
        FROM
        `u235_files_usage`
        WHERE
        `site_id`='".$site_id."'
        ORDER BY
        `usage_id` DESC
        LIMIT 1
        ")) $this->uFunc->error('uDrive common 100');
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            return $qr->usage_id+1;
        }
        return 1;
    }
    private $add_file_usage_ar;
    public function add_file_usage($file_id,$file_mod,$handler_type,$handler_id,$site_id=site_id) {
        if(!isset($this->add_file_usage_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id])) {
            if(!$usage_id=$this->check_if_usage_isset($file_id,$file_mod,$handler_type,$handler_id,$site_id)) {//let's add
                $usage_id=$this->get_new_usage_id();

                if(!$this->uCore->query("uDrive","INSERT INTO
                `u235_files_usage` (
                `usage_id`,
                `file_id`,
                `file_mod`,
                `handler_type`,
                `handler_id`,
                `site_id`
                ) VALUES (
                '".$usage_id."',
                '".$file_id."',
                '".$file_mod."',
                '".$handler_type."',
                '".$handler_id."',
                '".site_id."'
                )
                ")) $this->uFunc->error('uDrive common 110');
            }
            $this->add_file_usage_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id]=$usage_id;
        }
        return $this->add_file_usage_ar[$site_id][$file_id][$file_mod][$handler_type][$handler_id];
    }

    public function text($str) {
        return $this->uCore->text(array('uDrive','common'),$str);
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
    }
}
