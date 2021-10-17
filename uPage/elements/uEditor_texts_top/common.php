<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

class uEditor_texts_top{
    private $uFunc;
    private $uPage;
    private $uCore;

    //Widgets
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        //attach sects to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uEditor_texts_top',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'uEditor_texts_top',$col_id,$el_id);
    }

    public function load_el_content($cols_els_id) {
        if(!isset($this->uCat)) {
            require_once 'uCat/classes/common.php';
            $this->uCat=new \uCat\common($this->uCore);
        }
        if(site_id!=6) {
            if (!isset($this->uEditor)) {
                require_once 'uEditor/classes/common.php';
                $this->uEditor = new \uEditor\common($this->uCore);
            }
        }


        echo('{
        "status":"done",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "cols_els_id":"'.$cols_els_id.'",
        "cnt":"'.rawurlencode($this->uPage->build_pages_top_widget()).'"
        }');
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}
