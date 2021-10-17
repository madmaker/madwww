<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class owl_carousel{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($this->uSlider)) {
            require_once 'uSlider/inc/common.php';
            if (!isset($this->uSlider)) $this->uSlider = new \uSlider\common($this->uCore);
        }

        $new_el_id=$this->uSlider->copy_slider($el->el_id,$source_site_id,$dest_site_id);

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'owl_carousel',$el->el_pos,$el->el_style,$new_el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($el_id,$col_id) {
        //check if this slider_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSlider")->prepare("SELECT 
            slider_id
            FROM 
            u235_sliders 
            WHERE 
            slider_id=:slider_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':slider_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage_elements_owl_carousel_common 10");
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_owl_carousel_common 20'/*.$e->getMessage()*/);}

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'owl_carousel',$col_id,$el_id);

        echo '{'.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$el_id) {
        if(!isset($this->uSlider)) {
            require_once 'uSlider/inc/common.php';
            if (!isset($this->uSlider)) $this->uSlider = new \uSlider\common($this->uCore);
        }

        $this->uSlider->cache_owl_slider($cols_els_id);
        $dir='uSlider/cache/'.site_id.'/'.$cols_els_id;

        echo json_encode(array(
            "status"=>"done",
            "cols_els_id"=>$cols_els_id,
            "el_id"=>$el_id,

            "html"=>file_get_contents($dir."/slider.html"),
            "js"=>file_get_contents($dir."/slider.js")
        ));
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}