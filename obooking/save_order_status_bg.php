<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_order_status_bg {
    /**
     * @var int
     */
    private $status_id;
    private $status_name;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_new_status_id() {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            status_id 
            FROM 
            order_statuses
            ORDER BY
            status_id DESC
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->status_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function edit_order_status($site_id=site_id) {
        if(!isset($_POST["status_name"])) {
            $this->uFunc->error(10);
        }
        $this->status_name=$_POST["status_name"];

        if(!isset($_POST["status_id"])) {
            $this->uFunc->error(15);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            order_statuses
            SET 
            status_name=:status_name 
            WHERE
            status_id=:status_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':status_id', $_POST["status_id"],PDO::PARAM_INT);
            $stm->bindParam(':status_name', $this->status_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_order_status($site_id=site_id) {
        if(!isset($_POST["status_name"])) {
            $this->uFunc->error(10);
        }
        $this->status_name=$_POST["status_name"];

        $status_id=$this->get_new_status_id();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            order_statuses (
            status_id, 
            status_name, 
            site_id
            ) VALUES (
            :status_id, 
            :status_name, 
            :site_id          
            )
            ");
            $stm->bindParam(':status_id', $status_id,PDO::PARAM_INT);
            $stm->bindParam(':status_name', $this->status_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return $status_id;
    }
    private function delete_order_status() {
        if(!isset($_POST["status_id"])) {
            $this->uFunc->error(50);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            status_id
            FROM 
            orders 
            WHERE 
            status_id=:status_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':status_id', $_POST["status_id"],PDO::PARAM_INT);
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
            order_statuses 
            WHERE 
            status_id=:status_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':status_id', $_POST["status_id"],PDO::PARAM_INT);
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

        if(isset($_POST["delete_order_status"])) {
            if(!$this->delete_order_status()) {
                $status="error";
                $msg='status_id is used';
            }
            else {
                $status="done";
                $msg='';
            }

            print json_encode(array(
                "status" => $status,
                "status_id" => $_POST["status_id"],
                "msg"=>$msg
            ));
        }
        elseif(isset($_POST["edit_order_status"])) {
            $this->edit_order_status();

            print json_encode(array(
                "status" => "done",
                "status_id" => $_POST["status_id"],
                "status_name" => $_POST["status_name"]
            ));
        }
        else {
            $status_id = $this->create_new_order_status();

            print json_encode(array(
                "status" => "done",
                "status_id" => $status_id,
                "status_name" => $this->status_name
            ));
        }
    }
}
new save_order_status_bg($this);
