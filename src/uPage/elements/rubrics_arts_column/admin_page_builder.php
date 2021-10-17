<?php
require_once "uPage/elements/rubrics_arts_column/common.php";
$el=new \uPage\admin\urubrics_arts_column($this->uPage);

/** @noinspection PhpUndefinedVariableInspection */
$result_ar=$el->load_element_cnt($element->cols_els_id,$element->el_id,1);
?>
<?/*<script type="text/javascript">*/?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.urubrics_arts_column2conf=== "undefined") {uPage_setup_uPage.urubrics_arts_column2conf=[]}
if (typeof uPage_setup_uPage.rubrics_arts_column_id2cnt=== "undefined") {uPage_setup_uPage.rubrics_arts_column_id2cnt=[]}

uPage_setup_uPage.rubrics_arts_column_id2cnt[<?=$element->cols_els_id?>]=decodeURIComponent("<?=rawurlencode($result_ar->cnt)?>");

uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]=[];

uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['col_number']=<?=$result_ar->col_number?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['art_number']=<?=$result_ar->art_number?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['img_col_number']=<?=$result_ar->img_col_number?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['show_avatars']=<?=$result_ar->show_avatars?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['show_short_text']=<?=$result_ar->show_short_text?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['show_title']=<?=$result_ar->show_title?>;
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['rubric_name']=decodeURIComponent("<?=rawurlencode($result_ar->rubric_name)?>");
uPage_setup_uPage.urubrics_arts_column2conf[<?=$element->cols_els_id?>]['rubric_id']=<?=$element->el_id?>;