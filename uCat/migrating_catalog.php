<?php

use processors\uFunc;
require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uDrive/classes/common.php";
require_once "lib/simple_html_dom.php";

class migrating_catalog {
    private $uCore, $uSes, $uDrive_common, $microtime_start, $microtime_finish, $simple_html;
    private $mist_obj, $sect_obj, $branch;
    private $file_id, $folder_id, $folder, $targetDir, $save_file_name, $mime_type, $filename, $source_filename, $file_size, $orig_file_name, $file_ext, $uDrive_folder_id;
    private $cat_id_arr = array();

    private function check_data() {
        $this->microtime_start = microtime(true);
        ini_set("memory_limit", "256M");
        ini_set('max_execution_time', 3600);
        $this->branch = 1;
        if($this->branch == 1) {
            $this->get_sects();
        }
        else if($this->branch == 3) {
            $this->insert_size();
        }
        else {
            $this->get_sects(1);
            $this->get_obj();
            $this->obhod_obj_data();
        }
    }

    private function insert_size() {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            cat_id
            FROM
            u235_cats
            WHERE 
            site_id=:site_id
            ");

            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $cat_obj = $stm->fetchAll(PDO::FETCH_COLUMN);
        }
        catch(PDOException $e) {$this->uFunc->error('210'.$e->getMessage());}

        foreach ($cat_obj as $key=>$value) {
            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO
                u235_cats_fields
                (cat_id,field_id,site_id)
                VALUES
                (:cat_id,:field_id,:site_id)
                ");

                $site_id=site_id;
                $field_id=1;
                $stm->bindParam(':cat_id', $value,PDO::PARAM_INT);
                $stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('220'.$e->getMessage());}
        }
    }

    private function get_obj() {
        try {
            $stm=$this->uFunc->pdo("db_alexlsc_1")->prepare("SELECT
            price_goods.goodsId,
            price_groups.groupId,
            price_groups.groupPreId,
            price_groups.groupName,
            price_groups.groupIconName,
            price_goods.goodsName,
            price_goods.goodsTitle,
            price_goods.goodsIconName,
            price_goods.goodsPrice,
            price_goods.goodsPriceOld
            FROM
            price_goods
            LEFT JOIN
            price_groups
              ON
                price_goods.groupId=price_groups.groupId
            ");

            $stm->execute();

            $this->mist_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('1'.$e->getMessage());}
    }

    private function proccessing() {
        if($this->branch == 1) {
            unset($this->sect_obj);
        }
        $this->get_obj();
        foreach($this->mist_obj as $key => $value) {
            $goodsName = stripslashes(trim($value->goodsName));
            $search_art = substr($goodsName, 0, 4);
            if($search_art !== "арт." && $search_art !== "Арт." && $search_art !== "АРТ.") {
                if (strpos($goodsName, "арт.") !== false) {
                    $razdelitel = "арт.";
                    $groupName_arr = explode($razdelitel, $goodsName);
                    $item_name = trim($groupName_arr[0]);
                    $artikul = trim($groupName_arr[1]);
                    goto step2;
                }

                if (strpos($goodsName, "АРТ.") !== false) {
                    $razdelitel = "АРТ.";
                    $groupName_arr = explode($razdelitel, $goodsName);
                    $item_name = trim($groupName_arr[0]);
                    $artikul = trim($groupName_arr[1]);
                }
                else {
                    $artikul = $value->goodsId;
                    $item_name = $goodsName;
                }

                step2:
                $last_s = substr($item_name, -1);
                if ($last_s == ',') {
                    $item_name = substr($item_name,0,-1);
                }

                if (strpos($artikul, "цвет") !== false) {
                    if ($artikul{strlen($artikul)-1} !== ')') {
                        $razdelit = "цвет";
                        $artikul = trim($artikul);
                        $artikul_arr = explode($razdelit, $artikul);
                        $artikul = trim($artikul_arr[0]);
                        $item_name .= " ЦВЕТ" . $artikul_arr[1];
                        if ($artikul{strlen($artikul) - 1} == ',') {
                            $artikul = substr($artikul, 0, -1);
                        }
                        goto step3;
                    }
                }

                if (strpos($artikul, "Цвет") !== false) {
                    if ($artikul{strlen($artikul)-1} !== ')') {
                        $razdelit = "Цвет";
                        $artikul = trim($artikul);
                        $artikul_arr = explode($razdelit, $artikul);
                        $artikul = trim($artikul_arr[0]);
                        $item_name .= " ЦВЕТ" . $artikul_arr[1];
                        if ($artikul{strlen($artikul) - 1} == ',') {
                            $artikul = substr($artikul, 0, -1);
                        }
                        goto step3;
                    }
                }

                if (strpos($artikul, "ЦВЕТ") !== false) {
                    if ($artikul{strlen($artikul)-1} !== ')') {
                        $razdelit = "ЦВЕТ";
                        $artikul = trim($artikul);
                        $artikul_arr = explode($razdelit, $artikul);
                        $artikul = trim($artikul_arr[0]);
                        $item_name .= " ЦВЕТ" . $artikul_arr[1];
                        if ($artikul{strlen($artikul) - 1} == ',') {
                            $artikul = substr($artikul, 0, -1);
                        }
                    }
                }

                step3:
                if ($artikul{strlen($artikul)-1} == ')') {
                    $razdelit = "(";
                    $art_arr = explode($razdelit, $artikul);
                    $artikul = trim($art_arr[0]);
                    $item_name .= " (".$art_arr[1];
                }

                $artikul = trim($artikul);
                $item_name = trim($item_name);
            }
            else {
                $artikul = $value->goodsId;
                $item_name = $goodsName;
            }

            if(strlen($item_name) == 0) {
                $item_name = $artikul;
            }

            // Получаем размер товара
            $razmer = $this->get_size_item($value->goodsId);
            // Получаем размер товара *********************************************************************

            // Создание товара
            $this->create_items($value->goodsId, $item_name);
            // Создание товара ****************************************************************************

            // Создание категории
            $this->create_cats($value->groupId, $value->groupName, $value->groupIconName, $value->groupId);
            // Создание категории *************************************************************************

            // Прекрепление товара к категории
            $this->attach_item_and_cats($value->goodsId, $value->groupId);
            // Прекрепление товара к категории ************************************************************

            // Обновление информации о категории
            $this->update_cats_info($value->groupId);
            // Обновление информации о категории **********************************************************

            // Обновление информации о товаре
            $this->update_items_info($value->goodsId, $value->goodsTitle, $value->goodsPrice, $value->goodsPriceOld, $artikul, $razmer);
            // Обновление информации о товаре *************************************************************

            // Прикрепление друг к другу раздела и категории
            $this->attach_sects_and_cats($value->groupId, $value->groupPreId);
            // Прикрепление друг к другу раздела и категории **********************************************

            // Аватарки товаров
            $this->avatar_items($value->goodsId, $value->goodsIconName, $value->groupId);
            // Аватарки товаров ***************************************************************************
        }

        unset($this->mist_obj);

        if($this->branch == 1) {
            $this->microtime_finish = microtime(true);
            $time_exec = $this->microtime_finish - $this->microtime_start;
            echo $time_exec." sec.";
        }
    }

    private function avatar_items($item_id, $icon_name, $group_id) {
        if($icon_name !== "" && $icon_name !== null) {
            $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$group_id."/".$icon_name;
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/goods-".$item_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/group-".$group_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                return false;
            }
            $dir = "uCat/item_avatars/".site_id."/";
            $this->uFunc->rmdir($dir.$item_id);
            if (!file_exists($dir.$item_id)) mkdir($dir.$item_id, 0755, true);
            if (!$this->uFunc->create_empty_index($dir.$item_id)) $this->uFunc->error(10);

            $img = new \Imagick($source_filename);
            $img->setImageBackgroundColor('#ffffff');
            $img->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $img->setImageFormat("jpeg");
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(100);
            $img->writeImage($dir.$item_id."/orig.jpg");

            $img->clear();
            $img->destroy();

            $this->avatar_items_db_update($item_id);
        }
    }

    private function avatar_cats($cat_id, $icon_name, $group_id) {
        if($icon_name !== "" && $icon_name !== null) {
            $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$icon_name;
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$group_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/goods-".$cat_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/goods-".$group_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                return false;
            }
            $dir = "uCat/cat_avatars/".site_id."/";
            $this->uFunc->rmdir($dir.$cat_id);
            if (!file_exists($dir.$cat_id)) mkdir($dir.$cat_id, 0755, true);
            if (!$this->uFunc->create_empty_index($dir.$cat_id)) $this->uFunc->error(20);

            $img = new \Imagick($source_filename);
            $img->setImageBackgroundColor('#ffffff');
            $img->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $img->setImageFormat("jpeg");
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(100);
            $img->writeImage($dir.$cat_id."/orig.jpg");

            $img->clear();
            $img->destroy();

            $this->avatar_cats_db_update($cat_id);
        }
    }

    private function avatar_sects($sect_id, $icon_name, $group_id) {
        if($icon_name !== "" && $icon_name !== null) {
            $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$icon_name;
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/".$group_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/goods-".$sect_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                $source_filename = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/goods-".$group_id."/".$icon_name;
            }
            if (!file_exists($source_filename)) {
                return false;
            }
            $dir = "uCat/sect_avatars/".site_id."/";
            $this->uFunc->rmdir($dir.$sect_id);
            if (!file_exists($dir.$sect_id)) mkdir($dir.$sect_id, 0755, true);
            if (!$this->uFunc->create_empty_index($dir.$sect_id)) $this->uFunc->error(30);

            $img = new \Imagick($source_filename);
            $img->setImageBackgroundColor('#ffffff');
            $img->setImageAlphaChannel(11); // Imagick::ALPHACHANNEL_REMOVE
            $img->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $img->setImageFormat("jpeg");
            $img->setImageCompression(Imagick::COMPRESSION_JPEG);
            $img->setImageCompressionQuality(100);
            $img->writeImage($dir.$sect_id."/orig.jpg");

            $img->clear();
            $img->destroy();

            $this->avatar_sects_db_update($sect_id);
        }
    }

    private function avatar_items_db_update($item_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            item_img_time=:item_img_time
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $item_img_time=time();

            $stm->bindParam(':item_img_time', $item_img_time,PDO::PARAM_INT);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('6'.$e->getMessage());}
    }

    private function avatar_cats_db_update($cat_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_cats
            SET
            cat_avatar_time=:cat_avatar_time
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $cat_img_time=time();

            $stm->bindParam(':cat_avatar_time', $cat_img_time,PDO::PARAM_INT);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('7'.$e->getMessage());}
    }

    private function avatar_sects_db_update($sect_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_sects
            SET
            sect_avatar_time=:sect_avatar_time
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $sect_img_time=time();

            $stm->bindParam(':sect_avatar_time', $sect_img_time,PDO::PARAM_INT);
            $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('8'.$e->getMessage());}
    }

    private function attach_item_and_cats($item_id, $cat_id) {
        $postData = array(
            'item_id' => $item_id,
            'cat_id' => $cat_id,
            'action' => 'attach'
        );

        $ch = curl_init(u_sroot.'uCat/admin_cats_attach_item');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $result = curl_exec($ch);

//        return uFunc::POST(u_sroot.'uCat/admin_cats_attach_item', $postData);
    }

    private function get_size_item($item_id) {
        try {
            $stm=$this->uFunc->pdo("db_alexlsc_1")->prepare("SELECT
            tags.tagName
            FROM
            tags,
            taglinks
            WHERE
            tags.tagId=taglinks.tagId AND
            taglinks.itemId=:item_id
            ");

            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->execute();

            $tagName_arr = $stm->fetchAll(PDO::FETCH_COLUMN);

            $tagName_str = implode(", ", $tagName_arr);
        }
        catch(PDOException $e) {$this->uFunc->error('5'.$e->getMessage());}

        return $tagName_str;
    }

    private function create_sects() {
        foreach ($this->sect_obj as $key => $value) {
            $this->create_sects_post($value->groupId, $value->groupName);
            $this->avatar_sects($value->groupId, $value->groupIconName, $value->groupPreId);
        }

        $this->attach_sects();
    }

    private function attach_sects() {
        foreach ($this->sect_obj as $key => $value) {
            $this->attach_sects_post($value->groupPreId, $value->groupId);
        }

        $this->proccessing();
    }

    private function attach_sects_and_cats($cat_id, $sect_id) {
        $postData = array(
            'sect_id' => $sect_id,
            'cat_id' => $cat_id,
            'action' => 'attach'
        );

        $ch = curl_init(u_sroot.'uCat/admin_sects_attach_cat');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $result = curl_exec($ch);

//        return uFunc::POST(u_sroot.'uCat/admin_sects_attach_cat', $postData);
    }

    private function create_sects_post($sect_id, $sect_name) {
        $postData = array(
            'sect_id' => $sect_id,
            'sect_title' => $sect_name
        );

        $ch = curl_init(u_sroot.'uCat/admin_sects_create_bg');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        //curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID='.$_COOKIE['PHPSESSID']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //session_write_close();

        return $result = curl_exec($ch);

//        return uFunc::POST(u_sroot.'uCat/admin_sects_create_bg', $postData);
    }

    private function attach_sects_post($parent_sect_id, $child_sect_id) {
        $postData = array(
            'parent_sect_id' => $parent_sect_id,
            'child_sect_id' => $child_sect_id,
            'action' => 'attach'
        );

        $ch = curl_init(u_sroot.'uCat/admin_sects_attach_sect');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $result = curl_exec($ch);

//        return uFunc::POST(u_sroot.'uCat/admin_sects_attach_sect', $postData);
    }

    private function create_items($item_id, $item_name) {
        $postData = array(
            'item_id' => $item_id,
            'item_title' => $item_name,
            'cat_id' => 'no_cat',
            'art_id' => 'no_art'
        );

        $ch = curl_init(u_sroot.'uCat/admin_items_create_bg');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $result = curl_exec($ch);

//        return uFunc::POST(u_sroot.'uCat/admin_items_create_bg', $postData);
    }

    private function create_cats($cat_id, $cat_title, $icon_name, $group_id) {
        $postData = array(
            'cat_id' => $cat_id,
            'cat_title' => $cat_title
        );

        $ch = curl_init(u_sroot.'uCat/admin_cats_create_bg');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

//        $response = uFunc::POST(u_sroot.'uCat/admin_cats_create_bg', $postData);

        $this->avatar_cats($cat_id, $icon_name, $group_id);

        return $response;
    }

    private function update_cats_info($cat_id) {
        $cat_uuid = $this->uFunc->generate_uuid();
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_cats 
            SET 
            cat_uuid=:cat_uuid
            WHERE 
            cat_id=:cat_id AND 
            site_id=:site_id
            ");

            $site_id = site_id;
            $stm->bindParam(':cat_uuid', $cat_uuid,PDO::PARAM_STR);
            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('2'.$e->getMessage());}
    }

    private function update_items_info($item_id, $item_descr, $item_price, $prev_price, $item_article, $razmer) {
        $item_uuid = $this->uFunc->generate_uuid();
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items 
            SET 
            evotor_uuid=:item_uuid,
            item_descr=:item_descr,
            item_price=:item_price,
            prev_price=:prev_price,
            item_article_number=:item_article,
            field_1=:razmer
            WHERE 
            item_id=:item_id AND 
            site_id=:site_id
            ");

            $site_id = site_id;
            $stm->bindParam(':item_uuid', $item_uuid,PDO::PARAM_STR);
            $stm->bindParam(':item_descr', $item_descr,PDO::PARAM_STR);
            $stm->bindParam(':item_price', $item_price,PDO::PARAM_STR);
            $stm->bindParam(':prev_price', $prev_price,PDO::PARAM_STR);
            $stm->bindParam(':item_article', $item_article,PDO::PARAM_STR);
            $stm->bindParam(':razmer', $razmer,PDO::PARAM_STR);
            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('3'.$e->getMessage());}
    }

    private function get_sects($step=0) {
        try {
            $stm=$this->uFunc->pdo("db_alexlsc_1")->prepare("SELECT distinct 
            price_groups.groupId,
            price_groups.groupPreId,
            price_groups.groupName,
            price_groups.groupIconName
            FROM
            price_groups,
            price_goods
            WHERE
            price_groups.groupId<>price_goods.groupId
            ");

            $stm->execute();

            $this->sect_obj = $stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('4'.$e->getMessage());}

        if($step == 0) {
            $this->create_sects();
        }
    }

    private function get_contents_item($item_id) {
        try {
            $stm=$this->uFunc->pdo("db_alexlsc_1")->prepare("SELECT 
            contentData
            FROM
            contents
            WHERE
            contentName=:contentName
            LIMIT 1
            ");
            $content_name = "goods-".$item_id;

            $stm->bindParam(':contentName', $content_name,PDO::PARAM_STR);
            $stm->execute();

            $content = $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('9'.$e->getMessage());}

        if($content) {
            return $content->contentData;
        }
        else {
            return false;
        }
    }

    private function get_contents_group($group_id) {
        try {
            $stm=$this->uFunc->pdo("db_alexlsc_1")->prepare("SELECT 
            contentData
            FROM
            contents
            WHERE
            contentName=:contentName
            LIMIT 1
            ");
            $content_name = "group".$group_id;

            $stm->bindParam(':contentName', $content_name,PDO::PARAM_STR);
            $stm->execute();

            $content = $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('10'.$e->getMessage());}

        if($content) {
            return $content->contentData;
        }
        else {
            return false;
        }
    }

    private function obhod_obj_data() {
        foreach($this->sect_obj as $key => $value) {
            $contentData = $this->get_contents_group($value->groupId);
            if($contentData) {
                $contentData_parse = $this->parse_str_content($value->groupId, $value->groupName, $contentData, "sect");
                if($contentData_parse) {
                    $this->update_sect_contentData($value->groupId, $contentData_parse);
                }
            }
        }

        foreach($this->mist_obj as $key => $value) {
            $contentData = $this->get_contents_group($value->groupId);
            if($contentData) {
                $contentData_parse = $this->parse_str_content($value->goodsId, $value->goodsName, $contentData, "cat");
                if($contentData_parse) {
                    // Проверка есть ли такой groupId в массиве, если да то пропускаем
                    if(!isset($this->cat_id_arr[$value->groupId])) {
                        $this->update_cats_contentData($value->groupId, $contentData_parse);
                        // Добавляем groupId в массив
                        $this->cat_id_arr[$value->groupId] = 1;
                    }
                }
            }
            $contentData_item = $this->get_contents_item($value->goodsId);
            if($contentData_item) {
                $contentData_item_parse = $this->parse_str_content($value->goodsId, $value->goodsName, $contentData_item, "item");
                if($contentData_item_parse) {
                    $this->update_items_contentData($value->goodsId, $contentData_item_parse);
                }
            }
        }

        $this->microtime_finish = microtime(true);
        $time_exec = $this->microtime_finish - $this->microtime_start;
        echo $time_exec." sec.";
    }

    private function parse_str_content($id, $title, $str, $target) {
        $patch = "/var/www/test.site/mistral/1.alexlsc.z8.ru/docs/i/content/274/";

        if (preg_match("/url_image_274/", $str) || preg_match("/url_file_274/", $str)) {
            $str = stripslashes($str);
            $html = $this->simple_html->load($str);
            $data = $html->find('a');
            if(!empty($data)) {
                foreach($data as $a){
                    $a->class = "fancybox";
                    $src = explode("/", $a->href);
                    $link = $this->set_data_uDrive($target, $patch, end($src), $id, $title);
                    $a->href = $link;

                    $i = 0;
                    while($img = $a->children($i++)){
                        if($img->tag == "img") {
                            $img->src = $link;
                        }
                    }
                }

                $new_str = $this->simple_html->save();
            }
            else {
                foreach($html->find('img') as $img){
                    $src = explode("/", $img->src);
                    $link = $this->set_data_uDrive($target, $patch, end($src), $id, $title);
                    $img->src = $link;
                }
                $new_str = $html->save();
            }

            $html->clear();
            unset($html);
        }
        else {
            return $str;
        }

        return $new_str;
    }

    private function update_items_contentData($item_id, $contentData, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            item_descr 
            FROM
            u235_items
            WHERE 
            item_id=:item_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $descr = $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('13'.$e->getMessage());}

        if($descr !== false) {
            try {
                $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_items 
                SET 
                item_descr=:item_descr
                WHERE 
                item_id=:item_id AND 
                site_id=:site_id
                ");

                $item_descr = trim($descr->item_descr)." ".trim($contentData);
                $stm->bindParam(':item_descr', $item_descr, PDO::PARAM_STR);
                $stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            }
            catch (PDOException $e) {$this->uFunc->error('14'.$e->getMessage());}
        }
    }

    private function update_cats_contentData($cat_id, $contentData, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            cat_descr 
            FROM
            u235_cats
            WHERE 
            cat_id=:cat_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $descr = $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('15'.$e->getMessage());}

        if($descr !== false) {
            try {
                $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_cats 
                SET 
                cat_descr=:cat_descr
                WHERE 
                cat_id=:cat_id AND 
                site_id=:site_id
                ");

                $cat_descr = trim($descr->cat_descr)." ".trim($contentData);
                $stm->bindParam(':cat_descr', $cat_descr, PDO::PARAM_STR);
                $stm->bindParam(':cat_id', $cat_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            }
            catch (PDOException $e) {$this->uFunc->error('16'.$e->getMessage());}
        }
    }

    private function update_sect_contentData($group_id, $contentData, $site_id=site_id) {
        try {
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            sect_descr 
            FROM
            u235_sects
            WHERE 
            sect_id=:sect_id AND 
            site_id=:site_id
            ");

            $stm->bindParam(':sect_id', $group_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();

            $descr = $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('11'.$e->getMessage());}

        if($descr !== false) {
            try {
                $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                u235_sects 
                SET 
                sect_descr=:sect_descr
                WHERE 
                sect_id=:sect_id AND 
                site_id=:site_id
                ");

                $sect_descr = trim($descr->sect_descr)." ".trim($contentData);
                $stm->bindParam(':sect_descr', $sect_descr, PDO::PARAM_STR);
                $stm->bindParam(':sect_id', $group_id, PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();
            }
            catch (PDOException $e) {$this->uFunc->error('12'.$e->getMessage());}
        }
    }

    private function genHash() {
        mt_srand(time());
        return md5(mt_rand(0,time())*time()*rand(0,1000));
    }

    private function after_uploaded_db_work() {
        //Get new file_id
        if(!$query=$this->uCore->query("uDrive","SELECT
        `file_id`
        FROM
        `u235_files`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `file_id` DESC
        LIMIT 1
        ")) $this->uFunc->error(100);
        if(mysqli_num_rows($query)>0) {
            $qr=$query->fetch_object();
            $this->file_id=$qr->file_id+1;
        }
        else $this->file_id=1;

        //make hash name for file
        $this->save_file_name=$this->genHash();

        if($this->folder_id) {
            //Check if this folder_id exists
            if(!$query=$this->uCore->query("uDrive","SELECT
            `file_id`
            FROM
            `u235_files`
            WHERE
            `file_id`='".$this->folder_id."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(110);
            if(!mysqli_num_rows($query)>0) {
                $this->uFunc->error(120);
            }
        }

        if(file_exists($this->targetDir.$this->filename)) {
            if(is_file($this->targetDir.$this->filename)) {
                $this->mime_type = mime_content_type($this->targetDir.$this->filename);
                $this->source_filename = $this->targetDir.$this->filename;
                $this->file_size = filesize($this->source_filename);
            }
        }
        else {
            $this->mime_type = "jpeg";
            $this->source_filename = $this->targetDir.$this->filename;
            $this->file_size = 0;
        }

        //Save file to db
        if(!$this->uCore->query("uDrive","INSERT INTO
        `u235_files` (
		`file_id`,
		`file_name`,
		`file_size`,
		`file_ext`,
		`file_mime`,
		`file_hashname`,
		`file_timestamp`,
		`folder_id`,
		`owner_id`,
		`site_id`
		) VALUES (
		'".$this->file_id."',
		'".uString::sql2text($this->orig_file_name)."',
		'".$this->file_size."',
		'".$this->file_ext."',
		'".$this->mime_type."',
		'".$this->save_file_name."',
		'".time()."',
		'".$this->folder_id."',
		'".$this->uSes->get_val("user_id")."',
		'".site_id."'
		)")) $this->uFunc->error(130);

        return $this->register_file_type();
    }

    private function register_file_type() {
        if(!$query=$this->uCore->query("uDrive","SELECT
        `type_id`
        FROM
        `u235_file_types`
        WHERE
        `ext`='".$this->file_ext."' AND
        `mime_type`='".$this->mime_type."'
        ")) $this->uFunc->error(140);
        if(!mysqli_num_rows($query)) {
            if(!$query=$this->uCore->query("uDrive","SELECT
            `type_id`
            FROM
            `u235_file_types`
            ORDER BY
            `type_id` DESC
            LIMIT 1
            ")) $this->uFunc->error(150);
            if(mysqli_num_rows($query)) {
                $qr=$query->fetch_object();
                $type_id=$qr->type_id+1;
            }
            else $type_id=1;
            if(!$this->uCore->query("uDrive","INSERT INTO
            `u235_file_types` (
            `type_id`,
            `ext`,
            `mime_type`
            ) VALUES (
            '".$type_id."',
            '".$this->file_ext."',
            '".$this->mime_type."'
            )
            ")) $this->uFunc->error(160);
        }

        return $this->after_uploaded_fs_work();
    }

    private function after_uploaded_fs_work() {
        $dir = $_SERVER['DOCUMENT_ROOT'].'/'.$this->folder.'/'.$this->file_id.'/'; //Адрес директории для сохранения файла
        // Create dir
        if (!file_exists($dir)) mkdir($dir,0755,true);

        //copy file
        if (file_exists($this->source_filename)) {
            copy($this->source_filename, $dir.$this->save_file_name);
        }

        return '/uDrive/file/'.$this->file_id.'/'.$this->save_file_name."/".$this->filename;
    }

    private function set_data_uDrive($target, $target_dir, $filename, $id, $title) {
        $this->targetDir = $target_dir; // Целевая дириктория (путь к файлу загрузки)
        $this->filename = $filename; // Имя файла для обработки
        $this->orig_file_name=uString::sanitize_filename($this->filename);
        //$this->filename = preg_replace('/[^\w\._]+/', '', $this->filename);
        //$this->filename = uString::text2filename(uString::rus2eng($this->filename),true);
        //$this->filename = str_replace(' ', '', $this->filename);
        $this->file_ext = strrpos($this->filename, '.');
        $this->folder='uDrive/files/'.site_id;

        if($target == "item") {
            $this->get_uDrive_folder_id_item($id, 0, $title);
        }
        else if($target == "cat") {
            $this->get_uDrive_folder_id_cat($id, 0, $title);
        }
        else if($target == "sect") {
            $this->get_uDrive_folder_id_sect($id, 0, $title);
        }
        else {
            exit;
        }

        $this->folder_id=(int)$this->uDrive_folder_id;

        return $this->after_uploaded_db_work();
    }

    private function get_uDrive_folder_id_item($item_id, $uDrive_folder_id_item, $item_tit) {
        if($uDrive_folder_id_item=='0') {
            $item_title=trim(uString::sanitize_filename(uString::sql2text($item_tit)));
            if(!strlen($item_title)) $item_title='Товар '.$item_id;

            $uCat_items_folder_id=$this->uDrive_common->get_module_folder_id("uCat");
            $this->uDrive_folder_id=$this->uDrive_common->create_folder($item_title,$uCat_items_folder_id);

            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items
                SET
                uDrive_folder_id=:folder_id
                WHERE
                item_id=:item_id AND
                site_id=:site_id
                ");

                $site_id=site_id;
                $stm->bindParam(':folder_id', $this->uDrive_folder_id,PDO::PARAM_INT);
                $stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
        }
    }

    private function get_uDrive_folder_id_cat($cat_id, $uDrive_folder_id_cat, $cat_tit) {
        if($uDrive_folder_id_cat=='0') {
            $uDrive_uCat_cats_folder_id=$this->uDrive_common->get_module_folder_id("uCat");
            $cat_title=trim(uString::sanitize_filename(uString::sql2text($cat_tit)));
            if(!strlen($cat_title)) $cat_title='Категория '.$cat_id;
            $this->uDrive_folder_id=$this->uDrive_common->create_folder($cat_title,$uDrive_uCat_cats_folder_id);

            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_cats
                SET
                uDrive_folder_id=:folder_id
                WHERE
                cat_id=:cat_id AND
                site_id=:site_id
                ");

                $site_id=site_id;
                $stm->bindParam(':cat_id', $cat_id,PDO::PARAM_INT);
                $stm->bindParam(':folder_id', $uDrive_folder_id_cat,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}
        }
    }

    private function get_uDrive_folder_id_sect($sect_id, $uDrive_folder_id_sect, $sect_tit) {
        if($uDrive_folder_id_sect=='0') {
            $uDrive_uCat_sects_folder_id=$this->uDrive_common->get_module_folder_id("uCat");
            $sect_title=trim(uString::sanitize_filename(uString::sql2text($sect_tit)));
            if(!strlen($sect_title)) $sect_title='Раздел '.$sect_id;
            $this->uDrive_folder_id=$this->uDrive_common->create_folder($sect_title,$uDrive_uCat_sects_folder_id);

            try {
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_sects
                SET
                uDrive_folder_id=:folder_id
                WHERE
                sect_id=:sect_id AND
                site_id=:site_id
                ");

                $site_id=site_id;
                $stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
                $stm->bindParam(':folder_id', $uDrive_folder_id_sect,PDO::PARAM_INT);
                $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                $stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uDrive_common=new \uDrive\common($this->uCore);
        $this->simple_html = new \simple_html_dom();

        $this->check_data();
    }
}

new migrating_catalog($this);
