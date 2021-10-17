<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_managers_list_editor_bg {
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
    private function getManagersList() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            managers.manager_id,
            manager_name,
            manager_lastname,
            order_id
            FROM 
            managers
            LEFT JOIN
            order_managers oc on managers.manager_id = oc.manager_id AND
            order_id=:order_id
            WHERE 
            site_id=:site_id
            ORDER BY 
            manager_name
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
            <input type="text" id="obooking_order_managers_list_manager_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.edit_managers_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_inline_edit.edit_managers_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_order_managers_list">
        <?$managersRes=$this->getManagersList();
        while($manager=$managersRes->fetch(PDO::FETCH_OBJ)) {
            $is_added=!is_null($manager->order_id);?>
            <tr
                    id="obooking_order_managers_list_manager_row_<?=$manager->manager_id?>"
                    class="<?=$is_added?'bg-success':''?>"
                    data-manager_id="<?=$manager->manager_id?>"
                    data-is_added="<?=$is_added?1:0?>"
            >
                <td style="cursor: pointer" onclick="obooking_inline_edit.toggle_manager2order(this)">#<?=$manager->manager_id?></td>
                <td style="cursor: pointer" onclick="obooking_inline_edit.toggle_manager2order(this)" id="obooking_managers_list_manager_name_<?=$manager->manager_id?>"><?=$manager->manager_name?> <?=$manager->manager_lastname?></td>
            </tr>
        <?}?>
        </table>

    <?}
}
new load_managers_list_editor_bg($this);
