<?php
namespace uCat\checkout;
use PDO;
use PDOException;
use processors\uFunc;
use translator\translator;
use uCat\common as uCat;
use item_avatar;
use uCore;
use uDrive\common as uDrive;
use uString;

require_once 'uCat/classes/common.php';
require_once 'processors/classes/uFunc.php';
require_once 'uDrive/classes/common.php';
require_once 'uCat/classes/sberbank.php';
require_once "processors/uSes.php";

class order_info {
    public $q_items;
    public $uFunc;
    public $order;
    public $order_id;
    public $order_uuid;
    public $contractor;
    public $user_email;
    public $order_pay;
    public $use_uuid;
    public $not_pay;
    public $uCat;
    public $uDrive;
    public $avatar;
    public $order_has_real_items;
    public $order_has_e_items;
    public $bill_file;
    /**
     * @var translator
     */
    public $translator;
    private $order_status;
    public $uSes;
    private $uCore;
    private $sberbank;
    private function check_data() {
        $this->order_pay = false;
        $this->use_uuid = false;
        $this->not_pay = false;

        if(isset($this->uCore->url_prop[2])) {
            if(trim($this->uCore->url_prop[2])=="") unset($this->uCore->url_prop[2]);
        }

        $q_select_order_data="
                order_status,
                order_paid,
                user_id,
                user_name,
                user_phone,
                user_email,
                delivery_type,
                delivery_name,
                delivery_price,
                delivery_address,
                delivery_comment,
                customer_type,
                vat_number,
                tax_info_1,
                company_name,
                payment_method,
                order_timestamp,
                bill_number";

        if(isset($this->uCore->url_prop[1],$this->uCore->url_prop[2])) {
            $this->order_id = trim($this->uCore->url_prop[1]);
            $this->user_email = trim($this->uCore->url_prop[2]);
            if (!uString::isDigits($this->order_id)) return false;
            if (!uString::isEmail($this->user_email)) return false;


            if(!$this->order=$this->uCat->order_id2data($this->order_id,$q_select_order_data)) return false;

            $this->order_status=$this->order->order_status;

            if(
               $this->order_status!=="items selected"&&
               $this->order_status!=="order is confirmed"&&
               $this->order_status!=="order is processed"&&
               $this->order_status!=="waiting payment"&&
               $this->order_status!=="order has been paid"&&
               $this->order_status!=="awaiting delivery"&&
               $this->order_status!=="order completed"&&
               $this->order_status!=="order canceled"
            ) return false;

            if($this->order->user_email!==$this->user_email) return false;

            $this->check_order_status();

            return true;
        }//По уникальной ссылке
        elseif(isset($this->uCore->url_prop[1])) {
            if (strlen($this->uCore->url_prop[1]) >= 36) {
                // 0d3fead8-c68b-7987-0d3f-ead804b1d67d
                $response = explode("?", $this->uCore->url_prop[1]);
                $order_uuid = $response[0];
                if (count(explode("-", $order_uuid)) === 5) {
                    $this->use_uuid = true;

                    if ($acquiring_order_data = $this->uCat->acquiring_security_uuid2data($order_uuid, "
                    order_id,
                    UNIX_TIMESTAMP(expiration_date) AS expiration_date 
                    ")) {
                        $this->order_id = (int)$acquiring_order_data->order_id;

//                        if($acquiring_order_data->expiration_date<=time()) return false;

                        if (!$this->order = $this->uCat->order_id2data($this->order_id, $q_select_order_data)) return false;

                        $this->order_status = $this->order->order_status;

                        if (
                            //$this->order_status !== "items selected" &&
//                            $this->order_status !== "order is confirmed" &&
                            $this->order_status !== "order is processed" &&
                            $this->order_status !== "waiting payment" &&
                            $this->order_status !== "order has been paid"// &&
//                            $this->order_status !== "awaiting delivery" &&
//                            $this->order_status !== "order completed" &&
//                            $this->order_status !== "order canceled"
                        ) return false;

                        if($this->order_status==="waiting payment") $this->uCat->notify_about_order_change($this->order_id,"order has been paid",$this->order_status);

                        $this->uCat->order_update($this->order_id, [
                            ['order_paid', 1, PDO::PARAM_INT],
                            ['order_status', "order has been paid", PDO::PARAM_STR]
                        ],"",site_id,1588599151);

                        return $this->order_pay = true;
                    }
                    else {
                        if (!$acquiring_order_data = $this->uCat->acquiring_not_pay_key2data($order_uuid, "
                        order_id,
                        UNIX_TIMESTAMP(expiration_date) AS expiration_date
                        ")) return false;

                        $this->order_id = (int)$acquiring_order_data->order_id;

//                        if($acquiring_order_data->expiration_date<=time()) return false;

                        if (!$this->order = $this->uCat->order_id2data($this->order_id, $q_select_order_data)) return false;

                        $this->not_pay = true;
                        $this->check_order_status();

                        return 1;
                    }
                }
            }
            elseif ($this->uSes->access(2)) {
                $this->order_id = trim($this->uCore->url_prop[1]);
                if (!uString::isDigits($this->order_id)) return false;

                if (!$this->order = $this->uCat->order_id2data($this->order_id, $q_select_order_data)) return false;


                if ((int)$this->order->user_id !== $this->uSes->get_val("user_id")&&!$this->uSes->access(25)) return false;

                $this->order_status = $this->order->order_status;

                if ($this->order_status === "new"||$this->order_status === "items selected") {
                    header("location: /uCat/cart?order_id=".$this->order_id);
                    exit;
                }
                elseif (
                    $this->order_status !== "order is confirmed" &&
                    $this->order_status !== "order is processed" &&
                    $this->order_status !== "waiting payment" &&
                    $this->order_status !== "order has been paid" &&
                    $this->order_status !== "awaiting delivery" &&
                    $this->order_status !== "order completed" &&
                    $this->order_status !== "order canceled"
                ) return false;

                $this->check_order_status();
                return true;
            }//Из личного кабинета для авторизованных
        }

        return 0;
    }

    private function check_order_status() {
        if ($this->order_status === "waiting payment") {
            $data = array(
                'orderNumber' => $this->order_id
            );
            $status_extended = $this->sberbank->order_status_extended($data);

            if ($status_extended->errorCode == 0 && $status_extended->orderStatus == 6) {
                $this->order_status = "order is processed";
                $this->uCat->order_update($this->order_id,$update_ar=[
                    ['order_status',$this->order_status ,PDO::PARAM_STR]
                ],"",site_id,1588599158);
            }
            else if ($status_extended->errorCode == 0 && $status_extended->orderStatus == 2) {
                $this->order_status="order has been paid";
                $this->uCat->order_update($this->order_id,$update_ar=[
                    ['order_paid',1 ,PDO::PARAM_INT],
                    ['order_status',$this->order_status ,PDO::PARAM_STR]
                ],"",site_id,1588599169);
                $this->order_pay = true;
            }
        }
        else if ($this->order_status === "order has been paid") $this->order_pay = true;

        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uCat = new uCat($this->uCore);
        $this->uFunc = new uFunc($this->uCore);
        $this->uDrive=new uDrive($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->sberbank=new \sberbank($this->uCore);

        require_once "translator/translator.php";
        $this->translator=new translator(site_lang,"uCat/order_info.php");

        if($this->check_data()) {
            require_once 'uCat/inc/item_avatar_new.php';
            $this->avatar = new item_avatar($this->uCore);

            $stm=$this->uCat->get_order_items($this->order_id,"
            orders_items.item_id,
            var_id,
            item_count,
            item_article_number,
            orders_items.item_title,
            orders_items.item_price,
            orders_items.item_type,
            orders_items.file_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_items=$stm->fetchAll(PDO::FETCH_OBJ);

            $this->uFunc->incJs("/uCat/js/order_info.min.js", 1);
            $this->uFunc->incCss("/uCat/css/cart.min.css");

            $this->order->delivery_type=(int)$this->order->delivery_type;
            $this->order->delivery_price=(float)$this->order->delivery_price;
            $this->order->payment_method=(int)$this->order->payment_method;
            $this->order_has_real_items=$this->uCat->order_has_real_items($this->order_id);
            $this->order_has_e_items=$this->uCat->order_has_e_items($this->order_id);
            $this->order->tax_info_1=(int)$this->order->tax_info_1;
            $this->order->user_id=(int)$this->order->user_id;

            if($this->order->payment_method===3) {
                if (!$this->bill_file = u_sroot.$this->uFunc->bill_number2file_path($this->order->bill_number)) $this->bill_file=0;
            }
        }
        else unset($this->order,$this->order_id);
    }
}
$uCat=new order_info($this);

ob_start();?>
    <h1 class="page-header">Информация о заказе <?=isset($uCat->order_id)?("#".$uCat->order_id):''?> <?=$uCat->uSes->access(25)&&$uCat->order->order_status!="order canceled"&&$uCat->order->order_status!="order completed"?('<a href="/uCat/cart?order_id='.$uCat->order_id.'" class="btn btn-default">Изменить</a>'):''?></h1>
<?
if(isset($uCat->order)) {
?>
    <div class="jumbotron">
    <?
    if($uCat->order->order_status=="items selected") {
        if($uCat->uSes->access(25)) {?>
            <h3>Клиент еще оформляет заказ. <small><button class="btn btn-default" onclick="document.location=document.location">Обновить</button></small></h3>
            <p>Вы можете помочь ему оформить этот заказ.</p>
            <p><?if($uCat->order->user_id){?><a href="<?=u_sroot?>uAuth/profile/<?=$uCat->order->user_id?>"><?}?><?=$uCat->order->user_name?><?if($uCat->order->user_id){?></a><?}?> <a href="tel:<?=$uCat->order->user_phone?>"><?=$uCat->order->user_phone?></a> <a href="mailto:<?=$uCat->order->user_email?>"><?=$uCat->order->user_email?></a></p>
            <?if(!$uCat->order_has_real_items){?><p>В заказе только электронные товары</p><?}?>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
        else {?>
            <h3>Заказ оформлен</h3>
            <p>Ваш заказ проверяет оператор. При необходимости он свяжется с вами для уточнения деталей оплаты и получения заказа.</p>
            <p>На email <?=$uCat->order->user_email?> отправлена информация о заказе.</p>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
    }
    elseif($uCat->order->order_status=="order is confirmed") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ требует подтверждения</h3>
            <p>Проверьте состав заказа, детали оплаты и получения. При необходимости свяжитесь с клиентом.</p>
            <p><?if($uCat->order->user_id){?><a href="<?=u_sroot?>uAuth/profile/<?=$uCat->order->user_id?>"><?}?><?=$uCat->order->user_name?><?if($uCat->order->user_id){?></a><?}?> <a href="tel:<?=$uCat->order->user_phone?>"><?=$uCat->order->user_phone?></a> <a href="mailto:<?=$uCat->order->user_email?>"><?=$uCat->order->user_email?></a></p>
            <p><button class="btn btn-primary btn-lg" onclick="uCat_order_info.change_status_confirm('processed')">Заказ проверен</button></p>
            <?if($uCat->order->payment_method===0){?><p>Оплата заказа будет наличными, поэтому его нужно подготовить к вручению</p><?}
            elseif($uCat->order->payment_method===1){?><p>Оплата заказа будет картой, поэтому его нужно подготовить к вручению</p><?}
            elseif($uCat->order->payment_method===2){?><p>Оплата заказа будет картой онлайн на сайте - нужно дождаться оплаты. Как придут деньги - система вас уведомит</p><?}
            elseif($uCat->order->payment_method===3){?><p>Оплата заказа будет по счету от юр.лица - нужно проверить поступление средств на расчетный счет</p><?}
            if($uCat->order->payment_method===0||$uCat->order->payment_method===1) {
                if ($uCat->order->delivery_type === 0) {?>
                    <p>Если заказ готов к получению, то можно сразу уведомить об этом клиента</p>
                    <p><button class="btn btn-primary btn-lg" onclick="uCat_order_info.change_status_confirm('delivering')">Готов к получению</button></p>
                <?}
                if ($uCat->order->delivery_type === 1||$uCat->order->delivery_type === 2) {?>
                    <p>Если заказ уже передан в доставку, то можно сразу уведомить об этом клиента</p>
                    <p><button class="btn btn-primary btn-lg" onclick="uCat_order_info.change_status_confirm('delivering')">Передан в доставку</button></p>
                <?}
            }?>
            <p>Если планируется предоплата другими способами, которых нет на сайте, то на этом этапе нужно ее получить с клиента, а потом уже подтверждать заказ</p>
            <?if(!$uCat->order_has_real_items){?><p>В заказе только электронные товары</p><?}?>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
        else {?>
            <h3>Заказ оформлен</h3>
            <p>Ваш заказ проверяет оператор. При необходимости он свяжется с вами для уточнения деталей оплаты и получения заказа.</p>
            <p>На email <?=$uCat->order->user_email?> отправлена информация о заказе.</p>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
    }
    elseif($uCat->order->order_status=="order is processed"||$uCat->order->order_status=="waiting payment") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ подтвержден</h3>
            <p><?if($uCat->order->user_id){?><a href="<?=u_sroot?>uAuth/profile/<?=$uCat->order->user_id?>"><?}?><?=$uCat->order->user_name?><?if($uCat->order->user_id){?></a><?}?> <a href="tel:<?=$uCat->order->user_phone?>"><?=$uCat->order->user_phone?></a> <a href="mailto:<?=$uCat->order->user_email?>"><?=$uCat->order->user_email?></a></p>
            <?if($uCat->order->payment_method===0){?><p>Оплата заказа будет наличными, поэтому его нужно подготовить к вручению</p><?}
            elseif($uCat->order->payment_method===1){?><p>Оплата заказа будет картой, поэтому его нужно подготовить к вручению</p><?}
            elseif($uCat->order->payment_method===2){?><p>Оплата заказа будет картой онлайн на сайте - нужно дождаться оплаты. Как придут деньги - система вас уведомит</p><?
                if($uCat->order->order_status==="waiting payment") {?>
                    <p><button class="btn btn-primary" onclick="uCat_order_info.order_status(<?=isset($uCat->order_id)?($uCat->order_id):''?>, '<?=$uCat->order->user_email?>')">Проверить статус оплаты</button></p>
                <?}
            }
            elseif($uCat->order->payment_method===3){?><p>Оплата заказа будет по счету от юр.лица - нужно проверить поступление средств на расчетный счет</p><p><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('paid')">Оплата пришла</button></p><?}
            if($uCat->order->payment_method===0||$uCat->order->payment_method===1) {
                if ($uCat->order->delivery_type === 0) {?>
                    <p>Если заказ готов к получению, то можно сразу уведомить об этом клиента</p>
                    <p><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('delivering')">Готов к получению</button></p>
                <?}
                if ($uCat->order->delivery_type === 1||$uCat->order->delivery_type === 2) {?>
                    <p>Если заказ уже передан в доставку, то можно сразу уведомить об этом клиента</p>
                    <p><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('delivering')">Передан в доставку</button></p>
                <?}
            }?>
            <p>Если планируется предоплата другими способами, которых нет на сайте, то на этом этапе нужно ее получить с клиента, а потом уже передавать клиенту</p>
            <?if(!$uCat->order_has_real_items){?><p>В заказе только электронные товары</p><?}?>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
        else {?>
            <h3>Заказ подтвержден</h3>
            <?if($uCat->order->payment_method===2){?><div id="div-payment"><button class="btn btn-primary btn-lg" onclick="uCat_order_info.create_order_buy(<?=isset($uCat->order_id)?($uCat->order_id):''?>, '<?=$uCat->order->user_email?>')">Оплатить онлайн сейчас</button></div><?}
            elseif($uCat->order->payment_method===3){?><div><a class="btn btn-lg btn-primary" href="<?=$uCat->bill_file?>">Скачать счет на оплату</a></div><?}
            elseif($uCat->order->payment_method===0||$uCat->order->payment_method===1) {
                if ($uCat->order->delivery_type === 0) {?>
                    <p>Мы вас уведомим, когда заказ будет готов к выдаче</p>
                <?}
                if ($uCat->order->delivery_type === 1||$uCat->order->delivery_type === 2) {?>
                    <p>Мы вас уведомим, когда заказ будет передан в доставку</p>
                <?}
            }?>
            <?if($uCat->order_has_e_items){?><p>Электронные товары будут доступны для скачивания сразу после оплаты заказа</p><?}?>
        <?}
    }
    elseif($uCat->order->order_status=="order has been paid") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ оплачен</h3>
            <p><?if($uCat->order->user_id){?><a href="<?=u_sroot?>uAuth/profile/<?=$uCat->order->user_id?>"><?}?><?=$uCat->order->user_name?><?if($uCat->order->user_id){?></a><?}?> <a href="tel:<?=$uCat->order->user_phone?>"><?=$uCat->order->user_phone?></a> <a href="mailto:<?=$uCat->order->user_email?>"><?=$uCat->order->user_email?></a></p>
                <?if($uCat->uCat->site_has_delivery_types()&&$uCat->order_has_real_items) {
                if ($uCat->order->delivery_type === 0) { ?>
                    <p>Если заказ готов к получению, то можно сразу уведомить об этом клиента</p>
                    <p>
                        <button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('delivering')">
                            Готов к получению
                        </button>
                    </p>
                <?
                }
                if ($uCat->order->delivery_type === 1 || $uCat->order->delivery_type === 2) { ?>
                    <p>Если заказ уже передан в доставку, то можно сразу уведомить об этом клиента</p>
                    <p>
                        <button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('delivering')">
                            Передан в доставку
                        </button>
                    </p>
                <?
                }
            }?>
            <p>Если заказ выполнен, то просто нажмите на следующую кнопку <button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('complete')">Заказ выполнен</button></p>
            <?if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}?>
        <?}
        else {?>
            <h3>Заказ оплачен</h3>
                <?
            if($uCat->uCat->site_has_delivery_types()&&$uCat->order_has_real_items) {
                if ($uCat->order->delivery_type === 0) { ?><p>Мы вас уведомим, когда заказ будет готов к выдаче</p><?}
                if ($uCat->order->delivery_type === 1 || $uCat->order->delivery_type === 2) { ?><p>Мы вас уведомим, когда заказ будет передан в доставку</p><?}
            }
            if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}
        }
    }
    elseif($uCat->order->order_status=="awaiting delivery") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ готов к получению</h3>
            <p><?if($uCat->order->user_id){?><a href="<?=u_sroot?>uAuth/profile/<?=$uCat->order->user_id?>"><?}?><?=$uCat->order->user_name?><?if($uCat->order->user_id){?></a><?}?> <a href="tel:<?=$uCat->order->user_phone?>"><?=$uCat->order->user_phone?></a> <a href="mailto:<?=$uCat->order->user_email?>"><?=$uCat->order->user_email?></a></p>
            <p>Если заказ выполнен, то просто нажмите на следующую кнопку <button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('complete')">Заказ выполнен</button></p>
            <?if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}?>
        <?}
        else {?>
            <h3>Заказ готов к получению</h3>
                <?
            if($uCat->uCat->site_has_delivery_types()&&$uCat->order_has_real_items) {
                if ($uCat->order->delivery_type === 0) { ?><p>Заказ готов к получению. Можете забирать</p><?}
                if ($uCat->order->delivery_type === 1 || $uCat->order->delivery_type === 2) { ?><p>Заказ передан в службу доставки</p><?}
            }
            if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}
        }
    }
    elseif($uCat->order->order_status=="order completed") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ выполнен</h3>
            <?if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}?>
        <?}
        else {?>
            <h3>Заказ выполнен</h3>
            <p>Благодарим за сотрудничество. Ждем вас снова</p>
            <?if($uCat->order_has_e_items){?><p>Электронные товары доступны для скачивания в списке товаров</p><?}?>
        <?}
    }
    elseif($uCat->order->order_status=="order canceled") {
        if($uCat->uSes->access(25)) {?>
            <h3>Заказ отменен</h3>
        <?}
        else {?>
            <h3>Заказ отменен</h3>
        <?}
    }
    ?>
    </div>
        <div class="highlight">
            <div class="container-fluid">
                <div class="row">
                    <?if(
                            ($uCat->uCat->site_has_delivery_types()&&$uCat->order_has_real_items)||
                            $uCat->order_has_e_items) { ?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="col-xs-4 text-muted">Способ получения:</div>
                            <div class="col-xs-8"><?
                                if ($uCat->order_has_real_items&&$uCat->uCat->site_has_delivery_types()) { ?>
                                        <p><?= $uCat->order->delivery_name ?></p>
                                    <?
                                }
                                if ($uCat->order_has_e_items) {
                                    ?>
                                    <p>Электронные товары скачиваются по ссылке</p>
                                <?
                                } ?>
                            </div>
                        </div>
                    <?}
                    if($uCat->order->delivery_price) {?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="col-xs-4 text-muted">Стоимость <?=$uCat->order->delivery_type?'доставки':"получения"?>:</div>
                            <div class="col-xs-8"><?=$uCat->order->delivery_price?></div>
                        </div>
                    <?}
                    if($uCat->order_has_real_items&&($uCat->order->delivery_type===1||$uCat->order->delivery_type===2)) {?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Адрес доставки:</div>
                        <div class="col-xs-8"><?=$uCat->order->delivery_address?></div>
                    </div>
                        <?if($uCat->order->delivery_comment!=="") {?>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Комментарий:</div>
                        <div class="col-xs-8"><?=nl2br(htmlspecialchars($uCat->order->delivery_comment))?></div>
                    </div>
                            <?}?>
                    <?}?>

                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Оплата:</div>
                        <div class="col-xs-8"><?
                        if($uCat->order->payment_method===0) {?> <p>Наличными</p><?}
                        elseif($uCat->order->payment_method===1) {?> <p>Картой</p><?}
                        elseif($uCat->order->payment_method===2) {?> <p>Картой на сайте</p><?}
                        elseif($uCat->order->payment_method===3) {?> <p>Оплата по счету<br><?=$uCat->bill_file?('<a href="'.$uCat->bill_file.'">'):''?>Счет № <?=$uCat->order->bill_number?><?=$uCat->bill_file?'</a>':''?></p><?}?>
                        </div>
                    </div>


                    <?if($uCat->order->customer_type==1) {?>
                        <div class="col-sm-6 col-xs-12">
                            <div class="col-xs-4 text-muted">Компания: </div>
                            <div class="col-xs-8"><?=$uCat->order->company_name?></div>

                            <div class="col-xs-4 text-muted">ИНН: </div>
                            <div class="col-xs-8"><?=$uCat->order->vat_number?></div>

                            <?if($uCat->order->tax_info_1!==0) {?>
                            <div class="col-xs-4 text-muted">КПП: </div>
                            <div class="col-xs-8"><?=$uCat->order->tax_info_1?></div>
                            <?}?>
                        </div>
                    <?}?>
                </div>

                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Покупатель:</div>
                        <div class="col-xs-8"><?=$uCat->order->user_name?></div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Телефон:</div>
                        <div class="col-xs-8"><?
                            if($uCat->uSes->access(2)) echo $uCat->order->user_phone;
                            else echo uString::hide_phone_part($uCat->order->user_phone)?></div>

                        <div class="col-xs-4 text-muted">E-mail:</div>
                        <div class="col-xs-8"><?
                            if($uCat->uSes->access(2)) echo $uCat->order->user_email;
                            else echo uString::hide_email_part($uCat->order->user_email);
                            ?></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-xs-4 text-muted">Статус заказа</div>
                        <div class="col-xs-8"><?
                                if($uCat->order->order_status=='items selected') echo 'Заказ оформляется';
                                elseif($uCat->order->order_status=='order is confirmed') echo 'Заказ оформлен';
                                elseif($uCat->order->order_status=='order is processed') echo 'Заказ подтвержден оператором';
                                elseif($uCat->order->order_status=='order has been paid') echo 'Заказ оплачен';
                                elseif($uCat->order->order_status=='awaiting delivery') echo 'Ожидается доставка/получение';
                                elseif($uCat->order->order_status=='order completed') echo 'Заказ завершен';
                                elseif($uCat->order->order_status=='order canceled') echo 'Заказ отменен';
                                elseif($uCat->order->order_status=='waiting payment') echo 'Заказ ожидает оплаты';
                                ?></div>
                    </div>
                </div>
            </div>
        </div>

    <div>
        <div class="container-fluid">
            <div class="items_group">
                <div class="row order_info_table_header">
                    <div class="col-lg-7 col-md-7 col-sm-6 col-xs-6 text-muted">&nbsp;</div>
                    <div class="col-lg-1 col-md-1 col-sm-2 col-xs-2 text-muted">Цена</div>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 text-muted">Количество</div>
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 text-muted">Сумма</div>
                </div>
                <?
                $total_price=0;
                /** @noinspection PhpUndefinedMethodInspection */
                $items_count=count($uCat->q_items);
                for($i=0;$i<$items_count;$i++) {
                    $item=$uCat->q_items[$i];
                    $item_data=$uCat->uCat->item_id2data($item->item_id,"item_img_time,item_url");
                    $item_id=(int)$item->item_id;
                    $item_title=uString::sql2text($item->item_title,1);
                    $item_count=(int)$item->item_count;

                    if((int)$item->var_id) {
                        $var_data=$uCat->uCat->var_id2data($item->var_id);
                        $item_img_time=$var_data->img_time;
                        $var_id=(int)$item->var_id;

                        $item_url=u_sroot."uCat/item/".$item->item_id."?var_id=".$item->var_id;
                        if($item_data->item_url!="") $item_url=u_sroot."uCat/item/".$item_data->item_url."?var_id=".$item->var_id;
                    }
                    else {
                        $item_img_time=(int)$item_data->item_img_time;
                        $var_id=0;
                        $item_url=u_sroot."uCat/item/".$item->item_id;
                        if($item_data->item_url!="") $item_url=u_sroot."uCat/item/".$item_data->item_url;
                    }
                    ?>
                    <section class="item_container">
                        <div class="item_section item_section_checkout_confirm row">


                            <div class="col-lg-2 col-md-2 col-sm-3 col-xs-12">
                                <a href="<?=$item_url?>">
                                    <img class="img-responsive item_img" src="<?=$uCat->avatar->get_avatar(640,$item_id,$item_img_time,$var_id)?>">
                                </a>
                            </div>


                            <div class="col-lg-5 col-md-5 col-sm-3 col-xs-12">
                                <h3 class="item_title">
                                <span>
                                    <a target="_blank" href="<?=$item_url?>"><?

                                        $enable_var_options=(int)$uCat->uFunc->getConf("enable_var_options","uCat");

                                        if($enable_var_options&&(int)$item->var_id) echo uString::sql2text($uCat->uCat->var_type_id2data($var_data->var_type_id)->var_type_title);
                                        else echo $item_title;

                                        ?></a>
                                </span>
                                </h3>
                                <?
                                if((int)$item->var_id&&!$enable_var_options){
                                    $var_type_id=$uCat->uCat->var_id2var_type_id($item->var_id);?>
                                <div class="text-info uCar_cart_item_var_type_title">
                                Тип:
                                    <?=uString::sql2text($uCat->uCat->var_type_id2data($var_type_id)->var_type_title)?>
                                </div>
                                <?}
                                if($uCat->uFunc->getConf("show_item_article_number","uCat")){?>
                                <div class="item_info">
                                    <div class="item_id">
                                        <span class="art_label">Артикул</span>
                                        <span class="num"><?
                                            if((int)$item->var_id) echo $var_data->item_article_number;
                                            else echo $item->item_article_number
                                        ?></span>
                                    </div>
                                </div>
                                <?}
                                if((int)$uCat->order->order_paid&&(int)$item->file_id) {?>
                                    <p>Это электронный товар.<br><a href="<?=$uCat->uCat->item_file_id2url($item->file_id)?>?download" target="_blank">Нажмите сюда, чтобы скачать его&nbsp;<span class="icon-download-cloud"></span></a></p>
                                <?}?>
                            </div>


                            <div class="col-lg-1 col-md-1 col-sm-2 col-xs-12 item_cost_container">
                            <span class="item_cost">
                                <span>
                                    <?
                                        $currency='р';
                                        if(site_id==54) {
                                            $currency='Eur';
                                        }
                                        print number_format($item->item_price,(count(explode('.',$item->item_price))>1?2:0),'.',' ');
                                    ?>
                                </span>
                                <span><?=$currency?></span>
                            </span>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 item_quantity_container">
                                <span class="item_quantity"><?=$item_count?></span>
                            </div>
                            <div class="col-lg-2 col-md-2 col-sm-2 col-xs-12 item_total_container">
                                <?$total_price+=$item->item_price*$item_count?>
                                <span class="item_total">
                                    <?
                                        print number_format($item->item_price*$item_count,(count(explode('.',$item->item_price*$item_count))>1?2:0),'.','&nbsp;');
                                    ?> <?=$currency?></span>
                            </div>

                        </div>
                    </section>
                <?}?>
            </div>

            <div class="pull-right uCat_cart_total">
                <div id="uCat_cart_total_delivery_price_container" class="<?=!(int)$uCat->order->delivery_price?"hidden":""?>">
                    <p><span class="uCat_cart_total_label">Сумма заказа:</span>
                        <span>
                <span id="uCat_cart_order_total_amount"><?=number_format($total_price,(count(explode('.',$total_price))>1?2:0),',','&nbsp;');?></span>
                            <?php
                            $currency='р';
                            if(site_id==54) {
                                $currency='Eur';
                            }
                            ?>
                <span><?=$currency?></span>
                </span></p>
                    <p><span class="uCat_cart_total_label">Доставка:</span>
                        <span>
                    <span id="uCat_delivery_total_amount"><?=number_format($uCat->order->delivery_price,(count(explode('.',$uCat->order->delivery_price))>1?2:0),',','&nbsp;');?></span>
                    <span><?=$currency?></span>
                </span></p>
                </div>

                <p><span class="uCat_cart_total_label">К оплате:</span>
                    <span>
                <span id="uCat_cart_total_amount"><?=number_format($total_price+$uCat->order->delivery_price,(count(explode('.',$total_price+$uCat->order->delivery_price))>1?2:0),',','&nbsp;');?></span>
                <span><?=$currency?></span>
                </span></p>
            </div>
            <!-- Модальное окно -->
            <div id="paymentAnswer" class="modal fade" role="dialog">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h4 class="modal-title" id="paymentAnswerLabel"></h4>
                        </div>
                        <div class="modal-body" id="paymentAnswerText"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                order_pay = '<?php echo $uCat->order_pay;?>';
                use_uuid = '<?php echo $uCat->use_uuid;?>';
                not_pay = '<?php echo $uCat->not_pay;?>';
                order_id = <?=$uCat->order_id?>;
            </script>
        </div>

        <?if($uCat->uSes->access(25)) {?>
        <?if(
                $uCat->order->order_status==="order is confirmed"||
                $uCat->order->order_status==="items selected"
            ) {?><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('processed')">Проверен</button><?}?>
        <?if(
                $uCat->order->order_status==="items selected"||
                $uCat->order->order_status==="order is processed"||
                $uCat->order->order_status==="order is confirmed"||
                $uCat->order->order_status==="waiting payment"
            ) {?><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('paid')">Оплачен</button><?}?>
        <?if(
                $uCat->order->order_status==="items selected"||
                $uCat->order->order_status==="order is confirmed"||
                $uCat->order->order_status==="order is processed"||
                $uCat->order->order_status==="order has been paid"||
                $uCat->order->order_status==="waiting payment"
            ) {?><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('delivering')">Передан в доставку/Готов в пункте выдачи</button><?}?>
        <?if(
                $uCat->order->order_status==="items selected"||
                $uCat->order->order_status==="order is confirmed"||
                $uCat->order->order_status==="order is processed"||
                $uCat->order->order_status==="order has been paid"||
                $uCat->order->order_status==="awaiting delivery"||
                $uCat->order->order_status==="waiting payment"
            ) {?><button class="btn btn-primary" onclick="uCat_order_info.change_status_confirm('complete')">Успешно выполнен</button><?}?>
        <?}
        if($uCat->uSes->access(2)&&$uCat->order->order_status!=="order is confirmed") {?><button class="btn btn-danger" onclick="uCat_order_info.change_status_confirm('cancel')">Отменить заказ</button><?}?>
    </div>

<?}
else {?>
    <div class="jumbotron">
        <h3>Заказ не найден</h3>
        <p class="help-block">Возможно заказ отображается только зарегистрированным пользователям. <a href="javascript:void(0)"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        <p><a href="<?=u_sroot?>uCat/what_is_with_my_order">Проверить другой заказ</a></p>
        <p><a href="<?=u_sroot?>uCat/sects">Перейти в каталог</a></p>
    </div>
<?}?>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
