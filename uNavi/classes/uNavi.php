<?php
namespace uNavi\common;
use PDO;
use PDOException;
use processors\uFunc;

//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
require_once "processors/classes/uFunc.php";

class uNavi {
    private $purifier_config;
    private $uFunc;
    private $uCore;

    private function copy_menu_item($new_cat_id,$menu_item,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_id=$this->get_new_item_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("INSERT INTO u235_menu (
            id, 
            access, 
            position, 
            title, 
            link, 
            status, 
            cat_id, 
            target, 
            indent, 
            timestamp, 
            icon_regular_filename, 
            icon_hover_filename, 
            show_label, 
            is_system_btn, 
            site_id
            ) VALUES (
            :id, 
            :access, 
            :position, 
            :title, 
            :link, 
            :status, 
            :cat_id, 
            :target, 
            :indent, 
            :timestamp, 
            :icon_regular_filename, 
            :icon_hover_filename, 
            :show_label, 
            :is_system_btn, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $new_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':access', $menu_item->access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $menu_item->position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $menu_item->title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':link', $menu_item->link,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $menu_item->status,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $new_cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':target', $menu_item->target,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':indent', $menu_item->indent,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $menu_item->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':icon_regular_filename', $menu_item->icon_regular_filename,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':icon_hover_filename', $menu_item->icon_hover_filename,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_label', $menu_item->show_label,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_system_btn', $menu_item->is_system_btn,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/10'/*.$e->getMessage()*/);}

        if($menu_item->icon_regular_filename!="") {
            $src_folder='uNavi/item_icons/'.$source_site_id.'/'.$menu_item->id.'/regular/';
            $dest_folder='uNavi/item_icons/'.$dest_site_id.'/'.$new_id.'/regular/';

            $src_file = $src_folder.$menu_item->id.'.'.$menu_item->icon_regular_filename;
            $dest_file = $src_folder.$new_id.'.'.$menu_item->icon_regular_filename;

            // Create dir
            if (!file_exists($dest_folder)) mkdir($dest_folder,0755,true);
            if(!$this->uFunc->create_empty_index($dest_folder)) $this->uFunc->error("uNavi/classes/uNavi/20");

            copy($src_file,$dest_file);
        }
        if($menu_item->icon_hover_filename!="") {
            $src_folder='uNavi/item_icons/'.$source_site_id.'/'.$menu_item->id.'/hover/';
            $dest_folder='uNavi/item_icons/'.$dest_site_id.'/'.$new_id.'/hover/';

            $src_file = $src_folder.$menu_item->id.'.'.$menu_item->icon_hover_filename;
            $dest_file = $src_folder.$new_id.'.'.$menu_item->icon_hover_filename;

            // Create dir
            if (!file_exists($dest_folder)) mkdir($dest_folder,0755,true);
            if(!$this->uFunc->create_empty_index($dest_folder)) $this->uFunc->error("uNavi/classes/uNavi/30");

            copy($src_file,$dest_file);
        }
    }
    public function copy_cat($cat_id,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $new_cat_id=$this->get_new_cat_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT 
            * 
            FROM 
            u235_cats 
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/40'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$cat=$stm->fetch(PDO::FETCH_OBJ)) return 0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("INSERT INTO u235_cats (
            cat_id, 
            cat_title, 
            cat_type, 
            cat_access, 
            status, 
            timestamp, 
            site_id
            ) VALUES (
            :cat_id, 
            :cat_title, 
            :cat_type, 
            :cat_access, 
            :status, 
            :timestamp, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $new_cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_title', $cat->cat_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_type', $cat->cat_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_access', $cat->cat_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_access', $cat->cat_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $cat->status,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $cat->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/50'/*.$e->getMessage()*/);}

        //Get all cat_items
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT 
            * 
            FROM 
            u235_menu
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($menu_item=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_menu_item($new_cat_id,$menu_item,$source_site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/60'/*.$e->getMessage()*/);}

        return $new_cat_id;
    }

    private function get_new_cat_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT 
            cat_id 
            FROM 
            u235_cats
            WHERE site_id=:site_id
            ORDER BY 
            cat_id DESC 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->cat_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/70'/*.$e->getMessage()*/);}
        return 1;
    }
    private function get_new_item_id($site_id=site_id) {
        //get new item_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            id
            FROM
            u235_menu
            WHERE
            site_id=:site_id
            ORDER BY
            id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/80'/*.$e->getMessage()*/);}
        return 1;
    }
    private function get_last_pos($cat_id,$site_id=site_id) {
        //get last position of item for this cat
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            position
            FROM
            u235_menu
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ORDER BY
            position DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->position+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/90'/*.$e->getMessage()*/);}
        return 0;
    }
    public function create_new_item($cat_id,$title="",$indent=0,$position=0,$site_id=site_id) {
        $item_id=$this->get_new_item_id($site_id);
        if(!$position) $position=$this->get_last_pos($cat_id,$site_id);
        if($title=="") $title="Link ".$item_id;
        $timestamp=time();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("INSERT INTO
            u235_menu (
            id,
            position,
            title,
            cat_id,
            indent,
            timestamp,
            site_id
            ) VALUES (
            :id,
            :position,
            :title,
            :cat_id,
            :indent,
            :timestamp,
            :site_id
            )
            ");

            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':indent', $indent,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':position', $position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/100'/*.$e->getMessage()*/);}

        return $item_id;
    }
    public function update_item($item_id,$access,$target,$show_label,$is_system_btn,$link,$title,$site_id=site_id) {
        if(!isset($this->purifier)) {
            $this->purifier_config = \HTMLPurifier_Config::createDefault();
            $this->purifier = new \HTMLPurifier($this->purifier_config);
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
            u235_menu
            SET
            title=:title,
            link=:link,
            target=:target,
            access=:access,
            show_label=:show_label,
            is_system_btn=:is_system_btn
            WHERE
            id=:item_id AND
            site_id=:site_id
            ");
            $title=$this->purifier->purify(trim($title));
            $link=$this->purifier->purify(trim($link));
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':access', $access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':target', $target,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_label', $show_label,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_system_btn', $is_system_btn,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':link', $link,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/110'/*.$e->getMessage()*/);}
    }
    public function create_cat($cat_id=0,$cat_title="Menu",$cat_type=4,$cat_access=0,$site_id=site_id) {
        if(!$cat_id) $cat_id=$this->get_new_cat_id($site_id);
        if(strlen($cat_title)) {
            $config = \HTMLPurifier_Config::createDefault();
            $purifier = new \HTMLPurifier($config);

            $cat_title=$purifier->purify(htmlspecialchars(trim($cat_title)));
        }
        else {
            $cat_title=$cat_title="Menu #".$cat_id;
        }
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("INSERT INTO
            u235_cats (
            cat_id, 
            cat_title, 
            cat_type, 
            cat_access, 
            site_id
            ) VALUES (
            :cat_id, 
            :cat_title, 
            :cat_type, 
            :cat_access, 
            :site_id
            ) 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_title', $cat_title ,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_type', $cat_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_access', $cat_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uNavi/classes/uNavi/120'/*.$e->getMessage()*/);}

        return $cat_id;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
    }
}
