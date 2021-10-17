<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class load_manager_schedule_editor_bg {
    private $obooking;
    private $manager_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["manager_id"])) {
            $this->uFunc->error(10);
        }
        $this->manager_id=(int)$_POST["manager_id"];
    }
    private function get_hour_value($class_id,$day,$hour) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            manager_id
            FROM 
            manager_schedule
            WHERE 
            manager_id=:manager_id AND
            class_id=:class_id AND
            day_of_week=:day_of_week AND
            hour=:hour
            ");
//            $site_id=site_id;
            $stm->bindParam(':manager_id', $this->manager_id,PDO::PARAM_INT);
            $stm->bindParam(':class_id', $class_id,PDO::PARAM_INT);
            $stm->bindParam(':day_of_week', $day,PDO::PARAM_INT);
            $stm->bindParam(':hour', $hour,PDO::PARAM_INT);
            $stm->execute();

            if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                return 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('20'.$e->getMessage());}
        return 0;
    }
    private function print_editor($manager_id) {?>
        <input type="hidden" id="obooking_inline_edit_manager_schedule_manager_id" value="<?=$manager_id?>">

        <?$days_array=["Пн","Вт","Ср","Чт","Пт","Сб","Вс"];?>

        <?$offices_stm=$this->obooking->get_offices();

        while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {?>
            <div class="highlight">
            <h3><?=$office->office_name?></h3>
            <?$classes_of_office_stm=$this->obooking->get_classes($office->office_id);
            while($class=$classes_of_office_stm->fetch(PDO::FETCH_OBJ)) {?>
                <h4><?=$class->class_name?></h4>
                <table class="table table-condensed table-bordered sch_class_<?=$class->class_id?> ">
                    <?php
                    foreach ($days_array as $day => $dayValue) {?>
                        <tr class="sch_day_<?=$day?>">
                            <td onclick="obooking_inline_edit.manager_schedule_select_time(<?=$class->class_id?>,<?=$day?>,-1)"><?= $dayValue ?></td>
                            <?for($hour=8;$hour<24;$hour++) {
                                $hour_str=$hour<10?('0'.$hour):$hour;?>
                                <td class="<?=$this->get_hour_value($class->class_id,$day,$hour)?"bg-primary":""?> sch_hour_<?=$hour?>" onclick="obooking_inline_edit.manager_schedule_select_time(<?=$class->class_id?>,<?=$day?>,<?=$hour?>)"><?=$hour_str?>:00</td>
                            <?}?>
                        </tr>
                    <?} ?>
                </table>
            <?}?>
            </div>
        <?}
    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $this->print_editor($this->manager_id);
    }
}
new load_manager_schedule_editor_bg($this);
