<?php
namespace gallery;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class img_edit_bg {
    private $uFunc;
    private $uSes;
    private $img_id;
    private $gallery_id;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["gallery_id"],$_POST["img_id"],$_POST["action"])) $this->uFunc->error(10,1);
        $this->gallery_id=(int)$_POST["gallery_id"];
        $this->img_id=(int)$_POST["img_id"];
    }

//    public function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }

    private function delete_img() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("DELETE FROM 
            images
            WHERE
            gallery_id=:gallery_id AND
            img_id=:img_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $this->gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_id', $this->img_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }
        $pages_stm=$this->uPage->el_id2page_ids($this->gallery_id);
        while($page=$pages_stm->fetch(PDO::FETCH_OBJ)) {
            $this->uPage->clear_cache($page->page_id);
        }

        if(!isset($this->gallery)) {
            require_once "gallery/classes/common.php";
            $this->gallery=new common($this->uCore);
        }

        $this->gallery->clear_cache($this->gallery_id);

        $this->uFunc->rmdir('gallery/gallery_pictures/'.site_id.'/'.$this->gallery_id.'/'.$this->img_id);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();
        if($_POST["action"]==="delete") $this->delete_img();
        else $this->uFunc->error(30,1);
    }
}
new img_edit_bg($this);