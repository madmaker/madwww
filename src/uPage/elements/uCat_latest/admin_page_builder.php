<?php
require_once 'uCat/classes/common.php';
require_once "uPage/elements/uCat_latest/common.php";
$el_common=new \uPage\admin\uCat_latest($this->uPage);
/** @noinspection PhpUndefinedVariableInspection */
$conf=$el_common->get_el_settings($element->cols_els_id);

if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);?>

if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uCat_latest2cnt === "undefined") {uPage_setup_uPage.uCat_latest2cnt=[];}
if (typeof uPage_setup_uPage.uCat_latest2conf === "undefined") {uPage_setup_uPage.uCat_latest2conf=[];}

uPage_setup_uPage.uCat_latest2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($this->uCat->last_items_widget($element->cols_els_id))?>");

uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['items_number']=<?=(int)$conf->items_number?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['title']=decodeURIComponent("<?=rawurlencode($conf->title)?>");
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xlg_number']=<?=(int)$conf->xlg_number?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['lg_number'] =<?=(int)$conf->lg_number?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['md_number'] =<?=(int)$conf->md_number?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['sm_number'] =<?=(int)$conf->sm_number?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xs_number'] =<?=(int)$conf->xs_number?>;

uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['slide_height'] =<?=(int)$conf->slide_height?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['image_style'] =<?=(int)$conf->image_style?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['dots_style'] =<?=(int)$conf->dots_style?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xlg_show_markers'] =<?=(int)$conf->xlg_show_markers?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['lg_show_markers'] =<?=(int)$conf->lg_show_markers?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['md_show_markers'] =<?=(int)$conf->md_show_markers?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['sm_show_markers'] =<?=(int)$conf->sm_show_markers?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xs_show_markers'] =<?=(int)$conf->xs_show_markers?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xlg_show_arrows'] =<?=(int)$conf->xlg_show_arrows?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['lg_show_arrows'] =<?=(int)$conf->lg_show_arrows?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['md_show_arrows'] =<?=(int)$conf->md_show_arrows?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['sm_show_arrows'] =<?=(int)$conf->sm_show_arrows?>;
uPage_setup_uPage.uCat_latest2conf[<?=$element->cols_els_id?>]['xs_show_arrows'] =<?=(int)$conf->xs_show_arrows?>;