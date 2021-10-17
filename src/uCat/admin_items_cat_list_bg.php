<?php
if(!$this->access(25)) die('forbidden');

if(!isset($_POST['item_id'],$_POST['type'])) $this->error(1);
$item_id=$_POST['item_id'];
if(!uString::isDigits($item_id)) $this->error(2);

if(!$query=$this->query('uCat',"SELECT DISTINCT
    `u235_cats`.`cat_id`,
    `u235_cats`.`cat_title`
    FROM
    `u235_cats_items`,
    `u235_cats`
    WHERE
    `u235_cats_items`.`item_id`='".$item_id."' AND
    `u235_cats`.`cat_id`=`u235_cats_items`.`cat_id` AND
    `u235_cats`.`site_id`='".site_id."' AND
    `u235_cats_items`.`site_id`='".site_id."'
    ORDER BY
    `u235_cats`.`cat_title` ASC
    ")) $this->error(3);

if($_POST['type']=='unattached') {
    while($data=$query->fetch_assoc()) {
        $attached[$data['cat_id']]=1;
    }
    if(!$query=$this->query('uCat',"SELECT
    `cat_id`,
    `cat_title`
    FROM
    `u235_cats`
    WHERE
    `site_id`='".site_id."'
    ORDER BY
    `cat_title` ASC
    ")) $this->error(4);
}
$filter_id=time();
?>
<table class="table table-condensed table-hover table-striped uCat_item_admin_cats_list">
    <?
    if($_POST['type']=='unattached') {
        while($data=$query->fetch_assoc()) {
            if(isset($attached[$data['cat_id']])) continue; ?>
            <tr>
                <td><?=$data['cat_id']?></td>
                <td><a href="<?=u_sroot?>uCat/items/<?=$data['cat_id']?>" target="_blank"><?=uString::sql2text($data['cat_title'])?></a></td>
                <td><button class="btn btn-success btn-xs" onclick="uCat.attachCat_do(<?=$data['cat_id']?>,'attach');"><span class="glyphicon glyphicon-plus"></span> Прикрепить</button></td>
            </tr>
        <?}
    }
    else {
        while($data=$query->fetch_assoc()) {?>
            <tr>
                <td><?=$data['cat_id']?></td>
                <td><a href="<?=u_sroot?>uCat/items/<?=$data['cat_id']?>" target="_blank"><?=uString::sql2text($data['cat_title'])?></a></td>
                <td><button class="btn btn-danger btn-xs" onclick="uCat.attachCat_do(<?=$data['cat_id']?>,'detach');"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
            </tr>
        <?}
    }?>
</table>
