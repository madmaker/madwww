<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uAuth/inc/avatar.php';
require_once "uSupport/classes/common.php";

class uSup_request_save {
    public $uFunc;
    public $uSes;
    public $uSup;
    public $two_level;
    public $com_id;
    private $uCore,
        $is_com_client,$is_com_admin,$is_consultant,$is_operator,
        $qu_comps,
        $tic_id,
        $use_smtp,$smtp_settings,
        $user_avatar;
    private function check_data() {
        if(!isset($_POST['tic_id'])) $this->uFunc->error(10);
        $this->tic_id=$_POST['tic_id'];
        if(!uString::isDigits($this->tic_id)) $this->uFunc->error(20);
    }
    private function check_access() {
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=false;
        //consultant or operator
        if($this->uSes->access(9)) {
            $this->is_operator=true;
            return true;
        }
        if($this->uSes->access(8)) {
            $this->is_consultant=true;
            return true;
        }

        //get request's com_id
        if(!$req=$this->uSup->req_id2info($this->tic_id,"company_id")) $this->uFunc->error(25);
        $this->com_id=(int)$req->company_id;

        //get if company has two_level support
        if($com=$this->uSup->com_id2com_info($this->com_id,"two_level")) $this->two_level=(int)$com->two_level;
        else $this->two_level=0;

        //check if client of any company or admin
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_id`,
        `admin`
        FROM
        `u235_com_users`
        WHERE
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(30);
        if(mysqli_num_rows($query)) {
            $this->qu_comps="(";
            while($com=$query->fetch_object()) {
                if($com->admin=='1') $this->is_com_admin=true;
                $this->qu_comps.="`company_id`='".$com->com_id."' OR ";
            }
            $this->qu_comps.="1=0)";
            if(!$this->is_com_admin) $this->is_com_client=true;
            return true;
        }
        return false;
    }
    private function tic_subject_save() {
        if(!$this->is_consultant&&!$this->is_operator) die("{'status' : 'forbidden'}");
        $tic_subject=trim($_POST['tic_subject']);
        if(!strlen($tic_subject)) die('{"status":"error","msg":"title is empty"}');

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_subject`='".uString::text2sql($tic_subject)."'
        WHERE
        `tic_id`='".$_POST['tic_id']."' AND
        `tic_status`!='req_closed' AND
        `tic_status`!='case_closed' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(40);

        echo '{
        "status":"done",
        "tic_subject":"'.rawurlencode($tic_subject).'",
        "tic_id":"'.$this->tic_id.'"
        }';
        exit;
    }
    private function tic_cat_save() {
        if(!$this->is_consultant&&!$this->is_operator) die("{'status' : 'forbidden'}");
        $tic_cat=trim($_POST['tic_cat']);
        if(!uString::isDigits($tic_cat)) $this->uFunc->error(50);

        //get cat's title
        if(!$query=$this->uCore->query("uSup","SELECT
        `cat_title`
        FROM
        `u235_requests_cats`
        WHERE
        `cat_id`='".$tic_cat."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(60);
        if(!mysqli_num_rows($query)) $this->uFunc->error(70);
        $cat=$query->fetch_object();

        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `tic_cat`='".uString::text2sql($tic_cat)."'
        WHERE
        `tic_id`='".$_POST['tic_id']."' AND
        `tic_status`!='req_closed' AND
        `tic_status`!='case_closed' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(80);

        echo '{
        "status":"done",
        "cat_id":"'.$tic_cat.'",
        "cat_title":"'.rawurlencode(uString::sql2text($cat->cat_title,1)).'",
        "tic_id":"'.$this->tic_id.'"
        }';
        exit;
    }
    private function tic_cons_save() {
        $has_access=0;

        if($this->is_consultant||$this->is_operator) $has_access=1;
        else {
            if($this->two_level) {//if company has two level support
                //check if current user is company's admin
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uSup")->prepare("SELECT 
                    user_id 
                    FROM 
                    u235_com_users 
                    WHERE 
                    user_id=:user_id AND 
                    admin=1 AND 
                    com_id=:com_id AND 
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    $user_id=$this->uSes->get_val("user_id");
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
                if($stm->fetch(PDO::FETCH_OBJ)) {//check if current request is not escalated yet
                    if(!$req=$this->uSup->req_id2info($this->tic_id,"escalated")) $this->uFunc->error(95);
                    if(!(int)$req->escalated) $has_access=1;
                }
            }
        }

        if(!$has_access) die("{'status' : 'forbidden'}");

        $cons_id=trim($_POST['cons_id']);
        if(!uString::isDigits($cons_id)) $this->uFunc->error(100);

        //get consultant's name
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            firstname,
            lastname,
            email
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_users.user_id=u235_usersinfo.user_id AND
            u235_users.status=u235_usersinfo.status 
            WHERE
            u235_users.user_id=:user_id AND
            u235_users.status='active' AND
            u235_usersinfo.site_id=:site_id
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $cons_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            if(!$cons=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(105);
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}

        //get author's name (current user)
        if(!$query=$this->uCore->query("uAuth","SELECT
        `u235_users`.`firstname`,
        `u235_users`.`lastname`,
        `u235_users`.`avatar_timestamp`
        FROM
        `u235_users`,
        `u235_usersinfo`
        WHERE
        `u235_users`.`user_id`='".$this->uSes->get_val("user_id")."'
        LIMIT 1
        ")) $this->uFunc->error(120);
        if(!mysqli_num_rows($query)>0) $this->uFunc->error(130);
        $author=$query->fetch_object();

        //Update request's cons_id
        if(!$this->uCore->query("uSup","UPDATE
        `u235_requests`
        SET
        `cons_id`='".$cons_id."',
        `tic_changed_timestamp`='".time()."'
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(140);

        //get new msg_id
        if(!$query=$this->uCore->query("uSup","SELECT
        `msg_id`
        FROM
        `u235_msgs`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `msg_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(150);
        if(mysqli_num_rows($query)) {
            $qr=$query->fetch_object();
            $msg_id=$qr->msg_id+1;
        }
        else $msg_id=1;

        //get request's subject
        if(!$query=$this->uCore->query("uSup","SELECT
        `tic_subject`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(160);
        if(!mysqli_num_rows($query)) $this->uFunc->error(170);
        $req=$query->fetch_object();

        //Add new msg about changing consultant
        $msg_text=$cons->firstname." ".$cons->lastname." продолжит решение задачи";
        $msg_time=time();
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
        '".$msg_id."',
        '".$msg_text."',
        '".$this->uSes->get_val("user_id")."',
        '".$msg_time."',
        '1',
        '".site_id."'
        )")) $this->uFunc->error(180);

        $html='<div class="msg_text">
            <h1>Запрос #'.$this->tic_id.' '.uString::sql2text($req->tic_subject,1).'</h1>
            <p>Вам назначен кейс <a href="'.u_sroot.$this->uCore->mod.'/request_show/'.$this->tic_id.'">#'.$this->tic_id.'</a></p>
        </div>
        <p><small>На всякий случай: <a href="'.u_sroot.$this->uCore->mod.'/requests">войти в техподдержку можно здесь</a>. Сбросить пароль можно в диалоге авторизации</small></p>
';
        $title='Вам назначен Кейс '.$this->tic_id;

        $from_name=$this->uCore->uFunc->getConf("support_email_fromname","uSup");
        $from_email=$this->uCore->uFunc->getConf("support_email","uSup");

        $this->uFunc->mail($html,$title,$cons->email,$from_name,$from_email,u_sroot,site_id,'',$this->use_smtp,$this->smtp_settings);

        echo '{
        "status":"done",
        "support_email_fromname":"'.$from_name.'",
        "support_email":"'.$from_email.'",
        "cons->email":"'.$cons->email.'",
        "cons_id":"'.$cons_id.'",
        "cons_name":"'.rawurlencode(uString::sql2text($cons->firstname,1).' '.uString::sql2text($cons->lastname,1)).'",
        "tic_id":"'.$this->tic_id.'",
        "author_name":"'.rawurlencode(uString::sql2text($author->firstname,1).' '.uString::sql2text($author->lastname,1)).'",
        "msg_time":"'.date('d.m.Y H:i',$msg_time).'",
        "author_avatar_src":"'.rawurlencode($this->user_avatar->get_avatar('uSup_com_users_list',$this->uSes->get_val("user_id"),$author->avatar_timestamp)).'",
        "msg_text":"'.rawurlencode(uString::sql2text($msg_text,1)).'"
        }';
        exit;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uSup=new \uSupport\common($this->uCore);

        if(!$this->uSes->access(2)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->check_access();

        $this->use_smtp=$this->uCore->uFunc->getConf('smtp_use_madwww_server','uSup')=='0';

        if($this->use_smtp) {
            //port
            //server_name
            //use_ssl
            //user_name
            //password
            $this->smtp_settings['server_name']=$this->uCore->uFunc->getConf('smtp_server_name','uSup');
            $this->smtp_settings['port']=$this->uCore->uFunc->getConf('smtp_port','uSup');
            $this->smtp_settings['user_name']=$this->uCore->uFunc->getConf('smtp_user_name','uSup');
            $this->smtp_settings['password']=$this->uCore->uFunc->getConf('smtp_password','uSup');
            $this->smtp_settings['use_ssl']=$this->uCore->uFunc->getConf('smtp_use_ssl','uSup')=='1';
        }
        else $this->smtp_settings[0]=0;

        $this->user_avatar=new uAuth_avatar($this->uCore);

        if(isset($_POST['tic_subject'])) {
            $this->tic_subject_save();
        }
        elseif(isset($_POST['tic_cat'])) {
            $this->tic_cat_save();
        }
        elseif(isset($_POST['cons_id'])) {
            $this->tic_cons_save();
        }
        else die("{'status' : 'forbidden'}");
    }
}
new uSup_request_save($this);