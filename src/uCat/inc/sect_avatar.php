<?php
require_once "processors/classes/uFunc.php";

class uCat_sect_avatar {
    public $uFunc;
    private $uCore,$sect_id,$avatar_style,$width,$img_time;
    private function checkData() {
        if(
            $this->avatar_style=='sects_list'||
            $this->avatar_style=='orig'
        ) {
            if(!uString::isDigits($this->sect_id)) return false;
        }
        else return false;

        if(!$this->img_time) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                sect_avatar_time 
                FROM 
                u235_sects 
                WHERE
                sect_id=:sect_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->img_time=(int)$qr->sect_avatar_time;
        }

        return true;
    }
    private function get_file() {
        if($this->avatar_style!='orig') {
            $this->width=$this->uCore->uFunc->getConf("sect_avatar_".$this->avatar_style."_width","uCat",true);
            if(!uString::isDigits($this->width)) $this->width=$this->uCore->uFunc->getConf("sect_avatar_".$this->avatar_style."_width","uCat",false,0);
        }

        $dir='uCat/sect_avatars/'.site_id.'/'.$this->sect_id;
        $dir2='uCat/sect_avatars/'.site_id.'/'.$this->sect_id.'/'.$this->img_time;
        $orig_img=$dir.'/orig.jpg';
        if($this->avatar_style=='orig') $target_img=$orig_img;
        else $target_img=$dir2.'/'.$this->width.'.jpg';

        if(!file_exists($target_img)) {
//            if(site_id==42) if(!file_exists($orig_img)) $orig_img='templates/lomopak_def_item_img.jpg';
//            if(!file_exists($orig_img)) $orig_img='templates/site_'.site_id.'/images/uCat/sect_def_avatar.jpg';
            if(!file_exists($orig_img)) $orig_img='images/uCat/sect_def_avatar.jpg';
            if(!file_exists($orig_img)) return "";

            if (!file_exists($dir)) mkdir($dir,0755,true);
            if (!file_exists($dir2)) mkdir($dir2,0755,true);

            if(!class_exists('Imagick')) return false;
            /** @noinspection PhpUnhandledExceptionInspection */
            $im = new Imagick($orig_img);
            $im->setImageFormat('jpeg');
            if($this->avatar_style!='orig') $im->resizeImage($this->width,0,Imagick::FILTER_LAGRANGE,1);

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
            u235_sects
            SET
            sect_avatar_time=:sect_avatar_time
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_avatar_time', $this->img_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $this->sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    public  function get_avatar($avatar_style,$sect_id,$img_time=0) {
        $this->avatar_style=$avatar_style;
        $this->sect_id=$sect_id;
        $this->img_time=(int)$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}