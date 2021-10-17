<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

require_once "uRubrics/classes/common.php";

class urubrics_arts_column {
    private $uRubrics;
    private $uFunc;
    private $uPage;
    private $uCore;

    private function create_default_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_urubrics_arts_column (
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_column_common 10'/*.$e->getMessage()*/);}
    }
    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_urubrics_arts_column 
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_default_el_settings($cols_els_id,$site_id);
                $conf=$this->get_el_settings($cols_els_id,$site_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_column_common 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'rubrics_arts_column',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_urubrics_arts_column (
            cols_els_id, 
            site_id, 
            col_number, 
            art_number, 
            img_col_number, 
            show_avatars, 
            show_short_text, 
            show_title
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :col_number, 
            :art_number, 
            :img_col_number, 
            :show_avatars, 
            :show_short_text, 
            :show_title          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_number', $el_settings->col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_number', $el_settings->art_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_col_number', $el_settings->img_col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatars', $el_settings->show_avatars,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_short_text', $el_settings->show_short_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $el_settings->show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_column_common 30'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function get_rubric_name($el_id,$site_id=site_id) {
        $qr=$this->uRubrics->rubric_id2data($el_id,"rubric_name",$site_id);

        if($qr) return uString::sql2text($qr->rubric_name,1);
        return "";
    }
    public function get_texts_of_rubric($el_id,$limit,$site_id=site_id) {
        return $this->uRubrics->get_texts_of_rubric($el_id,$limit,0,$site_id);
    }
    public function get_pages_of_rubric($el_id,$limit,$site_id=site_id) {
        return $this->uRubrics->get_pages_of_rubric($el_id,$limit,0,$site_id);
    }

    public function load_element_cnt($cols_els_id,$el_id,$return=0,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id);
        $conf->col_number=(int)$conf->col_number;

        $rubric_name=$this->get_rubric_name($el_id,$site_id);

        $total_limit=$conf->art_number*$conf->col_number;
        $stm_pages=$this->get_pages_of_rubric($el_id,$total_limit,$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $page_ar=$stm_pages->fetchAll(PDO::FETCH_OBJ);
        $pare_ar_count=count($page_ar);
        $texts_limit=$total_limit-$pare_ar_count;
        if($texts_limit) {
            $stm_texts = $this->get_texts_of_rubric($el_id, $texts_limit, $site_id);

            /** @noinspection PhpUndefinedMethodInspection */
            $text_ar=$stm_texts->fetchAll(PDO::FETCH_OBJ);

            $common_ar=array_merge($page_ar,$text_ar);
        }
        else $common_ar=$page_ar;

        ob_start();?>

        <div id="uPage_rubrics_arts_column_<?=$cols_els_id?>" class="uPage_rubrics_arts_column">
            <?$this->uRubrics->print_rubric_columns($common_ar,$conf->col_number,$conf);?>
        </div>
        <?$cnt=ob_get_contents();
        ob_end_clean();


        $result_ar=(object)array(
            "cols_els_id"=>$cols_els_id,
            "col_number"=>$conf->col_number,
            "art_number"=>$conf->art_number,
            "img_col_number"=>$conf->img_col_number,
            "show_avatars"=>$conf->show_avatars,
            "show_short_text"=>$conf->show_short_text,
            "show_title"=>$conf->show_title,
            "cnt"=>$cnt,
            "rubric_name"=>$rubric_name,
            "status"=>"done"
        );

        if($return) return $result_ar;
        else echo json_encode($result_ar);

        return 0;
    }

    public function attach_el($el_id,$col_id,$site_id=site_id) {
        if(!$this->uRubrics->rubric_id2data($el_id,"rubric_id")) $this->uFunc->error("uPage_elements_rubrics_arts_column_common 40");

        $el_pos=$this->uPage->define_new_el_pos($col_id,$site_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id($site_id);

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'rubrics_arts_column',$col_id,$el_id,$site_id);

        $conf=$this->get_el_settings($cols_els_id,$site_id);
        echo '{';
        echo '"col_number":"'.$conf->col_number.'",';
        echo '"art_number":"'.$conf->art_number.'",';
        echo '"img_col_number":"'.$conf->img_col_number.'",';
        echo '"show_avatars":"'.$conf->show_avatars.'",';
        echo '"show_short_text":"'.$conf->show_short_text.'",';
        echo '"show_title":"'.$conf->show_title.'",';
        echo $res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['col_number'])) {
            $col_number=(int)$_POST['col_number'];
            if($col_number<1) $col_number=3;
        }
        else $col_number=3;

        if(isset($_POST['art_number'])) {
            $art_number=(int)$_POST['art_number'];
            if($art_number<1) $art_number=3;
        }
        else $art_number=3;

        if(isset($_POST['img_col_number'])) {
            $img_col_number=(int)$_POST['img_col_number'];
            if($img_col_number<3) $img_col_number=3;
            if($img_col_number>9) $img_col_number=9;
        }
        else $img_col_number=3;

        if(isset($_POST['show_avatars'])) {
            if((int)$_POST['show_avatars']) $show_avatars=1;
            else $show_avatars=0;
        }
        else $show_avatars=1;

        if(isset($_POST['show_short_text'])) {
            if((int)$_POST['show_short_text']) $show_short_text=1;
            else $show_short_text=0;
        }
        else $show_short_text=0;

        if(isset($_POST['show_title'])) {
            if((int)$_POST['show_title']) $show_title=1;
            else $show_title=0;
        }
        else $show_title=0;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_urubrics_arts_column 
                SET 
                col_number=:col_number,
                art_number=:art_number,
                img_col_number=:img_col_number,
                show_avatars=:show_avatars,
                show_short_text=:show_short_text,
                show_title=:show_title
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_number', $col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':art_number', $art_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':img_col_number', $img_col_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatars', $show_avatars,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_short_text', $show_short_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_column_common 50'/*.$e->getMessage()*/);}

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
                catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_column_common 60'/*.$e->getMessage()*/);}

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
