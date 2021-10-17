<?php
namespace uPage\admin;
use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use uPage\common;
use uString;

class uSubscr_news_form{
    private $uPage;
    private $uFunc;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','uSubscr_news_form'),$str);
    }

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_uSubscr_news_form 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uSubscr_news_form_common 10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'uSubscr_news_form',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_uSubscr_news_form (
            cols_els_id, 
            site_id, 
            header, 
            submit_btn_text, 
            show_name_field, 
            channels_used
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :header, 
            :submit_btn_text, 
            :show_name_field, 
            :channels_used          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':header', $el_settings->header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_text', $el_settings->submit_btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_name_field', $el_settings->show_name_field,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':channels_used', $el_settings->channels_used,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uSubscr_news_form_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
//        require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

        $el_pos=$this->uPage->define_new_el_pos($col_id,$el_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'uSubscr_news_form',$col_id,$el_id);

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['header'])) {
            $header=$purifier->purify(htmlspecialchars(trim($_POST['header'])));
            if(!strlen($header)) $header=$this->uPage->text("Subscribe element header"/*Подписаться на новости*/);
        }
        else $header=$this->uPage->text("Subscribe element header"/*Подписаться на новости*/);

        if(isset($_POST['submit_btn_text'])) {
            $submit_btn_text=$purifier->purify(htmlspecialchars(trim($_POST['submit_btn_text'])));
            if(!strlen($submit_btn_text)) $submit_btn_text=$this->uPage->text("Subscribe btn text"/*Подписаться*/);
        }
        else $submit_btn_text=$this->uPage->text("Subscribe btn text"/*Подписаться*/);

        if(isset($_POST['show_name_field'])) {
            $show_name_field=$_POST['show_name_field']?1:0;
        }
        else $show_name_field=0;

        if(isset($_POST['channels_used'])) {
            $channels_ar=explode(",",$_POST['channels_used']);
            $channels_used_ar=array();
            for($i=0;$i<count($channels_ar);$i++) {
                if(uString::isDigits($channels_ar[$i])) $channels_used_ar[count($channels_used_ar)]=$channels_ar[$i];
            }
            if(isset($channels_used_ar)) $channels_used=implode(",",$channels_used_ar);
            else $channels_used="";
        }
        else $channels_used="";

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_uSubscr_news_form (
                cols_els_id,
                site_id,
                header,
                submit_btn_text,
                show_name_field,
                channels_used
            ) VALUES (
              :cols_els_id,
              :site_id,
              :header,
              :submit_btn_text,
              :show_name_field,
              :channels_used
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':header', $header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_text', $submit_btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_name_field', $show_name_field,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':channels_used', $channels_used,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_uSubscr_news_form_common 30'/*.$e->getMessage()*/);}

        echo '{'.$res[0].',
        "header":"'.rawurlencode($header).'",
        "submit_btn_text":"'.rawurlencode($submit_btn_text).'",
        "show_name_field":"'.rawurlencode($show_name_field).'",
        "channels_used":"'.rawurlencode($channels_used).'"
        }';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("uPage_elements_uSubscr_news_form_common 500");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        echo('{
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "header":"'.rawurlencode($conf->header).'",
        "submit_btn_text":"'.rawurlencode($conf->submit_btn_text).'",
        "show_name_field":"'.rawurlencode($conf->show_name_field).'",
        "channels_used":"'.rawurlencode($conf->channels_used).'"
        }');

    }

    public function save_el_conf($cols_els_id) {
//        require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['header'])) {
            $header=$purifier->purify(htmlspecialchars(trim($_POST['header'])));
            if(!strlen($header)) $header=$this->text("Subscribe - form header"/*Подписаться на новости*/);
        }
        else $header=$this->text("Subscribe - form header"/*Подписаться на новости*/);

        if(isset($_POST['submit_btn_text'])) {
            $submit_btn_text=$purifier->purify(htmlspecialchars(trim($_POST['submit_btn_text'])));
            if(!strlen($submit_btn_text)) $submit_btn_text=$this->text("Subscribe - btn default txt"/*Подписаться*/);
        }
        else $submit_btn_text=$this->text("Subscribe - btn default txt"/*Подписаться*/);

        if(isset($_POST['show_name_field'])) {
            $show_name_field=$_POST['show_name_field']?1:0;
        }
        else $show_name_field=0;

        if(isset($_POST['channels_used'])) {
            $channels_used_ar=array();
            $channels_ar=explode(",",$_POST['channels_used']);
            for($i=0;$i<count($channels_ar);$i++) {
                if(uString::isDigits($channels_ar[$i])) $channels_used_ar[count($channels_used_ar)]=$channels_ar[$i];
            }
            if(isset($channels_used_ar)) $channels_used=implode(",",$channels_used_ar);
            else $channels_used="";
        }
        else $channels_used="";

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_uSubscr_news_form
                SET 
                header=:header,
                submit_btn_text=:submit_btn_text,
                show_name_field=:show_name_field,
                channels_used=:channels_used
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':header', $header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_text', $submit_btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_name_field', $show_name_field,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':channels_used', $channels_used,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('240'.$e->getMessage());}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
            "header":"'.rawurlencode($header).'",
            "submit_btn_text":"'.rawurlencode($submit_btn_text).'",
            "show_name_field":"'.rawurlencode($show_name_field).'",
            "channels_used":"'.rawurlencode($channels_used).'",
            "cols_els_id":"'.$cols_els_id.'",
            "status":"done"
            }');
    }

    function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}
