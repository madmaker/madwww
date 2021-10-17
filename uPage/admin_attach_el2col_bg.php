<?php
namespace uPage\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uPage/inc/common.php';
require_once 'uCat/classes/common.php';

class attach_el2col {
    public $uFunc;
    public $uSes;
    public $uPage;
    public $uCat;
    private $uCore,$el_id,$col_id;
    private function check_data() {
        if(!isset($_POST['el_type'],$_POST['el_id'],$_POST['col_id'])) $this->uFunc->error(10,1);
        $this->el_id=$_POST['el_id'];
        $this->col_id=$_POST['col_id'];
        if(!uString::isDigits($this->el_id)) $this->uFunc->error(20,1);
        if(!uString::isDigits($this->col_id)) $this->uFunc->error(30,1);
    }
    //uEvents
    private function attach_uEvents_calendar() {
        //check if this type_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id
            FROM
            u235_events_types
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $this->el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40,1);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}

        $el_pos=$this->uPage->define_new_el_pos($this->col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach events list to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'uEvents_calendar',$this->col_id,$this->el_id);
    }
    private function attach_uEvents_dates() {
        //check if this element_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_id
            FROM
            u235_events_list
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60,1);
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/,1);}

        $el_pos=$this->uPage->define_new_el_pos($this->col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach events list to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'uEvents_dates',$this->col_id,$this->el_id);
    }

    private function attach_el_template($site_id=site_id) {
        if(!isset($_POST["el_template_id"])) $this->uFunc->error(80,1);
        $el_template_id=(int)$_POST["el_template_id"];

        if(!$el_template_data=$this->uPage->el_template_id2data($el_template_id,"page_id,site_id")) $this->uFunc->error(85,1);
        $page_id=(int)$el_template_data->page_id;
        $src_site_id=(int)$el_template_data->site_id;
        if(!$source_cols_el=$this->uPage->get_first_cols_el_of_page($page_id,"u235_cols_els.*",$src_site_id))  $this->uFunc->error(90,1);

        $page_data=$this->uPage->page_id2data($page_id,"page_title,text_folder_id");
        $page_data=(array)$page_data;
        $page_data["page_id"]=$page_id;
        $page_data["text_folder_id"]=$this->uPage->define_text_folder_id($page_id,$page_data["page_title"],$page_data["text_folder_id"]);

        $el_pos=$el_pos=$this->uPage->define_new_el_pos($this->col_id);
        $cols_els_id=$this->uPage->copy_cols_el($page_data,$this->col_id,$source_cols_el,$src_site_id,$site_id);

        if(!$cols_els_data=$this->uPage->cols_els_id2data($cols_els_id,"el_id,el_type",$site_id)) $this->uFunc->error('100',1);

        $el_type=$cols_els_data->el_type;
        $el_id=$cols_els_data->el_id;

        $above_el_id=$this->uPage->get_above_el_id($el_pos,$this->col_id,$site_id);

        //Достаем все el с cols_els_id и el_pos, чтобы передать браузеру информацию об изменениях
        print '{
        "status":"done",';
        $stm=$this->uPage->get_all_col_els_of_col($this->col_id,"cols_els_id,el_pos",$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        while($el=$stm->fetch(PDO::FETCH_OBJ)) {
            print '"el_'.$el->cols_els_id.'":"'.$el->el_pos.'",';
        }

        $this->uPage->clear_cache($page_id);

        echo '
        "cols_els_id":"'.$cols_els_id.'",
        "col_id":"'.$this->col_id.'",
        "el_pos":"'.$el_pos.'",
        "el_type":"'.$el_type.'",
        "el_id":"'.$el_id.'",
        "el_type":"'.$el_type.'",
        "above_el_id":"'.$above_el_id.'",
        "template":"1"
        }';
        exit;
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','admin_attach_el2col_bg'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);
        $this->uCat=new common($this->uCore);


        $this->check_data();

        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        //pages
        if($_POST['el_type']=='el_template') {
            $this->attach_el_template();
        }
        elseif($_POST['el_type']=='art') {
            require_once "uPage/elements/art/common.php";
            $el=new art($this->uPage);
            $el->attach_text($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='banner') {
            require_once "uPage/elements/banner/common.php";
            $el_common=new banner($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='bootstrap_carousel') {
            require_once "uPage/elements/bootstrap_carousel/common.php";
            $el_common=new bootstrap_carousel($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='card') {
            require_once "uPage/elements/card/common.php";
            $el_common=new card($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='code') {
            require_once "uPage/elements/code/common.php";
            $el_common=new code($this->uPage);
            echo $el_common->attach_el2col($this->col_id,$this->el_id);
            exit;
        }
        elseif($_POST['el_type']=='flip_book') {
            require_once "uPage/elements/flip_book/common.php";
            $el_common=new flip_book($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='form') {
            require_once "uPage/elements/form/common.php";
            $el_common=new form($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='gallery') {
            require_once "uPage/elements/gallery/common.php";
            $el_common=new gallery($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='gmap') {
            require_once "uPage/elements/gmap/common.php";
            $el_common=new gmap($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='login_btn') {
            require_once "uPage/elements/login_btn/common.php";
            $el_common=new login_btn($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='menu') {
            require_once "uPage/elements/menu/common.php";
            $el_common=new menu($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='owl_carousel') {
            require_once "uPage/elements/owl_carousel/common.php";
            $el_common=new owl_carousel($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='page_filter') {
            require_once "uPage/elements/page_filter/common.php";
            $el_common=new page_filter($this->uPage);
            $el_common->attach_el2col($this->col_id);
        }
        elseif($_POST['el_type']=='rubrics_arts') {
            require_once "uPage/elements/rubrics_arts/common.php";
            $el_common=new rubrics_arts($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='rubrics_arts_column') {
            require_once "uPage/elements/rubrics_arts_column/common.php";
            $el=new urubrics_arts_column($this->uPage);
            $el->attach_el($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='rubrics_tiles') {
            require_once "uPage/elements/rubrics_tiles/common.php";
            $el=new urubrics_tiles($this->uPage);
            $el->attach_el($this->el_id,$this->col_id);
        }
        elseif($_POST['el_type']=='search') {
            require_once "uPage/elements/search/common.php";
            $el_common=new search($this->uPage);
            $el_common->attach_el2cat($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='share') {
            require_once "uPage/elements/share/common.php";
            $el_common=new share($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='spacer') {
            require_once "uPage/elements/spacer/common.php";
            $el_common=new spacer($this->uPage);
            $el_common->attach_spacer($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='tabs') {
            require_once "uPage/elements/tabs/common.php";
            $el_common=new tabs($this->uPage);
            $el_common->attach_el2col($this->col_id);
        }
        elseif($_POST['el_type']=='ticker') {
            require_once "uPage/elements/ticker/common.php";
            $el_common=new ticker($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_latest') {
            require_once "uPage/elements/uCat_latest/common.php";
            $el_common=new uCat_latest($this->uPage);
            $el_common->attach_uCat_latest($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_latest_articles_slider') {
            require_once "uPage/elements/uCat_latest_articles_slider/common.php";
            $el_common=new uCat_latest_articles_slider($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_new_items') {
            require_once "uPage/elements/uCat_new_items/common.php";
            $el_common=new uCat_new_items($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_popular') {
            require_once "uPage/elements/uCat_popular/common.php";
            $el_common=new uCat_popular($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_sale') {
            require_once "uPage/elements/uCat_sale/common.php";
            $el_common=new uCat_sale($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_search') {
            require_once "uPage/elements/uCat_search/common.php";
            $el_common=new uCat_search($this->uPage);
            $el_common->attach_el2cat($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uCat')&&$_POST['el_type']=='uCat_sects') {
            require_once "uPage/elements/uCat_sects/common.php";
            $el_common=new uCat_sects($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($_POST['el_type']=='uEditor_texts_top') {
            require_once "uPage/elements/uEditor_texts_top/common.php";
            $el_common=new uEditor_texts_top($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        elseif($this->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_calendar') $this->attach_uEvents_calendar();
        elseif($this->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_dates') $this->attach_uEvents_dates();
        elseif($this->uFunc->mod_installed('uEvents')&&$_POST['el_type']=='uEvents_list') {
            require_once "uPage/elements/uEvents_list/common.php";
            $el_common=new uEvents_list($this->uPage);
            $el_common->attach_el2col($this->el_id,$this->col_id);
        }
        elseif($this->uFunc->mod_installed('uSubscr')&&$_POST['el_type']=='uSubscr_news_form') {
            require_once "uPage/elements/uSubscr_news_form/common.php";
            $el_common=new uSubscr_news_form($this->uPage);
            $el_common->attach_el2col($this->col_id,$this->el_id);
        }
        else $this->uFunc->error(110,1);
    }
}
new attach_el2col($this);