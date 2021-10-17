<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class set_manager_business_hours_bg {
    private $manager_id;
    private $value;
    private $hour;
    private $day;
    private $class_id;
    private $uFunc;

    private function check_data() {
        if(!isset(
            $_POST["manager_id"],
            $_POST["class_id"],
            $_POST["day"],
            $_POST["hour"],
            $_POST["value"]
        )) {
            $this->uFunc->error(10);
        }

        $this->manager_id=(int)$_POST["manager_id"];
        $this->class_id=(int)$_POST["class_id"];

        $this->day=(int)$_POST["day"];
        if($this->day<0) {
            $this->day = 0;
        }
        elseif ($this->day>6) {
            $this->day = 6;
        }

        $this->hour=(int)$_POST["hour"];
        if($this->hour>23) {
            $this->hour = 23;
        }

        $this->value=(int)$_POST["value"]?1:0;
    }
    private function set_value($hour) {
        if($this->value) {
            try {

                $stm=$this->uFunc->pdo("obooking")->prepare("REPLACE INTO  
                manager_schedule (
                manager_id, 
                site_id, 
                class_id, 
                day_of_week, 
                hour
                ) VALUES (
                :manager_id, 
                :site_id, 
                :class_id, 
                :day_of_week, 
                :hour          
                )
                ");
                $site_id=site_id;
                $stm->bindParam(':manager_id', $this->manager_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':class_id', $this->class_id,PDO::PARAM_INT);
                $stm->bindParam(':day_of_week', $this->day,PDO::PARAM_INT);
                $stm->bindParam(':hour', $hour,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
        else {
            try {

                $stm=$this->uFunc->pdo("obooking")->prepare("DELETE FROM manager_schedule
                WHERE
                manager_id=:manager_id AND 
                class_id=:class_id AND 
                day_of_week=:day_of_week AND
                hour=:hour
                ");
                $stm->bindParam(':manager_id', $this->manager_id,PDO::PARAM_INT);
                $stm->bindParam(':class_id', $this->class_id,PDO::PARAM_INT);
                $stm->bindParam(':day_of_week', $this->day,PDO::PARAM_INT);
                $stm->bindParam(':hour', $hour,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        }
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
        if($this->hour<0) {
            for($hour=8;$hour<24;$hour++) {
                $this->set_value($hour);
            }
        }
        else {
            $this->set_value($this->hour);
        }
        echo "{'status':'done'}";
    }
}
new set_manager_business_hours_bg($this);
