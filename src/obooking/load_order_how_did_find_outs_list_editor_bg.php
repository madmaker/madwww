<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_order_how_did_find_outs_list_editor_bg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_order_how_did_find_outs_list() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            how_did_find_out_id,
            how_did_find_out_name
            FROM 
            order_how_did_find_outs 
            WHERE 
            site_id=:site_id
            ORDER BY 
            how_did_find_out_name
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    private function order_how_did_find_outIsUsedInOrders($how_did_find_out_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            how_did_find_out_id 
            FROM 
            orders 
            WHERE
            how_did_find_out_id=:how_did_find_out_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':how_did_find_out_id', $how_did_find_out_id,PDO::PARAM_INT);
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
        <?$order_how_did_find_outsRes=$this->get_order_how_did_find_outs_list();
        while($order_how_did_find_out=$order_how_did_find_outsRes->fetch(PDO::FETCH_OBJ)) {
            $order_how_did_find_outIsUsedInOrders=$this->order_how_did_find_outIsUsedInOrders($order_how_did_find_out->how_did_find_out_id);?>
            <tr>
                <td onclick="obooking_inline_edit.edit_order_how_did_find_out_init(<?=$order_how_did_find_out->how_did_find_out_id?>)">#<?=$order_how_did_find_out->how_did_find_out_id?></td>
                <td onclick="obooking_inline_edit.edit_order_how_did_find_out_init(<?=$order_how_did_find_out->how_did_find_out_id?>)" id="obooking_order_how_did_find_outs_list_how_did_find_out_name_<?=$order_how_did_find_out->how_did_find_out_id?>"><?=$order_how_did_find_out->how_did_find_out_name?></td>
                <td><em class="<?=$order_how_did_find_outIsUsedInOrders?'text-muted':'text-danger'?>" onclick="<?=$order_how_did_find_outIsUsedInOrders?'':'obooking_inline_edit.delete_order_how_did_find_out_init('.$order_how_did_find_out->how_did_find_out_id.')'?>"><em class="icon-cancel" title=" <?=$order_how_did_find_outIsUsedInOrders?'Источник откуда узнали используется в на сайте. Удалять нельзя':'Удалить источник откуда узнали'?>"></em></em></td>
            </tr>
        <?}?>
</table>

    <?}
}
new load_order_how_did_find_outs_list_editor_bg($this);
