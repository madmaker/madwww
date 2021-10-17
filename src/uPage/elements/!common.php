<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;

class el_name{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function copy_el($cols_els_id,$new_col_id,$el,$site_id=site_id) {
        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'rubrics_arts',$el->el_pos,$el->el_style,$el->el_id,$site_id);

        return 1;
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}