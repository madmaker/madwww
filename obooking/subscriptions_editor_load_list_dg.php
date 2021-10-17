<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class subscriptions_editor_load_list_dg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_subscription_types() {
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            subscription_type_id,
            subscription_type_name,
            validity,
            price,
            group_classes_included,
            rep_classes_included,
            classes_included
            FROM 
            subscription_types 
            WHERE 
            site_id=:site_id
            ");
            $site_id=site_id;
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
        ?>

        <table class="table table-striped">
            <tr>
                <th>Название</th>
                <th>Срок действия</th>
                <th>Цена</th>
                <th>Групповые вкл</th>
                <th>Репетиции вкл</th>
                <th>Количество занятий</th>
                <th></th>
            </tr>
            <?if($stm=$this->get_subscription_types()) {

                while($qr=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td id="subscription_editor_load_list_dg_subscription_type_name_<?=$qr->subscription_type_id?>"><?=$qr->subscription_type_name?></td>
                        <td id="subscription_editor_load_list_dg_validity_<?=$qr->subscription_type_id?>">
                            <?=$qr->validity?> дней <?php
                            if($qr->validity>=365) {
                                $years_num=(int)($qr->validity/365);
                                print "(";
                                print $years_num;
                                print " год/лет)";
                            }
                            elseif($qr->validity>=30) {
                                $month_num=(int)($qr->validity/30);
                                print "(";
                                print $month_num;
                                print " мес.)";
                            }
                            elseif($qr->validity>=7) {
                                $weeks_num=(int)($qr->validity/7);
                                print "(";
                                print $weeks_num;
                                print " нед.)";
                            }
                            ?></td>
                        <td id="subscription_editor_load_list_dg_price_<?=$qr->subscription_type_id?>"><?=$qr->price?></td>
                        <td id="subscription_editor_load_list_dg_group_classes_included_<?=$qr->subscription_type_id?>"><?=(int)$qr->group_classes_included?"+":"-"?></td>
                        <td id="subscription_editor_load_list_dg_rep_classes_included_<?=$qr->subscription_type_id?>"><?=(int)$qr->rep_classes_included?"+":"-"?></td>
                        <td id="subscription_editor_load_list_dg_classes_included_<?=$qr->subscription_type_id?>"><?=(int)$qr->classes_included?></td>
                        <td><a href="javascript:void(0)" onclick="obooking_inline_create.edit_selected_subscription_type(<?=$qr->subscription_type_id?>)">Изменить</a></td>
                    </tr>
                <?}
            }?>
        </table>

        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary pull-right" onclick="obooking_inline_create.edit_selected_subscription_type(0,1)">Создать новый абонемент</button>
            </div>
        </div>

    <?}
}
new subscriptions_editor_load_list_dg($this);
