<?php
namespace advert\ads;
use advert\common\advert;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
//require_once "advert/classes/advert.php";

class my_ads {
    public $uFunc;
    public $uSes;
    public $advert;
    public $my_ads;
    private $uCore;

    public function get_ads_of_user() {
        if(!isset($this->user_id)) $this->user_id=$this->uSes->get_val("user_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("advert")->prepare("SELECT 
            ad_id,
            cat_id,
            item_title,
            item_descr,
            location
            FROM 
            adverts
            WHERE
            user_id=:user_id AND 
            site_id=:site_id AND
            (status=2)
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if($this->uSes->access(2)) {
            $this->uFunc = new uFunc($this->uCore);
            $this->uFunc->incCss("/advert/css/ads.min.css");
//            $this->advert=new advert($this->uCore);
            $this->my_ads=$this->get_ads_of_user();
        }
    }
}
$ad=new my_ads($this);

$items_number_lg=3;
$items_number_md=3;
$items_number_sm=2;
$items_number_xs=1;

ob_start();
if($ad->uSes->access(2)) {?>
<div class="advert_ads"
    <div class="row">
    <?while($advert=$ad->my_ads->fetch(PDO::FETCH_OBJ)) {?>
        <div class="col-lg-<?=$items_number_lg?> col-md-<?=$items_number_md?> col-sm-<?=$items_number_sm?> col-xs-<?=$items_number_xs?>">
            <div class="item container-fluid">

                <div class="item_avatar_container">
                    <a href="<?=u_sroot?>advert/ad/<?=$advert->ad_id?>">
                        <img src="<?/*=$item_avatar*/?>">
                    </a>
                </div>


                <div class="item_title"><?=$advert->item_title?></div>
        </div>
    <?}?>
    </div>
<?} else {?>
    <div class="jumbotron">
        <h1 class="page-header">Обмен</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}
$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/template.php';
