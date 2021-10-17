<?php
namespace screenshooter;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "screenshoter/classes/common.php";

class get_screenshot {
    /**
     * @var common
     */
    private $screenshoter;
    private $timestamp;
    private $viewport;
    private $uFunc;
    private $url;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_GET["url"],$_GET["viewport"])) $this->uFunc->error(0,1);
        $this->url=$_GET["url"];
        $this->viewport=(int)$_GET["viewport"];
        if(!$this->viewport) $this->uFunc->error(1,1);
        if(isset($_GET["timestamp"])) {
            if($_GET["timestamp"]!=="") {
                if(\uString::isDigits($_GET["timestamp"])) $this->timestamp=(int)$_GET["timestamp"];
            }
        }
        if(!isset($this->timestamp)) $this->timestamp=time();
    }

    private function get_img() {
        $img_path=$this->screenshoter->get_img($this->url,$this->viewport,$this->timestamp);

        header('Content-type: image/jpeg');
        readfile($img_path);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(
            !$this->uSes->access(7)&&
            !$this->uSes->access(25)
        ) die("{'status' : 'forbidden'}");//Не админ каталога и не админ контента

        $this->uFunc=new uFunc($this->uCore);
        $this->screenshoter=new common($this->uCore);

        $this->check_data();
        $this->get_img();
    }
}
new get_screenshot($this);
