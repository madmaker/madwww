<?php
namespace uPage\admin;
use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use uPage\common;

class share{
    private $uFunc;
    private $uPage;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','share'),$str);
    }

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_share 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_share_common 10'/*.$e->getMessage()*/);}
        return 1;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach element to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'share',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_share (
            cols_els_id, 
            site_id, 
            show_fb, 
            show_lj, 
            show_mail, 
            show_ok, 
            show_twitter, 
            show_vk, 
            show_in, 
            orientation, 
            hide, 
            share_btn_txt, 
            shape, 
            size
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :show_fb, 
            :show_lj, 
            :show_mail, 
            :show_ok, 
            :show_twitter, 
            :show_vk, 
            :show_in, 
            :orientation, 
            :hide, 
            :share_btn_txt, 
            :shape, 
            :size          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_fb', $el_settings->show_fb,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_lj', $el_settings->show_lj,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_mail', $el_settings->show_mail,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_ok', $el_settings->show_ok,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_twitter', $el_settings->show_twitter,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_vk', $el_settings->show_vk,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_in', $el_settings->show_in,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':orientation', $el_settings->orientation,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hide', $el_settings->hide,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':share_btn_txt', $el_settings->share_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':shape', $el_settings->shape,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $el_settings->v,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_share_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
//        require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'share',$col_id,$el_id);

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['size'])) {
            $size=(int)$_POST['size'];
            if($size<0||$size>2) $size=0;
        }
        else $size=0;
        $res[0]='"size":"'.$size.'",'.$res[0];

        if(isset($_POST['shape'])) {
            $shape=(int)$_POST['shape'];
            if($shape<0||$shape>3) $shape=0;
        }
        else $shape=0;
        $res[0]='"shape":"'.$shape.'",'.$res[0];

        if(isset($_POST['hide'])) {
            $hide=(int)$_POST['hide'];
            if($hide<0||$hide>1) $hide=0;
        }
        else $hide=0;
        $res[0]='"hide":"'.$hide.'",'.$res[0];

        if(isset($_POST['orientation'])) {
            $orientation=(int)$_POST['orientation'];
            if($orientation<0||$orientation>1) $orientation=0;
        }
        else $orientation=0;
        $res[0]='"orientation":"'.$orientation.'",'.$res[0];

        if(isset($_POST['show_vk'])) $show_vk=(int)$_POST['show_vk']?1:0;
        else $show_vk=1;
        $res[0]='"show_vk":"'.$show_vk.'",'.$res[0];

        if(isset($_POST['show_in'])) $show_in=(int)$_POST['show_in']?1:0;
        else $show_in=1;
        $res[0]='"show_in":"'.$show_in.'",'.$res[0];

        if(isset($_POST['show_twitter'])) $show_twitter=(int)$_POST['show_twitter']?1:0;
        else $show_twitter=1;
        $res[0]='"show_twitter":"'.$show_twitter.'",'.$res[0];

        if(isset($_POST['show_ok'])) $show_ok=(int)$_POST['show_ok']?1:0;
        else $show_ok=1;
        $res[0]='"show_ok":"'.$show_ok.'",'.$res[0];

        if(isset($_POST['show_mail'])) $show_mail=(int)$_POST['show_mail']?1:0;
        else $show_mail=1;
        $res[0]='"show_mail":"'.$show_mail.'",'.$res[0];

        if(isset($_POST['show_lj'])) $show_lj=(int)$_POST['show_lj']?1:0;
        else $show_lj=1;
        $res[0]='"show_lj":"'.$show_lj.'",'.$res[0];

        if(isset($_POST['show_fb'])) $show_fb=(int)$_POST['show_fb']?1:0;
        else $show_fb=1;
        $res[0]='"show_fb":"'.$show_fb.'",'.$res[0];

        if(isset($_POST['share_btn_txt'])) {
            $share_btn_txt=$purifier->purify(htmlspecialchars(trim($_POST['share_btn_txt'])));

            if(!strlen($share_btn_txt)) $share_btn_txt=$this->text("Share btn - default btn txt"/*Поделиться*/);
        }
        else $share_btn_txt=$this->text("Share btn - default btn txt"/*Поделиться*/);
        $res[0]='"share_btn_txt":"'.rawurlencode($share_btn_txt).'",'.$res[0];

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_share (
                cols_els_id,
                site_id,
                show_fb, 
                show_lj, 
                show_mail, 
                show_ok, 
                show_twitter, 
                show_vk, 
                show_in, 
                orientation, 
                hide, 
                share_btn_txt, 
                shape, 
                size
            ) VALUES (
              :cols_els_id,
              :site_id,
              :show_fb, 
              :show_lj, 
              :show_mail, 
              :show_ok, 
              :show_twitter, 
              :show_vk, 
              :show_in, 
              :orientation, 
              :hide, 
              :share_btn_txt, 
              :shape, 
              :size
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_fb', $show_fb,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_lj', $show_lj,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_mail', $show_mail,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_ok', $show_ok,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_twitter', $show_twitter,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_vk', $show_vk,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_in', $show_in,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':orientation', $orientation,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hide', $hide,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':share_btn_txt', $share_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':shape', $shape,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_share_common 30'/*.$e->getMessage()*/);}

        echo '{'.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);

        print '{

        "size":"'.$conf->size.'",
        "shape":"'.$conf->shape.'",
        "hide":"'.$conf->hide.'",
        "orientation":"'.$conf->orientation.'",
        "show_vk":"'.$conf->show_vk.'",
        "show_in":"'.$conf->show_in.'",
        "show_twitter":"'.$conf->show_twitter.'",
        "show_ok":"'.$conf->show_ok.'",
        "show_mail":"'.$conf->show_mail.'",
        "show_lj":"'.$conf->show_lj.'",
        "show_fb":"'.$conf->show_fb.'",
        "share_btn_txt":"'.rawurlencode($conf->share_btn_txt).'",
        
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "code":"'.rawurlencode($conf->code).'",
        "do_not_run_in_editor":"'.$conf->do_not_run_in_editor.'"
        }';

    }

    public function save_el_conf($cols_els_id) {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['size'])) {
            $size=(int)$_POST['size'];
            if($size<0||$size>2) $size=0;
        }
        else $size=0;

        if(isset($_POST['shape'])) {
            $shape=(int)$_POST['shape'];
            if($shape<0||$shape>3) $shape=0;
        }
        else $shape=0;

        if(isset($_POST['hide'])) {
            $hide=(int)$_POST['hide'];
            if($hide<0||$hide>1) $hide=0;
        }
        else $hide=0;

        if(isset($_POST['orientation'])) {
            $orientation=(int)$_POST['orientation'];
            if($orientation<0||$orientation>1) $orientation=0;
        }
        else $orientation=0;

        if(isset($_POST['show_vk'])) $show_vk=(int)$_POST['show_vk']?1:0;
        else $show_vk=1;

        if(isset($_POST['show_in'])) $show_in=(int)$_POST['show_in']?1:0;
        else $show_in=1;

        if(isset($_POST['show_twitter'])) $show_twitter=(int)$_POST['show_twitter']?1:0;
        else $show_twitter=1;

        if(isset($_POST['show_ok'])) $show_ok=(int)$_POST['show_ok']?1:0;
        else $show_ok=1;

        if(isset($_POST['show_mail'])) $show_mail=(int)$_POST['show_mail']?1:0;
        else $show_mail=1;

        if(isset($_POST['show_lj'])) $show_lj=(int)$_POST['show_lj']?1:0;
        else $show_lj=1;

        if(isset($_POST['show_fb'])) $show_fb=(int)$_POST['show_fb']?1:0;
        else $show_fb=1;

        if(isset($_POST['share_btn_txt'])) {
            $share_btn_txt=$purifier->purify(htmlspecialchars(trim($_POST['share_btn_txt'])));

            if(!strlen($share_btn_txt)) $share_btn_txt=$this->text("Share btn - default btn txt"/*Поделиться*/);
        }
        else $share_btn_txt=$this->text("Share btn - default btn txt"/*Поделиться*/);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_share
                SET 
                show_fb=:show_fb, 
                show_lj=:show_lj, 
                show_mail=:show_mail, 
                show_ok=:show_ok, 
                show_twitter=:show_twitter, 
                show_vk=:show_vk, 
                show_in=:show_in, 
                orientation=:orientation, 
                hide=:hide, 
                share_btn_txt=:share_btn_txt, 
                shape=:shape, 
                size=:size
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_fb', $show_fb,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_lj', $show_lj,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_mail', $show_mail,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_ok', $show_ok,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_twitter', $show_twitter,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_vk', $show_vk,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_in', $show_in,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':orientation', $orientation,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':hide', $hide,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':share_btn_txt', $share_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':shape', $shape,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':size', $size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}

        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);

        exit('{
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
