<?php
require_once 'uPage/inc/common.php';
require_once 'uDrive/classes/common.php';

class uPage_setup_uPage_page {
    public $uFunc;
    public $uSes;
    public $uPage;
    public $uMenu;
    public $uDrive;
    public $site_font_color;
    private $site_font_color_inverse;
    private $site_primary_over_font_color_inverse;
    private $site_primary_color_highlight_inverse;
    private $site_primary_color_inverse;
    private $site_primary_over_font_color;
    private $sliders_dots_style;
    private $site_primary_color_highlight;
    private $site_primary_color;
    private $site_css;
    private $uCore,$page_id,$q_uPage_rows,$page, /** @noinspection PhpUnusedPrivateFieldInspection */
        $setup_uEvents,
    $uEvents_calendar_exists;
    private function increase_views_counter($page_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_pages
            SET
            views_counter=views_counter+1
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }
    private function get_page_info() {
        if(!$this->page=$this->uPage->page_id2data($this->page_id,"
        page_id,
        page_title,
        preview_text,
        preview_img_timestamp,
        page_timestamp,
        page_timestamp_show,
        show_title,
        page_url,
        page_description,
        page_keywords,
        page_width,
        page_css,
        uDrive_folder_id,
        text_folder_id
        ")) $this->uFunc->error("uPage setup_uPage_page 10");
        $this->page->page_css=uString::sql2text($this->page->page_css);
        $this->page->page_title=uString::sql2text($this->page->page_title,1);
        $this->page->preview_img_timestamp=(int)$this->page->preview_img_timestamp;
        $this->page->page_timestamp=(int)$this->page->page_timestamp;
        $this->page->page_timestamp_show=(int)$this->page->page_timestamp_show;

        $this->page->uDrive_folder_id=$this->uPage->define_page_uDrive_folder_id($this->page_id,$this->page->page_title,$this->page->uDrive_folder_id);
        $this->page->text_folder_id=$this->uPage->define_text_folder_id($this->page_id,$this->page->page_title,$this->page->text_folder_id);
    }
    private function css_str2css($css_str,$site_style_obj) {
        if(uString::isHexColor($site_style_obj->site_primary_color)&&!uString::isMadColor($site_style_obj->site_primary_color)) $site_style_obj->site_primary_color="#".$site_style_obj->site_primary_color;
        $site_style_obj->site_primary_color_inverse=$this->uFunc->color_inverse($site_style_obj->site_primary_color);

        if(uString::isHexColor($site_style_obj->site_primary_color_highlight)&&!uString::isMadColor($site_style_obj->site_primary_color_highlight)) $site_style_obj->site_primary_color_highlight="#".$site_style_obj->site_primary_color_highlight;
        $site_style_obj->site_primary_color_highlight_inverse=$this->uFunc->color_inverse($site_style_obj->site_primary_color_highlight);

        if(uString::isHexColor($site_style_obj->site_primary_over_font_color)&&!uString::isMadColor($site_style_obj->site_primary_over_font_color)) $site_style_obj->site_primary_over_font_color="#".$site_style_obj->site_primary_over_font_color;
        $site_style_obj->site_primary_over_font_color_inverse=$this->uFunc->color_inverse($site_style_obj->site_primary_over_font_color);

        if(uString::isHexColor($site_style_obj->site_font_color)&&!uString::isMadColor($site_style_obj->site_font_color)) $site_style_obj->site_font_color="#".$site_style_obj->site_font_color;
        $site_style_obj->site_font_color_inverse=$this->uFunc->color_inverse($site_style_obj->site_font_color);

    // site_primary_color
    // site_primary_color_highlight
    // site_primary_color_inverse
    // site_primary_color_highlight_inverse
    // site_primary_over_font_color
    // site_primary_over_font_color_inverse
    // site_font_color
    // site_font_color_inverse
    $css_str=str_replace("@mad_primary_color_highlight_inverse",$site_style_obj->site_primary_color_highlight_inverse,$css_str);
    $css_str=str_replace("@mad_primary_color_highlight",$site_style_obj->site_primary_color_highlight,$css_str);
    $css_str=str_replace("@mad_primary_color_inverse",$site_style_obj->site_primary_color_inverse,$css_str);
    $css_str=str_replace("@mad_primary_color",$site_style_obj->site_primary_color,$css_str);
    $css_str=str_replace("@mad_primary_over_font_color_inverse",$site_style_obj->site_primary_over_font_color_inverse,$css_str);
    $css_str=str_replace("@mad_primary_over_font_color",$site_style_obj->site_primary_over_font_color,$css_str);
    $css_str=str_replace("@mad_site_font_color_inverse",$site_style_obj->site_font_color_inverse,$css_str);
    $css_str=str_replace("@mad_site_font_color",$site_style_obj->site_font_color,$css_str);
    $css_str=str_replace("##","#",$css_str);
    // if(uString.isHexColor(css_str)) css_str='#'+css_str;

    return $css_str;
    }
    public function cache_css($block_type,$block_id,$page_id) {
        $dir='uPage/cache/'.site_id.'/'.$page_id;
        if($block_type=='page') {
            $css_field=$sql_field='page_css';
            $sql_table='u235_pages';
            $sql_key='page_id';
            $filename='page_'.$block_id.'.css';
        }
        elseif($block_type=='row') {
            $css_field="row_css";
            $sql_field='
            row_css,
            row_class,
            row_background_color,
            row_background_img,
            row_background_stretch,
            row_background_repeat_x,
            row_background_repeat_y,
            row_background_position,
            row_background_parallax,
            
            row_margin_top_xlg,
            row_margin_top_lg,
            row_margin_top_md,
            row_margin_top_sm,
            row_margin_top_xs,
            
            row_margin_bottom_xlg,
            row_margin_bottom_lg,
            row_margin_bottom_md,
            row_margin_bottom_sm,
            row_margin_bottom_xs,
            
            row_padding_top_xlg,
            row_padding_top_lg,
            row_padding_top_md,
            row_padding_top_sm,
            row_padding_top_xs,
            
            row_padding_bottom_xlg,
            row_padding_bottom_lg,
            row_padding_bottom_md,
            row_padding_bottom_sm,
            row_padding_bottom_xs,
            
            row_min_height_xlg,
            row_min_height_lg,
            row_min_height_md,
            row_min_height_sm,
            row_min_height_xs,
            row_font_color,
            row_link_color,
            row_hoverlink_color,
            row_font_size,
            row_content_centered';
            $sql_table='u235_rows';
            $sql_key='row_id';
            $filename='row_'.$block_id.'.css';
        }
        elseif($block_type=='col') {
            $css_field=$sql_field='col_css';
            $sql_table='u235_cols';
            $sql_key='col_id';
            $filename='col_'.$block_id.'.css';
        }
        elseif($block_type=='el') {
            $css_field='el_css';
            $sql_field='el_css,
            el_font_color,
            el_link_color,
            el_hoverlink_color';
            $sql_table='u235_cols_els';
            $sql_key='cols_els_id';
            $filename='el_'.$block_id.'.css';
        }
        else $this->uFunc->error("uPage setup_uPage_page 70");

        /** @noinspection PhpUndefinedVariableInspection */
        if(!file_exists($dir.'/'.$filename)) {
            if(!file_exists($dir)) mkdir($dir,0755,true);
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                /** @noinspection PhpUndefinedVariableInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                ".$sql_field."
                FROM
                ".$sql_table."
                WHERE
                ".$sql_key."=:".$sql_key." AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':'.$sql_key, $block_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uPage setup_uPage_page 80'.$e->getMessage());}
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage setup_uPage_page 90");

            $css="";
            if($block_type=="row") {
                $qr->row_background_repeat_x=(int)$qr->row_background_repeat_x;
                $qr->row_background_repeat_y=(int)$qr->row_background_repeat_y;

                if($qr->row_background_repeat_x&&$qr->row_background_repeat_y) $backgroundRepeat="repeat";
                else if($qr->row_background_repeat_x&&!$qr->row_background_repeat_y) $backgroundRepeat="repeat-x";
                else if(!$qr->row_background_repeat_x&&$qr->row_background_repeat_y) $backgroundRepeat="repeat-y";
                else $backgroundRepeat="no-repeat";

                $qr->row_background_position=(int)$qr->row_background_position;
                $qr->row_background_parallax=(int)$qr->row_background_parallax;

                $qr->row_margin_top_xlg=(int)$qr->row_margin_top_xlg;
                $qr->row_margin_top_lg=(int)$qr->row_margin_top_lg;
                $qr->row_margin_top_md=(int)$qr->row_margin_top_md;
                $qr->row_margin_top_sm=(int)$qr->row_margin_top_sm;
                $qr->row_margin_top_xs=(int)$qr->row_margin_top_xs;

                $qr->row_margin_bottom_xlg=(int)$qr->row_margin_bottom_xlg;
                $qr->row_margin_bottom_lg=(int)$qr->row_margin_bottom_lg;
                $qr->row_margin_bottom_md=(int)$qr->row_margin_bottom_md;
                $qr->row_margin_bottom_sm=(int)$qr->row_margin_bottom_sm;
                $qr->row_margin_bottom_xs=(int)$qr->row_margin_bottom_xs;

                $qr->row_padding_top_xlg=(int)$qr->row_padding_top_xlg;
                $qr->row_padding_top_lg=(int)$qr->row_padding_top_lg;
                $qr->row_padding_top_md=(int)$qr->row_padding_top_md;
                $qr->row_padding_top_sm=(int)$qr->row_padding_top_sm;
                $qr->row_padding_top_xs=(int)$qr->row_padding_top_xs;

                $qr->row_padding_bottom_xlg=(int)$qr->row_padding_bottom_xlg;
                $qr->row_padding_bottom_lg=(int)$qr->row_padding_bottom_lg;
                $qr->row_padding_bottom_md=(int)$qr->row_padding_bottom_md;
                $qr->row_padding_bottom_sm=(int)$qr->row_padding_bottom_sm;
                $qr->row_padding_bottom_xs=(int)$qr->row_padding_bottom_xs;

                $qr->row_min_height_xlg=(int)$qr->row_min_height_xlg;
                $qr->row_min_height_lg=(int)$qr->row_min_height_lg;
                $qr->row_min_height_md=(int)$qr->row_min_height_md;
                $qr->row_min_height_sm=(int)$qr->row_min_height_sm;
                $qr->row_min_height_xs=(int)$qr->row_min_height_xs;

                if($qr->row_background_position==1) $backgroundPosition="background-position: left;";
                else if($qr->row_background_position==2) $backgroundPosition="background-position-x: center;";
                else if($qr->row_background_position==3) $backgroundPosition="background-position: right;";

                else if($qr->row_background_position==4) $backgroundPosition="background-position-y: center;";
                else if($qr->row_background_position==5) $backgroundPosition="background-position: top;";
                else if($qr->row_background_position==6) $backgroundPosition="background-position: bottom;";

                else if($qr->row_background_position==7) $backgroundPosition="background-position: left top;";
                else if($qr->row_background_position==8) $backgroundPosition="background-position: center top;";
                else if($qr->row_background_position==9) $backgroundPosition="background-position: right top;";

                else if($qr->row_background_position==10) $backgroundPosition="background-position: left center;";
                else if($qr->row_background_position==11) $backgroundPosition="background-position: center center;";
                else if($qr->row_background_position==12) $backgroundPosition="background-position: right center;";

                else if($qr->row_background_position==13) $backgroundPosition="background-position: left bottom;";
                else if($qr->row_background_position==14) $backgroundPosition="background-position: center bottom;";
                else if($qr->row_background_position==15) $backgroundPosition="background-position: right bottom;";

                else $backgroundPosition="";


                $css.="#uPage_row_".$block_id." {";
                if(strlen($qr->row_background_color)) $css.='background-color:#'.$qr->row_background_color.';';
                if(strlen($qr->row_background_img)) $css.='background-image:url("'.$qr->row_background_img.'");';
                if($qr->row_background_stretch) $css.='background-size: cover;';
                $css.='background-repeat: '.$backgroundRepeat.';';
                $css.=$backgroundPosition;
                if(strlen($qr->row_font_color)) $css.='color:#'.$qr->row_font_color.';';
                if($qr->row_font_size) $css.='font-size:'.$qr->row_font_size.'em;';
                $css.="}
                #uPage_row_".$block_id." a:visited,
                #uPage_row_".$block_id." a {";
                if(strlen($qr->row_link_color)) $css.='color:#'.$qr->row_link_color.';';
                $css.="}
                #uPage_row_".$block_id." a:focus,
                #uPage_row_".$block_id." a.active,
                #uPage_row_".$block_id." a:hover {";
                if(strlen($qr->row_hoverlink_color)) $css.='color:#'.$qr->row_hoverlink_color.';';
                $css.="}";

                if($qr->row_background_parallax) {
                    $css.="@media(min-width:1200px) {
                        #uPage_row_" . $block_id . " {background-attachment:fixed;}
                    }";
                }

                $css .= "               
                @media (max-width:767px) {
                #uPage_row_" . $block_id . " {";
                    if($qr->row_margin_top_xs) $css.='margin-top: '.$qr->row_margin_top_xs.'px;';
                    if($qr->row_padding_top_xs) $css.='padding-top: '.$qr->row_padding_top_xs.'px;';

                    if($qr->row_margin_bottom_xs) $css.='margin-bottom: '.$qr->row_margin_bottom_xs.'px;';
                    if($qr->row_padding_bottom_xs) $css.='padding-bottom: '.$qr->row_padding_bottom_xs.'px;';

                    if($qr->row_min_height_xs) $css.='min-height: '.$qr->row_min_height_xs.'px;';
                    $css .= "}
                }
                    
                    @media(min-width:768px) {
                #uPage_row_" . $block_id . " {";
                    if($qr->row_margin_top_sm) $css.='margin-top: '.$qr->row_margin_top_sm.'px;';
                    if($qr->row_padding_top_sm) $css.='padding-top: '.$qr->row_padding_top_sm.'px;';

                    if($qr->row_margin_bottom_sm) $css.='margin-bottom: '.$qr->row_margin_bottom_sm.'px;';
                    if($qr->row_padding_bottom_sm) $css.='padding-bottom: '.$qr->row_padding_bottom_sm.'px;';

                    if($qr->row_min_height_sm) $css.='min-height: '.$qr->row_min_height_sm.'px;';
                    $css .= "}
                    }
                    
                @media(min-width:992px)  {
                #uPage_row_" . $block_id . " {";
                    if($qr->row_margin_top_md) $css.='margin-top: '.$qr->row_margin_top_md.'px;';
                    if($qr->row_padding_top_md) $css.='padding-top: '.$qr->row_padding_top_md.'px;';

                    if($qr->row_margin_bottom_md) $css.='margin-bottom: '.$qr->row_margin_bottom_md.'px;';
                    if($qr->row_padding_bottom_md) $css.='padding-bottom: '.$qr->row_padding_bottom_md.'px;';

                    if($qr->row_min_height_md) $css.='min-height: '.$qr->row_min_height_md.'px;';
                    $css .= "}
                    }
                    
                @media(min-width:1200px) {
                #uPage_row_" . $block_id . " {";
                    if($qr->row_margin_top_lg) $css.='margin-top: '.$qr->row_margin_top_lg.'px;';
                    if($qr->row_padding_top_lg) $css.='padding-top: '.$qr->row_padding_top_lg.'px;';

                    if($qr->row_margin_bottom_lg) $css.='margin-bottom: '.$qr->row_margin_bottom_lg.'px;';
                    if($qr->row_padding_bottom_lg) $css.='padding-bottom: '.$qr->row_padding_bottom_lg.'px;';

                    if($qr->row_min_height_lg) $css.='min-height: '.$qr->row_min_height_lg.'px;';
                    $css .= "}
                    }
                    
                    @media(min-width:1600px) {
                    #uPage_row_" . $block_id . " {";
                        if($qr->row_margin_top_xlg) $css.='margin-top: '.$qr->row_margin_top_xlg.'px;';
                        if($qr->row_padding_top_xlg) $css.='padding-top: '.$qr->row_padding_top_xlg.'px;';

                        if($qr->row_margin_bottom_xlg) $css.='margin-bottom: '.$qr->row_margin_bottom_xlg.'px;';
                        if($qr->row_padding_bottom_xlg) $css.='padding-bottom: '.$qr->row_padding_bottom_xlg.'px;';

                        if($qr->row_min_height_xlg) $css.='min-height: '.$qr->row_min_height_xlg.'px;';
                    $css .= "}
                }
                
                ";
            }
            elseif($block_type=="el") {
                $css.="#uPage_el_".$block_id." {";
                if(strlen($qr->el_font_color)) $css.='color:#'.$qr->el_font_color.';';
                $css.="}
                #uPage_el_".$block_id." a:visited:not(.btn),
                #uPage_el_".$block_id." a:not(.btn) {";
                if(strlen($qr->el_link_color)) $css.='color:#'.$qr->el_link_color.';';
                $css.="}
                #uPage_el_".$block_id." a:focus:not(.btn),
                #uPage_el_".$block_id." a.active:not(.btn),
                #uPage_el_".$block_id." a:hover:not(.btn) {";
                if(strlen($qr->el_hoverlink_color)) $css.='color:#'.$qr->el_hoverlink_color.';';
                $css.="}";
            }

            /** @noinspection PhpUndefinedVariableInspection */
            $css.=uString::sql2text($qr->$css_field);
            $site_style_obj=$this->uPage->get_site_style();
            $css=$this->css_str2css($css,$site_style_obj);
            $css_file = fopen($dir.'/'.$filename, 'w');
            fwrite($css_file, $css);
            fclose($css_file);

        }
        return file_get_contents($dir.'/'.$filename);

    }

    private function cache_print_element($el, /** @noinspection PhpUnusedParameterInspection */
                                         $col_id, /** @noinspection PhpUnusedParameterInspection */
                                         $row_id) {
        if(!isset($this->uEvents_calendar_exists)) $this->uEvents_calendar_exists=false;

        /** @noinspection PhpUnusedLocalVariableInspection */
        $el_id=(int)$el->el_id;
        $cols_els_id=(int)$el->cols_els_id;
        $el_type=$el->el_type;
        ?>
        <div class="uPage_el" id="uPage_el_<?=$cols_els_id?>">
            <style type="text/css" id="uPage_el_<?=$cols_els_id?>_style"><?
                echo '<?
                include_once "uPage/inc/setup_uPage_page.php";

                if(!isset($setup_page_css)) $setup_page_css=new uPage_setup_uPage_page($this->uCore,'.$this->page_id.');
                echo $setup_page_css->cache_css("el",'.$cols_els_id.','.$this->page_id.');
            ?>';
                ?>
            </style>

            <?
            //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
            if($el_type === 'art') {
                include "uPage/elements/art/cache_print_el.php";
            }
            elseif($el_type === 'banner') {
                include "uPage/elements/banner/cache_print_el.php";
            }
            elseif($el_type=='bootstrap_carousel') include "uPage/elements/bootstrap_carousel/cache_print_el.php";
            elseif($el_type=='card') include "uPage/elements/card/cache_print_el.php";
            elseif($el_type=='code') include "uPage/elements/code/cache_print_el.php";
            elseif($el_type=='flip_book') include "uPage/elements/flip_book/cache_print_el.php";
            elseif($el_type=='form') include "uPage/elements/form/cache_print_el.php";
            elseif($el_type=='gallery') include "uPage/elements/gallery/cache_print_el.php";
            elseif($el_type=='gmap') include "uPage/elements/gmap/cache_print_el.php";
            elseif($el_type=='login_btn') include "uPage/elements/login_btn/cache_print_el.php";
            elseif($el_type=='menu') include "uPage/elements/menu/cache_print_el.php";
            elseif($el_type=='owl_carousel') include "uPage/elements/owl_carousel/cache_print_el.php";
            elseif($el_type=='page_filter') include "uPage/elements/page_filter/cache_print_el.php";
            elseif($el_type=='rubrics_arts') include "uPage/elements/rubrics_arts/cache_print_el.php";
            elseif($el_type=='rubrics_arts_column') include "uPage/elements/rubrics_arts_column/cache_print_el.php";
            elseif($el_type=='rubrics_tiles') include "uPage/elements/rubrics_tiles/cache_print_el.php";
            elseif($el_type=='search') include "uPage/elements/search/cache_print_el.php";
            elseif($el_type=='share') include "uPage/elements/share/cache_print_el.php";
            elseif($el_type=='spacer') include "uPage/elements/spacer/cache_print_el.php";
            elseif($el_type=='tabs') include "uPage/elements/tabs/cache_print_el.php";
            elseif($el_type=='ticker') include "uPage/elements/ticker/cache_print_el.php";
            elseif($el_type=='uCat_latest') include "uPage/elements/uCat_latest/cache_print_el.php";
            elseif($el_type=='uCat_latest_articles_slider') include "uPage/elements/uCat_latest_articles_slider/cache_print_el.php";
            elseif($el_type=='uCat_new_items') {
                $include_uCat_cart=1;
                include "uPage/elements/uCat_new_items/cache_print_el.php";
            }
            elseif($el_type=='uCat_popular') {
                $include_uCat_cart=1;
                include "uPage/elements/uCat_popular/cache_print_el.php";
            }
            elseif($el_type=='uCat_sale') {
                $include_uCat_cart=1;
                include "uPage/elements/uCat_sale/cache_print_el.php";
            }
            elseif($el_type=='uCat_search') {
                $include_uCat_cart=1;
                include "uPage/elements/uCat_search/cache_print_el.php";
            }
            elseif($el_type=='uCat_sects') include "uPage/elements/uCat_sects/cache_print_el.php";
            elseif($el_type=='uEditor_texts_top') include "uPage/elements/uEditor_texts_top/cache_print_el.php";
            elseif($el_type=='uEvents_calendar') include "uPage/elements/uEvents_calendar/cache_print_el.php";
            elseif($el_type=='uEvents_dates') include "uPage/elements/uEvents_dates/cache_print_el.php";
            elseif($el_type=='uEvents_list') include "uPage/elements/uEvents_list/cache_print_el.php";
            elseif($el_type=='uSubscr_news_form') include "uPage/elements/uSubscr_news_form/cache_print_el.php";
            ?>
        </div>
        <?
        if(isset($include_uCat_cart)) include_once 'uCat/dialogs/uCat_cart.php';

    }
    private function cache_print_col($col,$row_id) {
        $col_id=(int)$col->col_id;
        $lg_width=(int)$col->lg_width?("col-lg-".$col->lg_width):"hidden-lg";
        $md_width=(int)$col->md_width?("col-md-".$col->md_width):"hidden-md";
        $sm_width=(int)$col->sm_width?("col-sm-".$col->sm_width):"hidden-sm";
        $xs_width=(int)$col->xs_width?("col-xs-".$col->xs_width):"hidden-xs";
        ?>
        <div id="uPage_col_<?=$col_id?>" class="<?=$md_width?> <?=$lg_width?> <?=$sm_width?> <?=$xs_width?> uPage_col">
            <style type="text/css" id="uPage_col_<?=$col_id?>_style"><?
                echo '<?
                include_once "uPage/inc/setup_uPage_page.php";

                if(!isset($setup_page_css)) $setup_page_css=new uPage_setup_uPage_page($this->uCore,'.$this->page_id.');
                echo $setup_page_css->cache_css("col",'.$col_id.','.$this->page_id.');
            ?>';
                ?></style>
            <?$els=$this->uPage->get_all_col_els_of_col($col_id,"cols_els_id,el_type,el_pos,el_css,el_font_color,
        el_link_color,
        el_hoverlink_color,el_id");
            /** @noinspection PhpUndefinedMethodInspection */
            while($el=$els->fetch(PDO::FETCH_OBJ)) {
                $this->cache_print_element($el,$col_id,$row_id);
            }?>
        </div>
    <?}
    private function cache_print_row($row) {
        $row_id=$row->row_id;?>
        <div class="row uPage_row" id="uPage_row_<?=$row_id?>">
            <style type="text/css" id="uPage_row_<?=$row_id?>_style"><?
                echo '<?
                include_once "uPage/inc/setup_uPage_page.php";

                if(!isset($setup_page_css)) $setup_page_css=new uPage_setup_uPage_page($this->uCore,'.$this->page_id.');
                echo $setup_page_css->cache_css("row",'.$row_id.','.$this->page_id.');
            ?>';
                ?></style>
            <div class="uPage_row_content <?=$row->row_content_centered?" uPage_row_content_centered ":""?>">
            <?$cols=$this->uPage->get_all_row_cols($row_id,"col_id,col_pos,col_css,lg_width,md_width,sm_width,xs_width");
            /** @noinspection PhpUndefinedMethodInspection */;
            /** @noinspection PhpUndefinedMethodInspection */
            while($col=$cols->fetch(PDO::FETCH_OBJ)) {
                $this->cache_print_col($col,$row_id);
            }?>
            </div>
        </div>
    <?}

    private function cache_build_struct($dir) {
        $this->get_page_info();

        if(!file_exists($dir)) mkdir($dir,0755,true);
        $struct = fopen($dir.'/struct.php', 'w');

        $this->q_uPage_rows=$this->uPage->get_rows_of_page($this->page_id,"row_id,
        row_pos,
        row_class,
        row_css,
        row_background_color,
        row_background_img,
        row_background_stretch,
        row_background_repeat_x,
        row_background_repeat_y,
        row_background_position,
        row_background_parallax,
        
        row_margin_top_xlg,
        row_margin_top_lg,
        row_margin_top_md,
        row_margin_top_sm,
        row_margin_top_xs,
        
        row_margin_bottom_xlg,
        row_margin_bottom_lg,
        row_margin_bottom_md,
        row_margin_bottom_sm,
        row_margin_bottom_xs,
        
        row_padding_top_xlg,
        row_padding_top_lg,
        row_padding_top_md,
        row_padding_top_sm,
        row_padding_top_xs,
        
        row_padding_bottom_xlg,
        row_padding_bottom_lg,
        row_padding_bottom_md,
        row_padding_bottom_sm,
        row_padding_bottom_xs,
        
        row_min_height_xlg,
        row_min_height_lg,
        row_min_height_md,
        row_min_height_sm,
        row_min_height_xs,
        row_font_color,
        row_link_color,
        row_hoverlink_color,
        row_font_size,
        row_content_centered
        ");
        ob_start();
        $page_data=$this->uPage->page_id2data($this->page_id,"show_title,page_title,page_timestamp_show,page_timestamp");?>
        <div class="uPage_container" id="uPage_container_<?=$this->page_id?>">
            <?=(int)$page_data->show_title?('<h1 id="uPage_page_title_'.$this->page_id.'" class="page-header">'.uString::sql2text($page_data->page_title,1).'</h1>'):''?>
        <style type="text/css" <?/*id="uPage_page_style"*/?>><?
            echo '<?
                include_once "uPage/inc/setup_uPage_page.php";';

                if((int)$this->page->preview_img_timestamp) {
                    if (!isset($this->page_preview_img)) {
                        require_once "uPage/inc/page_preview_img.php";
                        $this->page_preview_img = new page_preview_img($this->uCore);
                    }
                    if($img=$this->page_preview_img->get_img_url(500,$this->page->page_id,$this->page->preview_img_timestamp)) {
                        echo 'if(!isset($this->uCore->page["head_html"])) $this->uCore->page["head_html"]="";
                        $this->uCore->page["head_html"].=\'
                        <meta property="og:image" content="'.$img.'">
                        <meta name="twitter:image:src" content="'.$img.'">\';';
                    }
                }

                echo 'if(!isset($setup_page_css)) $setup_page_css=new uPage_setup_uPage_page($this->uCore,'.$this->page_id.');
                echo $setup_page_css->cache_css("page",'.$this->page_id.','.$this->page_id.');
                
                $this->uCore->page["page_width"]='.$this->page->page_width.';
            ?>';
            ?></style>
        <?/** @noinspection PhpUndefinedMethodInspection */
        while($row=$this->q_uPage_rows->fetch(PDO::FETCH_OBJ)) {
            $this->cache_print_row($row);
        }?>
        </div>
        <?if((int)$page_data->page_timestamp_show) {?><div class="text-muted" id="uPage_content_page_timestamp"><?=date('d.m.Y H:i',$page_data->page_timestamp)?></div><?}?>

        <?fwrite($struct, ob_get_contents());
        fclose($struct);
        ob_end_clean();
    }
    public function cache_builder() {
        $this->increase_views_counter($this->page_id);
        $dir='uPage/cache/'.site_id.'/'.$this->page_id;
        if(!file_exists($dir.'/struct.php')) $this->cache_build_struct($dir);
        /** @noinspection PhpIncludeInspection */
        include $dir.'/struct.php';
    }

    public function admin_page_builder() {
        //site_primary_color
        //site_primary_color_inverse
        //site_primary_color_highlight
        //site_primary_color_highlight_inverse
        //site_primary_over_font_color
        //site_primary_over_font_color_inverse
        //site_font_color
        //site_font_color_inverse
        $this->get_page_info();
        $site_style_obj=$this->uPage->get_site_style();
        $this->site_css = $site_style_obj->site_css;
        $this->sliders_dots_style = $site_style_obj->sliders_dots_style;
        $this->site_primary_color=$site_style_obj->site_primary_color;
        $this->site_primary_color_inverse=$site_style_obj->site_primary_color_inverse;
        $this->site_primary_color_highlight=$site_style_obj->site_primary_color_highlight;
        $this->site_primary_color_highlight_inverse=$site_style_obj->site_primary_color_highlight_inverse;
        $this->site_primary_over_font_color=$site_style_obj->site_primary_over_font_color;
        $this->site_primary_over_font_color_inverse=$site_style_obj->site_primary_over_font_color_inverse;
        $this->site_font_color=$site_style_obj->site_font_color;
        $this->site_font_color_inverse=$site_style_obj->site_font_color_inverse;

        if($this->page->preview_img_timestamp) {
            require_once "uPage/inc/page_preview_img.php";
            $page_preview_img=new page_preview_img($this->uCore);
            $preview_img=$page_preview_img->get_img_url(500,$this->page_id,$this->page->preview_img_timestamp);
        }
        else $preview_img="";

        $this->uFunc->incJs(staticcontent_url."js/uPage/page_admin.min.js");
        ?>

        <script type="text/javascript">
            let uEvents_installed=<?=$this->uFunc->mod_installed('uEvents')?1:0?>;
            let uCat_installed=<?=$this->uFunc->mod_installed('uCat')?1:0?>;
            let gallery_installed=<?=$this->uFunc->mod_installed('gallery')?1:0?>;
            let uSubscr_installed=<?=$this->uFunc->mod_installed('uSubscr')?1:0?>;

            if (typeof uPage_setup_uPage === "undefined") uPage_setup_uPage={};
            uPage_setup_uPage.rows=[];
            uPage_setup_uPage.row_id2row_i=[];
            uPage_setup_uPage.rows_id2cols=[];
            uPage_setup_uPage.col_id2row_id=[];
            uPage_setup_uPage.col_id2col_i=[];
            uPage_setup_uPage.cols_id2els=[];
            uPage_setup_uPage.cols_els_id2el_i=[];
            uPage_setup_uPage.cols_els_id2col_id=[];

            uPage_setup_uPage.site_css=decodeURIComponent("<?=rawurlencode($this->site_css)?>");

            uPage_setup_uPage.site_primary_color=decodeURIComponent("<?=rawurlencode($this->site_primary_color)?>");
            uPage_setup_uPage.site_primary_color_inverse=decodeURIComponent("<?=rawurlencode($this->site_primary_color_inverse)?>");
            uPage_setup_uPage.site_primary_color_highlight=decodeURIComponent("<?=rawurlencode($this->site_primary_color_highlight)?>");
            uPage_setup_uPage.site_primary_color_highlight_inverse=decodeURIComponent("<?=rawurlencode($this->site_primary_color_highlight_inverse)?>");
            uPage_setup_uPage.site_primary_over_font_color=decodeURIComponent("<?=rawurlencode($this->site_primary_over_font_color)?>");
            uPage_setup_uPage.site_primary_over_font_color_inverse=decodeURIComponent("<?=rawurlencode($this->site_primary_over_font_color_inverse)?>");
            uPage_setup_uPage.site_font_color=decodeURIComponent("<?=rawurlencode($this->site_font_color)?>");
            uPage_setup_uPage.site_font_color_inverse=decodeURIComponent("<?=rawurlencode($this->site_font_color_inverse)?>");

            uPage_setup_uPage.sliders_dots_style=<?=$this->sliders_dots_style?>;

            uPage_setup_uPage.page_title=decodeURIComponent("<?=rawurlencode($this->page->page_title)?>");
            uPage_setup_uPage.preview_text=decodeURIComponent("<?=rawurlencode($this->page->preview_text)?>");
            uPage_setup_uPage.preview_img=decodeURIComponent("<?=rawurlencode($preview_img)?>");
            uPage_setup_uPage.page_timestamp=<?=$this->page->page_timestamp?>;
            uPage_setup_uPage.page_date="<?=date("d.m.Y",$this->page->page_timestamp)?>";
            uPage_setup_uPage.page_time="<?=date("H:s",$this->page->page_timestamp)?>";
            uPage_setup_uPage.page_timestamp_show=<?=$this->page->page_timestamp_show?>;
            uPage_setup_uPage.show_title=<?=$this->page->show_title?>;
            uPage_setup_uPage.page_url=decodeURIComponent("<?=rawurlencode($this->page->page_url)?>");
            uPage_setup_uPage.page_description=decodeURIComponent("<?=rawurlencode($this->page->page_description)?>");
            uPage_setup_uPage.page_keywords=decodeURIComponent("<?=rawurlencode($this->page->page_keywords)?>");
            uPage_setup_uPage.page_css=decodeURIComponent("<?=rawurlencode($this->page->page_css)?>");
            uPage_setup_uPage.page_width=<?=$this->page->page_width?>;
            uPage_setup_uPage.page_id="<?=$this->page->page_id?>";
            uPage_setup_uPage.uDrive_folder_id=<?=$this->page->uDrive_folder_id?>;
            if (typeof uEditor_pages_manager=== "undefined") uEditor_pages_manager={};
            uEditor_pages_manager.cur_folder_id=<?=$this->page->text_folder_id?>;

            <?
            $this->q_uPage_rows=$this->uPage->get_rows_of_page($this->page_id,"row_id,
            row_pos,
            row_class,
            row_css,
            row_background_color,
            row_background_img,
            row_background_stretch,
            row_background_repeat_x,
            row_background_repeat_y,
            row_background_position,
            row_background_parallax,
            
            row_margin_top_xlg,
            row_margin_top_lg,
            row_margin_top_md,
            row_margin_top_sm,
            row_margin_top_xs,
            
            row_margin_bottom_xlg,
            row_margin_bottom_lg,
            row_margin_bottom_md,
            row_margin_bottom_sm,
            row_margin_bottom_xs,
            
            row_padding_top_xlg,
            row_padding_top_lg,
            row_padding_top_md,
            row_padding_top_sm,
            row_padding_top_xs,
            
            row_padding_bottom_xlg,
            row_padding_bottom_lg,
            row_padding_bottom_md,
            row_padding_bottom_sm,
            row_padding_bottom_xs,
            
            row_min_height_xlg,
            row_min_height_lg,
            row_min_height_md,
            row_min_height_sm,
            row_min_height_xs,
            row_font_color,
            row_link_color,
            row_hoverlink_color,
            row_font_size,
            row_content_centered
            ");
            /** @noinspection PhpUndefinedMethodInspection */
            while($row=$this->q_uPage_rows->fetch(PDO::FETCH_OBJ)) {
                $this->uPage->build_row_js4page_builder($row);
            }?>
        </script>

        <div class="uPage_container uPage_container_<?=$this->page_id?>" id="uPage_container_<?=$this->page->page_id?>"></div>
        <div id="uDrive_my_drive_uploader_init"></div>
        <div id="uEditor_pages_init"></div>
    <?
        include_once 'uDrive/inc/my_drive_manager.php';
        include_once 'uEditor/inc/pages_manager.php';
//        if(isset($include_uCat_cart)) include_once 'uCat/dialogs/uCat_cart.php';
        if($this->uFunc->mod_installed("uCat")) include_once 'uCat/dialogs/uCat_cart.php';
    }

    private function cols_els_id2col_id($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            col_id
            FROM
            u235_cols_els
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage setup_uPage_page 110'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage setup_uPage_page 230");
        return $qr->col_id;
    }
    private function col_id2row_id($col_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            row_id
            FROM
            u235_cols
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage setup_uPage_page 240'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage setup_uPage_page 250");
        return $qr->row_id;
    }
    private function row_id2page_id($row_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            page_id
            FROM
            u235_rows
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage setup_uPage_page 260'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uPage setup_uPage_page 270");
        return $qr->page_id;
    }
    public function get_page_id($el_type,$el_id) {
        if($el_type=='el') {
            $col_id=$this->cols_els_id2col_id($el_id);
            $row_id=$this->col_id2row_id($col_id);
            return $this->row_id2page_id($row_id);
        }
        elseif($el_type='col') {
            $row_id=$this->col_id2row_id($el_id);
            return $this->row_id2page_id($row_id);
        }
        elseif($el_type='row') {
            return $this->row_id2page_id($el_id);
        }
        else $this->uFunc->error("uPage setup_uPage_page 280");
        return 0;
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','setup_uPage_page'),$str);
    }

    function __construct (&$uCore,$page_id) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);
        $this->uMenu=new uMenu($this->uCore);
        $this->uDrive=new \uDrive\common($this->uCore);
        $this->page_id=$page_id;

        $this->uCore->uInt_js('uPage','setup_uPage_page');
        $this->uCore->uInt_js('uForms','form');
        $this->uCore->uInt_js('uPage','row_templates');
        $this->uCore->uInt_js('uPage','page_templates');
        $this->uCore->uInt_js('uPage','el_templates');
        $this->uCore->uInt_js('uPage','page_filter');
    }
}
