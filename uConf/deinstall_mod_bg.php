<?php
namespace uConf\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class uninstall_mod {
    private $uCore,$mod_id;
    public $site_id;
    private function check_data(){
        if(!isset($_POST['site_id'],$_POST['mod_id'])) $this->uFunc->error(10);
        $this->site_id=$_POST['site_id'];
        $this->mod_id=$_POST['mod_id'];

        if(!uString::isDigits($this->site_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->mod_id)) $this->uFunc->error(30);
    }

    private function uninstall() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("common")->prepare("DELETE FROM
            u235_sites_modules
            WHERE
            site_id=:site_id AND
            mod_id=:mod_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $this->site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':mod_id', $this->mod_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);

        if(!$this->uSes->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->uninstall();

        echo "{'status' : 'done',
        'site_id' : '".$this->site_id."'
        }";
    }
}
/*$uConf=*/new uninstall_mod($this);