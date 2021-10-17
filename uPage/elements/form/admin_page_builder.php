<?php
require_once 'uForms/inc/form_builder.php';
if(isset($this->uCore)) $this->uCore->uInt_js('uForms','form');
else $this->uCore->uInt_js;

$uForms=new uForms_form($this->uCore);

$form_id=$element->el_id;
$uForms->check_data($form_id);
$dir='uForms/cache/'.site_id.'/'.$form_id;
if(!file_exists($dir.'/form.html')) $uForms->build_form_php($dir,$uForms->form_id);?>

if (typeof uPage_setup_uPage === "undefined") {uPage_setup_uPage = {};}
if (typeof uPage_setup_uPage.form_id2data === "undefined") {uPage_setup_uPage.form_id2data = [];}

uPage_setup_uPage.form_id2data[<?=$element->el_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($dir."/form.html"))?>");