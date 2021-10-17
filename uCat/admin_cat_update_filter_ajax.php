<?php
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'inc/cat_filter.php';
class admin_cat_update_filter_ajax {
    private $uFunc;
    private $uSes;
    private $uCore,
        $q_fields,$cat_id;

    private function check_data() {
        if(!isset($_POST['cat_id'])) $this->uFunc->error(10);
        $this->cat_id=$_POST['cat_id'];
        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(20);
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
            filter_type_val
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
            u235_fields.field_id=u235_cats_fields.field_id AND
            u235_fields.site_id=u235_cats_fields.site_id
            WHERE
            u235_cats_fields.cat_id=:cat_id AND
            u235_fields_filter_types.filter_type_val!='no' AND
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
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');

        $this->uFunc=new \processors\uFunc($this->uCore);

        $this->check_data();
        $this->get_cat_fields();

        $filter=new cat_filter($this->uCore,$this->q_fields,$this->cat_id);
        echo $filter->make_filter();
    }
}
new admin_cat_update_filter_ajax($this);