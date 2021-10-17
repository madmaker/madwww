<?require_once 'uCat/inc/item_avatar.php';
if(!isset($uCat->avatar)) $uCat->avatar=new uCat_item_avatar($this);?>
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

<div class="item_list uCat_items_plane">
    <?
//    if(isset($uCat->q_items_pdo)) $uCat->q_items->execute();
//    else mysqli_data_seek($uCat->q_items,0);

//    function fetch_items(&$uCat) {
//        return 0;
//        $cat=&$uCat;
//        if(isset($cat->q_items_pdo)) return $uCat->q_items->fetch(PDO::FETCH_OBJ);
//        else return $cat->q_items->fetch_object();
//    }
    //uCat config
    $item_availability_show=(int)$this->uFunc->getConf("item_availability_show","uCat");
    $price_is_used=$this->uFunc->getConf("price_is_used","uCat")=='1';
    $inaccurate_price_descr=htmlspecialchars(strip_tags($this->uFunc->getConf('inaccurate_price_descr','uCat')));
    $item_prev_price_show=(int)$uCat->uFunc->getConf("item_prev_price_show","uCat");
    $link_item_descr=uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1);
    $link_item_label=uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1);
    $show_item_article_number=(int)$this->uFunc->getConf("show_item_article_number","uCat");
    $item_quantity_show=(int)$this->uFunc->getConf("item_quantity_show","uCat");
    $buy_button_show=(int)$this->uFunc->getConf('buy_button_show','uCat');
    $price_is_used=(int)$this->uFunc->getConf("price_is_used","uCat");
    $buy_btn_label=$this->uFunc->getConf("buy_btn_label","uCat");
    $cut_letters=$this->uFunc->getConf("items_item_descr_cut_letters","uCat");

    /** @noinspection PhpUndefinedMethodInspection */
    while($item=$uCat->q_items->fetch(PDO::FETCH_OBJ)) {

        $item->request_price=(int)$item->request_price;
        if($item->item_url!='') $item_url=uString::sql2text($item->item_url,true);
        else $item_url=$item->item_id;

        $item_avatar=$uCat->avatar->get_avatar(300,$item->item_id,$item->item_img_time);
        $avail_style=$uCat->uCat_common->avail_type_id2class($item->avail_type_id);

        if((int)$item->base_type_id==1) $availability_descr=$link_item_descr;
        else $availability_descr=$item->avail_descr;

        if((int)$item->base_type_id==1) $availability_label=$link_item_label;
        else $availability_label=$item->avail_label;

        $item_title=uString::sql2text($item->item_title,1);
        ?>
        <div class="row <?=$item->avail_type_id=='2'?'bg-info':''?>" id="uCat_item_<?=$item->item_id?>">
            <div class="col-md-4 col-sm-5 col-xs-12">
                <div class="item_avatar_container thumbnail">
                    <a id="uCat_item_avatar_a_<?=$item->item_id?>" href="<?=u_sroot?>uCat/item/<?=$item_url?>">
                        <img id="uCat_item_avatar_img_<?=$item->item_id?>" src="<?=$item_avatar?>" alt="<?=htmlspecialchars(strip_tags($item_title))?>">
                    </a>
                </div>

                <?if($item->item_article_number !== "" && $item->item_article_number !== null&&$show_item_article_number){?>
                    <p class="item_art"><span>Арт:</span> <span id="uCat_item_article_number_<?=$item->item_id?>"><?=$item->item_article_number?></span></p>
                <?}
                //$activ_evotor = (int)$this->uFunc->getConf('used_activation_token','uCat','return false', site_id);
                if($item_quantity_show){?>
                    <p class="item_quantity_<?=$item->item_id?>"><span>Остаток:</span> <?=$item->quantity?> <?=$uCat->uCat_common->unit_id2unit_name($item->unit_id);?></p>
                <?}?>

                <!--AVAILABILITY-->
                <? if($item_availability_show) {?>
                    <p style="font-size: 1px; line-height: 1px;">&nbsp;</p>
                    <div class="availability <?=$avail_style?>" title="<?=$availability_descr?>">
                        <span><?=$availability_label?></span>
                    </div>
                <?}?>

                <p class="clearfix"> </p>


                <?
                $options_number = (int)$uCat->uCat->has_options($item->item_id);
                if((int)$item->has_variants) {?>
                    <script type="text/javascript">
                        uCat.var_selected[<?=$item->item_id?>]=<?=(int)$item->has_variants?$uCat->uCat->item_id2default_variant_id($item->item_id):0?>;
                        uCat.var_selected_price[<?=$item->item_id?>]=<?=$item->item_price;?>;
                    </script>
                    <div class="variants_container">
                        <?$uCat->item_id = $item->item_id;
                        $avatar_style="plane";
                        if ($options_number) include "uCat/inc/options_table.php";
                        else include "uCat/inc/variants_table.php";?>
                    </div>
                <?}?>

                <!--PRICE-->
                <? if($price_is_used) {?>
                    <div class="price-container" <?=($item->inaccurate_price=='1')?('title="'.$inaccurate_price_descr.'"'):''?>>
                        <?if(!$item->request_price) {
                            $col_width=$item_prev_price_show&&(int)$item->prev_price?6:12;
                            $item_price=number_format ( $item->item_price ,  (count(explode('.',$item->item_price))>1?2:0) ,'.' , ' ' );?>
                            <span class="price col-lg-<?=$col_width?> col-md-<?=$col_width?> col-sm-12 col-xs-12"><?=$item_price?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?><span id="item_inaccurate_price_marker_<?=$item->item_id?>"><?=(int)$item->inaccurate_price?'*':''?></span></span>
                        <?}?>

                        <?if($item_prev_price_show&&(int)$item->prev_price) {?>
                            <div id="uCat_prev_price_<?=$item->item_id?>" class="prev_price text-primary col-lg-6 col-md-6 col-sm-12 col-xs-12"><?=number_format ( $item->prev_price , (count(explode('.',$item->prev_price))>1?2:0) ,'.' , ' ' )?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?></div>
                        <?}?>
                    </div>
                <?}?>

                <!-- BUY BTN-->
                <?if($buy_button_show&&$price_is_used){?>

                    <?if((int)$item->inaccurate_price&&!$item->request_price) {?>
                        <button class="btn btn-default btn-sm inaccurate_price_btn" id="inaccurate_price_btn_<?=$item->item_id?>" onclick="uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode($item_title)?>')">Уточнить цену</button>
                    <?}?>
                    <div class="buy_btn">
                        <?if($uCat->enable_item_quantity && $uCat->enable_tiles_plus_and_minus) { ?>
                            <div class="input-group-sm">
                                <input type="text" data-max="<?=$item->quantity?>" id="uCat_item_<?=$item->item_id?>_count" autocomplete="off"  class="items_count_spinner" value="1">
                            </div>
                            <button class="btn btn-primary btn-sm" data-variant="0" onclick="uCat_cart.buy_indicate_quantity(<?=$item->item_id?>,<?=$item->item_price?>)"><?=$buy_btn_label?></button>
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
                            if($item->request_price||$item->avail_type_id==4) {?>
                                    uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode($item_title)?>')
                            <?}
                            else {?>
                            <?if($uCat->enable_item_quantity && $uCat->enable_tiles_plus_and_minus) { ?>
                                    uCat_cart.buy_indicate_quantity(<?=$item->item_id?>,<?=$item->item_price?>)"><?=$buy_btn_label?>
                                <?}
                                else {?>
                                    uCat_cart.buy(<?=$item->item_id?>,<?=$item->item_price?>)
                                <?}?>
                                <?}?>
                                "><?
                                if((float)$item->item_price&&!$item->request_price&&$item->avail_type_id!=4) echo $buy_btn_label;
                                elseif($item->request_price) echo "Запросить цену";
                                elseif($item->avail_type_id==4) echo "Заказать";
                                elseif(!(float)$item->item_price) print $uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1);
                                ?></button>
                        <?}?>
                    </div>
                <?}?>

            </div>
            <div class="item_info col-md-8 col-sm-7 col-xs-12">
                <h2 class="item_title">
                    <a href="<?=u_sroot.'uCat/item/'.($item->item_url!=''?uString::sql2text($item->item_url,true):$item->item_id)?>">
                        <?=$item_title?>
                    </a>
                </h2>

                <div class="fields"><?
                    foreach ($uCat->q_fields as $key => $field) {
                        $item_field='field_'.$field->field_id;
                        if($field->planelist_show=='1'&&!empty($item->$item_field)) {
                            echo '<div>';
                            echo '<label>'.uString::sql2text($field->field_title).'</label> ';
                            if($uCat->field_type_id2style[$field->field_type_id]=='integer'||
                                $uCat->field_type_id2style[$field->field_type_id]=='double') {
                                echo $item->$item_field;
                            }
                            elseif($uCat->field_type_id2style[$field->field_type_id]=='text line') {
                                echo uString::sql2text($item->$item_field,true);
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
                    };
                    reset($uCat->q_fields);?>
                </div>


                <div class="item_descr"><?if(!empty($item->item_descr)) {
                        $item_descr=uString::sql2text($item->item_descr,true);
                        $txt_ar=explode('<!-- pagebreak -->',$item_descr);
                        if(count($txt_ar)>1) $item_descr=$txt_ar[0];
                        if($cut_letters!='0'&&uString::isDigits($cut_letters)) {
                            echo mb_substr(strip_tags($item_descr),0,$cut_letters,'UTF-8');
                            if(count($txt_ar)<2) print '... <a href="' . u_sroot . 'uCat/item/' . $item->item_id . '" style="opacity: 70%"> читать дальше...</a>';
                        }
                        else echo uString::sql2text($item->item_descr,true);
                    }
                ?></div>


            </div>
        </div>
    <?}?>
<div class="inaccurate_price_label"><?=$this->uFunc->getConf('inaccurate_price_label','uCat')?></div>
</div>
