<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";
require_once "obooking/get_clients_balance_history_filter_bg.php";

class get_client_balance_history {
    /**
     * @var get_clients_balance_history_filter_bg
     */
    public $get_clients_balance_history_filter_bg;
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

        $this->get_clients_balance_history_filter_bg=new get_clients_balance_history_filter_bg($uCore);

        $payment_method_id2text=[];
        $payment_method_id2text[0]="Наличные";
        $payment_method_id2text[1]="Карта";
        $payment_method_id2text[2]="Онлайн";
        $payment_method_id2text[200]="Отмененная операция (Наличные)";
        $payment_method_id2text[201]="Отмененная операция (Карта)";
        $payment_method_id2text[202]="Отмененная операция (Онлайн)";
        $payment_method_id2text[100]="Списание";
        $payment_method_id2text[300]="Отмененная операция (Списание)";
        $payment_method_id2text[101]="Отмена операции";

        if(!$client_info=$obooking->get_client_info("client_balance",$this->client_id)) {
            $this->uFunc->error('1581961280' . '-' . $this->client_id);
        }
        $client_balance=$client_info->client_balance;?>

        <div id="obooking_calendar_client_balance_history_deposit_client_account_block">
            <div class="row">
                <div class="col-md-3">
                    <label for="obooking_calendar_client_balance_history_payment_paid_input">Сумма операции</label>
                </div>
                <div class="col-md-3">
                    <label for="obooking_calendar_client_balance_history_office_selectbox">Филиал</label>
                </div>
                <div class="col-md-3">
                    <label for="obooking_calendar_client_balance_history_payment_type_selectbox">Операция</label>
                </div>
                <div class="col-md-3"></div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <input type="text" class="form-control" id="obooking_calendar_client_balance_history_payment_paid_input">
                </div>
                <div class="col-md-3">
                    <select id="obooking_calendar_client_balance_history_office_selectbox" class="form-control">
                        <?php
                        $offices_stm=$obooking->get_offices("office_id,office_name");
                        while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {?>
                            <option value="<?=$office->office_id?>"><?=$office->office_name?></option>
                        <?}
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="obooking_calendar_client_balance_history_payment_type_selectbox" class="form-control">
                        <option value="0">Оплата Наличными</option>
                        <option value="1">Оплата Картой</option>
                        <option value="2">Оплата Онлайн</option>
                        <option value="100">Списание</option>
                    </select>
                </div>

            </div>
            <div class="row">
                <div class="col-md-9">
                    <label for="obooking_calendar_client_balance_history_payment_comment">Комментарий к операции</label>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <textarea class="form-control" id="obooking_calendar_client_balance_history_payment_comment" style="height: 50px;"></textarea>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary" id="obooking_calendar_client_balance_history_deposit_client_account_btn" onclick="obooking_inline_edit.deposit_client_account()">Выполнить операцию</button>
                </div>
            </div>
        </div>

        <div class="row">&nbsp;</div>


        <label>Текущий баланс: </label>
        <span class="form-control-static" id="obooking_calendar_client_balance_amount"><?=number_format($client_balance,0,"."," ")?></span> <b class="icon-arrows-cw" title="Пересчитать остаток" onclick="obooking_inline_edit.recalculate_client_balance(<?=$this->client_id?>)" id="obooking_calendar_client_balance_amount_recalculate_btn"></b>

        <input type="hidden" id="obooking_calendar_client_balance_history_client_id" value="<?=$this->client_id?>">

        <div class="row">&nbsp;</div>

        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_inline_edit_get_client_balance_history_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.get_client_balance_history_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.get_client_balance_history_filter()"><span class="icon-search"></span></button>
            </span>
        </div>

        <table class="table table-striped table-condensed">
            <tr>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('id',<?=$this->client_id?>)">#</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('id',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('id',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_filter_open_timestamps_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('timestamp',<?=$this->client_id?>)">Дата</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('timestamp',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('timestamp',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $timestamps_start=$this->get_clients_balance_history_filter_bg->get_filtered_timestamps("start",$this->client_id);
                    $timestamps_end=$this->get_clients_balance_history_filter_bg->get_filtered_timestamps("end",$this->client_id);
                    if($timestamps_start||$timestamps_end) {
                        ?>
                        <div><span onclick="obooking_inline_edit.get_client_balance_history_toggle_timestamp2filter(<?=$this->client_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?php
                            if($timestamps_start) {
                                print 'с ';
                                print date('d.m.Y',$timestamps_start);
                            }
                            if($timestamps_start&&$timestamps_end) {
                                print ' ';
                            }
                            if($timestamps_end) {
                                print 'по ';
                                print date('d.m.Y',$timestamps_end);
                            }
                            ?></div>
                        <?php
                        if($timestamps_start&&!$timestamps_end) {
                            $q_timestamps_filter=" timestamp>=".$timestamps_start." AND ";
                        }
                        elseif(!$timestamps_start&&$timestamps_end) {
                            $q_timestamps_filter=" timestamp<=".($timestamps_end+86400)." AND ";
                        }
                        else {
                            $q_timestamps_filter = " (timestamp>=" . $timestamps_start . " AND timestamp<=" . ($timestamps_end + 86400) . ") AND ";
                        }
                    }
                    else {
                        $q_timestamps_filter = "";
                    } ?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_filter_open_offices_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('office_name',<?=$this->client_id?>)">Филиал</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('office_name',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('office_name',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $offices_ar=$this->get_clients_balance_history_filter_bg->get_filtered_offices($this->client_id);
                    $offices_filter=[];
                    foreach($offices_ar as $office_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $offices_filter[]=$office_id;
                        $office_name=$obooking->office_id2office_name($office_id);
                        ?>
                        <div><span onclick="obooking_inline_edit.get_client_balance_history_toggle_office2filter(<?=$office_id?>,<?=$this->client_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$office_name?></div>
                    <?}
                    if(count($offices_filter)) {
                        $q_offices_filter = "(offices.office_id=" . implode(" OR offices.office_id=", $offices_filter) . ') AND ';
                    }
                    else {
                        $q_offices_filter = '';
                    }
                    ?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_filter_open_amounts_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('amount',<?=$this->client_id?>)">Сумма</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('amount',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('amount',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $amounts_start=$this->get_clients_balance_history_filter_bg->get_filtered_amounts("start",$this->client_id);
                    $amounts_end=$this->get_clients_balance_history_filter_bg->get_filtered_amounts("end",$this->client_id);
                    if($amounts_start||$amounts_end) {
                        ?>
                        <div><span onclick="obooking_inline_edit.get_client_balance_history_toggle_amount2filter(<?=$this->client_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?php
                            if($amounts_start) {
                                print 'от ';
                                print $amounts_start;
                            }
                            if($amounts_start&&$amounts_end) {
                                print ' ';
                            }
                            if($amounts_end) {
                                print 'до ';
                                print $amounts_end;
                            }
                            ?></div>
                        <?php
                        if($amounts_start&&!$amounts_end) {
                            $q_amounts_filter=" amount>=".$amounts_start." AND ";
                        }
                        elseif(!$amounts_start&&$amounts_end) {
                            $q_amounts_filter=" amount<=".$amounts_end." AND ";
                        }
                        else {
                            $q_amounts_filter = " (amount>=" . $amounts_start . " AND amount<=" . $amounts_end . ") AND ";
                        }
                    }
                    else {
                        $q_amounts_filter = "";
                    } ?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_filter_open_payment_methods_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('payment_method',<?=$this->client_id?>)">Метод оплаты</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('payment_method',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('payment_method',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    $payment_methods_ar=$this->get_clients_balance_history_filter_bg->get_filtered_payment_methods($this->client_id);
                    $payment_methods_filter=[];
                    foreach($payment_methods_ar as $payment_method=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $payment_methods_filter[]=$payment_method;
                        $payment_method=(int)$payment_method;
                        $payment_method_name=$payment_method_id2text[$payment_method];
                        ?>
                        <div><span onclick="obooking_inline_edit.get_client_balance_history_toggle_payment_method2filter(<?=$payment_method?>,<?=$this->client_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$payment_method_name?></div>
                    <?}
                    if(count($payment_methods_filter)) {
                        $q_payment_methods_filter = "(payment_method=" . implode(" OR payment_method=", $payment_methods_filter) . ') AND ';
                    }
                    else {
                        $q_payment_methods_filter = '';
                    }
                    ?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('firstname',<?=$this->client_id?>)">Ответственный</b>
                    <?php
                    if($field_is_sorted=$this->get_clients_balance_history_filter_bg->field_is_sorted('firstname',$this->client_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_client_balance_history_setup_sorting('firstname',<?=$this->client_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b></td>
                <td style="cursor: pointer; white-space: nowrap">Информация</td>
                <td></td>
            </tr>
            <?php
        $sort_order_ar=$this->get_clients_balance_history_filter_bg->get_sort_order($this->client_id);
            $order_by_ar=[];
            foreach ($sort_order_ar as $iValue) {
                if(isset($iValue["field"], $iValue["direction"])) {
                    $order_by_ar[] = $iValue["field"] . " " . $iValue["direction"];
                }
            }
            if(count($order_by_ar)) {
                $q_order = implode(",", $order_by_ar);
            }
        else {
            $q_order = "id DESC";
        }

        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT
            id,
            clients_balance_history.office_id,
            office_name,
            timestamp,
            description,
            amount,
            payment_method,
            firstname,
            lastname
            FROM 
            clients_balance_history
            LEFT JOIN offices on clients_balance_history.office_id=offices.office_id AND clients_balance_history.site_id=offices.site_id
            LEFT JOIN madmakers_uAuth.u235_users ON clients_balance_history.user_id=u235_users.user_id
            WHERE
            $q_timestamps_filter
            $q_offices_filter
            $q_amounts_filter
            $q_payment_methods_filter
            client_id=:client_id AND
            clients_balance_history.site_id=:site_id
            ORDER BY $q_order
            ");
            $site_id=site_id;
            $stm->bindParam(':client_id', $this->client_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1581961571'.$e->getMessage());}
            /** @noinspection PhpUndefinedVariableInspection */
        while($event=$stm->fetch(PDO::FETCH_OBJ)) {
            $event->payment_method=(int)$event->payment_method;?>
            <tr class="obooking_inline_edit_get_client_balance_history_row" id="obooking_inline_edit_get_client_balance_history_row_<?=$event->id?>" style="<?php
            if(($event->payment_method>199&&$event->payment_method<203)||$event->payment_method===300) {
                print 'opacity:0.3';
            }
            ?>">
                <td><?=$event->id?></td>
                <td><?=date("d.m.Y H:i",$event->timestamp)?></td>
                <td><?=$event->office_name?></td>
                <td><?=number_format($event->amount,0,"."," ")?></td>
                <td><?=$payment_method_id2text[$event->payment_method]?></td>
                <td><?=$event->firstname?> <?=$event->lastname?></td>
                <td><?=$event->description?></td>
                <td><?php
                    if(
                            $event->payment_method===0||
                            $event->payment_method===1||
                            $event->payment_method===2||
                            $event->payment_method===96||
                            $event->payment_method===97||
                            $event->payment_method===98||
                            $event->payment_method===99||
                            $event->payment_method===100
                    ) {?><b onclick="obooking_inline_edit.cancel_client_balance_operation_confirm(<?=$event->id?>)" class="icon-cancel" title="Отменить операцию"></b><?}?></td>
            </tr>
        <?}?>
        </table>
    <?}
}
/*$obooking=*/new get_client_balance_history($this);
