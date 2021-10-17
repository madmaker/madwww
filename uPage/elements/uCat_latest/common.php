<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class uCat_latest {
    private $uFunc;
    private $uPage;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','uCat_latest'),$str);
    }

    private function create_default_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_uCat_latest(
            cols_els_id, 
            site_id
            ) VALUES (
            :cols_els_id, 
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_latest_common 10'/*.$e->getMessage()*/);}
    }
    public function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            items_number,
            title,
            xlg_number,
            lg_number,
            md_number,
            sm_number,
            xs_number,
            slide_height,
            image_style,
            dots_style,
            xlg_show_markers,
            lg_show_markers,
            md_show_markers,
            sm_show_markers,
            xs_show_markers,
            xlg_show_arrows,
            lg_show_arrows,
            md_show_arrows,
            sm_show_arrows,
            xs_show_arrows
            FROM
            el_config_uCat_latest
            WHERE 
            cols_els_id=:cols_els_id AND 
            site_id=:site_id 
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_default_el_settings($cols_els_id);
                $conf=$this->get_el_settings($cols_els_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_latest_common 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach sects to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uCat_latest',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_uCat_latest (
            cols_els_id, 
            site_id, 
            items_number,
            title,
            xlg_number,
            lg_number,
            md_number,
            sm_number,
            xs_number,
            slide_height,
            image_style,
            dots_style,
            xlg_show_markers,
            lg_show_markers,
            md_show_markers,
            sm_show_markers,
            xs_show_markers,
            xlg_show_arrows,
            lg_show_arrows,
            md_show_arrows,
            sm_show_arrows,
            xs_show_arrows
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :items_number,
            :title,
            :xlg_number,
            :lg_number,
            :md_number,
            :sm_number,
            :xs_number,
            :slide_height,
            :image_style,
            :dots_style,
            :xlg_show_markers,
            :lg_show_markers,
            :md_show_markers,
            :sm_show_markers,
            :xs_show_markers,
            :xlg_show_arrows,
            :lg_show_arrows,
            :md_show_arrows,
            :sm_show_arrows,
            :xs_show_arrows
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $el_settings->items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $el_settings->title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_number', $el_settings->xlg_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_number', $el_settings->lg_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_number', $el_settings->md_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_number', $el_settings->sm_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_number', $el_settings->xs_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_height', $el_settings->slide_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':image_style', $el_settings->image_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $el_settings->dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_show_markers', $el_settings->xlg_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_show_markers', $el_settings->lg_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_show_markers', $el_settings->md_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_show_markers', $el_settings->sm_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_show_markers', $el_settings->xs_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_show_arrows', $el_settings->xlg_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_show_arrows', $el_settings->lg_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_show_arrows', $el_settings->md_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_show_arrows', $el_settings->sm_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_show_arrows', $el_settings->xs_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_latest_common 30'/*.$e->getMessage()*/);}
        return $cols_els_id;
    }

    public function attach_uCat_latest($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'uCat_latest',$col_id,$el_id);


        echo '{';
        echo $res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        if(!isset($this->uCat)) {
            require_once "uCat/classes/common.php";
            $this->uCat=new \uCat\common($this->uCore);
        }
        $cnt=$this->uCat->last_items_widget($cols_els_id,1);

        $conf=$this->get_el_settings($cols_els_id,$site_id);

        echo json_encode(array(
        "status"=>"done",
        
        "title"=>$conf->title,
        "items_number"=>$conf->items_number,
        "xlg_number"=>$conf->xlg_number,
        "lg_number"=>$conf->lg_number,
        "md_number"=>$conf->md_number,
        "sm_number"=>$conf->sm_number,
        "xs_number"=>$conf->xs_number,

        "slide_height"=>$conf->slide_height,
        "image_style"=>$conf->image_style,
        "dots_style"=>$conf->dots_style,
        "xlg_show_markers"=>$conf->xlg_show_markers,
        "lg_show_markers"=>$conf->lg_show_markers,
        "md_show_markers"=>$conf->md_show_markers,
        "sm_show_markers"=>$conf->sm_show_markers,
        "xs_show_markers"=>$conf->xs_show_markers,
        "xlg_show_arrows"=>$conf->xlg_show_arrows,
        "lg_show_arrows"=>$conf->lg_show_arrows,
        "md_show_arrows"=>$conf->md_show_arrows,
        "sm_show_arrows"=>$conf->sm_show_arrows,
        "xs_show_arrows"=>$conf->xs_show_arrows,

        "cols_els_id"=>$cols_els_id,
        "cnt"=>$cnt
        ));
    }

    public function save_el_conf($cols_els_id) {

        $title=$this->text("New arrival - element title"/*Последние поступления*/);
        if(isset($_POST['title'])) $title=trim($_POST['title']);
        if($title==="") $title=$this->text("New arrival - element title"/*Последние поступления*/);

        $items_number=20;
        if(isset($_POST['items_number'])) $items_number=(int)$_POST['items_number'];
        if($items_number<1) $items_number=1;
        if($items_number>50) $items_number=50;


        $xlg_number=8;//5 4 2 1
        if(isset($_POST['xlg_number'])) $xlg_number=(int)$_POST["xlg_number"];
        if($xlg_number<1) $xlg_number=1;
        if($xlg_number>20) $xlg_number=20;

        $lg_number=5;
        if(isset($_POST['lg_number'])) $lg_number=(int)$_POST["lg_number"];
        if($lg_number<1) $lg_number=1;
        if($lg_number>20) $lg_number=20;

        $md_number=4;
        if(isset($_POST['md_number'])) $md_number=(int)$_POST["md_number"];
        if($md_number<1) $md_number=1;
        if($md_number>20) $md_number=20;

        $sm_number=2;
        if(isset($_POST['sm_number'])) $sm_number=(int)$_POST["sm_number"];
        if($sm_number<1) $sm_number=1;
        if($sm_number>20) $sm_number=20;

        $xs_number=1;
        if(isset($_POST['xs_number'])) $xs_number=(int)$_POST["xs_number"];
        if($xs_number<1) $xs_number=1;
        if($xs_number>20) $xs_number=20;

        $slide_height=250;
        if(isset($_POST['slide_height'])) $slide_height=(int)$_POST["slide_height"];
        if($slide_height<20) $slide_height=20;
        if($slide_height>1000) $slide_height=1000;

        $image_style=0;
        if(isset($_POST['image_style'])) $image_style=(int)$_POST["image_style"];
        if($image_style<0) $image_style=1;
        if($image_style>1) $image_style=1;

        $dots_style=0;
        if(isset($_POST['dots_style'])) $dots_style=(int)$_POST["dots_style"];
        if($dots_style<0) $dots_style=0;
        if($dots_style>16) $dots_style=0;

        $xlg_show_markers=1;
        if(isset($_POST['xlg_show_markers'])) $xlg_show_markers=(int)$_POST["xlg_show_markers"];
        if($xlg_show_markers) $xlg_show_markers=1;

        $lg_show_markers=1;
        if(isset($_POST['lg_show_markers'])) $lg_show_markers=(int)$_POST["lg_show_markers"];
        if($lg_show_markers) $lg_show_markers=1;

        $md_show_markers=1;
        if(isset($_POST['md_show_markers'])) $md_show_markers=(int)$_POST["md_show_markers"];
        if($md_show_markers) $md_show_markers=1;

        $sm_show_markers=1;
        if(isset($_POST['sm_show_markers'])) $sm_show_markers=(int)$_POST["sm_show_markers"];
        if($sm_show_markers) $sm_show_markers=1;

        $xs_show_markers=1;
        if(isset($_POST['xs_show_markers'])) $xs_show_markers=(int)$_POST["xs_show_markers"];
        if($xs_show_markers) $xs_show_markers=1;

        $xlg_show_arrows=1;
        if(isset($_POST['xlg_show_arrows'])) $xlg_show_arrows=(int)$_POST["xlg_show_arrows"];
        if($xlg_show_arrows) $xlg_show_arrows=1;

        $lg_show_arrows=1;
        if(isset($_POST['lg_show_arrows'])) $lg_show_arrows=(int)$_POST["lg_show_arrows"];
        if($lg_show_arrows) $lg_show_arrows=1;

        $md_show_arrows=1;
        if(isset($_POST['md_show_arrows'])) $md_show_arrows=(int)$_POST["md_show_arrows"];
        if($md_show_arrows) $md_show_arrows=1;

        $sm_show_arrows=1;
        if(isset($_POST['sm_show_arrows'])) $sm_show_arrows=(int)$_POST["sm_show_arrows"];
        if($sm_show_arrows) $sm_show_arrows=1;

        $xs_show_arrows=1;
        if(isset($_POST['xs_show_arrows'])) $xs_show_arrows=(int)$_POST["xs_show_arrows"];
        if($xs_show_arrows) $xs_show_arrows=1;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_uCat_latest
                SET 
                title=:title,
                items_number=:items_number,
                xlg_number=:xlg_number,
                lg_number=:lg_number,
                md_number=:md_number,
                sm_number=:sm_number,
                xs_number=:xs_number,
                slide_height=:slide_height,
                image_style=:image_style,
                dots_style=:dots_style,
                xlg_show_markers=:xlg_show_markers,
                lg_show_markers=:lg_show_markers,
                md_show_markers=:md_show_markers,
                sm_show_markers=:sm_show_markers,
                xs_show_markers=:xs_show_markers,
                xlg_show_arrows=:xlg_show_arrows,
                lg_show_arrows=:lg_show_arrows,
                md_show_arrows=:md_show_arrows,
                sm_show_arrows=:sm_show_arrows,
                xs_show_arrows=:xs_show_arrows
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_number', $xlg_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_number', $lg_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_number', $md_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_number', $sm_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_number', $xs_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slide_height', $slide_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':image_style', $image_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_show_markers', $xlg_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_show_markers', $lg_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_show_markers', $md_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_show_markers', $sm_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_show_markers', $xs_show_markers,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xlg_show_arrows', $xlg_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':lg_show_arrows', $lg_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':md_show_arrows', $md_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sm_show_arrows', $sm_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':xs_show_arrows', $xs_show_arrows,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_latest_common 40'/*.$e->getMessage()*/);}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}