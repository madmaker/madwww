<?php
namespace uSlider\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uSlider\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSlider/inc/common.php";

class slide_save {
    private $uCore,$slide_id;
    private $slider_id;

    private function check_data() {
        if(!isset($_POST['slide_id'])) $this->uFunc->error(10);
        if(!uString::isDigits($_POST['slide_id'])) $this->uFunc->error(20);
        $this->slide_id=$_POST['slide_id'];

        //get slider_id
        $this->slider_id=$this->uSlider->slide_id2slider_id($this->slide_id);
        if(!$this->slider_id) $this->uFunc->error(30);
    }
    private function save_slide_html() {
        $html=trim($_POST['html']);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            slide_html=:slide_html
            WHERE
            slide_id=:slide_id AND
            site_id=:site_id
            ");
            $slide_html=uString::text2sql($html);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_html', $slide_html,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        echo json_encode(array(
        'status' => 'done',
        'slide_id' =>$this->slide_id,
        'slide_html' =>$html
        ));
    }
    private function delete_slide_bg() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            img_timestamp=0
            WHERE
            slide_id=:slide_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir('uSlider/slides_bg/'.site_id.'/'.$this->slide_id);

        echo json_encode(array(
        'status' =>'done',
        'slide_id' =>$this->slide_id
        ));
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uSlider=new common($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();

        if(isset($_POST['html'])) $this->save_slide_html();
        elseif(isset($_POST['delete_slide_bg'])) $this->delete_slide_bg();
        else {
            echo json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        //clear uPage cache
        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
new slide_save($this);