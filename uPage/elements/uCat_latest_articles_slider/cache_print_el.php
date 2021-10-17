<?
echo '<?
require_once "uCat/classes/common.php";
                if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);
                echo $this->uCat->latest_articles_slider_widget('.$cols_els_id.');
                ?>';
?>