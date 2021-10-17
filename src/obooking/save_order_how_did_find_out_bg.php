<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_order_how_did_find_out_bg {
    /**
     * @var int
     */
    private $how_did_find_out_id;
    private $how_did_find_out_name;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_new_how_did_find_out_id() {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            how_did_find_out_id 
            FROM 
            order_how_did_find_outs
            ORDER BY
            how_did_find_out_id DESC
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->how_did_find_out_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function edit_order_how_did_find_out($site_id=site_id) {
        if(!isset($_POST["how_did_find_out_name"])) {
            $this->uFunc->error(10);
        }
        $this->how_did_find_out_name=$_POST["how_did_find_out_name"];

        if(!isset($_POST["how_did_find_out_id"])) {
            $this->uFunc->error(15);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            order_how_did_find_outs
            SET 
            how_did_find_out_name=:how_did_find_out_name 
            WHERE
            how_did_find_out_id=:how_did_find_out_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':how_did_find_out_id', $_POST["how_did_find_out_id"],PDO::PARAM_INT);
            $stm->bindParam(':how_did_find_out_name', $this->how_did_find_out_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_order_how_did_find_out($site_id=site_id) {
        if(!isset($_POST["how_did_find_out_name"])) {
            $this->uFunc->error(10);
        }
        $this->how_did_find_out_name=$_POST["how_did_find_out_name"];

        $how_did_find_out_id=$this->get_new_how_did_find_out_id();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            order_how_did_find_outs (
            how_did_find_out_id, 
            how_did_find_out_name, 
            site_id
            ) VALUES (
            :how_did_find_out_id, 
            :how_did_find_out_name, 
            :site_id          
            )
            ");
            $stm->bindParam(':how_did_find_out_id', $how_did_find_out_id,PDO::PARAM_INT);
            $stm->bindParam(':how_did_find_out_name', $this->how_did_find_out_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'.$e->getMessage());}
        return $how_did_find_out_id;
    }
    private function delete_order_how_did_find_out() {
        if(!isset($_POST["how_did_find_out_id"])) {
            $this->uFunc->error(50);
        }

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            how_did_find_out_id
            FROM 
            orders 
            WHERE 
            how_did_find_out_id=:how_did_find_out_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':how_did_find_out_id', $_POST["how_did_find_out_id"],PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) {
                return 0;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE
            FROM 
            order_how_did_find_outs 
            WHERE 
            how_did_find_out_id=:how_did_find_out_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':how_did_find_out_id', $_POST["how_did_find_out_id"],PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
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

        if(isset($_POST["delete_order_how_did_find_out"])) {
            if(!$this->delete_order_how_did_find_out()) {
                $status="error";
                $msg='how_did_find_out_id is used';
            }
            else {
                $status="done";
                $msg='';
            }

            print json_encode(array(
                "status" => $status,
                "how_did_find_out_id" => $_POST["how_did_find_out_id"],
                "msg"=>$msg
            ));
        }
        elseif(isset($_POST["edit_order_how_did_find_out"])) {
            $this->edit_order_how_did_find_out();

            print json_encode(array(
                "status" => "done",
                "how_did_find_out_id" => $_POST["how_did_find_out_id"],
                "how_did_find_out_name" => $_POST["how_did_find_out_name"]
            ));
        }
        else {
            $how_did_find_out_id = $this->create_new_order_how_did_find_out();

            print json_encode(array(
                "status" => "done",
                "how_did_find_out_id" => $how_did_find_out_id,
                "how_did_find_out_name" => $this->how_did_find_out_name
            ));
        }
    }
}
new save_order_how_did_find_out_bg($this);
