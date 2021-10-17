<?php
$cache_dir="uEvents/cache/event/".site_id."/".$element->el_id;
if(!file_exists($cache_dir."/dates.html")) {
    include_once "uEvents/event.php";

    $setup_uEvent=new \uEvents\event($this->uCore);
    $setup_uEvent->event_id=$element->el_id;
    $setup_uEvent->cache_dir=$cache_dir;

    if($setup_uEvent->check_data()) $setup_uEvent->get_dates_cache();
}?>
if (typeof uPage_setup_uPage=== "undefined") {
uPage_setup_uPage={};
}

uPage_setup_uPage.uEvents_dates_id2data[<?=$element->el_id?>]="<?=rawurlencode(file_get_contents($cache_dir."/dates.html"))?>";