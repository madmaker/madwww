<?php
class uAuth_avatar {
    /**
     * @var \processors\uFunc
     */
    private $uFunc;
    private $user_id,$avatar_style,$width,$height,$img_time;
    private function checkData() {
        if(
            $this->avatar_style === 'profile'||
            $this->avatar_style === 'admin_list'||
            $this->avatar_style === 'uSup_com_users_list'||
            $this->avatar_style === 'orig'||
            uString::isDigits($this->avatar_style)
        ) {
            if(!uString::isDigits($this->user_id)) {
                return false;
            }
        }
        else {
            return false;
        }
        return true;
    }
    private function get_file() {
        $defaultImgUrl='uAuth/avatars/default.jpg';
        if($this->avatar_style !== 'orig') {
            if($this->avatar_style === 'profile') {
                $this->width = 300;
            }
            elseif($this->avatar_style === 'admin_list') {
                $this->width = 45;
            }
            elseif($this->avatar_style === 'uSup_com_users_list') {
                $this->width=0;
                $this->height=50;
            }
            else {
                $this->width=$this->avatar_style;
                $this->height=$this->avatar_style;
            }
        }

        $dir='uAuth/avatars/'.$this->user_id;
        $orig_img=$dir.'/orig.jpg';
        if($this->avatar_style === 'orig') {
            $target_img = $orig_img;
        }
        else if($this->width) {
            $target_img = $dir . '/' . $this->width . '.jpg';
        }
        else {
            $target_img = $dir . '/0-' . $this->height . '.jpg';
        }

        print $target_img;

        if(!file_exists($target_img)) {
            if(!file_exists($orig_img)) {
                $orig_img = $defaultImgUrl;
            }

            if (!file_exists($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }

            if(class_exists('Imagick')) {
                try {
                    $im = new Imagick($orig_img);

                    $im->setImageFormat('jpeg');
                    if ($this->avatar_style !== 'orig') {
                        if ($this->width && !$this->height) {
                            $im->resizeImage($this->width, 0, Imagick::FILTER_LANCZOS, 1);
                        } elseif (!$this->width && $this->height) {
                            $im->resizeImage(0, $this->height, Imagick::FILTER_LANCZOS, 1);
                        } else {
                            try {
                                $im->cropThumbnailImage($this->width, $this->height);
                            } catch (ImagickException $e) {
                                return "";
                            }
                        }
                    }
                } catch (ImagickException $e) {
                    return "";
                }

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
            else {
                $target_img=$defaultImgUrl;
            }
        }
        return u_sroot.$target_img.'?'.$this->img_time;
    }
    private function update_img_time() {
        $this->img_time=time();
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare("UPDATE
            u235_users
            SET
            avatar_timestamp=:avatar_timestamp
            WHERE
            user_id=:user_id
            ");
            $stm->bindParam(':avatar_timestamp', $this->img_time,PDO::PARAM_INT);
            $stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('1589391175'/*.$e->getMessage()*/);}
    }
    public  function get_avatar($avatar_style,$user_id,$img_time=0) {
        $this->avatar_style=$avatar_style;
        $this->user_id=$user_id;
        $this->img_time=$img_time;

        if(!$this->checkData()) {
            return '';
        }
        return $this->get_file();
    }
    public function __construct (&$uCore) {
        $this->uFunc=new \processors\uFunc($uCore);
    }
}
