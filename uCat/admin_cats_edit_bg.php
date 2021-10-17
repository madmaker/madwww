<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_cats_edit_bg {
    private $uSes;
    private $uCore,$uFunc,
    $cat_id,$field,$value;
    private function check_data() {
        if(!isset($_POST['field'],$_POST['value'],$_POST['cat_id'])) $this->uFunc->error(10);
        $this->cat_id=(int)$_POST['cat_id'];
        $this->field=&$_POST['field'];
        $this->value=trim($_POST['value']);

        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(20);
    }
    private function update_uDrive_folder_name() {
        //get uDrive_folder_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            uDrive_folder_id
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $file_name=trim(uString::sanitize_filename($this->value));
            if(!strlen($file_name)) $file_name='Категория '.$this->cat_id;

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uDrive")->prepare("UPDATE
                u235_files
                SET
                file_name=:file_name
                WHERE
                file_id=:file_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_name', $file_name,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':file_id', $qr->uDrive_folder_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
    }

    private function save_field() {
        if($this->field=='cat_title') {
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'title is empty'}");
            $this->value=uString::text2sql($this->value);
            $this->update_uDrive_folder_name();
        }
        elseif($this->field=='cat_url') {
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'url is empty'}");
            if(!uString::isFilename($this->value)) die("{'status' : 'error', 'msg' : 'url is wrong'}");
            $this->value=uString::text2sql($this->value);
        }
        elseif($this->field=='cat_pos') {
            if(!uString::isInt($this->value)) die("{'status' : 'error', 'msg' : 'is not number'}");
        }
        elseif($this->field=='show_on_hp') {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                show_on_hp=1-show_on_hp
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
            die("{'status' : 'done'}");
        }
        elseif($this->field=='cat_descr') $this->value=uString::text2sql($this->value);
        elseif($this->field=='cat_keywords') $this->value=uString::text2sql($this->value);
        elseif($this->field=='seo_title') {
            $seo_title=$_POST['value'];
            $seo_descr=$_POST['seo_descr'];

            $seo_title=str_replace('"',"",$seo_title);
            $seo_title=str_replace('\'',"",$seo_title);
            $seo_title=uString::text2sql($seo_title);

            $seo_descr=str_replace('"',"",$seo_descr);
            $seo_descr=str_replace('\'',"",$seo_descr);
            $seo_descr=uString::text2sql($seo_descr);

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                seo_title=:seo_title,
                seo_descr=:seo_descr
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':seo_title', $seo_title,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':seo_descr', $seo_descr,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

            echo "{
            'status' : 'done',
            'seo_title' : '".rawurlencode($seo_title)."',
            'seo_descr' : '".rawurlencode($seo_descr)."'
            }";
            exit;
        }
        elseif($this->field=='def_sort_order') {
            $def_sort_order=$_POST['value'];
            if($def_sort_order!='1'&&$def_sort_order!='2') $def_sort_order='0';
            $def_sort_field=$_POST['def_sort_field'];
            if($def_sort_field!='0'&&$def_sort_field!='-1'&&$def_sort_field!='-2'&&$def_sort_field!='-3') {
                //check if field is attached to this cat
                if(uString::isDigits($def_sort_field)) {
                    try {
                        /** @noinspection PhpUndefinedMethodInspection */
                        $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                        cat_id
                        FROM
                        u235_cats_fields
                        WHERE
                        cat_id=:cat_id AND
                        field_id=:field_id AND
                        site_id=:site_id
                        ");
                        $site_id=site_id;
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $def_sort_field,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                        /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                        /** @noinspection PhpUndefinedMethodInspection */
                        if(!$stm->fetch(PDO::FETCH_OBJ)) die('{"status":"forbidden"}');
                    }
                    catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
                }
                else $this->uFunc->error(80);
            }

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                def_sort_order=:def_sort_order,
                def_sort_field=:def_sort_field
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':def_sort_order', $def_sort_order,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':def_sort_field', $def_sort_field,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}

            echo "{
            'status' : 'done'
            }";
            exit;
        }
        elseif($this->field=='yandex_cat_id') {
            $yandex_cat_id=(int)$_POST['value'];
            if($yandex_cat_id) {
                //check if this cat id exists
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                cat_id
                FROM
                yandex_cats
                WHERE
                cat_id=:cat_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $yandex_cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(100);
            }
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                yandex_cat_id=:yandex_cat_id
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':yandex_cat_id', $yandex_cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}

            echo "{
            'status' : 'done'
            }";
            exit;
        }
        else $this->uFunc->error(120);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_cats
            SET
            ".$this->field."=:field_value
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $this->value,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");
        $this->uFunc = new uFunc($this->uCore);

        $this->check_data();
        $this->save_field();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{
        'status' : 'done',
        '".$this->field."':'".rawurlencode(uString::sql2text($this->value,true))."'
        }";
    }
}
new admin_cats_edit_bg($this);