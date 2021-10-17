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

class requests {
    public $uSupport;
    public $uFunc;
    public $uSes;
    public $two_level;
    private $uCore;
    private $qu_comps;
    public $is_com_client,$is_com_admin,$is_consultant,$is_operator,$has_access,
        $companies_count,$def_com_id,
        $req_open_count,$req_answered_count,$req_closed_count,$req_done_count,$req_assigned2me_count,$req_assigned2others_count,$req_unassigned_count,$req_requests_count,$req_cases_count,$req_mine_count,$req_others_count,
        $qCompList,$q_cons_list,$qCatList,$qCom_users_list,$qUserList;

    private function set_users_default_settings() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO 
            u235_users_settings (
            user_id,
            show_opened,
            show_answered,
            show_done,
            show_closed,
            show_requests,
            show_cases,
            show_mine,
            show_others,
            show_assigned2me,
            show_assigned2others,
            show_unassigned
            ) VALUES (
            :user_id,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1,
            1
            )
            ");
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function get_users_settings() {
        $uSup_user_settings=$this->uSes->get_val("uSup_user_settings");
        if(!$uSup_user_settings) {
            $this->set_users_default_settings();
            return 0;
        }
        return 1;
    }

    private function check_access() {
        $this->companies_count=0;
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=$this->has_access=$this->two_level=false;
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
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        for($i=0; $com[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};

        if($this->companies_count=$i) {
            $this->qu_comps="(";
            for($i=0;$com[$i];$i++) {
                if($this->companies_count===1) $this->def_com_id=$com[$i]->com_id;
                if($com[$i]->admin=='1') $this->is_com_admin=true;
                if((int)$com[$i]->two_level) $this->two_level=1;
                $this->qu_comps.=" company_id='".$com[$i]->com_id."' OR ";
            }
            $this->qu_comps.="1=0)";
            if(!$this->is_com_admin) $this->is_com_client=true;
            return true;
        }
        //check if we can receive request from users not in companies
        if($this->uFunc->getConf("receive_only_from_comps_users","uSup")=='0') return true;
        return false;
    }

    public function userId2names($user_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            firstname
            lastname
            FROM
            u235_users
            JOIN
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id
            WHERE
            u235_users.user_id=:user_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    public function com_id2title($com_id) {
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_OBJ);
            return uString::sql2text($qr->com_title,1);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_comList() {
        if($this->is_consultant||$this->is_operator) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
                com_id,
                com_title
                FROM
                u235_comps
                WHERE
                site_id=:site_id
                ORDER BY
                com_title ASC
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        elseif($this->is_com_admin||$this->is_com_client) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT DISTINCT
                u235_comps.com_id,
                com_title
                FROM
                u235_comps
                JOIN 
                u235_com_users
                ON
                u235_comps.com_id=u235_com_users.com_id AND
                u235_comps.site_id=u235_com_users.site_id
                WHERE
                u235_comps.site_id=:site_id AND
                u235_com_users.user_id=:user_id
                ORDER BY
                com_title ASC
                ");
                $site_id=site_id;
                $user_id=$this->uSes->get_val("user_id");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        }
//        else $this->uFunc->error(70);

        /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(isset($stm)) {
            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->qCompList[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        else $this->qCompList=[];
//        if(count($this->qCompList)<2) $this->uFunc->error(80);
    }
    private function get_cons_users() {
        //get consultants and operators
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
            u235_usersinfo.user_id=u235_users.user_id
            JOIN
            u235_usersinfo_groups
            ON
            u235_usersinfo_groups.user_id=u235_usersinfo.user_id AND
            u235_usersinfo_groups.site_id=u235_usersinfo.site_id
            WHERE
            u235_users.status='active' AND
            u235_usersinfo.status='active' AND
            (group_id='4' OR group_id='5') AND
            u235_usersinfo.site_id=:site_id
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->q_cons_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
            if(count($this->q_cons_list)<2) $this->uFunc->error(90);
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    private function get_comp_users_list() {
        //Достаем список пользователей компаний
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            user_id,
            com_id
            FROM
            u235_com_users
            WHERE
            site_id=:site_id
            ORDER BY
            com_id ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->qCom_users_list[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
//            if(count($this->qCom_users_list)<2) $this->uFunc->error(110);
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        $q_user_ids='';
        for($i=0;$user=$this->qCom_users_list[$i];$i++) {
            $q_user_ids.=" u235_users.user_id='".$user->user_id."' OR ";
        }
        $q_user_ids.='1=0';


        //Достаем список пользователей
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            u235_users.user_id,
            firstname,
            secondname,
            lastname
            FROM
            u235_users
            JOIN
            u235_usersinfo
            ON 
            u235_users.user_id=u235_usersinfo.user_id AND
            u235_users.status=u235_usersinfo.status
            WHERE
            (".$q_user_ids.") AND
            u235_users.status='active' AND
            site_id=:site_id
            ORDER BY
            firstname
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->qUserList[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }

//    private function get_counts() {
//        if($this->is_consultant||$this->is_operator) {
//            //statuses
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_open' OR
//                tic_status='req_processing' OR
//                tic_status='case_open' OR
//                tic_status='case_processing'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_open_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_answered' OR
//                tic_status='case_answered'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_answered_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_closed' OR
//                tic_status='case_closed'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_closed_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='case_done'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_done_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
//
//            //assignment
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                cons_id=:cons_id
//                ) AND
//                (
//                tic_status!='new'
//                ) AND
//                site_id=:site_id
//                ");
//                $cons_id=$this->uSes->get_val("user_id");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cons_id', $cons_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_assigned2me_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                cons_id!=:cons_id AND
//                cons_id!=0
//                )AND
//                (
//                tic_status!='new'
//                ) AND
//                site_id=:site_id
//                ");
//                $cons_id=$this->uSes->get_val("user_id");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cons_id', $cons_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_assigned2others_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                cons_id=0
//                )AND
//                (
//                tic_status!='new'
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_unassigned_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}
//
//            //req type
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_open' OR
//                tic_status='req_answered' OR
//                tic_status='req_processing'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_requests_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='case_open' OR
//                tic_status='case_answered' OR
//                tic_status='case_processing' OR
//                tic_status='case_done'
//                )AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_cases_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('220'/*.$e->getMessage()*/);}
//        }
//        elseif($this->is_com_admin) {
//            //statuses
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_open' OR
//                tic_status='req_processing' OR
//                tic_status='case_open' OR
//                tic_status='case_processing'
//                ) AND
//                (
//                user_id=:user_id OR
//                ".$this->qu_comps."
//                ) AND
//                site_id=:site_id
//                ");
//                $user_id=$this->uSes->get_val("user_id");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_open_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('230'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_answered' OR
//                tic_status='case_answered'
//                )AND
//                (
//                user_id=:user_id OR
//                ".$this->qu_comps."
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_answered_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('240'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_closed' OR
//                tic_status='case_closed'
//                )AND
//                (
//                user_id=:user_id OR
//                ".$this->qu_comps."
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_closed_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('250'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='case_done'
//                )AND
//                (
//                user_id=:user_id OR
//                ".$this->qu_comps."
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_done_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('260'/*.$e->getMessage()*/);}
//
//            //ownership
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                user_id=:user_id
//                ) AND
//                (
//                tic_status!='new'
//                ) AND
//                site_id=:site_id
//                ");
//                $user_id=$this->uSes->get_val("user_id");
//                $site_id=site_id;
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_mine_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('270'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                user_id!=:user_id AND
//                ".$this->qu_comps."
//                )AND
//                (
//                tic_status!='new'
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_others_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('280'/*.$e->getMessage()*/);}
//        }
//        else {
//            //statuses
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_open' OR
//                tic_status='req_processing' OR
//                tic_status='case_open' OR
//                tic_status='case_processing'
//                ) AND
//                (
//                user_id=:user_id
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_open_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('290'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_answered' OR
//                tic_status='case_answered'
//                )AND
//                (
//                user_id=:user_id
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_answered_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('300'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='req_closed' OR
//                tic_status='case_closed'
//                )AND
//                (
//                user_id=:user_id
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_closed_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('310'/*.$e->getMessage()*/);}
//
//            try {
//                /** @noinspection PhpUndefinedMethodInspection */
//                $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
//                COUNT(tic_id)
//                FROM
//                u235_requests
//                WHERE
//                (
//                tic_status='case_done'
//                )AND
//                (
//                user_id=:user_id
//                ) AND
//                site_id=:site_id
//                ");
//                $site_id=site_id;
//                $user_id=$this->uSes->get_val("user_id");
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//                /** @noinspection PhpUndefinedMethodInspection */
//                $qr=$stm->fetch(PDO::FETCH_ASSOC);
//                $this->req_done_count=$qr['COUNT(tic_id)'];
//            }
//            catch(PDOException $e) {$this->uFunc->error('320'/*.$e->getMessage()*/);}
//        }
//    }

    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSupport=new common($this->uCore);

        if($this->uSes->access(2)) {
            if($this->check_access()) {
                $this->has_access=true;
                $this->get_users_settings();
                $this->get_comList();
                $this->get_comp_users_list();
                $this->get_cons_users();
//                $this->get_counts();
            }
        }
    }
}

$uSupport=new requests($this);

ob_start();
if(!isset($this->uCore)) $this->uCore=&$this;
if($uSupport->uSes->access(2)) {
    if($uSupport->has_access) {
        $uSup_settings=$uSupport->uSes->get_val("uSup_user_settings");

        //datepicker
        $this->uFunc->incCss(u_sroot.'js/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css');
        $this->uFunc->incJs(u_sroot.'js/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',1);

        //plupload dropbox
        $this->uFunc->incJs(u_sroot.'js/plupload/js/jquery.plupload.dropbox/jquery.plupload.queue.min.js',1);

        $this->uFunc->incJs(u_sroot.'uSupport/js/requests.min.js',1);
        $this->uFunc->incJs(u_sroot.'uSupport/js/request_show_common.min.js',1);
        $this->uFunc->incCss(u_sroot."templates/site_".site_id."/css/u235/u235_common.css");
        ?>
        <button id="uSup_filter_btn" type="button" class="btn btn-default btn-sm pull-left" data-toggle="modal" data-target="#uSup_filter_dg">Фильтр</button>
        <button class="btn btn-default btn-sm pull-right uTooltip" style="margin-left: 20px;" title="Обновить список запросов" onclick="uSup.load_requests(false)"><span class="glyphicon glyphicon-refresh"></span></button>

        <form class="<?/*form-inline*/?>" action="<?=u_sroot?>uSupport/requests_search" style="display: block">
            <div class="input-group">
                <input name="search" placeholder="Поиск по запросам" class="form-control input-sm" value="<?=(isset($_GET['search'])?$_GET['search']:'')?>">
            <span class="input-group-btn">
                <button class="btn btn-default btn-sm">
                    <span class="glyphicon glyphicon-search"></span>
                </button>
            </span>
            </div>
        </form>

        <div class="row">&nbsp;</div>

        <h4>Какие запросы показывать?</h4>
        <div class="row">
        <?if($uSupport->is_operator||$uSupport->is_consultant||$uSupport->is_com_admin) {?>
                <div class="col-md-3">
                    <b>Тип запроса</b><br>
                    <label><input onchange="uSup.change_users_settings('show_requests');" type="checkbox" id="uSup_user_settings_btn_show_requests" <?=(int)$uSup_settings['show_requests']?'checked':''?> class="uSup_user_settings_btn"> Запросы</label>
                    <br>
                    <label><input onchange="uSup.change_users_settings('show_cases')" type="checkbox" id="uSup_user_settings_btn_show_cases" <?=(int)$uSup_settings['show_cases']?'checked':''?> class="uSup_user_settings_btn"> Кейсы</label>
                </div>
            <?/*<div class="btn-group">
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_requests']=='1'?'active':''?>" id="uSup_user_settings_btn_show_requests" onclick="uSup.change_users_settings('show_requests')" data-loading-text="<?=$uSupport->req_requests_count?><br>Запросы" title="Если вкл, то отображаются Запросы"><?=$uSupport->req_requests_count?><br>Запросы</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_cases']=='1'?'active':''?>" id="uSup_user_settings_btn_show_cases" onclick="uSup.change_users_settings('show_cases')" data-loading-text="<?=$uSupport->req_cases_count?><br>Кейсы" title="Если вкл, то отображаются Кейсы"><?=$uSupport->req_cases_count?><br>Кейсы</button>
        </div>*/?>
        <?}?>
            <div class="col-md-2">
                <b>Статус</b><br>
                <label><input onchange="uSup.change_users_settings('show_opened');" type="checkbox" id="uSup_user_settings_btn_show_opened" <?=(int)$uSup_settings['show_opened']?'checked':''?> class="uSup_user_settings_btn"> Открытые</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_answered')" type="checkbox" id="uSup_user_settings_btn_show_answered" <?=(int)$uSup_settings['show_answered']?'checked':''?> class="uSup_user_settings_btn"> Отвеченные</label>
            </div>
            <div class="col-md-3">
                <b>&nbsp;</b><br>
                <label><input onchange="uSup.change_users_settings('show_done')" type="checkbox" id="uSup_user_settings_btn_show_done" <?=(int)$uSup_settings['show_done']?'checked':''?> class="uSup_user_settings_btn"> Выполненные</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_closed')" type="checkbox" id="uSup_user_settings_btn_show_closed" <?=(int)$uSup_settings['show_closed']?'checked':''?> class="uSup_user_settings_btn"> Закрытые</label>
            </div>
        <?/*<div class="btn-group">
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_opened']=='1'?'active':''?>" id="uSup_user_settings_btn_show_opened" onclick="uSup.change_users_settings('show_opened')" data-loading-text="<?=$uSupport->req_open_count?><br>Открытые" title="Если вкл, то отображаются Открытые"><?=$uSupport->req_open_count?><br>Открытые</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_answered']=='1'?'active':''?>" id="uSup_user_settings_btn_show_answered" onclick="uSup.change_users_settings('show_answered')" data-loading-text="<?=$uSupport->req_answered_count?><br>Отвеченные" title="Если вкл, то отображаются Отвеченные"><?=$uSupport->req_answered_count?><br>Отвеченные</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_done']=='1'?'active':''?>" id="uSup_user_settings_btn_show_done" onclick="uSup.change_users_settings('show_done')" data-loading-text="<?=$uSupport->req_done_count?><br>Выполненные" title="Если вкл, то отображаются Выполненные"><?=$uSupport->req_done_count?><br>Выполненные</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_closed']=='1'?'active':''?>" id="uSup_user_settings_btn_show_closed" onclick="uSup.change_users_settings('show_closed')" data-loading-text="<?=$uSupport->req_closed_count?><br>Закрытые" title="Если вкл, то отображаются Закрытые"><?=$uSupport->req_closed_count?><br>Закрытые</button>
        </div>*/?>
        <?if($uSupport->is_com_admin) {?>
            <div class="col-md-2">
                <b>Автор</b><br>
                <label><input onchange="uSup.change_users_settings('show_mine');" type="checkbox" id="uSup_user_settings_btn_show_mine" <?=(int)$uSup_settings['show_mine']?'checked':''?> class="uSup_user_settings_btn"> От меня</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_others');" type="checkbox" id="uSup_user_settings_btn_show_others" <?=(int)$uSup_settings['show_others']?'checked':''?> class="uSup_user_settings_btn"> От других</label>
            </div>
        <?/*<div class="btn-group">
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_mine']=='1'?'active':''?>" id="uSup_user_settings_btn_show_mine" onclick="uSup.change_users_settings('show_mine')" data-loading-text="<?=$uSupport->req_mine_count?><br>От меня" title="Если вкл, то отображаются отправленные Вами"><?=$uSupport->req_mine_count?><br>От меня</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_others']=='1'?'active':''?>" id="uSup_user_settings_btn_show_others" onclick="uSup.change_users_settings('show_others')" data-loading-text="<?=$uSupport->req_others_count?><br>От других" title="Если вкл, то отображаются отправленные другими пользователями Вашей компании"><?=$uSupport->req_others_count?><br>От других</button>
        </div>*/?>
        <?}?>
        <?if($uSupport->is_operator||$uSupport->is_consultant) {?>
            <div class="col-md-2">
                <b>Ответственный</b><br>
                <label><input onchange="uSup.change_users_settings('show_assigned2me');" type="checkbox" id="uSup_user_settings_btn_show_assigned2me" <?=(int)$uSup_settings['show_assigned2me']?'checked':''?> class="uSup_user_settings_btn"> Мои</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_unassigned');" type="checkbox" id="uSup_user_settings_btn_show_unassigned" <?=(int)$uSup_settings['show_unassigned']?'checked':''?> class="uSup_user_settings_btn"> Неназначенные</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_assigned2others');" type="checkbox" id="uSup_user_settings_btn_show_assigned2others" <?=(int)$uSup_settings['show_assigned2others']?'checked':''?> class="uSup_user_settings_btn"> Чужие</label>
            </div>
        <?/*<div class="btn-group">
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_assigned2me']=='1'?'active':''?>" id="uSup_user_settings_btn_show_assigned2me" onclick="uSup.change_users_settings('show_assigned2me')" data-loading-text="<?=$uSupport->req_assigned2me_count?><br>Мои" title="Если вкл, то отображаются назначенные Вам"><?=$uSupport->req_assigned2me_count?><br>Мои</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_unassigned']=='1'?'active':''?>" id="uSup_user_settings_btn_show_unassigned" onclick="uSup.change_users_settings('show_unassigned')" data-loading-text="<?=$uSupport->req_unassigned_count?><br>Неназначенные" title="Если вкл, то отображаются Не назначенные никому"><?=$uSupport->req_unassigned_count?><br>Неназначенные</button>
            <button class="btn btn-default btn-outline uTooltip <?=$uSup_settings['show_assigned2others']=='1'?'active':''?>" id="uSup_user_settings_btn_show_assigned2others" onclick="uSup.change_users_settings('show_assigned2others')" data-loading-text="<?=$uSupport->req_assigned2others_count?><br>Чужие" title="Если вкл, то отображаются назначенные другим консультантам"><?=$uSupport->req_assigned2others_count?><br>Чужие</button>
        </div>*/?>
        <?}
        if($uSupport->is_operator||($uSupport->is_com_admin&&$uSupport->two_level)) {?>
            <div class="col-md-2">
                <b>Эскалация</b>
                <label><input onchange="uSup.change_users_settings('show_internal');" type="checkbox" id="uSup_user_settings_btn_show_internal" <?=(int)$uSup_settings['show_internal']?'checked':''?> class="uSup_user_settings_btn"> Внутри компании</label>
                <br>
                <label><input onchange="uSup.change_users_settings('show_escalated');" type="checkbox" id="uSup_user_settings_btn_show_escalated" <?=(int)$uSup_settings['show_escalated']?'checked':''?> class="uSup_user_settings_btn"> Эскалированные</label>
            </div>
        <?}?>
        </div>
        <div class="row">&nbsp;</div>
        <?if($uSupport->is_com_admin||$uSupport->is_com_client) {
            if($uSupport->companies_count==1) {?>
                <a href="<?=u_sroot.$this->mod?>/company_info/<?=$uSupport->def_com_id?>" class="btn btn-default pull-right"><span class="glyphicon glyphicon-briefcase"></span> Моя компания</a>
            <?}
            else {?>
                <!--suppress HtmlUnknownTarget -->
                <a href="<?=u_sroot.$this->mod?>/companies" class="btn btn-default pull-right"><span class="glyphicon glyphicon-briefcase"></span> Мои компании</a>
            <?}?>
        <?}?>
        <button class="btn btn-success" onclick="uSup.new_request_init()"><span class="glyphicon glyphicon-plus"></span> Создать запрос</button>

        <a class="btn btn-default pull-right" href="<?=u_sroot?>uKnowbase/records"><span class="glyphicon glyphicon-book"></span> База знаний</a>

        <div class="row">&nbsp;</div>
        <div class="row text-muted">
            <div class="col-md-12" id="uSup_requests_request_shown_tip"><?=$uSupport->uSupport->define_requests_shown_tip($uSup_settings);?></div>
        </div>
        <div class="row">&nbsp;</div>

        <div id="uSupport_requests_list"></div>

        <?include 'inc/requests_dialogs.php';?>
        <?include 'inc/request_show_dialogs.php';?>

        <script type="text/javascript">
            if(typeof uSup==="undefined") uSup={};
            <?if($uSupport->is_com_client||$uSupport->is_com_admin||$uSupport->is_operator||$uSupport->is_consultant) {
                for($i=0;$com=$uSupport->qCompList[$i];$i++) {?>
            if(typeof uSup.com_id==="undefined") uSup.com_id=[];
            if(typeof uSup.com_title==="undefined") uSup.com_title=[];
            if(typeof uSup.com_id2index==="undefined") uSup.com_id2index=[];

            if(typeof uSup_req_show_common==="undefined") uSup_req_show_common={};
            if(typeof uSup_req_show_common.cons_id==="undefined") uSup_req_show_common.cons_id=[];
                uSup.com_id[<?=$i?>]=<?=$com->com_id?>;
                uSup.com_title[<?=$i?>]="<?=rawurlencode(uString::sql2text($com->com_title,1))?>";
                uSup.com_id2index[<?=$com->com_id?>]=<?=$i?>;
                <?}
            }

            if($uSupport->is_com_client||$uSupport->is_com_admin||$uSupport->is_operator||$uSupport->is_consultant) {?>
                uSup.req_force_write_noreason_sol=<?=$this->uFunc->getConf("req_force_write_noreason_sol","uSup")=='1'?'true':'false'?>;
            <?}?>
            if(typeof uSup.user_id==="undefined") uSup.user_id=[];
            if(typeof uSup.firstname==="undefined") uSup.firstname=[];
            if(typeof uSup.secondname==="undefined") uSup.secondname=[];
            if(typeof uSup.lastname==="undefined") uSup.lastname=[];
            if(typeof uSup.user_id2ind==="undefined") uSup.user_id2ind=[];

            <?for($i=0;$user=$uSupport->qUserList[$i];$i++) {?>
            uSup.user_id[<?=$i?>]=<?=$user->user_id?>;
            uSup.firstname[<?=$i?>]="<?=uString::sql2text($user->firstname)?>";
            uSup.secondname[<?=$i?>]="<?=uString::sql2text($user->secondname)?>";
            uSup.lastname[<?=$i?>]="<?=uString::sql2text($user->lastname)?>";
            uSup.user_id2ind[<?=$user->user_id?>]=<?=$i?>;
            <?}?>

            let user_id_length;
            if(typeof uSup.user_id2com==="undefined") uSup.user_id2com=[];
            <?for($i=0;$user=$uSupport->qCom_users_list[$i];$i++) {?>
            if(typeof uSup.user_id2com[<?=$user->user_id?>]=== 'undefined') uSup.user_id2com[<?=$user->user_id?>]=[];
            user_id_length=uSup.user_id2com[<?=$user->user_id?>].length;
            uSup.user_id2com[<?=$user->user_id?>][user_id_length]=<?=$user->com_id;?>;
            <?}?>

            <?if($uSupport->is_operator||$uSupport->is_consultant) {?>
            uSup.force_show_companies=true;
            <?}
            else {?>
            uSup.force_show_companies=false;
            <?}?>

            <?if(isset($_GET['create_new_request'])){?>
            $(document).ready(function() {
                uSup.new_request_init();
            });
            <?}?>
        </script>

    <?}
    else {?>
        <div class="jumbotron">
            <h1 class="page-header">Техническая поддержка</h1>
            <p>Для того, чтобы создать запрос в техническую поддержку Ваша компания должна быть клиентом нашей технической поддержки.</p>
            <p>Если вы сотрудник такой компании, попросите своего администратора добавить вашу учетную запись к Вашей компании.</p>
        </div>
    <?}
}
else {
    ?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}
$this->page_content=ob_get_contents();
ob_end_clean();

/** @noinspection PhpIncludeInspection */
include "templates/template.php";
