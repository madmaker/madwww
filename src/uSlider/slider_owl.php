<?php
namespace uSlider\admin;

use PDO;
use uString;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once "uSlider/inc/common.php";
require_once "uPage/inc/common.php";

class slider_owl {
    private $uFunc;
    public $uSlider;
    public $slider_data;
    private $uPage;
    private $uSes;
    private $uCore;
    public $slider_settings;
    public function text($str) {
        return $this->uCore->text(array('uSlider','slider_owl'),$str);
    }

    private function check_data($site_id=site_id) {
        if(!isset($_POST["cols_els_id"])) $this->uFunc->error(10,0);
        $cols_els_id=(int)$_POST["cols_els_id"];
        if(!$cols_els_data=$this->uPage->cols_els_id2data($cols_els_id,"el_id, el_type",$site_id)) $this->uFunc->error(20,0);
        if($cols_els_data->el_type!=="owl_carousel") $this->uFunc->error(30,0);
        if(!$slider_data=$this->get_slider((int)$cols_els_data->el_id)) $this->uFunc->error(40,0);

        return $slider_data;
    }
    private function get_slider($slider_id) {
        if(!$qr=$this->uSlider->slider_id2slider_info($slider_id,"slider_title,slider_type")) return 0;

        if($qr->slider_type!=="owl") return 0;

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

        $this->slider_settings=$this->uSlider->slider_id2slider_settings($this->slider_data->slider_id,"owl","
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
        ");
    }
}
$uSlider=new slider_owl($this); ?>

    <script type="text/javascript">
        if(typeof uSlider_owl==="undefined") {
            uSlider_owl = {};
        }


            uSlider_owl.slide_id=[];
            uSlider_owl.slide_pos=[];
            uSlider_owl.slide_html=[];
            uSlider_owl.slide_show=[];
            uSlider_owl.slide_id2index=[];

            uSlider_owl.file_id=[];
            uSlider_owl.file_name=[];
            uSlider_owl.file_mime=[];
            uSlider_owl.file_size=[];
            uSlider_owl.file_selected=[];
            uSlider_owl.file_show=[];

        uSlider_owl.slider_id=<?=$uSlider->slider_data->slider_id?>;
        uSlider_owl.slider_title=decodeURIComponent("<?=rawurlencode($uSlider->slider_data->slider_title)?>");

        uSlider_owl.items_number_xlg=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->items_number_xlg?>;
        uSlider_owl.items_number_lg =<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->items_number_lg ?>;
        uSlider_owl.items_number_md =<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->items_number_md ?>;
        uSlider_owl.items_number_sm =<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->items_number_sm ?>;
        uSlider_owl.items_number_xs =<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->items_number_xs ?>;

        uSlider_owl.nav_xlg=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->nav_xlg?>;
        uSlider_owl.nav_lg=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->nav_lg?>;
        uSlider_owl.nav_md=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->nav_md?>;
        uSlider_owl.nav_sm=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->nav_sm?>;
        uSlider_owl.nav_xs=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->nav_xs?>;

        uSlider_owl.dots_xlg=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_xlg?>;
        uSlider_owl.dots_lg=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_lg?>;
        uSlider_owl.dots_md=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_md?>;
        uSlider_owl.dots_sm=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_sm?>;
        uSlider_owl.dots_xs=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_xs?>;

        uSlider_owl.dots_style=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->dots_style?>;

        uSlider_owl.slideSpeed=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->slideSpeed?>;
        uSlider_owl.autoPlay=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->autoPlay?>;
        uSlider_owl.scrollPerPage=<?=/** @noinspection PhpUndefinedFieldInspection */$uSlider->slider_settings->scrollPerPage?>;

        <?
        $q_slides=$uSlider->uSlider->get_slides($uSlider->slider_data->slider_id,"slide_id,slide_html,slide_pos");
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$slide=$q_slides->fetch(PDO::FETCH_OBJ);$i++) {?>
        uSlider_owl.slide_id[<?=$i?>]=<?=$slide->slide_id?>;
        uSlider_owl.slide_pos[<?=$i?>]=<?=$slide->slide_pos?>;
        uSlider_owl.slide_html[<?=$i?>]=decodeURIComponent("<?=rawurlencode(uString::sql2text($slide->slide_html,true))?>");
        uSlider_owl.slide_show[<?=$i?>]=true;
        uSlider_owl.slide_id2index[<?=$slide->slide_id?>]=<?=$i?>;
        <?}?>
    </script>

    <h1><span id="uSlider_owl_slider_title_header"><?=$uSlider->slider_data->slider_title?></span><small> <button type="button" class="btn btn-default" onclick="uSlider_owl.change_title_init()"><span class="icon-pencil"></span> <?=$uSlider->text("Edit slider name - btn label")?></button></small></h1>

    <p class="clearfix"> </p>
    <div id="uSlider_owl_slides"><?=$uSlider->text("No slides created yet")?></div>

    <p class="clearfix"> </p>
    <button type="button" class="btn btn-success" onclick="uSlider_owl.add_slide();"><?=$uSlider->text("Add slide - btn")?></button>
    <button type="button" class="btn btn-default" onclick="uSlider_owl.init_settings_dg();"><?=$uSlider->text("Slider settings - btn")?></button>