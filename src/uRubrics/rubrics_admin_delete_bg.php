<?php
namespace uRubrics\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uPage/inc/common.php";

class delete_rubric {
    private $uCore,$rubric_id;
    private function check_data() {
        if(!isset($_POST['rubric_id'])) $this->uFunc->error(10);
        $this->rubric_id=$_POST['rubric_id'];
        if(!uString::isDigits($this->rubric_id)) $this->uFunc->error(20);
    }
    private function del_rubric() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("DELETE FROM
            u235_urubrics_list
            WHERE
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $this->rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uPage=new common($this->uCore);

        if(!$this->uSes->access(7)) die('forbidden');

        $this->check_data();
        $this->del_rubric();

        $uPage_rubrics_arts=$this->uPage->el_id_type2cols_els_id($this->rubric_id,"rubrics_arts");
        $uPage_rubrics_arts_column=$this->uPage->el_id_type2cols_els_id($this->rubric_id,"rubrics_arts_column");
        $uPage_rubrics_tiles=$this->uPage->el_id_type2cols_els_id($this->rubric_id,"rubrics_tiles");

        for($i=0;$uPage_rubrics_arts[$i];$i++) $this->uPage->delete_el($uPage_rubrics_arts[$i]->cols_els_id);
        for($i=0;$uPage_rubrics_arts_column[$i];$i++) $this->uPage->delete_el($uPage_rubrics_arts_column[$i]->cols_els_id);
        for($i=0;$uPage_rubrics_tiles[$i];$i++) $this->uPage->delete_el($uPage_rubrics_tiles[$i]->cols_els_id);

        $this->uPage->clean_cache4uRubrics($this->rubric_id);

        echo "{
        'status' : 'done',
        'rubric_id' : '".$this->rubric_id."'
        }";;
    }
}
/*$uRubrics=*/new delete_rubric($this);