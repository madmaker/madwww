<?php
require_once 'uSlider/inc/common.php';
if(!isset($this->uSlider)) $this->uSlider=new \uSlider\common($this->uCore);

$this->uSlider->cache_flip_book($element->cols_els_id);
$dir='uSlider/cache/'.site_id.'/'.$element->cols_els_id;?>

if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.flip_book_id2html=== "undefined") {uPage_setup_uPage.flip_book_id2html=[];}

uPage_setup_uPage.flip_book_id2html[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/slider.html"))?>");