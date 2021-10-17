<?php
require_once "processors/classes/uFunc.php";

class uCat_cat_avatar {
    public $uFunc;
    private $uCore,$cat_id,$avatar_style,$width,$img_time;
    private function checkData() {
        if(
            $this->avatar_style=='list_no_descr'||
            $this->avatar_style=='list_w_descr'||
            $this->avatar_style=='orig'
        ) {
            if(!uString::isDigits($this->cat_id)) return false;
        }
        else return false;

        if(!$this->img_time) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                cat_avatar_time
                FROM 
                u235_cats
                WHERE
                cat_id=:cat_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->img_time=(int)$qr->cat_avatar_time;
        }

        return true;
    }
    private function get_file() {
        if($this->avatar_style!='orig') {
            $this->width=$this->uCore->uFunc->getConf("cat_avatar_".$this->avatar_style."_width","uCat",true);
            if(!uString::isDigits($this->width)) $this->width=$this->uCore->uFunc->getConf("cat_avatar_".$this->avatar_style."_width","uCat",false,0);
        }

        $dir='uCat/cat_avatars/'.site_id.'/'.$this->cat_id;
        $dir2='uCat/cat_avatars/'.site_id.'/'.$this->cat_id.'/'.$this->img_time;
        $orig_img=$dir.'/orig.jpg';
        if($this->avatar_style=='orig') $target_img=$orig_img;
        else $target_img=$dir2.'/'.$this->width.'.jpg';

        if(!file_exists($target_img)) {
//            if(site_id==42) if(!file_exists($orig_img)) $orig_img='templates/lomopak_def_item_img.jpg';
//            if(!file_exists($orig_img)) $orig_img='templates/site_'.site_id.'/images/uCat/cat_def_avatar.jpg';
            if(!file_exists($orig_img)) $orig_img='images/uCat/cat_def_avatar.jpg';
            if(!file_exists($orig_img)) return "";

            if (!file_exists($dir)) mkdir($dir,0755,true);
            if (!file_exists($dir2)) mkdir($dir2,0755,true);

            if(!class_exists('Imagick')) return false;

            try {
                $im = new Imagick($orig_img);
            } catch (ImagickException $e) {
                return false;
            }
            $im->setImageFormat('jpeg');
            if($this->avatar_style!='orig') $im->resizeImage($this->width,0,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);
            // Strip out unneeded meta data

            $im->writeImage($target_img);

            $im->clear();
            $im->destroy();

            $this->update_img_time();
        }
        return u_sroot.$target_img.'?'.$this->img_time;
    }
    private function update_img_time() {
        $this->img_time=time();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_cats
            SET
            cat_avatar_time=:cat_avatar_time
            WHERE
            cat_id=:cat_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_id', $this->cat_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cat_avatar_time', $this->img_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    public  function get_avatar($avatar_style,$cat_id,$img_time=0) {
        $this->avatar_style=$avatar_style;
        $this->cat_id=$cat_id;
        $this->img_time=(int)$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}