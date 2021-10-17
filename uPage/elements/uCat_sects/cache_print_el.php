<?
echo '<?require_once "uCat/classes/common.php";
                if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);
                echo $this->uCat->sects_list_widget();
                ?>';
?>