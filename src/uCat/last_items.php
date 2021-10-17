<?php
use processors\uFunc;
use uCat\common;

require_once 'inc/item_avatar.php';
require_once 'processors/classes/uFunc.php';
require_once 'uCat/classes/common.php';
class uCat_last_items {
    private $uCore,$item_fields_sql;
    public $q_items,$q_fields,$curPage,
    $item_fields,$item_fields_id2data,$filter_query,
$items_count,$list_view,$field_type_id2sql_type,$field_type_id2style,$q_fields_types;

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat_common=new common($this->uCat);
        $this->avatar=new item_avatar($this->uCore);
        $this->q_items_pdo=1;

        $this->checkData();
        $this->get_cat_fields();
        $this->get_cat_items();
        $this->get_field_types();
    }
    private function checkData() {
        $this->curPage=0;
        if(isset($_GET['page'])) {
            if(uString::isDigits($_GET['page'])) $this->curPage=$_GET['page'];
        }

        if(isset($_GET['list_view'])) {
            if($_GET['list_view']=='table'||$_GET['list_view']=='plane') $_SESSION['uCat']['items_view']=$_GET['list_view'];
        }
        if(!isset($_SESSION['uCat']['items_view'])) $_SESSION['uCat']['items_view']=$this->uCore->uFunc->getConf("items_def_view","uCat");
    }
    private function get_cat_fields() {
        if(!$this->q_fields=$this->uCore->query('uCat',"SELECT DISTINCT
        `u235_fields`.`field_id`,
        `field_title`,
        `field_units`,
        `field_sql_type`,
        `u235_fields`.`field_type_id`,
        `field_style`,
        `filter_type_val`,
        `tablelist_show`,
        `planelist_show`,
        `tileslist_show`,
        `tileslist_show_on_card`,
        `sort_show`
        FROM
        `u235_fields`,
        `u235_fields_types`,
        `u235_fields_filter_types`
        WHERE
        `u235_fields`.`filter_type_id`=`u235_fields_filter_types`.`filter_type_id` AND
        `u235_fields`.`field_type_id`=`u235_fields_types`.`field_type_id` AND
        (
        `u235_fields`.`tileslist_show`='1' OR
        `u235_fields`.`tileslist_show_on_card`='1' OR
        `u235_fields`.`planelist_show`='1' OR
        `u235_fields`.`tablelist_show`='1' OR
        `u235_fields_filter_types`.`filter_type_val`!='no'
        ) AND
        `u235_fields`.`field_type_id`!='0' AND
        `u235_fields`.`site_id`='".site_id."'
        ORDER BY
        `field_pos` ASC,
        `field_title` ASC
        ")) $this->uCore->error(5);

        $this->item_fields_sql="";
        for($i=0;$fields=$this->q_fields->fetch_assoc();) {
            if($fields['tablelist_show']=='1'&&$_SESSION['uCat']['items_view']=='table') {
                $this->item_fields_sql.="u235_items.field_".$fields['field_id'].",";
                $this->item_fields[$i]=$fields['field_id'];
                $this->item_fields_id2data[$fields['field_id']]=$fields;
                $i++;
            }
            elseif($fields['planelist_show']=='1'&&$_SESSION['uCat']['items_view']=='plane') {
                $this->item_fields_sql.="u235_items.field_".$fields['field_id'].",";
                $this->item_fields[$i]=$fields['field_id'];
                $this->item_fields_id2data[$fields['field_id']]=$fields;
                $i++;
            }
            elseif($fields['tileslist_show_on_card']=='1'&&$_SESSION['uCat'][' ']=='tiles') {
                $this->item_fields_sql.="u235_items.field_".$fields['field_id'].",";
                $this->item_fields[$i]=$fields['field_id'];
                $this->item_fields_id2data[$fields['field_id']]=$fields;
                $i++;
            }
        }
    }
    private function get_cat_items(){
        $items_per_page=$this->uCore->uFunc->getConf('last_items_number_show','uCat');
        if(!uString::isDigits($items_per_page)) $items_per_page=30;
        if($items_per_page<1) $items_per_page=30;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_items=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_items.item_id,
            item_title,
            item_url,
            item_descr,
            item_price,
            inaccurate_price,
            request_price,
            ".$this->item_fields_sql."
            item_img_time,
            avail_label,
            avail_descr,
            avail_id,
            avail_type_id,
            has_variants,
            base_type_id
            
            FROM
            u235_items,
            u235_items_avail_values,
            items_types
            
            WHERE
            parts_autoadd=0 AND
            items_types.site_id=u235_items.site_id AND
            items_types.type_id=u235_items.item_type AND
        
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            u235_items_avail_values.avail_type_id!=2 AND
            u235_items_avail_values.site_id=:site_id AND
            u235_items_avail_values.site_id=u235_items.site_id
            
            ORDER BY
            u235_items.item_id DESC
            
            LIMIT ".$items_per_page);

            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->q_items->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_items->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('6'/*.$e->getMessage()*/);}

        //get total item's count in this cat
        if(!$query=$this->uCore->query("uCat","SELECT
        COUNT(DISTINCT `u235_items`.`item_id`)
        FROM
        `u235_items`,
        `u235_items_avail_values`
        WHERE
        parts_autoadd=0 AND
        `u235_items`.`item_avail`=`u235_items_avail_values`.`avail_id` AND
        `u235_items_avail_values`.`avail_type_id`!='2' AND
        `u235_items`.`site_id`='".site_id."' AND
        `u235_items_avail_values`.`site_id`=`u235_items`.`site_id`
        ")) $this->uCore->error(7);
        $qr=$query->fetch_assoc();
        $this->items_count=$qr["COUNT(DISTINCT `u235_items`.`item_id`)"];
    }
    private function get_field_types() {
        if(!$this->q_fields_types=$this->uCore->query("uCat","SELECT
        `field_type_id`,
        `field_type_title`,
        `field_sql_type`,
        `field_style`
        FROM
        `u235_fields_types`
        ")) $this->uCore->error(3);

        while($field=$this->q_fields_types->fetch_object()) {
            $this->field_type_id2sql_type[$field->field_type_id]=$field->field_sql_type;
            $this->field_type_id2style[$field->field_type_id]=$field->field_style;
        }
    }
}
$uCat=new uCat_last_items($this);
if(!isset($_GET['results_only'])) {
    $this->uFunc->incJs(u_sroot.'uCat/js/last_items.min.js');
    $this->uFunc->incJs(u_sroot.'js/phpjs/functions/datetime/date.js');

    //$this->page_breads='<a href="'.u_sroot.'">Главная</a>';
}
ob_start();

if(!isset($_GET['results_only'])) {
    /** @noinspection PhpIncludeInspection */
    include_once 'uCat/inc/request_price_form.php';?>
<h1 class="page-header"><?=$this->page['page_title'];?></h1>
<?}?>
<div class="uCat_items">
    <div class="items_container">
        <?
        if(isset($_GET['sort'],$_GET['order'])) {
            if(!empty($_GET['sort'])&&!empty($_GET['order'])) {
                if(strpos($_GET['sort'],'item_')===0||strpos($_GET['sort'],'field_')===0) {
                    $sort=strtolower($_GET['sort']);
                    $order=strtolower($_GET['order']);
                }
            }
        }
        if(!isset($sort,$order)) {
            $sort=strtolower($this->uFunc->getConf("def_sort_field","uCat"));
            $order=strtolower($this->uFunc->getConf("def_sort_order","uCat"));
        }
        /**/?><!--
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <ul class="nav navbar-nav navbar-right">
                    <li>Вид каталога&nbsp;</li>
                    <li>
                        <div class="btn-group">
                            <button class="btn btn-default btn-sm <?/*=($_SESSION['uCat']['items_view']=='table')?'active':''*/?> uTooltip" title="Отображать таблицей" onclick="uCat.filter_set_view('table')"><span class="glyphicon glyphicon-th"></span></button>
                            <button class="btn btn-default btn-sm <?/*=($_SESSION['uCat']['items_view']=='plane')?'active':''*/?> uTooltip" title="Отображать списком" onclick="uCat.filter_set_view('plane')"><span class="glyphicon glyphicon-th-list"></span></button>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

            --><?

        /*if($_SESSION['uCat']['items_view']=='table') include_once 'templates/items/table.php';
        else */include_once 'templates/items/plane.php';?>
    </div>
</div>

<?if(!isset($_GET['results_only'])) {
    include_once 'uCat/dialogs/uCat_cart.php';?>
<script type="text/javascript">
    if(typeof uCat==="undefined") uCat={};
    uCat.list_view="<?=$_SESSION['uCat']['items_view']?>";
</script>
<?}?>

<?$this->page_content=ob_get_contents();
ob_end_clean();
if(isset($_GET['results_only'])) echo $this->page_content;
else include "templates/template.php";
