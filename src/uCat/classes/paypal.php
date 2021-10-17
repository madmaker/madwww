<?php
require_once "processors/classes/uFunc.php";

class paypal {
    public $site_id, $receiver_email, $security_key, $not_pay_key, $timestamp, $action_url, $currency_code, $location, $exp_date, $order_uuid;

    private $uCore, $uFunc, $debug=false;

    public function create_vars() {
        $this->site_id = site_id;
        $this->receiver_email = $this->uFunc->getConf('conf_tab_acquiring email','uCat','return false',$this->site_id);

        $date_ant_time = time();

        $minutes = (int)$this->uFunc->getConf('conf_tab_acquiring exp_date','uCat','return false',$this->site_id);
        $minutes = $minutes*60;
        $this->exp_date = $date_ant_time + $minutes;

        $this->security_key = $this->uFunc->generate_uuid();
        $this->not_pay_key = $this->uFunc->generate_uuid();
        $this->order_uuid = $this->uFunc->generate_uuid();
        $this->timestamp = time();
        $this->currency_code = "RUB";
        $this->location = "RU";

        if($this->debug) {
            $this->action_url = "https://www.sandbox.paypal.com/cgi-bin/webscr"; //Тестовый сервер
        }
        else {
            $this->action_url = "https://www.paypal.com/cgi-bin/webscr"; //Боевой сервер
        }
    }

    public function save_order_uuid_and_exp_date($order_id, $order_uuid, $exp_date, $site_id=site_id) {
        try {
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

            $stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            $stm->bindParam(':timestamp', $this->timestamp, PDO::PARAM_INT);
            $stm->bindParam(':order_uuid', $order_uuid, PDO::PARAM_STR);
            $stm->bindParam(':security_uuid', $this->security_key, PDO::PARAM_STR);
            $stm->bindParam(':not_pay_key', $this->not_pay_key, PDO::PARAM_STR);
            $stm->bindParam(':expiration_date', $exp_date, PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/paypal 10'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);
        $this->create_vars();
    }
}