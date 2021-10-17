<?php
require_once 'gallery/classes/common.php';
if(!isset($this->gallery)) $this->gallery=new \gallery\common($this->uCore);

$this->gallery->cache_gallery($element->cols_els_id);
$dir='gallery/cache/'.site_id.'/'.$element->el_id;
$gallery_data=$this->gallery->gallery_id2data($element->el_id,"gallery_title");
$gallery_title=$gallery_data->gallery_title;
$gallery_conf=$this->gallery->get_gallery_conf($element->el_id);
$row_height=$gallery_conf->row_height;
$margins=$gallery_conf->margins;
?>

if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.gallery_id2html=== "undefined") {uPage_setup_uPage.gallery_id2html=[];}
if (typeof uPage_setup_uPage.gallery_id2js=== "undefined") {uPage_setup_uPage.gallery_id2js=[];}
if (typeof uPage_setup_uPage.gallery2data=== "undefined") {uPage_setup_uPage.gallery2data=[];}

uPage_setup_uPage.gallery_id2html[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/gallery.html"))?>");
uPage_setup_uPage.gallery_id2js[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/gallery.js"))?>");
uPage_setup_uPage.gallery2data[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.gallery2data[<?=$element->cols_els_id?>]["row_height"]=<?=$row_height?>;
uPage_setup_uPage.gallery2data[<?=$element->cols_els_id?>]["margins"]=<?=$margins?>;
uPage_setup_uPage.gallery2data[<?=$element->cols_els_id?>]["gallery_title"]=decodeURIComponent("<?=rawurlencode($gallery_title)?>");
