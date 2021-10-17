<?php

$ch_url = "https://api.berg.ru/ordering/get_stock.json?items[0][resource_article]=".$this->search."&analogs=1&key=a84bb97f4ca5049efaf3033492db3d75905c7e9521a17f36be2c23116300d0fb";

if(@$response=file_get_contents($ch_url)){

$parts = json_decode($response);


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


foreach ($parts->resources as $key => $value) {
    if (isset($value->offers)) {
        foreach ($value->offers as $stockKey => $stockValue) {
            $brand_ar[$j]=$value->brand->name;
            $brand_img_ar[$j]="";
            $type_ar[$j]="Оригинал";
            $part_number_ar[$j]=$value->article;
            $part_name_ar[$j]=$value->name;
            $price_ar[$j]=$stockValue->price;
            $quantity_ar[$j]=$stockValue->quantity;
            $supply_ar[$j]='BG<br>
                    ID '.$value->id /*.'<br>
            Склад '.$stockValue->warehouse->name*/;

            $supplier_ar[$j]="BG";

            $j++;
        }
    }
}
}