<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class card_editor_load_dg {
    /**
     * @var int
     */
    private $create;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $card_type_id;

    private function check_data() {
        if(!isset(
                $_POST["card_type_id"],
                $_POST["create"]
        )) {
            $this->uFunc->error(10);
        }

        $this->card_type_id=(int)$_POST["card_type_id"];
        $this->create=(int)$_POST["create"];
        $this->create=(int)(bool)$this->create;
    }
    private function get_card_type_info($card_type_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            card_type_name,
            validity,
            price
            FROM 
            card_types 
            WHERE 
            card_type_id=:card_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':card_type_id', $card_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ);
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

        $user_id=(int)$uSes->get_val('user_id');
        $obooking=new common($uCore);
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();

        if(!$this->create && !$card_type_info = $this->get_card_type_info($this->card_type_id)) {
            $this->uFunc->error(30);
        }
        ?>

        <div>
            <input type="hidden" id="card_editor_load_dg_card_type_id" value="<?=$this->card_type_id?>">
            <div class="form-group">
                <label for="card_editor_load_dg_card_type_name">Название карты</label>
                <input id="card_editor_load_dg_card_type_name" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */$this->create?"":addslashes($card_type_info->card_type_name)?>">
            </div>
            <div class="form-group">
                <label for="card_editor_load_dg_validity">Срок действия карты, дней</label>
                <input id="card_editor_load_dg_validity" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */$this->create?"":$card_type_info->validity?>">
            </div>
            <div class="form-group">
                <label for="card_editor_load_dg_price">Цена карты</label>
                <input id="card_editor_load_dg_price" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */$this->create?"":$card_type_info->price?>">
            </div>
        </div>
    <?}
}
new card_editor_load_dg($this);
