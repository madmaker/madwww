<?
echo '<?
require_once "uCat/classes/common.php";
require_once "uPage/inc/common.php";
require_once "uPage/elements/search/common.php";
if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
$el_common=new \uPage\admin\search($this->uPage);
$cols_els_id='.$cols_els_id.';
$conf=$el_common->get_el_config_search($cols_els_id);
echo $el_common->print_el($conf->placeholder);
?>';
?>