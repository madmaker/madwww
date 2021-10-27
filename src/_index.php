<?php
require __DIR__.'/vendor/autoload.php';
date_default_timezone_set('UTC');

//phpinfo();
//exit;
//ini_set('magic_quotes_gpc', 'Off');
//ini_set('register_globals', 'Off');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '1');

ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('output_buffering', 'on');
ini_set('max_execution_time', '1000');
ini_set('max_input_time', '1000');
ini_set('session.gc_maxlifetime', '14400');
ini_set('LimitRequestBody', '100M');
ini_set('memory_limit', '32M');

error_reporting(E_ALL);
ini_set('display_errors', '0'); //TODO-nik87 Перед загрузкой на сервер сделать, чтобы ошибки не показывались
$GLOBALS['BUILDER']['cron'] = false;
$GLOBALS['TEMPLATE'][2]['have_panel'] = false;
$GLOBALS['TEMPLATE'][3]['have_panel'] = true;

include_once 'processors/uCore.php';

session_save_path('/tmp');
session_start();
$uCore = new uCore();
