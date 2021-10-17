<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_order_sources_list_editor_bg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_order_sources_list() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            source_id,
            source_name
            FROM 
            order_sources 
            WHERE 
            site_id=:site_id
            ORDER BY 
            source_name
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    private function order_sourceIsUsedInOrders($source_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            source_id 
            FROM 
            orders 
            WHERE
            source_id=:source_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':source_id', $source_id,PDO::PARAM_INT);
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
        <?$order_sourcesRes=$this->get_order_sources_list();
        while($order_source=$order_sourcesRes->fetch(PDO::FETCH_OBJ)) {
            $order_sourceIsUsedInOrders=$this->order_sourceIsUsedInOrders($order_source->source_id);?>
            <tr>
                <td onclick="obooking_inline_edit.edit_order_source_init(<?=$order_source->source_id?>)">#<?=$order_source->source_id?></td>
                <td onclick="obooking_inline_edit.edit_order_source_init(<?=$order_source->source_id?>)" id="obooking_order_sources_list_source_name_<?=$order_source->source_id?>"><?=$order_source->source_name?></td>
                <td><em class="<?=$order_sourceIsUsedInOrders?'text-muted':'text-danger'?>" onclick="<?=$order_sourceIsUsedInOrders?'':'obooking_inline_edit.delete_order_source_init('.$order_source->source_id.')'?>"><em class="icon-cancel" title=" <?=$order_sourceIsUsedInOrders?'Источник заявки используется в на сайте. Удалять нельзя':'Удалить источник заявки'?>"></em></em></td>
            </tr>
        <?}?>
</table>

    <?}
}
new load_order_sources_list_editor_bg($this);
