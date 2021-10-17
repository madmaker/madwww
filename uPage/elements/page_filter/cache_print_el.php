<?
echo '<?
require_once "uPage/inc/common.php";
require_once "uPage/elements/page_filter/common.php";
if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
$el_common=new \uPage\admin\page_filter($this->uPage);
$cols_els_id='.$cols_els_id.';
$result_ar=$el_common->load_el_content('.$cols_els_id.',1);
echo $result_ar->html;
echo $result_ar->js;
                ?>';
?>