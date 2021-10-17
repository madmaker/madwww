<?php
namespace uConf;

use PDO;
use PDOException;
use processors\uFunc;
use uCore;
use uPage\common;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";

class migrate_texts2pages {
    /**
     * @var common
     */
    private $uPage;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;

    private function get_unattached_texts($site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT DISTINCT
            page_id,
            page_name,
            page_title,
            page_avatar_time,
            show_avatar,
            navi_parent_page_id,
            page_alias,
            page_show_title_in_content,
            meta_description,
            meta_keywords,
            page_timestamp,
            page_timestamp_show,
            views_counter,
            page_short_text,
            uDrive_folder_id
            FROM
            `madmakers_pages`.`u235_pages_html`
            LEFT JOIN
            `madmakers_uPage`.`u235_cols_els`
            ON
            el_id=page_id AND
            `madmakers_pages`.`u235_pages_html`.site_id=`madmakers_uPage`.`u235_cols_els`.site_id AND
            el_type='art'
            WHERE
            deleted=0 AND
            `madmakers_pages`.`u235_pages_html`.site_id=:site_id AND
            cols_els_id IS NULL AND
            `madmakers_pages`.`u235_pages_html`.page_category!='system' AND
            `madmakers_pages`.`u235_pages_html`.page_category!='folder'
            GROUP BY(page_id)
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1583878090'/*.$e->getMessage()*/);}
        return false;
    }

    private function update_text_folder_id($text_id,$new_folder_id,$site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE 
            u235_pages_html
            SET
            folder_id=:folder_id,
            page_alias='', 
            page_name='', 
            navi_parent_page_id=0, 
            page_show_title_in_content=0, 
            meta_description='', 
            meta_keywords='', 
            page_timestamp_show=0, 
            views_counter=0, 
            page_short_text=0, 
            uDrive_folder_id=0
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $new_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $text_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583879212'/*.$e->getMessage()*/);}
    }

    private function create_el_config($cols_els_id,$site_id,$show_avatar) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            el_config_art (
            cols_els_id,
            site_id,
            show_title,
            title_is_link2art,
            show_avatar,
            short_text_is_link2art,
            show_short_text,
            show_more_btn,
            show_text
            ) VALUES (
            :cols_els_id,
            :site_id,
            0,
            0,
            :show_avatar,
            0,
            0,
            0,
            1
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatar', $show_avatar,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583882513'/*.$e->getMessage()*/);}
    }

    private function copy_text_data2page($page_id,$site_id,$page_url,$old_text_page_name,$preview_img_timestamp,$navi_parent_page_id,$show_title,$page_description,$page_keywords,$page_timestamp,$page_timestamp_show,$views_counter,$preview_text,$uDrive_folder_id) {

        if($page_url==="") {
            $page_url=$old_text_page_name;
            $old_text_page_name="";
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            u235_pages
            SET
            page_url=:page_url,
            old_text_page_name=:old_text_page_name,
            preview_img_timestamp=:preview_img_timestamp,
            navi_parent_page_id=:navi_parent_page_id,
            show_title=:show_title,
            page_description=:page_description,
            page_keywords=:page_keywords,
            page_timestamp=:page_timestamp,
            page_timestamp_show=:page_timestamp_show,
            views_counter=:views_counter,
            preview_text=:preview_text,
            uDrive_folder_id=:uDrive_folder_id
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $page_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':old_text_page_name', $old_text_page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_img_timestamp', $preview_img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $navi_parent_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_description', $page_description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_keywords', $page_keywords,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp_show', $page_timestamp_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':views_counter', $views_counter,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_text', $preview_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':uDrive_folder_id', $uDrive_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583883351'/*.$e->getMessage()*/);}
    }

    private function copy_text_primary_img2page($site_id,$text_id,$page_id) {
        $text_img = 'uEditor/page_avatars/'.$site_id.'/'.$text_id.'/orig.jpg';
        $page_img_dir='uPage/preview_images/'.$site_id.'/'.$page_id;
        $page_img = $page_img_dir.'/orig.jpg';

        if(!file_exists($text_img)) return false;

        if(!file_exists($page_img_dir)) mkdir($page_img_dir,0755,true);
        if(!$this->uFunc->create_empty_index($page_img_dir)) $this->uFunc->error(1583883720);

        copy($text_img,$page_img);

        return true;
    }

    private function get_text_rubrics($text_id,$site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
            rubric_id 
            FROM 
            u235_urubrics_pages 
            WHERE
            page_id=:page_id AND
            `mod`=0 AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $text_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1583885040'/*.$e->getMessage()*/);}
        return false;
    }
    private function unattach_text_from_rubrics($text_id,$site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("DELETE FROM  
            u235_urubrics_pages 
            WHERE
            page_id=:page_id AND
            `mod`=0 AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $text_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583885052'/*.$e->getMessage()*/);}
    }

    private function attach_page_to_rubric($page_id,$rubric_id,$site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("INSERT INTO 
            u235_urubrics_pages (
            rubric_id,
            page_id,
            site_id,
            `mod`
            ) VALUES (
            :rubric_id,
            :page_id,
            :site_id,
            1
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583885236'/*.$e->getMessage()*/);}
    }

    function __construct ($site_id,&$uCore) {
        $this->uCore=&$uCore;

        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(17)) {
            print "forbidden";
            exit;
        }
        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);

        print "<h3>Getting unattached texts</h3>";
        if(!$texts_stm=$this->get_unattached_texts($site_id)) {
            print "<p>Error 1583878241 occurred</p>";
            exit;
        }
        /** @noinspection PhpUndefinedMethodInspection */
        for($text_counter=0;$text=$texts_stm->fetch(PDO::FETCH_OBJ);$text_counter++) {
            $text_id=(int)$text->page_id;
            $page_title= uString::sql2text($text->page_title,1);//page_title
            $page_url= uString::sql2text($text->page_alias,1);//page_url
            $old_text_page_name= uString::sql2text($text->page_name,1);//old_text_page_name
            $preview_img_timestamp= (int)$text->page_avatar_time;//preview_img_timestamp

            $show_avatar= $text->show_avatar;


            $navi_parent_page_id= $text->navi_parent_page_id;//navi_parent_page_id
            if(mb_strpos($navi_parent_page_id,"s")===0) $navi_parent_page_id=0;

            $show_title= (int)$text->page_show_title_in_content;//show_title
            $page_description= uString::sql2text($text->meta_description,1);//page_description
            $page_keywords= uString::sql2text($text->meta_keywords,1);//page_keywords
            $page_timestamp= (int)$text->page_timestamp;//page_timestamp
            $page_timestamp_show= (int)$text->page_timestamp_show;//page_timestamp_show
            $views_counter= (int)$text->views_counter;//views_counter
            $preview_text= uString::sql2text($text->page_short_text,1);//preview_text
            $uDrive_folder_id= (int)$text->uDrive_folder_id;//uDrive_folder_id


            print "<hr>
            <h4>Text # $text_id</h4>";

            //CREATE NEW PAGE
            print "<p>Creating new page</p>";

            if(!$page=$this->uPage->create_empty_page(uString::text2sql($page_title),$page_url,$site_id)) {
                print "<h4 class='bg-danger'>ERROR. Empty page is not created</h4>";
                continue;
            }
            $text_folder_id=(int)$page["text_folder_id"];
            $page_id=(int)$page["page_id"];

            print "<p>page #$page_id is created with text_folder_id #$text_folder_id</p>";

            print "<p>Updating text's #$text_id info: folder_id=$text_folder_id, page_alias='', page_name='', navi_parent_page_id=0, page_show_title_in_content=0, meta_description='', meta_keywords='', page_timestamp_show=0, views_counter=0, page_short_text=0, uDrive_folder_id=0</p>";
            $this->update_text_folder_id($text_id,$text_folder_id,$site_id);


            //ADD ROW
            print "<p>Creating new row on page</p>";

            $row_id=(int)$this->uPage->get_new_row_id($site_id);
            print "<p>row id is #$row_id, page_id is #$page_id, row_pos is 1, row_content_centered is 1</p>";

            $this->uPage->create_row($row_id,$page_id,1,1,$site_id);


            //ADD COL
            print "<p>Creating new column in row</p>";
            $col_id=(int)$this->uPage->get_new_col_id($site_id);

            $this->uPage->create_col($col_id,$row_id,1,12,12,12,12,$site_id);;
            print "<p>col id is #$col_id, row_id is #$row_id, col_pos is 1, width for lg,md,sm,xs is 12</p>";


            //ATTACH TEXT TO PAGE
            print "<p>Attaching text to column</p>";

            $cols_els_id=$this->uPage->get_new_cols_els_id($site_id);

            $this->uPage->create_el($cols_els_id,$col_id,"art",1,"",$text_id,$site_id);
            print "<p>cols_els_id is #$cols_els_id, col_id is #$col_id, el_type is art, el_pos is 1, el_id is #$text_id</p>";


            //CREATE EL CONFIG
            $this->create_el_config($cols_els_id,$site_id,$show_avatar);
            print "<p>create el config</p>";


            //COPY TEXT DATA TO PAGE
            $this->copy_text_data2page($page_id,$site_id,$page_url,$old_text_page_name,$preview_img_timestamp,$navi_parent_page_id,$show_title,$page_description,$page_keywords,$page_timestamp,$page_timestamp_show,$views_counter,$preview_text,$uDrive_folder_id);

            print "<p>Copied data from text to page</p>";

            //COPY PRIMARY IMG
            if($preview_img_timestamp) {
                if ($this->copy_text_primary_img2page($site_id, $text_id, $page_id)) print "<p>Copied primary img from text to page</p>";
                else print "<h4 class='bg-primary'>ERROR. TEXT's PRIMARY IMAGE IS NOT FOUND";
            }
            else print "<p>primary img timestamp is 0 - skip image copy</p>";

            print "<h4>Attach page to rubrics</h4>";
            //ATTACH PAGE TO RUBRICS
            $rubrics_stm=$this->get_text_rubrics($text_id,$site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($rubric=$rubrics_stm->fetch(PDO::FETCH_OBJ)) {
                $rubric_id=(int)$rubric->rubric_id;
                print "<p>attach page #$page_id to rubric #$rubric_id</p>";
                $this->attach_page_to_rubric($page_id,$rubric_id,$site_id);
            }

            print "<p>Unattach text #$text_id from all rubrics</p>";
            $this->unattach_text_from_rubrics($text_id,$site_id);

        }

        print "<h3>FINISHED</h3>";
    }
}
