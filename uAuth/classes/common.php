<?php
namespace uAuth;
require_once 'processors/classes/uFunc.php';
require_once 'translator/translator.php';
use PDO;
use PDOException;
use translator\translator;
use uFunc;
use uString;

/**
 * Class common
 * ===
 * Provides tool to work with user data
 *
 * Public methods
 * ---
 * - `array` **get_groups_with_assigned_to_user**(int $user_id) - Returns all groups that exists on website and marks those who assigned to user.
 * @package uAuth
 */
class common
{
    /**
     * @var \processors\uFunc
     * @ignore
     */
    public $uFunc;
    private $uCore;
    /**
     * @var translator
     */
    private $translator;
    private $get_user_data_by_email_ar;
    private $user_id2user_data_ar;
    private $user_id2usersinfo_ar;

    private function get_new_user_id()
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
            user_id
            FROM
            u235_users
            ORDER BY
            user_id DESC
            LIMIT 1
            ');
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if ($qr = $stm->fetch(PDO::FETCH_OBJ)) {
                $id = $qr->user_id + 1;
            }
            else {
                $id = 1;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('uAuth common 10'/*.$e->getMessage()*/);
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('DELETE FROM
            u235_usersinfo
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ');
            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
            $stm->bindParam(':user_id', $id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('uAuth common 20'/*.$e->getMessage()*/);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $id;
    }
    public function get_uAuth_usersinfo_fields() {
        $fields = array();
        try {
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
            field_id,
            label,
            visible,
            editable,
            field_type
            FROM
            u235_usersinfo_site_labels
            WHERE
            site_id=:site_id
            ORDER BY 
            sort
            ');
            $site_id = site_id;
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            for ($i = 0; $qr = $stm->fetch(PDO::FETCH_ASSOC); $i++) {
                $fields[$i] = $qr;
            }
        } catch (PDOException $e) {$this->uFunc->error('uAuth common 30'/*.$e->getMessage()*/);}

        return $fields;
    }

    /**
     * returns user_data form $q_data in object. Returns false if user is not found
     * @param string $q_data firstname, secondname, lastname, type, email, cellphone, password, status, regDate, avatar_timestamp
     * @param string $login email or cellphone
     * @param string $loginType email | cellphone
     * @return object | bool
     */
    public function userLogin2info($q_data,$login,$loginType='email') {
        if($loginType!=='email'&&$loginType!=='cellphone') {
            return false;
        }

        if (!isset($this->get_user_data_by_email_ar[$login][$q_data])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo('uAuth')->prepare("SELECT $q_data FROM u235_users WHERE $loginType=:$loginType");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(":$loginType", $login, PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error(1586312266,1);}

            /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
            if (!$user = $stm->fetch(PDO::FETCH_OBJ)) {
                $this->get_user_data_by_email_ar[$login][$q_data] = false;
            }
            else {
                $this->get_user_data_by_email_ar[$login][$q_data] = $user;
            }

        }
        return $this->get_user_data_by_email_ar[$login][$q_data];
    }

    /**
     * Retrieves information about user from u235_users table from madmakers_uAuth db
     * - user_id
     * - type
     * - firstname
     * - secondname
     * - lastname
     * - password
     * - email
     * - cellphone
     * - status
     * - regDate
     * - avatar_timestamp
     * @param int $user_id
     * @param string $q_data
     * @return mixed
     */
    public function user_id2user_data($user_id, $q_data)
    {
        if (!isset($this->user_id2user_data_ar[$user_id][$q_data])) {
            try {
                $stm = $this->uFunc->pdo('uAuth')->prepare("SELECT 
                $q_data 
                FROM 
                u235_users 
                WHERE 
                user_id=:user_id
                ");
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_STR);
                $stm->execute();

                $this->user_id2user_data_ar[$user_id][$q_data] = $stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error(1585456391,1);}

        }
        return $this->user_id2user_data_ar[$user_id][$q_data];
    }
    public function user_id2usersinfo($user_id, $q_data, $site_id = site_id)
    {
        if (!isset($this->user_id2usersinfo_ar[$site_id][$user_id][$q_data])) {
            try {
                $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
                ' . $q_data . '
                FROM
                u235_usersinfo
                WHERE
                user_id=:user_id AND
                site_id=:site_id
                ');
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stm->execute();

                $this->user_id2usersinfo_ar[$site_id][$user_id][$q_data] = $stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                $this->uFunc->error('uAuth common 60'/*.$e->getMessage()*/);
            }
        }
        return $this->user_id2usersinfo_ar[$site_id][$user_id][$q_data];
    }


    private $userExistsOnSiteAr=[];
    /**
     * Checks if user exists on site
     * @param int $user_id
     * @param int $site_id
     * @return bool true|false
     */
    public function userExistsOnSite($user_id, $site_id = site_id) {
//        $user_id=(int)$user_id;
//        $site_id=(int)$site_id;
        if (!isset($this->userExistsOnSiteAr[$site_id])) {
            $this->userExistsOnSiteAr[$site_id]=[];
            if (!isset($this->userExistsOnSiteAr[$site_id][$user_id])) {
                try {
                    $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
                    user_id
                    FROM
                    u235_usersinfo
                    WHERE
                    user_id=:user_id AND
                    site_id=:site_id
                    ');
                    $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();

                    if ($stm->fetch(PDO::FETCH_OBJ)) {
                        $this->userExistsOnSiteAr[$site_id][$user_id] = true;
                    } else {
                        $this->userExistsOnSiteAr[$site_id][$user_id] = false;
                    }
                } catch (PDOException $e) {
                    $this->uFunc->error('1587213969'/*.$e->getMessage()*/, 1);
                }
            }
        }
        return $this->userExistsOnSiteAr[$site_id][$user_id];
    }

    /**
     * Check's user's password
     * @param int $user_id
     * @param string $password
     * @param int $passwordIsEncrypted - optional. default value is 0. If password is unencrypted, it will be encrypted in this method
     * @param string $regDate - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @param string $email - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @param string $cellphone - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @return bool true if password is correct | false if password is wrong
     */
    public function checkUserPassword($user_id, $password, $passwordIsEncrypted=0, $regDate='', $email='', $cellphone='') {
        if(!$passwordIsEncrypted) {
            if($regDate==='') {
                if (!$userData = $this->user_id2user_data($user_id, 'regDate,email,cellphone')) {
                    $this->uFunc->error(1587216486, 1);
                }
                $regDate=$userData->regDate;
                $email=$userData->email;
                $cellphone=$userData->cellphone;
            }

            $password = \processors\uFunc::passCrypt($password, $regDate, $email, $user_id, $cellphone);
        }
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT user_id FROM u235_users WHERE password=:password');
            $stm->bindParam(':password', $password,PDO::PARAM_STR);
            $stm->execute();

            if($stm->fetch(PDO::FETCH_OBJ)) {
                return true;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1587216634'/*.$e->getMessage()*/,1);}

        return false;
    }

    /**
     * Checks if email is vacant. If there are no user profile with that email
     * @param $email
     * @return bool true - if email is not used in somebody's profile | false - if email is occupied
     */
    public function checkIfEmailIsVacant($email) {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT user_id FROM u235_users WHERE email=:email LIMIT 1');
            $stm->bindParam(':email', $email,PDO::PARAM_STR);
            $stm->execute();

            if($stm->fetch(PDO::FETCH_OBJ)) {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1587217157'/*.$e->getMessage()*/,1);}

        return true;
    }

    /**
     * Checks if phone is vacant. If there are no user profile with that phone
     * @param $cellphone
     * @return bool true - if email is not used in somebody's profile | false - if phone is occupied
     */
    public function checkIfPhoneIsVacant($cellphone) {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT user_id FROM u235_users WHERE cellphone=:cellphone LIMIT 1');
            $stm->bindParam(':cellphone', $cellphone,PDO::PARAM_STR);
            $stm->execute();

            if($stm->fetch(PDO::FETCH_OBJ)) {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1587222099'/*.$e->getMessage()*/,1);}

        return true;
    }

    /**
     * Updates user's email address
     *
     * Passwords are encrypted with email and phone so we need generate new encrypted hash. Because of that we need current password or a new one
     *
     * If mod obooking is installed - admin/manager/client email that is assigned to this user_id will be updated
     *
     * @param $user_id
     * @param string $newEmail
     * @param string $password - current or new user password. It will be saved to database
     * @param int $passwordIsEncrypted - optional. default value is 0. If password is unencrypted, it will be encrypted in this method
     * @param string $regDate - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @param string $cellphone - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     */
    public function updateUserEmail($user_id, $newEmail, $password, $passwordIsEncrypted=0, $regDate='', $cellphone='') {
        if(!$passwordIsEncrypted) {
            if($regDate==='') {
                if (!$userData = $this->user_id2user_data($user_id, 'regDate,cellphone')) {
                    $this->uFunc->error(1587220384, 1);
                }
                $regDate=$userData->regDate;
                $cellphone=$userData->cellphone;
            }

            $password = \processors\uFunc::passCrypt($password, $regDate, $newEmail, $user_id, $cellphone);
        }
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('UPDATE
            u235_users
            SET
            email=:email,
            password=:password
            WHERE
            user_id=:user_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':email', $newEmail,PDO::PARAM_STR);
            $stm->bindParam(':password', $password,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587220395'/*.$e->getMessage()*/,1);}

        if($this->uFunc->mod_installed('obooking')) {
            if(!isset($this->obooking)) {
                require_once 'obooking/classes/common.php';
                $this->obooking=new \obooking\common($this->uCore);
            }
            $this->obooking->update_user_email($user_id,$newEmail);
        }
    }

    /**
     * Updates user's phone number
     *
     * Passwords are encrypted with email and phone so we need generate new encrypted hash. Because of that we need current password or a new one
     *
     * If mod obooking is installed - admin/manager/client phone that is assigned to this user_id will be updated
     *
     * @param $user_id
     * @param string $newPhone
     * @param string $password - current or new user password. It will be saved to database
     * @param int $passwordIsEncrypted - optional. default value is 0. If password is unencrypted, it will be encrypted in this method
     * @param string $regDate - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @param string $email - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     */
    public function updateUserPhone($user_id, $newPhone, $password, $passwordIsEncrypted=0, $regDate='', $email='') {
        if(!$passwordIsEncrypted) {
            if($regDate==='') {
                if (!$userData = $this->user_id2user_data($user_id, 'regDate,email')) {
                    $this->uFunc->error(1587220384, 1);
                }
                $regDate=$userData->regDate;
                $email=$userData->email;
            }

            $password = \processors\uFunc::passCrypt($password, $regDate, $email, $user_id, $newPhone);
        }
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('UPDATE
            u235_users
            SET
            cellphone=:cellphone,
            password=:password
            WHERE
            user_id=:user_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':cellphone', $newPhone,PDO::PARAM_STR);
            $stm->bindParam(':password', $password,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587360462'/*.$e->getMessage()*/,1);}

        if($this->uFunc->mod_installed('obooking')) {
            if(!isset($this->obooking)) {
                require_once 'obooking/classes/common.php';
                $this->obooking=new \obooking\common($this->uCore);
            }
            $this->obooking->update_user_cellphone($user_id,$newPhone);
        }
    }

    /**
     * Updates user's password
     *
     * Passwords are encrypted with email and phone so we need generate new encrypted hash. Because of that we need current password or a new one
     * @param $user_id
     * @param string $password - current or new user password. It will be saved to database
     * @param int $passwordIsEncrypted - optional. default value is 0. If password is unencrypted, it will be encrypted in this method
     * @param string $regDate - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * * @param string $email - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     * @param string $cellphone - optional. Not needed if passwordIsEncrypted=1. If passwordIsEncrypted=0 and if not passed, it will be retrieved from database by user_id
     */
    public function updateUserPassword($user_id, $password, $passwordIsEncrypted=0, $regDate='', $email='', $cellphone='') {
        if(!$passwordIsEncrypted) {
            if($regDate==='') {
                if (!$userData = $this->user_id2user_data($user_id, 'regDate,email,cellphone')) {
                    $this->uFunc->error(1587221566, 1);
                }
                $regDate=$userData->regDate;
                $cellphone=$userData->cellphone;
                $email=$userData->email;
            }

            $password = \processors\uFunc::passCrypt($password, $regDate, $email, $user_id, $cellphone);
        }
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('UPDATE
            u235_users
            SET
            password=:password
            WHERE
            user_id=:user_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':password', $password,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587221622'/*.$e->getMessage()*/,1);}
    }

    /**
     * Sends notification to user that his password is changed
     * @param $recipientEmail - email to what notification will be sent
     * @param $newEmail - new email address - it will be noticed in message
     * @param $newPassword - new password - it will be noticed in message
     * @return bool emailSendResult - true | false
     */
    public function sendNotificationThatEmailAndPasswordAreChanged($recipientEmail,$newEmail,$newPassword) {
        $subject=$this->translator->txt('Email in your account has been changed');
        $content= '<p>' .$this->translator->txt('Your email has being changed on').' <a href="'.u_sroot.'">'.site_name.'</a></p>'.
            '<p>'.$this->translator->txt('New email is').' <b style="font-size: 1.5em">'.$newEmail.'</b></p>'.
            '<p>'.$this->translator->txt('Password is').' <b style="font-size: 1.5em">'.$newPassword.'</b></p>';

        return $this->uFunc->sendMail($content,$subject,$recipientEmail);
    }

    /**
     * Sends notification to user that his phone and password are changed
     * @param string $recipientPhone - phone to what notification will be sent
     * @param string $newPhone - new phone - it will be noticed in message
     * @param string $newPassword - new password - it will be noticed in message
     * @return bool emailSendResult - true | false
     */
    public function sendNotificationThatPhoneAndPasswordAreChanged($recipientPhone,$newPhone,$newPassword) {
        $content= $this->translator->txt('Your phone number has being changed to').' '.$newPhone;
        $content.="\n";
        $content.=$this->translator->txt('Password is').' '.$newPassword;
        $content.="\n";
        $content.=site_domain;

        return $this->uFunc->sendSms($content,$recipientPhone);
    }

    /**
     * Sends Email notification to user that his current password is being changed to new one
     * @param $recipientEmail - email to what notification will be sent
     * @param $newPassword - new password - it will be noticed in message
     * @return bool emailSendResult - true | false
     */
    public function sendEmailNotificationThatPasswordIsChanged($recipientEmail,$newPassword) {//TODO-nik87 проверить, чтобы уведомления о смене пароля, телефона, email отправлялись и на email, и на телефон - независимо от того, что меняют
        $subject=$this->translator->txt('Your password has been changed');
        $content= '<p>' .$this->translator->txt('Your password has being changed on').' <a href="'.u_sroot.'">'.site_name.'</a></p>'.
            '<p>'.$this->translator->txt('New password is').' <b style="font-size: 1.5em">'.$newPassword.'</b></p>';

        return $this->uFunc->sendMail($content,$subject,$recipientEmail);
    }
    /**
     * Sends SMS notification to user that his current password is being changed to new one
     * @param $recipientPhone - phone number to what notification will be sent
     * @param $newPassword - new password - it will be noticed in message
     * @return bool emailSendResult - true | false
     */
    public function sendSMSNotificationThatPasswordIsChanged($recipientPhone,$newPassword) {
        $content=$this->translator->txt('Your password has been changed').
        "\n".
        $this->translator->txt('New password is').' '.$newPassword.
        "\n".
        site_domain;

        return $this->uFunc->sendSms($content,$recipientPhone);
    }

    /**
     * Save user's firstname,secondname and lastname
     * @param $firstname
     * @param $secondname
     * @param $lastname
     * @param $user_id
     */
    public function saveUserFirstSecondLastNames($user_id,$firstname, $secondname, $lastname) {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('UPDATE 
                u235_users
                SET
                firstname=:firstname,
                secondname=:secondname,
                lastname=:lastname
                WHERE
                user_id=:user_id
                ');
            $stm->bindParam(':firstname', $firstname,PDO::PARAM_STR);
            $stm->bindParam(':secondname', $secondname,PDO::PARAM_STR);
            $stm->bindParam(':lastname', $lastname,PDO::PARAM_STR);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587216107'/*.$e->getMessage()*/,1);}
    }

    /**
     * Grants mad root access to user or revokes it
     * @param int $user_id
     * @param int $newValue - 0 - revoke mad root access. 1 - grant access
     */
    public function grantOrRevokeMadRootAccessToUser($user_id, $newValue) {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('UPDATE 
            u235_users
            SET
            type=:type
            WHERE
            user_id=:user_id
            ');
            $stm->bindParam(':type', $newValue,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587223990'/*.$e->getMessage()*/,1);}
    }

    /**
     * Assigns|Removes group To|From user
     * @param int $user_id
     * @param int $group_id
     * @param string $action : assign | remove
     * @param int $site_id
     */
    public function assignOrRemoveUserToGroup($user_id, $group_id, $action, $site_id=site_id) {
        if($action==='assign') {
            try {
                $stm = $this->uFunc->pdo('uAuth')->prepare('REPLACE INTO
                u235_usersinfo_groups (
                user_id,
                group_id,
                site_id
                ) VALUES (
                :user_id,
                :group_id,
                :site_id
                )
                ');
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stm->bindParam(':group_id', $group_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('1587236839'/*.$e->getMessage()*/,1);
            }
        }
        else {
            try {
                $stm = $this->uFunc->pdo('uAuth')->prepare('DELETE FROM
                u235_usersinfo_groups 
                WHERE
                user_id=:user_id AND
                group_id=:group_id AND
                site_id=:site_id
                ');
                $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stm->bindParam(':group_id', $group_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('1587237190'/*.$e->getMessage()*/,1);
            }
        }
    }

    public function user_id2user_groups($user_id) {
        try {
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT DISTINCT
            user_group_id
            FROM
            u235_usersinfo_groups
            JOIN 
            u235_groups
            ON
            u235_usersinfo_groups.group_id=u235_groups.user_group_id
            WHERE
            u235_usersinfo_groups.user_id=:user_id AND
            u235_usersinfo_groups.site_id=:site_id AND
            user_group_id!=0 AND
            user_group_id!=1 AND
            user_group_id!=2 AND
            user_group_id!=3 AND
            user_group_id!=13
            ');
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->execute();

            $groups=[];
            for ($i = 0; $group = $stm->fetch(PDO::FETCH_OBJ); $i++) {
                $groups[$i] = (int)$group->user_group_id;
            }
            return $groups;
        } catch (PDOException $e) {$this->uFunc->error('1587150137'/*.$e->getMessage()*/,1);}
        return [];
    }

    /**
     * <h1>get_groups_with_assigned_to_user</h1>
     * Returns all groups that exists on website and marks those who assigned to user
     *
     * <br/>
     *
     * `array get_groups_with_assigned_to_user ( int $user_id )`
     *
     * ---
     *
     * # Arguments:
     * - int `$user_id` - user_id to mark groups assigned to this user
     *
     * # Returns:
     * - array - all groups on site (assigned and not assigned to user). Assigned groups are marked
     * ~~~php
     * $groups=array(
     *      {'group_id'=>25, 'assigned'=>30},//group is assigned to user with user_id=30
     *      {'group_id'=>25, 'assigned'=>NULL}//group is not assigned to user
     * )
     * ~~~
     *
     * # Example
     * ## Call:
     * ~~~php
     * $groups=$uAuth->get_groups_with_assigned_to_user(30);//Get all groups and check groups assigned to user_id=30
     * ~~~
     *
     * ## Result:
     * ~~~php
     * $groups=array(
     * { 'group_id'=>25, 'assigned'=>30, mod='uCat' },//group is assigned to user with user_id=30
     * { 'group_id'=>25, 'assigned'=>NULL, mod='uAuth' }//group is not assigned to user
     * )
     * ~~~
     *
     * ## Run throw groups:
     * ~~~php
     * foreach($groups AS $group) {
     *  $group_id=$group->user_group_id;
     *  $assigned=!is_null($group->user_id);
     * }
     * ~~~~

     *
     * @param int $user_id
     * @return array $groupArray:  int user_group_id, NULL|user_id user_id, string mod
     */
    public function get_groups_with_assigned_to_user($user_id) {
        try {
            $stm = $this->uFunc->pdo('common');
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT DISTINCT
            user_group_id AS group_id,
            user_id AS assigned,
            module
            FROM
            u235_groups
            LEFT JOIN
            u235_usersinfo_groups
            ON
            u235_usersinfo_groups.group_id=u235_groups.user_group_id AND
            user_id=:user_id AND
            site_id=:site_id
            JOIN
            madmakers_common.u235_sites_modules
            ON
            u235_sites_modules.mod_name=u235_groups.module AND
            u235_sites_modules.site_id=:site_id
            WHERE
            user_group_id!=0 AND
            user_group_id!=1 AND
            user_group_id!=2 AND
            user_group_id!=3 AND
            user_group_id!=13
            ORDER BY
            module
            ');
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_OBJ);
        } catch (PDOException $e) {$this->uFunc->error('1587168562'/*.$e->getMessage()*/,1);}
        return [];
    }

    public function user_is_member_of_group($group_id,$user_id,$site_id=site_id) {
        try {
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
            user_id
            FROM
            u235_usersinfo_groups
            WHERE
            user_id=:user_id AND
            group_id=:group_id AND
            site_id=:site_id
            ');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':group_id', $group_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) {
                return 1;
            }
        }
        catch (PDOException $e) {$this->uFunc->error('uAuth common 90'/*.$e->getMessage()*/);}

        return 0;
    }

    public function usersinfo_field_id2val($field_id, $user_id) {
        $field_name = 'field_' . (int)$field_id;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('SELECT
                ' . $field_name . '
                FROM
                u235_usersinfo
                WHERE
                site_id=:site_id AND
                user_id=:user_id
                ');
            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('uAuth common 100'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr = $stm->fetch(PDO::FETCH_OBJ)) {
            return uString::sql2text($qr->$field_name, 1);
        }

        return '';
    }

    public function update_user($user_id, $q_data)
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('UPDATE
            u235_users
            SET 
            ' . $q_data .
                ' WHERE user_id=:user_id'
            );
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('uAuth common 110'/*.$e->getMessage()*/);
        }
    }

    public function add_new_user($firstname, $secondname, $lastname, $email, $password, $cellphone = '', $status = 'active')
    {
        $user_id = $this->get_new_user_id();
        $firstname = uString::text2sql($firstname);
        $regTime = time();
        $pass = uFunc::passCrypt($password, $regTime, $email, $user_id, $cellphone);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('INSERT INTO
            u235_users (
            user_id,
            firstname,
            secondname,
            lastname,
            password,
            email,
            cellphone,
            status,
            regDate
            ) VALUES (
            :user_id,
            :firstname,
            :secondname,
            :lastname,
            :pass,
            :email,
            :cellphone,
            :status,
            :regtime
            )');

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':firstname', $firstname, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':secondname', $secondname, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lastname', $lastname, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pass', $pass, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email', $email, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cellphone', $cellphone, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':regtime', $regTime, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('1586324852'/*.$e->getMessage()*/,1);}
        return $user_id;
    }
    public function add_user2usersinfo($user_id, $status = 'active', $site_id = site_id)
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo('uAuth')->prepare('INSERT IGNORE INTO u235_usersinfo (
            user_id,
            status,
            site_id
            ) VALUES (
            :user_id,
            :status,
            :site_id
            )');
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $status, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('1586324843' /*. $e->getMessage()*/,1);
        }
    }

    public function emailUserAboutRegistration($name, $email, $pass, $u_sroot = u_sroot, $site_name = site_name, $site_email = site_email, $site_id = site_id)
    {
        $html = '<p>' . $this->translator->txt('Hello text before user name'/*Здравствуйте,*/) . ' ' . $name . '</p>
        <div class="msg_text"><p>' . $this->translator->txt('Registration confirmation email text - part 1'/*Вы зарегистрированы на сайте*/) . ' <a href="' . $u_sroot . '">' . $site_name . '</a></p>
        <p>' . $this->translator->txt('Registration confirmation email text - part 2'/*Для входа на сайт используйте свои данные:*/) . '</p>
        ' . $this->translator->txt('Registration confirmation email text - part 3 - email'/*Ваш email:*/) . ' ' . $email . '<br>
        ' . $this->translator->txt('Registration confirmation email text - part 4 - password'/*Ваш пароль:*/) . ' ' . $pass . '</p>
        </div>';
        $title = $this->translator->txt('Registration on'/*Регистрация на */) . ' ' . $site_name;

        $this->uFunc->sendMail($html, $title, $email, $site_name, $site_email, $u_sroot, $site_id);
    }
    public function smsUserAboutRegistration($phone, $pass, $site_name = site_name, $site_id = site_id)
    {
        $text = $this->translator->txt('Your password is').': '.$pass."\n".$this->translator->txt('Welcome')."\n".strip_tags($site_name);

        $this->uFunc->sendSms($text,$phone,$site_id);
    }

    public function __construct(&$uCore)
    {
        $this->uCore=$uCore;
        $this->uFunc = new \processors\uFunc($uCore);
        $this->translator=new translator(site_lang,'uAuth/classes/common.php');
    }
}
