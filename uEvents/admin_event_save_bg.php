<?php
namespace uEvents\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_event_save_bg {
    public $uFunc;
    public $uSes;
    private $uCore,$event_id,$field,$type_id;
    private function check_data() {
        if(!isset($_POST['event_id'],$_POST['field'])) $this->uFunc->error(10,1);
        $this->event_id=$_POST['event_id'];
        if(!uString::isDigits($this->event_id)) $this->uFunc->error(20,1);
        $this->field=$_POST['field'];
        if($this->field=='event_title') return true;
        if($this->field=='event_title event_pos is_header') return true;
        elseif($this->field=='event_type_id') return true;
        elseif($this->field=='event_info') return true;
        elseif($this->field=='event_descr') return true;
        elseif($this->field=='event_url') return true;
        elseif($this->field=='add_date') return true;
        elseif($this->field=='edit_date') return true;
        elseif($this->field=='delete_date') return true;
        elseif($this->field=='assign_form') return true;
        elseif($this->field=='assign_code') return true;
        elseif($this->field=='show_select_date_btn') return true;
        elseif($this->field=='show_dates') return true;
        else $this->uFunc->error(30,1);
        return false;
    }
    private function update_event_title() {
        if(!isset($_POST['event_title'])) $this->uFunc->error(40,1);
        $event_title=trim($_POST['event_title']);
        if(!strlen($event_title)) {
            die('{
            "status":"error",
            "msg":"title is empty"
            }');
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_title=:event_title
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $event_title=uString::text2sql($event_title);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_title', $event_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_title":"'.rawurlencode($event_title).'"
        }';

        $this->get_type_id();
        $this->clear_cache();
    }
    private function update_event_title_pos_header() {
        if(!isset($_POST['event_title'],$_POST['event_pos'],$_POST['is_header'])) $this->uFunc->error(60,1);
        $event_title=trim($_POST['event_title']);
        $event_pos=trim($_POST['event_pos']);
        $is_header=(int)trim($_POST['is_header']);
        if(!strlen($event_title)) {
            die('{
            "status":"error",
            "msg":"title is empty"
            }');
        }
        if(!uString::isInt($event_pos)) {
            die('{
            "status":"error",
            "msg":"wrong pos"
            }');
        }
        if($is_header!=1) $is_header=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_title=:event_title,
            event_pos=:event_pos,
            is_header=:is_header
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $event_title=uString::text2sql($event_title);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_pos', $event_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_header', $is_header,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_title', $event_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_title":"'.rawurlencode($event_title).'"
        }';

        $this->get_type_id();
        $this->clear_cache();
    }
    private function update_event_type_id() {
        if(!isset($_POST['event_type_id'])) $this->uFunc->error(80,1);
        $event_type_id=trim($_POST['event_type_id']);
        if(!uString::isDigits($event_type_id)) $this->uFunc->error(90,1);

        $this->get_type_id();
        $this->clear_cache();

        //check if type_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_title,
            type_url
            FROM
            u235_events_types
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $event_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(110,1);
        $type_title=uString::sql2text($qr->type_title,1);
        $type_url=uString::sql2text($qr->type_url,1);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_type_id=:event_type_id
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_type_id', $event_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_type_id":"'.rawurlencode($event_type_id).'",
        "type_title":"'.rawurlencode($type_title).'",
        "type_url":"'.rawurlencode($type_url).'"
        }';

        $this->type_id=$event_type_id;
        $this->clear_cache();
    }
    private function update_event_info() {
        if(!isset($_POST['event_info'])) $this->uFunc->error(130,1);
        $event_info=trim($_POST['event_info']);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_info=:event_info
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $event_info=uString::text2sql($event_info);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_info', $event_info,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/,1);}
        
        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_info":"'.rawurlencode($event_info).'"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }
    private function update_event_descr() {
        if(!isset($_POST['event_descr'])) $this->uFunc->error(150,1);
        $event_descr=trim($_POST['event_descr']);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_descr=:event_descr
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $event_descr=uString::text2sql($event_descr);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_descr', $event_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/,1);}
        
        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_descr":"'.rawurlencode($event_descr).'"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }
    private function update_event_url() {
        if(!isset($_POST['event_url'])) $this->uFunc->error(165,1);
        $event_url=trim($_POST['event_url']);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            event_url=:event_url
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $event_url=uString::text2sql($event_url);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_url', $event_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "event_id":"'.$this->event_id.'",
        "event_url":"'.rawurlencode($event_url).'"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }
    private function event_add_date() {
        if(!isset($_POST['date'],$_POST['duration'],$_POST['comment'])) $this->uFunc->error(175,1);
        $date=trim($_POST['date']);
        $duration=trim($_POST['duration']);
        $comment=trim($_POST['comment']);

        if(!uString::isDigits($date)) die('{
        "status":"error",
        "msg":"date format is wrong"
        }');
        $date=(int)$date;

        if(!uString::isDigits($duration)) die('{
        "status":"error",
        "msg":"duration format is wrong"
        }');
        $duration=(int)$duration;

        //get new date_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            date_id
            FROM
            u235_events_dates
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ORDER BY
            date_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/,1);}


        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $date_id=$qr->date_id+1;
        else $date_id=1;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("INSERT INTO
            u235_events_dates (
            date_id,
            event_id,
            `date`,
            duration,
            `comment`,
            site_id
            ) VALUES (
            :date_id,
            :event_id,
            :date,
            :duration,
            :comment,
            :site_id
            )
            ");
            $q_comment=uString::text2sql($comment);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':date_id', $date_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':date', $date,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':duration', $duration,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':comment', $q_comment,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "date_id":"'.$date_id.'",
        "date":"'.$date.'",
        "duration":"'.$duration.'",
        "comment":"'.rawurlencode($comment).'"
        }';

        $this->get_type_id();
        $this->clear_cache(false);
    }
    private function show_dates_save() {
        if(!isset($_POST['show_begin_timestamp'],$_POST['show_end_timestamp'])) $this->uFunc->error(200,1);
        $show_begin_timestamp=trim($_POST['show_begin_timestamp']);
        $show_end_timestamp=trim($_POST['show_end_timestamp']);

        if(!uString::isDigits($show_begin_timestamp)) die('{
        "status":"error",
        "msg":"date format is wrong"
        }');
        $show_begin_timestamp=(int)$show_begin_timestamp;

        if(!uString::isDigits($show_end_timestamp)) die('{
        "status":"error",
        "msg":"date format is wrong"
        }');
        $show_end_timestamp=(int)$show_end_timestamp;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            show_begin_timestamp=:show_begin_timestamp,
            show_end_timestamp=:show_end_timestamp
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_begin_timestamp', $show_begin_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_end_timestamp', $show_end_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
        $this->clear_cache(false);

    }
    private function event_edit_date() {
        if(!isset($_POST['date_id'],$_POST['date'],$_POST['duration'],$_POST['comment'])) $this->uFunc->error(215,1);
        $date_id=$_POST['date_id'];
        if(!uString::isDigits($date_id)) $this->uFunc->error(220,1);
        $date=trim($_POST['date']);
        $duration=trim($_POST['duration']);
        $comment=trim($_POST['comment']);

        if(!uString::isDigits($date)) die('{
        "status":"error",
        "msg":"date format is wrong"
        }');
        $date=(int)$date;

        if(!uString::isDigits($duration)) die('{
        "status":"error",
        "msg":"duration format is wrong"
        }');
        $duration=(int)$duration;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_dates
            SET
            `date`=:date,
            duration=:duration,
            `comment`=:comment
            WHERE
            event_id=:event_id AND
            date_id=:date_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $q_comment=uString::text2sql($comment);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':date', $date,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':duration', $duration,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':date_id', $date_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':comment', $q_comment,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('225'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done",
        "date_id":"'.$date_id.'",
        "date":"'.$date.'",
        "duration":"'.$duration.'",
        "comment":"'.rawurlencode($comment).'"
        }';

        $this->get_type_id();
        $this->clear_cache(false);
    }
    private function event_delete_date() {
        if(!isset($_POST['date_id'])) $this->uFunc->error(230,1);
        $date_id=$_POST['date_id'];
        if(!uString::isDigits($date_id)) $this->uFunc->error(240,1);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("DELETE FROM
            u235_events_dates
            WHERE
            date_id=:date_id AND
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':date_id', $date_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('250'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done"
        }';

        $this->get_type_id();
        $this->clear_cache(false);
    }
    private function assign_form() {
        if(!isset($_POST['form_id'])) $this->uFunc->error(260,1);
        $form_id=$_POST['form_id'];
        if(!uString::isDigits($form_id)) $this->uFunc->error(270,1);
        //check if this form exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            form_id
            FROM
            u235_forms
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('280'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$stm->fetch(PDO::FETCH_OBJ)) die('{
        "status":"error",
        "msg":"form does not exists"
        }');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            form_id=:form_id
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('290'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }
    private function assign_code() {
        if(!isset($_POST['code'])) $this->uFunc->error(300,1);
        $code=$_POST['code'];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            code=:code
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':code', $code,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('310'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }
    private function show_select_date_btn() {
        if(!isset($_POST['value'])) $this->uFunc->error(315,1);
        $show_select_btn=$_POST['value'];
        $show_select_btn=!!$show_select_btn;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("UPDATE
            u235_events_list
            SET
            show_select_btn=:show_select_btn
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_select_btn', $show_select_btn,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('320'/*.$e->getMessage()*/,1);}

        echo '{
        "status":"done"
        }';

        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
    }

    private function get_type_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            event_type_id
            FROM
            u235_events_list
            WHERE
            event_id=:event_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_id', $this->event_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('325'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(330,1);
        $this->type_id=$qr->event_type_id;
    }
    private function clear_cache($clear_same_events=true) {
        //delete cache
        //for current event cache
        $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$this->event_id);
        //for event's type
        $this->uFunc->rmdir('uEvents/cache/events/'.site_id.'/'.$this->type_id);
        if($clear_same_events) {
            //for all same type events
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
                event_id
                FROM
                u235_events_list
                WHERE
                event_type_id=:event_type_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':event_type_id', $this->type_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('340'/*.$e->getMessage()*/,1);}

            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            while($ev=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->rmdir('uEvents/cache/event/'.site_id.'/'.$ev->event_id);
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uSes);

        if(!$this->uSes->access(300)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if($this->field=='event_title') $this->update_event_title();//complex cache clean
        elseif($this->field=='event_title event_pos is_header') $this->update_event_title_pos_header();//complex cache clean
        elseif($this->field=='event_type_id') $this->update_event_type_id();//complex cache clean
        elseif($this->field=='event_info') $this->update_event_info();//easy cache clean
        elseif($this->field=='event_descr') $this->update_event_descr();//easy cache clean
        elseif($this->field=='event_url') $this->update_event_url();//easy cache clean
        elseif($this->field=='add_date') $this->event_add_date();//complex cache clean
        elseif($this->field=='edit_date') $this->event_edit_date();//complex cache clean
        elseif($this->field=='delete_date') $this->event_delete_date();//complex cache clean
        elseif($this->field=='assign_form') $this->assign_form();//easy cache clean
        elseif($this->field=='assign_code') $this->assign_code();//easy cache clean
        elseif($this->field=='show_select_date_btn') $this->show_select_date_btn();//easy cache clean
        elseif($this->field=='show_dates') $this->show_dates_save();//easy cache clean
        else $this->uFunc->error(350,1);
        $this->uFunc->set_flag_update_sitemap(1, site_id);
    }
}
new admin_event_save_bg($this);