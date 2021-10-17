<?php
require_once 'inc/art_avatar.php';

if(!$this->access(25)) die('forbidden');

if(!isset($_POST['item_id'],$_POST['type'])) $this->error(1);
$item_id=$_POST['item_id'];
if(!uString::isDigits($item_id)) $this->error(2);

if(!$query=$this->query('uCat',"SELECT DISTINCT
    `u235_articles`.`art_id`,
    `u235_articles`.`art_title`,
    `art_avatar_time`,
    `art_text`
    FROM
    `u235_articles_items`,
    `u235_articles`
    WHERE
    `u235_articles_items`.`item_id`='".$item_id."' AND
    `u235_articles`.`art_id`=`u235_articles_items`.`art_id` AND
    `u235_articles`.`site_id`='".site_id."' AND
    `u235_articles_items`.`site_id`='".site_id."'
    ORDER BY
    `u235_articles`.`art_id` DESC
    ")) $this->error(3);

if($_POST['type']=='unattached') {
    while($data=$query->fetch_assoc()) {
        $attached[$data['art_id']]=1;
    }
    if(!$query=$this->query('uCat',"SELECT
    `art_id`,
    `art_title`
    FROM
    `u235_articles`
    WHERE
    `site_id`='".site_id."'
    ORDER BY
    `u235_articles`.`art_id` DESC
    ")) $this->error(4);
}
if($_POST['type']=='unattached') {?>
    <h3>Неприкрепленные статьи</h3>
<?}
elseif($_POST['type']!='html') {?>
    <h3>Прикрепленные статьи</h3>
<?}?>
<?if(mysqli_num_rows($query)) {
    $els_exists=false;
    if($_POST['type']!='html') {
        $filter_id=time();?>
    <div class="form-horizontal">
        <div class="form-group">
            <div class="input-group">
                <input type="text" id="uCat_items_art_filter<?=$filter_id?>" class="form-control" placeholder="Фильтр" onkeyup="uCat.arts_filter(<?=$filter_id?>)">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uCat.arts_filter(<?=$filter_id?>)"></span></button>
                    </span>
            </div>
        </div>
    </div>
    <table class="table table-condensed table-striped table-hover" id="uCat_items_art_list<?=$filter_id?>">
    <?}
        if($_POST['type']=='unattached') {
            while($data=$query->fetch_assoc()) {
                if(isset($attached[$data['art_id']])) continue;
                $els_exists=true;?>
                <tr>
                    <td><?=$data['art_id'];?></td>
                    <td><a href="<?=u_sroot?>uCat/article/<?=$data['art_id']?>" target="_blank"><?=uString::sql2text($data['art_title']);?></a></td>
                    <td><button class="btn btn-default btn-xs btn-success" onclick="uCat.attachArt_do(<? echo $data['art_id'];?>,<? echo $item_id;?>,'attach');"><span class="glyphicon glyphicon-plus"></span> Прикрепить</button></td>
                </tr>
            <?}
        }
        elseif($_POST['type']=='html') {?>
            <?if(mysqli_num_rows($query)>1) {?>
            <div class="customNavigation">
                <a class="btn prev">&nbsp;</a>
                <a class="btn next">&nbsp;</a>
            </div>
            <?}?>
            <h2 class="title"><?=$this->uFunc->getConf("arts_label","uCat")?></h2>
            <div id="uCat_articles_slider" class="owl-carousel uCat_articles_slider">
                <?//uCAt
                        while($arts=$query->fetch_object()) {
                            $art_avatar=new uCat_art_avatar($this);?>
                            <div class="item">
                                    <a href="<?=u_sroot?>uCat/article/<?=$arts->art_id?>" style="<?if($arts->art_avatar_time!='0') {
                                        if($art_avatar=$art_avatar->get_avatar(640,$arts->art_id,$arts->art_avatar_time)) {
                                        }
                                        else {
                                            $art_avatar='http://localhost/nofile#';//http://localhost/nofile# потому что блять браузеры дописывают всякую херню к пустому src или к src с #
                                            ?>display:none<?
                                        }
                                    }
                                    else {
                                        $art_avatar='http://localhost/nofile#';
                                        ?>display:none<?
                                    }
                                    ?>">
                                        <img id="uCat_item_art_avatar_<?=$arts->art_id?>" title="<?=htmlspecialchars(strip_tags(uString::sql2text($arts->art_title)))?>" class="avatar" src="<?=$art_avatar?>">
                                    </a>
                                <h3 class="title"><a href="<?=u_sroot?>uCat/article/<?=$arts->art_id?>"><span id="uCat_item_art_title_<?=$arts->art_id?>"><?=uString::sql2text($arts->art_title)?></span></a>
                                    <button class="btn btn-default btn-xs uTooltip pull-right u235_eip" title="Редактировать статью" onclick="uCat.edit_art(<?=$arts->art_id?>)"><span class="glyphicon glyphicon-pencil"></span></button></h3>
                                <div id="uCat_item_art_<?=$arts->art_id?>"><?
                                    $txt=uString::sql2text($arts->art_text,true);
                                    $pos=mb_strpos($txt,'<!-- my page break -->',0, 'UTF-8');
                                    if(!$pos) {
                                        $pos=mb_strpos($txt,'<!-- pagebreak -->',0, 'UTF-8');
                                        if(!$pos) {
                                            echo mb_substr(strip_tags($txt),0,600,'UTF-8').'...';
                                        }
                                        else echo mb_substr($txt,0,$pos,'UTF-8');
                                    }
                                    else echo mb_substr($txt,0,$pos,'UTF-8');?>
                                </div>
                            </div>
                        <?}?>
                    </div>
        <?}
        else {
            while($data=$query->fetch_object()) {
                $els_exists=true;?>
                <tr>
                    <td><?=$data->art_id;?></td>
                    <td><a href="<?=u_sroot?>uCat/article/<?=$data->art_id?>"><?=uString::sql2text($data->art_title);?></a></td>
                    <td><button class="btn btn-default btn-xs btn-danger" onclick="uCat.attachArt_do(<?=$data->art_id;?>,<?=$item_id?>,'detach');"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
                </tr>
            <?}
        }
    if($_POST['type']!='html') {?>
    </table>
    <?}
    if(!$els_exists&&$_POST['type']!='html')  {?>
        <p class="text-info">Нет элементов для отображения</p>
    <?}
}
elseif($_POST['type']!='html') {?>
    <p class="text-info">Нет элементов для отображения</p>
<?}?>
