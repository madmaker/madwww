<?php
namespace uSlider\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uSlider\common;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
require_once "uSlider/inc/common.php";

class create_slider {
    public $uFunc;
    public $uSes;
    public $purifier;
    private $uSlider;
    private $uCore,$slider_title,$slider_id,$slider_type;
    private $slide_id;
    private $slide_pos;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uSlider','create_slider_bg'),$str);
    }

    private function check_data() {
        if(!isset($_POST['slider_title'],$_POST['slider_type'])) $this->uFunc->error(10);
        $this->slider_title=trim($_POST['slider_title']);
        if(!strlen($this->slider_title)) die("{'status' : 'error', 'msg' : 'slider_title'}");
        $this->slider_type=$_POST['slider_type'];
        if($this->slider_type!='owl'&&$this->slider_type!='bootstrap'&&$this->slider_type!='flip_book') $this->uFunc->error(20);
    }
    private function get_new_slide_id_and_pos() {
        $this->slide_id=$this->uSlider->get_new_slide_id(site_id);

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
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ))             $this->slide_pos=$qr->slide_pos+1;
        else $this->slide_pos=1;
    }
    private function insert_into_db() {
        $this->slider_id=$this->uSlider->get_new_slider_id(site_id);

        //define title
        $this->slider_title=$this->purifier->purify(trim($this->slider_title));

        //insert slider
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
            u235_sliders (
            slider_id,
            slider_title,
            slider_type,
            site_id
            ) VALUES (
            :slider_id,
            :slider_title,
            :slider_type,
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_title', $this->slider_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_type', $this->slider_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        if($this->slider_type=='bootstrap') {
            //insert 4 default slides
            for ($i = 0; $i < 4; $i++) {
                $this->get_new_slide_id_and_pos();

                $slides_txt = array("Lorem ipsum dolor sit amet, consectetur adipiscing elit",
                    "In id urna ac est rhoncus suscipit eu eget dui",
                    "Aliquam erat volutpat",
                    "Nam volutpat et neque sed molestie"
                );
                //Insert slider to db
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    u235_slides (
                    slider_id,
                    slide_id,
                    slide_pos,
                    img_timestamp,
                    slide_html,
                    light_bg,
                    centered,
                    site_id
                    ) VALUES (
                    :slider_id,
                    :slide_id,
                    :slide_pos,
                    :img_timestamp,
                    :slide_html,
                    0,
                    1,
                    :site_id
                    )
                    ");
                    $img_timestamp = time();
                    $slide_html = uString::text2sql("<h3>".$this->text("Slide number"/*Слайд */). $i . "</h3><p>" . $slides_txt[$i] . "</p>");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':slider_id', $this->slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':slide_id', $this->slide_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':img_timestamp', $img_timestamp, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':slide_pos', $this->slide_pos, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':slide_html', $slide_html, PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('70'/*.$e->getMessage()*/);
                }

                //create slide bg
                $folder = "slides_bg/" . site_id . "/" . $this->slide_id;
                $dir = $_SERVER['DOCUMENT_ROOT'] . '/uSlider/' . $folder . '/'; //Адрес директории для сохранения файла
                // Create dir
                if (!file_exists($dir)) mkdir($dir, 0755, true);
                if (!uFunc::create_empty_index('uSlider/' . $folder . '/')) $this->uFunc->error(80);

                //copy file
                copy("uSlider/img/bootstrap_slider/placeholder_bg_images/300_" . $i . ".jpg", $dir . $this->slide_id . '.jpg');
            }

            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                u235_slider_bootstrap_settings (
                slider_id,
                min_height,
                site_id
                ) VALUES (
                :slider_id,
                300,
                :site_id
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
        }
        if($this->slider_type=='flip_book') {
            //insert 4 default slides
            for ($i = 0; $i < 4; $i++) {
                $this->get_new_slide_id_and_pos();

                $slides_txt = array("Lorem ipsum dolor sit amet, consectetur adipiscing elit",
                    "In id urna ac est rhoncus suscipit eu eget dui",
                    "Aliquam erat volutpat",
                    "Nam volutpat et neque sed molestie"
                );
                //Insert slide to db
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    u235_slides (
                    slider_id,
                    slide_id,
                    slide_pos,
                    img_timestamp,
                    slide_html,
                    light_bg,
                    centered,
                    site_id
                    ) VALUES (
                    :slider_id,
                    :slide_id,
                    :slide_pos,
                    :img_timestamp,
                    :slide_html,
                    0,
                    1,
                    :site_id
                    )
                    ");
                    $img_timestamp = time();
                    $slide_html = uString::text2sql("<h3>".$this->text("Slide number"/*Слайд */). $i . "</h3><p>" . $slides_txt[$i] . "</p>");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_timestamp', $img_timestamp, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $this->slide_pos, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_html', $slide_html, PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('100'/*.$e->getMessage()*/);
                }
            }

            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                slider_flip_book_settings (
                slider_id,
                height,
                site_id
                ) VALUES (
                :slider_id,
                600,
                :site_id
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
        }
        else {//owl
            //insert 5 default slides
            for ($i = 1; $i < 6; $i++) {
                $this->get_new_slide_id_and_pos();

                //Insert slider to db
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm = $this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                    u235_slides (
                    slider_id,
                    slide_id,
                    slide_pos,
                    slide_html,
                    site_id
                    ) VALUES (
                    :slider_id,
                    :slide_id,
                    :slide_pos,
                    :slide_html,
                    :site_id
                    )
                    ");
                    $slide_html = uString::text2sql("<p><img alt='' style=\"display: block; margin-left: auto; margin-right: auto;\" src=\"".u_sroot."uSlider/img/owl_slider/placeholder_images/".$i.".jpg\"/></p><p style=\"text-align: center;\">".$this->text("Slide number").$i."</p>");
                    $site_id = site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_id', $this->slide_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_pos', $this->slide_pos, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_html', $slide_html, PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('120'/*.$e->getMessage()*/);
                }
            }


            //insert settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("INSERT INTO
                u235_slider_owl_settings (
                slider_id,
                site_id
                ) VALUES (
                :slider_id,
                :site_id
                ) 
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uSlider=new common($this->uCore);

        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);


        $this->check_data();
        $this->insert_into_db();

        echo "{'status' : 'done',
        'slider_id' : '".$this->slider_id."',
        'slider_type' : '".$this->slider_type."',
        'slider_title' : '".rawurlencode($this->slider_title)."'
        }";
    }
}
new create_slider($this);
