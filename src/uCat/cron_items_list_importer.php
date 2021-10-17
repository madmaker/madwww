<?php
ini_set("memory_limit","256M");
header("Connection: close");
ignore_user_abort(true);
//ob_start();
//ob_end_flush(); // All output buffers must be flushed here
//flush();


use processors\uFunc;
use uCat\common;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/classes/import.php";
require_once "uCat/classes/common.php";

class cron_items_list_importer {
    public $site_id, $item_id, $unit_id, $sect_id, $cat_id, $filepath, $datapost, $data, $exp, $delimiter, $separat, $lines_to_skip, $column_name, $result_obj, $item_action_flag, $cat_action_flag, $sect_action_flag, $unit_action_flag;
    /**
     * @var int
     */
    private $time_limit;
    /**
     * @var int
     */
    private $start_time;
    /**
     * @var int
     */
    private $status;
    private $columns;
    private $extension;
    /**
     * @var int
     */
    private $lines_imported;
    /**
     * @var int
     */
    private $lines_total;
    private $list_name;
    /**
     * @var int
     */
    private $list_id;
    /**
     * @var int
     */
    private $file;

    private $uCore, $uFunc, $uCat, $uSes, $import;

    private function update_img_time_for_item($item_id,$site_id = site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm = $this->uFunc->pdo("uCat")->prepare("UPDATE 
                    u235_items
                    SET
                    item_img_time=:item_img_time
                    WHERE
                    item_id=:item_id AND
                    site_id=:site_id
                    ");
            $item_img_time = time();
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_img_time', $item_img_time, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        } catch (PDOException $e) {$this->uFunc->error('35'/*.$e->getMessage()*/);}
    }

    public function data_preparation() {
        if($this->delimiter == "empty") $this->separat = null;
        else if($this->delimiter == "comma") $this->separat = ",";
        else if($this->delimiter == "semicolon") $this->separat = ";";
        else if($this->delimiter == "colon") $this->separat = ":";
        else if($this->delimiter == "tab") $this->separat = "\t";
        else if($this->delimiter == "space") $this->separat = " ";
        else $this->separat = ";";


//        print_r($this->columns);

//            'itemid' => 'item_id',
//            'itemname' => 'item_title',
//            'item_img_url' => 'item_img_url',
//            'itemdescr' => 'item_descr',
//            'price' => 'item_price',
//            'quantity' => 'quantity',
//            'unitid' => 'unit_id',
//            'unit' => 'unit_name',
//            'article' => 'item_article_number',
//            'catid' => 'primary_cat_id',
//            'catname' => 'cat_title',
//            'sectid' => 'primary_sect_id',
//            'sectname' => 'sect_title'


//            'uuid' => 'evotor_uuid',

        $item_id_key=array_search("item_id", $this->columns);
        $item_article_number_key = array_search("item_article_number", $this->columns);
        $item_title_key=array_search("itemname", $this->columns);

        $price_key=array_search("price", $this->columns);
        $descr_key=array_search("itemdescr", $this->columns);
        $quantity_key=array_search("quantity", $this->columns);

        $cat_id_key=array_search("catid", $this->columns);
        $cat_title_key=array_search("catname", $this->columns);

        $sect_id_key=array_search("sectid", $this->columns);
        $sect_title_key=array_search("sectname", $this->columns);

        $unit_id_key=array_search("unitid", $this->columns);
        $unit_title_key=array_search("unit", $this->columns);

        $item_img_key=array_search("item_img_url", $this->columns);


        if($item_title_key===false) {
            unlink($this->filepath);
            $this->update_file_and_info(2,0);
            exit;
        }

        if ($item_id_key!==false) $item_identifier="item_id";
        elseif ($item_article_number_key!==false) $item_identifier="item_id";
        elseif ($item_title_key!==false) $item_identifier="item_title";
        else {
            unlink($this->filepath);
            $this->update_file_and_info(2,0);
            exit;
        }

        echo $item_identifier;
        echo $item_title_key;

        if ($price_key!==false) $price_identifier="item_price";
        else $price_identifier="none";

        if ($descr_key!==false) $descr_identifier="item_descr";
        else $descr_identifier="none";

        if ($quantity_key!==false) $quantity_identifier="quantity";
        else $quantity_identifier="none";

        if ($cat_id_key!==false) $cat_identifier="primary_cat_id";
        elseif ($cat_title_key!==false) $cat_identifier="cat_title";
        else $cat_identifier="none";

        if ($sect_id_key!==false) $sect_identifier="primary_sect_id";
        elseif ($sect_title_key!==false) $sect_identifier="sect_title";
        else $sect_identifier="none";

        if ($unit_id_key!==false) $unit_identifier="unit_id";
        elseif ($unit_title_key!==false) $unit_identifier="unit_name";
        else $unit_identifier="none";

        if ($item_img_key!==false) $item_img_key_identifier="item_img_url";
        else $item_img_key_identifier="none";




        $import_obj = $this->import->getXLS($this->filepath, $this->separat);
        $import_obj_ar = $import_obj[0];
        if(!$this->file->status) {
            $this->lines_total = count($import_obj_ar);
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                items_import
                SET
                status=1,
                lines_total=:lines_total
                WHERE
                list_id=:list_id 
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lines_total', $this->lines_total,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':list_id', $this->list_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
            $this->lines_imported=$this->lines_to_skip;
        }

        for($i=$this->lines_imported;$i<$this->lines_total;$i++) {
            // Проверяем на существование товара и устанавливаем флаг действия
            //item_id
//            print_r($import_obj_ar);
            $file_line=$import_obj_ar[$i];
            $item_id=$item_article_number=0;
            if ($item_identifier==="item_id") {
                $item_id = $this->uCat->item_search_by_id($file_line[$item_id_key],$this->site_id);
                $item_article_number=$item_id;
            }
            //item_article_number
            elseif ($item_identifier==="item_article_number") {
                $item_id = $this->uCat->item_article_number_exists($file_line[$item_article_number_key],$this->site_id);
                $item_article_number=$file_line[$item_article_number_key];
            }
            elseif($item_identifier==="item_title") {
                $item_id = $this->uCat->item_search_by_title($file_line[$item_title_key],$this->site_id);
                $item_article_number=$item_id;
                print "<br>ss";
                print (int)$item_id;
            }

            $item_title=$file_line[$item_title_key];

            if($price_identifier!=="none") {
                $item_price=(float)$file_line[$price_key];
            }
            else $item_price=0;

            if($descr_identifier!=="none") {
                $item_descr=$file_line[$descr_key];
            }
            else $item_descr="";

            if($quantity_identifier!=="none") {
                $quantity=$file_line[$quantity_key];
            }
            else $quantity=1;

            // Проверяем на существование категории и устанавливаем флаг действия
            $cat_id=0;
            if($cat_identifier!=="none") {
                if ($cat_identifier==="primary_cat_id") $cat_id=$this->uCat->cat_search_by_id($file_line[$cat_id_key],$this->site_id);
                else {
                    if(!$cat_id=$this->uCat->cat_search_by_title($file_line[$cat_title_key],$this->site_id)) {
                        $cat_uuid = $this->uFunc->generate_uuid();
                        $cat_id = $this->uCat->create_new_cat($file_line[$cat_title_key], $cat_uuid);
                    }
                }
            }

            // Проверяем на существование раздела и устанавливаем флаг действия
            $sect_id=0;
            if($sect_identifier!=="none") {
                if ($sect_identifier==="primary_sect_id") {
                    $sect_id=$this->uCat->sect_search_by_id($file_line[$sect_id_key],$this->site_id);
                }
                else {
                    if(!$sect_id=$this->uCat->sect_search_by_title($file_line[$sect_title_key],$this->site_id))
                        $sect_id=$this->uCat->create_new_sect($file_line[$sect_title_key]);
                }
            }

            // Проверяем на существование единицы измерения и устанавливаем флаг действия
            $unit_id=0;
            if($unit_identifier!=="none") {
                if ($unit_identifier==="unit_id") {
                    if(!$unit_id=$this->uCat->unit_search_by_id($file_line[$unit_id_key])) $unit_id=1;
                }
                else {//unit_name
                    if(!$unit_id=$this->uCat->unit_search_by_title($file_line[$unit_title_key]))
                        $unit_id = $this->uCat->unit_create($file_line[$unit_title_key]);
                }
            }


            if(!$item_id) {
                // Создание товара
                $item_article_number=$item_id = $this->uCat->get_new_item_id($this->site_id);
                $item_avail = $this->uCat->get_any_available_avail_id($this->site_id);
                $evotor_uuid = $this->uFunc->generate_uuid();

                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
                    u235_items (
                    item_id,
                    evotor_uuid,
                    quantity,
                    item_avail,
                    unit_id,
                    item_title,
                    item_descr,
                    item_price,
                    item_keywords,
                    item_article_number,
                    primary_cat_id,
                    site_id
                    ) VALUES (
                    :item_id,
                    :uuid,
                    :quantity,
                    :item_avail,
                    :unit_id,
                    :item_title,
                    :item_descr,
                    :item_price,
                    '',
                    :item_article_number,
                    :primary_cat_id,
                    :site_id
                    )");
                    $item_title=uString::text2sql($item_title,1);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':uuid', $evotor_uuid,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':quantity', $quantity,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_avail', $item_avail,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':unit_id', $unit_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title', $item_title,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_descr', $item_descr,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_price', $item_price,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':primary_cat_id', $cat_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('20'.$e->getMessage());}

                if($cat_id) {
                    $this->uCat->attach_item2cat($cat_id, $item_id,0,$this->site_id);
                    if($sect_id)
                        $this->uCat->attach_cat2sect($sect_id, $cat_id,0,$this->site_id);
                }
            }
            else {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
                    u235_items
                    SET 
                    quantity=:quantity,
                    unit_id=:unit_id,
                    item_title=:item_title,
                    item_descr=:item_descr,
                    item_price=:item_price,
                    item_article_number=:item_article_number,
                    primary_cat_id=:primary_cat_id
                    WHERE 
                        item_id=:item_id AND
                        site_id=:site_id
                    ");
                    $item_title=uString::text2sql($item_title,1);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':quantity', $quantity,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':unit_id', $unit_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title', $item_title,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_descr', $item_descr,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_price', $item_price,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':primary_cat_id', $cat_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('30'.$e->getMessage());}


                if($cat_id) {
                    $this->uCat->attach_item2cat($cat_id, $item_id);
                    if($sect_id)
                        $this->uCat->attach_cat2sect($sect_id, $cat_id);
                }
            }


            if ($item_img_key_identifier==="item_img_url") {
                echo $img_url=$file_line[$item_img_key];
                print "<br>";

                $dir='uCat/item_avatars/'.$this->site_id.'/'; //Адрес директории для сохранения картинки

                $this->update_img_time_for_item($item_id,$this->site_id);
                $this->uCat->save_item_avatar($dir,$img_url,$item_id);
            }



            if(time()-$this->start_time>$this->time_limit) {
//                $i--;
                break;
            }
        }

//        $lines_imported_upd=/*$this->lines_imported+*/$i;
        if($this->lines_total<=$i) {//Список успешно импортирован
            $new_status=2;//Готово
            unlink($this->filepath);
        }
        else {
            $new_status=1;//В процессе
        }


        $this->update_file_and_info($new_status,$i);

        print "File path: ";
        print $this->filepath;
        print "<br>";
        print "List Name:";
        print $this->list_name;
        print "<br>";
        print "Lines total:";
        print $this->lines_total;
        print "<br>";
        print "Lines imported:";
        print $i;
        print "<br>";
        print "Lines skipped:";
        print $this->lines_to_skip;
        print "<br>";
        print "Resulting status:";
        print $this->status;
    }

    private function update_file_and_info($new_status,$lines_imported_upd) {
        //Обновляем информацию о прайсе
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_import
            SET
            status=:status,
            lines_imported=:lines_imported
            WHERE
            list_id=:list_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $new_status,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lines_imported', $lines_imported_upd,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':list_id', $this->list_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->start_time=time();
        $this->time_limit=45;

        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        $this->uSes = new uSes($this->uCore);
        $this->uCat = new common($this->uCore);
        $this->import = new import_class($this->uCore);

        if(!$this->file=$this->uCat->get_import_file()) exit;

        $this->filepath = $this->file->filepath;
        $this->list_id=(int)$this->file->list_id;
        $this->list_name=$this->file->list_name;
        $this->lines_total=(int)$this->file->lines_total;
        $this->lines_to_skip=(int)$this->file->lines_to_skip;
        $this->lines_imported=(int)$this->file->lines_imported;
        $this->extension = $this->file->extension;
        $this->columns = json_decode($this->file->columns,1);
        $this->delimiter = $this->file->delimiter;
        $this->status = (int)$this->file->status;
        $this->site_id = (int)$this->file->site_id;

        $this->data_preparation();
    }
}
new cron_items_list_importer($this);
