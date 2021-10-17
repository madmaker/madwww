<div id="uCat_item_variants">
   <h3 class="uCat_item_variants_header">Выберите вариант</h3>
   <?//get all options of this item
   $options_stm = $uCat->uCat->get_item_options($uCat->item_id);
    if(!isset($uCat->selected_var_id)) $uCat->selected_var_id=(int)$uCat->uCat->item_id2default_variant_id($uCat->item_id);
//    echo $uCat->selected_var_id;
   $def_var_options_values_obj=$uCat->uCat->var_id2options_values($uCat->selected_var_id);
   $variants_options_values_obj=$uCat->uCat->get_item_variants_options_values($uCat->item_id);?>
       <script type="text/javascript">
           if(typeof uCat==="undefined") uCat={};

           options_ar[<?=$uCat->item_id?>]=[];
           option_id2i[<?=$uCat->item_id?>] = [];
           values_ar[<?=$uCat->item_id?>]=[];
           value_id2i[<?=$uCat->item_id?>]=[];

           variants_options_values[<?=$uCat->item_id?>]=[];

           variants_data[<?=$uCat->item_id?>]=[];
           <?/** @noinspection PhpUndefinedMethodInspection */
           while($variant_option_value=$variants_options_values_obj->fetch(PDO::FETCH_OBJ)) {?>
              if(typeof variants_options_values[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]==="undefined") variants_options_values[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]=[];
               n=variants_options_values[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>].length;
               variants_options_values[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>][n]=<?=$variant_option_value->value_id?>;

               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]=[];
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['var_id']=<?=$variant_option_value->var_id?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['var_type_id']=<?=$variant_option_value->var_type_id?>;
               <?$var_type_info=$uCat->uCat->var_type_id2data($variant_option_value->var_type_id);
               $item_type_id=(int)$var_type_info->item_type_id;
               $item_type_info_q=$uCat->uCat->item_type_id2data($item_type_id);?>
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['base_type_id']=<?=$item_type_info_q->base_type_id?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['type_title']="<?=$item_type_info_q->type_title?>";
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['price']=<?=$variant_option_value->price?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['prev_price']=<?=$variant_option_value->prev_price?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['var_quantity']=<?=$variant_option_value->var_quantity?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['item_article_number']=decodeURIComponent("<?=rawurlencode($variant_option_value->item_article_number)?>");
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['img_time']=<?=$variant_option_value->img_time?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['img_src']=decodeURIComponent("<?=$uCat->avatar->get_avatar(640,$uCat->item_id,$variant_option_value->img_time,$variant_option_value->var_id)?>");
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['img_orig_src']=decodeURIComponent("<?=$uCat->avatar->get_avatar('orig',$uCat->item_id,$variant_option_value->img_time,$variant_option_value->var_id)?>");
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['inaccurate_price']=<?=$variant_option_value->inaccurate_price?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['request_price']=<?=$variant_option_value->request_price?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['avail_id']=<?=$variant_option_value->avail_id?>;
               <?$avail_data=$uCat->uCat->avail_id2avail_data($variant_option_value->avail_id);
               if($avail_data) {
                   $avail_type_id=$avail_data->avail_type_id;
                   $avail_label=$avail_data->avail_label;
               }
               else {
                   $avail_type_id=2;
                   $avail_label="Нет в наличии";
               }
               ?>
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['avail_type_id']=<?=$avail_type_id?>;
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['avail_type_class']="<?=$uCat->uCat->avail_type_id2class($avail_type_id)?>";
               variants_data[<?=$uCat->item_id?>][<?=$variant_option_value->var_id?>]['avail_label']=decodeURIComponent("<?=rawurlencode($avail_label)?>");
           <?}?>

           def_var_options_values[<?=$uCat->item_id?>]=[];
   <?
   $def_var_options_values_ar=[];
   /** @noinspection PhpUndefinedMethodInspection */
   while($def_var_options_values=$def_var_options_values_obj->fetch(PDO::FETCH_OBJ)) {
    $def_var_options_values_ar[(int)$def_var_options_values->option_id]=(int)$def_var_options_values->value_id;?>
           def_var_options_values[<?=$uCat->item_id?>][<?=$def_var_options_values->option_id?>]=<?=$def_var_options_values->value_id?>;
   <?}?>
       </script>
   <?/*echo print_r($def_var_options_values_ar);*/
   /** @noinspection PhpUndefinedMethodInspection */
   while ($option = $options_stm->fetch(PDO::FETCH_OBJ)) {
       ?>
   <script type="text/javascript">
       i=options_ar[<?=$uCat->item_id?>].length;
       option_id2i[<?=$uCat->item_id?>]['<?=$option->option_id?>']=i;
       options_ar[<?=$uCat->item_id?>][i]=[];
       options_ar[<?=$uCat->item_id?>][i]['option_id']='<?=$option->option_id?>';
       options_ar[<?=$uCat->item_id?>][i]['option_name']=decodeURIComponent('<?=rawurlencode($option->option_name)?>');
       options_ar[<?=$uCat->item_id?>][i]['option_type']=<?=$option->option_type?>;
       options_ar[<?=$uCat->item_id?>][i]['option_display_style']=<?=$option->option_display_style?>;
   </script>
       <h4 class="option_name"><?=$option->option_name;?></h4>
       <?
       $option->option_type=(int)$option->option_type;
       $option->option_display_style=(int)$option->option_display_style;
       if($option->option_display_style===0/*TABLE*/) {?>
   <table class="table table-condensed table-hover uCat_item_options" id="uCat_item_option_<?=$option->option_id?>_<?=$uCat->item_id?>">
       <?
       $option_values_obj=$uCat->uCat->get_option_values($uCat->item_id,$option->option_id);
       /** @noinspection PhpUndefinedMethodInspection */
       while($val=$option_values_obj->fetch(PDO::FETCH_OBJ)) {
           $connected_vals_obj=$uCat->uCat->get_connected_values($val->value_id,$uCat->item_id);
           ?>
           <script type="text/javascript">
               j=values_ar[<?=$uCat->item_id?>].length;
               value_id2i[<?=$uCat->item_id?>][<?=$val->value_id?>]=j;
               values_ar[<?=$uCat->item_id?>][j]=[];
               values_ar[<?=$uCat->item_id?>][j]['value_id']='<?=$val->value_id?>';
               values_ar[<?=$uCat->item_id?>][j]['value']=decodeURIComponent('<?=rawurlencode($val->value)?>');
               values_ar[<?=$uCat->item_id?>][j]['color']=decodeURIComponent('<?=rawurlencode($val->color)?>');
               values_ar[<?=$uCat->item_id?>][j]['option_id']='<?=$option->option_id?>';
               values_ar[<?=$uCat->item_id?>][j]['connected_values']=[];
               <?/** @noinspection PhpUndefinedMethodInspection */
               while($connected_val=$connected_vals_obj->fetch(PDO::FETCH_OBJ)) {?>
                   k=values_ar[<?=$uCat->item_id?>][j]['connected_values'].length;
                   values_ar[<?=$uCat->item_id?>][j]['connected_values'][k]=[];
                   values_ar[<?=$uCat->item_id?>][j]['connected_values'][k]['value_id']=<?=$connected_val->value_id?>;
               <?}?>
           </script>
           <tr id="uCat_option_value_<?=$val->value_id?>_<?=$uCat->item_id?>"
               data-option_id="<?= $option->option_id?>"
               data-value_id="<?= $val->value_id?>"
               class="
               uCat_values_of_option_<?=$option->option_id?>
               uCat_item_options_tr
               uCat_item_options_tr_<?=$uCat->item_id?>
               <?= $def_var_options_values_ar[$option->option_id] == (int)$val->value_id ? ' sel ': '' ?>
               "
               <?if($option->option_type===1/*color*/){?>
                   style="color:<?=$color=$uCat->uFunc->color2monochrome($uCat->uFunc->color_inverse($val->color))?>; background:<?=$val->color?>"
               <?}?>
               <?='onclick="uCat.switch_option(' . $val->value_id.','.$uCat->item_id.')"'?>
           >
               <td class="selected_var_container selected_var_container_<?=$uCat->item_id?> text-primary" style="<?=$option->option_type===1?('color:'.$color):''?>">
                   <?= $def_var_options_values_ar[$option->option_id] == (int)$val->value_id ? '<span class="icon-ok"></span>' : '' ?>
               </td>
               <td><?=$val->value?></td>
           </tr>
       <?}?>
   </table>
           <?}
       elseif($option->option_display_style===1/*INLINE*/) {?>
       <div class="uCat_item_options" id="uCat_item_option_<?=$option->option_id?>_<?=$uCat->item_id?>">
           <?
           $option_values_obj=$uCat->uCat->get_option_values($uCat->item_id,$option->option_id);
           /** @noinspection PhpUndefinedMethodInspection */
           while($val=$option_values_obj->fetch(PDO::FETCH_OBJ)) {
               $connected_vals_obj=$uCat->uCat->get_connected_values($val->value_id,$uCat->item_id);
               ?>
               <script type="text/javascript">
                   j=values_ar[<?=$uCat->item_id?>].length;
                   value_id2i[<?=$uCat->item_id?>][<?=$val->value_id?>]=j;
                   values_ar[<?=$uCat->item_id?>][j]=[];
                   values_ar[<?=$uCat->item_id?>][j]['value_id']='<?=$val->value_id?>';
                   values_ar[<?=$uCat->item_id?>][j]['value']=decodeURIComponent('<?=rawurlencode($val->value)?>');
                   values_ar[<?=$uCat->item_id?>][j]['color']=decodeURIComponent('<?=rawurlencode($val->color)?>');
                   values_ar[<?=$uCat->item_id?>][j]['option_id']='<?=$option->option_id?>';
                   values_ar[<?=$uCat->item_id?>][j]['connected_values']=[];
                   <?/** @noinspection PhpUndefinedMethodInspection */
                   while($connected_val=$connected_vals_obj->fetch(PDO::FETCH_OBJ)) {?>
                   k=values_ar[<?=$uCat->item_id?>][j]['connected_values'].length;
                   values_ar[<?=$uCat->item_id?>][j]['connected_values'][k]=[];
                   values_ar[<?=$uCat->item_id?>][j]['connected_values'][k]['value_id']=<?=$connected_val->value_id?>;
                   <?}?>
               </script>
               <div id="uCat_option_value_<?=$val->value_id?>_<?=$uCat->item_id?>"
                   data-option_id="<?= $option->option_id?>"
                   data-value_id="<?= $val->value_id?>"
                   class="
               uCat_values_of_option_<?=$option->option_id?>
               uCat_item_options_tr
               uCat_item_options_tr_<?=$uCat->item_id?>
               <?=$option->option_type===1?'uCat_item_options_color_option':''?>
               <?= $def_var_options_values_ar[$option->option_id] == (int)$val->value_id ? ' sel ': '' ?>
               "
                   <?='onclick="uCat.switch_option(' . $val->value_id.','.$uCat->item_id.')"'?>
               >
<!--                   <div class="selected_var_container"></div>-->
                   <div
                           class="
                           uCat_option_value_container
                           uCat_option_value_container_<?=$uCat->item_id?>
                           <?=$def_var_options_values_ar[$option->option_id] == (int)$val->value_id ? 'bg-primary':''?>
                           "
                           id="uCat_option_value_container_<?=$val->value_id?>_<?=$uCat->item_id?>"
                           title="<?=$val->value?>"
                           style="
                           <?if($option->option_type===1) {?>
                               color:<?=$uCat->uFunc->color2monochrome($uCat->uFunc->color_inverse($val->color))?>;
                               background:<?=$val->color?>;
                               border:solid 1px <?=$color=$uCat->uFunc->color2monochrome($uCat->uFunc->color_inverse($val->color))?>
                           <?}
                           else {?>
                               border:1px solid #000;
                           <?}?>
                           "
                           ><?
                       if($option->option_type===1) {?>
                       &nbsp;
                       <?}
                       else {
                           echo $val->value;
                       }?></div>
               </div>
           <?}?>
       </div>
       <?}?>
   <?}?>
</div>