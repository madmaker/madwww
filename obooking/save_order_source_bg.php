<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_order_source_bg {
    /**
     * @var int
     */
    private $source_id;
    private $source_name;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_new_source_id() {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            source_id 
            FROM 
            order_sources
            ORDER BY
            source_id DESC
            LIMIT 1
            ");

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->source_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function edit_order_source($site_id=site_id) {
        if(!isset($_POST["source_name"])) {
            $this->uFunc->error(10);
        }
        $this->source_name=$_POST["source_name"];

        if(!isset($_POST["source_id"])) {
            $this->uFunc->error(15);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            order_sources
            SET 
            source_name=:source_name 
            WHERE
            source_id=:source_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':source_id', $_POST["source_id"],PDO::PARAM_INT);
            $stm->bindParam(':source_name', $this->source_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_order_source($site_id=site_id) {
        if(!isset($_POST["source_name"])) {
            $this->uFunc->error(10);
        }
        $this->source_name=$_POST["source_name"];

        $source_id=$this->get_new_source_id();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            order_sources (
            source_id, 
            source_name, 
            site_id
            ) VALUES (
            :source_id, 
            :source_name, 
            :site_id          
            )
            ");
            $stm->bindParam(':source_id', $source_id,PDO::PARAM_INT);
            $stm->bindParam(':source_name', $this->source_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return $source_id;
    }
    private function delete_order_source() {
        if(!isset($_POST["source_id"])) {
            $this->uFunc->error(50);
        }

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            source_id
            FROM 
            orders 
            WHERE 
            source_id=:source_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':source_id', $_POST["source_id"],PDO::PARAM_INT);
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
            order_sources 
            WHERE 
            source_id=:source_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':source_id', $_POST["source_id"],PDO::PARAM_INT);
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

        if(isset($_POST["delete_order_source"])) {
            if(!$this->delete_order_source()) {
                $status="error";
                $msg='source_id is used';
            }
            else {
                $status="done";
                $msg='';
            }

            print json_encode(array(
                "status" => $status,
                "source_id" => $_POST["source_id"],
                "msg"=>$msg
            ));
        }
        elseif(isset($_POST["edit_order_source"])) {
            $this->edit_order_source();

            print json_encode(array(
                "status" => "done",
                "source_id" => $_POST["source_id"],
                "source_name" => $_POST["source_name"]
            ));
        }
        else {
            $source_id = $this->create_new_order_source();

            print json_encode(array(
                "status" => "done",
                "source_id" => $source_id,
                "source_name" => $this->source_name
            ));
        }
    }
}
new save_order_source_bg($this);
