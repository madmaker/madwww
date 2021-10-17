<?php
namespace advert\create;
use advert\common\advert;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "advert/classes/advert.php";


class check {
    public $ad_id;
    public $uFunc;
    public $uSes;
    public $ad_info;
    public $advert;
    private $uCore;
    private function error($er_code) {
        $this->uFunc->error($er_code);
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1]))  $this->error(10);
        if(!\uString::isDigits($this->uCore->url_prop[1])) $this->error(20);
        $this->ad_id=(int)$this->uCore->url_prop[1];
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if($this->uSes->access(7)) {
            $this->uFunc=new uFunc($this->uCore);
            $this->advert=new advert($this->uCore);

            $this->check_data();
            $this->ad_info=$this->advert->get_ad_data_of_current_user($this->ad_id,'cat_id,item_title,item_descr,location','status=1');
            if(!$this->ad_info) $this->error(40);

            $this->uFunc->incJs("advert/js/check.min.js");
        }
    }
}
$ad=new check($this);
ob_start();
if($ad->uSes->access(2)) {?>

    <h3><a href="/advert/create/<?=$ad->ad_id?>"><span class="icon-left"></span> Изменить объявление</a></h3>
<h2>Проверьте данные</h2>

    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label for="advert_cat_id">Категория</label>
            </div>
            <div class="col-md-8"><?=$ad->ad_info->cat_id?></div>
        </div>
    </div>

    <div class="row">&nbsp;</div>

    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label for="advert_cat_id">Что меняете - название</label>
            </div>
            <div class="col-md-8"><?=$ad->ad_info->item_title?></div>
        </div>
    </div>

    <div class="row">&nbsp;</div>

    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label for="advert_cat_id">Описание</label>
            </div>
            <div class="col-md-8"><?=nl2br($ad->ad_info->item_descr)?></div>
        </div>
    </div>

    <div class="row">&nbsp;</div>

    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label>Фотографии</label>
            </div>
            <div class="col-md-8">

            </div>
        </div>
    </div>



    <div class="row">
        <div class="form-group">
            <div class="col-md-4">
                <label for="advert_location">Местоположение</label>
            </div>
            <div class="col-md-8"><?=$ad->ad_info->location?></div>
        </div>
    </div>

    <button class="btn btn-primary btn-lg" onclick="advert.publish()">Опубликовать обмен</button>

    <script typeof="text/javascript">
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
