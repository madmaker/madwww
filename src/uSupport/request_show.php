<?
namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uAuth/inc/avatar.php";
require_once "uSupport/classes/common.php";

class request_show {
    public $uFunc;
    public $uSes;
    public $uSup;
    public $com_title;
    public $two_level;
    private $uCore,$user_id2info_ar;
    public $request, $qMsg, $newMsg_id, $consData, $qCatList,
        $is_com_client,$is_com_admin,$is_consultant,$is_operator,$has_access,$readonly,
        $status2Icon_ar,$status2label_ar,
        $q_requests_time,$q_cons_list,
        $user_avatar;

    private function check_data() {
        if(isset($_POST['in_dialog'])) {
            if(!isset($_POST['req_id'])) die("forbidden");
            $tic_id=$_POST['req_id'];
            if(!uString::isDigits($tic_id)) die("forbidden");
        }
        else {
            if(!isset($this->uCore->url_prop[1])) header('Location: '.u_sroot.$this->uCore->mod.'/requests');
            $tic_id=$this->uCore->url_prop[1];
            if(!uString::isDigits($tic_id)) header('Location: '.u_sroot.$this->uCore->mod.'/requests');
        }

        //Take req data from db
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            tic_id,
            company_id,
            user_id,
            cons_id,
            company_id,
            tic_opened_timestamp,
            tic_changed_timestamp,
            tic_subject,
            tic_status,
            tic_cat,
            uknowbase_solution_isset,
            uknowbase_no_solution_reason,
            uknowbase_no_solution_user_id,
            tic_feedback_info,
            tic_time_spent,
            escalated
            FROM
            u235_requests
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->request=$stm->fetch(PDO::FETCH_ASSOC);
            if(!$this->request) {
                if(isset($_POST['in_dialog'])) die("forbidden");
                else {
                    header('Location: '.u_sroot.'uSupport/requests?all');
                    exit;
                }
            }
            $this->request['tic_subject']=uString::sql2text($this->request['tic_subject'],1);
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function check_access() {
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=$this->has_access=$this->readonly=false;
        //consultant or operator
        if($this->uSes->access(8)) {
            $this->is_operator=true;
            return true;
        }
        if($this->uSes->access(9)) {
            $this->is_consultant=true;
            return true;
        }
        //check if current user is admin ow request's company
        if($this->request['company_id']!='0') {

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                admin
                FROM
                u235_com_users
                WHERE
                user_id=:user_id AND
                com_id=:com_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                $user_id=$this->uSes->get_val("user_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->request['company_id'],PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($com=$stm->fetch(PDO::FETCH_OBJ)) {
                    if($com->admin=='1') {
                        $this->is_com_admin=true;
                        return true;
                    }
                    else {
                        if($this->request['user_id']==$this->uSes->get_val("user_id")) {//if this user is owner of request
                            $this->is_com_client=true;
                            return true;
                        }
                        else {//at least same company -readonly granted
                            $this->readonly=true;
                        }
                    }
                }
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
        //check if current user is owner of this request
        if($this->request['user_id']==$this->uSes->get_val("user_id")) {
            //check if we can receive request from users not in companies
            if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        }

        if($this->readonly) return true;

        return false;
    }

    private function set_labels() {
        //Статусы запросов
        $this->status2Icon_ar['req_open']=' class="btn btn-xs btn-info uTooltip" title="Запрос открыт">
        <span class="glyphicon glyphicon-envelope"></span>';
        $this->status2Icon_ar['req_answered']=' class="btn btn-xs btn-warning uTooltip" title="Есть ответ на запрос">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $this->status2Icon_ar['req_processing']=' class="btn btn-xs btn-warning uTooltip" title="Запрос рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $this->status2Icon_ar['req_closed']=' class="btn btn-xs btn-default uTooltip" title="Запрос закрыт">
        <span class="glyphicon glyphicon-lock"></span>';

        $this->status2Icon_ar['case_open']=' class="btn btn-xs btn-info uTooltip" title="Кейс открыт">
        <span class="glyphicon glyphicon-envelope text-warning"></span>';
        $this->status2Icon_ar['case_answered']=' class="btn btn-xs btn-success uTooltip" title="Кейс отвечен">
        <span class="glyphicon glyphicon-share-alt"></span>';
        $this->status2Icon_ar['case_processing']=' class="btn btn-xs btn-warning uTooltip" title="Кейс рассматривается">
        <span class="glyphicon glyphicon-time"></span>';
        $this->status2Icon_ar['case_closed']=' class="btn btn-xs btn-default uTooltip" title="Кейс закрыт">
        <span class="glyphicon glyphicon-lock"></span>';
        $this->status2Icon_ar['case_done']=' class="btn btn-xs btn-primary uTooltip" title="Кейс выполнен">
        <span class="glyphicon glyphicon-check"></span>';


        $this->status2label_ar['req_open']='Запрос открыт';
        $this->status2label_ar['req_answered']='Запрос отвечен';
        $this->status2label_ar['req_processing']='Запрос рассматривается';
        $this->status2label_ar['req_closed']='Запрос закрыт';

        $this->status2label_ar['case_open']='Кейс открыт';
        $this->status2label_ar['case_answered']='Кейс отвечен';
        $this->status2label_ar['case_processing']='Кейс рассматривается';
        $this->status2label_ar['case_closed']='Кейс закрыт';
        $this->status2label_ar['case_done']='Кейс выполнен';
    }

    private function get_req_cat_title() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            cat_title
            FROM
            u235_requests_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->request['tic_cat'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->request['tic_cat_title']=$qr->cat_title;
            else $this->request['tic_cat_title']='Без категории';
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
//    private function get_cat_list(){
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//            cat_id,
//            cat_title
//            FROM
//            u235_requests_cats
//             WHERE
//            site_id=:site_id
//            ORDER BY
//            cat_title ASC
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//            for($i=0;$this->qCatList[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++) {};
//        }
//        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
//    }

    private function make_tmp_id_for_new_msg(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            msg_id
            FROM
            u235_msgs
            WHERE
            site_id=:site_id
            ORDER BY
            msg_id DESC
            LIMIT 1");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->newMsg_id=$qr->msg_id+1;
            else $this->newMsg_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('45'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_msgs (
            tic_id,
            msg_id,
            msg_sender,
            msg_timestamp,
            msg_status,
            site_id
            ) VALUES (
            :tic_id,
            :msg_id,
            :msg_sender,
            :msg_timestamp,
            '',
            :site_id
            )");
            $site_id=site_id;
            $msg_sender=$this->uSes->get_val("user_id");
            $msg_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->request['tic_id'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $this->newMsg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_sender', $msg_sender,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_timestamp', $msg_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }

    private function get_req_messages() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            msg_id,
            msg_text,
            msg_sender,
            msg_timestamp
            FROM
            u235_msgs
            WHERE
            tic_id=:tic_id AND
            msg_status=1 AND
            site_id=:site_id
            ORDER BY
            msg_timestamp ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->request['tic_id'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->qMsg[$i]=$stm->fetch(PDO::FETCH_ASSOC); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }

    public function user_id2info($user_id) {
        if(!isset($this->user_id2info_ar[$user_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
                firstname,
                secondname,
                lastname,
                user_id,
                avatar_timestamp
                FROM
                u235_users
                WHERE
                user_id=:user_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->user_id2info_ar[$user_id]=$stm->fetch(PDO::FETCH_ASSOC)) echo 'error'.$user_id;
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        return $this->user_id2info_ar[$user_id];
    }
    private function get_req_cons_info() {
        $this->consData['id']=0;
        $this->consData['firstname']='Не назначен';
        $this->consData['lastname']='';

        if($this->request['cons_id']!='0') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
                 firstname,
                 lastname
                FROM
                 u235_users
                 JOIN
                 u235_usersinfo
                 ON
                 u235_users.user_id=u235_usersinfo.user_id
                WHERE
                u235_users.user_id=:user_id AND
                u235_usersinfo.site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->request['cons_id'],PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($res=$stm->fetch(PDO::FETCH_ASSOC))  $this->consData=$res;
            }
            catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
        }
    }
    private function get_cons_users() {
        //get consultants,operators,supervisors,administrators
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id AND
            u235_usersinfo.status=u235_users.status
            JOIN 
            u235_usersinfo_groups
            ON
            u235_usersinfo_groups.user_id=u235_usersinfo.user_id AND
            u235_usersinfo_groups.site_id=u235_usersinfo.site_id
            WHERE
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id AND
            (group_id=4 OR group_id=5 OR group_id=7 OR group_id=18)
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->q_cons_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }
    private function get_cons_com_admins() {
        //get list of com admins
        $q_admins=$this->uSup->get_com_admins("user_id",$this->request["company_id"]);

        $q_admin_user_ids=" ";
        while($qr=$q_admins->fetch(PDO::FETCH_OBJ)) {
            $q_admin_user_ids.=" OR u235_users.user_id=".$qr->user_id." ";
        }
        //get consultants,operators,supervisors,administrators
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id AND
            u235_usersinfo.status=u235_users.status
            WHERE
            (1=0 ".$q_admin_user_ids.") AND
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->q_cons_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    public function get_msg_files($msg_id) {
        //Достаём список файлов, прикреплённых к сообщению
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            file_id,
            filename,
            file_size,
            file_mime,
            timestamp
            FROM
            u235_msgs_files
            WHERE
            msg_id=:msg_id AND
            site_id=:site_id
            ORDER BY
            file_id ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $qr[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
            return $qr;
        }
        catch(PDOException $e) {$this->uFunc->error('105'/*.$e->getMessage()*/);}

        return array();
    }
    private function update_req_status2processing() {
        //if current user is not an owner of request
        //if($this->request['user_id']!==$this->uSes->get_val("user_id")) {
        if(
        (($this->uSes->access(8) || $this->uSes->access(9)) && $this->request['escalated'])
        || ($this->request['user_id'] == $this->is_com_admin && !$this->request['escalated'])) {
            if ($this->request['tic_status'] == 'req_open') {//if request not read yet - set processing status
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSup")->prepare("UPDATE
                u235_requests
                SET
                tic_status='req_processing',
                tic_changed_timestamp=:tic_changed_timestamp
                WHERE
                tic_id=:tic_id AND
                tic_status='req_open' AND
                site_id=:site_id
                ");
                    $tic_changed_timestamp = time();
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_changed_timestamp', $tic_changed_timestamp, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_id', $this->request['tic_id'], PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('110'/*.$e->getMessage()*/);
                }

                $this->request['tic_status'] = 'req_processing';
            } elseif ($this->request['tic_status'] == 'case_open') {//if request not read yet - set processing status
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSup")->prepare("UPDATE
                u235_requests
                SET
                tic_status='case_processing',
                tic_changed_timestamp=:tic_changed_timestamp
                WHERE
                tic_id=:tic_id AND
                tic_status='case_open' AND
                site_id=:site_id
                ");
                    $tic_changed_timestamp = time();
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_changed_timestamp', $tic_changed_timestamp, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':tic_id', $this->request['tic_id'], PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('120'/*.$e->getMessage()*/);
                }

                $this->request['tic_status'] = 'case_processing';
            }
        }
    }

    private function get_time_logs() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            rec_id,
            time_spent,
            comment,
            user_id,
            timestamp
            FROM
            u235_requests_time
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->request['tic_id'],PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->q_requests_time[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSup=new common($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->check_data();
        if($this->check_access()) {
            $this->has_access=true;
            $this->set_labels();
            $this->get_req_cat_title();
            $this->make_tmp_id_for_new_msg();
            $this->get_req_messages();
            $this->update_req_status2processing();
            $this->get_req_cons_info();
            if((int)$this->request['company_id']) {
                $com_info=$this->uSup->com_id2com_info($this->request['company_id'],"com_title,two_level");
                $this->com_title=uString::sql2text($com_info->com_title,1);
                $this->two_level=(int)$com_info->two_level;
            }
            else {
                $this->com_title="";
                $this->two_level=0;
            }

            if($this->is_consultant||$this->is_operator) {
//                $this->get_cat_list();
                $this->get_time_logs();
                $this->get_cons_users();
            }
            if($this->two_level&&$this->is_com_admin&&!(int)$this->request['escalated']) {
                $this->get_cons_com_admins();
            }

            $this->user_avatar=new \uAuth_avatar($this->uCore);
        }
    }
}

$uSupport=new request_show($this);


if(!isset($_POST['in_dialog'])) {
    ob_start();
}

if($uSupport->uSes->access(2)) {
    if($uSupport->has_access||$uSupport->readonly) {?>

        <?if(!isset($_POST['in_dialog'])) {
            //datepicker
            $this->uFunc->incCss(u_sroot.'js/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css');
            $this->uFunc->incJs(u_sroot.'js/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js');

                $this->uFunc->incJs(u_sroot.'uSupport/js/request_show.min.js');
                $this->uFunc->incJs(u_sroot.'uSupport/js/request_show_common.min.js');
            ?>
            <!--suppress HtmlUnknownTarget -->
            <a href="<?=u_sroot?>uSupport/requests" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Запросы</a>
        <?}?>
        <input type="hidden" id="uSupport_request_show_req_id" value="<?=$uSupport->request['tic_id']?>">
        <div class="uSup_request_show">

            <div style="float: right; display: table">
                <div class="btn-group" id="uSup_request_show_status_btn_group">
                    <?if($uSupport->request['tic_status']=='req_closed'||$uSupport->request['tic_status']=='case_closed') {
                        if($uSupport->is_operator||($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated'])) {
                            if($uSupport->request['tic_status']=='req_closed') {?>
                                <button class="btn btn-default" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'req_processing')">Открыть запрос заново</button>
                            <?}
                            else {?>
                                <button class="btn btn-default" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'case_processing')">Открыть кейс заново</button>
                            <?}
                        }
                        if($uSupport->is_consultant||$uSupport->is_operator) {
                            if($uSupport->request['uknowbase_solution_isset']=='0'&&$uSupport->request['uknowbase_no_solution_user_id']=='0') {?>
                                <button id="uSup_uKnowbase_btn1" onclick="uSup_req_show_common.set_solution(<?=$uSupport->request['tic_id']?>)" class="btn btn-warning">Назначить решение</button>
                            <?}
                            elseif($uSupport->request['uknowbase_no_solution_user_id']!='0') {
                                $user=$uSupport->user_id2info($uSupport->request['uknowbase_no_solution_user_id']);?>
                                <button id="uSup_uKnowbase_btn1" onclick="uSup_req_show_common.show_no_solution_reason(<?=$uSupport->request['uknowbase_no_solution_user_id']?>,'<?=rawurlencode($user['firstname'].' '.$user['lastname'])?>','<?=rawurlencode(uString::sql2text($uSupport->request['uknowbase_no_solution_reason']))?>',<?=$uSupport->request['tic_id']?>)" class="btn btn-warning">Решения нет</button>
                            <?}
                            else {?>
                                <button type="button" id="uSup_uKnowbase_btn1" onclick="uSup_req_show_common.open_solution(<?=$uSupport->request['tic_id']?>)" class="btn btn-default">Открыть решение</button>
                            <?}
                        }
                        else {
                            if($uSupport->request['uknowbase_solution_isset']!='0') {?>
                                <button type="button" id="uSup_uKnowbase_btn1" onclick="uSup_req_show_common.open_solution(<?=$uSupport->request['tic_id']?>)" class="btn btn-info">Есть готовое решение</button>
                            <?}
                        }
                    }

                    if($uSupport->request['tic_status']!='req_closed'&&$uSupport->request['tic_status']!='case_closed'&&!$uSupport->readonly) {
                        if(
                        ($uSupport->is_operator||$uSupport->request['user_id']==$uSupport->uSes->get_val("user_id")||$uSupport->is_com_admin)&&
                        ($uSupport->request['tic_status']=='req_open'||
                            $uSupport->request['tic_status']=='req_answered'||
                            $uSupport->request['tic_status']=='req_processing')
                        ) {?>
                            <button class="btn btn-danger" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'req_closed')">Закрыть запрос</button>
                        <? } if(
                            (
                                    $uSupport->is_operator&&(int)$uSupport->request['escalated']||
                                    ($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated'])
                            )&&
                            ($uSupport->request['tic_status']=='req_open'||
                                $uSupport->request['tic_status']=='req_answered'||
                                $uSupport->request['tic_status']=='req_processing'||
                                $uSupport->request['tic_status']=='req_closed'||
                                $uSupport->request['tic_status']=='req_processing'||
                                $uSupport->request['tic_status']=='req_processing')
                        ) {?>
                            <button class="btn btn-primary" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'case_open')">Открыть кейс</button>
                        <? } if(
                        ($uSupport->is_consultant||$uSupport->is_operator||
                            ($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated']))&&
                        ($uSupport->request['tic_status']=='case_open'||
                            $uSupport->request['tic_status']=='case_processing'||
                            $uSupport->request['tic_status']=='case_answered')
                        ) {?>
                            <button class="btn btn-success" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'case_done')">Кейс выполнен</button>
                        <? } if(
                            (
                                    (
                                            $uSupport->is_operator||
                                            ($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated'])
                                    )
                                    &&$uSupport->request['tic_status']=='case_done') || (
                                $uSupport->request['user_id']==$uSupport->uSes->get_val("user_id")&&(
                                    $uSupport->request['tic_status']=='case_open'||
                                    $uSupport->request['tic_status']=='case_done'||
                                    $uSupport->request['tic_status']=='case_processing'||
                                    $uSupport->request['tic_status']=='case_answered'||
                                    $uSupport->request['tic_status']=='case_done'
                                )
                            )
                        ) {?>
                            <button class="btn btn-danger" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'case_closed')">Закрыть кейс</button>
                        <?}?>
                    <?}
                    if(
                            ($uSupport->two_level&&!$uSupport->request["escalated"]&&$uSupport->is_com_admin)
                            ||
                            ($uSupport->two_level&&!$uSupport->request["escalated"]&&$uSupport->is_operator)
                            ||
                            (!$uSupport->two_level&&!$uSupport->request["escalated"]&&$uSupport->is_operator)
                    ) {?>
                        <button class="btn btn-primary" onclick="uSup_req_show_common.changeReqStatus(<?=$uSupport->request['tic_id']?>,'escalated')">Эскалировать</button>
                    <?}?>
                </div>
            </div>
            <div class="row">&nbsp;</div>

            <h1>
                <span id="uSup_request_show_req_status_<?=$uSupport->request['tic_id']?>">
                    <a href="<?=u_sroot.'uSupport/request_show/'.$uSupport->request['tic_id']?>" <?=$uSupport->status2Icon_ar[$uSupport->request['tic_status']]?><br>
                    <?=$uSupport->request['tic_id']?>
                    </a><?/*ТАК И ДОЛЖНО БЫТЬ - тэг закрывается в переменной $uSupport->status2Icon_ar*/?>
                </span>
                <span id="uSup_req_subj"><?=$uSupport->request['tic_subject']?></span>
                <?if($uSupport->is_operator&&
                $uSupport->request['tic_status']!='req_closed'&&$uSupport->request['tic_status']!='case_closed') {?><button class="btn btn-link uTooltip" title="Изменить заголовок" onclick="uSup_req_show_common.change_subject();"><span class="glyphicon glyphicon-pencil"></span></button><?}

                if(
                ($uSupport->request['tic_feedback_info']=='positive'||$uSupport->request['tic_feedback_info']=='negative'||$uSupport->request['tic_feedback_info']=='neutral')&&
                $uSupport->is_operator||$uSupport->is_consultant
                ){?>
                <button class="btn btn-default <?=($uSupport->request['tic_feedback_info']=='negative')?'btn-danger':''?> <?=($uSupport->request['tic_feedback_info']=='positive')?'btn-success':''?> uTooltip" title="Клиент оставил отзыв на этот запрос" onclick="uSup_req_show_common.show_feedback(<?=$uSupport->request['tic_id']?>)"><span class="glyphicon glyphicon-thumbs-<?=($uSupport->request['tic_feedback_info']=='negative')?'down':'up'?>"></span></button>
                <?}?>

                <?if($uSupport->request['tic_feedback_info']!='positive'&&$uSupport->request['tic_feedback_info']!='negative'&&$uSupport->request['tic_feedback_info']!='neutral'&&($uSupport->request['tic_status']=='req_closed'||$uSupport->request['tic_status']=='case_closed')){?>
                    <?if($uSupport->request['user_id']==$uSupport->uSes->get_val("user_id")) {?>
                        <button id="uSup_request_show_send_feedback_btn" class="btn btn-warning btn-sm uTooltip" title="Оцените качество техподдержки" onclick="uSup_req_show_common.send_feedback(<?=$uSupport->request['tic_id']?>)"><span class="glyphicon glyphicon-thumbs-up"></span></button>
                    <?}?>
                <?}?>
            </h1>
            <div class="row">
                <div class="col-md-4">
                    <b>Категория: </b>
                    <span id="uSup_req_cat" class="uSup_req_cat_title_<?=$uSupport->request['tic_cat']?>"><?=$uSupport->request['tic_cat_title']?></span>
                    <?if($uSupport->is_operator||$uSupport->is_consultant&&$uSupport->request['tic_status']!='req_closed'&&$uSupport->request['tic_status']!='case_closed') {?><button class="btn btn-link uTooltip" title="Изменить категорию" onclick="uSup_req_show_common.changeCat();"><span class="glyphicon glyphicon-pencil"></span></button>
                <?}?>
                </div>
                <?if($uSupport->request['company_id']!='0'){?>
                <div class="col-md-4">
                    <b>Компания: </b>
                    <span><?=$uSupport->com_title?></span>
                </div>
                <?}?>
                <div class="col-md-4">
                    <b>Статус запроса: </b>
                    <span id="uSup_requests_req_status_label_<?=$uSupport->request['tic_id']?>"><?=$uSupport->status2label_ar[$uSupport->request['tic_status']]?></span>
                </div>
            </div>

            <p><span class="text-muted"><?=date('d.m.Y H:i',$uSupport->request['tic_opened_timestamp']+$_SESSION['SESSION']['timezone_difference_isset_always']).' - '.date('d.m.Y H:i',$uSupport->request['tic_changed_timestamp']+$_SESSION['SESSION']['timezone_difference_isset_always'])?></span>

                <span id="uSup_request_show_cons_name_container" <?=($uSupport->request['tic_status']!='case_open'&&
                    $uSupport->request['tic_status']!='case_answered'&&
                    $uSupport->request['tic_status']!='case_done'&&
                    $uSupport->request['tic_status']!='case_processing')?'class="hidden"':''?>>Ответственный: <span class="text-muted">
            <span id="uSup_request_show_cons_name"><?=$uSupport->consData['firstname'].' '.$uSupport->consData['lastname']?></span>
                </span>
                    <?if($uSupport->is_consultant||$uSupport->is_operator||
                        ($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated'])
                    ){?>
                        <button class="btn btn-default btn-xs" onclick="uSup_req_show_common.setCons()">Сменить</button>
                    <?}?>
                    </span>
            </p>

        <table class="table table-striped" id="uSupport_request_show_table">
        <? for($i=0;$msgs=$uSupport->qMsg[$i];$i++) {
            $senderData=$uSupport->user_id2info($msgs['msg_sender']);
            $msgs['msg_text']=nl2br(htmlspecialchars(uString::sql2text($msgs['msg_text'],1)));?>

            <tr>
                <td>
                    <p><a target="_blank" href="<?=u_sroot?>uAuth/profile<?=$uSupport->uSes->access(13)?"_admin":""?>/<?=$senderData['user_id']?>"><?=uString::sql2text($senderData['firstname']).' '.uString::sql2text($senderData['lastname']);?></a><br>
                    <small class="text-muted"><?=date ( 'd.m.Y H:i' ,$msgs['msg_timestamp'])?></small></p>
                    <div><img class="avatar" src="<?=$uSupport->user_avatar->get_avatar('uSup_com_users_list',$senderData['user_id'],$senderData['avatar_timestamp'])?>"></div>
                </td>
                <td>
                <div class="username">
                </div>
                <div class="text">
                <?=$msgs['msg_text']?>
                </div>
                <? $qFiles=$uSupport->get_msg_files($msgs['msg_id']);
                if(count($qFiles)>1) { ?>
                    <div class="files">
                    <h4>Прикрепленные файлы:</h4>
                        <ul class="files">
                    <? for($j=0;$files=$qFiles[$j];$j++) {
                        if(!strpos('_'.$files->file_mime,'image')) {
                            $filename=uString::sql2text($files->filename,1)?>
                            <li title="<?=$filename?>, <?=$files->file_size?> байт" class="uTooltip">
                                <a href="<?=u_sroot.'uSupport/file/'.$files->file_id?>" target="_blank" class="img-thumbnail"><?=$filename?> <a href="<?=u_sroot.'uSupport/file/'.$files->file_id?>?download" class="btn btn-link uTooltip" title="Скачать файл"><span class="glyphicon glyphicon-download"></span></a></a>
                            </li>
                        <?}?>
                    <?}?>
                        </ul>
                        <ul class="images">
                    <?
                    for($j=0;$files=$qFiles[$j];$j++) {
                        if(strpos('_'.$files->file_mime,'image')) {
                            $filename=uString::sql2text($files->filename,1)?>
                            <li title="<?=$filename?>, <?=$files->file_size?> байт" class="uTooltip">
                                <a class="fancybox" rel="gallery1" href="<?=u_sroot.'uSupport/file/'.$files->file_id?>/img.jpg?<?=$files->timestamp?>" title="<?=$filename?>">
                                    <img src="<?=u_sroot.'uSupport/file/'.$files->file_id?>/sm?<?=$files->timestamp?>" alt="<?=$filename?>" class="img-thumbnail" />
                                </a>
                            </li>
                            <? if($files->file_size >= 10485760) {?>
                            <li>
                                <a id="uSup_btn_file_download" href="<?=u_sroot.'uSupport/file/'.$files->file_id?>/img.jpg?download" title="<?=$filename?>"><span class="btn-lg icon-floppy ic-download"></span></a>
                            </li>
                            <?}?>
                        <?}?>
                    <? }?>
                        </ul>
                    </div>
                <?}?>
                </td>
            </tr>
        <?}?>

        </table>

            <?if($uSupport->request['tic_status']!='req_closed'&&$uSupport->request['tic_status']!='case_closed'&&!$uSupport->readonly) {?>
                <div class="msg">&nbsp;</div>
                <div class="form-group">
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <textarea id="uSupport_new_msg_text" style="height:150px;" class="form-control col-sm-12" onchange="uSup_req_show_common.request_edited_init()" onclick="uSup_req_show_common.request_edited_init()"></textarea>
                    <p>&nbsp;</p>
                    <?$terms_link=$terms_link_closer="";
                    $terms_page_id=(int)$uSupport->uFunc->getConf("privacy_terms_text_id","content",1);
                    if($terms_page_id) {
                        $txt_obj=$uSupport->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
                        if($txt_obj) {
                            $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
                            $terms_link_closer = "</a>";
                        }
                    }?>
                    <p><?=$terms_link?> Нажимая на кнопку "Отправить", вы даете согласие на обработку своих персональных данных<?=$terms_link_closer?></p>
                    <button class="btn btn-primary" onclick="uSup_req_show_common.save_message()">Отправить</button>
                </div>

                <div id="uSupport_request_show_uploader"></div>
                <div id="uSupport_filelist" class="uSupport_filelist"></div>
            <?}?>

            <?if($uSupport->is_operator||$uSupport->is_consultant){?>
                <div class="time_logs">
                    <h3>Время
                        <button id="uSup_log_time_btn" class="btn btn-default btn-xs uTooltip" onclick="uSup_req_show_common.log_time()" title="Запишите потраченное время.">
                            <span class="glyphicon glyphicon-time"></span>
                        </button>
                    </h3>

                    <?if($uSupport->request['tic_time_spent']=='0'){?>
                    <p id="no_time_spent">Никто не списывал время на этот запрос.</p>
                    <?} ?>
                    <div id="uSup_time_spent_block" <?if($uSupport->request['tic_time_spent']=='0') echo 'style="display:none"';?>>
                        <p id="uSup_time_spent_line"><strong>Всего затрачено времени:</strong> <?$hours=floor($uSupport->request['tic_time_spent']/60);
                            $minutes=$uSupport->request['tic_time_spent']-$hours*60;
                            echo $hours.':'.$minutes?></p>
                        <table class="table table-striped table-hover table-condensed">
                            <tr>
                                <th>Дата</th>
                                <th>Сотрудник</th>
                                <th>Комментарий</th>
                                <th>Время</th>
                                <th></th>
                            </tr>
                        <?for($i=0;$time=$uSupport->q_requests_time[$i];$i++) {?>
                            <tr id="uSup_time_logs_rec_<?=$time->rec_id?>">
                                <td><?=date('d.m.Y H:i:s',$time->timestamp+$_SESSION['SESSION']['timezone_difference_isset_always'])?></td>
                                <td><?$user=$uSupport->user_id2info($time->user_id); echo $user['firstname'].' '.$user['secondname'].' '.$user['lastname']?></td>
                                <td><?=uString::sql2text($time->comment)?></td>
                                <td><? $hours=floor($time->time_spent/60);
                                    $minutes=$time->time_spent-$hours*60;
                                    echo $hours.':'.$minutes?></td>
                                <?if($time->user_id==$uSupport->uSes->get_val("user_id")||$uSupport->uSes->access(8)) {?>
                                <td><button class="btn btn-danger btn-xs" onclick="uSup_req_show_common.del_time_confirm(<?=$time->rec_id?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
                                <?}?>
                            </tr>
                        <?}?>
                        </table>
                    </div>
                </div>
            <?}

            if(!$uSupport->readonly) {?>
        <!--suppress ES6ModulesDependencies -->
            <script type="text/javascript">
                if(typeof uSup_req_show_common==="undefined") uSup_req_show_common={};
                if(typeof uSup==="undefined") uSup={};
            uSup_req_show_common.tic_id='<?=$uSupport->request['tic_id']?>';
            uSup_req_show_common.user_id=<?=$uSupport->uSes->get_val("user_id")?>;
            uSup_req_show_common.is_operator=<?=(int)$uSupport->is_operator?>;
            <? if($uSupport->uSes->access(8)) {?>
            uSup_req_show_common.tic_subject='<?=rawurlencode($uSupport->request['tic_subject'])?>';
            <?}?>
            //uSup_req_show_common.tic_cat='<?=$uSupport->request['tic_cat']?>';
            uSup_req_show_common.tic_status='<?=$uSupport->request['tic_status']?>';
            uSup_req_show_common.new_msg_id='<?=$uSupport->newMsg_id?>';

            <?
            if($uSupport->is_consultant||$uSupport->is_operator||
            ($uSupport->two_level&&$uSupport->is_com_admin&&!(int)$uSupport->request['escalated'])
            ) {?>
                if(typeof uSup_req_show_common.cons_id==="undefined") uSup_req_show_common.cons_id=[];
                if(typeof uSup_req_show_common.cons_name==="undefined") uSup_req_show_common.cons_name=[];

                <?for($i=0;$data=$uSupport->q_cons_list[$i];$i++) { ?>
                uSup_req_show_common.cons_id[<?=$i?>]=<?=$data->user_id?>;
                uSup_req_show_common.cons_name[<?=$i?>]=decodeURIComponent("<?=rawurlencode(uString::sql2text($data->firstname.' '.$data->lastname,1))?>");
                <?}
                if(!isset($_POST['in_dialog'])) {?>
                    uSup.req_force_write_noreason_sol=<?=$this->uFunc->getConf("req_force_write_noreason_sol","uSup")=='1'?'true':'false'?>;
                <?}
            }?>

            <?if($uSupport->request['tic_status']!='req_closed'&&$uSupport->request['tic_status']!='case_closed') {?>
                $(document).ready(function() {
                    uSup_req_show_common.openUploader();
                });
            <?}?>
        </script>
            <?}?>


        </div>
        <?if(!isset($_POST['in_dialog'])&&!$uSupport->readonly) {
            include_once 'inc/request_show_dialogs.php';
        }
    }
    else {
        if(!isset($_POST['in_dialog'])) {?>
        <div class="jumbotron">
            <h1 class="page-header">Техническая поддержка</h1>
            <p>Для того, чтобы создать запрос в техническую поддержку Ваша компания должна быть клиентом нашей технической поддержки.</p>
            <p>Если вы сотрудник такой компании, попросите своего администратора добавить вашу учетную запись к Вашей компании.</p>
        </div><?} else {?>forbidden<?}
    }
}
else {
    if(!isset($_POST['in_dialog'])) {?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div><?} else {?>forbidden<?}
}

if(!isset($_POST['in_dialog'])) {
    $this->page_content=ob_get_contents();
    ob_end_clean();

    /** @noinspection PhpIncludeInspection */
    include "templates/template.php";
}
