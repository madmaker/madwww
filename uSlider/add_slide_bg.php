<?php
namespace uSlider\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSlider\common;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uSlider/inc/common.php';

class add_slide {
    private $uCore,$slider_id,$slide_id,$slide_pos;
    private function check_data() {
        if(!isset($_POST['slider_id'])) $this->uFunc->error(10);
        $this->slider_id=$_POST['slider_id'];
        if(!uString::isDigits($this->slider_id)) $this->uFunc->error(20);
    }
    private function get_new_slide_id_and_pos() {
        //get id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slide_id
            FROM
            u235_slides
            WHERE
            site_id=:site_id
            ORDER BY
            slide_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->slide_id=$qr->slide_id+1;
            else $this->slide_id=1;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        //get pos
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slide_pos
            FROM
            u235_slides
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ORDER BY
            slide_pos DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->slide_pos=$qr->slide_pos+1;
            else $this->slide_pos=1;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    private function add_slide2db() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
            u235_slides (
            slider_id,
            slide_id,
            slide_pos,
            site_id
            ) VALUES (
            :slider_id,
            :slide_id,
            :slide_pos,
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $this->slide_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSlider=new common($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_new_slide_id_and_pos();
        $this->add_slide2db();

        echo json_encode(array(
        'status' => 'done',
        'slide_id' =>$this->slide_id,
        'slide_pos' =>$this->slide_pos
        ));

        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
/*$uSlide=*/new add_slide($this);