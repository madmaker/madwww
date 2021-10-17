<?php

//$arrContextOptions=array(
//    "ssl"=>array(
//        "verify_peer"=>false,
//        "verify_peer_name"=>false,
//    ),
//);

$ch_url = "http://voshod-avto.ru.public.api.abcp.ru/search/articles/?userlogin=cenzap@mail.ru&userpsw=".md5("vk071088")."&number=".$this->search;

$response=file_get_contents($ch_url);
//$response=mb_convert_encoding($response,
//    'UTF-8',
//        'windows-1251');

$parts=json_decode($response);
/*?>

<table class="table table-striped">
                <tr>
                    <th>"nr" - Номер производителя</th>
                    <th>"brand" - Название производителя</th>
                    <th>"name" - Название автозапчасти</th>
                    <th>"stock" - Кол-во позиций на складе, ("0" - нет наличии, "+" - есть в наличии, но кол-во не указано)</th>
                    <th>"sorder" - Кол-во позиций доступных под заказ, ("0" - нет наличии, "+" - есть в наличии, но кол-во не указано)</th>
                    <th>"delivery" - Средний срок поставки</th>
                    <th>"minq" - Минимальное кол-во для заказа</th>
                    <th>"upd" - Дата, время на которое актуальны данные</th>
                    <th>"price" - Цена для данного клиента</th>
                    <th>"currency" - Валюта в которой представлена цена</th>
                    <th>"gid" - Если указан доп. параметр "gid", ID товара в прайс-листе. </th>
                    <th>brand Объект бранда</th>
                    <th>name Наиманование товара</th>
                </tr>
                <?foreach ($parts->result as $key=>$value) {?>
                        <tr>
                            <td><?=$value->nr?></td>
                            <td><?=$value->brand?></td>
                            <td><?=$value->name?></td>
                            <td><?=$value->stock?></td>
                            <td><?=$value->sorder?></td>
                            <td><?=$value->delivery?></td>
                            <td><?=$value->minq?></td>
                            <td><?=$value->upd?></td>
                            <td><?=$value->price?></td>
                            <td><?=$value->currency?></td>
                            <td><?=$value->gid?></td>
                            <td><?=$value->brand?></td>
                            <td><?=$value->name?></td>
                        </tr>
                <?}?>
            </table>
<?*/
//echo count($parts);
echo "<pre>";
print_r($parts->result);
echo "</pre>";

