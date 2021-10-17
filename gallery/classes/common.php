<?php
namespace gallery;
use PDO;
use PDOException;
use processors\uFunc;
//use uSes;

require_once "processors/classes/uFunc.php";
//require_once "processors/uSes.php";

class common {
    private $uFunc;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('gallery','common'),$str);
    }

    public function clear_cache($gallery_id,$site_id=site_id) {
        $this->uFunc->rmdir("gallery/cache/".$site_id.'/'.$gallery_id);
    }

    private function get_new_gallery_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT
            gallery_id
            FROM 
            galleries 
            ORDER BY gallery_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->gallery_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 1;
    }
    public function create_new_gallery($gallery_title,$site_id=site_id) {
        $gallery_id=$this->get_new_gallery_id();

        //insert gallery
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("INSERT INTO
            galleries (
            gallery_id,
            gallery_title,
            site_id
            ) VALUES (
            :gallery_id,
            :gallery_title,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_title', $gallery_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/,1);}
        //insert gallery confs
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("INSERT INTO
            galleries_conf (
            gallery_id,
            site_id
            ) VALUES (
            :gallery_id,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/,1);}

        return $gallery_id;
    }

    public function get_new_img_id() {
        //get new img_id
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT
                img_id
                FROM
                images
                ORDER BY
                img_id DESC
                LIMIT 1
                ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) return 1;
            else return $qr->img_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}
        return 1;
    }
    public function gallery_id2data($gallery_id,$q_select="gallery_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT 
            ".$q_select."
            FROM 
            galleries 
            WHERE 
            gallery_id=:gallery_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gallery_common 20'/*.$e->getMessage()*/);}
        return 0;
    }

    private function get_images($gallery_id,$q_fields="gallery_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT 
            ".$q_fields."
            FROM 
            images 
            WHERE 
            gallery_id=:gallery_id AND 
            site_id=:site_id 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('gallery common 365'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_gallery_conf($gallery_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT 
            row_height,
            margins
            FROM 
            galleries_conf 
            WHERE 
            gallery_id=:gallery_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('0'/*.$e->getMessage()*/);}
        return 0;
    }
    public function cache_gallery($cols_els_id,$site_id=site_id) {
        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new \uPage\common($this->uCore);
        }

        if(!$gallery_data=$this->uPage->cols_els_id2data($cols_els_id,"el_id",$site_id)) $this->uFunc->error(0,1);
        $gallery_id=$gallery_data->el_id;

        $dir='gallery/cache/'.site_id.'/'.$gallery_id;

        //До этой строчки никаких запросов в БД!!!!
        if(!file_exists($dir.'/gallery.html')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            if(!isset($this->img)) {
                require_once "gallery/classes/img.php";
                $this->img=new img($this->uCore,$site_id);
            }

            $file = fopen($dir.'/gallery.html', 'w');
            ob_start();
            $q_images=$this->get_images($gallery_id,"img_id",$site_id);?>
            <div id="uPage_gallery_<?=$gallery_id?>" class="uPage_gallery hidden">
                <?
                if(!isset($gallery_settings)) $gallery_settings=$this->get_gallery_conf($gallery_id,$site_id);
                /** @noinspection PhpUndefinedMethodInspection */
                $i=0;
                /** @noinspection PhpUndefinedMethodInspection */
                while($img=$q_images->fetch(PDO::FETCH_OBJ)) {
                    $i++;?>
                    <a href="<?=$this->img->get_img($gallery_id,$img->img_id,1500)?>">
                        <img alt="" src="<?=$this->img->get_img($gallery_id,$img->img_id,$gallery_settings->row_height)/*TODO-nik87 потом вернуть alt="title 1" - названия фоток сюда грузить*/?>" />
                    </a>
                <?}

                if(!$i) {//example
                    $images_ar=array(
                    "6791628438_affaa19e10",
                    "6798453217_72dea2d06e",
                    "6806687375_07d2b7a1f9",
                    "6812090617_5fd5bbdda0",
                    "6840627709_92ed52fb41",
                    "6841267340_855273fd7e",
                    "6876412479_6268c6e2aa",
                    "6880502467_d4b3c4b2a8",
                    "6916180091_9c9559e463",
                    "7002395006_29fdc85f7a",
                    "7062575651_b23918b11a",
                    "7822678460_ee98ff1f69",
                    "7948632554_01f6ae6b6f",
                    "8157236803_78aa1698b6",
                    "8400794773_932654a20e",
                    "8811828736_88392f614a",
                    "8842312290_f310d491f4",
                    "13824322785_104dc0968c",
                    "13824674674_ca1e482394",
                    "16961685188_f130144d60",
                    "23753792354_bd75d8dabc",
                    "24014174029_2cfa940264",
                    "24096687789_c37d45712f"
                    );
                    $images_count=count($images_ar);
                    for($i=0;$i<$images_count;$i++) {?>
                        <a href="/gallery/img/demo_images/lg/<?=$images_ar[$i]?>_b.jpg">
                            <img alt="<?=$images_ar[$i]?>" src="/gallery/img/demo_images/sm/<?=$images_ar[$i]?>_m.jpg" />
                        </a>
                    <?}
                }?>
            </div>
            <?
            fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }


        if(!file_exists($dir.'/gallery.js')) {
            if(!file_exists($dir)) mkdir($dir,0755,true);

            $file = fopen($dir.'/gallery.js', 'w');
            ob_start();
//            if(!isset($gallery_id)) $gallery_id=$this->uPage->cols_els_id2data($cols_els_id,"el_id",$site_id);

            if(!isset($gallery_settings)) $gallery_settings=$this->get_gallery_conf($gallery_id,$site_id);
            ?>
            <?//<script type="text/javascript">?>
                $(document).ready(function() {
                    $('#uPage_gallery_<?=$gallery_id?>').justifiedGallery({
                        lastRow: 'nojustify',
                        rowHeight: <?=$gallery_settings->row_height?>,
                        rel: 'gallery<?=$gallery_id?>', //replace with 'gallery1' the rel attribute of each link
                        margins: <?=$gallery_settings->margins?>
                    }).on('jg.complete', function () {
                    $(this).find('a').colorbox({
                        maxWidth : '80%',
                        maxHeight : '80%',
                        opacity : 0.8,
                        transition : 'elastic',
                        current : ''
                    });
                });
                });
                <?//</script>?>
            <?fwrite($file, ob_get_contents());
            fclose($file);
            ob_end_clean();
        }
    }
    
    public function copy_gallery($gallery_id,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_gallery_id=$this->get_new_gallery_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT 
            * 
            FROM 
            galleries 
            WHERE
            gallery_id=:gallery_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('gallery common 30'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$gallery=$stm->fetch(PDO::FETCH_OBJ)) return 0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("INSERT INTO galleries (
            gallery_id, 
            gallery_title, 
            site_id
            ) 
            VALUES (
            :gallery_id, 
            :gallery_title, 
            :site_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $new_gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_title', $gallery->gallery_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('gallery common 40'/*.$e->getMessage()*/);}

        //copy gallery's settings
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("gallery")->prepare("SELECT 
                * 
                FROM 
                galleries_conf
                WHERE
                gallery_id=:gallery_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('gallery common 50'/*.$e->getMessage()*/);}
        

        /** @noinspection PhpUndefinedMethodInspection */
        if($settings=$stm->fetch(PDO::FETCH_OBJ)) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("gallery")->prepare("INSERT INTO galleries_conf (
                gallery_id,
                site_id,
                row_height,
                margins
                ) VALUES (
                :gallery_id,
                :site_id,
                :row_height,
                :margins
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $new_gallery_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_height', $settings->row_height, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':margins', $settings->margins, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            } catch (PDOException $e) {$this->uFunc->error('gallery common 80'/*.$e->getMessage()*/);}
        }

        //copy images
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("gallery")->prepare("SELECT
//            *
//            FROM
//            images
//            WHERE
//            gallery_id=:gallery_id AND
//            site_id=:site_id
//            ");
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//
//            /** @noinspection PhpUndefinedMethodInspection */
//            while($slide=$stm->fetch(PDO::FETCH_OBJ)) {
//                $this->copy_image($new_gallery_id,$slide,$source_site_id,$dest_site_id);
//            }
//        }
//        catch(PDOException $e) {$this->uFunc->error('gallery common 110'/*.$e->getMessage()*/);}

        return $new_gallery_id;
    }

//    private function copy_image($new_gallery_id,$slide,$source_site_id=site_id,$dest_site_id=0) {
//        if(!$dest_site_id) $dest_site_id=$source_site_id;
//        $new_img_id=$this->get_new_img_id();
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("gallery")->prepare("INSERT INTO images (
//            gallery_id,
//            img_id,
//            site_id
//            ) VALUES (
//            :gallery_id,
//            :img_id,
//            :site_id
//            )
//            ");
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $new_gallery_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_id', $new_img_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('gallery common 10'/*.$e->getMessage()*/);}
//
//        //Copy files
//        if((int)$slide->img_timestamp) {
//            $src_folder= 'gallery/img/site_images/'.$source_site_id.'/'.$slide->img_id.'/';
//            $dest_folder= 'gallery/img/site_images/'.$dest_site_id.'/'.$new_img_id.'/';
//
//            $src_file=$src_folder.$slide->img_id.'.jpg';
//            $dest_file=$dest_folder.$new_img_id.'.jpg';
//
//            // Create dir
//            if(!file_exists($dest_folder)) mkdir($dest_folder,0755,true);
//            if(!$this->uFunc->create_empty_index($dest_folder)) $this->uFunc->error("gallery common 20");
//
//            //copy file
//            @copy ($src_file,$dest_file);
//        }
//    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();//This string is made just for IDE to know who is uCore
        $this->uFunc=new uFunc($this->uCore);
//        $this->uSes=new uSes($this->uCore);


//        $this->uCore->uInt_js('gallery','common');
    }
}
//$newClass=new newClass($this);