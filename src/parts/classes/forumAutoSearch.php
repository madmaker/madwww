<?php
$login="eczio";
$pass="6b6755443d991800";

// Инициализация SOAP-клиента

$client = new SoapClient('https://api.forum-auto.ru/wsdl', ["exceptions" => false]);

// Выполнение запроса к серверу API Форум-Авто

$result = $client->listGoods($login, $pass, $this->search,1);

if (is_soap_fault($result)) {

    // Обработка ошибки

    echo "SOAP Fault: (faultcode: {$result->faultcode}, faultstring: {$result->faultstring}, detail: {$result->detail})";

} else {

    // Результат запроса

    echo '<pre>' . var_export($result, true) . '</pre>';

}