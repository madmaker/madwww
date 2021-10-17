<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class slider_title_save_bg {
    private $uFunc;
    private $uSes;
    private $uCore,$slider_id,$slider_title;
    private function check_data() {
        if(!isset($_POST['slider_id'],$_POST['slider_title'])) $this->uFunc->error(10);
        $this->slider_id=$_POST['slider_id'];
        $this->slider_title=trim($_POST['slider_title']);
        if(!uString::isDigits($this->slider_id)) $this->uFunc->error(20);

        if(!strlen($this->slider_title)) {
            echo json_encode(array(
                'status' =>'error',
                'msg' => 'slider_title'
            ));
            exit;
        }
    }
    private function save_title(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_sliders
            SET
            slider_title=:slider_title
            WHERE
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $slider_title=uString::text2sql($this->slider_title);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_title', $slider_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        
        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->check_data();
        $this->save_title();

        echo json_encode(array(
            'status' => 'done',
            'slider_title'=>$this->slider_title
        ));
    }
}
new slider_title_save_bg($this);