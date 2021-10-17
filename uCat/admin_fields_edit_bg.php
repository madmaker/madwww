<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class admin_fields_edit_bg{
    private $uFunc;
    private $uSes;
    private $uCore,
        $field_id,$field_title,$field_comment,$field_units,$field_type_id,$field_effect_id,$field_pos,$field_place_id,$filter_type_id,$search_use,$label_style_id,$tablelist_show,$planelist_show,$tileslist_show,$tileslist_show_on_card,$sort_show,$merge,
        $cur_field_sql_type,$field_sql_type;

    private function check_data() {
        if(!isset($_POST['field_id'])) $this->uFunc->error(10);
        if(!isset($_POST['field_title'])) $this->uFunc->error(20);
        if(!isset($_POST['field_comment'])) $this->uFunc->error(30);
        if(!isset($_POST['field_units'])) $this->uFunc->error(40);
        if(!isset($_POST['field_type_id'])) $this->uFunc->error(50);
        if(!isset($_POST['field_effect_id'])) $this->uFunc->error(60);
        if(!isset($_POST['field_pos'])) $this->uFunc->error(70);
        if(!isset($_POST['field_place_id'])) $this->uFunc->error(80);
        if(!isset($_POST['filter_type_id'])) $this->uFunc->error(90);
        if(!isset($_POST['search_use'])) $this->uFunc->error(100);
        if(!isset($_POST['label_style_id'])) $this->uFunc->error(110);
        if(!isset($_POST['tablelist_show'])) $this->uFunc->error(120);
        if(!isset($_POST['planelist_show'])) $this->uFunc->error(130);
        if(!isset($_POST['tileslist_show'])) $this->uFunc->error(140);
        if(!isset($_POST['tileslist_show_on_card'])) $this->uFunc->error(150);
        if(!isset($_POST['sort_show'])) $this->uFunc->error(160);
        if(!isset($_POST['merge'])) $this->uFunc->error(170);


        $this->field_id=(int)$_POST['field_id'];
        $this->field_title=trim($_POST['field_title']);
        $this->field_comment=trim($_POST['field_comment']);
        $this->field_units=trim($_POST['field_units']);
        $this->field_type_id=(int)$_POST['field_type_id'];
        $this->field_effect_id=(int)$_POST['field_effect_id'];
        $this->field_pos=trim($_POST['field_pos']);
        $this->field_place_id=(int)$_POST['field_place_id'];
        $this->filter_type_id=(int)$_POST['filter_type_id'];
        $this->search_use=(int)$_POST['search_use'];
        $this->label_style_id=(int)$_POST['label_style_id'];
        $this->tablelist_show=(int)$_POST['tablelist_show'];
        $this->planelist_show=(int)$_POST['planelist_show'];
        $this->tileslist_show=(int)$_POST['tileslist_show'];
        $this->tileslist_show_on_card=(int)$_POST['tileslist_show_on_card'];
        $this->sort_show=(int)$_POST['sort_show'];
        $this->merge=(int)$_POST['merge'];

        $this->field_title=uString::text2sql($this->field_title);
        if(!strlen($this->field_title)) die("{'status' : 'error', 'msg' : 'title'}");
        $this->field_comment=uString::text2sql($this->field_comment);
        $this->field_units=uString::text2sql($this->field_units);

        if($this->search_use) $this->search_use=1;
        if($this->tablelist_show) $this->tablelist_show=1;
        if($this->planelist_show) $this->planelist_show=1;
        if($this->tileslist_show) $this->tileslist_show=1;
        if($this->tileslist_show_on_card) $this->tileslist_show_on_card=1;
        if($this->sort_show) $this->sort_show=1;
        if($this->merge) $this->merge=1;

        $this->check_field_type_filter_type();
    }
    private function check_field_type_filter_type() {
        //check that filter type suits field type
        if(!$query=$this->uCore->query("uCat","SELECT
        `filter_type_id`
        FROM
        `u235_fields_filter_types`,
        `u235_fields_types`
        WHERE
        `filter_type_id`!='0' AND
        `filter_type_sql`=`field_sql_type` AND
        `field_type_id`='".$this->field_type_id."' AND
        `filter_type_id`='".$this->filter_type_id."'
        ")) $this->uFunc->error(8);
        if(!mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'filter_type'}");
    }

    private function get_cur_field_type() {
        if(!$query=$this->uCore->query('uCat',"SELECT DISTINCT
        `field_sql_type`
        FROM
        `u235_fields`,
        `u235_fields_types`
        WHERE
        `u235_fields`.`field_id`='".$this->field_id."' AND
        `u235_fields`.`field_type_id`=`u235_fields_types`.`field_type_id` AND
        `u235_fields`.`site_id`='".site_id."'
        ")) $this->uFunc->error(9);
        if(!mysqli_num_rows($query)) $this->uFunc->error(10);
        $field=$query->fetch_object();
        $this->cur_field_sql_type=$field->field_sql_type;
    }
    private function get_new_field_type() {
        if(!$query=$this->uCore->query("uCat","SELECT
        `field_sql_type`
        FROM
        `u235_fields_types`
        WHERE
        `field_type_id`='".$this->field_type_id."'
        ")) $this->uFunc->error(11);
        if(!mysqli_num_rows($query)) $this->uFunc->error(12);
        $field=$query->fetch_object();
        $this->field_sql_type=$field->field_sql_type;
    }

    private function update_field() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_fields
            SET
            field_title=:field_title,
            field_comment=:field_comment,
            field_units=:field_units,
            field_type_id=:field_type_id,
            field_effect_id=:field_effect_id,
            field_pos=:field_pos,
            field_place_id=:field_place_id,
            filter_type_id=:filter_type_id,
            search_use=:search_use,
            label_style_id=:label_style_id,
            tablelist_show=:tablelist_show,
            planelist_show=:planelist_show,
            tileslist_show=:tileslist_show,
            tileslist_show_on_card=:tileslist_show_on_card,
            sort_show=:sort_show,
            merge=:merge
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_title', $this->field_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_comment', $this->field_comment,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_units', $this->field_units,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_type_id', $this->field_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_effect_id', $this->field_effect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_pos', $this->field_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_place_id', $this->field_place_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':filter_type_id', $this->filter_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':search_use', $this->search_use,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':label_style_id', $this->label_style_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tablelist_show', $this->tablelist_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':planelist_show', $this->planelist_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tileslist_show', $this->tileslist_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':tileslist_show_on_card', $this->tileslist_show_on_card,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sort_show', $this->sort_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':merge', $this->merge,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}

        if($this->cur_field_sql_type!=$this->field_sql_type) $this->change_field_id();//we must change field's sql type
    }

    private function change_field_id() {
        $new_field_id=$this->change_type_get_new_field_id();
        $this->move_field_data_to_new_field_id($new_field_id);
    }
    private function move_field_data_to_new_field_id($newField_id) {
        //update field_id
        if(!$this->uCore->query("uCat","UPDATE
        `u235_fields`
        SET
        `field_id`='".$newField_id."',
        `field_type_id`='".$this->field_type_id."'
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(24);

        //move old values to new column
        if(!$this->uCore->query("uCat","UPDATE
        `u235_items`
        SET
        `field_".$newField_id."`=`field_".$this->field_id."`
        WHERE
        `site_id`='".site_id."'
        ")) $this->uFunc->error(35);

        //clean old column
        if(!$this->uCore->query("uCat","UPDATE
        `u235_items`
        SET
        `field_".$this->field_id."`=NULL
        WHERE
        `site_id`='".site_id."'
        ")) $this->uFunc->error(46);

        //change field_id in cats_fields
        if(!$this->uCore->query("uCat","UPDATE
        `u235_cats_fields`
        SET
        `field_id`='".$newField_id."'
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(57);
        $this->change_type_delete_unused_column();
    }
    private function change_type_delete_unused_column() {
        //check if old column is used by other sites
        if(!$query=$this->uCore->query("uCat","SELECT
        `field_id`
        FROM
        `u235_fields`
        WHERE
        `field_id`='".$this->field_id."'
        ")) $this->uFunc->error(68);
        if(!mysqli_num_rows($query)) {
            if(!$this->uCore->query("uCat","ALTER TABLE
            `u235_items`
            DROP
            `field_".$this->field_id."`
            ")) $this->uFunc->error(79);
        }
    }
    private function change_type_get_new_field_id() {
        //get list of field_id for current site
        if(!$query=$this->uCore->query("uCat","SELECT DISTINCT
            `field_id`
            FROM
            `u235_fields`
            WHERE
            `site_id`='".site_id."'
            ")) $this->uFunc->error(120);
        while($qr=$query->fetch_object()) $cur_site_field_id[$qr->field_id]=1;

        //get list of field_id for all sites with needed sql_type
        if(!$query=$this->uCore->query("uCat","SELECT DISTINCT
            `field_id`
            FROM
            `u235_fields`,
            `u235_fields_types`
            WHERE
            `u235_fields`.`field_type_id`=`u235_fields_types`.`field_type_id` AND
            `field_sql_type`='".$this->field_sql_type."'
            ")) $this->uFunc->error(221);
        if(!mysqli_num_rows($query)) return $this->change_type_make_new_field_id();

        while($qr=$query->fetch_object()) {
            if(!isset($cur_site_field_id[$qr->field_id])) {
                return $qr->field_id;
            }
        }
        return $this->change_type_make_new_field_id();
    }
    private function change_type_make_new_field_id(){
        //get new field id
        if(!$query=$this->uCore->query('uCat',"SELECT
            `field_id`
            FROM
            `u235_fields`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `field_id` DESC
            LIMIT 1
            ")) $this->uFunc->error(322);
        $id=$query->fetch_object();
        if(mysqli_num_rows($query)>0) $field_id=$id->field_id+1;
        else $field_id=1;

        //create new column in fields
        if(!$this->uCore->query("uCat","ALTER TABLE
            `u235_items`
            ADD
            `field_".$field_id."`
            ".$this->field_sql_type." NULL ")) $this->uFunc->error(423);

        return $field_id;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");
        
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $this->get_cur_field_type();
        $this->get_new_field_type();
        $this->update_field();
    }
}
new admin_fields_edit_bg($this);

echo "{'status' : 'done'}";;