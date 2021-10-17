<?php
namespace gallery;
use Imagick;

class img{
    private $site_id;
    private $uCore;

    public function get_img($gallery_id,$img_id,$height=0,$width=0) {
        $gallery_id=(int)$gallery_id;
        $img_id=(int)$img_id;
        $height=(int)$height;
        $width=(int)$width;

        if(!$height) {
            if($width) {
                if ($width < 50) $width = 50;
                if ($width > 1500) $width = 1500;
            }
            else {
                $height=150;
            }
        }
        else {
            if ($height < 50) $height = 50;
            if ($height > 1500) $height = 1500;
        }
        
        $dir='gallery/gallery_pictures/'.site_id.'/'.$gallery_id.'/'.$img_id;
        $orig_img=$dir.'/orig.jpg';
        $target_img=$dir.'/'.$height.'.jpg';

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) return "";

            if(!class_exists('Imagick')) return "";

            try {
                $im = new Imagick($orig_img);

                $dimensions = $im->getImageGeometry();
                if($width) {
                    $dim_height=$dimensions ['height'];
                    $dim_width=$dimensions ['width'];
                    $ratio=$dim_width/$width;
                    $height=$dim_height/$ratio;
                    if($height<=$width) {
                        $height=$width;
                        $width=0;
                    }
                }

                if($height) if($dimensions ['height']<=$height) return u_sroot.$orig_img;

                $im->setImageFormat('jpeg');
                if($height) $im->resizeImage(0, $height, Imagick::FILTER_LAGRANGE, 1);
                else $im->resizeImage($width, 0, Imagick::FILTER_LAGRANGE, 1);

                // Set to use jpeg compression
                $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                // Set compression level (1 lowest quality, 100 highest quality)
                $im->setImageCompressionQuality(90);
                // Strip out unneeded meta data

                $im->writeImage($target_img);

                $im->clear();
                $im->destroy();
            }
            catch (\Exception $e) {return "";}
        }
        return u_sroot.$target_img;
    }
    function __construct (&$uCore,$site_id=site_id) {
        $this->site_id=$site_id;
        $this->uCore=&$uCore;
    }
}