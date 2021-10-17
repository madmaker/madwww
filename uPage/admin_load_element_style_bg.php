<?php
use processors\uFunc;
use uCat\common;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uPage/inc/common.php';
require_once 'uCat/classes/common.php';

class admin_load_element_style_bg {
    public $uFunc;
    public $uSes;
    public $uPage;
    public $uCat;
    private $uCore,$cols_els_id,$el_type,$el_id;
    private function check_data() {
        if(!isset($_POST['cols_els_id'])) $this->uFunc->error(10);
        $this->cols_els_id=$_POST['cols_els_id'];
        if(!uString::isDigits($this->cols_els_id)) $this->uFunc->error(20);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);


        $this->check_data();
        if(!$cols_el=$this->uPage->cols_els_id2data($this->cols_els_id,"el_css,el_font_color,el_link_color,el_hoverlink_color")) $this->uFunc->error(30);
        $el_css=uString::sql2text($cols_el->el_css,1);

        echo('{
        "status":"done",
        "cols_els_id":"'.$this->cols_els_id.'",

        "el_css":"'.rawurlencode($el_css).'",
        
        "el_font_color":"'.$cols_el->el_font_color.'",
        "el_link_color":"'.$cols_el->el_link_color.'",
        "el_hoverlink_color":"'.$cols_el->el_hoverlink_color.'"
        
        }');
    }
}
new admin_load_element_style_bg ($this);