<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

/**
 * Load's client editor dialog
 *
 * ## Request (POST):
 * - client_id
 *
 * ## Response (text):
 * - html with client's editor dialog
 * - forbidden
 * - client is not found - if client_id provided in request is not found on website
 * @package obooking
 */
class load_client_editor {
    /**
     * @var common
     */
    private $obooking;
    private $client_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["client_id"])) {
            $this->uFunc->error(10);
        }
        $this->client_id=(int)$_POST["client_id"];
    }
    private function get_client_cards($client_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            * 
            FROM 
            clients_cards 
            WHERE 
            client_id=:client_id AND
            site_id=:site_id
            ORDER BY 
            valid_thru DESC
            ");
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 0;
    }
    private function get_client_subscriptions($client_id,$site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT 
            * 
            FROM 
            clients_subscriptions
            WHERE 
            client_id=:client_id AND
            site_id=:site_id
            ORDER BY 
            valid_thru DESC
            ");
            $stm->bindParam(':client_id', $client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        return 0;
    }
    public function get_client_statuses() {
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
    private function print_editor($client_data) {?>
        <input type="hidden" id="obooking_inline_edit_client_client_id" value="<?=$client_data->client_id?>">


        <div class="row">
            <div class="col-md-3">
                <h4><a class="btn disabled" href="javascript:void(0)">Открыть график занятий</a></h4>
            </div>
            <div class="col-md-3">
                <h4 class="btn"><a href="javascript:void(0)" onclick="obooking_inline_edit.get_client_records_history(<?=$client_data->client_id?>)">Прошлая активность: <?=(int)$client_data->client_last_activity_timestamp?date("d.m.Y",$client_data->client_last_activity_timestamp):'Не было'?></a></h4>
            </div>
            <div class="col-md-3">
                <h4 class="btn"><a href="javascript:void(0)" onclick="obooking_inline_edit.get_client_balance_history(<?=$client_data->client_id?>)">Баланс:</a> <span id="obooking_inline_edit_client_client_balance_field"><?=$client_data->client_balance?></span> <span>р.</span></h4>
            </div>
            <div class="col-md-3">
                <h4 class="btn disabled"><a href="javascript:void(0)">Бонусный баланс:</a> <?=$client_data->client_bonus_balance?> р.</h4>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_client_client_name_form_group">
                    <label for="obooking_inline_edit_client_client_name">Имя</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_name" type="text" value="<?=htmlspecialchars($client_data->client_name)?>">
                    <span class="help-block" id="obooking_inline_edit_client_client_name_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_client_client_lastname_form_group">
                    <label for="obooking_inline_edit_client_client_lastname">Фамилия</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_lastname" type="text" value="<?=htmlspecialchars($client_data->client_lastname)?>">
                    <span class="help-block" id="obooking_inline_edit_client_client_lastname_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_client_client_phone_form_group">
                    <label for="obooking_inline_edit_client_client_phone">Телефон</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_phone" type="tel" value="<?=htmlspecialchars($client_data->client_phone)?>">
                    <span class="help-block" id="obooking_inline_edit_client_client_phone_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_client_client_phone2_form_group">
                    <label for="obooking_inline_edit_client_client_phone2">Дополнительный телефон</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_phone2" type="tel" value="<?=htmlspecialchars($client_data->client_phone2)?>">
                    <span class="help-block" id="obooking_inline_edit_client_client_phone2_help_block"></span>
                </div>
                <div class="form-group" id="obooking_inline_edit_client_client_email_form_group">
                    <label for="obooking_inline_edit_client_client_email">E-mail</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_email" type="email" value="<?=htmlspecialchars($client_data->client_email)?>">
                    <span class="help-block" id="obooking_inline_edit_client_client_email_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_client_client_status_form_group">
                    <label for="obooking_inline_edit_client_client_status"><a href="javascript:void(0);" onclick="obooking_inline_edit.edit_client_status_list_init(1)">Статус <span class="icon-pencil"></span></a></label>
                    <select class="form-control" id="obooking_inline_edit_client_client_status">
                        <?$client_statuses_stm=$this->get_client_statuses();

                        $client_data->client_status=(int)$client_data->client_status;

                        while($qr=$client_statuses_stm->fetch(PDO::FETCH_OBJ)) {
                            $status=(int)$qr->client_status_id;?>
                            <option id="obooking_client_editor_status_option_<?=$qr->client_status_id?>" value="<?=$qr->client_status_id?>" <?=$client_data->client_status===$status?'selected':''?>><?=$qr->client_status_name?></option>
                        <?}?>
                    </select>
                    <span class="help-block" id="obooking_inline_edit_client_client_status_help_block"></span>
                </div>

                <div class="form-group" id="obooking_inline_edit_client_client_birthdate_form_group">
                    <label for="obooking_inline_edit_client_client_birthdate">День рождения</label>
                    <input class="form-control" id="obooking_inline_edit_client_client_birthdate" type="hidden" value="<?=(int)$client_data->client_birthdate?(date("d.m.Y",$client_data->client_birthdate)):0?>">
                    <div id="obooking_inline_edit_client_client_birthdate_datepicker" <?=(int)$client_data->client_birthdate?('data-date="'.date("d.m.Y",$client_data->client_birthdate).'"'):''?>></div>
                    <span class="help-block" id="obooking_inline_edit_client_client_birthdate_help_block"></span>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group" id="obooking_inline_edit_client_client_card_type_form_group">
                    <label for="obooking_inline_edit_client_client_card_type">Клубные карты</label>
                    <button class="btn btn-sm btn-primary" onclick="obooking_inline_create.add_new_card_init(<?=$client_data->client_id?>)">Добавить карту</button>
                    <p></p>

                    <table class="table table-condensed table-striped" id="obooking_inline_edit_client_client_card_type_form_group_table">
                        <tr>
                            <th>Тип</th>
                            <th>Номер</th>
                            <th>Дата начала действия</th>
                            <th>Срок действия</th>
                        </tr>
                        <?php
                        $client_cards=$this->get_client_cards($client_data->client_id,site_id);

                        while($card=$client_cards->fetch(PDO::FETCH_OBJ)) {
                        $onclickVal='obooking_inline_edit.edit_card_init('.$card->rec_id.',1)';
                        ?>
                        <tr onclick="<?=$onclickVal?>" id="obooking_inline_edit_client_card_row_<?=$card->rec_id?>">
                            <td id="obooking_inline_edit_client_card_row_card_type_<?=$card->rec_id?>" class="obooking_inline_edit_client_client_card_type_form_group_card_type_name_<?=$card->card_type_id?>"><?=$this->obooking->card_type_id2name($card->card_type_id)?></td>
                            <td id="obooking_inline_edit_client_card_row_card_number_<?=$card->rec_id?>"><?=$card->card_number?></td>
                            <td id="obooking_inline_edit_client_card_row_card_start_date_<?=$card->rec_id?>"><?=(int)$card->start_date?date("d.m.Y",$card->start_date):""?></td>
                            <td id="obooking_inline_edit_client_card_row_card_valid_thru_<?=$card->rec_id?>"><?=(int)$card->valid_thru?date("d.m.Y",$card->valid_thru):""?></td>
                        </tr>
                    <?}
                    ?>
                    </table>
                </div>

                <div class="form-group" id="obooking_inline_edit_client_client_card_type_form_group">
                    <label for="obooking_inline_edit_client_client_card_type">Абонементы</label>
                    <button class="btn btn-sm btn-primary" onclick="obooking_inline_create.add_new_subscription_init(<?=$client_data->client_id?>)">Добавить абонемент</button>
                    <p></p>

                    <table class="table table-condensed table-striped"  id="obooking_inline_edit_client_client_subscription_type_form_group_table">
                        <tr>
                            <th>Тип</th>
                            <th>Дата начала действия</th>
                            <th>Срок действия</th>
                            <th>Осталось занятий</th>
                        </tr>
                        <?php
                    $client_subscriptions=$this->get_client_subscriptions($client_data->client_id,site_id);

                        while($subscription=$client_subscriptions->fetch(PDO::FETCH_OBJ)) {
                        $onclickVal='obooking_inline_edit.edit_subscription_init('.$subscription->rec_id.')';?>
                        <tr onclick="<?=$onclickVal?>"  id="obooking_inline_edit_client_subscription_row_<?=$subscription->rec_id?>">
                            <td id="obooking_inline_edit_client_subscription_row_subscription_type_<?=$subscription->rec_id?>" class="obooking_inline_edit_client_client_subscription_type_form_group_subscription_type_name_<?=$subscription->subscription_type_id?>"><?=$this->obooking->subscription_type_id2name($subscription->subscription_type_id)?></td>
                            <td id="obooking_inline_edit_client_subscription_row_subscription_start_date_<?=$subscription->rec_id?>"><?=(int)$subscription->start_date?date("d.m.Y",$subscription->start_date):""?></td>
                            <td id="obooking_inline_edit_client_subscription_row_subscription_valid_thru_<?=$subscription->rec_id?>"><?=(int)$subscription->valid_thru?date("d.m.Y",$subscription->valid_thru):""?></td>
                            <td id="obooking_inline_edit_client_subscription_row_subscription_visits_left_<?=$subscription->rec_id?>"><?=(int)$subscription->visits_left?></td>
                        </tr>
                    <?}
                    ?>
                    </table>
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group" id="obooking_inline_edit_client_client_comment_form_group">
                    <label for="obooking_inline_edit_client_client_comment">Примечание</label>
                    <textarea rows="10" class="form-control" id="obooking_inline_edit_client_client_comment"><?=htmlspecialchars($client_data->client_comment)?></textarea>
                    <span class="help-block" id="obooking_inline_edit_client_client_comment_help_block"></span>
                </div>
            </div>
        </div>
    <?}

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
           print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $client_data=$this->obooking->get_client_info('client_name,
            client_lastname,
            client_birthdate,
            client_phone,
            client_phone2,
            client_email,
            client_balance,
            client_bonus_balance,
            client_status,
            client_last_activity_timestamp,
            client_comment,
            client_bonus_balance',$this->client_id);
        if(!$client_data) {
            print 'client is not found';
            exit;
        }
        $client_data->client_id=(int)$this->client_id;


        $this->print_editor($client_data);
    }
}
new load_client_editor($this);
