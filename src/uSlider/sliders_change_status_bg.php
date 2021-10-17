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

class sliders_change_status {
    private $uCore,$action,$executed_ids;

    private function checkData() {
        if(!isset($_POST['ids'],$_POST['action'])) $this->uFunc->error(10);
        $this->action=$_POST['action'];
        if($this->action=='delete') $this->action='deleted';
        else $this->action='';
    }
    private function make_query() {
        $idsAr=explode("#", $_POST['ids']);
        $ids_count=count($idsAr);

        $ids_list='1=0';
        $this->executed_ids='';
        for($i=1;$i<$ids_count;$i++) {
            $slider_id=$idsAr[$i];
            if(!uString::isDigits($slider_id)) $this->uFunc->error(20);
            $ids_list.=" OR slider_id=".(int)$slider_id;
            $this->executed_ids.="'slider_".$slider_id."':'1',";

            //clean uPage cache
            $this->uSlider->clear_cache_by_slider_id($slider_id);
        }
        $this->update_status($ids_list);
    }
    private function update_status($ids_list) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("UPDATE
            u235_sliders
            SET
            status=:status,
            timestamp=:timestamp
            WHERE
            (".$ids_list.") AND
            site_id=:site_id
            ");
            $timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $this->action,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSlider=new common($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->make_query();
        echo "{
        ".$this->executed_ids."
        'status' : 'done'
        }";
    }
}
new sliders_change_status ($this);