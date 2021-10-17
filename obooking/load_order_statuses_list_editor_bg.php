<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_order_statuses_list_editor_bg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_order_statuses_list() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            status_id,
            status_name
            FROM 
            order_statuses 
            WHERE 
            site_id=:site_id
            ORDER BY 
            status_name
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    private function order_statusIsUsedInOrders($status_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            status_id 
            FROM 
            orders 
            WHERE
            status_id=:status_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':status_id', $status_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm->fetch(PDO::FETCH_OBJ)?1:0;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
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

        $this->uFunc=new uFunc($uCore);?>

<table class="table table-condensed table-hover">
        <?$order_statusesRes=$this->get_order_statuses_list();
        while($order_status=$order_statusesRes->fetch(PDO::FETCH_OBJ)) {
            $order_statusIsUsedInOrders=$this->order_statusIsUsedInOrders($order_status->status_id);?>
            <tr>
                <td onclick="obooking_inline_edit.edit_order_status_init(<?=$order_status->status_id?>)">#<?=$order_status->status_id?></td>
                <td onclick="obooking_inline_edit.edit_order_status_init(<?=$order_status->status_id?>)" id="obooking_order_statuses_list_status_name_<?=$order_status->status_id?>"><?=$order_status->status_name?></td>
                <td><em class="<?=$order_statusIsUsedInOrders?'text-muted':'text-danger'?>" onclick="<?=$order_statusIsUsedInOrders?'':'obooking_inline_edit.delete_order_status_init('.$order_status->status_id.')'?>"><em class="icon-cancel" title=" <?=$order_statusIsUsedInOrders?'Статус заявки используется в на сайте. Удалять нельзя':'Удалить статус заявки'?>"></em></em></td>
            </tr>
        <?}?>
</table>

    <?}
}
new load_order_statuses_list_editor_bg($this);
