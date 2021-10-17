<?php
namespace uPage\admin;
use uNavi\common\uNavi;
use uPage\common;
use uString;

class menu{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($this->uNavi)) {
            require_once "uNavi/classes/uNavi.php";
            $this->uNavi=new uNavi($this->uCore);
        }

        $new_el_id=$this->uNavi->copy_cat($el->el_id,$source_site_id,$dest_site_id);

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'menu',$el->el_pos,$el->el_style,$new_el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'menu',$col_id,$el_id);


        echo '{'.$res[0].'}';
        exit;
    }
    
    public function load_el_content($el_id,$cols_els_id) {
        $cache_dir="uNavi/cache/menu/".site_id."/".$el_id;
        if(!file_exists($cache_dir."/menu.html")) {
            if(!isset($this->uMenu)) {
                require_once "processors/uMenu.php";
                $this->uMenu=new \uMenu($this->uCore);
            }
            $this->uMenu->build_menu_cache($el_id);
        }

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("/uPage/elements/menu/common/10");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        echo('{
        "status":"done",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "el_id":"'.rawurlencode($el_id).'",
        
        "cols_els_id":"'.$cols_els_id.'",
        "content":"'.rawurlencode(file_get_contents($cache_dir."/menu.html")).'"
        }');
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}