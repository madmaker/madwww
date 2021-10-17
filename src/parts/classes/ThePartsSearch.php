<?php
namespace parts;

use ThePartsWS;

require_once "parts/classes/common.php";
require_once "parts/classes/ThePartsWS.class.php";

class ThePartsSearch {
    private $parts;
    private $price_gain;
    private $ws;

//    private function delivery_days2date($days) {
//        $days=(int)$days;
//        if(!isset($this->delivery_days2date_ar)) $this->delivery_days2date_ar=[];
//
//        if(!isset($this->delivery_days2date_ar[$days])) {
//            $this->delivery_days2date_ar[$days]=date("d.m.Y",time()+$days*86400);
//        }
//
//        return $this->delivery_days2date_ar[$days];
//    }
    private function item_type2str_the_parts($type) {
        if(!isset($this->item_type2str_the_parts_ar)) $this->item_type2str_the_parts_ar=[];
        if(!isset($this->item_type2str_the_parts_ar[$type])) {
            if($type==="Original") $this->item_type2str_the_parts_ar[$type]="Оригинал";
            elseif($type==="ReplacementOriginal") $this->item_type2str_the_parts_ar[$type]="Замена";
            elseif($type==="ReplacementNonOriginal") $this->item_type2str_the_parts_ar[$type]="Неоригинал";
        }

        return $this->item_type2str_the_parts_ar[$type];
    }
    public function search_the_parts($search,$site_id=site_id) {
        /** @noinspection PhpUndefinedMethodInspection */
        $searchResult = $this->ws->searchDo2( $search, array(
            'present' => true,
            'original' => false,
            'noreplace' => false
        ) );

//        $search_id=$this->parts->save_result($searchResult,$site_id);

        $brand_ar=[];
        $brand_img_ar=[];
        $type_ar=[];
        $part_number_ar=[];
        $part_name_ar=[];
        $price_ar=[];
        $quantity_ar=[];
        $supply_ar=[];
        $supplier_ar=[];


        foreach ($searchResult as $result) {
                $brands_count=count($result["brands"]);
                $items_count=count($result["items"]);

                for($j=0;$j<$items_count;$j++) {
                    $item=$result["items"][$j];
                    $sell_price=$item["price"]*$this->price_gain;
//                    if(!(int)$item["days_avg"]) $class='bg-success';
//                    else $class="";


                    $brand_ar[$j]="";
                    $brand_img_ar[$j]="";

                    for ($i = 0; $i < $brands_count; $i++) {
                        $brand=$result["brands"][$i];
                        if(isset($result["brand_img"])) {
                            if ($result["brand_img"] !== "") {
                                $brand_img_ar[$j]=$result["brand_img"];
                            }
                        }

                        $brand_ar[$j]=$brand;
                    }
                    if($brand_ar[$j]=="") $brand_ar[$j]=$item["chname"];

                    $type_ar[$j]=$this->item_type2str_the_parts($item["group_rel"]);
                    $part_number_ar[$j]=$item["code"];
                    $part_name_ar[$j]=$item["name"];
                    $price_ar[$j]=$item["price"];
                    $quantity_ar[$j]=$item["stock"];
                    $supply_ar[$j]="TP ".$item["source_fullname"].' - '.$item["id"];
                    $supplier_ar[$j]="TP";}
            }
            return array($brand_ar,
            $brand_img_ar,
            $type_ar,
            $part_number_ar,
            $part_name_ar,
            $price_ar,
            $quantity_ar,
            $supply_ar,
                $supplier_ar);
    }
    function __construct (&$uCore,$price_gain) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->parts=new common($this->uCore);
        $this->price_gain=$price_gain;
        /** @noinspection SpellCheckingInspection */
        $login="127392";
        $password = '4GZxbTLdpt';
        $this->ws = new ThePartsWS( $login, $password );
    }
}