<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_courses_list_editor_bg {
    /**
     * @var int
     */
    private $order_id;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function checkData() {
        if(!isset($_POST["order_id"])) {
            echo json_encode(array(
               'status'=>'error',
               'msg'=>'have not got data required'
            ));
            exit;
        }
        $this->order_id=(int)$_POST['order_id'];
    }
    private function getCoursesList() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            courses.course_id,
            course_name,
            order_id
            FROM 
            courses
            LEFT JOIN
            order_courses oc on courses.course_id = oc.course_id AND
            order_id=:order_id
            WHERE 
            site_id=:site_id
            ORDER BY 
            course_name
            ");
            $site_id=site_id;
            $stm->bindParam(':order_id', $this->order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/,1);}
        return 0;
    }
    private function courseIsUsedInOrders($course_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            course_id
            FROM 
            order_courses
            LEFT JOIN orders o on order_courses.order_id = o.order_id
            WHERE
            course_id=:course_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':course_id', $course_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ)?1:0;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/,1);}
        return 0;
    }


    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->checkData();
        ?>

        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_order_courses_list_course_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.edit_courses_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_inline_edit.edit_courses_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_order_courses_list">
        <?$coursesRes=$this->getCoursesList();
        while($course=$coursesRes->fetch(PDO::FETCH_OBJ)) {
            $is_added=!is_null($course->order_id);
            $courseIsUsedInOrders=$this->courseIsUsedInOrders($course->course_id);?>
            <tr
                    id="obooking_order_courses_list_course_row_<?=$course->course_id?>"
                    class="<?=$is_added?'bg-success':''?>"
                    data-course_id="<?=$course->course_id?>"
                    data-is_added="<?=$is_added?1:0?>"
            >
                <td style="cursor: pointer" onclick="obooking_inline_edit.toggle_course2order(this)">#<?=$course->course_id?></td>
                <td><button onclick="obooking_inline_edit.edit_course_init(<?=$course->course_id?>)" class="btn-link btn-sm" title="Редактировать направление"><span class="icon-pencil"></span></button></td>
                <td style="cursor: pointer" onclick="obooking_inline_edit.toggle_course2order(this)" id="obooking_courses_list_course_name_<?=$course->course_id?>"><?=$course->course_name?></td>
                <td><em class="<?=$courseIsUsedInOrders?'text-muted':'text-danger'?>" onclick="<?=$courseIsUsedInOrders?'':'obooking_inline_edit.delete_course_init('.$course->course_id.')'?>"><em class="icon-cancel" <?=$courseIsUsedInOrders?'style="opacity:0.3"':''?> title=" <?=$courseIsUsedInOrders?'Направление используется в на сайте. Удалять нельзя. Удалять нельзя':'Удалить направление'?>"></em></em></td>
            </tr>
        <?}?>
        </table>

    <?}
}
new load_courses_list_editor_bg($this);
