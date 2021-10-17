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

class admin_option_value_save_bg {
    private $uCat;
    private $value_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['value_id'])) $this->uFunc->error(10);
        $this->value_id=(int)$_POST['value_id'];

        if(isset($_POST['value'])) {
            $value=$_POST['value'];

            $this->save_option_value($value);
            $this->update_variants_titles($value);
        }
        elseif(isset($_POST["color"])) {
            $color=$_POST["color"];
            $this->save_option_color($color);
        }
    }

    private function save_option_value($value) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            option_values
            SET
            value=:value 
            WHERE 
            value_id=:value_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value', $value,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $this->value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }

    private function save_option_color($color){
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE
            option_values
            SET
            color=:color
            WHERE 
            value_id=:value_id AND
            site_id=:site_id
            ");
            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':color', $color, PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $this->value_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    private function update_variants_titles($value) {
        //get option_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_id 
            FROM 
            option_values 
            WHERE 
            value_id=:value_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;
        $option_id=$qr->option_id;

        //get items with this option
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            item_id 
            FROM 
            items_options 
            WHERE 
            option_id=:option_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($item=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->uCat->update_all_item_variants_with_options_title($item->item_id);
        }

        return 1;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();

        echo "{
        'status':'done',
        'value_id':'".$this->value_id."'
        }";
        exit;
    }
}
new admin_option_value_save_bg($this);