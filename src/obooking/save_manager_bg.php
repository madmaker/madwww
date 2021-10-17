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
 * - Updates information about manager
 * - Automatically assigns manager to user's profile is email or phone is found in someone's user's profile
 *
 * ---
 * # Request
 * ## POST
 * - manager_id
 * - manager_name
 * - manager_lastname
 * - manager_phone
 * - manager_email
 * - manager_status
 * - manager_specialization
 * - manager_birthday
 * - manager_comment
 *
 * ## Cookies
 * - ses_id
 * - user_id
 *
 * # Response
 * - status
 *      - done
 *      - error
 *      - forbidden
 * - msg - in case of error
 *      - manager name is empty
 *      - manager phone is wrong
 *      - manager email is wrong
 *      - manager birthday is wrong
 *      - manager is not found
 * - user_id - assigned user's profile id
 * - phone - manager phone after save (if it's empty, it can be retrieved automatically from user's profile)
 * - email - manager email after save (if it's empty, it can be retrieved automatically from user's profile)
 * @package obooking
 */
class save_manager_bg{
//    private $manager_bank_card_number;
    private $manager_name;
    private $manager_lastname;
    private $manager_phone;
    private $manager_email;
    private $manager_status;
    private $manager_specialization;
    private $manager_birthday;
    private $manager_comment;
    private $manager_vk_id;
    private $manager_id;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST['manager_id'],
            $_POST['manager_name'],
            $_POST['manager_lastname'],
            $_POST['manager_phone'],
            $_POST['manager_email'],
            $_POST['manager_status'],
            $_POST['manager_specialization'],
            $_POST['manager_birthday'],
            $_POST['manager_comment'],
            $_POST['manager_vk_id']/*,
            $_POST["manager_bank_card_number"]*/
        )) {
            $this->uFunc->error(10);
        }

        $this->manager_id=(int)$_POST['manager_id'];
        $this->manager_status=(int)$_POST['manager_status'];
        if($this->manager_status!==0&&$this->manager_status!==1) {
            $this->uFunc->error(0, 1);
        }

        $this->manager_name=trim($_POST['manager_name']);
        if($this->manager_name=== '') {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'manager name is empty'
            ));
            exit;
        }
        $this->manager_lastname=trim($_POST['manager_lastname']);
        $this->manager_phone=trim($_POST['manager_phone']);

        if(($this->manager_phone !== '') && !uString::isPhone($this->manager_phone)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'manager phone is wrong'
            ));
            exit;
        }
        $this->manager_email=trim($_POST['manager_email']);
        if(($this->manager_email !== '') && !uString::isEmail($this->manager_email)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'manager email is wrong'
            ));
            exit;
        }
        $this->manager_specialization=trim($_POST['manager_specialization']);

        $this->manager_birthday=trim($_POST['manager_birthday']);
        $Manager_birthday_isDate=uString::isDate($this->manager_birthday);

        if($this->manager_birthday!== '' &&$this->manager_birthday!== '0' &&!$Manager_birthday_isDate) {
                echo json_encode(array(
                    'status' => 'error',
                    'msg' => 'manager birthday is wrong'
                ));
                exit;
        }
        if($Manager_birthday_isDate) {
            $date_formatted=DateTime::createFromFormat('d.m.Y',$this->manager_birthday)->format('U');
            $this->manager_birthday=$date_formatted;
        }
        else {
            $this->manager_birthday = 0;
        }

        $this->manager_comment=trim($_POST['manager_comment']);
        $this->manager_vk_id=trim($_POST['manager_vk_id']);
    }

    private function save_manager($user_id) {
        try {
            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
            managers
            SET
            manager_name=:manager_name,
            manager_lastname=:manager_lastname,
            manager_phone=:manager_phone,
            manager_email=:manager_email,
            manager_status=:manager_status,
            manager_specialization=:manager_specialization,
            manager_birthdate=:manager_birthday,
            manager_comment=:manager_comment,
            manager_vk_id=:manager_vk_id,
            /*manager_bank_card_number=:manager_bank_card_number*/
            user_id=:user_id
            WHERE
            manager_id=:manager_id AND
            site_id=:site_id
            ');
            $site_id=site_id;

            $stm->bindParam(':manager_name', $this->manager_name,PDO::PARAM_STR);
            $stm->bindParam(':manager_lastname', $this->manager_lastname,PDO::PARAM_STR);
            $stm->bindParam(':manager_phone', $this->manager_phone,PDO::PARAM_STR);
            $stm->bindParam(':manager_email', $this->manager_email,PDO::PARAM_STR);
            $stm->bindParam(':manager_status', $this->manager_status,PDO::PARAM_INT);
            $stm->bindParam(':manager_specialization', $this->manager_specialization,PDO::PARAM_STR);
            $stm->bindParam(':manager_birthday', $this->manager_birthday,PDO::PARAM_INT);
            $stm->bindParam(':manager_comment', $this->manager_comment,PDO::PARAM_STR);
            $stm->bindParam(':manager_vk_id', $this->manager_vk_id,PDO::PARAM_STR);
            $stm->bindParam(':manager_id', $this->manager_id,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588160736'/*.$e->getMessage()*/);}
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
        if(!$ManagerCurrentInfo=$obooking->get_manager_info('manager_email,manager_phone,user_id',$this->manager_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'manager is not found'
            ]);
            exit;
        }

        if($ManagerCurrentInfo->manager_email!==$this->manager_email||$ManagerCurrentInfo->manager_phone!==$this->manager_phone) {
            //check if user exists by email
            if(uString::isEmail($this->manager_email) && $userData = $uAuth->userLogin2info('user_id,cellphone', $this->manager_email, 'email')) {
                $user_id=(int)$userData->user_id;
                if(!uString::isPhone($this->manager_phone) && uString::isPhone($userData->cellphone)) {
                    $this->manager_phone=$userData->cellphone;
                }
            }
            //check if user exists by phone
            elseif(uString::isPhone($this->manager_phone) && $userData = $uAuth->userLogin2info('user_id,email', $this->manager_phone, 'cellphone')) {
                $user_id=(int)$userData->user_id;
                if(!uString::isEmail($this->manager_email) && uString::isEmail($userData->email)) {
                    $this->manager_email=$userData->email;
                }
            }
            else {
                //user is not found - we should unassign manager from previous user_id
                $user_id = 0;
            }
        }
        else {
            $user_id=(int)$ManagerCurrentInfo->user_id;
        }

        $this->save_manager($user_id);

        echo json_encode(array(
            'status'=>'done',
            'user_id'=>$user_id,
            'phone'=>$this->manager_phone,
            'email'=>$this->manager_email
        ));
    }
}
new save_manager_bg($this);
