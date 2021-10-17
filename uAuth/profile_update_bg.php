<?php
namespace uAuth;

use processors\uFunc;
use translator\translator;
use uCore;
use uSes;
use uString;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'uAuth/classes/common.php';
require_once 'translator/translator.php';

/**
 * profile_update_bg constructor.
 *
 * ACL 2 - for any authorized user
 *
 * ##  fields that can be updated
 * - firstname - profile owner, mad root
 * - site-specific fields - profile owner, site admin
 * - email - profile owner, mad root
 * - password - profile owner, mad root
 * - phone - profile owner, mad root
 * - isMadRoot - (regular user OR Mad Root) - mad root
 *
 * # Request
 * ## POST JSON
 * - user_id `int` - user_id (profile owner sends his user_id)
 * - firstname `string` - profile owner, mad root
 * - secondname `string` - profile owner, mad root
 * - lastname `string` - profile owner, mad root
 * - fields `array` of key-value -  profile owner, site root
 *      - {'id':'field_id', 'value':'field_value'},
 *      - {'id':'field_id', 'value':'field_value'}
 * - isMadRoot `int` - mad root
 *      - 0 - user becomes regular user
 *      - 1 - user becomes mad root
 * - newEmail `string` - profile owner (currentPassword required), mad root
 *      - User will receive validation code to new email
 *      - User will receive notification email to old email
 * - newPhone `string` - profile owner (currentPassword required) , mad root
 *      - User will receive validation code to new phone
 *      - User will receive notification sms to old phone
 * - newPassword `string` - profile owner (currentPassword required), mad root
 * - currentPassword `string` - profile owner.
 *
 * ## COOKIES
 * - user_id `int` - Authorized user id
 * - ses_id `string` - Access token given after signing in
 *
 * # Response JSON
 * - status `string`
 *      - success
 *      - forbidden
 *      - error
 * - code `string` - in case of error
 *      - wrong request - in case of wrong request (see debug to resolve)
 *      - wrong user_id - if user_id is not found in request or user_id has wrong format
 *      - wrong firstname - if firstname has wrong format
 *      - wrong current password - if current password differs from user's real password
 *      - wrong email - if email has wrong format
 *      - wrong phone - if phone has wrong format
 *      - email is used in someone's profile - if email address is already used. We can't assign same email to different accounts
 *      - phone is occupied - if phone is already used. We can't assign same phone to different accounts
 *      - validation email send error - Email with validation code could not be sent
 *      - validation sms send error - Sms with validation code could not be sent
 *      - sms sending is not provided - There are no sms service configured
 *      - new password is too weak - If new password is too weak, to short, empty and so on
 * - debug `string` - additional information in case of error
 * @package uAuth
 */
class profile_update_bg {
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var int
     */
    private $use_MadSMS;
    /**
     * @var common
     */
    private $uAuth;
    /**
     * @var uCore
     */
    private $uCore;
    /**
     * @var int
     */
    private $is_mad_root;
    /**
     * @var int
     */
    private $is_admin;
    /**
     * @var int
     */
    private $is_profile_owner;
    /**
     * @var translator
     */
    private $translator;

    private function forbidden() {
        print json_encode([
            'status'=>'forbidden'
        ]);
        exit;
    }
    /**
     * Checks required data in request and validates user permissions
     *
     * @return int $user_id
     */
    private function checkData(){
        $uSes=new uSes($this->uCore);

        if(!$uSes->access(2)) {$this->forbidden();}

        if(!isset($_POST['user_id'])) {
            print json_encode([
                'status'=>'error',
                'code'=>'wrong request',
                'debug'=>'no user_id in request'
            ]);
            exit;
        }
        if(!uString::isDigits($_POST['user_id'])) {
            print json_encode([
                'status'=>'error',
                'code'=>'wrong user_id',
                'debug'=>'wrong user_id format in request'
            ]);
            exit;
        }
        $user_id=(int)$_POST['user_id'];

        $this->is_profile_owner=$this->is_admin=$this->is_mad_root=0;

        $currentUserId=$uSes->get_val('user_id');

        if($user_id===$currentUserId) {
            $this->is_profile_owner=1;
        }
        if($uSes->access(13)) {
            $this->is_admin=1;
        }
        if($uSes->access(28)) {
            $this->is_mad_root=1;
        }

//        $this->is_admin=0;
//        $this->is_mad_root=0;

        //Check of user exists on this site
        if(!$userData=$this->uAuth->userExistsOnSite($user_id)) {
            print json_encode(array(
                'status' => 'error',
                'code'=>'wrong user_id',
                'debug'=>'user is not exists'
            ));
            exit;
        }
        return $user_id;
    }

    /**
     * Emits when user want to change email address.
     *
     * Sending validation code in Email message to new email address.
     *
     * Stores new password and new email while user will not validate new email.
     *
     * When user validates new email by code, system will update a email address to new one.
     * @param string $email
     * @return array|bool $result=validationCode if email is sent and false if email send has failed
     */
    private function sendValidationCodeToNewEmailToVerifyEmailChange($email) {
        $code=uFunc::genCode(8);

        $subject=$this->translator->txt('Your email confirmation code is')." $code";
        $content= '<p>' .$this->translator->txt('Your email confirmation code is')." <span style='font-weight: bold; font-size: 1.5em'>$code</span> </p>";

        if($this->uFunc->sendMail($content,$subject,$email)) {
            return $code;
        }

        return false;
    }

    /**
     * Emits when user want to change phone number
     *
     * Sending validation code in sms to new phone number
     *
     * Stores new password and new phone while user will not validate new phone number.
     *
     * When user validates new phone by code, system will update a phone to new one.
     * @param string $phone
     * @return array $result=array('smsIsSent' : 'true|false', 'code' : 'validationCode')
     */
    private function sendValidationCodeToNewPhoneToVerifyPhoneChange($phone) {
        if(!$this->use_MadSMS) {
            return [
                'smsIsSent'=>false,
                'code'=>''
            ];
        }
        $code=uFunc::genCode(8);

        $content= $this->translator->txt('Your phone confirmation code is')." $code";

        return [
            'smsIsSent'=>$this->uFunc->sendSms($content,$phone),
            'code'=>$code
        ];
    }

    /**
     * Sends notification to user that his current email is being changed to new one
     * @param string $email
     * @return bool emailSendResult
     */
    private function sendNotificationAboutEmailChangingToCurrentEmail($email) {
        $subject=$this->translator->txt('Email in your account is being changed');
        $content= '<p>' .$this->translator->txt('Your email is being changed on').' <a href="'.u_sroot.'">'.site_name.'</a></p>
        <p>'.$this->translator->txt('If it is not you changing email - change password').'</p>';

        return $this->uFunc->sendMail($content,$subject,$email);
    }

    /**
     * Sends sms to current user phone that profile's phone number is being changed
     * @param $phone
     * @return bool
     */
    private function sendNotificationAboutPhoneChangingToCurrentPhone($phone) {
        if(!$this->use_MadSMS) {
            return false;
        }
        $content= $this->translator->txt('Your phone is being changed on').' '.strip_tags(site_name);

        return $this->uFunc->sendSMS($content,$phone);
    }

    /**
     * Updates user information from request
     * @param int $user_id
     * @return bool|string
     */
    private function updateUserDataFromRequest($user_id) {
        if(isset($_POST['firstname'])) {
            if($this->is_profile_owner||$this->is_mad_root) {
                $firstname=trim($_POST['firstname']);
                if(strlen($firstname)<2) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong firstname',
                        'debug'=>'too short'
                    ]);
                    exit;
                }
                $firstname=uString::text2sql($firstname);

                if(!isset($_POST['secondname'])) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong request',
                        'debug'=>'second name is not found'
                    ]);
                    exit;
                }
                $secondname=uString::text2sql(trim($_POST['secondname']));

                if(!isset($_POST['lastname'])) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong request',
                        'debug'=>'lastname name is not found'
                    ]);
                    exit;
                }
                $lastname=uString::text2sql(trim($_POST['lastname']));

                $this->uAuth->saveUserFirstSecondLastNames($user_id,$firstname,$secondname,$lastname);
            }
            else {
                $this->forbidden();
            }
        }

        if(isset($_POST['isMadRoot'])&&$this->is_mad_root) {
            $isMadRoot=(int)$_POST['isMadRoot'];
            if($isMadRoot!==1) {
                $isMadRoot = 0;
            }
            $this->uAuth->grantOrRevokeMadRootAccessToUser($user_id,$isMadRoot);
        }

        /*if(isset($_POST['fields'])) {//TODO-nik87 доделать
            if($this->is_profile_owner||$this->is_admin) {
                $field_num=$_POST['field_num'];
                if(!uString::isDigits($field_num)) {
                    $this->uFunc->error(1585458983, 1);
                }

                $update_fields_id=[];
                $update_fields_value=[];
                for($i=0;$i<$field_num;$i++) {
                    if(!isset($_POST["field_$i"])) {
                        continue;
                    }
                    if(!isset($_POST["field_ids_$i"])) {
                        continue;
                    }

                    if(!uString::isDigits($_POST['field_ids_'.$i])) {
                        continue;
                    }
                    $field_val=uString::text2sql($_POST["field_$i"]);

                    $field_id=$_POST['field_ids_'.$i];

                    //check if user can see this field
                    try {
                        $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
                    visible,
                    editable
                    FROM
                    u235_usersinfo_site_labels
                    WHERE
                    field_id=:field_id AND
                    field_type!=3 AND
                    site_id=:site_id
                    ');
                        $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
                        $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        $stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('1585457287'.$e->getMessage(),1);}

                    if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
                        continue;
                    }
                    $qr->visible=(int)$qr->visible;
                    $qr->editable=(int)$qr->editable;

                    if(($qr->visible===1||$qr->visible===3)&&$qr->editable===1) {
                        $update_fields_id[]=$field_id;
                        $update_fields_value[]=$field_val;
                    }
                }

                if($update_fields_id_count=count($update_fields_id)) {
                    $sql='';
                    foreach ($update_fields_id as $i => $iValue) {
                        $sql .= 'field_' . $update_fields_id[$i] .= '=:field_' . $iValue;
                    }

                    try {
                        $stm=$this->uFunc->pdo('uAuth')->prepare("UPDATE
                    u235_usersinfo
                    SET
                    $sql
                    WHERE
                    user_id=:user_id AND
                    site_id=:site_id
                    ");
                        $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
                        $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        for($i=0;$i<$update_fields_id_count;$i++)  {
                            $stm->bindParam(':field_' . $update_fields_id[$i], $update_fields_value[$i], PDO::PARAM_STR);
                        }

                        $stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error('1585457965'.$e->getMessage(),1);}
                }
            }
        }*/

        if(isset($_POST['newEmail'])) {
            if($this->is_profile_owner||$this->is_mad_root) {
                $newEmail=&$_POST['newEmail'];

                if(!isset($_POST['currentPassword'])&&!$this->is_mad_root) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong current password',
                        'debug'=>''
                    ]);
                    exit;
                }

                if(!uString::isEmail($newEmail)) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong email',
                        'debug'=>'check email format'
                    ]);
                    exit;
                }

                if(!$userData=$this->uAuth->user_id2user_data($user_id,'regDate,email,cellphone')) {
                    $this->uFunc->error(1587216486,1);
                }
                $currentEmail=$userData->email;
                $currentPhone=$userData->cellphone;

                //check if new email is free
                if(!$this->uAuth->checkIfEmailIsVacant($newEmail)) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'email is occupied',
                        'debug'=>'email is used in someone\'s profile'
                    ]);
                    exit;
                }

                if(!$this->is_mad_root) {
                    $currentPassword=$_POST['currentPassword'];
                    $passwordEncrypted = uFunc::passCrypt($currentPassword, $userData->regDate, $currentEmail, $user_id, $currentPhone);

                    //check current password
                    if ($this->is_profile_owner && !$this->uAuth->checkUserPassword($user_id, $passwordEncrypted, 1)) {
                        print json_encode([
                            'status' => 'error',
                            'code' => 'wrong current password',
                            'debug' => ''
                        ]);
                        exit;
                    }

                    if (!$emailValidationCode = $this->sendValidationCodeToNewEmailToVerifyEmailChange($newEmail)) {
                            print json_encode([
                                'status' => 'error',
                                'code' => 'validation email send error',
                                'debug' => 'could not send a email with validation code'
                            ]);
                            exit;
                    }

                    //Store Email change attempt in session
                    if (!isset($_SESSION['uAuth'])) {
                        $_SESSION['uAuth'] = [];
                    }
                    if (!isset($_SESSION['uAuth']['profile_update_bg'])) {
                        $_SESSION['uAuth']['profile_update_bg'] = [];
                    }
                    if (!isset($_SESSION['uAuth']['profile_update_bg']['changeemail'])) {
                        $_SESSION['uAuth']['profile_update_bg']['changeemail'] = [];
                    }

                    $_SESSION['uAuth']['profile_update_bg']['changeemail']['code'] = $emailValidationCode;
                    $_SESSION['uAuth']['profile_update_bg']['changeemail']['email'] = $newEmail;
                    $_SESSION['uAuth']['profile_update_bg']['changeemail']['timestamp'] = time();
                    $_SESSION['uAuth']['profile_update_bg']['changeemail']['password'] = $currentPassword;

                    if(uString::isEmail($currentEmail)) {
                        $this->sendNotificationAboutEmailChangingToCurrentEmail($currentEmail);
                    }
                }
                else {//is mad root
                    $password=uFunc::genPass();
                    $passwordEncrypted = uFunc::passCrypt($password, $userData->regDate, $newEmail, $user_id, $userData->cellphone);
                    $this->uAuth->updateUserEmail($user_id,$newEmail,$passwordEncrypted,1);

                    if(uString::isEmail($currentEmail)) {
                        //current email
                        $this->uAuth->sendNotificationThatEmailAndPasswordAreChanged($currentEmail, $newEmail, $password);
                    }

                    //new email
                    $this->uAuth->sendNotificationThatEmailAndPasswordAreChanged($newEmail, $newEmail, $password);
                }
            }
            else {
                $this->forbidden();
            }
        }

        if(isset($_POST['newPhone'])) {
            if(!$this->use_MadSMS) {
                print json_encode([
                    'status'=>'error',
                    'code'=>'sms sending is not provided',
                    'debug'=>'server does not know how to send sms'
                ]);
                exit;
            }
            if($this->is_profile_owner||$this->is_mad_root) {
                $newPhone=&$_POST['newPhone'];

                if(!isset($_POST['currentPassword'])&&!$this->is_mad_root) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong current password',
                        'debug'=>''
                    ]);
                    exit;
                }

                if(!uString::isPhone($newPhone)) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'wrong phone',
                        'debug'=>'check phone format'
                    ]);
                    exit;
                }

                if(!$userData=$this->uAuth->user_id2user_data($user_id,'regDate,email,cellphone')) {
                    $this->uFunc->error(1587216486,1);
                }
                $currentPhone=$userData->cellphone;
                $currentEmail=$userData->email;

                if(!$this->uAuth->checkIfPhoneIsVacant($newPhone)) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'phone is occupied',
                        'debug'=>'phone is used in someone\'s profile'
                    ]);
                    exit;
                }

                if(!$this->is_mad_root) {
                    $currentPassword=$_POST['currentPassword'];
                    $passwordEncrypted = uFunc::passCrypt($_POST['currentPassword'], $userData->regDate, $currentEmail, $user_id, $currentPhone);

                    //check current password
                    if ($this->is_profile_owner && !$this->uAuth->checkUserPassword($user_id, $passwordEncrypted, 1)) {
                        print json_encode([
                            'status' => 'error',
                            'code' => 'wrong current password',
                            'debug' => ''
                        ]);
                        exit;
                    }

                    if (!$result = $this->sendValidationCodeToNewPhoneToVerifyPhoneChange($newPhone)) {
                        /** @noinspection NestedPositiveIfStatementsInspection */
                        if (!$result['emailIsSent']) {
                            print json_encode([
                                'status' => 'error',
                                'code' => 'validation sms send error',
                                'debug' => 'could not send an sms with validation code'
                            ]);
                            exit;
                        }
                    }
                    $phoneValidationCode = $result['code'];


                    //Store Phone change attempt in session
                    if(!isset($_SESSION['uAuth'])) {
                        $_SESSION['uAuth']=[];
                    }
                    if(!isset($_SESSION['uAuth']['profile_update_bg'])) {
                        $_SESSION['uAuth']['profile_update_bg']=[];
                    }
                    if(!isset($_SESSION['uAuth']['profile_update_bg']['changecellphone'])) {
                        $_SESSION['uAuth']['profile_update_bg']['changecellphone']=[];
                    }

                    $_SESSION['uAuth']['profile_update_bg']['changecellphone']['code']=$phoneValidationCode;
                    $_SESSION['uAuth']['profile_update_bg']['changecellphone']['cellphone']=$newPhone;
                    $_SESSION['uAuth']['profile_update_bg']['changecellphone']['timestamp']=time();
                    $_SESSION['uAuth']['profile_update_bg']['changecellphone']['password']=$currentPassword;

                    if(uString::isPhone($currentPhone)) {
                        $this->sendNotificationAboutPhoneChangingToCurrentPhone($currentPhone);
                    }
                }
                else {//is mad root
                    $password=uFunc::genPass();
                    $passwordEncrypted = uFunc::passCrypt($password, $userData->regDate, $newPhone, $user_id, $userData->cellphone);
                    $this->uAuth->updateUserPhone($user_id,$newPhone,$passwordEncrypted,1);

                    $this->uAuth->sendNotificationThatPhoneAndPasswordAreChanged($currentPhone,$newPhone,$password);
                    $this->uAuth->sendNotificationThatPhoneAndPasswordAreChanged($newPhone,$newPhone,$password);
                }
            }
            else {
                $this->forbidden();
            }
        }

        if(isset($_POST['newPassword'])) {
            if($this->is_mad_root||$this->is_profile_owner) {

                $newPassword=$_POST['newPassword'];
                if(strlen($newPassword)<5) {
                    print json_encode([
                        'status'=>'error',
                        'code'=>'new password is too weak',
                        'debug'=>''
                    ]);
                    exit;
                }

                if(!$userData=$this->uAuth->user_id2user_data($user_id,'email,regDate,cellphone')) {
                    $this->uFunc->error(1587221368,1);
                }
                $currentEmail=$userData->email;
                $currentPhone=$userData->cellphone;

                $newPasswordEncrypted=uFunc::passCrypt($newPassword,$userData->regDate,$currentEmail,$user_id,$currentPhone);

                $this->uAuth->updateUserPassword($user_id,$newPasswordEncrypted,1);

                if(uString::isPhone($currentPhone)) {
                    $this->uAuth->sendSMSNotificationThatPasswordIsChanged($currentPhone,$newPassword);
                }
                if(uString::isEmail($currentEmail)) {
                    $this->uAuth->sendEmailNotificationThatPasswordIsChanged($currentEmail,$newPassword);
                }
            }
            else {
                $this->forbidden();
            }
        }

        print json_encode([
            'status'=>'success'
        ]);
        exit;
    }

    /**
     * profile_update_bg constructor.
     * @param $uCore
     */
    public function __construct(&$uCore) {
        if(!isset($uCore)) {$uCore=new uCore();}
        $this->uCore=$uCore;
        $this->uAuth=new common($uCore);

        $user_id=$this->checkData();

        $this->uFunc=new uFunc($uCore);

        $this->translator=new translator(site_lang, 'uAuth/profile_update_bg.php');
        $this->use_MadSMS=(int)$this->uFunc->getConf('use MAD SMS to send SMS','content',false);

        $this->updateUserDataFromRequest($user_id);
    }
}

new profile_update_bg($this);
