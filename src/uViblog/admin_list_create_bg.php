<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";

if(!$this->access(4)) die('forbidden');

if(!isset($_POST['title'])) $this->error(1);
$video_title=uString::text2sql(trim($_POST['title']));

if(empty($video_title)) die('title is empty');

if(!$query=$this->query("uViblog","SELECT `video_id` FROM `u235_list` ORDER BY `video_id` DESC LIMIT 1")) $this->error(2);
$qr=$query->fetch_object();
if(mysqli_num_rows($query)>0) $video_id=$qr->video_id+1;
else $video_id=1;

if(!$this->query("uViblog","INSERT INTO `u235_list` (
`video_id`,
`video_title`,
`site_id`
) VALUES (
	'".$video_id."',
	'".$video_title."',
	'".site_id."'
)")) $this->error(3);

$uFunc = new uFunc($this);
$uFunc->set_flag_update_sitemap(1, site_id);

echo $video_id;
