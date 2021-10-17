<?php
namespace Listener;
require_once "processors/classes/uFunc.php";
require_once "api/classes/PaypalIPN.php";
use PaypalIPN;

$uFunc = new \processors\uFunc($this);
$ipn = new PaypalIPN($this);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    /** @noinspection PhpUnhandledExceptionInspection */
    $ipn->createIpnListener();
    //http_response_code(200);
    $uFunc->journal($ipn->get_post_data(), 'evotor_documents');
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
