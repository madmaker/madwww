<?php
require_once 'uCat/classes/common.php';
if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);?>

if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}

uPage_setup_uPage.uCat_sects_cnt=decodeURIComponent("<?=rawurlencode($this->uCat->sects_list_widget())?>");