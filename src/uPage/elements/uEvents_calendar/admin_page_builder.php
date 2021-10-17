<?php
$cache_dir="uEvents/cache/events/".site_id."/".$element->el_id;
if(!file_exists($cache_dir."/events.js")||!file_exists($cache_dir."/calendar_widget.html")) {
    include_once "uEvents/events.php";

    $setup_uEvents=new \uEvents\events($this->uCore);
    $setup_uEvents->type_id=$element->el_id;
    $setup_uEvents->cache_dir=$cache_dir;

    if($setup_uEvents->check_data()) {
        $setup_uEvents->build_events_js_cache();
        $setup_uEvents->calendar_widget_cache($element->el_id,1);
    }
}?>
if (typeof uPage_setup_uPage=== "undefined") {uPage_setup_uPage={};}
if (typeof uPage_setup_uPage.uEvents_calendar_id2data=== "undefined") {uPage_setup_uPage.uEvents_calendar_id2data=[];}

uPage_setup_uPage.uEvents_calendar_id2data[<?=$element->el_id?>]="<?=rawurlencode(file_get_contents($cache_dir."/calendar_widget.html"))?>";