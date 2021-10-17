<?php
namespace uSlider;
use PDO;
use PDOException;
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

class common {
    public $uCore;
    public $uFunc;

    private function copy_slide($new_slider_id,$slide,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_slide_id=$this->get_new_slide_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO u235_slides (
            slider_id, 
            slide_id, 
            slide_html, 
            slide_pos, 
            img_timestamp, 
            light_bg, 
            full_width, 
            centered, 
            dark_bg, 
            site_id
            ) VALUES (
            :slider_id, 
            :slide_id, 
            :slide_html, 
            :slide_pos, 
            :img_timestamp, 
            :light_bg, 
            :full_width, 
            :centered, 
            :dark_bg, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $new_slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $new_slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_html', $slide->slide_html,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $slide->slide_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_timestamp', $slide->img_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':light_bg', $slide->light_bg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':full_width', $slide->full_width,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':centered', $slide->centered,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dark_bg', $slide->dark_bg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 10'/*.$e->getMessage()*/,1);}

        //Copy files
        if((int)$slide->img_timestamp) {
            $src_folder= 'uSlider/slides_bg/'.$source_site_id.'/'.$slide->slide_id.'/';
            $dest_folder= 'uSlider/slides_bg/'.$dest_site_id.'/'.$new_slide_id.'/';

            $src_file=$src_folder.$slide->slide_id.'.jpg';
            $dest_file=$dest_folder.$new_slide_id.'.jpg';

            // Create dir
            if(!file_exists($dest_folder)) mkdir($dest_folder,0755,true);
            if(!$this->uFunc->create_empty_index($dest_folder)) $this->uFunc->error("uSlider common 20",1);

            //copy file
            @copy ($src_file,$dest_file);
        }
    }
    public function copy_slider($slider_id,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_slider_id=$this->get_new_slider_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            * 
            FROM 
            u235_sliders 
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 30'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$slider=$stm->fetch(PDO::FETCH_OBJ)) return 0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO u235_sliders (
            slider_id, 
            slider_title, 
            status, 
            timestamp, 
            slider_type, 
            site_id
            ) 
            VALUES (
            :slider_id, 
            :slider_title, 
            :status, 
            :timestamp, 
            :slider_type, 
            :site_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $new_slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_title', $slider->slider_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $slider->status,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $slider->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_type', $slider->slider_type,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 40'/*.$e->getMessage()*/,1);}

        //copy slider's settings
        if($slider->slider_type==="owl") {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                * 
                FROM 
                u235_slider_owl_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('uSlider common 50'/*.$e->getMessage()*/,1);}
        }
        elseif($slider->slider_type==="bootstrap") {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                * 
                FROM 
                u235_slider_bootstrap_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('uSlider common 60'/*.$e->getMessage()*/,1);}
        }
        elseif($slider->slider_type==="flip_book") {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                * 
                FROM 
                slider_flip_book_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('uSlider common 70'/*.$e->getMessage()*/,1);}
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if($settings=$stm->fetch(PDO::FETCH_OBJ)) {
            if($slider->slider_type==="owl") {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO u235_slider_owl_settings (
                    slider_id, 
                    items_number_xlg, 
                    items_number_lg, 
                    items_number_md, 
                    items_number_sm, 
                    items_number_xs, 
                    nav_xlg, 
                    nav_lg, 
                    nav_md, 
                    nav_sm, 
                    nav_xs, 
                    dots_xlg, 
                    dots_lg, 
                    dots_md, 
                    dots_sm, 
                    dots_xs, 
                    dots_style, 
                    slideSpeed, 
                    autoPlay, 
                    scrollPerPage, 
                    site_id
                    ) VALUES (
                    :slider_id, 
                    :items_number_xlg, 
                    :items_number_lg, 
                    :items_number_md, 
                    :items_number_sm, 
                    :items_number_xs, 
                    :nav_xlg, 
                    :nav_lg, 
                    :nav_md, 
                    :nav_sm, 
                    :nav_xs, 
                    :dots_xlg, 
                    :dots_lg, 
                    :dots_md, 
                    :dots_sm, 
                    :dots_xs, 
                    :dots_style, 
                    :slideSpeed, 
                    :autoPlay, 
                    :scrollPerPage, 
                    :site_id         
                    )
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $new_slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_xlg', $settings->items_number_xlg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_lg', $settings->items_number_lg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_md', $settings->items_number_md, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_sm', $settings->items_number_sm, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_xs', $settings->items_number_xs, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_xlg', $settings->nav_xlg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_lg', $settings->nav_lg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_md', $settings->nav_md, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_sm', $settings->nav_sm, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_xs', $settings->nav_xs, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_xlg', $settings->dots_xlg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_lg', $settings->dots_lg, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_md', $settings->dots_md, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_sm', $settings->dots_sm, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_xs', $settings->dots_xs, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $settings->dots_style, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slideSpeed', $settings->slideSpeed, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':autoPlay', $settings->autoPlay, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':scrollPerPage', $settings->scrollPerPage, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {$this->uFunc->error('uSlider common 80'/*.$e->getMessage()*/,1);}
            }
            elseif($slider->slider_type==="bootstrap") {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO u235_slider_bootstrap_settings (
                    slider_id, 
                    interval_delay, 
                    pause, 
                    wrap, 
                    keyboard, 
                    show_indicators, 
                    show_controls, 
                    min_height, 
                    max_height, 
                    site_id,
                    dots_style
                    ) VALUES (
                    :slider_id, 
                    :interval_delay, 
                    :pause, 
                    :wrap, 
                    :keyboard, 
                    :show_indicators, 
                    :show_controls, 
                    :min_height, 
                    :max_height, 
                    :site_id,        
                    :dots_style        
                    )
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $new_slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':interval_delay', $settings->interval_delay, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pause', $settings->pause, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':wrap', $settings->wrap, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':keyboard', $settings->keyboard, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_indicators', $settings->show_indicators, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_controls', $settings->show_controls, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_height', $settings->min_height, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':max_height', $settings->max_height, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $settings->dots_style, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {$this->uFunc->error('uSlider common 90'/*.$e->getMessage()*/,1);}
            }
            elseif($slider->slider_type==="flip_book") {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO slider_flip_book_settings (
                    slider_id, 
                    site_id, 
                    height
                    ) VALUES (
                    :slider_id, 
                    :site_id, 
                    :height          
                    )
                    ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $new_slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':height', $settings->height, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {$this->uFunc->error('uSlider common 100'/*.$e->getMessage()*/,1);}
            }
        }

        //copy slides
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            * 
            FROM 
            u235_slides
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($slide=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_slide($new_slider_id,$slide,$source_site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 110'/*.$e->getMessage()*/,1);}

        return $new_slider_id;
    }

    public function get_new_slider_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            slider_id
            FROM 
            u235_sliders
            WHERE 
            site_id=:site_id
            ORDER BY 
            slider_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->slider_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 120'/*.$e->getMessage()*/,1);}

        return 1;
    }
    public function get_new_slide_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            slide_id
            FROM 
            u235_slides
            WHERE 
            site_id=:site_id
            ORDER BY 
            slide_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->slide_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 130'/*.$e->getMessage()*/,1);}

        return 1;
    }

    public function slide_id2slider_id($slide_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            slider_id 
            FROM 
            u235_slides 
            WHERE 
            slide_id=:slide_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;
            return $qr->slider_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 140'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function slider_id2slider_type($slider_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slider_type
            FROM 
            u235_sliders 
            WHERE 
            slider_id=:slider_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 'error';
            return $qr->slider_type;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 150'/*.$e->getMessage()*/,1);}

        return false;
    }
    public function slider_id2slider_info($slider_id,$q_fields="slider_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            ".$q_fields."
            FROM
            u235_sliders
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 160'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function slider_id2slider_settings($slider_id,$slider_type,$q_fields="slider_id") {
        if($slider_type=='bootstrap') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
                ".$q_fields."
                FROM
                u235_slider_bootstrap_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 170'/*.$e->getMessage()*/,1);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr;
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    u235_slider_bootstrap_settings (
                    slider_id,
                    site_id
                    ) VALUES (
                    :slider_id,
                    :site_id
                    )
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uSlider common 180'/*.$e->getMessage()*/,1);}

                return $this->slider_id2slider_settings($slider_id,$slider_type,$q_fields);
            }
        }
        elseif($slider_type=='flip_book') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
                ".$q_fields."
                FROM
                slider_flip_book_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 240'/*.$e->getMessage()*/,1);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr;
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    slider_flip_book_settings (
                    slider_id,
                    site_id,
                    height
                    ) VALUES (
                    :slider_id,
                    :site_id,
                    600          
                    )
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uSlider common 250'/*.$e->getMessage()*/,1);}

                return $this->slider_id2slider_settings($slider_id,$slider_type,$q_fields);
            }
        }
        elseif($slider_type=='owl') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
                ".$q_fields."
                FROM
                u235_slider_owl_settings
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 260'/*.$e->getMessage()*/,1);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr;
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    u235_slider_owl_settings (
                    slider_id,
                    site_id
                    ) VALUES (
                    :slider_id,
                    :site_id
                    )
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uSlider common 270'/*.$e->getMessage()*/);}

                return $this->slider_id2slider_settings($slider_id,$slider_type,$q_fields);
            }
        }
        return false;
    }
    public function cols_els_id2slider_id($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            el_id 
            FROM 
            u235_cols_els 
            WHERE 
            cols_els_id=:cols_els_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;
            return $qr->el_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 350'/*.$e->getMessage()*/);}

        return false;
    }
    public function clear_cache($cols_els_id) {
        $this->uFunc->rmdir('uSlider/cache/'.site_id.'/'.$cols_els_id);
    }
    public function clear_cache_by_slider_id($slider_id) {
        //get cols_els_id of this slider_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id,
            u235_cols_els.cols_els_id
            FROM 
            u235_cols_els
            JOIN 
            u235_cols
            ON
            u235_cols_els.col_id=u235_cols.col_id AND
            u235_cols_els.site_id=u235_cols.site_id
            JOIN
            u235_rows
            ON
            u235_cols.row_id=u235_rows.row_id AND
            u235_cols.site_id=u235_rows.site_id
            WHERE 
            u235_cols_els.el_id=:el_id AND
            u235_cols_els.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->clear_cache($qr->cols_els_id);
                $this->uFunc->rmdir("uPage/cache/".site_id.'/'.$qr->page_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 360'/*.$e->getMessage()*/);}
    }

    public function get_slides($slider_id,$q_fields="slide_id") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            ".$q_fields."
            FROM 
            u235_slides 
            WHERE 
            slider_id=:slider_id AND 
            site_id=:site_id
            ORDER BY 
            slide_pos ASC,
            slide_id ASC 
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 370'/*.$e->getMessage()*/);}
        return 0;
    }
    private function create_slider_default_settings($slider_id,$slider_type) {
        if($slider_type=="bootstrap") {
            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                u235_slider_bootstrap_settings (
                slider_id,
                site_id
                ) VALUES (
                :slider_id,
                :site_id
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 375'/*.$e->getMessage()*/);}
        }
        elseif($slider_type=="owl") {
            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                u235_slider_owl_settings (
                slider_id,
                site_id
                ) VALUES (
                :slider_id,
                :site_id
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 380'/*.$e->getMessage()*/);}
        }
        elseif($slider_type=="flip_book") {
            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                slider_flip_book_settings (
                slider_id,
                site_id,
                height
                ) VALUES (
                :slider_id,
                :site_id,
                600
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uSlider common 385'/*.$e->getMessage()*/);}
        }
    }
    private function get_slider_settings($slider_id) {
        try {
            $slider_type=$this->slider_id2slider_type($slider_id);
            if($slider_type=="bootstrap") {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                max_height,
                min_height,
                interval_delay,
                pause,
                wrap,
                keyboard,
                show_indicators,
                show_controls,
                dots_style
                FROM 
                u235_slider_bootstrap_settings 
                WHERE 
                slider_id=:slider_id AND 
                site_id=:site_id
                ");
            }
            elseif($slider_type=="owl") {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                items_number_xlg,
                items_number_lg,
                items_number_md,
                items_number_sm,
                items_number_xs,
                nav_xlg,
                nav_lg,
                nav_md,
                nav_sm,
                nav_xs,
                dots_xlg,
                dots_lg,
                dots_md,
                dots_sm,
                dots_xs,
                dots_style,
                slideSpeed,
                autoPlay,
                navigation,
                scrollPerPage,
                pagination
                FROM 
                u235_slider_owl_settings
                WHERE 
                slider_id=:slider_id AND 
                site_id=:site_id
                ");
            }
            elseif($slider_type=="flip_book") {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSlider")->prepare("SELECT 
                height
                FROM 
                slider_flip_book_settings
                WHERE 
                slider_id=:slider_id AND 
                site_id=:site_id
                ");
            }
            else return 0;
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_slider_default_settings($slider_id,$slider_type);
                return $this->get_slider_settings($slider_id);
            }

            return $qr;
        }
        catch(PDOException $e) {$this->uFunc->error('uSlider common 390'/*.$e->getMessage()*/);}

        return false;
    }
    public function cache_bootstrap_slider($cols_els_id,$site_id=site_id) {
        $dir='uSlider/cache/'.site_id.'/'.$cols_els_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/slider.html')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);
            $file = fopen($dir.'/slider.html', 'w');
            ob_start();
            $slider_id=$this->cols_els_id2slider_id($cols_els_id);
            $q_slides=$this->get_slides($slider_id,"slide_id,slide_html,light_bg,full_width,centered,img_timestamp");
            $slider_settings=$this->get_slider_settings($slider_id);

            $slider_settings->dots_style=(int)$slider_settings->dots_style;
            $dots_style=$slider_settings->dots_style;
            if(!$dots_style) {
                if(!isset($this->uPage)) {
                    require_once "uPage/inc/common.php";
                    $this->uPage=new \uPage\common($this->uCore);
                }
                if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style",$site_id)) {
                    $dots_style=(int)$site_style_obj->sliders_dots_style;
                }
                else $dots_style=4;
            }
            ?>
            <div id="uPage_bootstrap_carousel_<?=$cols_els_id?>" class="carousel slide dots_style_<?=$dots_style?> <?=$slider_settings->dots_style?"":"dots_style_0"?>" data-ride="carousel"
                 style="
                 <?=$slider_settings->max_height?(' max-height:'.$slider_settings->max_height.'px; '):''?>
                 <?=$slider_settings->min_height?(' min-height:'.$slider_settings->min_height.'px; '):''?>
                         ">
                <!-- Indicators -->
                <div class="carousel-indicators-container">
                    <ol class="carousel-indicators">
                        <?
                        /** @noinspection PhpUndefinedMethodInspection */
                        for($i=0; $slide[$i]=$q_slides->fetch(PDO::FETCH_OBJ); $i++) {?>
                        <li data-target="#uPage_bootstrap_carousel_<?=$cols_els_id?>" data-slide-to="<?=$i?>" class="<?=!$i?"active":""?>"></li>
                        <?}?>
                    </ol>
                </div>

                <!-- Wrapper for slides -->
                <div class="carousel-inner" role="listbox">
                    <?
                    for($i=0;$slide[$i];$i++) {?>
                    <div class="item
                    <?=!$i?" active ":""?>
                    <?=(int)$slide[$i]->light_bg?' light_bg ':''?>
                    " style='
                    <?=$slider_settings->max_height?(' max-height:'.$slider_settings->max_height.'px; '):''?>
                    <?=$slider_settings->min_height?(' min-height:'.$slider_settings->min_height.'px; '):''?>
                    <?=$slide[$i]->img_timestamp?(' background-image:url("'.u_sroot.'uSlider/slides_bg/'.site_id.'/'.$slide[$i]->slide_id.'/'.$slide[$i]->slide_id.'.jpg"); '):('background-image:url("'.u_sroot.'images/common/000000-0.png"); ')?>
                    <?=(int)$slide[$i]->full_width?' background-size:cover; ':''?>
                    <?=(int)$slide[$i]->centered?' background-position:center center; ':''?>
                            '>
                        <div class="carousel-caption d-none d-md-block"><?=\uString::sql2text($slide[$i]->slide_html,1)?></div>
                    </div>
                    <?}?>
                </div>

                <!-- Left and right controls -->
                <a class="left carousel-control" href="#uPage_bootstrap_carousel_<?=$cols_els_id?>" role="button" data-slide="prev">
                    <span class="icon-left-open" aria-hidden="true"></span>
                    <span class="sr-only">Previous</span>
                </a>
                <a class="right carousel-control" href="#uPage_bootstrap_carousel_<?=$cols_els_id?>" role="button" data-slide="next">
                    <span class="icon-right-open" aria-hidden="true"></span>
                    <span class="sr-only">Next</span>
                </a>
            </div>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();

            $file = fopen($dir.'/slider.js', 'w');
            ob_start(); ?>
            //<script type="text/javascript">
            $(document).ready(function() {
                $("#uPage_bootstrap_carousel_<?=$cols_els_id?>").carousel({
                    interval: <?=(int)$slider_settings->interval_delay?>,
                    pause: <?=(int)$slider_settings->pause?'"hover"':"false"?>,
                    wrap: <?=(int)$slider_settings->wrap?"true":"false"?>,
                    keyboard: <?=(int)$slider_settings->keyboard?"true":"false"?>
                });
                <?if((int)$slider_settings->show_indicators) {?>
                $("#uPage_bootstrap_carousel_<?=$cols_els_id?> .carousel-indicators").show();
                <?} else {?>
                $("#uPage_bootstrap_carousel_<?=$cols_els_id?> .carousel-indicators").hide();
                <?}?>
                <?if((int)$slider_settings->show_controls){?>
                $("#uPage_bootstrap_carousel_<?=$cols_els_id?> .carousel-control").show();
                <?} else {?>
                $("#uPage_bootstrap_carousel_<?=$cols_els_id?> .carousel-control").hide();
                <?}?>
            });
            <?/*//</script>*/?>
            <?fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }
    public function cache_owl_slider($cols_els_id,$site_id=site_id) {
        $dir='uSlider/cache/'.site_id.'/'.$cols_els_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/slider.html')||!file_exists($dir.'/slider.js')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            $slider_id=$this->cols_els_id2slider_id($cols_els_id);
            if(!isset($slider_settings)) $slider_settings=$this->get_slider_settings($slider_id);

            $file = fopen($dir.'/slider.html', 'w');
            ob_start();
            $q_slides=$this->get_slides($slider_id,"slide_id,slide_html,light_bg,full_width,centered,img_timestamp");

            $slider_settings->dots_style=(int)$slider_settings->dots_style;
            $dots_style=$slider_settings->dots_style;
            if(!$dots_style) {
                if(!isset($this->uPage)) {
                    require_once "uPage/inc/common.php";
                    $this->uPage=new \uPage\common($this->uCore);
                }
                if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style",$site_id)) {
                    $dots_style=(int)$site_style_obj->sliders_dots_style;
                }
                else $dots_style=4;
            }
            ?>

            <div id="uPage_owl_carousel_<?=$cols_els_id?>" class="owl-carousel dots_style_<?=$dots_style?> <?=$slider_settings->dots_style?"":"dots_style_0"?>">
                <?
                    $slider_settings=$this->get_slider_settings($slider_id);
                /** @noinspection PhpUndefinedMethodInspection */
                for($slide_i=1;$slide=$q_slides->fetch(PDO::FETCH_OBJ);$slide_i++) {?>
                        <div id="uSlider_slide_preview_<?=$slide->slide_id?>" class="item"><?=\uString::sql2text($slide->slide_html,1)?></div>
                    <?}?>
            </div>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();


            $file = fopen($dir.'/slider.js', 'w');
            ob_start();

            $slider_settings->items_number_xs=(int)$slider_settings->items_number_xs;
            $slider_settings->items_number_sm=(int)$slider_settings->items_number_sm;
            $slider_settings->items_number_md=(int)$slider_settings->items_number_md;
            $slider_settings->items_number_lg=(int)$slider_settings->items_number_lg;
            $slider_settings->items_number_xlg=(int)$slider_settings->items_number_xlg;
            $slider_settings->nav_xlg=(int)$slider_settings->nav_xlg;
            $slider_settings->nav_lg=(int)$slider_settings->nav_lg;
            $slider_settings->nav_md=(int)$slider_settings->nav_md;
            $slider_settings->nav_sm=(int)$slider_settings->nav_sm;
            $slider_settings->nav_xs=(int)$slider_settings->nav_xs;
            $slider_settings->dots_xlg=(int)$slider_settings->dots_xlg;
            $slider_settings->dots_lg=(int)$slider_settings->dots_lg;
            $slider_settings->dots_md=(int)$slider_settings->dots_md;
            $slider_settings->dots_sm=(int)$slider_settings->dots_sm;
            $slider_settings->dots_xs=(int)$slider_settings->dots_xs;

            $slider_settings->dots_style=(int)$slider_settings->dots_style;
            ?>
            //<script type="text/javascript">
           $(document).ready(function() {
                $("#uPage_owl_carousel_<?=$cols_els_id?>").owlCarousel({
                    autoplayTimeout:<?=(int)$slider_settings->autoPlay?>,
                    slideSpeed:<?=(int)$slider_settings->slideSpeed?>,
                    autoplayHoverPause:true,
                    slideBy:<?=(int)$slider_settings->scrollPerPage?'"page"':1?>,
                    navText:['<span class="icon-left-open"></span>','<span class="icon-right-open"></span>'],
                    loop:true,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:10,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:<?=$slider_settings->items_number_xs?>,
                            nav:<?=(int)$slider_settings->nav_xs?'true':'false'?>,
                            dots:<?=(int)$slider_settings->dots_xs?'true':'false'?>,
                            autoplay:<?=$slide_i>$slider_settings->items_number_xs?'true':'false'?>
                        },
                        768:{
                            items:<?=$slider_settings->items_number_sm?>,
                            nav:<?=(int)$slider_settings->nav_sm?'true':'false'?>,
                            dots:<?=(int)$slider_settings->dots_sm?'true':'false'?>,
                            autoplay:<?=$slide_i>$slider_settings->items_number_sm?'true':'false'?>
                        },
                        992:{
                            items:<?=$slider_settings->items_number_md?>,
                            nav:<?=(int)$slider_settings->nav_md?'true':'false'?>,
                            dots:<?=(int)$slider_settings->dots_md?'true':'false'?>,
                            autoplay:<?=$slide_i>$slider_settings->items_number_md?'true':'false'?>
                        },
                        1200:{
                            items:<?=$slider_settings->items_number_lg?>,
                            nav:<?=(int)$slider_settings->nav_lg?'true':'false'?>,
                            dots:<?=(int)$slider_settings->dots_lg?'true':'false'?>,
                            autoplay:<?=$slide_i>$slider_settings->items_number_lg?'true':'false'?>
                        },
                        1600:{
                            items:<?=$slider_settings->items_number_xlg?>,
                            nav:<?=(int)$slider_settings->nav_xlg?'true':'false'?>,
                            dots:<?=(int)$slider_settings->dots_xlg?'true':'false'?>,
                            autoplay:<?=$slide_i>$slider_settings->items_number_xlg?'true':'false'?>
                        }
                    }
                });
           });
            <?/*//</script>*/?>
            <?fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }
    public function cache_flip_book($cols_els_id/*,$site_id=site_id*/) {
        $dir='uSlider/cache/'.site_id.'/'.$cols_els_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/slider.html')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);
            $file = fopen($dir.'/slider.html', 'w');
            ob_start();
            $slider_id=$this->cols_els_id2slider_id($cols_els_id);
            $slider_settings=$this->get_slider_settings($slider_id);
            $q_slides=$this->get_slides($slider_id,"slide_id,slide_html");?>
            <div id="uPage_flip_book_<?=$cols_els_id?>">
                <style type="text/css" rel="stylesheet">
                    .book {
                        <?=$slider_settings->height?>px;
                    } +
                </style>
                <div class="content">
                    <div class="book">
                    <?

                    $page_i2num=array("","one","two","three","four","five","six","seven","eight");
                    /** @noinspection PhpUndefinedMethodInspection */
                    for($i=0;$slide=$q_slides->fetch(PDO::FETCH_OBJ);$i++) {
                        $j=$i+1;
                        if($j>8) $j=8;
                        ?>
                        <div class="<?=$page_i2num[$j]?> page">
                            <div class="pageContents" id="uSlider_slide_preview_<?=$slide->slide_id?>">
                                <?=\uString::sql2text($slide->slide_html,1)?>
                            </div>
                        </div>
                    <?}
                    for(;$i<8;$i++) {
                        $j=$i+1;
                        if($j>8) $j=8;
                        ?>
                        <div class="<?=$page_i2num[$j]?> page">
                            <div class="pageContents"></div>
                        </div>
                    <?}
                    ?>
                    </div>
                </div>
                <div class="bookslider_controls">
                    <span class="control left"><span class="icon-left"></span> </span>
                    <span class="control right"><span class="icon-right"></span> </span>
                </div>
            </div>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }

    function __construct(&$uCore){
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
    }
}

new common($this);