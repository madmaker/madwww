<?php
require_once "inc/cat_avatar.php";
require_once "inc/sect_avatar.php";
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uDrive/classes/common.php";
require_once "uCat/classes/common.php";

class cats {
    public $uFunc;
    public $uDrive;
    public $uSes;
    private $uCat;
    private $uCore;
    public $sect_id,$sect_id_tmp,$sect,$q_cats,$cat_avatar,$sect_avatar,$q_sects,$switcher_empty_partitions;

    private function error(/** @noinspection PhpUnusedParameterInspection */$reason) {
        //echo $reason;
        header('Location: '.u_sroot);
        exit;
    }
    private function check_data() {
        $q_sect_info="sect_id,
            sect_url,
            sect_title,
            seo_title,
            sect_descr,
            seo_descr,
            sect_keywords,
            show_cats_descr,
            sect_avatar_time,
            uDrive_folder_id,
            primary_sect_id";

        if(!isset($this->uCore->url_prop[1])) $this->error(10);
        $this->sect_id=$this->uCore->url_prop[1];
        if(uString::isDigits($this->sect_id)) {
            try {//Информация о разделе достается в 3-х местах: по sect_id и по sect_url
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            ".$q_sect_info."
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->sect=$stm->fetch(PDO::FETCH_OBJ)) {
                header('Location: '.u_sroot.'uCat/sects');
                exit;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
        else {
            $sect_url=uString::text2sql($this->sect_id);

            try {//Информация о разделе достается в 3-х местах: по sect_id и по sect_url
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                ".$q_sect_info."
                FROM
                u235_sects
                WHERE
                sect_url=:sect_url AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_url', $sect_url,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if(!$this->sect=$stm->fetch(PDO::FETCH_OBJ)) {
                $sect_url=rawurldecode($this->sect_id);
                $sect_url=uString::text2sql($sect_url);

                try {//Информация о разделе достается в 3-х местах: по sect_id и по sect_url
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                    ".$q_sect_info."
                    FROM
                    u235_sects
                    WHERE
                    sect_url=:sect_url AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_url', $sect_url,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$this->sect=$stm->fetch(PDO::FETCH_OBJ)) {
                    header('Location: '.u_sroot.'uCat/sects');
                    exit;
                }
            }

            $this->sect_id=$this->sect->sect_id;


        }
            $this->get_uDrive_folder_id();
    }

    private function increase_views_counter($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_sects
            SET
            views_counter=views_counter+1
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583243141'/*.$e->getMessage()*/);}
    }

    private function define_breadcrumb() {
        if((int)$this->uFunc->getConf("Show link to sects in bc","uCat")) $this->uCore->uBc->add_info->html='<li><a href="'.u_sroot.'uCat/sects">Каталог</a></li>';
        else $this->uCore->uBc->add_info->html='';

        $bc_array[] = '<li class="active"><a id="uCat_sect_breadcrumb" href="'.u_sroot.'uCat/cats/'.(strlen($this->sect->sect_url)?$this->sect->sect_url:$this->sect_id).'">'.uString::sql2text($this->sect->sect_title,1).'</a></li>';

        $this->sect_id_tmp = $this->sect_id;
        for($i=0; (int)$this->sect->primary_sect_id; $i++) {
            $bc_array[] = $this->create_breadcrumb($this->sect->primary_sect_id)    ;
            if(!isset($this->sect->primary_sect_id)) break;
        }
        $this->sect_id = $this->sect_id_tmp;
        if(!$this->get_sect_info($this->sect_id_tmp))  $this->uFunc->error('60');

        $bc_array = array_reverse($bc_array);

        foreach ($bc_array as $key => $value) {
            $this->uCore->uBc->add_info->html.=$value;
        }
    }

    private function create_breadcrumb($parent_sect_id) {
        $this->sect_id = $parent_sect_id=(int)$parent_sect_id;
        if(!$this->get_sect_info($parent_sect_id)) {
            $parent_sect_id=(int)$this->uCat->set_auto_primary_sect_id4sect($this->sect_id,$parent_sect_id);
        }
        if($parent_sect_id) {
            return $res = '<li class="active"><a id="uCat_sect_breadcrumb" href="' . u_sroot . 'uCat/cats/' . (strlen($this->sect->sect_url) ? $this->sect->sect_url : $parent_sect_id) . '">' . uString::sql2text($this->sect->sect_title, 1) . '</a></li>';
        }

        return "";
    }

    private function get_uDrive_folder_id() {
        //define uDrive sect default folder
        if($this->sect->uDrive_folder_id=='0') {
            $uDrive_uCat_sects_folder_id=$this->uDrive->get_module_folder_id("uCat_sects");
            $sect_title=trim(uString::sanitize_filename(uString::sql2text($this->sect->sect_title)));
            if(!strlen($sect_title)) $sect_title='Раздел '.$this->sect_id;
            $this->sect->uDrive_folder_id=$this->uDrive->create_folder($sect_title,$uDrive_uCat_sects_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_sects
                SET
                uDrive_folder_id=:folder_id
                WHERE
                sect_id=:sect_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $this->sect->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
    }

    private function define_sort_order() {
        $cats_def_sort_order=$this->uFunc->getConf("cats_def_sort_order","uCat");

        if($cats_def_sort_order=="item_number_za") return  "item_count DESC, cat_title ASC";
        elseif($cats_def_sort_order=="alphabet_az") return  "cat_title ASC, item_count DESC";
        elseif($cats_def_sort_order=="alphabet_za") return  "cat_title DESC, item_count DESC";
        else return  "item_count ASC, cat_title ASC";//item_number_az


    }
    public function get_sect_info($sect_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_url,
            sect_title,
            seo_title,
            sect_descr,
            seo_descr,
            sect_keywords,
            show_cats_descr,
            sect_avatar_time,
            uDrive_folder_id,
            primary_sect_id,
            views_counter
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->sect=$stm->fetch(PDO::FETCH_OBJ)) return false;
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        $this->get_uDrive_folder_id();

        return true;
    }
    public function get_sect_cats(){
        $sql=$this->define_sort_order();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_cats.cat_id,
            cat_title,
            cat_url,
            cat_descr,
            cat_avatar_time,
            cat_pos,
            item_count,
            show_on_hp
            FROM
            u235_cats
            JOIN
            u235_sects_cats
            ON
            u235_cats.cat_id=u235_sects_cats.cat_id AND 
            u235_sects_cats.site_id=u235_cats.site_id
            WHERE
            item_count>0 AND
            u235_sects_cats.sect_id=:sect_id AND 
            u235_cats.site_id=:site_id AND 
            u235_cats.cat_id!=0
            ORDER BY
            cat_pos ASC,
            ".$sql."
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_cats = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    public function get_sect_cats_admin(){
        $sql=$this->define_sort_order();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_cats.cat_id,
            cat_title,
            cat_url,
            cat_descr,
            cat_avatar_time,
            cat_pos,
            item_count,
            show_on_hp
            FROM
            u235_cats
            JOIN
            u235_sects_cats
            ON
            u235_cats.cat_id=u235_sects_cats.cat_id AND 
            u235_sects_cats.site_id=u235_cats.site_id
            WHERE
            u235_sects_cats.sect_id=:sect_id AND 
            u235_cats.site_id=:site_id 
            ORDER BY
            cat_pos ASC,
            ".$sql."
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_cats = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }

    public function get_sect_sects_admin(){
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
            INNER JOIN sects_sects
              ON
                u235_sects.sect_id=sects_sects.child_sect_id AND
                u235_sects.site_id=sects_sects.site_id
            WHERE
            u235_sects.site_id=:site_id AND
            sects_sects.parent_sect_id=:sect_id
            ORDER BY
            sect_pos ASC,
            sect_title ASC
            ");

            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_sects = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }

    public function get_sect_sects(){
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
            INNER JOIN sects_sects
              ON
                u235_sects.sect_id=sects_sects.child_sect_id AND
                u235_sects.site_id=sects_sects.site_id
            WHERE
            item_count>:item_count AND
            u235_sects.site_id=:site_id AND
            sects_sects.parent_sect_id=:sect_id
            ORDER BY
            sect_pos ASC,
            sect_title ASC
            ");

            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':item_count', $item_count,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_sects = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uDrive=new \uDrive\common($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->check_data();

        $this->cat_avatar=new uCat_cat_avatar($this->uCore);
        $this->sect_avatar=new uCat_sect_avatar($this->uCore);
        $this->switcher_empty_partitions = (int)$this->uFunc->getConf('show_empty_partitions','uCat','return false',site_id);

        $this->uCat=new \uCat\common($this->uCore);

        if($this->uSes->access(25)) {
            $this->get_sect_cats_admin();
            $this->get_sect_sects_admin();
        }
        else {
            $this->get_sect_cats();
            $this->get_sect_sects();

            if((count($this->q_cats)+count($this->q_sects))<2) {
                foreach($this->q_sects as $key=>$sect) {
                    header('Location: '.u_sroot.'uCat/cats/'.$sect->sect_id);
                    echo $sect->sect_id;
                    exit;
                }
                foreach($this->q_cats as $key=>$cat) {
                    header('Location: '.u_sroot.'uCat/items/'.$cat->cat_id);
                    exit;
                }
            }
        }

        $this->define_breadcrumb();

        $this->increase_views_counter();
    }
}
$uCat=new cats($this);

ob_start();
if(!isset($_GET['results_only'])) {
    $this->uFunc->incJs(u_sroot.'uCat/js/cats.min.js');
    if($uCat->uSes->access(25)) {
        include_once 'uDrive/inc/my_drive_manager.php';?>
        <div id="uDrive_my_drive_uploader_init"></div>
        <script type="text/javascript">
            if(typeof uCat_cats_admin==="undefined") uCat_cats_admin={};
            if(typeof uDrive_manager==="undefined") uDrive_manager={};

            uCat_cats_admin.uDrive_folder_id=<?=$uCat->sect->uDrive_folder_id;?>;

            $(document).ready(function() {
                uDrive_manager.init('uDrive_my_drive_uploader',<?=$uCat->sect->uDrive_folder_id;?>, 1, "uCat_cats_admin.insert_tinymce_url", 'uCat', 'sect',<?=$uCat->sect_id?>);
            });
        </script>
        <?

        $this->uFunc->incJs(u_sroot.'uCat/js/cats_admin.min.js');
        $this->uFunc->incJs(u_sroot.'uCat/js/sects_admin.min.js');
        $this->uFunc->incJs(u_sroot.'uCat/js/sects.min.js');
    }

    if(!empty($uCat->sect->seo_title)) $this->page['page_title']=uString::sql2text($uCat->sect->seo_title);
    else $this->page['page_title']=uString::sql2text($uCat->sect->sect_title).'. Каталог';
    if(!empty($uCat->sect->seo_descr)) $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->sect->seo_descr))))));
    else $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->sect->sect_descr))))));
    if(!empty($uCat->sect->sect_keywords)) $this->page['meta_keywords']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->sect->sect_keywords))))));

    if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
    <h1 class="page-header"><span id="uCat_sect_title"><?=$uCat->sect->sect_title?></span></h1>

    <?
    if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2) include "uCat/templates/search.php";
    if($uCat->uFunc->getConf("sect_descr_place","uCat")=="top") {?>
    <div class="descr row">
        <div id="uCat_sect_descr"><?=uString::sql2text($uCat->sect->sect_descr,true);?></div>
    </div>
<?}?>

    <div class="uCat_cats">
        <div class="cats_list row"><div class="col-md-12 <?=$uCat->sect->show_cats_descr=='1'?'':'uCat_list'?> <?=((int)$uCat->uFunc->getConf("show_sects_fullheight","uCat"))?"sects_fullheight":""?>" id="uCat_sect_cats_list">
<?}?>
            <?
            $uCat_sect_show_left_bar=(int)$uCat->uFunc->getConf("sect_show_left_bar","uCat");
            foreach($uCat->q_sects as $key=>$value) {?>
                <div class="cat col-md-4 col-sm-6 col-lg-<?=$uCat_sect_show_left_bar?4:3?>  <?=site_id==63/*TODO-nik87 сделать, чтобы была возможность переключать: в 2 или в 1 колонку */?'col-xs-6':''?>">
                    <div class="content <?=$value->item_count=='0'?'text-info':''?>">
                        <a href="<?=u_sroot?>uCat/cats/<?=(empty($value->sect_url)?$value->sect_id:uString::sql2text($value->sect_url))?>" class="thumbnail" style="<?=site_id<50&&site_id!=35?'height: 180px; padding-bottom:5px; border: none;':''?> <?if((int)$uCat->uFunc->getConf("show_sects_fullheight","uCat")) {
                            echo " background-image:url('";
                            echo $uCat->sect_avatar->get_avatar('sects_list',$value->sect_id,$value->sect_avatar_time);
                            echo "');  background-size:cover;";
                        }?>">
                            <?if(!(int)$uCat->uFunc->getConf("show_sects_fullheight","uCat")) {?>
                            <img class="avatar" src="<?=$uCat->sect_avatar->get_avatar('sects_list',$value->sect_id,$value->sect_avatar_time);?>" style="<?=site_id<50&&site_id!=35?'max-height: 165px;':''?>">
                            <?}?>
                        </a>
                        <div class="caption descr">
                            <div class="title" style="font-size: 1.2em;font-weight: normal;text-align: center;">
                                <?if($uCat->uSes->access(25)){?>
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

            //uCAt
            foreach($uCat->q_cats as $key=>$cats) {
//            while($cats=$uCat->q_cats->fetch(PDO::FETCH_OBJ)) {
            ?>
                <?if($uCat->sect->show_cats_descr=='1') {?>
                    <div class="row"><div class="cat_with_descr">
                            <div class="col-md-5 col-sm-6 col-lg-4 thumbnail <?=$cats->item_count=='0'?'bg-info':''?>">
                                <a href="<? echo u_sroot.'uCat/items/'.(empty($cats->cat_url)?$cats->cat_id:uString::sql2text($cats->cat_url));?>">
                                    <img src="<?=$uCat->cat_avatar->get_avatar('list_w_descr',$cats->cat_id,$cats->cat_avatar_time);?>">
                                </a>
                            </div>
                            <div class="col-md-7 col-sm-6 col-lg-8 descr">
                                <h1 class="title">
                                    <?if($uCat->uSes->access(25)){?>
                                        <button class="<?/*if(!isset($_GET['results_only'])) {*/?>u235_eip<?/*}*/?> btn btn-default btn-xs uTooltip" title="Изменить положение категории" onclick="uCat.cat_pos_init(<?=$cats->cat_id?>,<?=$cats->cat_pos?>)"><span class="icon-switch"></span></button>
                                        <button class="u235_eip btn btn-xs uTooltip <?=$cats->show_on_hp=='1'?'btn-primary':'btn-default'?>" onclick="uCat.show_on_homepage(<?=$cats->cat_id?>)" title="Показывать в виджетах"><span class="glyphicon glyphicon-tags"></span></button>
                                    <?}?>
                                    <a href="<?=u_sroot?>uCat/items/<?=(empty($cats->cat_url)?$cats->cat_id:uString::sql2text($cats->cat_url))?>">
                                        <?=uString::sql2text($cats->cat_title);?>
                                    </a>
                                </h1>
                                <div class="descr"><?=uString::sql2text($cats->cat_descr,true)?></div>
                            </div>
                        </div></div>
                <?}
                else {?>
                    <div class="cat col-sm-6 col-md-4 col-lg-<?=$uCat_sect_show_left_bar?4:3?> cat_without_descr  <?=site_id==63/*TODO-nik87 сделать, чтобы была возможность переключать: в 2 или в 1 колонку */?'col-xs-6':''?>">
                        <div class="content <?=$cats->item_count=='0'?'text-info':''?>">
                            <a href="<? echo u_sroot.'uCat/items/'.(empty($cats->cat_url)?$cats->cat_id:uString::sql2text($cats->cat_url));?>" class="thumbnail" style="<?=site_id<50&&site_id!=35?'height: 180px; padding-bottom:5px; border: none;':''?> <?if((int)$uCat->uFunc->getConf("show_sects_fullheight","uCat")) {
                                echo " background-image:url('";
                                echo $uCat->cat_avatar->get_avatar('list_w_descr',$cats->cat_id,$cats->cat_avatar_time);
                                echo "'); background-size:cover;";
                            }?>">
                            <?if(!(int)$uCat->uFunc->getConf("show_sects_fullheight","uCat")) {?>
                                <img class="avatar" src="<?=$uCat->cat_avatar->get_avatar('list_no_descr',$cats->cat_id,$cats->cat_avatar_time);?>"  style="<?=site_id<50&&site_id!=35?'max-height: 165px;':''?>">
                            <?}?>
                            </a>
                            <div class="caption">
                                <div class="cat_title">
                                    <?if($uCat->uSes->access(25)){?>
                                        <button class="<?/*if(!isset($_GET['results_only'])) {*/?>u235_eip<?/*}*/?> btn btn-default btn-xs uTooltip" title="Изменить положение категории" onclick="uCat.cat_pos_init(<?=$cats->cat_id?>,<?=$cats->cat_pos?>)"><span class="icon-switch"></span></button>
                                        <button class="<?/*if(!isset($_GET['results_only'])) {*/?>u235_eip<?/*}*/?> btn btn-xs uTooltip <?=$cats->show_on_hp=='1'?'btn-primary':'btn-default'?>" title="Показывать в виджетах" onclick="uCat.show_on_homepage(<?=$cats->cat_id?>)"><span class="glyphicon glyphicon-tags"></span></button>
                                    <?}?>
                                    <a href="<?=u_sroot?>uCat/items/<?=(empty($cats->cat_url)?$cats->cat_id:uString::sql2text($cats->cat_url))?>" class="default-color"><?=uString::sql2text($cats->cat_title)?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?}?>
            <?}
if(!isset($_GET['results_only'])) {?>
            </div></div>
        <?if($uCat->uFunc->getConf("sect_descr_place","uCat")=="bottom") {?>
        <div class="descr row">
            <div id="uCat_sect_descr"><?=uString::sql2text($uCat->sect->sect_descr,true);?></div>
        </div>
        <?}?>

        <div id="item_views_counter" class="text-muted pull-right"><span class="icon-eye"></span> <?=$uCat->sect->views_counter ?></div>
    </div>

    <script type="text/javascript">
    <?if($uCat->uSes->access(25)){//admin part
        //tinymce
        $this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');?>
            if(typeof uCat==="undefined") uCat={};

            uCat.sect_id=<?=$uCat->sect_id?>;

            uCat.sect_title="<?=rawurlencode(uString::sql2text($uCat->sect->sect_title,true))?>";
            uCat.sect_url="<?=rawurlencode(uString::sql2text($uCat->sect->sect_url,true))?>";
            uCat.seo_title="<?=rawurlencode(uString::sql2text($uCat->sect->seo_title,true))?>";
            uCat.seo_descr="<?=rawurlencode(uString::sql2text($uCat->sect->seo_descr,true))?>";
            uCat.sect_keywords="<?=rawurlencode(uString::sql2text($uCat->sect->sect_keywords,true))?>";
            uCat.show_cats_descr=<?=$uCat->sect->show_cats_descr?>;
            uCat.site_title="<?=rawurlencode(site_name)?>";
        </script>
        <?include 'dialogs/sect_admin.php';?>
        <?include 'dialogs/sects_admin.php';?>
    <?}?>
    <script type="text/javascript">
    var item_quantity_show=<?=$uCat->uFunc->getConf('item_quantity_show','uCat');?>;
    </script>
<?}

$this->page_content=ob_get_contents();
ob_end_clean();

if(!isset($_GET['results_only'])) {
    /** @noinspection PhpIncludeInspection */
    include "templates/template.php";
}
else echo $this->page_content;
