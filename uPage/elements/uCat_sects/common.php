<?php
namespace uPage\admin;
use uPage\common;
use uString;

class uCat_sects {
    private $uFunc;
    private $uPage;
    private $uCore;

    public function load_el_content($cols_els_id) {
        require_once 'uCat/classes/common.php';
        if(!isset($this->uCat)) $this->uCat=new common($this->uCore);


        echo('{
        "status":"done",
                
        "cols_els_id":"'.$cols_els_id.'",
        "cnt":"'.rawurlencode($this->uCat->sects_list_widget()).'"
        }');
    }

    public function copy_el($cols_els_id, $new_col_id, $el, /** @noinspection PhpUnusedParameterInspection */$source_site_id=site_id, $dest_site_id=0) {
        $this->uPage->create_el($cols_els_id,$new_col_id,'uCat_sects',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);
        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();
        //attach sects to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'uCat_sects',$col_id,$el_id);
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}