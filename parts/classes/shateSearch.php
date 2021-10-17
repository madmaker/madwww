<?php
//"https://api.shate-m.com" (для РБ) или "https://api.shate-m.ru" (для РФ) или "https://api.shate-m.kz:4443" (для РК)
$ch_url = "https://api.shate-m.ru";
$Login = "CENZAP";
$Password = "79111143671";
$ApiKey = "fc9bdbca-01d3-4518-add6-5f2ec83885b7";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $ch_url."/login");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_USERPWD, $Login.":".$Password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "ApiKey=".$ApiKey);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

$response = curl_exec($ch);

$headers = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
$headers = explode("\r\n", $headers);

foreach ($headers as $header) {
    if (strpos($header,'Token:')!==false) {
        $token = array($header);

    }
}
//        echo $headers["content"];

curl_setopt($ch, CURLOPT_HTTPHEADER,$token);
curl_setopt($ch, CURLOPT_URL, $ch_url."/api/search/GetPricesByArticle?ArticleCode=".$this->search."&IncludeAnalogs=1");
curl_setopt($ch, CURLOPT_POST, false);

$response = curl_exec($ch);

echo $response;

curl_close($ch);