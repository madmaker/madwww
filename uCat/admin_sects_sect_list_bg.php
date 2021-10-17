<?php

use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_sects_sect_list_bg {
    public $site_id, $sect_id, $type, $sect_obj, $switcher;
    public $attached;
    public $hierarchy;
    private $uCore, $uFunc, $uSes;

    private function check_data() {
        if(!isset($_POST['sect_id'], $_POST['type'])) $this->uFunc->error(10);
        $this->sect_id=$_POST['sect_id'];
        $this->type = $_POST['type'];
        if(!uString::isDigits($this->sect_id)) $this->uFunc->error(20);

        if(isset($_POST["hierarchy"])) {
            if($_POST["hierarchy"]==="parent") $this->hierarchy="parent";
            else $this->hierarchy="children";
        }
        else $this->hierarchy="children";

        if($this->type == "attached") {
            if($this->hierarchy=="children") $this->create_object_attached();
            else $this->create_object_attached_parent();
        }
        else if($this->type == "unattached") {
            if($this->hierarchy=="children") $this->create_object_unattached();
            else $this->create_object_unattached_parent();
        }
        else {
            exit;
        }
    }

    public function create_object_attached() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_sects.sect_id,
            u235_sects.sect_title
            FROM
            sects_sects,
            u235_sects
            WHERE
            sects_sects.parent_sect_id=:sect_id AND
            u235_sects.sect_id=sects_sects.child_sect_id AND
            u235_sects.site_id=:site_id AND
            sects_sects.site_id=:site_id
            ORDER BY
            u235_sects.sect_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    public function create_object_attached_parent() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_sects.sect_id,
            u235_sects.sect_title
            FROM
            u235_sects
            JOIN
            sects_sects
            ON
            u235_sects.sect_id=sects_sects.parent_sect_id AND
            u235_sects.site_id=sects_sects.site_id
            WHERE
            sects_sects.child_sect_id=:sect_id AND
            u235_sects.site_id=:site_id
            ORDER BY
            u235_sects.sect_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    public function create_object_unattached() {
        $this->create_object_attached();
        $sect_obj_count=count($this->sect_obj);
        for($i=0;$i<$sect_obj_count;$i++){
            $data=$this->sect_obj[$i];
            $this->attached[$data->sect_id]=1;
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_title
            FROM
            u235_sects
            WHERE
            site_id=:site_id AND
            sect_id!=:sect_id
            ORDER BY
            sect_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    public function create_object_unattached_parent() {
        $this->create_object_attached_parent();
        $sect_obj_count=count($this->sect_obj);
        for($i=0;$i<$sect_obj_count;$i++){
            $data=$this->sect_obj[$i];
            $this->attached[$data->sect_id]=1;
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            sect_id,
            sect_title
            FROM
            u235_sects
            WHERE
            site_id=:site_id AND
            sect_id!=:sect_id
            ORDER BY
            sect_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');
        $this->check_data();
    }
}

$sect_list=new admin_sects_sect_list_bg($this);

?>
<table class="table table-condensed table-hover table-striped uCat_sects_sect_list">
    <?
    if($sect_list->type == 'unattached') {
        foreach($sect_list->sect_obj as $key=>$value) {
            $sect_id=$value->sect_id;
            if(isset($sect_list->attached[$sect_id])) continue;
            ?>
            <tr>
                <td><? echo $value->sect_id?></td>
                <td><a href="<?=u_sroot?>uCat/cats/<?=$value->sect_id?>" target="_blank"><?=uString::sql2text($value->sect_title)?></a></td>
                <td><button type="button" class="btn btn-success btn-xs" onclick="<?
                    if($sect_list->hierarchy==="parent") echo "uCat_cats_admin.attach_parent_sect_do(".$value->sect_id.",'attach');";
                    else echo "uCat_cats_admin.attach_subsect_do(".$value->sect_id.",'attach');"
                    ?>"><span class="glyphicon glyphicon-plus"></span> Прикрепить</button></td>
            </tr>
        <?}
    }
    else {
        foreach($sect_list->sect_obj as $key=>$value) {?>
            <tr>
                <td><? echo $value->sect_id;?></td>
                <td><a href="<?=u_sroot?>uCat/cats/<?=$value->sect_id?>" target="_blank"><?=uString::sql2text($value->sect_title)?></a></td>
                <td><button type="button" class="btn btn-danger btn-xs" onclick="<?
                    if($sect_list->hierarchy==="parent") echo "uCat_cats_admin.attach_parent_sect_do(".$value->sect_id.",'detach');";
                    else echo "uCat_cats_admin.attach_subsect_do(".$value->sect_id.",'detach');"
                    ?>"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
            </tr>
        <?}
    }?>
</table>