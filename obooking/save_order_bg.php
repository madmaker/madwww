<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

/**
 * Saves information about order
 * ## Request (POST):
 * - data
 * - order_id
 *
 * ### To save common order data
 * - office_id : int - office_id from offices table in madamagers_obooking db
 * - trial_date : string - date in dd.mm.YYYY format
 * - next_contact_date : string - date in dd.mm.YYYY format
 * - client_name : string
 * - phone : string - should be in +09992223333 format
 * - email : string - should be in me@gmail.com format
 * - status : int - status_id from order_statuses table in madmakers_obooking db
 * - comment : string - just a additional comment
 * - source : int - source_id from order_sources table in madmakers_obooking db
 * - how_did_find_out : int - how_did_find_out_id from order_how_did_find_outs table in madmakers_obooking db
 *
 * ### To save course
 * - course_id : int - course_id from course table in madmakers_obooking db
 * - action : int
 *      - 0 - unassign course from order
 *      - 1 - assign course to order
 *
 * ### To save manager
 * - manager_id : int - manager_id from managers table in madmakers_obooking db
 * - action : int
 *      - 0 - unassign manager from order
 *      - 1 - assign manager to order
 *
 * ## Response (JSON-encoded array):
 * - status
 *      - done - in case of success
 *      - forbidden - if you have no sufficient rights
 *      - error - in case of error
 *
 * ### If course is assigned/unassigned to/from order
 * - order_id : int
 * - course_id : int
 * - course_name : string name of course passed in request as course_id
 * - action : int
 *
 * ### If manager is assigned/unassigned to/from order
 * - order_id : int
 * - manager_id : int
 * - manager_name : string name of manager passed in request as manager_id
 * - manager_lastname : string
 * - action : string
 *
 * ### In case of error
 * - msg
 *      - trial date is wrong - in case if trial_date has wrong format
 *      - next_contact_date date is wrong - in case if next_contact_date has wrong format
 *      - client name is wrong - in case if client_name is empty or too short
 *      - phone is wrong - in case if phone has wrong format
 *      - email is wrong - in case if email has wrong format
 *      - order status is not found - if status passed in requests doesn't exists
 *      - order source is not found - if status passed in requests doesn't exists
 *      - order how_did_find_out is not found - if status passed in requests doesn't exists
 *      - course is not found - if status passed in requests doesn't exists
 *      - office is not found - if status passed in requests doesn't exists
 *      - order is not found - if status passed in requests doesn't exists
 *      - wrong request - if have't received fields required in request
 *
 * @package obooking
 * @API
 */
class save_order_bg {
    /**
     * @var int
     */
    private $orderInfo;
    /**
     * @var int
     */
    private $order_id;
    private $manager_name;
    private $office_name;
    private $obooking;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["data"],
            $_POST["order_id"]
        )) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request'
            ));
            exit;
        }

        $this->order_id=(int)$_POST["order_id"];

        //check if order belongs to current site_id and get client info if order is assigned to client
        if(!$this->orderInfo=$this->obooking->order_id2data($this->order_id,'client_id,client_name,email,phone')) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'order is not found'
            ));
            exit;
        }
        $this->orderInfo->client_id=(int)$this->orderInfo->client_id;

        if($_POST["data"]==='common_data') {
            $this->save_order_common_data();
        }
        if($_POST["data"]==='toggle course') {
            $this->toggle_course();
        }
        if($_POST["data"]==='toggle manager') {
            $this->toggle_manager();
        }
    }

    private function save_order_common_data($site_id=site_id) {
        if(!isset(
            $_POST["office_id"],
            $_POST["trial_date"],
            $_POST["next_contact_date"],
            $_POST["client_name"],
            $_POST["phone"],
            $_POST["email"],
            $_POST["status"],
            $_POST["comment"],
            $_POST["source"],
            $_POST["how_did_find_out"],
            $_POST["client_id"]
        )) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request'
            ));
            exit;
        }

        $office_id=(int)$_POST["office_id"];
        if($office_id) {
            if (!$office_info = $this->obooking->get_office_info("office_name", $office_id, site_id)) {
                echo json_encode(array(
                    'status'=>'error',
                    'msg'=>'office is not found'
                ));
                exit;
            }
            $office_name = $office_info->office_name;
        }
        else {
            $office_name="Не выбран";
        }

        $trial_date=trim($_POST["trial_date"]);
        if(($trial_date !== '') && !uString::isDate($trial_date)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'trial date is wrong'
            ));
            exit;
        }

        $next_contact_date=trim($_POST["next_contact_date"]);
        if(($next_contact_date !== '') && !uString::isDate($next_contact_date)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'next_contact_date date is wrong'
            ));
            exit;
        }

        /** @noinspection PhpUndefinedFieldInspection */
        if($this->orderInfo->client_id) {//Order is already assigned to client
            /** @noinspection PhpUndefinedFieldInspection */
            $client_id=(int)$this->orderInfo->client_id;
            /** @noinspection PhpUndefinedFieldInspection */
            $phone=$this->orderInfo->phone;
            /** @noinspection PhpUndefinedFieldInspection */
            $email=$this->orderInfo->email;

            $client_name=$this->orderInfo->client_name;
        }
        else {//we should assign this order to client
            $client_name=trim($_POST["client_name"]);
            if(strlen($client_name)<2) {
                echo json_encode(array(
                    'status'=>'error',
                    'msg'=>'client name is wrong'
                ));
                exit;
            }

            $phone = trim($_POST["phone"]);
            if (($phone !== '') && !uString::isPhone($phone)) {
                echo json_encode(array(
                    'status' => 'error',
                    'msg' => 'phone is wrong'
                ));
                exit;
            }

            $email = trim($_POST["email"]);
            if (($email !== '') && !uString::isEmail($email)) {
                echo json_encode(array(
                    'status' => 'error',
                    'msg' => 'email is wrong'
                ));
                exit;
            }

            $stm_clients=$this->obooking->getClientByUserIdOrEmailOrPhone(0,$email,$phone,$site_id);
            if($client=$stm_clients->fetch(PDO::FETCH_OBJ)) {
                //client with email or phone provided exists - we'll assign order to this client
                $client_name=$client->client_name.' '.$client->client_lastname;
                $email=$client->client_email;
                $phone=$client->client_phone;
                if(!uString::isPhone($phone)&&uString::isPhone($client->client_phone2)) {
                    $phone=$client->client_phone2;
                }
                $client_id=(int)$client->client_id;
            }
            else {
                //we should create new client
                $client_id=$this->obooking->create_new_client($client_name);

                $client_data=[
                    'client_phone'=>$phone,
                    'client_email'=>$email
                ];
                $this->obooking->updateClient($client_id,$client_data,$site_id);

                $this->obooking->assignUserToOwnerAdminManagerClient(0,$email,$phone,$site_id);
            }
        }

        $status=(int)$_POST["status"];
        if($status && !$qr = $this->obooking->status_id2data($status)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'order status is not found'
            ));
            exit;
        }

        $source=(int)$_POST["source"];
        if($source && !$qr = $this->obooking->source_id2data($source)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'order source is not found'
            ));
            exit;
        }

        $how_did_find_out=(int)$_POST["how_did_find_out"];
        if($how_did_find_out && !$qr = $this->obooking->how_did_find_out_id2data($how_did_find_out)) {
            echo json_encode(array(
                'status' => 'error',
                'msg' => 'order how_did_find_out is not found'
            ));
            exit;
        }

        $comment=trim($_POST["comment"]);


        if(uString::isDate($trial_date)) {
            $trial_date_ar = explode(".", $trial_date);
            $trial_date_formatted = $trial_date_ar[1] . '/' . $trial_date_ar[0] . '/' . $trial_date_ar[2];
            $trial_date_timestamp = strtotime($trial_date_formatted);
        }
        else {
            $trial_date_timestamp = 0;
        }

        if(uString::isDate($next_contact_date)) {
            $next_contact_date_ar = explode(".", $next_contact_date);
            $next_contact_date_formatted = $next_contact_date_ar[1] . '/' . $next_contact_date_ar[0] . '/' . $next_contact_date_ar[2];
            $next_contact_date_timestamp = strtotime($next_contact_date_formatted);
        }
        else {
            $next_contact_date_timestamp = 0;
        }

        $timestamp=time();
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            orders 
            SET 
            office_id=:office_id,
            next_contact_date=:next_contact_date,
            trial_date=:trial_date,
            client_name=:client_name,
            phone=:phone,
            email=:email,
            status_id=:status,
            comment=:comment,
            source_id=:source,
            how_did_find_out_id=:how_did_find_out,
            sys_status=2,
            timestamp=:timestamp,
            client_id=:client_id
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':office_id', $office_id,PDO::PARAM_INT);
            $stm->bindParam(':next_contact_date', $next_contact_date_timestamp,PDO::PARAM_INT);
            $stm->bindParam(':trial_date', $trial_date_timestamp,PDO::PARAM_INT);
            $stm->bindParam(':client_name', $client_name,PDO::PARAM_STR);
            $stm->bindParam(':phone', $phone,PDO::PARAM_STR);
            $stm->bindParam(':email', $email,PDO::PARAM_STR);
            $stm->bindParam(':status', $status,PDO::PARAM_INT);
            $stm->bindParam(':comment', $comment,PDO::PARAM_STR);
            $stm->bindParam(':source', $source,PDO::PARAM_INT);
            $stm->bindParam(':how_did_find_out', $how_did_find_out,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589228637'/*.$e->getMessage()*/,1);}

        echo json_encode(array(
        'status'=>'done'
        ));
        exit;
    }
    private function toggle_course($site_id=site_id) {
        if(!isset(
            $_POST["course_id"],
            $_POST["action"]
        )) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request'
            ));
            exit;
        }

        $course_id=(int)$_POST["course_id"];

        //check if course_id belongs to site and get course_name
        if(!$course_data=$this->obooking->course_id2data($course_id,"course_name",$site_id)) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'course is not found'
            ));
            exit;
        }
        $course_name=$course_data->course_name;

        $action=(int)$_POST["action"];
        if($action<0||$action>1) {
            $action = 0;
        }

        $timestamp=time();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            orders 
            SET 
            timestamp=:timestamp
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1580859250'/*.$e->getMessage()*/,1);}

        if($action) {
            try {

                $stm = $this->uFunc->pdo("obooking")->prepare("REPLACE INTO 
                order_courses (order_id, course_id) VALUES (:order_id,:course_id)
                ");
                $stm->bindParam(':order_id', $this->order_id, PDO::PARAM_INT);
                $stm->bindParam(':course_id', $course_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('1580859374'/*.$e->getMessage()*/, 1);}
        }
        else {
            try {

                $stm = $this->uFunc->pdo("obooking")->prepare("DELETE FROM 
                order_courses
                WHERE
                order_id=:order_id AND
                course_id=:course_id
                ");
                $stm->bindParam(':order_id', $this->order_id, PDO::PARAM_INT);
                $stm->bindParam(':course_id', $course_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('1580859374'/*.$e->getMessage()*/, 1);}
        }

        echo json_encode(array(
        'status'=>'done',
        'order_id'=>$this->order_id,
        'course_id'=>$course_id,
        'course_name'=>$course_name,
        'action'=>$action
        ));
        exit;
    }
    private function toggle_manager($site_id=site_id) {
        if(!isset(
            $_POST["manager_id"],
            $_POST["action"]
        )) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'wrong request'
            ));
            exit;
        }

        $manager_id=(int)$_POST["manager_id"];

        //check if manager_id belongs to site and get manager_name
        if(!$manager_data=$this->obooking->get_manager_info("manager_name,manager_lastname",$manager_id,$site_id)) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'manager is not found'
            ));
            exit;
        }
        $manager_name=$manager_data->manager_name;
        $manager_lastname=$manager_data->manager_lastname;

        $action=(int)$_POST["action"];
        if($action<0||$action>1) {
            $action = 0;
        }

        $timestamp=time();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            orders 
            SET 
            timestamp=:timestamp
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581920067'/*.$e->getMessage()*/,1);}

        if($action) {
            try {

                $stm = $this->uFunc->pdo("obooking")->prepare("REPLACE INTO 
                order_managers (order_id, manager_id) VALUES (:order_id,:manager_id)
                ");
                $stm->bindParam(':order_id', $this->order_id, PDO::PARAM_INT);
                $stm->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('1581920071'/*.$e->getMessage()*/, 1);}
        }
        else {
            try {

                $stm = $this->uFunc->pdo("obooking")->prepare("DELETE FROM 
                order_managers
                WHERE
                order_id=:order_id AND
                manager_id=:manager_id
                ");
                $stm->bindParam(':order_id', $this->order_id, PDO::PARAM_INT);
                $stm->bindParam(':manager_id', $manager_id, PDO::PARAM_INT);
                $stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('1581920075'/*.$e->getMessage()*/, 1);}
        }

        echo json_encode(array(
        'status'=>'done',
        'order_id'=>$this->order_id,
        'manager_id'=>$manager_id,
        'manager_name'=>$manager_name,
        'manager_lastname'=>$manager_lastname,
        'action'=>$action
        ));
        exit;
    }
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
    }
}
new save_order_bg($this);
