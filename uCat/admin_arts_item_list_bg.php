<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_arts_item_list_bg {
    public $attached_items_ar;
    private $art_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['art_id'],$_POST['type'])) $this->uFunc->error(1);
        $this->art_id=$_POST['art_id'];
        if(!uString::isDigits($this->art_id)) $this->uFunc->error(2);
    }
    private function get_attached_items() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_items.item_id,
            item_article_number,
            item_title
            FROM
            u235_items
            JOIN 
            u235_articles_items
            ON
            u235_items.item_id=u235_articles_items.item_id AND
            u235_items.site_id=u235_articles_items.site_id
            WHERE
            parts_autoadd=0 AND
            u235_articles_items.art_id=:art_id AND
            u235_items.site_id=:site_id
            ORDER BY
            item_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_id', $this->art_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $result=$stm->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return array();
    }
    public function get_all_items() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            item_id,
            item_article_number,
            item_title
            FROM
            u235_items
            WHERE
            parts_autoadd=0 AND
            site_id=:site_id
            ORDER BY
            item_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return false;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->attached_items_ar=$this->get_attached_items();
    }
}
$uCat=new admin_arts_item_list_bg($this);


if($_POST['type']=='unattached') {
    $attached_items_count=count($uCat->attached_items_ar);
    for($i=0;$i<$attached_items_count;$i++) {
        $data=$uCat->attached_items_ar[$i];
        $attached[$data['item_id']]=1;
    }
}
$filter_id=time();
?>
    <?
    if(isset($_POST['html'])) {
        $attached_items_count=count($uCat->attached_items_ar);
        for($i=0;$i<$attached_items_count;$i++) {
            $data=$uCat->attached_items_ar[$i];
            if($i) echo '. ';
            else echo '<br>'?>
            <a href="<?=u_sroot?>uCat/item/<?=$data['item_id']?>"><?=uString::sql2text($data['item_title'])?></a>
        <?}
    }
    else {?>
        <div class="form-horizontal">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" id="uCat_admin_items_item_filter<?=$filter_id?>" class="form-control" placeholder="Фильтр" onkeyup="uCat.items_filter(<?=$filter_id?>);">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button" onclick="uCat.items_filter(<?=$filter_id?>)"><span class="glyphicon glyphicon-search"></span></button>
                    </span>
                </div>
            </div>
        </div>
    <table class="table table-condensed table-hover table-striped" id="uCat_admin_items_item_list<?=$filter_id?>">
        <?if($_POST['type']=='unattached') {
            $all_items=$uCat->get_all_items();
            /** @noinspection PhpUndefinedMethodInspection */
            while($data=$all_items->fetch(PDO::FETCH_ASSOC)) {
                if(isset($attached[$data['item_id']])) continue; ?>
                <tr>
                    <td><?=$data['item_article_number']?></td>
                    <td><a href="<?=u_sroot?>uCat/item/<?=$data['item_id']?>" target="_blank"><?=uString::sql2text($data['item_title'])?></a></td>
                    <td><button class="btn btn-success btn-xs" onclick="uCat.attach_item_do(<?=$data['item_id']?>,'attach');"><span class="glyphicon glyphicon-plus"></span> Прикрепить</button></td>
                </tr>
            <?}
        }
        else {
            $attached_items_count=count($uCat->attached_items_ar);
            for($i=0;$i<$attached_items_count;$i++) {
                $data=$uCat->attached_items_ar[$i];?>
                <tr>
                    <td><?=$data['item_article_number'];?></td>
                    <td><a href="<?=u_sroot?>uCat/item/<?=$data['item_id']?>" target="_blank"><?=uString::sql2text($data['item_title'])?></a></td>
                    <td><button class="btn btn-danger btn-xs" onclick="uCat.attach_item_do(<?=$data['item_id']?>,'detach');"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
                </tr>
            <?}
        }?>
    </table>
    <?}?>