<?php
namespace obooking;
use PDO;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class get_clients_balance_history_filter_bg {
    /**
     * @var common
     */
    private $obooking;

    private function check_ses_var($client_id) {
        if(!isset($_SESSION["obooking"])) {
            $_SESSION["obooking"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"])) {
            $_SESSION["obooking"]["client_balance_history"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id] = [];
        }

        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["start"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["start"] = 0;
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["start"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["start"] = 0;
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["end"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["end"] = 0;
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["end"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["end"] = 0;
        }
//
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"])) {
            $_SESSION["obooking"]["sort"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"] = [];
        }
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"] = [];
        }
    }

    private function timestamp_is_filtered(/*start|end*/$point,$client_id) {
        $this->check_ses_var($client_id);
        if(!$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"][$point]) {
            return "";
        }

        return date('d.m.Y',$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"][$point]);
    }
    private function amount_is_filtered(/*start|end*/$point,$client_id) {
        $this->check_ses_var($client_id);
        if(!$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"][$point]) {
            return "";
        }

        return date('d.m.Y',$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"][$point]);
    }
    private function office_is_filtered(/*int*/$office_id,$client_id) {
        $this->check_ses_var($client_id);
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id] = 0;
        }

        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id];
    }
    private function payment_method_is_filtered(/*int*/$payment_method,$client_id) {
        $this->check_ses_var($client_id);
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method] = 0;
        }

        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method];
    }

    private function toggle_timestamp_filter($start_date,$end_date,$client_id) {
        if($start_date==="0") {
            $start_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$start_date);
            $start_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        if($end_date==="0") {
            $end_timestamp = 0;
        }
        else {
            $timestamp_ar=explode('.',$end_date);
            $end_timestamp = strtotime($timestamp_ar[1]."/".$timestamp_ar[0]."/".$timestamp_ar[2]);
        }

        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["start"]=$start_timestamp;
        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"]["end"]=$end_timestamp;
    }
    private function toggle_amount_filter($start_amount,$end_amount,$client_id) {
        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["start"]=$start_amount;
        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"]["end"]=$end_amount;
    }
    private function toggle_office_filter(/*int*/$office_id,$client_id) {
        $this->check_ses_var($client_id);
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id] = 0;
        }
        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id]=1-$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id];

        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"][$office_id];
    }
    private function toggle_payment_method_filter(/*int*/$payment_method,$client_id) {
        $this->check_ses_var($client_id);
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method])) {
            $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method] = 0;
        }
        $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method]=1-$_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method];

        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"][$payment_method];
    }

    private function print_offices_dg($client_id) {
        ob_start();
        $offices_stm=$this->obooking->get_offices("office_id,office_name");
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_inline_edit_get_client_balance_history_filter_offices_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.get_client_balance_history_filter_offices_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_inline_edit.get_client_balance_history_filter_offices_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_inline_edit_get_client_balance_history_filter_offices_dg_list">
            <?php
            while($office=$offices_stm->fetch(PDO::FETCH_OBJ)) {
                $office->office_id=(int)$office->office_id;
                $is_filtered=$this->office_is_filtered($office->office_id,$client_id);?>
                <tr
                        id="obooking_inline_edit_get_client_balance_history_filter_offices_dg_row_<?=$office->office_id?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-office_id="<?=$office->office_id?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_inline_edit.get_client_balance_history_toggle_office2filter(<?=$office->office_id?>,<?=$client_id?>)">#<?=$office->office_id?></td>
                    <td class="obooking_inline_edit_get_client_balance_history_filter_offices_dg_list_office_name" style="cursor: pointer" onclick="obooking_inline_edit.get_client_balance_history_toggle_office2filter(<?=$office->office_id?>,<?=$client_id?>)"><?=$office->office_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_payment_methods_dg($client_id) {
        ob_start();
        ?>
        <div class="input-group">
            <!--suppress HtmlFormInputWithoutLabel -->
            <input type="text" id="obooking_inline_edit_get_client_balance_history_filter_payment_methods_dg_row_filter" class="form-control" placeholder="Фильтр" onkeyup="obooking_inline_edit.get_client_balance_history_filter_payment_methods_list_filter()">
            <span class="input-group-btn">
                <button class="btn btn-default" type="button"><span class="icon-search" onclick="obooking_inline_edit.get_client_balance_history_filter_payment_methods_list_filter()"></span></button>
            </span>
        </div>

        <table class="table table-condensed table-hover" id="obooking_inline_edit_get_client_balance_history_filter_payment_methods_dg_list">
            <?php
            $payment_methods[0]="Оплата наличными";
            $payment_methods[1]="Оплата картой";
            $payment_methods[2]="Оплата онлайн";
            $payment_methods[96]="Списание наличных средств";
            $payment_methods[97]="Списание безналичных средств";
            $payment_methods[98]="Внесение наличных средств";
            $payment_methods[99]="Внесение безналичных средств";
            $payment_methods[100]="Списание";
            $payment_methods[101]="Отмена операции";
            $payment_methods[200]="Отмененная операция (Оплата наличными)";
            $payment_methods[201]="Отмененная операция (Оплата картой)";
            $payment_methods[202]="Отмененная операция (Оплата онлайн)";
            $payment_methods[296]="Отмененная операция (Списание наличных средств)";
            $payment_methods[297]="Отмененная операция (Списание безналичных средств)";
            $payment_methods[298]="Отмененная операция (Внесение наличных средств)";
            $payment_methods[299]="Отмененная операция (Внесение безналичных средств)";
            $payment_methods[300]="Отмененная операция (Списание)";
            foreach($payment_methods as $payment_method=>$payment_method_name) {
                $is_filtered=$this->payment_method_is_filtered($payment_method,$client_id);?>
                <tr
                        id="obooking_inline_edit_get_client_balance_history_filter_payment_methods_dg_row_<?=$payment_method?>"
                        class="<?=$is_filtered?'bg-success':''?>"
                        data-payment_method="<?=$payment_method?>"
                        data-is_filtered="<?=$is_filtered?>"
                >
                    <td style="cursor: pointer" onclick="obooking_inline_edit.get_client_balance_history_toggle_payment_method2filter(<?=$payment_method?>,<?=$client_id?>)">#<?=$payment_method?></td>
                    <td class="obooking_inline_edit_get_client_balance_history_filter_payment_methods_dg_list_payment_method_name" style="cursor: pointer" onclick="obooking_inline_edit.get_client_balance_history_toggle_payment_method2filter(<?=$payment_method?>,<?=$client_id?>)"><?=$payment_method_name?></td>
                </tr>
            <?}?>
        </table>
        <?php
        return ob_get_clean();
    }
    private function print_timestamps_dg($client_id) {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_inline_edit_get_client_balance_history_filter_timestamps_dg_start_timestamp">Дата с</label>
                    <input id="obooking_inline_edit_get_client_balance_history_filter_timestamps_dg_start_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_inline_edit.get_client_balance_history_toggle_timestamp2filter(<?=$client_id?>,'start')" value="<?=$this->timestamp_is_filtered("start",$client_id)?>">
                </div>
                <div class="col-md-6">
                    <label for="obooking_inline_edit_get_client_balance_history_filter_timestamps_dg_end_timestamp">Дата по</label>
                    <input id="obooking_inline_edit_get_client_balance_history_filter_timestamps_dg_end_timestamp" type="text" class="form-control" placeholder="<?=date('d.m.Y')?>" onblur="obooking_inline_edit.get_client_balance_history_toggle_timestamp2filter(<?=$client_id?>,'end')" value="<?=$this->timestamp_is_filtered("end",$client_id)?>">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    private function print_amounts_dg($client_id) {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-6">
                    <label for="obooking_inline_edit_get_client_balance_history_filter_amounts_dg_start_amount">Сумма от</label>
                    <input id="obooking_inline_edit_get_client_balance_history_filter_amounts_dg_start_amount" type="text" class="form-control" placeholder="100" onblur="obooking_inline_edit.get_client_balance_history_toggle_amount2filter(<?=$client_id?>,'start')" value="<?=$this->amount_is_filtered("start",$client_id)?>">
                </div>
                <div class="col-md-6">
                    <label for="obooking_inline_edit_get_client_balance_history_filter_amounts_dg_end_amount">Сумма до</label>
                    <input id="obooking_inline_edit_get_client_balance_history_filter_amounts_dg_end_amount" type="text" class="form-control" placeholder="5000" onblur="obooking_inline_edit.get_client_balance_history_toggle_amount2filter(<?=$client_id?>,'end')" value="<?=$this->amount_is_filtered("end",$client_id)?>">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function get_filtered_timestamps($point,$client_id) {
        $this->check_ses_var($client_id);
        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["timestamps"][$point];
    }
    public function get_filtered_amounts($point,$client_id) {
        $this->check_ses_var($client_id);
        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["amounts"][$point];
    }
    public function get_filtered_offices($client_id) {
        $this->check_ses_var($client_id);
        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["offices"];
    }
    public function get_filtered_payment_methods($client_id) {
        $this->check_ses_var($client_id);
        return $_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]["payment_methods"];
    }

    public function get_sort_order($client_id) {
        $this->check_ses_var($client_id);
        return $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"];
    }
    public function field_is_sorted($field,$client_id) {
        if(isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field])) {
            return $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field];
        }

        return false;
    }

    private function reduce_sort_order_counter_for_next_fields($order,$client_id) {
        $found=false;
        $initial_order=$order;

        while(isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order+1])){
            $found=true;
            $field=$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order+1]["field"];
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["order"]--;
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order]=$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order+1];
            unset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order+1]);
            $order++;
        }

        if(!$found) {
            unset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$initial_order]);
        }
    }
    private function setup_sort_order($field,$client_id) {
        $this->check_ses_var($client_id);
        if(!isset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field])) {
            $order=count($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"]);
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]=array(
                "direction"=>"ASC",
                "order"=>$order
            );
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order]=array(
                "field"=>$field,
                "direction"=>$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["direction"]
            );
        }
        else if($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["direction"]==="ASC") {
            $order=$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["order"];
            $_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["direction"]=$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["order"][$order]["direction"]="DESC";
        }
        else {
            $order=$_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]["order"];
            $this->reduce_sort_order_counter_for_next_fields($order,$client_id);
            unset($_SESSION["obooking"]["client_balance_history"][$client_id]["sort"]["fields"][$field]);
        }
    }

    public function prepare_filters() {
        if(!isset($_POST["client_id"])) {
            print json_encode(array(
                'status'=>'error',
                'msg'=>'wrong data 1581937573'
            ));
            exit;
        }
        $client_id=(int)$_POST["client_id"];
        if(isset($_POST["reset_filter"])) {
            unset($_SESSION["obooking"]["client_balance_history"][$client_id]["filter"]);
            print json_encode(array(
                'status'=>'done'
            ));
            exit;
        }
        if(isset($_POST['sort'])) {
            if(!isset($_POST['field'])) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data 1581893725'
                ));
                exit;
            }

            $field=$_POST["field"];
            if(
                $field!=="timestamp"&&
                $field!=="office_name"&&
                $field!=="amount"&&
                $field!=="payment_method"&&
                $field!=="firstname"&&
                $field!=="id"
            ) {
                print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data 1581998924'
                ));
                exit;
            }

            $this->setup_sort_order($field,$client_id);

            print json_encode(array(
                'status'=>'done',
                'client_id'=>$client_id
            ));
            exit;
        }

        if (isset($_POST["data"])) {
            $data=$_POST["data"];
            if($data==="toggle office") {
                if(!isset($_POST['office_id'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $office_id=(int)$_POST['office_id'];
                $this->toggle_office_filter($office_id,$client_id);
                print json_encode(array(
                    'status'=>'done',
                    'client_id'=>$client_id,
                    'office_id'=>$office_id,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle payment_method") {
                if(!isset($_POST['payment_method'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581693182'
                    ));
                    exit;
                }
                $payment_method=(int)$_POST['payment_method'];
                $this->toggle_payment_method_filter($payment_method,$client_id);
                print json_encode(array(
                    'status'=>'done',
                    'client_id'=>$client_id,
                    'payment_method'=>$payment_method,
                    'is_filtered'=>$_POST['is_filtered']
                ));
                exit;
            }
            if($data==="toggle timestamp") {
                if(!isset($_POST['start_timestamp'],$_POST['end_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581916755'
                    ));
                    exit;
                }
                if($_POST['start_timestamp']!=='0'&&!uString::isDate($_POST['start_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'start date is wrong'
                    ));
                    exit;
                }
                if($_POST['end_timestamp']!=='0'&&!uString::isDate($_POST['end_timestamp'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'end date is wrong'
                    ));
                    exit;
                }
                $this->toggle_timestamp_filter($_POST['start_timestamp'],$_POST['end_timestamp'],$client_id);
                print json_encode(array(
                    'status'=>'done',
                    'client_id'=>$_POST['client_id'],
                    'start_timestamp'=>$_POST['start_timestamp'],
                    'end_timestamp'=>$_POST['end_timestamp']
                ));
                exit;
            }
            if($data==="toggle amount") {
                if(!isset($_POST['start_amount'],$_POST['end_amount'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'wrong data 1581916755'
                    ));
                    exit;
                }
                if($_POST['start_amount']!=='0'&&!uString::isFloat($_POST['start_amount'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'start amount is wrong'
                    ));
                    exit;
                }
                if($_POST['end_amount']!=='0'&&!uString::isFloat($_POST['end_amount'])) {
                    print json_encode(array(
                        'status'=>'error',
                        'msg'=>'end amount is wrong'
                    ));
                    exit;
                }
                $this->toggle_amount_filter($_POST['start_amount'],$_POST['end_amount'],$client_id);
                print json_encode(array(
                    'status'=>'done',
                    'client_id'=>$_POST['client_id'],
                    'start_amount'=>$_POST['start_amount'],
                    'end_amount'=>$_POST['end_amount']
                ));
                exit;
            }

            print json_encode(array(
                    'status'=>'error',
                    'msg'=>'wrong data'
                ));
                exit;
        }

        $offices_dg_content=$this->print_offices_dg($client_id);
        $payment_methods_dg_content=$this->print_payment_methods_dg($client_id);
        $timestamps_dg_content=$this->print_timestamps_dg($client_id);
        $amounts_dg_content=$this->print_amounts_dg($client_id);
        print json_encode(array(
            'status'=>'done',
            'offices_dg_content'=>$offices_dg_content,
            'payment_methods_dg_content'=>$payment_methods_dg_content,
            'timestamps_dg_content'=>$timestamps_dg_content,
            'amounts_dg_content'=>$amounts_dg_content//,
        ));
        exit;
    }

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
    }
}
if($this->mod==='obooking'&&$this->page_name==='get_clients_balance_history_filter_bg') {
    $obooking=new get_clients_balance_history_filter_bg($this);
    $obooking->prepare_filters();
}
