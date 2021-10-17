<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class uCat_latest_articles_slider {
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
            el_config_uCat_latest_articles_slider (
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
        catch(PDOException $e) {$this->uFunc->error('uCat_latest_articles_slider 10'/*.$e->getMessage()*/);}
    }
    public function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            items_number,
            dots_style,
            title
            FROM
            el_config_uCat_latest_articles_slider
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
        catch(PDOException $e) {$this->uFunc->error('uCat_latest_articles_slider 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach sects to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uCat_latest_articles_slider',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_uCat_latest_articles_slider (
            cols_els_id,
            site_id,
            items_number,
            dots_style,
            title
            ) VALUES (
            :cols_els_id,
            :site_id,
            :items_number,
            :dots_style,
            :title
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $el_settings->items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $el_settings->dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $el_settings->title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_latest_articles_slider 30'/*.$e->getMessage()*/);}
        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'uCat_latest_articles_slider',$col_id,$el_id);

        $res_ar=json_decode('{'.$res[0].'}');
        $res_ar->status="done";
        $res_ar->cols_els_id=$cols_els_id;

        echo  json_encode($res_ar);
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        if(!isset($this->uCat)) {
            require_once "uCat/classes/common.php";
            $this->uCat=new \uCat\common($this->uCore);
        }
        $cnt=$this->uCat->latest_articles_slider_widget($cols_els_id);

        $conf=$this->get_el_settings($cols_els_id,$site_id);


        echo json_encode(array(
        "status"=>"done",
        
        "title"=>$conf->title,
        "items_number"=>$conf->items_number,
        "dots_style"=>$conf->dots_style,

        "cols_els_id"=>$cols_els_id,
        "cnt"=>$cnt
        ));
    }

    public function save_el_conf($cols_els_id) {

        $title="Последние статьи";
        if(isset($_POST['title'])) $title=trim($_POST['title']);
        if($title==="") $title="Последние статьи";

        $items_number=20;
        if(isset($_POST['items_number'])) $items_number=(int)$_POST['items_number'];
        if($items_number<1) $items_number=1;
        if($items_number>50) $items_number=50;

        $dots_style=0;
        if(isset($_POST['dots_style'])) $dots_style=(int)$_POST['dots_style'];
        if($dots_style<1) $dots_style=0;
        if($dots_style>16) $dots_style=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_uCat_latest_articles_slider
                SET 
                title=:title,
                items_number=:items_number,
                dots_style=:dots_style
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title', $title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':items_number', $items_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uCat_latest_articles_slider  40'/*.$e->getMessage()*/);}

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