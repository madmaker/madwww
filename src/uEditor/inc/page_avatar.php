<?php
use processors\uFunc;

require_once 'processors/classes/uFunc.php';

class uEditor_page_avatar {
    private $uFunc;
    private $img_width;
    private $uCore,$page_id,$img_time;
    private function checkData() {
        if($this->img_width=='orig'||
            uString::isDigits($this->img_width)
        ) {
            if(!uString::isDigits($this->page_id)) return false;

            return true;
        }

        return false;
    }
    private function get_file() {
        if($this->img_width!='orig') {
            $this->img_width=(int)$this->img_width;
            if($this->img_width<50) $this->img_width=50;
            if($this->img_width>2500) $this->img_width="orig";
        }

        $dir='uEditor/page_avatars/'.site_id.'/'.$this->page_id;
        $orig_img=$dir.'/orig.jpg';
        if($this->img_width=='orig') $target_img=$orig_img;
        else $target_img=$dir.'/'.$this->img_width.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) {
                $this->update_img_time(true);
                return false;
            }
            if (!file_exists($dir)) mkdir($dir,0755,true);

            if(!class_exists('Imagick')) return false;
            try {
                $im = new Imagick($orig_img);
                $im->setImageFormat('jpeg');
                if($this->img_width!='orig') {
                    $im->resizeImage($this->img_width, 0, Imagick::FILTER_LANCZOS, 1);

                    // Set to use jpeg compression
                    $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                    // Set compression level (1 lowest quality, 100 highest quality)
                    $im->setImageCompressionQuality(75);
                    // Strip out unneeded meta data

                    $im->writeImage($target_img);

                    $im->clear();
                    $im->destroy();
                }
            } catch (ImagickException $e) {
            }

            $this->update_img_time();
        }
        return u_sroot.$target_img.'?'.$this->img_time;
    }
    private function update_img_time($reset=false) {
        if($reset) $this->img_time=0;
        else $this->img_time=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("UPDATE
            u235_pages_html
            SET
            page_avatar_time=:page_avatar_time
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_avatar_time', $this->img_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uEditor/page_avatar/1'/*.$e->getMessage()*/);}
    }
    public  function get_avatar($img_width,$page_id,$img_time=0) {
        $this->img_width=$img_width;
        $this->page_id=$page_id;
        $this->img_time=$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
    }
}