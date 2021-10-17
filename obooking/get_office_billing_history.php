<?php
namespace obooking;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";
require_once "obooking/get_office_balance_history_filter_bg.php";

class get_office_billing_history {
    /**
     * @var string
     */
    private $office_id_sql;
    /**
     * @var string
     */
    private $payment_type_sql;
    /**
     * @var string
     */
    private $payment_method_sql;
    /**
     * @var string
     */
    private $payment_type;
    /**
     * @var string
     */
    private $payment_method;
    /**
     * @var string
     */
    private $period;
    /**
     * @var int
     */
    private $office_id;
    /**
     * @var uFunc
     */
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["office_id"])) {
            $this->uFunc->error(10);
        }
        $this->office_id=(int)$_POST["office_id"];
        if($this->office_id) {
            $this->office_id_sql = "clients_balance_history.office_id=" . $this->office_id . " AND ";
        }
        else {
            $this->office_id_sql = "";
        }

        $this->period="current_day";
        if(isset($_POST["period"])) {
            if(
                    $_POST["period"]==="current_day"||
                    $_POST["period"]==="current_week"||
                    $_POST["period"]==="current_month"||
                    $_POST["period"]==="last_day"||
                    $_POST["period"]==="last_week"||
                    $_POST["period"]==="last_month"||
                    $_POST["period"]==="free"
            ) {
                $this->period = $_POST["period"];
            }
        }

        $this->payment_method=3;
        $this->payment_method_sql="";

        if(isset($_POST["payment_method"])) {
            $_POST["payment_method"]=(int)$_POST["payment_method"];

            if(
                    $_POST["payment_method"]===0||
                    $_POST["payment_method"]===1||
                    $_POST["payment_method"]===2
            ) {
                $this->payment_method = $_POST["payment_method"];
                $this->payment_method_sql=" AND payment_method=".$_POST["payment_method"]." ";
            }
        }

        $this->payment_type="all";
        $this->payment_type_sql="";

        if(isset($_POST["payment_type"])) {
            if($_POST["payment_type"]==="credit") {
                $this->payment_type=$_POST["payment_type"];
                $this->payment_type_sql=" AND amount<0 ";
            }
            elseif ($_POST["payment_type"]==="debit") {
                $this->payment_type=$_POST["payment_type"];
                $this->payment_type_sql=" AND amount>0 ";
            }
        }
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

        $get_office_balance_history_filter_bg=new get_office_balance_history_filter_bg($uCore);

        $payment_method_id2text=[];
        $payment_method_id2text[0]="Оплата Наличными";
        $payment_method_id2text[1]="Оплата Картой";
        $payment_method_id2text[2]="Оплата Онлайн";
        $payment_method_id2text[96]="Списание наличных";
        $payment_method_id2text[97]="Списание безналом";
        $payment_method_id2text[98]="Внесение наличных";
        $payment_method_id2text[99]="Внесение безналом";
        $payment_method_id2text[100]="Списание";
        $payment_method_id2text[101]="Отмена операции";
        $payment_method_id2text[200]="Отмененная операция (Наличные)";
        $payment_method_id2text[201]="Отмененная операция (Карта)";
        $payment_method_id2text[202]="Отмененная операция (Онлайн)";
        $payment_method_id2text[296]="Отмененная операция (Списание наличных средств)";
        $payment_method_id2text[297]="Отмененная операция (Списание безналичных средств)";
        $payment_method_id2text[298]="Отмененная операция (Внесение наличных средств)";
        $payment_method_id2text[299]="Отмененная операция (Внесение безналичных средств)";
        $payment_method_id2text[300]="Отмененная операция (Списание)";

        if($this->office_id) {?>
            <div class="row">
                <div class="col-md-4">
                    <b>Операция</b>
                </div>
                <div class="col-md-4">
                    <b>Сумма</b>
                </div>
                <div class="col-md-4"></div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <select class="form-control" id="office_balance_operation_method">
                        <option value="96">Списание наличных средств</option>
                        <option value="97">Списание безналичных средств</option>
                        <option disabled></option>
                        <option value="98">Внесение наличных средств</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="form-group" id="office_balance_operation_amount_form_group">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <input class="form-control" type="text" id="office_balance_operation_amount">
                        <span class="help-block" id="office_balance_operation_amount_help_block"></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <b>Комментарий</b>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group" id="office_balance_operation_comment_form_group">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <textarea class="form-control" id="office_balance_operation_comment" style="height: 50px"></textarea>
                        <span class="help-block" id="office_balance_operation_comment_help_block"></span>
                    </div>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-default" id="office_balance_operation_amount_submit_btn" onclick="obooking_inline_edit.office_balance_operation()">Выполнить</button>
                </div>
            </div>
            <div class="row">&nbsp;</div>
        <?}?>

        <input type="hidden" id="office_billing_history_office_id" value="<?=$this->office_id?>">
        <?php

        $timestamps_start=$get_office_balance_history_filter_bg->get_filtered_timestamps("start",$this->office_id);
        $timestamps_end=$get_office_balance_history_filter_bg->get_filtered_timestamps("end",$this->office_id);
        if($timestamps_start||$timestamps_end) {
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
        }


        $clients_ar=$get_office_balance_history_filter_bg->get_filtered_clients($this->office_id);
        $clients_filter=[];
        foreach($clients_ar as $client_id=>$is_filtered) {
            if(!$is_filtered) {
                continue;
            }
            $clients_filter[]=$client_id;
        }
        if(count($clients_filter)) {
            $q_clients_filter = "(clients.client_id=" . implode(" OR clients.client_id=", $clients_filter) . ') AND ';
        }
        else {
            $q_clients_filter = '';
        }


        $amounts_start=$get_office_balance_history_filter_bg->get_filtered_amounts("start",$this->office_id);
        $amounts_end=$get_office_balance_history_filter_bg->get_filtered_amounts("end",$this->office_id);
        if($amounts_start||$amounts_end) {
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
        }


        $payment_methods_ar=$get_office_balance_history_filter_bg->get_filtered_payment_methods($this->office_id);
        $payment_methods_filter=[];
        foreach($payment_methods_ar as $payment_method=>$is_filtered) {
            if(!$is_filtered) {
                continue;
            }
            $payment_methods_filter[]=$payment_method;
            ?>
        <?}
        if(count($payment_methods_filter)) {
            $q_payment_methods_filter = "(payment_method=" . implode(" OR payment_method=", $payment_methods_filter) . ') AND ';
        }
        else {
            $q_payment_methods_filter = '';
        }


        $site_id=site_id;

        //expense
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS expense
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            amount<0 AND 
            client_id=0 AND
            $this->office_id_sql
            site_id=:site_id 
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $expense=$qr->expense*-1;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        //income
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS income
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            amount>0 AND
            (payment_method=0 OR
                payment_method=1 OR
                payment_method=2 
            ) AND
            $this->office_id_sql
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $income=$qr->income;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        //income_cash
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS income_cash
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            amount>0 AND
            $this->office_id_sql
            site_id=:site_id AND 
            payment_method=0
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $income_cash=$qr->income_cash;
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        //income_card
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS income_card
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            amount>0 AND
            $this->office_id_sql
            site_id=:site_id AND 
            payment_method=1
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $income_card=$qr->income_card;
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        //income_online
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS income_online
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            amount>0 AND
            $this->office_id_sql
            site_id=:site_id AND 
            payment_method=2
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $income_online=$qr->income_online;
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        //total
        try {
            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS total
            FROM 
            clients_balance_history
            WHERE
            (
                payment_method=0 OR 
                payment_method=98 OR 
                (payment_method=96 AND client_id=0)
                ) AND
            $this->office_id_sql
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $total=$qr->total;
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}

        //debited from customers
        try {

            $stm=$this->uFunc->pdo("obooking")->prepare("SELECT SUM(amount) AS debited_from_customers
            FROM 
            clients_balance_history
            WHERE
            $q_timestamps_filter
            payment_method=100 AND
            $this->office_id_sql
            site_id=:site_id 
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $debited_from_customers=$qr->debited_from_customers*-1;
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        ?>

        <div class="row">&nbsp;</div>
        <div class="row">
            <div class="col-md-12"><h4>Остаток наличных в кассе: <?=number_format($total,0,"."," ")?></h4></div>
            <div class="col-md-12">&nbsp;</div>
            <div class="col-md-6"><b>Приход за период: </b><?=number_format($income,0,"."," ")?><br>
                <br>
            <b>Наличными: </b><?=number_format($income_cash,0,"."," ")?><br>
            <b>Картой: </b><?=number_format($income_card,0,"."," ")?><br>
            <b>Онлайн: </b><?=number_format($income_online,0,"."," ")?><br>
            </div>
            <div class="col-md-6"><b>Расход за период: </b><?=number_format($expense,0,"."," ")?><br>
                <br>
            <b>Итого за период: </b><?=number_format($income-$expense,0,"."," ")?><br>
            <b>Реализовано за период: </b><?=number_format($debited_from_customers,0,"."," ")?></div>

            <div class="col-md-12">&nbsp;</div>
            <div class="col-md-12">


                <div class="input-group">
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <input type="text" id="obooking_inline_edit_get_office_balance_history_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.get_office_balance_history_filter()">
                    <span class="input-group-btn">
                <button class="btn btn-default" type="button" onclick="obooking_inline_edit.get_office_balance_history_filter()"><span class="icon-search"></span></button>
            </span>
                </div>

                <table class="table table-striped table-condensed">
            <tr>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('id',<?=$this->office_id?>)">#</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('id',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('id',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_filter_open_timestamps_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('timestamp',<?=$this->office_id?>)">Дата</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('timestamp',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('timestamp',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    if($timestamps_start||$timestamps_end) {
                        ?>
                        <div><span onclick="obooking_inline_edit.get_office_balance_history_toggle_timestamp2filter(<?=$this->office_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?php
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
                    }?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_filter_open_clients_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('client_name',<?=$this->office_id?>)">Ученик</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('client_name',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('client_name',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    foreach($clients_ar as $client_id=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        if($client_info=$obooking->get_client_info("clients.client_name,clients.client_lastname",$client_id)){
                            $client_name=$client_info->client_name;
                            $client_lastname=$client_info->client_lastname;
                        }
                        else {
                            $client_name='';
                            $client_lastname='';
                        }
                        ?>
                        <div><span onclick="obooking_inline_edit.get_office_balance_history_toggle_client2filter(<?=$client_id?>,<?=$this->office_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$client_name?> <?=$client_lastname?></div>
                    <?}?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_filter_open_amounts_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('amount',<?=$this->office_id?>)">Сумма</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('amount',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('amount',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    if($amounts_start||$amounts_end) {
                        ?>
                        <div><span onclick="obooking_inline_edit.get_office_balance_history_toggle_amount2filter(<?=$this->office_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?php
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
                    }?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_filter_open_payment_methods_list()" class="icon-filter"></b>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('payment_method',<?=$this->office_id?>)">Метод оплаты</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('payment_method',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('payment_method',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b><?php
                    foreach($payment_methods_ar as $payment_method=>$is_filtered) {
                        if(!$is_filtered) {
                            continue;
                        }
                        $payment_method=(int)$payment_method;
                        $payment_method_name=$payment_method_id2text[$payment_method];
                        ?>
                        <div><span onclick="obooking_inline_edit.get_office_balance_history_toggle_payment_method2filter(<?=$payment_method?>,<?=$this->office_id?>)" class="icon-cancel" title="удалить этот фильтр"></span> <?=$payment_method_name?></div>
                    <?}?>
                </td>
                <td style="cursor: pointer; white-space: nowrap">
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('firstname',<?=$this->office_id?>)">Ответственный</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('firstname',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('firstname',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b>
                </td>
                <td style="cursor: pointer; white-space: nowrap">Информация</td>
                <td>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('office_name',<?=$this->office_id?>)">Филиал</b>
                    <?php
                    if($field_is_sorted=$get_office_balance_history_filter_bg->field_is_sorted('office_name',$this->office_id)) {
                        $icon_add=$field_is_sorted["direction"]==="ASC"?"-up":"-down";
                        $sort_order=$field_is_sorted["order"]+1;
                    }
                    else {
                        $sort_order = $icon_add = "";
                    }
                    ?>
                    <b onclick="obooking_inline_edit.get_office_balance_history_setup_sorting('office_name',<?=$this->office_id?>)">
                        <b class="icon-sort<?=$icon_add?>"></b>
                        <b><?=$sort_order?></b>
                    </b>
                </td>
                <td></td>
            </tr>
                    <?php
        $sort_order_ar=$get_office_balance_history_filter_bg->get_sort_order($this->office_id);
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

            $history_stm=$this->uFunc->pdo("obooking")->prepare("SELECT
            id,
            clients_balance_history.office_id,
            office_name,
            clients_balance_history.client_id,
            client_name,
            client_lastname,
            clients_balance_history.user_id,
            firstname,
            lastname,
            timestamp,
            description,
            amount,
            payment_method
            FROM 
            clients_balance_history
            LEFT JOIN clients ON clients_balance_history.client_id=clients.client_id AND clients_balance_history.site_id=clients.site_id
            LEFT JOIN offices ON clients_balance_history.office_id=offices.office_id AND clients_balance_history.site_id=offices.site_id
            LEFT JOIN madmakers_uAuth.u235_users ON clients_balance_history.user_id=u235_users.user_id
            WHERE
            $q_timestamps_filter
            $q_clients_filter
            $q_amounts_filter
            $q_payment_methods_filter
            $this->office_id_sql
            clients_balance_history.site_id=:site_id
            ORDER BY $q_order
            ");
            $site_id=site_id;
//            $history_stm->bindParam(':start_timestamp', $this->start_timestamp,PDO::PARAM_INT);
//            $history_stm->bindParam(':stop_timestamp', $this->stop_timestamp,PDO::PARAM_INT);
            $history_stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $history_stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1582006179'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        while($event=$history_stm->fetch(PDO::FETCH_OBJ)) {
            $event->payment_method=(int)$event->payment_method;?>
            <tr class="obooking_inline_edit_get_office_balance_history_row" id="obooking_inline_edit_get_office_balance_history_row_<?=$event->id?>" style="<?php
            if(($event->payment_method>199&&$event->payment_method<203)||$event->payment_method===300) {
                print 'opacity:0.3';
            }
            ?>">
                <td><?=$event->id?></td>
                <td><?=date('d.m.Y H:i',$event->timestamp)?></td>
                <td><?=$event->client_name?> <?=$event->client_lastname?></td>
                <td><?=number_format($event->amount,0,"."," ")?></td>
                <td><?=$payment_method_id2text[$event->payment_method]?></td>
                <td><?=$event->firstname?> <?=$event->lastname?></td>
                <td><?=$event->description?></td>
                <td><?=$event->office_name?></td>
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
                    ) {?><b onclick="obooking_inline_edit.cancel_office_balance_operation_confirm(<?=$event->id?>)" class="icon-cancel" title="Отменить операцию"></b><?}?></td>
            </tr>
        <?}?>
        </table>
            </div>
        </div>
    <?}
}
/*$obooking=*/new get_office_billing_history($this);
