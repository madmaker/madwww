<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class gallery{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('gallery','elements_gallery_common'),$str);
    }

    private function get_el_settings($gallery_id,$site_id=site_id) {
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 10'/*.$e->getMessage()*/);}

        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($this->gallery)) {
            require_once 'gallery/classes/common.php';
            if (!isset($this->gallery)) $this->gallery = new \gallery\common($this->uCore);
        }

        $new_el_id=$this->gallery->copy_gallery($el->el_id,$source_site_id,$dest_site_id);

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'gallery',$el->el_pos,$el->el_style,$new_el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($el_id,$col_id) {
        if(!isset($this->gallery)) {
            require_once "gallery/classes/common.php";
            $this->gallery=new \gallery\common($this->uCore);
        }
        //check if this gallery_id exists
        if(!$this->gallery->gallery_id2data($el_id,"gallery_id")) $this->uFunc->error(0,1);

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'gallery',$col_id,$el_id);

        echo '{'.$res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        if(!isset($this->gallery)) {
            require_once 'gallery/classes/common.php';
            if (!isset($this->gallery)) $this->gallery = new \gallery\common($this->uCore);
        }

        if(!$el_data=$this->uPage->cols_els_id2data($cols_els_id,"el_id")) $this->uFunc->error(0,1);
        $gallery_id=(int)$el_data->el_id;

        $row_height=50;
        if(isset($_POST['row_height'])) {
            if((int)$_POST['row_height']>50) $row_height=(int)$_POST['row_height'];
        }
        $margins=1;
        if(isset($_POST['margins'])) {
            if((int)$_POST['margins']>0) $margins=(int)$_POST['margins'];
        }


        $gallery_title=$this->text("default gallery title");
        if(isset($_POST['gallery_title'])) {
            $_POST['gallery_title']=trim($_POST['gallery_title']);
            if(strlen($_POST["gallery_title"])) $gallery_title=$_POST['gallery_title'];
        }

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("UPDATE
                galleries_conf
                SET 
                row_height=:row_height,
                margins=:margins
                WHERE 
                gallery_id=:gallery_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_height', $row_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':margins', $margins,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 50'.$e->getMessage());}
        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("gallery")->prepare("UPDATE
                galleries
                SET 
                gallery_title=:gallery_title
                WHERE 
                gallery_id=:gallery_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_id', $gallery_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':gallery_title', $gallery_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_gmap_common 50'.$e->getMessage());}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);
        $this->gallery->clear_cache($gallery_id);

        exit('{
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    public function load_el_content($cols_els_id,$el_id) {
        if(!isset($this->gallery)) {
            require_once 'gallery/classes/common.php';
            if (!isset($this->gallery)) $this->gallery = new \gallery\common($this->uCore);
        }

        $this->gallery->cache_gallery($cols_els_id);
        $dir='gallery/cache/'.site_id.'/'.$el_id;

        echo json_encode(array(
            "status"=>"done",
            "cols_els_id"=>$cols_els_id,
            "el_id"=>$el_id,
            "html"=>file_get_contents($dir."/gallery.html"),
            "js"=>file_get_contents($dir."/gallery.js")
        ));
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();//Just for IDE to know who is uCore
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}