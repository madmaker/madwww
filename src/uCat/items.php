<?php
require_once 'inc/item_avatar_new.php';
require_once 'inc/cat_filter.php';
require_once 'uCat/classes/common.php';

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uDrive/classes/common.php";
class uCat_items {
    public $uFunc;
    public $item_fields_all;
    public $uSes;
    public $uCat;
    public $buy_button_show;
    public $price_is_used;
    public $item_prev_price_show;
    public $item_quantity_show;
    public $item_availability_show;
    public $enable_item_plus_and_minus;
    private $item_fields4filter_id2data;
    private $uCore,$sort,$order,$item_fields_sql;
    public $uCat_common,$uDrive_common,
        $cat_id,$sect_id,$cat,$sect,$q_items,$q_fields,$curPage,
        $def_sort_order,$def_sort_field,
    $items_per_page,$item_fields,$item_fields_id2data,$filter_query,$filter_js_define,
$items_count,$list_view,$debug,$filter_bar,$avatar,$field_type_id2style,$field_type_id2sql_type,$q_fields_types,$q_fields_filter_types,$q_fields_places,$q_fields_label_styles,$q_fields_effects,
    $uDrive_folder_id,$enable_item_quantity,$enable_tiles_plus_and_minus,$enable_table_plus_and_minus,$enable_plane_plus_and_minus;

    private function get_uDrive_folder_id() {
        //define uDrive cat default folder
        if($this->cat->uDrive_folder_id=='0') {
            $uDrive_uCat_cats_folder_id=$this->uDrive_common->get_module_folder_id("uCat_cats");
            $cat_title=trim(uString::sanitize_filename(uString::sql2text($this->cat->cat_title)));
            if(!strlen($cat_title)) $cat_title='Категория '.$this->cat_id;
            $this->cat->uDrive_folder_id=$this->uDrive_common->create_folder($cat_title,$uDrive_uCat_cats_folder_id);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                uDrive_folder_id=:folder_id
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':folder_id', $this->cat->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        }
    }
    private function get_field_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_types=$this->uCore->query("uCat","SELECT
        `field_type_id`,
        `field_type_title`,
        `field_sql_type`,
        `field_style`
        FROM
        `u235_fields_types`
        ")) $this->uFunc->error(20);

        /** @noinspection PhpUndefinedMethodInspection */
        while($field=$this->q_fields_types->fetch_object()) {
            $this->field_type_id2sql_type[$field->field_type_id]=$field->field_sql_type;
            $this->field_type_id2style[$field->field_type_id]=$field->field_style;
        }
    }
    private function error() {
        header('Location: '.u_sroot.'uCat/sects');
    }
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) $this->error();
        $this->cat_id=$this->uCore->url_prop[1];
        if(uString::isDigits($this->cat_id)) $q_cat_id="`cat_id`='".$this->cat_id."' AND";
        else {
            $cat_url=uString::text2sql($this->cat_id);
            $q_cat_id="`cat_url`='".$cat_url."' AND";
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        `cat_id`,
        `cat_title`,
        `seo_title`,
        `cat_descr`,
        `cat_url`,
        `seo_descr`,
        `cat_keywords`,
        `primary_sect_id`,
        `cat_avatar_time`,
        `def_sort_order`,
        `def_sort_field`,
        `uDrive_folder_id`,
        views_counter
        FROM
        `u235_cats`
        WHERE
        ".$q_cat_id."
        `site_id`='".site_id."'
        ")) $this->uFunc->error(30);
        if(!mysqli_num_rows($query)) $this->error();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->cat=$query->fetch_object();
        $this->cat_id=$this->cat->cat_id;

        if($this->cat->def_sort_order=='0') $this->def_sort_order=$this->uCore->uFunc->getConf("def_sort_order","uCat");
        else {
            if($this->cat->def_sort_order=='1') $this->def_sort_order='ASC';
            elseif($this->cat->def_sort_order=='2') $this->def_sort_order='DESC';
        }
        if($this->cat->def_sort_field=='0') $this->def_sort_field=$this->uCore->uFunc->getConf("def_sort_field","uCat");
        else {
            if($this->cat->def_sort_field=='-1') $this->def_sort_field='item_title';
            elseif($this->cat->def_sort_field=='-2') $this->def_sort_field='item_price';
            elseif($this->cat->def_sort_field=='-3') $this->def_sort_field='item_id';
            else $this->cat->def_sort_field=$this->def_sort_field='field_'.$this->cat->def_sort_field;
        }

        $this->order="ASC";
        if(isset($_GET['order'])) {
            $order=strtolower($_GET['order']);
            if($order=='desc') $this->order='DESC';
            else $this->order='ASC';
        }
        else $this->order=$this->def_sort_order;

        $this->sort="`item_title`";
        if(isset($_GET['sort'])) $this->sort=$_GET['sort'];
        else $this->sort=$this->def_sort_field;

        if($this->sort=='item_price') $this->sort="`item_price`";
        elseif(strpos($this->sort,"field_")===0) {
            $field_id=substr($this->sort,6);
            if(uString::isDigits($field_id)) {
                /** @noinspection PhpUndefinedMethodInspection */
                if(!$query=$this->uCore->query("uCat","SELECT
                `cat_id`
                FROM
                `u235_cats_fields`
                WHERE
                `cat_id`='".$this->cat_id."' AND
                `field_id`='".$field_id."' AND
                `site_id`='".site_id."'
                ")) $this->uFunc->error(40);
                if(mysqli_num_rows($query)) $this->sort="`field_".$field_id."`";
                else $this->sort='`item_title`';
            }
            else $this->sort='`item_title`';
        }
        else $this->sort='`item_title`';

        $_GET['order']=$this->order;
        $_GET['sort']=str_replace('`','',$this->sort);

        $this->curPage=0;
        if(isset($_GET['page'])) {
            if(uString::isDigits($_GET['page'])) $this->curPage=$_GET['page'];
        }

        if(isset($_GET['list_view'])) {
            if($_GET['list_view']=='table'||$_GET['list_view']=='plane'||$_GET['list_view']=='tiles') $_SESSION['uCat']['items_view']=$_GET['list_view'];
        }
        if(!isset($_SESSION['uCat']['items_view'])) $_SESSION['uCat']['items_view']=$this->uCore->uFunc->getConf("items_def_view","uCat");
    }
    private function increase_views_counter($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_cats
            SET
            views_counter=views_counter+1
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1583243136'/*.$e->getMessage()*/);}
    }

    private function get_sect_info() {
        if(!(int)$this->cat->primary_sect_id) $this->uCat_common->set_auto_primary_sect_id($this->cat_id,site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_title,
            sect_url,
            sect_id
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->cat->primary_sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($this->sect=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->sect_id=$this->sect->sect_id;
            return 1;
        }
        else $this->sect=false;

        return 0;
    }
    private function get_cat_fields() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_fields.field_id,
            field_title,
            field_units,
            field_sql_type,
            u235_fields.field_type_id,
            field_style,
            filter_type_val,
            tablelist_show,
            planelist_show,
            tileslist_show,
            tileslist_show_on_card,
            sort_show
            FROM
            u235_fields
            JOIN 
            u235_fields_types
            ON
            u235_fields.field_type_id=u235_fields_types.field_type_id
            JOIN 
            u235_fields_filter_types
            ON
            u235_fields.filter_type_id=u235_fields_filter_types.filter_type_id
            JOIN 
            u235_cats_fields
            ON
            u235_cats_fields.site_id=u235_fields.site_id AND
            u235_fields.field_id=u235_cats_fields.field_id
            WHERE
            u235_cats_fields.cat_id=:cat_id AND
            (
            u235_fields.tablelist_show='1' OR
            u235_fields.planelist_show='1' OR
            u235_fields.tileslist_show='1' OR
            u235_fields.tileslist_show_on_card='1' OR
            u235_fields.sort_show='1' OR
            u235_fields_filter_types.filter_type_val!='no'
            ) AND
            u235_fields.field_type_id!='0' AND
            u235_fields.site_id=:site_id
            ORDER BY
            field_pos ASC,
            field_title ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_fields=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        $this->item_fields_sql="";
        $this->item_fields=[];
        $i=0;
        foreach ($this->q_fields as $key => $fields) {
            if($fields->filter_type_val!='no') $this->item_fields4filter_id2data[$fields->field_id]=$fields;

            if(
            ((int)$fields->tablelist_show&&$_SESSION['uCat']['items_view']==='table')||
            ((int)$fields->planelist_show&&($_SESSION['uCat']['items_view']==='plane'))||
            ((int)$fields->tileslist_show&&($_SESSION['uCat']['items_view']==='tiles'))||
            ((int)$fields->tileslist_show_on_card&&($_SESSION['uCat']['items_view']==='tiles'))
            ) {
                $this->item_fields_sql.="u235_items.field_".$fields->field_id.",";
                $this->item_fields[$i]=$fields->field_id;
                $this->item_fields_id2data[$fields->field_id]=$fields;
                $this->item_fields_all[$i]=$fields->field_id;
                $i++;
            }
        }
        reset($this->q_fields);
    }
    private function def_filter() {
        $field_id2ind=[];
        $this->filter_js_define='$(\'#uCat_filter_bar input\').prop("checked",false);';
        for($i=0,$j=0;isset($_GET['field_id_'.$i]);$i++) {

            $id=$_GET['field_id_'.$i];
            if(!uString::isDigits($id)&&$id!='price') continue;
            $val=$_GET['field_val_'.$i];

            if(!isset($field_id2ind[$id])) {
                $field_id2ind[$id]=$j;
                $field_id[$j]=$id;
                $field_val[$j][]=uString::text2sql($val);
                $j++;
            }
            else {
                $ind=$field_id2ind[$id];
                $field_val[$ind][]=uString::text2sql($val);
            }
        }

        $this->filter_query="1=1 ";
        if(isset($field_id)) {
            for($i=0;$i<count($field_id);$i++) {
//                print_r($field_id);
//                print_r($this->item_fields_id2data);
                if(isset($this->item_fields4filter_id2data[$field_id[$i]])) {
                    $field=$this->item_fields4filter_id2data[$field_id[$i]];

                    if($field->filter_type_val=='range') {
                        /** @noinspection PhpUndefinedVariableInspection */
                        $val=explode(' - ',$field_val[$i][0]);
                        $min=$val[0];
                        $max=$val[1];

                        $this->filter_query.=" AND (field_".$field_id[$i].">=".$min." AND field_".$field_id[$i]."<=".$max.")";

                        $this->filter_js_define.='jQuery( "#slider-range_field_'.$field_id[$i].'" ).slider( "option", "values",['.$min.','.$max.']);
                    jQuery ( "#amount_field_'.$field_id[$i].'" ).val("'.$field_val[$i][0].'");
                    uCat.filter_set_range("'.$field_id[$i].'","'.$field_val[$i][0].'");';
                    }
                    elseif($field->filter_type_val=='checkbox') {
                        /** @noinspection PhpUndefinedVariableInspection */
                        if(count($field_val[$i])) {
                            $this->filter_query.="AND (1=0";
                            for($n=0;$n<count($field_val[$i]);$n++) {
                                $this->filter_js_define.='
                                var last=uCat.filter_field_id.length;
                                uCat.filter_field_id[last]='.$field_id[$i].';
                                uCat.filter_field_val[last]="'.$field_val[$i][$n].'";
                                $(\'input.uCat_filter_checkbox_'.$field_id[$i].'[value="'.$field_val[$i][$n].'"]\').prop("checked",true);
                                ';
                                $this->filter_query.=" OR field_".$field_id[$i]."='".$field_val[$i][$n]."'";
                            }
                            $this->filter_query.=") ";
                        }
                    }
                }
                elseif($field_id[$i]=='price') {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $val=explode(' - ',$field_val[$i][0]);
                    $min=$val[0];
                    $max=$val[1];

                    $this->filter_query.=" AND (item_price>=".$min." AND item_price<=".$max.")";

                    $this->filter_js_define.='jQuery( "#slider-range_field_price" ).slider( "option", "values",['.$min.','.$max.']);
                    jQuery ( "#amount_field_price" ).val("'.$field_val[$i][0].'");
                    uCat.filter_set_range("price","'.$field_val[$i][0].'");';
                }

            }
//            echo "SSSS".$this->filter_query;
//            print_r($field_id);
        }
    }
    private function get_cat_items($site_id=site_id){
        $this->def_filter();

        $this->items_per_page=$this->uCore->uFunc->getConf('items_on_page','uCat');

        if($this->uSes->access(25)) $q_items_avail_values="";
        else $q_items_avail_values="u235_items_avail_values.avail_type_id!=2 AND";

        $request_price_order=(int)$this->uCore->uFunc->getConf("request_price_items_order","uCat");
        if($request_price_order) $q_request_price_order="request_price ASC,";
        else $q_request_price_order="request_price DESC,";

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->q_items=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_items.item_id,
            item_title,
            item_url,
            unit_id,
            item_descr,
            item_price,
            prev_price,
            inaccurate_price,
            request_price,
            ".$this->item_fields_sql."
            item_img_time,
            avail_label,
            avail_descr,
            item_article_number,
            quantity,
            avail_id,
            avail_type_id,
            has_variants,
            base_type_id
            FROM
            u235_items,
            u235_cats_items,
            u235_items_avail_values,
            items_types
            WHERE
            parts_autoadd=0 AND
            items_types.site_id=:site_id AND
            items_types.type_id=u235_items.item_type AND
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            ".$q_items_avail_values."
            u235_items_avail_values.site_id=:site_id AND
            u235_items.item_id=u235_cats_items.item_id AND
            u235_cats_items.cat_id=:cat_id AND
            u235_items.site_id=:site_id AND
            u235_cats_items.site_id=:site_id AND
            (".$this->filter_query.")
            ORDER BY
            ".$q_request_price_order."
            ".$this->sort." ".$this->order."
            LIMIT ".($this->curPage*$this->items_per_page).",".$this->items_per_page."
            ");
            /** @noinspection PhpUndefinedMethodInspection */$this->q_items->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_items->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->q_items->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}

        //get total item's count in this cat
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        COUNT(DISTINCT `u235_items`.`item_id`)
        FROM
        `u235_items`,
        `u235_cats_items`,
        `u235_items_avail_values`,
        `items_types`
        WHERE
        parts_autoadd=0 AND
       `items_types`.`site_id`='".site_id."' AND
        `items_types`.`type_id`=`u235_items`.`item_type` AND
        `u235_items`.`item_avail`=`u235_items_avail_values`.`avail_id` AND
        ".$q_items_avail_values."
        `u235_items_avail_values`.`site_id`='".site_id."' AND
        `u235_items`.`item_id`=`u235_cats_items`.`item_id` AND
        `u235_cats_items`.`cat_id`='".$this->cat_id."' AND
        `u235_items`.`site_id`='".site_id."' AND
        `u235_cats_items`.`site_id`='".site_id."' AND
        (".$this->filter_query.")
        ")) $this->uFunc->error(90);
        /** @noinspection PhpUndefinedMethodInspection */
        $qr=$query->fetch_assoc();
        $this->items_count=$qr["COUNT(DISTINCT `u235_items`.`item_id`)"];
    }
    private function get_fields_filter_types() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_filter_types=$this->uCore->query("uCat","SELECT
        `filter_type_id`,
        `filter_type_sql`,
        `filter_type_title`
        FROM
        `u235_fields_filter_types`
        ")) $this->uFunc->error(100);
    }
    private function get_fields_places() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_places=$this->uCore->query("uCat","SELECT
        `place_id`,
        `place_title`
        FROM
        `u235_fields_places`
        ")) $this->uFunc->error(110);
    }
    private function get_fields_label_styles() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_label_styles=$this->uCore->query("uCat","SELECT
        `label_style_id`,
        `label_style_title`
        FROM
        `u235_fields_label_styles`
        ")) $this->uFunc->error(120);
    }
    private function get_fields_effects() {
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->q_fields_effects=$this->uCore->query("uCat","SELECT
        `effect_id`,
        `effect_title`
        FROM
        `u235_fields_effects`
        ")) $this->uFunc->error(130);
    }
    public function insertPageNums($pageNumber) {
        $cnt='';
        if($pageNumber>1) {
        $cnt='<ul class="pagination uCat_items_pagination">';
        $butNum=4;//number of buttons before and after
            $start=0;
            $end=$pageNumber;
            if($pageNumber>$butNum*2) {
                $start=($this->curPage-$butNum)<0?0:($this->curPage-$butNum);
                $end=($this->curPage+$butNum)>$pageNumber?$pageNumber:($this->curPage+$butNum);
                if(($start+$end)<$pageNumber) $end=($start+$butNum*2)<$pageNumber?$start+$butNum*2:$pageNumber;
            }
            if($start>0) {
                if(isset($_GET['results_only'])) $cnt.='<li onclick="uCat.filter_set_page('.($start-1).')"><a href="javascript:void(0);">&laquo;</a></li>';
                else $cnt.='<li><a href="'.u_sroot.'uCat/'.$this->uCore->page_name.'/'.$this->cat_id.'?page='.($start-1).'">&laquo;</a></li>';
            }
            for($i=$start;$i<$end;$i++) {
                if(isset($_GET['results_only'])) {
                    $cnt.='<li '; if($this->curPage==$i) $cnt.='class="active"'; $cnt.=' onclick="uCat.filter_set_page('.$i.')"><a href="javascript:void(0);">'.($i+1).'</a></li>';
                }
                else {
                    $cnt.='<li '; if($this->curPage==$i) $cnt.='class="active"'; $cnt.='><a href="'.u_sroot.'uCat/'.$this->uCore->page_name.'/'.$this->cat_id.'?page='.$i.'">'.($i+1).'</a></li>';
                }
            }
            if($end<$pageNumber) {
                if(isset($_GET['results_only'])) $cnt.='<li onclick="uCat.filter_set_page('.($end).')"><a href="javascript:void(0);">&raquo;</a></li>';
                else $cnt.='<li><a href="'.u_sroot.'uCat/'.$this->uCore->page_name.'/'.$this->cat_id.'?page='.($end).'">&raquo;</a></li>';
            }
            $cnt.='</ul>';
        }
        return $cnt;
    }

    private function define_breadcrumb() {
        if((int)$this->uFunc->getConf("Show link to sects in bc","uCat")) $this->uCore->uBc->add_info->html='<li><a href="'.u_sroot.'uCat/sects">Каталог</a></li>';
        else $this->uCore->uBc->add_info->html='';

        if($this->sect) $this->uCore->uBc->add_info->html.='<li><a href="'.u_sroot.'uCat/cats/'.(strlen($this->sect->sect_url)?uString::sql2text($this->sect->sect_url):$this->sect->sect_id).'">'.uString::sql2text($this->sect->sect_title).'</a></li>';
        $this->uCore->uBc->add_info->html.='<li class="active"><a id="uCat_cat_breadcrumb" href="'.u_sroot.'uCat/items/'.(strlen($this->cat->cat_url)?uString::sql2text($this->cat->cat_url,1):$this->cat_id).'">'.uString::sql2text($this->cat->cat_title,1).'</a></li>';
    }

    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->avatar=new uCat_item_avatar($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->uDrive_common=new \uDrive\common($this->uCore);
        $this->uCat_common=new \uCat\common($this->uCore);
        $this->uCat=&$this->uCat_common;

        $this->enable_item_quantity = (int)$this->uFunc->getConf('item_quantity_show','uCat','return false', site_id);
        $this->enable_tiles_plus_and_minus = (int)$this->uFunc->getConf('enable_tiles_plus_and_minus','uCat','return false', site_id);
        $this->enable_table_plus_and_minus = (int)$this->uFunc->getConf('enable_table_plus_and_minus','uCat','return false', site_id);
        $this->enable_plane_plus_and_minus = (int)$this->uFunc->getConf('enable_plane_plus_and_minus','uCat','return false', site_id);
        $this->buy_button_show              = (int) $this->uFunc->getConf('buy_button_show','uCat');
        $this->price_is_used                = (int) $this->uFunc->getConf('price_is_used','uCat');
        $this->item_prev_price_show         = (int) $this->uFunc->getConf('item_prev_price_show','uCat');
        $this->item_quantity_show           = (int) $this->uFunc->getConf('item_quantity_show','uCat');
        $this->item_availability_show       = (int) $this->uFunc->getConf('item_availability_show','uCat');
        $this->enable_item_plus_and_minus   = (int) $this->uFunc->getConf('enable_item_plus_and_minus','uCat');

        $this->checkData();
        $this->get_sect_info();
        $this->get_cat_fields();
        $this->get_cat_items();
        $this->get_field_types();

        if((int)$this->uFunc->getConf("use_filter","uCat")) {
            $filter=new cat_filter($this->uCore,$this->q_fields,$this->cat_id);
            $this->filter_bar=$filter->make_filter();
        }

        if($this->uSes->access(25)) {
            $this->get_uDrive_folder_id();
            $this->get_fields_filter_types();
            $this->get_fields_places();
            $this->get_fields_label_styles();
            $this->get_fields_effects();
        }

        $this->define_breadcrumb();

        $this->uFunc->incCss('/uCat/css/items.min.css');
        if((int)$this->uFunc->getConf("Table view is enabled","uCat")) $this->uFunc->incCss('/uCat/css/table.min.css');
        if((int)$this->uFunc->getConf("Plane view is enabled","uCat")) $this->uFunc->incCss('/uCat/css/plane.min.css');
        if((int)$this->uFunc->getConf("Tiles view is enabled","uCat")) $this->uFunc->incCss('/uCat/css/tiles.min.css');

        //bootstrap-touchspin
        $this->uFunc->incCss('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.css');
        $this->uFunc->incJs('js/bootstrap_plugins/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js');

        //phpjs
        $this->uFunc->incJs(u_sroot."js/phpjs/functions/strings/str_replace.js");

        $this->increase_views_counter();
    }
}
$uCat=new uCat_items($this);

ob_start();

if(!isset($_GET['results_only'])) {
    if($uCat->uSes->access(25)) {
        /** @noinspection PhpIncludeInspection */
        include_once 'uDrive/inc/my_drive_manager.php';?>
        <div id="uDrive_my_drive_uploader_init"></div>
        <script type="text/javascript">
            if(typeof uCat_items_admin==="undefined") uCat_items_admin={};
            if(typeof uDrive_manager==="undefined") uDrive_manager={};

            uCat_items_admin.uDrive_folder_id=<?=$uCat->cat->uDrive_folder_id;?>;//ID ПАПКИ страницы
            $(document).ready(function() {
                uDrive_manager.init('uDrive_my_drive_uploader',<?=$uCat->cat->uDrive_folder_id;?>, 1, "uCat_items_admin.insert_tinymce_url", 'uCat', 'cat',<?=$uCat->cat_id?>);
            });
        </script>
        <?
    }

    $this->uFunc->incJs('/uCat/js/common.js');
    $this->uFunc->incJs(u_sroot.'uCat/js/items.min.js');
    if($uCat->uSes->access(25)) $this->uFunc->incJs(u_sroot.'uCat/js/items_admin.min.js');

    if(!empty($uCat->cat->seo_title)) $this->page['page_title']=uString::sql2text($uCat->cat->seo_title);
    else $this->page['page_title']=uString::sql2text($uCat->cat->cat_title).'. '.($uCat->sect?$uCat->sect->sect_title:'').'. Каталог';
    if(!empty($uCat->cat->seo_descr)) $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->cat->seo_descr))))));
    else $this->page['meta_description']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->cat->cat_descr))))));
    if(!empty($uCat->cat->cat_keywords)) $this->page['meta_keywords']=stripslashes(htmlspecialchars(strip_tags(str_replace("'",'',str_replace('"','',uString::sql2text($uCat->cat->cat_keywords))))));
}

$pageNumber=ceil($uCat->items_count/$uCat->items_per_page);?>
<script type="text/javascript">
    if(typeof uCat==="undefined") uCat={};
    uCat.var_selected=[];
    uCat.var_selected_price=[];
</script>
    <div class="uCat_items">
<?if(!isset($_GET['results_only'])) {
    if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===1) include "uCat/templates/search.php";?>
<h1 class="page-header"><span id="uCat_cat_title"><?=uString::sql2text($uCat->cat->cat_title)?></span> <?if((int)$uCat->items_count){
    ?><small class="cat_items_shown_number">(<?=$uCat->items_per_page*$uCat->curPage+1?> - <?=($to_number=$uCat->items_per_page*$uCat->curPage+$uCat->items_per_page)>$uCat->items_count?$uCat->items_count:$to_number?> из <?=$uCat->items_count?>)</small><?
}?></h1>
    <?if((int)$uCat->uFunc->getConf("search_field_pos","uCat")===2) include "uCat/templates/search.php";

    if($uCat->uFunc->getConf("cat_descr_place","uCat")=="top") {?>
        <div class="row"><div class="col-md-12 descr">
                <div id="uCat_cat_descr" <?=$this->uFunc->getConf("cat_descr_only_on_first_page","uCat")?($uCat->curPage?'style="display:none"':''):''?>><?=uString::sql2text($uCat->cat->cat_descr,true)?></div>
            </div></div>
    <?}?>
<?}?>

    <div class="items_container">
            <?/*if(mysqli_num_rows($uCat->q_items)) {*/
                if(isset($_GET['sort'],$_GET['order'])) {
                    if(!empty($_GET['sort'])&&!empty($_GET['order'])) {
                        if(strpos($_GET['sort'],'item_')===0||strpos($_GET['sort'],'field_')===0) {
                            $sort=strtolower($_GET['sort']);
                            $order=strtolower($_GET['order']);
                        }
                    }
                }
                if(!isset($sort,$order)) {
                    $sort=$uCat->def_sort_field;
                    $order=strtolower($uCat->def_sort_order);
                }?>

                <?if((int)$this->uFunc->getConf("show_sorting_in_items_lists","uCat")){?>
        <nav class="navbar navbar-default">
            <div class="container-fluid">
                <ul class="nav navbar-nav navbar-left">
                <li class="uCat_items_sort_order_label">Сортировка:&nbsp;</li>
                <li>
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <select id="uCat_sort_selectbox" name="sort" onchange="uCat.filter_set_sort(jQuery(this).val());" class="form-control selectpicker">
                        <? if($this->uFunc->getConf("price_is_used","uCat")=='1') {?>
                            <option value="sort=item_price&order=asc" <?=($sort=='item_price'&&$order=='asc')?"selected":""?>>Цена, по возрастанию</option>
                            <option value="sort=item_price&order=desc" <?=($sort=='item_price'&&$order=='desc')?"selected":""?>>Цена, по убыванию</option>
                        <?}?>
                        <option value="sort=item_title&order=asc" <?=($sort=='item_title'&&$order=='asc')?"selected":""?>>Название, по возрастанию</option>
                        <option value="sort=item_title&order=desc" <?=($sort=='item_title'&&$order=='desc')?"selected":""?>>Название, по убыванию</option>
                        <?
                        for($i=0;$i<count($uCat->item_fields_all);$i++) {
                            $field=$uCat->item_fields_id2data[$uCat->item_fields_all[$i]];
                            if($field->sort_show=='1'||($field->tablelist_show=='1'&&$_SESSION['uCat']['items_view']=='table')) {
                                echo '<option value="sort=field_'.$field->field_id.'&order=asc" '.(($sort=='field_'.$field->field_id&&strtolower($order)=='asc')?"selected":"").'>'.uString::sql2text($field->field_title).', по возрастанию</option>';
                                echo '<option value="sort=field_'.$field->field_id.'&order=desc" '.(($sort=='field_'.$field->field_id&&strtolower($order)=='desc')?"selected":"").'>'.uString::sql2text($field->field_title).', по убыванию</option>';
                            }
                        }?>
                    </select>
                </li>
            </ul>

            <ul class="nav navbar-nav navbar-right">
                <!--<li>Вид каталога&nbsp;</li>-->
                <li>
                    <div class="btn-group">
                        <?if((int)$this->uFunc->getConf("Table view is enabled","uCat")) {?>
                        <button class="btn btn-default btn-sm <?=($_SESSION['uCat']['items_view']=='table')?'active':''?> uTooltip" title="Таблица" onclick="uCat.filter_set_view('table')"><span class="icon-list-alt"></span></button>
                        <?}
                        if((int)$this->uFunc->getConf("Plane view is enabled","uCat")) {?>
                        <button class="btn btn-default btn-sm <?=($_SESSION['uCat']['items_view']=='plane')?'active':''?> uTooltip" title="Список" onclick="uCat.filter_set_view('plane')"><span class="icon-list"></span></button>
                        <?}
                        if((int)$this->uFunc->getConf("Tiles view is enabled","uCat")) {?>
                        <button class="btn btn-default btn-sm <?=($_SESSION['uCat']['items_view']=='tiles')?'active':''?> uTooltip" title="Плитки" onclick="uCat.filter_set_view('tiles')"><span class="icon-th-large"></span></button>
                        <?}?>
                    </div>
                </li>
            </ul>
            </div>
        </nav>
                <?}?>

                <?=$uCat->insertPageNums($pageNumber)?>
        <?/*}*/

        if($uCat->items_count == 0) {
             print '<div id="empty-item-list">здесь пока нет товаров</div>';
        }
        if($_SESSION['uCat']['items_view']=='table') include_once "uCat/templates/items/table.php";
        elseif($_SESSION['uCat']['items_view']=='tiles') include_once "uCat/templates/items/tiles.php";
        else include_once "uCat/templates/items/plane.php";
        echo $uCat->insertPageNums($pageNumber);?>
    </div>

    <?if(!isset($_GET['results_only'])) {?>
        <p>&nbsp;</p>
        <?if($uCat->uFunc->getConf("cat_descr_place","uCat")=="bottom") {?>
            <div class="row"><div class="col-md-12 descr">
                    <div id="uCat_cat_descr" <?=$this->uFunc->getConf("cat_descr_only_on_first_page","uCat")?($uCat->curPage?'style="display:none"':''):''?>><?=uString::sql2text($uCat->cat->cat_descr,true)?></div>
            </div></div>
        <?}?>
    <?}?>

        <div id="item_views_counter" class="text-muted pull-right"><span class="icon-eye"></span> <?=$uCat->cat->views_counter ?></div>
</div>

<?if(!isset($_GET['results_only'])) {
//    include 'uCat/buy_form.inc.php';
    /** @noinspection PhpIncludeInspection */
    include 'uCat/inc/request_price_form.php';
    ?>
    <script type="text/javascript">

        uCat.cat_id=<?=$uCat->cat_id;?>;
        uCat.cat_descr_only_on_first_page=<?=$this->uFunc->getConf("cat_descr_only_on_first_page","uCat")?>;
        uCat.cur_page=<?=$uCat->curPage?>;
        uCat.pageNumber=<?=$pageNumber?>;
        uCat.list_view="<?=$_SESSION['uCat']['items_view']?>";
        uCat.editable=false;

        uCat.filter_values="<?=rawurlencode($uCat->filter_js_define)?>";

        uCat.def_sort_order="<?=$uCat->def_sort_order?>";
        uCat.def_sort_field="<?=$uCat->def_sort_field?>";

        uCat.one_click_add2cart_btn=<?=$uCat->uFunc->getConf("one_click_add2cart_btn","uCat")?>;

        var buy_button_show=<?=$uCat->buy_button_show?>;
        var price_is_used=<?=$uCat->price_is_used?>;
        var item_prev_price_show=<?=$uCat->item_prev_price_show?>;
        var buy_btn_label=decodeURIComponent("<?=rawurlencode($uCat->uFunc->getConf("buy_btn_label","uCat"))?>");
        let get_item_for_free_btn_txt=decodeURIComponent("<?=rawurlencode($uCat->uFunc->getConf("get_item_for_free_btn_txt","uCat",1))?>");
        var item_quantity_show=<?=$uCat->item_quantity_show?>;
        var item_availability_show=<?=$uCat->item_availability_show?>;
        var enable_item_plus_and_minus=<?=$uCat->enable_item_plus_and_minus?>;
    </script>

    <?if($uCat->uSes->access(25)){//admin part
        //tinymce
        $this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');?>
    <script type="text/javascript">
        uCat.editable=true;
        uCat.cat_title="<?=rawurlencode(uString::sql2text($uCat->cat->cat_title,true))?>";
        uCat.cat_url="<?=rawurlencode(uString::sql2text($uCat->cat->cat_url,true))?>";
        uCat.seo_title="<?=rawurlencode(uString::sql2text($uCat->cat->seo_title,true))?>";
        uCat.seo_descr="<?=rawurlencode(uString::sql2text($uCat->cat->seo_descr,true))?>";
        uCat.cat_keywords="<?=rawurlencode(uString::sql2text($uCat->cat->cat_keywords,true))?>";
        uCat.site_title="<?=rawurlencode(site_name)?>";
        <?/*$hash=$this->uFunc->sesHack();*/?>
        //uCat.sessions_hack_id=<?//=/*$hash['id']*/?>//;
        //uCat.sessions_hack_hash="<?//=/*$hash['hash']*/?>//";
        if(typeof uCat.filter_type_id==="undefined") uCat.filter_type_id=[];
        if(typeof uCat.filter_type_sql==="undefined") uCat.filter_type_sql=[];
        if(typeof uCat.filter_type_title==="undefined") uCat.filter_type_title=[];
        if(typeof uCat.field_type_id==="undefined") uCat.field_type_id=[];
        if(typeof uCat.field_sql_type==="undefined") uCat.field_sql_type=[];

        <?mysqli_data_seek($uCat->q_fields_filter_types,0);
        /** @noinspection PhpUndefinedMethodInspection */for($i=0;$fields_filter_types=$uCat->q_fields_filter_types->fetch_object();$i++) {?>
            uCat.filter_type_id[<?=$i?>]=<?=$fields_filter_types->filter_type_id?>;
            uCat.filter_type_sql[<?=$i?>]="<?=$fields_filter_types->filter_type_sql?>";
            uCat.filter_type_title[<?=$i?>]="<?=$fields_filter_types->filter_type_title?>";
        <?}
        mysqli_data_seek($uCat->q_fields_types,0);
        /** @noinspection PhpUndefinedMethodInspection */for($i=0;$q_fields_types=$uCat->q_fields_types->fetch_object();$i++) {?>
            uCat.field_type_id[<?=$i?>]=<?=$q_fields_types->field_type_id?>;
            uCat.field_sql_type[<?=$i?>]="<?=$q_fields_types->field_sql_type?>";
        <?}?>
    </script>
    <?include 'dialogs/cat_admin.php';?>
    <?}
}

include_once 'uCat/dialogs/uCat_cart.php';

$this->page_content=ob_get_contents();
ob_end_clean();
if(isset($_GET['results_only'])) echo $this->page_content;
else {/** @noinspection PhpIncludeInspection */include "templates/template.php";}
