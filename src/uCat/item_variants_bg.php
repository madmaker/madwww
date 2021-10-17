<?php
namespace uCat\item;
use PDO;
use uCat\common;
use uCat_item_avatar;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
require_once "inc/item_avatar.php";

class variants {
    public $uSes;
    public $avatar;
    public $item_title;
    private $uCore;
    public $uFunc;
    public $item_id,$uCat,$enable_item_quantity;

    private function check_data() {
        if(!isset($_POST['item_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!$item_data=$this->uCat->item_id2data($this->item_id,"item_title")) $this->uFunc->error(0);
        $this->item_title=$item_data->item_title;

        if(!\uString::isDigits($this->item_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        $this->uCat=new common($this->uCore);
        $this->avatar=new uCat_item_avatar($this->uCore);
        $this->enable_item_quantity = (int)$this->uFunc->getConf('item_quantity_show','uCat');

        $this->check_data();
    }
}
$uCat=new variants($this);

$variants=$uCat->uCat->get_item_variants_pdo($uCat->item_id);
?>
<?if(isset($_POST['item_page'])) {
    if($uCat->uCat->has_variants($uCat->item_id)) {
        $options_number = (int)$uCat->uCat->has_options($uCat->item_id);
        if ($options_number) include "uCat/inc/options_table.php";
        else include "uCat/inc/variants_table.php";
    }
}
/*elseif(isset($_POST['admin_new_order'])) {?>
    <table class="table">
<?
while($var=$variants->fetch(\PDO::FETCH_OBJ)){
    if($avail_type_id=(int)$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_type_id!=2) {
        $base_type_id=(int)$uCat->uCat->item_type_id2data($uCat->uCat->var_type_id2data($var->var_type_id)->item_type_id)->base_type_id?>
    <tr>
        <td><?=\uString::sql2text($uCat->uCat->var_type_id2data($var->var_type_id)->var_type_title,1)?></td>
        <td><?=number_format ( $var->price , 0 , '.' , '')?> р. <?=(int)$var->inaccurate_price?'*':''?></td>
        <td><?=number_format ( $var->var_quantity, 0, '.', '')?></td>
        <td
            class="<?=$uCat->uCat->avail_type_id2class($avail_type_id)?> uTooltip"
            title="<?=$base_type_id==1?\uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1):$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_descr?>"
            ><?=$base_type_id==0?
                \uString::sql2text($uCat->uCat->avail_id2avail_data($var->avail_id)->avail_label,1):
                \uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1)
            ?>
        </td>
        <td><button class="btn btn-sm btn-primary" onclick="$('#uCat_test_input').val(<?=$var->var_id?>).click()">Добавить в заказ</button></td>
    </tr>
    <?}
}?>
    </table>
<p class="text-muted"><?=\uString::sql2text($this->uFunc->getConf("inaccurate_price_label","uCat"),1)?></p>
<?}*/
    else {?>
    <table class="table">
<?
$price_is_used=(int)$this->uFunc->getConf("price_is_used", "uCat");
$buy_button_show=(int)$this->uFunc->getConf("buy_button_show", "uCat");
/** @noinspection PhpUndefinedMethodInspection */
while($var=$variants->fetch(\PDO::FETCH_OBJ)){
    $avail_type_id=(int)$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_type_id;
    $var->request_price=(int)$var->request_price;
    $var->var_quantity=(float)$var->var_quantity;
    $var->inaccurate_price=(int)$var->inaccurate_price;
    $var->price=(float)$var->price;
    if($avail_type_id!=2) {
        $base_type_id=(int)$uCat->uCat->item_type_id2data($uCat->uCat->var_type_id2data($var->var_type_id)->item_type_id)->base_type_id?>
    <tr>
        <td><?=\uString::sql2text($uCat->uCat->var_type_id2data($var->var_type_id)->var_type_title,1)?></td>
        <?if((int)$this->uFunc->getConf("price_is_used","uCat")) {?>
        <td>
            <?if((int)$var->prev_price) {?>
            <span class="prev_price text-danger"><?=number_format ( $var->prev_price , 0 , '.' , '')?> </span>&nbsp;&nbsp;
            <?}?>
            <?=number_format ( $var->price , 0 , '.' , '')?> <?if(site_id==54) {?><span>Eur</span><?}
            else {?><span class="icon-rouble"></span><?}?> <?=$var->inaccurate_price?'*':''?>
        </td>
        <?}

        if($uCat->enable_item_quantity) {?>
        <td><small class="text-muted">Остаток:</small> <?=number_format($var->var_quantity, 0, '.', '')." ";
            if($unit_name = $uCat->uCat->unit_of_item($uCat->item_id)) {
                print $unit_name;
            }?>
        </td>
        <?}

        if((int)$this->uFunc->getConf("item_availability_show","uCat")) {?>
        <td
            class="<?=$uCat->uCat->avail_type_id2class($avail_type_id)?> uTooltip"
            title="<?=$base_type_id==1?\uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1):$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_descr?>"
            ><?=$base_type_id==0?
                \uString::sql2text($uCat->uCat->avail_id2avail_data($var->avail_id)->avail_label,1):
                \uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1)
            ?>
        </td>
        <?}?>
        <td>
            <?if($var->request_price||$avail_type_id===4) {?>
                <button class="btn btn-sm btn-default" onclick="uCat_request_price_form.openForm(<?=$uCat->item_id?>,0,'<?=rawurlencode(uString::sql2text($uCat->item_title, 1))?>')"><?=$var->request_price?'Запросить цену':'Заказать'?></button>
            <?}
            else if (
                $price_is_used&&
                $buy_button_show&&
                $avail_type_id!==2&&
                $avail_type_id!==3
            ) {
                if($uCat->enable_item_quantity&&$var->price) {?>
                <div class="input-group input-group-sm" style="float: left; margin-right: 20px;">
                    <input type="text" data-max="<?=$var->var_quantity?>" id="uCat_item_<?=$var->var_id?>_count" autocomplete="off"  class="items_count_spinner" value="1">
                </div>
                    <button class="btn btn-sm btn-primary <?=$var->var_quantity||!(int)$uCat->enable_item_quantity?"":"disabled"?>" data-variant="1" onclick="uCat_cart.buy_indicate_quantity(<?=$uCat->item_id?>,<?=$var->price?>,<?=$var->var_id?>, this)"><?=$uCat->uFunc->getConf("buy_btn_label","uCat")?></button>
                <?} else {?>
                <button class="btn btn-sm btn-primary <?=$var->var_quantity||!$uCat->enable_item_quantity?"":"disabled"?>" data-variant="1" onclick="uCat_cart.buy(<?=$uCat->item_id?>,<?=$var->price?>,<?=$var->var_id?>, this)"><?=$var->price?$uCat->uFunc->getConf("buy_btn_label","uCat"):$uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1)?></button>
                <?}
            }

            if(!(int)$var->request_price&&$var->inaccurate_price){?>
                <button onclick="uCat_request_price_form.openForm(<?=$uCat->item_id?>,<?=$var->var_id?>,'<?=rawurlencode(\uString::sql2text($uCat->uCat->item_id2data($uCat->item_id,"`item_title`")->item_title,1).' ('.\uString::sql2text($uCat->uCat->var_type_id2data($var->var_type_id)->var_type_title,1).')')?>')" class="btn btn-default btn-sm">Уточнить цену</button>
            <?}?>
        </td>
    </tr>
    <?}
}?>
    </table>
<p class="text-muted"><?=\uString::sql2text($this->uFunc->getConf("inaccurate_price_label","uCat"),1)?></p>
<?}
