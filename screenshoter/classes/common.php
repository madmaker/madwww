<?php
namespace screenshooter;

ini_set("memory_limit","256M");
set_time_limit(3000);

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class common {
    private $uCore;
    public function get_img($url,$width,$timestamp,$allowJPG=1,$site_id=site_id) {
        if($allowJPG) $format="allowJPG";
        else $format="png";

        if(strpos($url,"?")) $sep="&";
        else $sep="?";
//        $img_src="https://image.thum.io/get/auth/7176-madwww.ru/fullpage/".$format."/viewportWidth/".$width."/width/".$width."/noanimate/maxAge/0/"./*rawurlencode(*/$url.$sep.$timestamp/*)*/;
        $request="https://www2png.com/api/capture/02dfab6c-9895-43ed-ad6e-224b93ae5eb6?url="./*rawurlencode(*/$url.$sep.$timestamp/*)*/."&resolution=".$width."x0&full_page=true";
        $request_result=file_get_contents($request);
//        print_r($request_result);
        $request_result=json_decode($request_result);
        $img_src=$request_result->image_url;
        $status_url=$request_result->status_url;
        $status=file_get_contents($status_url);
        $status=json_decode($status);
        for($i=0;!$status->screenshot_available&&$i<30;$i++) {
            $status=file_get_contents($status_url);
            $status=json_decode($status);
            sleep(1);
        }
        if(!$status->screenshot_available) return $status->screenshot_available;

        $dir="screenshoter/images/".$site_id;
        if(!file_exists($dir)) mkdir($dir,0755,true);

        $done=0;
        for($i=0;!$done&&$i<5;$i++) {
            $in = fopen($img_src, "r");
            $out = fopen($dir . "/" . $timestamp, "wb");

            while ($chunk = fread($in,8192))
            {
                fwrite($out, $chunk, 8192);
            }
            fclose($in);
            fclose($out);
            if(filesize($dir . "/" . $timestamp)) $done=1;
        }

        return $dir."/".$timestamp;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();
    }
}
