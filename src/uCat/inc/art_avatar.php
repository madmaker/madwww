<?php
class uCat_art_avatar {
    private $uCore,$art_id,$avatar_style,$width,$img_time;
    private function checkData() {
        if(
            $this->avatar_style=='item_page'||
            $this->avatar_style=='art_page'||
            $this->avatar_style=='arts_list'||
            $this->avatar_style=='admin_page'||
            $this->avatar_style=='orig'
        ) {
            if(!uString::isDigits($this->art_id)) return false;
        }
        else return false;
        return true;
    }
    private function get_file() {
        if($this->avatar_style!='orig') {
            if($this->avatar_style=='admin_page') $this->width=40;
            else {
                $this->width=$this->uCore->uFunc->getConf($this->avatar_style."_art_img_width","uCat",true);
                if(!uString::isDigits($this->width)) $this->width=$this->uCore->uFunc->getConf($this->avatar_style."_art_img_width","uCat",false,0);
            }
        }

        $dir='uCat/art_avatars/'.site_id.'/'.$this->art_id;
        $orig_img=$dir.'/orig.jpg';
        if($this->avatar_style=='orig') $target_img=$orig_img;
        else $target_img=$dir.'/'.$this->width.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) return false;
            if (!file_exists($dir)) mkdir($dir,0755,true);

            if(!class_exists('Imagick')) return false;
            $im = new Imagick($orig_img);
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
        if(!$this->uCore->query("uCat","UPDATE
        `u235_articles`
        SET
        `art_avatar_time`='".$this->img_time."'
        WHERE
        `art_id`='".$this->art_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(10);
    }
    public function get_avatar($avatar_style,$art_id,$img_time=0) {
        $this->avatar_style=$avatar_style;
        $this->art_id=$art_id;
        $this->img_time=$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
    }
}
