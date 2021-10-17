<?php
require_once "uPage/elements/page_filter/common.php";
$el=new \uPage\admin\page_filter($this->uPage);

/** @noinspection PhpUndefinedVariableInspection */
$el->cache_page_filter($element->cols_els_id);
$dir='uPage/cache/page_filter/'.site_id.'/'.$element->cols_els_id;?>

if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.page_filter_id2html=== "undefined") {uPage_setup_uPage.page_filter_id2html=[];}
if (typeof uPage_setup_uPage.page_filter_id2js=== "undefined") {uPage_setup_uPage.page_filter_id2js=[];}

uPage_setup_uPage.page_filter_id2html[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/page_filter.html"))?>");
uPage_setup_uPage.page_filter_id2js[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/page_filter.js"))?>");