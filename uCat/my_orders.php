<?php
namespace uCat\checkout;

use processors\uFunc;
use uSes;

require_once 'processors/uSes.php';

class my_orders {
    private $uFunc;
    public $uSes;
    private $uCore;
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);

        if($this->uSes->access(2)) {
            $this->uFunc=new uFunc($this->uCore);
            $this->uFunc->incJs("/uCat/js/my_orders.min.js");
        }
    }
}
$uCat=new my_orders($this);
ob_start();
if($uCat->uSes->access(2)) {?>
    <div>
        <div role="group" id="uCat_my_orders_status_btns">
            <?if($uCat->uSes->access(25)) {?>
            <button onclick="uCat_my_orders.get_orders('new')"                  id="uCat_status_btn_new" type="button"                      class="btn btn-default btn-link active">Новые</button>
            <button onclick="uCat_my_orders.get_orders('unconfirmed')"          id="uCat_status_btn_unconfirmed" type="button"              class="btn btn-default btn-link">На проверке</button>
            <button onclick="uCat_my_orders.get_orders('awaiting payment')"     id="uCat_status_btn_awaiting_payment" type="button"         class="btn btn-default btn-link">В оплате</button>
            <button onclick="uCat_my_orders.get_orders('preparing')"            id="uCat_status_btn_preparing" type="button"                class="btn btn-default btn-link">В сборке</button>
            <button onclick="uCat_my_orders.get_orders('awaiting delivery')"    id="uCat_status_btn_awaiting_delivery" type="button"        class="btn btn-default btn-link">В доставке</button>
            <button onclick="uCat_my_orders.get_orders('awaiting pickup')"      id="uCat_status_btn_awaiting_pickup" type="button"          class="btn btn-default btn-link">В выдаче</button>
            <button onclick="uCat_my_orders.get_orders('completed')"            id="uCat_status_btn_completed" type="button"                class="btn btn-default btn-link">Завершенны</button>
            <button onclick="uCat_my_orders.get_orders('canceled')"             id="uCat_status_btn_canceled" type="button"                 class="btn btn-default btn-link">Отмененны</button>
            <?} else {?>
                <button onclick="uCat_my_orders.get_orders('current')" id="uCat_status_btn_current" type="button" class="btn btn-default active">Текущие</button>
                <button onclick="uCat_my_orders.get_orders('completed')" id="uCat_status_btn_canceled" type="button" class="btn btn-default">Завершенные</button>
            <?}?>
        </div>
        <h1 class="page-header">Мои заказы</h1>
        <div id="uCat_my_orders_list">Загрузка...</div>
    </div>
<?}
else {?>
    <div class="jumbotron">
        <h1 class="page-header">Мои заказы</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
 <?}

$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
