<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class load_yandex_market_cat_settings_bg {
    private $yandex_cat_id;
    private $cat_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["cat_id"])) $this->uFunc->error(10);
        $this->cat_id=(int)$_POST["cat_id"];
        if(!$cat_conf=$this->get_cat_conf($this->cat_id)) $this->uFunc->error(20);
        $this->yandex_cat_id=(int)$cat_conf->yandex_cat_id;
    }
    private function get_yandex_cats() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_id,
            cat_title
            FROM 
            yandex_cats 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_cat_conf($cat_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            yandex_cat_id 
            FROM 
            u235_cats 
            WHERE 
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("FORBIDDEN");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $yandex_cats=$this->get_yandex_cats();?>
        <label>Укажите, какая это категория в Яндекс Маркете</label>
        <p class="text-muted">Если не указывать, то Яндекс постарается определить автоматически</p>
        <div class="col-md-12">
            <div class="form-horizontal">
                <div class="form-group">
                    <div class="input-group">
                        <input type="text" id="uCat_yandex_cats_filter" class="form-control" placeholder="Фильтр" onkeyup="uCat_items_admin.yandex_cats_filter()">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button"><span class="icon-search" onclick="uCat_items_admin.yandex_cats_filter()"></span></button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div id="uCat_yandex_cats_list">
                <table class="table table-condensed">
                    <tr id="uCat_yandex_cat_0" <?=!$this->yandex_cat_id?'class="bg-success"':''?> data-cat_id="0"><td>Определить автоматически</td></tr>
                    <?
                    while($cat=$yandex_cats->fetch(PDO::FETCH_OBJ)) {?>
                        <tr id="uCat_yandex_cat_<?=$cat->cat_id?>" <?=$this->yandex_cat_id==(int)$cat->cat_id?'class="bg-success"':''?> data-cat_id="<?=$cat->cat_id?>"><td><?=$cat->cat_title?></td></tr>
                    <?}?>
                </table>
            </div>
        </div>
    <?}
}
new load_yandex_market_cat_settings_bg($this);