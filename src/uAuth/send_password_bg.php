<?php
namespace uAuth;
use PDO;
use PDOException;
use processors\uFunc;
use translator\translator;
use uSes;
use uString;

require_once 'uAuth/classes/common.php';
require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'translator/translator.php';

class send_password_bg {
    public $uAuth;
    public $uFunc;
    public $uSes;
    /**
     * @var translator
     */
    private $translator;
    private $email;
    /**
     * @var string
     */
    private $login;
    private $isPhone;
    private $isEmail;
    private $user_id, $firstname, $cellphone;

    private function checkData() {
        if(!isset($_POST['email'])) {
            print json_encode(array(
                'status' => 'error',
                'msg' => 'user is not found',
                'description'=>1586316872
            ));
            exit;
        }
        $this->login=trim($_POST['email']);

        $use_MAD_SMS_to_send_SMS=(int)$this->uFunc->getConf('use MAD SMS to send SMS', 'content',0);

        if(uString::isEmail($this->login)) {
            $this->isEmail=1;
            $this->isPhone=0;
        }
        elseif($use_MAD_SMS_to_send_SMS&&uString::isPhone($this->login)) {
            $this->isPhone=1;
            $this->isEmail=0;
        }
        else {
            print json_encode(array(
                'status' => 'error',
                'msg' => 'user is not found',
                'description'=>1586316874
            ));
            exit;
        }
    }
    private function getUserData() {
        if(!$user=$this->uAuth->userLogin2info('user_id,email,cellphone,firstname,status',$this->login,($this->isEmail?'email':'cellphone'))) {
            print json_encode(array(
                'status' => 'error',
                'msg' => 'user is not found',
                'description'=>1586316857
            ));
            exit;
        }
        if($user->status !== 'active') {
            print json_encode(array(
                'status'=> 'error',
                'msg'=> 'wrong status',
                'cur_status'=>$user->status
            ));
            exit;
        }

        $this->user_id=$user->user_id;
        $this->firstname=$user->firstname;
        $this->cellphone=$user->cellphone;
        $this->email=$user->email;
    }
    private function check_last_one_off_time() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
            timestamp
            FROM
            pass_one_off
            WHERE
            user_id=:user_id
            ORDER BY 
            timestamp DESC
            LIMIT 1
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return 1;
            }
            $time=(int)$qr->timestamp;

            if($time>(time()-300/*5 minutes*/)) {
                print json_encode(array(
                'status'=>'error',
                'msg'=>'not yet',
                'hash_time'=> date('H=>i', $time) ,
                'next_time'=> date('H:i', ($time + 300))
                ));
                exit;
            }
            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('1586312582'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private function send_password() {
        $timestamp=time();
        $code=uFunc::genCode(8);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo('uAuth')->prepare('REPLACE INTO 
            pass_one_off (
            user_id,
            pass,
            timestamp
            ) VALUES (
            :user_id,
            :pass,
            :timestamp
            )');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pass', $code,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1586313222'/*.$e->getMessage()*/,1);}

        if($this->isEmail) {
            $this->send_email_with_code($code);
        }
        else {
            $this->send_sms_with_code($code);
        }
    }
    private function send_email_with_code($code) {
        $html='<p>'.$this->translator->txt('Hello'). ', ' .$this->firstname.'</p>
        <div class="msg_text">
            <p>'.$this->translator->txt('Your password is').' <span style="font-size: 1.5em; font-weight: bold">'.$code.'</p>
        </div>
        <p><small><a href="'.u_sroot.'uAuth/profile_edit">'.$this->translator->txt('You can change your password in your profile after sign in').'</a></small></p>';

        $title=$this->translator->txt('Your password is').' '.$code;

        $this->uFunc->sendMail($html,$title,$this->email);
    }
    private function send_sms_with_code($code) {
        $text= 'Одноразовый пароль: ' .$code."\n".site_name;

        $this->uFunc->sendSms($text,$this->login);
    }
    public function __construct(&$uCore) {
        $this->uAuth=new common($uCore);
        $this->uFunc=new uFunc($uCore);
        $uSes=new uSes($uCore);
        $this->translator=new translator(site_lang,'uAuth/send_password_bg.php');

        if(!$uSes->access(11)) {
            print json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        $this->checkData();
        $this->getUserData();

        if($this->check_last_one_off_time()) {
            $this->send_password();
        }

        print json_encode(array(
            'status' => 'done'
        ));
    }
}
new send_password_bg($this);
