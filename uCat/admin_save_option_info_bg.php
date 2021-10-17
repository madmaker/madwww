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

class admin_save_option_info_bg {
    private $uCat;
    private $option_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['option_id'])) $this->uFunc->error(10);
        $this->option_id=(int)$_POST['option_id'];

        if(isset($_POST['option_name'])) {
            $this->save_option_name($_POST['option_name']);
        }
        elseif(isset($_POST['option_type'])) {
            $this->save_option_type((int)$_POST['option_type']);
        }
        elseif(isset($_POST['option_display_style'])) {
            $this->save_option_display_style((int)$_POST['option_display_style']);
        }
    }

    private function save_option_name($option_name) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            variant_options 
            SET
            option_name=:option_name 
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_name', $option_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    private function save_option_type($option_type) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            variant_options 
            SET
            option_type=:option_type 
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_type', $option_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function save_option_display_style($option_display_style) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            variant_options 
            SET
            option_display_style=:option_display_style 
            WHERE
            option_id=:option_id AND 
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_display_style', $option_display_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    private function update_variants_titles() {
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
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($item=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->uCat->update_all_item_variants_with_options_title($item->item_id);
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uCat=new common($this->uCore);

        $this->check_data();

        $this->update_variants_titles();
        echo "{
        'status':'done'
        }";
        exit;
    }
}
new admin_save_option_info_bg($this);