<?php
require_once 'uCat/inc/item_avatar_new.php';
if(!isset($this->uCore)) {
    $this->uCore =& $this;
}
$uCat->avatar=new uCat_item_avatar($this->uCore);

require_once "translator/translator.php";
/** @noinspection PhpFullyQualifiedNameUsageInspection */
$translator=new \translator\translator(site_lang,"uCat/templates/cart/cart_content.php");

/** @noinspection MissingIssetImplementationInspection */
if(isset($uCat->uSes)) {
    $uSes =& $uCat->uSes;
}
else {
    $uSes = $this->uSes;
}

if($this->uCore->mod==="uCat"&&$this->uCore->page_name==="cart"&&$uSes->access(25)) {
    if (
        $uCat->order_status === "new"||
        $uCat->order_status === "items selected"||
        $uCat->order_status === "order is processed"||
        $uCat->order_status === "order is confirmed" ||
        (
                ($uCat->payment_method===1||$uCat->payment_method===2)&&
                $uCat->order_status !== "order completed"&&
                $uCat->order_status !== "order canceled"
        )
    ) {?>
<div>
    <button id="uCat_cart_add_article" class="btn btn-default btn-sm" data-container="body" data-toggle="popover" data-placement="bottom">Добавить артикул</button>&nbsp;&nbsp;&nbsp;&nbsp;
<!--    <button id="uCat_cart_delete_all_items" class="btn btn-default btn-sm" onclick="uCat_cart_page.delete_item(2,0)">Очистить корзину</button>-->
</div>
    <?}
} ?>


<div class="items_group">
    <?
    $currency='р';
    if(site_id==54) $currency='EUR';
    ?>

    <?php
    $total_price=$total_item_count=0;
    /** @noinspection PhpUndefinedVariableInspection */
    for(;
        $item;
        $item=$q_items->fetch(PDO::FETCH_OBJ)) {
        if((int)$item->var_id) {
            $var_data=$uCat->uCat->var_id2data($item->var_id);
            $item_data=$uCat->uCat->item_id2data($item->item_id,"
            `item_img_time`,
            `item_title`,
            `item_descr`,
            `item_url`,
            `has_variants`,
            `unit_id`
            ");
            $var_id=$item->var_id;
            if($var_data) {
                $avail_id=(int)$var_data->avail_id;
                $price=(float)$var_data->price;
                $inaccurate_price=(float)$var_data->inaccurate_price;
                $request_price=(float)$var_data->request_price;
                $quantity = (float)$var_data->var_quantity;
                $item_img_time=(int)$var_data->img_time;
            }
            else {
                $item_data->item_title='<b class="bg-danger">Этого варианта товара больше нет в магазине.</b> '.$item_data->item_title;
                $var_data=new stdClass();
                $var_data->item_article_number='';
                $avail_id=0;
                $price=0;
                $inaccurate_price=0;
                $request_price=1;
                $quantity=0;
                $item_img_time=0;

            }
            $unit_name = $uCat->uCat->unit_id2unit_name($item_data->unit_id);

            $item_url=u_sroot."uCat/item/".$item->item_id."?var_id=".$item->var_id;
            if($item_data->item_url!=="") {
                $item_url = u_sroot . "uCat/item/" . $item_data->item_url . "?var_id=" . $item->var_id;
            }
        }
        else {
            $item_data=$uCat->uCat->item_id2data($item->item_id,"
            `item_avail`,
            `item_img_time`,
            `item_title`,
            `item_descr`,
            `item_url`,
            `item_price`,
            `inaccurate_price`,
            `request_price`,
            `has_variants`,
            `quantity`,
            `unit_id`
            ");
            $var_id=0;
            $avail_id=(int)$item_data->item_avail;
            $price=(float)$item_data->item_price;
            $inaccurate_price=(float)$item_data->inaccurate_price;
            $request_price=(float)$item_data->request_price;
            $quantity = (float)$item_data->quantity;
            $unit_name = $uCat->uCat->unit_id2unit_name($item_data->unit_id);
            $item_img_time=(int)$item_data->item_img_time;

            $item_url=u_sroot."uCat/item/".$item->item_id;
            if($item_data->item_url!=="") {
                $item_url = u_sroot . "uCat/item/" . $item_data->item_url;
            }
        }
        $item_id=(int)$item->item_id;
        $item_title=uString::sql2text($item_data->item_title,1);
        $item_descr=uString::sql2text($item_data->item_descr,1);
//        $item_url=$item_data->item_url;
        $has_variants=(int)$item_data->has_variants;
        $item_count=(int)$item->item_count;

        ?>
        <section class="item_container">
            <div class="item_section row">


                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-12">
                    <a href="<?=strlen($item_url)?$item_url:$item_id?>">
                        <img class="img-responsive" src="<?=$uCat->avatar->get_avatar(500,$item_id,$item_img_time,$var_id)?>" style="margin: 0 auto" alt="">
                    </a>
                </div>


                <div class="uCat_item_info col-lg-7 col-md-6 col-sm-6 col-xs-12 hidden-xs">
                    <?$info_column='
                    <h3 class="item_title"><span><a target="_blank" href="'.$item_url.'">';

                    $enable_var_options=(int)$uCat->uFunc->getConf("enable_var_options","uCat");

                    if($enable_var_options&&(int)$item->var_id) {
                        $info_column .= uString::sql2text($uCat->uCat->var_type_id2data($var_data->var_type_id)->var_type_title);
                    }
                    else {
                        $info_column .= $item_title;
                    }

                    $info_column.='</a></span></h3>';

                    if($has_variants&&!$enable_var_options){
                    $info_column.='
                        <div class="text-info uCar_cart_item_var_type_title">
                            '.uString::sql2text($uCat->uCat->var_type_id2data($var_data->var_type_id)->var_type_title).'
                            <button class="btn btn-sm btn-link text-info uTooltip" title="'.$translator->txt("Click to show other variants").'" onclick="uCat_cart.show_item_variants('.$item_id.')"><span class="icon-tag"></span> '.$uCat->uFunc->getConf("item_has_variants_label"/*есть еще варианты*/,"uCat").'</button>
                        </div>';
                    }
                    elseif(!$has_variants&&!$enable_var_options&&site_id==5) {
                        $info_column.='
                        <div class="text-info uCar_cart_item_var_type_title"> 
                        '.uString::sql2text($uCat->uCat->item_type_id2data($item->item_type)->type_title).'
                        </div>';
                    }


                    $more_link_is_shown=0;
                    $cut_letters=(int)$this->uFunc->getConf("items_item_descr_cut_letters","uCat");
                    $txt_ar=explode('<!-- pagebreak -->',$item_descr);
                    if(count($txt_ar)>1) {
                        $item_descr = $txt_ar[0];
                    }
                    if($cut_letters!='0'&&uString::isDigits($cut_letters)) {
                        $descr_length=mb_strlen(trim($item_descr));
                        $item_descr=mb_substr($item_descr,0,$cut_letters,'UTF-8');
                        if($descr_length>$cut_letters&&count($txt_ar)<2) {
                            if(!$more_link_is_shown) {
                                $item_descr .= ' <a class="" style="margin-left:10px;" title="Подробнее" href="' . u_sroot . 'uCat/item/' . $item->item_id . '"><span class="icon-right"></span></a><br>';
                            }
                            $more_link_is_shown=1;
                        }

                        foreach ($uCat->descr_field as $iValue) {
                            $field_id= $iValue;
                            $item_field="field_".$field_id;
                            if($item->$item_field!=="") {
                                $field_val=$item->$item_field;
                                $field_val_length=mb_strlen(trim($field_val));
                                $field_val=mb_substr(trim($field_val),0,$cut_letters,'UTF-8');
                                if($field_val_length>$cut_letters) {
                                    $item->$item_field.=' <a class="" style="margin-left:10px;" title="Подробнее" href="'.u_sroot.'uCat/item/'.$item->item_id.'"><span class="icon-right"></span></a><br>';
                                    $more_link_is_shown=1;
                                }
                            }
                        }
                    }

                    $info_column.='
                    <div class="item_descr">';
                    $info_column.=$item_descr;

                    for($i=0, $iMax = count($uCat->descr_field); $i< $iMax; $i++) {
                        $field_id=$uCat->descr_field[$i];
                        $item_field="field_".$field_id;
                        if($item->$item_field!=="") {
                            $field_title=$uCat->descr_field_title[$i];
                            $field_value=uString::sql2text($item->$item_field,1);
                            $info_column.="<div><span><b>$field_title</b></span><br>$field_value</div><p>&nbsp;</p>";
                        }
                    }
//                    $info_column.='<pre>'.print_r($uCat,1).'</pre>';

                    $info_column.='</div>
                    <div class="item_info">';

                    if($uCat->uFunc->getConf("show_item_article_number","uCat")) {
                        $info_column .= '<div class="item_id">Артикул <span class="num">';
                        if ((int)$item->var_id) {
                            $info_column .= $var_data->item_article_number;
                        }
                        else {
                            $info_column .= $item->item_article_number;
                        }
                        $info_column .= '</span></div>';
                    }
                    $info_column.='</div>';
                    echo $info_column;
                    ?>
                </div>


                <div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
                    <div class="item_info_right">

                    <div class="item_price_container">

                        <span class="item_price" style="margin: 0 auto">
                            Цена:
                            <span id="uCat_cart_item_<?=$item_id?>_<?=$var_id?>_price">
                                <?php
                                    print number_format($price,(count(explode('.',$price))>1?2:0),'.','');
                                ?></span>
                            <span><?=$currency?></span>
                        </span>
                    </div>
                    <div>
                        <div class="input-group input-group-sm" style="margin: 0 auto">
                            <!--suppress HtmlFormInputWithoutLabel -->
                            <input type="text" data-max="<?=$quantity?>" data-unit="<?=$unit_name?>" id="uCat_cart_item_<?=$item_id?>_<?=$var_id?>_count"  autocomplete="off"  class="items_count_spinner" value="<?=$item_count?>">
                        </div>
                    </div>

                    <div style="text-align: center">
                        <?php
                        $total_price+=$price*$item_count;
                        $total_item_count+=$item_count;
                        ?>
                        Сумма:
                        <span>
                            <span id="uCat_cart_item_<?=$item_id?>_<?=$var_id?>_total_price" style="margin: 0 auto">
                                <?php
                                    print number_format($price*$item_count,(count(explode('.',$price*$item_count))>1?2:0),'.','&nbsp;');
                                ?> <?=$currency?></span>
                        </span>
                    </div>

                        <div style="text-align: center">
                            <div>
                                <a class="btn btn-sm btn-link text-danger uCat_cart_delete_item_btn no_popconfirm" href="javascript:void(0);" onclick="uCat_cart_page.delete_item(1,<?=$item_id?>,<?=$var_id?>)"><span class="icon-cancel"></span> Удалить</a>
                            </div>
                        </div>


                    </div>
                </div>


                <div class="uCat_item_info col-xs-12 visible-xs "><?=$info_column?></div>


            </div>
        </section>
    <?}?>
</div>
<?if($this->uCore->mod==='uCat'&&$this->uCore->page_name==="cart") {
    $currency='р';
    if(site_id==54) {
        $currency='Eur';
    }?>
<div class="uCat_cart_total">
    <ul class="list-unstyled">
        <li>
            <?$delivery_price=$uCat->define_delivery($total_price);?>
            <div id="uCat_cart_total_delivery_price_container" class="<?=!$delivery_price?"hidden":""?>">
                <p><span class="uCat_cart_total_label">Сумма заказа:</span>
                <span>
                <span id="uCat_cart_order_total_amount"><?=number_format($total_price,(count(explode('.',$total_price))>1?2:0),',','&nbsp;') ?></span>
                <span><?=$currency?></span>
                </span></p>
                <p><span class="uCat_cart_total_label">Доставка:</span>
                <span>
                    <span id="uCat_delivery_total_amount"><?=number_format($delivery_price,(count(explode('.',$delivery_price))>1?2:0),',','&nbsp;') ?></span>
                    <span><?=$currency?></span>
                </span></p>
            </div>

            <p><span class="uCat_cart_total_label">К оплате:</span>
            <span>
                <span id="uCat_cart_total_amount"><?=number_format($total_price+$delivery_price,(count(explode('.',$total_price+$delivery_price))>1?2:0),',','&nbsp;') ?></span>
                <span><?=$currency?></span>
            </span></p>
        </li>
    </ul>
</div>
<script type="text/javascript">
    if(typeof uCat_cart_page==="undefined") uCat_cart_page={};
    if(typeof uCat_cart==="undefined") uCat_cart={};
    uCat_cart.items_in_cart=uCat_cart_page.items_in_cart=<?=$total_item_count?>;
</script>
<?}?>
<script type="text/javascript">
    var item_quantity_show=<?=$uCat->uFunc->getConf('item_quantity_show','uCat')?>;
</script>
