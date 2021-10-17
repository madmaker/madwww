<?php
namespace uAuth;
use uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';

class register_bg {
    /**
     * @var string
     */
    private $login;
    /**
     * @var int
     */
    private $isPhone;
    /**
     * @var int
     */
    private $isEmail;
    /**
     * @var common
     */
    private $uAuth;
    /**
     * @var \processors\uFunc
     */
    private $uFunc;

    private function checkData() {
        if(!isset($_POST['email'])) {
            $this->uFunc->error(1586321737,1);
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
                'status'=>'error',
                'msg'=>'wrong email'
            ));
            exit;
        }
    }
    private function checkUser() {
        //check if email is already registered in users
        $user=$this->uAuth->userLogin2info('user_id',$this->login,($this->isEmail?'email':'cellphone'));
        //if email isn't registered we can continue
        if(!$user) {
            return true;
        }
        //if email is registered we must check user's status
        $status=$user->status;
        $user_id=$user->user_id;

        //then we return current user status to browser
        if($status === 'active') {
            //check if user is already registered on this certain site
            $user=$this->uAuth->user_id2usersinfo($user_id, 'status');
            if(!$user) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'user is registered'
                ));
                exit;
            }
            $site_status=$user->status;
            if($site_status === 'active') {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'user is registered'
                ));
                exit;
            }

            if($status === 'banned') {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'user is banned'
                ));
                exit;
            }

            print json_encode(array(
                'status'=>'error',
                'msg'=>'unknown user status '.$site_status
            ));
            exit;
        }

        if($status === 'banned') {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'user is banned'
            ));
            exit;
        }

        print json_encode(array(
            'status'=>'error',
            'msg'=>'unknown user status '.$status
        ));
        exit;
    }

    public function __construct(&$uCore) {
        $this->uFunc=new \processors\uFunc($uCore);
        $uSes=new uSes($uCore);
        if(!$uSes->access(11)) {
            print json_encode(array(
                'status'=>'forbidden'
            ));
            exit;
        }

        $this->uAuth=new common($uCore);

        $this->checkData();

        $this->checkUser();

        $password=uFunc::genPass();
        $user_id=$this->uAuth->add_new_user($this->login, '', '',($this->isEmail?$this->login:''),$password,($this->isPhone?$this->login:''),'active');
        $this->uAuth->add_user2usersinfo($user_id,'active');
        if($this->isEmail) {
            $this->uAuth->emailUserAboutRegistration('',$this->login,$password);
        }
        else {
            $this->uAuth->smsUserAboutRegistration($this->login,$password);
        }

        print json_encode(array(
            'status'=>'done'
        ));
        exit;
    }
}

new register_bg($this);
