<?
echo '<?
                $cache_dir="uEvents/cache/events/".site_id."/'.$el_id.'";
                if(!file_exists($cache_dir."/events_list.html")) {
                    include_once "uEvents/events.php";

                    $setup_uEvents=new \uEvents\events($this->uCore);
                    $setup_uEvents->type_id='.$el_id.';
                    if($setup_uEvents->check_data()) $setup_uEvents->build_events_list_cache();
                }

                if(file_exists($cache_dir."/events_list.html")) echo file_get_contents($cache_dir."/events_list.html");
            ?>';