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

<style type="text/css">
    .uCat_items_tiles .item_title {
        height: <?=$this->uFunc->getConf("Item title h in Tiles","uCat")?>px;
    }
</style>
<div class="item_list uCat_items_tiles">
    <div class="row"><?
//    if(isset($uCat->q_items_pdo)) $uCat->q_items->execute();
//    else mysqli_data_seek($uCat->q_items,0);
//
//    function fetch_items(&$uCat) {
//        $cat=&$uCat;
//        if(isset($cat->q_items_pdo)) return $uCat->q_items->fetch(PDO::FETCH_OBJ);
//        else return $cat->q_items->fetch_object();
//    }

    //uCat config
    $item_availability_show=$this->uFunc->getConf("item_availability_show","uCat")=='1';
    $price_is_used=$this->uFunc->getConf("price_is_used","uCat")=='1';
    $inaccurate_price_descr=htmlspecialchars(strip_tags($this->uFunc->getConf('inaccurate_price_descr','uCat')));

        //lg - (2,3,4,6)
        //md - (2,3,4)
        //sm - (1,2,3)
        //xs - (1,2)
        $items_number_xs_values=$items_number_sm_values=$items_number_md_values=$items_number_lg_values=array();

        $items_number_xs_values[1]=12;
        $items_number_xs_values[2]=6;

        $items_number_sm_values[1]=12;
        $items_number_sm_values[2]=6;
        $items_number_sm_values[3]=4;

        $items_number_md_values[2]=6;
        $items_number_md_values[3]=4;
        $items_number_md_values[4]=3;

        $items_number_lg_values[2]=6;
        $items_number_lg_values[3]=4;
        $items_number_lg_values[4]=3;
        $items_number_lg_values[6]=2;

    $items_number_lg=$items_number_lg_values[(int)$this->uFunc->getConf("items number on tiles - lg","uCat")];
    $items_number_md=$items_number_md_values[(int)$this->uFunc->getConf("items number on tiles - md","uCat")];
    $items_number_sm=$items_number_sm_values[(int)$this->uFunc->getConf("items number on tiles - sm","uCat")];
    $items_number_xs=$items_number_xs_values[(int)$this->uFunc->getConf("items number on tiles - xs","uCat")];

        /** @noinspection PhpUndefinedMethodInspection */
        while($item=$uCat->q_items->fetch(PDO::FETCH_OBJ)) {
        if($item->item_url!='') $item_url=uString::sql2text($item->item_url,true);
        else $item_url=$item->item_id;

        $item_avatar=$uCat->avatar->get_avatar(500,$item->item_id,$item->item_img_time);
        $avail_style=$uCat->uCat_common->avail_type_id2class($item->avail_type_id);

        if((int)$item->base_type_id==1) $availability_descr=uString::sql2text($this->uFunc->getConf("link_item_descr","uCat"),1);
        else $availability_descr=$item->avail_descr;

        if((int)$item->base_type_id==1) $availability_label=uString::sql2text($this->uFunc->getConf("link_item_label","uCat"),1);
        else $availability_label=$item->avail_label;

            $item_title=uString::sql2text($item->item_title,1);

        ?>
        <div class="col-lg-<?=$items_number_lg?> col-md-<?=$items_number_md?> col-sm-<?=$items_number_sm?> col-xs-<?=$items_number_xs?>">

            <div class="item <?=$item->avail_type_id=='2'?'bg-info':''?> container-fluid" id="uCat_item_<?=$item->item_id?>">

                <div class="item_avatar_container">
                    <a id="uCat_item_avatar_a_<?=$item->item_id?>" href="<?=u_sroot?>uCat/item/<?=$item_url?>">
                        <img id="uCat_item_avatar_img_<?=$item->item_id?>" src="<?=$item_avatar?>" alt="<?=htmlspecialchars(strip_tags($item_title))?>">
                    </a>
                </div>


                <div class="item_title">
                    <a href="<?=u_sroot.'uCat/item/'.($item->item_url!=''?uString::sql2text($item->item_url,true):$item->item_id)?>">
                        <?=$item_title?>
                    </a>
                </div>

                <!--PRICE-->
                <? if($price_is_used) {?>
                    <div class="price-container" <?=($item->inaccurate_price=='1')?('title="'.$inaccurate_price_descr.'"'):''?>>
                        <?if($item->request_price!='1') {
                            $item_price=number_format ( $item->item_price ,  (count(explode('.',$item->item_price))>1?2:0) ,'.' , ' ' );?>
                            <span class="price col-lg-6 col-md-6 col-sm-6 col-xs-12"><?=$item_price?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?><span id="item_inaccurate_price_marker_<?=$item->item_id?>"><?=(int)$item->inaccurate_price?'*':''?></span></span>
                        <?}?>

                        <?if((int)$uCat->uFunc->getConf("item_prev_price_show","uCat")&&(int)$item->prev_price) {?>
                            <div id="uCat_prev_price_<?=$item->item_id?>" class="prev_price text-primary col-lg-6 col-md-6 col-sm-6 col-xs-12"><?=number_format ( $item->prev_price , (count(explode('.',$item->prev_price))>1?2:0) ,'.' , ' ' )?> <?if(site_id==54) {?><span>Eur</span><?}
                                else {?><span class="icon-rouble"></span><?}?></div>
                        <?}?>
                    </div>
                <?}?>

                <div class="fields fields_on_tiles_card"><?
                    foreach ($uCat->q_fields as $key => $field) {
                        $item_field='field_'.$field->field_id;
                        if($field->tileslist_show_on_card=='1'&&!empty($item->$item_field)) {
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

                <div class="add_info">
                    <?if($uCat->uFunc->getConf("show_item_article_number","uCat")){?><div class="item_art"><span>Арт:</span> <span id="uCat_item_article_number_<?=$item->item_id?>"><?=$item->item_article_number?></span></div><?}?>
                    <?if((int)$this->uFunc->getConf("item_quantity_show","uCat")) {?><div class="item_quantity_<?=$item->item_id?>"><span>Остаток:</span> <?=$item->quantity?> <?=$uCat->uCat_common->unit_id2unit_name($item->unit_id);?></div><?}?>

                    <div class="availability_container">
                        <!--AVAILABILITY-->
                        <? if($item_availability_show) {?>
                            <div class="availability <?=$avail_style?>" title="<?=$availability_descr?>">
                                <span><?=$availability_label?></span>
                            </div>
                        <?}?>
                    </div>

                    <div class="fields"><?
                        foreach ($uCat->q_fields as $key => $field) {
                            $item_field='field_'.$field->field_id;
                            if($field->tileslist_show=='1'&&!empty($item->$item_field)) {
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

                    <?
                    $options_number = (int)$uCat->uCat->has_options($item->item_id);
                    if((int)$item->has_variants) {?>
                        <script type="text/javascript">
                            uCat.var_selected[<?=$item->item_id?>]=<?=(int)$item->has_variants?$uCat->uCat->item_id2default_variant_id($item->item_id):0?>;
                            uCat.var_selected_price[<?=$item->item_id?>]=<?=$item->item_price;?>;
                        </script>
                        <div class="variants_container">
                            <?$uCat->item_id = $item->item_id;
                            $avatar_style="tiles";
                            if ($options_number) include "uCat/inc/options_table.php";
                            else include "uCat/inc/variants_table.php";?>
                        </div>
                    <?}?>

                    <!-- BUY BTN-->
                    <?if(
                        (int)$this->uFunc->getConf('buy_button_show','uCat')&&
                        (int)$this->uFunc->getConf("price_is_used","uCat")){?>

                    <?if((int)$item->inaccurate_price&&!(int)$item->request_price) {?>
                        <button class="btn btn-default btn-sm inaccurate_price_btn" id="inaccurate_price_btn_<?=$item->item_id?>" onclick="uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode(uString::sql2text($item->item_title,1))?>')">Уточнить цену</button>
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
                                        uCat_request_price_form.openForm(<?=$item->item_id?>,0,'<?=rawurlencode(uString::sql2text($item->item_title,1))?>')
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
                </div>
            </div>
        </div>
    <?}?></div>

<div class="inaccurate_price_label_<?=$uCat->item_id?>"><?=$this->uFunc->getConf('inaccurate_price_label','uCat')?></div>
</div>
