<?php
namespace uCat\item;
use PDO;
//use PDOException;
use processors\uFunc;
use uCat\common;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uDrive/classes/common.php";
require_once "uCat/classes/common.php";

class admin_item_get_options_bg{
    public $uFunc;
    public $item_quantity_show;
    public $item_prev_price_show;
    private $uSes;
    private $uCore;
    public $uCat;
    public $uDrive;
    public $item_id,$q_variants;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!\uString::isDigits($this->item_id)) $this->uFunc->error(20);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uCat=new common($this->uCore);
        $this->uDrive=new \uDrive\common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();

        $this->q_variants=$this->uCat->get_item_variants_pdo($this->item_id);
        $this->item_quantity_show=(int)$this->uFunc->getConf('item_quantity_show','uCat');
        $this->item_prev_price_show=(int)$this->uFunc->getConf('item_prev_price_show','uCat');
    }
}
$uCat=new admin_item_get_options_bg($this);
$inaccurate_price_descr=strip_tags($uCat->uFunc->getConf('inaccurate_price_descr','uCat'))?>

<? /** @noinspection PhpUndefinedMethodInspection */
if($var=$uCat->q_variants->fetch(PDO::FETCH_OBJ)) {?>
<table class="table" id="uCat_item_get_variants_bg_table">
    <tr>
        <th></th>
        <th></th>
        <th></th>
        <th>Артикул</th>
        <th>Вариант</th>
        <th style="min-width: 100px;">Цена</th>
        <?if($uCat->item_prev_price_show) {?>
        <th style="min-width: 100px;">Старая цена</th>
        <?}
        if($uCat->item_quantity_show){?>
        <th style="min-width: 100px;">Количество</th>
        <?}?>
        <th>Неточная цена  <span class="icon-question uTooltip" title="<?=htmlspecialchars($inaccurate_price_descr)?>"></span></th>
        <th>Запрашивать цену <span class="icon-question uTooltip" title="Вместо кнопки <?=addslashes($uCat->uFunc->getConf("buy_btn_label","uCat"))?> отображать кнопку Запросить цену"></span> </th>
        <th>Файл, наличие <button class="btn btn-default btn-xs" onclick="uCat.item_availabilities_edit(1051)"><span class="icon-pencil"></span></button> </th>
    </tr>
    <?
    /** @noinspection PhpUndefinedMethodInspection */
    for(; $var; $var=$uCat->q_variants->fetch(PDO::FETCH_OBJ)){
            $item_type=(int)$uCat->uCat->var_type_id2data($var->var_type_id)->item_type_id;
            $item_info=$uCat->uCat->item_id2data($uCat->item_id,"item_title");
            $item_title=\uString::sql2text($item_info->item_title,1);
            $base_type_id=(int)$uCat->uCat->item_type_id2data($item_type)->base_type_id;?>
            <tr class="<?=$var->default_var=='1'?'active':''?> uCat_item_get_variants_bg_var" id="uCat_item_get_variants_bg_var_<?=$var->var_id?>">
                <td><button class="uCat_item_get_variants_bg_set_def_var_btn btn <?=$var->default_var=='1'?'btn-success':'btn-default'?> btn-xs uTooltip" title="<?=$var->default_var=='1'?'Этот вариант основной':'Сделать основным вариантом'?>" onclick="uCat_item_admin.set_default_variant(<?=$var->var_id?>)"><span class="icon-pin"></span></button></td>
                <td><button class="uCat_item_get_variants_bg_set_img_btn btn <?=(int)$var->img_time?'btn-success':'btn-default'?> btn-xs uTooltip" title="Загрузить изображение для этого варианта" onclick="uCat_item_admin.change_avatar(<?=$var->var_id?>)"><span class="icon-camera"></span></button></td>
                <td><button class="uCat_item_get_variants_bg_delete_var_btn has_no_popConfirm btn btn-danger btn-xs uTooltip" title="Удалить вариант" onclick="uCat_item_admin.delete_variant(<?=$var->var_id?>)"><span class="icon-cancel"></span></button></td>
                <td>
                    <span class="eip_element" id="uCat_admin_item_get_variants_item_article_number_<?=$var->var_id?>"
                          onclick="eip_el.init_el(this)"
                          data-value="<?=rawurlencode($var->item_article_number)?>"
                          data-el_type="textfield"
                          data-save_action="uCat_item_admin.variant_item_article_number_update(value,<?=$var->var_id?>)"
                    >
                        <?=$var->item_article_number?>
                    </span>
                </td>
                <td>
                    <span  id="uCat_admin_item_get_variants_var_type_id_<?=$var->var_id?>"
                        class="eip_element"
                        onclick="uCat_item_admin.edit_variant_with_options_dg(<?=$var->var_id?>)"
                        >
                        <?
                        echo $item_title;
                        echo ". (";
                        $options_obj=$uCat->uCat->get_options_with_values($var->var_id);
                        /** @noinspection PhpUndefinedMethodInspection */
                        while($option=$options_obj->fetch(PDO::FETCH_OBJ)) {
                            echo $option->option_name;
                            echo ": ";
                            echo $option->value;
                            echo ". ";
                        }
                        echo ")";
                        ?>
                    </span>
                </td>
                <td>
                    <span class="eip_element" id="uCat_admin_item_get_variants_price_<?=$var->var_id?>"
                          onclick="eip_el.init_el(this)"
                          data-value="<?=rawurlencode($var->price)?>"
                          data-el_type="textfield"
                          <?/*data-mask="9{+}"*/?>
                          data-save_action="uCat_item_admin.variant_price_update(value,<?=$var->var_id?>,'price')"
                        >
                        <?php
                        $currency='р';
                        if(site_id==54) {
                            $currency='Eur';
                        }
                        ?>
                        <?=number_format($var->price,(count(explode('.',$var->price))>1?2:0),',',' ')?> <?=$currency?>
                    </span>
                </td>
                <?if($uCat->item_prev_price_show) {?>
                <td>
                    <span class="eip_element" id="uCat_admin_item_get_variants_prev_price_<?=$var->var_id?>"
                          onclick="eip_el.init_el(this)"
                          data-value="<?=rawurlencode($var->prev_price)?>"
                          data-el_type="textfield"
                          <?/*data-mask="9{+}"*/?>
                          data-save_action="uCat_item_admin.variant_price_update(value,<?=$var->var_id?>,'prev_price')"
                    >
                        <?=number_format($var->prev_price,(count(explode('.',$var->prev_price))>1?2:0),',',' ')?> <?=$currency?>
                    </span>
                </td>
                <?}
                if($uCat->item_quantity_show){?>
                <td>
                    <span class="eip_element" id="uCat_admin_item_get_variants_quantity_<?=$var->var_id?>"
                          onclick="eip_el.init_el(this)"
                          data-value="<?=rawurlencode($var->var_quantity)?>"
                          data-el_type="textfield"
                          data-mask="9{+}"
                          data-save_action="uCat_item_admin.variant_quantity_update(value,<?=$var->var_id?>)"
                    >
                        <?=$var->var_quantity?>
                    </span>
                </td>
                <?}?>
                <td><!--suppress HtmlFormInputWithoutLabel -->
                    <input type="checkbox" id="uCat_price_switcher_inaccurate_price_<?=$var->var_id?>" class="uCat_price_switcher_inaccurate_price_bootstrap-switch" <?=(int)$var->inaccurate_price?'checked':''?>></td>
                <td><!--suppress HtmlFormInputWithoutLabel -->
                    <input type="checkbox" id="uCat_price_switcher_request_price_<?=$var->var_id?>" class="uCat_price_switcher_request_price_bootstrap-switch" <?=(int)$var->request_price?'checked':''?>></td>
                <?if($base_type_id==0) {?>
                    <td id="uCat_admin_item_get_variants_avail_id_file_id_<?=$var->var_id?>" class="<?=$uCat->uCat->avail_type_id2class($uCat->uCat->avail_id2avail_data($var->avail_id)->avail_type_id)?>">
                        <?
                        $q_avails=$uCat->uCat->get_avails();
                        $avail_str='{';
                        /** @noinspection PhpUndefinedMethodInspection */
                        $avail=$q_avails->fetch_object();
                        for($i=0;$avail;$i++) {
                            $avail_str.='"'.$i.'":{
                            "val":"'.$avail->avail_id.'",
                            "label":"'.rawurlencode($avail->avail_label).'",
                            "selected":"'.((int)$var->avail_id==(int)$avail->avail_id?'1':'0').'"
                            }';
                            /** @noinspection PhpUndefinedMethodInspection */
                            if($avail=$q_avails->fetch_object()) $avail_str.=',';
                        }
                        $avail_str.='}';
                        ?>
                        <span id="uCat_admin_item_get_variants_avail_id_<?=$var->var_id?>"
                            class="eip_element"
                            onclick="eip_el.init_el(this)"
                            data-el_type="selectbox"
                            data-save_action="uCat_item_admin.variant_avail_id_update(value,<?=$var->var_id?>)"
                            data-selectbox_values="<?=rawurlencode($avail_str);?>"
                            >
                            <?=$uCat->uCat->avail_id2avail_data($var->avail_id)->avail_label?>
                        </span>
                    </td>
                <?}
                else {?>
                    <td id="uCat_admin_item_get_variants_avail_id_file_id_<?=$var->var_id?>">
                        <div id="uCat_admin_item_get_variants_file_id_<?=$var->var_id?>">
                            <?=$var->file_id=='0'?'<div class="well-sm bg-danger uTooltip" title="Такой вариант товара невозможно купить"><span class="icon-attention"></span> Файл не указан</div>':
                                ('<a target="_blank" href="'.u_sroot.'uDrive/file/'.$var->file_id.'/'.$uCat->uDrive->file_id2data($var->file_id)->file_hashname.'">'.$uCat->uDrive->file_id2data($var->file_id)->file_name.'</a>')?>
                            <div class="clearfix" style="margin-top: 2px">
                                <button class="btn btn-sm btn-primary" onclick="uCat_item_admin.set_file_init(<?=$var->var_id?>,<?=$var->file_id?><?=(int)$var->file_id?(',\''.rawurlencode($uCat->uDrive->file_id2data($var->file_id)->file_hashname).'\',\''.rawurlencode($uCat->uDrive->file_id2data($var->file_id)->file_name).'\''):''?>)">Указать файл</button>
                            </div>
                        </div>
                    </td>
                <?}?>
            </tr>
        <?}?>
</table>

<div class="row">
    <div class="col-md-6">
        <div class="bs-callout bs-callout-primary">
            <p>Нажмите <span class="eip_element">на поле</span>, чтобы его редактировать</p>
        </div>

        <div class="bs-callout bs-callout-default">
            <p>У каждого товара есть основной вариант. Он отображается во всех списках товаров по умолчанию.</p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="bs-callout bs-callout-default">
            <p>Можно создавать несколько вариантов одного и того же товара.<br>
                Варианты могут отличаться по цене, типу товара (ссылка для скачивания для электронных книг, обычный товар и т.п.).</p>
            <p>Используйте эту возможность, если у вашего товара есть несколько вариантов</p>
        </div>
    </div>
</div>

<?}
else {?>
    <div class="jumbotron">
        <p>У этого товара пока только один вариант. Вы можете <a href="javascript:void(0)" class="btn-link" onclick="uCat_item_admin.new_variant_with_options_dg()">добавить еще</a></p>
    </div>
    <div class="bs-callout bs-callout-default">
        <p>Можно создавать несколько вариантов одного и того же товара.<br>
            Варианты могут отличаться по цене, типу товара (ссылка для скачивания для электронных книг, обычный товар и т.п.).</p>
        <p>Используйте эту возможность, если у вашего товара есть несколько вариантов</p>
    </div>
<?}?>
