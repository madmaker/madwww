<?php
namespace uSlider\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSlider\common;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uSlider/inc/common.php";

class slide_save_settings {
    public $uSlider;
    public $uSes;
    public $uFunc;
    private $uCore,$slide_id,$field,$val;
    private $slider_id;

    private function check_data() {
        if(!isset($_POST['slide_id'],$_POST['field'])) $this->uFunc->error(10);
        $this->slide_id=$_POST['slide_id'];
        $this->field=$_POST['field'];

        if(
            $this->field!='light_bg'&&
            $this->field!='full_width'&&
            $this->field!='centered') $this->uFunc->error(20);

        $this->slider_id=$this->uSlider->slide_id2slider_id($this->slide_id);
        if(!$this->slider_id) $this->uFunc->error(30);
    }
    private function save_data() {
        //update settings value
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_slides
            SET
            ".$this->field."=(1-".$this->field.")
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

        //get new value
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT
            ".$this->field."
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

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(50);
            $field_name=$this->field;
            $this->val=$qr->$field_name;
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSlider=new common($this->uCore);

        if(!$this->uSes->access(7)) die(json_encode(array('status' => 'forbidden')));

        $this->check_data();
        $this->save_data();

        echo json_encode(array(
            'status' => 'done',
            'slide_id' => $this->slide_id,
            'field' => $this->field,
            'val' => $this->val,
        ));
        //clear uPage cache
        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
new slide_save_settings($this);