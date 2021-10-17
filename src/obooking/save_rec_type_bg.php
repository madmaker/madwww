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

class save_rec_type_bg{
    private $rec_type_duration;
    private $rec_type_price;
    private $rec_type_price_without_card;
    private $obooking;
    private $rec_type_id;
    private $rec_type_name;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["rec_type_id"],
            $_POST["rec_type_name"],
            $_POST["rec_type_price"],
            $_POST["rec_type_price_without_card"],
            $_POST["rec_type_duration"]
        )) {
            $this->uFunc->error(10, 1);
        }

        $this->rec_type_name=trim($_POST["rec_type_name"]);
        $this->rec_type_price=trim($_POST["rec_type_price"]);
        $this->rec_type_price_without_card=trim($_POST["rec_type_price_without_card"]);
        $duration=trim($_POST["rec_type_duration"]);

        if($this->rec_type_name==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type name is empty"
            ));
            exit;
        }
        if($this->rec_type_price==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type price is empty"
            ));
            exit;
        }
        if($this->rec_type_price_without_card==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type price_without_card is empty"
            ));
            exit;
        }
        if($duration==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type duration is empty"
            ));
            exit;
        }

        $this->rec_type_price=str_replace(",",".",$this->rec_type_price);
        $this->rec_type_price_without_card=str_replace(",",".",$this->rec_type_price_without_card);

        if(!uString::isPrice($this->rec_type_price)) {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type price is wrong"
            ));
            exit;
        }
        if(!uString::isPrice($this->rec_type_price_without_card)) {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type price_without_card is wrong"
            ));
            exit;
        }
        if(!uString::isTime($duration)) {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"rec_type duration is wrong"
            ));
            exit;
        }

        $duration_ar=explode(":",$duration);
        $this->rec_type_duration=$duration_ar[0]*3600+$duration_ar[1]*60;


        $this->rec_type_id=(int)$_POST["rec_type_id"];

        if(!$this->obooking->get_rec_type_info("rec_type_id",$this->rec_type_id)) {
            $this->uFunc->error(20, 1);
        }
    }
    private function save_rec_type() {
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            rec_types
            SET
            rec_type_name=:rec_type_name,
            rec_type_price=:rec_type_price,
            rec_type_price_without_card=:rec_type_price_without_card,
            rec_type_duration=:rec_type_duration
            WHERE
            rec_type_id=:rec_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;

            $stm->bindParam(':rec_type_name', $this->rec_type_name,PDO::PARAM_STR);
            $stm->bindParam(':rec_type_price', $this->rec_type_price,PDO::PARAM_STR);
            $stm->bindParam(':rec_type_price_without_card', $this->rec_type_price_without_card,PDO::PARAM_STR);
            $stm->bindParam(':rec_type_duration', $this->rec_type_duration,PDO::PARAM_INT);

            $stm->bindParam(':rec_type_id', $this->rec_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}
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
        $this->save_rec_type();

        echo json_encode(array(
            'status'=>'done'
        ));
    }
}
new save_rec_type_bg($this);
