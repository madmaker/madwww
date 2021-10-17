<?php
namespace obooking;
use DateTime;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';
require_once 'uAuth/classes/common.php';

/**
 * REST API
 * ===
 * - Updates information about administrator
 * - Automatically assigns administrator to user's profile is email or phone is found in someone's user's profile
 *
 * ---
 * # Request
 * ## POST
 * - administrator_id
 * - administrator_name
 * - administrator_lastname
 * - administrator_phone
 * - administrator_email
 * - administrator_status
 * - administrator_birthday
 * - administrator_comment
 * - administrator_vk_id
 *
 * ## Cookies
 * - ses_id
 * - user_id
 *
 * # Response
 * - status
 *      - done
 *      - error
 * - msg - in case of error
 *      - administrator name is empty
 *      - administrator phone is wrong
 *      - administrator email is wrong
 *      - administrator birthday is wrong
 *      - administrator is not found
 * - user_id - assigned user's profile id
 * - phone - administrator phone after save (if it's empty, it can be retrieved automatically from user's profile)
 * - email - administrator email after save (if it's empty, it can be retrieved automatically from user's profile)
 * @package obooking
 */
class save_administrator_bg{
    private $administrator_name;
    private $administrator_lastname;
    private $administrator_phone;
    private $administrator_email;
    private $administrator_status;
    private $administrator_birthday;
    private $administrator_comment;
    private $administrator_vk_id;
    private $administrator_id;
    private $uFunc;
    private function check_data() {
        if(!isset(
            $_POST['administrator_id'],
            $_POST['administrator_name'],
            $_POST['administrator_lastname'],
            $_POST['administrator_phone'],
            $_POST['administrator_email'],
            $_POST['administrator_status'],
            $_POST['administrator_birthday'],
            $_POST['administrator_comment'],
            $_POST['administrator_vk_id']
        )) {
            $this->uFunc->error(10);
        }

        $this->administrator_id=(int)$_POST['administrator_id'];
        $this->administrator_status=(int)$_POST['administrator_status'];
        if($this->administrator_status!==0&&$this->administrator_status!==1) {
            $this->uFunc->error(0, 1);
        }

        $this->administrator_name=trim($_POST['administrator_name']);
        if($this->administrator_name=== '') {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'administrator name is empty'
            ));
            exit;
        }
        $this->administrator_lastname=trim($_POST['administrator_lastname']);
        $this->administrator_phone=trim($_POST['administrator_phone']);

        if(($this->administrator_phone !== '') && !uString::isPhone($this->administrator_phone)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'administrator phone is wrong'
            ));
            exit;
        }
        $this->administrator_email=trim($_POST['administrator_email']);
        if(($this->administrator_email !== '') && !uString::isEmail($this->administrator_email)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'administrator email is wrong'
            ));
            exit;
        }

        $this->administrator_birthday=trim($_POST['administrator_birthday']);
        $administrator_birthday_isDate=uString::isDate($this->administrator_birthday);

        if($this->administrator_birthday!== '' &&$this->administrator_birthday!== '0' &&!$administrator_birthday_isDate) {
                echo json_encode(array(
                    'status' => 'error',
                    'msg' => 'administrator birthday is wrong'
                ));
                exit;
        }
        if($administrator_birthday_isDate) {
            $date_formatted=DateTime::createFromFormat('d.m.Y',$this->administrator_birthday)->format('U');
            $this->administrator_birthday=$date_formatted;
        }
        else {
            $this->administrator_birthday = 0;
        }

        $this->administrator_comment=trim($_POST['administrator_comment']);
        $this->administrator_vk_id=trim($_POST['administrator_vk_id']);

//        $this->administrator_bank_card_number=(int)$_POST["administrator_bank_card_number"];
    }
    private function save_administrator($user_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            administrators
            SET
            administrator_name=:administrator_name,
            administrator_lastname=:administrator_lastname,
            administrator_phone=:administrator_phone,
            administrator_email=:administrator_email,
            administrator_status=:administrator_status,
            administrator_birthday=:administrator_birthday,
            administrator_comment=:administrator_comment,
            administrator_vk_id=:administrator_vk_id,
            /*administrator_bank_card_number=:administrator_bank_card_number,*/
            user_id=:user_id
            WHERE
            administrator_id=:administrator_id AND
            site_id=:site_id
            ');
            $site_id=site_id;

            $stm->bindParam(':administrator_name', $this->administrator_name,PDO::PARAM_STR);
            $stm->bindParam(':administrator_lastname', $this->administrator_lastname,PDO::PARAM_STR);
            $stm->bindParam(':administrator_phone', $this->administrator_phone,PDO::PARAM_STR);
            $stm->bindParam(':administrator_email', $this->administrator_email,PDO::PARAM_STR);
            $stm->bindParam(':administrator_status', $this->administrator_status,PDO::PARAM_INT);
            $stm->bindParam(':administrator_birthday', $this->administrator_birthday,PDO::PARAM_INT);
            $stm->bindParam(':administrator_comment', $this->administrator_comment,PDO::PARAM_STR);
            $stm->bindParam(':administrator_vk_id', $this->administrator_vk_id,PDO::PARAM_STR);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':administrator_bank_card_number', $this->administrator_bank_card_number,PDO::PARAM_STR);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);

            $stm->bindParam(':administrator_id', $this->administrator_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        $this->uFunc=new uFunc($uCore);
        $uAuth=new \uAuth\common($uCore);

        $this->check_data();

        //get current admin user_id,phone and email (we'll check if they are changed)
        if(!$administratorCurrentInfo=$obooking->get_administrator_info('administrator_email,administrator_phone,user_id',$this->administrator_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'administrator is not found'
            ]);
            exit;
        }

        if($administratorCurrentInfo->administrator_email!==$this->administrator_email||$administratorCurrentInfo->administrator_phone!==$this->administrator_phone) {
                //check if user exists by email
                if(uString::isEmail($this->administrator_email) && $userData = $uAuth->userLogin2info('user_id,cellphone', $this->administrator_email, 'email')) {
                    $user_id=(int)$userData->user_id;
                    if(!uString::isPhone($this->administrator_phone) && uString::isPhone($userData->cellphone)) {
                        $this->administrator_phone=$userData->cellphone;
                    }
                }
                //check if user exists by phone
                elseif(uString::isPhone($this->administrator_phone) && $userData = $uAuth->userLogin2info('user_id,email', $this->administrator_phone, 'cellphone')) {
                    $user_id=(int)$userData->user_id;
                    if(!uString::isEmail($this->administrator_email) && uString::isEmail($userData->email)) {
                        $this->administrator_email=$userData->email;
                    }
                }
                else {
                    //user is not found - we should unassign administrator from previous user_id
                    $user_id = 0;
                }
        }
        else {
            $user_id=(int)$administratorCurrentInfo->user_id;
        }

        $this->save_administrator($user_id);


        echo json_encode(array(
            'status'=>'done',
            'user_id'=>$user_id,
            'phone'=>$this->administrator_phone,
            'email'=>$this->administrator_email
        ));
    }
}
new save_administrator_bg($this);
