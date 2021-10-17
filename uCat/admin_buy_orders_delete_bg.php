<?php
class uCat_admin_buy_orders_delete_bg {
    private $uCore;
    private $status;

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die('forbidden');

        $this->checkData();
        $this->run();
    }
    private function checkData() {
        if(!isset($_POST['ids'],$_POST['action'])) $this->uCore->error(1);
        $action=&$_POST['action'];
        if($action=='delete') $this->status='deleted';
        else $this->status='';
    }
    private function update_orderStatus($ids_list) {
        if(!$this->uCore->query('uCat',"UPDATE
        `u235_buy_form_orders`
        SET
        `order_status`='".$this->status."'
        WHERE
        (".$ids_list.") AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(2);
    }

    private function run() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        for($i=1;$i<$ids_count;$i++) {
            $order_id=$idsAr[$i];
            if(!uString::isDigits($order_id)) $this->uCore->error(3);
            $ids_list.=" OR `order_id`='".$order_id."'";
        }
        //update status to new
        $this->update_orderStatus($ids_list);
        echo 'done';
    }
}
$uCat=new uCat_admin_buy_orders_delete_bg  ($this);
