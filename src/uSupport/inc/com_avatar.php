<?php
class uSup_com_avatar {
    private $uCore,$com_id,$avatar_style,$width,$img_time;
    private function checkData() {
        if(
            $this->avatar_style=='com_page'||
            $this->avatar_style=='orig'
        ) {
            if(!uString::isDigits($this->com_id)) return false;
        }
        else return false;
        return true;
    }
    private function get_file() {
        if($this->avatar_style!='orig') {
            //$this->width=$this->uCore->uFunc->getConf("avatar_".$this->avatar_style."_width","uCat",true);
            //if(!uString::isDigits($this->width)) $this->width=$this->uCore->uFunc->getConf("avatar_".$this->avatar_style."_width","uCat",false,0);
            if($this->avatar_style=='com_page') $this->width=200;
        }

        $dir='uSupport/com_avatars/'.site_id.'/'.$this->com_id;
        $orig_img=$dir.'/orig.jpg';
        if($this->avatar_style=='orig') $target_img=$orig_img;
        else $target_img=$dir.'/'.$this->width.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) $orig_img='uSupport/com_avatars/default.jpg';

            if (!file_exists($dir)) mkdir($dir,0755,true);

            if(!class_exists('Imagick')) return false;
            $im = new Imagick($orig_img);
            $im->setImageFormat('jpeg');
            if($this->avatar_style!='orig') $im->resizeImage($this->width,0,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(75);
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
        if(!$this->uCore->query("uSup","UPDATE
        `u235_comps`
        SET
        `logo_timestamp`='".$this->img_time."'
        WHERE
        `com_id`='".$this->com_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
    }
    public  function get_avatar($avatar_style,$com_id,$img_time=0) {
        $this->avatar_style=$avatar_style;
        $this->com_id=$com_id;
        $this->img_time=$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
    }
}
