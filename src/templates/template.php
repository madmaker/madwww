<?php
$translator_1588598067=new \translator\translator(site_lang,'templates/template.php');
if(!isset($uFunc)) {
    require_once "processors/classes/uFunc.php";
    $uFunc=new \processors\uFunc($this);
}
if(!isset($uSes)) {
    require_once "processors/uSes.php";
    $uSes=new uSes($this);
}

require_once "processors/uString.php";
require_once "processors/classes/uFunc.php";
require_once "uPage/inc/common.php";
require_once "uPage/inc/setup_uPage_page.php";

if(!isset($uPage_common)) $uPage_common=new uPage\common($this);

//curl -X POST -H 'Content-type: application/json' --data '{"text":"Hello, World!"}' https://hooks.slack.com/services/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


Header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //Дата в прошлом
Header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
Header("Pragma: no-cache"); // HTTP/1.1
Header('Content-type: text/html; charset=utf-8');
Header("Last-Modified: ".gmdate("D, d M Y H:i:s")."GMT");
//Тип html-файла. Заголовок html?>
<!DOCTYPE html><html>
<!--[if lt IE 7 ]> <html class='ie6' lang='ru'> <![endif]-->
<!--[if IE 7 ]> <html class='ie7' lang='ru'> <![endif]-->
<!--[if IE 8 ]> <html class='ie8' lang='ru'> <![endif]-->
<!--[if IE 9 ]> <html class='ie9' lang='ru'> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang='ru'> <!--<![endif]-->
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <base href="<?=u_sroot;?>" />


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="<?=u_sroot?>js/html5shiv/http_oss.maxcdn.com_html5shiv_3.7.2_html5shiv.js"></script>
    <script src="<?=u_sroot?>js/respond/http_oss.maxcdn.com_respond_1.4.2_respond.min.js"></script>
    <![endif]-->

    <?if($uFunc->mod_installed("uCat")) {
        if(isset($uCat)&&$this->page_name=="items"&&$this->mod=="uCat") {
            $uCat_common = &$uCat->uCat_common;
        }
        else {
            require_once 'uCat/classes/common.php';
            $uCat_common = new \uCat\common($this);
            $uCat=&$uCat_common;
        }
    }?>

    <? if(isset($this->page['meta_keywords'])) {
        if(!empty($this->page['meta_keywords'])) {?>
            <meta name="keywords" content="<?=str_replace('"','',strip_tags(uString::sql2text($this->page['meta_keywords'],1)))?>" />
        <?}
        else {?>
            <meta name="keywords" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_keywords','content',true)))?>" />
        <?}
    }
    elseif(isset($this->page['page_keywords'])) {
        if(!empty($this->page['page_keywords'])) {?>
            <meta name="keywords" content="<?=$this->page['page_keywords']?>" />
        <?}
        else {?>
            <meta name="keywords" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_keywords','content',true)))?>" />
        <?}
    }
    else {?>
        <meta name="keywords" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_keywords','content',true)))?>" />
    <?}
    if(isset($this->page['meta_description'])) {
        if(!empty($this->page['meta_description'])) {?>
            <meta name="description" content="<?=str_replace('"','',strip_tags(uString::sql2text($this->page['meta_description'],1)))?>" />
        <?}
        else {?>
            <meta name="description" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_description','content',true)))?>" />
        <?}
    }
    elseif(isset($this->page['page_description'])) {
        if(!empty($this->page['page_description'])) {?>
            <meta name="description" content="<?=$this->page['page_description']?>" />
        <?}
        else {?>
            <meta name="description" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_description','content',true)))?>" />
        <?}
    }
    else {?>
        <meta name="description" content="<?=strip_tags(str_replace('"','',$uFunc->getConf('site_description','content',true)))?>" />
    <?}?>

    <title><?
        if($this->page['page_title']!=='') {
            if(!$this->is_homepage) echo htmlspecialchars(strip_tags(uString::sql2text($this->page['page_title'],1))).'. ';
        }
        echo site_name?></title>
    <?$favicon_ico=$uFunc->getConf('favicon_url','content',1);
    if($favicon_ico) {?>
    <link rel="shortcut icon" href="<?=$favicon_ico?>" type="image/x-icon">
    <link rel="icon" href="<?=$favicon_ico?>" type="image/x-icon">
    <?}?>

    <?
    //DIN Pro normal
    if(site_id==2||site_id==4||site_id==6||site_id==13||site_id==18||site_id==35||site_id==36||site_id==37||site_id==39||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==45||site_id==46||site_id==47||site_id==48||site_id==49||site_id==50||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56||site_id==58||site_id==59||site_id==61||site_id==62||site_id==63||site_id==64||site_id==69) $uFunc->incCss(u_sroot.'fonts/DINPro/DIN Pro normal.min.css');
    //DIN PRO LIGHT
    if(site_id==4||site_id==13||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==47||site_id==69) $uFunc->incCss('/fonts/DINPro/DIN Pro Light.min.css');
    //DIN PRO Cond
    if(site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46) $uFunc->incCss('/fonts/DINPro/DIN Pro Cond.min.css');
    //DIN PRO Black
    if(site_id==13||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==59||site_id==69) $uFunc->incCss('/fonts/DINPro/DIN Pro Black.min.css');
    //DIN PRO MEDIUM
    if(site_id==2||site_id==6||site_id==13||site_id==18||site_id==35||site_id==36||site_id==37||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==47||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56||site_id==58||site_id==59||site_id==62||site_id==63||site_id==64||site_id==69) $uFunc->incCss('/fonts/DINPro/DIN Pro Medium.min.css');
    //DIN PRO BOLD
    if(site_id==13||site_id==35||site_id==36||site_id==37||site_id==39||site_id==40||site_id==41||site_id==42||site_id==43||site_id==44||site_id==46||site_id==51||site_id==52||site_id==53||site_id==54||site_id==55||site_id==56||site_id==59||site_id==64||site_id==69) $uFunc->incCss('/fonts/DINPro/DIN Pro Bold.min.css');
    //Cormorant
    if(site_id==50) {?><link href="https://fonts.googleapis.com/css?family=Cormorant:400,700i&amp;subset=cyrillic,cyrillic-ext" rel="stylesheet"><?}
    //Roboto
    if(site_id==61) {?><link href="https://fonts.googleapis.com/css?family=Roboto:400,400i,700,700i&display=swap&subset=cyrillic" rel="stylesheet"><?}
    //ARIMO
    if(site_id==3||site_id==8) $uFunc->incCss('/fonts/Arimo/stylesheet.min.css');
    //FUTURA LIGHT
    if(site_id==6||site_id==18) $uFunc->incCss('/fonts/Futura/Futura-Light-Normal.min.css');
    //BookmanOldStyle
    if(site_id==54) $uFunc->incCss('/fonts/BookmanOldStyle/stylesheet.min.css');
    //Century Gothic
    if(site_id==12) $uFunc->incCss('/fonts/Century Gothic/stylesheet.min.css');
    //Rupster Script
    if(site_id==12) $uFunc->incCss('/fonts/Rupster Script/stylesheet.min.css');
    //Rupster Script
    if(site_id==17||site_id==31) $uFunc->incCss('/fonts/DIN/stylesheet.min.css');
    //ChinaCyr
    if(site_id==24) $uFunc->incCss('/fonts/ChinaCyr/stylesheet.min.css');
    //Raleway-Thin
    if(site_id==33||site_id==34||site_id==60||site_id==61||site_id==65) $uFunc->incCss('/fonts/Raleway/Raleway-Thin.min.css');
    //Raleway-Light
    if(site_id==33||site_id==34||site_id==60||site_id==61||site_id==65) $uFunc->incCss('/fonts/Raleway/Raleway-Light.min.css');
    //Raleway-Bold
    if(site_id==33||site_id==34||site_id==60||site_id==61||site_id==65) $uFunc->incCss('/fonts/Raleway/Raleway-Bold.min.css');
    //Raleway-Regular
    if(site_id==33||site_id==34||site_id==55||site_id==60||site_id==61||site_id==65) $uFunc->incCss('/fonts/Raleway/Raleway-Regular.min.css');
    //Raleway-Medium
    if(site_id==33||site_id==34||site_id==55||site_id==60||site_id==61||site_id==65) $uFunc->incCss('/fonts/Raleway/Raleway-Medium.min.css');
    //Raleway-Black
    if(site_id==33||site_id==34||site_id==60||site_id==61) $uFunc->incCss('/fonts/Raleway/Raleway-Black.min.css');
    //GillSans
    if(site_id==36||site_id==46) $uFunc->incCss('/fonts/GillSans/stylesheet.min.css');
    //LifeIsStrangeRU
    if(site_id==44) $uFunc->incCss('/fonts/LifeIsStrangeRU/stylesheet.min.css');

    //Fontello
    $uFunc->incCss(u_sroot.'fonts/fontello/css/fontello.min.css');
    $uFunc->incCss(u_sroot.'fonts/fontello/css/fontello-ie7.min.css');
    //jQuery
    $uFunc->incCss(u_sroot.'js/jquery/jquery-ui.min.css');

    //Bootstrap
    $uFunc->incCss(u_sroot.'js/bootstrap/css/bootstrap.min.css');

    //Bootstrap-switch
    $uFunc->incCss(staticcontent_url.'node_modules/bootstrap-switch/dist/css/bootstrap3/bootstrap-switch.min.css');

    //Bootstrap-tockenfield
    $uFunc->incCss(u_sroot.'js/bootstrap_plugins/tockenfield/css/bootstrap-tokenfield.min.css');

    //Bootstrap-calendar
    $uFunc->incCss(staticcontent_url.'js/lib/bootstrap_plugins/bootstrap-calendar/css/calendar.min.css');
    $uFunc->incCss(staticcontent_url. 'js/lib/bootstrap_plugins/bootstrap-calendar/css/small.min.css');

    //plupload
    $uFunc->incCss(u_sroot."js/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.min.css");

    //pnotify
    $uFunc->incCss(u_sroot.'js/pnotify/pnotify.custom.min.css');

    //fancybox
    $uFunc->incCss(u_sroot.'js/fancybox/jquery.fancybox.min.css');

    //datatable
    $uFunc->incCss(u_sroot.'/js/DataTables/DataTables-1.10.16/css/dataTables.bootstrap.min.css');
    $uFunc->incCss(u_sroot.'/js/DataTables/buttons/buttons.bootstrap.min.css');

    //owl-carousel
    $uFunc->incCss(u_sroot.'js/OwlCarousel2/dist/assets/owl.carousel.min.css');
    $uFunc->incCss(u_sroot.'js/OwlCarousel2/dist/assets/owl.theme.default.min.css');

    //common css
    $uFunc->incCss(staticcontent_url.'css/lib/u235/common.min.css');

    //MADWWWW SCRIPTS

    //u235_common
    $uFunc->incCss(u_sroot.'css/u235/u235_common.min.css');
    //uNavi
    $uFunc->incCss(u_sroot.'uNavi/css/uNavi.min.css');

    //uAuth
    $uFunc->incCss(u_sroot.'uAuth/css/uAuth.min.css');

    //uForms
    $uFunc->incCss(u_sroot.'uForms/css/uForms.min.css');

    //uLeadGen
    if(!$uSes->access(2)) {
        $uFunc->incCss(u_sroot . 'uLeadGen/css/callback.min.css');
    }

    if($this->uSes->access(2)) {
        $uFunc->incCss(u_sroot.'css/u235/u235_panel.min.css');
    }

    //uCat
    if($uFunc->mod_installed("uCat")) {
        $uFunc->incCss(u_sroot.'uCat/css/common.min.css');
        $uFunc->incCss(u_sroot.'uCat/css/cart.min.css');
        $this->uFunc->incCss('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
    }

    //uSupport
    if($uFunc->mod_installed("uSup")) {
        $uFunc->incCss(u_sroot.'uSupport/css/common.min.css');
    }

    //uEvents
    if($uFunc->mod_installed("uEvents")) {
        $uFunc->incCss(u_sroot . "uEvents/css/common.min.css");
    }

    //uSlider
    $uFunc->incCss(u_sroot.'uSlider/css/common.min.css');


    //Accessibility
    $this->uFunc->incCss("accessibility/css/vision.min.css");

    $uFunc->incCss($uPage_common->get_site_css_file());



    ?>
    <script type="text/javascript">
        <?if(!isset($_SESSION['SESSION']['timezone_difference'])){?>
        var u_timezone_difference='na';
        <?} else {?>
        var u_timezone_difference=<?=$_SESSION['SESSION']['timezone_difference']?>;
        <?}?>
        const u_sroot="<?=u_sroot?>";
        const staticcontent_url="<?=staticcontent_url?>";
        const v_timestamp="<?=v_timestamp?>";
        //const urlbox_api_key="<?//=urlbox_api_key?>//";
        const u_protocol="<?=u_protocol?>";
        const site_id="<?=site_id?>";
        var u_mod="<?=$this->page['page_mod']?>";
        var u_page="<?=$this->page['page_name']?>";
        <?if($this->uSes->access(7)) {?>
        var u_page_id=<?=$this->page['page_id']?>;
        <?}?>
        var u_uAuth_captcha=<?=$this->uSes->get_val("captcha_needed")?>;
        const u_lang=site_lang="<?=site_lang?>";
    </script>
    <?

    echo $uFunc->returnCss(0);
    echo $uFunc->returnCss(1);


    //jQuery
    echo $uFunc->printJs(u_sroot.'js/jquery/jquery-3.2.0.min.js');

    if(isset($this->page["head_html"])) echo $this->page["head_html"];
    echo $uFunc->getConf("head_beginning_ins","content");
    echo $uFunc->getConf("head_end_ins","content");?>
</head>



<body class="<?=$this->mod=="uCat"?"uCat_page":""?> u235_read_mode">
<?=$uFunc->getConf("body_beginning_ins","content");?>

<div id="uScrollTop" style="display: none;"><button class="btn btn-default" onclick="window.scrollTo(0, 0);"><span class="glyphicon glyphicon-chevron-up"></span></button></div>

<?php
if($this->mod==='obooking'&&$this->page_name!=='calendar_demo') {
    if($uFunc->mod_installed("obooking")&&$this->uSes->access(2)) {
        require_once "obooking/inc/sidebar.php";
        $obooking_sidebar=new \obooking\sidebar($this);
        $obooking_sidebar->draw_panel();
    }
}
else {
    include "templates/uranium_menu.php";
}?>
<div class="main">

    <?if($uFunc->mod_installed("uCat")) {
    if($uCat_navbar_top_page_id=(int)$uFunc->getConf("uCat_navbar_top_page_id","uCat")) {?>
        <nav class="navbar navbar-default navbar-static-top uCat_navbar_top" style="font-size:0.9em;">
            <?php
            $page=$this->page;//Это нужно из-за того, что ниже будет перезатерно значение $this->page
            if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
            if(uString::isDigits($uCat_navbar_top_page_id)) {
                if($this->uPage->page_id2data($uCat_navbar_top_page_id)) {
                    $this->page['show_title']=0;
                    $header=new \uPage_setup_uPage_page($this,$uCat_navbar_top_page_id);

                    $header->cache_builder();
                }
            }
            $this->page=$page;//Это нужно из-за того, что выше было перезатерно значение $this->page
            ?>
        </nav>
    <?}
    }?>
    <div id="u_progressbar" class="bg-success loading"><div id="u_progressbar_content">&nbsp;</div></div>
    <div id="u_errorbar" class="bg-danger"><div id="u_errorbar_content">&nbsp;</div></div>


    <?php
    $page=$this->page;//Это нужно из-за того, что ниже будет перезатерно значение $this->page
    if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
    if($this->is_homepage) $header_page_id=(int)$uFunc->getConf("header_page_id","content");
    else $header_page_id=(int)$uFunc->getConf("header_page_id_others","content");
    if($header_page_id) {
        if($page_data=$this->uPage->page_id2data($header_page_id,'show_title,page_width')) {
                $this->page['show_title']=(int)$page_data->show_title;
                $this->page['page_width']=(int)$page_data->page_width;
            $header=new \uPage_setup_uPage_page($this,$header_page_id);
        }
    }
    if(isset($page_data)) {
        if(!(site_id==31&&@$page->page_id==147)) {//Временно. Для финской страницы merger. Надо было от шапки и подвала избавиться
            if ($page_data) { ?>
                <header class="container<?= $this->page['page_width'] ? '-fluid' : '' ?>  width_<?= $this->page['page_width'] ?>"><?
                    isset($header) ? $header->cache_builder() : false ?></header>
            <?
            }
        }
    }?>

    <?$this->page=$page//Это нужно из-за того, что выше было перезатерно значение $this->page?>

    <div class="<?
    if(
            $this->mod=='uPage'||
            $this->mod=='obooking'||
            $this->mod=='configurator'
    ) {
        if(@(int)$this->page['page_width']) echo ' container-fluid ';
        else echo ' container ';
    }
    else echo ' container ';
    if($this->mod=='uPage'||$this->mod=='page'/*||($this->page_name=='index')*/) {
        echo ' static_text ';
    }
    ?> main_container" id="main_container">
        <div class="page_body row">
            <?
            if($uFunc->show_left_bar()) {echo "";
            ?>
                <div class="leftBar col-md-3">
                    <?//uCat
                    if($uFunc->mod_installed("uCat")) {
                        if(!isset($uCat)) {
                            $uCat=new stdClass();
                        }
                        if(!isset($uCat->uCat)) {
                            require_once "uCat/classes/common.php";
                            $uCat->uCat=new \uCat\common($this);
                        }
                        /*Левое меню каталога*/
                        $uCat->uCat->left_menu($uCat,$uFunc->show_uCat_left_menu);
                        /*Фильтр по товарам каталога*/
                        $uCat->uCat->filter_bar($uCat);
                    }

                    /*Временная специфичная для MAX GG хрень в левом меню*/?>
                    <?if(site_id==41) {
                        $page=$this->page;//Это нужно из-за того, что ниже будет перезатерно значение $this->page
                        if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
                            if($page_data=$this->uPage->page_id2data($header_page_id,'show_title,page_width')) {
                                    $this->page['show_title']=(int)$page_data->show_title;
                                    $this->page['page_width']=(int)$page_data->page_width;
                                $header=new \uPage_setup_uPage_page($this,16);
                            }
                        if(isset($page_data)) {
                            if($page_data) {?>
                                <?isset($header)?$header->cache_builder():false?>
                            <?}
                        }?>

                        <?$this->page=$page//Это нужно из-за того, что выше было перезатерно значение $this->page?>
                    <?}?>
                </div>
            <?}?>
            <div class="<?=$this->mod?><?=$this->page_name?>midBar midBar col-md-<?
            if($uFunc->show_left_bar()&&$uFunc->show_right_bar()) echo '7';
            elseif($uFunc->show_left_bar()&&!$uFunc->show_right_bar()) echo '9';
            elseif(!$uFunc->show_left_bar()&&$uFunc->show_right_bar()) echo '10';
            else echo '12';
            ?>">
                <?if(
                !$this->is_homepage&&
                !(
                    (
                        $uFunc->getConf("mainpage_page_id","content")==$this->page['page_id']||
                        $uFunc->getConf("header_page_id","content")==$this->page['page_id']||
                        $uFunc->getConf("header_page_id_others","content")==$this->page['page_id']||
                        $uFunc->getConf("footer_page_id","content")==$this->page['page_id']||
                        $uFunc->getConf("footer_page_id_others","content")==$this->page['page_id']
                    ) &&
                    $this->mod=='uPage'
                )) {?>
                <div class="breads uPage_row_content <?if(@(int)$this->page['page_width']){?>uPage_row_content_centered<?}?>" id="u235_site_breadcrumbs"><?$this->uBc->insert()?></div>
                <?}?>

                <?if($this->mod=='page') {
                    echo '<h1 class="page-header" '.($this->page['page_show_title_in_content']?'':'style="display:none"').'>'.uString::sql2text($this->page['page_title'],true).'</h1>';?>
                    <div class="pull-right nav nav-stacked right_menu"><?=$this->uMenu->insert(2)?></div>
                <?}?>
                <?=$this->page_content;?>
                <?if(($this->mod=='page'||$this->mod=='uPage')&&(int)$uFunc->getConf("show_views_counter_on_page","content")) {
                    if ($this->page['views_counter'] > 0) { ?>
                        <div id="page-views_counter" class="text-muted pull-right"><span class="icon-eye"></span> <?=$this->page['views_counter'] ?></div>
                        <?
                    }
                }?>
            </div>
            <?if(!empty($right_col)){?>
                <div class="col-md-4 right_menu right_menu_col"><?=$right_col?></div>
            <?}?>
        </div>
    </div> <!-- /container -->

    <?if(!isset($this->uPage)) $this->uPage=new uPage\common($this);
    $page=$this->page;//Это нужно из-за того, что ниже будет перезатерно значение $this->page
    if($this->is_homepage) $footer_page_id=(int)$uFunc->getConf("footer_page_id","content");
    else $footer_page_id=(int)$uFunc->getConf("footer_page_id_others","content");

    if($page["page_id"]!=$footer_page_id) {
        if ($footer_page_id) {
            if ($page_data = $this->uPage->page_id2data($footer_page_id, 'show_title,page_width')) {
                $this->page['show_title'] = (int)$page_data->show_title;
                $this->page['page_width'] = (int)$page_data->page_width;
                $footer = new \uPage_setup_uPage_page($this, $footer_page_id);
            }
        }
        if (isset($page_data)) {
            if ($page_data) {
                if (!(site_id == 31 && @$page->page_id == 147)) {//Временно. Для финской страницы merger. Надо было от шапки и подвала избавиться?>
                    <footer class="container<?= $this->page['page_width'] ? '-fluid' : '' ?> width_<?= $this->page['page_width'] ?>"><?
                        isset($footer) ? $footer->cache_builder() : false; ?>
                        <?
                        $this->page = $page//Это нужно из-за того, что выше было перезатерно значение $this->page?>
                        <?
                        if ($this->uInt->lang === "ru_RU") { ?>
                            <div class="madwww_sign"><a href="https://madwww.ru" target="_blank">Разработка и
                                    продвижение
                                    сайта веб-студия MAD MAKERS</a></div><?
                        } else { ?>
                            <div class="madwww_sign"><a href="https://madwww.ru" target="_blank">Web site development
                                    and
                                    SEO are made by MAD MAKERS</a></div><?
                        } ?>
                    </footer>
                    <?
                }
            }
        }
    }

    echo $uFunc->getConf("body_end_ins","content");

    //RECAPTCHA
    //        echo $this->uFunc->printJs("https://www.google.com/recaptcha/api.js");//touch events

    /** @noinspection PhpUndefinedMethodInspection */
    $this->uInt_js('templates','common');

    //Modernizr
    $uFunc->incJs(u_sroot.'js/Modernizr/modernizr-custom.min.js');//touch events

    //jQuery
    $uFunc->incJs(u_sroot.'js/jquery/jquery-ui.min.js');

    //Bootstrap
    $uFunc->incJs(u_sroot.'js/bootstrap/js/bootstrap.min.js');

    //Bootstrap-switch
    $uFunc->incJs(staticcontent_url.'node_modules/bootstrap-switch/dist/js/bootstrap-switch.min.js');

    //Bootstrap-tockenfield
    $uFunc->incJs(u_sroot.'js/bootstrap_plugins/tockenfield/bootstrap-tokenfield.min.js');

    //Bootstrap-calendar
    if($this->uInt->lang==="ru_RU") $uFunc->incJs("js/bootstrap_plugins/bootstrap-calendar/js/language/ru-RU.min.js");
    $uFunc->incJs("js/bootstrap_plugins/bootstrap-calendar/js/language/template.min.js");

    $uFunc->incJs(staticcontent_url. 'js/lib/bootstrap_plugins/bootstrap-calendar/js/calendar.min.js');

    //underscore
    $uFunc->incJs("js/underscore/underscore.min.js");

    //moment js
    $uFunc->incJs("js/moment/moment.min.js");

    //dropdown hover
    if(site_id!=7&&site_id!=8) $uFunc->incJs(u_sroot.'js/bootstrap_plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js');

    //Numeral-js
    $uFunc->incJs(u_sroot.'js/Numeral-js/numeral.min.js');

    //inputmask
    $uFunc->incJs(u_sroot."js/jquery.inputmask/js/inputmask.min.js");
    $uFunc->incJs(u_sroot."js/jquery.inputmask/js/jquery.inputmask.min.js");
    $uFunc->incJs(u_sroot."js/jquery.inputmask/js/inputmask.extensions.min.js");

    //plupload
    $uFunc->incJs(u_sroot."js/plupload/js/plupload.full.min.js");
    $uFunc->incJs(u_sroot."js/plupload/js/i18n/".$this->uInt->text(array('processors', 'uFunc'),"Plupload lang").".min.js");
    $uFunc->incJs(u_sroot."js/plupload/js/jquery.plupload.dropbox/jquery.plupload.queue.min.js");

    //pnotify
    $uFunc->incJs(u_sroot.'js/pnotify/pnotify.custom.min.js');
    $uFunc->incJs(u_sroot.'js/pnotify/pnotify.functions.min.js');

    //phpjs
    $uFunc->incJs(u_sroot.'js/phpjs/functions/strings/explode.min.js');
    $uFunc->incJs(u_sroot.'js/phpjs/functions/strings/str_ireplace.min.js');

    //fancybox
    $uFunc->incJs(u_sroot.'js/fancybox/jquery.fancybox.pack.js');

    //placeholder support for ie
    $uFunc->incJs(u_sroot.'js/jquery_plugins/jquery-placeholder/jquery.placeholder.min.js');

    //owl-carousel
    $uFunc->incJs(u_sroot.'js/OwlCarousel2/dist/owl.carousel.min.js');

    //MADWWWW SCRIPTS

    //u235_common
    $uFunc->incJs(staticcontent_url.'js/lib/u235/common.min.js');
    $uFunc->incJs(u_sroot.'js/u235/uString.min.js');
    $uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.min.js');
    $uFunc->incJs(staticcontent_url.'js/lib/u235/common_old.min.js');
    echo $this->uInt_print_js("js","u235_admin_menu");
    //uNavi
    if($this->uSes->access(7)) {//uNavi in place
        $uFunc->incJs(u_sroot.'uNavi/js/eip.min.js');
        /** @noinspection PhpUndefinedMethodInspection */
        echo $this->uInt_print_js("uNavi","eip");
    }

    //uForms
    $uFunc->incJs(u_sroot."uForms/js/form.min.js");

    //uLeadGen
    if(!$uSes->access(2)&&(int)$uFunc->getConf("show_callback_btn","content")) {
        $uFunc->incJs(u_sroot . "uLeadGen/js/callback.min.js");
    }

    if($this->uSes->access(12)) {//u235_admin_menu
        $uFunc->incJs(u_sroot.'js/u235/u235_admin_menu.min.js');
    }

    //uCat
    if($uFunc->mod_installed("uCat")) {
        //velocity
        $uFunc->incJs(u_sroot.'js/velocity/velocity.min.js');
        $uFunc->incJs(u_sroot.'js/velocity/velocity.ui.min.js');

        //js-cookie
        $uFunc->incJs(u_sroot.'js/js-cookie/js.cookie.min.js');
        //uCat cart
        $uFunc->incJs(u_sroot.'uCat/js/uCat_cart.js');
    }

    //uEvents
    if($uFunc->mod_installed("uEvents")) {
        /** @noinspection PhpUndefinedMethodInspection */
        echo $this->uInt_print_js("uEvents","events_common");
        $uFunc->incJs(u_sroot . "uEvents/js/events_common.min.js");
    }

    //tinymce vars
    /** @noinspection PhpUndefinedMethodInspection */
    echo $this->uInt_print_js("js","tinymce_vars");
    $uFunc->incJs(u_sroot . 'js/u235/tinymce_vars.min.js');

    if($this->uSes->access(11)) {
        print $uFunc->insAuthDialog();
    }

    //CREATOR SCRIPTS
    if($this->uSes->access(7)) {/*uPage and uEditor*/
        $this->uFunc_new->incJs(u_sroot.'uEditor/js/admin_common.min.js');
        $this->uFunc_new->incJs(u_sroot.'uPage/js/admin_common.min.js');
        $this->uFunc_new->incJs(u_sroot.'uNavi/js/admin_common.min.js');
        $this->uFunc_new->incJs(u_sroot.'uForms/js/admin_common.min.js');
        $this->uFunc_new->incJs(u_sroot.'uSlider/js/inline_create.min.js');

        echo $this->uInt_print_js('uEditor','admin_common');
        echo $this->uInt_print_js('uPage','admin_common');
        echo $this->uInt_print_js('uNavi','admin_common');
        echo $this->uInt_print_js('uForms','admin_common');
        echo $this->uInt_print_js('uSlider','inline_create');
    }

    /*uCat*/
    if($this->uFunc->mod_installed('uCat')&&$this->uSes->access(25)) {
        $this->uFunc_new->incJs(u_sroot.'uCat/js/admin_common.min.js');
        //bootstrap-touchspin
        $this->uFunc->incJs(staticcontent_url.'js/lib/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js');
    }
    //uSup
    if($this->uFunc->mod_installed('uSup')) {
        $this->uFunc_new->incJs(u_sroot.'uSupport/js/inline_create.js');
    }
    //uSup
    if($this->uFunc->mod_installed('uKnowbase')) {
        $this->uFunc_new->incJs(u_sroot.'uKnowbase/js/inline_create.js');
    }
    //uEvents
    if($this->uFunc->mod_installed('uEvents')) {
        echo $this->uInt_print_js('uEvents','inline_create');
        $this->uFunc_new->incJs(u_sroot.'uEvents/js/inline_create.min.js');
    }
    //uPeople
    if($this->uFunc->mod_installed('uPeople')) {
        $this->uFunc_new->incJs(u_sroot.'uPeople/js/inline_create.min.js');
    }

    echo $uFunc->returnJs();
    echo $uFunc->returnCss(2);



    if($uFunc->mod_installed("uCat")) {
        $uCat_show_bottom_bar=1;
        if(!isset($uCat_common)) {
            if(isset($uCat->uCat)) $uCat_common=&$uCat->uCat;
            elseif(isset($uCat->uCat_common)) $uCat_common=&$uCat->uCat_common;
            else {
                require_once 'uCat/classes/common.php';
                $uCat_common=new \uCat\common($this);
            }
        }
        if($this->mod=='uCat') {
           if($this->page_name=='cart') $uCat_show_bottom_bar=0;
        }
        $has_items=$uCat_common->order_has_items($uCat_common->get_order_id());

        if($uCat_show_bottom_bar) {
            $cat_feedback_form_id=(int)$this->uFunc_new->getConf("cat_feedback_form_id","uCat");
            if($cat_feedback_form_id) $help_page_name=u_sroot."uForms/form/".$cat_feedback_form_id;
            else $help_page_name="#"
            ?>
            <nav id="uCat_bottom_bar" class="navbar navbar-default navbar-fixed-bottom navbar-inverse uCat_bottom_bar <?=$has_items?"":"hidden"?>">
                <div class="container">
                    <?/*if($help_page_name!="#") {?>
                        <ul class="nav navbar-nav navbar-left">
                            <li class="navbar-form"><button class="btn btn-default" onclick="document.location='<?=$help_page_name?>'">Обратная связь</button> </li>
                        </ul>
                    <?}*/?>
                    <ul class="nav navbar-nav navbar-<?=(int)$this->uFunc_new->getConf("show_cart_on_left","uCat")?"left":"right"?>">
                        <li id="uCat_bottom_nav_cart" class="active"><a href="javascript:void(0);" onclick="uCat_cart.show_cart_content_in_dialog()"><span class="icon-basket text-primary"></span> <span class="cart-label">Корзина&nbsp;&nbsp;&nbsp;&nbsp;</span><span class="label label-primary text-default" id="uCat_cart_items_number">0</span>&nbsp;&nbsp;&nbsp;&nbsp;<span id="uCat_cart_total_price">Пока пусто</span></a></li>

                        <li class="navbar-form"><button id="uCat_cart_order_btn" class="btn btn-primary" disabled onclick="document.location='<?=u_sroot?>uCat/cart'">Оформить заказ</button> </li>
                    </ul>
                </div>
            </nav>

            <div class="modal fade" id="uCat_cart_content_in_dialog_dg" tabindex="-1" role="dialog" aria-labelledby="uCat_cart_content_in_dialog_dgLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                            <h4 class="modal-title" id="uCat_cart_content_in_dialog_dgLabel">Корзина</h4>
                        </div>
                        <div class="modal-body" id="uCat_cart_content_in_dialog_content"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                            <button type="button" class="btn btn-primary" onclick="document.location='<?=u_sroot?>uCat/cart'">Оформить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
    <?}
    }


    if(!$uSes->access(2)&&(int)$uFunc->getConf("show_callback_btn","content")) {
        $uFunc->insert_callback_widget();
    }


    ?>
</div>
<?
if (strpos($_SERVER['HTTP_HOST'], ".local")) {?>
   <div id="u_local_reminder" style="width:100%; height: 50px; position: fixed; bottom: 0; background: rgba(0,16,228,0.3); outline: blue 1px dotted; text-align: center; font-size: 2em; color:white; z-index: 999999999999" onclick="$('#u_local_reminder').hide()" onmouseover="$('#u_local_reminder').html('Click to Hide')" onmouseout="$('#u_local_reminder').html('LOCAL')">LOCAL</div>
<?}

if(!$this->uSes->get_val("cookies_disclaimer_closed")&&!$uSes->access(2)&&(int)$uFunc->getConf("show_cookies_disclaimer","content")) {
    $about_cookies_page_id=(int)$uFunc->getConf("about_cookie_text_id","content",1);
    if($about_cookies_page_id) {
    $txt_obj=$uFunc->getStatic_data_by_id($about_cookies_page_id,"page_name");
    if($txt_obj) {
        $about_cookies_link = '<a target="_blank" class="cookies-notify__link" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">'.$translator_1588598067->txt('About cookies link text')."</a>";
    }
    }
    ?>
    <div class="u235-slideup-prom id_cookies hidden_disclaimer" id="cookies_disclaimer">
        <div class="u235-prom__body" style="width: auto; height: 100%; background-image: none;">
            <div class="cookies-notify" id="cookies-terminal">
                <p class="cookies-notify__paragraph"><?=$translator_1588598067->txt('About cookies text')?></p>
                <p class="cookies-notify__paragraph"><?=$about_cookies_link?></p>
                <p>&nbsp;</p>
            </div>
        </div>
        <div class="u235-prom__close-button u235-slideup-prom__close-button" title="Close" onclick="u235_common.hide_cookies_disclaimer()"></div>
    </div>
<?}?>
</body>
</html>
