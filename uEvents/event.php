<?php
namespace uEvents;
use PDO;
use PDOException;
use uString;

require_once "processors/uFunc.php";

class event{
    public $code;
    public $show_select_btn;
    public $uFunc;
    public $event_url;
    private $show_end_timestamp;
    private $show_begin_timestamp;
    private $uCore;
    public
        $event_id,
        $event_title,$event_info,$event_descr,$event_img_timestamp,$form_id,
        $event_type_id,$type_title,$type_url,
        $cache_dir;
    public function check_data() {
        if(uString::isDigits($this->event_id)) {
            $this->event_id=(int)$this->event_id;
            return true;
        }
        return false;
    }
    private function get_event() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_type_id,
            event_title,
            event_info,
            event_descr,
            form_id,
            code,
            show_select_btn,
            event_url,
            show_begin_timestamp,
            show_end_timestamp
            FROM
            u235_events_list
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
            header('Location: '.u_sroot);
            exit('1');
        }
        $this->event_type_id=(int)$qr->event_type_id;
        $this->event_title=uString::sql2text($qr->event_title,1);
        $this->event_info=uString::sql2text($qr->event_info,1);
        $this->event_info=uString::repairHtml($this->event_info);
        $this->event_descr=uString::sql2text($qr->event_descr,1);
        $this->event_descr=uString::repairHtml($this->event_descr);
        $this->event_img_timestamp=(int)$qr->event_descr;
        $this->form_id=(int)$qr->form_id;
        $this->code=$qr->code;
        $this->show_select_btn=(int)$qr->show_select_btn;
        $this->event_url=uString::sql2text($qr->event_url,1);
        $this->show_begin_timestamp=(int)$qr->show_begin_timestamp;
        $this->show_end_timestamp=(int)$qr->show_end_timestamp;
    }
    private function get_event_type() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `type_title`,
        `type_url`
        FROM
        `u235_events_types`
        WHERE
        `type_id`='".$this->event_type_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(20);
        if(!mysqli_num_rows($query)) {//Почему-то заданный тип событий не найден - нужно назначить любой
            if(!$query=$this->uCore->query("uEvents","SELECT
            `type_id`,
            `type_title`,
            `type_url`
            FROM
            `u235_events_types`
            WHERE
            `site_id`='".site_id."'
            LIMIT 1
            ")) $this->uCore->error(30);
            if(!mysqli_num_rows($query)) {//Типов событий вообще нет ни одного
                header('Location: '.u_sroot);
                exit('2');
            }
            $qr=$query->fetch_object();
            if(!$this->uCore->query("uEvents","UPDATE
            `u235_events`
            SET
            `event_type_id`='".$qr->type_id."'
            WHERE
            `event_id`='".$this->event_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(40);
        }
        else $qr=$query->fetch_object();

        $this->type_title=uString::sql2text($qr->type_title,1);
        $this->type_url=$qr->type_url;
    }
    public function get_event_dates() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `date_id`,
        `date`,
        `duration`,
        `comment`
        FROM
        `u235_events_dates`
        WHERE
        `event_id`='".$this->event_id."' AND
        (`date`+`duration`*86400)>=".time()." AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(50);
        return $query;
    }
    public function get_same_events() {
        if(!$query=$this->uCore->query("uEvents","SELECT
        `event_id`,
        `event_title`,
        `event_pos`,
        `is_header`
        FROM
        `u235_events_list`
        WHERE
        `event_type_id`='".$this->event_type_id."' AND
        `site_id`='".site_id."'
        ORDER BY
        `event_pos` ASC
        ")) $this->uCore->error(60);
        return $query;
    }

    public function get_admin_js_vars_cache() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/admin_js_vars_cache.js.html', 'w');

        $html='
        <script type="text/javascript">
        if(typeof uEvents_event_admin==="undefined") uEvents_event_admin={};
            uEvents_event_admin.show_select_btn='.($this->show_select_btn?1:0).';
            uEvents_event_admin.event_url=decodeURIComponent("'.rawurlencode($this->event_url).'");';
            $q_dates=$this->get_event_dates();
            while($date=$q_dates->fetch_object()) {
            $html.='
            if(typeof uEvents_event_admin.dates==="undefined") {
                uEvents_event_admin.dates=[];
                uEvents_event_admin.date_id2i=[];
            }
            var i=uEvents_event_admin.dates.length;
            uEvents_event_admin.dates[i]=[];
            uEvents_event_admin.dates[i]["date_id"]='.$date->date_id.';
            uEvents_event_admin.dates[i]["date"]='.$date->date.';
            uEvents_event_admin.dates[i]["duration"]='.$date->duration.';
            uEvents_event_admin.dates[i]["comment"]="'.rawurlencode($date->comment).'";
            uEvents_event_admin.date_id2i['.$date->date_id.']=i;';
            }
        $html.='
            uEvents_event_admin.event_id='.$this->event_id.';
            uEvents_event_admin.event_title="'.rawurlencode($this->event_title).'";
            uEvents_event_admin.event_type_id='.(int)$this->event_type_id.';
            uEvents_event_admin.event_info="'.rawurlencode($this->event_info).'";
            uEvents_event_admin.event_descr="'.rawurlencode($this->event_descr).'";
            uEvents_event_admin.form_id='.(int)$this->form_id.';
            uEvents_event_admin.show_begin_timestamp="'.($this->show_begin_timestamp?$this->show_begin_timestamp:"").'";
            uEvents_event_admin.show_end_timestamp="'.($this->show_end_timestamp?$this->show_end_timestamp:"").'";
            uEvents_event_admin.code=decodeURIComponent("'.rawurlencode($this->code).'");
        </script>';
        fwrite($file, $html);
        fclose($file);
    }
    public function get_dates_cache() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/dates.html', 'w');

        $this->cache_dir='uEvents/cache/event/'.site_id.'/'.$this->event_id;
        $this->get_event();

        $html='<div class="uEvents_events_dates">
            <script type="text/javascript">
            if(typeof uEvents_events==="undefined") {
                uEvents_events = {};
            
                uEvents_events.ev_id2dates = [];
                uEvents_events.calendar_ev_classes = [];
            }
            uEvents_events.select_event=function(date) {
                history.pushState("data", "", "uEvents/event/'.$this->event_id.'?date="+date);
            }
            </script>';
                    $q_dates=$this->get_event_dates();
                    if(mysqli_num_rows($q_dates)) {
                        $html.='<table class="table table-striped">
                                                <tr>
                                                    <th>'.$this->text('Start date'/*Дата начала*/).'</th>
                                                    <th colspan="3">'.$this->text('Duration, days'/*Продолжительность, дней*/).'</th>
                                                </tr>';
                        while($date=$q_dates->fetch_object()) {
                            $html.='<tr>
                                                        <td>
                                                                <div class="btn-group u235_eip">
                                                                    <button class="btn btn-xs btn-danger uTooltip uEvents_event_delete_date_btn" title="'.$this->text('Delete date'/*Удалить эту дату*/).'" onclick="uEvents_event_admin.delete_date_do('.$date->date_id.')"><span class="glyphicon glyphicon-remove"></span></button>
                                                                    <button class="btn btn-xs btn-default uTooltip uEvents_event_edit_date_btn" title="'.$this->text('Change date'/*Изменить дату*/).'" onclick="uEvents_event_admin.edit_date_init('.$date->date_id.')"><span class="glyphicon glyphicon-pencil"></span></button>
                                                                </div>&nbsp;
                                                            '.date('d.m.Y',$date->date).'</td>
                                                        <td>'.$date->duration.'</td>
                                                        <td>'.$date->comment.'</td>
                                                        <td><button class="btn btn-primary btn-sm show_select_date_btn" onclick="uEvents_events.select_event(\''.date('d.m.Y',$date->date).'\');" '.((int)$this->show_select_btn?"":'style="display:none"').'>'.$this->text('Select date btn'/*Выбрать*/).'</button> </td>
                                        </tr>';
            }
            $html.='</table>';
        }
        $html.='</div>';

        fwrite($file, $html);
        fclose($file);
    }
    public function get_content_cache1() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/content1.html', 'w');

        $html='
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <h1 class="page-header"><span id="uEvents_event_title">'.$this->event_title.'</span></h1>
                    <div class="row">
                        <div class="col-md-5">
                            <div id="uEvents_event_info">'.$this->event_info.'</div>
                            <div class="well text-center" id="uEvents_event_contactus">
                                <div id="uEvents_event_contactus_info_btn_container" class="u235_eip">
                                    <button id="uEvents_event_contactus_info_btn" class="btn btn-xs btn-default" data-toggle="popover" title="'.$this->text('Where to set up this button?'/*Где настроить эту кнопку?*/).'" data-content="">
                                        <span class="glyphicon glyphicon-question-sign"></span>
                                    </button>
                                </div>
                                <h3>'.$this->text('Have questions?'/*Есть вопросы?*/).'</h3>
                                <p><a href="'.$this->uCore->uFunc->getConf("events_feedback_url","uEvents").'" class="btn btn-primary btn-lg" target="_blank">'.$this->text('Contact us!'/*Свяжитесь с нами!*/).'</a></p>
                            </div>
                        </div>
                        <div class="col-md-7" id="uEvents_event_descr">'.$this->event_descr.'</div>
                    </div>
                    <div class="row">';

        fwrite($file, $html);
        fclose($file);
    }
    public function get_content_cache2() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/content2.html', 'w');

        $html='</div>
                    <div class="row">
                        <div id="uEvents_event_code_container">'.$this->code.'</div>
                        <div id="uEvents_event_form_container">';

        fwrite($file, $html);
        fclose($file);
    }
    public function get_php_cache() {
        if(!file_exists($this->cache_dir)) mkdir($this->cache_dir,0755,true);
        $file= fopen($this->cache_dir.'/php.php', 'w');

        $this->get_event();
        $this->get_event_type();

        $code='<?
        $cache_dir="'.$this->cache_dir.'";
        $this->uCore->page["page_title"]="'.htmlspecialchars(strip_tags($this->event_title)).'";

        $this->uCore->uBc->add_info->type_title="'.htmlspecialchars(strip_tags($this->type_title)).'";
        $this->uCore->uBc->add_info->type_url="'.$this->type_url.'";

        $this->uCore->uFunc->incCss("uEvents/css/common.min.css");
        $this->uCore->uFunc->incCss("templates/site_'.site_id.'/css/uEvents/common.css");

        //uForms
        $this->uCore->uFunc->incJs(u_sroot."uForms/js/form.min.js",0);
        $this->uCore->uFunc->incCss(u_sroot."uForms/css/uForms.min.css");
        $this->uCore->uFunc->incCss(u_sroot."templates/site_1/css/uForms/uForms.css");

        if($this->uCore->access(300)) {
            include_once "uEvents/inc/event_dialogs.php";
            //tinymce
            $this->uCore->uFunc->incJs(u_sroot."js/tinymce/tinymce.min.js",0);
            //$this->uCore->uFunc->incJs(u_sroot."uEditor/js/uEditor_in_place.min.js",1);

            //popConfirm
            $this->uCore->uFunc->incJs("js/bootstrap_plugins/PopConfirm/jquery.popconfirm.min.js",1);

            //datepicker
            $this->uCore->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js",1);
            $this->uCore->uFunc->incJs(u_sroot."js/bootstrap-datepicker/dist/locales/bootstrap-datepicker.'.$this->text('Datepicker lang').'.min.js",1);
            $this->uCore->uFunc->incCss(u_sroot."js/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css");

            //moment-js
            $this->uCore->uFunc->incJs(u_sroot."js/moment/moment.js",1);

            $this->uCore->uFunc->incJs("uEvents/js/event_admin.min.js",2);

            $hash=$this->uCore->uFunc->sesHack();?>
            <script type="text/javascript">
            if(typeof uEvents_event_admin==="undefined") uEvents_event_admin={};
                uEvents_event_admin.sessions_hack_hash="<?=$hash[\'hash\']?>";
                uEvents_event_admin.sessions_hack_id="<?=$hash[\'id\']?>";
            </script>
            <?
            if(!file_exists($cache_dir."/admin_js_vars_cache.js.html")) $this->get_admin_js_vars_cache();
            echo file_get_contents($cache_dir."/admin_js_vars_cache.js.html");
        }

        if(!file_exists($cache_dir."/content1.html")) $this->get_content_cache1();
        echo file_get_contents($cache_dir."/content1.html");
        if(!file_exists($cache_dir."/dates.html")) $this->get_dates_cache();
        echo file_get_contents($cache_dir."/dates.html");
        if(!file_exists($cache_dir."/content2.html")) $this->get_content_cache2();
        echo file_get_contents($cache_dir."/content2.html");
        ';

        if($this->form_id!=0) {
            $code.='
            require_once "uForms/inc/form_builder.php";
                if(!isset($uForms)) $uForms=new uForms_form($this->uCore);
                
                $form_id='.$this->form_id.';
                $uForms->check_data($form_id);
                $dir="uForms/cache/'.site_id.'/'.$this->form_id.'";
                if(!file_exists($dir."/form.html")) $uForms->build_form_php($dir,'.$this->form_id.');

                echo file_get_contents($dir."/form.html");
            ';
        }

        $code.='?>
        </div>
        </div>
        </div>
        <div class="col-md-3 uEvents_events_list">
        <?
        $cache_dir="uEvents/cache/events/".site_id."/'.$this->event_type_id.'";
                if(!file_exists($cache_dir."/events_list.html")) {
                    include_once "uEvents/events.php";

                    /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
                    $setup_uEvents=new \uEvents\events($this->uCore);
                    $setup_uEvents->type_id='.$this->event_type_id.';
                    if($setup_uEvents->check_data()) $setup_uEvents->build_events_list_cache();
                }

                if(file_exists($cache_dir."/events_list.html")) echo file_get_contents($cache_dir."/events_list.html");
        ?>
        </div>
        </div>
        </div>';

        fwrite($file, $code);
        fclose($file);
    }
    public function get_page() {
        $this->cache_dir='uEvents/cache/event/'.site_id.'/'.$this->event_id;

        if(!file_exists($this->cache_dir.'/php.php')) $this->get_php_cache();
        include_once $this->cache_dir.'/php.php';
    }

    private function text($str) {
        return $this->uCore->text(array('uEvents','event'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->uCore->uInt_js('uEvents','event');
    }
}

if(isset($this->mod)) {
    if($this->mod=='uEvents'&&$this->page_name=='event') {
        if(!isset($this->url_prop[1])) {
            header('Location: '.u_sroot);
            exit('3');
        }
        $event_id=$this->url_prop[1];


        $uEvent=new event($this);
        $uEvent->event_id=$event_id;
        if(!$uEvent->check_data()) {
            header('Location: '.u_sroot);
            exit('4');
        }
        ob_start();
        $uEvent->get_page();

        $this->page_content=ob_get_contents();
        ob_end_clean();
        include "templates/template.php";

    }
}
