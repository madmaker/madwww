<?php
require_once "uPage/elements/rubrics_tiles/common.php";
$el=new \uPage\admin\urubrics_tiles($this->uPage);

/** @noinspection PhpUndefinedVariableInspection */
$result_ar=$el->load_element_cnt($element->cols_els_id,$element->el_id,1);
$rubric_name=$el->get_rubric_name($element->el_id);
?>
<?/*<script type="text/javascript">*/?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.urubrics_tiles2conf=== "undefined") {uPage_setup_uPage.urubrics_tiles2conf=[]}
if (typeof uPage_setup_uPage.rubrics_tiles_id2cnt=== "undefined") {uPage_setup_uPage.rubrics_tiles_id2cnt=[]}

uPage_setup_uPage.rubrics_tiles_id2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($result_ar->cnt)?>");

uPage_setup_uPage.urubrics_tiles2conf[<?=$element->cols_els_id?>]=[];
uPage_setup_uPage.urubrics_tiles2conf[<?=$element->cols_els_id?>]['col_number']=<?=$result_ar->col_number?>;
uPage_setup_uPage.urubrics_tiles2conf[<?=$element->cols_els_id?>]['row_number']=<?=$result_ar->row_number?>;
uPage_setup_uPage.urubrics_tiles2conf[<?=$element->cols_els_id?>]['rubric_name']=decodeURIComponent("<?=rawurlencode($rubric_name)?>");
uPage_setup_uPage.urubrics_tiles2conf[<?=$element->cols_els_id?>]['rubric_id']=<?=$element->el_id?>;