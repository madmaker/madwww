<?php
require_once "uPage/elements/search/common.php";
$el_common=new \uPage\admin\search($this->uPage);
$conf=$el_common->get_el_config_search($element->cols_els_id); ?>
if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.search2cnt === "undefined") {uPage_setup_uPage.search2cnt=[];}
if (typeof uPage_setup_uPage.search2conf === "undefined") {uPage_setup_uPage.search2conf=[];}

uPage_setup_uPage.search2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($el_common->print_el($conf->placeholder))?>");


uPage_setup_uPage.search2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.search2conf[<?=$element->cols_els_id?>]['placeholder']=decodeURIComponent("<?=rawurlencode($conf->placeholder)?>");