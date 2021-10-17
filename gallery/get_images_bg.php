<?php
namespace uPage\admin;
use gallery\img;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "gallery/classes/img.php";

class get_images_bg {
    private $img;
    private $gallery_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["gallery_id"])) {
            echo "forbidden";
            exit;
        }
        $this->gallery_id=(int)$_POST["gallery_id"];
    }

//    private function text($str) {
//        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
//    }
    private function get_images($gallery_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT 
            img_id 
            FROM 
            images 
            WHERE 
            gallery_id=:gallery_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->img=new img($this->uCore);

        $this->check_data();
        $images_stm=$this->get_images($this->gallery_id);

        /** @noinspection PhpUndefinedMethodInspection */
        while($img=$images_stm->fetch(PDO::FETCH_OBJ)) {?>
            <div class="uPage_gallery_img_container" id="uPage_gallery_img_container_<?=$img->img_id?>" style="background-image: url('<?=$this->img->get_img($this->gallery_id,$img->img_id,0,300)?>')"><div class="uPage_gallery_img_controls"><button class="btn btn-danger" onclick="uPage_setup_uPage.gallery_delete_image_confirm(<?=$img->img_id?>)"><span class="icon-cancel"></span></button></div></div>
        <?}
    }
}
new get_images_bg($this);