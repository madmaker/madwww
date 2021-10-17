<?php

$connect = array(
    'wsdl'    => 'http://api.rossko.ru/service/GetSearch',
    'options' => array(
        'connection_timeout' => 1,
        'trace' => true
    )
);

$param = array(
    'KEY1' => 'c9a6e68568d74033d32fbee0c85b9d44',
    'KEY2' => '156a41f0e6c063f1ccbf89ddcaf6e156',
    'TEXT' => $this->search
);

$query  = new SoapClient($connect['wsdl'], $connect['options']);
$result = $query->GetSearch($param);

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

if(isset($result->SearchResult)) {
    if($result->SearchResult->success) {
        if(isset($result->SearchResult->PartsList)) {
            if(isset($result->SearchResult->PartsList->Part)) {?>
                <?foreach ($result->SearchResult->PartsList->Part as $key=>$value) {
                    if(isset($value->stocks)||isset($value->crosses)) {
                        if(isset($value->stocks->stock)) {
                            foreach ($value->stocks as $stockKey=>$stockVal) {
                                if(is_array($stockVal)) {
                                    foreach ($stockVal as $substokKey=>$substockVal) {
                                        $brand_ar[$j]=$value->brand;
                                        $brand_img_ar[$j]="";
                                        $type_ar[$j]="Оригинал";
                                        $part_number_ar[$j]=$value->partnumber;
                                        $part_name_ar[$j]=$value->name;
                                        $price_ar[$j]=$substockVal->price;
                                        $quantity_ar[$j]=$substockVal->count;
                                        $supply_ar[$j]='RS<br>
                                        ID '.$value->guid.'<br>
                                        Склад: '.$substockVal->id;

                                        $supplier_ar[$j]="RS";
                                        $j++;
                                    }
                                } else {
                                    $brand_ar[$j]=$value->brand;
                                    $brand_img_ar[$j]="";
                                    $type_ar[$j]="Оригинал";
                                    $part_number_ar[$j]=$value->partnumber;
                                    $part_name_ar[$j]=$value->name;
                                    $price_ar[$j]=$stockVal->price;
                                    $quantity_ar[$j]=$stockVal->count;
                                    $supply_ar[$j]='RS<br>
                                        ID '.$value->guid.'<br>
                                        Склад: '.$stockVal->id;

                                    $supplier_ar[$j]="RS";

                                    $j++;
                                }
                            }
                        }
                        if(isset($value->crosses)) {?>
                            <?
                            foreach ($value->crosses->Part as $crossesKey=>$crossesValue) {
                                if (isset($crossesValue->stocks)) {
                                    foreach ($crossesValue->stocks->stock as $stockKey=>$stockVal) {
                                        if (isset($stockVal->count)) {
                                            $brand_ar[$j]=$value->brand;
                                            $brand_img_ar[$j]="";
                                            $type_ar[$j]="Замена";
                                            $part_number_ar[$j]=$value->partnumber;
                                            $part_name_ar[$j]=$value->name;
                                            $price_ar[$j]=$stockVal->price;
                                            $quantity_ar[$j]=$stockVal->count;
                                            $supply_ar[$j]='RS<br>
                                        ID '.$value->guid.'<br>
                                        Склад: '.$stockVal->id;

                                            $supplier_ar[$j]="RS";

                                            $j++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }?>
                <?
            }?>
        <?}
    }
}