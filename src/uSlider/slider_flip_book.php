<?php
namespace uSlider\admin;

use PDO;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uSlider/inc/common.php";
require_once "uPage/inc/common.php";

class slider_flip_book {
    private $uFunc;
    public $uSlider;
    public $slider_data;
    private $uPage;
    private $uSes;
    private $uCore;
    public $slider_settings;

    public function text($str) {
        return $this->uCore->text(array('uSlider','slider_flip_book'),$str);
    }

    private function check_data($site_id=site_id) {
        if(!isset($_POST["cols_els_id"])) $this->uFunc->error(10,0);
        $cols_els_id=(int)$_POST["cols_els_id"];
        if(!$cols_els_data=$this->uPage->cols_els_id2data($cols_els_id,"el_id, el_type",$site_id)) $this->uFunc->error(20,0);
        if($cols_els_data->el_type!=="flip_book") $this->uFunc->error(30,0);
        if(!$slider_data=$this->get_slider((int)$cols_els_data->el_id)) $this->uFunc->error(40,0);

        return $slider_data;
    }
    private function get_slider($slider_id) {
        if(!$qr=$this->uSlider->slider_id2slider_info($slider_id,"slider_title,slider_type")) return 0;

        if($qr->slider_type!=="flip_book") return 0;

        $slider_title=uString::sql2text($qr->slider_title,true);

        return (object)array(
            "slider_id"=>$slider_id,
            "slider_title"=>$slider_title
        );
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("forbidden");

        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSlider=new \uSlider\common($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);

        $this->slider_data=$this->check_data();

        $this->check_data();
        $this->slider_settings=$this->uSlider->slider_id2slider_settings($this->slider_data->slider_id,"flip_book","height");
    }
}
$uSlider=new slider_flip_book($this);?>

    <script type="text/javascript">
        if(typeof uSlider_flip_book==="undefined") {
            uSlider_flip_book={};

            uSlider_flip_book.slide_id=[];
            uSlider_flip_book.slide_pos=[];
            uSlider_flip_book.slide_html=[];
            uSlider_flip_book.img_timestamp=[];
            uSlider_flip_book.light_bg=[];
            uSlider_flip_book.full_width=[];
            uSlider_flip_book.centered=[];
            uSlider_flip_book.slide_id2index=[];
        }

        uSlider_flip_book.slider_id=<?=$uSlider->slider_data->slider_id?>;
        uSlider_flip_book.slider_title=decodeURIComponent("<?=rawurlencode($uSlider->slider_data->slider_title)?>");

        uSlider_flip_book.height=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->height?>;

        <?
        $q_slides=$uSlider->uSlider->get_slides($uSlider->slider_data->slider_id,"slide_id,slide_html,slide_pos,img_timestamp,light_bg,full_width,centered");;
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$slide=$q_slides->fetch(PDO::FETCH_OBJ);$i++) {?>
        uSlider_flip_book.slide_id[<?=$i?>]=<?=$slide->slide_id?>;
        uSlider_flip_book.slide_pos[<?=$i?>]=<?=$slide->slide_pos?>;
        uSlider_flip_book.slide_html[<?=$i?>]=decodeURIComponent("<?=rawurlencode(uString::sql2text($slide->slide_html,true))?>");
        uSlider_flip_book.img_timestamp[<?=$i?>]=<?=$slide->img_timestamp?>;
        uSlider_flip_book.light_bg[<?=$i?>]=<?=$slide->light_bg?>;
        uSlider_flip_book.full_width[<?=$i?>]=<?=$slide->full_width?>;
        uSlider_flip_book.centered[<?=$i?>]=<?=$slide->centered?>;
        uSlider_flip_book.slide_id2index[<?=$slide->slide_id?>]=<?=$i?>;
        <?}?>
    </script>

<h1><span id="uSlider_flip_book_slider_title_header"><?=$uSlider->slider_data->slider_title?></span><small> <button type="button" class="btn btn-default" onclick="uSlider_flip_book.change_title_init()"><span class="icon-pencil"></span> <?=$uSlider->text("Edit slider name - btn label")?></button></small></h1>

    <div id="uSlider_flip_book_slides"><?=$uSlider->text("No slides created yet"/*Пока нет ни одного слайда*/)?></div>

    <button type="button" class="btn btn-success" onclick="uSlider_flip_book.add_slide();"><span class="icon-plus"></span> <?=$uSlider->text("Add slide - btn"/*Добавить слайд*/)?></button>
    <button type="button" class="btn btn-default" onclick="uSlider_flip_book.init_settings_dg();"><?=$uSlider->text("Slider settings - btn"/*Настройки слайдера*/)?></button>

    <div id="uSlider_flip_book_preview_container"></div>

<div class="bs-callout bs-callout-primary"><h3><?=$uSlider->text("Attention - header"/*Внимание!*/)?></h3><p><?=$uSlider->text("Make not less than 8 slides"/*Для корректной работы слайдера, нужно создать не менее 8 слайдов!*/)?></p></div>