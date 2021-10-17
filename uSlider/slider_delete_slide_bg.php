<?php
namespace uSlider\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSlider\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSlider/inc/common.php";

class delete_slide {
    private $uSlider;
    private $uFunc;
    private $uSes;
    private $uCore,$slide_id;
    private $slider_id;

    private function check_data(){
        if(!isset($_POST['slide_id'])) $this->uFunc->error(10);
        $this->slide_id=$_POST['slide_id'];
        if(!uString::isDigits($this->slide_id)) $this->uFunc->error(20);

        $this->slider_id=$this->uSlider->slide_id2slider_id($this->slide_id);
        if(!$this->slider_id) {
            die(json_encode(array(
                'status' => 'done',
                'slide_id' =>$this->slide_id
            )));
        }
    }
    private function delete_slide() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("DELETE FROM
            u235_slides
            WHERE
            slide_id=:slide_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uSlider=new common($this->uCore);


        $this->check_data();
        $this->delete_slide();

        echo json_encode(array(
            'status' => 'done',
            'slide_id' =>$this->slide_id
        ));

        //clear uPage cache
        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
new delete_slide($this);