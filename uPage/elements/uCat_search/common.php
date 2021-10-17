<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class uCat_search {
    private $uFunc;
    private $uPage;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uPage','uCat_search'),$str);
    }

    private function create_default_el_config_uCat_search($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_uCat_search(
            cols_els_id, 
            placeholder, 
            site_id
            ) VALUES (
            :cols_els_id, 
            :placeholder, 
            :site_id
            )
            ");
            $site_id=site_id;
            $placeholder=$this->uPage->text("Catalog search - input placeholder");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':placeholder', $placeholder,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_search_common 10'/*.$e->getMessage()*/);}
    }
    public function get_el_config_uCat_search($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            placeholder
            FROM
            el_config_uCat_search
            WHERE 
            cols_els_id=:cols_els_id AND 
            site_id=:site_id 
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_default_el_config_uCat_search($cols_els_id);
                $conf=$this->get_el_config_uCat_search($cols_els_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_search_common 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function load_el_content($cols_els_id) {
        if(!isset($this->uCat)) {
            require_once "uCat/classes/common.php";
            $this->uCat=new \uCat\common($this->uCore);
        }
        $cnt=$this->uCat->search_widget($cols_els_id);

        echo('{
        "status":"done",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "cols_els_id":"'.$cols_els_id.'",
        "cnt":"'.rawurlencode($cnt).'"
        }');
    }

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_uCat_search 
            WHERE 
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_search_common 30'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        $this->uPage->create_el($cols_els_id,$new_col_id,'uCat_search',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_uCat_search (
            cols_els_id, 
            site_id, 
            placeholder
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :placeholder
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':placeholder', $el_settings->placeholder,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uCat_search_common 40'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2cat($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'uCat_search',$col_id,$el_id);

        $conf=$this->get_el_config_uCat_search($cols_els_id);

        echo '{';
        echo '"placeholder":"'.$conf->placeholder.'",';
        echo $res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['placeholder'])) {
            $placeholder=trim($_POST['placeholder']);
        }
        else $placeholder=$this->text("Catalog search - input placeholder"/*Поиск*/);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_uCat_search
                SET 
                placeholder=:placeholder
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':placeholder', $placeholder,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}


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