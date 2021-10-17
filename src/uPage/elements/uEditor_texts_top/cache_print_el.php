<?
echo '<?
require_once "uPage/inc/common.php";
if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
print $this->uPage->build_pages_top_widget();
?>';
?>
