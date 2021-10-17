<?php
namespace uPage\admin;

use PDO;
use PDOException;
use uPage\common;

class code {
    private $uPage;
    private $uFunc;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_code 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_code_common 10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach element to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'code',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_code (
            cols_els_id, 
            site_id, 
            code, 
            do_not_run_in_editor
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :code, 
            :do_not_run_in_editor          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':code', $el_settings->code,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':do_not_run_in_editor', $el_settings->do_not_run_in_editor,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_code_common 20'/*.$e->getMessage()*/);}
        return $cols_els_id;
    }
    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'code',$col_id,$el_id);


        if(!isset($_POST['code'],$_POST['do_not_run_in_editor'])) $this->uFunc->error("uPage_elements_code_common 30");
        $code=$_POST['code'];
        $do_not_run_in_editor=(int)$_POST['do_not_run_in_editor'];
        if($do_not_run_in_editor) $do_not_run_in_editor=1;

        $res[0]='
        "do_not_run_in_editor":"'.$do_not_run_in_editor.'",
        "code":"'.rawurlencode($code).'",'.$res[0];

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_code (
                cols_els_id,
                site_id,
                code,
                do_not_run_in_editor
            ) VALUES (
              :cols_els_id,
              :site_id,
              :code,
              :do_not_run_in_editor
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':code', $code,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':do_not_run_in_editor', $do_not_run_in_editor,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_code_common 40'.$e->getMessage());}

        return '{
        '.$res[0].'}';
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("uPage_elements_code_common 50");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        echo('{
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "code":"'.rawurlencode($conf->code).'",
        "do_not_run_in_editor":"'.$conf->do_not_run_in_editor.'"
        }');

    }

    public function save_el_conf($cols_els_id) {
        if(!isset($_POST['code'],$_POST['do_not_run_in_editor'])) $this->uFunc->error(220);

        $code=trim($_POST['code']);
        $do_not_run_in_editor=(int)$_POST['do_not_run_in_editor'];
        if($do_not_run_in_editor) $do_not_run_in_editor=1;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_code
                SET 
                code=:code,
                do_not_run_in_editor=:do_not_run_in_editor
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':do_not_run_in_editor', $do_not_run_in_editor,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':code', $code,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('230'.$e->getMessage());}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=$this->uPage->uFunc;
    }
}