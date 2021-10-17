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

class create_bg {
    private $advert;
    private $ad_id;
    private $cat_id;
    private $item_title;
    private $item_descr;
    private $location;
    private $uFunc;
    private $uSes;
    private $uCore;

    private function error($er_code) {
        $this->uFunc->error($er_code);
    }
    private function check_data() {
        if(!isset($_POST['ad_id'],$_POST['cat_id'],$_POST['item_title'],$_POST['item_descr'],$_POST['location'])) $this->error(10);

        if(!\uString::isDigits($_POST['ad_id'])) $this->error(20);
        $this->ad_id=(int)$_POST['ad_id'];

        if(!\uString::isDigits($_POST['cat_id'])) $this->error(30);
        $this->cat_id=(int)$_POST['cat_id'];

        $this->item_title=trim($_POST['item_title']);
        if(strlen($this->item_title)<2) {
            echo "{
            'status':'error',
            'msg':'fill the title'
            }";
            exit;
        }
        $this->item_descr=$_POST['item_descr'];
        if(strlen($this->item_descr)<2) {
            echo "{
            'status':'error',
            'msg':'fill the descr'
            }";
            exit;
        }
        $this->location=$_POST['location'];
        if(strlen($this->location)<2) {
            echo "{
            'status':'error',
            'msg':'fill the location'
            }";
            exit;
        }

        //check if this ad_id exists and it belongs to current user and status is new (0) or just created (1)
        $ad_data=$this->advert->get_ad_data_of_current_user($this->ad_id,'ad_id','status=0 OR status=1');
        if(!$ad_data) $this->error(40);
    }


    private function save_advert() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("advert")->prepare("UPDATE 
            adverts 
            SET
            cat_id=:cat_id, 
            item_title=:item_title, 
            item_descr=:item_descr, 
            location=:location,
            status=1
            WHERE
            ad_id=:ad_id AND
            site_id=:site_id AND 
            user_id=:user_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ad_id', $this->ad_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_title', $this->item_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_descr', $this->item_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':location', $this->location,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(2)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->advert=new advert($this->uCore);
        $this->check_data();
        $this->save_advert();

        echo "{
        'status':'done',
        'ad_id':'".$this->ad_id."'
        }";
        exit;


//        $this->uCore->uInt_js('uPage','setup_uPage_page');
    }
}
new create_bg($this);