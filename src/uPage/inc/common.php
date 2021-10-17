<?php
namespace uPage;

use PDO;
use PDOException;
use processors\uFunc;
use uDrive\file_update;
use uPage\admin\art;
use uPage\admin\banner;
use uPage\admin\bootstrap_carousel;
use uPage\admin\card;
use uPage\admin\code;
use uPage\admin\flip_book;
use uPage\admin\form;
use uPage\admin\gallery;
use uPage\admin\gmap;
use uPage\admin\login_btn;
use uPage\admin\menu;
use uPage\admin\owl_carousel;
use uPage\admin\page_filter;
use uPage\admin\rubrics_arts;
use uPage\admin\share;
use uPage\admin\spacer;
use uPage\admin\tabs;
use uPage\admin\ticker;
use uPage\admin\uCat_latest;
use uPage\admin\uCat_latest_articles_slider;
use uPage\admin\uCat_new_items;
use uPage\admin\uCat_popular;
use uPage\admin\uCat_sale;
use uPage\admin\uCat_search;
use uPage\admin\search;
use uPage\admin\uCat_sects;
use uPage\admin\uEditor_texts_top;
use uPage\admin\uEvents_list;
use uPage\admin\urubrics_arts_column;
use uPage\admin\urubrics_tiles;
use uPage\admin\uSubscr_news_form;
use uString;

require_once "processors/classes/uFunc.php";
require_once "uEditor/classes/common.php";

class common {
    public $uFunc;
    public $uCore;
    private $uPage;

    //CACHE
    //Clear cache
    private function clean_rubrics_cache($page_id,$site_id=site_id) {
        try {
            $this->uFunc->pdo("uPage");
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
            `madmakers_uPage`.u235_rows.page_id
            FROM 
            `madmakers_pages`.u235_urubrics_pages
            JOIN
            `madmakers_uPage`.u235_cols_els
            ON
            `madmakers_pages`.u235_urubrics_pages.rubric_id=el_id AND
            `madmakers_uPage`.u235_cols_els.site_id=`madmakers_pages`.u235_urubrics_pages.site_id
            JOIN
            `madmakers_uPage`.u235_cols
            ON
            `madmakers_uPage`.u235_cols.col_id=`madmakers_uPage`.u235_cols_els.col_id AND
            `madmakers_uPage`.u235_cols.site_id=`madmakers_uPage`.u235_cols_els.site_id
            JOIN 
            `madmakers_uPage`.u235_rows
            ON
            `madmakers_uPage`.u235_cols.row_id=`madmakers_uPage`.u235_rows.row_id AND
            `madmakers_uPage`.u235_cols.site_id=`madmakers_uPage`.u235_rows.site_id
            WHERE
            `madmakers_pages`.u235_urubrics_pages.page_id=:page_id AND
            `madmakers_pages`.u235_urubrics_pages.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($page=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->uFunc->rmdir("uPage/cache/".$site_id.'/'.$page->page_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 5'/*.$e->getMessage()*/);}
    }
    public function clear_cache($page_id,$site_id=site_id) {
        $this->uFunc->rmdir("uPage/cache/".$site_id.'/'.$page_id);
        $this->clean_rubrics_cache($page_id,$site_id);
    }
    public function clean_cache_for_site($site_id=site_id) {
        $this->uFunc->rmdir("uPage/cache/".$site_id);
        $this->uFunc->rmdir("uSlider/cache/".$site_id);
    }
    public function clear_cache4uCat_latest($site_id=site_id) {
        //clear uPage cache
        try {//get uPage_id that uses any item
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
            u235_rows.row_id=u235_cols.row_id AND
            u235_rows.site_id=u235_cols.site_id
            JOIN
            u235_cols_els
            ON
            u235_cols.col_id=u235_cols_els.col_id AND
            u235_cols.site_id=u235_cols_els.site_id
            WHERE 
            el_type='uCat_latest' AND
            u235_pages.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 10'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        while($res=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->clear_cache($res->page_id,$site_id);
        }
    }
    public function clean_cache4uRubrics($rubric_id) {
        //get pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT DISTINCT 
            u235_pages.page_id
            FROM
            u235_pages
            LEFT JOIN
            u235_rows
            ON
            u235_rows.page_id=u235_pages.page_id AND
            u235_rows.site_id=u235_pages.site_id
            LEFT JOIN 
            u235_cols
            ON
            u235_cols.row_id=u235_rows.row_id AND
            u235_cols.site_id=u235_rows.site_id
            LEFT JOIN 
            u235_cols_els
            ON
            u235_cols_els.col_id=u235_cols.col_id AND
            u235_cols_els.site_id=u235_cols.site_id
            WHERE
            el_id=:el_id AND 
            (
                el_type='rubrics_arts' OR 
                el_type='rubrics_arts_column' OR
                el_type='rubrics_tiles'
            ) AND
            u235_pages.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                uFunc::rmdir("uPage/cache/".site_id.'/'.$qr->page_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 20'.$e->getMessage());}
    }
    public function get_site_css_file($site_id=site_id) {
        $dir="uPage/css/site_css/".$site_id;
        if(!file_exists($dir."/site_css.css")) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uPage")->prepare("SELECT 
                site_css,
                site_primary_color,
                site_primary_color_highlight,
                site_primary_over_font_color,
                site_font_color,
                sliders_dots_style
                FROM 
                site_style 
                WHERE 
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('uPage/common 30'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if ($qr = $stm->fetch(PDO::FETCH_OBJ)) {
                $site_inline_css = $qr->site_css;
                $primary_color=$qr->site_primary_color;
                $primary_color_highlight=$qr->site_primary_color_highlight;
                $primary_over_font_color=$qr->site_primary_over_font_color;
                $font_color=$qr->site_font_color;
            }
            else {
                $site_inline_css  = "";
                $primary_color="#337ab7";
                $primary_color_highlight="#23527c";
                $primary_over_font_color="#ffffff";
                $font_color="#000000";
            }

            if(uString::isHexColor($primary_color)&&!uString::isMadColor($primary_color)) $primary_color="#".$primary_color;
            $primary_color_inverse=$this->uFunc->color_inverse($primary_color);

            if(uString::isHexColor($primary_color_highlight)&&!uString::isMadColor($primary_color_highlight)) $primary_color_highlight="#".$primary_color_highlight;
            $primary_color_highlight_inverse=$this->uFunc->color_inverse($primary_color_highlight);

            if(uString::isHexColor($primary_over_font_color)&&!uString::isMadColor($primary_over_font_color)) $primary_over_font_color="#".$primary_over_font_color;
            $primary_over_font_color_inverse=$this->uFunc->color_inverse($primary_over_font_color);

            if(uString::isHexColor($font_color)&&!uString::isMadColor($font_color)) $font_color="#".$font_color;
            $font_color_inverse=$this->uFunc->color_inverse($font_color);


            $site_css="";
            $site_css.=file_get_contents("uPage/css/common.less");
            $site_css.=file_get_contents("uCat/css/sects.less");
            $site_css.=file_get_contents("uCat/css/sidebar.less");
            $site_css.=file_get_contents("uCat/css/items.less");
            $site_css.=file_get_contents("uCat/css/navbar_top.less");
            $site_css.=file_get_contents("uPage/css/primary_colors.less");
            $site_css.=file_get_contents("uSlider/css/slider_markers.less");
            //art
            //banner
            $site_css.=file_get_contents("uPage/elements/banner/banner.less");
            //bootstrap_carousel
            $site_css.=file_get_contents("uPage/elements/bootstrap_carousel/bootstrap_carousel.less");
            //card
            $site_css.=file_get_contents("uPage/elements/card/card.less");
            //code
            //flip_book
            //form
            //gallery
            //gmap
            //login_btn
            //menu
            //owl_carousel
            $site_css.=file_get_contents("uPage/elements/owl_carousel/owl_carousel.less");
            //page_filter
            $site_css.=file_get_contents("uPage/elements/page_filter/page_filter.less");
            //rubrics_arts
            $site_css.=file_get_contents("uPage/elements/rubrics_arts/rubrics_arts.less");
            //rubrics_arts_column
            $site_css.=file_get_contents("uPage/elements/rubrics_arts_column/rubrics_arts_column.less");
            //rubrics_tiles
            $site_css.=file_get_contents("uPage/elements/rubrics_tiles/rubrics_tiles.less");
            //search
            $site_css.=file_get_contents("uPage/elements/search/search.less");
            //share
            $site_css.=file_get_contents("uPage/elements/share/share.less");
            //spacer
            //tabs
            $site_css.=file_get_contents("uPage/elements/tabs/tabs.less");
            //ticker
            $site_css.=file_get_contents("uPage/elements/ticker/ticker.less");
            //uCat_latest
            $site_css.=file_get_contents("uPage/elements/uCat_latest/uCat_latest.less");
            //uCat_latest_articles_slider
            $site_css.=file_get_contents("uPage/elements/uCat_latest_articles_slider/uCat_latest_articles_slider.less");
            //uCat_new_items
            $site_css.=file_get_contents("uPage/elements/uCat_new_items/uCat_new_items.less");
            //uCat_popular
            $site_css.=file_get_contents("uPage/elements/uCat_popular/uCat_popular.less");
            //uCat_sale
            $site_css.=file_get_contents("uPage/elements/uCat_sale/uCat_sale.less");
            //uCat_search
            //uCat_sects
            $site_css.=file_get_contents("uPage/elements/uCat_sects/uCat_sects.less");
            //uEditor_texts_top
            //uEvents_calendar
            //uEvents_dates
            //uEvents_list
            //uSubscr_news_form

            $site_css.=$site_inline_css;

            $site_css=str_replace("@mad_primary_color_highlight_inverse",$primary_color_highlight_inverse,$site_css);
            $site_css=str_replace("@mad_primary_color_highlight",$primary_color_highlight,$site_css);
            $site_css=str_replace("@mad_primary_color_inverse",$primary_color_inverse,$site_css);
            $site_css=str_replace("@mad_primary_color",$primary_color,$site_css);
            $site_css=str_replace("@mad_primary_over_font_color_inverse",$primary_over_font_color_inverse,$site_css);
            $site_css=str_replace("@mad_primary_over_font_color",$primary_over_font_color,$site_css);
            $site_css=str_replace("@mad_site_font_color_inverse",$font_color_inverse,$site_css);
            $site_css=str_replace("@mad_site_font_color",$font_color,$site_css);

//            $css_pre.=$primary_colors_css;

//            $site_css=$css_pre/*.$site_css*/;

            $css_file = fopen($dir. "/site_css.css", 'w');
            fwrite($css_file, $site_css);
            fclose($css_file);
        }

        return $dir."/site_css.css";
    }

    //FOLDERS
    public function create_folder($folder_name,$parent_folder_id=0,$site_id=site_id) {
        $folder_id=$this->get_new_page_id($site_id);

        if($parent_folder_id) {//check if parent folder exists
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
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $parent_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 40'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $parent_folder_id=0;
        }

        try {//create folder
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            u235_pages (
            page_id,
            page_title,
            page_type,
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
        catch(PDOException $e) {$this->uFunc->error('uPage/common 50'/*.$e->getMessage()*/);}

        return $folder_id;
    }
    public function get_system_folder($folder_type,$site_id=site_id) {
        //templates

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            folder_id 
            FROM 
            system_folders 
            WHERE 
            folder_type=:folder_type AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_type', $folder_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 55'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->folder_id;

        $new_folder_id=$this->create_folder($this->text("Templates system folder name"));
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO system_folders 
            (
             folder_id, 
             folder_type, 
             site_id
             ) VALUES (
             :folder_id, 
             'templates', 
             :site_id      
             )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $new_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 60'.$e->getMessage());}
        return $new_folder_id;
    }

    //TEMPLATES
    public function get_new_row_template_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            row_template_id 
            FROM 
            rows_templates 
            ORDER BY 
            row_template_id DESC 
            LIMIT 1");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->row_template_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 65'/*.$e->getMessage()*/);}
        return 1;
    }
    public function get_new_page_template_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            page_template_id 
            FROM 
            pages_templates 
            ORDER BY 
            page_template_id DESC 
            LIMIT 1");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->page_template_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 70'/*.$e->getMessage()*/);}
        return 1;
    }
    public function get_new_el_template_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            el_template_id 
            FROM 
            els_templates 
            ORDER BY 
            el_template_id DESC 
            LIMIT 1");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->el_template_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 75'/*.$e->getMessage()*/);}
        return 1;
    }
    public function row_template_id2data($row_template_id,$q_data="row_template_id",$site_id=0) {
        if($site_id) $q_site="AND site_id=:site_id";
        else $q_site="";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_data." 
            FROM 
            rows_templates
            WHERE
            row_template_id=:row_template_id
            ".$q_site);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $row_template_id,PDO::PARAM_INT);
            if($site_id) {/** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);}
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 80'/*.$e->getMessage()*/);}
        return 0;
    }
    public function page_template_id2data($page_template_id,$q_data="page_template_id",$site_id=0) {
        if($site_id) $q_site="AND site_id=:site_id";
        else $q_site="";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_data." 
            FROM 
            pages_templates
            WHERE
            page_template_id=:page_template_id
            ".$q_site);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_template_id', $page_template_id,PDO::PARAM_INT);
            if($site_id) {/** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);}
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 85'/*.$e->getMessage()*/);}
        return 0;
    }
    public function el_template_id2data($el_template_id,$q_data="el_template_id",$site_id=0) {
        if($site_id) $q_site="AND site_id=:site_id";
        else $q_site="";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_data." 
            FROM 
            els_templates
            WHERE
            el_template_id=:el_template_id
            ".$q_site);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_template_id', $el_template_id,PDO::PARAM_INT);
            if($site_id) {/** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);}
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 90'/*.$e->getMessage()*/);}
        return 0;
    }
    public function delete_row_template($row_template_id,$site_id=site_id) {
        //get template's page_id
        if(!$tmp_data=$this->row_template_id2data($row_template_id,"page_id",$site_id)) return 0;
        $page_id=(int)$tmp_data->page_id;

        //DELETE row_template
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            rows_templates 
            WHERE
            row_template_id=:row_template_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $row_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 95'/*.$e->getMessage()*/);}

        //DELETE template page
        $this->delete_page($page_id,$site_id);

        $this->uFunc->rmdir("uPage/templates/row_templates/".$site_id."/".$row_template_id);
        return 1;
    }
    public function delete_page_template($page_template_id,$site_id=site_id) {
        //get template's page_id
        if(!$tmp_data=$this->page_template_id2data($page_template_id,"page_id",$site_id)) return 0;
        $page_id=(int)$tmp_data->page_id;

        //DELETE page_template
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            pages_templates 
            WHERE
            page_template_id=:page_template_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_template_id', $page_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 100'/*.$e->getMessage()*/);}

        //DELETE template page
        $this->delete_page($page_id,$site_id);

        $this->uFunc->rmdir("uPage/templates/page_templates/".$site_id."/".$page_template_id);
        return 1;
    }
    public function delete_el_template($el_template_id,$site_id=site_id) {
        //get template's page_id
        if(!$tmp_data=$this->el_template_id2data($el_template_id,"page_id",$site_id)) return 0;
        $page_id=(int)$tmp_data->page_id;

        //DELETE el_template
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            els_templates 
            WHERE
            el_template_id=:el_template_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_template_id', $el_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 105'/*.$e->getMessage()*/);}

        //DELETE template page
        $this->delete_page($page_id,$site_id);

        $this->uFunc->rmdir("uPage/templates/el_templates/".$site_id."/".$el_template_id);
        return 1;
    }
    public function create_page_for_template($template_name,$page_width,$site_id=site_id) {
        if(!isset($this->uDrive)) {
            require_once "uDrive/classes/common.php";
            $this->uDrive=new \uDrive\common($this->uCore);
        }

        $uDrive_uPage_folder_id = $this->uDrive->get_module_folder_id("uPage");
        $uDrive_folder_id=$this->uDrive->create_folder($template_name,$uDrive_uPage_folder_id);

        $templates_system_folder_id=$this->uPage->get_system_folder("templates");

        $page_id=$this->uPage->get_new_page_id();


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO u235_pages
            (
            page_id,
            page_title,
            show_title,
            page_width,
            page_type,
            page_timestamp,
            site_id,
            uDrive_folder_id,
            text_folder_id,
            folder_id
            ) 
            VALUES 
            (
            :page_id,
            :page_title,
            1,
            :page_width,
            'el_template',
            :page_timestamp,
            :site_id,
            :uDrive_folder_id,
            0,
            :folder_id
            )
            ");
            $page_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':uDrive_folder_id', $uDrive_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $template_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_width', $page_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $templates_system_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 110'/*.$e->getMessage()*/);}

        $text_folder_id=$this->define_text_folder_id($page_id,$template_name,0);

        $page_data=[];
        $page_data["page_id"]=$page_id;
        $page_data["page_title"]=$template_name;
        $page_data["text_folder_id"]=$text_folder_id;
        return $page_data;
    }

    //PAGES
    public function get_page_id($el_type,$el_id,$site_id=site_id) {
        if($el_type=='el') {
            $col_id=$this->cols_els_id2col_id($el_id,$site_id);
            $row_id=$this->col_id2row_id($col_id,$site_id);
            return $this->row_id2page_id($row_id,$site_id);
        }
        elseif($el_type=='col') {
            $row_id=$this->col_id2row_id($el_id,$site_id);
            return $this->row_id2page_id($row_id,$site_id);
        }
        elseif($el_type=='row') {
            return $this->row_id2page_id($el_id,$site_id);
        }
        else $this->uFunc->error('uPage/common 115');

        return 0;
    }
    public function page_id2data($page_id,$q_select="page_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            ".$q_select."
            FROM 
            u235_pages 
            WHERE 
            page_id=:page_id AND
            site_id=:site_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 120'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_new_page_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            SELECT 
            page_id 
            FROM 
            u235_pages 
            WHERE 
            site_id=:site_id 
            ORDER BY 
            page_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->page_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 125'/*.$e->getMessage()*/);}
        return 1;
    }
    public function create_empty_page($page_title,$page_url,$site_id=site_id) {
        //define folder_id for texts created for this page
        if(!isset($this->uEditor)) $this->uEditor=new \uEditor\common($this->uCore);
        $uPage_folder_id = $this->uEditor->get_module_folder_id("uPage");
        $text_folder_id=$this->uEditor->create_folder($page_title,$uPage_folder_id);

        $page_id=$this->get_new_page_id($site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            u235_pages (
            page_id,
            page_title,
            page_url,
            site_id,
            text_folder_id
            ) VALUES (
            :page_id,
            :page_title,
            :page_url,
            :site_id,
            :text_folder_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $page_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text_folder_id', $text_folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 130'/*.$e->getMessage()*/);}

        if(!isset($stm)) exit;

        return array(
            "text_folder_id"=>$text_folder_id,
            "page_id"=>$page_id
        );
    }
    public function save_page_css($page_id,$page_css,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            page_css=:page_css
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $page_css=uString::text2sql($page_css);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_css', $page_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 135'/*.$e->getMessage()*/);}

        $this->clear_cache($page_id,$site_id);
    }
    public function define_page_uDrive_folder_id($page_id,$page_title,$cur_folder_id=0,$site_id=site_id) {
        if(!(int)$cur_folder_id) {
            if(!isset($this->uDrive)) {
                require_once "uDrive/classes/common.php";
                $this->uDrive=new \uDrive\common($this->uCore);
            }
            $uDrive_uPage_folder_id = $this->uDrive->get_module_folder_id("uPage");
            $page_title=trim($page_title);
            if(!strlen($page_title)) $page_title=$this->text("Page"/*Страница*/)." ".$page_id;
            $cur_folder_id=$this->uDrive->create_folder($page_title,$uDrive_uPage_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
                u235_pages
                SET
                uDrive_folder_id=:folder_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $cur_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 140'/*.$e->getMessage()*/);}
        }
        return $cur_folder_id;
    }
    public function define_text_folder_id($page_id,$page_title,$text_folder_id) {
        if(!isset($this->uEditor)) {
            require_once "uEditor/classes/common.php";
            $this->uEditor=new \uEditor\common($this->uCore);
        }
        if(!(int)$text_folder_id) {
            $uPage_folder_id = $this->uEditor->get_module_folder_id("uPage");
            $page_title=trim($page_title);
            if(!strlen($page_title)) $page_title=$this->text("Page"/*Страница*/)." ".$page_id;
            $text_folder_id=$this->uEditor->create_folder($page_title,$uPage_folder_id );

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
                u235_pages
                SET
                text_folder_id=:folder_id
                WHERE
                page_id=:page_id AND
                site_id=:site_id");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $text_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 145'/*.$e->getMessage()*/);}
        }
        return $text_folder_id;
    }
    public function delete_page($page_id,$site_id=site_id) {
        if(!isset($this->uDrive_file_update)) {
            require_once "uDrive/file_update_bg.php";
            $this->uDrive_file_update=new file_update($this->uCore);
        }
        if(!isset($this->uEditor)) {
            require_once "uEditor/classes/common.php";
            $this->uEditor=new \uEditor\common($this->uCore);
        }

        //get page's uDrive folder
        $page_data=$this->page_id2data($page_id,"uDrive_folder_id,text_folder_id",$site_id);
        $uDrive_folder_id=(int)$page_data->uDrive_folder_id;
        $text_folder_id=(int)$page_data->text_folder_id;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM
            u235_pages
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 150'/*.$e->getMessage()*/);}

        if($uDrive_folder_id) $this->uDrive_file_update->recycle_files_from_folder($uDrive_folder_id,"recycle");
        if($text_folder_id) $this->uEditor->recycle_pages_from_folder($text_folder_id,"recycle");
    }
    public function get_rows_of_page($page_id,$q_select="row_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_rows 
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ORDER BY
            row_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 155'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_page($page,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $new_page_id=$this->get_new_page_id($dest_site_id);
//        $page_url=$page->page_url."_".$new_page_id;
//        $page_title=$page->page_title."_".$new_page_id;
        $page_timestamp=time();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO u235_pages (
            page_id, 
            uDrive_folder_id, 
            page_title, 
            show_title, 
            page_url, 
            page_description, 
            page_keywords, 
            page_width, 
            page_css, 
            navi_parent_page_id, 
            navi_personal_menu, 
            folder_id, 
            page_type, 
            page_timestamp, 
            page_timestamp_show,
            preview_img_timestamp,
            preview_text,
            site_id, 
            deleted, 
            deleted_directly, 
            text_folder_id
            ) VALUES (
            :page_id, 
            0, 
            :page_title, 
            :show_title, 
            :page_url, 
            :page_description, 
            :page_keywords, 
            :page_width, 
            :page_css, 
            :navi_parent_page_id, 
            :navi_personal_menu, 
            :folder_id, 
            :page_type, 
            :page_timestamp, 
            :page_timestamp_show,
            :preview_img_timestamp,
            :preview_text,
            :site_id, 
            0, 
            0, 
            0          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $new_page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_title', $page->page_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $page->show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_url', $page->page_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_description', $page->page_description,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_keywords', $page->page_keywords,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_width', $page->page_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_css', $page->page_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_page_id', $page->navi_parent_page_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_personal_menu', $page->navi_personal_menu,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $page->folder_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_type', $page->page_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp', $page_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_timestamp_show', $page->page_timestamp_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_img_timestamp', $page->preview_img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':preview_text', $page->preview_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 160'/*.$e->getMessage()*/);}

        $text_folder_id=$this->define_text_folder_id($new_page_id,$page->page_title,0);

        $page_data["page_id"]=$new_page_id;
        $page_data["page_title"]=$page->page_title;
        $page_data["text_folder_id"]=$text_folder_id;

        if($page->page_type===""||$page->page_type==="el_template") {
            $rows=$this->get_rows_of_page($page->page_id,"*",$page->site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($row=$rows->fetch(PDO::FETCH_OBJ)) {
                $this->copy_row($page_data,$row,$row->row_pos,$row->site_id,$dest_site_id);
            }
        }

        $page_data=new \stdClass();
        $page_data->page_timestamp=$page_timestamp;
        $page_data->page_id=$new_page_id;
//        $page_data->page_url=$page_url;
        return $page_data;
    }

    //SITES
    public function save_site_css($site_style_ar,$site_id=site_id) {
        $site_style_ar["site_primary_color"]=str_replace("#","",trim($site_style_ar["site_primary_color"]));
        if(!uString::isHexColor($site_style_ar["site_primary_color"])&&!uString::isMadColor($site_style_ar["site_primary_color"])) $site_style_ar["site_primary_color"]="";

        $site_style_ar["site_primary_color_highlight"]=str_replace("#","",trim($site_style_ar["site_primary_color_highlight"]));
        if(!uString::isHexColor($site_style_ar["site_primary_color_highlight"])&&!uString::isMadColor($site_style_ar["site_primary_color_highlight"])) $site_style_ar["site_primary_color_highlight"]="";

        $site_style_ar["site_primary_over_font_color"]=str_replace("#","",trim($site_style_ar["site_primary_over_font_color"]));
        if(!uString::isHexColor($site_style_ar["site_primary_over_font_color"])&&!uString::isMadColor($site_style_ar["site_primary_over_font_color"])) $site_style_ar["site_primary_over_font_color"]="";
        if(!uString::isHexColor($site_style_ar["site_primary_color_highlight"])&&!uString::isMadColor($site_style_ar["site_primary_color_highlight"])) $site_style_ar["site_primary_color_highlight"]="";

        $site_style_ar["site_font_color"]=str_replace("#","",trim($site_style_ar["site_font_color"]));
        if(!uString::isHexColor($site_style_ar["site_font_color"])&&!uString::isMadColor($site_style_ar["site_font_color"])) $site_style_ar["site_font_color"]="";

        $site_style_ar["sliders_dots_style"]=(int)$site_style_ar["sliders_dots_style"];
        if($site_style_ar["sliders_dots_style"]<0||$site_style_ar["sliders_dots_style"]>16) $site_style_ar["sliders_dots_style"]=4;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("REPLACE INTO
            site_style (
            site_id, 
            site_css,
            site_primary_color,
            site_primary_color_highlight,
            site_primary_over_font_color,
            site_font_color,
            sliders_dots_style            
            ) VALUES (
            :site_id,
            :site_css,
            :site_primary_color,
            :site_primary_color_highlight,
            :site_primary_over_font_color,
            :site_font_color,
            :sliders_dots_style
            ) 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_css', $site_style_ar["site_css"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_primary_color', $site_style_ar["site_primary_color"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_primary_color_highlight', $site_style_ar["site_primary_color_highlight"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_primary_over_font_color', $site_style_ar["site_primary_over_font_color"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sliders_dots_style', $site_style_ar["sliders_dots_style"],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_font_color', $site_style_ar["site_font_color"],PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 165'/*.$e->getMessage()*/);}

        $file_addr="uPage/css/site_css/".$site_id."/site_css.css";
        if(file_exists($file_addr)) unlink($file_addr);
        $this->get_site_css_file($site_id);
        $this->clean_cache_for_site($site_id);
    }
    public function get_site_style($q_select="site_css,
            site_primary_color,
            site_primary_color_highlight,
            site_primary_over_font_color,
            site_font_color,
            sliders_dots_style",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select."
            FROM 
            site_style 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                if(isset($qr->site_primary_color)) $qr->site_primary_color_inverse=$this->uFunc->color_inverse($qr->site_primary_color);
                if(isset($qr->site_primary_color_highlight)) $qr->site_primary_color_highlight_inverse=$this->uFunc->color_inverse($qr->site_primary_color_highlight);
                if(isset($qr->site_primary_over_font_color)) $qr->site_primary_over_font_color_inverse=$this->uFunc->color_inverse($qr->site_primary_over_font_color);
                if(isset($qr->site_font_color)) $qr->site_font_color_inverse=$this->uFunc->color_inverse($qr->site_font_color);
            }
            else {
                $qr=new \stdClass();
                $qr->site_css="";
                $qr->sliders_dots_style=4;
                $qr->site_primary_color="";
                $qr->site_primary_color_inverse="";
                $qr->site_primary_color_highlight="";
                $qr->site_primary_color_highlight_inverse="";
                $qr->site_primary_over_font_color="";
                $qr->site_primary_over_font_color_inverse="";
                $qr->site_font_color="";
                $qr->site_font_color_inverse="";
            }
            return $qr;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 170'/*.$e->getMessage()*/);}

        return 0;
    }

    //ROWS
    public function row_id2page_id($row_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_rows
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 175'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uPage/common 180');
        return $qr->page_id;
    }
    public function get_new_row_id($site_id=site_id) {
        //get new row id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            row_id
            FROM
            u235_rows
            WHERE
            site_id=:site_id
            ORDER BY
            row_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->row_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 185'/*.$e->getMessage()*/);}

        return 1;
    }
    public function create_row($row_id,$page_id,$row_pos,$row_content_centered=0,$site_id=site_id) {
        //save new row
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            u235_rows (
            row_id,
            page_id,
            row_pos,
            row_content_centered,
            site_id
            ) VALUES (
            :row_id,
            :page_id,
            :row_pos,
            :row_content_centered,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_content_centered', $row_content_centered,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583879314'/*.$e->getMessage()*/);}
    }
    public function save_row_css($row_id,$row_style_ar,$site_id=site_id) {
        if(!isset($row_style_ar['row_css'])) $row_style_ar['row_css']="";
        if(!isset($row_style_ar['row_class'])) $row_style_ar['row_class']="";
        if(!isset($row_style_ar['row_class'])) $row_style_ar['row_class']="";
        if(!isset($row_style_ar['row_content_centered'])) $row_style_ar['row_content_centered']=0;
        if(!isset($row_style_ar['row_background_color'])) $row_style_ar['row_background_color']="";
        if(!isset($row_style_ar['row_background_img'])) $row_style_ar['row_background_img']="";
        if(!isset($row_style_ar['row_background_stretch'])) $row_style_ar['row_background_stretch']=0;
        if(!isset($row_style_ar['row_background_repeat_x'])) $row_style_ar['row_background_repeat_x']=0;
        if(!isset($row_style_ar['row_background_repeat_y'])) $row_style_ar['row_background_repeat_y']=0;
        if(!isset($row_style_ar['row_background_position'])) $row_style_ar['row_background_position']=0;
        if(!isset($row_style_ar['row_background_parallax'])) $row_style_ar['row_background_parallax']=0;

        if(!isset($row_style_ar['row_margin_top_xlg'])) $row_style_ar['row_margin_top_xlg']=0;
        if(!isset($row_style_ar['row_margin_top_lg'])) $row_style_ar['row_margin_top_lg']=0;
        if(!isset($row_style_ar['row_margin_top_md'])) $row_style_ar['row_margin_top_md']=0;
        if(!isset($row_style_ar['row_margin_top_sm'])) $row_style_ar['row_margin_top_sm']=0;
        if(!isset($row_style_ar['row_margin_top_xs'])) $row_style_ar['row_margin_top_xs']=0;

        if(!isset($row_style_ar['row_margin_bottom_xlg'])) $row_style_ar['row_margin_bottom_xlg']=0;
        if(!isset($row_style_ar['row_margin_bottom_lg'])) $row_style_ar['row_margin_bottom_lg']=0;
        if(!isset($row_style_ar['row_margin_bottom_md'])) $row_style_ar['row_margin_bottom_md']=0;
        if(!isset($row_style_ar['row_margin_bottom_sm'])) $row_style_ar['row_margin_bottom_sm']=0;
        if(!isset($row_style_ar['row_margin_bottom_xs'])) $row_style_ar['row_margin_bottom_xs']=0;

        if(!isset($row_style_ar['row_padding_top_xlg'])) $row_style_ar['row_padding_top_xlg']=0;
        if(!isset($row_style_ar['row_padding_top_lg'])) $row_style_ar['row_padding_top_lg']=0;
        if(!isset($row_style_ar['row_padding_top_md'])) $row_style_ar['row_padding_top_md']=0;
        if(!isset($row_style_ar['row_padding_top_sm'])) $row_style_ar['row_padding_top_sm']=0;
        if(!isset($row_style_ar['row_padding_top_xs'])) $row_style_ar['row_padding_top_xs']=0;

        if(!isset($row_style_ar['row_padding_bottom_xlg'])) $row_style_ar['row_padding_bottom_xlg']=0;
        if(!isset($row_style_ar['row_padding_bottom_lg'])) $row_style_ar['row_padding_bottom_lg']=0;
        if(!isset($row_style_ar['row_padding_bottom_md'])) $row_style_ar['row_padding_bottom_md']=0;
        if(!isset($row_style_ar['row_padding_bottom_sm'])) $row_style_ar['row_padding_bottom_sm']=0;
        if(!isset($row_style_ar['row_padding_bottom_xs'])) $row_style_ar['row_padding_bottom_xs']=0;

        if(!isset($row_style_ar['row_min_height_xlg'])) $row_style_ar['row_min_height_xlg']=0;
        if(!isset($row_style_ar['row_min_height_lg'])) $row_style_ar['row_min_height_lg']=0;
        if(!isset($row_style_ar['row_min_height_md'])) $row_style_ar['row_min_height_md']=0;
        if(!isset($row_style_ar['row_min_height_sm'])) $row_style_ar['row_min_height_sm']=0;
        if(!isset($row_style_ar['row_min_height_xs'])) $row_style_ar['row_min_height_xs']=0;
        if(!isset($row_style_ar['row_font_color'])) $row_style_ar['row_font_color']="";
        if(!isset($row_style_ar['row_link_color'])) $row_style_ar['row_link_color']="";
        if(!isset($row_style_ar['row_hoverlink_color'])) $row_style_ar['row_hoverlink_color']="";
        if(!isset($row_style_ar['row_font_size'])) $row_style_ar['row_font_size']=0;

        $row_css=trim($row_style_ar['row_css']);
        $row_css=uString::clean_css($row_css);
        $row_css_converted=uString::text2sql($row_css);

        $row_class=trim($row_style_ar['row_class']);
        $row_class=uString::removeHTML($row_class);

        if((int)$row_style_ar['row_content_centered']) $row_content_centered=1; else $row_content_centered=0;
        if((int)$row_style_ar['row_background_stretch']) $row_background_stretch=1; else $row_background_stretch=0;
        if((int)$row_style_ar['row_background_repeat_x']) $row_background_repeat_x=1; else $row_background_repeat_x=0;
        if((int)$row_style_ar['row_background_repeat_y']) $row_background_repeat_y=1; else $row_background_repeat_y=0;

        $row_background_color=str_replace("#","",trim($row_style_ar['row_background_color']));
        if(!uString::isHexColor($row_background_color)&&!uString::isMadColor($row_background_color)) $row_background_color="";

        $row_font_color=str_replace("#","",trim($row_style_ar['row_font_color']));
        if(!uString::isHexColor($row_font_color)&&!uString::isMadColor($row_font_color)) $row_font_color="";

        $row_link_color=str_replace("#","",trim($row_style_ar['row_link_color']));
        if(!uString::isHexColor($row_link_color)&&!uString::isMadColor($row_link_color)) $row_link_color="";

        $row_hoverlink_color=str_replace("#","",trim($row_style_ar['row_hoverlink_color']));
        if(!uString::isHexColor($row_hoverlink_color)&&!uString::isMadColor($row_hoverlink_color)) $row_hoverlink_color="";

        $row_background_img=trim($row_style_ar['row_background_img']);

        $row_background_position=(int)$row_style_ar['row_background_position'];
        if($row_background_position<0||$row_background_position>15) $row_background_position=0;

        $row_background_parallax=(int)$row_style_ar['row_background_parallax'];
        if($row_background_parallax<0||$row_background_parallax>15) $row_background_parallax=0;

        $row_margin_top_xlg=(int)trim($row_style_ar['row_margin_top_xlg']); if(!is_numeric($row_margin_top_xlg)) $row_margin_top_xlg=0;
        $row_margin_top_lg=(int)trim($row_style_ar['row_margin_top_lg']); if(!is_numeric($row_margin_top_lg)) $row_margin_top_lg=0;
        $row_margin_top_md=(int)trim($row_style_ar['row_margin_top_md']); if(!is_numeric($row_margin_top_md)) $row_margin_top_md=0;
        $row_margin_top_sm=(int)trim($row_style_ar['row_margin_top_sm']); if(!is_numeric($row_margin_top_sm)) $row_margin_top_sm=0;
        $row_margin_top_xs=(int)trim($row_style_ar['row_margin_top_xs']); if(!is_numeric($row_margin_top_xs)) $row_margin_top_xs=0;

        $row_margin_bottom_xlg=(int)trim($row_style_ar['row_margin_bottom_xlg']); if(!is_numeric($row_margin_bottom_xlg)) $row_margin_bottom_xlg=0;
        $row_margin_bottom_lg=(int)trim($row_style_ar['row_margin_bottom_lg']); if(!is_numeric($row_margin_bottom_lg)) $row_margin_bottom_lg=0;
        $row_margin_bottom_md=(int)trim($row_style_ar['row_margin_bottom_md']); if(!is_numeric($row_margin_bottom_md)) $row_margin_bottom_md=0;
        $row_margin_bottom_sm=(int)trim($row_style_ar['row_margin_bottom_sm']); if(!is_numeric($row_margin_bottom_sm)) $row_margin_bottom_sm=0;
        $row_margin_bottom_xs=(int)trim($row_style_ar['row_margin_bottom_xs']); if(!is_numeric($row_margin_bottom_xs)) $row_margin_bottom_xs=0;

        $row_padding_top_xlg=(int)trim($row_style_ar['row_padding_top_xlg']); if(!is_numeric($row_padding_top_xlg)) $row_padding_top_xlg=0;
        $row_padding_top_lg=(int)trim($row_style_ar['row_padding_top_lg']); if(!is_numeric($row_padding_top_lg)) $row_padding_top_lg=0;
        $row_padding_top_md=(int)trim($row_style_ar['row_padding_top_md']); if(!is_numeric($row_padding_top_md)) $row_padding_top_md=0;
        $row_padding_top_sm=(int)trim($row_style_ar['row_padding_top_sm']); if(!is_numeric($row_padding_top_sm)) $row_padding_top_sm=0;
        $row_padding_top_xs=(int)trim($row_style_ar['row_padding_top_xs']); if(!is_numeric($row_padding_top_xs)) $row_padding_top_xs=0;

        $row_padding_bottom_xlg=(int)trim($row_style_ar['row_padding_bottom_xlg']); if(!is_numeric($row_padding_bottom_xlg)) $row_padding_bottom_xlg=0;
        $row_padding_bottom_lg=(int)trim($row_style_ar['row_padding_bottom_lg']); if(!is_numeric($row_padding_bottom_lg)) $row_padding_bottom_lg=0;
        $row_padding_bottom_md=(int)trim($row_style_ar['row_padding_bottom_md']); if(!is_numeric($row_padding_bottom_md)) $row_padding_bottom_md=0;
        $row_padding_bottom_sm=(int)trim($row_style_ar['row_padding_bottom_sm']); if(!is_numeric($row_padding_bottom_sm)) $row_padding_bottom_sm=0;
        $row_padding_bottom_xs=(int)trim($row_style_ar['row_padding_bottom_xs']); if(!is_numeric($row_padding_bottom_xs)) $row_padding_bottom_xs=0;

        $row_min_height_xlg=(int)trim($row_style_ar['row_min_height_xlg']);
        if(!is_numeric($row_min_height_xlg)) $row_min_height_xlg=0;

        $row_min_height_lg=(int)trim($row_style_ar['row_min_height_lg']);
        if(!is_numeric($row_min_height_lg)) $row_min_height_lg=0;

        $row_min_height_md=(int)trim($row_style_ar['row_min_height_md']);
        if(!is_numeric($row_min_height_md)) $row_min_height_md=0;

        $row_min_height_sm=(int)trim($row_style_ar['row_min_height_sm']);
        if(!is_numeric($row_min_height_sm)) $row_min_height_sm=0;

        $row_min_height_xs=(int)trim($row_style_ar['row_min_height_xs']);
        if(!is_numeric($row_min_height_xs)) $row_min_height_xs=0;

        $row_font_size=trim($row_style_ar['row_font_size']);
        if(!is_numeric($row_font_size)) $row_font_size=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_rows
            SET
            row_css=:row_css,
            row_class=:row_class,
            row_background_color=:row_background_color,
            row_background_img=:row_background_img,
            row_background_stretch=:row_background_stretch,
            row_background_repeat_x=:row_background_repeat_x,
            row_background_repeat_y=:row_background_repeat_y,
            row_background_position=:row_background_position,
            row_background_parallax=:row_background_parallax,
            
                row_margin_top_xlg=:row_margin_top_xlg,
                row_margin_top_lg=:row_margin_top_lg,
                row_margin_top_md=:row_margin_top_md,
                row_margin_top_sm=:row_margin_top_sm,
                row_margin_top_xs=:row_margin_top_xs,
            
                row_margin_bottom_xlg=:row_margin_bottom_xlg,
                row_margin_bottom_lg=:row_margin_bottom_lg,
                row_margin_bottom_md=:row_margin_bottom_md,
                row_margin_bottom_sm=:row_margin_bottom_sm,
                row_margin_bottom_xs=:row_margin_bottom_xs,
            
                row_padding_top_xlg=:row_padding_top_xlg,
                row_padding_top_lg=:row_padding_top_lg,
                row_padding_top_md=:row_padding_top_md,
                row_padding_top_sm=:row_padding_top_sm,
                row_padding_top_xs=:row_padding_top_xs,
            
                row_padding_bottom_xlg=:row_padding_bottom_xlg,
                row_padding_bottom_lg=:row_padding_bottom_lg,
                row_padding_bottom_md=:row_padding_bottom_md,
                row_padding_bottom_sm=:row_padding_bottom_sm,
                row_padding_bottom_xs=:row_padding_bottom_xs,
            
                row_min_height_xlg=:row_min_height_xlg,
            row_min_height_lg=:row_min_height_lg,
            row_min_height_md=:row_min_height_md,
            row_min_height_sm=:row_min_height_sm,
            row_min_height_xs=:row_min_height_xs,
            row_font_color=:row_font_color,
            row_link_color=:row_link_color,
            row_hoverlink_color=:row_hoverlink_color,
            row_font_size=:row_font_size,
            row_content_centered=:row_content_centered
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_color', $row_background_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_img', $row_background_img,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_stretch', $row_background_stretch,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_repeat_x', $row_background_repeat_x,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_repeat_y', $row_background_repeat_y,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_position', $row_background_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_parallax', $row_background_parallax,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_xlg', $row_margin_top_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_lg', $row_margin_top_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_md', $row_margin_top_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_sm', $row_margin_top_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_xs', $row_margin_top_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_xlg', $row_margin_bottom_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_lg', $row_margin_bottom_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_md', $row_margin_bottom_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_sm', $row_margin_bottom_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_xs', $row_margin_bottom_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_xlg', $row_padding_top_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_lg', $row_padding_top_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_md', $row_padding_top_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_sm', $row_padding_top_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_xs', $row_padding_top_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_xlg', $row_padding_bottom_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_lg', $row_padding_bottom_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_md', $row_padding_bottom_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_sm', $row_padding_bottom_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_xs', $row_padding_bottom_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_xlg', $row_min_height_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_lg', $row_min_height_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_md', $row_min_height_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_sm', $row_min_height_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_xs', $row_min_height_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_font_color', $row_font_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_link_color', $row_link_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_hoverlink_color', $row_hoverlink_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_font_size', $row_font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_content_centered', $row_content_centered,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_css', $row_css_converted,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_class', $row_class,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 195'/*.$e->getMessage()*/);}

        $page_id=$this->get_page_id('row',$row_id);

        $this->clear_cache($page_id,$site_id);
        return $row_style_ar;
    }
    public function is_row_exists($row_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            row_id 
            FROM 
            u235_rows 
            WHERE 
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 200'/*.$e->getMessage()*/);}
        return 0;
    }
    public function row_id2data($row_id,$q_select="row_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_rows 
            WHERE 
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 205'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_all_row_cols($row_id,$q_select="col_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_cols 
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ORDER BY col_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 210'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_row($page_data,$row,$row_pos,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($page_data["page_id"])) return 0;
        $page_id=(int)$page_data["page_id"];

        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $row_id=$this->get_new_row_id($dest_site_id);

        //update row's css
        $row_css=uString::sql2text($row->row_css,1);
        $row_css=$this->update_ids_in_row_css($row->row_id,$row_id,$row_css);
        $row_css=uString::text2sql($row_css);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO u235_rows
            (
             row_id, 
             page_id, 
             row_pos, 
             row_class, 
             row_css, 
             row_background_color, 
             row_background_img, 
             row_background_stretch, 
             row_background_repeat_x, 
             row_background_repeat_y, 
             row_background_position, 
             row_background_parallax, 
             
             row_margin_top_xlg, 
             row_margin_top_lg, 
             row_margin_top_md, 
             row_margin_top_sm, 
             row_margin_top_xs, 
             
             row_margin_bottom_xlg, 
             row_margin_bottom_lg, 
             row_margin_bottom_md, 
             row_margin_bottom_sm, 
             row_margin_bottom_xs, 
             
             row_padding_top_xlg, 
             row_padding_top_lg, 
             row_padding_top_md, 
             row_padding_top_sm, 
             row_padding_top_xs, 
             
             row_padding_bottom_xlg, 
             row_padding_bottom_lg, 
             row_padding_bottom_md, 
             row_padding_bottom_sm, 
             row_padding_bottom_xs, 
             
             row_min_height_xlg, 
             row_min_height_lg, 
             row_min_height_md, 
             row_min_height_sm, 
             row_min_height_xs, 
             row_font_color, 
             row_font_size, 
             row_content_centered, 
             site_id, 
             row_link_color, 
             row_hoverlink_color
             ) VALUES (      
             :row_id,
             :page_id, 
             :row_pos, 
             :row_class, 
             :row_css, 
             :row_background_color, 
             :row_background_img, 
             :row_background_stretch, 
             :row_background_repeat_x, 
             :row_background_repeat_y, 
             :row_background_position, 
             :row_background_parallax, 
             
                       :row_margin_top_xlg, 
                       :row_margin_top_lg, 
                       :row_margin_top_md, 
                       :row_margin_top_sm, 
                       :row_margin_top_xs, 
             
                       :row_margin_bottom_xlg, 
                       :row_margin_bottom_lg, 
                       :row_margin_bottom_md, 
                       :row_margin_bottom_sm, 
                       :row_margin_bottom_xs, 
             
                       :row_padding_top_xlg, 
                       :row_padding_top_lg, 
                       :row_padding_top_md, 
                       :row_padding_top_sm, 
                       :row_padding_top_xs, 
             
                       :row_padding_bottom_xlg, 
                       :row_padding_bottom_lg, 
                       :row_padding_bottom_md, 
                       :row_padding_bottom_sm, 
                       :row_padding_bottom_xs, 
             
                       :row_min_height_xlg, 
             :row_min_height_lg, 
             :row_min_height_md, 
             :row_min_height_sm, 
             :row_min_height_xs, 
             :row_font_color, 
             :row_font_size, 
             :row_content_centered, 
             :site_id, 
             :row_link_color, 
             :row_hoverlink_color                                                 
             )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_class', $row->row_class,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_css', $row_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_color', $row->row_background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_img', $row->row_background_img,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_stretch', $row->row_background_stretch,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_repeat_x', $row->row_background_repeat_x,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_repeat_y', $row->row_background_repeat_y,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_position', $row->row_background_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_background_parallax', $row->row_background_parallax,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_xlg', $row->row_margin_top_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_lg', $row->row_margin_top_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_md', $row->row_margin_top_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_sm', $row->row_margin_top_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_top_xs', $row->row_margin_top_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_xlg', $row->row_margin_bottom_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_lg', $row->row_margin_bottom_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_md', $row->row_margin_bottom_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_sm', $row->row_margin_bottom_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_margin_bottom_xs', $row->row_margin_bottom_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_xlg', $row->row_padding_top_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_lg', $row->row_padding_top_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_md', $row->row_padding_top_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_sm', $row->row_padding_top_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_top_xs', $row->row_padding_top_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_xlg', $row->row_padding_bottom_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_lg', $row->row_padding_bottom_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_md', $row->row_padding_bottom_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_sm', $row->row_padding_bottom_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_padding_bottom_xs', $row->row_padding_bottom_xs,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_xlg', $row->row_min_height_xlg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_lg', $row->row_min_height_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_md', $row->row_min_height_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_sm', $row->row_min_height_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_min_height_xs', $row->row_min_height_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_font_color', $row->row_font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_font_size', $row->row_font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_content_centered', $row->row_content_centered,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_link_color', $row->row_link_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_hoverlink_color', $row->row_hoverlink_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 215'/*.$e->getMessage()*/,1);}

        //Copy all cols of source row
        $cols_stm=$this->get_all_row_cols($row->row_id,"*",$row->site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($col=$cols_stm->fetch(PDO::FETCH_OBJ)) {
            $this->copy_col($page_data,$row_id,$col,$col->site_id,$dest_site_id);
        }

        $row->row_css=$row_css;
        $row->row_pos=$row_pos;
        $row->row_id=$row_id;
        $row->page_id=$page_id;
        return $row;
    }
    public function update_ids_in_row_css($row_id,$new_row_id,$row_css) {
        return str_replace("uPage_row_".$row_id,"uPage_row_".$new_row_id,$row_css);
    }
    public function build_row_js4page_builder($row,$return=0) {
        if($return) ob_start();?>
        //<!--suppress ES6ConvertVarToLetConst -->
<!--        <script type="text/javascript">-->

            <?//get all cols attached to current row
        $q_uPage_cols=$this->get_all_row_cols($row->row_id,"col_id,col_pos,col_css,lg_width,md_width,sm_width,xs_width");
        ?>

        row_i=uPage_setup_uPage.rows.length;
        uPage_setup_uPage.rows[row_i]=[];
        uPage_setup_uPage.row_id2row_i[<?=$row->row_id?>]=row_i;
        uPage_setup_uPage.rows[row_i]['row_id']=<?=$row->row_id?>;
        uPage_setup_uPage.rows[row_i]['row_pos']=<?=$row->row_pos?>;
        uPage_setup_uPage.rows[row_i]['row_class']=decodeURIComponent("<?=rawurlencode($row->row_class)?>");
        uPage_setup_uPage.rows[row_i]['row_css']=decodeURIComponent("<?=rawurlencode(uString::sql2text($row->row_css))?>");
        uPage_setup_uPage.rows[row_i]['row_background_color']="<?=$row->row_background_color?>";
        uPage_setup_uPage.rows[row_i]['row_background_img']=decodeURIComponent("<?=$row->row_background_img?>");
        uPage_setup_uPage.rows[row_i]['row_background_stretch']=<?=$row->row_background_stretch?>;
        uPage_setup_uPage.rows[row_i]['row_background_repeat_x']=<?=$row->row_background_repeat_x?>;
        uPage_setup_uPage.rows[row_i]['row_background_repeat_y']=<?=$row->row_background_repeat_y?>;
        uPage_setup_uPage.rows[row_i]['row_background_position']=<?=$row->row_background_position?>;
        uPage_setup_uPage.rows[row_i]['row_background_parallax']=<?=$row->row_background_parallax?>;

        uPage_setup_uPage.rows[row_i]['row_margin_top_xlg']=<?=$row->row_margin_top_xlg?>;
        uPage_setup_uPage.rows[row_i]['row_margin_top_lg']=<?=$row->row_margin_top_lg?>;
        uPage_setup_uPage.rows[row_i]['row_margin_top_md']=<?=$row->row_margin_top_md?>;
        uPage_setup_uPage.rows[row_i]['row_margin_top_sm']=<?=$row->row_margin_top_sm?>;
        uPage_setup_uPage.rows[row_i]['row_margin_top_xs']=<?=$row->row_margin_top_xs?>;

        uPage_setup_uPage.rows[row_i]['row_margin_bottom_xlg']=<?=$row->row_margin_bottom_xlg?>;
        uPage_setup_uPage.rows[row_i]['row_margin_bottom_lg']=<?=$row->row_margin_bottom_lg?>;
        uPage_setup_uPage.rows[row_i]['row_margin_bottom_md']=<?=$row->row_margin_bottom_md?>;
        uPage_setup_uPage.rows[row_i]['row_margin_bottom_sm']=<?=$row->row_margin_bottom_sm?>;
        uPage_setup_uPage.rows[row_i]['row_margin_bottom_xs']=<?=$row->row_margin_bottom_xs?>;

        uPage_setup_uPage.rows[row_i]['row_padding_top_xlg']=<?=$row->row_padding_top_xlg?>;
        uPage_setup_uPage.rows[row_i]['row_padding_top_lg']=<?=$row->row_padding_top_lg?>;
        uPage_setup_uPage.rows[row_i]['row_padding_top_md']=<?=$row->row_padding_top_md?>;
        uPage_setup_uPage.rows[row_i]['row_padding_top_sm']=<?=$row->row_padding_top_sm?>;
        uPage_setup_uPage.rows[row_i]['row_padding_top_xs']=<?=$row->row_padding_top_xs?>;

        uPage_setup_uPage.rows[row_i]['row_padding_bottom_xlg']=<?=$row->row_padding_bottom_xlg?>;
        uPage_setup_uPage.rows[row_i]['row_padding_bottom_lg']=<?=$row->row_padding_bottom_lg?>;
        uPage_setup_uPage.rows[row_i]['row_padding_bottom_md']=<?=$row->row_padding_bottom_md?>;
        uPage_setup_uPage.rows[row_i]['row_padding_bottom_sm']=<?=$row->row_padding_bottom_sm?>;
        uPage_setup_uPage.rows[row_i]['row_padding_bottom_xs']=<?=$row->row_padding_bottom_xs?>;

        uPage_setup_uPage.rows[row_i]['row_min_height_xlg']=<?=$row->row_min_height_xlg?>;
        uPage_setup_uPage.rows[row_i]['row_min_height_lg']=<?=$row->row_min_height_lg?>;
        uPage_setup_uPage.rows[row_i]['row_min_height_md']=<?=$row->row_min_height_md?>;
        uPage_setup_uPage.rows[row_i]['row_min_height_sm']=<?=$row->row_min_height_sm?>;
        uPage_setup_uPage.rows[row_i]['row_min_height_xs']=<?=$row->row_min_height_xs?>;
        uPage_setup_uPage.rows[row_i]['row_font_color']="<?=$row->row_font_color?>";
        uPage_setup_uPage.rows[row_i]['row_link_color']="<?=$row->row_link_color?>";
        uPage_setup_uPage.rows[row_i]['row_hoverlink_color']="<?=$row->row_hoverlink_color?>";
        uPage_setup_uPage.rows[row_i]['row_font_size']=<?=$row->row_font_size?>;
        uPage_setup_uPage.rows[row_i]['row_content_centered']=<?=$row->row_content_centered?>;
        uPage_setup_uPage.rows[row_i]['new']=1;

        uPage_setup_uPage.rows_id2cols[<?=$row->row_id?>]=[];
        <?/** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        while($col=$q_uPage_cols->fetch(PDO::FETCH_OBJ)) {?>
            col=uPage_setup_uPage.rows_id2cols[<?=$row->row_id?>];
            var col_i=col.length;
            col[col_i]=[];

            col[col_i]['col_id']=<?=$col->col_id?>;
            col[col_i]['col_pos']=<?=$col->col_pos?>;
            col[col_i]['col_css']=decodeURIComponent("<?=rawurlencode(uString::sql2text($col->col_css))?>");
            col[col_i]['lg_width']=<?=$col->lg_width?>;
            col[col_i]['md_width']=<?=$col->md_width?>;
            col[col_i]['sm_width']=<?=$col->sm_width?>;
            col[col_i]['xs_width']=<?=$col->xs_width?>;
            uPage_setup_uPage.col_id2row_id[<?=$col->col_id?>]=<?=$row->row_id?>;
            uPage_setup_uPage.col_id2col_i[<?=$col->col_id?>]=col_i;
            <?
            //get all elements attached to current col
            $q_elements=$this->get_all_col_els_of_col($col->col_id,"cols_els_id,el_type,el_pos,el_css,el_id,el_font_color,el_link_color,el_hoverlink_color");?>
            uPage_setup_uPage.cols_id2els[<?=$col->col_id?>]=[];
            <?/** @noinspection PhpUndefinedMethodInspection */
            while($element=$q_elements->fetch(PDO::FETCH_OBJ)) {?>
                var el=uPage_setup_uPage.cols_id2els[<?=$col->col_id?>];
                var el_i=el.length;
                el[el_i]=[];

                el[el_i]['cols_els_id']=<?=$element->cols_els_id?>;
                uPage_setup_uPage.cols_els_id2el_i[<?=$element->cols_els_id?>]=el_i;
                uPage_setup_uPage.cols_els_id2col_id[<?=$element->cols_els_id?>]=col[col_i]['col_id'];
                el[el_i]['el_id']=<?=$element->el_id?>;
                el[el_i]['el_type']="<?=$element->el_type?>";
                el[el_i]['el_pos']=<?=$element->el_pos?>;
                el[el_i]['el_css']=decodeURIComponent("<?=rawurlencode(uString::sql2text($element->el_css))?>");
                el[el_i]['el_font_color']="<?=$element->el_font_color?>";
                el[el_i]['el_link_color']="<?=$element->el_link_color?>";
                el[el_i]['el_hoverlink_color']="<?=$element->el_hoverlink_color?>";

                <?
                //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
                $els=array(
                    "art",
                    "banner",
                    "bootstrap_carousel",
                    "card",
                    "code",
                    "flip_book",
                    "form",
                    "gallery",
                    "gmap",
                    "login_btn",
                    "menu",
                    "owl_carousel",
                    "page_filter",
                    "rubrics_arts",
                    "rubrics_arts_column",
                    "rubrics_tiles",
                    "share",
                    "spacer",
                    "tabs",
                    "ticker",
                    "uCat_latest",
                    "uCat_latest_articles_slider",
                    "uCat_new_items",
                    "uCat_popular",
                    "uCat_sale",
                    "uCat_search",
                    "search",
                    "uCat_sects",
                    "uEditor_texts_top",
                    "uEvents_calendar",
                    "uEvents_dates",
                    "uEvents_list",
                    "uSubscr_news_form");

                if(in_array($element->el_type,$els)) /** @noinspection PhpIncludeInspection */include "uPage/elements/".$element->el_type."/admin_page_builder.php";
            }
        }
        if($return) {
            $cnt = ob_get_contents();
            ob_end_clean();
            return $cnt;
        }
        return 0;
    }
    //position
    private function move_down_rows_since_row_pos($row_pos,$page_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_rows
            SET
            row_pos=row_pos+1
            WHERE
            row_pos>=:row_pos AND
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 220'/*.$e->getMessage()*/);}
    }
    public function define_new_row_pos($row_pos,$page_id) {
        $row_pos=(int)$row_pos;
        if($row_pos==0) {//Если 0, то вставляем на самый верх. Нужно посмотреть row_pos самого верхнего row и поставить над ним (значение может быть отрицательным). Создавать row с row_pos=0 нельзя. Это зарезервировано!
            //Смотрим row_pos самого верхнего элемента
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                row_pos
                FROM
                u235_rows
                WHERE
                page_id=:page_id AND
                site_id=:site_id
                ORDER BY
                row_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 225'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $next_row_pos=(int)$qr->row_pos;
                $new_row_pos=$next_row_pos-1;
                if($new_row_pos==0) $new_row_pos=-1;
            }
            else $new_row_pos=1;
        }
        else {//Вставляем под какой-то уже существующий row
            //Ищем row, у которого row_pos идет следующим за тем, под которым мы вставляем, чтобы понять, между какими значениями row_pos нам нужно впихнуть наш новый row
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                row_pos
                FROM
                u235_rows
                WHERE
                row_pos>:row_pos AND
                page_id=:page_id AND
                site_id=:site_id
                ORDER BY
                row_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 230'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $next_row_pos=(int)$qr->row_pos;
                if($next_row_pos-$row_pos>1) {
                    $new_row_pos=$row_pos+1;
                    if(!$new_row_pos) {
                        $new_row_pos=$row_pos+2;
                        if($new_row_pos>=$next_row_pos) {
                            //next_row_pos и ниже нужно подвинуть вниз
                            $this->move_down_rows_since_row_pos($next_row_pos,$page_id);
                            if($next_row_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                                $this->move_down_rows_since_row_pos(0,$page_id);
                            }
                        }
                    }


                }
                else {
                    //next_row_pos и ниже нужно подвинуть вниз
                    $this->move_down_rows_since_row_pos($next_row_pos,$page_id);
                    if($next_row_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                        $this->move_down_rows_since_row_pos(0,$page_id);
                    }
                    $new_row_pos=$row_pos+1;
                }
            }
            else $new_row_pos=$row_pos+1;
        }
        return $new_row_pos;
    }

    //COLS
    public function col_id2row_id($col_id,$site_id=site_id) {
        if($res=$this->col_id2data($col_id,"row_id",$site_id)) return (int)$res->row_id;
        return 0;
    }
    public function col_id2data($col_id,$q_select="col_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            ".$q_select."
            FROM
            u235_cols
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $res=$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 235'/*.$e->getMessage()*/);}

        return 0;
    }
    public function get_new_col_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            col_id
            FROM
            u235_cols
            WHERE
            site_id=:site_id
            ORDER BY
            col_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->col_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 240'/*.$e->getMessage()*/);}

        return 1;
    }
    public function create_col($col_id,$row_id,$col_pos,$lg_width,$md_width,$sm_width,$xs_width,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            u235_cols (
            col_id,
            row_id,
            col_pos,
            lg_width,
            md_width,
            sm_width,
            xs_width,
            site_id
            ) VALUES (
            :col_id,
            :row_id,
            :col_pos,
            :lg_width,
            :md_width,
            :sm_width,
            :xs_width,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_width', $lg_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_width', $md_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_width', $sm_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_width', $xs_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 245'/*.$e->getMessage()*/);}
    }
    public function save_col_css($col_id,$col_css,$site_id=site_id) {
        $col_css=uString::clean_css($col_css);
        $col_css_converted=uString::text2sql($col_css);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols
            SET
            col_css=:col_css
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_css', $col_css_converted,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 250'/*.$e->getMessage()*/);}

        $page_id=$this->get_page_id('col',$col_id);
        //clear cache
        $this->clear_cache($page_id,$site_id);
    }
    private function copy_col($page_data,$row_id,$col,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $col_id=$this->get_new_col_id($dest_site_id);

        //update col's css
        $col_css=uString::sql2text($col->col_css,1);
        $col_css=$this->update_ids_in_col_css($col->col_id,$col_id,$col_css);
        $col_css=uString::text2sql($col_css);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO u235_cols
            (
             col_id, 
             row_id, 
             col_pos, 
             col_css, 
             lg_width, 
             md_width, 
             sm_width, 
             xs_width, 
             site_id
            ) VALUES (
             :col_id, 
             :row_id, 
             :col_pos, 
             :col_css, 
             :lg_width, 
             :md_width, 
             :sm_width, 
             :xs_width, 
             :site_id       
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col->col_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_css', $col_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_width', $col->lg_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_width', $col->md_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_width', $col->sm_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_width', $col->xs_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 260'/*.$e->getMessage()*/);}

        //update col's parent row css
        $this->update_ids_in_col_parent_row_css($row_id,$col->col_id,$col_id,$dest_site_id);

        //COPY COLS_ELS
        $cols_els_stm=$this->get_all_cols_els($col->col_id,$source_site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($cols_el=$cols_els_stm->fetch(PDO::FETCH_OBJ)) {
            $this->copy_cols_el($page_data,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }

    }
    public function update_ids_in_col_parent_row_css($row_id,$col_id,$new_col_id,$site_id=site_id) {
        if(!$row_data=$this->row_id2data($row_id,"row_css",$site_id)) return 0;
        $row_css=$row_data->row_css;
        $row_css=uString::sql2text($row_css,1);
        $row_css=str_replace("uPage_col_".$col_id,"uPage_col_".$new_col_id,$row_css);
        $row_css=uString::text2sql($row_css);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            u235_rows
            SET
            row_css=:row_css
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_css', $row_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 270'/*.$e->getMessage()*/);}

        return 1;
    }
    public function update_ids_in_col_css($col_id,$new_col_id,$col_css) {
        return str_replace("uPage_col_".$col_id,"uPage_col_".$new_col_id,$col_css);
    }
    public function get_all_col_els_of_col($col_id,$q_select="cols_els_id",$site_id=site_id) {
        //get all elements attached to this column
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            ".$q_select."
            FROM
            u235_cols_els
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ORDER BY
            el_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 280'/*.$e->getMessage()*/);}
        return 0;
    }
    //position
    private function move_down_cols_since_col_pos($col_pos,$row_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols
            SET
            col_pos=col_pos+1
            WHERE
            col_pos>=:col_pos AND
            row_id=:row_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 285'/*.$e->getMessage()*/);}
    }
    public function define_new_col_pos($col_pos,$row_id) {
        $col_pos=(int)$col_pos;
        if(!$col_pos) {//Если 0, то вставляем на самый верх. Нужно посмотреть col_pos самого верхнего col и поставить над ним (значение может быть отрицательным). Создавать col с col_pos=0 нельзя. Это зарезервировано!
            //Смотрим col_pos самого верхнего элемента
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                col_pos
                FROM
                u235_cols
                WHERE
                row_id=:row_id AND
                site_id=:site_id
                ORDER BY
                col_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 290'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $next_col_pos=(int)$qr->col_pos;
                $new_col_pos=$next_col_pos-1;
                if(!$new_col_pos) $new_col_pos=-1;
            }
            else $new_col_pos=1;
        }
        else {//Вставляем под какой-то уже существующий col
            //Ищем col, у которого col_pos идет следующим за тем, под которым мы вставляем, чтобы понять, между какими значениями col_pos нам нужно впихнуть наш новый col
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                col_pos
                FROM
                u235_cols
                WHERE
                col_pos>:col_pos AND
                row_id=:row_id AND
                site_id=:site_id
                ORDER BY
                col_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 295'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $next_col_pos=(int)$qr->col_pos;
                if($next_col_pos-$col_pos>1) {
                    $new_col_pos=$col_pos+1;
                    if(!$new_col_pos) {
                        $new_col_pos=$col_pos+2;
                        if($new_col_pos>=$next_col_pos) {
                            //next_col_pos и ниже нужно подвинуть вниз
                            $this->move_down_cols_since_col_pos($next_col_pos,$row_id);
                            if($next_col_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                                $this->move_down_cols_since_col_pos(0,$row_id);
                            }
                        }
                    }


                }
                else {
                    //next_col_pos и ниже нужно подвинуть вниз
                    $this->move_down_cols_since_col_pos($next_col_pos,$row_id);
                    if($next_col_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                        $this->move_down_cols_since_col_pos(0,$row_id);
                    }
                    $new_col_pos=$col_pos+1;
                }
            }
            else $new_col_pos=$col_pos+1;
        }
        return $new_col_pos;
    }

    //COLS ELS
    public function cols_els_id2col_id($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            col_id
            FROM
            u235_cols_els
            WHERE
            cols_els_id='".$cols_els_id."' AND
            site_id='".site_id."'
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 300'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uPage/common 310');
        return $qr->col_id;
    }
    public function delete_el($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("DELETE FROM 
            u235_cols_els
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 320'/*.$e->getMessage()*/);}
    }
    public function get_new_cols_els_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            cols_els_id
            FROM
            u235_cols_els
            WHERE
            site_id=:site_id
            ORDER BY
            cols_els_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->cols_els_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 330'/*.$e->getMessage()*/);}
        return 0;
    }
    public function create_el($cols_els_id,$col_id,$el_type,$el_pos,$el_style,$el_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            u235_cols_els (
            cols_els_id,
            col_id,
            el_type,
            el_pos,
            el_css,
            el_id,
            el_font_color, 
            el_link_color, 
            el_hoverlink_color,
            site_id
            ) VALUES (
            :cols_els_id,
            :col_id,
            :el_type,
            :el_pos,
            :el_css,
            :el_id,
            :el_font_color, 
            :el_link_color, 
            :el_hoverlink_color,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_type', $el_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_css', $el_style->el_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_font_color', $el_style->el_font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_link_color', $el_style->el_link_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_hoverlink_color', $el_style->el_hoverlink_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 340'/*.$e->getMessage()*/);}
    }
    public function save_el_css($cols_els_id,$el_style_ar,$site_id=site_id) {
        if(!isset($el_style_ar['el_font_color'])) $el_style_ar['el_font_color']="";
        if(!isset($el_style_ar['el_link_color'])) $el_style_ar['el_link_color']="";
        if(!isset($el_style_ar['el_hoverlink_color'])) $el_style_ar['el_hoverlink_color']="";
        if(!isset($el_style_ar['el_css'])) $el_style_ar['el_css']="";

        $el_style_ar['el_css']=uString::clean_css($el_style_ar['el_css']);
        $el_css=uString::text2sql($el_style_ar['el_css']);

        $el_font_color=str_replace("#","",trim($el_style_ar['el_font_color']));
        if(!uString::isHexColor($el_font_color)&&!uString::isMadColor($el_font_color)) $el_font_color="";

        $el_link_color=str_replace("#","",trim($el_style_ar['el_link_color']));
        if(!uString::isHexColor($el_link_color)&&!uString::isMadColor($el_link_color)) $el_link_color="";

        $el_hoverlink_color=str_replace("#","",trim($el_style_ar['el_hoverlink_color']));
        if(!uString::isHexColor($el_hoverlink_color)&&!uString::isMadColor($el_hoverlink_color)) $el_hoverlink_color="";

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols_els
            SET
            el_css=:el_css,
            el_font_color=:el_font_color,
            el_link_color=:el_link_color,
            el_hoverlink_color=:el_hoverlink_color
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_css', $el_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_font_color', $el_font_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_link_color', $el_link_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_hoverlink_color', $el_hoverlink_color,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 350'/*.$e->getMessage()*/);}

        $page_id=$this->get_page_id('el',$cols_els_id);

        $this->clear_cache($page_id,$site_id);
    }
    private function get_all_cols_els($col_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            u235_cols_els
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 360'/*.$e->getMessage()*/);}

        return 0;
    }
    public function copy_cols_el($page_data,$col_id,$cols_el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $el_type=$cols_el->el_type;

        //get new cols_els_id
        $cols_els_id=$this->get_new_cols_els_id($dest_site_id);

        //update cols_els css
        $el_css=uString::sql2text($cols_el->el_css,1);
        $el_css=$this->update_ids_in_cols_els_css($cols_el->cols_els_id,$cols_els_id,$el_css);
        $el_css=uString::text2sql($el_css);
        $cols_el->el_css=$el_css;

        $cols_el->el_style=new \stdClass();
        $cols_el->el_style->el_css=&$cols_el->el_css;
        $cols_el->el_style->el_font_color=$cols_el->el_font_color;
        $cols_el->el_style->el_link_color=$cols_el->el_link_color;
        $cols_el->el_style->el_hoverlink_color=$cols_el->el_hoverlink_color;

        $this->update_ids_in_cols_els_parent_col_css($col_id,$cols_el->cols_els_id,$cols_els_id,$dest_site_id);

        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        if($el_type==="art") {
            if(!isset($this->art)) {
                require_once "uPage/elements/art/common.php";
                $this->art=new art($this);
            }
                $this->art->copy_el($page_data,$cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="banner") {
            if(!isset($this->banner)) {
                require_once "uPage/elements/banner/common.php";
                $this->banner=new banner($this);
            }
                $this->banner->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="bootstrap_carousel") {
            if(!isset($this->bootstrap_carousel)) {
                require_once "uPage/elements/bootstrap_carousel/common.php";
                $this->bootstrap_carousel=new bootstrap_carousel($this);
            }
                $this->bootstrap_carousel->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="code") {
            if(!isset($this->code)) {
                require_once "uPage/elements/code/common.php";
                $this->code=new code($this);
            }
                $this->code->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="card") {
            if(!isset($this->card)) {
                require_once "uPage/elements/card/common.php";
                $this->code=new card($this);
            }
                $this->code->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="flip_book") {
            if(!isset($this->flip_book)) {
                require_once "uPage/elements/flip_book/common.php";
                $this->flip_book=new flip_book($this);
            }
                $this->flip_book->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="form") {
            if(!isset($this->form)) {
                require_once "uPage/elements/form/common.php";
                $this->form=new form($this);
            }
                $this->form->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="gallery") {
            if(!isset($this->gallery)) {
                require_once "uPage/elements/gallery/common.php";
                $this->gallery=new gallery($this);
            }
                $this->gallery->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        if($el_type==="gmap") {
            if(!isset($this->gmap)) {
                require_once "uPage/elements/gmap/common.php";
                $this->gmap=new gmap($this);
            }
                $this->gmap->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="login_btn") {
            if(!isset($this->login_btn)) {
                require_once "uPage/elements/login_btn/common.php";
                $this->login_btn=new login_btn($this);
            }
                $this->login_btn->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="menu") {
            if(!isset($this->menu)) {
                require_once "uPage/elements/menu/common.php";
                $this->menu=new menu($this);
            }
                $this->menu->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="owl_carousel") {
            if(!isset($this->owl_carousel)) {
                require_once "uPage/elements/owl_carousel/common.php";
                $this->owl_carousel=new owl_carousel($this);
            }
                $this->owl_carousel->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="page_filter") {
            if(!isset($this->page_filter)) {
                require_once "uPage/elements/page_filter/common.php";
                $this->page_filter=new page_filter($this);
            }
            $this->page_filter->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="rubrics_arts") {
            if(!isset($this->rubrics_arts)) {
                require_once "uPage/elements/rubrics_arts/common.php";
                $this->rubrics_arts=new rubrics_arts($this);
            }
                $this->rubrics_arts->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="rubrics_arts_column") {
            if(!isset($this->rubrics_arts_column)) {
                require_once "uPage/elements/rubrics_arts_column/common.php";
                $this->rubrics_arts_column=new urubrics_arts_column($this);
            }
                $this->rubrics_arts_column->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="rubrics_tiles") {
            if(!isset($this->rubrics_tiles)) {
                require_once "uPage/elements/rubrics_tiles/common.php";
                $this->rubrics_tiles=new urubrics_tiles($this);
            }
                $this->rubrics_tiles->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="search") {
            if(!isset($this->search)) {
                require_once "uPage/elements/search/common.php";
                $this->search=new search($this);
            }
            $this->search->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="share") {
            if(!isset($this->share)) {
                require_once "uPage/elements/share/common.php";
                $this->share=new share($this);
            }
                $this->share->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="spacer") {
            if(!isset($this->spacer)) {
                require_once "uPage/elements/spacer/common.php";
                $this->spacer=new spacer($this);
            }
                $this->spacer->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="tabs") {
            if(!isset($this->tabs)) {
                require_once "uPage/elements/tabs/common.php";
                $this->tabs=new tabs($this);
            }
                $this->tabs->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="ticker") {
            if(!isset($this->ticker)) {
                require_once "uPage/elements/ticker/common.php";
                $this->ticker=new ticker($this);
            }
                $this->ticker->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_latest") {
            if(!isset($this->uCat_latest)) {
                require_once "uPage/elements/uCat_latest/common.php";
                $this->uCat_latest=new uCat_latest($this);
            }
                $this->uCat_latest->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_latest_articles_slider") {
            if(!isset($this->uCat_latest_articles_slider)) {
                require_once "uPage/elements/uCat_latest_articles_slider/common.php";
                $this->uCat_latest_articles_slider=new uCat_latest_articles_slider($this);
            }
                $this->uCat_latest_articles_slider->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_new_items") {
            if(!isset($this->uCat_new_items)) {
                require_once "uPage/elements/uCat_new_items/common.php";
                $this->uCat_new_items=new uCat_new_items($this);
            }
                $this->uCat_new_items->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_popular") {
            if(!isset($this->uCat_popular)) {
                require_once "uPage/elements/uCat_popular/common.php";
                $this->uCat_popular=new uCat_popular($this);
            }
                $this->uCat_popular->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_sale") {
            if(!isset($this->uCat_sale)) {
                require_once "uPage/elements/uCat_sale/common.php";
                $this->uCat_sale=new uCat_sale($this);
            }
                $this->uCat_sale->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_search") {
            if(!isset($this->uCat_search)) {
                require_once "uPage/elements/uCat_search/common.php";
                $this->uCat_search=new uCat_search($this);
            }
                $this->uCat_search->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uCat_sects") {
            if(!isset($this->uCat_sects)) {
                require_once "uPage/elements/uCat_sects/common.php";
                $this->uCat_sects=new uCat_sects($this);
            }
                $this->uCat_sects->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uEditor_texts_top") {
            if(!isset($this->uEditor_texts_top)) {
                require_once "uPage/elements/uEditor_texts_top/common.php";
                $this->uEditor_texts_top=new uEditor_texts_top($this);
            }
                $this->uEditor_texts_top->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uEvents_list") {
            if(!isset($this->uEvents_list)) {
                require_once "uPage/elements/uEvents_list/common.php";
                $this->uEvents_list=new uEvents_list($this);
            }
                $this->uEvents_list->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }
        elseif($el_type==="uSubscr_news_form") {
            if(!isset($this->uSubscr_news_form)) {
                require_once "uPage/elements/uSubscr_news_form/common.php";
                $this->uSubscr_news_form=new uSubscr_news_form($this);
            }
                $this->uSubscr_news_form->copy_el($cols_els_id,$col_id,$cols_el,$source_site_id,$dest_site_id);
        }

        return $cols_els_id;
    }
    private function update_ids_in_cols_els_parent_col_css($col_id,$cols_els_id,$new_cols_els_id,$site_id=site_id) {
        if(!$col_data=$this->col_id2data($new_cols_els_id,"col_css,row_id",$site_id)) return 0;

        $row_id=(int)$col_data->row_id;
        $col_css=$col_data->col_css;
        $col_css=uString::sql2text($col_css,1);
        $col_css=$this->update_ids_in_cols_els_css($cols_els_id,$new_cols_els_id,$col_css);
        $col_css=uString::text2sql($col_css);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            u235_cols
            SET
            col_css=:col_css
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_css', $col_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 380'/*.$e->getMessage()*/);}

        $row_data=$this->row_id2data($row_id,"row_css",$site_id);
        $row_css=$row_data->row_css;
        $row_css=uString::sql2text($row_css,1);
        $row_css=$this->update_ids_in_cols_els_css($cols_els_id,$new_cols_els_id,$row_css);
        $row_css=uString::text2sql($row_css);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
            u235_rows
            SET
            row_css=:row_css
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_css', $row_css,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 390'/*.$e->getMessage()*/);}

        return 1;
    }
    private function update_ids_in_cols_els_css($cols_els_id,$new_cols_els_id,$css) {
        return str_replace("uPage_el_".$cols_els_id,"uPage_el_".$new_cols_els_id,$css);
    }
    public function cols_els_id2data($cols_els_id,$q_select="cols_els_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_cols_els 
            WHERE 
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 395'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_above_el_id($el_pos,$col_id,$site_id=site_id) {
        //Достаем id элемента, под который вставляем новый
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            cols_els_id
            FROM
            u235_cols_els
            WHERE
            col_id=:col_id AND
            site_id=:site_id AND
            el_pos<:el_pos
            ORDER BY
            el_pos DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->cols_els_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 400'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_first_cols_el_of_page($page_id,$q_select="cols_els_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_cols_els
            JOIN
            u235_cols
            ON 
            u235_cols.col_id=u235_cols_els.col_id AND
            u235_cols.site_id=u235_cols_els.site_id
            JOIN 
            u235_rows
            ON
            u235_rows.row_id=u235_cols.row_id AND
            u235_rows.site_id=u235_cols.site_id
            WHERE 
            page_id=:page_id AND
            u235_cols_els.site_id=:site_id
            ORDER BY el_id ASC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 410'/*.$e->getMessage()*/);}
        return 0;
    }

    //ELS
    public function add_el2db($cols_els_id,$el_pos,$el_type,$col_id,$el_id,$site_id=site_id) {
        $this->create_el($cols_els_id,$col_id,$el_type,$el_pos,"",$el_id);

        $above_el_id=$this->get_above_el_id($el_pos,$col_id,$site_id);

        //Достаем все el с cols_els_id и el_pos, чтобы передать браузеру информацию об изменениях
        $result='';
        $stm=$this->get_all_col_els_of_col($col_id,"cols_els_id,el_pos",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($el=$stm->fetch(PDO::FETCH_OBJ)) {
            $result.='"el_'.$el->cols_els_id.'":"'.$el->el_pos.'",';
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $result.='"status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        "col_id":"'.$col_id.'",
        "el_pos":"'.$el_pos.'",
        "el_id":"'.$el_id.'",
        "el_type":"'.$el_type.'",
        "above_el_id":"'.$above_el_id.'"
        ';

        $page_id=$this->get_page_id('col',$col_id,$site_id);

        $this->clear_cache($page_id,$site_id);
        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        if(
            $_POST['el_type']=='uEditor_texts_top'||
            $_POST['el_type']=='rubrics_arts'||

            $_POST['el_type']=='uCat_sects'||

            $_POST['el_type']=='form'||

            $_POST['el_type']=='uEvents_list'||
            $_POST['el_type']=='uEvents_dates'||
            $_POST['el_type']=='uEvents_calendar'
        ) die('{'.$result.'}');
        else return array($result,$cols_els_id);
    }
    public function move_down_els_since_el_pos($el_pos,$col_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols_els
            SET
            el_pos=el_pos+1
            WHERE
            el_pos>=:el_pos AND
            col_id=:col_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 420'/*.$e->getMessage()*/);}
    }
    public function define_new_el_pos($col_id,$site_id=site_id) {
        $el_pos=(int)$_POST['el_pos'];
        if($el_pos==0) {//Если 0, то вставляем на самый верх. Нужно посмотреть el_pos самого верхнего el и поставить над ним (значение может быть отрицательным). Создавать el с el_pos=0 нельзя. Это зарезервировано!
            //Смотрим el_pos самого верхнего элемента
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                el_pos
                FROM
                u235_cols_els
                WHERE
                col_id=:col_id AND
                site_id=:site_id
                ORDER BY
                el_pos ASC
                LIMIT 1
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $next_el_pos=(int)$qr->el_pos;
                    $new_el_pos=$next_el_pos-1;
                    if($new_el_pos==0) $new_el_pos=-1;
                }
                else $new_el_pos=1;
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 430'/*.$e->getMessage()*/);}
        }
        else {//Вставляем под какой-то уже существующий el
            //Ищем el, у которого el_pos идет следующим за тем, под которым мы вставляем, чтобы понять, между какими значениями el_pos нам нужно впихнуть наш новый el
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                el_pos
                FROM
                u235_cols_els
                WHERE
                el_pos>:el_pos AND
                col_id=:col_id AND
                site_id=:site_id
                ORDER BY
                el_pos ASC
                LIMIT 1
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $next_el_pos=(int)$qr->el_pos;
                    if($next_el_pos-$el_pos>1) {
                        $new_el_pos=$el_pos+1;
                        if(!$new_el_pos) {
                            $new_el_pos=$el_pos+2;
                            if($new_el_pos>=$next_el_pos) {
                                //next_el_pos и ниже нужно подвинуть вниз
                                $this->move_down_els_since_el_pos($next_el_pos,$col_id);
                                if($next_el_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                                    $this->move_down_els_since_el_pos(0,$col_id);
                                }
                            }
                        }
                    }
                    else {
                        //next_el_pos и ниже нужно подвинуть вниз
                        $this->move_down_els_since_el_pos($next_el_pos,$col_id);
                        if($next_el_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                            $this->move_down_els_since_el_pos(0,$col_id);
                        }
                        $new_el_pos=$el_pos+1;
                    }
                }
                else $new_el_pos=$el_pos+1;
            }
            catch(PDOException $e) {$this->uFunc->error('uPage/common 440'/*.$e->getMessage()*/);}
        }
        /** @noinspection PhpUndefinedVariableInspection */
        return $new_el_pos;
    }
    public function el_id_type2cols_els_id($el_id,$el_type) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
             cols_els_id
             FROM 
             u235_cols_els 
             WHERE
             el_id=:el_id AND
             el_type=:el_type AND
             site_id=:site_id");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_type', $el_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpStatementHasEmptyBodyInspection */
            for($i=0; $res[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++);

            return $res;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 450'/*.$e->getMessage()*/);}
        return 0;
    }
    public function el_id2page_ids($el_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            u235_rows.page_id 
            FROM 
            u235_cols_els
            JOIN 
            u235_cols
            ON 
            u235_cols_els.col_id=u235_cols.col_id AND
            u235_cols_els.site_id=u235_cols.site_id
            JOIN u235_rows
            ON 
            u235_cols.row_id=u235_rows.row_id AND
            u235_cols.site_id=u235_rows.site_id
            WHERE 
            u235_cols_els.el_id=:el_id AND
            u235_cols_els.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage/common 460'/*.$e->getMessage()*/);}

        return 0;
    }

    //WIDGETS
    public function build_pages_top_widget($site_id=site_id){
        //get last 10 most viewable pages
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT DISTINCT
            page_url,
            `madmakers_uPage`.u235_pages.page_id,
            page_title
            FROM
            `madmakers_uPage`.u235_pages
            JOIN 
            `madmakers_pages`.u235_urubrics_pages
            ON
            `madmakers_pages`.u235_urubrics_pages.page_id=`madmakers_uPage`.u235_pages.page_id AND
            `madmakers_pages`.u235_urubrics_pages.site_id=`madmakers_uPage`.u235_pages.site_id
            WHERE
            `madmakers_uPage`.u235_pages.site_id=:site_id
            ORDER BY
            views_counter DESC
            LIMIT 10
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor common 90'/*.$e->getMessage()*/);}
        $pages_top_cnt='<table class="table table-hover">';
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        while($page=$stm->fetch(PDO::FETCH_OBJ)) {
            $page_url=$page->page_url;
            $page_id=(int)$page->page_id;

            if($page_url==="") $url="uPage/".$page_id;
            else $url=$page_url;

            $pages_top_cnt.='<tr><td><a style="color:inherit; text-decoration:none;" href="'.u_sroot.$url.'">'.uString::sql2text($page->page_title,1).'</a></td></tr>';
        }
        $pages_top_cnt.='</table>';

        return $pages_top_cnt;
    }

    //COMMON
    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','common'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=&$this;
    }
}
