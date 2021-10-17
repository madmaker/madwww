<?php
namespace gallery\admin;

use processors\uFunc;
use uSes;
use gallery\common;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once "gallery/classes/common.php";

class create_gallery {
    public $uFunc;
    public $uSes;
    public $purifier;
    private $purifier_config;
    private $gallery;
    private $uCore,$gallery_title;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('gallery','create_gallery_bg'),$str);
    }

    private function check_data() {
        if(isset($_POST['gallery_title'])) {
            $this->gallery_title=trim($_POST['gallery_title']);
            if($this->gallery_title!=="") {
                if(!isset($this->purifier)) {
                    require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
                    $this->purifier_config = \HTMLPurifier_Config::createDefault();
                    $this->purifier = new \HTMLPurifier($this->purifier_config);
                }
                $this->gallery_title=$this->purifier->purify($this->gallery_title);
            }
        }
        if(!isset($this->gallery_title)) $this->gallery_title=$this->text("default gallery title");
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        if(!$this->uFunc->mod_installed("gallery")) die("{'status' : 'forbidden'}");;

        $this->gallery=new common($this->uCore);

        $this->check_data();
        $gallery_id=$this->gallery->create_new_gallery($this->gallery_title);

        echo json_encode(array(
            "status"=>"done",
            "gallery_id"=>$gallery_id/*,
            "gallery_title"=>$this->gallery_title*/
        ));
    }
}
new create_gallery($this);