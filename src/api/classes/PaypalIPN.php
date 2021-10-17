<?php
require_once "processors/classes/uFunc.php";

class PaypalIPN
{
    private $debug = true;
    private $projectName = "App";

    /**
     * @throws Exception
     */
    public function createIpnListener()
    {
        $postData = file_get_contents('php://input');
        $transactionType = $this->getPaymentType($postData);

        if ($transactionType == "web_accept") {
            $raw_post_data = file_get_contents('php://input');
            $raw_post_array = explode('&', $raw_post_data);
            $myPost = [];
            foreach ($raw_post_array as $keyval) {
                $keyval = explode('=', $keyval);
                if (count($keyval) == 2) {
                    $myPost[$keyval[0]] = urldecode($keyval[1]);
                }
            }

            // read the post from PayPal system and add 'cmd'
            $req = 'cmd=_notify-validate';
            if (function_exists('get_magic_quotes_gpc')) {
                $get_magic_quotes_exists = true;
            } else {
                $get_magic_quotes_exists = false;
            }

            foreach ($myPost as $key => $value) {
                if (
                    $get_magic_quotes_exists == true &&
                    get_magic_quotes_gpc() == 1
                ) {
                    $value = urlencode(stripslashes($value));
                } else {
                    $value = urlencode($value);
                }
                $req .= "&$key=$value";
            }

            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
            //$paypal_url = 'https://www.paypal.com/cgi-bin/webscr';

            // проверка подлинности IPN запроса
            $res = $this->sendRequest($paypal_url, $req);

            // Inspect IPN validation result and act accordingly
            // Split response headers and payload, a better way for strcmp
            $tokens = explode("\r\n\r\n", trim($res));
            $res = trim(end($tokens));

            if (strcmp($res, "VERIFIED") == 0) {
                // продолжаем обраюотку запроса
                $this->processPayment($myPost);
                $this->uFunc->journal("VERIFIED", 'evotor_documents');
                return true;
            } elseif (strcmp($res, "INVALID") == 0) {
                // запрос не прощел проверку
                $this->uFunc->journal("INVALID", 'evotor_documents');
                return false;
            }

            return false;
        } else {
            return false;
        }
    }

    private function validateTransaction($myPost, $order)
    {
        $valid = true;
        $site_id = site_id;
        $receiver_email = $this->uFunc->getConf(
            'conf_tab_acquiring email',
            'uCat',
            'return false',
            $site_id
        );
        /*
         * Проверка соответствия цен
         */
        if ($order->total_price != $myPost['mc_gross']) {
            $valid = false;
            $this->uFunc->journal(
                "PRICE NOT VERIFIED - " .
                    $myPost['mc_gross'] .
                    " and total price " .
                    $order->total_price,
                'evotor_documents'
            );
            $this->uFunc->journal(
                "order_status = " .
                    $order->order_status .
                    " total_price = " .
                    $order->total_price,
                'evotor_documents'
            );
        } /*
         * Проверка на нулевую цену
         */ elseif ($myPost['mc_gross'] == 0) {
            $valid = false;
        } /*
         * Проверка статуса платежа
         */ elseif ($myPost['payment_status'] !== 'Completed') {
            $valid = false;
            $this->uFunc->journal(
                "STATUS NOT COMPLETED - " . $myPost['payment_status'],
                'evotor_documents'
            );
        } /*
         * Проверка получателя платежа
         */ elseif ($myPost['receiver_email'] != $receiver_email) {
            $valid = false;
            $this->uFunc->journal(
                "EMAIL NOT VERIFIED - " . $myPost['receiver_email'],
                'evotor_documents'
            );
        } /*
         * Проверка валюты
         */ elseif ($myPost['mc_currency'] != 'RUB') {
            $valid = false;
            $this->uFunc->journal(
                "CURRENCY COD NOT VERIFIED - " . $myPost['mc_currency'],
                'evotor_documents'
            );
        }

        return $valid;
    }

    private function processPayment($myPost)
    {
        $order = $this->getOrderById($myPost['item_number']);
        if ($order->order_status !== "order has been paid") {
            // проводим валидацию транзакции
            if ($this->validateTransaction($myPost, $order)) {
                // оплата прошла успешно.
                // Обновить статус заказа
                $this->set_order_status(
                    $myPost['item_number'],
                    "order has been paid"
                );
                return true;
            } else {
                // платеж не прошел валидацию. Необходимо проверить вручную
                return false;
            }
        } else {
            return false;
        }
    }

    private function set_order_status($order_id, $status, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
            orders 
            SET 
            order_status=:order_status 
            WHERE  
            order_id=:order_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':order_status', $status, PDO::PARAM_STR);
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {
            $this->uFunc->error('10' /*.$e->getMessage()*/);
        }
    }

    private function getOrderById($orderId, $site_id = site_id)
    {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            order_status,
            total_price
            FROM
            orders
            WHERE
            order_id=:order_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':order_id', $orderId, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();

            if ($order = $stm->fetch(PDO::FETCH_OBJ)) {
                return $order;
            } else {
                return false;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('20' /*.$e->getMessage()*/);
        }

        return false;
    }

    private function sendRequest($paypal_url, $req)
    {
        $debug = $this->debug;

        $ch = curl_init($paypal_url);
        if ($ch == false) {
            return false;
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        if ($debug == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);

        //передаем заголовок, указываем User-Agent - название нашего приложения. Необходимо для работы в live режиме
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Connection: Close',
            'User-Agent: ' . $this->projectName,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
    }

    public function getPaymentType($rawPostData)
    {
        $post = $this->getPostFromRawData($rawPostData);

        if (isset($post['subscr_id'])) {
            return "subscr_payment";
        } else {
            return "web_accept";
        }
    }

    public function getPostFromRawData($raw_post_data)
    {
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = [];
        foreach ($raw_post_array as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }

        return $myPost;
    }

    public function get_post_data()
    {
        $raw_post_data = file_get_contents('php://input');
        return json_encode($raw_post_data, JSON_UNESCAPED_UNICODE);
    }

    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);
    }
}
