<div id="uCat_item_variants">
<h3 class="uCat_item_variants_header">Выберите вариант</h3>
    <?php
    if(!isset($uCat->selected_var_id)) $uCat->selected_var_id=(int)$uCat->uCat->item_id2default_variant_id($uCat->item_id);
    $vars_stm = $uCat->uCat->get_item_variants_pdo($uCat->item_id);
    ?>
    <script type="text/javascript">
        variants_data[<?=$uCat->item_id?>]=[];
    </script>
    <table
        class="table table-condensed table-hover"
        id="uCat_item_variants"
    >
        <? /** @noinspection PhpUndefinedMethodInspection */
        while($var=$vars_stm->fetch(PDO::FETCH_OBJ)) {
        if (($avail_type_id = (int)$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_type_id) == 2 /*&& !$uCat->uSes->access(25)*/) continue;
        $base_type_id = (int)$uCat->uCat->item_type_id2data($uCat->uCat->var_type_id2data($var->var_type_id)->item_type_id)->base_type_id;
            ?>
        <tr id="uCat_item_variants_var_<?=$var->var_id?>"
        class="
        uCat_item_variants_tr
        uCat_item_variants_tr_<?=$uCat->item_id?>
        <?= $uCat->selected_var_id === (int)$var->var_id ? ' active ' : '' ?>
        "
        onclick="uCat.switch_var(<?=$uCat->item_id?>,<?=$var->var_id?>,'<?=$var->price?>')"
            >
            <td class="selected_var_container selected_var_container_<?=$uCat->item_id?>">
            <?= $uCat->selected_var_id === (int)$var->var_id ? '<span class="icon-ok text-primary"></span>' : '' ?>
            </td>
            <td>
            <?=uString::sql2text($uCat->uCat->var_type_id2data($var->var_type_id)->var_type_title, 1)?>
            </td>
            </tr>
            <script type="text/javascript">

                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]=[];
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['item_article_number']=decodeURIComponent("<?=rawurlencode($var->item_article_number)?>");
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['var_id']=<?=$var->var_id?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['var_type_id']=<?=$var->var_type_id?>;
                <?$var_type_info=$uCat->uCat->var_type_id2data($var->var_type_id);
                $item_type_id=(int)$var_type_info->item_type_id;
                $item_type_info_q=$uCat->uCat->item_type_id2data($item_type_id);?>
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['base_type_id']=<?=$item_type_info_q->base_type_id?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['type_title']="<?=$item_type_info_q->type_title?>";
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['default_var']=<?=$var->default_var?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['price']=<?=$var->price?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['prev_price']=<?=$var->prev_price?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['var_quantity']=<?=$var->var_quantity?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['img_time']=<?=$var->img_time?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['img_src']=decodeURIComponent("<?=$uCat->avatar->get_avatar(640,$uCat->item_id,$var->img_time,$var->var_id)?>");
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['img_orig_src']=decodeURIComponent("<?=$uCat->avatar->get_avatar('orig',$uCat->item_id,$var->img_time,$var->var_id)?>");
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['inaccurate_price']=<?=$var->inaccurate_price?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['request_price']=<?=$var->request_price?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['avail_id']=<?=$var->avail_id?>;
                <?$avail_data=$uCat->uCat->avail_id2avail_data($var->avail_id);
                if($avail_data) {
                    $avail_type_id=$avail_data->avail_type_id;
                    $avail_label=$avail_data->avail_label;
                }
                else {
                    $avail_type_id=2;
                    $avail_label="Нет в наличии";
                }
                ?>
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['avail_type_id']=<?=$avail_type_id?>;
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['avail_type_class']="<?=$uCat->uCat->avail_type_id2class($avail_type_id)?>";
                variants_data[<?=$uCat->item_id?>][<?=$var->var_id?>]['avail_label']=decodeURIComponent("<?=rawurlencode($avail_label)?>");
        </script>
        <?}?>
</table>
</div>