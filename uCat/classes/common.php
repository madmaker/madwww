<?php
namespace uCat;

require_once 'uCat/inc/item_avatar.php';
require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';
require_once 'uPage/inc/common.php';

use Imagick;
use ImagickException;
use PDO;
use PDOException;
use processors\uFunc;
use uCat_art_avatar;
use uCat_item_avatar;
use uCat_sect_avatar;
use uDrive\file_update;
use uPage\admin\uCat_latest;
use uPage\admin\uCat_latest_articles_slider;
use uPage\admin\uCat_new_items;
use uPage\admin\uCat_sale;
use uPage\admin\uCat_search;
use uSes;
use uString;

class common {
    public $uFunc;
    private $uCore;

    public function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
    }

    //PRIVATE
    //SECTS
    private function get_sect_cat_count($sect_id, $site_id=site_id) {
        $item_count = 0;
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT
            COUNT(DISTINCT u235_cats.cat_id)
            FROM
            u235_sects_cats,
            u235_cats
            WHERE
            u235_sects_cats.sect_id=:sect_id AND
            u235_sects_cats.cat_id=u235_cats.cat_id AND
            u235_cats.item_count > :item_count AND
            u235_sects_cats.site_id=:site_id AND
            u235_cats.site_id=:site_id
            ');


            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);

            $stm->bindParam(':item_count', $item_count, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            if($qr = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $qr['COUNT(DISTINCT u235_cats.cat_id)'];
            }

            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/10'/*.$e->getMessage()*/);}
        return false;
    }
    private function attach_sects_cats($sect_id, $cat_id, $site_id=site_id) {
        if(!(int)$sect_id&&!(int)$cat_id) {
            return 0;
        }
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('REPLACE INTO 
            u235_sects_cats (
            cat_id,
            sect_id,
            site_id
            ) VALUES (
            :cat_id,
            :sect_id,
            :site_id
            )');


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/20'/*.$e->getMessage()*/);}

        return 1;
    }
    private function attach_sects_sects($child_sect_id, $parent_sect_id, $site_id=site_id) {
        if(!(int)$child_sect_id&&!(int)$parent_sect_id) {
            return 0;
        }
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('REPLACE INTO 
            sects_sects (
            parent_sect_id,
            child_sect_id,
            site_id
            ) VALUES (
            :parent_sect_id,
            :child_sect_id,
            :site_id
            )');


            $stm->bindParam(':parent_sect_id', $parent_sect_id, PDO::PARAM_INT);

            $stm->bindParam(':child_sect_id', $child_sect_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/30'/*.$e->getMessage()*/);}

        return 1;
    }
    private function detach_sects_sects($child_sect_id, $parent_sect_id, $site_id=site_id) {
        if(!(int)$child_sect_id&&!(int)$parent_sect_id) {
            return 0;
        }
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM 
            sects_sects 
            WHERE parent_sect_id=:parent_sect_id AND
            child_sect_id=:child_sect_id AND
            site_id=:site_id
            ');

            $stm->bindParam(':parent_sect_id', $parent_sect_id, PDO::PARAM_INT);
            $stm->bindParam(':child_sect_id', $child_sect_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/40'/*.$e->getMessage()*/);}

        return 1;
    }
    private function get_child_attached_sect_sects($sect_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            child_sect_id
            FROM
            sects_sects
            WHERE
            site_id=:site_id AND 
            parent_sect_id=:sect_id
            ');


            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            return $stm->fetchAll(PDO::FETCH_COLUMN);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/50'/*.$e->getMessage()*/);}
        return 0;
    }
    private function detach_sects_cats($sect_id, $cat_id, $site_id=site_id) {
        if(!(int)$sect_id&&!(int)$cat_id) {
            return 0;
        }
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM 
            u235_sects_cats
            WHERE 
            cat_id=:cat_id AND 
            sect_id=:sect_id AND 
            site_id=:site_id
            ');


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/60'/*.$e->getMessage()*/);}

        return 1;
    }
    private function get_new_sect_id($site_id=site_id) {
        try {

            $query = $this->uFunc->pdo('uCat')->prepare('SELECT
            sect_id
            FROM
            u235_sects
            WHERE
            site_id=:site_id
            ORDER BY
            sect_id DESC
            LIMIT 1
            ');


            $query->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $query->execute();


            if($new_sect_id = $query->fetch(PDO::FETCH_ASSOC)) {
                return (int)$new_sect_id['sect_id'] + 1;
            }

            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/70'/*.$e->getMessage()*/);}

        return 1;
    }
    private function get_children_sects_item_count($sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
        SUM(u235_sects.item_count) as item_count
        FROM 
        sects_sects
        JOIN
        u235_sects
        ON 
        sects_sects.child_sect_id=u235_sects.sect_id AND
        sects_sects.site_id=u235_sects.site_id
        WHERE 
        sects_sects.parent_sect_id=:sect_id AND
        sects_sects.site_id=:site_id
        ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->item_count;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/80'/*.$e->getMessage()*/);}

        return 0;
    }
    private function get_children_cats_item_count($sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
        SUM(u235_cats.item_count) as item_count
        FROM 
        u235_sects_cats
        JOIN
        u235_cats
        ON 
        u235_sects_cats.cat_id=u235_cats.cat_id AND
        u235_sects_cats.site_id=u235_cats.site_id
        WHERE 
        u235_sects_cats.sect_id=:sect_id AND
        u235_sects_cats.site_id=:site_id
        ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->item_count;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/90'/*.$e->getMessage()*/);}

        return 0;
    }
    private function update_sect_item_count($sect_id,$site_id=site_id) {
        //get child sect's item_count
        $children_sects_item_count=$this->get_children_sects_item_count($sect_id,$site_id);
        //get child cat's item_count
        $children_cats_item_count=$this->get_children_cats_item_count($sect_id,$site_id);

        $total_item_count=$children_cats_item_count+$children_sects_item_count;
        //update sect's item_count
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('UPDATE 
        u235_sects
        SET
        item_count=:item_count 
        WHERE 
        sect_id=:sect_id AND
        site_id=:site_id
        ');
            $stm->bindParam(':item_count', $total_item_count,PDO::PARAM_INT);
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/100'/*.$e->getMessage()*/);}
    }
    private function calculate_sect_item_count($sect_id,$site_id=site_id) {
        //update sect's item_count
        $this->update_sect_item_count($sect_id,$site_id);
        //get parent sects
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            parent_sect_id 
            FROM 
            sects_sects
            WHERE 
            child_sect_id=:child_sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':child_sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            while($parent_sect_obj=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->calculate_sect_item_count($parent_sect_obj->parent_sect_id,$site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/110'/*.$e->getMessage()*/);}
    }
    private function calculate_sect_cat_count($sect_id, $site_id=site_id) {
            $cat_count=$this->get_sect_cat_count($sect_id);

            try {

                $stm=$this->uFunc->pdo('uCat')->prepare('UPDATE 
                u235_sects
                SET
                cat_count=:cat_count
                WHERE 
                sect_id=:sect_id AND 
                site_id=:site_id
                ');

                $stm->bindParam(':cat_count', $cat_count, PDO::PARAM_INT);
                $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/120'/*.$e->getMessage()*/);}

            //get parent sects
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            parent_sect_id
            FROM 
            sects_sects 
            WHERE 
            child_sect_id=:sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            while($sect_obj=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->calculate_sect_cat_count($sect_obj->parent_sect_id,$site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/130'/*.$e->getMessage()*/);}
    }
    private $loop_flag;
    private function find_child_sects_for_loops_check($parent_sect_id,$child_sect_id,$site_id=site_id) {
        $chain = array();
        $obj_attached_sects_child = $this->get_child_attached_sect_sects($child_sect_id);

        if($obj_attached_sects_child) {
            foreach ($obj_attached_sects_child as $key => $value) {
                if($value !== $parent_sect_id) {
                    $chain[$value] = 1;
                    $chain_two = $this->find_child_sects_for_loops_check($parent_sect_id,$value,$site_id);
                    if ($chain_two) {
                        $chain = array_merge($chain, $chain_two);
                    }
                }
                else {
                    $this->loop_flag = true;
                    break;
                }

                if($this->loop_flag) {
                    return false;
                }
            }
        }

        if (!$chain) {
            return false;
        }

        return $chain;
    }
    private function search_sects_for_loops($parent_sect_id,$child_sect_id,$site_id=site_id) {
        $obj_attached_sects_child = $this->find_child_sects_for_loops_check($parent_sect_id,$child_sect_id,$site_id);

        $flag = false;

        if($obj_attached_sects_child) {
            $chain = $obj_attached_sects_child;
        }
        else {
            $chain = array();
        }

        $chain[$parent_sect_id] = 1;

        if(isset($chain[$child_sect_id])) {
            $flag = true;
        }

        return !$this->loop_flag && !$flag;
    }
    private function get_sect_all_cats($sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            cat_id 
            FROM 
            u235_sects_cats 
            WHERE 
            sect_id=:sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/140'/*.$e->getMessage()*/);}
        return 0;
    }
    private function detach_sects_all_cats($sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM
            u235_sects_cats
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/150'/*.$e->getMessage()*/);}
    }
    private function delete_sect_files($sect_id,$site_id=site_id) {
        //get uDrive_folder_id
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT
            uDrive_folder_id
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/160'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $uDrive_folder_id = (int)$qr->uDrive_folder_id;
        }
        else {
            $uDrive_folder_id = 0;
        }

        //remove sect_avatars
        uFunc::rmdir('uCat/sect_avatars/'.$site_id.'/'.$sect_id);

        //update uDrive files
        include_once 'uDrive/file_update_bg.php';
        $uDrive=new file_update($this->uCore);

        //Delete file usage for this sect
        try {

            $stm=$this->uFunc->pdo('uDrive')->prepare("SELECT
            file_id
            FROM
            u235_files_usage
            WHERE
            file_mod='uCat' AND
            handler_type='sect' AND
            handler_id=:handler_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':handler_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/170'/*.$e->getMessage()*/);}

        try {

            $stm1=$this->uFunc->pdo('uDrive')->prepare("DELETE FROM
                u235_files_usage
                WHERE
                file_mod='uCat' AND
                handler_type='sect' AND
                handler_id=:handler_id AND
                site_id=:site_id
                ");
            $stm1->bindParam(':handler_id', $sect_id,PDO::PARAM_INT);
            $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm1->execute();

        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/180'/*.$e->getMessage()*/);}


        while($file=$stm->fetch(PDO::FETCH_OBJ)) {
            $uDrive->recheck_file_usage($file->file_id);
        }
        //move files to recycled bin
        if($uDrive_folder_id) {
            $uDrive->file_id=$uDrive_folder_id;

            ob_start();
            $uDrive->recycle_files('recycle',1);
            ob_end_clean();
        }
    }
    private function delete_sect_from_db($sect_id,$site_id=site_id) {
        //Delete sect from db
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/190'/*.$e->getMessage()*/);}
    }
    private function set_certain_primary_sect_id4sect($sect_id,$primary_sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('UPDATE 
            u235_sects
            SET
            primary_sect_id=:primary_sect_id
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
          ');
            $stm->bindParam(':primary_sect_id', $primary_sect_id,PDO::PARAM_INT);
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/200'/*.$e->getMessage()*/);}

        return $primary_sect_id;
    }

    //CATS
    private function get_cat_sects($cat_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT DISTINCT
            sect_id
            FROM
            u235_sects_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ');


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/210'/*.$e->getMessage()*/);}
        return false;
    }
    private function attach_cats_items($cat_id, $item_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('REPLACE INTO 
            u235_cats_items (
            cat_id,
            item_id,
            site_id
            ) VALUES (
            :cat_id,
            :item_id,
            :site_id
            )');


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/220'/*.$e->getMessage()*/);}
    }
    private function detach_cats_items($cat_id, $item_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM 
            u235_cats_items
            WHERE 
            cat_id=:cat_id AND 
            item_id=:item_id AND 
            site_id=:site_id
            ');


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/230'/*.$e->getMessage()*/);}
    }
    private function get_new_cat_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT
            cat_id
            FROM
            u235_cats
            WHERE
            site_id=:site_id
            ORDER BY
            cat_id DESC
            LIMIT 1
            ');


            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            if($new_cat_id = $stm->fetch(PDO::FETCH_ASSOC)) {
                return (int)$new_cat_id['cat_id'] + 1;
            }

            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/240'/*.$e->getMessage()*/);}

        return 1;
    }
    private function set_certain_primary_sect_id($cat_id,$primary_sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('UPDATE 
            u235_cats
            SET
            primary_sect_id=:primary_sect_id
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
          ');
            $stm->bindParam(':primary_sect_id', $primary_sect_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/250'/*.$e->getMessage()*/);}

        return $primary_sect_id;
    }
    private function set_certain_primary_sect_id_in_sects($parent_sect_id,$child_sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('UPDATE 
            u235_sects
            SET
            primary_sect_id=:parent_sect_id
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
          ');
            $stm->bindParam(':parent_sect_id', $parent_sect_id,PDO::PARAM_INT);
            $stm->bindParam(':sect_id', $child_sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/260'/*.$e->getMessage()*/);}

        return $parent_sect_id;
    }
    private function delete_cats_uDrive_file_usage($cat_id,$site_id=site_id) {
        require_once 'uDrive/file_update_bg.php';
        $uDrive=new file_update($this->uCore);

        try {

            $stm=$this->uFunc->pdo('uDrive')->prepare("SELECT
            file_id
            FROM
            u235_files_usage
            WHERE
            file_mod='uCat' AND
            handler_type='cat' AND
            handler_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/270'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if($file=$stm->fetch(PDO::FETCH_OBJ)) {
            try {

                $stm=$this->uFunc->pdo('uDrive')->prepare("DELETE FROM
                u235_files_usage
                WHERE
                file_mod='uCat' AND
                handler_type='sect' AND
                handler_id=:cat_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/280'/*.$e->getMessage()*/);}

            while($file) {
                $uDrive->recheck_file_usage($file->file_id);

                $file=$stm->fetch(PDO::FETCH_OBJ);
            }

        }
    }
    private function detach_all_fields_from_cat($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('DELETE FROM
            u235_cats_fields
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/290'/*.$e->getMessage()*/);}
    }
    private function detach_all_sects_cats($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_sects_cats
            WHERE
            cat_id=:cat_id AND
            sect_id!=0 AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/300'/*.$e->getMessage()*/);}
    }
    private function detach_cats_all_items($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_cats_items
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/310'/*.$e->getMessage()*/);}
    }
    private function get_cat_fields($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            field_id
            FROM
            u235_cats_fields
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();

        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/320'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    private function get_cat_items($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            item_id
            FROM
            u235_cats_items
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/330'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    private function sect_id2cats_for_home($sect_id,$q_select="u235_cats.cat_id") {
        try {

            $stm1=$this->uFunc->pdo("uCat")->prepare("SELECT
            ".$q_select."
            FROM
            u235_cats
            JOIN
            u235_sects_cats
            ON
            u235_cats.cat_id=u235_sects_cats.cat_id AND
            u235_cats.site_id=u235_sects_cats.site_id
            WHERE
            show_on_hp=1 AND
            item_count>0 AND
            sect_id=:sect_id AND
            u235_sects_cats.site_id=:site_id
            ORDER BY
            cat_pos ASC,
            item_count DESC,
            cat_title ASC
            ");
            $site_id=site_id;
            $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm1->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm1->execute();

        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/340'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm1;
    }
    private function cat_id2uDrive_folder_id($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            uDrive_folder_id
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();


            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;
            else return (int)$qr->uDrive_folder_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/350'/*.$e->getMessage()*/);}

        return 0;
    }
    private function get_children_items_number($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
        COUNT(item_id) as item_count 
        FROM 
        u235_cats_items 
        WHERE 
        cat_id=:cat_id AND
        site_id=:site_id
        ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->item_count;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/360'/*.$e->getMessage()*/);}

        return 0;
    }
    private function update_cat_item_count($cat_id,$site_id=site_id) {
        //get number of children items
        $item_count=$this->get_children_items_number($cat_id,$site_id);

        //update cat's item_count
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
        u235_cats
        SET
        item_count=:item_count 
        WHERE 
        cat_id=:cat_id AND
        site_id=:site_id
        ");
            $stm->bindParam(':item_count', $item_count,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/370'/*.$e->getMessage()*/);}
    }
    private function get_cat_sect_count($cat_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            COUNT(cat_id)
            FROM
            u235_sects_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            if($catid = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $catid["COUNT(cat_id)"];
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/380'/*.$e->getMessage()*/);}

        return false;
    }
    private function attach_cats_fields($cat_id,$field_id,$site_id=site_id) {
        //Attach cats_fields
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
            u235_cats_fields (
            cat_id,
            field_id,
            site_id
            ) VALUES (
            :cat_id,
            :field_id,
            :site_id
            )");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/390'/*.$e->getMessage()*/);}
    }
    private function get_cat_field_count($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            COUNT(cat_id) AS field_count
            FROM
            u235_cats_fields
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            return $qr->field_count;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/400'/*.$e->getMessage()*/);}
        return 0;
    }
    private function update_cat_field_count($cat_id,$site_id=site_id) {
        $field_count=$this->get_cat_field_count($cat_id,$site_id);
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_cats
            SET
            field_count=:field_count
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':field_count', $field_count,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/410'/*.$e->getMessage()*/);}
    }
    private function detach_cats_fields($cat_id,$field_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_cats_fields
             WHERE
            cat_id=:cat_id AND
            field_id=:field_id AND
            site_id=:site_id
            ");
        $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
        $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
        $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/420'/*.$e->getMessage()*/);}
    }

    //ITEM
    private function clean_item_arrays($site_id=site_id) {
        unset($this->item_id2data_ar[$site_id]);
        unset($this->has_variants_ar[$site_id]);
        unset($this->has_variant_ar[$site_id]);
        unset($this->item_id2default_variant_id_ar[$site_id]);
        unset($this->q_item_variants[$site_id]);
    }
    private $item_id2data_ar;
    private $has_variants_ar;
    private $has_variant_ar;
    private $item_id2default_variant_id_ar;
    private $q_item_variants;
    private $get_item_price_ar;
    public function get_item_quantity($item_id,$var_id=0,$site_id=site_id) {
        if(!$var_id) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                quantity
                FROM 
                u235_items
                WHERE 
                item_id=:item_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();


                return (int)$stm->fetch(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/430'/*.$e->getMessage()*/);}
        }
        else {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                var_quantity as quantity 
                FROM 
                items_variants
                WHERE 
                var_id=:var_id AND
                item_id=:item_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_id', $var_id, PDO::PARAM_INT);
                $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();


                return (int)$stm->fetch(PDO::FETCH_COLUMN);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/440'/*.$e->getMessage()*/);}
        }
        return 0;
    }
    private function get_item_cat_count($item_id, $site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            COUNT(item_id)
            FROM
            u235_cats_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");


            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            if($qr = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $qr["COUNT(item_id)"];
            }
            else {
                return false;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/450'/*.$e->getMessage()*/);}
        return false;
    }
    private function calculate_item_cat_count($item_id, $site_id=site_id) {
        $cat_count=$this->get_item_cat_count($item_id);
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items
            SET
            cat_count=:cat_count
            WHERE 
            item_id=:item_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':cat_count', $cat_count, PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/460'/*.$e->getMessage()*/);}

        //calculate cat_count for parent sects
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            DISTINCT sect_id 
            FROM 
            u235_cats_items
            JOIN
            u235_sects_cats
            ON 
            u235_cats_items.cat_id=u235_sects_cats.cat_id AND
            u235_cats_items.site_id=u235_sects_cats.site_id
            WHERE
            item_id=:item_id AND
            u235_cats_items.site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            while($sect_obj=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->calculate_sect_cat_count($sect_obj->sect_id,$site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/470'/*.$e->getMessage()*/);}
    }
    private function detach_item_all_cats($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_cats_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/480'/*.$e->getMessage()*/);}
    }
    private function detach_item_all_articles($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_articles_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/490'/*.$e->getMessage()*/);}
    }
    private function detach_from_cats($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_id 
            FROM 
            u235_cats_items 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/500'/*.$e->getMessage()*/);}

        $this->detach_item_all_cats($item_id,$site_id);


        /** @noinspection PhpUndefinedVariableInspection */
        while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->calculate_cat_item_count($qr->cat_id,$site_id);
        }
    }
    private function detach_from_arts($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            art_id 
            FROM 
            u235_articles_items 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/510'/*.$e->getMessage()*/);}

        $this->detach_item_all_articles($item_id,$site_id);


        /** @noinspection PhpUndefinedVariableInspection */
        while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            $this->calculate_article_item_count($qr->art_id,$site_id);
        }
    }
    private function delete_item_files($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            uDrive_folder_id
            FROM
             u235_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/520'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $uDrive_folder_id=(int)$qr->uDrive_folder_id;
        else $uDrive_folder_id=0;

        //delete item's files
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
             u235_items_files
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/530'/*.$e->getMessage()*/);}
        $this->uFunc->rmdir($this->uCore->mod.'/items_files/'.$site_id.'/'.$item_id);

        //delete item's images
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_items_pictures
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/540'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir($this->uCore->mod.'/item_pictures/'.$site_id.'/'.$item_id);
        //delete item's avatar
        $this->uFunc->rmdir($this->uCore->mod.'/item_avatars/'.$site_id.'/'.$item_id);

        //update uDrive files
        /** @noinspection PhpIncludeInspection */
        include_once 'uDrive/file_update_bg.php';
        $uDrive=new file_update($this->uCore);

        //Delete file usage for this item

        try {

            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
            file_id
            FROM
             u235_files_usage
            WHERE
            file_mod='uCat' AND
            handler_type='item' AND
            handler_id=:handler_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':handler_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/550'/*.$e->getMessage()*/);}

        try {

            $stm1=$this->uFunc->pdo("uDrive")->prepare("DELETE FROM
                u235_files_usage
                WHERE
                file_mod='uCat' AND
                handler_type='item' AND
                handler_id=:handler_id AND
                site_id=:site_id
                ");
            $stm1->bindParam(':handler_id', $item_id,PDO::PARAM_INT);
            $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm1->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/560'/*.$e->getMessage()*/);}


        while($file=$stm->fetch(PDO::FETCH_OBJ)) {
            $uDrive->recheck_file_usage($file->file_id);
        }
        //move files to recycled bin
        if($uDrive_folder_id) {
            $uDrive->file_id=$uDrive_folder_id;;

            ob_start();
            $uDrive->recycle_files('recycle',1);
            ob_end_clean();
        }
    }
    private function delete_item_from_db($item_id,$site_id=site_id){
        //delete item from db
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
             u235_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/570'/*.$e->getMessage()*/);}
    }
    private function attach_arts_items($art_id,$item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_articles_items (
            art_id,
            item_id,
            site_id
            ) VALUES (
            :art_id,
            :item_id,
            :site_id
            )");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/580'/*.$e->getMessage()*/);}
    }
    private function get_item_art_number($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            COUNT(art_id) as art_count 
            FROM 
            u235_articles_items 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            return $qr->art_count;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/590'/*.$e->getMessage()*/);}
        return 0;
    }
    private function calculate_item_art_count($item_id,$site_id=site_id) {
        //get parent cats
        $art_count=$this->get_item_art_number($item_id,$site_id);

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items 
                SET
                art_count=:art_count
                WHERE 
                item_id=:item_id AND
                site_id=:site_id
                ");
            $stm->bindParam(':art_count', $art_count,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/600'/*.$e->getMessage()*/);}
    }
    private function increase_added_to_cart_counter($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE  
            u235_items 
            SET
            added_to_cart_counter=added_to_cart_counter+1
            WHERE
            item_id=:item_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/610'/*.$e->getMessage()*/);}
    }
    private function is_real_item($item_id,$var_id,$site_id=site_id) {
        $var_id=(int)$var_id;
        if(!$var_id){
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                item_id 
                FROM 
                u235_items
                JOIN 
                items_types
                ON
                item_type=type_id AND
                u235_items.site_id=items_types.site_id
                WHERE 
                base_type_id=0 AND
                item_id=:item_id AND
                u235_items.site_id=:site_id
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                if($stm->fetch(PDO::FETCH_OBJ)) return 1;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/620'/*.$e->getMessage()*/);}
        }
        else {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                item_id 
                FROM 
                items_variants 
                JOIN 
                items_variants_types
                ON 
                items_variants.var_type_id=items_variants_types.var_type_id
                JOIN
                items_types
                ON
                item_type_id=type_id AND
                items_variants_types.site_id=items_types.site_id
                WHERE 
                base_type_id=0 AND
                item_id=:item_id AND
                var_id=:var_id AND
                items_variants.site_id=:site_id
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                if($stm->fetch(PDO::FETCH_OBJ)) return 1;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/630'/*.$e->getMessage()*/);}
        }
        return 0;
    }

    public function attach_art2item($item_id,$art_id,$site_id=site_id) {
        $this->attach_arts_items($art_id,$item_id,$site_id);
        $this->calculate_article_item_count($art_id,$site_id);
        $this->calculate_item_art_count($item_id,$site_id);
    }
    public function detach_art_from_item($item_id,$art_id,$site_id=site_id) {
        //get affected items
        $q_items=$this->get_article_items($art_id,$site_id);
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_articles_items
             WHERE
            art_id=:art_id AND
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/640'/*.$e->getMessage()*/);}

        $this->calculate_article_item_count($art_id,$site_id);


        while($item_obj=$q_items->fetch(PDO::FETCH_OBJ)) $this->calculate_item_art_count($item_obj->item_id,$site_id);
    }
    public function attach_field2item($field_id,$item_id,$site_id=site_id) {
        $q_cats=$this->get_item_cats($item_id,$site_id);

        while($cat_obj=$q_cats->fetch(PDO::FETCH_OBJ)) {
            $this->attach_field2cat($field_id,$cat_obj->cat_id,$site_id);
            $this->update_cat_field_count($cat_obj->cat_id);
        }
        $this->update_field_cat_count($field_id,$site_id);
    }
    public function get_new_item_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            site_id=:site_id
            ORDER BY
            item_id DESC
            LIMIT 1
            ");


            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();


            if($new_item_id = $stm->fetch(PDO::FETCH_ASSOC)) {
                return (int)$new_item_id["item_id"] + 1;
            }
            else {
                return 1;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/660'/*.$e->getMessage()*/);}

        return 1;
    }
    public function create_new_item($item_title,$site_id=site_id) {
        $item_id = $this->get_new_item_id(site_id);

        $avail_id=$this->get_any_available_avail_id();
        if(!$unit_id=$this->get_default_unit_id(site_id)) $unit_id="NULL";

        $uuid=$this->uFunc->generate_uuid();

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_items (
            item_id,
            evotor_uuid,
            item_avail,
            unit_id,
            item_title,
            item_descr,
            item_keywords,
            item_article_number,
            site_id
            ) VALUES (
            :item_id,
            :uuid,
            :item_avail,
            :unit_id,
            :item_title,
            '',
            '',
            :item_id,
            :site_id
            )");
            $item_title=uString::text2sql($item_title,1);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':uuid', $uuid,PDO::PARAM_INT);
            $stm->bindParam(':item_avail', $avail_id,PDO::PARAM_INT);
            $stm->bindParam(':unit_id', $unit_id,PDO::PARAM_INT);
            $stm->bindParam(':item_title', $item_title,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/680'.$e->getMessage());}

        return $item_id;
    }
    public function item_search_by_id($item_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            item_id=:item_id AND 
            site_id=:site_id
            ");


            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($itemid = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$itemid["item_id"];

            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/690'/*.$e->getMessage()*/);}

        return false;
    }
    public function item_search_by_title($item_title,$site_id=site_id) {
        $item_title=uString::text2sql($item_title,1);
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            item_title=:item_title AND 
            site_id=:site_id
            ");

            $stm->bindParam(':item_title', $item_title,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$qr["item_id"];
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/700'/*.$e->getMessage()*/);}

        return false;
    }
    public function item_exists($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return ($stm->fetch(PDO::FETCH_OBJ))?1:0;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/710'/*.$e->getMessage()*/);}

        return 0;
    }
    public function item_article_number_exists($item_article_number,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id
            FROM
            u235_items
            WHERE
            item_article_number=:item_article_number AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int) $qr->item_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/720'/*.$e->getMessage()*/);}

        return 0;
    }
    public function item_id2data($item_id,$data="`item_id`",$site_id=site_id) {
        if(!isset($this->item_id2data_ar[$site_id][$item_id][$data])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                ".$data."
                FROM
                u235_items
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/730'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            $this->item_id2data_ar[$site_id][$item_id][$data]=$stm->fetch(PDO::FETCH_OBJ);
        }
        return $this->item_id2data_ar[$site_id][$item_id][$data];
    }
    public function item_update($item_id,$set_ar=array(),$where_ar=array(),$site_id=site_id) {
        /*$set_ar=array(
          array('cont_name',$value,PDO::PARAM_INT),
          array('cont_name',$value,PDO::PARAM_INT)
        );*/
        /*$where_ar=array(
          array('cont_name',$value,PDO::PARAM_INT),
          array('cont_name',$value,PDO::PARAM_INT)
        );*/

        try {
            if(!count($set_ar)) return false;
            $set_sql='';
            $url_is_saving=$url_is_used=0;
            for($i=0;$i<count($set_ar);$i++) {
                $set_sql.=$set_ar[$i][0].'=:'.$set_ar[$i][0] .', ';
                if($set_ar[$i][0]==="item_url") $url_is_saving=1;
            }

            if(!count($where_ar)) {
                $where_ar=array(
                    array(
                        'item_id',$item_id,PDO::PARAM_INT
                    )
                );
            }
            $where_sql='';
            for($i=0;$i<count($where_ar);$i++) {
                $where_sql.=$where_ar[$i][0].'=:'.$where_ar[$i][0] .' AND ';
            }

            if($url_is_saving) {
                try {

                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                   item_id
                   FROM
                   u235_items
                   WHERE
                   item_url=:item_url
                   AND
                   site_id=:site_id
                   ");
                    $stm->bindParam(':item_url', $set_ar[0][1],PDO::PARAM_STR);
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uCat/common/740'/*.$e->getMessage()*/);}


                /** @noinspection PhpUndefinedVariableInspection */
                if ($stm->fetch(PDO::FETCH_OBJ)) $url_is_used=1;
            }

            if ($url_is_used) $status = 1;
            else {
                $status = 0;

                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                   u235_items
                   SET
                   ".$set_sql."
                   site_id=site_id
                   WHERE
                   ".$where_sql."
                   site_id=:site_id
                   ");

                for($i=0;$i<count($set_ar);$i++) {
                    $stm->bindParam(':'.$set_ar[$i][0], $set_ar[$i][1],$set_ar[$i][2]);
                }
                for($i=0;$i<count($where_ar);$i++) {
                    $stm->bindParam(':'.$where_ar[$i][0], $where_ar[$i][1],$where_ar[$i][2]);
                }

                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }

        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/750'/*.$e->getMessage()*/);}

        //Clean uPage cache
        if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
        $this->uPage->clear_cache4uCat_latest();
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $status;
    }
    public function has_variants($item_id,$site_id=site_id) {
        if(!isset($this->has_variants_ar[$site_id][$item_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_id
                FROM
                items_variants
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                LIMIT 1
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/760'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            $this->has_variants_ar[$site_id][$item_id]=($stm->fetch(PDO::FETCH_OBJ)?1:0);
        }
        return $this->has_variants_ar[$site_id][$item_id];
    }
    public function has_options($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
             COUNT(item_id) AS options_number
             FROM
             items_options
             WHERE
             item_id=:item_id AND
             site_id=:site_id
             ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/770'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        if($res=$stm->fetch(PDO::FETCH_OBJ)) return (int)$res->options_number;
        return 0;
    }
    public function has_variant($item_id,$var_type_id,$site_id=site_id) {
        if(!isset($this->has_variant_ar[$site_id][$item_id][$var_type_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_id
                FROM
                items_variants
                WHERE
                item_id=:item_id AND
                var_type_id=:var_type_id AND
                site_id=:site_id 
                LIMIT 1
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':var_type_id', $var_type_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/780'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            $this->has_variant_ar[$site_id][$item_id][$var_type_id]=($stm->fetch(PDO::FETCH_OBJ)?1:0);
        }
        return $this->has_variant_ar[$site_id][$item_id][$var_type_id];
    }
    public function item_id2default_variant_id($item_id,$site_id=site_id) {
        if(!isset($this->item_id2default_variant_id_ar[$site_id][$item_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_id
                FROM
                items_variants
                WHERE
                default_var=1 AND
                item_id=:item_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/790'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            if($variant=$stm->fetch(PDO::FETCH_OBJ)) $this->item_id2default_variant_id_ar[$site_id][$item_id]=(int)$variant->var_id;
            else {//we have no default variant for item. Let's make default any item's variant
                $q_vars=$this->get_item_variants_pdo($item_id,$site_id);


                if($variant=$q_vars->fetch(PDO::FETCH_OBJ)) {
                    $this->set_default_variant($item_id,$variant->var_id);
                    $this->item_id2default_variant_id_ar[$site_id][$item_id]=(int)$variant->var_id;
                }
                else {//we have no variants for this item.
                    $this->item_id2default_variant_id_ar[$site_id][$item_id]=0;
                }
            }

        }
        return $this->item_id2default_variant_id_ar[$site_id][$item_id];
    }

    public function get_item_variants_pdo($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            var_id,
            item_article_number,
            var_type_id,
            default_var,
            price,
            prev_price,
            var_quantity,
            img_time,
            inaccurate_price,
            request_price,
            avail_id,
            file_id
            FROM
            items_variants
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/800'/*.$e->getMessage()*/);}
        return false;
    }
    public function get_item_price($order_id,$item_id,$var_id=0,$site_id=site_id) {
        if(!isset($this->get_item_price_ar[$site_id][$order_id][$item_id][$var_id])) {
            if($var_id) {
                $var=$this->var_id2data($var_id,$site_id);
                if($var) {
                    $price=(float)$var->price;
                }
                else {
                    $this->order_delete_item($order_id,$item_id,$var_id,$site_id);
                    $price=0;
                }
            }
            else {
                $item=$this->item_id2data($item_id,"`item_price`");

                if($item) {
                    $price=(float)$item->item_price;
                }
                else {
                    $this->order_delete_item($order_id,$item_id,$var_id,$site_id);
                    $price=0;
                }
            }
            $this->get_item_price_ar[$site_id][$order_id][$item_id][$var_id]=$price;
        }
        return $this->get_item_price_ar[$site_id][$order_id][$item_id][$var_id];
    }
    public function calculate_item_parents_item_count($item_id,$site_id=site_id) {
        //get parent cats
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
        cat_id 
        FROM 
        u235_cats_items
        WHERE 
        item_id=:item_id AND
        site_id=:site_id
        ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/810'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($cat_obj=$stm->fetch(PDO::FETCH_OBJ)) $this->calculate_cat_item_count($cat_obj->cat_id,$site_id);
    }
    public function get_item_cats($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_id 
            FROM 
            u235_cats_items 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/820'/*.$e->getMessage()*/);}
        return 0;
    }
    public function delete_item($item_id,$site_id=site_id) {
        $this->detach_from_cats($item_id,$site_id);
        $this->detach_from_arts($item_id,$site_id);
        $this->delete_item_files($item_id,$site_id);
        $this->delete_item_from_db($item_id,$site_id);

        //Clean uPage cache
        if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
        $this->uPage->clear_cache4uCat_latest();
    }
    public function item_type_id2data($item_type,$site_id=site_id) {
        if(!isset($this->item_type_id2data_ar[$site_id][$item_type])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                base_type_id,
                type_title
                FROM
                items_types
                WHERE
                type_id=:type_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':type_id', $item_type,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/830'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            $this->item_type_id2data_ar[$site_id][$item_type]=$stm->fetch(PDO::FETCH_OBJ);
        }
        return $this->item_type_id2data_ar[$site_id][$item_type];
    }
    public function set_certain_primary_cat_id($item_id,$primary_cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items
            SET
            primary_cat_id=:primary_cat_id
            WHERE
            item_id=:item_id AND
            site_id=:site_id
          ");
            $stm->bindParam(':primary_cat_id', $primary_cat_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/840'/*.$e->getMessage()*/);}

        return $primary_cat_id;
    }

    private function populate_item_widgets_default($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            items_widgets (
            item_id,
            site_id
            ) VALUES ( 
            :item_id,
            :site_id
            )
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/850'/*.$e->getMessage()*/);}
    }
    public function get_item_widgets($item_id,$q_select="
        wgt_0,
        wgt_1,
        wgt_2,
        wgt_3,
        wgt_4,
        wgt_5,
        wgt_6,
        wgt_7
        ",$site_id=site_id,$antireccursion=0) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select." 
            FROM 
            items_widgets 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr;
            else {
                if(!$antireccursion) {
                    $this->populate_item_widgets_default($item_id, $site_id);
                    return $this->get_item_widgets($item_id, $q_select, $site_id, 1);
                }
                else $this->uFunc->error("uCat/common/860",1);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/870'/*.$e->getMessage()*/);}
        return 0;
    }

    public function item_file_id2url($file_id,$site_id=site_id) {
        $file_id=(int)$file_id;
        if($file_id) {
            require_once "uDrive/classes/common.php";
            $uDrive=new \uDrive\common($this->uCore);

            if($file_data=$uDrive->file_id2data($file_id,"file_hashname,file_name",$site_id)) {
                return u_sroot.'uDrive/file/'.$file_id.'/'.$file_data->file_hashname.'/'.$file_data->file_name;
            }
        }
        return "";
    }

    //FIELDS
    private function get_field_cat_count($field_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            COUNT(field_id) AS cat_count
            FROM
            u235_cats_fields
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            return $qr->cat_count;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/880'/*.$e->getMessage()*/);}
        return 0;
    }
    private function update_field_cat_count($field_id,$site_id=site_id) {
        $cat_count=$this->get_field_cat_count($field_id,$site_id);

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_fields
            SET
            cat_count=:cat_count
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_count', $cat_count,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/890'/*.$e->getMessage()*/);}
    }
    private function getField_sql_type($field_type_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_sql_type
            FROM
            u235_fields_types
            WHERE
            field_type_id=:field_type_id
            ");
            $stm->bindParam(':field_type_id', $field_type_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->field_sql_type;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/900'/*.$e->getMessage()*/);}

        $this->uFunc->error('uCat/common/910');
        return 0;
    }
    private function get_free_field_id($field_sql_type){
        //get new field id
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_id
            FROM
            u235_fields
            ORDER BY
            field_id DESC
            LIMIT 1
            ");
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/920'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $field_id=$qr->field_id+1;
        else $field_id=1;

        //create new column in fields
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("ALTER TABLE
            u235_items
            ADD
            field_".$field_id." ".$field_sql_type." NULL 
            ");
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/930'.$e->getMessage());}

        return $field_id;
    }
    public function get_show_in_cart_fields($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            field_id,
            field_title
            FROM 
            u235_fields 
            WHERE 
            planelist_show=1 AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/940'/*.$e->getMessage()*/);}
        return 0;
    }

    //VARIANTS - var types
    private $get_variants_types_ar;
    private $var_type_id2data_ar;
    private function get_new_var_type_id($site_id=site_id) {

        if(!$query=$this->uCore->query("uCat","SELECT
        `var_type_id`
        FROM
        `items_variants_types`
        WHERE
        `site_id`='".$site_id."'
        ORDER BY
        `var_type_id` DESC
        LIMIT 1
        ")) $this->uFunc->error('uCat/common/950');
        if(mysqli_num_rows($query)) {

            $qr=$query->fetch_object();
            return $qr->var_type_id+1;
        }
        return 1;
    }
    private $get_var_types_json_ar;
    private $get_var_type_of_selected_item_type_ar;
    private function get_var_type_of_selected_item_type($item_type_id,$site_id=site_id) {
        if(!isset($this->get_var_type_of_selected_item_type_ar[$site_id][$item_type_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `var_type_id`
            FROM
            `items_variants_types`
            WHERE
            `item_type_id`='".$item_type_id."' AND
            `site_id`='".$site_id."'
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/960');
            if(mysqli_num_rows($query)) {

                $qr=$query->fetch_object();
                $this->get_var_type_of_selected_item_type_ar[$site_id][$item_type_id]=(int)$qr->var_type_id;
            }
            else $this->get_var_type_of_selected_item_type_ar[$site_id][$item_type_id]=0;
        }
        return $this->get_var_type_of_selected_item_type_ar[$site_id][$item_type_id];
    }

    //VARIANTS - variants
    private function clean_variants_arrays($site_id=site_id) {
        unset($this->q_item_variants[$site_id]);
        unset($this->var_id2data_ar[$site_id]);
        unset($this->is_default_item_variant_ar[$site_id]);
        unset($this->has_variants_ar[$site_id]);
        unset($this->has_variant_ar[$site_id]);
        unset($this->item_id2default_variant_id_ar[$site_id]);
        unset($this->var_type_id2var_id_ar[$site_id]);
        unset($this->var_exists_ar[$site_id]);
    }
    private $var_exists_ar;
    private $var_id2data_ar;
    private $var_id2price;
    private $var_id2var_type_id;
    private $is_default_item_variant_ar;
    private $var_type_id2var_id_ar;

    //AVAILABILITY
    private $get_avails_ar;
    private $get_avails_json_ar;
    private $avail_id2avail_data_ar;
    private $get_any_dontshow_avail_id_ar;
    private $get_any_available_avail_id_ar;
    private function create_avail_value($avail_type_id=2,$avail_label="Не отображать",$avail_descr="Товар не отображается на сайте",$site_id=site_id) {
        //get new id

        if(!$query=$this->uCore->query("uCat","SELECT
        `avail_id`
        FROM
        `u235_items_avail_values`
        WHERE
        `site_id`='".$site_id."'
        ORDER BY
        `avail_id` DESC
        LIMIT 1
        ")) $this->uFunc->error('uCat/common/970');
        if(mysqli_num_rows($query)) {

            $qr=$query->fetch_object();
            $avail_id=$qr->avail_id+1;
        }
        else $avail_id=1;


        if(!$this->uCore->query("uCat","INSERT INTO
        `u235_items_avail_values` (
        `avail_id`,
        `avail_label`,
        `avail_descr`,
        `avail_type_id`,
        `site_id`
        ) VALUES (
        '".$avail_id."',
        '". uString::text2sql($avail_label)."',
        '". uString::text2sql($avail_descr)."',
        '".$avail_type_id."',
        '".$site_id."'
        )
        ")) $this->uFunc->error('uCat/common/980');
        return $avail_id;
    }

    //ITEM TYPE
    private function clean_items_types_arrays($site_id=site_id) {
        unset($this->item_type_is_used_ar[$site_id]);
        unset($this->get_default_type_id_ar[$site_id]);
        unset($this->item_type_id2data_ar[$site_id]);
        unset($this->q_item_types[$site_id]);
        unset($this->item_type_is_used_ar[$site_id]);
        unset($this->item_type_exists_ar[$site_id]);
    }
    private $item_type_exists_ar;
    private $get_default_type_id_ar;
    private function get_default_type_id($site_id=site_id) {
        if(!isset($this->get_default_type_id_ar[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `type_id`
            FROM
            `items_types`
            WHERE
            `base_type_id`='0' AND
            `site_id`='".$site_id."'
            ORDER BY `type_id` DESC
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/990');
            if(mysqli_num_rows($query)) {

                $qr=$query->fetch_object();
                $this->get_default_type_id_ar[$site_id]=(int)$qr->type_id;
            }
            else {//create default type
                $this->get_default_type_id_ar[$site_id]=$this->create_item_type();
            }
        }
        return $this->get_default_type_id_ar[$site_id];
    }
    private function create_item_type($base_type_id=0,$type_title='Товар',$site_id=site_id) {
        $type_id=$this->get_new_item_type_id();

        if(!$this->uCore->query("uCat","INSERT INTO
        `items_types` (
        `base_type_id`,
        `type_id`,
        `type_title`,
        `site_id`
        ) VALUES (
        '".$base_type_id."',
        '".$type_id."',
        '". uString::text2sql($type_title)."',
        '".$site_id."'
        )
        ")) $this->uFunc->error('uCat/common/1000');
        $this->clean_items_types_arrays();
        return $type_id;
    }
    private function get_new_item_type_id($site_id=site_id) {

        if(!$query=$this->uCore->query("uCat","SELECT
        `type_id`
        FROM
        `items_types`
        WHERE
        `site_id`='".$site_id."'
        ORDER BY
        `type_id` DESC
        LIMIT 1
        ")) $this->uFunc->error('uCat/common/1010');
        if(mysqli_num_rows($query)) {

            $qr=$query->fetch_object();
            return $qr->type_id+1;
        }
        return 1;
    }
    private $item_type_id2data_ar;
    private $item_type_is_used_ar;

    //CART
    public function get_order_items($order_id,$q_select="orders_items.item_id",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            ".$q_select."
            FROM
            orders_items
            JOIN 
            u235_items
            ON
            orders_items.item_id=u235_items.item_id AND
            orders_items.site_id=u235_items.site_id
            WHERE
            order_id=:order_id AND
            orders_items.site_id=:site_id
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        } /** @noinspection PhpUndefinedClassInspection */ catch(PDOException $e) {$this->uFunc->error('uCat/common/1020'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_item_quantity_in_order($order_id,$item_id,$var_id=0,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            item_count
            FROM 
            orders_items
            WHERE 
            item_id=:item_id AND
            order_id=:order_id AND
            var_id=:var_id AND 
            site_id=:site_id 
            LIMIT 1
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return (int)$stm->fetch(PDO::FETCH_COLUMN);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1030'/*.$e->getMessage()*/);}
        return 0;
    }
    private function clean_order_variables($site_id=site_id) {
        unset($this->order_check_if_item_is_added_ar[$site_id]);
        unset($this->order_check_if_item_can_be_added_ar[$site_id]);
        unset($this->order_get_items_ar[$site_id]);
        unset($this->order_get_every_item_count_ar[$site_id]);
        unset($this->get_order_id_ar);
    }
    private $order_check_if_item_is_added_ar;
    private function order_check_if_item_is_added($item_id,$order_id,$var_id=0,$site_id=site_id) {
        //returns 0 or more if this items is added to cart (number is quantity. May be 0. However record about this item is already exists in cart)
        //returns -1 if there are even no record about this item in cart
        if(!isset($this->order_check_if_item_is_added_ar[$site_id][$order_id][$item_id][$var_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_count
                FROM
                orders_items
                WHERE
                item_id=:item_id AND
                order_id=:order_id AND
                var_id=:var_id AND
                site_id=:site_id
                LIMIT 1
                ");
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $item_count=(int)$qr->item_count;
                    $this->order_check_if_item_is_added_ar[$site_id][$order_id][$item_id][$var_id]=$item_count;
                }
                else $this->order_check_if_item_is_added_ar[$site_id][$order_id][$item_id][$var_id]=-1;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1040'/*.$e->getMessage()*/);}
        }
        return $this->order_check_if_item_is_added_ar[$site_id][$order_id][$item_id][$var_id];
    }
    private $order_check_if_item_can_be_added_ar;
    private function order_check_if_item_can_be_added($item_id,$var_id,$site_id=site_id) {
        if(!isset($this->order_check_if_item_can_be_added_ar[$site_id][$item_id][$var_id])) {
            if($var_id) {
                try {

                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                    items_variants.item_id
                    FROM
                    u235_items_avail_values,
                    items_types,
                    items_variants,
                    items_variants_types
                    WHERE
                    u235_items_avail_values.site_id=:site_id AND
                    items_types.site_id=:site_id AND
                    items_variants.site_id=:site_id AND
                    items_variants_types.site_id=:site_id AND

                    items_variants.var_type_id=items_variants_types.var_type_id AND

                    items_variants.avail_id=u235_items_avail_values.avail_id AND
                    items_variants_types.item_type_id=items_types.type_id AND

                    items_variants.item_id=:item_id AND
                    items_variants.var_id=:var_id AND

                    u235_items_avail_values.avail_type_id!=2 AND
                    u235_items_avail_values.avail_type_id!=3 AND

                    items_variants.request_price=0 AND

                    (
                    items_types.base_type_id=0 OR
                    items_types.base_type_id=1 AND file_id!='0'
                    )
                    LIMIT 1
                    ");
                    $site_id=site_id;
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                    $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uCat/common/1050");}
            }
            else {
                try {

                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                    item_id
                    FROM
                    u235_items
                    JOIN 
                    u235_items_avail_values
                    ON
                    u235_items.item_avail=u235_items_avail_values.avail_id AND
                    u235_items_avail_values.site_id=u235_items.site_id
                    JOIN 
                    items_types
                    ON
                    u235_items.item_type=items_types.type_id AND
                    items_types.site_id=u235_items.site_id
                    WHERE
                    u235_items.site_id=:site_id AND

                    item_id=:item_id AND

                    avail_type_id!=2 AND
                    avail_type_id!=3 AND

                    request_price=0 AND

                    (
                    base_type_id=0 OR
                    base_type_id=1 AND file_id!=0
                    )
                    LIMIT 1
                    ");
                    $site_id=site_id;
                    $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uCat/common/1060");}
            }

            /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection */
            if($stm->fetch(PDO::FETCH_OBJ)) $this->order_check_if_item_can_be_added_ar[$site_id][$item_id][$var_id]=1;
            else $this->order_check_if_item_can_be_added_ar[$site_id][$item_id][$var_id]=0;
        }
        return $this->order_check_if_item_can_be_added_ar[$site_id][$item_id][$var_id];
    }
    private $get_order_id_ar;
    private $order_get_items_ar;
    private $order_get_every_item_count_ar;
    public function get_order_items_without_variants($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_items.item_id,
            item_count,
            u235_items.item_title,
            u235_items.item_price,
            u235_items.inaccurate_price,
            u235_items.request_price,
            u235_items.item_type,
            u235_items.file_id
            FROM
            u235_items
            JOIN
            orders_items
            ON
            u235_items.item_id=orders_items.item_id AND
            orders_items.site_id=u235_items.site_id
            WHERE
            has_variants=0 AND
            order_id=:order_id AND
            u235_items.site_id=:site_id
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1070'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_order_items_with_variants($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            items_variants.item_id,
            items_variants.var_id,
            orders_items.item_count,
            u235_items.item_title,
            items_variants.price,
            items_variants.inaccurate_price,
            items_variants.request_price,
            items_variants.var_type_id,
            items_variants.file_id
            FROM
            orders_items
            JOIN
            items_variants
            ON
            orders_items.item_id=items_variants.item_id AND
            orders_items.site_id=items_variants.site_id AND
            orders_items.var_id=items_variants.var_id
            JOIN 
            u235_items
            ON
            items_variants.item_id=u235_items.item_id AND
            items_variants.site_id=u235_items.site_id
            WHERE
            u235_items.has_variants=1 AND
            orders_items.order_id=:order_id AND
            items_variants.site_id=:site_id
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1080'/*.$e->getMessage()*/);}
        return 0;
    }

    //CONTRACTORS
    private $user_id2default_contractor_id_ar;
    private $user_id2cont_num_ar;
    private $user_id2cont_query_ar;
    private function get_new_cont_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT cont_id FROM contractors WHERE site_id=:site_id ORDER BY cont_id DESC LIMIT 1");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/1090");}

        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        $res=$stm->fetch(PDO::FETCH_OBJ);
        if($res) return $res->cont_id+1;
        return 1;
    }

    //ORDER
    private function get_new_order_id() {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT order_id FROM orders ORDER BY order_id DESC LIMIT 1");
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            if(!$qr) return 1;
            return $qr->order_id+1;
        }
        catch(PDOException $e) {return $this->uFunc->error('uCat/common/1100'/*.$e->getMessage()*/);}
    }
    private function create_order($site_id=site_id) {
        if(!isset($this->uSes)) $this->uSes=new uSes($this->uCore);
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO orders (
            order_id,
            order_status,
            order_timestamp,
            ses_id,
            user_id,
            site_id
            ) VALUES (
            :order_id,
            'new',
            ".time().",
            :ses_id,
            :user_id,
            :site_id
            )");
            $order_id=$this->get_new_order_id();
            $ses_id=$this->uSes->get_val('ses_id');
            $user_id=$this->uSes->get_val('user_id');
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $order_id;
        }
        catch(PDOException $e) {return $this->uFunc->error('uCat/common/1110'/*.$e->getMessage()*/);}
    }
    private $get_order_id_var;
    private $order_id2data_ar;
    public function empty_order_items($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM 
            orders_items 
            WHERE 
            order_id=:order_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1120'/*.$e->getMessage()*/);}
        return 0;
    }
    public function acquiring_security_uuid2data($security_uuid,$q_select="order_id",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select." 
            FROM 
            acquiring 
            WHERE 
            security_uuid=:security_uuid AND
            site_id=:site_id
            ");
            $stm->bindParam(':security_uuid', $security_uuid,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1130'/*.$e->getMessage()*/);}
        return 0;
    }
    public function acquiring_not_pay_key2data($not_pay_key,$q_select="order_id",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select." 
            FROM 
            acquiring 
            WHERE 
            not_pay_key=:not_pay_key AND
            site_id=:site_id
            ");
            $stm->bindParam(':not_pay_key', $not_pay_key,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1140'/*.$e->getMessage()*/);}
        return 0;
    }

    //UNITS

    //ARTICLES
    private function get_article_item_count($article_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            COUNT(item_id) as item_count 
            FROM 
            u235_articles_items 
            WHERE 
            art_id=:art_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':art_id', $article_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            return $qr->item_count;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1150'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_article_items($art_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            item_id 
            FROM 
            u235_articles_items 
            WHERE 
            art_id=:art_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1160'/*.$e->getMessage()*/);}
        return 0;
    }
    private function calculate_article_item_count($art_id,$site_id=site_id) {
        //get parent cats
        $item_count=$this->get_article_item_count($art_id,$site_id);

            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_articles 
                SET
                item_count=:item_count
                WHERE 
                art_id=:art_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':item_count', $item_count,PDO::PARAM_INT);
                $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1170'/*.$e->getMessage()*/);}
        }
    private function get_new_article_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            art_id
            FROM
            u235_articles
            WHERE
            site_id=:site_id
            ORDER BY
            art_id DESC
            LIMIT 1
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->art_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1180'/*.$e->getMessage()*/);}
        return 1;
    }
    private function detach_article_all_items($art_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM 
            u235_articles_items
            WHERE 
            art_id=:art_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1190'/*.$e->getMessage()*/);}
    }
    private function delete_art_from_db($art_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_articles
            WHERE
            art_id=:art_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1200'/*.$e->getMessage()*/);}
    }
    private function delete_art_files($art_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            uDrive_folder_id
            FROM
            u235_articles
            WHERE
            art_id='".$art_id."' AND
            site_id='".site_id."'
            ");
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1210'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $uDrive_folder_id=(int)$qr->uDrive_folder_id;
        else  $uDrive_folder_id=0;

        //remove art_avatars
        uFunc::rmdir('uCat/art_avatars/'.$site_id.'/'.$art_id);

        //update uDrive files
        /** @noinspection PhpIncludeInspection */
        include_once 'uDrive/file_update_bg.php';
        $uDrive=new file_update($this->uCore);

        //Delete file usage for this art
        try {

            $stm=$this->uFunc->pdo("uDrive")->prepare("SELECT
            file_id
            FROM
            u235_files_usage
            WHERE
            file_mod='uCat' AND
            handler_type='art' AND
            handler_id=:handler_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':handler_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1220'/*.$e->getMessage()*/);}

        try {

            $stm1=$this->uFunc->pdo("uDrive")->prepare("DELETE FROM
                u235_files_usage
                WHERE
                file_mod='uCat' AND
                handler_type='art' AND
                handler_id=:handler_id AND
                site_id=:site_id
                ");
            $stm1->bindParam(':handler_id', $art_id,PDO::PARAM_INT);
            $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm1->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1230'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */

        while($file=$stm->fetch(PDO::FETCH_OBJ)) $uDrive->recheck_file_usage($file->file_id);

        //move files to recycled bin
        if($uDrive_folder_id) {
            $uDrive->file_id=$uDrive_folder_id;

            ob_start();
            $uDrive->recycle_files('recycle',1);
            ob_end_clean();
        }
    }



    //PUBLIC
    //ARTICLES
    public function get_last_articles($limit=5,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            art_id,
            art_title,
            art_avatar_time,
            art_text
            FROM
            u235_articles
            WHERE
            site_id=:site_id
            ORDER BY
            art_id DESC
            LIMIT ".$limit."
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('1583214889'/*.$e->getMessage()*/);}
        return 0;
    }

    //SECTS
    public function attach_cat2sect($sect_id, $cat_id, $calculate_count=true, $site_id=site_id) {
        if(!(int)$cat_id) return 0;
        $this->attach_sects_cats($sect_id, $cat_id);
        $this->calculate_sect_item_count($sect_id, $site_id);
        if($calculate_count) {
            $this->calculate_sect_cat_count($sect_id, $site_id);
            $this->calculate_cat_sect_count($cat_id, $site_id);
        }
        $this->set_certain_primary_sect_id($cat_id,$sect_id,$site_id);
        return 1;
    }
    public function detach_sectFromCat($sect_id, $cat_id,$site_id=site_id) {
        if(!(int)$sect_id&&!(int)$cat_id) return 0;
        $this->detach_sects_cats($sect_id, $cat_id);

        $this->calculate_sect_cat_count($sect_id, $site_id);
        $this->calculate_sect_item_count($sect_id, $site_id);
        $this->calculate_cat_sect_count($cat_id, $site_id);
        $this->set_auto_primary_sect_id($cat_id,$site_id);

        return 0;
    }
    public function create_new_sect($sect_title, $show_in_menu=1, $site_id=site_id) {
        $sect_id = $this->get_new_sect_id();
        $sect_uuid = $this->uFunc->generate_uuid();

        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("INSERT INTO
            u235_sects
            (sect_id,
            sect_uuid,
            sect_title,
            show_in_menu,
            site_id) 
            VALUES 
            (:sect_id,
            :sect_uuid,
            :sect_title,
            :show_in_menu,
            :site_id)
            ");


            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);

            $stm->bindParam(':sect_uuid', $sect_uuid,PDO::PARAM_STR);

            $stm->bindParam(':sect_title', $sect_title,PDO::PARAM_STR);

            $stm->bindParam(':show_in_menu', $show_in_menu,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();

            return $sect_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1250'/*.$e->getMessage()*/);}

        return false;
    }
    public function sect_search_by_id($sect_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id
            FROM
            u235_sects
            WHERE
            sect_id=:sect_id AND 
            site_id=:site_id
            ");


            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($sectid = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$sectid["sect_id"];
            return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1260'/*.$e->getMessage()*/);}
        return false;
    }
    public function sect_search_by_title($sect_title,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id
            FROM
            u235_sects
            WHERE
            sect_title=:sect_title AND 
            site_id=:site_id
            ");


            $stm->bindParam(':sect_title', $sect_title,PDO::PARAM_STR);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($secttitle = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$secttitle["sect_id"];
            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1270'/*.$e->getMessage()*/);}

        return false;
    }
    public function get_sects($q_select="sect_id") {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            ".$q_select."
            FROM
            u235_sects
            WHERE
            cat_count>0 AND
            item_count>0 AND
            site_id=:site_id
            ORDER BY
            sect_pos ASC,
            sect_title ASC
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1280'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    public function attach_sect2sect($parent_sect_id,$child_sect_id,$calculate_count=true,$site_id=site_id) {
        if($this->search_sects_for_loops($parent_sect_id,$child_sect_id,$site_id)) {
            $this->attach_sects_sects($child_sect_id, $parent_sect_id);
            $this->set_certain_primary_sect_id_in_sects($parent_sect_id, $child_sect_id, site_id);
        }
        else {
            echo "{
                'status' : 'loop',
                'action' : 'attach',
                'sect_id' : '".$child_sect_id."',
                'parent_sect_id':'".$parent_sect_id."'
                }";
            exit;
        }

        if($calculate_count) {
            //Update sect's cat_count
            $this->calculate_sect_item_count($parent_sect_id);
            $this->calculate_sect_cat_count($parent_sect_id);
        }
    }
    public function detach_sectFromSect($parent_sect_id,$child_sect_id,$site_id=site_id) {
        //attach or detach
        $this->detach_sects_sects($child_sect_id,$parent_sect_id,$site_id);

        //Update sect's count
        $this->calculate_sect_item_count($parent_sect_id);
        $this->calculate_sect_cat_count($parent_sect_id);
    }
    public function delete_sect($sect_id,$site_id=site_id){
        if(!(int)$sect_id) return 0;

        $this->delete_sect_files($sect_id,$site_id);
        //get affected cats
        $q_cats=$this->get_sect_all_cats($sect_id,$site_id);

        //Detach cats from sect
        $this->detach_sects_all_cats($sect_id,$site_id);

        $this->delete_sect_from_db($sect_id,$site_id);

        //update cat's sect_count

        while($cat=$q_cats->fetch(PDO::FETCH_OBJ)) $this->calculate_cat_sect_count($cat->cat_id,$site_id);
        return 1;
    }
    public function get_show_in_menu_sects($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id,
            sect_title,
            sect_url,
            sect_avatar_time,
            cat_count
            FROM
            u235_sects
            WHERE
            /*cat_count>0 AND*/
            item_count>0 AND
            show_in_menu=1 AND
            site_id=:site_id
            ORDER BY
            sect_pos ,
            sect_title
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1290'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_show_in_menu_sects2ndlvl($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            sect_id,
            sect_title,
            sect_url,
            sect_avatar_time,
            cat_count
            FROM
            u235_sects
            LEFT JOIN
            sects_sects
            ON
            sect_id=parent_sect_id AND
            u235_sects.site_id=sects_sects.site_id
            WHERE
            item_count>0 AND
            show_in_menu=1 AND
            child_sect_id IS NOT NULL AND
            u235_sects.site_id=:site_id
            ORDER BY
            sect_pos ,
            sect_title
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1300'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_show_in_menu_children_sects($parent_sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            sect_id,
            sect_title,
            sect_url,
            sect_avatar_time,
            cat_count
            FROM
            u235_sects
            LEFT JOIN
            sects_sects
            ON
            sect_id=child_sect_id AND
            u235_sects.site_id=sects_sects.site_id
            WHERE
            item_count>0 AND
            show_in_menu=1 AND
            parent_sect_id=:parent_sect_id AND
            u235_sects.site_id=:site_id
            ORDER BY
            sect_pos ,
            sect_title
            ");
            $stm->bindParam(':parent_sect_id', $parent_sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1310'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_cats_of_sect_for_sects_widget($sect_id,$site_id=site_id){
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            u235_cats.cat_id,
            u235_cats.cat_url,
            cat_title
            FROM
            u235_cats
            JOIN 
            u235_sects_cats
            ON
            u235_sects_cats.cat_id=u235_cats.cat_id AND
            u235_sects_cats.site_id=u235_cats.site_id
            WHERE
            /*show_on_hp=1 AND*/
            item_count>0 AND
            u235_sects_cats.sect_id=:sect_id AND
            u235_cats.site_id=:site_id
            ORDER BY
            u235_cats.cat_pos ,
            u235_cats.item_count DESC,
            u235_cats.cat_title
            ");
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1320'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_sects_of_sect_for_sects_widget($sect_id,$site_id=site_id)
    {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            u235_sects.sect_id,
            u235_sects.sect_url,
            sect_title
            FROM
            u235_sects
            JOIN 
            sects_sects
            ON
            sects_sects.child_sect_id=u235_sects.sect_id AND
            sects_sects.site_id=u235_sects.site_id
            WHERE
            /*show_in_menu=1 AND*/
            item_count>0 AND
            sects_sects.parent_sect_id=:sect_id AND
            u235_sects.site_id=:site_id
            ORDER BY
            u235_sects.sect_pos ,
            u235_sects.item_count DESC,
            u235_sects.sect_title
            ");

            $stm->bindParam(':sect_id', $sect_id, PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1330'/*.$e->getMessage()*/);}
        return 0;
    }
    public function set_auto_primary_sect_id4sect($sect_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            parent_sect_id
            FROM 
            sects_sects
            WHERE
            child_sect_id=:child_sect_id AND
            site_id=:site_id
            ORDER BY
            parent_sect_id DESC
            LIMIT 1
            ");
            $stm->bindParam(':child_sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1340'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $primary_sect_id=(int)$qr->sect_id;
        else $primary_sect_id=0;

        $this->set_certain_primary_sect_id4sect($sect_id,$primary_sect_id,$site_id);
        return $primary_sect_id;
    }

    //CATS
    public function detach_field_from_cat($field_id,$cat_id,$site_id=site_id) {
        $this->detach_cats_fields($cat_id,$field_id,$site_id);
        $this->update_cat_field_count($cat_id,$site_id);
        $this->update_field_cat_count($field_id,$site_id);
    }
    public function attach_item2cat($cat_id, $item_id, $calculate_cat_item_count=true, $site_id=site_id) {
        $this->attach_cats_items($cat_id, $item_id,$site_id);
        if($calculate_cat_item_count) {
            $this->calculate_cat_item_count($cat_id, $site_id);
        }
        $this->calculate_item_cat_count($item_id, $site_id);

        $this->set_certain_primary_cat_id($item_id,$cat_id,$site_id);

        //Clean uPage cache
        if (!isset($this->uPage)) $this->uPage = new \uPage\common($this->uCore);
        $this->uPage->clear_cache4uCat_latest();
    }
    public function detach_itemFromCat($cat_id, $item_id, $site_id=site_id) {
        $this->detach_cats_items($cat_id, $item_id,$site_id);
        $this->calculate_cat_item_count($cat_id, $site_id);
        $this->calculate_item_cat_count($item_id, $site_id);
    }
    public function create_new_cat($cat_title, $cat_uuid=0, $site_id=site_id) {
        if(!$cat_uuid) $cat_uuid=$this->uFunc->generate_uuid();
        $cat_id = $this->get_new_cat_id();

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            u235_cats (
            cat_id,
            cat_uuid,
            cat_title,
            site_id
            ) VALUES (
            :cat_id,
            :cat_uuid,
            :cat_title,
            :site_id
            )");


            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);

            $stm->bindParam(':cat_uuid', $cat_uuid, PDO::PARAM_STR);

            $stm->bindParam(':cat_title', $cat_title, PDO::PARAM_STR);

            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);

            $stm->execute();

            return (int)$cat_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1350'/*.$e->getMessage()*/);}

        return 0;
    }
    public function cat_search_by_id($cat_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND 
            site_id=:site_id
            ");


            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($catid = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$catid["cat_id"];
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1360'/*.$e->getMessage()*/);}

        return 0;
    }
    public function cat_search_by_title($cat_title,$site_id=site_id) {
        $cat_title=uString::text2sql($cat_title);
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats
            WHERE
            cat_title=:cat_title AND 
            site_id=:site_id
            ");


            $stm->bindParam(':cat_title', $cat_title,PDO::PARAM_STR);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($cattitle = $stm->fetch(PDO::FETCH_ASSOC)) return (int)$cattitle["cat_id"];

            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1370'/*.$e->getMessage()*/);}
        return false;
    }
    public function set_auto_primary_sect_id($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            sect_id
            FROM 
            u235_sects_cats
            WHERE
            u235_sects_cats.cat_id=:cat_id AND
            sect_id!=0 AND
            u235_sects_cats.site_id=:site_id
            ORDER BY
            sect_id DESC
            ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1380'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if($qr=$stm->fetch(PDO::FETCH_OBJ)) $primary_sect_id=(int)$qr->sect_id;
        else $primary_sect_id=0;


        $this->set_certain_primary_sect_id($cat_id,$primary_sect_id,$site_id);
        return $primary_sect_id;
    }
    public function delete_cat($cat_id,$site_id=site_id){
        if(!(int)$cat_id) return 0;
        //get affected items
        $q_items=$this->get_cat_items($cat_id,$site_id);
        //get affected sects
        $q_sects=$this->get_cat_sects($cat_id,$site_id);
        //get affected fields
        $q_fields=$this->get_cat_fields($cat_id,$site_id);

        //Get uDrive folder for this cat
        $uDrive_folder_id=$this->cat_id2uDrive_folder_id($cat_id,$site_id);

        //Update items primary_cat_id


        //delete cat
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
            u235_cats
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1390'/*.$e->getMessage()*/);}

        //remove cat_avatars
        $this->uFunc->rmdir('uCat/cat_avatars/'.site_id.'/'.$cat_id);

        //Detach items from cat
        $this->detach_cats_all_items($cat_id,$site_id);
        //Detach sects from cat
        $this->detach_all_sects_cats($cat_id,$site_id);
        //Detach fields from cat
        $this->detach_all_fields_from_cat($cat_id,$site_id);

        //update item's cat_count

        for($i=0; $item=$q_items->fetch(PDO::FETCH_OBJ); $i++) $this->calculate_item_cat_count($item->item_id,$site_id);
        //update sect's counters

        for($i=0; $sect=$q_sects->fetch(PDO::FETCH_OBJ); $i++) {
            $this->calculate_sect_item_count($sect->sect_id);
            $this->calculate_sect_cat_count($sect->sect_id);
        }
        //update field's cat_count

        for($i=0; $field=$q_fields->fetch(PDO::FETCH_OBJ); $i++) {
            $this->update_field_cat_count($field->field_id);
        }

        //update uDrive files
        require_once 'uDrive/file_update_bg.php';
        $uDrive=new file_update($this->uCore);

        //Delete file usage for this cat
        $this->delete_cats_uDrive_file_usage($cat_id,$site_id);

        //move files to recycled bin
        if($uDrive_folder_id) {
            $uDrive->file_id=$uDrive_folder_id;;

            ob_start();
            $uDrive->recycle_files('recycle',1);
            ob_end_clean();
        }

        return 1;
    }
    public function calculate_cat_item_count($cat_id,$site_id=site_id) {
        //update cat's item_count
        $this->update_cat_item_count($cat_id,$site_id);
        //get parent sects
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
        sect_id 
        FROM 
        u235_sects_cats
        WHERE 
        cat_id=:cat_id AND
        site_id=:site_id
        ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1400'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($sect_obj=$stm->fetch(PDO::FETCH_OBJ)) $this->calculate_sect_item_count($sect_obj->sect_id,$site_id);
    }
    public function calculate_cat_sect_count($cat_id, $site_id=site_id) {
        $sect_count=$this->get_cat_sect_count($cat_id);

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_cats
                SET
                sect_count=:sect_count
                WHERE 
                cat_id=:cat_id AND 
                site_id=:site_id
                ");

            $stm->bindParam(':sect_count', $sect_count, PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1410'/*.$e->getMessage()*/);}

        //calculate sect count for parent sects
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            sect_id 
            FROM 
            u235_sects_cats 
            WHERE 
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            while($sect_obj=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->calculate_sect_cat_count($sect_obj->sect_id,$site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1420'/*.$e->getMessage()*/);}
    }
    public function attach_field2cat($field_id,$cat_id,$site_id=site_id) {
        $this->attach_cats_fields($cat_id,$field_id,$site_id);
        $this->update_cat_field_count($cat_id,$site_id);
        $this->update_field_cat_count($field_id,$site_id);
    }
    public function get_all_cats($only_shown_in_widgets=1,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_id,
            cat_title,
            cat_url
            FROM 
            u235_cats 
            WHERE
            show_on_hp=:show_in_widgets AND
            cat_id!=0 AND
            site_id=:site_id
            ORDER BY 
                     cat_pos
            ");
            $stm->bindParam(':show_in_widgets', $only_shown_in_widgets,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1430'/*.$e->getMessage()*/);}
        return 0;
    }
    public function cat_exists($cat_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_id 
            FROM 
            u235_cats 
            WHERE 
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1440'/*.$e->getMessage()*/);}
        return 0;
    }

    //WIDGETS
    public function sects_list_widget() {
        require_once 'uCat/inc/sect_avatar.php';
        $sect_avatar=new uCat_sect_avatar($this->uCore);

        $sects_stm=$this->get_sects("sect_id,sect_title,sect_url,sect_avatar_time,cat_count");

        $cnt='
        <div class="row uCat_list">';

        for($i=$j=1;$sects=$sects_stm->fetch(PDO::FETCH_OBJ);$i++,$j++) {
            $cnt .= '
            <div class="col-md-6 col-sm-6 col-xs-12 col-lg-'.(site_id==41?3:4).' uCat_list '.(((int)$this->uFunc->getConf("show_sects_fullheight","uCat"))?"sects_fullheight":"").'">
                <div class="content">
                    <a 
                    href="'.u_sroot.'uCat/cats/'.(empty($sects->sect_url) ? $sects->sect_id : uString::sql2text($sects->sect_url)).'" 
                    class="thumbnail" 
                    style="';
                    if((int)$this->uFunc->getConf("show_sects_fullheight","uCat")) {
                            $cnt.=" background-image:url('";
                            $cnt.=$sect_avatar->get_avatar('sects_list',$sects->sect_id,$sects->sect_avatar_time);
                            $cnt.="');  background-size:cover;";
                    }
                    $cnt.='"
                    >';
                    if(!(int)$this->uFunc->getConf("show_sects_fullheight","uCat")) {
                        $cnt .= '<img class="uCat_sects_item_img" src="' . $sect_avatar->get_avatar("sects_list", $sects->sect_id, $sects->sect_avatar_time) . '">';
                    }
                    $cnt.='</a>
                    <b><a class="default-color" href="' . u_sroot . 'uCat/cats/' . $sects->sect_id . '">' . $sects->sect_title . '</a></b>';
                    if(site_id!=41) $cnt.='<br><br>';
                    $cnt.='<ul class="list-unstyled default-color">';
            $cats_stm=$this->sect_id2cats_for_home($sects->sect_id,"u235_cats.cat_id,u235_cats.cat_url,cat_title");

            while ($cats = $cats_stm->fetch(PDO::FETCH_OBJ)) {
                $cnt .= '<li><a href="' . u_sroot . 'uCat/items/' . (empty($cats->cat_url) ? $cats->cat_id : uString::sql2text($cats->cat_url)) . '">' . $cats->cat_title . '</a></li>';
            }
            $cnt .= '</ul>
                    <div class="uCat_list_more_btn"><a href="' . u_sroot . 'uCat/cats/' . $sects->sect_id . '">Посмотреть все (' . $sects->cat_count . ')</a></div>
                </div>
            </div>';
            if(site_id==41&&$i>3||site_id!=41&&$i>2) {
                $i=0;
                $cnt.='<div class="col-md-12 col-lg-12 hidden-sm hidden-xs">&nbsp;</div>';
            }
            if($j>1) {
                $j=0;
                $cnt.='<div class="col-sm-12 hidden-lg hidden-md hidden-xs ">&nbsp;</div>';
            }
        }
        $cnt.='</div>

        <script type="text/javascript">
        
        uCat_sects_widget={
            tune_cats_menu:function() {
                let cats = $(\'div.uCat_list ul.list-unstyled\');
                let curCat;
                for(let i=0; i<cats.length; i++) {
                    curCat=cats[i];
                    if($(curCat).height()<100) $(curCat).parent(\'div.content\').children(\'div.uCat_list_more_btn\').hide();
                    else {
                        let j;
                        let curCat_lis = $(curCat).children();
                        let curCat_lis_html = [];
                        for(j = 0; j<curCat_lis.length; j++) curCat_lis_html[j]=$(curCat_lis[j]).html();
                        $(curCat).html(\'\');
                        for(j = 0; j<curCat_lis.length; j++) {
                            $(curCat).html($(curCat).html()+\'<li>\'+curCat_lis_html[j]+\'</li>\');
                            if($(curCat).height()>90) break;
                        }
                    }
                }
            },
            
            tune_sects_height:function() {
                if($(document).width()>=768) {
                    let cont_ar = $(".uCat_list .content");
                    let height = 0;
                    for (let i = 0; i < cont_ar.length; i++) {
                        let cont_ar_height = $(cont_ar[i]).height();
                        if (cont_ar_height > height) height = cont_ar_height;
                    }
                    $(cont_ar).height(height+15);
                }
                else $(".uCat_list .content").height("auto");
            }
         };
        
                
        $(document).ready(function() {
            uCat_sects_widget.tune_cats_menu();
            uCat_sects_widget.tune_sects_height();
        });
        </script>';
        return $cnt;
    }
    public function last_items_widget ($cols_els_id,$js=1) {
        $uCat_avatar=new uCat_item_avatar($this->uCore);
        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        if(!isset($this->uCat_latest)) {
            require_once "uPage/elements/uCat_latest/common.php";
            $this->uCat_latest=new uCat_latest($this->uPage);
        }
        $conf=$this->uCat_latest->get_el_settings($cols_els_id);

        $conf->dots_style=(int)$conf->dots_style;
        $dots_style=$conf->dots_style;
        if(!$dots_style) {
            if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style")) {
                $dots_style=(int)$site_style_obj->sliders_dots_style;
            }
            else $dots_style=4;
        }

        $cnt='<h2><a href="'.u_sroot.'uCat/last_items">'.$conf->title.'</a></h2>
            <div class="wrapper-with-margin">
                <div id="uCat_latest_'.$cols_els_id.'" class="owl-carousel dots_style_'.$dots_style.' '.($conf->dots_style?'':'dots_style_0').'">';

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_items.item_id,
            item_img_time,
            item_title,
            item_url
            FROM 
            u235_items
            JOIN
            u235_items_avail_values
            ON
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            u235_items.site_id=u235_items_avail_values.site_id
            JOIN 
            items_widgets
            ON
            u235_items.item_id=items_widgets.item_id AND
            u235_items.site_id=items_widgets.site_id
            WHERE 
            parts_autoadd=0 AND
            wgt_0=1 AND
            (
            avail_type_id=1 OR
            avail_type_id=4 OR
            avail_type_id=5
            ) AND
            item_img_time>0 AND
            u235_items.site_id=:site_id
            ORDER BY
            item_id DESC 
            LIMIT ".$conf->items_number."
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1584597637'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($items=$stm->fetch(PDO::FETCH_OBJ)) {
            $item_img_url=$uCat_avatar->get_avatar(300,$items->item_id,$items->item_img_time);
            $cnt.='
                    <div>
                        <div style=\'background:url("'.$item_img_url.'") center; background-size:'.((int)$conf->image_style?'cover':'contain').'; background-repeat:no-repeat\'>
                            <a href="'.u_sroot.'uCat/item/'.(empty($items->item_url)?$items->item_id:uString::sql2text($items->item_url)).'" style="display: block; width: 100%; height: '.$conf->slide_height.'px;">
                                <img alt="'.$items->item_img_time.'" class="avatar" src="'.$item_img_url.'" style="visibility:hidden; width:100%; height: 100%;">
                            </a>
                        </div>
                    </div>';
        }
        $cnt.='
                </div>
            </div>';
        if($js) $cnt.='
            <script type="text/javascript">
            $(document).ready(function() {
                $("#uCat_latest_' . $cols_els_id . '").owlCarousel({
                    autoplayTimeout:6000,
                    autoplay:true,
                    autoplayHoverPause:true,
                    slideBy:"page",
                    navText:[\'<span class="icon-left-open"></span>\',\'<span class="icon-right-open"></span>\'],
                    loop:true,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:10,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:'.$conf->xs_number.',
                            nav:'.($conf->xs_show_arrows?'true':'false').',
                            dots:'.($conf->xs_show_markers?'true':'false').'
                        },
                        768:{
                            items:'.$conf->sm_number.',
                            nav:'.($conf->sm_show_arrows?'true':'false').',
                            dots:'.($conf->sm_show_markers?'true':'false').'
                        },
                        992:{
                            items:'.$conf->md_number.',
                            nav:'.($conf->md_show_arrows?'true':'false').',
                            dots:'.($conf->md_show_markers?'true':'false').'
                        },
                        1200:{
                            items:'.$conf->lg_number.',
                            nav:'.($conf->lg_show_arrows?'true':'false').',
                            dots:'.($conf->lg_show_markers?'true':'false').'
                        },
                        1920:{
                            items:'.$conf->xlg_number.',
                            nav:'.($conf->xlg_show_arrows?'true':'false').',
                            dots:'.($conf->xlg_show_markers?'true':'false').'
                        }
                    }
                })
            });
            </script>';

        return $cnt;
    }
    public function latest_articles_slider_widget ($cols_els_id) {
        require_once 'uCat/inc/art_avatar.php';
        $uCat_art_avatar=new uCat_art_avatar($this->uCore);
        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        if(!isset($this->uCat_latest_articles_slider)) {
            require_once "uPage/elements/uCat_latest_articles_slider/common.php";
            $this->uCat_latest_articles_slider=new uCat_latest_articles_slider($this->uPage);
        }

        $conf=$this->uCat_latest_articles_slider->get_el_settings($cols_els_id);

        $conf->dots_style=(int)$conf->dots_style;
        $dots_style=$conf->dots_style;
        if(!$dots_style) {
            if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style")) {
                $dots_style=(int)$site_style_obj->sliders_dots_style;
            }
            else $dots_style=4;
        }

        $stm_articles=$this->get_last_articles($conf->items_number);
        $cnt='<h2><a href="'.u_sroot.'uCat/articles">'.$conf->title.'</a></h2>
                <div id="latest_articles_slider_widget_'.$cols_els_id.'" class="owl-carousel dots_style_'.$dots_style.' '.($conf->dots_style?'':'dots_style_0').' uCat_latest_articles_slider">';

        while($art=$stm_articles->fetch(PDO::FETCH_OBJ)) {
            $art->art_id=(int)$art->art_id;
            $art->art_title=uString::sql2text($art->art_title,1);
            $art->art_text=uString::sql2text($art->art_text,1);
            $pos=mb_strpos($art->art_text,'<!-- my page break -->',0, 'UTF-8');
            if(!$pos) {
                $pos=mb_strpos($art->art_text,'<!-- pagebreak -->',0, 'UTF-8');
                if(!$pos) {
                    $art->art_text=mb_substr(strip_tags($art->art_text),0,600,'UTF-8').'...';
                }
                else $art->art_text=mb_substr($art->art_text,0,$pos,'UTF-8');
            }
            else $art->art_text=mb_substr($art->art_text,0,$pos,'UTF-8');

            $art->art_avatar=$uCat_art_avatar->get_avatar('art_page',$art->art_id);

            $cnt.='<div class="item">';
                if($art->art_avatar) {
                $cnt.='<a href="'.u_sroot.'uCat/article/'.$art->art_id.'">
                        <img alt="" class="avatar" src="'.$art->art_avatar.'">
                    </a>';
                }
                $cnt.='<h3 class="title"><a href="'.u_sroot.'uCat/article/'.$art->art_id.'">'.$art->art_title.'</a></h3>
                '.$art->art_text.'
            </div>';
        }
        $cnt.='</div>';
        $cnt.='
            <script type="text/javascript">
            $(document).ready(function() {
                 $("#latest_articles_slider_widget_'.$cols_els_id.'").owlCarousel({
                    autoHeight:false,
                    items:1,
                    dots:true,
                    navText:[\'<span class="icon-left-open"></span>\',\'<span class="icon-right-open"></span>\'],
                    nav:true,
                    autoplay:true,
                    autoplayHoverPause:true,
                    lazyLoad:true,
                    slideBy:"page",
            
                    loop:true,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:10
                });
            });
            </script>';

        return $cnt;
    }
    public function sale_items_widget ($cols_els_id) {
        $uCat_avatar=new uCat_item_avatar($this->uCore);

        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        if(!isset($this->uCat_sale)) {
            require_once "uPage/elements/uCat_sale/common.php";
            $this->uCat_sale=new uCat_sale($this->uPage);
        }

        $conf=$this->uCat_sale->get_el_settings($cols_els_id);

        $conf->dots_style=(int)$conf->dots_style;
        $dots_style=$conf->dots_style;
        if(!$dots_style) {
            if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style")) {
                $dots_style=(int)$site_style_obj->sliders_dots_style;
            }
            else $dots_style=4;
        }

        $cnt='<div class="wrapper-with-margin">
                <div id="uCat_sale_'.$cols_els_id.'" class="owl-carousel dots_style_'.$dots_style.' '.($conf->dots_style?'':'dots_style_0').' uCat_sale">';

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
                    u235_items.item_id,
                    item_img_time,
                    item_title,
                    item_url,
                    item_price,
                    prev_price,
                    item_article_number,
                    has_variants
                    FROM 
                    u235_items
                    JOIN
                    u235_items_avail_values
                    ON
                    u235_items.item_avail=u235_items_avail_values.avail_id AND
                    u235_items.site_id=u235_items_avail_values.site_id
                    JOIN
                    items_widgets
                    ON
                    u235_items.item_id=items_widgets.item_id AND
                    u235_items.site_id=items_widgets.site_id
                    WHERE 
                          parts_autoadd=0 AND
                    wgt_3=1 AND
                    prev_price!=0 AND
                    (
                    avail_type_id=1 OR
                    avail_type_id=4 OR
                    avail_type_id=5
                    ) AND
                    u235_items.site_id=:site_id
                    ORDER BY
                    prev_price DESC
                    LIMIT 20
                    ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1460'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($items=$stm->fetch(PDO::FETCH_OBJ)) {
            $cnt.='
                    <div class="uCat_sale_item">
                        <!--suppress CssUnknownTarget -->
<div class="item_image" style="background:url(\''.$uCat_avatar->get_avatar(300,$items->item_id,$items->item_img_time).'\') center; background-size:cover;">
                            <a href="'.u_sroot.'uCat/item/'.(empty($items->item_url)?$items->item_id:uString::sql2text($items->item_url)).'">&nbsp;</a>
                        </div>
                        <div class="item_info">
                            <div class="item_title" style="min-height: '.$conf->item_title_lines.'em;">'.uString::sql2text($items->item_title,1).'</div>'.
            ($this->uFunc->getConf("show_item_article_number","uCat")?('<div class="item_article_number text-muted">Арт: '.$items->item_article_number.'</div>'):"")
                            .'<div class="col-md-4 item_price text-primary">'.number_format($items->item_price,(count(explode('.',$items->item_price))>1?2:0),'.',' ').' '.(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>').'</div>
                            <div class="col-md-4 prev_price">'.number_format($items->prev_price,(count(explode('.',$items->prev_price))>1?2:0),'.',' ').' '.(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>').'</div>
                            <div class="col-md-4 buy_btn"><button type="button" class="btn btn-primary btn-sm" onclick="'.(
                (int)$items->has_variants?'uCat_cart.show_item_variants('.$items->item_id.')':
                    ((int)$this->uFunc->getConf('item_quantity_show','uCat')?
                        'uCat_cart.buy_indicate_quantity('.$items->item_id.','.$items->item_price.',0, this)':
                        'uCat_cart.buy('.$items->item_id.','.$items->item_price.',0, this)'
                    )).'">'.$this->uFunc->getConf("buy_btn_label","uCat").'</button></div>
                        </div>
                    </div>';
        }
        $cnt.='
                </div>
            </div>
            <script type="text/javascript">
            $(document).ready(function() {
                $("#uCat_sale_' . $cols_els_id . '").owlCarousel({
                    autoHeight:false,
                    autoplayTimeout:3000,
                    autoPlay:true,
                    autoplayHoverPause:true,
                    slideBy:"page",
                    dots:true,
                    navText:[\'<span class="icon-left-open"></span>\',\'<span class="icon-right-open"></span>\'],
                    nav:false,
                    loop:false,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:20,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:1,
                            nav:0,
                            dots:1,
                        },
                        480:{
                            items:2,
                            nav:0,
                            dots:1,
                        },
                        768:{
                            items:2,
                            nav:1,
                            dots:1,
                        },
                        1200:{
                            items:3,
                            nav:1,
                            dots:1,
                        },
                    }
                });
            });
            </script>';

        return $cnt;
    }
    public function popular_items_widget ($cols_els_id) {
        $uCat_avatar=new uCat_item_avatar($this->uCore);

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
             u235_items.item_id,
             item_img_time,
             item_title,
             item_url,
             item_price,
             prev_price,
             item_article_number,
             item_descr,
             has_variants,
             wgt_4
             FROM 
             u235_items
             JOIN
             u235_items_avail_values
             ON
             u235_items.item_avail=u235_items_avail_values.avail_id AND
             u235_items.site_id=u235_items_avail_values.site_id
             JOIN 
             items_widgets
             ON
             u235_items.item_id=items_widgets.item_id AND
             u235_items.site_id=items_widgets.site_id
             WHERE 
                   parts_autoadd=0 AND
             wgt_2=1 AND
             (
             avail_type_id=1 OR
             avail_type_id=4 OR
             avail_type_id=5
             ) AND
             u235_items.site_id=:site_id
             ORDER BY
             added_to_cart_counter DESC
             LIMIT 16
             ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1470'/*.$e->getMessage()*/);}

        $cnt='<div id="uCat_popular_'.$cols_els_id.'" class="uCat_popular row">';

        /** @noinspection PhpUndefinedVariableInspection */
        for($i=$j=0; $items=$stm->fetch(PDO::FETCH_OBJ);) {
            $wgts=$this->get_item_widgets($items->item_id,"wgt_5");
            $wgt_5=(int)$wgts->wgt_5;
            if($i===0) $cnt.='<div class="col-lg-12 col-md-12 hidden-sm hidden-xs"></div>';
            $cnt.='<div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                        <div class="uCat_popular_item" id="uCat_popular_item_'.$items->item_id.'">
                            <!--suppress CssUnknownTarget -->
<div class="item_image '.((int)$items->wgt_4?"show_hit_label":"").'" style="background:url(\''.$uCat_avatar->get_avatar(300,$items->item_id,$items->item_img_time).'\') center; background-size:cover;">
                                <a href="'.u_sroot.'uCat/item/'.(empty($items->item_url)?$items->item_id:uString::sql2text($items->item_url)).'">&nbsp;</a>
                            </div>
                            <div class="item_info">
                                <div class="item_title">'.uString::sql2text($items->item_title,1).'</div>'.
                ($this->uFunc->getConf("show_item_article_number","uCat")?('<div class="item_article_number text-muted">Арт: '.$items->item_article_number.'</div>'):"")
                                .'<div class="col-md-4 item_price text-primary">'.($wgt_5?"<small style='font-size: 0.7em;'>От</small>":"").number_format($items->item_price,(count(explode('.',$items->item_price))>1?2:0),'.',' ').(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>').'</div>
                                <div class="col-md-4 prev_price">';
            if((float)$items->prev_price>0) $cnt.=number_format($items->prev_price,(count(explode('.',$items->prev_price))>1?2:0),'.',' ').(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>');
                        $cnt.='</div>
                                <div class="col-md-4 buy_btn"><button type="button" class="btn btn-primary btn-sm" onclick="';
                        if((int)$items->has_variants) $cnt.='uCat_cart.show_item_variants('.$items->item_id.')';
                        else {
                            if((int)$this->uFunc->getConf('item_quantity_show','uCat')) {
                                $cnt .= 'uCat_cart.buy_indicate_quantity(' . $items->item_id . ',' . $items->item_price . ',0, this)';
                            }
                            else {
                                $cnt.='uCat_cart.buy('.$items->item_id.','.$items->item_price.',0, this)';
                            }
                        }
                        $cnt.='">'.$this->uFunc->getConf("buy_btn_label","uCat").'</button></div>
                            </div>
                        </div>
                    </div>';
            $i++;
            $j++;
            if($j>1) {
                $cnt.='<div class="hidden-lg hidden-md col-sm-12 hidden-xs"></div>';
                $j=0;
            }
            if($i>3) $i=0;
        }
        $cnt.='</div>';

        return $cnt;
    }
    public function new_items_widget ($cols_els_id) {
        require_once 'uCat/inc/item_avatar.php';
        $uCat_avatar=new uCat_item_avatar($this->uCore);

        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        if(!isset($this->uCat_new_items)) {
            require_once "uPage/elements/uCat_new_items/common.php";
            $this->uCat_new_items=new uCat_new_items($this->uPage);
        }
        $conf=$this->uCat_new_items->get_el_settings($cols_els_id);

        $conf->dots_style=(int)$conf->dots_style;
        $dots_style=$conf->dots_style;
        if(!$dots_style) {
            if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style")) {
                $dots_style=(int)$site_style_obj->sliders_dots_style;
            }
            else $dots_style=4;
        }

        $cnt='<!--<h2>Новинки</h2>-->
            <div class="wrapper-with-margin">
                <div id="uCat_new_items_'.$cols_els_id.'" class="owl-carousel dots_style_'.$dots_style.' '.($conf->dots_style?'':'dots_style_0').' uCat_new_items">';

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_items.item_id,
            item_img_time,
            item_title,
            item_url,
            item_price,
            prev_price,
            item_article_number,
            has_variants
            FROM 
            u235_items
            JOIN
            u235_items_avail_values
            ON
            u235_items.item_avail=u235_items_avail_values.avail_id AND
            u235_items.site_id=u235_items_avail_values.site_id
            JOIN
            items_widgets
            ON 
            u235_items.item_id=items_widgets.item_id AND
            u235_items.site_id=items_widgets.site_id
            WHERE 
                  parts_autoadd=0 AND
            wgt_1=1 AND
            item_price!=0 AND
            (
            avail_type_id=1 OR
            avail_type_id=4 OR
            avail_type_id=5
            ) AND
            u235_items.site_id=:site_id
            ORDER BY
            item_id DESC
            LIMIT ".$conf->items_number."
            ");
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1480'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($items=$stm->fetch(PDO::FETCH_OBJ)) {
            $cnt.='
                    <div class="uCat_new_items_item">
                        <!--suppress CssUnknownTarget -->
<div class="item_image" style="background:url(\''.$uCat_avatar->get_avatar(500,$items->item_id,$items->item_img_time).'\') center; background-size:cover;">
                            <a href="'.u_sroot.'uCat/item/'.(empty($items->item_url)?$items->item_id:uString::sql2text($items->item_url)).'">&nbsp;</a>
                        </div>
                        <div class="item_info">
                            <div class="item_title" style="min-height: '.$conf->item_title_lines.'em;">'.uString::sql2text($items->item_title,1).'</div>'.
                            ($this->uFunc->getConf("show_item_article_number","uCat")?('<div class="item_article_number text-muted">Арт: '.$items->item_article_number.'</div>'):'')
                            .'<div class="col-md-4 col- item_price text-primary">'.number_format($items->item_price,(count(explode('.',$items->item_price))>1?2:0),'.',' ').(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>').' </div>
                            <div class="col-md-4 prev_price" '.((float)$items->prev_price?"":'style="visibility:hidden;"').'>'.number_format($items->prev_price,(count(explode('.',$items->prev_price))>1?2:0),'.',' ').(site_id==54?'<span>Eur</span>':'<span class="icon-rouble"></span>').' </div>
                            <div class="col-md-4 buy_btn"><button type="button" class="btn btn-primary btn-sm" onclick="'.(
                        (int)$items->has_variants?'uCat_cart.show_item_variants('.$items->item_id.')':
                            ((int)$this->uFunc->getConf('item_quantity_show','uCat')?
                        'uCat_cart.buy_indicate_quantity('.$items->item_id.','.$items->item_price.',0, this)':
                        'uCat_cart.buy('.$items->item_id.','.$items->item_price.',0, this)'
                    )).'">'.$this->uFunc->getConf("buy_btn_label","uCat").'</button></div>
                        </div>
                    </div>';
        }
        $cnt.='
                </div>
            </div>
        
            <script type="text/javascript">
            $(document).ready(function() {
                $("#uCat_new_items_' . $cols_els_id . '").owlCarousel({
                    autoHeight:false,
                    autoplayTimeout:3000,
                    autoPlay:true,
                    autoplayHoverPause:true,
                    slideBy:"page",
                    navText:[\'<span class="icon-left-open"></span>\',\'<span class="icon-right-open"></span>\'],
                    loop:false,
                    merge:false,
                    mergeFit:false,
                    autoWidth:false,
                    margin:20,
                    responsiveClass:true,
                    responsive:{
                        0:{
                            items:1,
                            nav:0,
                            dots:1,
                        },
                        480:{
                            items:2,
                            nav:0,
                            dots:1,
                        },
                        768:{
                            items:2,
                            nav:1,
                            dots:1,
                        },
                        992:{
                            items:3,
                            nav:1,
                            dots:1,
                        },
                        1200:{
                            items:4,
                            nav:1,
                            dots:1,
                        },
                        1600:{
                            items:5,
                            nav:1,
                            dots:1,
                        },
                    }
            });
            });
            </script>';

        return $cnt;
    }
    public function search_widget ($cols_els_id) {
        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        if(!isset($this->uCat_search)) {
            require_once "uPage/elements/uCat_search/common.php";
            $this->uCat_search=new uCat_search($this->uPage);
        }
        $conf=$this->uCat_search->get_el_config_uCat_search($cols_els_id);

        $cnt='<form method="GET" action="'.u_sroot.'uCat/search" id="uCat_search_form_'.$cols_els_id.'">
        <div class="input-group" id="uCat_search_group'.$cols_els_id.'">
        <input name="search"';
        if(isset($_GET['search'])) {
            $_GET['search']=trim($_GET['search']);
            if(!empty($_GET['search'])) $cnt.=' value="'.htmlspecialchars(strip_tags($_GET['search'])).'"';
        }
        $cnt.='type="text" placeholder="'.htmlspecialchars($conf->placeholder).'" class="form-control">
                        <div class="input-group-btn">
                            <button class="btn btn-primary" onclick="jQuery(\'#uCat_search_form_'.$cols_els_id.'\').submit()"><span class="glyphicon glyphicon-search"></span></button>
                        </div>
                    </div>
                    <input type="submit" value=" " style="position: absolute; z-index:-100; width:0; height:0; overflow:hidden; display:block; background:transparent; border:none;">
                </form>';

        return $cnt;
    }
    public function left_menu(&$uCat_link,$show_uCat_left_menu) {
        $uCat=&$uCat_link;
        if($show_uCat_left_menu) {
            $left_bar_lvls_number=(int)$this->uFunc->getConf("left_bar_lvls_number","uCat");
            if($left_bar_lvls_number===3) {?>
                <div class="uCat_3lvl_menu">
                    <?$q_parent_sects=$this->get_show_in_menu_sects2ndlvl();


                    for($i=0; $parent_sect=$q_parent_sects->fetch(PDO::FETCH_OBJ);) {?>
                        <h4>
                            <a href="<?= u_sroot ?>uCat/cats/<?= empty($parent_sect->sect_url) ? $parent_sect->sect_id : uString::sql2text($parent_sect->sect_url) ?>">
                                <?=$parent_sect->sect_title?>
                            </a>
                        </h4>
                        <div class="uCat_menu" id="uCat_menu_sect_<?=$parent_sect->sect_id?>">
                            <?
                            $q_sects=$this->get_show_in_menu_children_sects($parent_sect->sect_id);


                            for ($j=0; $sects = $q_sects->fetch(PDO::FETCH_OBJ); $i++, $j++) {
                                $sect_id2accordion_num[$sects->sect_id] = $j;
                                $query1 = $this->get_cats_of_sect_for_sects_widget($sects->sect_id);
                                $query2 = $this->get_sects_of_sect_for_sects_widget($sects->sect_id);
                                ?>
                                <h3><?= $sects->sect_title ?></h3>
                                <div>
                                    <?/*if(mysqli_num_rows($query1)) {*/ ?>
                                    <ul>
                                        <?

                                        while ($child_sects = $query2->fetch(PDO::FETCH_OBJ)) {
                                            if ($this->uCore->mod=='uCat'&$this->uCore->page_name=='cats'&&$this->uCore->url_prop[1]==$child_sects->sect_id) $active = true; else $active = false; ?>
                                            <li <?= $active ? 'class="active"' : '' ?>><a
                                                    href="<?= u_sroot ?>uCat/cats/<?= empty($child_sects->sect_url) ? $child_sects->sect_id : uString::sql2text($child_sects->sect_url) ?>"><?= $child_sects->sect_title; ?></a>
                                            </li>
                                            <?
                                        } ?>
                                        <!--                                        </ul>-->

                                        <!--                                        <ul>-->
                                        <?

                                        while ($cats = $query1->fetch(PDO::FETCH_OBJ)) {
                                            if ($this->uCore->mod=='uCat' && $this->uCore->page_name=='items' && (int)$uCat->cat_id==(int)$cats->cat_id) $active = true; else $active = false; ?>
                                            <li class="<?= $active ? 'active' : '' ?>" data-cat_id="<?=isset($uCat->cat_id)?(int)$uCat->cat_id:''?>" data-cats-cat_id="<?=isset($cats->cat_id)?(int)$cats->cat_id:''?>">
                                                <a href="<?= u_sroot ?>uCat/items/<?= empty($cats->cat_url) ? $cats->cat_id : uString::sql2text($cats->cat_url) ?>"><?= $cats->cat_title; ?></a>
                                            </li>
                                            <?
                                        } ?>
                                    </ul>
                                    <?/*}*/ ?>
                                </div>
                            <?}?>
                        </div>
                        <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery('#uCat_menu_sect_<?=$parent_sect->sect_id?>').accordion({
                                    collapsible: true,
                                    heightStyle:'content',
                                    active: <?
                                    if(isset($uCat->sect_id)) {
                                        if(isset($sect_id2accordion_num[$uCat->sect_id])) /** @noinspection PhpUndefinedVariableInspection */echo $sect_id2accordion_num[$uCat->sect_id];
                                        else echo 'false';
                                    }
                                    else echo 'false';
                                    ?>
                                });
                            });
                        </script>
                    <?}?>
                </div>
            <?}
            elseif($left_bar_lvls_number===1) {?>
                <div class="uCat_1lvl_menu">
                    <ul>
                        <?
                        $q_cats=$this->get_all_cats();


                        while ($cat = $q_cats->fetch(PDO::FETCH_OBJ)) {
                            if ($this->uCore->mod=='uCat'&$this->uCore->page_name=='items'&&$this->uCore->url_prop[1]==$cat->cat_id) $active = true; else $active = false; ?>
                            <li <?= $active ? 'class="active"' : '' ?>>
                                <a href="<?=u_sroot?>uCat/items/<?= empty($cat->cat_url) ? $cat->cat_id: uString::sql2text($cat->cat_url) ?>"><?=uString::sql2text($cat->cat_title,1);?></a>
                            </li>
                            <?
                        }?>
                    </ul>
                </div>
            <?}
            elseif($left_bar_lvls_number===2) {?>
                <div class="uCat_menu">
                    <?
                    $q_sects=$this->get_show_in_menu_sects();

                    for($i=0; $sects=$q_sects->fetch(PDO::FETCH_OBJ); $i++) {
                        $sect_id2accordion_num[$sects->sect_id]=$i;
                        $query1=$this->get_cats_of_sect_for_sects_widget($sects->sect_id);
                        $query2=$this->get_sects_of_sect_for_sects_widget($sects->sect_id);
                        ?>
                        <h3><?=$sects->sect_title?></h3>
                        <div>
                            <?/*if(mysqli_num_rows($query1)) {*/?>
                            <ul>
                                <?
                                while($child_sects=$query2->fetch(PDO::FETCH_OBJ)) {
                                    if($this->uCore->mod=='uCat'&&$this->uCore->page_name=='cats'&&$this->uCore->url_prop[1]==$child_sects->sect_id) $active=true; else $active=false;?>
                                    <li <?=$active?'class="active"':''?>><a href="<?=u_sroot?>uCat/cats/<?=empty($child_sects->sect_url)?$child_sects->sect_id:uString::sql2text($child_sects->sect_url)?>"><?=$child_sects->sect_title;?></a></li>
                                <?}?>
                                <!--                                        </ul>-->

                                <!--                                        <ul>-->
                                <?
                                while($cats=$query1->fetch(PDO::FETCH_OBJ)) {
                                    if($this->uCore->mod=='uCat'&&$this->uCore->page_name=='items'&&$this->uCore->url_prop[1]==$cats->cat_id) $active=true; else $active=false;?>
                                    <li <?=$active?'class="active"':''?>><a href="<?=u_sroot?>uCat/items/<?=empty($cats->cat_url)?$cats->cat_id:uString::sql2text($cats->cat_url)?>"><?=$cats->cat_title;?></a></li>
                                <?}?>
                            </ul>
                            <?/*}*/?>
                        </div>
                    <?}?>
                </div>

                <script type="text/javascript">
                    jQuery(document).ready(function() {
                        jQuery('.uCat_menu').accordion({
                            collapsible: true,
                            heightStyle:'content',
                            active: <?
                            if(isset($uCat->sect_id)) {
                                if(isset($sect_id2accordion_num[$uCat->sect_id])) {
                                    /** @noinspection PhpUndefinedVariableInspection */
                                    echo $sect_id2accordion_num[$uCat->sect_id];
                                }
                                else echo 'false';
                            }
                            else echo 'false';
                            ?>
                        });
                    });
                </script>
            <?}
        }
    }
    public function filter_bar(&$uCat_link) {
        $uCat=&$uCat_link;
        if(isset($uCat->filter_bar)) echo $uCat->filter_bar;
    }

    //FIELDS
    public function get_new_field_id($field_type_id,$site_id=site_id) {
        //get list of field_id for current site
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            field_id
            FROM
            u235_fields
            WHERE
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1490'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($qr=$stm->fetch(PDO::FETCH_OBJ)) $cur_site_field_id[$qr->field_id]=1;

        $field_sql_type=$this->getField_sql_type($field_type_id);

        //get list of field_id for all sites with needed sql_type
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            field_id
            FROM
            u235_fields
            JOIN 
            u235_fields_types
            ON
            u235_fields.field_type_id=u235_fields_types.field_type_id
            WHERE
            field_sql_type=:field_sql_type
            ");
            $stm->bindParam(':field_sql_type', $field_sql_type,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1500'/*.$e->getMessage()*/);}


        while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            if(!isset($cur_site_field_id[$qr->field_id])) {
                return $qr->field_id;
            }
        }
        return $this->get_free_field_id($field_sql_type);
    }
    public function create_new_field($field_type_id,$field_data_ar,$site_id=site_id) {
        $field_id=$this->get_new_field_id($field_type_id);
//        $field_data_ar:
//        field_title
//        field_comment
//        field_units
//        field_pos
//        field_place_id
//        filter_type_id
//        field_effect_id
//        search_use
//        label_style_id
//        tablelist_show
//        planelist_show
//        tileslist_show
//        tileslist_show_on_card
//        sort_show
//        merge


        $field_data_ar['field_title']=uString::text2sql($field_data_ar['field_title']);
        $field_data_ar['field_comment']=uString::text2sql($field_data_ar['field_comment']);
        $field_data_ar['field_units']=uString::text2sql($field_data_ar['field_units']);

        //insert field_id
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO u235_fields (
            field_id,
            field_title,
            field_comment,
            field_units,
            field_type_id,
            field_pos,
            field_place_id,
            filter_type_id,
            field_effect_id,
            search_use,
            label_style_id,
            tablelist_show,
            planelist_show,
            tileslist_show,
            tileslist_show_on_card,
            sort_show,
            site_id,
            merge
            ) VALUES (
            :field_id,
            :field_title,
            :field_comment,
            :field_units,
            :field_type_id,
            :field_pos,
            :field_place_id,
            :filter_type_id,
            :field_effect_id,
            :search_use,
            :label_style_id,
            :tablelist_show,
            :planelist_show,
            :tileslist_show,
            :tileslist_show_on_card,
            :sort_show,
            :site_id,
            :merge
            )");
            $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            $stm->bindParam(':field_type_id', $field_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->bindParam(':field_title', $field_data_ar['field_title'],PDO::PARAM_STR);
            $stm->bindParam(':field_comment', $field_data_ar['field_comment'],PDO::PARAM_STR);
            $stm->bindParam(':field_units', $field_data_ar['field_units'],PDO::PARAM_STR);
            $stm->bindParam(':field_pos', $field_data_ar['field_pos'],PDO::PARAM_INT);
            $stm->bindParam(':field_place_id', $field_data_ar['field_place_id'],PDO::PARAM_INT);
            $stm->bindParam(':filter_type_id', $field_data_ar['filter_type_id'],PDO::PARAM_INT);
            $stm->bindParam(':field_effect_id', $field_data_ar['field_effect_id'],PDO::PARAM_INT);
            $stm->bindParam(':search_use', $field_data_ar['search_use'],PDO::PARAM_INT);
            $stm->bindParam(':label_style_id', $field_data_ar['label_style_id'],PDO::PARAM_INT);
            $stm->bindParam(':tablelist_show', $field_data_ar['tablelist_show'],PDO::PARAM_INT);
            $stm->bindParam(':planelist_show', $field_data_ar['planelist_show'],PDO::PARAM_INT);
            $stm->bindParam(':tileslist_show', $field_data_ar['tileslist_show'],PDO::PARAM_INT);
            $stm->bindParam(':tileslist_show_on_card', $field_data_ar['tileslist_show_on_card'],PDO::PARAM_INT);
            $stm->bindParam(':sort_show', $field_data_ar['sort_show'],PDO::PARAM_INT);
            $stm->bindParam(':merge', $field_data_ar['merge'],PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1510'/*.$e->getMessage()*/);}
        return $field_id;
    }
    public function check_if_field_title_exists($field_title,$site_id=site_id) {
        if(!strlen($field_title)) return "";

        $field_title_ar=explode(' ',$field_title);

        $field_title_sql="(";
        $field_binds=array();
        for($i=0;$i<count($field_title_ar);$i++) {
            if(strlen(trim($field_title_ar[$i]))>4) {
                $field_title_sql.="field_title LIKE '%:field_bind_".$i."%' OR";
                $field_binds[$i]=uString::text2sql(trim($field_title_ar[$i]));
            }
        }
        $field_title_sql.="field_title LIKE '%:field_bind_".$i."%')";
        $field_binds[$i]=$field_title;

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            field_title
            FROM
            u235_fields
            WHERE
            ".$field_title_sql." AND
            site_id=:site_id
            ");
            $bind_count=count($field_binds);
            for($i=0;$i<$bind_count;$i++) $stm->bindParam(':field_bind_'.$i, $field_binds[$i],PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1520'/*.$e->getMessage()*/);}

        $fields_html='';

        /** @noinspection PhpUndefinedVariableInspection */
        while($field=$stm->fetch(PDO::FETCH_OBJ)) $fields_html.='<li>'.uString::sql2text($field->field_title,1).'</li>';

        return $fields_html;
    }
    public function get_item_tab_fields($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            u235_fields.field_id,
            field_title,
            field_pos,
            field_units,
            field_style,
            field_place_id,
            field_effect_id,
            label_style_id
            FROM
            u235_fields
            JOIN 
            u235_fields_types
            ON
            u235_fields.field_type_id=u235_fields_types.field_type_id
            JOIN
            u235_cats_fields
            ON
            u235_cats_fields.field_id=u235_fields.field_id AND
            u235_fields.site_id=u235_cats_fields.site_id
            JOIN 
            u235_cats_items
            ON
            u235_cats_items.cat_id=u235_cats_fields.cat_id AND
            u235_fields.site_id=u235_cats_items.site_id
            WHERE
            u235_cats_items.item_id=:item_id AND
            u235_fields.site_id=:site_id AND
            field_place_id=6
            ORDER BY
            field_pos ,
            field_title
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1530'/*.$e->getMessage()*/);}
        return array();
    }
    public function get_site_fields_and_fields_types($q_select="u235_fields.field_id",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            $q_select 
            FROM 
            u235_fields
            JOIN
            u235_fields_types
            ON
            u235_fields.field_type_id=u235_fields_types.field_type_id
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1583214015'/*.$e->getMessage()*/,1);}
        return false;
    }

    //VARIANTS - var types
    public function update_variant_type($var_type_id,$set_sql,$site_id=site_id) {

        if(!$this->uCore->query("uCat","UPDATE
        `items_variants_types`
        SET
        ".$set_sql."
        WHERE
        `var_type_id`='".$var_type_id."' AND
        `site_id`='".$site_id."'
        ")) $this->uFunc->error('uCat/common/1540');
    }
    public function get_variants_types($site_id=site_id) {
        if(!isset($this->get_variants_types_ar[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `var_type_id`,
            `var_type_title`,
            `item_type_id`
            FROM
            `items_variants_types`
            WHERE
            `hidden`='0' AND
            `site_id`='".$site_id."'
            ")) $this->uFunc->error('uCat/common/1550');
            if(!mysqli_num_rows($query)) {//make at least 1 variant
                $this->create_variant_type();


                if(!$query=$this->uCore->query("uCat","SELECT
                `var_type_id`,
                `var_type_title`,
                `item_type_id`
                FROM
                `items_variants_types`
                WHERE
                `hidden`='0' AND
                `site_id`='".$site_id."'
                ")) $this->uFunc->error('uCat/common/1560');
            }
            $this->get_variants_types_ar[$site_id]=$query;
        }
        else mysqli_data_seek($this->get_variants_types_ar[$site_id],0);
        return $this->get_variants_types_ar[$site_id];
    }
    public function var_type_id2data($var_type_id,$site_id=site_id) {
        if(!isset($this->var_type_id2data_ar[$site_id][$var_type_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_type_title,
                item_type_id,
                item_type_id
                FROM
                items_variants_types
                WHERE
                var_type_id=:var_type_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_type_id', $var_type_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                $this->var_type_id2data_ar[$site_id][$var_type_id]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1570'/*.$e->getMessage()*/);}
        }
        return $this->var_type_id2data_ar[$site_id][$var_type_id];
    }
    public function create_variant_type($var_type_title='Товар',$item_type_id=0,$site_id=site_id) {
        if(!$item_type_id) {//get default item_type_id
            $item_type_id=$this->get_default_type_id();
        }
        //get new var_type_id
        $var_type_id=$this->get_new_var_type_id();

        if(!$this->uCore->query("uCat","INSERT INTO
        `items_variants_types` (
        `var_type_id`,
        `var_type_title`,
        `item_type_id`,
        `site_id`
        ) VALUES (
        '".$var_type_id."',
        '". uString::text2sql($var_type_title)."',
        '".$item_type_id."',
        '".$site_id."'
        )
        ")) $this->uFunc->error('uCat/common/1580');
        return array($var_type_id,$item_type_id);
    }
    public function get_var_types_json($var_type_id,$site_id=site_id) {
        if(!isset($this->get_var_types_json_ar[$site_id][$var_type_id])) {
            $q_var_types=$this->get_variants_types();
            $var_type_json='{';

            $var_type=$q_var_types->fetch_object();
            for($i=0;$var_type;$i++) {
                $var_type_json.='"'.$i.'":{
                    "val":"'.$var_type->var_type_id.'",
                    "label":"'.rawurlencode($var_type->var_type_title.' ('.$this->item_type_id2data($var_type->item_type_id)->type_title.')').'",
                    "selected":"'.((int)$var_type_id==(int)$var_type->var_type_id?'1':'0').'"
                }';

                if($var_type=$q_var_types->fetch_object()) $var_type_json.=',';
            }
            $var_type_json.='}';
            $this->get_var_types_json_ar[$site_id][$var_type_id]=$var_type_json;
        }
        return $this->get_var_types_json_ar[$site_id][$var_type_id];
    }
    public function get_var_types_json_not_added2item($current_var_type_id,$item_id,$show_current_var_type=0,$site_id=site_id) {
        if(!isset($this->get_var_types_json_ar[$site_id][$current_var_type_id])) {
            $q_var_types=$this->get_variants_types($site_id);
            $var_type_json='{';

            for($i=0;$var_type=$q_var_types->fetch_object();) {
                $skip=0;
                if(
                    ($current_var_type_id==$var_type->var_type_id&&!$show_current_var_type)||
                    ($this->has_variant($item_id,$var_type->var_type_id,$site_id)&&$current_var_type_id!=$var_type->var_type_id)
                ) {
                    $skip=1;
                }
                if(!$skip) {
                    $var_type_json.='"'.$i.'":{
                        "val":"'.$var_type->var_type_id.'",
                        "label":"'.rawurlencode(uString::sql2text($var_type->var_type_title,1).' ('. uString::sql2text($this->item_type_id2data($var_type->item_type_id,$site_id)->type_title,1).')').'",
                        "selected":"'.((int)$current_var_type_id==(int)$var_type->var_type_id?'1':'0').'"
                    },';
                    $i++;
                }
            }
            $var_type_json=substr($var_type_json,0,-1);
            $var_type_json.='}';
            $this->get_var_types_json_ar[$site_id][$current_var_type_id]=$var_type_json;
        }
        return $this->get_var_types_json_ar[$site_id][$current_var_type_id];
    }

    //VARIANTS - variants
    public function var_type_id2var_id($var_type_id,$item_id,$site_id=site_id) {
        if(!isset($this->var_type_id2var_id_ar[$site_id][$var_type_id][$item_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `var_id`
            FROM
            `items_variants`
            WHERE
            `var_type_id`='".$var_type_id."' AND
            `item_id`='".$item_id."' AND
            `site_id`='".$site_id."'
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/1590');
            if(mysqli_num_rows($query)) {

                $qr=$query->fetch_object();
                $this->var_type_id2var_id_ar[$site_id][$var_type_id][$item_id]=(int)$qr->var_id;
            }
            else $this->var_type_id2var_id_ar[$site_id][$var_type_id][$item_id]=0;
        }
        return $this->var_type_id2var_id_ar[$site_id][$var_type_id][$item_id];
    }
    public function var_exists($var_id,$site_id=site_id) {
        if(!isset($this->var_exists_ar[$site_id][$var_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_id
                FROM
                items_variants
                WHERE
                site_id=:site_id AND
                var_id=:var_id
                ");
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->execute();


                $this->var_exists_ar[$site_id][$var_id]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1600'/*.$e->getMessage()*/);}
        }
        return $this->var_exists_ar[$site_id][$var_id];
    }
    public function var_id2data($var_id,$site_id=site_id) {
        if(!isset($this->var_id2data_ar[$site_id][$var_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_article_number,
                items_variants.var_type_id,
                item_id,
                default_var,
                price,
                prev_price,
                var_quantity,
                img_time,
                inaccurate_price,
                request_price,
                avail_id,
                file_id,
                item_type_id,
                var_type_title
                FROM
                items_variants
                JOIN 
                items_variants_types
                ON
                items_variants.var_type_id=items_variants_types.var_type_id AND
                items_variants.site_id=items_variants_types.site_id
                WHERE
                var_id=:var_id AND
                items_variants.site_id=:site_id
                ");
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();

                $this->var_id2data_ar[$site_id][$var_id]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1610'/*.$e->getMessage()*/);}
        }
        return $this->var_id2data_ar[$site_id][$var_id];
    }
    public function var_id2price($var_id,$site_id=site_id) {
        if(!isset($this->var_id2price[$site_id][$var_id])) {

            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                price 
                FROM 
                items_variants 
                WHERE 
                site_id=:site_id AND
                var_id=:var_id
                ");
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();

                $this->var_id2price[$site_id][$var_id]=$stm->fetch(PDO::FETCH_OBJ)->price;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1620'/*.$e->getMessage()*/);}
        }
        return $this->var_id2price[$site_id][$var_id];
    }
    public function var_id2var_type_id($var_id,$site_id=site_id) {
        if(!isset($this->var_id2var_type_id[$site_id][$var_id])) {
            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                var_type_id
                FROM 
                items_variants
                WHERE 
                site_id=:site_id AND
                var_id=:var_id
                ");
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();

                if($res=$stm->fetch(PDO::FETCH_OBJ)) {
                    $this->var_id2var_type_id[$site_id][$var_id] = $res->var_type_id;
                }
                else {
                    return false;
                }
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1630'/*.$e->getMessage()*/);}
        }
        return $this->var_id2var_type_id[$site_id][$var_id];
    }
    public function setup_item_initial_variant($item_id,$site_id=site_id) {
        $q_select="
        `item_type`,
        `item_article_number`,
        `evotor_uuid`,
        `item_price`,
        `prev_price`,
        `quantity`,
        `inaccurate_price`,
        `request_price`,
        `item_avail`,
        `file_id`
        ";
        $item=$this->item_id2data($item_id,$q_select,$site_id);

        //get first available variant with same item_type_id
        $var_type_id=$this->get_var_type_of_selected_item_type($item->item_type,$site_id);
        if(!$var_type_id) {//we must create new var_type of selected item_type
            switch((int)$var_type_id) {
                case 0 :
                    $var_type_title='Товар';
                    break;
                case 1 :
                    $var_type_title='Ссылка';
                    break;
                default :
                    $this->uFunc->error('uCat/common/1640');
                    break;
            }
            /** @noinspection PhpUndefinedVariableInspection */
            $var_type_id=$this->create_variant_type($var_type_title,$item->item_type,$site_id)[0];
        }

        $var=$this->add_new_variant($item_id,$item->item_article_number,$var_type_id,$item->evotor_uuid,$item->item_price,$item->prev_price,$item->quantity,$item->inaccurate_price,$item->request_price,$item->item_avail,$item->file_id,$site_id);
        $this->set_default_variant($item_id,$var['var_id'],$site_id);
        return $var['var_id'];
    }
    public function add_new_variant($item_id,$item_article_number,$var_type_id,$uuid_variant=0,$price=0,$prev_price=0,$var_quantity=0,$inaccurate_price=0,$request_price=0,$avail_id=0,$file_id=0,$site_id=site_id) {
        if(!$uuid_variant) {
            $uuid_variant = $this->uFunc->generate_uuid();
        }
        if(!$avail_id) {//get avail id to not show on web-site
            $avail_id=$this->get_any_dontshow_avail_id();
        }

        //get new var id
        $var_id=$this->get_new_var_id($site_id);
        if($item_article_number===0) {
            $item_article_number = $item_id . 'V' . $var_id;
        }

        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
            items_variants (
            var_id,
            item_article_number,
            uuid_variant,
            var_type_id,
            item_id,
            price,
            prev_price,
            var_quantity,
            inaccurate_price,
            request_price,
            avail_id,
            file_id,
            site_id
            ) VALUES (
            :var_id,
            :item_article_number,
            :uuid_variant,
            :var_type_id,
            :item_id,
            :price,
            :prev_price,
            :var_quantity,
            :inaccurate_price,
            :request_price,
            :avail_id,
            :file_id,
            :site_id
            )
            ");
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            $stm->bindParam(':uuid_variant', $uuid_variant,PDO::PARAM_INT);
            $stm->bindParam(':var_type_id', $var_type_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':price', $price,PDO::PARAM_STR);
            $stm->bindParam(':prev_price', $prev_price,PDO::PARAM_STR);
            $stm->bindParam(':var_quantity', $var_quantity,PDO::PARAM_STR);
            $stm->bindParam(':inaccurate_price', $inaccurate_price,PDO::PARAM_INT);
            $stm->bindParam(':request_price', $request_price,PDO::PARAM_INT);
            $stm->bindParam(':avail_id', $avail_id,PDO::PARAM_INT);
            $stm->bindParam(':file_id', $file_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1650'.$e->getMessage());}

        $this->item_update($item_id,array(
            array('has_variants',1,PDO::PARAM_INT)
        ),array(),$site_id);

        $this->clean_item_arrays($site_id);
        $this->clean_variants_arrays($site_id);
        return array(
            'var_id'=>$var_id,
            'item_article_number'=>$item_article_number,
            'uuid_variant'=>$uuid_variant,
            'var_type_id'=>$var_type_id,
            'item_id'=>$item_id,
            'price'=>$price,
            'prev_price'=>$prev_price,
            'var_quantity'=>$var_quantity,
            'inaccurate_price'=>$inaccurate_price,
            'request_price'=>$request_price,
            'avail_id'=>$avail_id,
            'file_id'=>$file_id
        );
    }
    private function save_avatar($source_filename,$avatar_folder) {
        $is_url=0;
        if(!file_exists($source_filename)) {
            $ch = curl_init($source_filename);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code !== 200) return 0;
            $is_url=1;
        }

//        echo "still here";

        $this->uFunc->rmdir($avatar_folder);
        if (!file_exists($avatar_folder)) mkdir($avatar_folder,0755,true);
        if(!$this->uFunc->create_empty_index($avatar_folder)) $this->uFunc->error('uCat/common/1670');

        if($is_url) {
            copy($source_filename, $avatar_folder.'/downloaded_from_url');
            $source_filename=$avatar_folder.'/downloaded_from_url';
        }

        try {
            $img = new Imagick($source_filename);

            $img->setImageFormat('jpeg');
            $img->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
            /** @noinspection PhpUnhandledExceptionInspection */
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $img->writeImage($avatar_folder.'/orig.jpg');
            if($is_url) unlink($avatar_folder.'/downloaded_from_url');

            $img->clear();
            $img->destroy();

//            echo "worked";
            return 1;
        } catch (ImagickException $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            return 0;
        }
    }
    public function save_item_avatar($dir,$source_filename,$item_id) {
        $avatar_folder=$dir.$item_id;

        $this->save_avatar($source_filename,$avatar_folder);
        return 1;
    }
    public function save_var_avatar($dir,$source_filename,$item_id,$var_id) {
        $avatar_folder=$dir.$item_id.'-'.$var_id;

        $this->save_avatar($source_filename,$avatar_folder);
    }
    public function set_default_variant($item_id,$var_id,$site_id=site_id) {
        //check if this item exists
        if(!$this->item_exists($item_id)) $this->uFunc->error('uCat/common/1680');
        //check if this var_id is attached to this item_id
        $var=$this->var_id2data($var_id);
        if((int)$item_id!=(int)$var->item_id) $this->uFunc->error('uCat/common/1690');

        //set default_var=0 to all variants of this item
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_variants
            SET
            default_var=0
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1700'/*.$e->getMessage()*/);}

        //set default_var=1 to needed var_id
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_variants
            SET
            default_var=1
            WHERE
            var_id=:var_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1710'/*.$e->getMessage()*/);}

        if((int)$var->img_time) {
            $img_time=$var->img_time;

            //update item image
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_items
                SET
                item_img_time=:img_time 
                WHERE 
                item_id=:item_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':img_time', $img_time,PDO::PARAM_INT);
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1720'/*.$e->getMessage()*/);}

            //copy item orig image to var folder
            $dir='uCat/item_avatars/'.$site_id.'/'; //Адрес директории для сохранения картинки
            $source_filename=$dir.$item_id.'-'.$var_id.'/orig.jpg';
            $this->save_item_avatar($dir,$source_filename,$item_id);
        }
        else {
            $item_id2data=$this->item_id2data($item_id,"item_img_time",$site_id);
            $img_time=(int)$item_id2data->item_img_time;

            if($img_time) {
                //update var image
                try {

                    $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    items_variants
                    SET
                    img_time=:img_time 
                    WHERE 
                    var_id=:var_id AND
                    site_id=:site_id
                    ");
                    $stm->bindParam(':img_time', $img_time, PDO::PARAM_INT);
                    $stm->bindParam(':var_id', $var_id, PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('uCat/common/1730'/*.$e->getMessage()*/);
                }

                //copy item orig image to var folder
                $dir = 'uCat/item_avatars/' . $site_id . '/'; //Адрес директории для сохранения картинки
                $source_filename = $dir . $item_id . '/orig.jpg';
                $this->save_var_avatar($dir, $source_filename, $item_id, $var_id);
            }
        }

        //update item_data
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            item_price=:item_price,
            prev_price=:prev_price,
            item_article_number=:item_article_number,
            inaccurate_price=:inaccurate_price,
            request_price=:request_price,
            item_avail=:item_avail,
            item_img_time=:item_img_time,
            quantity=:quantity,
            file_id=:file_id,
            item_type=:item_type
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_price', $var->price,PDO::PARAM_STR);
            $stm->bindParam(':prev_price', $var->prev_price,PDO::PARAM_STR);
            $stm->bindParam(':item_article_number', $var->item_article_number,PDO::PARAM_STR);
            $stm->bindParam(':inaccurate_price', $var->inaccurate_price,PDO::PARAM_INT);
            $stm->bindParam(':request_price', $var->request_price,PDO::PARAM_INT);
            $stm->bindParam(':item_avail', $var->avail_id,PDO::PARAM_INT);
            $stm->bindParam(':quantity', $var->var_quantity,PDO::PARAM_STR);
            $stm->bindParam(':file_id', $var->file_id,PDO::PARAM_INT);
            $stm->bindParam(':item_type', $var->item_type_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':item_img_time', $img_time,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1740'/*.$e->getMessage()*/);}

        $this->clean_item_arrays($site_id);
        $this->clean_variants_arrays($site_id);

        return $var;
    }

    /**DEPRECATED. NO PDO
     * @param $var_id
     * @param $set_sql
     * @param int $site_id
     */
    public function update_variant($var_id, $set_sql, $site_id=site_id) {

        if(!$this->uCore->query("uCat","UPDATE
        `items_variants`
        SET
        ".$set_sql."
        WHERE
        `var_id`='".$var_id."' AND
        `site_id`='".$site_id."'
        ")) $this->uFunc->error('uCat/common/1750');
        $this->clean_variants_arrays($site_id);
    }
    public function is_default_item_variant($item_id,$var_id,$site_id=site_id) {
        if(!isset($this->is_default_item_variant_ar[$site_id][$item_id][$var_id])) {
            $var=$this->var_id2data($var_id);
            if((int)$var->default_var&&(int)$var->item_id==(int)$item_id) {
                $this->is_default_item_variant_ar[$site_id][$item_id][$var_id]=1;
            }
            else $this->is_default_item_variant_ar[$site_id][$item_id][$var_id]=0;
        }
        return $this->is_default_item_variant_ar[$site_id][$item_id][$var_id];
    }
    public function delete_variant($var_id,$site_id=site_id) {
        //get var's item
        $var=$this->var_id2data($var_id);
        $item_id=$var->item_id;
        $default_var=(int)$var->default_var;


        if(!$this->uCore->query("uCat","DELETE FROM
        `items_variants`
        WHERE
        `var_id`='".$var_id."' AND
        `site_id`='".$site_id."'
        ")) $this->uFunc->error('uCat/common/1760');

        $this->clean_variants_arrays($site_id);
        $this->clean_item_arrays($site_id);

        //get item's variants
        $q_vars=$this->get_item_variants_pdo($item_id);


        if($last_vars=$q_vars->fetch(PDO::FETCH_OBJ)) {//we have more than 1 variants
            if($default_var) {//deleted var was default one. Let's make default another variant

                $new_def_var=$last_vars;//$q_vars->fetch(PDO::FETCH_OBJ);
                $this->set_default_variant($item_id,$new_def_var->var_id);
                //let's update item
                $this->item_update($item_id,array(
                    array('item_avail',$new_def_var->avail_id,PDO::PARAM_INT),
                    array('item_price',$new_def_var->price,PDO::PARAM_STR),
                    array('inaccurate_price',$new_def_var->inaccurate_price,PDO::PARAM_INT),
                    array('request_price',$new_def_var->request_price,PDO::PARAM_INT),
                    array('item_type',$this->var_type_id2data($new_def_var->var_type_id)->item_type_id,PDO::PARAM_INT),
                    array('file_id',$new_def_var->file_id,PDO::PARAM_INT)
                ),array(),$site_id);
            }
        }
        elseif(/*count($last_vars)===1 && */false) {//Only 1 variant left. Let's delete it too. Item has no variants anymore/**TODO-nik87 Костыль. Раньше последний вариант удалялся сам. Сейчас решил его не удалять. Пусть пользователь сам удаляет*/
            $last_var=$last_vars[0];

            if($default_var) {//deleted variant was default. Let's write last present variant's data directly to item.
                //let's update item
                $this->item_update($item_id,array(
                    array('item_avail',$last_var->avail_id,PDO::PARAM_INT),
                    array('item_price',$last_var->price,PDO::PARAM_STR),
                    array('inaccurate_price',$last_var->inaccurate_price,PDO::PARAM_INT),
                    array('request_price',$last_var->request_price,PDO::PARAM_INT),
                    array('item_type',$this->var_type_id2data($last_var->var_type_id)->item_type_id,PDO::PARAM_INT),
                    array('file_id',$last_var->file_id,PDO::PARAM_INT)
                ),array(),$site_id);
            }

            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
                items_variants
                WHERE
                var_id=:var_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_id', $last_var->var_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1770'/*.$e->getMessage()*/);}
        }
        $this->clean_variants_arrays($site_id);
        $this->clean_item_arrays($site_id);

        if(!$this->has_variants($item_id)) {
            $this->item_update($item_id,array(
                array('has_variants',0,PDO::PARAM_INT)
            ),array(),$site_id);
        }
    }
    public function get_new_var_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            var_id 
            FROM 
            items_variants 
            WHERE 
            site_id=:site_id
            ORDER BY var_id DESC
            LIMIT 1
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->var_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1780'/*.$e->getMessage()*/);}
        return 1;
    }
    public function var_article_number_exists($item_article_number,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id,
            var_id
            FROM
            items_variants
            WHERE
            item_article_number=:item_article_number AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return $qr=$stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1790'/*.$e->getMessage()*/);}

        return 0;
    }

    public function get_options_with_values($var_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            option_name,
            value
            FROM 
            option_values
            JOIN
            variants_options_values
            ON
            option_values.value_id=variants_options_values.value_id AND
            option_values.option_id=variants_options_values.option_id AND
            option_values.site_id=variants_options_values.site_id
            JOIN 
            variant_options
            ON
            option_values.option_id=variant_options.option_id AND
            option_values.site_id=variant_options.site_id
            JOIN
            items_variants
            ON
            variants_options_values.var_id=items_variants.var_id AND
            variants_options_values.site_id=items_variants.site_id
            JOIN
            items_options
            ON
            items_options.item_id=items_variants.item_id AND
            option_values.option_id=items_options.option_id AND
            option_values.site_id=items_options.site_id
            WHERE 
            variants_options_values.var_id=:var_id AND
            variants_options_values.site_id=:site_id
            ");
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1800'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_item_options($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            variant_options.option_id,
            option_name,
            option_type,
            option_display_style
            FROM 
            variant_options
            JOIN 
            items_options
            ON
            variant_options.option_id=items_options.option_id AND
            variant_options.site_id=items_options.site_id
            WHERE 
            item_id=:item_id AND
            variant_options.site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1810'/*.$e->getMessage()*/);}
        return false;
    }
    public function get_option_values($item_id,$option_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT 
            option_values.value_id,
            value,
            color
            FROM
            variants_options_values
            JOIN
            option_values
            ON
            variants_options_values.value_id=option_values.value_id AND
            variants_options_values.site_id=option_values.site_id
            JOIN
            variant_options
            ON
            variants_options_values.option_id=variant_options.option_id AND
            variants_options_values.site_id=variant_options.site_id
            JOIN
            items_options
            ON
            variant_options.option_id=items_options.option_id AND
            variant_options.site_id=items_options.site_id
            JOIN items_variants
            ON
            items_variants.var_id=variants_options_values.var_id AND
            items_variants.site_id=option_values.site_id
            WHERE 
            variants_options_values.option_id=:option_id AND
            items_variants.item_id=:item_id AND
            variant_options.site_id=:site_id
            ");
            $stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1820'/*.$e->getMessage()*/);}
        return false;
    }
    public function set_default_value_for_option_for_all_item_variants($option_id,$item_id,$site_id=site_id) {
        //get default value
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            value_id 
            FROM 
            option_values 
            WHERE 
            option_id=:option_id AND
            site_id=:site_id
            ORDER BY value_id DESC
            LIMIT 1
            ");
            $stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1830'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
                    option_values (
                    option_id, 
                    value, 
                    site_id
                    ) VALUES (
                    :option_id, 
                    'Новое значение', 
                    :site_id
                    )
                    ");
                $stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1840'/*.$e->getMessage()*/);}

            $value_id=(int)$this->uFunc->pdo("uCat")->lastInsertId();
        }
        else {
            $value_id=(int)$qr->value_id;
        }

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            var_id 
            FROM 
            items_variants 
            WHERE
            item_id=:item_id AND 
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1850'/*.$e->getMessage()*/);}


        while($var=$qr=$stm->fetch(PDO::FETCH_OBJ)) {
            try {

                $stm1=$this->uFunc->pdo("uCat")->prepare("REPLACE INTO 
                variants_options_values (
                var_id, 
                option_id, 
                value_id, 
                site_id
                ) VALUES (
                :var_id, 
                :option_id, 
                :value_id, 
                :site_id
                ) 
                ");
                $stm1->bindParam(':var_id', $var->var_id,PDO::PARAM_INT);
                $stm1->bindParam(':option_id', $option_id,PDO::PARAM_INT);
                $stm1->bindParam(':value_id', $value_id,PDO::PARAM_INT);
                $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1860'/*.$e->getMessage()*/);}
        }


    }
    public function update_all_item_variants_with_options_title($item_id,$site_id=site_id) {
        //get item_title
        $item_info=$this->item_id2data($item_id,"item_title",$site_id);
        $item_title=$item_info->item_title;
        //get item's variants
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            var_id,
            var_type_id 
            FROM 
            items_variants 
            WHERE 
            item_id=:item_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1870'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        while($var=$stm->fetch(PDO::FETCH_OBJ)) {
            $options_obj=$this->get_options_with_values($var->var_id);
            $var_title_addition=". (";

            while ($option = $options_obj->fetch(PDO::FETCH_OBJ)) {
                $var_title_addition.=$option->option_name.": ".$option->value.". ";
            }
            $var_title_addition.=")";

            $var_type_title=$item_title.$var_title_addition;
            try {

                $stm1=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                items_variants_types
                SET
                var_type_title=:var_type_title
                WHERE 
                var_type_id=:var_type_id AND
                site_id=:site_id
                ");
                $stm1->bindParam(':var_type_title', $var_type_title,PDO::PARAM_STR);
                $stm1->bindParam(':var_type_id', $var->var_type_id,PDO::PARAM_INT);
                $stm1->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm1->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1880'/*.$e->getMessage()*/);}
        }
    }
    public function get_connected_values($value_id,$item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            value_id
            FROM
                 variants_options_values
            JOIN
                     items_variants
            ON
                items_variants.var_id=variants_options_values.var_id AND
                    items_variants.site_id=variants_options_values.site_id
            JOIN
                     items_options
            ON
                items_options.option_id=variants_options_values.option_id AND
                items_variants.item_id=items_options.item_id AND
                    items_variants.site_id=items_options.site_id
            WHERE
                value_id!=:value_id AND
                items_options.item_id=:item_id AND
                variants_options_values.var_id IN (
            SELECT DISTINCT
                   items_variants.var_id
            FROM
                 variants_options_values
            JOIN
                     items_variants
            ON
                variants_options_values.var_id=items_variants.var_id AND
                    variants_options_values.site_id=items_variants.site_id
            WHERE
                item_id=:item_id AND
              value_id=:value_id AND
              variants_options_values.site_id=:site_id)
            ");
            $stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1890'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    public function var_id2options_values($var_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            option_id, 
            value_id 
            FROM 
            variants_options_values 
            WHERE 
            var_id=:var_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1900'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    public function get_item_variants_options_values($item_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            value_id,
            var_type_id,
            variants_options_values.var_id,
            price,
            prev_price,
            var_quantity,
            item_article_number,
            img_time,
            inaccurate_price,
            request_price,
            avail_id
            FROM
            variants_options_values
            JOIN
            items_variants
            ON
            variants_options_values.var_id=items_variants.var_id AND
            variants_options_values.site_id=items_variants.site_id
            WHERE
            item_id=:item_id AND
            variants_options_values.site_id=:site_id
            ORDER BY 
            variants_options_values.var_id ,
            value_id
            ");
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/1910'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }
    public function get_site_options($q_select="option_id",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            $q_select 
            FROM 
            variant_options 
            WHERE 
            site_id=:site_id
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1583231974'/*.$e->getMessage()*/,1);}
        return false;
    }
    public function variant_id_option_id2option_value($variant_id,$option_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            `value`
            FROM 
            option_values
            LEFT JOIN 
            variants_options_values
            ON
            option_values.value_id=variants_options_values.value_id AND
            option_values.site_id=variants_options_values.site_id
            WHERE
            var_id=:variant_id AND
            option_values.option_id=:option_id AND
            option_values.site_id=:site_id
            ");
            $stm->bindParam(':variant_id', $variant_id,PDO::PARAM_INT);
            $stm->bindParam(':option_id', $option_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->value;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return "";
    }

    //AVAILABILITY
    public function get_avails($site_id=site_id) {
        if(!isset($this->get_avails_ar[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `avail_id`,
            `avail_label`,
            `avail_descr`,
            `avail_type_id`
            FROM
            `u235_items_avail_values`
            WHERE
            `site_id`='".$site_id. "'
            ORDER BY
            `avail_label`
            ")) $this->uFunc->error('uCat/common/1920');
            $this->get_avails_ar[$site_id]=$query;
        }
        else mysqli_data_seek($this->get_avails_ar[$site_id],0);
        return $this->get_avails_ar[$site_id];
    }
    public function get_avails_json($avail_id,$site_id=site_id) {
        if(!isset($this->get_avails_json_ar[$site_id][$avail_id])) {
            $q_avails=$this->get_avails();
            $avail_json='{';

            $avail=$q_avails->fetch_object();
            for($i=0;$avail;$i++) {
                $avail_json.='"'.$i.'":{
                            "val":"'.$avail->avail_id.'",
                            "label":"'.rawurlencode($avail->avail_label).'",
                            "selected":"'.((int)$avail_id==(int)$avail->avail_id?'1':'0').'"
                            }';

                if($avail=$q_avails->fetch_object()) $avail_json.=',';
            }
            $avail_json.='}';
            $this->get_avails_json_ar[$site_id][$avail_id]=$avail_json;
        }
        return $this->get_avails_json_ar[$site_id][$avail_id];
    }
    public function avail_id2avail_data($avail_id,$site_id=site_id) {
        if(!isset($this->avail_id2avail_data_ar[$site_id][$avail_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                avail_type_id,
                avail_label,
                avail_descr
                FROM
                u235_items_avail_values
                WHERE
                avail_id=:avail_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':avail_id', $avail_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/1930'/*.$e->getMessage()*/);}


            /** @noinspection PhpUndefinedVariableInspection */
            $this->avail_id2avail_data_ar[$site_id][$avail_id]=$stm->fetch(PDO::FETCH_OBJ);
        }
        return $this->avail_id2avail_data_ar[$site_id][$avail_id];
    }
    public function get_any_dontshow_avail_id($site_id=site_id) {
        if(!isset($this->get_any_dontshow_avail_id_ar[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `avail_id`
            FROM
            `u235_items_avail_values`
            WHERE
            `avail_type_id`='2' AND
            `site_id`='".$site_id."'
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/1940');
            if(mysqli_num_rows($query)) {

                $qr=$query->fetch_object();
                $this->get_any_dontshow_avail_id_ar[$site_id]=$qr->avail_id;
            }
            else {
                $this->get_any_dontshow_avail_id_ar[$site_id]=$this->create_avail_value($avail_type_id=2,$avail_label="Не отображать",$avail_descr="Товар не отображается на сайте",$site_id=site_id);
            }
        }
        return $this->get_any_dontshow_avail_id_ar[$site_id];
    }
    public function get_any_available_avail_id($site_id=site_id) {
        if(!isset($this->get_any_available_avail_id_ar[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `avail_id`
            FROM
            `u235_items_avail_values`
            WHERE
            `avail_type_id`='1' AND
            `site_id`='".$site_id."'
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/1950');
            if(mysqli_num_rows($query)) {

                $qr=$query->fetch_object();
                $this->get_any_available_avail_id_ar[$site_id]=$qr->avail_id;
            }
            else {
                $this->get_any_available_avail_id_ar[$site_id]=$this->create_avail_value(1,"В наличии","Товар можно купить",$site_id);
            }
        }
        return $this->get_any_available_avail_id_ar[$site_id];
    }
    public function avail_type_id2class($avail_type_id) {
        switch((int)$avail_type_id) {
            case 1: return 'text-success';
            case 2: return 'text-muted';
            case 3: return 'text-danger';
            case 4: return 'text-warning';
            case 5: return 'text-info';
        }
        return '';
    }

    //ITEM TYPE
    public function item_type_is_used($type_id,$site_id=site_id) {
        if(!isset($this->item_type_is_used_ar[$site_id][$type_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `item_type`
            FROM
            `u235_items`
            WHERE
            `item_type`='".$type_id."' AND
            `site_id`='".$site_id."'
            LIMIT 1
            ")) $this->uFunc->error('uCat/common/1960');
            if(mysqli_num_rows($query)) {
                return $this->item_type_is_used_ar[$site_id][$type_id]=1;
            }
            else {

                if(!$query=$this->uCore->query("uCat","SELECT
                `item_type_id`
                FROM
                `items_variants_types`
                WHERE
                `item_type_id`='".$type_id."' AND
                `site_id`='".$site_id."'
                LIMIT 1
                ")) $this->uFunc->error('uCat/common/1970');
                return $this->item_type_is_used_ar[$site_id][$type_id]=mysqli_num_rows($query);
            }
        }
        return $this->item_type_is_used_ar[$site_id][$type_id];
    }
    public function item_type_exists($type_id,$site_id=site_id) {
        if(!isset($this->item_type_exists_ar[$site_id][$type_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `type_id`
            FROM
            `items_types`
            WHERE
            `type_id`='".$type_id."' AND
            `site_id`='".$site_id."'
            ")) $this->uFunc->error('uCat/common/1980');
            $this->item_type_exists_ar[$site_id][$type_id]=mysqli_num_rows($query);
        }
        return $this->item_type_exists_ar[$site_id][$type_id];
    }
    public $q_item_types;
    public function get_item_types($site_id=site_id) {
        if(!isset($this->q_item_types[$site_id])) {

            if(!$query=$this->uCore->query("uCat","SELECT
            `base_type_id`,
            `type_id`,
            `type_title`
            FROM
            `items_types`
            WHERE
            `site_id`='".$site_id."'
            ")) $this->uFunc->error('uCat/common/1990');
            $this->q_item_types[$site_id]=$query;
        }
        else mysqli_data_seek($this->q_item_types[$site_id],0);
        return $this->q_item_types[$site_id];
    }

    //CHECKOUT
    public function checkout_user_skipped_reg() {
        $ses_id=$this->uCore->uSes->get_val('ses_id');
        $site_id=site_id;
        try {

            $stm=$this->uCore->pdo("uCat")->prepare("SELECT site_id FROM order_nologin_user_data WHERE ses_id=:ses_id AND site_id=:site_id");
            $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2000');}


        /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    //CONTRACTORS
    public function create_default_cont($user_id,$cont_name=false,$site_id=site_id) {
        if(!isset($this->uAuth)) $this->uAuth=new \uAuth\common($this->uCore);
        $user_data=$this->uAuth->user_id2user_data($user_id,"firstname,lastname,email,cellphone");
        $email=$user_data->email;
        $phone=$user_data->cellphone;
        if(!$cont_name) {
            $cont_name=$user_data->firstname." ".$user_data->lastname;
        }
        $cont_id=$this->get_new_cont_id($site_id);
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO contractors (
            cont_id,
            cont_name,
            def_value,
            user_id,
            site_id
            ) VALUES (
            :cont_id,
            :cont_name,
            1,
            :user_id,
            :site_id
            )
            ");
            $stm->bindParam(':cont_id', $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':cont_name', $cont_name,PDO::PARAM_STR);
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2010"/*.$e->getMessage()*/);}

        $this->cont_data_populate_default_personal($cont_id,$cont_name,$email,$phone,$site_id);
        unset($this->user_id2default_contractor_id_ar,$this->user_id2cont_num_ar,$this->user_id2cont_query_ar);

        /** @noinspection PhpUndefinedVariableInspection */
        return $cont_id;
    }
    public function create_new_cont($user_id,$cont_name,$cont_type,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO contractors (
            cont_id,
            cont_name,
            cont_type,
            user_id,
            site_id
            ) VALUES (
            :cont_id,
            :cont_name,
            :cont_type,
            :user_id,
            :site_id
            )
            ");
            $cont_id=$this->get_new_cont_id($site_id);
            $stm->bindParam(':cont_id',    $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':cont_name',  $cont_name,PDO::PARAM_STR);
            $stm->bindParam(':cont_type',  $cont_type,PDO::PARAM_INT);
            $stm->bindParam(':user_id',    $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id',    $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2020"/*.$e->getMessage()*/);}
        unset($this->user_id2default_contractor_id_ar,$this->user_id2cont_num_ar,$this->user_id2cont_query_ar);

        if((int)$cont_type) {
            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cont_data_populate_default_company($cont_id);
        }
        else {
            /** @noinspection PhpUndefinedVariableInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $this->cont_data_populate_default_personal($cont_id);
        }

        /** @noinspection PhpUndefinedVariableInspection */
        return $cont_id;
    }
    public function update_cont($cont_id,$update_ar,$site_id=site_id) {
        /*$update_ar=array(
            array('cont_name',$cont_name,PDO_DATA_TYPE),
            array('cont_type',$cont_type,PDO_DATA_TYPE),
        );*/
        try {

            $sql_update='';
            for($i=0;$i<count($update_ar);$i++) {
                $sql_update.=$update_ar[$i][0].'=:'.$update_ar[$i][0].',';
            }
            $sql_update=substr($sql_update,0,strlen($sql_update)-1);


            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE contractors
            SET
            ".$sql_update."
            WHERE
            cont_id=:cont_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cont_id',    $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id',    $site_id,PDO::PARAM_INT);

            for($i=0;$i<count($update_ar);$i++) {
                $stm->bindParam(':'.$update_ar[$i][0],  $update_ar[$i][1],$update_ar[$i][2]);
            }
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2030"/*.$e->getMessage()*/);}
        unset($this->user_id2default_contractor_id_ar,$this->user_id2cont_num_ar,$this->user_id2cont_query_ar);

        /** @noinspection PhpUndefinedVariableInspection */
        return $cont_id;
    }
    public function update_cont_data($cont_id,$update_ar,$site_id=site_id) {
        /*$update_ar=array(
            array('cont_name',$cont_name,PDO_DATA_TYPE),
            array('cont_type',$cont_type,PDO_DATA_TYPE),
        );*/
        try {

            $sql_update='';
            for($i=0;$i<count($update_ar);$i++) {
                $sql_update.=$update_ar[$i][0].'=:'.$update_ar[$i][0].',';
            }
            $sql_update=substr($sql_update,0,strlen($sql_update)-1);


            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE contractors_data
            SET
            ".$sql_update."
            WHERE
            cont_id=:cont_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cont_id',    $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id',    $site_id,PDO::PARAM_INT);

            for($i=0;$i<count($update_ar);$i++) {
                $stm->bindParam(':'.$update_ar[$i][0],  $update_ar[$i][1],$update_ar[$i][2]);
            }
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2040"/*.$e->getMessage()*/);}
        unset($this->user_id2default_contractor_id_ar,$this->user_id2cont_num_ar,$this->user_id2cont_query_ar);

        /** @noinspection PhpUndefinedVariableInspection */
        return $cont_id;
    }
    public function set_def_cont($cont_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE contractors
            SET
            def_value=0
            WHERE
            site_id=:site_id
            ");
            $stm->bindParam(':site_id',    $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2050"/*.$e->getMessage()*/);}

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE contractors
            SET
            def_value=1
            WHERE
            cont_id=:cont_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':cont_id',    $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id',    $site_id,PDO::PARAM_INT);

            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2060"/*.$e->getMessage()*/);}
        unset($this->user_id2default_contractor_id_ar,$this->user_id2cont_num_ar,$this->user_id2cont_query_ar);

        /** @noinspection PhpUndefinedVariableInspection */
        return $cont_id;
    }
    public function cont_id2cont_info($cont_id=0,$cont_data='cont_name',$where_ar=array(),$site_id=site_id) {
        /*$where_ar=array(
          array('cont_name',$value,PDO::PARAM_INT),
          array('cont_name',$value,PDO::PARAM_INT)
        );*/
        try {
            if(!count($where_ar)) {
                $where_ar=array(
                    array('cont_id',$cont_id,PDO::PARAM_INT)
                );
            }
            $q_where='';
            for($i=0;$i<count($where_ar);$i++) {
                $q_where.=$where_ar[$i][0].'=:'.$where_ar[$i][0] .' AND ';
            }


            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT ".$cont_data." FROM contractors WHERE ".$q_where." site_id=:site_id");
            for($i=0;$i<count($where_ar);$i++) {
                $stm->bindParam(':'.$where_ar[$i][0], $where_ar[$i][1],$where_ar[$i][2]);
            }
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2070"/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;

    }
    public function cont_id2cont_data($cont_id=0,$cont_data='firstname',$where_ar=array(),$site_id=site_id) {
        /*$where_ar=array(
          array('cont_name',$value,PDO::PARAM_INT),
          array('cont_name',$value,PDO::PARAM_INT)
        );*/
        try {
            if(!count($where_ar)) {
                $where_ar=array(
                    array('cont_id',$cont_id,PDO::PARAM_INT)
                );
            }
            $q_where='';
            for($i=0;$i<count($where_ar);$i++) {
                $q_where.=$where_ar[$i][0].'=:'.$where_ar[$i][0] .' AND ';
            }


            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT ".$cont_data." FROM contractors_data WHERE ".$q_where." site_id=:site_id");
            for($i=0;$i<count($where_ar);$i++) {
                $stm->bindParam(':'.$where_ar[$i][0], $where_ar[$i][1],$where_ar[$i][2]);
            }
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error("uCat/common/2080".$e->getMessage());}


        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;

    }
    public function cont_data_populate_default_personal($cont_id,$cont_name='Физическое лицо',$email="",$phone="",$site_id=site_id) {
        $cont_name=trim($cont_name);
        if(!strlen($cont_name)) $cont_name='Физическое лицо';

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                    contractors_data (
                    cont_id,
                    firstname,
                    secondname,
                    lastname,
                    birthdate,
                    email,
                    phone,
                    site_id
                    ) VALUES (
                    :cont_id,
                    :firstname,
                    '',
                    '',
                    0,
                    :email,
                    :phone,
                    :site_id
                    )");
            $stm->bindParam(':cont_id', $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':firstname', $cont_name,PDO::PARAM_STR);
            $stm->bindParam(':email', $email,PDO::PARAM_STR);
            $stm->bindParam(':phone', $phone,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        } /** @noinspection PhpUndefinedClassInspection */ catch(PDOException $e) {$this->uFunc->error("uCat/common/2090"/*.$e->getMessage()*/);}
    }
    public function cont_data_populate_default_company($cont_id,$cont_name='Компания',$site_id=site_id) {
        $cont_name=trim($cont_name);
        if(!strlen($cont_name)) $cont_name='Компания';

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                    contractors_data (
                    cont_id,
                    firstname,
                    legal_address,
                    actual_address,
                    tax_info_1,
                    tax_info_2,
                    bank_name,
                    bank_info_1,
                    bank_info_2,
                    bank_info_3,
                    phone,
                    email,
                    site_id
                    ) VALUES (
                    :cont_id,
                    :firstname,
                    '',
                    '',
                    0,
                    0,
                    '',
                    0,
                    0,
                    0,
                    '',
                    '',
                    :site_id
                    )");
            $stm->bindParam(':cont_id', $cont_id,PDO::PARAM_INT);
            $stm->bindParam(':firstname', $cont_name,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        } /** @noinspection PhpUndefinedClassInspection */ catch(PDOException $e) {$this->uFunc->error("uCat/common/2100"/*.$e->getMessage()*/);}
    }
    public function user_id2default_contractor_id($user_id,$site_id=site_id) {
        if(!isset($this->user_id2default_contractor_id_ar[$user_id][$site_id])) {
            //get default cont_id
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT cont_id FROM contractors WHERE status IS NULL AND user_id=:user_id AND def_value=1 AND site_id=:site_id");
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error("uCat/common/2110");}

            /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection */
            $res=$stm->fetch(PDO::FETCH_OBJ);
            if($res) $this->user_id2default_contractor_id_ar[$user_id][$site_id]=(int)$res->cont_id;
            else {//if there are no default cont_id - we assign first cont as default cont_id
                try {

                    $stm=$this->uFunc->pdo("uCat")->prepare("SELECT cont_id FROM contractors WHERE status IS NULL AND user_id=:user_id AND site_id=:site_id LIMIT 1");
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error("uCat/common/2120");}


                $res=$stm->fetch(PDO::FETCH_OBJ);
                if($res) {
                    try {

                        $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE contractors SET
                        def_value=1
                        WHERE
                        cont_id=:cont_id");
                        $cont_id=$res->cont_id;
                        $stm->bindParam(':cont_id', $cont_id,PDO::PARAM_INT);
                        $stm->execute();
                    }
                    catch(PDOException $e) {$this->uFunc->error("uCat/common/2130");}

                    $this->user_id2default_contractor_id_ar[$user_id][$site_id]=(int)$res->cont_id;
                }
                //if there are no contractors at all - we return 0
                else $this->user_id2default_contractor_id_ar[$user_id][$site_id]=0;
            }
        }
        return $this->user_id2default_contractor_id_ar[$user_id][$site_id];
    }
    public function user_id2cont_num($user_id,$site_id=site_id) {
        if(!isset($this->user_id2cont_num_ar[$user_id][$site_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT COUNT(cont_id) FROM contractors WHERE status IS NULL AND user_id=:user_id AND site_id=:site_id");
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error("uCat/common/2140");}

            /** @noinspection PhpUndefinedVariableInspection, PhpUndefinedMethodInspection */
            $this->user_id2cont_num_ar[$user_id][$site_id]=$stm->fetchColumn();
        }
        return $this->user_id2cont_num_ar[$user_id][$site_id];
    }
    public function user_id2cont_query($user_id,$q_data,$site_id=site_id) {//ONLY ACTIVE CONTRACTORS!!!!
        unset($this->user_id2cont_query_ar);
        if(!isset($this->user_id2cont_query_ar[$user_id][$q_data][$site_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT ".$q_data." FROM contractors WHERE status IS NULL AND user_id=:user_id AND site_id=:site_id");
                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error("uCat/common/2150");}
            /** @noinspection PhpUndefinedVariableInspection */
            $this->user_id2cont_query_ar[$user_id][$q_data][$site_id]=$stm;
        }
        return $this->user_id2cont_query_ar[$user_id][$q_data][$site_id];
    }

    //ORDER
    public function get_order_id($ses_id=0,$site_id=site_id) {
        if(!isset($this->uSes)) $this->uSes=new uSes($this->uCore);
        if(!isset($this->get_order_id_var)) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                order_id,
                order_status
                FROM 
                orders 
                WHERE 
                ses_id=:ses_id AND 
                site_id=:site_id
                ");
                if(!$ses_id) $ses_id=$this->uSes->get_val('ses_id');
                $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                $qr=$stm->fetch(PDO::FETCH_OBJ);
                if($qr) {
                    if($qr->order_status==="new"||$qr->order_status==="items selected") {
                        $this->get_order_id_var=$qr->order_id;
                    }
                    else {
                        $this->order_update($qr->order_id,[['ses_id',"",PDO::PARAM_STR]],"",$site_id,1588599177);
                        $this->get_order_id_var=$this->create_order($site_id);
                    }
                }
                else $this->get_order_id_var=$this->create_order($site_id);
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2160'/*.$e->getMessage()*/);}
        }
        return $this->get_order_id_var;
    }
    public function is_order_exists($ses_id=0,$site_id=site_id) {
        if(!isset($this->uSes)) $this->uSes=new uSes($this->uCore);
        if(isset($this->get_order_id_var)) return 1;
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT order_id FROM orders WHERE ses_id=:ses_id AND site_id=:site_id");
            if(!$ses_id) $ses_id=$this->uSes->get_val('ses_id');
            $stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            $qr=$stm->fetch(PDO::FETCH_OBJ);
            if($qr) {
                $this->get_order_id_var=$qr->order_id;
                return 1;
            }
            else return 0;
        }
        catch(PDOException $e) {return $this->uFunc->error('uCat/common/2170'/*.$e->getMessage()*/);}
    }
    public function order_id2data($order_id,$q_data="order_id",$site_id=site_id) {
        if(!isset($this->order_id2data_ar[$site_id][$q_data][$order_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT ".$q_data." FROM orders WHERE order_id=:order_id AND site_id=:site_id");
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();


                $this->order_id2data_ar[$site_id][$q_data][$order_id]=$stm->fetch(PDO::FETCH_OBJ);
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2180'/*.$e->getMessage()*/);}
        }
        return $this->order_id2data_ar[$site_id][$q_data][$order_id];
    }

    /**
     * Updates information about order
     * receives $update_ar as an array of key-values:
     * $update_ar=[
    ['cont_name',$cont_name,PDO_DATA_TYPE],
    ['cont_type',$cont_type,PDO_DATA_TYPE],
    ]
     * @param int $order_id
     * @param array $update_ar
     * @param string $q_where
     * @param int $site_id
     * @param int $call_place - ID of place that calls this method. For debug purposes
     */
    public function order_update($order_id,$update_ar,$q_where="",$site_id=site_id,$call_place=0) {
        /*
            $update_ar=[
            ['cont_name',$cont_name,PDO_DATA_TYPE],
            ['cont_type',$cont_type,PDO_DATA_TYPE],
        ]
        */
        $no_initial_where=0;
        if($q_where=="") {
            $no_initial_where=1;
            $q_where='order_id=:order_id AND site_id=:site_id';
        }
        unset($this->order_id2data_ar[$site_id]);
        try {
            $sql_update='';
            foreach ($update_ar as $iValue) {
                $sql_update.= $iValue[0].'=:'. $iValue[0].',';
            }
            $sql_update=substr($sql_update,0,strlen($sql_update)-1);

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE orders SET
            ".$sql_update."
            WHERE
            ".$q_where."
            ");
            if($no_initial_where) {
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->bindParam(':order_id', $order_id, PDO::PARAM_INT);
            }

            foreach ($update_ar as $iValue) {
                $stm->bindParam(':'. $iValue[0],  $iValue[1], $iValue[2]);
            }
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1588598426 - '.$call_place.' '.$e->getMessage());}
    }
    public function order_get_every_item_count($order_id,$site_id=site_id) {
        if(!isset($this->order_get_every_item_count_ar[$site_id][$order_id])) {
            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_id,
                var_id,
                item_count
                FROM
                orders_items
                WHERE
                order_id=:order_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
                $this->order_get_every_item_count_ar[$site_id][$order_id]=$stm;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2200'/*.$e->getMessage()*/);}
        }
        return $this->order_get_every_item_count_ar[$site_id][$order_id];
    }
    public function order_add_item($order_id,$item_id,$var_id=0,$quantity=1,$site_id=site_id) {
        $enable_var_options = (int)$this->uFunc->getConf("enable_var_options", "uCat");
        $item_quantity_show = (int) $this->uFunc->getConf('item_quantity_show','uCat');

        $quantity = (int)$quantity;
        if(!$var_id) {//check if item has variants
            if($this->has_variants($item_id)) {//get default var_id
                $var_id=(int)$this->item_id2default_variant_id($item_id);
            }
        }

        $item_data=$this->item_id2data($item_id,"
            item_title,
            item_price,
            inaccurate_price,
            request_price,
            item_type,
            file_id,
            item_avail AS avail_id,
            quantity AS var_quantity
            ",$site_id);

        if($var_id) {
            $var_data=$this->var_id2data($var_id,$site_id);

            $var_type_title = uString::sql2text($this->var_type_id2data($var_data->var_type_id)->var_type_title);
            if($var_type_title!=="") {
                if ($enable_var_options) $item_data->item_title = $var_type_title;
                else $item_data->item_title .= " (" . $var_type_title . ")";
            }

            $item_data->item_price=$var_data->price;
            $item_data->inaccurate_price=$var_data->inaccurate_price;
            $item_data->request_price=$var_data->request_price;
            $var_type_id2data=$this->var_type_id2data($var_data->var_type_id,$site_id);
            $item_data->item_type=$var_type_id2data->item_type_id;
            $item_data->file_id=$var_data->file_id;
            $item_data->avail_id=$var_data->avail_id;
            $item_data->var_quantity=$var_data->var_quantity;
        }

        if($item_data) {
            $avail_data=$this->avail_id2avail_data($item_data->avail_id,"avail_type_id");
            if($avail_data) {
                $avail_type_id=(int)$avail_data->avail_type_id;
                if($avail_type_id===2||$avail_type_id===3||$avail_type_id===4) return 0;
            }
            if((int)$item_data->request_price) return 0;
            if((int)$item_data->var_quantity&&$item_quantity_show) return 0;
        }
        else return 0;

        if($this->order_check_if_item_is_added($item_id,$order_id,$var_id)>=0) {//increase item_count
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                orders_items
                SET
                item_count=item_count+:quantity
                WHERE
                item_id=:item_id AND
                order_id=:order_id AND
                var_id=:var_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':quantity', $quantity,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2210'/*.$e->getMessage()*/);}

            $this->increase_added_to_cart_counter($item_id,$site_id);
            $this->clean_order_variables($site_id);
            return 1;
        }
        else {//add new item to cart
            if($this->order_check_if_item_can_be_added($item_id,$var_id,$site_id)) {
                try {

                    $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                    orders_items (
                    order_id,
                    item_id,
                    var_id,
                    item_count,
                    item_title,
                    item_price,
                    inaccurate_price,
                    request_price,
                    item_type,
                    file_id,
                    site_id
                    ) VALUES (
                    :order_id,
                    :item_id,
                    :var_id,
                    :item_count,
                    :item_title,
                    :item_price,
                    :inaccurate_price,
                    :request_price,
                    :item_type,
                    :file_id,
                    :site_id
                    )
                    ");
                    $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                    $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                    $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                    $stm->bindParam(':item_count', $quantity,PDO::PARAM_INT);
                    $stm->bindParam(':item_title', $item_data->item_title,PDO::PARAM_INT);
                    $stm->bindParam(':item_price', $item_data->item_price,PDO::PARAM_INT);
                    $stm->bindParam(':inaccurate_price', $item_data->inaccurate_price,PDO::PARAM_INT);
                    $stm->bindParam(':request_price', $item_data->request_price,PDO::PARAM_INT);
                    $stm->bindParam(':item_type', $item_data->item_type,PDO::PARAM_INT);
                    $stm->bindParam(':file_id', $item_data->file_id,PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    $stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uCat/common/2220'/*.$e->getMessage()*/);}

                $this->increase_added_to_cart_counter($item_id,$site_id);
                $this->clean_order_variables($site_id);
                return 1;
            }
        }
        return 0;
    }
    public function order_delete_item($order_id,$item_id=0,$var_id=0,$site_id=site_id) {
        if($item_id) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
                orders_items
                WHERE
                item_id=:item_id AND
                order_id=:order_id AND
                var_id=:var_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2230'/*.$e->getMessage()*/);}
        }
        else {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("DELETE FROM
                orders_items
                WHERE
                order_id=:order_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2240'/*.$e->getMessage()*/);}
        }
    }
    public function order_set_item_count($order_id,$item_id,$item_count,$var_id=0,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            orders_items
            SET
            item_count=:item_count
            WHERE
            order_id=:order_id AND
            item_id=:item_id AND
            var_id=:var_id AND
            site_id=:site_id
            ");
            $stm->bindParam(':item_count', $item_count,PDO::PARAM_INT);
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2250'/*.$e->getMessage()*/);}

        $this->clean_order_variables($site_id);
    }
    public function order_get_items($order_id,$site_id=site_id) {
        if(!isset($this->order_get_items_ar[$site_id][$order_id])) {
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
                item_id
                FROM
                orders_items
                WHERE
                order_id=:order_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
                $this->order_get_items_ar[$site_id][$order_id]=$stm;
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2260'/*.$e->getMessage()*/);}
        }
        return $this->order_get_items_ar[$site_id][$order_id];
    }
    public function order_has_items($order_id,$site_id=site_id) {
        try {

            $stm=$this->uCore->pdo("uCat")->prepare("SELECT 
            item_id 
            FROM 
            orders_items
            WHERE 
            order_id=:order_id AND 
            site_id=:site_id 
            LIMIT 1");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2270');}


        /** @noinspection PhpUndefinedMethodInspection, PhpUndefinedVariableInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }
    public function order_count_items_price($order_id,$site_id=site_id) {
        try {

            $stm=$this->uCore->pdo("uCat")->prepare("SELECT 
            SUM(item_price*item_count) as total
            FROM 
            orders_items
            WHERE 
            order_id=:order_id AND 
            site_id=:site_id 
            LIMIT 1");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm->fetch(PDO::FETCH_OBJ)->total;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2280');}
        return 0;
    }
    public function order_has_real_items($order_id,$site_id=site_id) {
        //Items without variants
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            orders_items.item_id
            FROM
            orders_items
            JOIN
            u235_items
            ON
            orders_items.item_id=u235_items.item_id AND
            orders_items.site_id=u235_items.site_id
            JOIN 
            items_types
            ON
            u235_items.item_type=type_id AND
            u235_items.site_id=items_types.site_id
            WHERE
            order_id=:order_id AND
            base_type_id=0 AND
            var_id=0 AND
            orders_items.site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2290'/*.$e->getMessage()*/);}
        //Items with variants
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            orders_items.item_id
            FROM
            orders_items
            JOIN 
            items_variants
            ON
            orders_items.item_id=items_variants.item_id AND
            orders_items.var_id=items_variants.var_id AND
            orders_items.site_id=items_variants.site_id
            JOIN
            items_variants_types
            ON 
            items_variants.var_type_id=items_variants_types.var_type_id AND
            items_variants.site_id=items_variants_types.site_id
            JOIN 
            items_types
            ON
            item_type_id=base_type_id AND
            items_variants_types.site_id=items_types.site_id
            WHERE
            order_id=:order_id AND
            base_type_id=0 AND
            orders_items.var_id!=0 AND
            orders_items.site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2300'/*.$e->getMessage()*/);}

        return 0;
    }
    public function order_has_inaccurate_price_items($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            orders_items.item_id
            FROM
            orders_items
            WHERE
            order_id=:order_id AND
            orders_items.site_id=:site_id AND
            inaccurate_price=1
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return $stm->fetch(PDO::FETCH_OBJ)?1:0;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2310'/*.$e->getMessage()*/);}

        return 0;
    }
    public function order_has_avail5_items($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            orders_items.item_id
            FROM
            orders_items
            JOIN 
            u235_items
            ON
            u235_items.item_id=orders_items.item_id AND
            u235_items.site_id=orders_items.site_id
            JOIN 
            u235_items_avail_values
            ON 
            item_avail=avail_id AND
            u235_items.site_id=u235_items_avail_values.site_id
            WHERE
            var_id=0 AND
            avail_type_id=5 AND
            order_id=:order_id AND
            orders_items.site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2320'/*.$e->getMessage()*/);}

        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            orders_items.item_id
            FROM
            orders_items
            JOIN 
            items_variants
            ON
            items_variants.item_id=orders_items.item_id AND
            items_variants.var_id=orders_items.var_id AND
            items_variants.site_id=orders_items.site_id
            JOIN 
            u235_items_avail_values
            ON 
            items_variants.avail_id=u235_items_avail_values.avail_id AND
            items_variants.site_id=u235_items_avail_values.site_id
            WHERE
            orders_items.var_id!=0 AND
            avail_type_id=5 AND
            order_id=:order_id AND
            orders_items.site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($stm->fetch(PDO::FETCH_OBJ)) return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2330'/*.$e->getMessage()*/);}

        return 0;
    }
    public function order_has_e_items($order_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            item_id,
            var_id
            FROM
            orders_items
            WHERE
            order_id=:order_id AND
            site_id=:site_id
            LIMIT 1
            ");
            $stm->bindParam(':order_id', $order_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2340'/*.$e->getMessage()*/);}



        /** @noinspection PhpUndefinedVariableInspection */
        while($order=$stm->fetch(PDO::FETCH_OBJ)) {
            if(!$this->is_real_item($order->item_id,$order->var_id,$site_id)) return 1;
        }
        return 0;
    }
    private function create_items_list4email($order_id,$order_paid,$site_id=site_id) {
        require_once 'uDrive/classes/common.php';
        $uDrive=new \uDrive\common($this->uCore);

        $u_sroot=$this->uFunc->site_id2u_sroot($site_id);
        if(!isset($this->uCat_avatar)) {
            require_once 'uCat/inc/item_avatar.php';
            $this->uCat_avatar = new uCat_item_avatar($this->uCore);
        }
        $q_items=$this->get_order_items($order_id,"
        orders_items.item_id,
        item_article_number,
        orders_items.var_id,
        orders_items.item_count,
        orders_items.item_price,
        orders_items.item_title,
        orders_items.file_id
        ",$site_id);

//        $items_ar=array();
        $items_list='<table class="uCat_items_list">
        <tr class="uCat_items_list_header_row">
        <td style="border-width:1px 0 1px 1px; "></td>
        <td style="border-width:1px 1px 1px 0; ">Товар/Услуга</td>
        <td style="border-width:1px 1px 1px 0; ">Цена</td>
        <td style="border-width:1px 1px 1px 0; ">Количество</td>
        </tr>';

        $order_total=0;

        for($i=0; $item=$q_items->fetch(PDO::FETCH_OBJ); $i++) {
            $item_data=$this->item_id2data($item->item_id,"`item_img_time`",$site_id);

            $item_title = uString::sql2text($item->item_title, 1);

            if ((int)$item->var_id) {
                $var_data = $this->var_id2data($item->var_id,$site_id);
                if($var_data) $item_img_time=$var_data->img_time;
                else $item_img_time=0;

                $var_type_id=$this->var_id2var_type_id($item->var_id,$site_id);

                $var_type_title=uString::sql2text($this->var_type_id2data($var_type_id,$site_id)->var_type_title);
                $enable_var_options=(int)$this->uFunc->getConf("enable_var_options","uCat");
                if($enable_var_options) $item_title=$var_type_title;
                else $item_title.='. ('.$var_type_title.')';


                $item_url=$u_sroot."uCat/item/".$item->item_id."?var_id=".$item->var_id;

                if($this->uFunc->getConf("show_item_article_number","uCat",0,$site_id)) $item_title.=' | Артикул: '.$var_data->item_article_number;
            }
            else {
                if($item_data) $item_img_time=(int)$item_data->item_img_time;
                else $item_img_time=0;

                $item_url=$u_sroot."uCat/item/".$item->item_id;

                if($this->uFunc->getConf("show_item_article_number","uCat",0,$site_id)) $item_title.=' | Артикул: '.$item->item_article_number;
            }

            $order_total+=$item->item_price*$item->item_count;
            $items_list.='<tr>
                            <td style="border-width:0 0 1px 1px; padding: 0!important;">
                                <a href="'.$item_url.'">
                                    <img alt="Товар" class="uCat_items_list_item_img" src="'.$this->uCat_avatar->get_avatar(640,$item->item_id,$item_img_time,$item->var_id).'">
                                </a>
                            </td>


                            <td style="border-width:0 1px 1px 0;"><a href="'.$item_url.'" style="float:left;">'.$item_title.'</a>';

                            if($order_paid&&(int)$item->file_id) {
                                $file_data=$uDrive->file_id2data($item->file_id,"file_hashname,file_name");
                                if($file_data) {
                                    $items_list.='<p>Это электронный товар.<br><a href="'.$u_sroot.'uDrive/file/'.$item->file_id.'/'.$file_data->file_hashname.'/'.$file_data->file_name.'?download" target="_blank">Нажмите сюда, чтобы скачать его&nbsp;<span class="icon-download-cloud"></span></a></p>';
                                }
                            }

                $items_list.='</td>
                            <td  style="border-width:0 1px 1px 0; text-align: center; ">'.number_format($item->item_price,2,",","&nbsp;").'</td>
                            <td  style="border-width:0 1px 1px 0; text-align: center; ">'.$item->item_count.'</td>
                        </tr>';
        }
        $items_list.='</table>
        <p class="uCat_order_total">Итого: '.$order_total.'</p>';

        return $items_list;
    }
    public function notify_about_order_change($order_id,$new_order_status,$cur_order_status,$site_id=site_id) {
        if(!$order_data=$this->order_id2data($order_id,"
            user_email,
            order_status,
            order_paid,
            user_id,
            user_name,
            user_phone,
            user_email,
            delivery_type,
            delivery_price,
            delivery_name,
            delivery_address,
            customer_type,
            company_name,
            company_address,
            payment_method,
            bill_number
            ",$site_id)) return 0;

        $items_list=$this->create_items_list4email($order_id,$order_data->order_paid,$site_id);
        $u_sroot=$this->uFunc->site_id2u_sroot($site_id);

        $has_real_items=$this->order_has_real_items($order_id,$site_id);

        if($new_order_status==="items selected") {
            $admin_email=$this->uFunc->getConf("order_admin_email","uCat",0,$site_id);
            $user_email=$order_data->user_email;

            //Email to manager
            if(uString::isEmail($admin_email)) {
                $mail_html='<div class="msg_text">
                <h3>Пользователь набрал корзину в заказ #'.$order_id.'</h3>
                <p>Посетитель прямо сейчас оформляет новый заказ. Посмотреть можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>
                <p><strong>Клиент: </strong> '.$order_data->user_name.'<br>
                <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a>
                <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>
                </div>';

                $mail_html.=$items_list;

                $mail_title= 'Оформляется заказ ' .$order_id;

                $this->uFunc->sendMail($mail_html,$mail_title,$admin_email);

                uFunc::slack($mail_title. ' ' .$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email);
            }
        }
        elseif($new_order_status=== 'order is confirmed') {
            $user_email=$order_data->user_email;
            $delivery_type=(int)$order_data->delivery_type;
            $payment_method=(int)$order_data->payment_method;
            $customer_type=(int)$order_data->customer_type;

            $admin_email=$this->uFunc->getConf('order_admin_email', 'uCat',0,$site_id);

            //Email to manager
            if(uString::isEmail($admin_email)) {
                $mail_html='<div class="msg_text">
                <h3>Оформлен новый заказ #'.$order_id.'</h3>
                <p>Требуется, чтобы вы проверили заказ и подтвердили его. Открыть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>'.
                    (($this->site_has_delivery_types()&&$has_real_items)?('<p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>'):'').
                    ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                    ($delivery_type&&($this->site_has_delivery_types()&&$has_real_items)?'<p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>':'').
                '<p><strong>Оплата: </strong>';
                if($payment_method===0) {
                    $mail_html .= 'Наличными';
                }
                elseif($payment_method===1) {
                    $mail_html .= 'Картой';
                }
                elseif($payment_method===2) {
                    $mail_html .= 'Картой онлайн на сайте';
                }
                else {
                    $mail_html .= 'По счету от юр. лица. <a href="' . $u_sroot . $this->uFunc->bill_number2file_path($order_data->bill_number) . '">Счет № ' . $order_data->bill_number . '</a>';
                }

                $mail_html.='</p>
                <p><strong>Клиент: </strong>'.
                ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').'<br>
                <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a><br>
                <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>'.
                ($customer_type===1?('<p><strong>Компания: </strong> '.$order_data->company_name.'<br>'.$order_data->company_address.'</p>'):'').
                '</div>';

                $mail_html.=$items_list;

                $mail_title= 'Проверьте заказ ' .$order_id;
                $this->uFunc->sendMail($mail_html,$mail_title,$admin_email);
                uFunc::slack($mail_title);
            }

            //Email to customer
            if(uString::isEmail($user_email)) {
                $mail_html='<div class="msg_text">
            <h3>Заказ #'.$order_id.' оформлен</h3>
            <p>Оператор проверяет заказ. При необходимости он свяжется с вами для уточнения деталей оплаты и получения. </p>
            <p>Посмотреть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>'.
                    (($this->site_has_delivery_types()&&$has_real_items)?('<p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>'):'').
                    ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                ($delivery_type?'<p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>':'').
                '<p><strong>Оплата: </strong>';
                    if($payment_method===0) $mail_html.='Наличными';
                    elseif($payment_method===1) $mail_html.='Картой';
                    elseif($payment_method===2) $mail_html.='Картой онлайн на сайте';
                    else $mail_html.='По счету от юр. лица. <a href="'.$u_sroot.$this->uFunc->bill_number2file_path($order_data->bill_number).'">Счет № '.$order_data->bill_number.'</a>';
                $mail_html.='</p>
                <p><strong>Ваши данные: </strong>'.
                ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').'<br>
                <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a><br>
                <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>'.
                ($customer_type===1?('<p><strong>Компания: </strong> '.$order_data->company_name.'<br>'.$order_data->company_address.'</p>'):'').
            '</div>';

                $mail_html.=$items_list;

                $this->uFunc->sendMail($mail_html, 'Заказ ' .$order_id. ' оформлен',$user_email);
            }
        }
        elseif($new_order_status=== 'order is processed') {
            $user_email=$order_data->user_email;
            $payment_method=(int)$order_data->payment_method;
            $delivery_type=(int)$order_data->delivery_type;

            $admin_email=$this->uFunc->getConf('order_admin_email', 'uCat',0,$site_id);

            //Email to manager
            if(uString::isEmail($admin_email)) {
                $mail_html='<div class="msg_text">';
                if($cur_order_status=== 'items selected') {//Заказ проверился автоматически - проверка оператором не требуется
                    $subject = 'Оформлен заказ ' . $order_id;

                    if (!$has_real_items || !$this->site_has_delivery_types()) {
                        if ($payment_method === 0 || $payment_method === 1) {//Налом или картой при получении
                                $mail_html .= '<h3>Заказ #' . $order_id . ' оформлен</h3>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            ';
                            $mail_html .= '
                            <p><strong>Оплата: </strong>' . ($payment_method === 0 ? 'Наличными' : 'Картой') . '</p>
                            <p><strong>Клиент: </strong> ' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') .
                                ' <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a>
                          <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                        } elseif ($payment_method === 2) {//Картой онлайн на сайте
                            $mail_html .= '<h3>Заказ #' . $order_id . ' оформлен и ожидает оплаты</h3>
                            <p>Мы уведомим вас при получении оплаты.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Оплата: </strong>Картой онлайн на сайте</p>
                            <p><strong>Клиент: </strong> ' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') .
                                ' <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a>
                          <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                        } elseif ($payment_method === 3) {//По счету
                            $mail_html .= '<h3>Заказ #' . $order_id . ' оформлен на юр. лицо и ожидает оплаты счета</h3>
                            <p>Следите за поступлением денежных средств на счет.</p>
                            <p>После поступления оплаты, откройте заказ и измените статус.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Оплата: </strong>По счету от юр. лица. <a href="' . $u_sroot . $this->uFunc->bill_number2file_path($order_data->bill_number) . '">Счет № ' . $order_data->bill_number . '</a></p>
                            <p><strong>Клиент: </strong>' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') . '<br>
                            <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a><br>
                            <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>
                            <p><strong>Компания: </strong> ' . $order_data->company_name . '<br>' . $order_data->company_address . '</p>
                          
                          ';
                        }
                    }
                    else {
                        if ($payment_method === 0 || $payment_method === 1) {//Налом или картой при получении

                            if (!$delivery_type) {//Самовывоз
                                $mail_html .= '<h3>Заказ #' . $order_id . ' требует сборки в пункте выдачи</h3>
                            <p>Заказ будет оплачен ' . ($payment_method === 0 ? 'Наличными' : 'Картой') . '.</p>
                            <p>После того, как заказ будет готов, откройте его и измените статус.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>';
                            } else {//Доставка
                                $mail_html .= '<h3>Заказ #' . $order_id . ' требует сборки. </h3>
                            <p>Заказ будет оплачен ' . ($payment_method === 0 ? 'Наличными' : 'Картой') . ' в момент доставки.</p>
                            <p>После того, как заказ будет готов, откройте его и измените статус.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Получение: </strong>' . $order_data->delivery_name . ' </p>'.
                                    ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                            '<p><strong>Адрес доставки: </strong>' . $order_data->delivery_address . '</p>
                            ';
                            }
                            $mail_html .= '
                        <p><strong>Оплата: </strong>' . ($payment_method === 0 ? 'Наличными' : 'Картой') . '</p>
                        <p><strong>Клиент: </strong> ' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') .
                                ' <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a>
                          <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                        } elseif ($payment_method === 2) {//Картой онлайн на сайте
                            $mail_html .= '<h3>Заказ #' . $order_id . ' ожидает оплаты клиентом на сайте</h3>
                            <p>Мы уведомим вас при поступлении оплаты.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Получение: </strong>' . $order_data->delivery_name . '</p>' .
                                ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                                ($delivery_type? ('
                            <p><strong>Адрес доставки: </strong>' . $order_data->delivery_address . '</p>
                            ') : '') .
                                '<p><strong>Оплата: </strong>Картой онлайн на сайте</p>
                            <p><strong>Клиент: </strong> ' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') .
                                ' <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a>
                          <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                        } elseif ($payment_method === 3) {//По счету
                            $mail_html .= '<h3>Заказ #' . $order_id . ' оформлен на юр. лицо и ожидает оплаты счета</h3>
                            <p>Следите за поступлением денежных средств на счет.</p>
                            <p>После поступления оплаты, откройте заказ и измените статус.</p>
                            <p>Открыть заказ можно по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'">' . $u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email.'</a></p>
                            <p><strong>Получение: </strong>' . $order_data->delivery_name . '</p>' .
                                ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                                ($delivery_type ? ('
                            <p><strong>Адрес доставки: </strong>' . $order_data->delivery_address . '</p>
                            ') : '') .
                                '<p><strong>Оплата: </strong>По счету от юр. лица. <a href="' . $u_sroot . $this->uFunc->bill_number2file_path($order_data->bill_number) . '">Счет № ' . $order_data->bill_number . '</a></p>
                            <p><strong>Клиент: </strong>' .
                                ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') . '<br>
                            <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a><br>
                            <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>
                            <p><strong>Компания: </strong> ' . $order_data->company_name . '<br>' . $order_data->company_address . '</p>
                          
                          ';
                        }
                    }
                    $mail_html .= '</div>';

                    $mail_html .= $items_list;

                    $this->uFunc->sendMail($mail_html, $subject, $admin_email);

                    uFunc::slack($subject. ' ' .$u_sroot . 'uCat/order_info/' . $order_id . '/'.$user_email);
                }
            }

            //Email to customer
            if(uString::isEmail($user_email)) {
                $mail_html='<div class="msg_text">';

                if($cur_order_status=== 'items selected') {//Заказ проверился автоматически - проверка оператором не требуется
                    $subject= 'Заказ ' .$order_id. ' оформлен';
                    $mail_html.='<h3>Заказ #'.$order_id.' принят</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>';
                }
                else {//Заказ был подтвержден оператором
                    $subject= 'Заказ ' .$order_id. ' подтвержден';
                    $mail_html.='<h3>Заказ #'.$order_id.' подтвержден</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>';
                }

                if($payment_method===0||$payment_method===1) {//Налом или картой при получении
                    if(!$delivery_type) {//Самовывоз
                        $mail_html.='<p>Мы готовим ваш заказ. Мы уведомим вас отдельным сообщением о готовности.</p>
                            <p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>';
                    }
                    else {//Доставка
                        $mail_html.='<p>Мы готовим ваш заказ. Мы уведомим вас отдельным сообщением о готовности.</p>
                            <p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>'.
                            ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                            '<p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>
                            ';
                    }
                    $mail_html.='
                        <p><strong>Оплата: </strong>'.($payment_method===0?'Наличными':'Картой').'</p>
                        <p><strong>Ваши данные: </strong> '.
                        ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').
                        ' <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a>
                          <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>';
                }
                elseif($payment_method===2) {//Картой онлайн на сайте
                    $mail_html.='<p style="font-size: 1.3em;">Вам нужно оплатить заказ. <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">Нажмите сюда и на открывшейся странице нажмите кнопку "Оплатить онлайн сейчас"</a></p>
                            <p>Открыть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>'.
                        ($this->site_has_delivery_types() &&$has_real_items?('<p><strong>Получение: </strong>'.$order_data->delivery_name):'').
                        ($delivery_type&&($this->site_has_delivery_types()&&$has_real_items)?('
                            <p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>
                            '):'').
                            ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                        '<p><strong>Оплата: </strong>Картой онлайн на сайте</p>
                            <p><strong>Ваши данные: </strong> '.
                        ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').
                        ' <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a>
                          <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>';
                }
                elseif($payment_method===3) {//По счету
                    $mail_html.='<p style="font-size: 1.3em;">Вам нужно оплатить заказ. <a href="'.$u_sroot.$this->uFunc->bill_number2file_path($order_data->bill_number).'">Нажмите сюда, чтобы скачать счет на оплату</a></p>
                            <p>Открыть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>'.
                        (($this->site_has_delivery_types()&&$has_real_items)?('<p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>'):'').
                        ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                        ($delivery_type&&($this->site_has_delivery_types()&&$has_real_items)?('
                            <p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>
                            '):'').
                        '<p><strong>Оплата: </strong>По счету от юр. лица. <a href="'.$u_sroot.$this->uFunc->bill_number2file_path($order_data->bill_number).'">Счет № '.$order_data->bill_number.'</a></p>
                            <p><strong>Ваши данные: </strong>'.
                        ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').'<br>
                            <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a><br>
                            <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>
                            <p><strong>Компания: </strong> '.$order_data->company_name.'<br>'.$order_data->company_address.'</p>
                          
                          ';
                }
                $mail_html.='</div>';

                $mail_html.=$items_list;
                $this->uFunc->sendMail($mail_html,$subject,$user_email);
            }
        }
        elseif($new_order_status=== 'order has been paid') {
            /*if(!$order_data=$this->order_id2data($order_id,"order_status,
            user_id,
            user_name,
            user_phone,
            user_email,
            payment_method,
            delivery_type,
            delivery_price,
            delivery_name
            delivery_address,
            company_name,
            company_address",$site_id)) return 0;*/
            $user_email=$order_data->user_email;
            $payment_method=(int)$order_data->payment_method;
            $delivery_type=(int)$order_data->delivery_type;

            $admin_email=$this->uFunc->getConf('order_admin_email', 'uCat',0,$site_id);
            if(uString::isEmail($admin_email)) {
                $mail_html='<div class="msg_text">';
                $subject= 'Оплачен заказ ' .$order_id;
                    if($payment_method===2) {//Картой онлайн на сайте
                        $mail_html.='<h3>Заказ #'.$order_id.' оплачен и ожидает выдачи</h3>
                            <p>Открыть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'">'.$u_sroot.'uCat/order_info/'.$order_id.'/'.$user_email.'</a></p>'.
                            (($this->site_has_delivery_types()&&$has_real_items)?('<p><strong>Получение: </strong>'.$order_data->delivery_name.'</p>'):'').
                            ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                            ($delivery_type&&($this->site_has_delivery_types()&&$has_real_items)?('
                            <p><strong>Адрес доставки: </strong>'.$order_data->delivery_address.'</p>
                            '):'').
                            '<p><strong>Оплата: </strong>Картой онлайн на сайте</p>
                            <p><strong>Клиент: </strong> '.
                            ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').
                            ' <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a>
                          <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>';
                    }
                $mail_html.='</div>';
                $mail_html.=$items_list;
                $this->uFunc->sendMail($mail_html,$subject,$admin_email);
            }

            //Email to customer
            if(uString::isEmail($user_email)) {
                if ($payment_method === 2 || $payment_method === 3) {//Картой онлайн на сайте или безналом
                    $mail_html = '<div class="msg_text">';
                    $subject = 'Заказ ' . $order_id . ' оплачен';
                    $mail_html .= '<h3>Мы получили оплату заказа #' . $order_id . '</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '">' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '</a></p>'.
                        (($this->site_has_delivery_types()&&$has_real_items)?('<p>Заказ готовится к выдаче. Мы уведомим вас отдельным письмом о готовности</p>'):'');

                    $mail_html .= (($this->site_has_delivery_types()&&$has_real_items)?('<p><strong>Получение: </strong>' . $order_data->delivery_name . '</p>'):'') .
                        ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                        ($delivery_type&&($this->site_has_delivery_types()&&$has_real_items) ? ('
                            <p><strong>Адрес доставки: </strong>' . $order_data->delivery_address . '</p>
                            ') : '') .
                        '<p><strong>Оплата: </strong>'.($payment_method===2?'Картой онлайн на сайте': 'По счету от юр. лица').'</p>
                            <p><strong>Ваши данные: </strong>' .
                        ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') . '<br>
                            <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a><br>
                            <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                    if ($payment_method === 3) {
                        $mail_html .= '<p><strong>Компания: </strong> ' . $order_data->company_name . '<br>' . $order_data->company_address . '</p>';
                    }
                    $mail_html .= '</div>';

                    $mail_html .= $items_list;
                    $this->uFunc->sendMail($mail_html, $subject, $user_email);
                }
            }
        }
        elseif($new_order_status=== 'awaiting delivery') {
            if(!$has_real_items || !$this->site_has_delivery_types()) {
                return 0;
            }

            /*if(!$order_data=$this->order_id2data($order_id,"
            user_id,
            user_name,
            user_phone,
            user_email,
            delivery_type,
            delivery_price,
            delivery_name
            delivery_address,
            payment_method,
            company_name,
            company_address",$site_id)) return 0;*/
            $user_email=$order_data->user_email;
            $payment_method=(int)$order_data->payment_method;
            $delivery_type=(int)$order_data->delivery_type;

            //Email to customer
            if(uString::isEmail($user_email)) {
                    $mail_html = '<div class="msg_text">';
                    $subject = 'Заказ ' . $order_id.($delivery_type===0?' готов к выдаче. Можете его забирать':'Передан в доставку');
                    $mail_html .= '<h3>Заказ #' . $order_id .$order_id.($delivery_type===0?' готов к выдаче. Можете его забирать':' передан в доставку').'</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '">' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '</a></p>';

                    $mail_html .= '<p><strong>Получение: </strong>' .$order_data->delivery_name. '</p>' .
                        ((float)$order_data->delivery_price?('<p><strong>Стоимость доставки:</strong> '.$order_data->delivery_price.'</p>'):'').
                        ($delivery_type? ('
                            <p><strong>Адрес доставки: </strong>' . $order_data->delivery_address . '</p>
                            ') : '') .
                        '<p><strong>Оплата: </strong>';

                    if($payment_method===0) $mail_html.='Наличными';
                    elseif($payment_method===1) $mail_html.='Картой';
                    elseif($payment_method===2) $mail_html.='Картой онлайн на сайте';
                    else $mail_html.='По счет от юр. лица';

                $mail_html.='</a></p>
                            <p><strong>Ваши данные: </strong>' .
                        ((int)$order_data->user_id ? ('<a href="' . $u_sroot . 'uAuth/profile/' . $order_data->user_id . '">') : '') . $order_data->user_name . ((int)$order_data->user_id ? '</a>' : '') . '<br>
                            <a href="tel:' . $order_data->user_phone . '">' . $order_data->user_phone . '</a><br>
                            <a href="mailto:' . $order_data->user_email . '">' . $order_data->user_email . '</a></p>';
                    if ($payment_method === 3) {
                        $mail_html .= '<p><strong>Компания: </strong> ' . $order_data->company_name . '<br>' . $order_data->company_address . '</p>';
                    }
                    $mail_html .= '</div>';

                    $mail_html .= $items_list;
                    $this->uFunc->sendMail($mail_html, $subject, $user_email);
            }
        }
        elseif($new_order_status=== 'order completed') {
            //order completed - заказ получен. Благодарим пользователя
            if(!$order_data=$this->order_id2data($order_id, 'user_email',$site_id)) {
                return 0;
            }
            $user_email=$order_data->user_email;

            //Email to customer
            if(uString::isEmail($user_email)) {
                    $mail_html = '<div class="msg_text">';
                    $subject = 'Заказ ' . $order_id . ' выполнен';
                    $mail_html .= '<h3>Мы выполнили ваш заказ #' . $order_id . '</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '">' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '</a></p>
                    <p>Благодарим за сотрудничество и ждем вас снова!</p>
                    ';
                    $mail_html .= '</div>';

                    $mail_html .= $items_list;
                    $this->uFunc->sendMail($mail_html, $subject, $user_email);
            }
        }
        elseif($new_order_status=== 'order canceled') {
            //order canceled - заказ отменен. Просто пишем уведомления клиенту и менеджеру
            /*if(!$order_data=$this->order_id2data($order_id,"order_status,
            user_id,
            user_name,
            user_phone,
            user_email,
            delivery_type,
            delivery_price,
            delivery_name
            delivery_address,
            payment_method,
            company_name,
            company_address",$site_id)) return 0;*/
            $user_email=$order_data->user_email;
            $payment_method=(int)$order_data->payment_method;

            $admin_email=$this->uFunc->getConf('order_admin_email', 'uCat',0,$site_id);
            if(uString::isEmail($admin_email)) {
                $subject= 'Заказ' .$order_id. ' отменен';
                $mail_html='<div class="msg_text">';
                        $mail_html.='<h3>Заказ #'.$order_id.' отменен</h3>
                            <p>Открыть заказ можно по ссылке <a href="'.$u_sroot.'uCat/order_info/'.$order_id.'">'.$u_sroot.'uCat/order_info/'.$order_id.'</a></p>
                            <p><strong>Оплата: </strong>';
                        if($payment_method===0) $mail_html.='Наличными';
                        elseif($payment_method===1) $mail_html.='Картой';
                        elseif($payment_method===2) $mail_html.='Картой онлайн на сайте';
                        else $mail_html.='По счет от юр. лица';
                    $mail_html.='</p>
                            <p><strong>Клиент: </strong> '.
                            ((int)$order_data->user_id?('<a href="'.$u_sroot.'uAuth/profile/'.$order_data->user_id.'">'):'').$order_data->user_name.((int)$order_data->user_id?'</a>':'').
                            ' <a href="tel:'.$order_data->user_phone.'">'.$order_data->user_phone.'</a>
                          <a href="mailto:'.$order_data->user_email.'">'.$order_data->user_email.'</a></p>';
                $mail_html.='</div>';
                $mail_html.=$items_list;
                $this->uFunc->sendMail($mail_html,$subject,$admin_email);
            }

            //Email to customer
            if(uString::isEmail($user_email)) {
                    $subject = 'Заказ ' . $order_id . ' отменен';
                    $mail_html = '<div class="msg_text">
                    <h3>Заказ #' . $order_id . ' отменен</h3>
                    <p>Подробную информацию о заказе можно посмотреть по ссылке <a href="' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '">' . $u_sroot . 'uCat/order_info/' . $order_id . '/' . $user_email . '</a></p>';
                    $mail_html .= '</div>';

                    $mail_html .= $items_list;
                    $this->uFunc->sendMail($mail_html, $subject, $user_email);
            }
        }
        return 1;
    }

    //UNITS
    public function unit_search_by_id($unit_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo('uCat')->prepare('SELECT
            unit_id
            FROM
            units
            WHERE
            unit_id=:unit_id AND 
            site_id=:site_id
            ');


            $stm->bindParam(':unit_id', $unit_id,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($unitid = $stm->fetch(PDO::FETCH_ASSOC)) {
                return (int)$unitid["unit_id"];
            }
            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2350'/*.$e->getMessage()*/);}

        return false;
    }
    public function unit_of_item($item_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo('uCat')->prepare('SELECT
            units.unit_name
            FROM
            u235_items
            LEFT JOIN
            units
             ON
             u235_items.unit_id=units.unit_id AND 
             u235_items.site_id=units.site_id
            WHERE
            u235_items.item_id=:item_id AND 
            u235_items.site_id=:site_id
            ');


            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($unitname = $stm->fetch(PDO::FETCH_ASSOC)) {
                return $unitname['unit_name'];
            }

            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2360'/*.$e->getMessage()*/);}

        return false;
    }
    public function unit_search_by_title($unit_title,$site_id=site_id) {
        $unit_title=uString::text2sql($unit_title);
        try {

            $stm = $this->uFunc->pdo('uCat')->prepare('SELECT
            unit_id
            FROM
            units
            WHERE
            unit_name=:unit_name AND 
            site_id=:site_id
            ');


            $stm->bindParam(':unit_name', $unit_title,PDO::PARAM_STR);

            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

            $stm->execute();


            if($unittitle = $stm->fetch(PDO::FETCH_ASSOC)) {
                return (int)$unittitle["unit_id"];
            }
            return false;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2370'/*.$e->getMessage()*/);}

        return false;
    }
    public function unit_create($unit_title,$site_id=site_id) {
        if(!$this->unit_search_by_title($unit_title)) {
            try {

                $stm = $this->uFunc->pdo('uCat')->prepare('INSERT INTO
                units
                (unit_name,
                site_id) 
                VALUES 
                (:unit_name,
                :site_id)
                ');


                $stm->bindParam(':unit_name', $unit_title,PDO::PARAM_STR);

                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);

                $stm->execute();


                return $this->uFunc->pdo('uCat')->LastInsertId();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2380'/*.$e->getMessage()*/);}

        }

        return 0;
    }
    public function unit_id2unit_name($unit_id,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            unit_name 
            FROM 
            units 
            WHERE
            unit_id=:unit_id AND 
            site_id=:site_id
            ');
            $stm->bindParam(':unit_id', $unit_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return $qr->unit_name;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2390'/*.$e->getMessage()*/);}

        return '';
    }
    public function get_default_unit_id($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            unit_id 
            FROM 
            units 
            WHERE
            `default`=1 AND 
            site_id=:site_id
            ');
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                return (int)$qr->unit_id;
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2400'/*.$e->getMessage()*/);}

        return 0;
    }

    //ARTICLES
    public function delete_article($art_id,$site_id=site_id) {
        //get affected items
        $q_items=$this->get_article_items($art_id,$site_id);
        $this->delete_art_files($art_id,$site_id);

        //Detach art from items
        $this->detach_article_all_items($art_id,$site_id);

        $this->delete_art_from_db($art_id,$site_id);
        //update item's art_count

        while($item_obj=$q_items->fetch(PDO::FETCH_OBJ)) {
            $this->calculate_item_art_count($item_obj->item_id);
        }
    }
    public function create_new_article($art_title,$site_id=site_id) {
        $art_id=$this->get_new_article_id($site_id);
        $art_title=uString::text2sql($art_title);

        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('INSERT INTO 
            u235_articles (
            art_id,
            art_title,
            site_id
            ) VALUES (
            :art_id,
            :art_title,
            :site_id
            )');
            $stm->bindParam(':art_id', $art_id,PDO::PARAM_INT);
            $stm->bindParam(':art_title', $art_title,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2410'/*.$e->getMessage()*/);}
        return $art_id;
    }

    //USER PREFERENCES
    public function user_id2user_preferences($user_id,$q_select,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare('SELECT 
            ' .$q_select. '
            FROM 
            user_preferences 
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ');
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2420'/*.$e->getMessage()*/);}

        return 0;
    }
    public function save_user_preferences($user_id,$preferences_ar,$site_id=site_id) {
//        $preferences_ar=array(
//            "user_name"=>$val,
//            "user_email"=>$val,
//            "user_phone"=>$val,
//            "delivery_type"=>$val,
//            "delivery_addr"=>$val,
//            "delivery_comment"=>$val,
//            "customer_type"=>$val,
//            "vat_number"=>$val,
//            "company_name"=>$val,
//            "tax_info1"=>$val,
//            "company_addr"=>$val,
//            "payment_method"=>$val);

        if($this->user_id2user_preferences($user_id, 'user_id',$site_id)) {
            $q_set= '';
            if(isset($preferences_ar["user_name"])) $q_set.="user_name=:user_name,";
            if(isset($preferences_ar["user_email"])) $q_set.="user_email=:user_email,";
            if(isset($preferences_ar["user_phone"])) $q_set.="user_phone=:user_phone,";
            if(isset($preferences_ar["delivery_type"])) $q_set.="delivery_type=:delivery_type,";
            if(isset($preferences_ar["delivery_addr"])) $q_set.="delivery_addr=:delivery_addr,";
            if(isset($preferences_ar["delivery_comment"])) $q_set.="delivery_comment=:delivery_comment,";
            if(isset($preferences_ar["customer_type"])) $q_set.="customer_type=:customer_type,";
            if(isset($preferences_ar["vat_number"])) $q_set.="vat_number=:vat_number,";
            if(isset($preferences_ar["company_name"])) $q_set.="company_name=:company_name,";
            if(isset($preferences_ar["tax_info1"])) $q_set.="tax_info1=:tax_info1,";
            if(isset($preferences_ar["company_addr"])) $q_set.="company_addr=:company_addr,";
            if(isset($preferences_ar["payment_method"])) $q_set.="payment_method=:payment_method,";
            try {

                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                user_preferences
                SET
                ".$q_set."
                user_id=:user_id
                WHERE
                user_id=:user_id AND
                site_id=:site_id
                ");
                if(isset($preferences_ar["user_name"])) $stm->bindParam(':user_name', $preferences_ar["user_name"],PDO::PARAM_STR);
                if(isset($preferences_ar["user_email"])) $stm->bindParam(':user_email', $preferences_ar["user_email"],PDO::PARAM_STR);
                if(isset($preferences_ar["user_phone"])) $stm->bindParam(':user_phone', $preferences_ar["user_phone"],PDO::PARAM_STR);
                if(isset($preferences_ar["delivery_type"])) $stm->bindParam(':delivery_type', $preferences_ar["delivery_type"],PDO::PARAM_INT);
                if(isset($preferences_ar["delivery_addr"])) $stm->bindParam(':delivery_addr', $preferences_ar["delivery_addr"],PDO::PARAM_STR);
                if(isset($preferences_ar["delivery_comment"])) $stm->bindParam(':delivery_comment', $preferences_ar["delivery_comment"],PDO::PARAM_STR);
                if(isset($preferences_ar["customer_type"])) $stm->bindParam(':customer_type', $preferences_ar["customer_type"],PDO::PARAM_INT);
                if(isset($preferences_ar["vat_number"])) $stm->bindParam(':vat_number', $preferences_ar["vat_number"],PDO::PARAM_INT);
                if(isset($preferences_ar["company_name"])) $stm->bindParam(':company_name', $preferences_ar["company_name"],PDO::PARAM_STR);
                if(isset($preferences_ar["tax_info1"])) $stm->bindParam(':tax_info1', $preferences_ar["tax_info1"],PDO::PARAM_INT);
                if(isset($preferences_ar["company_addr"])) $stm->bindParam(':company_addr', $preferences_ar["company_addr"],PDO::PARAM_STR);
                if(isset($preferences_ar["payment_method"])) $stm->bindParam(':payment_method', $preferences_ar["payment_method"],PDO::PARAM_INT);
                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2430'.$e->getMessage());}
        }
        else {
            $q_insert=$q_values="";
            if(isset($preferences_ar["user_name"]))         {$q_insert.="user_name,"; $q_values.=":user_name,";}
            if(isset($preferences_ar["user_email"]))        {$q_insert.="user_email,"; $q_values.=":user_email,";}
            if(isset($preferences_ar["user_phone"]))        {$q_insert.="user_phone,"; $q_values.=":user_phone,";}
            if(isset($preferences_ar["delivery_type"]))     {$q_insert.="delivery_type,"; $q_values.=":delivery_type,";}
            if(isset($preferences_ar["delivery_addr"]))     {$q_insert.="delivery_addr,"; $q_values.=":delivery_addr,";}
            if(isset($preferences_ar["delivery_comment"]))  {$q_insert.="delivery_comment,"; $q_values.=":delivery_comment,";}
            if(isset($preferences_ar["customer_type"]))     {$q_insert.="customer_type,"; $q_values.=":customer_type,";}
            if(isset($preferences_ar["vat_number"]))        {$q_insert.="vat_number,"; $q_values.=":vat_number,";}
            if(isset($preferences_ar["company_name"]))      {$q_insert.="company_name,"; $q_values.=":company_name,";}
            if(isset($preferences_ar["tax_info1"]))         {$q_insert.="tax_info1,"; $q_values.=":tax_info1,";}
            if(isset($preferences_ar["company_addr"]))      {$q_insert.="company_addr,"; $q_values.=":company_addr,";}
            if(isset($preferences_ar["payment_method"]))    {$q_insert.="payment_method,"; $q_values.=":payment_method,";}
            try {

                /** @noinspection SqlInsertValues */
                $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                user_preferences (
                user_id,
                ".$q_insert."
                site_id
                ) VALUES (
                :user_id,
                ".$q_values."
                :site_id
                )
                ");
                if(isset($preferences_ar["user_name"])) $stm->bindParam(':user_name', $preferences_ar["user_name"],PDO::PARAM_STR);
                if(isset($preferences_ar["user_email"])) $stm->bindParam(':user_email', $preferences_ar["user_email"],PDO::PARAM_STR);
                if(isset($preferences_ar["user_phone"])) $stm->bindParam(':user_phone', $preferences_ar["user_phone"],PDO::PARAM_STR);
                if(isset($preferences_ar["delivery_type"])) $stm->bindParam(':delivery_type', $preferences_ar["delivery_type"],PDO::PARAM_INT);
                if(isset($preferences_ar["delivery_addr"])) $stm->bindParam(':delivery_addr', $preferences_ar["delivery_addr"],PDO::PARAM_STR);
                if(isset($preferences_ar["delivery_comment"])) $stm->bindParam(':delivery_comment', $preferences_ar["delivery_comment"],PDO::PARAM_STR);
                if(isset($preferences_ar["customer_type"])) $stm->bindParam(':customer_type', $preferences_ar["customer_type"],PDO::PARAM_INT);
                if(isset($preferences_ar["vat_number"])) $stm->bindParam(':vat_number', $preferences_ar["vat_number"],PDO::PARAM_INT);
                if(isset($preferences_ar["company_name"])) $stm->bindParam(':company_name', $preferences_ar["company_name"],PDO::PARAM_STR);
                if(isset($preferences_ar["tax_info1"])) $stm->bindParam(':tax_info1', $preferences_ar["tax_info1"],PDO::PARAM_INT);
                if(isset($preferences_ar["company_addr"])) $stm->bindParam(':company_addr', $preferences_ar["company_addr"],PDO::PARAM_STR);
                if(isset($preferences_ar["payment_method"])) $stm->bindParam(':payment_method', $preferences_ar["payment_method"],PDO::PARAM_INT);
                $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat/common/2440'/*.$e->getMessage()*/);}
        }
    }


    //DELIVERY
    public function get_new_delivery_type_id() {
        try {

            $stm=$this->uFunc->pdo('uCat')->prepare("SELECT 
            del_type_id
            FROM 
            delivery_types
            ORDER BY 
            del_type_id DESC 
            LIMIT 1
            ");

            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->del_type_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2450'/*.$e->getMessage()*/,1);}
        return 1;
    }
    public function get_new_delivery_point_id() {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            point_id 
            FROM 
            delivery_points
            ORDER BY 
            point_id DESC 
            LIMIT 1
            ");
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->point_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2460'/*.$e->getMessage()*/,1);}
        return 1;
    }
    public function get_new_delivery_point_variant_id() {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            var_id 
            FROM 
            delivery_point_variants
            ORDER BY 
            var_id DESC 
            LIMIT 1
            ");
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->var_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2470'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private $site_has_delivery_types=array();
    public function site_has_delivery_types($site_id=site_id) {
        if(!isset($this->site_has_delivery_types[$site_id])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                del_type_id 
                FROM 
                delivery_types 
                WHERE 
                site_id=:site_id 
                LIMIT 1
                ");
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->site_has_delivery_types[$site_id]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/2480'/*.$e->getMessage()*/);}
        }
        return $this->site_has_delivery_types[$site_id];
    }
    private $site_has_delivery_type_1=array();
    public function site_has_delivery_type_1($site_id=site_id) {
        if(!isset($this->site_has_delivery_type_1[$site_id])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                del_type_id 
                FROM 
                delivery_types 
                WHERE
                del_type=1 AND
                site_id=:site_id 
                LIMIT 1
                ");
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->site_has_delivery_type_1[$site_id]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/2490'/*.$e->getMessage()*/);}
        }
        return $this->site_has_delivery_type_1[$site_id];
    }
    private $site_has_delivery_type_0=array();
    public function site_has_delivery_type_0($site_id=site_id) {
        if(!isset($this->site_has_delivery_type_0[$site_id])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                del_type_id 
                FROM 
                delivery_types 
                WHERE
                del_type=0 AND
                site_id=:site_id 
                LIMIT 1
                ");
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->site_has_delivery_type_0[$site_id]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/2500'/*.$e->getMessage()*/);}
        }
        return $this->site_has_delivery_type_0[$site_id];
    }
    public function get_number_of_site_delivery_types($site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            COUNT(del_type_id) AS number 
            FROM 
            delivery_types 
            WHERE 
            site_id=:site_id 
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->number;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2510'/*.$e->getMessage()*/);}
        return 0;
    }
    private $delivery_type_id2data_ar=array();
    public function delivery_type_id2data($del_type_id,$q_data="del_type_id",$site_id=site_id) {
        if(!(int)$del_type_id) return 0;
        if(!isset($this->delivery_type_id2data_ar[$site_id][$del_type_id][$q_data])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                " . $q_data . "
                FROM 
                delivery_types 
                WHERE
                del_type_id=:del_type_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':del_type_id', $del_type_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();


                $this->delivery_type_id2data_ar[$site_id][$del_type_id][$q_data]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('uCat/common/2520'/*.$e->getMessage()*/, 1);}
        }
        return $this->delivery_type_id2data_ar[$site_id][$del_type_id][$q_data];
    }
    private $delivery_point_id2data_ar=array();
    public function delivery_point_id2data($point_id,$q_data="point_id",$site_id=site_id) {
        if(!(int)$point_id) return 0;
        if(!isset($this->delivery_point_id2data_ar[$site_id][$point_id][$q_data])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                " . $q_data . "
                FROM 
                delivery_points 
                WHERE
                point_id=:point_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':point_id', $point_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                $this->delivery_point_id2data_ar[$site_id][$point_id][$q_data]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                $this->uFunc->error('uCat/common/2530'/*.$e->getMessage()*/, 1);
            }
        }
        return $this->delivery_point_id2data_ar[$site_id][$point_id][$q_data];
    }
    private $delivery_point_variant_id2data_ar=array();
    public function delivery_point_variant_id2data($var_id,$q_data="var_id",$site_id=site_id) {
        if(!(int)$var_id) return 0;
        if(!isset($this->delivery_point_variant_id2data_ar[$site_id][$var_id][$q_data])) {
            try {

                $stm = $this->uFunc->pdo("uCat")->prepare("SELECT 
                " . $q_data . "
                FROM 
                delivery_point_variants 
                WHERE
                var_id=:var_id AND
                site_id=:site_id
                ");
                $stm->bindParam(':var_id', $var_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();


                $this->delivery_point_variant_id2data_ar[$site_id][$var_id][$q_data]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                $this->uFunc->error('uCat/common/2540'/*.$e->getMessage()*/, 1);
            }
        }
        return $this->delivery_point_variant_id2data_ar[$site_id][$var_id][$q_data];
    }
    public function get_delivery_types($q_select="del_type_id, 
            del_type_name, 
            del_type_descr, 
            del_type, 
            is_default,
            del_show,
            pos",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select."
            FROM 
            delivery_types 
            WHERE 
            site_id=:site_id
            ORDER BY pos ASC
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2550'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_delivery_points($del_type_id,$q_select="point_id,
            point_name,
            point_descr,
            is_default,
            point_show,
            pos",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select."
            FROM 
            delivery_points 
            WHERE 
            del_type_id=:del_type_id AND
            site_id=:site_id
            ORDER BY pos ASC
            ");
            $stm->bindParam(':del_type_id', $del_type_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2560'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_delivery_point_variants($point_id,$q_select="
            var_id,
            var_name,
            var_descr,
            delivery_price,
            avail_at_price_since,
            avail_at_price_till,
            set_at_price_since,
            manager_must_confirm,
            manager_sets_delivery_price,
            var_show,
            pos",$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            ".$q_select."
            FROM 
            delivery_point_variants 
            WHERE 
            point_id=:point_id AND
            site_id=:site_id
            ORDER BY pos ASC
            ");
            $stm->bindParam(':point_id', $point_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2570'/*.$e->getMessage()*/);}
        return 0;
    }
    public function delivery_point_variant_id2del_type($var_id,$site_id=site_id) {
        if(!$var_data=$this->delivery_point_variant_id2data($var_id,"point_id",$site_id)) return 0;
        /** @noinspection PhpUndefinedFieldInspection */
        if(!$point_data=$this->delivery_point_id2data($var_data->point_id,"del_type_id",$site_id)) return 0;
        /** @noinspection PhpUndefinedFieldInspection */
        if(!$del_data=$this->delivery_type_id2data($point_data->del_type_id,"del_type",$site_id)) return 0;
        /** @noinspection PhpUndefinedFieldInspection */
        return (int)$del_data->del_type;
    }

    //ITEM IMPORT
    private function get_new_import_file_id() {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            list_id 
            FROM 
            items_import 
            ORDER BY 
            list_id DESC 
            LIMIT 1
            ");
            $stm->execute();

            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->list_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2580'/*.$e->getMessage()*/);}

        return 1;
    }
    public function save_import_file_to_db($filepath,$lines_to_skip,$extension,$columns,$delimiter) {
        $list_id=$this->get_new_import_file_id();
        $list_name=str_replace("uCat/import_upload/54/","",$filepath);
        $lines_imported=0;
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            items_import (
            list_id,
            list_name,  
            filepath,
            lines_total,
            lines_to_skip,
            lines_imported,
            extension,
            columns,
            delimiter,
            status,
            site_id
            ) VALUES (
            :list_id,
            :list_name,
            :filepath,
            0,
            :lines_to_skip,
            :lines_imported,
            :extension,
            :columns,
            :delimiter,
            0,
            :site_id          
            )
            ");
            $site_id=site_id;
            $stm->bindParam(':list_id', $list_id,PDO::PARAM_INT);
            $stm->bindParam(':list_name', $list_name,PDO::PARAM_STR);
            $stm->bindParam(':filepath', $filepath,PDO::PARAM_STR);
            $stm->bindParam(':lines_to_skip', $lines_to_skip,PDO::PARAM_INT);
            $stm->bindParam(':lines_imported', $lines_imported,PDO::PARAM_INT);
            $stm->bindParam(':extension', $extension,PDO::PARAM_STR);
            $stm->bindParam(':columns', $columns,PDO::PARAM_STR);
            $stm->bindParam(':delimiter', $delimiter,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2590'.$e->getMessage());}
    }
    public function get_import_file() {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            * 
            FROM 
            items_import
            WHERE 
            status!=2
            ORDER BY
            list_id
            LIMIT 1
            ");
            $stm->execute();


            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2600'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_import_files($q_select,$site_id=site_id) {
        try {

            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            $q_select 
            FROM 
            items_import
            WHERE
            site_id=:site_id
            ORDER BY
            list_name
            ");
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();


            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uCat/common/2610'/*.$e->getMessage()*/);}
        return 0;
    }


    public function reset_img_time_for_var($var_id,$item_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    items_variants
                    SET
                    img_time=0
                    WHERE
                    var_id=:var_id AND
                    item_id=:item_id AND
                    site_id=:site_id
                    ");
            $stm->bindParam(':var_id', $var_id, PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('uCat/common/2620'/*.$e->getMessage()*/);}
    }
    public function reset_img_time_for_item($item_id,$site_id=site_id) {
        try {

            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    u235_items
                    SET
                    item_img_time=0
                    WHERE
                    item_id=:item_id AND
                    site_id=:site_id
                    ");
            $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            $stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('uCat/common/2630'/*.$e->getMessage()*/);}
    }
}
