<?php
use processors\uFunc;
use uCat\common;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uPage/inc/common.php';
require_once 'uCat/classes/common.php';

class admin_load_element_cnt_bg {
    public $uFunc;
    public $uSes;
    public $uPage;
    public $uCat;
    private $uCore,$cols_els_id,$el_type,$el_id;
    private function check_data() {
        if(!isset($_POST['cols_els_id'])) $this->uFunc->error(10);
        $this->cols_els_id=$_POST['cols_els_id'];
        if(!uString::isDigits($this->cols_els_id)) $this->uFunc->error(20);
    }
    private function get_el_type() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            el_type,
            el_id
            FROM
            u235_cols_els
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $this->cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->el_type=$qr->el_type;
                $this->el_id=$qr->el_id;
            }
            else $this->uFunc->error(30);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    //uEvents
    private function load_uEvents_calendar_content() {
        $cache_dir="uEvents/cache/events/".site_id."/".$this->el_id;
        if(!file_exists($cache_dir."/events.js")||!file_exists($cache_dir."/calendar_widget.html")) {
            include_once "uEvents/events.php";

            $setup_uEvents=new \uEvents\events($this->uCore);
            $setup_uEvents->type_id=$this->el_id;
            $setup_uEvents->cache_dir=$cache_dir;

            if($setup_uEvents->check_data()) {
                $setup_uEvents->build_events_js_cache();
                $setup_uEvents->calendar_widget_cache($this->el_id,1);
            }
        }

        echo('{
        "status":"done",
        "cols_els_id":"'.$this->cols_els_id.'",
        "content":"'.rawurlencode(file_get_contents($cache_dir."/calendar_widget.html")).'"
        }');
    }
    private function load_uEvents_dates_content() {
        $cache_dir="uEvents/cache/event/".site_id."/".$this->el_id;
        if(!file_exists($cache_dir."/dates.html")) {
            include_once "uEvents/event.php";

            $setup_uEvent=new \uEvents\event($this->uCore);
            $setup_uEvent->event_id=$this->el_id;
            $setup_uEvent->cache_dir=$cache_dir;

            if($setup_uEvent->check_data()) $setup_uEvent->get_dates_cache();
        }

        echo('{
        "status":"done",
        "cols_els_id":"'.$this->cols_els_id.'",
        "content":"'.rawurlencode(file_get_contents($cache_dir."/dates.html")).'"
        }');
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);
        $this->uCat=new common($this->uCore);


        $this->check_data();
        $this->get_el_type();
        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        if($this->el_type=='art') {
            require_once "uPage/elements/art/common.php";
            $el=new \uPage\admin\art($this->uPage);
            $el->load_text_content($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='banner') {
            require_once "uPage/elements/banner/common.php";
            $el_common=new \uPage\admin\banner($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='bootstrap_carousel') {
            require_once "uPage/elements/bootstrap_carousel/common.php";
            $el_common=new \uPage\admin\bootstrap_carousel($this->uPage);
            $el_common->load_el_content($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='card') {
            require_once "uPage/elements/card/common.php";
            $el_common=new \uPage\admin\card($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='code') {
            require_once "uPage/elements/code/common.php";
            $el_common=new \uPage\admin\code($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='flip_book') {
            require_once "uPage/elements/flip_book/common.php";
            $el_common=new \uPage\admin\flip_book($this->uPage);
            $el_common->load_el_content($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='form') {
            require_once "uPage/elements/form/common.php";
            $el_common=new \uPage\admin\form($this->uPage);
            $el_common->load_el_content($this->el_id,$this->cols_els_id);
        }
        elseif($this->el_type=='gallery') {
            require_once "uPage/elements/gallery/common.php";
            $el_common=new \uPage\admin\gallery($this->uPage);
            $el_common->load_el_content($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='gmap') {
            require_once "uPage/elements/gmap/common.php";
            $el_common=new \uPage\admin\gmap($this->uPage);
            $el_common->load_el_content($this->el_id);
        }
        elseif($this->el_type=='login_btn') {
            require_once "uPage/elements/login_btn/common.php";
            $el_common=new \uPage\admin\login_btn($this->uPage);
            $el_common->load_el_content($this->el_id);
        }
        elseif($this->el_type=='menu') {
            require_once "uPage/elements/menu/common.php";
            $el_common=new \uPage\admin\menu($this->uPage);
            $el_common->load_el_content($this->el_id,$this->cols_els_id);
        }
        elseif($this->el_type=='owl_carousel') {
            require_once "uPage/elements/owl_carousel/common.php";
            $el_common=new \uPage\admin\owl_carousel($this->uPage);
            $el_common->load_el_content($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='page_filter') {
            require_once "uPage/elements/page_filter/common.php";
            $el=new \uPage\admin\page_filter($this->uPage);
            $el->load_el_content($this->cols_els_id,0);
        }
        elseif($this->el_type=='rubrics_arts') {
            require_once "uPage/elements/rubrics_arts/common.php";
            $el_common=new \uPage\admin\rubrics_arts($this->uPage);
            $el_common->load_el_content($this->el_id,$this->cols_els_id);
        }
        elseif($this->el_type=='rubrics_arts_column') {
            require_once "uPage/elements/rubrics_arts_column/common.php";
            $el=new \uPage\admin\urubrics_arts_column($this->uPage);
            $el->load_element_cnt($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='rubrics_tiles') {
            require_once "uPage/elements/rubrics_tiles/common.php";
            $el=new \uPage\admin\urubrics_tiles($this->uPage);
            $el->load_element_cnt($this->cols_els_id,$this->el_id);
        }
        elseif($this->el_type=='search') {
            require_once "uPage/elements/search/common.php";
            $el_common=new \uPage\admin\search($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='share') {
            require_once "uPage/elements/share/common.php";
            $el=new \uPage\admin\share($this->uPage);
            $el->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='spacer') {
            require_once "uPage/elements/spacer/common.php";
            $el=new \uPage\admin\spacer($this->uPage);
            $el->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='tabs') {
            require_once "uPage/elements/tabs/common.php";
            $el=new \uPage\admin\tabs($this->uPage);
            $el->load_el_content($this->cols_els_id,0);
        }
        elseif($this->el_type=='ticker') {
            require_once "uPage/elements/ticker/common.php";
            $el=new \uPage\admin\ticker($this->uPage);
            $el->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_latest') {
            require_once "uPage/elements/uCat_latest/common.php";
            $el_common=new \uPage\admin\uCat_latest($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_latest_articles_slider') {
            require_once "uPage/elements/uCat_latest_articles_slider/common.php";
            $el_common=new \uPage\admin\uCat_latest_articles_slider($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_new_items') {
            require_once "uPage/elements/uCat_new_items/common.php";
            $el_common=new \uPage\admin\uCat_new_items($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_popular') {
            require_once "uPage/elements/uCat_popular/common.php";
            $el_common=new \uPage\admin\uCat_popular($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_sale') {
            require_once "uPage/elements/uCat_sale/common.php";
            $el_common=new \uPage\admin\uCat_sale($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_search') {
            require_once "uPage/elements/uCat_search/common.php";
            $el_common=new \uPage\admin\uCat_search($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uCat_sects') {
            require_once "uPage/elements/uCat_sects/common.php";
            $el_common=new \uPage\admin\uCat_sects($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uEditor_texts_top') {
            require_once "uPage/elements/uEditor_texts_top/common.php";
            $el_common=new \uPage\admin\uEditor_texts_top($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        elseif($this->el_type=='uEvents_calendar') $this->load_uEvents_calendar_content();
        elseif($this->el_type=='uEvents_dates') $this->load_uEvents_dates_content();
        elseif($this->el_type=='uEvents_list') {
            require_once "uPage/elements/uEvents_list/common.php";
            $el_common=new \uPage\admin\uEvents_list($this->uPage);
            $el_common->load_el_content($this->el_id,$this->cols_els_id);
        }
        elseif($this->el_type=='uSubscr_news_form') {
            require_once "uPage/elements/uSubscr_news_form/common.php";
            $el_common=new \uPage\admin\uSubscr_news_form($this->uPage);
            $el_common->load_el_content($this->cols_els_id);
        }
        else exit;
    }
}
new admin_load_element_cnt_bg($this);