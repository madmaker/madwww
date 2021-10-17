<?php
namespace uCat\api;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class search {
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;


    public function search($request) {
        $db_request="%".uString::replace4sqlLike(uString::text2sql($request))."%";

        /*if((int)$this->uFunc->getConf("search_in_item_descr","uCat")) $q_item_descr=" item_descr LIKE :req OR ";
        else */$q_item_descr="";

        //1. search direct query in items only (fields values, item data)
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT DISTINCT
            item_id,
            item_article_number,
            item_img_time,
            item_title,
            item_price,
            avail_label,
            avail_descr,
            manufacturer,
            manufacturer_part_number,
            search_part_number,
            orig_replacement
            FROM
            u235_items
            JOIN 
            u235_items_avail_values
            ON 
            item_avail=avail_id AND
            u235_items.site_id=u235_items_avail_values.site_id
            WHERE
            parts_autoadd=0 AND
            (
                manufacturer_part_number LIKE :req OR
                search_part_number LIKE :req OR
                item_id LIKE :req OR
                item_article_number LIKE :req OR
                item_title LIKE :req OR
                ".$q_item_descr."
                item_keywords LIKE :req 
            )
            AND
            u235_items_avail_values.avail_type_id!=2 AND
            cat_count>0 AND
            u235_items.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':req', $db_request,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();

        $this->uFunc=new uFunc($this->uCore);

    }
}
