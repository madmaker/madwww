<?
echo '<?
if(isset($this->uMenu)) $uMenu=&$this->uMenu;
elseif(isset($this->uCore->uMenu)) $uMenu=&$this->uCore->uMenu;
else {
    if(isset($this->uCore)) $uCore=&$this->uCore;
    else $uCore=&$this;
    
    require_once "processors/uMenu.php";
    $uMenu = new uMenu($uCore);
}

//$dir="uNavi/cache/menu/'.site_id.'/'.$el_id.'";
//if(!file_exists($dir."/menu.html")) $uMenu->build_menu_cache('.$el_id.');
//echo file_get_contents($dir."/menu.html");
echo $uMenu->insert_menu('.$el_id.');        
?>';