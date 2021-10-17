<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

require_once "uRubrics/classes/common.php";

class urubrics_tiles {
    private $uRubrics;
    private $uFunc;
    private $uPage;
    private $uCore;

    public function get_rubric_name($el_id,$site_id=site_id) {
        $qr=$this->uRubrics->rubric_id2data($el_id,"rubric_name",$site_id);

        if($qr) return uString::sql2text($qr->rubric_name,1);
        return "";
    }

    private function create_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_urubrics_tiles (
            cols_els_id, 
            site_id
            ) VALUES (
            :cols_els_id, 
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_tiles_common 10'/*.$e->getMessage()*/);}
    }
    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_urubrics_tiles 
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_el_settings($cols_els_id,$site_id);
                $conf=$this->get_el_settings($cols_els_id,$site_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_tiles_common 20'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'rubrics_tiles',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_urubrics_tiles (
            cols_els_id, 
            site_id, 
            col_number, 
            `row_number` 
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :col_number, 
            :row_number 
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_number', $el_settings->col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_number', $el_settings->row_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_tiles_common 30'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }
    public function get_pages_of_rubric($el_id,$conf,$site_id=site_id) {
        return $this->uRubrics->get_pages_of_rubric($el_id,$conf->row_number*$conf->col_number,0,$site_id);
    }

    public function load_element_cnt($cols_els_id,$el_id,$return=0/*,$site_id=site_id*/) {
        $conf=$this->get_el_settings($cols_els_id);
        $conf->col_number=(int)$conf->col_number;

        $pages_stm=$this->get_pages_of_rubric($el_id,$conf);
        /** @noinspection PhpUndefinedMethodInspection */
        $pages_ar=$pages_stm->fetchAll(PDO::FETCH_OBJ);
        ob_start();?>

        <div id="uPage_rubrics_tiles_<?=$cols_els_id?>">
            <?$this->uRubrics->print_rubric_tiles($pages_ar,$conf->col_number)?>
        </div>
        <?$cnt=ob_get_contents();
        ob_end_clean();

        $result_ar=(object)array(
            "cols_els_id"=>$cols_els_id,
            "cnt"=>$cnt,
            "col_number"=>$conf->col_number,
            "row_number"=>$conf->row_number,
            "status"=>"done"
        );

        if($return) return $result_ar;
        else echo json_encode($result_ar);

        return 0;
    }
    public function attach_el($el_id,$col_id,$site_id=site_id) {
        if(!$this->uRubrics->rubric_id2data($el_id,"rubric_id")) $this->uFunc->error("uPage_elements_rubrics_tiles_common 40");

        $el_pos=$this->uPage->define_new_el_pos($col_id,$site_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id($site_id);

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'rubrics_tiles',$col_id,$el_id,$site_id);

        $conf=$this->get_el_settings($cols_els_id,$site_id);
        echo '{';
        echo '"col_number":"'.$conf->col_number.'",';
        echo '"row_number":"'.$conf->row_number.'",';
        echo $res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['col_number'])) {
            $col_number=(int)$_POST['col_number'];
            if($col_number<1) $col_number=3;
        }
        else $col_number=3;

        if(isset($_POST['row_number'])) {
            $row_number=(int)$_POST['row_number'];
            if($row_number<1) $row_number=3;
        }
        else $row_number=3;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_urubrics_tiles 
                SET 
                col_number=:col_number,
                `row_number`=:row_number
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_number', $col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_number', $row_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_tiles_common 50'/*.$e->getMessage()*/);}


        if(isset($_POST['rubric_id'])) {
            $rubric_id=(int)$_POST['rubric_id'];
            if($this->uRubrics->rubric_id2data($rubric_id,"rubric_id")) {
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE 
                    u235_cols_els 
                    SET
                    el_id=:el_id
                    WHERE
                    cols_els_id=:cols_els_id AND
                    site_id=:site_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_id', $rubric_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_tiles_common 60'/*.$e->getMessage()*/);}

            }
        }

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit( json_encode(array(
            "cols_els_id"=>$cols_els_id,
            "status"=>"done"
        )));
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
        $this->uRubrics=new \uRubrics\common($this->uCore);
    }
}
