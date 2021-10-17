<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

class banner{
    private $uPage;
    private $uFunc;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_banner 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_banner_common 10'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'banner',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_banner (
            cols_els_id, 
            site_id, 
            background_color, 
            background_img, 
            background_stretch, 
            background_repeat_x, 
            background_repeat_y, 
            background_position, 
            min_height, 
            font_color, 
            font_size, 
            text
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :background_color, 
            :background_img, 
            :background_stretch, 
            :background_repeat_x, 
            :background_repeat_y, 
            :background_position, 
            :min_height, 
            :font_color, 
            :font_size, 
            :text          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_color', $el_settings->background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_img', $el_settings->background_img,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_stretch', $el_settings->background_stretch,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_x', $el_settings->background_repeat_x,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_y', $el_settings->background_repeat_y,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_position', $el_settings->background_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_height', $el_settings->min_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_color', $el_settings->font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_size', $el_settings->font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text', $el_settings->text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_banner_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'banner',$col_id,$el_id);


        if(isset($_POST['background_stretch'])) {
            if((int)$_POST['background_stretch']) $background_stretch=1;
            else $background_stretch=0;
        }
        else $background_stretch=1;
        $res[0]='"background_stretch":"'.$background_stretch.'",'.$res[0];

        if(isset($_POST['background_repeat_x'])) {
            if((int)$_POST['background_repeat_x']) $background_repeat_x=1;
            else $background_repeat_x=0;
        }
        else $background_repeat_x=1;
        $res[0]='"background_repeat_x":"'.$background_repeat_x.'",'.$res[0];

        if(isset($_POST['background_repeat_y'])) {
            if((int)$_POST['background_repeat_y']) $background_repeat_y=1;
            else $background_repeat_y=0;
        }
        else $background_repeat_y=1;
        $res[0]='"background_repeat_y":"'.$background_repeat_y.'",'.$res[0];

        if(isset($_POST['min_height'])) {
            if(uString::isDigits($_POST['min_height'])) $min_height=$_POST['min_height'];
            else $min_height=0;
        }
        else $min_height=1;
        $res[0]='"min_height":"'.$min_height.'",'.$res[0];

        if(isset($_POST['background_position'])) {
            if(uString::isDigits($_POST['background_position'])) {
                if($_POST['background_position']>=0&&$_POST['background_position']<16) $background_position=$_POST['background_position'];
                else $background_position=0;
            }
            else $background_position=0;
        }
        else $background_position=0;
        $res[0]='"background_position":"'.$background_position.'",'.$res[0];

        if(isset($_POST['background_color'])) {
            $background_color=str_replace("#","",trim($_POST['background_color']));
            if(!uString::isHexColor($background_color)) $background_color="";
        }
        else $background_color="";
        $res[0]='"background_color":"'.$background_color.'",'.$res[0];

        if(isset($_POST['font_color'])) {
            $font_color=str_replace("#","",trim($_POST['font_color']));
            if(!uString::isHexColor($font_color)) $font_color="";
        }
        else $font_color="";
        $res[0]='"font_color":"'.$font_color.'",'.$res[0];

        if(isset($_POST['background_img'])) {
            $background_img=str_replace("#","",trim($_POST['background_img']));
//            require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = \HTMLPurifier_Config::createDefault();
            if(!isset($this->purifier)) $this->purifier = new \HTMLPurifier($config);

            $background_img=$this->purifier->purify(htmlspecialchars(trim($background_img)));
//            if(!uString::isUrl_rus($background_img)) $background_img="";
        }
        else $background_img=1;
        $res[0]='"background_img":"'.$background_img.'",'.$res[0];

        if(isset($_POST['font_size'])) {
            $font_size=str_replace("#","",trim($_POST['font_size']));
            if(!uString::isFloat($font_size)) $font_size="";
        }
        else $font_size=1;
        $res[0]='"font_size":"'.$font_size.'",'.$res[0];

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_banner (
                cols_els_id,
                site_id,
                background_stretch,
                background_repeat_x,
                background_repeat_y,
                background_color,
                background_img,
                background_position,
                min_height,
                font_color,
                font_size,
                text
            ) VALUES (
              :cols_els_id,
              :site_id,
              :background_stretch,
              :background_repeat_x,
              :background_repeat_y,
              :background_color,
              :background_img,
              :background_position,
              :min_height,
              :font_color,
              :font_size,
              :text
            )
            ");
            $cols_els_id=$res[1];
            $text="<h1>Lorem ipsum dolor sit amet!</h1><p>consectetur adipiscing elit. Nullam ornare ante</p>";
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_stretch', $background_stretch,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_x', $background_repeat_x,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_y', $background_repeat_y,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_color', $background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_img', $background_img,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_position', $background_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_height', $min_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_color', $font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_size', $font_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text', $text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_banner_common 30'/*.$e->getMessage()*/);}

        echo '{
        '.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id)    {
        $conf = $this->get_el_settings($cols_els_id, $site_id);

//        if (!$cols_el = $this->uPage->cols_els_id2data($cols_els_id, "el_css")) $this->uFunc->error("uPage_elements_banner_common 300");
//        $el_css = uString::sql2text($cols_el->el_css, 1);




        echo('{
        "status":"done",
        "cols_els_id":"' . $cols_els_id . '",
        
        
        
        '/*"el_css":"' . rawurlencode($el_css) . '",*/.'
        
        "background_stretch":"'.$conf->background_stretch.'",
        "background_repeat_x":"'.$conf->background_repeat_x.'",
        "background_repeat_y":"'.$conf->background_repeat_y.'",
        "min_height":"'.$conf->min_height.'",
        "background_position":"'.$conf->background_position.'",
        "background_color":"'.$conf->background_color.'",
        "font_color":"'.$conf->font_color.'",
        "background_img":"'.$conf->background_img.'",
        "font_size":"'.$conf->font_size.'",
        "text":"'.rawurlencode($conf->text).'"
        }');

    }

    private function save_banner_conf($cols_els_id) {
        if(isset($_POST['background_stretch'])) {
            if((int)$_POST['background_stretch']) $background_stretch=1;
            else $background_stretch=0;
        }
        else $background_stretch=1;

        if(isset($_POST['background_repeat_x'])) {
            if((int)$_POST['background_repeat_x']) $background_repeat_x=1;
            else $background_repeat_x=0;
        }
        else $background_repeat_x=1;

        if(isset($_POST['background_repeat_y'])) {
            if((int)$_POST['background_repeat_y']) $background_repeat_y=1;
            else $background_repeat_y=0;
        }
        else $background_repeat_y=1;

        if(isset($_POST['min_height'])) {
            if(uString::isDigits($_POST['min_height'])) $min_height=$_POST['min_height'];
            else $min_height=0;
        }
        else $min_height=1;

        if(isset($_POST['background_position'])) {
            if(uString::isDigits($_POST['background_position'])) {
                if($_POST['background_position']>=0&&$_POST['background_position']<16) $background_position=$_POST['background_position'];
                else $background_position=0;
            }
            else $background_position=0;
        }
        else $background_position=0;

        if(isset($_POST['background_color'])) {
            $background_color=str_replace("#","",trim($_POST['background_color']));
            if(!uString::isHexColor($background_color)) $background_color="";
        }
        else $background_color="";

        if(isset($_POST['font_color'])) {
            $font_color=str_replace("#","",trim($_POST['font_color']));
            if(!uString::isHexColor($font_color)) $font_color="";
        }
        else $font_color="";

        if(isset($_POST['background_img'])) {
            $background_img=str_replace("#","",trim($_POST['background_img']));
            if(!uString::isUrl_rus($background_img)) $background_img="";
        }
        else $background_img=1;

        if(isset($_POST['font_size'])) {
            $font_size=str_replace("#","",trim($_POST['font_size']));
            if(!uString::isFloat($font_size)) $font_size="";
        }
        else $font_size=1;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_banner
                SET 
                background_stretch=:background_stretch,
                background_repeat_x=:background_repeat_x,
                background_repeat_y=:background_repeat_y,
                background_color=:background_color,
                background_img=:background_img,
                background_position=:background_position,
                min_height=:min_height,
                font_color=:font_color,
                font_size=:font_size 
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_stretch', $background_stretch,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_x', $background_repeat_x,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_repeat_y', $background_repeat_y,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_color', $background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_img', $background_img,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':background_position', $background_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_height', $min_height,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_color', $font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':font_size', $font_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('125'.$e->getMessage());}

    }
    private function save_banner_text($cols_els_id) {
        if(isset($_POST['text'])) {
            $text=trim($_POST['text']);
            if(!strlen($text)) $text="<p></p>";
        }
        else $this->uFunc->error(130);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_banner
                SET 
                text=:text 
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $stm->bindParam(':text', $text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('135'.$e->getMessage());}
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['text'])) $this->save_banner_text($cols_els_id);
        else $this->save_banner_conf($cols_els_id);


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
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}
