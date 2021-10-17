<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_client_status_bg {
    /**
     * @var int
     */
    private $client_status_id;
    private $client_status_name;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_new_client_status_id() {
        try {
            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            client_status_id 
            FROM 
            client_statuses
            ORDER BY
            client_status_id DESC
            LIMIT 1
            ");


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->client_status_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function edit_client_status($site_id=site_id) {
        if(!isset($_POST["client_status_name"])) {
            $this->uFunc->error(10);
        }
        $this->client_status_name=$_POST["client_status_name"];

        if(!isset($_POST["client_status_id"])) {
            $this->uFunc->error(15);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            client_statuses
            SET 
            client_status_name=:client_status_name 
            WHERE
            client_status_id=:client_status_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':client_status_id', $_POST["client_status_id"],PDO::PARAM_INT);
            $stm->bindParam(':client_status_name', $this->client_status_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_client_status($site_id=site_id) {
        if(!isset($_POST["client_status_name"])) {
            $this->uFunc->error(10);
        }
        $this->client_status_name=$_POST["client_status_name"];

        $client_status_id=$this->get_new_client_status_id();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            client_statuses (
            client_status_id, 
            client_status_name, 
            site_id
            ) VALUES (
            :client_status_id, 
            :client_status_name, 
            :site_id          
            )
            ");
            $stm->bindParam(':client_status_id', $client_status_id,PDO::PARAM_INT);
            $stm->bindParam(':client_status_name', $this->client_status_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return $client_status_id;
    }
    private function delete_client_status() {
        if(!isset($_POST["client_status_id"])) {
            $this->uFunc->error(50);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            client_status 
            FROM 
            clients 
            WHERE 
            client_status=:client_status_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':client_status_id', $_POST["client_status_id"],PDO::PARAM_INT);
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
            client_statuses 
            WHERE 
            client_status_id=:client_status_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':client_status_id', $_POST["client_status_id"],PDO::PARAM_INT);
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

        if(isset($_POST["delete_client_status"])) {
            if(!$this->delete_client_status()) {
                $status="error";
                $msg='client_status_id is used';
            }
            else {
                $status="done";
                $msg='';
            }

            print json_encode(array(
                "status" => $status,
                "client_status_id" => $_POST["client_status_id"],
                "msg"=>$msg
            ));
        }
        elseif(isset($_POST["edit_client_status"])) {
            $this->edit_client_status();

            print json_encode(array(
                "status" => "done",
                "client_status_id" => $_POST["client_status_id"],
                "client_status_name" => $_POST["client_status_name"]
            ));
        }
        else {
            $client_status_id = $this->create_new_client_status();

            print json_encode(array(
                "status" => "done",
                "client_status_id" => $client_status_id,
                "client_status_name" => $this->client_status_name
            ));
        }
    }
}
new save_client_status_bg($this);
