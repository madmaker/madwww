<?php
require_once "uPage/elements/art/common.php";
$el_common=new \uPage\admin\art($this->uPage);
$conf=$el_common->get_el_config_art($element->cols_els_id);
$q_get="page_name,page_alias,page_avatar_time".
    ($conf->show_title?",page_title":"").
    ($conf->show_short_text?",page_short_text":"").
    ($conf->show_text?",page_text":"");

if($conf->show_avatar) {
    require_once 'uEditor/inc/page_avatar.php';
    $page_avatar=new uEditor_page_avatar($this->uCore);
}
if($art=$el_common->get_art($element->el_id,$q_get)) {
    if(isset($art->page_short_text)) {
        $short_text = uString::sql2text($art->page_short_text, 1);
        if ($conf->short_text_is_link2art) $short_text = uString::removeHTML($short_text);
    }
    else $short_text="";

    if(isset($art->page_title)) {
        $page_title=uString::sql2text($art->page_title,1);
    }
    else $page_title="";

    if(isset($art->page_text)) {
        $text=uString::repairHtml(uString::sql2text($art->page_text,1));
    }
    else $text="";

    $page_avatar=$conf->show_avatar&&$art->page_avatar_time?$page_avatar->get_avatar(450, $element->el_id):"";
    ?>
    if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
    if (typeof uPage_setup_uPage.art_id2name=== "undefined") {uPage_setup_uPage.art_id2name=[]}
    if (typeof uPage_setup_uPage.art_id2page_short_text=== "undefined") {uPage_setup_uPage.art_id2page_short_text=[]}
    if (typeof uPage_setup_uPage.art_id2page_text=== "undefined") {uPage_setup_uPage.art_id2page_text=[]}
    if (typeof uPage_setup_uPage.art_id2page_title=== "undefined") {uPage_setup_uPage.art_id2page_title=[]}
    if (typeof uPage_setup_uPage.art_id2page_alias=== "undefined") {uPage_setup_uPage.art_id2page_alias=[]}
    if (typeof uPage_setup_uPage.art_id2page_avatar_time=== "undefined") {uPage_setup_uPage.art_id2page_avatar_time=[]}
    if (typeof uPage_setup_uPage.art_id2show_short_text=== "undefined") {uPage_setup_uPage.art_id2show_short_text=[]}
    if (typeof uPage_setup_uPage.art_id2page_avatar=== "undefined") {uPage_setup_uPage.art_id2page_avatar=[]}
    if (typeof uPage_setup_uPage.art_id2conf=== "undefined") {uPage_setup_uPage.art_id2conf=[]}

    uPage_setup_uPage.art_id2name               [<?=$element->el_id?>]  =decodeURIComponent("<?=rawurlencode(uString::sql2text($art->page_name,1))?>");
    uPage_setup_uPage.art_id2page_title         [<?=$element->el_id?>]  =decodeURIComponent("<?=rawurlencode($page_title)?>");
    uPage_setup_uPage.art_id2page_alias         [<?=$element->el_id?>]  =decodeURIComponent("<?=rawurlencode($art->page_alias)?>");
    uPage_setup_uPage.art_id2page_short_text    [<?=$element->el_id?>]  =decodeURIComponent("<?=rawurlencode($short_text)?>");
    uPage_setup_uPage.art_id2page_text          [<?=$element->el_id?>]  =decodeURIComponent("<?=rawurlencode($text)?>");
    uPage_setup_uPage.art_id2page_avatar_time   [<?=$element->el_id?>]  =<?=$art->page_avatar_time?>;
    uPage_setup_uPage.art_id2page_avatar        [<?=$element->el_id?>]  ="<?=$page_avatar?>";

    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]=[];
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['show_title']             =<?=$conf->show_title?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['title_is_link2art']      =<?=$conf->title_is_link2art?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['show_avatar']            =<?=$conf->show_avatar?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['show_short_text']        =<?=$conf->show_short_text?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['short_text_is_link2art'] =<?=$conf->short_text_is_link2art?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['show_more_btn']          =<?=$conf->show_more_btn?>;
    uPage_setup_uPage.art_id2conf[<?=$element->cols_els_id?>]['show_text']              =<?=$conf->show_text?>;
<?}