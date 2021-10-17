<?php
$key=MD5("cenzap@mail.ru" . MD5("profitlyam1000000") . "1>6)/MI~{J");

//header('Accept: application/json, text/javascript, */*; q=0.01');
$url = "https://api2.autotrade.su/?json";

$data = array(
    "auth_key" => $key,
    "method" => "getItemsByQuery",
    "params" => array(
        "q" => array(
            $this->search
        ),
        "strict" => 0,
        "replace" => 1,
        "cross" => 1,
        "with_stocks_and_prices"=>1,
        "discount"=>0
    )
);
$request = 'data=' . json_encode($data);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$html = curl_exec($ch);
curl_close($ch);
$result = json_decode($html, true);

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

if(isset($result["items"])) {?>
                    <?foreach ($result["items"] as $key=>$value) {
                        $has_items=1;
                foreach ($value["stocks"] as $stockKey=>$stockValue) {
                    $stockValue["name"]=str_replace("(Апаринки)","",$stockValue["name"]);
                    if(!(int)$stockValue["quantity_unpacked"]&&!(int)$stockValue["quantity_packed"]&&!array_key_exists("delivery_period",$stockValue)) $has_items=0;
                    $stockHtml='<p>
                        Склад: '.$stockValue["name"].'<br>
                        Отгрузка штучно сегодня: '.$stockValue["quantity_unpacked"].'<br>
                        Отгрузка упаковками через 2-3 дня: '.$stockValue["quantity_packed"].'<br>';
                    if(array_key_exists("delivery_period",$stockValue)) $stockHtml.='Отсутствует, доставка: '.$stockValue["delivery_period"].' дн<br>';
                    $stockHtml.='
                    Ед. Изм: '.$value["unit"].'</p>';
                }
                if(!$has_items) continue;

        $brand_ar[$j]=$value["brand_name"];
        $brand_img_ar[$j]="";
        $type_ar[$j]=$value["type"]==""?"Оригинал":"Замена";
        $part_number_ar[$j]=$value["article"];
        $part_name_ar[$j]=$value["name"];
        $price_ar[$j]=$value["price"];
        $quantity_ar[$j]=$stockHtml;
        $supply_ar[$j]=$value["id"].'<br>
        AT '.$value["inside_id_in"];
        $supplier_ar[$j]="AT";


        /*?>
                            <tr>
                                <td colspan="1"><?=$value["brand_name"]?></td>
                                <td><?=$value["type"]==""?"Оригинал":"Замена"?></td>
                                <td><?=$value["article"]?></td>
                                <td>
                                    <?=$value["name"]?><br>
                                    <a class="fancybox" href="<?=$value["photo"]?>"><img src="<?=$value["photo"]?>" class="img-responsive"></a>//TODO-nik87 Фотки запчастей цеплять в массив
                                </td>
                                <td><?=number_format($value["price"]*$price_gain,2,'.',' ')?> <?=$value["currency"]?></td>
                                <td><?=$stockHtml?></td>
                                <td><?=$value["id"]?><br>
                                AT <?=$value["inside_id_in"]?></td>
                            </tr>
                    <?*/
        $j++;
                    }?>
<?}
