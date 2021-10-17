<?php
namespace uCat\admin;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;

class get_my_orders_bg {
    public $uCat;
    private $status_query;
    public $q_orders;
    public $uSes;
    public $uFunc;

    private function check_data() {
        $isUCatManager=$this->uSes->access(25);
        if(!isset($_POST['status'])) {
            if($isUCatManager) {
                $_POST['status'] = "new";
            }
            else {
                $_POST['status'] = "current";
            }
        }
        $status=$_POST['status'];


        //delivery_type: 0 - самовывоз, 1,2 - доставка
        //payment_method: 0 - нал, 1 - картой при получении, 2 - картой онлайн, 3 - счет
        if($status === "current") {
            $this->status_query = "(
            order_status='order is confirmed' OR
            order_status='order is processed' OR
            order_status='waiting payment' OR
            order_status='order has been paid' OR
            order_status='awaiting delivery' OR
            order_status='new' OR
            order_status='items selected'
            )";
        }
        elseif($status==="unconfirmed"&&!$isUCatManager) {
            $this->status_query = "(
            order_status='order is confirmed'
            )";
        }
        elseif($status==="completed"&&!$isUCatManager) {
            $this->status_query = "(
            order_status='order completed' OR
            order_status='order canceled'
            )";
        }
        elseif($status==="new") {
            $this->status_query = "(
            order_status='order is confirmed' OR
            order_status='new' OR
            order_status='items selected'
            )";
        }
        elseif($status==="awaiting payment") {
            $this->status_query = "(
            (order_status='order is processed' AND (payment_method=2 OR payment_method=3)) OR
            order_status='waiting payment'
            )";
        }
        elseif($status==="preparing") {
            $this->status_query = "(
            (order_status='order is processed' AND (payment_method=0 OR payment_method=1)) OR
            order_status='order has been paid'
            )";
        }
        elseif($status==="awaiting delivery") {
            $this->status_query = "(
            order_status='awaiting delivery' AND (delivery_type=1 OR delivery_type=2)
            )";
        }
        elseif($status==="awaiting pickup") {
            $this->status_query = "(
            order_status='awaiting delivery' AND delivery_type=0
            )";
        }
        elseif($status==="completed"&&$isUCatManager) {
            $this->status_query = "(
            order_status='order completed' 
            )";
        }
        elseif($status==="canceled") {
            $this->status_query = "(
            order_status='order canceled'
            )";
        }

        else {
            if($isUCatManager) {
                $_POST["status"] = "new";
            }
            else {
                $_POST["status"] = "current";
            }

            $this->check_data();
        }
    }
    private function get_orders() {
        if($this->uSes->access(25)) {
            $q_user_id = "";
        }
        else {
            $q_user_id = 'user_id=:user_id AND';
        }
        try {
            $this->q_orders=$this->uFunc->pdo("uCat")->prepare("SELECT
            order_id,
            order_status,
            order_timestamp,
            user_id,
            user_name,
            user_phone,
            user_email,
            total_price,
            delivery_type,
            delivery_name,
            delivery_price,
            delivery_address,
            delivery_comment,
            customer_type,
            company_name,
            payment_method,
            bill_number,
            admin_comment
            FROM 
            madmakers_uCat.orders 
            WHERE 
            ".$q_user_id."
            ".$this->status_query." AND
            user_name!='' AND
            site_id=:site_id
            ORDER BY order_id DESC
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            $this->q_orders->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            if(!$this->uSes->access(25)) {
                $this->q_orders->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            }
            $this->q_orders->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        if(!$this->uSes->access(2)) {
            die("<h3>В доступе отказано</h3>");
        }

        $this->uFunc=new uFunc($uCore);
        $this->uCat=new common($uCore);


        $this->check_data();
        $this->get_orders();
    }
}
$uCat=new get_my_orders_bg($this);
?>
<div class="input-group">
    <!--suppress HtmlFormInputWithoutLabel -->
    <input type="text" id="uCat_my_orders_filter" class="form-control" placeholder="Фильтр, например: Самовывоз или номер телефона, или статус заказ, или дата, или фамилия клиента" onkeyup="uCat_my_orders.filter_orders()">
    <span class="input-group-btn">
    <button class="btn btn-default" type="button"><span class="icon-search" onclick="uCat_my_orders.filter_orders()"></span></button>
</span>
</div>
<table class="table table-hover" id="uCat_my_orders_list">
    <tr>
        <th>Заказ</th>
        <th>Оплата</th>
        <th>Дата</th>
        <th>Статус</th>
        <th>Клиент</th>
        <th>Получение</th>
    </tr>
    <?php /** @noinspection PhpUndefinedMethodInspection */
    for($orders_found=0; $order=$uCat->q_orders->fetch(PDO::FETCH_OBJ);) {
        $order->customer_type=(int)$order->customer_type;
        $order->bill_number=(int)$order->bill_number;
        $orders_found=1;
        $order->payment_method=(int)$order->payment_method;

        $currency='р';
        if(site_id==54) {
            $currency='Eur';
        }
        //delivery_type: 0 - самовывоз, 1,2 - доставка
        ?>

        <tr>
            <td ><a href="/uCat/order_info/<?=$order->order_id?>" title="Открыть заказ">#<?=$order->order_id?></a></td>
            <td  style="; white-space: nowrap">
                <?=number_format($order->total_price,2,","," ")?> <?=$currency?><br>
                <?php
                if($order->payment_method===0) {
                    print 'Наличными';
                }
                elseif($order->payment_method===1) {
                    print 'Картой при получении';
                }
                elseif($order->payment_method===2) {
                    print 'Картой на сайте';
                }
                elseif($order->payment_method===3) {
                    print 'По счету';
                }
                if ($order->bill_number) { ?>
                    <a href="<?= u_sroot . $uCat->uFunc->bill_number2file_path($order->bill_number) ?>">Счет на оплату № <?= $order->bill_number ?></a>
                <?}?>
            </td>
            <td ><?=date('d.m.Y',$order->order_timestamp)?></td>
            <td ><?php
                if($order->order_status==="order is confirmed") {
                    echo "Оформлен";
                }
                elseif($order->order_status==="new"||$order->order_status==="items selected") {
                    echo "Оформляется";
                }
                elseif($order->order_status==="order is processed") {
                    echo "Подтвержден. Ожидается оплата";
                }
                elseif($order->order_status==="waiting payment") {
                    echo "Производится оплата";
                }
                elseif($order->order_status==="order has been paid") {
                    echo "Оплачен";
                }
                elseif($order->order_status==="awaiting delivery") {
                    echo "Передан в доставку";
                }
                elseif($order->order_status==="order completed") {
                    echo "Завершен";
                }
                elseif($order->order_status==="order canceled") {
                    echo "Отменен";
                }
                ?>
            </td>
            <td >
                <a href="/uAuth/profile/<?=$order->user_id?>"><span class="icon-user"></span></a> <?=$order->user_name?><br>
                <a href="tel:<?=$order->user_phone?>"><span class="icon-phone-1"></span></a> <?=$order->user_phone?><br>
                <a href="mailto:<?=$order->user_email?>"><span class="icon-mail-alt"></span></a><?=$order->user_email?><br>
                <?php if ($order->customer_type === 1) { print $order->company_name; }?>
            </td>
            <td >
            <?php
            if($uCat->uCat->order_has_real_items($order->order_id)&&$uCat->uCat->site_has_delivery_types()) {
                if($uCat->uCat->order_has_e_items($order->order_id)) {
                    print '<b>Есть электронные товары</b><br>';
                }
                $order->delivery_type=(int)$order->delivery_type;
                $order->delivery_price=(float)$order->delivery_price;

                print $order->delivery_name?><br>
                <?if($order->delivery_price){?>
                    <?=$order->delivery_price?> <?=$currency?><br>
                <?}?>
                <?if ($order->delivery_type) {
                    print 'Доставка <br>';
                    print $order->delivery_address;
                    print '<br>';
                    print htmlspecialchars(strip_tags($order->delivery_comment));
                    print '<br>';
                } else {
                    print 'Самовывоз<br>';
                }
            }
            else {
                print 'Только электронные товары';
            } ?>
            </td>

        </tr>

            <?php
            if($uCat->uSes->access(25)) {?>
                <tr>
               <td colspan="6" style="border-top:none;"><!--suppress HtmlFormInputWithoutLabel --><textarea id="admin_comment_<?=$order->order_id?>" onblur="uCat_my_orders.save_admin_comment(<?=$order->order_id?>)" placeholder="Комментарий" class="form-control" style="height: 100%"><?=$order->admin_comment?></textarea></td>
                </tr>
            <?}?>
    <?}
    if(!$orders_found) {?>
        <tr><td>Заказы не найдены</td></tr>
    <?}?>
</table>
