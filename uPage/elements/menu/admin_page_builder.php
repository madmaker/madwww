<?if(!isset($this->uMenu)) $this->uMenu=new uMenu($this->uCore);?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.menu_id2cnt === "undefined") {uPage_setup_uPage.menu_id2cnt=[];}

uPage_setup_uPage.menu_id2cnt[<?=$element->el_id?>]=decodeURIComponent("<?=rawurlencode($this->uMenu->insert_menu($element->el_id))?>");