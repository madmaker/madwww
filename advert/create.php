<?php
namespace advert\create;
use advert\common\advert;
//use PDO;
//use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "advert/classes/advert.php";

class create {
    public $uFunc;
    public $uSes;
    public $advert;
    public $ad_id;
    public $ad_data;
    public $mode;
    private $uCore;

    private function error($er_code) {
        $this->uFunc->error($er_code);
    }
    private function check_data() {
        if(isset($this->uCore->url_prop[1])) {//Вернулись назад к редактированию объявления
            $this->ad_id=$this->uCore->url_prop[1];
            if(!\uString::isDigits($this->ad_id)) $this->error(10);

            //Проверяем, есть ли такое объявление на текущем сайте, у текущего пользователя со статусом "1 just created"
            //Заодно достаем данные объявления
            $this->ad_data=$this->advert->get_ad_data_of_current_user($this->ad_id,'cat_id,item_title,item_descr,location','status=1');
            if(!$this->ad_data) $this->error(20);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if($this->uSes->access(2)) {
            $this->uFunc = new uFunc($this->uCore);
            $this->advert = new advert($this->uCore);

            $this->check_data();
            $this->mode = "create";
            if (!isset($this->ad_id)) {
                $this->ad_id = $this->advert->book_new_ad_id();
                $this->ad_data = new \stdClass();
                $this->ad_data->cat_id = 0;
                $this->ad_data->item_title = "";
                $this->ad_data->item_descr = "";
                $this->ad_data->location = "";
            } else {
                $this->mode = "edit";
                $this->ad_data->cat_id = (int)$this->ad_data->cat_id;
            }

            $this->uFunc->incJs("advert/js/create.js");
        }
    }
}
$ad=new create($this);

ob_start();

if($ad->uSes->access(2)) {?>


<h1>Категория обмена (например, монеты)</h1>

<div class="row">
    <div class="form-group">
        <div class="col-md-4">
            <label for="advert_cat_id">Категория</label>
        </div>
        <div class="col-md-4">
            <select class="form-control">
                <option>Выберите категорию</option>
            </select>
        </div>
        <div class="col-md-4">
            <select id="advert_cat_id" class="form-control">
                <option value="0" class="text-muted" <?=$ad->ad_data->cat_id===0?"selected":""?>>Выберите подкатегорию</option>
                <option value="1" <?=$ad->ad_data->cat_id===1?"selected":""?>>Тестовая категория 1</option>
                <option value="2" <?=$ad->ad_data->cat_id===2?"selected":""?>>Тестовая категория 2</option>
                <option value="3" <?=$ad->ad_data->cat_id===3?"selected":""?>>Тестовая категория 3</option>
                <option value="4" <?=$ad->ad_data->cat_id===4?"selected":""?>>Тестовая категория 4</option>
            </select>
        </div>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row">
    <div class="form-group">
        <div class="col-md-4">
            <label for="advert_item_title">Предмет обмена - название</label>
        </div>
        <div class="col-md-8">
            <input id="advert_item_title" type="text"  class="form-control" value="<?=htmlspecialchars($ad->ad_data->item_title)?>">
        </div>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row">
    <div class="form-group">
        <div class="col-md-4">
            <label for="advert_item_descr">Описание</label>
        </div>
        <div class="col-md-8">
            <textarea id="advert_item_descr" class="form-control"><?=htmlspecialchars($ad->ad_data->item_descr)?></textarea>
        </div>
    </div>
</div>

<div class="row">&nbsp;</div>

<div class="row">
    <div class="form-group">
        <div class="col-md-4">
            <label>Фотографии</label>
        </div>
        <div class="col-md-8">
            Перетащите файл сюда
        </div>
    </div>
</div>



    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label for="advert_location">Местоположение</label>
            </div>
            <div class="col-md-8">
                <input id="advert_location" type="text" class="form-control"  value="<?=htmlspecialchars($ad->ad_data->location)?>">

                <p>&nbsp;</p>
                <style>
                    /* Always set the map height explicitly to define the size of the div
                     * element that contains the map. */
                    #map {
                        height: 400px;
                    }
                </style>
                <div id="map"></div>
                <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBj7vxm5QY_7MPomU0anUcaBMpV9zqqhjw&callback=initMap" async defer></script>
            </div>
        </div>
    </div>

<button class="btn btn-primary btn-lg" onclick="advert.create()"><span class="icon-right"></span> Далее</button>

<script type="text/javascript">
    if(typeof advert==="undefined") advert={};
    advert.ad_id=<?=$ad->ad_id?>;
</script>

    <?}
    else {?>
        <div class="jumbotron">
            <h1 class="page-header">Обмен</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
    <?}

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
