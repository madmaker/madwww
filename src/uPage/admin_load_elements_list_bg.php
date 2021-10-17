<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
class load_elements_list {
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['el_type'])) $this->uFunc->error(10);
    }
    private function load_rubrics_list() {
        $action="add";
        if(isset($_POST["action"])) {
            if($_POST["action"]==="edit") $action="edit";
        };
        ?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_rubrics_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_rubrics()">
                <span class="input-group-btn">
                    <button class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_rubrics()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            rubric_id,
            rubric_name
            FROM 
            u235_urubrics_list
            WHERE 
            site_id=:site_id 
            ORDER BY
            rubric_name
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_rubrics_list" class="table table-hover">
                <?
                /** @noinspection PhpUndefinedMethodInspection */
                while($rubric=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td><a href="<?=u_sroot?>uRubrics/show/<?=$rubric->rubric_id?>" target="_blank"><small><?=$rubric->rubric_id?></small> <?=uString::sql2text($rubric->rubric_name,1)?></a></td>
                        <td><button class="btn btn-success btn-xs" onclick="<?
                            if($action==="add") {?>uPage_setup_uPage.add_el_do(<?=$rubric->rubric_id?>)<?}
                            else {?>
                                if(typeof uPage_setup_uPage.el_rubrics_tiles_settings_change_rubric==='function') uPage_setup_uPage.el_rubrics_tiles_settings_change_rubric(<?=$rubric->rubric_id?>,'<?=rawurlencode($rubric->rubric_name)?>');
                                if(typeof uPage_setup_uPage.el_rubrics_arts_settings_change_rubric==='function') uPage_setup_uPage.el_rubrics_arts_settings_change_rubric(<?=$rubric->rubric_id?>,'<?=rawurlencode($rubric->rubric_name)?>');
                                if(typeof uPage_setup_uPage.el_rubrics_arts_column_settings_change_rubric==='function') uPage_setup_uPage.el_rubrics_arts_column_settings_change_rubric(<?=$rubric->rubric_id?>,'<?=rawurlencode($rubric->rubric_name)?>');
                            <?}?>"><span class="icon-plus"></span> <?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
                <?}?>
            </table>
            <?

        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    //uForms
    private function uForms_load_form_list() {?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_forms_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_forms()">
                <span class="input-group-btn">
                    <button id="uPage_forms_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_forms()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            form_id,
            form_title
            FROM 
            u235_forms
            WHERE 
            site_id=:site_id AND
            status IS NULL 
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_forms_list" class="table table-hover">
            <?
            /** @noinspection PhpUndefinedMethodInspection */
            while($form=$stm->fetch(PDO::FETCH_OBJ)) {?>
                <tr>
                    <td><a href="<?=u_sroot?>uForms/form/<?=$form->form_id?>" target="_blank"><small><?=$form->form_id?></small> <?=uString::sql2text($form->form_title,1)?></a></td>
                    <td><button class="btn btn-success btn-xs" onclick="uPage_setup_uPage.add_el_do(<?=$form->form_id?>)"><span class="icon-plus"></span><?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
            <?}?>
            </table>
            <?

        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    //Gallery
    private function gallery_load_galleries_list() {?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_galleries_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_galleries()">
                <span class="input-group-btn">
                    <button id="uPage_galleries_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_galleries()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT
            gallery_id,
            gallery_title
            FROM 
            galleries
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_galleries_list" class="table table-hover">
            <?
            /** @noinspection PhpUndefinedMethodInspection */
            while($gallery=$stm->fetch(PDO::FETCH_OBJ)) {?>
                <tr>
                    <td><small><?=$gallery->gallery_id?></small> <?=$gallery->gallery_title?></td>
                    <td><button class="btn btn-success btn-xs" onclick="uPage_setup_uPage.add_el_do(<?=$gallery->gallery_id?>)"><span class="icon-plus"></span><?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
            <?}?>
            </table>
            <?

        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    //uSlider
    private function uForms_load_sliders_bootstrap_list() {?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_sliders_bootstrap_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_bootstrap_carousels()">
                <span class="input-group-btn">
                    <button id="uPage_sliders_bootstrap_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_bootstrap_carousels()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slider_id,
            slider_title
            FROM 
            u235_sliders
            WHERE
            (status IS NULL OR status='') AND
            slider_type='bootstrap' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_sliders_bootstrap_list" class="table table-hover">
                <?
                /** @noinspection PhpUndefinedMethodInspection */
                while($slider=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td><small><?=$slider->slider_id?></small> <?=uString::sql2text($slider->slider_title,1)?></td>
                        <td><button class="btn btn-success btn-xs" onclick="uPage_setup_uPage.add_slider_bootstrap_carousel_do(<?=$slider->slider_id?>)"><span class="icon-plus"></span> <?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
                <?}?>
            </table>
            <?
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function uForms_load_sliders_owl_list() {?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_sliders_owl_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_owl_carousels()">
                <span class="input-group-btn">
                    <button id="uPage_sliders_owl_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_owl_carousels()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slider_id,
            slider_title
            FROM 
            u235_sliders
            WHERE
            (status IS NULL OR status='') AND
            slider_type='owl' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_sliders_owl_list" class="table table-hover">
                <?
                /** @noinspection PhpUndefinedMethodInspection */
                while($slider=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td><small><?=$slider->slider_id?></small> <?=uString::sql2text($slider->slider_title,1)?></td>
                        <td><button class="btn btn-success btn-xs" onclick="uPage_setup_uPage.add_el_do(<?=$slider->slider_id?>)"><span class="icon-plus"></span> <?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
                <?}?>
            </table>
            <?
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function uForms_load_sliders_flip_book() {?>
        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_flip_book_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_flip_book()">
                <span class="input-group-btn">
                    <button id="uPage_flip_book_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_flip_book()"></span></button>
                </span>
            </div>
        </div>
        <?
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slider_id,
            slider_title
            FROM 
            u235_sliders
            WHERE
            (status IS NULL OR status='') AND
            slider_type='flip_book' AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();?>

            <table id="uPage_flip_book_list" class="table table-hover">
                <?
                /** @noinspection PhpUndefinedMethodInspection */
                while($slider=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td><small><?=$slider->slider_id?></small> <?=uString::sql2text($slider->slider_title,1)?></td>
                        <td><button class="btn btn-success btn-xs" onclick="uPage_setup_uPage.add_slider_flip_book_do(<?=$slider->slider_id?>)"><span class="icon-plus"></span> <?=$this->text("Insert - btn text"/*Вставить*/)?></button></td></tr>
                <?}?>
            </table>
            <?
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    //uEvents
    private function uEvents_load_events_list() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id,
            type_title,
            type_url
            FROM
            u235_events_types
            WHERE
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        echo '<div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_uEvents_lists_filter" class="form-control" placeholder="'.$this->text("Filter placeholder"/*Фильтр*/).'" onkeyup="uPage_setup_uPage.filter_uEvents_lists()">
                    <span class="input-group-btn">
                        <button id="uPage_uEvents_lists_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_uEvents_lists()"></span></button>
                    </span>
                </div>
            </div>';
        echo '<table id="uPage_uEvents_lists" class="table table-hover">';
        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        while($ev=$stm->fetch(PDO::FETCH_OBJ)) {
            echo '<tr><td><a href="'.u_sroot.'uEvents/events/'.uString::sql2text($ev->type_url).'" target="_blank">'.uString::sql2text($ev->type_title,1).'</a></td><td><button class="btn btn-success btn-xs"  data-type_id="'.$ev->type_id.'" onclick="uPage_setup_uPage.add_el_do('.$ev->type_id.')"><span class="icon-plus"></span> '.$this->text("Insert - btn text"/*Вставить*/).'</button></td></tr>';
        }
        echo '</table>';
    }
    private function uEvents_load_events_dates() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_id,
            event_title
            FROM
            u235_events_list
            WHERE
            site_id=:site_id AND
            is_header=0
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        echo '<div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_events_dates_filter" class="form-control" placeholder="'.$this->text("Filter placeholder"/*Фильтр*/).'" onkeyup="uPage_setup_uPage.filter_events_dates()">
                    <span class="input-group-btn">
                        <button id="uPage_uEvents_lists_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_events_dates()"></span></button>
                    </span>
                </div>
            </div>';
        echo '<table id="uPage_events_dates" class="table table-hover">';
        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        while($ev=$stm->fetch(PDO::FETCH_OBJ)) {
            echo '<tr><td><a href="'.u_sroot.'uEvents/event/'.uString::sql2text($ev->event_id).'" target="_blank">'.uString::sql2text($ev->event_title,1).'</a></td><td><button class="btn btn-success btn-xs" data-event_id="'.$ev->event_id.'" onclick="uPage_setup_uPage.add_uEvents_dates_do(this)"><span class="icon-plus"></span> '.$this->text("Insert - btn text"/*Вставить*/).'</button></td></tr>';
        }
        echo '</table>';
    }
    private function uEvents_load_events_calendar() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id,
            type_title
            FROM
            u235_events_types
            WHERE
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}?>

        <div class="form-horizontal">
            <div class="input-group">
                <input type="text" id="uPage_events_dates_filter" class="form-control" placeholder="<?=$this->text("Filter placeholder"/*Фильтр*/)?>" onkeyup="uPage_setup_uPage.filter_events_calendar()">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="icon-search" onclick="uPage_setup_uPage.filter_events_calendar()"></span></button>
                    </span>
                </div>
            </div>
        <table id="uPage_events_dates" class="table table-hover">
        <?
        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($ev=$stm->fetch(PDO::FETCH_OBJ)) {
            echo '<tr><td><a href="'.u_sroot.'uEvents/events/'.uString::sql2text($ev->type_id).'" target="_blank">'.uString::sql2text($ev->type_title,1).'</a></td><td><button class="btn btn-success btn-xs" data-type_id="'.$ev->type_id.'" onclick="uPage_setup_uPage.add_uEvents_calendar_do(this)"><span class="icon-plus"></span> '.$this->text("Insert - btn text"/*Вставить*/).'</button></td></tr>';
        }
        echo '</table>';
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','admin_load_elements_list_bg'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uSes);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        //uEditor
//        if($_POST['el_type']=='art') $this->uEditor_load_art_list();
        //uRubrics
        if($_POST['el_type']=='rubrics') $this->load_rubrics_list();
        //uForms
        elseif($_POST['el_type']=='form') $this->uForms_load_form_list();
        elseif($_POST['el_type']=='galleries') $this->gallery_load_galleries_list();
        //uEvents
        elseif($this->uCore->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_list') $this->uEvents_load_events_list();
        elseif($this->uCore->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_dates') $this->uEvents_load_events_dates();
        elseif($this->uCore->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_calendar') $this->uEvents_load_events_calendar();
        //uSlider
        elseif($_POST['el_type']=='sliders_bootstrap') $this->uForms_load_sliders_bootstrap_list();
        elseif($_POST['el_type']=='sliders_owl') $this->uForms_load_sliders_owl_list();
        elseif($_POST['el_type']=='flip_book') $this->uForms_load_sliders_flip_book();
        else $this->uFunc->error(70);
    }
}
new load_elements_list($this);
