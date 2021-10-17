<?php
if(!$this->access(25)) die('forbidden');
if(!isset($_POST['cat_id'],$_POST['type'])) $this->error(1);
$cat_id=$_POST['cat_id'];
if(!uString::isDigits($cat_id)) $this->error(2);

if(!$query=$this->query('uCat',"SELECT DISTINCT
    `u235_sects`.`sect_id`,
    `u235_sects`.`sect_title`
    FROM
    `u235_sects_cats`,
    `u235_sects`
    WHERE
    `u235_sects_cats`.`cat_id`='".$cat_id."' AND
    `u235_sects`.`sect_id`=`u235_sects_cats`.`sect_id` AND
    `u235_sects`.`site_id`='".site_id."' AND
    `u235_sects_cats`.`site_id`='".site_id."'
    ORDER BY
    `u235_sects`.`sect_title` ASC
    ")) $this->error(3);

if($_POST['type']=='unattached') {
    while($data=$query->fetch_assoc()) {
        $attached[$data['sect_id']]=1;
    }
    if(!$query=$this->query('uCat',"SELECT
    `sect_id`,
    `sect_title`
    FROM
    `u235_sects`
    WHERE
    `site_id`='".site_id."'
    ORDER BY
    `sect_title` ASC
    ")) $this->error(4);
}
$filter_id=time();?>
<table class="table table-condensed table-hover table-striped uCat_cats_sect_list">
    <?
    if($_POST['type']=='unattached') {
        while($data=$query->fetch_assoc()) {
            if(isset($attached[$data['sect_id']])) continue; ?>
            <tr>
                <td><?=$data['sect_id'];?></td>
                <td><a href="<?=u_sroot?>uCat/cats/<?=$data['sect_id']?>" target="_blank"><?=uString::sql2text($data['sect_title']);?></a></td>
                <td><button type="button" class="btn btn-success btn-xs" onclick="uCat.attachSect_do(<?=$data['sect_id']?>,'attach');"><span class="glyphicon glyphicon-plus"></span> Прикрепить</button></td>
            </tr>
        <?}
    }
    else {
        while($data=$query->fetch_assoc()) {?>
            <tr>
                <td><? echo $data['sect_id'];?></td>
                <td><a href="<?=u_sroot?>uCat/cats/<?=$data['sect_id']?>" target="_blank"><?=uString::sql2text($data['sect_title']);?></a></td>
                <td><button type="button" class="btn btn-danger btn-xs" onclick="uCat.attachSect_do(<?=$data['sect_id']?>,'detach');"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
            </tr>
        <?}
    }?>
</table>
