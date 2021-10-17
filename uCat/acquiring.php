<?php
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/sberbank.php";
//require_once "uCat/classes/tochka.php";
//require_once "uCat/classes/paypal.php";

class acquiring {
    public $site_id, $bank, $on_off;
    private $sberbank_acquiring_status;

    private $uCore, $uFunc, $sberbank;


//    public function paypal() {
//        if(isset($_POST["type"])) {
//            if($_POST["type"] == "registration" && isset($_POST["order_id"]) && isset($_POST["order_mail"])) {
//                $get_order_status = $this->get_order_status($_POST["order_id"]);
//                if ($get_order_status == "waiting payment") {
//                    $order_uuid = $this->find_uuid_order($_POST["order_id"]);
//                }
//                else {
//                    $order_uuid = false;
//                }
//
//                $total_price = $this->get_amount_order($_POST["order_id"], $_POST["order_mail"]);
//                if($total_price) {
//                    $return_url = urlencode(u_sroot.'uCat/order_info/'.$_POST["order_id"].'/'.$_POST["order_mail"]);
//                    $return_url_cancel = urlencode(u_sroot.'uCat/order_info/'.$this->paypal->not_pay_key);
//
//                    $obj = '<form action="'.$this->paypal->action_url.'" id="paypal-form" method="post">
//                            <input type="hidden" name="cmd" value="_xclick">
//                            <input type="hidden" name="business" value="'.$this->paypal->receiver_email.'">
//                            <input type="hidden" name="item_name" value="Заказ №'.$_POST["order_id"].'">
//                            <input type="hidden" name="item_number" value="'.$_POST["order_id"].'">
//                            <input type="hidden" name="amount" value="'.number_format((float)$total_price, (count(explode('.',$total_price))>1?2:0), ".", "").'">
//                            <input type="hidden" name="return" value="'.$return_url.'">
//                            <input type="hidden" name="no_shipping" value="1">
//                            <input type="hidden" name="cancel_return" value="'.$return_url_cancel.'">
//                            <input type="hidden" name="currency_code" value="'.$this->paypal->currency_code.'">
//                            <input type="hidden" name="lc" value="'.$this->paypal->location.'">
//                            <button class="btn btn-primary" type="submit">Оплатить картой</button>
//                            </form>';
//                    $obj_json = json_encode($obj, JSON_UNESCAPED_UNICODE);
//
//                    $this->paypal->save_order_uuid_and_exp_date($_POST["order_id"], $this->paypal->order_uuid, date("Y-m-d H:i:s", $this->paypal->exp_date));
//
//                    $result = array(
//                        'orderId' => $order_uuid,
//                        'bank' => $this->bank,
//                        'jsonobj' => $obj_json
//                    );
//                    echo json_encode($result, JSON_UNESCAPED_UNICODE);
//                }
//            }
//            else if($_POST["type"] == "status" && isset($_POST["order_id"]) && isset($_POST["order_mail"])) {
//                exit;
//            }
//            else {
//                exit;
//            }
//        }
//        else {
//            exit;
//        }
//    }

//    public function tochkabank() {
//        if(isset($_POST["type"])) {
//            if($_POST["type"] == "registration" && isset($_POST["order_id"]) && isset($_POST["order_mail"])) {
//
//                $total_price = (int)$this->get_amount_order($_POST["order_id"], $_POST["order_mail"]);
//                if($total_price) {
//                    $data = array(
//                        'orderNumber' => $_POST["order_id"],
//                        'amount' => round((float)$total_price, 2)
//                    );
//
//                    $get_order_status = $this->get_order_status($data["orderNumber"]);
//                    if ($get_order_status == "waiting payment") {
//                        $order_uuid = $this->find_uuid_order($data["orderNumber"]);
//                    }
//                    else {
//                        $order_uuid = false;
//                    }
//
//                    $this->set_order_status($data["orderNumber"], "waiting payment");
//
//                    if ($order_uuid) {
//                        $formUrl = "https://money.yandex.ru/payments/external/confirmation?orderId=".$order_uuid;
//
//                        $timestamp = $this->get_order_timestamp($data["orderNumber"], $_POST["order_mail"]);
//                        if ($timestamp) {
//                            $payment = $this->tochkabank->get_order_status($order_uuid);
//                            $status = $payment->status;
//
//                            if ($status !== "pending" && $status !== "waiting_for_capture" && $status !== "succeeded" && $status !== "canceled") {
//                                echo $this->tochkabank->reg_order($data);
//                            }
//                            else {
//                                $result = array(
//                                    'orderId' => $order_uuid,
//                                    'bank' => $this->bank,
//                                    'formUrl' => $formUrl
//                                );
//                                echo json_encode($result, JSON_UNESCAPED_UNICODE);
//                            }
//                        }
//                    }
//                    else {
//                        echo $this->tochkabank->reg_order($data);
//                    }
//                }
//            }
//            else {
//                exit;
//            }
//        }
//    }

    public function sberbank() {
        if(isset($_POST["type"])) {
            if($_POST["type"] == "registration" && isset($_POST["order_id"]) && isset($_POST["order_mail"])) {

                $total_price = $this->get_amount_order($_POST["order_id"], $_POST["order_mail"]);
                if($total_price) {
                    $data = array(
                        'orderNumber' => $_POST["order_id"],
                        'amount' => (int)$total_price * 100
                    );

                    $get_order_status = $this->get_order_status($data["orderNumber"]);
                    if ($get_order_status == "waiting payment") {
                        $order_uuid = $this->find_uuid_order($data["orderNumber"]);
                    }
                    else {
                        $order_uuid = false;
                    }

                    $this->set_order_status($data["orderNumber"], "waiting payment");

                    if ($order_uuid) {
                        $formUrl = $this->sberbank->url."/payment/merchants/sbersafe/payment_ru.html?mdOrder=".$order_uuid;

                        $timestamp = $this->get_order_timestamp($data["orderNumber"], $_POST["order_mail"]);
                        if ($timestamp) {
                            $data_status = array(
                                'orderNumber' => $_POST["order_id"]."/".$timestamp
                            );
                            $status = $this->sberbank->order_status_extended($data_status);

                            if ($status->orderStatus == 6) {
                                echo $this->sberbank->check_order($data);
                            }
                            else {
                                $result = array(
                                    'orderId' => $order_uuid,
                                    'bank' => 'sberbank',
                                    'formUrl' => $formUrl
                                );
                                echo json_encode($result, JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                    else {
                        echo $this->sberbank->check_order($data);
                    }
                }
            }
            else if($_POST["type"] == "status" && isset($_POST["order_id"]) && isset($_POST["order_mail"])) {
                $timestamp = $this->get_order_timestamp($_POST["order_id"], $_POST["order_mail"]);
                if ($timestamp) {
                    $data = array(
                        'orderNumber' => $_POST["order_id"]."/".$timestamp
                    );
                }
                else {
                    $data = array(
                        'orderNumber' => $_POST["order_id"]
                    );
                }
                $status_extended = $this->sberbank->order_status_extended($data);
                echo json_encode($status_extended, JSON_UNESCAPED_UNICODE);
            }
            else {
                exit;
            }
        }
    }

    private function set_order_status($order_id, $status, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            orders 
            SET 
            order_status=:order_status 
            WHERE  
            order_id=:order_id AND 
            site_id=:site_id
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_status', $status, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/acquiring 05'/*.$e->getMessage()*/);}
    }

    private function get_amount_order($order_id, $user_email, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            delivery_price,
            total_price
            FROM
            orders
            WHERE
            order_id=:order_id AND 
            site_id=:site_id AND 
            user_email=:user_email
            LIMIT 1
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_email', $user_email, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($order = $stm->fetch(PDO::FETCH_OBJ)) {
                return $order->total_price+$order->delivery_price;
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/acquiring 10'/*.$e->getMessage()*/);}
        return false;
    }

    private function get_order_timestamp($order_id, $user_email, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
            acquiring.timestamp
            FROM
            acquiring
            LEFT JOIN
            orders
            ON
            acquiring.order_id=orders.order_id AND 
            acquiring.site_id=orders.site_id
            WHERE
            acquiring.order_id=:order_id AND 
            acquiring.site_id=:site_id AND 
            orders.user_email=:user_email
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_email', $user_email, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($order = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $order["timestamp"];
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/acquiring 15'/*.$e->getMessage()*/);}
        return false;
    }

    private function get_order_status($order_id, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
            order_status
            FROM
            orders
            WHERE
            order_id=:order_id AND 
            site_id=:site_id
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($order = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $order["order_status"];
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/acquiring 20'/*.$e->getMessage()*/);}
        return false;
    }

    public function find_uuid_order($order_id, $site_id=site_id) {
        $exp_date = date("Y-m-d H:i:s");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
            order_uuid
            FROM
            acquiring
            WHERE
            order_id=:order_id AND 
            expiration_date>:expiration_date AND 
            site_id=:site_id
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':expiration_date', $exp_date, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($order = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $order["order_uuid"];
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/acquiring 25'/*.$e->getMessage()*/);}
        return false;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);

        $this->sberbank_acquiring_status=(int)$this->uFunc->getConf('sberbank_acquiring_status','uCat');

        if($this->sberbank_acquiring_status) {
            $this->sberbank = new sberbank($this->uCore);
            $this->sberbank();
        }
//        else if($this->bank == "paypal") {
//            $this->paypal = new paypal($this->uCore);
//            $this->paypal();
//        }
        else {
            exit;
        }
    }
}
new acquiring($this);