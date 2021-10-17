<?php
require_once "uPage/elements/uCat_search/common.php";
$el_common=new \uPage\admin\uCat_search($this->uPage);
$conf=$el_common->get_el_config_uCat_search($element->cols_els_id);

require_once 'uCat/classes/common.php';
if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);?>
if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uCat_search2cnt === "undefined") {uPage_setup_uPage.uCat_search2cnt=[];}
if (typeof uPage_setup_uPage.uCat_search2conf === "undefined") {uPage_setup_uPage.uCat_search2conf=[];}

uPage_setup_uPage.uCat_search2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($this->uCat->search_widget($element->cols_els_id))?>");


uPage_setup_uPage.uCat_search2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.uCat_search2conf[<?=$element->cols_els_id?>]['placeholder']=decodeURIComponent("<?=rawurlencode($conf->placeholder)?>");