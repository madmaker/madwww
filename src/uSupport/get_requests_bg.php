<?
namespace uSupport;

use PDO;
use PDOException;
use processors\uFunc;
use uString;
use uSupport\common;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSupport/classes/common.php";

class get_requests {
    public $requsets;
    public $date_filter;
    public $com_filter;
    public $filter_hint_addition;
    public $two_level;
    public $uFunc;
    public $uSes;
    public $uSupport;
    public $uSup_settings;
    private $uCore;
    private $qu_status,$qu_comps,$catId2Title_ar,
        $user_id2name,$com_id2name;
    public $is_com_client,$is_com_admin,$is_consultant,$is_operator,$has_access,
        $has_requests,
        $status2Icon_ar,$page_num,$lines_per_page;

    private function check_access() {
        if(!isset($_SESSION['uSupport']['users_settings'])) {
            echo json_encode(array(
                'status' => 'forbidden',
                'msg'=>'1'
            ));
            exit;
        }

        $this->two_level=0;

        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=$this->has_access=false;
        //consultant or operator
        if($this->uSes->access(9)) {
            $this->is_operator=true;
            return true;
        }
        if($this->uSes->access(8)) {
            $this->is_consultant=true;
            return true;
        }
        //check if client of any company or admin
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT DISTINCT 
            u235_com_users.com_id,
            admin,
            two_level
            FROM
            u235_com_users
            JOIN 
            u235_comps
            ON 
            u235_comps.com_id=u235_com_users.com_id AND
            u235_comps.site_id=u235_com_users.site_id
            WHERE
            user_id=:user_id AND
            u235_com_users.site_id=:site_id
            ");
            $user_id=$this->uSes->get_val("user_id");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        $this->qu_comps="";
        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        while($com=$stm->fetch(PDO::FETCH_OBJ)) {
            if($com->admin=='1') {
                $this->is_com_admin=true;
            }
            $this->qu_comps.=" company_id='".$com->com_id."' OR ";
            if((int)$com->two_level) $this->two_level=1;
        }
        if(strlen($this->qu_comps)) {
            $this->qu_comps="(".$this->qu_comps." 1=0)";
            if(!$this->is_com_admin) $this->is_com_client=true;
            return true;
        }

        //check if we can receive request from users not in companies
        if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        return false;
    }

    private function set_status_labels() {
        //Статусы запросов
        $this->status2Icon_ar['req_open']=' class="btn btn-xs btn-info uTooltip" title="Запрос открыт">
        <span class="glyphicon glyphicon-envelope"></span>';
        $this->status2Icon_ar['req_answered']=' class="btn btn-xs btn-success uTooltip" title="Есть ответ на запрос">
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
    }
    private function define_status(){
        $req_open=$req_answered=$req_processing=$req_closed=0;

        $case_open=$case_answered=$case_processing=$case_done=$case_closed=0;

        if($this->is_com_admin||$this->is_consultant||$this->is_operator||$this->is_com_admin) {
            $requests=$cases=0;

            if($_SESSION['uSupport']['users_settings']['show_requests']=='1') $requests=1;
            if($_SESSION['uSupport']['users_settings']['show_cases']=='1') $cases=1;
        }
        else $requests=$cases=1;//com client or client with no com

        if($_SESSION['uSupport']['users_settings']['show_opened']=='1') {
            if($requests) $req_open=$req_processing=1;
            if($cases) $case_open=$case_processing=1;
        }
        if($_SESSION['uSupport']['users_settings']['show_answered']=='1') {
            if($requests) $req_answered=1;
            if($cases) $case_answered=1;
        }
        if($_SESSION['uSupport']['users_settings']['show_done']=='1') {
            if($cases) $case_done=1;
        }
        if($_SESSION['uSupport']['users_settings']['show_closed']=='1') {
            if($requests) $req_closed=1;
            if($cases) $case_closed=1;
        }

        $this->qu_status=" (".
            ($req_open?" tic_status='req_open' OR ":'').
            ($req_answered?" tic_status='req_answered' OR ":'').
            ($req_processing?" tic_status='req_processing' OR ":'').
            ($req_closed?" tic_status='req_closed' OR ":'').
            ($case_open?" tic_status='case_open' OR ":'').
            ($case_answered?" tic_status='case_answered' OR ":'').
            ($case_processing?" tic_status='case_processing' OR ":'').
            ($case_done?" tic_status='case_done' OR":'').
            ($case_closed?" tic_status='case_closed' OR ":'').
            " 1=0) ";
    }
    private function define_escalation() {
        if($this->is_operator) {
            $show_internal = $show_escalated = 0;

            if ($_SESSION['uSupport']['users_settings']['show_internal'] == '1') $show_internal = 1;
            if ($_SESSION['uSupport']['users_settings']['show_escalated'] == '1') $show_escalated = 1;

            $this->qu_status .= " AND (" .
                ($show_internal ? " escalated=0 OR " : '') .
                ($show_escalated ? " escalated=1 OR " : '') .
                " 1=0) ";
        }
        elseif($this->is_com_admin&&$this->two_level) {
            $show_internal = $show_escalated = 0;

            if ($_SESSION['uSupport']['users_settings']['show_internal'] == '1') $show_internal = 1;
            if ($_SESSION['uSupport']['users_settings']['show_escalated'] == '1') $show_escalated = 1;

            $this->qu_status .= " AND (" .
                ($show_internal ? " escalated=0 OR " : '') .
                ($show_escalated ? " escalated=1 OR " : '') .
                " 1=0) ";
        }
    }
    private function define_assignment() {

    }

    private function get_requests() {
        //DATE FILTER
        $date_filter="";
        if(isset($_POST['start_date'])) if(uString::isDigits($_POST['start_date'])) $start=(int)$_POST['start_date'];//:tic_opened_timestamp_start
        if(isset($_POST['stop_date'])) if(uString::isDigits($_POST['stop_date'])) $stop=$_POST['stop_date'];//:tic_opened_timestamp_stop

        if(isset($stop,$start)) $date_filter=" (tic_opened_timestamp>=:tic_opened_timestamp_start AND tic_opened_timestamp<=:tic_opened_timestamp_stop) AND ";
        elseif(isset($start)) $date_filter=" tic_opened_timestamp>=:tic_opened_timestamp_start AND ";
        elseif(isset($stop)) $date_filter=" tic_opened_timestamp<=:tic_opened_timestamp_stop AND ";

        //COMPANY FILTER
        $com_filter='';
        if(isset($_POST['com_id'])) {
            if(uString::isDigits($_POST['com_id'])&&(int)$_POST['com_id']) {
                if($this->is_com_client||$this->is_com_admin) {
                    //check if user belongs to this comp
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                        user_id
                        FROM
                        u235_com_users
                        WHERE
                        user_id=:user_id AND
                        com_id=:com_id AND
                        site_id=:site_id
                        ");
                        $user_id=$this->uSes->get_val("user_id");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $_POST['com_id'],PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                        /** @noinspection PhpUndefinedMethodInspection */
                        if($stm->fetch(PDO::FETCH_OBJ)) $com_filter=" company_id=:company_id AND " ;
                    }
                    catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
                }
                elseif($this->is_com_client||$this->is_com_admin||$this->is_operator||$this->is_consultant) $com_filter=" company_id=:company_id AND ";
            }
        }

        //OWNER FILTER
        $qu_owner='';
        if($this->is_operator||$this->is_consultant) $qu_owner='';
        elseif($this->is_com_admin) {//admin-client
            $show_mine=0;
            $show_others=0;
            if($_SESSION['uSupport']['users_settings']['show_mine']=='1') $show_mine=1;;
            if($_SESSION['uSupport']['users_settings']['show_others']) $show_others=1;

            if($show_mine&&!$show_others) $qu_owner="AND (user_id=:user_id)";
            elseif(!$show_mine&&$show_others) $qu_owner="AND (".$this->qu_comps." AND user_id!=:user_id)";
            elseif($show_mine&&$show_others||!$show_mine&&!$show_others) $qu_owner="AND (".$this->qu_comps." OR user_id=:user_id)";
        }
        else $qu_owner="AND (user_id=:user_id)";

        //ASSIGNMENT FILTER
        $qu_assignment='';
        if($this->is_operator||$this->is_consultant) {
            $show_assigned2me=0;
            $show_assigned2others=0;
            $show_unassigned=0;

            if($_SESSION['uSupport']['users_settings']['show_assigned2me']=='1') $show_assigned2me=1;
            if($_SESSION['uSupport']['users_settings']['show_assigned2others']=='1') $show_assigned2others=1;
            if($_SESSION['uSupport']['users_settings']['show_unassigned']=='1') $show_unassigned=1;

            if($show_assigned2me&&!$show_assigned2others&&!$show_unassigned) $qu_assignment=" AND (cons_id=:cons_id)";
            if(!$show_assigned2me&&$show_assigned2others&&!$show_unassigned) $qu_assignment=" AND (cons_id!=:cons_id AND cons_id!=0)";
            if(!$show_assigned2me&&!$show_assigned2others&&$show_unassigned) $qu_assignment=" AND (cons_id=0)";
            if($show_assigned2me&&$show_assigned2others&&!$show_unassigned) $qu_assignment=" AND (cons_id!=0)";
            if(!$show_assigned2me&&$show_assigned2others&&$show_unassigned) $qu_assignment=" AND (cons_id!=:cons_id OR cons_id=0)";
            if($show_assigned2me&&!$show_assigned2others&&$show_unassigned) $qu_assignment=" AND (cons_id=:cons_id OR cons_id=0)";
            if($show_assigned2me&&!$show_assigned2others&&$show_unassigned) $qu_assignment=" AND (cons_id=:cons_id OR cons_id=0)";
        }

        //Get requests
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            tic_id,
            company_id,
            tic_subject,
            cons_id,
            user_id,
            tic_opened_timestamp,
            tic_changed_timestamp,
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
            tic_status!='new' AND
            tic_confirmed='1' AND
            site_id=:site_id AND
            ".$date_filter.
                $com_filter.
                $this->qu_status.
                $qu_owner.
                $qu_assignment."
            ORDER BY
            FIELD (tic_status,
            'req_open',
            'case_open',
            'req_processing',
            'case_processing',
            'req_answered',
            'case_answered',
            'case_done',
            'case_closed',
            'req_closed'
            ),
            tic_changed_timestamp DESC LIMIT :page_num,:lines_per_page
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lines_per_page', $this->lines_per_page,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_num', $this->page_num,PDO::PARAM_INT);
            //timestamp filter
            if(isset($stop,$start)) {
                $date_filter_enabled=1;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_opened_timestamp_start', $start,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_opened_timestamp_stop', $stop,PDO::PARAM_INT);
            }
            elseif(isset($start)) {
                $date_filter_enabled=1;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_opened_timestamp_start', $start,PDO::PARAM_INT);
            }
            elseif(isset($stop)) {
                $date_filter_enabled=1;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_opened_timestamp_stop', $stop, PDO::PARAM_INT);
            }

            //company filter
            if(strlen($com_filter)) {
                $com_filter_enabled=1;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_id', $_POST['com_id'],PDO::PARAM_INT);
            }

            //owner filter
            if(strlen($qu_owner)) /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);

            //consultant filter
            /** @noinspection PhpUndefinedVariableInspection */
            if(strlen($qu_assignment)&&
                !(!$show_assigned2me&&!$show_assigned2others&&$show_unassigned)&&
                !($show_assigned2me&&$show_assigned2others&&!$show_unassigned)
            ) /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cons_id', $user_id,PDO::PARAM_INT);

            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $this->filter_hint_addition="";
            if(isset($date_filter_enabled)||isset($com_filter_enabled)) {
                $this->filter_hint_addition.=". С фильтром по ";
                if(isset($date_filter_enabled,$com_filter_enabled)) $this->filter_hint_addition.=" компании и дате запроса";
                elseif(isset($date_filter_enabled)) $this->filter_hint_addition.=" дате запроса";
                elseif(isset($com_filter_enabled)) $this->filter_hint_addition.=" компании";
            }
        }
        catch(PDOException $e) {$this->uFunc->error('30'.$e->getMessage());}

        /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        for($i=0; $this->requsets[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++);

        if(count($this->requsets[$i])>1) {
            if($this->is_consultant||$this->is_operator) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                    COUNT(tic_id)
                    FROM
                    u235_requests
                    WHERE
                    tic_status!='new' AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
            }
            elseif($this->is_com_admin) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                    COUNT(tic_id)
                    FROM
                    u235_requests
                    WHERE
                    tic_status!='new' AND
                    (".$this->qu_comps." OR user_id=:user_id) AND
                    site_id=:site_id
                    ");
                    $user_id=$this->uSes->get_val("user_id");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
            }
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                    COUNT(tic_id)
                    FROM
                    u235_requests
                    WHERE
                    tic_status!='new' AND
                    user_id=:user_id AND
                    site_id=:site_id
                    ");
                    $user_id=$this->uSes->get_val("user_id");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
            }
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_ASSOC);
            if($qr['COUNT(tic_id)']>0) $this->has_requests=true;
            else $this->has_requests=false;
        }
    }
    public function userId2names($user_id) {
        if(!isset($this->user_id2name[$user_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
                firstname,
                lastname
                FROM
                u235_users
                WHERE
                user_id=:user_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if($user=$stm->fetch(PDO::FETCH_OBJ)) $this->user_id2name[$user_id]=uString::sql2text($user->firstname,1).' '.uString::sql2text($user->lastname);
            else $this->user_id2name[$user_id]='not_found';
        }
        return $this->user_id2name[$user_id];
    }
    public function com_id2title($com_id) {
        if(!isset($this->com_id2name[$com_id])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                com_title
                FROM
                u235_comps
                WHERE
                site_id=:site_id AND
                com_id=:com_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if($com=$stm->fetch(PDO::FETCH_OBJ)) $this->com_id2name[$com_id]=uString::sql2text($com->com_title,1);
            else $this->com_id2name[$com_id]='Компания не найдена';
        }
        return $this->com_id2name[$com_id];
    }
    public function catId2Title($cat_id) {
        if(!isset($this->catId2Title_ar[$cat_id])) {
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
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if($data=$stm->fetch(PDO::FETCH_OBJ)) {
                $cat_title=$data->cat_title;
                $this->catId2Title_ar[$cat_id]=$cat_title;
                return $cat_title;
            }
            return '';
        }
        return $this->catId2Title_ar[$cat_id];
    }
    private function print_requests_list() {
        if($this->uSes->access(2)) {
            if($this->has_access) {
                if(count($this->requsets)>1) {
                    ob_start();

                         for($i=0;$request=$this->requsets[$i];$i++) {
                            $request->tic_subject=uString::sql2text($request->tic_subject); //Деконвертируем заголовки тикетов?>
                            <tr id="tr_tic_<?=$request->tic_id?>">
                                <td id="uSup_requests_req_status_<?=$request->tic_id?>">
                                    <button onclick="uSup.show_request(<?=$request->tic_id?>)"<?=$this->status2Icon_ar[$request->tic_status]?><br><?=$request->tic_id?></button>
                                    <?if((int)$request->escalated) {?><span class="icon-angle-double-up"></span> <?}?>
                                </td>
                                <td>
                                    <a id="uSup_tic_subject_<?=$request->tic_id?>" href="<?=u_sroot?>uSupport/request_show/<?=$request->tic_id?>" onclick="uSup.show_request(<?=$request->tic_id?>); return false;"><?=$request->tic_subject?></a><br>
                                    <div class="pull-left">
                                        <div class="btn-group" id="uSup_requests_tic_btn_group_<?=$request->tic_id?>">
                                            <?
                                            if($this->is_consultant||$this->is_operator) {
                                                if($request->tic_status=='req_closed'||$request->tic_status=='case_closed') {
                                                    if($request->tic_feedback_info=='positive'||$request->tic_feedback_info=='negative'||$request->tic_feedback_info=='neutral'){?>
                                                        <button class="btn btn-default btn-xs <?=($request->tic_feedback_info=='negative')?'btn-danger':''?> <?=($request->tic_feedback_info=='positive')?'btn-success':''?> uTooltip pull-left" title="Клиент оставил отзыв на этот запрос" onclick="uSup_req_show_common.show_feedback(<?=$request->tic_id?>)">
                                                            <span class="glyphicon glyphicon-thumbs-<?=($request->tic_feedback_info=='negative')?'down':'up'?>"></span>
                                                        </button>
                                                    <?}?>
                                                    <?if($request->uknowbase_solution_isset=='0'&&$request->uknowbase_no_solution_user_id=='0') {?>
                                                        <button onclick="uSup_req_show_common.set_solution(<?=$request->tic_id?>)" class="uknowbase_rec_btn btn btn-default btn-xs btn-warning uTooltip" title="Назначьте решение в базе знаний!">
                                                            <span class="glyphicon glyphicon-book"></span>
                                                        </button>
                                                    <?}
                                                    elseif($request->uknowbase_no_solution_user_id!='0') {
                                                        $user=$this->userId2names($request->uknowbase_no_solution_user_id);?>
                                                        <button onclick="uSup_req_show_common.show_no_solution_reason(<?=$request->uknowbase_no_solution_user_id?>,'<?=rawurlencode($user)?>','<?=rawurlencode(uString::sql2text($request->uknowbase_no_solution_reason))?>',<?=$request->tic_id?>)" class="uknowbase_rec_btn btn btn-default btn-xs uTooltip" title="Консультант не стал создавать решение в базе знаний">
                                                            <span class="glyphicon glyphicon-book"></span>
                                                        </button>
                                                    <?}
                                                    else {?>
                                                        <button type="button" onclick="uSup_req_show_common.open_solution(<?=$request->tic_id?>)" class="uknowbase_rec_btn btn btn-default btn-xs uTooltip" title="Есть решение в базе знаний.">
                                                            <span class="glyphicon glyphicon-book"></span>
                                                        </button>
                                                    <?}
                                                }?>
                                                <button id="uSup_log_time_btn_<?=$request->tic_id?>" class="btn btn-default <?if($request->tic_time_spent!='0') echo 'btn-info';?> btn-xs uTooltip" data-toggle="modal" onclick="uSup_req_show_common.log_time(<?=$request->tic_id?>)" title="<?if($request->tic_time_spent=='0') echo 'Запишите потраченное время'; else {
                                                    $hours=floor($request->tic_time_spent/60);
                                                    $minutes=$request->tic_time_spent-$hours*60;
                                                    echo 'Всего затрачено времени: '.$hours.':'.$minutes;}?>">
                                                    <span class="glyphicon glyphicon-time"></span>
                                                </button>
                                            <?}
                                            else {//CLIENT
                                                if(($request->tic_status=='req_closed'||$request->tic_status=='case_closed')){
                                                    if(($request->tic_feedback_info!='negative'&&$request->tic_feedback_info!='positive'&&$request->tic_feedback_info!='neutral'&&$request->user_id==$this->uSes->get_val("user_id"))){?>
                                                        <button id="uSup_send_feedback_btn_<?=$request->tic_id?>" class="btn btn-warning btn-xs uTooltip" title="Оцените качество поддержки" onclick="uSup_req_show_common.send_feedback(<?=$request->tic_id?>)"><span class="glyphicon glyphicon-thumbs-up"></span></button>
                                                    <?}
                                                    if($request->uknowbase_solution_isset=='1') {?>
                                                        <button type="button" onclick="uSup_req_show_common.open_solution(<?=$request->tic_id?>)" class="uknowbase_rec_btn btn btn-default btn-xs uTooltip" title="Есть решение в базе знаний."><span class="glyphicon glyphicon-book"></span></button>
                                                    <?}?>
                                                <?}
                                            }?>

                                        </div>
                                    </div>&nbsp;
                                    <span class="text-muted">
                                <?$subject=$this->catId2Title($request->tic_cat);
                                if(!empty($subject)) echo '<b id="uSup_tic_cat_'.$request->tic_id.'" class="uSup_req_cat_title_'.$request->tic_cat.'">'.$this->catId2Title($request->tic_cat).'</b>, ';
                                if($this->is_operator||$this->is_consultant||$this->is_com_admin) {//we must show author's name and company if exists
                                    $user_name=$this->userId2names($request->user_id);
                                    if($user_name!='not_found') {?>
                                        <a target="_blank" href="<?=u_sroot?>uAuth/profile<?=$this->uSes->access(13)?"_admin":""?>/<?=$request->user_id?>"><?=$user_name?></a>,
                                        <?if($request->company_id!='0'){?><a target="_blank" href="<?=u_sroot?>uSupport/admin_com_info/<?=$request->company_id?>"><?=$this->com_id2title($request->company_id)?></a><?}?>
                                    <?}
                                }
                                //we must show consultant's name
                                if($request->user_id!='0') {
                                    $user_name=$this->userId2names($request->cons_id);
                                    if($user_name!='not_found') {?>
                                        | Ответственный: <a target="_blank" href="<?=u_sroot?>uAuth/profile<?=$this->uSes->access(13)?"_admin":""?>/<?=$request->cons_id?>"><?=$user_name?></a>
                                    <?}
                                }?>
                                        <?=date ('d.m.Y H:i' ,$request->tic_changed_timestamp+$_SESSION['SESSION']['timezone_difference_isset_always'])?>
                            </span>
                                </td>
                            </tr>
                        <? }
                    $content=ob_get_contents();
                    ob_end_clean();
                    return $content;
                }
                else {
                    if($this->has_requests) {
                        die ("{
                        'status':'error',
                        'msg':'no requests',
                        'request shown':'".rawurlencode($this->uSupport->define_requests_shown_tip($this->uSup_settings)).$this->filter_hint_addition."'
                        }");
                    }

                    else {
                        die("{
                        'status':'error',
                        'msg':'no requests',
                        'request shown':'".rawurlencode($this->uSupport->define_requests_shown_tip($this->uSup_settings)).$this->filter_hint_addition."'
                        }");
                    }
                }
            }
            else {
                echo json_encode(array(
                    'status' => 'forbidden',
                    'msg'=>'2'
                ));
                exit;
            }
        }
    }
    public function __construct (&$uCore) {
        $start_time=time();
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSupport=new common($this->uCore);

        $this->uSup_settings=&$_SESSION['uSupport']['users_settings'];

        if($this->uSes->access(2)) {
            if($this->check_access()){

                if(isset($_POST["page_num"])) {
                    $this->lines_per_page = 5000;//Количество показанных строк [В дальнейшем параметр настрйки]
                    $this->page_num=(int)$_POST["page_num"] * (int)$this->lines_per_page;
                    if(isset($_POST["auto_update"])) {
                        if ($_POST["auto_update"] == 1 || $_POST["auto_update"] == '1') {
                            $this->page_num = 0;
                            $tmp = (int)$_POST["page_num"] + 1;
                            $this->lines_per_page = $tmp * (int)$this->lines_per_page;
                        }
                    }
                }
                else {
                    $this->lines_per_page = 50;
                    $this->page_num = 0;
                }

                $this->has_access=true;

                $this->define_status();
                $this->define_escalation();
                $this->define_assignment();
                $this->set_status_labels();
                $this->get_requests();

                $content=$this->print_requests_list();

                echo "{
                'status':'done',
                'time_spent':'".(time()-$start_time)."',
                'count':'".count($this->requsets)."',
                'requests':'".rawurlencode($content)."',
                'request shown':'".rawurlencode($this->uSupport->define_requests_shown_tip($this->uSup_settings)).$this->filter_hint_addition."'
                }";
            }
            else {
                echo json_encode(array(
                    'status' => 'forbidden',
                    'msg'=>'3'
                ));
                exit;
            }
        }
        else {
            echo json_encode(array(
                'status' => 'forbidden',
                'msg'=>'4'
            ));
            exit;
        }
    }
}

new get_requests($this);