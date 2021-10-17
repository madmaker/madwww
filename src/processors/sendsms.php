<?php
//$sendsms['phone'];
//$sendsms['msg'];

//Отправка смс
$host = 'smsc.ru';
$params = '/sys/send.php?login=Aleximus&psw=Ht49Lb5F&phones='.$sendsms['phone'].'&mes='.urlencode($sendsms['msg']). '&translit=0&charset=UTF-8';

$fp = fsockopen($host, 80, $errno, $errstr, 30);
if (!$fp) {
   echo "$errstr ($errno)
\n";
} else {
   $out = "GET " . $params . " HTTP/1.1\r\n";
   $out .= "Host: " . $host . "\r\n";
   $out .= "Connection: Close\r\n\r\n";

   fwrite($fp, $out);
   while (!feof($fp)) {
       $test.=fgets($fp, 128);
   }
   fclose($fp);
}
?>