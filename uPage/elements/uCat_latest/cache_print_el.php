<?
echo '<?
require_once "uCat/classes/common.php";
                if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);
                echo $this->uCat->last_items_widget('.$cols_els_id.');
                ?>';
?>