<?
echo '<?
require_once "uPage/inc/common.php";
require_once "uPage/elements/rubrics_tiles/common.php";
if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
$el_common=new \uPage\admin\urubrics_tiles($this->uPage);
$cols_els_id='.$cols_els_id.';
$result_ar=$el_common->load_element_cnt('.$cols_els_id.','.$el_id.',1);
echo $result_ar->cnt;
                ?>';
?>