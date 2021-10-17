<?php
namespace uEvents;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class events {
    private $uFunc;
    private $uCore,$by_url;
    public $type_id,$type_title,$type_descr,$type_url,
        $cache_dir;
    public function check_data() {
        $this->by_url=0;
        if(!uString::isDigits($this->type_id)) {
            //may be url
            if(uString::isUrl_rus($this->type_id)) {
                $this->by_url=1;
                return $this->get_type_data();
            }
            else return false;
        }
        return $this->get_type_data();
    }
    public function get_type_data($site_id=site_id) {
        if($this->by_url) $by_field="type_url";
        else $by_field="type_id";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id,
            type_title,
            type_descr,
            type_url
            FROM
            u235_events_types
            WHERE
            $by_field=:$by_field AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(":$by_field", $this->type_id,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;

            $this->type_id=$qr->type_id;
            $this->type_title=uString::sql2text($qr->type_title,1);
            $this->type_descr=uString::sql2text($qr->type_descr,1);
            $this->type_url=$qr->type_url;

            $this->cache_dir='uEvents/cache/events/'.site_id.'/'.$this->type_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uEvents events 10'/*.$e->getMessage()*/);}

        return true;
    }
    public function get_events($event_type_id,$site_id=site_id) {
        $cur_timestamp=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_id,
            event_title,
            event_pos,
            is_header,
            event_url
            FROM
            u235_events_list
            WHERE
            event_type_id=:event_type_id AND
            (show_begin_timestamp<=:cur_timestamp OR show_begin_timestamp=0) AND
            (show_end_timestamp>=:cur_timestamp OR show_end_timestamp=0) AND
            site_id=:site_id
            ORDER BY
            event_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cur_timestamp', $cur_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_type_id', $event_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uEvents events 20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_events_with_dates($event_type_id,$site_id=site_id) {
        $cur_timestamp=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            u235_events_list.event_id,
            event_title,
            event_pos,
            is_header,
            date
            FROM
            u235_events_list
            JOIN 
            u235_events_dates
            ON
            u235_events_list.event_id=u235_events_dates.event_id AND
            u235_events_list.site_id=u235_events_dates.site_id
            WHERE
            event_type_id=:event_type_id AND
            (show_begin_timestamp<=:cur_timestamp OR show_begin_timestamp=0) AND
            (show_end_timestamp>=:cur_timestamp OR show_end_timestamp=0) AND
            u235_events_list.site_id=:site_id
            ORDER BY
            date DESC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cur_timestamp', $cur_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_type_id', $event_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uEvents events 20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function event_id2dates($event_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            date_id,
            date,
            duration
            FROM
            u235_events_dates
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uEvents events 30'/*.$e->getMessage()*/);}

        return 0;
    }

    public function calendar_widget_cache($type_id,$slide_events=1) {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/calendar_widget.html', 'w');
        $html='
        <script type="text/javascript" src="'.$this->cache_dir.'/events.js?'.time().'"></script>
                                        <div class="btn-group pull-right">
                                            <button class="btn btn-primary" onclick="uEvents_events.calendar'.$type_id.'.navigate(\'prev\');"><span class="glyphicon glyphicon-backward"></span></button>
                                            <button class="btn" onclick="uEvents_events.calendar'.$type_id.'.navigate(\'today\');">'.$this->text('Today'/*Сегодня*/).'</button>
                                            <button class="btn btn-primary" onclick="uEvents_events.calendar'.$type_id.'.navigate(\'next\');"><span class="glyphicon glyphicon-forward"></span></button>
                                        </div>
                                        <h3 id="uEvents_events_calendar_header_'.$type_id.'"></h3>
                                        <div id="uEvents_events_calendar_calendar_'.$type_id.'"></div>

                                        <script type="text/javascript">
                                        if(typeof uEvents_events==="undefined") {
                                            uEvents_events = {};
                                        
                                            uEvents_events.ev_id2dates = [];
                                            uEvents_events.calendar_ev_classes = [];
                                        }

                                        var month=moment().month()+1;
                                        month=month.toString();
                                        if(month.length<2) month="0"+month;

                                            uEvents_events.calendar'.$type_id.'_options = {
                                                events_source: uEvents_events.calendar'.$type_id.'_events,
                                                view: "month",
                                                tmpl_path: "js/bootstrap_plugins/bootstrap-calendar/tmpls/",
                                                tmpl_cache: false,
                                                day: moment().year()+"-"+month+"-10",
                                                onAfterViewLoad: function(view) {
                                                    $("#uEvents_events_calendar_header_'.$type_id.'").text(this.getTitle());
                                                },
                                                classes: {
                                                    months: {
                                                        general: "label"
                                                    }
                                                },
                                                language:(u_lang==="ru_RU"?"ru-RU":"en_US"),
                                                views:              {
                                                    year:  {
                                                        enable:       0
                                                    },
                                                    month: {
                                                        slide_events: '.$slide_events.',
                                                        enable:       1
                                                    },
                                                    week:  {
                                                        enable: 0
                                                    },
                                                    day:   {
                                                        enable: 0
                                                    }
                                                },
                                                onBeforeEventsLoad: function(next) {
                                                    next();
                                                },
                                                first_day:1
                                            };

                                            uEvents_events.calendar'.$type_id.' = $("#uEvents_events_calendar_calendar_'.$type_id.'").calendar(uEvents_events.calendar'.$type_id.'_options);


                                    </script>
                                        ';
        fwrite($file, $html);
        fclose($file);
    }

    private $calendar_ev_last_class;
    private $calendar_ev_classes;

    private function get_calendar_class(){
        if($this->calendar_ev_last_class>5) $this->calendar_ev_last_class=0;
        return $this->calendar_ev_classes[$this->calendar_ev_last_class++];
    }

    public function build_events_js_cache($site_id=site_id) {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/events.js', 'w');

        $html='
        
            if(typeof uEvents_events==="undefined") {
                uEvents_events = {};

                uEvents_events.ev_id2dates = [];
                uEvents_events.calendar_ev_classes = [];
            }
            
        uEvents_events.calendar'.$this->type_id.'_events=[';
        $q_events=$this->get_events($this->type_id,$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $q_events_ar=$q_events->fetchAll(PDO::FETCH_OBJ);
        $q_events_ar_count=count($q_events_ar);
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$i<$q_events_ar_count;$i++) {
            $ev=$q_events_ar[$i];
            $q_dates=$this->event_id2dates($ev->event_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($date=$q_dates->fetch(PDO::FETCH_OBJ)) {
                $html.='{
            "id": '.$ev->event_id.',
                    "title": "'.addslashes(uString::sql2text($ev->event_title,1)).'",
                    "url": "'.u_sroot.'uEvents/event/'.$ev->event_id.'",
                    "class": "'.$this->get_calendar_class().'",
                    "start": ('.$date->date.'*1000), // Milliseconds
                    "end": ('.$date->date.'*1000+'.($date->duration-1).'*60*60*24*1000) // Milliseconds
                },';
            }
        }
        $html.='{}
    ];';

        for($i=0;$i<$q_events_ar_count;$i++) {
            $ev=$q_events_ar[$i];
            $html.='uEvents_events.ev_id2dates['.$ev->event_id.']=[];';

            $q_dates=$this->event_id2dates($ev->event_id);
            /** @noinspection PhpUndefinedMethodInspection */
            while($date=$q_dates->fetch(PDO::FETCH_OBJ)) {
                $html.='var i=uEvents_events.ev_id2dates['.$ev->event_id.'].length;
            uEvents_events.ev_id2dates['.$ev->event_id.'][i]='.$date->date_id.';';
            }
        }

        fwrite($file, $html);
        fclose($file);
    }

    public function build_events_list_cache($site_id=site_id) {
        if(!isset($this->cache_dir)) $this->cache_dir='uEvents/cache/events/'.site_id.'/'.$this->type_id;

        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/events_list.html', 'w');

        $this->check_data();
        if($this->get_type_data()) {
            $q_events=$this->get_events($this->type_id,$site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            $q_events_ar=$q_events->fetchAll(PDO::FETCH_OBJ);
            $q_events_ar_count=count($q_events_ar);

            $html='<h3 id="uEvents_events_list_title"><a href="'.u_sroot.'uEvents/events/'.$this->type_id.'">'.$this->type_title.'</a></h3>
                <button id="uEvents_events_list_add_header_btn" class="u235_eip btn btn-sm btn-default" onclick="uEvents_inline_create.add_header_init()"><span class="glyphicon glyphicon-plus"></span>'.$this->text('Add title'/*Добавить заголовок*/).'</button>
            <div class="uEvents_events_list_container">';

            if($q_events_ar_count) {
                $html.='<ul class="list-unstyled">';
                for($i=0;$i<$q_events_ar_count;$i++) {
                    $ev=$q_events_ar[$i];
                    $html.='<li
                                onmouseover="
                                    if(typeof uEvents_events!=\'undefined\') {
                                        if(typeof uEvents_events.calendar_event_mouseover!=\'undefined\') {
                                            uEvents_events.calendar_event_mouseover('.$ev->event_id.')
                                        }
                                    }"
                                onmouseout="
                                    if(typeof uEvents_events!=\'undefined\') {
                                        if(typeof uEvents_events.calendar_event_mouseout!=\'undefined\') {
                                            uEvents_events.calendar_event_mouseout('.$ev->event_id.')
                                        }
                                    }"
                                >'.
                        ($ev->is_header=='1'?'<h4>':'').
                        '<button class="u235_eip btn btn-xs btn-default uTooltip" title="'.$this->text('Move or rename this element'/*Переместить или переименовать этот элемент*/).'" onclick="uEvents_inline_create.edit_element_init('.$ev->event_id.',\''.rawurlencode(uString::sql2text($ev->event_title,1)).'\','.$ev->event_pos.','.$ev->is_header.')">
                                    <span class="glyphicon glyphicon-pencil"></span>
                                </button>
                                 '.($ev->is_header=='0'?(
                                     '<a href="'.u_sroot.($ev->event_url==""?('uEvents/event/'.$ev->event_id):(uString::sql2text($ev->event_url,1))).'">'
                        ):'').
                        uString::sql2text($ev->event_title,1).
                        ($ev->is_header=='0'?'</a>':'</h4>').
                        '</li>';
                }
                $html.='</ul>';
            }

            $html.='</div>';
        }
        else $html='';

        fwrite($file, $html);
        fclose($file);
    }
    public function build_dates_list_cache($site_id=site_id) {
        return "";//для финнов свыпилил отображение дат под календарем
        if($this->uCore->uInt->lang==="ru_RU") $date_format="d.m.Y";
        else $date_format="m/d/Y";
        $html='';
        if($this->get_type_data()) {
            $q_events=$this->get_events_with_dates($this->type_id,$site_id);
            /** @noinspection PhpUndefinedMethodInspection */
            $q_events_ar=$q_events->fetchAll(PDO::FETCH_OBJ);
            $q_events_ar_count=count($q_events_ar);

            if($q_events_ar_count) {
                $html .= '<div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                        <h2>'.$this->type_title.' - '.$this->text("Event type dates - header").'</h2>
                            <ul class="list-unstyled">';
                for ($i = 0; $i < $q_events_ar_count; $i++) {
                    $ev = $q_events_ar[$i];
                    $html .= '<li
                        onmouseover="
                        if(typeof uEvents_events!=\'undefined\') {
                            if(typeof uEvents_events.calendar_event_mouseover!==\'undefined\') {
                             uEvents_events.calendar_event_mouseover(' . $ev->event_id . ')
                            }
                        }"
                        onmouseout="
                        if(typeof uEvents_events!==\'undefined\') {
                            if(typeof uEvents_events.calendar_event_mouseout!==\'undefined\') {
                                uEvents_events.calendar_event_mouseout(' . $ev->event_id . ')
                            }
                        }"
                    >
                        <button class="u235_eip btn btn-xs btn-default uTooltip" title="' . $this->text('Move or rename this element') . '" onclick="uEvents_inline_create.edit_element_init(' . $ev->event_id . ',\'' . rawurlencode(uString::sql2text($ev->event_title, 1)) . '\',' . $ev->event_pos . '\',' . $ev->is_header . ')">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </button>
                        <a href="'.u_sroot.'uEvents/event/'.$ev->event_id.'">'.date($date_format,$ev->date).' - ' . $ev->event_title . '</a>
                    </li>';
                }
                $html .= '</ul>
                    </div>
                </div>
                </div>
                ';
            }
        }
        return $html;
    }

    public function build_content_cache($site_id=site_id) {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/content.html', 'w');

        $html='

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <h1 class="page-header">
                        <div class="btn-group pull-right">
                            <button class="btn btn-primary" onclick="uEvents_events.calendar_prev()"><span class="glyphicon glyphicon-backward"></span></button>
                            <button class="btn" onclick="uEvents_events.calendar_today()">'.$this->text('Today'/*Сегодня*/).'</button>
                            <button class="btn btn-primary" onclick="uEvents_events.calendar_next()"><span class="glyphicon glyphicon-forward"></span></button>
                        </div>
                        <span id="uEvents_type_title">'.$this->type_title.'</span>&nbsp;
                        <span class="u235_eip">
                            <button class="btn btn-sm btn-default uTooltip" title="'.$this->text('Change the name of the event type'/*Изменить название типа событий*/).'" onclick="uEvents_events_admin.edit_title_init()"><span class="glyphicon glyphicon-pencil"></span></button>
                            <button class="btn btn-sm btn-default uTooltip" title="'.$this->text('Change the URL type of event'/*Изменить URL типа событий*/).'" onclick="uEvents_events_admin.edit_url_init()">URL</button>
                            <button class="btn btn-sm btn-danger uTooltip" title="'.$this->text('Delete event type'/*Удалить тип событий*/).'" onclick="uEvents_events_admin.delete_type_init()"><span class="glyphicon glyphicon-remove"></span></button>
                        </span>
                    </h1>
                    <div class="row" id="uEvents_events_calendar_container_'.$this->type_id.'">
                        <div class="col-md-6">
                            <h3 id="uEvents_events_calendar_header_'.$this->type_id.'"></h3>
                            <div id="uEvents_events_calendar_calendar_'.$this->type_id.'"></div>
                        </div>
                        <div class="col-md-6">
                            <h3 id="uEvents_events_calendar2_header_'.$this->type_id.'"></h3>
                            <div id="uEvents_events_calendar_calendar2_'.$this->type_id.'"></div>
                        </div>
                        
                        <div class="uEvents_events_dates_list">'.$this->build_dates_list_cache($site_id).'</div>
                    </div>
                    <div id="uEvents_type_descr">'.$this->type_descr.'</div>
                </div>
                <div class="col-md-3 uEvents_events_list">
            ';

        fwrite($file, $html);
        fclose($file);
    }
    public function build_content_cache2() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/content2.html', 'w');

        $html='
        </div>
        </div>
        </div>
        ';

        fwrite($file, $html);
        fclose($file);
    }
    public function build_cache() {
        $this->cache_dir;
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/php.php', 'w');

        $this->build_events_js_cache();

        $code='<?
        $uEvents->cache_dir="uEvents/cache/events/".site_id."/".$uEvents->type_id;
        $uEvents->type_id='.$this->type_id.';
        $this->page["page_title"]="'.htmlspecialchars(strip_tags($this->type_title)).'";

        $this->uFunc->incJs("js/moment/moment.js",-1);

        $this->uFunc->incJs("uEvents/cache/events/'.site_id.'/'.$this->type_id.'/events.js?'.time().'",2);
        $this->uFunc->incJs("uEvents/js/events.min.js",2);?>

        <script type="text/javascript">
            if(typeof uEvents_events==="undefined") {
                uEvents_events = {};
            
                uEvents_events.ev_id2dates = [];
                uEvents_events.calendar_ev_classes = [];
             }

                uEvents_events.type_id='.$this->type_id.';
                uEvents_events.calendar_events_source="uEvents_events.calendar'.$this->type_id.'_events";
        </script>
        <?

        if($this->access(300)) {
            $hash=$this->uFunc->sesHack();
            include_once "uEvents/inc/events_dialogs.php";


            //tinymce
            $this->uFunc->incJs(u_sroot."js/tinymce/tinymce.min.js",-1);
            //$this->uFunc->incJs(u_sroot."uEditor/js/uEditor_in_place.min.js",-1);

            //popConfirm
            $this->uFunc->incJs("js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js",-1);


            $this->uFunc->incJs("uEvents/js/events_admin.min.js",2);
            ?>
            <script type="text/javascript">
            
            if(typeof uEvents_events_admin==="undefined") uEvents_events_admin={};

                uEvents_events_admin.type_id='.$this->type_id.';
                uEvents_events_admin.type_url="'.rawurlencode($this->type_url).'";

                uEvents_events_admin.sessions_hack_hash="<?=$hash["hash"]?>";
                uEvents_events_admin.sessions_hack_id="<?=$hash["id"]?>";
            </script>
        <?}

        $this->uFunc->incCss("uEvents/css/common.min.css");
        $this->uFunc->incCss("templates/site_'.site_id.'/css/uEvents/common.css");

        if(!file_exists($uEvents->cache_dir."/content.html")) $uEvents->build_content_cache();
        echo file_get_contents($uEvents->cache_dir."/content.html");

        if(!file_exists($uEvents->cache_dir."/events_list.html")) $uEvents->build_events_list_cache();
        echo file_get_contents($uEvents->cache_dir."/events_list.html");

        if(!file_exists($uEvents->cache_dir."/content2.html")) $uEvents->build_content_cache2();
        echo file_get_contents($uEvents->cache_dir."/content2.html");';

        fwrite($file, $code);
        fclose($file);
    }

    private function text($str) {
        return $this->uCore->text(array('uEvents','events'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

        $this->calendar_ev_classes=[];

        $this->calendar_ev_classes[0]="event-important";
        $this->calendar_ev_classes[1]="event-success";
        $this->calendar_ev_classes[2]="event-warning";
        $this->calendar_ev_classes[3]="event-info";
        $this->calendar_ev_classes[4]="event-inverse";
        $this->calendar_ev_classes[5]="event-special";
        $this->calendar_ev_last_class=0;

        $this->uCore->uInt_js('uEvents','events');
    }
}
if(property_exists($this,'mod')) {
    if($this->mod=='uEvents'&&$this->page_name=='events') {

        $uEvents=new events($this);
        if(!isset($this->url_prop[1])) {
            header('Location:'.u_sroot);
            exit;
        }
        $uEvents->type_id=$this->url_prop[1];
        if(!$uEvents->check_data()) {
            header('Location:'.u_sroot);
            exit;
        }

        ob_start();

        $cache_dir="uEvents/cache/events/".site_id."/".$uEvents->type_id;
        if(!file_exists($cache_dir.'/php.php')) $uEvents->build_cache();

        include_once $cache_dir.'/php.php';

        $this->page_content=ob_get_contents();
        ob_end_clean();
        include "templates/template.php";

    }
}
