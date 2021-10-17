<?php
namespace uNavi\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "processors/uMenu.php";

class item_icon_delete {
    private $uCore,$item_id,$icon_style,$cat_id;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['icon_style'])) $this->uCore->error(10);
        $this->item_id=$_POST['item_id'];
        $this->icon_style=$_POST['icon_style'];
        if(!uString::isDigits($this->item_id)) $this->uCore->error(20);
        if($this->icon_style!='hover') $this->icon_style='regular';
    }
    private function get_cat_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("SELECT
            cat_id
            FROM
            u235_menu
            WHERE
            id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uCore->error(30);
            $this->cat_id=$qr->cat_id;
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    private function delete_icon() {
        $this->uFunc->rmdir("uNavi/item_icons/".site_id."/".$this->item_id."/".$this->icon_style);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("UPDATE
            u235_menu
            SET
            icon_".$this->icon_style."_filename=NULL,
            timestamp=:timestamp
            WHERE
            id=:item_id AND
            site_id=:site_id
            ");
            $timestamp=time();
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->uMenu=new \uMenu($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_cat_id();
        $this->delete_icon();

        echo "{
        'status':'done',
        'icon_style':'".$this->icon_style."',
        'cat_id2update':'".$this->cat_id."',
        'cat_new_html':'".rawurlencode($this->uCore->uMenu->return_cat_id_content($this->cat_id))."'
        }";

        $this->uMenu->clean_cache($this->cat_id);
    }
}
/*$uNavi=*/new item_icon_delete($this);