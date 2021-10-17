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

class publish_bg {
    private $uFunc;
    private $uSes;
    private $advert;
    private $ad_id;
    private $uCore;
    private function error($er_code) {
        $this->uFunc->error($er_code);
    }
    private function check_data() {
        if(!isset($_POST['ad_id'])) $this->error(10);
        if(!\uString::isDigits($_POST['ad_id'])) $this->error(20);
        $this->ad_id=$_POST['ad_id'];

        //check if ad exists
        $ad_data=$this->advert->get_ad_data_of_current_user($this->ad_id,'ad_id','status=1');
        if(!$ad_data) $this->error(30);
    }

    private function update_ad_status() {
        if(!isset($this->user_id)) $this->user_id=$this->uSes->get_val("user_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("advert")->prepare("UPDATE 
            adverts 
            SET
            status=2
            WHERE
            ad_id=:ad_id AND
            user_id=:user_id AND 
            site_id=:site_id AND
            status=1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ad_id', $this->ad_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(2)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);
        $this->advert=new advert($this->uCore);

        $this->check_data();
        $this->update_ad_status();
        echo "{
        'status':'done'
        }";
    }
}
new publish_bg($this);