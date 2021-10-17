<?php
require_once "uPage/elements/rubrics_arts/common.php";
$el=new \uPage\admin\rubrics_arts($this->uPage);

/** @noinspection PhpUndefinedVariableInspection */
$result_ar=$el->load_el_content($element->el_id,$element->cols_els_id,1);
?>
<?/*<script type="text/javascript">*/?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.rubrics_arts_id2conf=== "undefined") uPage_setup_uPage.rubrics_arts_id2conf=[];
if (typeof uPage_setup_uPage.rubrics_arts_id2cnt=== "undefined") uPage_setup_uPage.rubrics_arts_id2cnt=[];

uPage_setup_uPage.rubrics_arts_id2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($result_ar->cnt)?>");

uPage_setup_uPage.rubrics_arts_id2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.rubrics_arts_id2conf[<?=$element->cols_els_id?>]['page_number']=<?=$result_ar->page_number?>;
uPage_setup_uPage.rubrics_arts_id2conf[<?=$element->cols_els_id?>]['dots_style']=<?=$result_ar->dots_style?>;
uPage_setup_uPage.rubrics_arts_id2conf[<?=$element->cols_els_id?>]['rubric_name']=decodeURIComponent("<?=rawurlencode($result_ar->rubric_name)?>");
uPage_setup_uPage.rubrics_arts_id2conf[<?=$element->cols_els_id?>]['rubric_id']=<?=$element->el_id?>;