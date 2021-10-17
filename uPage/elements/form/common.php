<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uForms_form;
use uPage\common;
use uString;

class form{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($this->uForms)) {
            require_once "uForms/inc/common.php";
            $this->uForms=new \uForms($this->uCore);
        }

        $new_el_id=$this->uForms->copy_form($el->el_id,$source_site_id,$dest_site_id);

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'form',$el->el_pos,$el->el_style,$new_el_id,$dest_site_id);

        return $cols_els_id;
    }

    public function attach_el2col($el_id,$col_id) {
        //check if this form_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            form_id
            FROM 
            u235_forms 
            WHERE 
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage_elements_form_common 10");
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_form_common 20'/*.$e->getMessage()*/);}

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $this->uPage->add_el2db($cols_els_id,$el_pos,'form',$col_id,$el_id);
    }

    public function load_el_content($el_id,$cols_els_id) {
        if(!isset($this->uForms_form)) {
            require_once 'uForms/inc/form_builder.php';
            $this->uForms_form = new uForms_form($this->uCore);
        }

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("uPage_elements_form_common 300");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        $form_id=$el_id;
        $this->uForms_form->check_data($form_id);
        $dir='uForms/cache/'.site_id.'/'.$form_id;
        if(!file_exists($dir.'/form.html')) $this->uForms_form->build_form_php($dir,$this->uForms_form->form_id);
        echo('{
        "status":"done",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "el_id":"'.rawurlencode($el_id).'",
        
        "cols_els_id":"'.$cols_els_id.'",
        "cnt":"'.rawurlencode(file_get_contents($dir."/form.html")).'"
        }');
    }
    
    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}