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

class slider_save_settings_bg {
    private $dots_xs;
    private $dots_sm;
    private $dots_md;
    private $dots_lg;
    private $dots_xlg;
    private $dots_style;
    private $nav_xs;
    private $nav_sm;
    private $nav_md;
    private $nav_lg;
    private $nav_xlg;
    private $height;
    private $uSlider;
    private $uSes;
    private $uFunc;
    private $uCore,$slider_id,$slider_type,
        $items_number_xlg,$items_number_lg,$items_number_md,$items_number_sm,$items_number_xs,$slideSpeed,$autoPlay,$scrollPerPage,
        $pause,$wrap,$keyboard,$show_indicators,$show_controls,$interval_delay,$max_height,$min_height;
    private function check_data() {
        if(!isset($_POST['slider_id'],$_POST['slider_type'])) $this->uFunc->error(10);

        $this->slider_id=$_POST['slider_id'];
        $this->slider_type=$_POST['slider_type'];
        if(!uString::isDigits($this->slider_id)) $this->uFunc->error(20);

        if($this->slider_type=='owl') {
            if(!isset(
                $_POST['items_number_xlg'],
                $_POST['items_number_lg'],
                $_POST['items_number_md'],
                $_POST['items_number_sm'],
                $_POST['items_number_xs'],
                $_POST['nav_xlg'],
                $_POST['nav_lg'],
                $_POST['nav_md'],
                $_POST['nav_sm'],
                $_POST['nav_xs'],
                $_POST['dots_xlg'],
                $_POST['dots_lg'],
                $_POST['dots_md'],
                $_POST['dots_sm'],
                $_POST['dots_xs'],
                $_POST['dots_style'],
                $_POST['slideSpeed'],
                $_POST['autoPlay'],
                $_POST['scrollPerPage']
            )) $this->uFunc->error(30);

            $this->items_number_xlg=$_POST['items_number_xlg'];
            $this->items_number_lg=$_POST['items_number_lg'];
            $this->items_number_md=$_POST['items_number_md'];
            $this->items_number_sm=$_POST['items_number_sm'];
            $this->items_number_xs=$_POST['items_number_xs'];

            $this->nav_xlg=$_POST['nav_xlg'];
            $this->nav_lg=$_POST['nav_lg'];
            $this->nav_md=$_POST['nav_md'];
            $this->nav_sm=$_POST['nav_sm'];
            $this->nav_xs=$_POST['nav_xs'];

            $this->dots_xlg=$_POST['dots_xlg'];
            $this->dots_lg=$_POST['dots_lg'];
            $this->dots_md=$_POST['dots_md'];
            $this->dots_sm=$_POST['dots_sm'];
            $this->dots_xs=$_POST['dots_xs'];

            $this->dots_style=$_POST['dots_style'];

            $this->slideSpeed=$_POST['slideSpeed'];
            $this->autoPlay=$_POST['autoPlay'];
            $this->scrollPerPage=$_POST['scrollPerPage'];

            if(!uString::isDigits($this->items_number_xlg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->items_number_lg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->items_number_md)) $this->uFunc->error(41);
            if(!uString::isDigits($this->items_number_sm)) $this->uFunc->error(42);
            if(!uString::isDigits($this->items_number_xs)) $this->uFunc->error(43);

            if(!uString::isDigits($this->nav_xlg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->nav_lg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->nav_md)) $this->uFunc->error(41);
            if(!uString::isDigits($this->nav_sm)) $this->uFunc->error(42);
            if(!uString::isDigits($this->nav_xs)) $this->uFunc->error(43);

            if(!uString::isDigits($this->dots_xlg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->dots_lg)) $this->uFunc->error(40);
            if(!uString::isDigits($this->dots_md)) $this->uFunc->error(41);
            if(!uString::isDigits($this->dots_sm)) $this->uFunc->error(42);
            if(!uString::isDigits($this->dots_xs)) $this->uFunc->error(43);

            if(!uString::isDigits($this->dots_style)) $this->uFunc->error(43);

            if(!uString::isDigits($this->slideSpeed)) die(json_encode(array('status' => 'error', 'msg' => 'slideSpeed')));
            if(!uString::isDigits($this->autoPlay)) die(json_encode(array('status' => 'error', 'msg' => 'autoPlay')));
            if(!uString::isDigits($this->scrollPerPage)) $this->uFunc->error(80);
        }
        elseif($this->slider_type=='bootstrap') {
            if(!isset($_POST['pause'],
            $_POST['wrap'],
            $_POST['keyboard'],
            $_POST['show_indicators'],
            $_POST['show_controls'],
            $_POST['interval_delay'],
            $_POST['max_height'],
            $_POST['min_height'],
            $_POST['dots_style']
            )) $this->uFunc->error(130);

            $this->pause=$_POST['pause'];
            $this->wrap=$_POST['wrap'];
            $this->keyboard=$_POST['keyboard'];
            $this->show_indicators=$_POST['show_indicators'];
            $this->show_controls=$_POST['show_controls'];
            $this->interval_delay=$_POST['interval_delay'];
            $this->max_height=$_POST['max_height'];
            $this->min_height=$_POST['min_height'];
            $this->dots_style=$_POST['dots_style'];

            if(!uString::isDigits($this->pause)) $this->uFunc->error(140);
            if(!uString::isDigits($this->wrap)) $this->uFunc->error(150);
            if(!uString::isDigits($this->keyboard)) $this->uFunc->error(160);
            if(!uString::isDigits($this->show_indicators)) $this->uFunc->error(170);
            if(!uString::isDigits($this->show_controls)) $this->uFunc->error(180);
            if(!uString::isDigits($this->interval_delay)) die(json_encode(array("status" => 'error', 'msg' => 'interval_delay')));
            if(!uString::isDigits($this->max_height)) die(json_encode(array('status' => 'error', 'msg' => 'max_height')));
            if(!uString::isDigits($this->min_height)) die(json_encode(array('status' => 'error', 'msg' => 'min_height')));
            if(!uString::isDigits($this->dots_style)) die(json_encode(array('status' => 'error', 'msg' => 'dots_style')));
        }
        elseif($this->slider_type=='flip_book') {
            if(!isset($_POST['height'])) $this->uFunc->error(130);

            $this->height=$_POST['height'];

            if(!uString::isDigits($this->height)) die(json_encode(array('status' => 'error', 'msg' => 'min_height')));
        }
        else $this->uFunc->error(190);
    }
    private function update_settings() {
        if($this->slider_type=='owl') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
                u235_slider_owl_settings
                SET
                items_number_xlg=:items_number_xlg,
                items_number_lg=:items_number_lg,
                items_number_md=:items_number_md,
                items_number_sm=:items_number_sm,
                items_number_xs=:items_number_xs,
                nav_xlg=:nav_xlg,
                nav_lg=:nav_lg,
                nav_md=:nav_md,
                nav_sm=:nav_sm,
                nav_xs=:nav_xs,
                dots_xlg=:dots_xlg,
                dots_lg=:dots_lg,
                dots_md=:dots_md,
                dots_sm=:dots_sm,
                dots_xs=:dots_xs,
                dots_style=:dots_style,
                slideSpeed=:slideSpeed,
                autoPlay=:autoPlay,
                scrollPerPage=:scrollPerPage
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_xlg', $this->items_number_xlg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_lg', $this->items_number_lg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_md', $this->items_number_md,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_sm', $this->items_number_sm,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number_xs', $this->items_number_xs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_xlg', $this->nav_xlg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_lg', $this->nav_lg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_md', $this->nav_md,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_sm', $this->nav_sm,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':nav_xs', $this->nav_xs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_xlg', $this->dots_xlg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_lg', $this->dots_lg,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_md', $this->dots_md,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_sm', $this->dots_sm,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_xs', $this->dots_xs,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $this->dots_style,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slideSpeed', $this->slideSpeed,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':autoPlay', $this->autoPlay,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':scrollPerPage', $this->scrollPerPage,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}
        }
        elseif($this->slider_type=='bootstrap') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
                u235_slider_bootstrap_settings
                SET
                interval_delay=:interval_delay,
                pause=:pause,
                wrap=:wrap,
                keyboard=:keyboard,
                show_indicators=:show_indicators,
                show_controls=:show_controls,
                max_height=:max_height,
                min_height=:min_height,
                dots_style=:dots_style
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':interval_delay', $this->interval_delay,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pause', $this->pause,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':wrap', $this->wrap,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':keyboard', $this->keyboard,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_indicators', $this->show_indicators,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_controls', $this->show_controls,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':max_height', $this->max_height,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_height', $this->min_height,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $this->dots_style,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}
        }
        elseif($this->slider_type=='flip_book') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
                slider_flip_book_settings
                SET
                height=:height
                WHERE
                slider_id=:slider_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':height', $this->height,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $this->slider_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}
        }
    }
    private function result() {
        if($this->slider_type=='owl') {
            echo json_encode(array(
                'status' => 'done',
            'items_number_xlg'=>$this->items_number_xlg,
            'items_number_lg'=>$this->items_number_lg,
            'items_number_md'=>$this->items_number_md,
            'items_number_sm'=>$this->items_number_sm,
            'items_number_xs'=>$this->items_number_xs,
            'nav_xlg'=>$this->nav_xlg,
            'nav_lg'=>$this->nav_lg,
            'nav_md'=>$this->nav_md,
            'nav_sm'=>$this->nav_sm,
            'nav_xs'=>$this->nav_xs,
            'dots_xlg'=>$this->dots_xlg,
            'dots_lg'=>$this->dots_lg,
            'dots_md'=>$this->dots_md,
            'dots_sm'=>$this->dots_sm,
            'dots_xs'=>$this->dots_xs,
            'dots_style'=>$this->dots_style,
            'slideSpeed'=>$this->slideSpeed,
            'autoPlay'=>$this->autoPlay,
            'scrollPerPage'=>$this->scrollPerPage
            ));
        }
        elseif($this->slider_type=='bootstrap') {
            echo json_encode(array(
                'status' => 'done',
            'pause'=>$this->pause,
            'wrap'=>$this->wrap,
            'keyboard'=>$this->keyboard,
            'show_indicators'=>$this->show_indicators,
            'show_controls'=>$this->show_controls,
            'interval_delay'=>$this->interval_delay,
            'max_height'=>$this->max_height,
            'min_height'=>$this->min_height,
            'dots_style'=>$this->dots_style
            ));
        }
        elseif($this->slider_type=='flip_book') {
            echo json_encode(array(
            'status' => 'done',
            'height'=>$this->height
            ));
        }
    }
    function __construct (&$uCore) {
        $this->uCore =& $uCore;
        $this->uSes = new \uSes($this->uCore);
        if (!$this->uSes->access(7)) die(json_encode(array('status' => 'forbidden')));

        $this->uFunc = new uFunc($this->uCore);
        $this->uSlider = new common($this->uCore);


        $this->check_data();
        $this->update_settings();
        $this->result();

        //clean uPage cache
        $this->uSlider->clear_cache_by_slider_id($this->slider_id);
    }
}
new slider_save_settings_bg($this);