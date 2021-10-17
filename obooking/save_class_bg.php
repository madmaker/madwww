<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class save_class_bg{
    private $obooking;
    private $office_id;
    private $class_name;
    private $class_id;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["class_id"],
            $_POST["class_name"],
            $_POST["office_id"]
        )) {
            $this->uFunc->error(10, 1);
        }

        $this->class_id=(int)$_POST["class_id"];

        $this->class_name=trim($_POST["class_name"]);
        if($this->class_name==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"class name is empty"
            ));
            exit;
        }

        $this->office_id=(int)$_POST["office_id"];

        if(!$this->obooking->get_office_info("office_id",$this->office_id)) {
            $this->uFunc->error(20, 1);
        }
    }
    private function save_class() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            classes
            SET
            class_name=:class_name,
            office_id=:office_id
            WHERE
            class_id=:class_id AND
            site_id=:site_id
            ");
            $site_id=site_id;

            $stm->bindParam(':class_name', $this->class_name,PDO::PARAM_STR);
            $stm->bindParam(':office_id', $this->office_id,PDO::PARAM_INT);

            $stm->bindParam(':class_id', $this->class_id,PDO::PARAM_INT);
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
        $this->save_class();

        echo json_encode(array(
            'status'=>'done'
        ));
    }
}
new save_class_bg($this);
