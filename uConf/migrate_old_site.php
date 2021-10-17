<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uDrive\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

//for import
require_once "uDrive/classes/common.php";

class newClass {
    private $uCore;

    //MIGRATE TEXTS
    function STRING_textConvertBkwd($text) {//Конвертация из безопасного формата в обычный
        $text=str_replace('#dblquote','"',$text);//Замена кодового слова двойными кавычками
        $text=str_replace("#quote","'",$text);//замена кодового слова апострофом
        $text=str_replace("#quote2","`",$text);//Замена спец. кавычки у буквы Ё
        $text=str_replace("#slash","\\",$text);//замена кодового слова обратным слешем
        $text=str_replace("##","#",$text);//замена символа ## на исходный #
        $text=stripslashes($text);
        $text=htmlspecialchars_decode($text,ENT_QUOTES) ;
        return $text;
    }
    private function get_new_link_for_file($filename) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT 
            file_id,
            file_hashname 
            FROM 
            u235_files 
            WHERE 
            file_name=:file_name AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_name', $filename,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ))return $qr;

            return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function update_texts() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("balser_site")->prepare("SELECT 
            `text`,
            id,
            `name`,
            title,
            alias,
            show_title_in_content,
            description,
            keywords,
            short_text
            FROM 
            webcr_staticpages
            ");
            //    $site_id=site_id;
            //    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $text=$orig_text=$this->STRING_textConvertBkwd($qr->text);
                $orig_text=str_replace("http://balser.ru","",$orig_text);

                $search_line="mod_textEditor/files/";
                for($i=0;($pos=mb_strpos($text,$search_line))&&$i<50;$i++) {
                    $text=mb_substr($text,$pos+strlen($search_line));
                    $end_pos=mb_strpos($text,'"');
//                    echo $pos."-text id is ".$qr->id."<br>";
                    $filename=mb_substr($text,0,$end_pos);
                    $new_file_name=$this->get_new_link_for_file($filename);
                    if($new_file_name) {
                        $replacement="uDrive/file/".$new_file_name->file_id."/".$new_file_name->file_hashname."/".$filename;
                        $orig_text=str_replace($search_line.$filename,$replacement,$orig_text);
                    }
//                    else echo $search_line.$filename." => WTF???"."<br>";
                }
//                echo $orig_text;

                //get new text id
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

                    if($qr1=$stm1->fetch(PDO::FETCH_OBJ)) $page_id=$qr1->page_id+1;
                    else $page_id=1;
                }
                catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

                //Insert text
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm1=$this->uFunc->pdo("pages")->prepare("INSERT INTO 
                    u235_pages_html (
                    page_id, 
                    site_id, 
                    page_text, 
                    page_name, 
                    page_title, 
                    page_alias, 
                    page_show_title_in_content, 
                    meta_description, 
                    meta_keywords, 
                    page_timestamp, 
                    page_short_text 
                    ) VALUES (
                    :page_id, 
                    :site_id, 
                    :page_text, 
                    :page_name, 
                    :page_title, 
                    :page_alias, 
                    :page_show_title_in_content, 
                    :meta_description, 
                    :meta_keywords, 
                    :page_timestamp, 
                    :page_short_text
                    )
                    ");
                    $site_id=site_id;
                    $page_timestamp=time();
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_text', $orig_text,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_name', $qr->name,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_title', $qr->title,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_alias', $qr->alias,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_show_title_in_content', $qr->show_title_in_content,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_description', $qr->description,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':meta_keywords', $qr->keywords,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':page_short_text', $qr->short_text,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'.$e->getMessage());}
            }
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    //MIGRATE FILES
    private function register_file_type($ext,$mime_type) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
            type_id
            FROM
            u235_file_types
            WHERE
            ext=:ext AND
            mime_type=:mime_type
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ext', $ext,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mime_type', $mime_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        if(!$stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
                type_id
                FROM
                u235_file_types
                ORDER BY
                type_id DESC
                LIMIT 1
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $type_id=$qr->type_id+1;
            else $type_id=1;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("INSERT INTO
                u235_file_types (
                type_id,
                ext,
                mime_type
                ) VALUES (
                :type_id,
                :ext,
                :mime_type
                )
            ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ext', $ext,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mime_type', $mime_type,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
    }
    private function save_file2db($file_id,$orig_file_name,$file_size,$file_ext,$file_mime,$save_file_name) {
        $folder_id=7;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uDrive")->prepare("INSERT INTO
            u235_files (
            file_id,
            file_name,
            file_size,
            file_ext,
            file_mime,
            file_hashname,
            file_timestamp,
            folder_id,
            owner_id,
            site_id
            ) VALUES (
            :file_id,
            :file_name,
            :file_size,
            :file_ext,
            :file_mime,
            :file_hashname,
            :file_timestamp,
            :folder_id,
            1,
            :site_id
            )");
            $site_id=site_id;
            $file_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_size', $file_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_timestamp', $file_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_ext', $file_ext,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_mime', $file_mime,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_name', $orig_file_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_hashname', $save_file_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    private function set_uDrive_file_id($uDrive_file_id,$img_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("balser_site")->prepare("UPDATE
            webcr_mod_texteditor_files
            SET
            uDrive_file_id=:uDrive_file_id
            WHERE
            img_id=:img_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':uDrive_file_id', $uDrive_file_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_id', $img_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }
    private function update_files() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("balser_site")->prepare("SELECT 
            img_id,
            filename
            FROM 
            webcr_mod_texteditor_files 
            ");
        //    $site_id=site_id;
        //    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $file_id=$this->uDrive->get_new_file_id();
                $save_file_name=$this->uFunc->genHash();
                $file_addr=$_SERVER['DOCUMENT_ROOT']."/uConf/files_import/".$qr->filename;
                if(file_exists($file_addr)) {
                    $mime_type = mime_content_type($file_addr);
                    $file_size = filesize($file_addr);
                    $dot = strrpos($qr->filename, '.');
                    $file_ext = substr($qr->filename, $dot + 1);

                    $this->save_file2db($file_id, $qr->filename, $file_size, $file_ext, $mime_type, $save_file_name);

                    $this->register_file_type($file_ext, $mime_type);

                    $folder = 'uDrive/files/' . site_id;
                    $dir = $_SERVER['DOCUMENT_ROOT'] . '/' . $folder . '/' . $file_id . '/'; //Адрес директории для сохранения файла
                    // Create dir
                    if (!file_exists($dir)) mkdir($dir, 0755, true);

                    //copy file
                    copy($file_addr, $dir . $save_file_name);

                    $this->set_uDrive_file_id($file_id,$qr->img_id);

                    echo $qr->filename." - COMPLETE<br>";
                }
                else echo $qr->filename." - NOT FOUND<br>";
            }
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);

        //for import
        $this->uDrive=new common($this->uCore);

//        $this->update_files();//Сделал
//        $this->update_texts();//СДелал

    }
}
$newClass=new newClass($this);