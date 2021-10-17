<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class get_client_records_history {
    /**
     * @var int
     */
    private $client_id;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["client_id"])) {
            $this->uFunc->error(10);
        }
        $this->client_id=(int)$_POST["client_id"];
    }

    private function get_client_records_history($site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            timestamp,
            description
            FROM 
            clients_records_history
            WHERE
            client_id=:client_id AND
            site_id=:site_id
            ORDER BY id DESC
            ");
            $stm->bindParam(':client_id', $this->client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
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

        $this->check_data();

        $client_balance_history_stm=$this->get_client_records_history();
        ?>

        <div class="container-fluid">
            <table class="table table-striped table-condensed">

                <?php
                while($event=$client_balance_history_stm->fetch(PDO::FETCH_OBJ)) {?>
                <tr>
                    <td><?=date("d.m.Y H:i",$event->timestamp)?></td>
                    <td><?=$event->description?></td>
                </tr>
            <?}?>
            </table>
        </div>
    <?}
}
/*$obooking=*/new get_client_records_history($this);
