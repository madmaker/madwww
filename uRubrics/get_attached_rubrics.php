<?php
namespace uRubrics\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class get_attached_rubrics {
    public $page_id;
    public $attached;
    public $rubrics;
    private $uSes;
    private $uFunc;
    public $mod;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['page_id'],$_POST['type'])) $this->uFunc->error(10);
        if(isset($_POST["mod"])) $this->mod=1;
        else $this->mod=0;
        $this->page_id=$_POST['page_id'];
        if(!uString::isDigits($this->page_id)) $this->uFunc->error(20);
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uRubrics','get_attached_rubrics'),$str);
    }

    private function get_attached() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT DISTINCT
            u235_urubrics_list.rubric_id,
            u235_urubrics_list.rubric_name
            FROM
            u235_urubrics_pages
            JOIN
            u235_urubrics_list
            ON 
            u235_urubrics_list.rubric_id=u235_urubrics_pages.rubric_id AND
            u235_urubrics_list.site_id=u235_urubrics_pages.site_id
            WHERE
            u235_urubrics_pages.site_id=:site_id AND
            u235_urubrics_pages.page_id=:page_id AND
            `mod`=:mod
            ORDER BY
            u235_urubrics_list.rubric_name ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod', $this->mod,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }

    private function get_unattached() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            rubric_id,
            rubric_name
            FROM
            u235_urubrics_list
            WHERE
            site_id=:site_id
            ORDER BY
            rubric_name ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("forbidden");

        $this->check_data();
        $this->rubrics=$this->get_attached();

        if($_POST['type']=='unattached') {
            $this->attached=[];
            /** @noinspection PhpUndefinedMethodInspection */
            while($rubric=$this->rubrics->fetch(PDO::FETCH_OBJ)) $this->attached[$rubric->rubric_id]=1;
            $this->rubrics=$this->get_unattached();
        }
    }
}
$uRubrics=new get_attached_rubrics($this);?>

<table class="table table-striped table-condensed table-hover">
<?
if($_POST['type']=='unattached') {
    /** @noinspection PhpUndefinedMethodInspection */
    while($rubric=$uRubrics->rubrics->fetch(PDO::FETCH_OBJ)) {
        if(isset($uRubrics->attached[$rubric->rubric_id])) continue;
        $rubric_name=uString::sql2text($rubric->rubric_name,1);
        ?>
        <tr>
            <td><button class="btn btn-xs btn-default uTooltip" title="<?=$uRubrics->text("Edit rubric - btn"/*Редактировать рубрику*/)?>" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.edit_rubric_title_init(<?=$rubric->rubric_id?>,'<?=rawurlencode($rubric_name)?>')"><span class="glyphicon glyphicon-pencil"></span></button></td>
            <td><button class="btn btn-xs btn-danger uTooltip" title="<?=$uRubrics->text("Delete rubric - btn"/*Удалить рубрику*/)?>" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.delete_rubric_init(<?=$rubric->rubric_id?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
            <td><a target="_blank" href="<?=u_sroot?>uRubrics/show/<?=$rubric->rubric_id?>"># <?=$rubric->rubric_id?> <?=$rubric_name?></a></td>
            <td><button class="btn btn-success btn-xs" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.attachRubric(<?=$rubric->rubric_id?>)"><span class="glyphicon glyphicon-plus"></span> <?=$uRubrics->text("Attach - btn"/*Прикрепить*/)?></button></td>
        </tr>
    <?}
}
else {
    /** @noinspection PhpUndefinedMethodInspection */
    while($rubric=$uRubrics->rubrics->fetch(PDO::FETCH_OBJ)) {
        $rubric_name=uString::sql2text($rubric->rubric_name,1);?>
        <tr>
            <td><button class="btn btn-xs btn-default uTooltip" title="<?=$uRubrics->text("Edit rubric - btn"/*Редактировать рубрику*/)?>" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.edit_rubric_title_init(<?=$rubric->rubric_id?>,'<?=rawurlencode($rubric_name)?>')"><span class="glyphicon glyphicon-pencil"></span></button></td>
            <td><button class="btn btn-xs btn-danger uTooltip" title="<?=$uRubrics->text("Delete rubric - btn"/*Удалить рубрику*/)?>" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.delete_rubric_init(<?=$rubric->rubric_id?>)"><span class="glyphicon glyphicon-remove"></span></button></td>
            <td><a target="_blank" href="<?=u_sroot?>uRubrics/show/<?=$rubric->rubric_id?>"># <?=$rubric->rubric_id?> <?=$rubric_name?></a></td>
            <td><button class="btn btn-danger btn-xs" onclick="<?=$uRubrics->mod?'uPage_setup_uPage':'uEditor'?>.detachRubric(<?=$rubric->rubric_id?>)"><span class="glyphicon glyphicon-minus"></span> <?=$uRubrics->text("Detach - btn"/*Открепить*/)?></button></td>
        </tr>
    <?}
}?>
</table>