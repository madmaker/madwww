<?php
namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

//require_once "lib/emogrifier/Classes/Emogrifier.php";
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSupport/classes/common.php";
require_once "uAuth/classes/common.php";

class request_change_status_bg {
    public $uFunc;
    public $uSes;
    public $uSup;
    public $uAuth;
    public $com_id;
    public $two_level;
    public $is_com_admin;
    public $escalated;
    public $tic_subject;
    public $cons_id;
    private $uCore,
        $tic_id,$status,$tic_feedback_hash,$user_id;
    private $cur_tick_status;

    private function check_data() {
        if(!isset($_POST['tic_id'],$_POST['status'])) $this->uFunc->error(10);
        if(!uString::isDigits($_POST['tic_id'])) $this->uFunc->error(20);
        $this->tic_id=&$_POST['tic_id'];

        $this->status=$_POST['status'];
    }
    private function check_access() {
        $this->is_com_admin=0;

        if(!$tic=$this->uSup->req_id2info($this->tic_id,"user_id,tic_status,escalated,company_id,tic_subject,cons_id")) $this->uFunc->error(30);

        $this->user_id=(int)$tic->user_id;
        $this->cur_tick_status=$tic->tic_status;
        $this->com_id=(int)$tic->company_id;
        $this->escalated=(int)$tic->escalated;
        $this->tic_subject=uString::sql2text($tic->tic_subject,1);
        $this->cons_id=(int)$tic->cons_id;

        $com_info=$this->uSup->com_id2com_info($this->com_id,"two_level");
        //check if current user is com_admin
        if(!$this->uSes->access(9)&&!$this->uSes->access(8)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                user_id 
                FROM 
                u235_com_users 
                WHERE 
                user_id=:user_id AND 
                com_id=:com_id AND 
                admin=1 AND
                site_id=:site_id
                ");
                $site_id=site_id;
                $user_id=$this->uSes->get_val("user_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) $this->is_com_admin=1;
        }

        if($com_info) $this->two_level=(int)$com_info->two_level;
        else $this->two_level=0;

        if(
            $this->uSes->access(8)||//Оператор
            ($this->two_level&&$this->is_com_admin&&!(int)$this->escalated)) {//Двухуровневая вкл, не эскалирован, админ компании - считай оператор при двухуровневой техподдержке
            if(
            (($this->cur_tick_status=='req_open'||$this->cur_tick_status=='req_answered'||$this->cur_tick_status=='req_processing')&&($this->status=='case_open'||$this->status=='req_closed'))||
            (($this->cur_tick_status=='case_open'||$this->cur_tick_status=='case_answered'||$this->cur_tick_status=='case_processing')&&$this->status=='case_done')||
            ($this->cur_tick_status=='case_done'&&$this->status=='case_closed')||
            ($this->cur_tick_status=='req_closed'&&$this->status=='req_processing')||
            ($this->cur_tick_status=='case_closed'&&$this->status=='case_processing')||
            $this->status=="escalated"
            ) return true;
            return false;
        }
        elseif(
        ($tic->user_id==$this->uSes->get_val("user_id")||//Автор запроса
            $this->is_com_admin//Админ компании
        )&&
            $this->cur_tick_status!='req_closed'&&$this->cur_tick_status!='case_closed'&&
            ($this->status=='req_closed'||$this->status=='case_closed')) return true;
        elseif($this->uSes->access(9)) {//Консультант
            if(
               ($this->cur_tick_status=='case_open'||$this->cur_tick_status=='case_answered'||$this->cur_tick_status=='case_processing')&&$this->status=='case_done'
            ) return true;
            return false;
        }
        return false;
    }
    private function update_status() {
        $tic_feedback_info_add='';
        if($this->status=='req_closed'||$this->status=='case_closed') {
            $this->tic_feedback_hash=$this->uFunc->genHash();
            $tic_feedback_info_add=" tic_feedback_info=:tic_feedback_info, ";
            $this->uSup->request_is_closed_notification($this->tic_id,site_id);
        }

        if($this->cur_tick_status=='req_open'&&$this->status=='case_open') $this->status='case_processing';
        elseif($this->cur_tick_status=='req_answered'&&$this->status=='case_open') $this->status='case_answered';
        elseif($this->cur_tick_status=='req_processing'&&$this->status=='case_open') $this->status='case_processing';

        if($this->status=="escalated") {
            $this->escalated=1;
            $this->status='req_open';
            //Update ticket status
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare("UPDATE
                    u235_requests
                    SET 
                    escalated=1,
                    tic_changed_timestamp=:tic_changed_timestamp,
                    tic_status='req_open'
                    WHERE
                    tic_id=:tic_id AND
                    site_id=:site_id
                    ");
                    $site_id = site_id;
                    $tic_changed_timestamp = time();

                if (strlen($tic_feedback_info_add)) /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_feedback_info', $this->tic_feedback_hash, PDO::PARAM_STR);

                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_changed_timestamp', $tic_changed_timestamp, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('50'/*.$e->getMessage()*/);
            }
        }
        else {
            //Update ticket status
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare('UPDATE
            u235_requests
            SET ' .
                    $tic_feedback_info_add .
                    'tic_status=:tic_status,
            tic_changed_timestamp=:tic_changed_timestamp
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ');
                $site_id = site_id;
                $tic_changed_timestamp = time();

                if (strlen($tic_feedback_info_add)) /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_feedback_info', $this->tic_feedback_hash, PDO::PARAM_STR);

                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':tic_id', $this->tic_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':tic_changed_timestamp', $tic_changed_timestamp, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':tic_status', $this->status, PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('60'/*.$e->getMessage()*/);
            }
        }

    }
    private function add_msg() {
        if($this->uSes->access(9)) {
            if($this->status=='req_closed') $status_label='Специалист техподдержки закрыл этот запрос';
//            elseif($this->status=='case_open') $status_label='Специалист техподдержки открыл кейс по этому запросу';
//            elseif($this->status=='case_done') $status_label='Специалист техподдержки отметил этот кейс выполненным';
            elseif($this->status=='case_closed') $status_label='Специалист техподдержки закрыл этот кейс';
            elseif($this->status=='req_processing') $status_label='Специалист техподдержки открыл этот запрос заново';
            elseif(($this->cur_tick_status=='case_closed'||$this->cur_tick_status=='case_done')&&$this->status=='case_processing') $status_label='Специалист техподдержки открыл этот кейс заново';
//            elseif(($this->cur_tick_status!='case_closed'&&$this->cur_tick_status!='case_done')&&$this->status=='case_processing') $status_label='Специалист техподдержки открыл кейс по этому запросу';
//            else $status_label='Ваш запрос рассматривается';
        }
        else {
            if($this->status=='req_closed') $status_label='Клиент закрыл этот запрос';
            elseif($this->status=='case_closed') $status_label='Клиент закрыл этот кейс';
            elseif($_POST['status']=="escalated") $status_label='Запрос эскалирован';
        }

        if(isset($status_label)) {
            //get new msg_id
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare("SELECT
                msg_id
                FROM
                u235_msgs
                WHERE
                site_id=:site_id
                ORDER BY
                msg_id DESC
                LIMIT 1
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */
                $stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if ($qr = $stm->fetch(PDO::FETCH_OBJ)) $msg_id = $qr->msg_id + 1;
            else $msg_id = 1;

            //Add new msg about changing consultant
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uSup")->prepare("INSERT INTO
                u235_msgs (
                tic_id,
                msg_id,
                msg_text,
                msg_sender,
                msg_timestamp,
                msg_status,
                site_id
                ) VALUES (
                :tic_id,
                :msg_id,
                :msg_text,
                :msg_sender,
                :msg_timestamp,
                1,
                :site_id
                )");
                $msg_timestamp = time();
                $msg_sender = $this->uSes->get_val("user_id");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_text', $status_label, PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_sender', $msg_sender, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_timestamp', $msg_timestamp, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSup=new common($this->uCore);
        $this->uAuth=new \uAuth\common($this->uCore);

        if(!$this->uSes->access(2)) die('{"status":"forbidden"}');

        $this->check_data();
        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->update_status();
        $this->add_msg();

        if($_POST['status']=="escalated") {//if request is escalcated - notify operators that request is escalated
            $q_operators=$this->uSup->get_operators("firstname,email");
            /** @noinspection PhpUndefinedMethodInspection */
            while($oper=$q_operators->fetch(PDO::FETCH_OBJ)) {
                $this->uSup->new_msg_cons_notification($this->tic_id,$oper->email,$oper->firstname,site_id);
            }
        }

        $new_status_btn_gr='';
        if($this->status=='req_closed'||$this->status=='case_closed') {
            if($this->status=='req_closed') {
                //operator
                if(
                    $this->uSes->access(8)||
                    ($this->two_level&&$this->is_com_admin&&!(int)$this->escalated)
                ) {
                    $new_status_btn_gr.='<button class="btn btn-default" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'req_processing\')">Открыть запрос заново</button>';
                }
            }
            else {
                //operator
                if(
                    $this->uSes->access(8)||
                    ($this->two_level&&$this->is_com_admin&&!(int)$this->escalated)
                ) {
                    $new_status_btn_gr.='<button class="btn btn-default" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'case_processing\')">Открыть кейс заново</button>';
                }
            }
            //operator or consultant
            if($this->uSes->access(8)||$this->uSes->access(9)) {
                $new_status_btn_gr.='<button id="uSup_uKnowbase_btn1" onclick="uSup_req_show_common.set_solution('.$this->tic_id.')" class="btn btn-warning">Назначить решение</button>';
            }
        }

        if($this->status!='req_closed'&&$this->status!='case_closed') {
            if(
            (
                $this->uSes->access(8)||
                $this->user_id==$this->uSes->get_val("user_id")||
                $this->is_com_admin
            )&&//operator or owner or com_admin
            ($this->status=='req_open'||
                $this->status=='req_answered'||
                $this->status=='req_processing')
            ) {
                $new_status_btn_gr.='<button class="btn btn-danger" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'req_closed\')">Закрыть запрос</button>';
            }
            elseif(
            (
                $this->uSes->access(8)||$this->uSes->access(9)||
                ($this->two_level&&$this->is_com_admin&&!(int)$this->escalated)
            )&&//operator or consultant
            ($this->status=='case_open'||
            $this->status=='case_processing'||
                $this->status=='case_answered')
            ) {
                $new_status_btn_gr.='<button class="btn btn-success" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'case_done\')">Кейс выполнен</button>';
            }
            elseif(
                (
                    $this->uSes->access(8)||$this->user_id==$this->uSes->get_val("user_id")||
                    $this->is_com_admin
                )&&//operator or owner
                ($this->status=='case_done'||
                $this->status=='case_open'||
                $this->status=='case_processing'||
                $this->status=='case_answered')
            ) {
                $new_status_btn_gr.='<button class="btn btn-danger" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'case_closed\')">Закрыть кейс</button>';
            }
        }

        if (
            ($this->uSes->access(8)||$this->is_com_admin)
            &&
            ($this->status!='req_closed'&&$this->status!='case_closed')
            &&
            ($this->two_level&&!(int)$this->escalated)
        ) {
            $new_status_btn_gr.='<button class="btn btn-primary" onclick="uSup_req_show_common.changeReqStatus('.$this->tic_id.',\'escalated\')">Эскалировать</button>';
        }


        $status2Icon_ar['req_open']=' class="btn btn-xs btn-info uTooltip" title="Запрос открыт">
        <span class="glyphicon glyphicon-envelope"></span>';
        $status2Icon_ar['req_answered']=' class="btn btn-xs btn-success uTooltip" title="Есть ответ на запрос">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $status2Icon_ar['req_processing']=' class="btn btn-xs btn-warning uTooltip" title="Запрос рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $status2Icon_ar['req_closed']=' class="btn btn-xs btn-default uTooltip" title="Запрос закрыт">
        <span class="glyphicon glyphicon-lock"></span>';

        $status2Icon_ar['case_open']=' class="btn btn-xs btn-info uTooltip" title="Кейс открыт">
        <span class="glyphicon glyphicon-envelope text-warning"></span>';
        $status2Icon_ar['case_answered']=' class="btn btn-xs btn-success uTooltip" title="Кейс отвечен">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $status2Icon_ar['case_processing']=' class="btn btn-xs btn-warning uTooltip" title="Кейс рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $status2Icon_ar['case_closed']=' class="btn btn-xs btn-default uTooltip" title="Кейс закрыт">
        <span class="glyphicon glyphicon-lock"></span>';
        $status2Icon_ar['case_done']=' class="btn btn-xs btn-primary uTooltip" title="Кейс выполнен">
        <span class="glyphicon glyphicon-check"></span>';

        $status2label_ar['req_open']='Запрос открыт';
        $status2label_ar['req_answered']='Запрос отвечен';
        $status2label_ar['req_processing']='Запрос рассматривается';
        $status2label_ar['req_closed']='Запрос закрыт';

        $status2label_ar['case_open']='Кейс открыт';
        $status2label_ar['case_answered']='Кейс отвечен';
        $status2label_ar['case_processing']='Кейс рассматривается';
        $status2label_ar['case_closed']='Кейс закрыт';
        $status2label_ar['case_done']='Кейс выполнен';

        if($this->status=="escalated") {
            $new_status_indicator = '<button onclick="uSup.show_request(' . $this->tic_id . ')" ' . $status2Icon_ar[$this->cur_tick_status] . '<br>' . $this->tic_id . '</button>';
            $new_req_show_status_indicator = '<a href="' . u_sroot . 'uSupport/request_show/' . $this->tic_id . '" ' . $status2Icon_ar[$this->cur_tick_status] . '<br>' . $this->tic_id . '</a>';
            $new_status_label=$status2label_ar[$this->cur_tick_status];
        }
        else {
            $new_status_indicator = '<button onclick="uSup.show_request(' . $this->tic_id . ')" ' . $status2Icon_ar[$this->status] . '<br>' . $this->tic_id . '</button>';
            $new_req_show_status_indicator = '<a href="' . u_sroot . 'uSupport/request_show/' . $this->tic_id . '" ' . $status2Icon_ar[$this->status] . '<br>' . $this->tic_id . '</a>';
            $new_status_label=$status2label_ar[$this->status];
        }

        echo "{
        'status':'done',
        'tic_id':'".$this->tic_id."',
        'tic_status':'".$this->status."',
        'escalated':'".$this->escalated."',
        'new_status_btn_gr':'".rawurlencode($new_status_btn_gr)."',
        'new_status_indicator':'".rawurlencode($new_status_indicator)."',
        'new_req_show_status_indicator':'".rawurlencode($new_req_show_status_indicator)."',
        'new_status_label':'".rawurlencode($new_status_label)."',
        'add_feedback_btn':'".($this->uSes->access(8)||$this->uSes->access(9)?'0':'1')."'
        }";
    }
}
new request_change_status_bg($this);
