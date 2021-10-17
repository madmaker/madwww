<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "obooking/classes/common.php";
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class save_card_type_bg {
    /**
     * @var int
     */
    private $price;
    /**
     * @var int
     */
    private $validity;
    private $card_type_name;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $card_type_id;

    private function check_data() {
        if(!isset(
                $_POST["card_type_id"],
                $_POST["card_type_name"],
                $_POST["validity"],
                $_POST["price"]
        )) {
            $this->uFunc->error(10);
        }
        $this->card_type_id=(int)$_POST["card_type_id"];
        $this->card_type_name=$_POST["card_type_name"];
        $this->validity=(int)$_POST["validity"];
        $this->price=(int)$_POST["price"];
    }
    private function save_card_type() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            card_types
            SET 
            card_type_name=:card_type_name,
            validity=:validity,
            price=:price
            WHERE 
            card_type_id=:card_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':card_type_id', $this->card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':card_type_name', $this->card_type_name,PDO::PARAM_STR);
            $stm->bindParam(':validity', $this->validity,PDO::PARAM_INT);
            $stm->bindParam(':price', $this->price,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function create_card_type($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            card_type_id 
            FROM 
            card_types 
            ORDER BY
            card_type_id DESC
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $card_type_id = $qr->card_type_id + 1;
            }
            else {
                $card_type_id = 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            card_types (
            card_type_id, 
            card_type_name,
            validity,
            price,
            site_id
            ) VALUES (
            :card_type_id, 
            :card_type_name,
            :validity,
            :price,
            :site_id
            )
            ");
            $stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':card_type_name', $this->card_type_name,PDO::PARAM_STR);
            $stm->bindParam(':validity', $this->validity,PDO::PARAM_INT);
            $stm->bindParam(':price', $this->price,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
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

        $this->check_data();

        if($this->card_type_id) {
            $this->save_card_type();
        }
        else {
            $this->create_card_type();
        }

        print json_encode(array(
            "status"=>"done",
            "card_type_id"=>$this->card_type_id,
            "card_type_name"=>$this->card_type_name,
            "validity"=>$this->validity,
            "price"=>$this->price
        ));
    }
}
new save_card_type_bg($this);
