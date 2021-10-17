<?php
namespace uConf\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class ban_site{
    private $uCore,$site_id;
    private function check_data(){
        if(!isset($_POST['site_id'])) $this->uFunc->error(10);
        $this->site_id=$_POST['site_id'];

        if(!uString::isDigits($this->site_id)) $this->uFunc->error(20);
    }

    private function ban_site() {
        //deinstall mod
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("UPDATE
            u235_sites
            SET
            status='banned'
            WHERE
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uSes->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->ban_site();

        echo "{'status' : 'done',
        'site_id' : '".$this->site_id."'
        }";
    }
}
/*$uConf=*/new ban_site($this);