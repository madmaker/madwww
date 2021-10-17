<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

class cats_edit_bg {
    private $uCore;
    private $cat_id;
    private $cat_title;
    private $cat_type;
    private $cat_access;

    private function cat_type_exists($cat_type) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            type_id
            FROM
            u235_cattypes 
            WHERE 
            type_id=:type_id 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $cat_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function access_exists($access_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            access_id
            FROM
            u235_acl
            WHERE 
            access_id=:access_id 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':access_id', $access_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    private function check_data() {
        if(!isset(
            $_POST['cat_id'],
            $_POST['cat_title'],
            $_POST['cat_type'],
            $_POST['cat_access']
        )) $this->uFunc->error(30);

        $this->cat_id=$_POST['cat_id'];
        $this->cat_title=trim($_POST['cat_title']);
        $this->cat_type=$_POST['cat_type'];
        $this->cat_access=$_POST['cat_access'];

        if(!uString::isDigits($this->cat_id)) $this->uFunc->error(40);

        if(!strlen($this->cat_title)) $this->cat_title="Menu #".$this->cat_id;
        if(!$this->cat_type_exists($this->cat_type)) $this->uFunc->error(50);
        if(!$this->access_exists($this->cat_access)) $this->uFunc->error(60);
    }
    private function save_cat() {
        $config = \HTMLPurifier_Config::createDefault();
        $purifier = new \HTMLPurifier($config);

        $cat_title=$purifier->purify(htmlspecialchars($this->cat_title));

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
            u235_cats
            SET
            cat_title=:cat_title,
            cat_type=:cat_type,
            cat_access=:cat_access
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_title', $cat_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_type', $this->cat_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_access', $this->cat_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'.$e->getMessage());}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->save_cat();

        echo '{
        "status":"done",
        "cat_id":"'.$this->cat_id.'",
        "cat_title":"'.rawurlencode($this->cat_title).'",
        "cat_type":"'.$this->cat_type.'",
        "cat_access":"'.$this->cat_access.'"
        }';
    }
}
/*$newClass=*/new cats_edit_bg($this);
