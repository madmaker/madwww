<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class admin_items_fields_save_bg{
    private $system_field;
    private $uFunc;
    private $uSes;
    private $uCore,
        $item_id,$field_id,$value,
        $field_style,$field_sql_type,$field_title;

    private function checkData() {
        if(!isset($_POST['item_id'],$_POST['field_id'],$_POST['value'])) $this->uFunc->error(10);

        $this->item_id=(int)$_POST['item_id'];
        if($_POST['field_id']==="manufactured_in"||
            $_POST['field_id']==="manufacturer"||
            $_POST['field_id']==="delivery_time"||
            $_POST['field_id']==="delivery_cost"||
            $_POST['field_id']==="manufacturer_warranty"||
            $_POST['field_id']==="buy_without_order_on"||
            $_POST['field_id']==="pickup_on"||
            $_POST['field_id']==="delivery_on"||
            $_POST['field_id']==="yandex_description"||
            $_POST['field_id']==="upload_to_yandex_market"||
            $_POST['field_id']==="manufacturer_part_number"||
            $_POST['field_id']==="search_part_number"
        ) {
            $this->system_field=1;
            $this->field_id=$_POST["field_id"];
        }
        else {
            $this->system_field=0;
            $this->field_id=(int)$_POST["field_id"];
        }

        $this->value=/*trim(*/$_POST['value']/*)*/;
    }
    private function get_field_info(){
        //get item's fields
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            field_style,
            field_title,
            field_sql_type
            FROM
            u235_fields_types
            JOIN 
            u235_fields
            ON 
            u235_fields.field_type_id=u235_fields_types.field_type_id
            WHERE
            u235_fields.field_id=:field_id AND
            u235_fields.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(1581514073);
            $this->field_style=$qr->field_style;
            $this->field_sql_type=$qr->field_sql_type;
            $this->field_title=uString::sql2text($qr->field_title,true);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function update_fields(){
                if($this->field_sql_type=='INT'&&$this->field_style=='text line') {
                    if(strlen($this->value)) if(!uString::isDigits($this->value)) die("{'status' : 'error','msg':'integer'}");
                }
                elseif($this->field_sql_type=='DOUBLE'&&$this->field_style=='text line') {
                    if(strlen($this->value)) {
                        $this->value=str_replace(',','.',$this->value);
                        if(!uString::isFloat($this->value)) die("{'status' : 'error','msg':'double'}");
                    }
                }
                elseif($this->field_sql_type=='TINYTEXT'&&$this->field_style=='text line'||
                    $this->field_sql_type=='TEXT'&&$this->field_style=='html text'||
                    $this->field_sql_type=='TEXT'&&$this->field_style=='multiline') $this->value=uString::text2sql($this->value);
                elseif($this->field_sql_type=='INT'&&$this->field_style=='date'){
                    if(!uString::isDate($this->value)) die("{'status' : 'error','msg':'date'}");
                    $dateAr=explode('.',$this->value);
                    $this->value=mktime(0,0,0,$dateAr[1],$dateAr[0],$dateAr[2])/*+$_POST['user_timezoneOffset']*60*/;
                }
                elseif($this->field_sql_type=='INT'&&$this->field_style=='datetime'){
                    if(!strpos($this->value,'_')) die("{'status' : 'error','msg':'datetime'}");
                    $datetimeAr=explode('_',$this->value);
                    $date=$datetimeAr[0];
                    $time=$datetimeAr[1];
                    if(!uString::isDate($date)) die("{'status' : 'error','msg':'date'}");
                    $dateAr=explode('.',$date);

                    if(!uString::isTime($time)) die("{'status' : 'error','msg':'time'}");
                    $timeAr=explode(':',$time);

                    $this->value=mktime($timeAr[0],$timeAr[1],0,$dateAr[1],$dateAr[0],$dateAr[2]);
                }
                elseif($this->field_sql_type=='TEXT'&&$this->field_style=='link') {
                    if(!isset($_POST['target'],$_POST['label'])) $this->uFunc->error(40);
                    $label=trim($_POST['label']);
                    $target=$_POST['target'];
                    if($target!='_blank') $target='_self';
                    $this->value=uString::text2sql('<a href="'.$this->value.'" target="'.$target.'">'.$label.'</a>');
                    if(empty($href)&&empty($label)) $this->value='';
                }
                else $this->uFunc->error(50);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            field_".$this->field_id."=:field_value
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $this->value,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    private function update_system_field(){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            ".$this->field_id."=:field_value
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $this->value,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->checkData();
        if(!$this->system_field) {
            $this->get_field_info();
            $this->update_fields();
        }
        else $this->update_system_field();

        echo "{
        'status' : 'done',
        'field_title':'".rawurlencode(addslashes($this->field_title))."'
        }";
    }
}
new admin_items_fields_save_bg($this);
