<?php
class uCat_admin_cats_field_list {
    private $uCore,
        $cat_id,$item_id,$field_place_id2title;
    private function check_data() {
        if(!isset($_POST['type'])) $this->uCore->error(10);
        if(!isset($_POST['cat_id'])&&!isset($_POST['item_id'])) $this->uCore->error(20);
        if(isset($_POST['cat_id'])) {
            $this->cat_id=$_POST['cat_id'];
            if(!uString::isDigits($this->cat_id)) $this->uCore->error(30);
        }
        else {
            $this->cat_id=false;
            $this->item_id=$_POST['item_id'];
            if(!uString::isDigits($this->item_id)) $this->uCore->error(40);
        }
    }
    private function get_fields() {
        if(!$this->cat_id) {
            //get item's cats
            if(!$query=$this->uCore->query("uCat","SELECT
            `cat_id`
            FROM
            `u235_cats_items`
            WHERE
            `item_id`='".$this->item_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(50);
            if(!mysqli_num_rows($query)) die('<p class="text-danger">Сначала нужно <button class="btn btn-default" onclick="uCat.item_attach2cat_instead_of_add_field()">прикрепить товар</button> хотя бы к одной категории каталога</p>');
            $q_cats='';
            while($item=$query->fetch_object()) {
                $q_cats.=" `cat_id`='".$item->cat_id."' OR";
            }
            $q_cats='('.substr ( $q_cats, 0 , -3 ).')';
        }
        else $q_cats="`cat_id`='".$this->cat_id."'";

        if(!$query=$this->uCore->query('uCat',"SELECT DISTINCT
        `u235_fields`.`field_id`,
        `field_title`,
        `field_comment`,
        `field_place_id`
        FROM
        `u235_cats_fields`,
        `u235_fields`
        WHERE
        ".$q_cats." AND
        `u235_fields`.`field_id`=`u235_cats_fields`.`field_id` AND
        `u235_fields`.`site_id`='".site_id."' AND
        `u235_cats_fields`.`site_id`='".site_id."'
        ORDER BY
        `field_title` ASC
        ")) $this->error(60);

        if($_POST['type']=='unattached') {
            while($data=$query->fetch_assoc()) {
                $attached[$data['field_id']]=1;
            }
            if(!$query=$this->uCore->query('uCat',"SELECT
            `field_id`,
            `field_title`,
            `field_comment`,
            `field_place_id`
            FROM
            `u235_fields`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `field_title` ASC
            ")) $this->error(70);
        }

        $filter_id=time();?>

        <div class="form-horizontal">
            <div class="form-group">
                <div class="input-group">
                    <input type="text" id="uCat_arts_field_filter<?=$filter_id?>" class="form-control" placeholder="Фильтр" onkeyup="uCat.fields_filter(<?=$filter_id?>)">
                    <span class="input-group-btn">
                        <button class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uCat.fields_filter(<?=$filter_id?>)"></span></button>
                    </span>
                </div>
            </div>
        </div>
        <table class="table table-condensed table-hover table-striped" id="uCat_arts_field_list<?=$filter_id?>">
            <tr><th colspan="2">Характеристика</th><th colspan="2">Комментарий</th></tr>
            <?
            if($_POST['type']=='unattached') {
                while($data=$query->fetch_assoc()) {
                    if(isset($attached[$data['field_id']])) continue; ?>
                    <tr>
                        <td><button type="button" class="btn btn-default btn-xs" onclick="uCat.edit_field(<?=$data['field_id']?>)"><span class="glyphicon glyphicon-pencil"></span></button> &nbsp; <?=$data['field_id'];?></td>
                        <td><?=$data['field_title'];?></td>
                        <td class="text-muted"><?=$data['field_comment'];?></td>
                        <td><button class="btn btn-xs btn-success" onclick="uCat.attachFields_do(<?=$data['field_id'];?>,'attach');"><span class="glyphicon glyphicon-plus"></span> <?=$this->field_place_id2title[$data['field_place_id']]?></button></td>
                    </tr>
                <?}
            }
            else {
                while($data=$query->fetch_assoc()) {?>
                    <tr>
                        <td><button type="button" class="btn btn-default btn-xs" onclick="uCat.edit_field(<?=$data['field_id']?>)"><span class="glyphicon glyphicon-pencil"></span></button> &nbsp; <?=$data['field_id'];?></td>
                        <td><?=$data['field_title'];?></td>
                        <td class="text-muted"><?=$data['field_comment'];?></td>
                        <td><button class="btn btn-xs btn-danger" onclick="uCat.attachFields_do(<?=$data['field_id'];?>,'detach');"><span class="glyphicon glyphicon-minus"></span> Открепить</button></td>
                    </tr>
                <?}
            }?>
        </table>
        <div class="bs-callout bs-callout-primary">Прикрепляя и открепляя характеристики вы добавляете/убираете их ко всем товарам, прикрепленным к тем же категорям каталога, что и текущий товар.</div>
        <div class="bs-callout bs-callout-default">Характеристики, которые долго никуда не прикреплены, удаляются автоматически</div>
    <?}
    private function get_fields_places() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `place_id`,
        `place_title`
        FROM
        `u235_fields_places`
        ")) $this->uCore->error(80);
        while($place=$query->fetch_object())
            $this->field_place_id2title[$place->place_id]=uString::sql2text($place->place_title,true);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(25)) die('forbidden');

        $this->check_data();
        $this->get_fields_places();
        $this->get_fields();
    }
}
$uCat=new uCat_admin_cats_field_list($this);
