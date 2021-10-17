<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class save_course_bg {
    /**
     * @var int
     */
    private $course_id;
    private $course_name;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_new_course_id() {
        try {

            $stm=$this->uFunc->pdo("obooking")->query("SELECT 
            course_id 
            FROM 
            courses
            ORDER BY
            course_id DESC
            LIMIT 1
            ");


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->course_id + 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function edit_course($site_id=site_id) {
        if(!isset($_POST["course_name"])) {
            $this->uFunc->error(10);
        }
        $this->course_name=$_POST["course_name"];

        if(!isset($_POST["course_id"])) {
            $this->uFunc->error(15);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("UPDATE
            courses
            SET 
            course_name=:course_name 
            WHERE
            course_id=:course_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':course_id', $_POST["course_id"],PDO::PARAM_INT);
            $stm->bindParam(':course_name', $this->course_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_course($site_id=site_id) {
        if(!isset($_POST["course_name"])) {
            $this->uFunc->error(10);
        }
        $this->course_name=$_POST["course_name"];

        $course_id=$this->get_new_course_id();
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("INSERT INTO
            courses (
            course_id, 
            course_name, 
            site_id
            ) VALUES (
            :course_id, 
            :course_name, 
            :site_id          
            )
            ");
            $stm->bindParam(':course_id', $course_id,PDO::PARAM_INT);
            $stm->bindParam(':course_name', $this->course_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return $course_id;
    }
    private function delete_course() {
        if(!isset($_POST["course_id"])) {
            $this->uFunc->error(50);
        }

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            course_id
            FROM 
            order_courses
            JOIN orders ON order_courses.order_id = orders.order_id=orders.order_id
            WHERE 
            course_id=:course_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':course_id', $_POST["course_id"],PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) {
                return 0;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('1582004313'/*.$e->getMessage()*/,1);}

        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("DELETE
            FROM 
            courses 
            WHERE 
            course_id=:course_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':course_id', $_POST["course_id"],PDO::PARAM_INT);
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

        if(isset($_POST["delete_course"])) {
            if(!$this->delete_course()) {
                $status="error";
                $msg='course_id is used';
            }
            else {
                $status="done";
                $msg='';
            }

            print json_encode(array(
                "status" => $status,
                "course_id" => $_POST["course_id"],
                "msg"=>$msg
            ));
        }
        elseif(isset($_POST["edit_course"])) {
            $this->edit_course();

            print json_encode(array(
                "status" => "done",
                "course_id" => $_POST["course_id"],
                "course_name" => $_POST["course_name"]
            ));
        }
        else {
            $course_id = $this->create_new_course();

            print json_encode(array(
                "status" => "done",
                "course_id" => $course_id,
                "course_name" => $this->course_name
            ));
        }
    }
}
new save_course_bg($this);
