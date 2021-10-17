<?php
namespace uNavi\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uNavi\common\uNavi;
use uSes;

//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

require_once "uNavi/classes/uNavi.php";
require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class create_cat {
    private $uNavi;
    private $uSes;
    private $uFunc;
    private $uCore;
    private $cat_title;
    private $cat_type;
    private $cat_access;

    private function check_data() {
        if(!isset($_POST['cat_title'],
            $_POST['cat_type'],
            $_POST['cat_access'])) $this->uFunc->error(10);

        $this->cat_title=trim($_POST['cat_title']);
        $this->cat_type=$_POST['cat_type'];
        $this->cat_access=$_POST['cat_access'];

        if(!\uString::isDigits($this->cat_type)) $this->uFunc->error(20);
        if(!\uString::isDigits($this->cat_access)) $this->uFunc->error(30);

        //check if cat_type exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            type_id
            FROM
            u235_cattypes
            WHERE 
            type_id=:type_id 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $this->cat_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        //check if access exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT
            access_id
            FROM
            u235_acl
            WHERE 
            access_id=:access_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':access_id', $this->cat_access,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(60);
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uNavi=new uNavi($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        $cat_id=$this->uNavi->create_cat(0,$this->cat_title,$this->cat_type,$this->cat_access);

        $this->uNavi->create_new_item($cat_id,"Menu");
        $this->uNavi->create_new_item($cat_id,"Menu");
        $this->uNavi->create_new_item($cat_id,"Menu");
        $this->uNavi->create_new_item($cat_id,"Menu");

        echo '{
        "status":"done",
        "cat_id":"'.$cat_id.'",
        "cat_title":"'.rawurlencode($this->cat_title).'",
        "cat_type":"'.$this->cat_type.'",
        "cat_access":"'.$this->cat_access.'"
        }';
    }
}
new create_cat($this);
