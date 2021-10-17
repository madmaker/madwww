<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uAuth\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSupport/classes/common.php";
require_once "uAuth/classes/common.php";

class company_info_add_user_by_email{
    public $uFunc;
    public $uSes;
    public $uAuth;
    public $uSup;
    private $uCore,
        $is_com_admin,$is_operator,
        $com_id,$com_title,$email,$user_id,$firstname,$secondname,$lastname,
        $support_email,$support_email_from,$use_smtp,$smtp_settings,$term;

    private function check_data() {
        if(!isset($_POST['com_id'],$_POST['email'],$_POST['firstname'],$_POST['secondname'],$_POST['lastname'])) $this->uFunc->error(10);
        $this->com_id=$_POST['com_id'];
        if(!uString::isDigits($this->com_id)) $this->uFunc->error(20);
        $this->email=$_POST['email'];
        if(!uString::isEmail($this->email)) die("{'status' : 'error', 'msg' : 'check_email'}");
        $this->firstname=trim($_POST['firstname']);
        if(empty($this->firstname)) die("{'status' : 'error', 'msg' : 'firstname_empty'}");
        $this->secondname=trim($_POST['secondname']);
        $this->lastname=trim($_POST['lastname']);
        if(empty($this->lastname)) die("{'status' : 'error', 'msg' : 'lastname_empty'}");

        // check if company exists
        $com=$this->uSup->com_id2com_info($this->com_id,"com_title");
        if(!$com) $this->uFunc->error(30);
        $this->com_title=uString::sql2text($com->com_title,1);
    }
    private function check_access() {
        $this->is_com_admin=$this->is_operator=false;
        //operator
        if($this->uSes->access(201)) return $this->is_operator=true;

        //check if client or admin of this company
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) return true;
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        return false;
    }
    private function addUser() {
        //check if user exists on this site
        $user=$this->uAuth->userLogin2info('user_id,firstname,secondname,lastname,email,status',$this->email,'email');

        if($user) {//user exists on cloud
            if($usersinfo=$this->uAuth->user_id2usersinfo($user->user_id,"status")) {//user exists on this site
                if ($usersinfo->status != 'active') die("{'status' : 'error', 'msg' : 'user_inactive'}");

                $this->user_id = $user->user_id;
                $this->firstname = uString::sql2text($user->firstname);
                $this->secondname = uString::sql2text($user->secondname);
                $this->lastname = uString::sql2text($user->lastname);

                //check if user already added to this company
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSup")->prepare("SELECT
                    user_id
                    FROM
                    u235_com_users
                    WHERE
                    user_id=:user_id AND
                    com_id=:com_id AND
                    site_id=:site_id
                    ");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}


                /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
                if ($stm->fetch(PDO::FETCH_OBJ)) {
                    //user is already added to this company. do nothing
                    die("{'status' : 'error', 'msg' : 'user_is_already_added', 'user_name':'" . rawurlencode('#' . $this->user_id . ' ' . $this->firstname . ' ' . $this->secondname . ' ' . $this->lastname . ' ') . "'}");
                } else {//user isn't added to company yet
                    //add user to company
                    $this->uSup->attach_user2company($this->user_id,$this->com_id,1);
                }
            }
            else {//user does not exists on current site
                if($user->status!='active') die("{'status' : 'error', 'msg' : 'user_inactive_mp'}");
                $this->user_id=$user->user_id;
                $this->firstname=uString::sql2text($user->firstname);
                $this->secondname=uString::sql2text($user->secondname);
                $this->lastname=uString::sql2text($user->lastname);

                //we must add user to this site
                $this->uAuth->add_user2usersinfo($this->user_id,"active",site_id);
                //attach user to comp
                $this->uSup->attach_user2company($this->user_id,$this->com_id,1);
            }
        }
        else {//user isn't found on cloud
            //we must create user
            $passUnencrypted=uFunc::genPass();
            $firstname=uString::text2sql($this->firstname);
            $secondname=uString::text2sql($this->secondname);
            $lastname=uString::text2sql($this->lastname);

            $this->user_id=(int)$this->uAuth->add_new_user($firstname,$secondname,$lastname,$this->email,$passUnencrypted,"","active");
            $this->uAuth->emailUserAboutRegistration($this->firstname,$this->email,$passUnencrypted);

            //add new user to usersinfo for this site
            $this->uAuth->add_user2usersinfo($this->user_id,"active");
            //add user to company
            $this->uSup->attach_user2company($this->user_id,$this->com_id,1);
        }
    }
    private function check_email_and_resp_data() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            firstname,secondname,lastname
            FROM
            u235_users
            JOIN
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id
            WHERE
            u235_users.user_id=:user_id AND
            u235_usersinfo.site_id=:site_id AND
            u235_users.email=:email
            LIMIT 1
            ");

            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email', $this->term, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $array = array (
                    "label" => $this->term,
                    "firstname" => $row["firstname"],
                    "secondname" => $row["secondname"],
                    "lastname" => $row["lastname"]
                );
                echo json_encode($array, JSON_UNESCAPED_UNICODE);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSup=new \uSupport\common($this->uCore);
        $this->uAuth=new common($this->uCore);

        if (isset($_GET["term"])) {
            $this->term = $_GET["term"];
            $this->check_email_and_resp_data();
        }
        else {

        $this->check_data();

        if(!$this->check_access()) die('{"status":"forbidden"}');

        $this->support_email=$this->uCore->uFunc->getConf("support_email","uSup");
        $this->support_email_from=$this->uCore->uFunc->getConf("support_email_fromname","uSup");
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

        $this->addUser();
        echo "{'status' : 'done'}";
        }
    }
}
new company_info_add_user_by_email($this);
