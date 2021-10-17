<?php
namespace parts;

use PDO;
use processors\uFunc;
use item_avatar;
use uCore;

require_once "processors/classes/uFunc.php";
require_once 'uCat/inc/item_avatar.php';
require_once "uCat/classes/search.php";

class uCatSearch
{
    /**
     * @var \uCat\api\search
     */
    private $uCat_search;
    /**
     * @var item_avatar
     */
    private $uCat_item_avatar;
    /**
     * @var uFunc
     */
    private $uFunc;
    private $uCore;

    function __construct(&$uCore) {
        $this->uCore =& $uCore;
        if (!isset($this->uCore)) $this->uCore = new uCore();

        $this->uFunc = new uFunc($this->uCore);

        $this->item_avatar=new item_avatar($this->uCore);
        $this->uCat_search=new \uCat\api\search($this->uCore);
    }

    public function search($request) {
        return $this->uCat_search->search($request);
    }
}

$uCatSearch=new uCatSearch($this->uCore);
$stm=$uCatSearch->search($this->search);


$brand_ar=[];
$brand_img_ar=[];
$type_ar=[];
$part_number_ar=[];
$part_name_ar=[];
$price_ar=[];
$quantity_ar=[];
$supply_ar=[];
$supplier_ar=[];
$j=0;

if($stm) {
    /** @noinspection PhpUndefinedMethodInspection */
    while($item=$stm->fetch(PDO::FETCH_OBJ)) {
        $brand_ar[$j]=$item->manufacturer;
        $brand_img_ar[$j]="";
        $type_ar[$j]=(int)$item->orig_replacement===0?"Оригинал":"Замена";
        $part_number_ar[$j]=$item->manufacturer_part_number;
        $part_name_ar[$j]=\uString::sql2text($item->item_title,1);
        $price_ar[$j]=$item->item_price;
        $quantity_ar[$j]=$item->avail_label;
        $supply_ar[$j]='Склад';
        $supplier_ar[$j]="uCat";

        $j++;
    }?>
<?}


new item_avatar($this->uCore);
