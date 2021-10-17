<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_subscription_type_bg {
    /**
     * @var int
     */
    private $classes_included;
    /**
     * @var int
     */
    private $rep_classes_included;
    /**
     * @var int
     */
    private $group_classes_included;
    /**
     * @var int
     */
    private $price;
    /**
     * @var int
     */
    private $validity;
    private $subscription_type_name;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $subscription_type_id;

    private function check_data() {
        if(!isset(
                $_POST["subscription_type_id"],
                $_POST["subscription_type_name"],
                $_POST["validity"],
                $_POST["group_classes_included"],
                $_POST["rep_classes_included"],
                $_POST["classes_included"],
                $_POST["price"]
        )) {
            $this->uFunc->error(10);
        }
        $this->subscription_type_id=(int)$_POST["subscription_type_id"];
        $this->subscription_type_name=$_POST["subscription_type_name"];
        $this->validity=(int)$_POST["validity"];
        $this->price=(int)$_POST["price"];
        $this->group_classes_included= (bool)(int)$_POST["group_classes_included"];
        $this->rep_classes_included= (bool)(int)$_POST["rep_classes_included"];
        $this->classes_included=(int)$_POST["classes_included"];
    }
    private function save_subscription_type() {
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            subscription_types
            SET 
            subscription_type_name=:subscription_type_name,
            validity=:validity,
            group_classes_included=:group_classes_included,
            rep_classes_included=:rep_classes_included,
            classes_included=:classes_included,
            price=:price
            WHERE 
            subscription_type_id=:subscription_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':subscription_type_id', $this->subscription_type_id,PDO::PARAM_INT);
            $stm->bindParam(':subscription_type_name', $this->subscription_type_name,PDO::PARAM_STR);
            $stm->bindParam(':validity', $this->validity,PDO::PARAM_INT);
            $stm->bindParam(':price', $this->price,PDO::PARAM_INT);
            $stm->bindParam(':group_classes_included', $this->group_classes_included,PDO::PARAM_INT);
            $stm->bindParam(':rep_classes_included', $this->rep_classes_included,PDO::PARAM_INT);
            $stm->bindParam(':classes_included', $this->classes_included,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return 0;
    }
    private function create_subscription_type($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            subscription_type_id 
            FROM 
            subscription_types 
            ORDER BY
            subscription_type_id DESC
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $subscription_type_id = $qr->subscription_type_id + 1;
            }
            else {
                $subscription_type_id = 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            subscription_types (
            subscription_type_id, 
            subscription_type_name,
            validity,
            price,
            group_classes_included,
            rep_classes_included,
            classes_included,
            site_id
            ) VALUES (
            :subscription_type_id, 
            :subscription_type_name,
            :validity,
            :price,
            :group_classes_included,
            :rep_classes_included,
            :classes_included,
            :site_id
            )
            ");
            $stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
            $stm->bindParam(':subscription_type_name', $this->subscription_type_name,PDO::PARAM_STR);
            $stm->bindParam(':validity', $this->validity,PDO::PARAM_INT);
            $stm->bindParam(':price', $this->price,PDO::PARAM_INT);
            $stm->bindParam(':group_classes_included', $this->group_classes_included,PDO::PARAM_INT);
            $stm->bindParam(':rep_classes_included', $this->rep_classes_included,PDO::PARAM_INT);
            $stm->bindParam(':classes_included', $this->classes_included,PDO::PARAM_INT);
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

        if($this->subscription_type_id) {
            $this->save_subscription_type();
        }
        else {
            $this->create_subscription_type();
        }

        print json_encode(array(
            "status"=>"done",
            "subscription_type_id"=>$this->subscription_type_id,
            "subscription_type_name"=>$this->subscription_type_name,
            "validity"=>$this->validity,
            "group_classes_included"=>$this->group_classes_included,
            "rep_classes_included"=>$this->rep_classes_included,
            "classes_included"=>$this->classes_included,
            "price"=>$this->price
        ));
    }
}
new save_subscription_type_bg($this);
