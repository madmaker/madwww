<?php
require_once "inc/sect_avatar.php";
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class sects {
    public $q_child_sect;
    public $uFunc;
    private $uCore;
    public $uSes,$sect_id,$sect,$q_sects,$sect_avatar,$switcher,$sect_obj;

    public function check_data() {
        $this->switcher = (int)$this->uFunc->getConf('show_or_hide_child_sects','uCat','return false',site_id);

        if($this->uSes->access(25)) {
            if($this->switcher) {
                $this->get_sects_admin_switcher_on();
            }
            else {
                $this->get_sects_admin();
                $this->get_child_sects_admin();
            }
        }
        else {
            if($this->switcher) {
                $this->get_sects_switcher_on();
            }
            else {
                $this->get_sects();
            }
        }
    }

    public function get_sects_attached() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT 
            u235_sects.sect_id,
            u235_sects.sect_title
            FROM
            u235_sects
            INNER JOIN sects_sects
            ON
            u235_sects.sect_id=sects_sects.child_sect_id AND
            u235_sects.site_id=sects_sects.site_id
            WHERE
            u235_sects.site_id=:site_id
            ORDER BY
            sect_title ASC
            ");

            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1'/*.$e->getMessage()*/);}
    }

    public function get_sects(){
        $item_count = 0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_sects.sect_id,
            u235_sects.sect_title,
            u235_sects.sect_url,
            u235_sects.sect_descr,
            u235_sects.sect_avatar_time,
            u235_sects.sect_pos,
            u235_sects.item_count,
            u235_sects.show_in_menu
            FROM
            u235_sects
            LEFT JOIN sects_sects
            ON
              u235_sects.sect_id=sects_sects.child_sect_id AND
              u235_sects.site_id=sects_sects.site_id
            WHERE
            item_count>:item_count AND
            u235_sects.site_id=:site_id AND 
            sects_sects.child_sect_id IS NULL AND 
            u235_sects.sect_id!=0
            ORDER BY
            sect_pos ASC,
            item_count DESC,
            sect_title ASC
            ");

            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':item_count', $item_count, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('2'/*.$e->getMessage()*/);}
    }

    public function get_sects_switcher_on(){
        $item_count = 0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_title,
            sect_url,
            sect_descr,
            sect_avatar_time,
            sect_pos,
            item_count,
            show_in_menu
            FROM
            u235_sects
            WHERE
            item_count>:item_count AND
            site_id=:site_id
            ORDER BY
            sect_pos ASC,
            item_count DESC,
            sect_title ASC
            ");

            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':item_count', $item_count, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('3'/*.$e->getMessage()*/);}
    }

    public function get_sects_admin(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_sects.sect_id,
            u235_sects.sect_title,
            u235_sects.sect_url,
            u235_sects.sect_descr,
            u235_sects.sect_avatar_time,
            u235_sects.sect_pos,
            u235_sects.item_count,
            u235_sects.show_in_menu
            FROM
            u235_sects
            LEFT JOIN sects_sects
            ON
              u235_sects.sect_id=sects_sects.child_sect_id AND
              u235_sects.site_id=sects_sects.site_id
            WHERE
            u235_sects.site_id=:site_id AND 
            sects_sects.child_sect_id IS NULL
            ORDER BY
            sect_pos ASC,
            item_count DESC,
            sect_title ASC
            ");

            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('4'/*.$e->getMessage()*/);}
    }

    public function get_sects_admin_switcher_on(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_title,
            sect_url,
            sect_descr,
            sect_avatar_time,
            sect_pos,
            item_count,
            show_in_menu
            FROM
            u235_sects
            WHERE
            site_id=:site_id
            ORDER BY
            sect_pos ASC,
            item_count DESC,
            sect_title ASC
            ");

            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('5'/*.$e->getMessage()*/);}
    }

    private function get_child_sects_admin() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_child_sect=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT 
            u235_sects.sect_id,
            u235_sects.sect_title,
            u235_sects.sect_url,
            u235_sects.sect_descr,
            u235_sects.sect_avatar_time,
            u235_sects.sect_pos,
            u235_sects.item_count,
            u235_sects.show_in_menu
            FROM
            u235_sects
            LEFT JOIN sects_sects
            ON
              u235_sects.sect_id=sects_sects.child_sect_id AND
              u235_sects.site_id=sects_sects.site_id
            WHERE
            u235_sects.site_id=:site_id AND 
            sects_sects.child_sect_id IS NOT NULL
            ORDER BY
            sect_pos ASC,
            item_count DESC,
            sect_title ASC
            ");

            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->q_child_sect->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_child_sect->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('6'/*.$e->getMessage()*/);}
    }

    public function print_row($value,$col_lg_x) {?>
        <div class="sect col-md-6 col-sm-12 col-xs-12 col-lg-<?=$col_lg_x?>">
            <div class="content <?=$value->item_count=='0'?'text-info':''?>">
                <a href="<?=u_sroot?>uCat/cats/<?=(empty($value->sect_url)?$value->sect_id:uString::sql2text($value->sect_url))?>" class="thumbnail" style="<?=site_id<50&&site_id!=35&&site_id!=5?'height: 180px; padding-bottom:5px; border: none;':''?> <?if((int)$this->uFunc->getConf("show_sects_fullheight","uCat")) {
                    echo " background-image:url('";
                    echo $this->sect_avatar->get_avatar('sects_list',$value->sect_id,$value->sect_avatar_time);
                    echo "');  background-size:cover;";
                }?>">
                    <?if(!(int)$this->uFunc->getConf("show_sects_fullheight","uCat")) {?>
                        <div class="sect_avatar_container"><div class="sect_avatar_container2">
                            <img alt="" class="avatar" src="<?=$this->sect_avatar->get_avatar('sects_list',$value->sect_id,$value->sect_avatar_time);?>" style="<?=site_id<50&&site_id!=35&&site_id!=5?'max-height: 165px;':''?>">
                        </div></div>
                    <?}?>
                </a>
                <div class="caption">
                    <div class="sect_title">
                        <?if($this->uSes->access(25)){?>
                            <button class="<?/*if(!isset($_GET['results_only'])) {*/?>u235_eip<?/*}*/?> btn btn-default btn-xs uTooltip" title="Изменить положение раздела" onclick="uCat.sect_pos_init(<?=$value->sect_id?>,<?=$value->sect_pos?>)">
                                <span class="icon-switch"></span>
                            </button>
                            <button class="<?/*if(!isset($_GET['results_only'])) {*/?>u235_eip<?/*}*/?> btn btn-xs uTooltip <?=$value->show_in_menu=='1'?'btn-primary':'btn-default'?>" title="Отображать в меню" onclick="uCat.show_in_menu(<?=$value->sect_id?>)"><span class="glyphicon glyphicon-tags"></span></button>
                        <?}?>
                        <a class="default-color" href="<?=u_sroot?>uCat/cats/<?=(empty($value->sect_url)?$value->sect_id:uString::sql2text($value->sect_url))?>"><?=uString::sql2text($value->sect_title)?></a>
                    </div>
                </div>
            </div>
        </div>
    <?}

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->sect_avatar=new uCat_sect_avatar($this->uCore);

        $this->check_data();
    }
}
$uCat=new sects($this);

ob_start();
if(!isset($_GET['results_only'])) {
    $this->uFunc->incJs(u_sroot.'uCat/js/sects.min.js');
    if($uCat->uSes->access(25)) {
        $this->uFunc->incJs(u_sroot.'uCat/js/sects_admin.min.js');
    }

    $this->page_breads='<a href="'.u_sroot.'">Главная</a>&gt;
    <a href="'.u_sroot.'uCat/sects">Каталог</a>';?>

    <div class="uCat_sects">
    <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
    <h1 class="page-header">Каталог</h1>
    <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2) include "uCat/templates/search.php";?>

    <div class="sects_list row <?=((int)$uCat->uFunc->getConf("show_sects_fullheight","uCat"))?"sects_fullheight":""?>"><div class="col-md-12 uCat_sect_list" id="uCat_sect_list">
<?}?>
    <div class="uCat_list <?=((int)$uCat->uFunc->getConf("show_sects_fullheight","uCat"))?"sects_fullheight":""?>">
            <?//uCAt
            $number_of_sects_per_row=(int)$this->uFunc->getConf("number_of_sects_per_row","uCat");
            if($number_of_sects_per_row>6) $number_of_sects_per_row=6;
            elseif($number_of_sects_per_row==5) $number_of_sects_per_row=4;
            elseif(!$number_of_sects_per_row) $number_of_sects_per_row=2;

            $col_lg_x=12/$number_of_sects_per_row;
            foreach($uCat->sect_obj as $key=>$value) {
                $uCat->print_row($value,$col_lg_x);
            }?>
    </div></div>
    <div class="col-md-12 uCat_sect_list" id="uCat_sect_sects_list">
    <div class="uCat_list">
        <?
        if(isset($uCat->q_child_sect)) {
            print "<h3>Подразделы</h3>";
            /** @noinspection PhpUndefinedMethodInspection */
            while ($sect=$uCat->q_child_sect->fetch(PDO::FETCH_OBJ)) {
                $uCat->print_row($sect,$col_lg_x);
            }
        }?>
    </div>
    <?if(!isset($_GET['results_only'])) {?>
    </div>

    </div></div>

    <?if($uCat->uSes->access(25)){?>
        <?include 'dialogs/sects_admin.php';?>
    <?}
}

$this->page_content=ob_get_contents();
ob_end_clean();

if(!isset($_GET['results_only'])) {
    include "templates/template.php";
}
else echo $this->page_content;