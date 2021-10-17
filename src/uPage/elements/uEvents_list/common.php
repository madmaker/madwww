<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uEvents\events;
use uPage\common;
use uString;

class uEvents_list{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uEvents_list',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($el_id,$col_id) {
        //check if this type_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uEvents")->prepare("SELECT
            type_id
            FROM
            u235_events_types
            WHERE
            type_id=:type_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':type_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage_elements_uEvents_list_common 10");
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uEvents_list_common 20'/*.$e->getMessage()*/);}

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach events list to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'uEvents_list',$col_id,$el_id);
    }

    public function load_el_content($el_id,$cols_els_id) {
        $cache_dir="uEvents/cache/events/".site_id."/".$el_id;
        if(!file_exists($cache_dir."/events_list.html")) {
            require_once "uEvents/events.php";

            $setup_uEvents=new events($this->uCore);
            $setup_uEvents->type_id=$el_id;
            if($setup_uEvents->check_data()) $setup_uEvents->build_events_list_cache();
        }

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("uPage_elements_uEvents_list_common 500");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        echo('{
        "status":"done",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "el_id":"'.rawurlencode($el_id).'",
        
        "cols_els_id":"'.$cols_els_id.'",
        "content":"'.rawurlencode(file_get_contents($cache_dir."/events_list.html")).'"
        }');
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}