<?php
require_once "processors/classes/uFunc.php";
require_once 'uAuth/classes/common.php';
require_once "uSupport/classes/common.php";

//include_once('lib/flourishlib/init.php');
class cron_check_new_emails {
    public $uAuth;
    public $uFunc;
    public $user_default_com;
    public $uSup;
    private $uCore,
        $secret,
        $time_limit,$stop_before_limit,$start_time,$email_rec_livetime,
        $mailbox,$messages,$msg_uid,$attachments,
        $support_email,$support_email_from,$use_smtp,$smtp_settings,
        $user_active,$user_name,$user_id,$user_email,$user_com_id_ar,$user_firstname,$user_secondname,$user_lastname,$have_many_comps,$q_user_comps,
        $tic_id,$tic_subject,$msg_id,$file_id,
        $from_cons,$from_com_admin,$from_user,
        $msg_user_id,$msg_cons_id,$msg_com_id,$confirmation_hash,
        $is_reply,
        $msg_hash,
        $site_id,$site_name,$site_domain,$u_sroot,$emails_black_list_ar,
        $body_plain_tmp;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uFunc->error(10);//Вернуть для production
        if($this->secret!=$_POST['uSecret']) $this->uFunc->error(20);//Вернуть для production
        if(!isset($_POST['site_id'])) $this->uFunc->error(30);//Вернуть для production
//        $_POST['site_id']=site_id;//Убрать для production
        $this->site_id=$_POST['site_id'];
        if(!uString::isDigits($this->site_id)) $this->uFunc->error(40);

        if(!$this->uCore->uFunc->mod_installed('uSup',$this->site_id)) die('uSup is not installed');
    }

    private function set_vars() {
        $this->time_limit=30;
        $this->stop_before_limit=10;

        $this->site_name=$this->uCore->uFunc->getConf('site_name','content',true,$this->site_id);
        $this->site_domain=$this->uCore->uFunc->getConf('site_domain','content',true,$this->site_id);
        $this->u_sroot='http://'.$this->site_domain.'/';
        $this->use_smtp=$this->uCore->uFunc->getConf('smtp_use_madwww_server','uSup',true,$this->site_id)=='0';
        if($this->use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $this->smtp_settings['server_name']=$this->uCore->uFunc->getConf('smtp_server_name','uSup',true,$this->site_id);
            if($this->smtp_settings['server_name']=='') return false;
            $this->smtp_settings['port']=$this->uCore->uFunc->getConf('smtp_port','uSup',true,$this->site_id);
            $this->smtp_settings['user_name']=$this->uCore->uFunc->getConf('smtp_user_name','uSup',true,$this->site_id);
            if($this->smtp_settings['user_name']=='') return false;
            $this->smtp_settings['password']=$this->uCore->uFunc->getConf('smtp_password','uSup',true,$this->site_id);
            if($this->smtp_settings['password']=='') return false;
            $this->smtp_settings['use_ssl']=$this->uCore->uFunc->getConf('smtp_use_ssl','uSup',true,$this->site_id)=='1';
        }
        else $this->smtp_settings[0]=0;

            $this->support_email=$this->uCore->uFunc->getConf("support_email","uSup",true,$this->site_id);
            $this->support_email_from=$this->uCore->uFunc->getConf("support_email_fromname","uSup",true,$this->site_id);
        $emails_black_list=$this->uCore->uFunc->getConf("emails_black_list","uSup",true,$this->site_id);
        $this->emails_black_list_ar=explode(' ',$emails_black_list);

        $this->email_rec_livetime=86400;//60sec * 60min * 24hour = 1 day
        return true;
    }

    private function connect() {
        if($this->uCore->uFunc->getConf("inbox_use_ssl","uSup",true,$this->site_id)=='1') $ssl=true;
        else $ssl=false;
        $inbox_server_type=strtolower($this->uCore->uFunc->getConf("inbox_server_type","uSup",true,$this->site_id));
        $port=$this->uCore->uFunc->getConf("inbox_server_port","uSup",true,$this->site_id);
        $inbox_server=$this->uCore->uFunc->getConf("inbox_server","uSup",true,$this->site_id);
        if($inbox_server=='') return false;
        $inbox_server_user_name=$this->uCore->uFunc->getConf("inbox_user_name","uSup",true,$this->site_id);
        if($inbox_server_user_name=='') return false;
        $inbox_server_password=$this->uCore->uFunc->getConf("inbox_password","uSup",true,$this->site_id);
        if($inbox_server_password=='') return false;

        if($port=='0') {
            if($inbox_server_type=='imap') {
                if($ssl) $port=993;
                else $port=143;
            }
            else {
                if($ssl) $port=995;
                else $port=110;
            }
        }
        try {
            $this->mailbox = new fMailbox($inbox_server_type,
                $inbox_server,
                $inbox_server_user_name,
                $inbox_server_password,
                $port,
                $ssl
            );
            $this->messages = $this->mailbox->listMessages();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    private function check_msgs() {
        foreach($this->messages as $msg) {
            if(time()>=$this->start_time+$this->time_limit-$this->stop_before_limit) exit('time limit exceeded');

            /** @noinspection PhpUndefinedMethodInspection */
            $msg_fetched=$this->mailbox->fetchMessage($msg['uid']);

            $this->from_cons=false;
            $this->from_com_admin=false;
            $this->from_user=false;

            $this->msg_uid=$msg['uid'];

            $this->user_email=$msg_fetched['headers']['from']['mailbox'].'@'.$msg_fetched['headers']['from']['host'];
            $this->user_name=$msg_fetched['headers']['from']['personal'];
            $this->user_active=false;

            $subject=imap_utf8($msg['subject']);
            $timestamp=strtotime($msg['date']);

            $body=$msg_fetched['text'];

            if(isset($msg_fetched['attachment'])) {
                foreach($msg_fetched['attachment'] as $attachment) {
                    $j=count($this->attachments);
                    $this->attachments[$j]['type']='attachment';
                    $this->attachments[$j]['filename']=$attachment['filename'];
                    $this->attachments[$j]['mimetype']=$attachment['mimetype'];
                    $this->attachments[$j]['data']=$attachment['data'];
                }
            }
            if(isset($msg_fetched['inline'])) {
                foreach($msg_fetched['inline'] as $attachment) {
                    $j=count($this->attachments);
                    $this->attachments[$j]['type']='inline';
                    $this->attachments[$j]['filename']=$attachment['filename'];
                    $this->attachments[$j]['mimetype']=$attachment['mimetype'];
                    $this->attachments[$j]['data']=$attachment['data'];
                }
            }
            if(isset($msg_fetched['related'])) {
                foreach($msg_fetched['related'] as $attachment) {
                    $j=count($this->attachments);
                    $this->attachments[$j]['type']='related';
                    $this->attachments[$j]['filename']='';
                    $this->attachments[$j]['mimetype']=$attachment['mimetype'];
                    $this->attachments[$j]['data']=$attachment['data'];
                }
            }

            $this->record_msg($subject,$timestamp,$body);
        }
    }

    private function check_msg_data($timestamp) {
        //check if sender_email is real email
        if(!uString::isEmail($this->user_email)) {
            uFunc::journal($this->user_email.' - not an email','uSup_wrong_email');
            return false;
        }
        //check if timestamp is timestamp
        if(!uString::isDigits($timestamp)) {
            uFunc::journal($timestamp.' - is not timestamp','uSup_wrong_email');
            return false;
        }
        return true;
    }
    private function check_if_msg_is_duplicate ($timestamp) {
        //delete old emails
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","DELETE FROM
        `u235_emails`
        WHERE
        `email_timestamp`<'".(time()-$this->email_rec_livetime)."'
        ")) $this->uFunc->error(80);

        //check
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `email_id`
        FROM
        `u235_emails`
        WHERE
        `email_sender_email`='".$this->user_email."' AND
        `email_timestamp`='".$timestamp."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uFunc->error(90);
        if(mysqli_num_rows($query)) return true;
        return false;
    }
    private function get_last_email_msg_id() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `email_id`
        FROM
        `u235_emails`
        WHERE
        `site_id`='".$this->site_id."'
        ORDER BY
        `email_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(100);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            return $qr->email_id+1;
        }
        return 1;
    }
    private function write_msg2db($email_id,$timestamp,$subject_encoded,$body_plain) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_emails` (
        `email_id`,
        `email_sender_email`,
        `email_timestamp`,
        `email_subject`,
        `email_text`,
        `tic_id`,
        `site_id`
        ) VALUES (
        '".$email_id."',
        '".$this->user_email."',
        '".$timestamp."',
        '".$subject_encoded."',
        '".uString::text2sql($body_plain)."',
        '0',
        '".$this->site_id."'
        )
        ")) $this->uFunc->error(110);
    }
    private function register_new_user_on_madplugin() {
        $pass=uFunc::genPass();

        $this->user_id=(int)$this->uAuth->add_new_user($this->user_name, '', '',$this->user_email,$pass);

        //send user a email with his registration info
//        if($this->user_active) {
            $this->uAuth->update_user($this->user_id,"status='active'");
            $this->uAuth->emailUserAboutRegistration($this->user_name,$this->user_email,$pass,$this->u_sroot,$this->site_name,$this->support_email,$this->site_id);
//        }
//        else {
//            $this->uSup->new_account_is_created_notification($this->user_name,$this->user_email,$hash,$pass,$this->site_id);
//        }
    }

    private function check_if_msg_from_cons() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            user_id
            FROM
            u235_groups_acl
            JOIN 
            u235_usersinfo_groups
            ON
            group_id=user_group_id
            WHERE
            acl_id=9 AND
            user_id=:user_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}

        if($stm->fetch(PDO::FETCH_OBJ)) return true;

        return false;
    }
    private function check_if_user_is_registered() {
        $this->user_active=true;
        //check if user registered on madplugin
        $user=$this->uAuth->userLogin2info('user_id,status',$this->user_email,'email');
        if(!$user) {//not registered even on madplugin
            if($this->uCore->uFunc->getConf("receive_only_from_registered","uSup",0,$this->site_id)=='0') {//we can register new user
                if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup",0,$this->site_id)=='1') {//we CAN'T proceed requests from users not attached to companies
                    //check if user's email belongs to email domain of any company and then register user
                    $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                    if(count($this->user_com_id_ar)>1) {//email belongs to company
                        if($this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1') {//we must add user to company by his email
                            //we must register new user
                            $this->register_new_user_on_madplugin();
                            $this->uAuth->add_user2usersinfo($this->user_id, 'active',$this->site_id);

                            for($i=0;$this->user_com_id_ar[$i];$i++) {
                                $this->uSup->attach_user2company($this->user_id,$this->user_com_id_ar[$i]->com_id,0,$this->site_id);
                            }
                            return true;
                        }
                        return false;
                    }
                    return false;
                }
                else {//we can proceed requests from users not attached to companies
                    $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                    $add2com=$this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1';
                    $this->user_active=false;
                    if($add2com&&count($this->user_com_id_ar)>1) $this->user_active=true;

                    //we must register new user
                    $this->register_new_user_on_madplugin();
                    $this->uAuth->add_user2usersinfo($this->user_id,"active",$this->site_id);
                    if($add2com) {//we must add user to company by his email
                        for($i=0;$this->user_com_id_ar[$i];$i++) {//check if user's email belongs to email domain of any company and then attach user to company
                            $this->uSup->attach_user2company($this->user_id,$this->user_com_id_ar[$i]->com_id,0,$this->site_id);
                        }
                    }
                    return true;
                }
            }
            return false;
        }
        else {//registered on madplugin
            /** @noinspection PhpUndefinedMethodInspection */
            $this->user_id=(int)$user->user_id;
            $status=$user->status;
            if($status=='banned') return false;
            elseif($status=='activation_needed') $this->user_active=false;

            //check if user is registered on this site (usersinfo)
            $usersinfo=$this->uAuth->user_id2usersinfo($this->user_id,"status",$this->site_id);
            if(!$usersinfo) {//registered on madplugin BUT not on this site
                if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup",0,$this->site_id)=='1') {//we CAN'T proceed requests from users not attached to companies
                    //check if user's email belongs to email domain of any company and then register user
                    $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                    if(count($this->user_com_id_ar)>1) {//email belongs to company
                        if($this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1') {//we must add user to company by his email
                            //we must register user on this site
                            $this->uAuth->add_user2usersinfo($this->user_id,"active",$this->site_id);
                            //then we must attach user to company
                            for($i=0;$this->user_com_id_ar[$i];$i++) {
                                $this->uSup->attach_user2company($this->user_id, $this->user_com_id_ar[$i]->com_id, 0, $this->site_id);
                            }
                            return true;
                        }
                    }
                    return false;
                }
                else {//we can proceed requests from users not attached to companies
                    //we must register new user
                    $this->uAuth->add_user2usersinfo($this->user_id,"active",$this->site_id);
                    if($this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1') {//we must add user to company by his email
                        $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                        for($i=0;$this->user_com_id_ar[$i];$i++) {//check if user's email belongs to email domain of any company and then add user to company
                            $this->uSup->attach_user2company($this->user_id,$this->user_com_id_ar[$i]->com_id,0,$this->site_id);
                        }
                    }
                    return true;
                }
            }
            else {//registered on this site
                /** @noinspection PhpUndefinedMethodInspection */
                $status=$usersinfo->status;
                if($status=='banned') return false;

                $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                //check if user is consultant or operator
                if($this->check_if_msg_from_cons()) {//user is consultant
                    $this->from_cons=true;
                    return true;
                }
                //check if user is attached to company
                elseif(count($this->user_com_id_ar)>1) {//user is attached to company
                    return true;
                }
                else {//user is not attached to company
                    if($this->uCore->uFunc->getConf("receive_only_from_comps_users","uSup",0,$this->site_id)=='1') {//we CAN'T proceed requests from users not attached to companies
                        if($this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1') {//we must add user to company by his email
                            //check if user's email belongs to email domain of any company and then register user
                            $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                            for($i=0;$this->user_com_id_ar[$i];$i++) {//email belongs to company
                                $this->uSup->attach_user2company($this->user_id,$this->user_com_id_ar[$i]->com_id,0,$this->site_id);
                                return true;
                            }
                        }
                        return false;
                    }
                    else {//we can proceed requests from users not attached to companies
                        if($this->uCore->uFunc->getConf("assign_users2comps_by_email","uSup",0,$this->site_id)=='1') {//we must add user to company by his email
                            $this->user_com_id_ar=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
                            for($i=0;$this->user_com_id_ar[$i];$i++) {//check if user's email belongs to email domain of any company and then add user to company
                                $this->uSup->attach_user2company($this->user_id,$this->user_com_id_ar[$i]->com_id,0,$this->site_id);
                            }
                        }
                        return true;
                    }
                }
            }
        }
        /** @noinspection PhpUnreachableStatementInspection */
        return false;
    }

    private function notify_about_request($request_id,$site_id) {
        $request_info=$this->uSup->req_id2info($request_id,"company_id",$site_id);
        $company_id=(int)$request_info->company_id;

        if($com=$this->uSup->com_id2com_info($company_id,"two_level")) $two_level=(int)$com->two_level;
        else $two_level=0;

        $this->uSup->request_is_received_notification($request_id,1,$site_id);

        if($this->user_active) {//only if user account is active
            if ($two_level) {
                $q_com_admins = $this->uSup->get_com_admins_to_notify_about_requests("user_id", $company_id, $site_id);

                $q_admins = "(1=0";
                /** @noinspection PhpUndefinedMethodInspection */
                while ($admin = $q_com_admins->fetch(PDO::FETCH_OBJ)) $q_admins .= " OR u235_users.user_id=" . $admin->user_id . " ";
                $q_admins .= ")";

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT 
                firstname,
                secondname,
                email
                FROM 
                u235_users
                JOIN 
                u235_usersinfo
                ON
                u235_users.user_id=u235_usersinfo.user_id AND
                u235_users.status=u235_usersinfo.status
                WHERE 
                " . $q_admins . " AND
                u235_users.status='active' AND
                u235_usersinfo.site_id=:site_id
                ");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('130'/*.$e->getMessage()*/);
                }

                /** @noinspection PhpStatementHasEmptyBodyInspection */
                /** @noinspection PhpUndefinedMethodInspection */
                for ($i = 0; $stm_recipients[$i] = $stm->fetch(PDO::FETCH_OBJ); $i++) {
                };

            }
            else {
                $stm = $this->uSup->get_operators("firstname,secondname,email", $site_id);
                for ($i = 0; $stm_recipients[$i] = $stm->fetch(PDO::FETCH_OBJ); $i++) {
                };
            }

            for ($i = 0; $user = $stm_recipients[$i]; $i++) $this->uSup->new_request_cons_notification($user->firstname, $user->secondname, $user->email, $request_id, $site_id);
        }
    }
    private function notify_about_msg($request_id,$site_id) {
        //get request's com_id and escalation status

        $req_info=$this->uSup->req_id2info($request_id,"company_id,escalated");
        $com_id=$req_info->company_id;
        $escalated=(int)$req_info->escalated;

        $two_level=0;//initial value
        if(!$escalated) {//not escalated - get company two_level config
            if($com_info=$this->uSup->com_id2com_info($com_id, "two_level")) {
                $two_level=(int)$com_info->two_level;
            }
        }

        //notify author that msg has been received
        $this->uSup->msg_is_received_notification($request_id,$site_id);

        if($this->from_user) {//New message is received from author
            if($escalated||!$two_level) {//send notification to support engineers
                if (!$this->msg_cons_id) {//to operator
                    $operators=$this->uSup->get_operators("firstname,email",$this->site_id);
                    while ($oper = $operators->fetch(PDO::FETCH_OBJ)) $this->uSup->new_msg_cons_notification($request_id, $oper->email, $oper->firstname, $site_id);
                }
                else {//to consultant
                    $cons = $this->uAuth->user_id2user_data($this->msg_cons_id, "firstname,email");
                    $this->uSup->new_msg_cons_notification($request_id, $cons->email, $cons->firstname, $site_id);
                }
            }
            else {//send notification to company's admins
                $q_com_admins_list=$this->uSup->get_com_admins_to_notify_about_requests("user_id",$com_id);
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
                catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/);}
                $com_admins=$stm;

                while ($oper = $com_admins->fetch(PDO::FETCH_OBJ)) $this->uSup->new_msg_cons_notification($request_id, $oper->email, $oper->firstname, $site_id);
            }
        }
        elseif($this->from_com_admin) {//New message is received from company's admin
            //we must notify msg owner that there are a new msg
            $this->uSup->new_msg_author_notification($request_id,$site_id);

            if($escalated||!$two_level) {//only if we need to notify help desk engineers
                //We must notify operator OR consultant about new message from company's admin
                if (!$this->msg_cons_id) {//to operator
                    $operators=$this->uSup->get_operators("firstname,email",$this->site_id);
                    while ($oper = $operators->fetch(PDO::FETCH_OBJ)) $this->uSup->new_msg_cons_notification($request_id, $oper->email, $oper->firstname, $site_id);
                }
                else {//to consultant
                    $cons =$this->uAuth->user_id2user_data($this->msg_cons_id,"firstname,lastname,secondname");
                    $this->uSup->new_msg_cons_notification($request_id, $cons->email, $cons->firstname, $site_id);
                }
            }
        }
        elseif($this->from_cons) {//New message is from consultant or operator
            //we must notify msg owner that there are new msg made
            $this->uSup->new_msg_author_notification($request_id,$site_id);
        }

        return 1;
    }

    private function open_request($subject,$body_plain) {
        //get new request_id
        $this->tic_id=$this->uSup->get_new_req_id();

        //define user's com_id
        $comps=$this->uSup->is_email_belongs2company($this->user_email,$this->site_id);
        for($i=0;$comps[$i];$i++) {
            $this->uSup->attach_user2company($this->user_id,$comps[$i]->com_id,0,$this->site_id);
        };

        $this->q_user_comps=$this->uSup->user_id2comps($this->user_id,$this->site_id);
        if(count($this->q_user_comps)>2) $this->have_many_comps=true;
        else $this->have_many_comps=false;

        //try to find default com_id
        $this->user_default_com=(int)$this->uSup->user_id2default_com_id($this->user_id,$this->site_id);

        if($this->user_active) {
            $tic_confirmed='1';
            $this->confirmation_hash='';
        }
        else {
            $tic_confirmed='0';
            $this->confirmation_hash=$this->uCore->uFunc->genHash();
        }

        $q_com=$this->uSup->com_id2com_info($this->user_default_com,"two_level");
        $two_level=(int)$q_com->two_level;

        //add request 2 db
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
            u235_requests (
            tic_id,
            company_id,
            user_id,
            tic_opened_timestamp,
            tic_changed_timestamp,
            tic_subject,
            tic_status,
            tic_confirmed,
            confirmation_hash,
            site_id,
            escalated
            ) VALUES (
            :tic_id,
            :company_id,
            :user_id,
            :tic_opened_timestamp,
            :tic_opened_timestamp,
            :tic_subject,
            'req_open',
            :tic_confirmed,
            :confirmation_hash,
            :site_id,
            :escalated
            )
            ");
            $tic_subject=uString::text2sql($subject);
            $tic_opened_timestamp=time();
            $escalated=!$two_level;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':escalated', $escalated,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':company_id', $this->user_default_com,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_opened_timestamp', $tic_opened_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_confirmed', $tic_confirmed,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_subject', $tic_subject,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':confirmation_hash', $this->confirmation_hash,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('370'/*.$e->getMessage()*/);}

        //add msg 2 request
        //get new msg_id
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
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->msg_id=$qr->msg_id+1;
            else $this->msg_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('380'/*.$e->getMessage()*/);}

        //write msg 2 db
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("INSERT INTO
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
            )
            ");
            $msg_text=uString::text2sql($body_plain);
            $msg_timestamp=time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tic_id', $this->tic_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_id', $this->msg_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_sender', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_timestamp', $msg_timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_text', $msg_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('390'/*.$e->getMessage()*/);}
    }
    private function get_req_status($tic_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_status`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$tic_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uFunc->error(400);
        if(!mysqli_num_rows($query)) $this->uFunc->error(410);
        /** @noinspection PhpUndefinedMethodInspection */
        $tic=$query->fetch_object();
        return $tic->tic_status;
    }
    private function write_reply_msg($body_plain) {
        //check if this request_id exists
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_subject`,
        `company_id`,
        `cons_id`,
        `user_id`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `tic_status`!='req_closed' AND
        `tic_status`!='case_closed' AND
        `site_id`='".$this->site_id."'
        ")) $this->uFunc->error(420);
        if(!mysqli_num_rows($query)) return false;
        /** @noinspection PhpUndefinedMethodInspection */
        $qr=$query->fetch_object();
        $this->msg_com_id=(int)$qr->company_id;
        $this->msg_user_id=(int)$qr->user_id;
        $this->msg_cons_id=(int)$qr->cons_id;
        $this->tic_subject=uString::sql2text($qr->tic_subject);

        $allow=false;

        if($this->from_cons) $allow=true;//allow for consultant
        elseif($this->msg_user_id===$this->user_id) {//allow for request owner
            $this->from_user=true;
            $allow=true;
        }
        else {
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
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->msg_com_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($stm->fetch(PDO::FETCH_OBJ)) {//allow for support company admin
                    $this->from_com_admin=true;
                    $allow=true;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('430'/*.$e->getMessage()*/);}
        }

        if(!$allow) return false;

        //get req status
        $req_status=$this->get_req_status($this->tic_id);
        if($req_status=='case_open'||
            $req_status=='case_answered'||
            $req_status=='case_processing'||
            $req_status=='case_closed'||
            $req_status=='case_done') {
            if($this->from_cons) $req_status='case_answered';
            else $req_status='case_open';
        }
        else {
            if($this->from_cons) $req_status='req_answered';
            else $req_status='req_open';
        }

        //update request in db
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_changed_timestamp`='".time()."',
        `tic_status`='".$req_status."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uFunc->error(440);

        //add msg 2 request
        //get new msg_id
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`
        FROM
        `u235_msgs`
        WHERE
        `site_id`='".$this->site_id."'
        ORDER BY
        `msg_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(450);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $this->msg_id=$qr->msg_id+1;
        }
        else $this->msg_id=1;
        //write msg 2 db
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_msgs` (
        `tic_id`,
        `msg_id`,
        `msg_text`,
        `msg_sender`,
        `msg_timestamp`,
        `msg_status`,
        `site_id`
        ) VALUES (
        '".$this->tic_id."',
        '".$this->msg_id."',
        '".uString::text2sql($body_plain)."',
        '".$this->user_id."',
        '".time()."',
        '1',
        '".$this->site_id."'
        )
        ")) $this->uFunc->error(460);

        return true;
    }
    private function write_attachment2file($i) {
        $filename = $this->attachments[$i]['filename'];
        if(empty($filename)) {
            $filename = "Вложение ".$i.time();
        }

        //Get new file_id
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uSup","SELECT
        `file_id`
        FROM
        `u235_msgs_files`
        WHERE
        `site_id`='".$this->site_id."'
        ORDER BY
        `file_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(470);
        if(mysqli_num_rows($query)>0) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $this->file_id=$qr->file_id+1;
        }
        else $this->file_id=1;

        //write empty file info 2 db
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","INSERT INTO
        `u235_msgs_files` (
        `file_id`,
        `owner_id`,
        `filename`,
        `msg_id`,
        `tic_id`,
        `site_id`
        ) VALUES (
        '".$this->file_id."',
        '".$this->user_id."',
        '".uString::text2sql($filename)."',
        '".$this->msg_id."',
        '".$this->tic_id."',
        '".$this->site_id."'
        )
        ")) $this->uFunc->error(480);

        //folder to write attachments
        $save_file_name=$hash_id=uFunc::genHash();
        $dir = $_SERVER['DOCUMENT_ROOT'].'/uSupport/msgs_files/'.$this->site_id.'/'.$this->tic_id.'/'.$this->file_id.'/'; //Адрес директории для сохранения файла
        if (!file_exists($dir)) mkdir($dir,0740,true);

        //write attachment to file
        $fp = fopen($dir.$save_file_name, "w+");
        fwrite($fp, $this->attachments[$i]['data']);
        fclose($fp);

        $mime_type=$this->attachments[$i]['mimetype'];
        if(!$mime_type) $mime_type='application/octet-stream';

        $file_size=filesize($dir.$save_file_name);

        if(strpos('_'.$mime_type,'image')) {
            //make thumb
            $height=150;

            $im = new Imagick($dir.$save_file_name);
            $im->setImageFormat('jpeg');

            $im->resizeImage(0,$height,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
// Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(75);
// Strip out unneeded meta data
            $im->stripImage();

            $im->writeImage($dir.$this->file_id.'_sm.jpg');

            $im->clear();
            $im->destroy();
        }

        //update file info
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query("uSup","UPDATE
        `u235_msgs_files`
        SET
        `timestamp`='".time()."',
        `file_size`='".$file_size."',
        `file_mime`='".$mime_type."',
        `hash`='".$save_file_name."'
        WHERE
        `file_id`='".$this->file_id."' AND
        `site_id`='".$this->site_id."'
        ")) $this->uFunc->error(490);
    }
    private function check_if_reply($body_plain,$subject) {
        $write_above_line=$this->uCore->uFunc->getConf("email_write_above_line_content","uSup",true,$this->site_id);

        $req_id_pos=fUTF8::pos('0'.$subject,'[#req-');
        if($req_id_pos) {
            //find ] position - we don't know the length of tic_id - so we'll try to find first ] after [#req-
            $bkt_pos=false;
            for($i=0;$i<100;$i++) {
                $bkt=substr($subject,$req_id_pos+5+$i,1);
                if($bkt==']') {
                    $bkt_pos=$i;
                    break;
                }
            }
            $this->tic_id=substr($subject,$req_id_pos+5,$bkt_pos);

            if(!uString::isDigits($this->tic_id)) return false;//wrong tic_id

            if($line_pos=strpos('0'.$body_plain,$write_above_line)) {
                $body_plain=substr($body_plain,0,$line_pos-1);
            }

            if(!$this->write_reply_msg($body_plain)) return false;//can't write as reply
            $this->body_plain_tmp=$body_plain;
            return true;
        }
        return false;
    }
    private function save_attachments() {
        for($i=0;$i<count($this->attachments);$i++) {
            //if(empty($this->attachments[$i]['filename'])&&$this->attachments[$i]['type']!='related') continue;
            $this->write_attachment2file($i);
        }
    }

    private function record_msg($subject,$timestamp,$body) {
        //check msg email and timestamp
        if(!$this->check_msg_data($timestamp)) return false;

        //check if this message is duplicate
        if($this->check_if_msg_is_duplicate($timestamp)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->mailbox->deleteMessages($this->msg_uid);
            echo 'duplicate';
            return false;
        }

        //check if this email is on black list
        if(in_array($this->user_email,$this->emails_black_list_ar)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->mailbox->deleteMessages($this->msg_uid);
            echo 'black list user';
            return false;
        }
        //check if this email is from myself
        if($this->user_email==$this->support_email) {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->mailbox->deleteMessages($this->msg_uid);
            echo 'black list user';
            return false;
        }

        //check if user with this email is registered
        if(!$this->check_if_user_is_registered()) {//user isn't registered
            $this->uSup->reject_forbidden_user_request_notification($this->user_email,$this->site_id);//we must reject user's request
            /** @noinspection PhpUndefinedMethodInspection */
            $this->mailbox->deleteMessages($this->msg_uid);//we must delete this email from IMAP
            echo 'unregistered user';
            return false;
        }

        $qr=$this->uAuth->user_id2user_data($this->user_id,"firstname,lastname,secondname");
        $this->user_firstname=uString::sql2text($qr->firstname,1);
        $this->user_secondname=uString::sql2text($qr->secondname,1);
        $this->user_lastname=uString::sql2text($qr->lastname,1);

        $body_plain=strip_tags($body);
        $body_plain=preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]{2}/', "\n", $body_plain));
        $body_plain=preg_replace('/\[cid:(.*?)@[\w.]*\]/', '', $body_plain);

        $subject_encoded=uString::text2sql($subject);


        //write out msg info
        $email_id=$this->get_last_email_msg_id();//get last email_id in db
        $this->write_msg2db($email_id,$timestamp,$subject_encoded,$body_plain);


        //check if this email is reply to request
        if($body_plain_1=$this->check_if_reply($body_plain,$subject)) {//this email is reply
            $this->is_reply=true;
        }
        else {
            $this->is_reply=false;
            $this->from_user=true;
            //open new request
            $this->open_request($subject,$body_plain);
        }

        //get attachments
        $this->save_attachments();

        //Email notifications
        $this->msg_hash=$this->uSup->make_msg_hash($this->msg_id,$this->site_id);
        if($this->is_reply) {
            //notify user and operator that we've added new reply
            $this->notify_about_msg($this->tic_id,$this->site_id);
        }
        else {
            //notify user and operator that we've added new request
            $this->notify_about_request($this->tic_id,$this->site_id);
        }

        //delete email
        /** @noinspection PhpUndefinedMethodInspection */
        $this->mailbox->deleteMessages($this->msg_uid);

        return 1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->start_time=time();
        $this->secret="oZmcDvhvmnwJ!lwMnS7FQb?r4q";

        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uAuth=new \uAuth\common($this->uCore);
        $this->uSup=new \uSupport\common($this->uCore);

        $this->check_data();
        if(!$this->set_vars()) exit('not configured');

        if(!$this->connect()) exit('can not connect');

        $this->check_msgs();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->mailbox->close();
        $this->mailbox->purge;
        echo 'done';
    }
}
ob_start();

new cron_check_new_emails($this);
