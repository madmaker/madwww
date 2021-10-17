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

class attach_option2item_bg {
    private $uCat;
    private $uSes;
    private $uFunc;
    private $item_id;
    private $option_id;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['option_id'],$_POST['item_id'],$_POST['action'])) $this->uFunc->error(10);
        $this->option_id=(int)$_POST['option_id'];
        $this->item_id=(int)$_POST['item_id'];
    }
    private function attach() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("REPLACE INTO 
            items_options (
            item_id, 
            option_id, 
            site_id
            ) VALUES (
            :item_id, 
            :option_id, 
            :site_id
            ) 
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    private function unattach() {
        //delete options_values
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE 
            variants_options_values
            FROM
            variants_options_values
            JOIN
            items_variants
            ON
            variants_options_values.var_id=items_variants.var_id AND
            variants_options_values.site_id=items_variants.site_id
            WHERE
            items_variants.item_id=:item_id AND
            variants_options_values.option_id=:option_id AND
            variants_options_values.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('25'/*.$e->getMessage()*/);}

        //delete item's options
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM 
            items_options 
            WHERE
            item_id=:item_id AND 
            option_id=:option_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uCat=new common($this->uCore);

        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        if($_POST['action']=="attach") {
            $this->attach();
            $this->uCat->set_default_value_for_option_for_all_item_variants($this->option_id,$this->item_id);
        }
        else $this->unattach();

//        $this->uCat->set_default_value_for_option_for_all_item_variants($this->option_id,$this->item_id);
        $this->uCat->update_all_item_variants_with_options_title($this->item_id);
        echo "{
        'status':'done'
        }";
        exit;
    }
}
new attach_option2item_bg($this);