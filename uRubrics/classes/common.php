<?php
namespace uRubrics;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";

class common {
    private $uFunc;
    private $uCore;
    public function text($str) {
        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
    }

    public function get_texts_of_rubric($rubric_id,$limit=20,$start_point=0,$site_id=site_id) {
        if($limit===0) $limit=1000;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            u235_pages_html.page_id,
            page_short_text,
            page_title,
            page_name,
            page_alias,
            page_avatar_time,
            show_avatar,
            page_timestamp
            FROM 
            u235_pages_html
            JOIN
            u235_urubrics_pages
            ON
            u235_urubrics_pages.page_id=u235_pages_html.page_id AND 
            u235_urubrics_pages.site_id=u235_pages_html.site_id
            WHERE
            rubric_id=:rubric_id AND
            `mod`=0 AND
            u235_pages_html.site_id=:site_id
            ORDER BY
            u235_pages_html.page_timestamp DESC
            LIMIT $start_point, $limit
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uRubrics_common 10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_pages_of_rubric($rubric_id,$limit=20,$start_point=0,$site_id=site_id) {
        if($limit===0) $limit=1000;
        try {
            $this->uFunc->pdo("pages");
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            `madmakers_uPage`.u235_pages.page_id,
            `madmakers_uPage`.u235_pages.preview_text,
            `madmakers_uPage`.u235_pages.page_timestamp,
            page_title,
            `madmakers_uPage`.u235_pages.page_url,
            `madmakers_uPage`.u235_pages.preview_img_timestamp
            FROM 
            `madmakers_uPage`.u235_pages
            JOIN
            `madmakers_pages`.u235_urubrics_pages
            ON
            `madmakers_pages`.u235_urubrics_pages.page_id=`madmakers_uPage`.u235_pages.page_id AND 
            `madmakers_pages`.u235_urubrics_pages.site_id=`madmakers_uPage`.u235_pages.site_id
            WHERE
            rubric_id=:rubric_id AND
            `mod`=1 AND
            `madmakers_uPage`.u235_pages.site_id=:site_id
            ORDER BY
            `madmakers_uPage`.u235_pages.page_timestamp DESC
            LIMIT $start_point, $limit
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uRubrics_common 20'.$e->getMessage());}
        return 0;
    }
    public function get_number_of_pages_of_rubric($rubric_id,$site_id=site_id) {
        try {
            $this->uFunc->pdo("pages");
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            COUNT(`madmakers_uPage`.u235_pages.page_id) AS number
            FROM 
            `madmakers_uPage`.u235_pages
            JOIN
            `madmakers_pages`.u235_urubrics_pages
            ON
            `madmakers_pages`.u235_urubrics_pages.page_id=`madmakers_uPage`.u235_pages.page_id AND 
            `madmakers_pages`.u235_urubrics_pages.site_id=`madmakers_uPage`.u235_pages.site_id
            WHERE
            rubric_id=:rubric_id AND
            `mod`=1 AND
            `madmakers_uPage`.u235_pages.site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ)->number;
        }
        catch(PDOException $e) {$this->uFunc->error('1584658344'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_site_rubrics($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
            rubric_id, 
            rubric_name,
            pages_limit,
            pages_limit_on_news_list,
            display_style
            FROM 
            u235_urubrics_list 
            WHERE 
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('uRubrics_common 30'/*.$e->getMessage()*/);}
        return 0;
    }
    public function rubric_id2data($rubric_id,$q_select="rubric_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT 
            ".$q_select." 
            FROM 
            u235_urubrics_list
            WHERE
            rubric_id=:rubric_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rubric_id', $rubric_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('uRubrics_common 40'/*.$e->getMessage()*/);}
        return 0;
    }

    public function print_rubric_columns($common_ar,$col_number,$conf=0) {
        if(!$conf) {
            $conf=new \stdClass();
            $conf->img_col_number=3;
            $conf->show_avatars=1;
            $conf->show_short_text=1;
            $conf->show_title=1;
        }
        require_once 'uEditor/inc/page_avatar.php';
        require_once "uPage/inc/page_preview_img.php";
        if(!isset($this->text_avatar)) $this->text_avatar=new \uEditor_page_avatar($this->uCore);
        if(!isset($this->page_preview_img)) $this->page_preview_img=new \page_preview_img($this->uCore);
        $common_ar_count=count($common_ar);

        $col_width=12/$col_number;
        ?>
        <div class="row">
            <?for($col_counter=1,$col_counter_sm=1,$i=0;$i<$common_ar_count;$i++,$col_counter++,$col_counter_sm++) {
                $page = $common_ar[$i];

                if (isset($page->page_alias)) $href = strlen($page->page_alias) ? (u_sroot . $page->page_alias) : (u_sroot . 'page/' . $page->page_name);
                else $href = strlen($page->page_url) ? (u_sroot . $page->page_url) : (u_sroot . 'uPage/' . $page->page_id);

                $preview_img = 0;
                if($conf->show_avatars) {
                    if (isset($page->page_avatar_time)) {//TEXT avatar
                        if ((int)$page->page_avatar_time) $preview_img = $this->text_avatar->get_avatar(350, $page->page_id);
                    } else {//PAGE avatar
                        if ((int)$page->preview_img_timestamp) $preview_img = $this->page_preview_img->get_img_url(350, $page->page_id);
                    }
                }

                $preview_text=0;
                if($conf->show_short_text) {
                    if (key_exists("preview_text", $page)) $preview_text = $page->preview_text;
                    else $preview_text = uString::removeHTML(uString::sql2text($page->page_short_text, 1));
                }?>
                <div class="col-md-<?=$col_width?> col-lg-<?= $col_width ?> col-sm-<?= $col_number > 1 ? 6 : $col_width ?> col-xs-12 item">
                    <? if ($preview_img) { ?>
                        <div class="image_column col-md-<?=$col_number>2?12:4?>">
                            <? if ($preview_img) { ?>
                                <a href="<?= $href ?>"><img src="<?= $preview_img ?>" class="img-responsive" style="margin: 0 auto"
                                                            alt="<?= htmlspecialchars(uString::sql2text($page->page_title, 1)) ?>"></a>
                            <? } ?>
                        </div>
                    <? } ?>
                    <div class="content_column col-md-<?=$preview_img?($col_number>2?12:8):12?>">
                        <?if($conf->show_title) {?>
                        <h3 class="item_header"><a
                                href="<?= $href ?>"><?= uString::sql2text($page->page_title, 1) ?></a></h3>
                        <?}?>
                        <div class="page_timestamp"><span><?= date("d.m.Y", $page->page_timestamp) ?></span></div>
                        <?if($preview_text) {?>
                        <div class="text">
                            <?if(!$conf->show_title) {?><a href="<?= $href ?>"><?}?><?= $preview_text ?><?if(!$conf->show_title) {?></a><?}?>
                        </div>
                        <?}?>
                    </div>
                </div>

                <? if ($col_number > 1) {
                    if ($col_counter_sm >= 2) {
                        $col_counter_sm = 0; ?>
                        <div class="sm_screen_row_divider hidden-md hidden-lg col-sm-12 hidden-xs"></div>
                        <?
                    }

                    if ($col_counter >= $col_number ) {
                        $col_counter = 0; ?>
                        <div class="md_screen_row_divider col-md-12 col-lg-12 hidden-sm hidden-xs"></div>
                        <?
                    }
                }
            }?>
        </div>
    <?}
    public function print_rubric_tiles($common_ar,$col_number) {
        require_once 'uEditor/inc/page_avatar.php';
        require_once "uPage/inc/page_preview_img.php";
        if(!isset($this->text_avatar)) $this->text_avatar=new \uEditor_page_avatar($this->uCore);
        if(!isset($this->page_preview_img)) $this->page_preview_img=new \page_preview_img($this->uCore);
        $common_ar_count=count($common_ar);

        $col_width=12/$col_number;

        if($col_number===1) $img_width=1200;
        elseif($col_number===2) $img_width=700;
        elseif($col_number===3) $img_width=500;
        elseif($col_number===4) $img_width=300;
        ?>

        <div class="uPage_rubrics_tiles container-fluid">
            <div class="row">
                <?
                for($col_counter=1,$col_counter_sm=1,$i=0;$i<$common_ar_count;$i++,$col_counter++,$col_counter_sm++) {
                    $page = $common_ar[$i];
                    if (isset($page->page_alias)) $href = strlen($page->page_alias) ? (u_sroot . $page->page_alias) : (u_sroot . 'page/' . $page->page_name);
                    else $href = strlen($page->page_url) ? (u_sroot . $page->page_url) : (u_sroot . 'uPage/' . $page->page_id);

                    $preview_img = 0;
                    if (isset($page->page_avatar_time)) {//TEXT avatar
                        if ((int)$page->page_avatar_time) $preview_img = $this->text_avatar->get_avatar($img_width, $page->page_id);
                    } else {//PAGE avatar
                        if ((int)$page->preview_img_timestamp) $preview_img = $this->page_preview_img->get_img_url($img_width, $page->page_id);
                    }

                    if (isset($page->preview_text)) $preview_text = $page->preview_text;
                    else $preview_text = uString::removeHTML(uString::sql2text($page->page_short_text, 1));?>
                    <style type="text/css">
                        #uPage_rubrics_tile_<?=$page->page_id?> {
                        <?=strlen($preview_img)?('background-image:url("'.$preview_img.'");'):""?>
                        }
                    </style>


                    <div id="uPage_rubrics_tile_<?=$page->page_id?>" class="col-md-<?=$col_width?> col-lg-<?=$col_width?> col-sm-<?=$col_number>1?6:$col_width?> col-xs-12 uPage_rubrics_tile">
                        <div class="tile_content">
                            <div class="tile_wrapper">
                                <div class="tile_content_container">
                                    <p class="more_btn"><a href="<?=$href?>"><span class="icon-right"></span></a></p>
                                    <p class="page_date"><?=date('d.m.Y',$page->page_timestamp)?></p>
                                    <p class="page_prev_text"><?=$preview_text?></p>
                                </div>
                            </div>
                        </div>
                        <h4 class="page_name"><a href="<?=$href?>"><?=uString::sql2text($page->page_title, 1)?></a></h4>
                    </div>

                    <?if($col_number>1) {
                        if($col_counter_sm >= 2) {
                            $col_counter_sm= 0;?>
                            <div class="sm_screen_row_divider hidden-md hidden-lg col-sm-12 hidden-xs"></div>
                        <?}

                        if($col_counter >= $col_number) {
                            $col_counter = 0;?>
                            <div class="md_screen_row_divider col-md-12 col-lg-12 hidden-sm hidden-xs"></div>
                        <?}
                    }
                }?>
            </div>
        </div>
    <?}

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uFunc=new uFunc($this->uCore);
    }
}
