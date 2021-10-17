<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class rec_result_save_bg {
    private $classes_left;
    /**
     * @var common
     */
    private $obooking;
    /**
     * @var int
     */
    private $payment_type;
    /**
     * @var int
     */
    private $rec_id;
    /**
     * @var int
     */
    private $client_id;
    /**
     * @var int
     */
    private $status;
    /**
     * @var int
     */
    private $cost;
    /**
     * @var int
     */
    private $paid_amount;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["payment_type"],
            $_POST["status"],
            $_POST["rec_id"],
            $_POST["client_id"]
        )) {
            $this->uFunc->error(0);
        }

        $this->status = (int)$_POST["status"];
        $this->client_id = (int)$_POST["client_id"];
        $this->rec_id = (int)$_POST["rec_id"];

        if($_POST["payment_type"]==="subscription") {
            if(($this->classes_left=(int)$this->obooking->get_client_subscription_classes_left($this->client_id))<1) {
                $this->uFunc->error(0);
            }
        }
        else {
            if (!isset(
                $_POST["paid_amount"],
                $_POST["cost"]
            )) {
                $this->uFunc->error(10);
            }

            $this->paid_amount = (int)$_POST["paid_amount"];
            $this->cost = (int)$_POST["cost"];
            $this->payment_type = (int)$_POST["payment_type"];
            if ($this->payment_type < 0 || $this->payment_type > 2) {
                $this->uFunc->error(20, 1);
            }
        }
    }

    private function save_result($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE 
            records_clients
            SET
            status=:status
            WHERE
            rec_id=:rec_id AND
            client_id=:client_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':status', $this->status,PDO::PARAM_INT);
            $stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
            $stm->bindParam(':client_id', $this->client_id,PDO::PARAM_INT);
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

        $this->save_result();

        if($rec_info=$this->obooking->get_record_info("rec_type,office_id,class_id,manager_id,timestamp",$this->rec_id)) {
            $rec_type_id=(int)$rec_info->rec_type;
            $office_id=(int)$rec_info->office_id;
            $class_id=(int)$rec_info->class_id;
            $manager_id=(int)$rec_info->manager_id;
            $timestamp=(int)$rec_info->timestamp;

            if($rec_type_info=$this->obooking->get_rec_type_info("rec_type_name",$rec_type_id)) {
                $rec_type_name = $rec_type_info->rec_type_name;
            }
            else {
                $rec_type_name = "";
            }

            if($office_info=$this->obooking->get_office_info("office_name",$office_id)) {
                $office_name = $office_info->office_name;
            }
            else {
                $office_id=0;
                $office_name="";
            }

            if($class_info=$this->obooking->get_class_info("class_name",$class_id)) {
                $class_name = $class_info->class_name;
            }
            else {
                $class_name = "";
            }

            if($manager_info=$this->obooking->get_manager_info("manager_name,manager_lastname",$manager_id)) {
                $manager_name = $manager_info->manager_name . " " . $manager_info->manager_lastname;
            }
            else {
                $manager_name = "";
            }

            $description_end=": ".$rec_type_name."<br>
                        Класс: ".$class_name."<br>
                        Наставник: ".$manager_name."<br>
                        Время занятия: ".date("d.m.Y H:i",$timestamp);

            if($this->status===0) {
                $this->obooking->save_records_history(time(), $this->client_id, "Прогулял(а) занятие" . $description_end);
            }
            elseif($this->status===1) {
                $this->obooking->save_records_history(time(), $this->client_id, "Посетил(а) занятие" . $description_end);
            }


            if($_POST["payment_type"]==="subscription") {
                $description = "Оплата занятия <b>абонементом</b>: " . $rec_type_name . "<br>
                Филиал: " . $office_name . "<br>
                Класс: " . $class_name . "<br>
                Наставник: " . $manager_name . "<br>
                Время занятия: " . date("d.m.Y H:i", $timestamp);
                $this->obooking->pay_with_subscription(
                    time(),
                    $this->client_id,
                    $office_id,
                    $description
                );
            }
            else {
                if ($this->cost) {

                    $description = "Оплата занятия: " . $rec_type_name . "<br>
                    Филиал: " . $office_name . "<br>
                    Класс: " . $class_name . "<br>
                    Наставник: " . $manager_name . "<br>
                    Время занятия: " . date("d.m.Y H:i", $timestamp);
                    $this->obooking->save_balance_history(
                        time(),
                        $this->client_id,
                        $office_id,
                        $description,
                        $this->cost * -1,
                        100
                    );
                }


                if ($this->paid_amount) {
                    $this->obooking->save_balance_history(
                        time(),
                        $this->client_id,
                        $office_id,
                        "Внесение оплаты",
                        $this->paid_amount,
                        $this->payment_type
                    );
                }
            }
        }

        if($_POST["payment_type"]!=="subscription") {
            $balance_delta=$this->obooking->update_client_balance($this->client_id,$this->paid_amount-$this->cost);
        }
        else {
            $balance_delta = 0;
        }



        echo json_encode(array(
            "status"=>"done",
            "balance_delta"=>$balance_delta,
            "rec_status"=>$this->status,
            "rec_id"=>$this->rec_id,
            "client_id"=>$this->client_id
        ));
    }
}
/*$newClass=*/new rec_result_save_bg($this);
