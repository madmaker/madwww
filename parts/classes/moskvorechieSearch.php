<?php

$ch_url = "http://portal.moskvorechie.ru/portal.api?l=eczio77&p=PBLFXw2wNWuEjWY8IPnc43p1ECUCeTrB6flJyhZsaU2CnY29NnCxM7qT9HNfroTG&cs=utf8&act=price_by_nr_firm&nr=".$this->search."&alt&avail&oe&gid";

$response=file_get_contents($ch_url);

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

$parts=json_decode($response);

foreach ($parts->result as $key=>$value) {
    $brand_ar[$j]=$value->brand;
    $brand_img_ar[$j]="";
    $type_ar[$j]="Оригинал";
    $part_number_ar[$j]=$value->nr;
    $part_name_ar[$j]=$value->name;
    $price_ar[$j]=$value->price;
    $quantity_ar[$j]=$value->stock;
    $supply_ar[$j]='MR ID '.$value->gid;
    $supplier_ar[$j]="MR";

    $j++;
}