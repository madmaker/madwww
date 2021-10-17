<?php
use processors\uFunc;
require_once 'processors/classes/uFunc.php';
class cron_orders_cleaner {
    private $uCore,$secret,$uFunc;

    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uFunc->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uFunc->error(2);
    }
    private function clean() {
        try {
            $stm = $this->uFunc->pdo("uCat")->prepare("DELETE 
            FROM
            orders
            WHERE
            order_timestamp<:order_timestamp AND 
            (order_status=:order_status_a OR order_status=:order_status_b OR order_status=:order_status_c OR order_status=:order_status_d OR order_status=:order_status_e OR order_status=:order_status_f)
            ");

            $order_timestamp = time()-$this->order_lifetime;
            $order_status_a = "new";
            $order_status_b = "items selected";
            $order_status_c = "cart confirmed";
            $order_status_d = "logged in";
            $order_status_e = "contractor is selected";
            $order_status_f = "delivery is selected";

            $stm->bindParam(':order_timestamp', $order_timestamp, PDO::PARAM_INT);
            $stm->bindParam(':order_status_a', $order_status_a, PDO::PARAM_STR);
            $stm->bindParam(':order_status_b', $order_status_b, PDO::PARAM_STR);
            $stm->bindParam(':order_status_c', $order_status_c, PDO::PARAM_STR);
            $stm->bindParam(':order_status_d', $order_status_d, PDO::PARAM_STR);
            $stm->bindParam(':order_status_e', $order_status_e, PDO::PARAM_STR);
            $stm->bindParam(':order_status_f', $order_status_f, PDO::PARAM_STR);
            $stm->execute();

            return true;
        }
        catch(PDOException $e) {$this->uFunc->error(3/*.$e->getMessage()*/);}

        return false;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->secret='99PoP6hgjmiMnF';
        $this->order_lifetime=259200;//3 days
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->clean();
    }
}
new cron_orders_cleaner($this);