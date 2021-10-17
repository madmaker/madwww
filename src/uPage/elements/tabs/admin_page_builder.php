<?php
require_once "uPage/elements/tabs/common.php";
$el=new \uPage\admin\tabs($this->uPage);

/** @noinspection PhpUndefinedVariableInspection */
$el->cache_tabs($element->cols_els_id);
$dir='uPage/cache/tabs/'.site_id.'/'.$element->cols_els_id;?>

if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.tabs_id2html=== "undefined") {uPage_setup_uPage.tabs_id2html=[];}
if (typeof uPage_setup_uPage.tabs_id2js=== "undefined") {uPage_setup_uPage.tabs_id2js=[];}

uPage_setup_uPage.tabs_id2html[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/tabs.html"))?>");
uPage_setup_uPage.tabs_id2js[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/tabs.js"))?>");