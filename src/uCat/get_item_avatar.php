<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uCat/inc/item_avatar.php";

class get_item_avatar {
    private $avatar;
    private $var_id;
    private $item_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['item_id'],$_POST['var_id'])) $this->uFunc->error(10);
        if(!\uString::isDigits($_POST['item_id'])) $this->uFunc->error(20);
        if(!\uString::isDigits($_POST['var_id'])) $this->uFunc->error(30);
        $this->item_id=(int)$_POST['item_id'];
        $this->var_id=(int)$_POST['var_id'];
    }

//    public function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }

    private function get_item_img_time() {
        if($this->var_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                img_time 
                FROM 
                items_variants 
                WHERE 
                var_id=:var_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $this->var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        }
        else {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                item_img_time AS img_time
                FROM 
                u235_items 
                WHERE 
                item_id=:item_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 0;
        return (int)$qr->img_time;
    }
    private function return_avatar_url() {
        $img_time=$this->get_item_img_time();
        return $this->avatar->get_avatar(640,$this->item_id,$img_time,$this->var_id);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("forbidden");

        $this->uFunc=new uFunc($this->uCore);
        $this->check_data();

        $this->avatar=new \item_avatar($this->uCore);

        echo "{
        'status':'done',
        'var_id':'".$this->var_id."',
        'src':'".rawurlencode($this->return_avatar_url())."'
        }";


//        $this->uCore->uInt_js('uPage','setup_uPage_page');
    }
}
new get_item_avatar($this);
