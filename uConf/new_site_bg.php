<?php
namespace uConf\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once "api/classes/api_functions.php";

class new_site {
    private  $uCore,$site_domain,$site_id,$mods2install_ar,$cat_uuid,$apiEvotor;

    private function check_data() {
        if(!isset($_POST['site_domain'])) $this->uFunc->error(10);
        $this->site_domain=$_POST['site_domain'];
        if(!uString::isDomain_name($this->site_domain)) die("{'status' : 'error', 'msg' : 'domain'}");


        //check if this domain is already registered
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT
            site_id
            FROM
            u235_sites
            WHERE
            site_name=:site_name
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_name', $this->site_domain,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) die("{'status' : 'error', 'msg' : 'exists'}");
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }

    private function add_site() {
        //get new site_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT
            site_id
            FROM
            u235_sites
            ORDER BY
            site_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $res=$stm->fetch(PDO::FETCH_OBJ);

            if(!$res) $this->site_id=1;
            else $this->site_id=$res->site_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        //insert new site_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("INSERT INTO
            u235_sites (
            site_id,
            site_name,
            status,
            main,
            `ssl`
            ) VALUES (
            :site_id,
            :site_name,
            'active',
            1,
            0
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_name', $this->site_domain,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        $this->uFunc->setConf($this->apiEvotor->generate_uuid(), "site_uuid", "uCat", PDO::PARAM_STR, $this->site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_cats (
            cat_id, 
            cat_uuid, 
            cat_title, 
            seo_title, 
            cat_descr, 
            seo_descr, 
            cat_keywords, 
            cat_avatar_time, 
            cat_pos, 
            cat_url, 
            item_count, 
            sect_count, 
            field_count, 
            primary_sect_id, 
            show_on_hp, 
            timestamp, 
            def_sort_field, 
            def_sort_order, 
            uDrive_folder_id, 
            site_id
            ) VALUES (
            0, 
            :cat_uuid, 
            'Без категории', 
            null, 
            '', 
            null, 
            '', 
            0, 
            0, 
            '', 
            0, 
            0, 
            0, 
            0, 
            0, 
            0, 
            0, 
            0, 
            0, 
            :site_id)
            ");
            $stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_uuid', $this->cat_uuid,PDO::PARAM_STR);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('45'/*.$e->getMessage()*/);}
    }

    private function mod_name2ar($mod_name) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("SELECT
            mod_id
            FROM
            u235_sites_modules
            WHERE
            site_id=0 AND
            mod_name=:mod_name
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_name', $mod_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$res=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("50".$mod_name);
            return $res;
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    private function install_core_modules() {
        //clear u235_sites_modules for this site_id. Just in case
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("DELETE FROM 
                u235_sites_modules
                WHERE 
                site_id=:site_id
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('65'/*.$e->getMessage()*/);}

        for($i=0;$i<count($this->mods2install_ar);$i++) {
            //add mod to db
            $mod=$this->mod_name2ar($this->mods2install_ar[$i]);
            //Insert modules
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("common")->prepare("INSERT INTO
                u235_sites_modules (
                mod_id,
                mod_name,
                installed,
                site_id
                ) VALUES (
                :mod_id,
                :mod_name,
                '1',
                :site_id
                )
                ");
                $mod_name=$this->mods2install_ar[$i];
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_id', $mod->mod_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_name', $mod_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
        }
    }

    private function set_conf() {
        //clear conf for this site_id. Just in case
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("DELETE FROM 
                u235_conf
                WHERE 
                site_id=:site_id
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('75'/*.$e->getMessage()*/);}
        //Get default conf
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
            (
            `mod`='common' OR
            `mod`='content' OR
            `mod`='mainpage' OR
            `mod`='uAuth' OR
            `mod`='uBlocks' OR
            `mod`='uConf' OR
            `mod`='uCore' OR
            `mod`='uEditor' OR
            `mod`='uForms' OR
            `mod`='uNavi' OR
            `mod`='uPage' OR
            `mod`='uRubrics' OR
            `mod`='uSlider'
            ) AND
            site_id='0'
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            //insert default fields
            while($conf=$stm->fetch(PDO::FETCH_OBJ)) {
                //insert new fields for this site
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
                    :mod,
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
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field_id', $conf->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':mod', $conf->mod,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field', $conf->field,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':descr', $conf->descr,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':value', $conf->value,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':field_type', $conf->field_type,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':min_length', $conf->min_length,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':max_length', $conf->max_length,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':tab_id', $conf->tab_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':pos', $conf->pos,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm1->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
            }
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
    }

    private function sitemap() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("INSERT INTO
            sitemap (
            site_id,
            update_sitemap
            ) VALUES (
            :site_id,
            :update_sitemap
            )
            ");

            $update_sitemap = 1;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':update_sitemap', $update_sitemap,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->apiEvotor=new \apiEvotor($this->uCore);

        $this->cat_uuid = $this->apiEvotor->generate_uuid();

        if(!$this->uSes->access(17)) die("{'status' : 'forbidden'}");

        $this->mods2install_ar=array(
            'common',
            'content',
            'mainpage',
            'uAuth',
            'uConf',
            'uCore',
            'uEditor',
            'uForms',
            'uRubrics'
        );

        $this->check_data();
        $this->add_site();

        $this->install_core_modules();
        $this->sitemap();

        $this->set_conf();

        echo "{'status' : 'done',
        'site_id' : '".$this->site_id."',
        'site_name' : '".$this->site_domain."'
        }";
    }
}
new new_site ($this);