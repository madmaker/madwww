<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class subscription_editor_load_dg {
    /**
     * @var int
     */
    private $create;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $subscription_type_id;

    private function check_data() {
        if(!isset(
                $_POST["subscription_type_id"],
                $_POST["create"]
        )) {
            $this->uFunc->error(10);
        }

        $this->subscription_type_id=(int)$_POST["subscription_type_id"];
        $this->create=(int)$_POST["create"];
        $this->create=(int)(bool)$this->create;
    }
    private function get_subscription_type_info($subscription_type_id) {
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            subscription_type_name,
            validity,
            price,
            group_classes_included,
            rep_classes_included,
            classes_included
            FROM 
            subscription_types 
            WHERE 
            subscription_type_id=:subscription_type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $stm->bindParam(':subscription_type_id', $subscription_type_id,PDO::PARAM_INT);
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
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();

        if(!$this->create && !$subscription_type_info = $this->get_subscription_type_info($this->subscription_type_id)) {
            $this->uFunc->error(30);
        }
        ?>

        <div>
            <input type="hidden" id="subscription_editor_load_dg_subscription_type_id" value="<?=$this->subscription_type_id?>">
            <div class="form-group">
                <label for="subscription_editor_load_dg_subscription_type_name">Название абонемента</label>
                <input id="subscription_editor_load_dg_subscription_type_name" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */
                $this->create?"":addslashes($subscription_type_info->subscription_type_name)?>">
            </div>
            <div class="form-group">
                <label for="subscription_editor_load_dg_validity">Срок действия, дней</label>
                <input id="subscription_editor_load_dg_validity" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */
                $this->create?"":$subscription_type_info->validity?>">
            </div>
            <div class="form-group">
                <label for="subscription_editor_load_dg_price">Цена</label>
                <input id="subscription_editor_load_dg_price" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */
                $this->create?"":$subscription_type_info->price?>">
            </div>
            <div class="form-group">
                <label for="subscription_editor_load_dg_group_classes_included">
                    <input type="checkbox" id="subscription_editor_load_dg_group_classes_included" <?php
                    /** @noinspection PhpUndefinedVariableInspection */
                    if(!$this->create && $subscription_type_info->group_classes_included) {
                        print " checked ";
                    } ?>> Групповые занятия включены
                </label>
            </div>
            <div class="form-group">
                <label for="subscription_editor_load_dg_rep_classes_included">
                    <input type="checkbox" id="subscription_editor_load_dg_rep_classes_included" <?php
                    /** @noinspection PhpUndefinedVariableInspection */
                    if(!$this->create && $subscription_type_info->rep_classes_included) {
                        print " checked ";
                    } ?>> Репетиции включены
                </label>
            </div>
            <div class="form-group">
                <label for="subscription_editor_load_dg_classes_included">Сколько занятий включено</label>
                <input id="subscription_editor_load_dg_classes_included" type="text" class="form-control" value="<?= /** @noinspection PhpUndefinedVariableInspection */
                $this->create?"":$subscription_type_info->classes_included?>">
            </div>
        </div>
    <?}
}
new subscription_editor_load_dg($this);
