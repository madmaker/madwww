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

class slider_move {
    private $uSlider;
    private $uFunc;
    private $uSes;
    private $uCore,$slide_id,$dir,$slider_id;
    private function check_data() {
        if(!isset($_POST['slide_id'],$_POST['dir'])) $this->uFunc->error(10);
        $this->slide_id=$_POST['slide_id'];
        $this->dir=$_POST['dir'];

        if(!uString::isDigits($this->slide_id)) $this->uFunc->error(20);
    }
    private function move_slides() {
        //get current slide's pos
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slide_pos,
            slider_id
            FROM
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
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$slide=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);

        $this->slider_id=$slide->slider_id;

        if($this->dir=='up') {
            $all_slides_cur_pos=$slide->slide_pos-1;
            $all_slides="slide_pos+1";
            $this_slide=$slide->slide_pos-1;
        }
        else {
            $all_slides_cur_pos=$slide->slide_pos+1;
            $all_slides="slide_pos-1";
            $this_slide=$slide->slide_pos+1;
        }

        //move upper slides down/up
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            slide_pos=".$all_slides."
            WHERE
            slide_pos=:slide_pos AND
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $all_slides_cur_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        //move current slide up/down
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            slide_pos=:slide_pos
            WHERE
            slide_id=:slide_id AND
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $this_slide,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

    }
    private function get_slides() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            slide_id,
            slide_pos
            FROM
            u235_slides
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        return 0;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uSlider=new common($this->uCore);


        $this->check_data();
        $this->move_slides();
        $q_slides=$this->get_slides();

        $slides_ar=[];
        /** @noinspection PhpUndefinedMethodInspection */
        while($slide=$q_slides->fetch(PDO::FETCH_OBJ)) {
            $slides_ar['slide_'.$slide->slide_id.'_pos']=$slide->slide_pos;
        }
        $slides_ar['status']='done';

        echo json_encode($slides_ar);

        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
new slider_move($this);