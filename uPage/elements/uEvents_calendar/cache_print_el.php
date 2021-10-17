<?php
echo '<?
                                    $cache_dir="uEvents/cache/events/".site_id."/'.$el_id.'";

                                    if(!file_exists($cache_dir."/events.js")||!file_exists($cache_dir."/calendar_widget.html")) {
                                        include_once "uEvents/events.php";

                                        $setup_uEvents=new \uEvents\events($this->uCore);
                                        $setup_uEvents->type_id='.$el_id.';
                                        $setup_uEvents->cache_dir=$cache_dir;

                                        if($setup_uEvents->check_data()) {
                                            $setup_uEvents->build_events_js_cache();
                                            $setup_uEvents->calendar_widget_cache('.$el_id.',$this->uEvents_calendar_exists?0:1);
                                        }
                                    }

                                    $this->uCore->uFunc->incCss("js/bootstrap_plugins/bootstrap-calendar/css/calendar.css");
                                        $this->uCore->uFunc->incCss("js/bootstrap_plugins/bootstrap-calendar/css/small.min.css");
                                        $this->uCore->uFunc->incJs("js/underscore/underscore.js");
                                        $this->uCore->uFunc->incJs("js/bootstrap_plugins/bootstrap-calendar/js/language/ru-RU.js");
                                        $this->uCore->uFunc->incJs("js/bootstrap_plugins/bootstrap-calendar/js/calendar.js");

                                        $this->uCore->uFunc->incJs("js/moment/moment.js");

                                        $this->uCore->uFunc->incJs($cache_dir."/events.js?".time());
                                        echo file_get_contents($cache_dir."/calendar_widget.html");
                                        $this->uEvents_calendar_exists=true;
                                ?>';