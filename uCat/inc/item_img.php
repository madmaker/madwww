<?php
class uCat_item_img{
    private $uCore,$item_id,$img_id,$img_style,$width,$img_time;
    private function checkData() {
        if(
            $this->img_style=='item_page'||
            $this->img_style=='orig'
        ) {
            if(!uString::isDigits($this->item_id)) return false;
        }
        else return false;
        return true;
    }
    private function get_file() {
        if($this->img_style!='orig') {
            $this->width=$this->uCore->uFunc->getConf("item_img_".$this->img_style."_width","uCat",true);
            if(!uString::isDigits($this->width)) $this->width=$this->uCore->uFunc->getConf("item_img_".$this->img_style."_width","uCat",false,0);
        }
        $dir='uCat/item_pictures/'.site_id.'/'.$this->item_id.'/'.$this->img_id;
        $orig_img=$dir.'/orig.jpg';
        if($this->img_style=='orig') $target_img=$orig_img;
        else $target_img=$dir.'/'.$this->width.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) return false;

            if (!file_exists($dir)) mkdir($dir,0755,true);

            if(!class_exists('Imagick')) return false;

            $im = new Imagick($orig_img);
            $im->setImageFormat('jpeg');
            if($this->img_style!='orig') $im->resizeImage($this->width,0,Imagick::FILTER_LANCZOS,1);

            // Set to use jpeg compression
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            // Set compression level (1 lowest quality, 100 highest quality)
            $im->setImageCompressionQuality(100);
            // Strip out unneeded meta data

            $im->writeImage($target_img);

            $im->clear();
            $im->destroy();
        }
        return u_sroot.$target_img.'?'.$this->img_time;
    }
    public function get_img($img_style,$item_id,$img_id,$img_time=0) {
        $this->img_style=$img_style;
        $this->item_id=$item_id;
        $this->img_id=$img_id;
        $this->img_time=$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
    }
}