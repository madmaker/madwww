<?php
namespace uNavi\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class delete_item {
    private $uCore,
        $item_id,$item;
    private function check_data() {
        if(!isset($_POST['item_id'])) $this->uFunc->error(10);
        $this->item_id=$_POST['item_id'];
        if(!uString::isDigits($this->item_id)) $this->uFunc->error(20);

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
            if(!$this->item=$stm->fetch(PDO::FETCH_OBJ)) return 0;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
        return 1;
    }
    private function delete_item() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uNavi")->prepare("DELETE FROM
            u235_menu
            WHERE
            id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            navi_parent_menu_id=0
            WHERE
            navi_parent_menu_id=:navi_parent_menu_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':navi_parent_menu_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir('uNavi/item_icons/'.site_id.'/'.$this->item_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uMenu=new \uMenu($this->uCore);

        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_item();

        echo "{
        'status':'done',
        'item_id':'".$this->item_id."',
        'cat_id2update':'".$this->item->cat_id."',
        'cat_new_html':'".rawurlencode($this->uCore->uMenu->return_cat_id_content($this->item->cat_id))."'
        }";

        $this->uMenu->clean_cache($this->item->cat_id);

    }
}
/*$uNavi=*/new delete_item($this);