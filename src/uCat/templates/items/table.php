<script type="text/javascript">
    var options_ar = [];
    var values_ar=[];
    var option_id2i = [];
    var value_id2i=[];
    var j;
    var k;
    var n;

    var variants_options_values=[];
    var variants_data=[];
    var def_var_options_values=[];
</script>

<table class="item_list uCat_items_table">

    <tr class="header">
        <th class="td0" onclick="uCat.filter_set_sort('<?='sort=item_title&order='.(($_GET['sort']=='item_title'&&$_GET['order']=='asc')?'desc':'asc');?>')">
            Наименование
        </th><?//uCAt
        $i=1;
        $col_count=0;
        foreach ($uCat->q_fields as $key => $cat_fields) {
            if((int)$cat_fields->tablelist_show===1) {?>
                <th class="td<? echo $i;?>" onclick="uCat.filter_set_sort('<?='sort=field_'.$cat_fields->field_id.'&order='.(($_GET['sort']=='field_'.$cat_fields->field_id&&$_GET['order']=='asc')?'desc':'asc');?>')">
                    <? echo uString::sql2text($cat_fields->field_title);?><? if(!empty($cat_fields->field_units)) {?>,<br><? echo uString::sql2text($cat_fields->field_units); }?>
                </th>
                <? $i=1-$i;
                $col_count++;
                $last_col_ind=$i;?>
            <?}
        };
        reset($uCat->q_fields);?>
    </tr>

    <?
//    if(isset($uCat->q_items_pdo)) $uCat->q_items->execute();
//    else mysqli_data_seek($uCat->q_items,0);

//    function fetch_items(&$uCat) {
//        $cat=&$uCat;
//        if(isset($cat->q_items_pdo)) return $uCat->q_items->fetch(PDO::FETCH_OBJ);
//        else return $cat->q_items->fetch_object();
//    }

    //uCat config
    $item_availability_show=$this->uFunc->getConf("item_availability_show","uCat")=='1';
    $price_is_used=$this->uFunc->getConf("price_is_used","uCat")=='1';
    $inaccurate_price_descr=htmlspecialchars(strip_tags($this->uFunc->getConf('inaccurate_price_descr','uCat')));

    /** @noinspection PhpUndefinedMethodInspection */
    for($i=0; $item=$uCat->q_items->fetch(PDO::FETCH_OBJ);) {
        if($item->item_url!='') $item_url=uString::sql2text($item->item_url,true);
        else $item_url=$item->item_id;

        $item_avatar=$uCat->avatar->get_avatar(200,$item->item_id,$item->item_img_time);
        $avail_style=$uCat->uCat_common->avail_type_id2class($item->avail_type_id);

        if((int)$item->base_type_id==1) $availability_descr=uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1);
        else $availability_descr=$item->avail_descr;

        if((int)$item->base_type_id==1) $availability_label=uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1);
        else $availability_label=$item->avail_label;


        $item_title=uString::sql2text($item->item_title,1);
        ?>
        <tr class="<?=$item->avail_type_id=='2'?'bg-info':''?>"  id="uCat_item_<?=$item->item_id?>">
            <td class="item_avatar_container" rowspan="2">
                <a id="uCat_item_avatar_a_<?=$item->item_id?>" href="<?=u_sroot?>uCat/item/<?=$item_url?>">
                    <img id="uCat_item_avatar_img_<?=$item->item_id?>" src="<?=$item_avatar?>" alt="<?=$item_title?>">
                </a>

                <?if($uCat->uFunc->getConf("show_item_article_number","uCat")){?>
                <p class="item_art"><span>Арт:</span> <span id="uCat_item_article_number_<?=$item->item_id?>"><?=$item->item_article_number?></span></p>
                <?}
                //$activ_evotor = (int)$this->uFunc->getConf('used_activation_token','uCat','return false', site_id);
                $item_quantity_show=(int)$this->uFunc->getConf("item_quantity_show","uCat");
                if($item_quantity_show){?>
                    <p class="item_quantity_<?=$item->item_id?>"><span>Остаток:</span> <?=$item->quantity?> <?=$uCat->uCat_common->unit_id2unit_name($item->unit_id);?></p>
                <?}?>

                <div class="availability_container">
                    <!--AVAILABILITY-->
                    <? if($item_availability_show) {?>
                        <div class="availability <?=$avail_style?>" title="<?=$availability_descr?>">
                            <span><?=$availability_label?></span>
                        </div>
                    <?}?>
                </div>

                <!--PRICE-->
                <? if($price_is_used) {?>
                    <div class="price-container" <?=($item->inaccurate_price=='1')?('title="'.$inaccurate_price_descr.'"'):''?>>
                        <?if($item->request_price!='1') {
                            $item_price=number_format ( $item->item_price ,  (count(explode('.',$item->item_price))>1?2:0) ,'.' , ' ' );?>
                            <span class="price"><?=$item_price?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?><span id="item_inaccurate_price_marker_<?=$item->item_id?>"><?=(int)$item->inaccurate_price?'*':''?></span></span>
                        <?}?>

                        <?if((int)$uCat->uFunc->getConf("item_prev_price_show","uCat")&&(int)$item->prev_price) {?>
                            <div id="uCat_prev_price_<?=$item->item_id?>" class="prev_price text-primary"><?=number_format ( $item->prev_price , (count(explode('.',$item->prev_price))>1?2:0) ,'.' , ' ' )?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?></div>
                        <?}?>
                    </div>
                <?}?>

                <?
                $options_number = (int)$uCat->uCat->has_options($item->item_id);
                if((int)$item->has_variants) {?>
                    <script type="text/javascript">
                        uCat.var_selected[<?=$item->item_id?>]=<?=(int)$item->has_variants?$uCat->uCat->item_id2default_variant_id($item->item_id):0?>;
                        uCat.var_selected_price[<?=$item->item_id?>]=<?=$item->item_price;?>;
                    </script>
                    <div class="variants_container">
                        <?$uCat->item_id = $item->item_id;
                        $avatar_style="table";
                        if ($options_number) include "uCat/inc/options_table.php";
                        else include "uCat/inc/variants_table.php";?>
                    </div>
                <?}?>

                <!-- BUY BTN-->
                <?if(
                    (int)$this->uFunc->getConf('buy_button_show','uCat')&&
                    (int)$this->uFunc->getConf("price_is_used","uCat")){?>

                    <?if((int)$item->inaccurate_price&&!(int)$item->request_price) {?>
                        <button class="btn btn-default btn-sm inaccurate_price_btn" id="inaccurate_price_btn_<?=$item->item_id?>" onclick="uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode($item_title)?>')">Уточнить цену</button>
                    <?}?>
                    <div class="buy_btn">
                        <?if($uCat->enable_item_quantity /*&& !(int)$item->has_variants */&& $uCat->enable_tiles_plus_and_minus) { ?>
                            <div class="input-group-sm">
                                <input type="text" data-max="<?=$item->quantity?>" id="uCat_item_<?=$item->item_id?>_count" autocomplete="off"  class="items_count_spinner" value="1">
                            </div>
                            <button class="btn btn-primary btn-sm" data-variant="0" onclick="uCat_cart.buy_indicate_quantity(<?=$item->item_id?>,<?=$item->item_price?>)"><?=$uCat->uFunc->getConf("buy_btn_label","uCat")?></button>
                        <?}
                        else{?>
                            <button id="buy_btn_<?=$item->item_id?>" class="
                                    btn btn-primary btn-sm
                                    <?if(
                                (!(float)$item->quantity&&$uCat->enable_item_quantity)||
                                (int)$item->avail_type_id===2||
                                (int)$item->avail_type_id===3
                            ) {?>disabled<?}?>
                                    " data-variant="0" onclick="<?
                            if((int)$item->request_price||$item->avail_type_id==4) {?>
                                    uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode($item_title)?>')
                            <?}
                            else {?>
                            <?if($uCat->enable_item_quantity /*&& !(int)$item->has_variants */&& $uCat->enable_tiles_plus_and_minus) { ?>
                                    uCat_cart.buy_indicate_quantity(<?=$item->item_id?>,<?=$item->item_price?>)"><?=$uCat->uFunc->getConf("buy_btn_label","uCat")?>
                                <?}
                                else {?>
                                    uCat_cart.buy(<?=$item->item_id?>,<?=$item->item_price?>)
                                <?}?>
                                <?}?>
                                "><?
                                if((float)$item->item_price&&!(int)$item->request_price&&$item->avail_type_id!=4) echo $uCat->uFunc->getConf("buy_btn_label","uCat");
                                elseif((int)$item->request_price) echo "Запросить цену";
                                elseif($item->avail_type_id==4) echo "Заказать";
                                elseif(!(float)$item->item_price) print $uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1);
                                ?></button>
                        <?}?>
                    </div>
                <?}?>


            </td>
            <td colspan="<?=$col_count?>" class="item_title">
                <a href="<?=u_sroot.'uCat/item/'?><?
                if($item->item_url!='') echo uString::sql2text($item->item_url,true);
                else echo $item->item_id
                ?>">
                    <?=$item_title?>
                </a>
            </td>
        </tr>
        <tr class="<?=$item->avail_type_id=='2'?'bg-info':''?>">
            <?
            for($j=$i=0;$i<count($uCat->item_fields);$i++) {?>
                <td class="<?=$item->avail_type_id=='2'?'bg-info':('td'.($j=1-$j))?>"><?
                    echo $i;
                    if(isset($uCat->item_fields[$i])) {
                        $field = $uCat->item_fields_id2data[$uCat->item_fields[$i]];
                        $field_name = 'field_' . $uCat->item_fields[$i];
                        if ($uCat->field_type_id2sql_type[$field->field_type_id] == 'TINYTEXT' || $uCat->field_type_id2sql_type[$field->field_type_id] == 'TEXT') $val = uString::sql2text($item->$field_name, true);
                        else $val = $item->$field_name;

                        if ($uCat->field_type_id2style[$field->field_type_id] == 'date') {
                            if (!empty($val)) $val = date('d.m.Y', $val);
                            else $val = '';
                        } elseif ($uCat->field_type_id2style[$field->field_type_id] == 'datetime') {
                            if (!empty($val)) $val = date('d.m.Y H:i', $val);
                            else $val = '';
                        } elseif ($uCat->field_type_id2style[$field->field_type_id] == 'link') {
                            $val = uString::sql2text($val, 1);
                        }

                        echo $val;
                    }
                    ?></td>
            <?}?>
        </tr>
    <?}?>
</table>
<div class="inaccurate_price_label"><?=$this->uFunc->getConf('inaccurate_price_label','uCat')?></div>
