<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";

if(!$this->access(4)) die('forbidden');

if(!isset($_POST['field'],$_POST['value'],$_POST['video_id'])) $this->error(1);
$video_id=&$_POST['video_id'];
$field=&$_POST['field'];
$value=&$_POST['value'];

if(!uString::isDigits($video_id)) $this->error(2);

if($field=='title') {
	 $field='video_title';
	 $value=uString::text2sql($value);
}
elseif($field=='video_descr'||$field=='video_code') {
	 $value=uString::text2sql($value,true);
}
else	 $this->error(5);

if(!$this->query("uViblog","UPDATE
`u235_list`
SET
`".$field."`='".$value."'
WHERE
`video_id`='".$video_id."' AND
`site_id`='".site_id."'
")) $this->error(6);

$uFunc = new uFunc($this);
$uFunc->set_flag_update_sitemap(1, site_id);

echo 'done';
