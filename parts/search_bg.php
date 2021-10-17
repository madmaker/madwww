<?

namespace parts;
//use processors\uFunc;
//use uSes;

//require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

require_once "parts/classes/common.php";

class search_bg {
    public $ThePartsSearch;
    /**
     * @var common
     */
    private $parts;
    private $search;
    private $uCore;
    private function check_data() {
        if(isset($_GET["search"])) {
            $this->search=$_GET["search"];
            return 1;
        }
        if(!isset($_POST["search"])) {
            exit(/*json_encode(array(
                "status"=>"error",*/
                /*"msg"=>*/"wrong search request"/*
            ))*/);
        }
        $this->search=$_POST["search"];
        return 1;
    }


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) /** @noinspection PhpFullyQualifiedNameUsageInspection */ $this->uCore=new \uCore();

        $this->parts=new common($this->uCore);

        $price_gain=1.15;

        $brand_ar_common=[];
        $brand_img_ar_common=[];
        $type_ar_common=[];
        $part_number_ar_common=[];
        $part_name_ar_common=[];
        $price_ar_common=[];
        $quantity_ar_common=[];
        $supply_ar_common=[];
        $supplier_ar_common=[];


        $this->check_data();

        $brand_ar=[];
        $brand_img_ar=[];
        $type_ar=[];
        $part_number_ar=[];
        $part_name_ar=[];
        $price_ar=[];
        $quantity_ar=[];
        $supply_ar=[];
        $supplier_ar=[];

        //uCat
        require "parts/classes/uCatSearch.php";
        //ST-13717571355


        $brand_ar_common=array_merge($brand_ar_common,$brand_ar);
        $brand_img_ar_common=array_merge($brand_img_ar_common,$brand_img_ar);
        $type_ar_common=array_merge($type_ar_common,$type_ar);
        $part_number_ar_common=array_merge($part_number_ar_common,$part_number_ar);
        $part_name_ar_common=array_merge($part_name_ar_common,$part_name_ar);
        $price_ar_common=array_merge($price_ar_common,$price_ar);
        $quantity_ar_common=array_merge($quantity_ar_common,$quantity_ar);
        $supply_ar_common=array_merge($supply_ar_common,$supply_ar);
        $supplier_ar_common=array_merge($supplier_ar_common,$supplier_ar);

        //Autotrade - AT
        include "parts/classes/autotradeSearch.php";
        //ST-13717571355


        $brand_ar_common=array_merge($brand_ar_common,$brand_ar);
        $brand_img_ar_common=array_merge($brand_img_ar_common,$brand_img_ar);
        $type_ar_common=array_merge($type_ar_common,$type_ar);
        $part_number_ar_common=array_merge($part_number_ar_common,$part_number_ar);
        $part_name_ar_common=array_merge($part_name_ar_common,$part_name_ar);
        $price_ar_common=array_merge($price_ar_common,$price_ar);
        $quantity_ar_common=array_merge($quantity_ar_common,$quantity_ar);
        $supply_ar_common=array_merge($supply_ar_common,$supply_ar);
        $supplier_ar_common=array_merge($supplier_ar_common,$supplier_ar);

        //Armtek - AM
        //include "parts/classes/ArmtekSearch.php";
        //Ошибка авторизации

//        //Rosko - RS
//        include "parts/classes/roskoSearch.php";
//        //5DM
//
//        $brand_ar_common=array_merge($brand_ar_common,$brand_ar);
//        $brand_img_ar_common=array_merge($brand_img_ar_common,$brand_img_ar);
//        $type_ar_common=array_merge($type_ar_common,$type_ar);
//        $part_number_ar_common=array_merge($part_number_ar_common,$part_number_ar);
//        $part_name_ar_common=array_merge($part_name_ar_common,$part_name_ar);
//        $price_ar_common=array_merge($price_ar_common,$price_ar);
//        $quantity_ar_common=array_merge($quantity_ar_common,$quantity_ar);
//        $supply_ar_common=array_merge($supply_ar_common,$supply_ar);
//        $supplier_ar_common=array_merge($supplier_ar_common,$supplier_ar);



        //echo "<h3>Shate-M</h3>";
        //Shate-M - SM
        //include "parts/classes/shateSearch.php";
        //Что-то не работает

        //Berg
        include "parts/classes/bergSearch.php";
        //082009003HE

        $brand_ar_common=array_merge($brand_ar_common,$brand_ar);
        $brand_img_ar_common=array_merge($brand_img_ar_common,$brand_img_ar);
        $type_ar_common=array_merge($type_ar_common,$type_ar);
        $part_number_ar_common=array_merge($part_number_ar_common,$part_number_ar);
        $part_name_ar_common=array_merge($part_name_ar_common,$part_name_ar);
        $price_ar_common=array_merge($price_ar_common,$price_ar);
        $quantity_ar_common=array_merge($quantity_ar_common,$quantity_ar);
        $supply_ar_common=array_merge($supply_ar_common,$supply_ar);
        $supplier_ar_common=array_merge($supplier_ar_common,$supplier_ar);



        //ForumAuto - FA
        //include "parts/classes/forumAutoSearch.php";
        //Не работает логин пароль


        //Moskvorechie - MR
        include "parts/classes/moskvorechieSearch.php";


        $brand_ar_common=array_merge($brand_ar_common,$brand_ar);
        $brand_img_ar_common=array_merge($brand_img_ar_common,$brand_img_ar);
        $type_ar_common=array_merge($type_ar_common,$type_ar);
        $part_number_ar_common=array_merge($part_number_ar_common,$part_number_ar);
        $part_name_ar_common=array_merge($part_name_ar_common,$part_name_ar);
        $price_ar_common=array_merge($price_ar_common,$price_ar);
        $quantity_ar_common=array_merge($quantity_ar_common,$quantity_ar);
        $supply_ar_common=array_merge($supply_ar_common,$supply_ar);
        $supplier_ar_common=array_merge($supplier_ar_common,$supplier_ar);


        //The-parts
        require_once "classes/ThePartsSearch.php";
        $this->ThePartsSearch=new ThePartsSearch($this->uCore,$price_gain);
        $res_array=$this->ThePartsSearch->search_the_parts($this->search);


        $brand_ar_common=array_merge($brand_ar_common,$res_array[0]);
        $brand_img_ar_common=array_merge($brand_img_ar_common,$res_array[1]);
        $type_ar_common=array_merge($type_ar_common,$res_array[2]);
        $part_number_ar_common=array_merge($part_number_ar_common,$res_array[3]);
        $part_name_ar_common=array_merge($part_name_ar_common,$res_array[4]);
        $price_ar_common=array_merge($price_ar_common,$res_array[5]);
        $quantity_ar_common=array_merge($quantity_ar_common,$res_array[6]);
        $supply_ar_common=array_merge($supply_ar_common,$res_array[7]);
        $supplier_ar_common=array_merge($supplier_ar_common,$res_array[8]);

        $searchResult=array($brand_ar_common,
        $brand_img_ar_common,
        $type_ar_common,
        $part_number_ar_common,
        $part_name_ar_common,
        $price_ar_common,
        $quantity_ar_common,
        $supply_ar_common,
        $supplier_ar_common);


        $search_id=$this->parts->save_result($searchResult,site_id);

        $results_count=count($supply_ar_common);


        $parts_by_type_ar=[];
        $parts_by_type_ar[0]=[];//Оригинал
        $parts_by_type_ar[1]=[];//Замена. Оригинал
        $parts_by_type_ar[2]=[];;//Неоригинал
        $parts_by_type_ar[3]=[];//Замена. Неоригинал


        $parts_by_part_number_ar=[];
        $parts_by_part_number_lowest_price_brand_ar=[];
        $parts_by_part_number_lowest_price_price_ar=[];
        $parts_by_part_number_lowest_price_quantity_ar=[];
        $parts_by_part_number_lowest_price_supply_ar=[];
//        $parts_by_part_number_lowest_price_supplier_ar=[];
        $parts_by_part_number_ar_key_finder_ar=[];


        for($parts_by_type_i=0;$parts_by_type_i<4;$parts_by_type_i++) {
            $parts_by_part_number_ar[$parts_by_type_i]=[];
        }

        for($i=0;$i<$results_count;$i++) {
            $parts_by_type_i=0;
            /*if($type_ar_common[$i]==="Оригинал") $parts_by_type_i=0;
            else*/if($type_ar_common[$i]==="Замена. Оригинал") $parts_by_type_i=1;
            elseif($type_ar_common[$i]==="Неоригинал") $parts_by_type_i=2;
            elseif($type_ar_common[$i]==="Замена. Неоригинал") $parts_by_type_i=3;

            $parts_by_type_ar[$parts_by_type_i][]=$i;

            if(!isset($parts_by_part_number_ar_key_finder_ar[$parts_by_type_i][$part_number_ar_common[$i]])) {
                $parts_by_part_number_ar_i=count($parts_by_part_number_ar[$parts_by_type_i]);
                $parts_by_part_number_ar[$parts_by_type_i][$parts_by_part_number_ar_i]=[];
                $parts_by_part_number_ar_key_finder_ar[$parts_by_type_i][$part_number_ar_common[$i]]=$parts_by_part_number_ar_i;

                $parts_by_part_number_lowest_price_price_ar[$parts_by_type_i][$parts_by_part_number_ar_i]=0;
            }
            else $parts_by_part_number_ar_i=$parts_by_part_number_ar_key_finder_ar[$parts_by_type_i][$part_number_ar_common[$i]];

            if($parts_by_part_number_lowest_price_price_ar[$parts_by_type_i][$parts_by_part_number_ar_i]===0||$price_ar_common[$i]<$parts_by_part_number_lowest_price_price_ar[$parts_by_type_i][$parts_by_part_number_ar_i]) {
                $parts_by_part_number_ar[$parts_by_type_i][$parts_by_part_number_ar_i][] = $i;
                $parts_by_part_number_lowest_price_brand_ar[$parts_by_type_i][$parts_by_part_number_ar_i] = $brand_ar_common[$i];
                $parts_by_part_number_lowest_price_price_ar[$parts_by_type_i][$parts_by_part_number_ar_i] = $price_ar_common[$i];
                $parts_by_part_number_lowest_price_quantity_ar[$parts_by_type_i][$parts_by_part_number_ar_i] = $quantity_ar_common[$i];
                $parts_by_part_number_lowest_price_supply_ar[$parts_by_type_i][$parts_by_part_number_ar_i] = $supply_ar_common[$i];
//            $parts_by_part_number_lowest_price_supplier_ar[$parts_by_type_i][$parts_by_part_number_ar_i]=$supplier_ar_common[$i];
            }
        }

        ?>
        <table class="table table-hover table-condensed">
        <tr>
            <th>Бренд</th>
            <th>Артикул</th>
            <th>Наименование</th>
            <th>Цена</th>
            <th>Наличие</th>
            <th>Поставка</th>
            <th></th>
        </tr>
        <?

        for($parts_by_type_i=0;$parts_by_type_i<4;$parts_by_type_i++) {
            $parts_by_part_number_ar_count=count($parts_by_part_number_ar[$parts_by_type_i]);

            if($parts_by_part_number_ar_count) {?>
                <tr>
                    <td colspan="7"><h3><?
                        if($parts_by_type_i===0) print "Оригинал";
                        elseif ($parts_by_type_i === 1) print "Замена. Оригинал";
                        elseif ($parts_by_type_i === 2) print "Неоригинал";
                        elseif ($parts_by_type_i === 3) print "Замена. Неоригинал";
                        ?></h3></td>
                </tr>
            <?}

            for ($parts_by_part_number_ar_i = 0; $parts_by_part_number_ar_i < $parts_by_part_number_ar_count;$parts_by_part_number_ar_i++) {

                $parts_by_part_number_ar2_count=count($parts_by_part_number_ar[$parts_by_type_i][$parts_by_part_number_ar_i]);

                $last_part_number="";
                $last_part_number_i=0;
                $show_more_btn_is_shown=1;
                for($j=0;$j<$parts_by_part_number_ar2_count;$j++) {
                    $i = $parts_by_part_number_ar[$parts_by_type_i][$parts_by_part_number_ar_i][$j];

                    if($supplier_ar_common[$i]==='uCat') $sell_price=$price_ar_common[$i];
                    else $sell_price = $price_ar_common[$i] * $price_gain;

                    if($last_part_number!==$part_number_ar_common[$i]) {
                        $show_more_btn_is_shown=0;
                        $last_part_number=$part_number_ar_common[$i];
                        $last_part_number_i=$i;


                        $brand=$parts_by_part_number_lowest_price_brand_ar[$parts_by_type_i][$parts_by_part_number_ar_i];
                        $price=$parts_by_part_number_lowest_price_price_ar[$parts_by_type_i][$parts_by_part_number_ar_i];

                        if($supplier_ar_common[$i]==='uCat') $sell_price=$price;
                        else $sell_price = $price * $price_gain;

                        $quantity=$parts_by_part_number_lowest_price_quantity_ar[$parts_by_type_i][$parts_by_part_number_ar_i];
                        $supply=$parts_by_part_number_lowest_price_supply_ar[$parts_by_type_i][$parts_by_part_number_ar_i];
//                        $supplier=$parts_by_part_number_lowest_price_supplier_ar[$parts_by_type_i][$parts_by_part_number_ar_i];
                        ?>
                        <tr id="initial_part_number_<?=$last_part_number_i?>">
                            <td><?
                                print $brand;
                                if ($brand_img_ar_common[$i] !== "") print '<br><img src="' . $brand_img_ar_common[$i] . '" />';
                                ?>
                            </td>
                            <td><?= $part_number_ar_common[$i]; ?></td>
                            <td><?= $part_name_ar_common[$i]; ?></td>
                            <td><?= number_format((int)($sell_price), 0, "", " ") ?></td>
                            <td><?= $quantity; ?></td>
                            <td><?= $supply; ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                        onclick="parts.search.buy(<?= $search_id ?>0,<?= $i ?>,<?= $sell_price ?>)"><span
                                            class="icon-cart-plus"></span> В корзину
                                </button>
                            </td>
                        </tr>
                    <?}
                    else {
                        if (!$show_more_btn_is_shown) {
                            $show_more_btn_is_shown = 1; ?>
                            <tr>
                                <td colspan="7">
                                    <a id="show_part_number_<?=$last_part_number_i?>_btn" href="javascript:void(0)" onclick="
                                            $('.part_number_<?=$last_part_number_i?>').show();
                                            $('#hide_part_number_<?=$last_part_number_i?>_btn').show();
                                            //$('#initial_part_number_<?//=$last_part_number_i?>//').hide();
                                            $(this).hide();
                                            ">Посмотреть еще варианты <?=$part_number_ar_common[$i]?></a>
                                    <a id="hide_part_number_<?=$last_part_number_i?>_btn" href="javascript:void(0)" onclick="
                                            $('.part_number_<?=$last_part_number_i?>').hide();
                                            $('#show_part_number_<?=$last_part_number_i?>_btn').show();
                                            //$('#initial_part_number_<?//=$last_part_number_i?>//').show();
                                            $(this).hide();
                                            " style="display: none">Свернуть варианты <?=$part_number_ar_common[$i]?></a>
                                </td>
                            </tr>
                        <?
                        }
                    }

                    ?>
                    <tr class="part_number_<?=$last_part_number_i?>" style="display: none">
                        <td><?
                            print $brand_ar_common[$i];
                            if ($brand_img_ar_common[$i] !== "") print '<br><img src="' . $brand_img_ar_common[$i] . '" />';
                            ?>
                        </td>
                        <td><?= $part_number_ar_common[$i]; ?></td>
                        <td><?= $part_name_ar_common[$i]; ?></td>
                        <td><?= number_format((int)($sell_price), 0, "", " ") ?></td>
                        <td><?= $quantity_ar_common[$i]; ?></td>
                        <td><?= $supply_ar_common[$i]; ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                    onclick="<?
                    if($supplier_ar_common[$i]==='uCat'){?>uCat_cart.buy=function(item_id,item_price,var_id);<?}
                    else {?>parts.search.buy(<?= $search_id ?>0,<?= $i ?>,<?= $sell_price ?>);<?}?>
                    "><span
                                        class="icon-cart-plus"></span> В корзину
                            </button>
                        </td>
                    </tr>
                    <?
                }
            }
        }


        //Autorus
        //include "parts/classes/autorusSearch.php";
        //Ошибка авторизации



        ?></table><?

        ob_start();
        $result=ob_get_contents();
        ob_end_clean();

        /*print json_encode(array(
        "status"=>"done",
        "result"=>*/print $result;
        //));

    }
}
/*$search=*/new search_bg($this);
