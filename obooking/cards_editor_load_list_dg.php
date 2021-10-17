<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class cards_editor_load_list_dg {
    /**
     * @var uFunc
     */
    private $uFunc;

    private function get_card_types() {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            card_type_id,
            card_type_name,
            validity,
            price
            FROM 
            card_types 
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

        $user_id=(int)$uSes->get_val('user_id');
        $obooking=new common($uCore);
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
                <th></th>
            </tr>
            <?if($stm=$this->get_card_types()) {
                while($qr=$stm->fetch(PDO::FETCH_OBJ)) {?>
                    <tr>
                        <td id="card_editor_load_list_dg_card_type_name_<?=$qr->card_type_id?>"><?=$qr->card_type_name?></td>
                        <td id="card_editor_load_list_dg_validity_<?=$qr->card_type_id?>">
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
                        <td id="card_editor_load_list_dg_price_<?=$qr->card_type_id?>"><?=$qr->price?></td>
                        <td><a href="javascript:void(0)" onclick="obooking_inline_create.edit_selected_card_type(<?=$qr->card_type_id?>)">Изменить</a></td>
                    </tr>
                <?}
            }?>
        </table>

        <div class="row">
            <div class="col-md-12">
                <button class="btn btn-primary pull-right" onclick="obooking_inline_create.edit_selected_card_type(0,1)">Создать новую карту</button>
            </div>
        </div>

    <?}
}
new cards_editor_load_list_dg($this);
