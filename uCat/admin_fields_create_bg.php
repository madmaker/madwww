<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";

class admin_fields_create_bg{
    private $uCat;
    private $uFunc;
    private $uSes;
    private $uCore,
    $field_title,$field_comment,$field_units,$field_type_id,$field_pos,$field_place_id,$filter_type_id,$field_effect_id,$search_use,$label_style_id,$tablelist_show,$planelist_show,$tileslist_show,$tileslist_show_on_card,$sort_show,$merge,$item_id,
        $by_cat,$cat_id;

    private function checkData() {
        if(!isset(
        $_POST['field_title'],
        $_POST['field_comment'],
        $_POST['field_units'],
        $_POST['field_type_id'],
        $_POST['field_pos'],
        $_POST['field_place_id'],
        $_POST['filter_type_id'],
        $_POST['field_effect_id'],
        $_POST['search_use'],
        $_POST['label_style_id'],
        $_POST['tablelist_show'],
        $_POST['planelist_show'],
        $_POST['tileslist_show'],
        $_POST['tileslist_show_on_card'],
        $_POST['sort_show'],
        $_POST['merge']
        )) $this->uFunc->error(10);
        if(!isset($_POST['item_id'])&&!isset($_POST['cat_id'])) $this->uFunc->error(20);
        if(isset($_POST['cat_id'])) $this->by_cat=true;
        else $this->by_cat=false;

        $this->field_pos=trim($_POST['field_pos']);
        if(!uString::isDigits($this->field_pos)) die("{'status' : 'error', 'msg' : 'field_pos'}");

        $this->field_title=trim($_POST['field_title']);
        if(empty($this->field_title)) die("{'status' : 'error', 'msg' : 'field_title'}");

        $this->field_comment=trim($_POST['field_comment']);
        $this->field_units=trim($_POST['field_units']);
        $this->field_type_id=(int)$_POST['field_type_id'];
        $this->field_place_id=(int)$_POST['field_place_id'];
        $this->filter_type_id=(int)$_POST['filter_type_id'];
        $this->field_effect_id=(int)$_POST['field_effect_id'];
        $this->search_use=(int)$_POST['search_use'];
        $this->label_style_id=(int)$_POST['label_style_id'];
        $this->tablelist_show=(int)$_POST['tablelist_show'];
        $this->planelist_show=(int)$_POST['planelist_show'];
        $this->tileslist_show=(int)$_POST['tileslist_show'];
        $this->tileslist_show_on_card=(int)$_POST['tileslist_show_on_card'];
        $this->sort_show=(int)$_POST['sort_show'];
        $this->merge=(int)$_POST['merge'];

        if($this->search_use) $this->search_use=1;
        if($this->tablelist_show) $this->tablelist_show=1;
        if($this->planelist_show) $this->planelist_show=1;
        if($this->tileslist_show) $this->tileslist_show=1;
        if($this->tileslist_show_on_card) $this->tileslist_show_on_card=1;
        if($this->sort_show) $this->sort_show=1;
        if($this->merge) $this->merge=1;

        if($this->by_cat) $this->cat_id=(int)$_POST['cat_id'];
        else $this->item_id=(int)$_POST['item_id'];

        $this->check_field_type_filter_type();
    }
    private function check_field_type_filter_type() {
        //check that filter type suits field type
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            filter_type_id
            FROM
            u235_fields_filter_types
            JOIN 
            u235_fields_types
            ON 
            filter_type_sql=field_sql_type
            WHERE
            filter_type_id!=0 AND
            field_type_id=:field_type_id AND
            filter_type_id=:filter_type_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_type_id', $this->field_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':filter_type_id', $this->filter_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) die("{'status' : 'error', 'msg' : 'filter_type'}");
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    private function attach_field2cat($field_id) {
        if($this->by_cat) $this->uCat->attach_field2cat($field_id,$this->cat_id);
        else $this->uCat->attach_field2item($field_id,$this->item_id);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);
        
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        if(isset($_POST['check'])) {
            $fields_html=$this->uCat->check_if_field_title_exists($this->field_title);
            if($fields_html!="") {
                echo '{
                "status":"found",
                "msg":"'.rawurlencode('<p>Найдены похожие характеристики:<ul class="list-unstyled">'.$fields_html.'</ul><b>Не создавайте одинаковые характеристики</b>, если в этом нет острой необходимости.').'"
                }';
            }
            else echo '{"status":"not found"}';
        }
        else {
            $this->checkData();
            $field_data_ar=array();
            $field_data_ar['field_title']=$this->field_title;
            $field_data_ar['field_comment']=$this->field_comment;
            $field_data_ar['field_units']=$this->field_units;
            $field_data_ar['field_pos']=$this->field_pos;
            $field_data_ar['field_place_id']=$this->field_place_id;
            $field_data_ar['filter_type_id']=$this->filter_type_id;
            $field_data_ar['field_effect_id']=$this->field_effect_id;
            $field_data_ar['search_use']=$this->search_use;
            $field_data_ar['label_style_id']=$this->label_style_id;
            $field_data_ar['tablelist_show']=$this->tablelist_show;
            $field_data_ar['planelist_show']=$this->planelist_show;
            $field_data_ar['tileslist_show']=$this->tileslist_show;
            $field_data_ar['tileslist_show_on_card']=$this->tileslist_show_on_card;
            $field_data_ar['sort_show']=$this->sort_show;
            $field_data_ar['merge']=$this->merge;
            $field_id=$this->uCat->create_new_field($this->field_type_id,$field_data_ar);
            $this->attach_field2cat($field_id);
            echo "{'status' : 'done'}";
        }
    }
}
new admin_fields_create_bg($this);