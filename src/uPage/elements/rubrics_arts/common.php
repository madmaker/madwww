<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

require_once "uRubrics/classes/common.php";

class rubrics_arts{
    private $uSes;
    private $uRubrics;
    private $uPage;
    private $uFunc;
    private $uCore;

    private function create_default_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_urubrics_arts (
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_common 10'/*.$e->getMessage()*/);}
    }
    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_urubrics_arts 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_common 20'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'rubrics_arts',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_urubrics_arts (
            cols_els_id, 
            site_id, 
            page_number,
            dots_style
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :page_number,
            :dots_style          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_number', $el_settings->page_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $el_settings->dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_common 30'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function get_rubric_name($el_id,$site_id=site_id) {
        $qr=$this->uRubrics->rubric_id2data($el_id,"rubric_name",$site_id);

        if($qr) return uString::sql2text($qr->rubric_name,1);
        return "";
    }
    public function get_texts_of_rubric($el_id,$page_number,$site_id=site_id) {
        return $this->uRubrics->get_texts_of_rubric($el_id,$page_number,0,$site_id);
    }
    public function get_pages_of_rubric($el_id,$page_number,$site_id=site_id) {
        return $this->uRubrics->get_pages_of_rubric($el_id,$page_number,0,$site_id);
    }

    public function load_el_content($el_id,$cols_els_id,$return=0,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id);

        require_once 'uEditor/inc/page_avatar.php';
        $text_avatar = new \uEditor_page_avatar($this->uCore);
        require_once 'uPage/inc/page_preview_img.php';
        $page_avatar=new \page_preview_img($this->uCore);

        $rubric_name=$this->get_rubric_name($el_id,$site_id);

        $stm_pages=$this->get_pages_of_rubric($el_id,$conf->page_number,$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $page_ar=$stm_pages->fetchAll(PDO::FETCH_OBJ);
        $stm_texts=$this->get_texts_of_rubric($el_id,$conf->page_number,$site_id);
        /** @noinspection PhpUndefinedMethodInspection */
        $text_ar=$stm_texts->fetchAll(PDO::FETCH_OBJ);

        $common_ar=array_merge($page_ar,$text_ar);

        $common_ar_count=count($common_ar);

        $conf->dots_style=(int)$conf->dots_style;
        $dots_style=$conf->dots_style;
        if(!$dots_style) {
            if($site_style_obj=$this->uPage->get_site_style("sliders_dots_style",$site_id)) {
                $dots_style=(int)$site_style_obj->sliders_dots_style;
            }
            else $dots_style=4;
        }

        ob_start();?>
        <div class="uRubrics_arts_slider">
            <div id="uPage_el_urubrics_arts_<?=$cols_els_id?>" class="owl-carousel dots_style_<?=$dots_style?> <?=$conf->dots_style?"":"dots_style_0"?> uPage_el_urubrics_arts ">
            <?for($i=0;$i<$common_ar_count;$i++) {
                $page=$common_ar[$i];
                if(isset($page->page_alias)) $href=strlen($page->page_alias)?(u_sroot.$page->page_alias):(u_sroot.'page/'.$page->page_name);
                else $href = strlen($page->page_url) ? (u_sroot . $page->page_url) : (u_sroot . 'uPage/' . $page->page_id);

                if(isset($page->page_avatar_time)) {//TEXT avatar
                    $preview_img_timestamp=(int)$page->page_avatar_time;
                        if ($preview_img_timestamp) $preview_img=$text_avatar->get_avatar(350,$page->page_id);
                        else $preview_img="";
                }
                else {//PAGE avatar
                    $preview_img_timestamp=(int)$page->preview_img_timestamp;
                    if ($preview_img_timestamp) $preview_img=$page_avatar->get_img_url(350,$page->page_id);
                    else $preview_img="";
                }

                if(property_exists($page,"preview_text")) $preview_text=$page->preview_text;
                else $preview_text=uString::removeHTML(uString::sql2text($page->page_short_text,1));
                ?>

                <div class="item">
                    <?
                    if($preview_img_timestamp) {?>
                        <a class="avatar_container" href="<?=$href?>">
                            <img alt="<?=uString::sql2text($page->page_title,1)?>" class="avatar" src="<?=$preview_img?>" />
                        </a>
                    <?}?>
                    <a href="<?=$href?>" class="item_content"><?=$preview_text?></a>
                </div>
            <?}?>
            </div>
        </div>

        <script type="text/javascript">
            <?=!$this->uSes->access(7)?'$(document).ready(function() {':''?>
            $("#uPage_el_urubrics_arts_<?=$cols_els_id?>").owlCarousel({
                autoplayTimeout:3000,
                autoplay:true,
                autoplayHoverPause:true,
                slideBy:"page",
                navText:['<span class="icon-left-open"></span>','<span class="icon-right-open"></span>'],
                loop:true,
                merge:false,
                mergeFit:false,
                autoWidth:false,
                margin:10,
                responsiveClass:true,
                responsive: {
                    0: {
                        items: 1,
                        nav: 0,
                        dots: 1
                    },
                    768: {
                        items: 1,
                        nav: 1,
                        dots: 1
                    },
                    992: {
                        items: 1,
                        nav: 1,
                        dots: 1
                    },
                    1200: {
                        items: 1,
                        nav: 1,
                        dots: 1
                    },
                    1920: {
                        items: 1,
                        nav: 1,
                        dots: 1
                    }
                }
            });
            <?=!$this->uSes->access(7)?'});':''?>
        </script>

        <?$cnt=ob_get_contents();
        ob_end_clean();

        $result_ar=(object)array(
            "cols_els_id"=>$cols_els_id,
            "page_number"=>$conf->page_number,
            "dots_style"=>$conf->dots_style,
            "cnt"=>$cnt,
            "rubric_name"=>$rubric_name,
            "status"=>"done",
        );

        if($return) return $result_ar;
        else echo json_encode($result_ar);

        return 0;
    }

    public function attach_el2col($el_id,$col_id,$site_id=site_id) {
        if(!$this->uRubrics->rubric_id2data($el_id,"rubric_id")) $this->uFunc->error("uPage_elements_rubrics_arts_common  40");

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'rubrics_arts',$col_id,$el_id);

        $conf=$this->get_el_settings($cols_els_id,$site_id);
        echo '{';
        echo '"page_number":"'.$conf->page_number.'",';
        echo '"dots_style":"'.$conf->dots_style.'",';
        echo $res[0].'}';
        exit;
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['page_number'])) {
            $page_number=(int)$_POST['page_number'];
            if($page_number<1||$page_number>20) $page_number=20;
        }
        else $page_number=20;

        if(isset($_POST['dots_style'])) {
            $dots_style=(int)$_POST['dots_style'];
            if($dots_style<1||$dots_style>16) $dots_style=0;
        }
        else $dots_style=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_urubrics_arts 
                SET 
                page_number=:page_number,
                dots_style=:dots_style
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_number', $page_number,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':dots_style', $dots_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_common 50'/*.$e->getMessage()*/);}

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
                catch(PDOException $e) {$this->uFunc->error('uPage_elements_rubrics_arts_common 60'/*.$e->getMessage()*/);}

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
        $this->uSes=new \uSes($this->uCore);
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
        $this->uRubrics=new \uRubrics\common($this->uCore);
    }
}
