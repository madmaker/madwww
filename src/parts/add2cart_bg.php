<?php
namespace parts;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
require_once "uCat/classes/common.php";
require_once "parts/classes/common.php";
//require_once "processors/uSes.php";

class add2cart_bg{
    private $price_gain;
    private $parts;
    private $uCat;
    private $uFunc;
    private $uCore;



    private function check_data() {
        if(!isset(
            $_POST["array_index"],
            $_POST["search_id"]
        )) $this->uFunc->error(10,1);

        if(!\uString::isDigits($_POST["search_id"])) $this->uFunc->error(20,1);
        $search_id=(int)$_POST["search_id"];

        if(!isset($_SESSION["parts"]["searchResults"][$search_id])) $this->uFunc->error(30,1);
        $searchResult =$_SESSION["parts"]["searchResults"][$search_id];

        $brand_ar_common=$searchResult[0];
        $brand_img_ar_common=$searchResult[1];
        $type_ar_common=$searchResult[2];
        $part_number_ar_common=$searchResult[3];
        $part_name_ar_common=$searchResult[4];
        $price_ar_common=$searchResult[5];
        $quantity_ar_common=$searchResult[6];
        $supply_ar_common=$searchResult[7];
        $supplier_ar_common=$searchResult[8];

        $array_index=(int)$_POST["array_index"];

        if(isset($brand_ar_common[$array_index])) return array(
            $brand_ar_common[$array_index],
            $brand_img_ar_common[$array_index],
            $type_ar_common[$array_index],
            $part_number_ar_common[$array_index],
            $part_name_ar_common[$array_index],
            $price_ar_common[$array_index],
            $quantity_ar_common[$array_index],
            $supply_ar_common[$array_index],
            $supplier_ar_common[$array_index]
        );

        return 0;
    }


    private function create_item($item,$site_id=site_id) {
        $brand=$item[0];
        $brand_img=$item[1];
        $type=$item[2];
        $part_number=$item[3];
        $part_name=$item[4];
        $price=$item[5];
        $quantity=$item[6];
        $supply=$item[7];
        $supplier=$item[8];

        $item_price=number_format($price*$this->price_gain,2,".","");
        $item_cost_price=$price;

        $item_title=$part_number." [".$part_name."] (".$brand.") (".$supplier.")";

        $item_descr="
                <p><b>Тип</b>: ".$type."</p>
                <p><b>Информация о поставке</b>: ".$supply."</p>
                ";

        $item_article_number=$part_number."M".\uString::text2article_number($brand)."S".\uString::text2article_number($supplier);

        $delivery_time=3;

        $item_avail=$this->uCat->get_any_available_avail_id($site_id);

        if(!$item_id=$this->uCat->item_article_number_exists($item_article_number,$site_id)) {
            $item_id=$this->uCat->create_new_item($item_title,$site_id);
            $this->uCat->attach_item2cat(0,$item_id);
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE 
            u235_items
            SET
            item_avail=:item_avail,
            item_article_number=:item_article_number,
            item_price=:item_price,
            quantity=:quantity,
            manufacturer=:manufacturer,
            item_title=:item_title,
            item_descr=:item_descr,
            delivery_time=:delivery_time,
            item_cost_price=:item_cost_price,
            parts_autoadd=1
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_avail', $item_avail,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_article_number', $item_article_number,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_price', $item_price,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':quantity', $quantity,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':manufacturer', $brand,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title', $item_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_descr', $item_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':delivery_time', $delivery_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_cost_price', $item_cost_price,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        return $item_id;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);

        $this->price_gain=1.15;

        $this->parts=new common($this->uCore);
        $this->uCat=new \uCat\common($this->uCore);

        if(!$item=$this->check_data()) $this->uFunc->error(50,1);
        $item_id=$this->create_item($item);

        echo json_encode(array(
            "status"=>"done",
            "item_id"=>$item_id
        ));
    }
}
new add2cart_bg($this);
