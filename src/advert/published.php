<?php
namespace advert\ad;
use advert\common\advert;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "advert/classes/advert.php";

class published {
    public $uFunc;
    public $uSes;
    public $advert;
    public $ad_id;
    public $ad_info;
    private $uCore;
    private function error($er_code) {
        $this->uFunc->error($er_code);
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->error(10);
        $this->ad_id=$this->uCore->url_prop[1];
        if(!\uString::isDigits($this->ad_id)) $this->error(20);
        $this->ad_id=(int)$this->ad_id;

        $this->ad_info=$this->advert->get_ad_data_of_current_user($this->ad_id,'cat_id,item_title,item_descr,location','status=2');
        if(!$this->ad_info) $this->error(30);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if($this->uSes->access(2)) {
            $this->uFunc = new uFunc($this->uCore);
            $this->advert = new advert($this->uCore);

            $this->check_data();
        }
    }
}
$ad=new published($this);
ob_start();

if($ad->uSes->access(2)) {?>

<h2>Предложение обмена опубликовано</h2>
    <p>Оно будет проверено модератором и после этого появится на сайте</p>

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
