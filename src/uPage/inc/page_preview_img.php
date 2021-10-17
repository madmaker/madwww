<?php
require_once "processors/classes/uFunc.php";

class page_preview_img {
    public $uFunc;
    private $uCore,$page_id,$img_width,$preview_img_timestamp;
    private function check_data() {
        if($this->img_width<50) $this->img_width=50;

        if(!$this->preview_img_timestamp) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("uPage")->prepare("SELECT 
                preview_img_timestamp
                FROM 
                u235_pages
                WHERE
                page_id=:page_id AND 
                site_id=:site_id
                ");
                $site_id = site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */

            if ($qr = $stm->fetch(PDO::FETCH_OBJ)) $this->preview_img_timestamp = (int)$qr->preview_img_timestamp;
        }

        return true;
    }
    private function get_file() {
        $dir='uPage/preview_images/'.site_id.'/'.$this->page_id;
        $dir2='uPage/preview_images/'.site_id.'/'.$this->page_id.'/'.$this->preview_img_timestamp;
        $orig_img=$dir.'/orig.jpg';

        $target_img=$dir2.'/'.$this->img_width.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) $orig_img="uPage/img/default_page_img.jpg";

            if (!file_exists($dir)) mkdir($dir,0755,true);
            if (!file_exists($dir2)) mkdir($dir2,0755,true);

            if(!class_exists('Imagick')) return "";

            try {
                $im = new Imagick($orig_img);
            } catch (ImagickException $e) {return "";}

            $width=$im->getImageWidth();
            if($this->img_width>$width) $this->img_width=$width;

            $im->setImageFormat('jpeg');

            $im->resizeImage($this->img_width,0,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);

            $im->writeImage($target_img);

            $im->clear();
            $im->destroy();
        }
        return u_sroot.$target_img;
    }
    public  function get_img_url($img_width,$page_id,$preview_img_timestamp=0) {
        $this->img_width=(int)$img_width;
        $this->page_id=(int)$page_id;
        $this->preview_img_timestamp=(int)$preview_img_timestamp;

        if(!$this->check_data()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}