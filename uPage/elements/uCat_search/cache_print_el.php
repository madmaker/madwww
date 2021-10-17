<?
echo '<?
require_once "uCat/classes/common.php";
require_once "uPage/inc/common.php";
require_once "uPage/elements/uCat_search/common.php";
if(!isset($this->uPage)) $this->uPage=new \uPage\common($this);
$el_common=new \uPage\admin\uCat_search($this->uPage);
$cols_els_id='.$cols_els_id.';
$conf=$el_common->get_el_config_uCat_search($cols_els_id);
                if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);
                echo $this->uCat->search_widget($cols_els_id);
                ?>';
?>