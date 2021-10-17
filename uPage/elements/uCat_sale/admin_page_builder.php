<?php
require_once 'uCat/classes/common.php';
require_once "uPage/elements/uCat_sale/common.php";
$el_common=new \uPage\admin\uCat_sale($this->uPage);
/** @noinspection PhpUndefinedVariableInspection */
$conf=$el_common->get_el_settings($element->cols_els_id);

if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);?>

if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uCat_sale2cnt === "undefined") {uPage_setup_uPage.uCat_sale2cnt=[];}
if (typeof uPage_setup_uPage.uCat_sale2conf === "undefined") {uPage_setup_uPage.uCat_sale2conf=[];}

uPage_setup_uPage.uCat_sale2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($this->uCat->sale_items_widget($element->cols_els_id))?>");

uPage_setup_uPage.uCat_sale2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.uCat_sale2conf[<?=$element->cols_els_id?>]['items_number']=<?=(int)$conf->items_number?>;
uPage_setup_uPage.uCat_sale2conf[<?=$element->cols_els_id?>]['dots_style']=<?=(int)$conf->dots_style?>;
uPage_setup_uPage.uCat_sale2conf[<?=$element->cols_els_id?>]['item_title_lines']=<?=(int)$conf->item_title_lines?>;