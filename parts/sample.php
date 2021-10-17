<?

namespace parts;
//use processors\uFunc;
//use uSes;

use ThePartsWS;

require_once "parts/classes/ThePartsWS.class.php";
//require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class sample {
    private $price_gain;
    private $search;
    private $uCore;
    private function check_data() {
        $this->search=$_GET["search"];
    }

    private function delivery_days2date($days) {
        $days=(int)$days;
        if(!isset($this->delivery_days2date_ar)) $this->delivery_days2date_ar=[];

        if(!isset($this->delivery_days2date_ar[$days])) {
            $this->delivery_days2date_ar[$days]=date("d.m.Y",time()+$days*86400);
        }

        return $this->delivery_days2date_ar[$days];
    }

    private function item_type2str_the_parts($type) {
        if(!isset($this->item_type2str_the_parts_ar)) $this->item_type2str_the_parts_ar=[];
        if(!isset($this->item_type2str_the_parts_ar[$type])) {
            if($type==="Original") $this->item_type2str_the_parts_ar[$type]="Оригинал";
            elseif($type==="ReplacementOriginal") $this->item_type2str_the_parts_ar[$type]="Замена. Оригинал";
            elseif($type==="ReplacementNonOriginal") $this->item_type2str_the_parts_ar[$type]="Замена. Неоригинал";
        }

        return $this->item_type2str_the_parts_ar[$type];
    }
    public function search_the_parts() {
        $password = '4GZxbTLdpt';
//        echo '<pre>';
        $ws = new ThePartsWS( '127392', $password );
//        $token = $ws->authorize();//Регистрирует в системе уникальный токен авторизации. В дальнейшем его можно использовать вместо связки логин+пароль, что существенно безопаснее.
//        $ws = new ThePartsWS( $token );Регистрирует в системе уникальный токен авторизации. В дальнейшем его можно использовать вместо связки логин+пароль, что существенно безопаснее.
        //Каждый токен привязывается к ip-адресу, с которого был зарегистрирован.
        //Время жизни токена - 90 дней с момента последнего использования.
        //При смене пароля через сервис восстановления - все зарегистрированные токены будут удалены.
///
//        $me = $ws->userInfo();
//        $balance = $ws->balance();
//        $brands = $ws->brandsGet();
//        $ac = $ws->autocomplete( '1k0', 5 );

//9091510001
        $searchResult = $ws->searchDo2( $this->search, array(
            'present' => true,
            'original' => false,
            'noreplace' => false
        ) ); /* */?>
        <table class="table table-striped">
        <?foreach ($searchResult as $result) {
            $brands_count=count($result["brands"]);
            $items_count=count($result["items"]);?>
            <tr>
                <th>Бренд</th>
                <th>Тип</th>
                <th>Артикул</th>
                <th>Наименование</th>
                <th>Наличие</th>
                <th>Цена</th>
                <th>Ожидаем на складе</th>
                <th></th>
            </tr>
            <?
            for($j=0;$j<$items_count;$j++) {
                $item=$result["items"][$j];
                ?>
                <tr>
                    <?if(!$j) {?>
                        <td rowspan="<?=$items_count?>"><?
                            for ($i = 0; $i < $brands_count; $i++) {
                                $brand=$result["brands"][$i];
                                if(isset($result["brand_img"])) {
                                    if ($result["brand_img"] !== "") { ?>
                                        <img src="<?= $result["brand_img"] ?>" class="img-responsive">
                                    <?
                                    }
                                }
                                print $brand;
                                print "<br>";
                            } ?>
                        </td>
                    <?}?>
                <td><?=$this->item_type2str_the_parts($item["group_rel"])?></td>
                <td><?=$item["code"]?></td>
                <td><?=$item["name"]?></td>
                <td>
                    <?=$item["stock"]?>
                </td>
                <td>
                    <?=number_format($item["price"]*$this->price_gain,2,'.',' ')?><br>
                    <?if($item["quant"]>1) {?>
                        от <?=$item["quant"]?> шт.
                    <?}?>
                </td>
                <td><?=$this->delivery_days2date($item["days_avg"])?><br>
                    TP <?=$item["chname"]?> - <?=$item["id"]?></td>
                <td><button class="btn btn-sm btn-primary"><span class="icon-cart-plus"></span> В корзину</button></td>
                </tr>
            <?}
//            print_r($result);
            ?>
            <?
        }?>
        </table>
<?//        print_r($searchResult);
    }


    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();

        $this->price_gain=1.15;

//        $this->uSes=new uSes($this->uCore);
        //if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
//        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

    }
}
$search=new sample($this);

ob_start();

$search->search_the_parts();

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';

/*if( !$password ) */
#$history = $ws->searchHistory( 3 );
#$addResult = $ws->basketAdd( $searchResult['g5'][8]['id'], 1, 'test' );
#$delResult = $ws->basketDel( $basket[0]['id'] );
#$addResult = $ws->basketAdd( $searchResult['g5'][8]['id'], 3, 'test' );
#$addResult = $ws->basketAdd( $data['g5'][0]['id'], 4, 'test2' );
#$basketResult = $ws->basketGet();
#$clearResult = $ws->basketClear();
/*
$orderResult = $ws->basketOrder( array( 
	$basketResult[0]['id'] 
), true );
*/
/*
$data = $ws->journalDetails( array( 
	'year' => date('Y')
), 5 );
*/
