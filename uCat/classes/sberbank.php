<?php
require_once "processors/classes/uFunc.php";

class sberbank {
    public $site_id, $login, $passwd, $token, $order_uuid, $url;

    private $uCore, $uFunc, $security_key, $not_pay_key, $timestamp;


    public function check_order($data) {
        // Регистрация заказа
        $date_ant_time = time();

        $minutes = (int)$this->uFunc->getConf('conf_tab_acquiring exp_date','uCat');
        $minutes = $minutes*60;
        $result_time = $date_ant_time + $minutes;

        $this->security_key = $this->uFunc->generate_uuid();
        $this->not_pay_key = $this->uFunc->generate_uuid();
        $this->timestamp = time();

        $register = array(
            'userName' => urlencode($this->login),
            'password' => urlencode($this->passwd),
            'orderNumber' => urlencode($data["orderNumber"])."/".$this->timestamp,
            'amount' => (int)$data["amount"],
            'returnUrl' => urlencode(u_sroot.'/uCat/order_info/'.$this->security_key),
            'failUrl' => urlencode(u_sroot.'/uCat/order_info/'.$this->not_pay_key),
            'expirationDate' => date("Y-m-d", $result_time)."T".date("H:i:s", $result_time)
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.'/payment/rest/register.do?userName='.$register["userName"].'&password='.$register["password"].'&orderNumber='.$register["orderNumber"].'&amount='.$register["amount"].'&returnUrl='.$register["returnUrl"].'&failUrl='.$register["failUrl"].'&expirationDate='.$register["expirationDate"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);
        curl_close($curl);
        $resultarr = json_decode($result);

        if(isset($resultarr->formUrl)) {
            $this->order_uuid = $resultarr->orderId;
            $done = array(
                'orderId' => $resultarr->orderId,
                'bank' => 'sberbank',
                'formUrl' => $resultarr->formUrl
            );
            $this->save_order_uuid_and_exp_date($register["orderNumber"], $this->order_uuid, date("Y-m-d H:i:s", $result_time));
            return json_encode($done, JSON_UNESCAPED_UNICODE);
        }
        else {
            $err = array(
                'errorCode' => $resultarr->errorCode,
                'errorMessage' => $resultarr->errorMessage,
                'place' => 0
            );
            return json_encode($err, JSON_UNESCAPED_UNICODE);
        }
    }

    private function save_order_uuid_and_exp_date($order_id, $order_uuid, $exp_date, $site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            acquiring (
            order_id,
            timestamp,
            order_uuid,
            security_uuid,
            not_pay_key,
            expiration_date,
            site_id
            ) VALUES (
            :order_id,
            :timestamp,
            :order_uuid,
            :security_uuid,
            :not_pay_key,
            :expiration_date,
            :site_id
            ) ON DUPLICATE KEY UPDATE 
            timestamp=:timestamp,
            order_uuid=:order_uuid,
            security_uuid=:security_uuid,
            not_pay_key=:not_pay_key,
            expiration_date=:expiration_date
            ");

            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':timestamp', $this->timestamp, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':order_uuid', $order_uuid, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':security_uuid', $this->security_key, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':not_pay_key', $this->not_pay_key, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':expiration_date', $exp_date, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/sberbank 10'/*.$e->getMessage()*/);}
    }

    public function cancel_payment($order_uuid) {
        // Отмена оплаты заказа
        $reverse = array(
            'userName' => urlencode($this->login),
            'password' => urlencode($this->passwd),
            'orderId' => trim($order_uuid)
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.'/payment/rest/reverse.do?userName='.$reverse["userName"].'&password='.$reverse["password"].'&orderId='.$reverse["orderId"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);
        curl_close($curl);

        $resultarr = json_decode($result);
        if(isset($resultarr["errorCode"])) {
            $err = array(
                'errorCode' => $resultarr["errorCode"],
                'errorMessage' => $resultarr["errorMessage"],
                'place' => 1
            );
            return json_encode($err);
        }
        else {
            return true;
        }
    }

    public function refund_order($data) {
        // Возврат средств оплаты заказа
        $refund = array(
            'userName' => urlencode($this->login),
            'password' => urlencode($this->passwd),
            'orderId' => $data["order_uuid"],
            'amount' => $data["amount"]
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.'/payment/rest/refund.do?userName='.$refund["userName"].'&password='.$refund["password"].'&orderId='.$refund["orderId"].'&amount='.$refund["amount"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);
        curl_close($curl);
        $resultarr = json_decode($result);
        if(isset($resultarr["errorCode"])) {
            $err = array(
                'errorCode' => $resultarr["errorCode"],
                'errorMessage' => $resultarr["errorMessage"],
                'place' => 2
            );
            return json_encode($err);
        }
        else {
            return true;
        }
    }

    public function order_status_extended($data) {
        // Расширенное Состояние заказа
        $orderstatusextended = array(
            'userName' => urlencode($this->login),
            'password' => urlencode($this->passwd),
            'orderNumber' => $data["orderNumber"]
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.'/payment/rest/getOrderStatusExtended.do?userName='.$orderstatusextended["userName"].'&password='.$orderstatusextended["password"].'&orderNumber='.$orderstatusextended["orderNumber"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);
        curl_close($curl);
        $resultarr = json_decode($result);

        return $resultarr;

//        {"errorCode":"0","errorMessage":"Успешно","orderNumber":"1421","orderStatus":2,"actionCode":0,"actionCodeDescription":"","amount":1040000,"currency":"643","date":1534842579271,"ip":"185.211.158.46","merchantOrderParams":[],"attributes":[{"name":"mdOrder","value":"a169a819-b051-7c3d-a169-a81904b1d67d"}],"cardAuthInfo":{"expiration":"201912","cardholderName":"CARDHOLDER NAME","approvalCode":"123456","pan":"555555XXXXXX5599"},"authDateTime":1534842689614,"terminalId":"123456","authRefNum":"111111111111","paymentAmountInfo":{"paymentState":"DEPOSITED","approvedAmount":1040000,"depositedAmount":1040000,"refundedAmount":0},"bankInfo":{"bankName":"SOME BANK IN USA","bankCountryCode":"US","bankCountryName":"Соединенные Штаты Америки"}}
    }

    public function check_card_3ds($data) {
        // Проверка вовлечённости карты в 3DS
        $threeds = array(
            'userName' => urlencode($this->login),
            'password' => urlencode($this->passwd),
            'pan' => $data["pan"]
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->url.'/payment/rest/verifyEnrollment.do?userName='.$threeds["userName"].'&password='.$threeds["password"].'&pan='.$threeds["pan"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);

        $result = curl_exec($curl);
        curl_close($curl);
        $resultarr = json_decode($result);
        if(isset($resultarr["errorCode"])) {
            $err = array(
                'errorCode' => $resultarr["errorCode"],
                'errorMessage' => $resultarr["errorMessage"],
                'enrolled' => $resultarr["enrolled"],
                'emitterName' => $resultarr["emitterName"],
                'emitterCountryCode' => $resultarr["emitterCountryCode"],
                'place' => 3
            );
            return json_encode($err);
        }
        else {
            return true;
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);

        $sberbank_acquiring_status=(int)$this->uFunc->getConf('sberbank_acquiring_status','uCat');
        if($sberbank_acquiring_status===1) $this->url = "https://3dsec.sberbank.ru"; //Тестовый сервер
        elseif($sberbank_acquiring_status===2) $this->url = "https://securepayments.sberbank.ru"; //Боевой сервер
        else return 0;

        $this->login = $this->uFunc->getConf('conf_tab_acquiring login','uCat');
        $this->passwd = $this->uFunc->getConf('conf_tab_acquiring password','uCat');
        $this->token = $this->uFunc->getConf('conf_tab_acquiring token','uCat');
    }
}