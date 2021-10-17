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

class create_delivery_type_bg {
    private $uCat;
    private $parent_handler_id;
    private $handler_type;
    private $uFunc;
    private $uSes;
    private $uCore;

    private function check_data() {
        if(!isset($_POST["handler_type"],$_POST["parent_handler_id"])) $this->uFunc->error(10,1);
        $this->handler_type=$_POST["handler_type"];
        $this->parent_handler_id=(int)$_POST["parent_handler_id"];

        if($this->handler_type==="point") {
            if(!$this->uCat->delivery_type_id2data($this->parent_handler_id)) $this->uFunc->error(20,1);
        }
    }

    private function create_new_delivery_type($site_id=site_id) {
        $site_has_delivery_types=$this->uCat->site_has_delivery_types($site_id);
        if($site_has_delivery_types) $is_default=0;
        else $is_default=1;

        $del_type_id=$this->uCat->get_new_delivery_type_id();
        $del_type_name="Новый способ доставки";

        echo $del_type_id;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            delivery_types (
            del_type_id, 
            del_type_name, 
            site_id,
            is_default,
            del_show,
            pos
            ) VALUES (
            :del_type_id, 
            :del_type_name, 
            :site_id,
            :is_default,
            :is_default,
            :del_type_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_default', $is_default,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':del_type_id', $del_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':del_type_name', $del_type_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/,1);}

        /*$point_id=*/$this->create_new_delivery_point($del_type_id,1,1,$site_id);

        return $del_type_id;
    }
    private function create_new_delivery_point($del_type_id,$is_default=0,$point_show=0,$site_id=site_id) {
        $point_id=$this->uCat->get_new_delivery_point_id();
        $point_name="Новый адрес выдачи/район доставки";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            delivery_points (
            point_id, 
            point_name, 
            del_type_id, 
            is_default,
            point_show,
            site_id,
            pos
            ) VALUES (
            :point_id, 
            :point_name, 
            :del_type_id,
            :is_default,
            :point_show,          
            :site_id,
            :point_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':point_id', $point_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':point_name', $point_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':del_type_id', $del_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_default', $is_default,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':point_show', $point_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/,1);}

        /*$var_id=*/$this->create_new_delivery_point_variant($point_id,1,$site_id);

        return $point_id;
    }
    private function create_new_delivery_point_variant($point_id,$var_show=0,$site_id=site_id) {
        $var_id=$this->uCat->get_new_delivery_point_variant_id();
        $var_name="Новый вариант получения";
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO delivery_point_variants (
            var_id, 
            var_name, 
            var_show,
            point_id,
            site_id,
            pos
            ) VALUES (
            :var_id, 
            :var_name, 
            :var_show, 
            :point_id, 
            :site_id,
            :var_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_show', $var_show,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_name', $var_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':point_id', $point_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}

        return $var_id;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();

        if($this->handler_type==="delivery_type") $this->create_new_delivery_type();
        elseif($this->handler_type==="point") $this->create_new_delivery_point($this->parent_handler_id,0,0);
        elseif($this->handler_type==="var") $this->create_new_delivery_point_variant($this->parent_handler_id,0);
        else $this->uFunc->error(120,1);
    }
}
new create_delivery_type_bg($this);