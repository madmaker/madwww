<?php
namespace uConf\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class install_mod {
    public $uFunc;
    public $uSes;
    private $uCore,$mod_id,$mod_name;
    public $site_id;
    private function check_data(){
        if(!isset($_POST['site_id'],$_POST['mod_id'])) $this->uFunc->error(10);
        $this->site_id=$_POST['site_id'];
        $this->mod_id=$_POST['mod_id'];

        if(!uString::isDigits($this->site_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->mod_id)) $this->uFunc->error(30);

        //check if this mod is not installed
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT
            mod_id
            FROM
            u235_sites_modules
            WHERE
            site_id=:site_id AND
            mod_id=:mod_id AND
            installed=1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_id', $this->mod_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) die("{'status' : 'error', 'msg' : 'already_installed'}");
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    private function install_pages_conf() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            field_id,
            `mod`,
            field,
            descr,
            value,
            field_type,
            min_length,
            max_length,
            tab_id,
            pos
            FROM
            u235_conf
            WHERE
            `mod`=:mod_name AND
            site_id=0
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_name', $this->mod_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($data=$stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("pages")->prepare("DELETE FROM
                u235_conf
                WHERE
                field_id=:field_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field_id', $data->field_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("pages")->prepare("INSERT INTO
                u235_conf (
                field_id,
                `mod`,
                field,
                descr,
                value,
                field_type,
                min_length,
                max_length,
                tab_id,
                pos,
                site_id
                ) VALUES (
                :field_id,
                :mod_name,
                :field,
                :descr,
                :value,
                :field_type,
                :min_length,
                :max_length,
                :tab_id,
                :pos,
                :site_id
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':pos', $data->pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':tab_id', $data->tab_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':max_length', $data->max_length,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':min_length', $data->min_length,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field_type', $data->field_type,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':value', $data->value,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':descr', $data->descr,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field', $data->field,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':mod_name', $data->mod,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field_id', $data->field_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
    }

    private function install_mod() {
        //get mod_name and title
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT
            mod_name
            FROM
            u235_sites_modules
            WHERE
            `site_id`='0' AND
            `mod_id`='".$this->mod_id."'
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->mod_name=$qr->mod_name;
            else $this->uFunc->error(80);
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

        $this->install_pages_conf();

        //mark mod as installed
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("DELETE FROM
            u235_sites_modules
            WHERE
            site_id=:site_id AND
            mod_id=:mod_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_id', $this->mod_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

        try {
            $stm=$this->uFunc->pdo("common")->prepare('INSERT INTO
            u235_sites_modules (
            mod_id,
            mod_name,
            installed,
            site_id
            ) VALUES (
            :mod_id,
            :mod_name,
            1,
            :site_id
            )
            ');
            $stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            $stm->bindParam(':mod_id', $this->mod_id,PDO::PARAM_INT);
            $stm->bindParam(':mod_name', $this->mod_name,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1587254914'/*.$e->getMessage()*/);}

        if($this->mod_name=="uCat") $this->uCat_postinstall_tasks();
    }

    private function uCat_postinstall_tasks() {
        $tables_to_clean=array(
            "u235_cats_fields",
            "u235_cats_files",
            "u235_cats_items",
            "u235_sects_cats",
            "u235_sects_files",
            "u235_items_avail_values",
            "u235_items_pictures",
            "items_download_links",
            "items_variants_types",
            "u235_articles_files",
            "u235_articles_items",
            "items_types",
            "items_variants",
            "sects_sects",
            "u235_items_files",
            "contractors_delivery_info",
            "u235_buy_form_orders",
            "order_nologin_user_data",
            "evotor_documents",
            "orders_items",
            "contractors_data",
            "receipts_items",
            "u235_articles",
            "receipts",
            "acquiring",
            "contractors",
            "orders",
            "u235_fields",
            "u235_items",
            "u235_cats",
            "u235_sects",
            "units");

        //Чистим все таблицы каталога на всякий случай
        for($i=0;$i<count($tables_to_clean);$i++) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
                ".$tables_to_clean[$i]." 
                WHERE 
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
        }

        //Создаем раздел "Без раздела"
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_sects (
            sect_id,
            sect_uuid,
            sect_title, 
            timestamp, 
            site_id
            )
            VALUES (
            0,
            :sect_uuid,
            'Без раздела',
            ".time().",
            :site_id
            )
            ");
            $sect_uuid=$this->uFunc->generate_uuid();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_uuid', $sect_uuid,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}

        //Создаем категорию "Без категории"
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_cats(
            cat_id, 
            cat_uuid, 
            cat_title, 
            sect_count, 
            primary_sect_id, 
            timestamp, 
            site_id
            )
            VALUES (
            0,
            :cat_uuid,
            'Без категории',
            1,
            0,
            ".time().",
            :site_id
            )
            ");
            $cat_uuid=$this->uFunc->generate_uuid();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_uuid', $cat_uuid,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('160'/*.$e->getMessage()*/);}

        //Прикрепляем категорию к разделу
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_sects_cats(
            sect_id, 
            cat_id, 
            site_id
            )
            VALUES (
            0,
            0,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}

        //Копируем на units - еденицы измерения
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            unit_name,
            `default`
            FROM 
            units 
            WHERE 
            site_id=0
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm1=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
                units (
                unit_name,
                `default`, 
                site_id
                ) VALUES (
                :unit_name, 
                :default, 
                :site_id
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':unit_name', $qr->unit_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':default', $qr->default,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('190'.$e->getMessage());}
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uSes->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->install_mod();

        echo "{'status' : 'done',
        'site_id' : '".$this->site_id."'
        }";;
    }
}
new install_mod($this);
