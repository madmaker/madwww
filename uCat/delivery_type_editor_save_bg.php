<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uCat\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/common.php";

class delivery_type_editor_save_bg {
    private $uCat;
    private $handler_id;
    private $handler_name;
    private $db_table;
    private $pdo_type;
    private $value;
    private $field_name;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["handler_type"],$_POST["handler_id"],$_POST["field_name"],$_POST["value"])) $this->uFunc->error(10,1);
        $this->handler_id=(int)$_POST["handler_id"];
        $this->field_name=$_POST["field_name"];

        if($_POST["handler_type"]==="delivery_type") {
            $this->db_table="delivery_types";
            $this->handler_name="del_type_id";

            //del_type_name
            //del_type_descr
            //del_type
            //is_default
            //del_show
            //pos
            if($this->field_name==="del_type_name") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="del_type_descr") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="del_type") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;
            }
            elseif($this->field_name==="is_default") {//
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;

                if($this->value) {//Если пытаемся сделать по умолчанию
                    $del_data = $this->uCat->delivery_type_id2data($this->handler_id, "del_show");//Если вариант скрыт, то нельзя его делать по умолчанию
                    if (!(int)$del_data->del_show) $this->value = 0;
                }
            }
            elseif($this->field_name==="del_show") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;

                if(!$this->value) {//Если пытаемся скрыть
                    $del_data = $this->uCat->delivery_type_id2data($this->handler_id, "is_default");//Если вариант по умолчанию, то нельзя его скрывать
                    if ((int)$del_data->is_default) $this->value = 1;
                }
            }
            elseif($this->field_name==="pos") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
            }
            elseif($this->field_name==="delete") {
                $del_data=$this->uCat->delivery_type_id2data($this->handler_id,"is_default");//Если вариант по умолчанию, то нельзя его удалять
                if((int)$del_data->is_default) $this->uFunc->error(0,1);
            }
            else $this->uFunc->error(20,1);
        }
        elseif($_POST["handler_type"]==="point") {
            $this->db_table="delivery_points";
            $this->handler_name="point_id";

            //point_name
            //point_descr
            //is_default
            //point_show
            //pos
            if($this->field_name==="point_name") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="point_descr") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="is_default") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;

                if($this->value) {//Если пытаемся сделать по умолчанию
                    $point_data = $this->uCat->delivery_point_id2data($this->handler_id, "point_show");//Если вариант скрыт, то нельзя его делать по умолчанию
                    if ((int)$point_data->point_show) $this->value = 0;
                }
            }
            elseif($this->field_name==="point_show") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;

                if(!$this->value) {//Если пытаемся скрыть
                    $point_data = $this->uCat->delivery_point_id2data($this->handler_id, "is_default");//Если вариант по умолчанию, то нельзя его скрывать
                    if ((int)$point_data->is_default) $this->value = 1;
                }
            }
            elseif($this->field_name==="pos") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
            }
            elseif($this->field_name==="delete") {
                $point_data=$this->uCat->delivery_point_id2data($this->handler_id,"is_default");//Если вариант по умолчанию, то нельзя его удалять
                if((int)$point_data->is_default) $this->value=1;
            }
            else $this->uFunc->error(30,1);
        }
        elseif($_POST["handler_type"]==="var") {
            $this->db_table="delivery_point_variants";
            $this->handler_name="var_id";

            if($this->field_name==="var_name") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="var_descr") {
                $this->pdo_type=PDO::PARAM_STR;
            }
            elseif($this->field_name==="var_show") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;
            }
            elseif($this->field_name==="manager_must_confirm") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;
            }
            elseif($this->field_name==="manager_sets_delivery_price") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
                if($this->value) $this->value=1;
            }
            elseif($this->field_name==="pos") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(int)$this->value;
            }
            elseif($this->field_name==="delivery_price") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(float)str_replace(",",".",$this->value);
            }
            elseif($this->field_name==="avail_at_price_since") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(float)str_replace(",",".",$this->value);
            }
            elseif($this->field_name==="avail_at_price_till") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(float)str_replace(",",".",$this->value);
            }
            elseif($this->field_name==="set_at_price_since") {
                $this->pdo_type=PDO::PARAM_INT;
                $this->value=(float)str_replace(",",".",$this->value);
            }
            elseif($this->field_name==="delete") {
                $point_data=$this->uCat->delivery_point_id2data($this->handler_id,"is_default");//Если вариант по умолчанию, то нельзя его удалять
                if((int)$point_data->is_default) $this->value=1;
            }
            else $this->uFunc->error(30,1);
        }
        else $this->uFunc->error(40,1);

        $this->field_name=$_POST["field_name"];
        $this->value=$_POST["value"];

    }

    private function delete_handler($site_id=site_id) {//Можно ли удалять проверяется в check_data
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM 
            ".$this->db_table."
            WHERE 
            ".$this->handler_name."=:".$this->handler_name." AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':'.$this->handler_name, $this->handler_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
    }

    private function set_non_default4other($site_id=site_id) {
        if($this->db_table==="delivery_types") {
            $q_parent_handler_id="";
        }
        elseif($this->db_table==="delivery_points") {
            if(!$point_data=$this->uCat->delivery_point_id2data($this->handler_id,"del_type_id")) $this->uFunc->error(0,1);
            $q_parent_handler_id="del_type_id=".$point_data->del_type_id." AND";
        }
        elseif($this->db_table==="delivery_point_variants") {
            if(!$var_data=$this->uCat->delivery_point_variant_id2data($this->handler_id,"point_id")) $this->uFunc->error(0,1);
            $q_parent_handler_id="point_id=".$var_data->point_id." AND";
        }
        else {
            $this->uFunc->error(0,1);
            exit;
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            ".$this->db_table." 
            SET
            ".$this->field_name."=0
            WHERE 
            ".$q_parent_handler_id."
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/,1);}
    }

    private function update_value($site_id=site_id) {//Можно ли менять значение проверяется в check_data
        if($this->field_name==="is_default") {
            if($this->value) $this->set_non_default4other($site_id);//Нужно остальным способам поставить "Не по умолчанию".
            else $this->uFunc->error(0,1);//Способ не может сам стать "не по умолчанию" - только если какой-то другой стал по умолчанию
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            ".$this->db_table."
            SET
            ".$this->field_name."=:".$this->field_name."
            WHERE 
            ".$this->handler_name."=:".$this->handler_name." AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':'.$this->field_name, $this->value,$this->pdo_type);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':'.$this->handler_name, $this->handler_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die('forbidden');
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();
        if($this->field_name==="delete") $this->delete_handler();
        else $this->update_value();
    }
}
new delivery_type_editor_save_bg($this);