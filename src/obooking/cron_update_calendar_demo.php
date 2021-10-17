<?php
namespace obooking;

require_once "screenshoter/classes/common.php";

class cron_update_calendar_demo {
    /**
     * @var \screenshooter\common
     */
    private $screenshooter;
    private $uCore;

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();

        $this->screenshooter=new \screenshooter\common($this->uCore);

        $img_path=$this->screenshooter->get_img("http://dance.madwww.ru/obooking/calendar_demo",992,time(),0,57);

        if(filesize($img_path)>100) copy($img_path,"obooking/calendar_demo_images/57/992.png");

    }
}
/*$obooking=*/new cron_update_calendar_demo($this);