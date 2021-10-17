<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class load_client_statuses_list_editor_bg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_client_statusesList() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            client_status_id,
            client_status_name
            FROM 
            client_statuses 
            WHERE 
            site_id=:site_id
            ORDER BY 
            client_status_name
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    private function client_statusIsUsed($client_status_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            client_status 
            FROM 
            clients 
            WHERE
            client_status=:client_status_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':client_status_id', $client_status_id,PDO::PARAM_INT);
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
        <?$client_statusesRes=$this->get_client_statusesList();
        while($client_status=$client_statusesRes->fetch(PDO::FETCH_OBJ)) {
            $client_statusIsUsed=$this->client_statusIsUsed($client_status->client_status_id)?>
            <tr>
                <td onclick="obooking_inline_edit.edit_client_status_init(<?=$client_status->client_status_id?>)">#<?=$client_status->client_status_id?></td>
                <td onclick="obooking_inline_edit.edit_client_status_init(<?=$client_status->client_status_id?>)" id="obooking_client_statuses_list_client_status_name_<?=$client_status->client_status_id?>"><?=$client_status->client_status_name?></td>
                <td><em class="<?=$client_statusIsUsed?'text-muted':'text-danger'?>" onclick="<?=$client_statusIsUsed?'':'obooking_inline_edit.delete_client_status_init('.$client_status->client_status_id.')'?>"><em class="icon-cancel" title=" <?=$client_statusIsUsed?'Статус используется в на сайте. Удалять нельзя':'Удалить статус'?>"></em></em></td>
            </tr>
        <?}?>
</table>

    <?}
}
new load_client_statuses_list_editor_bg($this);
