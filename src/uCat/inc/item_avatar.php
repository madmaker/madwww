<?php
require_once "processors/classes/uFunc.php";

class uCat_item_avatar {
    private $uFunc;
    private $var_id;
    private $uCore,$item_id,$img_width,$img_time;
    private function checkData() {
        if($this->img_width=='orig'||
            uString::isDigits($this->img_width)
        ) {
            if(!uString::isDigits($this->item_id)) return false;
        }
        else return false;

        if(!$this->img_time&&!$this->var_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                item_img_time
                FROM 
                u235_items
                WHERE
                item_id=:item_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat item_avatar 10'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->img_time=(int)$qr->item_img_time;
        }
        elseif(!$this->img_time&&$this->var_id) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
                img_time
                FROM 
                items_variants
                WHERE
                item_id=:item_id AND 
                var_id=:var_id AND 
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $this->var_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uCat item_avatar 20'/*.$e->getMessage()*/);}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->img_time=(int)$qr->img_time;
        }


        return true;
    }
    private function update_var_img_time($default_var_id) {
        $var_id=$this->var_id;
        if(!$var_id) $var_id=$default_var_id;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            items_variants
            SET
            img_time=:img_time
            WHERE
            item_id=:item_id AND
            var_id=:var_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_time', $this->img_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':var_id', $var_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat item_avatar 30'/*.$e->getMessage()*/);}
    }
    private function update_item_img_time() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items
            SET
            item_img_time=:img_time
            WHERE
            item_id=:item_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_time', $this->img_time,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':item_id', $this->item_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat item_avatar 50'/*.$e->getMessage()*/);}
    }
    private function get_file() {

        if($this->img_width!='orig') {
            $this->img_width=(int)$this->img_width;
            if($this->img_width<50) $this->img_width=50;
            if($this->img_width>2500) $this->img_width="orig";
        }

        if($this->var_id) {
            $dir='uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$this->var_id;
            $dir2='uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$this->var_id.'/'.$this->img_time;
        }
        else {
            $dir='uCat/item_avatars/'.site_id.'/'.$this->item_id;
            $dir2='uCat/item_avatars/'.site_id.'/'.$this->item_id.'/'.$this->img_time;
        }
        
        $orig_img=$dir.'/orig.jpg';

        if($this->img_width=='orig') $target_img=$orig_img;
        else $target_img=$dir2.'/'.$this->img_width.'.jpg';

        if(!file_exists($target_img)) {
            $this->img_time=time();

            require_once "uCat/classes/common.php";
            if(!isset($this->uCat)) $this->uCat=new \uCat\common($this->uCore);

            $default_var_id=(int)$this->uCat->item_id2default_variant_id($this->item_id);

            if($this->var_id) {
                $dir='uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$this->var_id;
                $dir2='uCat/item_avatars/'.site_id.'/'.$this->item_id.'-'.$this->var_id.'/'.$this->img_time;

                if($default_var_id===$this->var_id) $this->update_item_img_time();

            }
            else {
                $dir='uCat/item_avatars/'.site_id.'/'.$this->item_id;
                $dir2='uCat/item_avatars/'.site_id.'/'.$this->item_id.'/'.$this->img_time;

                $this->update_item_img_time();

            }
            $this->update_var_img_time($default_var_id);

            if($this->img_width=='orig') $target_img=$orig_img;
            else $target_img=$dir2.'/'.$this->img_width.'.jpg';

            if($this->var_id) {
                if(!file_exists($orig_img)) {
                    $item_img_dir = 'uCat/item_avatars/' . site_id . '/' . $this->item_id;
                    $orig_img = $item_img_dir . '/orig.jpg';
                }
            }
//            if(site_id==42) if(!file_exists($orig_img)) $orig_img='templates/lomopak_def_item_img.jpg';
//            if(!file_exists($orig_img)) $orig_img='templates/site_'.site_id.'/images/uCat/item_def_avatar.jpg';
            if(!file_exists($orig_img)) return u_sroot.'images/uCat/item_def_avatar.jpg';
//                $orig_img='images/uCat/item_def_avatar.jpg';

            if (!file_exists($dir)) mkdir($dir,0755,true);
            if (!file_exists($dir2)) mkdir($dir2,0755,true);

            if(!class_exists('Imagick')) return false;
            try {
                $im = new Imagick($orig_img);
            }
            catch (Exception $e) {}

            if(isset($im)) {
                $im->setImageFormat('jpeg');
                if ($this->img_width != 'orig') $im->resizeImage($this->img_width, 0, Imagick::FILTER_LAGRANGE, 1);

                // Set to use jpeg compression
                $im->setImageCompression(Imagick::COMPRESSION_JPEG);
                // Set compression level (1 lowest quality, 100 highest quality)
                $im->setImageCompressionQuality(75);
                // Strip out unneeded meta data

                $im->writeImage($target_img);

                $im->clear();
                $im->destroy();
            }
        }
        return u_sroot.$target_img.'?'.$this->img_time;
    }
    public  function get_avatar($img_width,$item_id,$img_time=0,$var_id=0) {
        $this->img_width=$img_width;
        $this->item_id=(int)$item_id;
        $this->var_id=(int)$var_id;
        $this->img_time=(int)$img_time;

        if(!$this->checkData()) return '';
        return $this->get_file();
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
    }
}