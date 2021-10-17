<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";
require_once "processors/uSes.php";

class save_office_bg{
    private $obooking;
    private $office_id;
    private $office_name;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["office_id"],
            $_POST["office_name"]
        )) {
            $this->uFunc->error(10, 1);
        }

        $this->office_name=trim($_POST["office_name"]);
        if($this->office_name==="") {
            echo json_encode(array(
                "status"=>"error",
                "msg"=>"office name is empty"
            ));
            exit;
        }

        $this->office_id=(int)$_POST["office_id"];

        if(!$this->obooking->get_office_info("office_id",$this->office_id)) {
            $this->uFunc->error(20, 1);
        }
    }
    private function save_office() {
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            offices
            SET
            office_name=:office_name
            WHERE
            office_id=:office_id AND
            site_id=:site_id
            ");
            $site_id=site_id;

            $stm->bindParam(':office_name', $this->office_name,PDO::PARAM_STR);

            $stm->bindParam(':office_id', $this->office_id,PDO::PARAM_INT);
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
        $this->save_office();

        echo json_encode(array(
            'status'=>'done'
        ));
    }
}
new save_office_bg($this);
