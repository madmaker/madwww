<?php
namespace obooking;
use DateTime;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';
require_once 'uAuth/classes/common.php';

/**
 * REST API
 * ===
 * - Updates information about client
 * - Automatically assigns client to user's profile is email or phone is found in someone's user's profile
 *
 * ---
 * # Request
 * ## POST
 * - client_id
 * - client_name
 * - client_lastname
 * - client_birthday
 * - client_phone
 * - client_phone2
 * - client_email
 * - client_status
 * - client_comment
 * - rec_id
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
 *      - client name is empty
 *      - client phone is wrong
 *      - client phone2 is wrong
 *      - client email is wrong
 *      - client is not found
 * - user_id - assigned user's profile id
 * - phone - client phone after save (if it's empty, it can be retrieved automatically from user's profile)
 * - email - client email after save (if it's empty, it can be retrieved automatically from user's profile)
 * @package obooking
 * @todo Выпилить лишние функции отсюда, задокументировать работу с картой и балансом при сохранении клиента
 */
class save_client_bg{
    /**
     * @var int
     */
    private $rec_id;
    /**
     * @var common
     */
    private $obooking;
    private $client_phone2;
    private $client_name;
    private $client_lastname;
    private $client_phone;
    private $client_email;
    private $client_status;
    private $client_birthday;
    private $client_balance;
    private $client_comment;
    private $client_id;
    private $uFunc;
    private function check_data() {
        if(!isset(
            $_POST['client_id'],
            $_POST['client_name'],
            $_POST['client_lastname'],
            $_POST['client_birthday'],
            $_POST['client_phone'],
            $_POST['client_phone2'],
            $_POST['client_email'],
            $_POST['client_status'],
            $_POST['client_comment'],
            $_POST['rec_id']
        )) {
            $this->uFunc->error(10);
        }

        $this->rec_id=(int)$_POST['rec_id'];

        //
        $this->client_id=(int)$_POST['client_id'];
        //
        $this->client_name=trim($_POST['client_name']);
        if($this->client_name=== '') {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'client name is empty'
            ));
            exit;
        }
        //
        $this->client_lastname=trim($_POST['client_lastname']);
        //
        $this->client_birthday=trim($_POST['client_birthday']);
        $client_birthday_isDate=uString::isDate($this->client_birthday);
        if($this->client_birthday!== '' &&$this->client_birthday!== '0' &&!$client_birthday_isDate) {
            $this->uFunc->error(20, 1);
        }
        if($client_birthday_isDate) {
            $date_formatted=DateTime::createFromFormat('d.m.Y',$this->client_birthday)->format('U');
            $this->client_birthday=$date_formatted;
        }
        else {
            $this->client_birthday = 0;
        }
        //
        $this->client_phone=trim($_POST['client_phone']);

        if(($this->client_phone !== '') && !uString::isPhone($this->client_phone)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'client phone is wrong'
            ));
            exit;
        }
        //
        $this->client_phone2=trim($_POST['client_phone2']);
        if($this->client_phone2!== '' &&!uString::isPhone($this->client_phone2)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'client phone2 is wrong'
            ));
            exit;
        }
        //
        $this->client_email=trim($_POST['client_email']);
        if($this->client_email!== ''&&!uString::isEmail($this->client_email)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'client email is wrong'
            ));
            exit;
        }
        //
        $this->client_status=(int)$_POST['client_status'];
        if($this->client_status<0&&$this->client_status>12) {
            $this->uFunc->error(50, 1);
        }
        //
        $this->client_comment=trim($_POST['client_comment']);
    }

    /**
     * Following Options in this scope should be defined:
     * - $this->client_name,
     * - $this->client_lastname,
     * - $this->client_birthday,
     * - $this->client_phone,
     * - $this->client_phone2,
     * - $this->client_email,
     * - $this->client_status,
     * - $this->client_comment
     * @param int $user_id
     * @param int $site_id
     */
//    private function save_client($user_id,$site_id=site_id) {
//        try {
//            $stm=$this->uFunc->pdo('obooking')->prepare('UPDATE
//            madmakers_obooking.clients
//            SET
//            client_name=:client_name,
//            client_lastname=:client_lastname,
//            client_birthdate=:client_birthday,
//            client_phone=:client_phone,
//            client_phone2=:client_phone2,
//            client_email=:client_email,
//            client_status=:client_status,
//            client_comment=:client_comment,
//            user_id=:user_id
//            WHERE
//            client_id=:client_id AND
//            site_id=:site_id
//            ');
//
//            $stm->bindParam(':client_name', $this->client_name,PDO::PARAM_STR);
//            $stm->bindParam(':client_lastname', $this->client_lastname,PDO::PARAM_STR);
//            $stm->bindParam(':client_birthday', $this->client_birthday,PDO::PARAM_INT);
//            $stm->bindParam(':client_phone', $this->client_phone,PDO::PARAM_STR);
//            $stm->bindParam(':client_phone2', $this->client_phone2,PDO::PARAM_STR);
//            $stm->bindParam(':client_email', $this->client_email,PDO::PARAM_STR);
//            $stm->bindParam(':client_status', $this->client_status,PDO::PARAM_INT);
//            $stm->bindParam(':client_comment', $this->client_comment,PDO::PARAM_STR);
//            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
//
//            $stm->bindParam(':client_id', $this->client_id,PDO::PARAM_INT);
//            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            $stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('1588158842'/*.$e->getMessage()*/);}
//    }

    private function deposit_account() {
        if(!isset(
            $_POST['paid_amount'],
            $_POST['payment_type'],
            $_POST['office_id'],
            $_POST['client_id'],
            $_POST['comment']
        )) {
            $this->uFunc->error(70, 1);
        }

        $paid_amount=trim($_POST['paid_amount']);
        $paid_amount=(int)$paid_amount;

        $comment='<br>'.htmlspecialchars(trim($_POST['comment']));

        if($paid_amount<0) {
            $paid_amount *= -1;
        }

        $payment_type=(int)$_POST['payment_type'];
        if(
            $payment_type===0||
            $payment_type===1||
            $payment_type===2
        ) {
            $description= 'Внесение оплаты';
        }
        elseif ($payment_type===100) {
            $description= 'Списание';
            $paid_amount *= -1;
        }
        else {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong payment_type'
            ));
            exit;
        }

        $office_id=(int)$_POST['office_id'];
        if(!$office_info=$this->obooking->get_office_info('office_id',$office_id)) {
            $this->uFunc->error(1588157061);
        }

        $client_id=(int)$_POST['client_id'];

        if(!$this->obooking->get_client_info('client_id',$client_id)) {
            $this->uFunc->error(1582013250, 1);
        }

        $this->obooking->update_client_balance($client_id,$paid_amount);

        $this->obooking->save_balance_history(
            time(),
            $client_id,
            $office_id,
            $description.$comment,
            $paid_amount,
            $payment_type
        );

        echo json_encode(array(
            'status'=>'done',
            'client_id'=>$client_id
        ));
        exit;
    }
    private function cancel_operation() {
        if(!isset($_POST['operation_id'])) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong data 1581995375'
            ));
            exit;
        }
        $operation_id=(int)$_POST['operation_id'];

        //Get operation info
        if(!$operation_data=$this->obooking->client_balance_operation_id2data('amount,client_id,office_id,timestamp,payment_method',$operation_id)) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'operation does not exists'
            ));
            exit;
        }
        $amount=$operation_data->amount*(-1);
        $client_id=(int)$operation_data->client_id;
        $office_id=(int)$operation_data->office_id;
        $timestamp=(int)$operation_data->timestamp;
        $payment_method=(int)$operation_data->payment_method;
        $description= 'Отмена операции #' .$operation_id. ' от ' .date('d.m.Y H:i',$timestamp). ' по инициативе администратора сайта';

        if(
            $payment_method!==0&&
            $payment_method!==1&&
            $payment_method!==2&&
            $payment_method!==96&&
            $payment_method!==97&&
            $payment_method!==98&&
            $payment_method!==99&&
            $payment_method!==100
        ) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong operation status 1581997273'
            ));
            exit;
        }


        $this->obooking->update_client_balance($client_id,$amount);

        $id=$this->obooking->save_balance_history(
            time(),
            $client_id,
            $office_id,
            $description,
            $amount,
            101
        );

        try {
            $stm=$this->uFunc->pdo('obooking')->prepare("UPDATE 
            clients_balance_history
            SET
            payment_method=200+payment_method,
            description=concat(description,'<br>Отменена операцией #$id')
            WHERE
            id=:id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':id', $operation_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581997402'/*.$e->getMessage()*/);}

        echo json_encode(array(
            'status'=>'done',
            'client_id'=>$client_id
        ));
        exit;
    }
    private function recalculate_balance() {
        if(!isset($_POST['client_id'])) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong data 1581998353'
            ));
            exit;
        }
        $client_id=(int)$_POST['client_id'];

        //Get operation info
        $client_balance=$this->obooking->recalculate_client_balance($client_id);

        echo json_encode(array(
            'status'=>'done',
            'client_id'=>$client_id,
            'client_balance'=>number_format($client_balance,0, '.', ' ')
        ));
        exit;
    }
    private function office_balance() {
        if(!isset(
            $_POST['amount'],
            $_POST['method'],
            $_POST['office_id'],
            $_POST['comment']
        )) {
            $this->uFunc->error(1582005048, 1);
        }

        $amount=trim($_POST['amount']);
        $amount=(int)$amount;

        if($amount<0) {
            $amount = 0;
        }

        $comment=trim($_POST['comment']);

        $method=(int)$_POST['method'];

        if($method===96) {
            $description= 'Списание наличных средств со счета филиала';
            $amount*=-1;
        }
        elseif($method===97) {
            $description= 'Списание безналичных средств со счета филиала';
            $amount*=-1;
        }
        elseif($method===98) {
            $description = 'Внесение наличных средств на счет филиала';
        }
        else {
            $this->uFunc->error(80, 1);
        }

        $payment_method=$method;

        $office_id=(int)$_POST['office_id'];
        if(!$office_info=$this->obooking->get_office_info('office_id',$office_id)) {
            $this->uFunc->error(1582013148);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        $this->obooking->save_balance_history(
            time(),
            0,
            $office_id,
            $description.'<br>'.$comment,
            $amount,
            $payment_method
        );

        echo json_encode(array(
            'status'=>'done',
            'office_id'=>$office_id
        ));
        exit;
    }
    public function __construct (&$uCore) {
        $this->uFunc=new uFunc($uCore);
        $this->obooking=new common($uCore);
        $uAuth=new \uAuth\common($uCore);


        if(isset($_POST['office_balance'])) {//TODO-nik87 вынести в отдельное REST API
            $this->office_balance();
        }
        elseif(isset($_POST['paid_amount'])) {//TODO-nik87 вынести в отдельное REST API
            $this->deposit_account();
        }
        elseif(isset($_POST['action'])) {//TODO-nik87 вынести в отдельное REST API
            $action=$_POST['action'];
            if($action==='cancel_operation') {
                $this->cancel_operation();
            }
            if($action==='recalculate_balance') {//TODO-nik87 вынести в отдельное REST API
                $this->recalculate_balance();
            }
            exit;
        }
        else {
            $this->check_data();

            //get current client user_id,phone,phone2 and email (we'll check if they are changed)
            if(!$ClientCurrentInfo=$this->obooking->get_client_info('client_email,client_phone,client_phone2,user_id',$this->client_id)) {
                print json_encode([
                    'status'=>'error',
                    'msg'=>'client is not found'
                ]);
                exit;
            }

            if($ClientCurrentInfo->client_email!==$this->client_email||$ClientCurrentInfo->client_phone!==$this->client_phone||$ClientCurrentInfo->client_phone2!==$this->client_phone2) {
                //check if user exists by email
                if(uString::isEmail($this->client_email) && $userData = $uAuth->userLogin2info('user_id,cellphone', $this->client_email, 'email')) {
                    $user_id=(int)$userData->user_id;
                    if(!uString::isPhone($this->client_phone) && uString::isPhone($userData->cellphone)) {
                        $this->client_phone=$userData->cellphone;
                    }
                }
                //check if user exists by phone
                elseif(uString::isPhone($this->client_phone) && $userData = $uAuth->userLogin2info('user_id,email', $this->client_phone, 'cellphone')) {
                    $user_id=(int)$userData->user_id;
                    if(!uString::isEmail($this->client_email) && uString::isEmail($userData->email)) {
                        $this->client_email=$userData->email;
                    }
                }
                //check if user exists by phone2
                elseif(uString::isPhone($this->client_phone2) && $userData = $uAuth->userLogin2info('user_id,email', $this->client_phone2, 'cellphone')) {
                    $user_id=(int)$userData->user_id;
                    if(!uString::isEmail($this->client_email) && uString::isEmail($userData->email)) {
                        $this->client_email=$userData->email;
                    }
                }
                else {
                    //user is not found - we should unassign client from previous user_id
                    $user_id = 0;
                }
            }
            else {
                $user_id=(int)$ClientCurrentInfo->user_id;
            }

//            $this->save_client($user_id);

            $client_data=[
                'client_name'=>$this->client_name,
                'client_lastname'=>$this->client_lastname,
                'client_birthdate'=>$this->client_birthday,
                'client_phone'=>$this->client_phone,
                'client_phone2'=>$this->client_phone2,
                'client_email'=>$this->client_email,
                'client_status'=>$this->client_status,
                'client_comment'=>$this->client_comment,
                'user_id'=>$user_id
            ];
            $this->obooking->updateClient($this->client_id,$client_data);

            $card_info_ar = [];
            if ($card_info = $this->obooking->get_client_longest_card($this->client_id, site_id)) {
                $card_info_ar = array(
                    'card_type_name' => $this->obooking->card_type_id2name($card_info->card_type_id),
                    'card_number' => $card_info->card_number,
                    'start_date' => date('d.m', $card_info->start_date),
                    'valid_thru' => date('d.m', $card_info->valid_thru)
                );
            }

            $record_client_info_ar = array(
                'record_client_status' => -1,
                'record_client_trial' => 0
            );

            try {
                $stm = $this->uFunc->pdo('obooking')->prepare('SELECT 
                status,
                trial
                FROM 
                records_clients 
                WHERE 
                rec_id=:rec_id AND
                client_id=:client_id AND
                site_id=:site_id
                ');
                $site_id = site_id;
                $stm->bindParam(':client_id', $this->client_id, PDO::PARAM_INT);
                $stm->bindParam(':rec_id', $this->rec_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('110'/*.$e->getMessage()*/);
            }

            /** @noinspection PhpUndefinedVariableInspection */
            if ($qr = $stm->fetch(PDO::FETCH_OBJ)) {
                $record_client_info_ar = array(
                    'record_client_status' => $qr->status,
                    'record_client_trial' => $qr->trial
                );
            }


            $client_info_ar = array(
                'client_balance' => 0
            );
            if ($client_info = $this->obooking->get_client_info('client_balance', $this->client_id, site_id)) {
                $client_info_ar = array(
                    'client_balance' => $client_info->client_balance
                );
            }
            $has_card=0;
            if($card_data=$this->obooking->get_client_longest_card($this->client_id)) {
                $has_card=1;
                if($card_data->valid_thru<time()) {
                    $has_card=0;
                }
            }

            $classes_left=$this->obooking->get_client_subscription_classes_left($this->client_id);

            $record_info_ar = array(
                'price' => 0,
                'price_without_card' => 0,
                'classes_left' => $classes_left
            );
            if ($rec_info = $this->obooking->get_record_info('price,price_without_card', $this->rec_id, site_id)) {
                $record_info_ar = array(
                    'price' => $rec_info->price,
                    'price_without_card' => $rec_info->price_without_card
                );
            }


            echo json_encode(array_merge(
                array(
                    'status' => 'done',
                    'user_id'=> $user_id,
                    'email'=> $this->client_email,
                    'phone'=> $this->client_phone,
                    'has_card'=>$has_card
                ),
                $card_info_ar,
                $record_client_info_ar,
                $client_info_ar,
                $record_info_ar
            ));
        }
    }
}
new save_client_bg($this);
