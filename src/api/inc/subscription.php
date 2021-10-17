<?php
require_once "processors/classes/uFunc.php";
$uFunc = new \processors\uFunc($this);

header('Accept: application/json');
header('Content-Type: application/json');
$conn_token = "41e035ac-00f9-4deb-932f-f7871965de7b";
$authorization_header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
$authorization_header = str_replace("Bearer", '', $authorization_header);
$authorization_header = trim($authorization_header);

if ($this->url_prop[2] === "event" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($authorization_header === $conn_token) {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata, true);

        $subscriptionId = $postdata["subscriptionId"];
        $productId = $postdata["productId"];
        $userId = $postdata["userId"];
        $timestamp = $postdata["timestamp"];
        $sequenceNumber = $postdata["sequenceNumber"];
        $type = $postdata["type"];

        if ($type == "SubscriptionCreated") {
            // Новая подписка. Сообщает о том, что пользователь установил приложение в Личном кабинете. Приходит в начале пробного периода или перед сообщением об успешной оплате, если пробного периода нет.
            $planId = $postdata["planId"];
            $trialPeriodDuration = $postdata["trialPeriodDuration"];
            $deviceNumber = $postdata["deviceNumber"];

            http_response_code(200);
        } elseif ($type == "AddonsUpdated") {
            // список платных опций, выбранных пользователем.
            $addons = $postdata["addons"];
            $addons_id = $addons["id"];
            $addons_quantity = $addons["quantity"];

            http_response_code(200);
        } elseif ($type == "SubscriptionActivated") {
            // подписка активирована. Сообщает об успешной оплате.
            $nextBillingDate = $postdata["nextBillingDate"];
            $chargedSum = $postdata["chargedSum"];
            $chargedSum_amount = $chargedSum["amount"];
            $chargedSum_currency = $chargedSum["currency"];

            http_response_code(200);
        } elseif ($type == "SubscriptionRenewed") {
            // подписка продлена на следующий период. Сообщает об успешной оплате очередного периода.
            $nextBillingDate = $postdata["nextBillingDate"];
            $chargedSum = $postdata["chargedSum"];
            $chargedSum_amount = $chargedSum["amount"];
            $chargedSum_currency = $chargedSum["currency"];

            http_response_code(200);
        } elseif ($type == "SubscriptionTermsChanged") {
            // изменились условия подписки, например, тарифный план или количество устройств.
            $planId = $postdata["planId"];
            $deviceNumber = $postdata["deviceNumber"];
            $nextBillingDate = $postdata["nextBillingDate"];
            $chargedSum = $postdata["chargedSum"];
            $chargedSum_amount = $chargedSum["amount"];
            $chargedSum_currency = $chargedSum["currency"];

            http_response_code(200);
        } elseif ($type == "SubscriptionTerminationRequested") {
            // Пользователь отправил запрос на завершение подписки (удалил приложение из Личного кабинета). Пользователь может возобновить подписку до окончания оплаченного периода.
            http_response_code(200);
        } elseif ($type == "SubscriptionTerminated") {
            // Подписка завершена. Приходит если не прошла регулярная оплата, независимо от того запросил пользователь завершение подписки или нет.
            http_response_code(200);
        } else {
            http_response_code(404);
        }
    } else {
        http_response_code(401);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
