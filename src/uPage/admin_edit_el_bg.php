<?php
namespace uPage\admin;

use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

require_once 'uPage/inc/common.php';

class admin_edit_el_bg {
    public $uFunc;
    public $uSes;
    public $uPage;
    private $uCore,$cols_els_id,
        $el_pos,$col_id;
    private function check_data() {
        if(!isset($_REQUEST['cols_els_id'],$_REQUEST['field'])) $this->uFunc->error(5);
        $this->cols_els_id=$_REQUEST['cols_els_id'];
        if(!uString::isDigits($this->cols_els_id)) $this->uFunc->error(10);

        if($_REQUEST['field']=='el_css') {
            if(!isset($_REQUEST['el_css'],
                $_REQUEST['el_font_color'],
                $_REQUEST['el_link_color'],
                $_REQUEST['el_hoverlink_color'])) $this->uFunc->error(20);
        }
        elseif($_REQUEST['field']=='el_pos') {
            if(!isset($_REQUEST['el_pos'],$_REQUEST['col_id'])) $this->uFunc->error(30);
            $this->el_pos=$_REQUEST['el_pos'];
            $this->col_id=$_REQUEST['col_id'];
            if(!uString::isDigits($this->el_pos)) $this->uFunc->error(40);
            if(!uString::isDigits($this->col_id)) $this->uFunc->error(50);
        }
        elseif($_REQUEST['field']=='el_config') {}
        else $this->uFunc->error(60);
    }
    private function save_el_css() {
        $el_style_ar['el_font_color']=$_REQUEST['el_font_color'];
        $el_style_ar['el_link_color']=$_REQUEST['el_link_color'];
        $el_style_ar['el_hoverlink_color']=$_REQUEST['el_hoverlink_color'];
        $el_style_ar['el_css']=$_REQUEST['el_css'];

        $this->uPage->save_el_css($this->cols_els_id,$el_style_ar);

        die('{
        "status":"done",
        "cols_els_id":"'.$this->cols_els_id.'",
        "el_font_color":"'.$el_style_ar['el_font_color'].'",
        "el_link_color":"'.$el_style_ar['el_link_color'].'",
        "el_hoverlink_color":"'.$el_style_ar['el_hoverlink_color'].'",
        "el_css":"'.rawurlencode($el_style_ar['el_css']).'"
        }');
    }
    private function move_down_els_since_el_pos($el_pos) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols_els
            SET
            el_pos=el_pos+1
            WHERE
            el_pos>=:el_pos AND
            col_id=:col_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $this->col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/);}
    }
    private function define_new_el_pos() {
        $el_pos=(int)$_REQUEST['el_pos'];
        if($el_pos==0) {//Если 0, то вставляем на самый верх. Нужно посмотреть el_pos самого верхнего el и поставить над ним (значение может быть отрицательным). Создавать el с el_pos=0 нельзя. Это зарезервировано!
            //Смотрим el_pos самого верхнего элемента
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                el_pos
                FROM
                u235_cols_els
                WHERE
                col_id=:col_id AND
                site_id=:site_id
                ORDER BY
                el_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $this->col_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $next_el_pos=(int)$qr->el_pos;
                    $new_el_pos=$next_el_pos-1;
                    if($new_el_pos==0) $new_el_pos=-1;
                }
                else $new_el_pos=1;
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
        }
        else {
            //Вставляем под какой-то уже существующий el
            //Ищем el, у которого el_pos идет следующим за тем, под которым мы вставляем, чтобы понять, между какими значениями el_pos нам нужно впихнуть наш новый el
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
                el_pos
                FROM
                u235_cols_els
                WHERE
                el_pos>:el_pos AND
                col_id=:col_id AND
                site_id=:site_id
                ORDER BY
                el_pos ASC
                LIMIT 1
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $this->col_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                    $next_el_pos=(int)$qr->el_pos;
                    if($next_el_pos-$el_pos>1) {
                        $new_el_pos=$el_pos+1;
                        if(!$new_el_pos) {
                            $new_el_pos=$el_pos+2;
                            if($new_el_pos>=$next_el_pos) {
                                //next_el_pos и ниже нужно подвинуть вниз
                                $this->move_down_els_since_el_pos($next_el_pos);
                                if($next_el_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                                    $this->move_down_els_since_el_pos(0);
                                }
                            }
                        }
                    }
                    else {
                        //next_el_pos и ниже нужно подвинуть вниз
                        $this->move_down_els_since_el_pos($next_el_pos);
                        if($next_el_pos<0) {//Нужно все, что 0 и выше опять подвинуть вниз
                            $this->move_down_els_since_el_pos(0);
                        }
                        $new_el_pos=$el_pos+1;
                    }
                }
                else $new_el_pos=$el_pos+1;
            }
            catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

        }
        /** @noinspection PhpUndefinedVariableInspection */
        return $new_el_pos;
    }
    private function move_el() {
        $el_pos=$this->define_new_el_pos();

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
            u235_cols_els
            SET
            el_pos=:el_pos,
            col_id=:col_id
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_pos', $el_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $this->col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $this->cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}

        //Достаем все el с cols_els_id и el_pos, чтобы передать браузеру информацию об изменениях
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            cols_els_id,
            el_pos
            FROM
            u235_cols_els
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $this->col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
        $result='{';
        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        while($el=$stm->fetch(PDO::FETCH_OBJ)) {
            $result.='"el_'.$el->cols_els_id.'":"'.$el->el_pos.'",';
        }
        $result.='"status":"done",
        "col_id":"'.$this->col_id.'",
        "cols_els_id":"'.$this->cols_els_id.'"
        }';

        $this->clear_cache();
        die($result);
    }
    private function clear_cache() {
        //clear cache
        $page_id=$this->uPage->get_page_id('el',$this->cols_els_id);

        $this->uPage->clear_cache($page_id);
    }

    private function save_el_conf() {
        //get el_type
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            el_type
            FROM 
            u235_cols_els
            WHERE 
            cols_els_id=:cols_els_id AND 
            site_id=:site_id");

            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $this->cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$res=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(140);
        }
        catch(PDOException $e) {$this->uFunc->error('145'/*.$e->getMessage()*/);}

        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        /** @noinspection PhpUndefinedVariableInspection */
        if($res->el_type=='art') {
            if(!isset($this->art)) {
                require_once "uPage/elements/art/common.php";
                $this->art=new art($this->uPage);
            }
            $this->art->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='banner') {
            if(!isset($this->banner)) {
                require_once "uPage/elements/banner/common.php";
                $this->banner=new banner($this->uPage);
            }
            $this->banner->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='card') {
            if(!isset($this->card)) {
                require_once "uPage/elements/card/common.php";
                $this->card=new card($this->uPage);
            }
            $this->card->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='code') {
            if(!isset($this->code)) {
                require_once "uPage/elements/code/common.php";
                $this->code=new code($this->uPage);
            }
            $this->code->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='gallery') {
            if(!isset($this->gallery)) {
                require_once "uPage/elements/gallery/common.php";
                $this->gallery=new gallery($this->uPage);
            }
            $this->gallery->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='gmap') {
            if(!isset($this->gmap)) {
                require_once "uPage/elements/gmap/common.php";
                $this->gmap=new gmap($this->uPage);
            }
            $this->gmap->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='login_btn') {
            if(!isset($this->login_btn)) {
                require_once "uPage/elements/login_btn/common.php";
                $this->login_btn=new login_btn($this->uPage);
            }
            $this->login_btn->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='page_filter') {
            if(!isset($this->page_filter)) {
                require_once "uPage/elements/page_filter/common.php";
                $this->page_filter=new page_filter($this->uPage);
            }
            $this->page_filter->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='rubrics_arts') {
            if(!isset($this->rubrics_arts)) {
                require_once "uPage/elements/rubrics_arts/common.php";
                $this->rubrics_arts=new rubrics_arts($this->uPage);
            }
            $this->rubrics_arts->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='rubrics_arts_column') {
            if(!isset($this->rubrics_arts_column)) {
                require_once "uPage/elements/rubrics_arts_column/common.php";
                $this->rubrics_arts_column=new urubrics_arts_column($this->uPage);
            }
            $this->rubrics_arts_column->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='rubrics_tiles') {
            if(!isset($this->rubrics_tiles)) {
                require_once "uPage/elements/rubrics_tiles/common.php";
                $this->rubrics_tiles=new urubrics_tiles($this->uPage);
            }
            $this->rubrics_tiles->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='search') {
            if(!isset($this->search)) {
                require_once "uPage/elements/search/common.php";
                $this->search=new search($this->uPage);
            }
            $this->search->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='share') {
            if(!isset($this->share)) {
                require_once "uPage/elements/share/common.php";
                $this->share=new share($this->uPage);
            }
            $this->share->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='spacer') {
            if(!isset($this->spacer)) {
                require_once "uPage/elements/spacer/common.php";
                $this->spacer=new spacer($this->uPage);
            }
            $this->spacer->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='tabs') {
            if(!isset($this->tabs)) {
                require_once "uPage/elements/tabs/common.php";
                $this->tabs=new tabs($this->uPage);
            }
            $this->tabs->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='ticker') {
            if(!isset($this->ticker)) {
                require_once "uPage/elements/ticker/common.php";
                $this->ticker=new ticker($this->uPage);
            }
            $this->ticker->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uCat_latest') {
            if(!isset($this->uCat_latest)) {
                require_once "uPage/elements/uCat_latest/common.php";
                $this->uCat_latest=new uCat_latest($this->uPage);
            }
            $this->uCat_latest->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uCat_latest_articles_slider') {
            if(!isset($this->uCat_latest_articles_slider)) {
                require_once "uPage/elements/uCat_latest_articles_slider/common.php";
                $this->uCat_latest_articles_slider=new uCat_latest_articles_slider($this->uPage);
            }
            $this->uCat_latest_articles_slider->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uCat_new_items') {
            if(!isset($this->uCat_new_items)) {
                require_once "uPage/elements/uCat_new_items/common.php";
                $this->uCat_new_items=new uCat_new_items($this->uPage);
            }
            $this->uCat_new_items->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uCat_sale') {
            if(!isset($this->uCat_sale)) {
                require_once "uPage/elements/uCat_sale/common.php";
                $this->uCat_sale=new uCat_sale($this->uPage);
            }
            $this->uCat_sale->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uCat_search') {
            if(!isset($this->uCat_search)) {
                require_once "uPage/elements/uCat_search/common.php";
                $this->uCat_search=new uCat_search($this->uPage);
            }
            $this->uCat_search->save_el_conf($this->cols_els_id);
        }
        elseif($res->el_type=='uSubscr_news_form') {
            if(!isset($this->uSubscr_news_form)) {
                require_once "uPage/elements/uSubscr_news_form/common.php";
                $this->uSubscr_news_form=new uSubscr_news_form($this->uPage);
            }
            $this->uSubscr_news_form->save_el_conf($this->cols_els_id);
        }
        else $this->uFunc->error(500);
    }

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','admin_edit_el_bg'),$str);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);


        $this->check_data();
        if($_REQUEST['field']=='el_css') $this->save_el_css();
        elseif($_REQUEST['field']=='el_pos') $this->move_el();
        elseif($_REQUEST['field']=='el_config') $this->save_el_conf();
        else $this->uFunc->error(510);
    }
}
new admin_edit_el_bg($this);