<?php
require_once 'uCat/classes/common.php';
if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);?>

if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uCat_popular2cnt === "undefined") {uPage_setup_uPage.uCat_popular2cnt=[];}

uPage_setup_uPage.uCat_popular2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($this->uCat->popular_items_widget($element->cols_els_id))?>");