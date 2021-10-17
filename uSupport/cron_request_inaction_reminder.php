<?php
namespace uSupport\cron;
use PDO;
use PDOException;
use processors\uFunc;
use uAuth\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "uAuth/classes/common.php";
require_once "uSupport/classes/common.php";

class cron_request_inaction_reminder {
    public $uFunc;
    public $uAuth;
    public $uSup;
    private $uCore,$secret,
        $reminder_days,$reminder_timestamp,
        $start_time,$time_limit,$stop_before_limit,
        $site_id;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uFunc->error(10);//Вернуть в production
        if($this->secret!=$_POST['uSecret']) $this->uFunc->error(20);//Вернуть в production
        if(!isset($_POST['site_id'])) $this->uFunc->error(30);//Вернуть в production
        $this->site_id=$_POST['site_id'];//Вернуть в production
//        $this->site_id=site_id;//Убрать в production

        if(!uString::isDigits($this->site_id)) $this->uFunc->error(40);

        if(!$this->uFunc->mod_installed("uSup",$this->site_id)) $this->uFunc->error(50);
    }
    private function set_vars() {
        $this->time_limit=30;
        $this->stop_before_limit=10;

        $this->reminder_days=trim($this->uFunc->getConf("noaction_request_reminder_days","uSup",0,$this->site_id));
        if(!uString::isDigits($this->reminder_days)) $this->reminder_days=2;

        $this->reminder_timestamp=time()-$this->reminder_days*86400;
    }

    private function remind_about_inaction($req) {
        $request_id=(int)$req->tic_id;
        $cons_id=(int)$req->cons_id;
        $escalated=(int)$req->escalated;

        if($cons_id) {
            if(!$user_data=$this->uAuth->user_id2user_data($cons_id,"firstname,email,status")) return 0;
            if(!$usersinfo_data=$this->uAuth->user_id2usersinfo($cons_id,"status",$this->site_id)) return 0;
            if($user_data->status!=$usersinfo_data->status&&$usersinfo_data->status!="active") return 0;
            $cons_firstname=uString::sql2text($user_data->firstname,1);
            $cons_email=$user_data->email;

            $this->uSup->msg_inaction_reminder($request_id,$cons_firstname,$cons_email,$this->site_id);
        }
        else {
            if($escalated) {
                $q_operators=$this->uSup->get_com_admins_to_notify_about_requests("firstname,email");
                $q_com_admins_list=$this->uSup->get_com_admins_to_notify_about_requests("user_id",$tic->company_id);
                $q_admin_ids=" (1=0";
                while($admin=$q_com_admins_list->fetch(PDO::FETCH_OBJ)) {
                    $q_admin_ids.=" OR user_id=".$admin->user_id." ";
                }
                $q_admin_ids.=" )";

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT 
                        firstname,
                        email
                        FROM 
                        u235_users 
                        WHERE 
                        ".$q_admin_ids."
                        ");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
                $q_operators=$stm;
            }//to admins
            else $q_operators=$this->uSup->get_operators("firstname,email", $this->site_id);//to operators

            /** @noinspection PhpUndefinedMethodInspection */
            while($oper=$q_operators->fetch(PDO::FETCH_OBJ)) {
                $oper_firstname=uString::sql2text($oper->firstname,1);
                $oper_email=$oper->email;
                $this->uSup->msg_inaction_reminder($request_id,$oper_firstname,$oper_email,$this->site_id);
            }
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("UPDATE
            u235_requests
            SET
            tic_notified_about_noaction_timestamp=:tic_notified_about_noaction_timestamp
            WHERE
            tic_id=:tic_id AND
            site_id=:site_id
            ");
            $tic_notified_about_noaction_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $request_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_notified_about_noaction_timestamp', $tic_notified_about_noaction_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        return 1;
    }

    private function run_throw_requests() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            tic_id,
            cons_id,
            company_id,
            tic_notified_about_noaction_timestamp,
            escalated
            FROM
            u235_requests
            WHERE
            tic_changed_timestamp<:tic_changed_timestamp AND
            tic_status!='req_answered' AND
            tic_status!='' AND
            tic_status!='tmp' AND
            tic_status!='new' AND
            tic_status!='req_closed' AND
            tic_status!='case_answered' AND
            tic_status!='case_closed' AND
            tic_status!='case_done' AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_changed_timestamp', $this->reminder_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        while($req=$stm->fetch(PDO::FETCH_OBJ)) {
            if(time()>=$this->start_time+$this->time_limit-$this->stop_before_limit) exit('time limit exceeded');
            if($req->tic_notified_about_noaction_timestamp<$this->reminder_timestamp) $this->remind_about_inaction($req);
        }
        return 0;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='JzXM5lQn@Ys$Dc%udk8Z4Pn!h#';
        $this->start_time=time();

        $this->uFunc=new uFunc($this->uCore);
        $this->uAuth=new common($this->uCore);
        $this->uSup=new \uSupport\common($this->uCore);

        $this->check_data();
        $this->set_vars();

        $this->run_throw_requests();
    }
}
new cron_request_inaction_reminder($this);