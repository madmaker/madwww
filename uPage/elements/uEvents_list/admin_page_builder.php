<?php
$cache_dir="uEvents/cache/events/".site_id."/".$element->el_id;
if(!file_exists($cache_dir."/events_list.html")) {
    include_once "uEvents/events.php";

    if(!isset($this->setup_uEvents)) $this->setup_uEvents=new \uEvents\events($this->uCore);
    $this->setup_uEvents->type_id=$element->el_id;
    $this->setup_uEvents->cache_dir=$cache_dir;
    if($this->setup_uEvents->check_data()) $this->setup_uEvents->build_events_list_cache();
}?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uEvents_list_id2data=== "undefined") {uPage_setup_uPage.uEvents_list_id2data=[];}

uPage_setup_uPage.uEvents_list_id2data[<?=$element->el_id?>]=decodeURIComponent("<?=rawurlencode(file_get_contents($cache_dir."/events_list.html"))?>");