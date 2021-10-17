<?php
namespace uCat;
require_once 'inc/item_avatar_new.php';
require_once 'uCat/classes/common.php';
require_once "processors/classes/uFunc.php";

use PDO;
use PDOException;
use uString;

class search {
    public $uCat;
    public $uFunc;
    public $item_fields;
    private $uSes;
    private $uCore,$item_fields_q_add,$item_fields_q_where,$req,$q_sects,$q_cats;
    public $field_id2title,$q_items,$q_fields,$item_id2cat_title,$item_id2sect_title,$item_id2cat_id,$item_id2sect_id,$field_type_id2style,$field_type_id2sql_type,$search_count,$avatar,
        $items_per_page,$curPage,$search_unsafe,
        $order,$sort;
    private function checkData(){
        if(isset($_GET['search'])) {
            $this->search_unsafe=trim($_GET['search']);
        }

        $this->order=$this->uFunc->getConf("def_sort_order","uCat");
        if(isset($_GET['order'])) {
            $order=&$_GET['order'];
            if($order=='desc'||$order=='DESC') $this->order='DESC';
        }
        else $_GET['order']=$this->order;

        $this->sort=$this->uFunc->getConf("def_sort_field","uCat");
        if(isset($_GET['sort'])) {
            $sort=&$_GET['sort'];
            if($sort=='item_price') $this->sort="item_price";
            elseif(strpos($sort,"field_")===0) {
                $field_id=substr($sort,6);
                if(uString::isDigits($field_id)) {
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                        cat_id
                        FROM
                        u235_cats_fields
                        WHERE
                        field_id=:field_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                        /** @noinspection PhpUndefinedMethodInspection */
                        if($stm->fetch(PDO::FETCH_OBJ)) $this->sort="field_".$field_id;
                    }
                    catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
                }
            }
        }
        else $_GET['sort']="item_title";

        $this->curPage=0;
        if(isset($_GET['page'])) {
            if(uString::isDigits($_GET['page'])) $this->curPage=$_GET['page'];
        }

        if(isset($_GET['sort'],$_GET['order'])) {
            if(!empty($_GET['sort'])&&!empty($_GET['order'])) {
                if(strpos($_GET['sort'],'item_')===0||strpos($_GET['sort'],'field_')===0) {
                    $sort=$_GET['sort'];
                    $order=$_GET['order'];
                }
            }
        }
    }
    private function get_site_fields() {
        $this->req="%".uString::replace4sqlLike(uString::text2sql($this->search_unsafe))."%";
//        $this->req="%п%";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_id,
            field_title,
            field_units,
            field_type_id,
            planelist_show
            FROM
            u235_fields
            WHERE
            field_type_id!=0 AND
            search_use=1 AND
            site_id=:site_id
            ORDER BY
            field_pos ASC,
            field_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();


        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        $this->item_fields_q_add='';
        $this->item_fields=[];
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$qr=$stm->fetch(PDO::FETCH_OBJ);$i++) {
            $this->item_fields[$i]=$qr;
            $this->field_id2title[$qr->field_id];
            $this->item_fields_q_add.="field_".$qr->field_id.",";
            $this->item_fields_q_where.="field_".$qr->field_id." LIKE :req OR ";
        }
    }
    private function get_field_types() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_type_id,
            field_sql_type,
            field_style
            FROM
            u235_fields_types
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($field=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->field_type_id2sql_type[$field->field_type_id]=$field->field_sql_type;
                $this->field_type_id2style[$field->field_type_id]=$field->field_style;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    private function search_items() {
        $this->items_per_page=$this->uFunc->getConf('items_on_search','uCat');

        if(!uString::isDigits($limit=$this->items_per_page)) $limit=20;

        if($this->uSes->access(25)) $q_items_avail_values="";
        else $q_items_avail_values="u235_items_avail_values.avail_type_id!=2 AND";

        if((int)$this->uFunc->getConf("search_in_item_descr","uCat")) $q_item_descr=" item_descr LIKE :req OR ";
        else $q_item_descr="";

        //1. search direct query in items only (fields values, item data)
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            ".$this->item_fields_q_add."
            item_id,
            item_article_number,
            item_img_time,
            item_title,
            item_url,
            item_descr,
            item_price,
            inaccurate_price,
            request_price,
            avail_label,
            avail_descr,
            avail_id,
            avail_type_id,
            has_variants,
            base_type_id
            FROM
            u235_items,
            u235_items_avail_values,
            items_types
            WHERE
            parts_autoadd=0 AND
            items_types.site_id=u235_items.site_id AND
            items_types.type_id=u235_items.item_type AND
            (".$this->item_fields_q_where."
            item_id LIKE :req OR
            item_article_number LIKE :req OR
            item_title LIKE :req OR
            ".$q_item_descr."
            item_keywords LIKE :req OR
            item_price LIKE :req) AND
            cat_count>0 AND
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            ".$q_items_avail_values."
            u235_items_avail_values.site_id=:site_id AND
            u235_items.site_id=:site_id
            ORDER BY
            ".$this->sort." ".$this->order."
            LIMIT ".($this->curPage*$limit).",".$limit."
            ");
            $site_id=site_id;
//            echo "LIMIT ".($this->curPage*$limit).",".$limit."";
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':req', $this->req,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_items=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

//        $this->search_count=300;
//        return 1;
        //get search item count
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            COUNT(DISTINCT item_id) AS item_count
            FROM
            u235_items,
            u235_items_avail_values
            WHERE
            parts_autoadd=0 AND
            (".$this->item_fields_q_where."
            item_title LIKE :req OR
            ".$q_item_descr."
            item_keywords LIKE :req OR
            item_price LIKE :req) AND
            cat_count>0 AND
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            ".$q_items_avail_values."
            u235_items_avail_values.site_id=:site_id AND
            u235_items.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':req', $this->req,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_OBJ);
            $this->search_count=$qr->item_count;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    private function get_sects_cats_list() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            item_id,
            u235_sects.sect_id,
            u235_cats.cat_id,
            sect_title,
            cat_title
            FROM
            u235_cats_items,
            u235_sects_cats,
            u235_sects,
            u235_cats
            WHERE
            u235_cats_items.cat_id=u235_cats.cat_id AND
            u235_sects_cats.cat_id=u235_cats.cat_id AND
            u235_sects_cats.sect_id=u235_sects.sect_id AND
            u235_cats_items.site_id=:site_id AND
            u235_sects_cats.site_id=:site_id AND
            u235_sects.site_id=:site_id AND
            u235_cats.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->item_id2cat_title[$qr->item_id]=uString::sql2text($qr->cat_title);
                $this->item_id2cat_id[$qr->item_id]=$qr->cat_id;
                $this->item_id2sect_title[$qr->item_id]=uString::sql2text($qr->sect_title);
                $this->item_id2sect_id[$qr->item_id]=$qr->sect_id;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    private function search_sects() {
        //1. search direct query in items only (fields values, item data)
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_title
            FROM
            u235_sects
            WHERE
            (sect_title LIKE :req OR
            sect_descr LIKE :req) AND
            cat_count>0 AND
            item_count>0 AND
            site_id=:site_id
            ORDER BY
            sect_pos ASC,
            sect_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':req', $this->req,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_sects=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    private function search_cats() {
        //1. search direct query in items only (fields values, item data)
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id,
            cat_title
            FROM
            u235_cats
            WHERE
            (cat_title LIKE :req OR
            cat_descr LIKE :req) AND
            sect_count>0 AND
            item_count>0 AND
            site_id=:site_id
            ORDER BY
            cat_pos ASC,
            cat_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':req', $this->req,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_cats=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }
    private function def_right_bar() {
        $this->search_sects();
        $this->search_cats();
        ob_start();

        $q_sects_count=count($this->q_sects);
        if($q_sects_count) {?>
            <div class="uCat_search">
            <h2>Разделы</h2>
                    <ul>
            <? for($i=0;$i<$q_sects_count;$i++) {
                $sect=$this->q_sects[$i];
                ?>
                <li><a href="/uCat/cats/<?=$sect->sect_id?>"><?=uString::sql2text($sect->sect_title,1)?></a></li>
            <?}?>
                    </ul>
            </div>
        <?}
        $q_cats_count=count($this->q_cats);
        if($q_cats_count) {?>
            <div class="uCat_search">
            <h2>Категории</h2>
                <ul>
                <? for($i=0;$i<$q_cats_count;$i++) {
                    $cat=$this->q_cats[$i];?>
                    <li><a href="/uCat/items/<?=$cat->cat_id?>"><?=uString::sql2text($cat->cat_title,1)?></a></li>
                <?}?>
                </ul>
            </div>
        <?}

        $this->uCore->page_rightBar=ob_get_contents();
        ob_end_clean();
    }
    public function insertPageNums($pageNumber) {
        $cnt='';
        $butNum=4;//number of buttons before and after
        if($pageNumber>1) {
            $cnt.='<ul class="pagination">';
            $start=0;
            $end=$pageNumber;
            if($pageNumber>$butNum*2) {
                $start=($this->curPage-$butNum)<0?0:($this->curPage-$butNum);
                $end=($this->curPage+$butNum)>$pageNumber?$pageNumber:($this->curPage+$butNum);
                if(($start+$end)<$pageNumber) $end=($start+$butNum*2)<$pageNumber?$start+$butNum*2:$pageNumber;
            }
            if($start>0) {
                $cnt.='<li><a href="/uCat/search?search='.$this->search_unsafe.'&page='.($start-1).'&sort='.$this->sort.'&order='.$this->order.'">&laquo;</a></li>';
            }
            for($i=$start;$i<$end;$i++) {
                $cnt.='<li '; if($this->curPage==$i) $cnt.='class="active"'; $cnt.='><a href="/uCat/search?search='.$this->search_unsafe.'&page='.$i.'&sort='.$this->sort.'&order='.$this->order.'">'.($i+1).'</a></li>';
            }
            if($end<$pageNumber) {
                $cnt.='<li><a href="/uCat/search?search='.$this->search_unsafe.'&page='.($end).'&sort='.$this->sort.'&order='.$this->order.'">&raquo;</a></li>';
            }
            $cnt.='</ul>';
        }
        return $cnt;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uCat=new common($this->uCore);
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->avatar=new \uCat_item_avatar($this->uCore);

        $this->checkData();

        $this->get_site_fields();
        $this->search_items();

        $this->get_sects_cats_list();
        $this->get_field_types();

        $this->def_right_bar();
    }
}
$uCat=new search($this);

$this->uFunc->incJs(u_sroot.'uCat/js/search.min.js');

$pageNumber=ceil($uCat->search_count/$uCat->items_per_page);

ob_start();

/** @noinspection PhpIncludeInspection */
include 'uCat/inc/request_price_form.php';

?>

    <div class="uCat_search uCat_items">
        <div class="items_container">
            <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
        <h1>Результаты поиска <span>(<?=$uCat->search_count?>)</span></h1>
            <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2) include "uCat/templates/search.php";?>
        <?=$uCat->insertPageNums($pageNumber)?>

<?if((int)$this->uFunc->getConf("show_sorting_in_items_lists","uCat")){?>
        <form>
            <label class="uCat_items_sort_order_label" for="uCat_sort_selectbox">Сортировка:</label>
            <select class="form-control" id="uCat_sort_selectbox" name="select" onchange="document.location='<?=u_sroot.'uCat/'.$this->page_name.'?search='.$uCat->search_unsafe.'&page='.$uCat->curPage?>&'+jQuery(this).val()">
                <? if($this->uFunc->getConf("price_is_used","uCat")=='1') {?>
                    <option value="sort=item_price&order=asc" <? if($uCat->sort=='item_price'&&$uCat->order=='ASC') echo "selected";?>>Цена, по возрастанию</option>
                    <option value="sort=item_price&order=desc" <? if($uCat->sort=='item_price'&&$uCat->order=='DESC') echo "selected";?>>Цена, по убыванию</option>
                <?}?>
                <option value="sort=item_title&order=asc" <? if($uCat->sort=='item_title'&&$uCat->order=='ASC') echo "selected";?>>Название, по возрастанию</option>
                <option value="sort=item_title&order=desc" <? if($uCat->sort=='item_title'&&$uCat->order=='DESC') echo "selected";?>>Название, по убыванию</option>
                <?
                $item_fields_count=count($uCat->item_fields);
                for($i=0;$i<$item_fields_count;$i++) {
                    $field=$uCat->item_fields[$i];
                    if($field->sort_show=='1') {
                        echo '<option value="sort=field_'.$field->field_id.'&order=asc" '.(($uCat->sort=='field_'.$field->field_id&&$uCat->order=='ASC')?"selected":"").'>'.uString::sql2text($field->field_title).', по возрастанию</option>';
                        echo '<option value="sort=field_'.$field->field_id.'&order=desc" '.(($uCat->sort=='field_'.$field->field_id&&$uCat->order=='DESC')?"selected":"").'>'.uString::sql2text($field->field_title).', по убыванию</option>';
                    }
                }?>
            </select>
        </form>
    <?}?>

        <div class="item_list">
        <?
        $q_items_count=count($uCat->q_items);
        for($i=0;$i<$q_items_count;$i++) {
            $item=$uCat->q_items[$i]?>
            <div class="row <?=$item->avail_type_id=='2'?'bg-info':''?>">
                <div class="col-md-4 col-xs-5">
                    <a href="<?=u_sroot.'uCat/item/'.($item->item_url!=''?uString::sql2text($item->item_url,true):$item->item_id)?>">
                        <img alt="" src="<?=$uCat->avatar->get_avatar(300,$item->item_id,$item->item_img_time)?>">
                    </a>
                    <p class="clearfix"> </p>
                    <p class="item_art"><span>Арт:</span> <?=$item->item_article_number?></p>
                    <p class="clearfix"> </p>

                    <?
                    if((int)$item->base_type_id) {?>
                            <span class="text-success uTooltip" title="<?=uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1)?>">
                                <?=uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1)?>
                            </span>
                    <?}
                    elseif((int)$this->uFunc->getConf('item_availability_show','uCat')) {?>
                            <span class="<?=$uCat->uCat->avail_type_id2class($item->avail_type_id)?> uTooltip" title="<?=uString::sql2text($uCat->uCat->avail_id2avail_data($item->avail_id)->avail_descr,1)?>">
                                <?=uString::sql2text($uCat->uCat->avail_id2avail_data($item->avail_id)->avail_label,1)?>
                            </span>
                    <?}?>
                    <p class="clearfix"> </p>
                    <!--PRICE-->
                    <? if($this->uFunc->getConf("price_is_used","uCat")=='1') {?>
                        <div class="price <?=$item->inaccurate_price=='1'?'uTooltip':''?>" <?=$item->inaccurate_price=='1'?'title="'.htmlspecialchars(strip_tags($this->uFunc->getConf('inaccurate_price_descr','uCat'))).'"':''?>>

                            <?
                            $currency='р';
                            if(site_id==54) {
                                $currency='Eur';
                            }
                            if($item->request_price!='1') {?>
                                <?=number_format ( $item->item_price , 0 ,'.' , ' ' )?> <?=$currency?><?=(int)$item->inaccurate_price?'*':''?>
                                <?if((int)$item->inaccurate_price&&!(int)$item->request_price) {?>
                                    <button class="btn btn-default btn-sm" onclick="uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode(uString::sql2text($item->item_title))?>')">Уточнить цену</button>
                                <?}
                            }?>

                            <p class="clearfix"> </p>

                            <?if((int)$item->request_price) {?>
                                <button class="btn btn-default pull-left" onclick="uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode(uString::sql2text($item->item_title))?>')">Запросить цену</button>
                            <?}?>
                        </div>
                    <?}?>

                    <!-- BUY BTN-->
                    <?if(
                        (int)$this->uFunc->getConf('buy_button_show','uCat')&&
                        (int)$this->uFunc->getConf("price_is_used","uCat")&&
                        $item->avail_type_id!='2'&&
                        $item->avail_type_id!='3'&&
                        $item->request_price=='0'){?>
                        <div class="buy_btn pull-left">
                            <button class="btn btn-primary" onclick="<?
                            if((int)$item->has_variants) {?>uCat_cart.show_item_variants(<?=$item->item_id?>)<?}
                            else {?>uCat_cart.buy(<?=$item->item_id?>,<?=$item->item_price?>)<?}
                            ?>"><?=$uCat->uFunc->getConf("buy_btn_label","uCat")?></button>
                        </div>
                    <?}?>
                    <?if((int)$item->has_variants){?>
                        &nbsp;<a class="btn btn-link uTooltip" title="<?=uString::sql2text($this->uFunc->getConf('item_has_variants_label','uCat'))?>" onclick="uCat_cart.show_item_variants(<?=$item->item_id?>)" ><span class="icon-tag"></span> еще варианты</a>
                    <?}?>

                </div>
                <div class="item_info col-md-8 col-xs-7">
                    <p class="cat_title"><a href="<?=u_sroot.'uCat/cats/'.$uCat->item_id2sect_id[$item->item_id]?>"><?=$uCat->item_id2sect_title[$item->item_id]?>.</a> <a href="<?=u_sroot.'uCat/items/'.$uCat->item_id2cat_id[$item->item_id]?>"><?=$uCat->item_id2cat_title[$item->item_id]?></a></p>
                    <h1 class="item_title">
                        <a href="<?=u_sroot.'uCat/item/'.($item->item_url!=''?uString::sql2text($item->item_url,true):$item->item_id)?>">
                            <?=$item->item_title?>
                        </a>
                    </h1>

                    <div class="fields"><?
                        $item_fields_count=count($uCat->item_fields);
                        for($j=0;$j<$item_fields_count;$j++) {
                            $field=$uCat->item_fields[$j];

                            $item_field='field_'.$field->field_id;
                            if($field->planelist_show=='1'&&!empty($item->$item_field)) {
                                echo '<div>';
                                echo '<label>'.uString::sql2text($field->field_title).'</label> ';
                                if($uCat->field_type_id2style[$field->field_type_id]=='integer'||
                                    $uCat->field_type_id2style[$field->field_type_id]=='double') {
                                    echo $item->$item_field;
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='text line') {
                                    echo uString::removeHTML(uString::sql2text($item->$item_field,true));
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='multiline') {
                                    echo nl2br(uString::sql2text($item->$item_field,true));
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='date') {
                                    echo date('d.m.Y',$item->$item_field);
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='datetime') {
                                    echo date('d.m.Y H:i',$item->$item_field);
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='link') {
                                    $val=uString::sql2text($item->$item_field,1);
                                    echo $val;
                                }
                                elseif($uCat->field_type_id2style[$field->field_type_id]=='file') {
                                    echo '<a href="'.u_sroot.'uCat/field_files/'.site_id.'/'.$field->field_id.'/'.$item->item_id.'/'.$item->$item_field.'">'.$item->$item_field.'</a>';
                                }
                                echo ' '.uString::sql2text($field->field_units).'&nbsp;&nbsp;';

                                echo '</div>';
                            }
                        }?>
                    </div>


                    <div class="item_descr">
                        <?if(!empty($item->item_descr)) {
                            $cut_letters=$this->uFunc->getConf("items_item_descr_cut_letters","uCat");
                            $item_descr=uString::sql2text($item->item_descr,true);
                            $txt_ar=explode('<!-- pagebreak -->',$item_descr);
                            if(count($txt_ar)>1) $item_descr=$txt_ar[0];
                            if($cut_letters!='0'&&uString::isDigits($cut_letters)) {
                                echo mb_substr(strip_tags($item_descr),0,$cut_letters,'UTF-8');
                                if(count($txt_ar)<2) {
                                    print '... <a href="' . u_sroot . 'uCat/item/' . $item->item_id . '" style="opacity: 70%"> читать дальше...</a>';
                                }
                            }
                            else echo uString::sql2text($item->item_descr,true);
                        }
                        ?>
                    </div>

                </div>
            </div>
        <?}?>
        </div>
        <?if(!$q_items_count) {?>
            <p>Попробуйте вводить текст без запятых, точек и лишних символов.<br>Используйте единственное число и именительный падеж.<br>Также можно использовать только часть слова.</p>
        <?}
        echo $uCat->insertPageNums($pageNumber)?>
    </div>
        <div class="inaccurate_price_label"><?=$this->uFunc->getConf('inaccurate_price_label','uCat')?></div>
    </div>

<?include_once 'uCat/dialogs/uCat_cart.php';?>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/template.php";
