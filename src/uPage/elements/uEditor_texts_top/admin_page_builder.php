<?php
require_once "uPage/inc/common.php";
if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
$text_top_cnt = $this->uPage->build_pages_top_widget();
//<script type="text/javascript">
?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}

uPage_setup_uPage.uEditor_texts_top_cnt=decodeURIComponent("<?=rawurlencode($text_top_cnt)?>");
