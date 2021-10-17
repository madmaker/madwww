<?php
namespace uAuth;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';

/**
 Class profile_email_phone_change_confirm_bg

 ## Validates code to change email or phone in user profile

 - Receives validation code
 - Checks if this code validates change of email or phone
 - If correct - saves new phone or email (stored by uAuth/profile_update_bg)

 ## ACL: Any authorized user
 ---

 # Request
 ## POST JSON
 - code - validation code sent with email or sms
 - field - phone | email
 ## COOKIE
  - ses_id `int`
  - user_id `int`

 * ---
 *
 # Response JSON
 - status
      - success
      - error
      - forbidden
 - msg - in case of error occurred
      - wrong request - if server have not received required data (code, field)
      - wrong field - if `field` value in request is wrong
     - sms send is not supported - if sms sending system is not configured
     - code is expired - if verification code has been expired
       - wrong code - if verification code has wrong value

 * ---
 *
  # Example
    ## Request 1
    ~~~json
    {
    "code": 123456,
    "field": "email"
    }
    ~~~
    ## Response 1
    ~~~json
    {
    "status":"success"
    }
    ~~~
    ## Request 1
    ~~~json
    {
    "code": 123456,
    "field": "phone"
    }
    ~~~
    ## Response 1
    ~~~json
    {
    "status":"error",
    "msg": "wrong code"
    }
    ~~~
 * ---
 * @package uAuth
 * @api
 */
class profile_email_phone_change_confirm_bg {
    /**
     * @var common
     */
    private $uAuth;
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var uFunc
     */
    private $uFunc;

    /**
     * Check data received in request and runs updateEmailOrPhone() if all is correct
     * @return string saved new phone | email
     */
    private function checkData() {
        if(!isset(
            $_POST['code'],
            $_POST['field']
        )) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request'
            ));
            exit;
        }

        if($_POST['field']==='email') {
            $field = 'email';
        }
        elseif($_POST['field']==='phone') {
            $field = 'cellphone';
        }
        else {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong field'
            ));
            exit;
        }

        $code=trim($_POST['code']);

        $user_id=$this->uSes->get_val('user_id');
        if (isset(
            $_SESSION['uAuth']['profile_update_bg']['change'.$field]['code'],
            $_SESSION['uAuth']['profile_update_bg']['change'.$field]['timestamp'],
            $_SESSION['uAuth']['profile_update_bg']['change'.$field]['password'],
            $_SESSION['uAuth']['profile_update_bg']['change'.$field][$field]
        )) {
            if($field==='cellphone'&&!(int)$this->uFunc->getConf('use MAD SMS to send SMS','content',false)) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'sms send is not supported'
                ));
                exit;
            }

            if($_SESSION['uAuth']['profile_update_bg']['change'.$field]['timestamp']<(time()-300)) {//5min
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'code is expired'
                ));
                exit;
            }
            if($_SESSION['uAuth']['profile_update_bg']['change'.$field]['code']!==$code) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong code'
                ));
                exit;
            }

            $password=$_SESSION['uAuth']['profile_update_bg']['change'.$field]['password'];

            $value=$_SESSION['uAuth']['profile_update_bg']['change'.$field][$field];

            $this->updateEmailOrPhone($user_id,$field,$value,$password);

            unset($_SESSION['uAuth']['profile_update_bg']['change'.$field]);

            return $value;
        }

        print json_encode([
            'status'=>'error',
            'msg'=>'code is expired'
        ]);
        exit;
    }

    /**
     * Saves new email or phone value to database
     *
     * @param int $user_id : user id
     * @param string $field : phone | email
     * @param string $value : new value for email or phone
     * @param string $password : unencrypted password to be encrypted with new phone|email value and saved
     * @param $passwordEncrypted : encrypted password
     * @return bool true | false
     */
    private function updateEmailOrPhone($user_id, $field, $value, $password) {
        if(!$user_data=$this->uAuth->user_id2user_data($user_id,'email,cellphone')) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request',
                'debug'=>'user is not found'
            ));
            exit;
        }
        $currentEmail=$user_data->email;
        $currentPhone=$user_data->cellphone;

        if($field==='cellphone') {
            $this->uAuth->updateUserPhone($user_id,$value,$password);
            //current phone
            $this->uAuth->sendNotificationThatPhoneAndPasswordAreChanged($currentPhone,$value,$password);
            //new phone
            $this->uAuth->sendNotificationThatPhoneAndPasswordAreChanged($value,$value,$password);

            return true;
        }
        if($field==='email') {
            $this->uAuth->updateUserEmail($user_id,$value,$password);
            //current phone
            $this->uAuth->sendNotificationThatEmailAndPasswordAreChanged($currentEmail,$value,$password);
            //new phone
            $this->uAuth->sendNotificationThatEmailAndPasswordAreChanged($value,$value,$password);

            return true;
        }

        return false;
    }

    /**
     * profile_email_phone_change_confirm_bg constructor.
     * @param $uCore
     */
    public function __construct(&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->uSes=new uSes($uCore);
        $this->uAuth=new common($uCore);

        if(!$this->uSes->access(2)) {
            print json_encode(array(
                'status'=>'forbidden'
            ));
            exit;
        }

        $value=$this->checkData();

        print json_encode(array(
            'status'=>'success',
            'value'=>$value
        ));
        exit;
    }
}
new profile_email_phone_change_confirm_bg($this);
