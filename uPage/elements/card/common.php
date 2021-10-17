<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uPage\common;
use uString;

class card{
    private $uPage;
    private $uFunc;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_card 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_card_common 10'/*.$e->getMessage()*/);}
        return 0;
    }

    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'card',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_card (
            cols_els_id, 
            site_id, 
            card_background_color,
            card_img_url,
            card_img_size,
            card_img_position,
            card_text,
            card_min_height_lg,
            card_min_height_md,
            card_min_height_sm,
            card_min_height_xs,
            card_font_size,
            card_font_color
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :card_background_color,
            :card_img_url,
            :card_img_size,
            :card_img_position,
            :card_text,
            :card_min_height_lg,
            :card_min_height_md,
            :card_min_height_sm,
            :card_min_height_xs,
            :card_font_size,
            :card_font_color
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_background_color', $el_settings->card_background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_url', $el_settings->card_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_size', $el_settings->card_img_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_position', $el_settings->card_img_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_text', $el_settings->card_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_lg', $el_settings->card_min_height_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_md', $el_settings->card_min_height_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_sm', $el_settings->card_min_height_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_xs', $el_settings->card_min_height_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_size', $el_settings->card_font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_color', $el_settings->card_font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_card_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'card',$col_id,$el_id);


        if(isset($_POST['card_background_color'])) {
            $card_background_color=str_replace("#","",trim($_POST['card_background_color']));
            if(!uString::isHexColor($card_background_color)) $card_background_color="";
        }
        else $card_background_color="000";
        $res[0]='"card_background_color":"'.$card_background_color.'",'.$res[0];

        if(isset($_POST['card_font_color'])) {
            $card_font_color=str_replace("#","",trim($_POST['card_font_color']));
            if(!uString::isHexColor($card_font_color)) $card_font_color="";
        }
        else $card_font_color="fff";
        $res[0]='"card_font_color":"'.$card_font_color.'",'.$res[0];

        if(isset($_POST['card_img_url'])) {
            $card_img_url=str_replace("#","",trim($_POST['card_img_url']));
//            require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = \HTMLPurifier_Config::createDefault();
            if(!isset($this->purifier)) $this->purifier = new \HTMLPurifier($config);

            $card_img_url=$this->purifier->purify(htmlspecialchars(trim($card_img_url)));
        }
        else $card_img_url="";
        $res[0]='"card_img_url":"'.$card_img_url.'",'.$res[0];


        if(isset($_POST['card_img_size'])) {
            if((int)$_POST['card_img_size']) $card_img_size=1;
            else $card_img_size=0;
        }
        else $card_img_size=0;
        $res[0]='"card_img_size":"'.$card_img_size.'",'.$res[0];

        if(isset($_POST['card_min_height_lg'])) {
            $card_min_height_lg=(int)$_POST['card_min_height_lg'];
        }
        else $card_min_height_lg=0;
        $res[0]='"card_min_height_lg":"'.$card_min_height_lg.'",'.$res[0];


        if(isset($_POST['card_min_height_md'])) {
            $card_min_height_md=(int)$_POST['card_min_height_md'];
        }
        else $card_min_height_md=0;
        $res[0]='"card_min_height_md":"'.$card_min_height_md.'",'.$res[0];


        if(isset($_POST['card_min_height_sm'])) {
            $card_min_height_sm=(int)$_POST['card_min_height_sm'];
        }
        else $card_min_height_sm=0;
        $res[0]='"card_min_height_sm":"'.$card_min_height_sm.'",'.$res[0];


        if(isset($_POST['card_min_height_xs'])) {
            $card_min_height_xs=(int)$_POST['card_min_height_xs'];
        }
        else $card_min_height_xs=0;
        $res[0]='"card_min_height_xs":"'.$card_min_height_xs.'",'.$res[0];


        if(isset($_POST['card_font_size'])) {
            $card_font_size=(float)$_POST['card_font_size'];
        }
        else $card_font_size=1;
        $res[0]='"card_font_size":"'.$card_font_size.'",'.$res[0];

        if(isset($_POST['card_img_position'])) {
            if(uString::isDigits($_POST['card_img_position'])) {
                if($_POST['card_img_position']>=0&&$_POST['card_img_position']<16) $card_img_position=$_POST['card_img_position'];
                else $card_img_position=0;
            }
            else $card_img_position=0;
        }
        else $card_img_position=0;
        $res[0]='"card_img_position":"'.$card_img_position.'",'.$res[0];


        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_card (
                cols_els_id,
                site_id,
                card_background_color,
                card_img_url,
                card_img_size,
                card_img_position,
                card_text,
                card_min_height_lg,
                card_min_height_md,
                card_min_height_sm,
                card_min_height_xs,
                card_font_size,
                card_font_color
            ) VALUES (
                :cols_els_id,
                :site_id,
                :card_background_color,
                :card_img_url,
                :card_img_size,
                :card_img_position,
                :card_text,
                :card_min_height_lg,
                :card_min_height_md,
                :card_min_height_sm,
                :card_min_height_xs,
                :card_font_size,
                :card_font_color
            )
            ");
            $cols_els_id=$res[1];
            $card_text="<p>Lorem ipsum dolor sit amet consectetur adipiscing elit. Nullam ornare ante</p>";
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_background_color', $card_background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_url', $card_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_size', $card_img_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_position', $card_img_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_text', $card_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_lg', $card_min_height_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_md', $card_min_height_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_sm', $card_min_height_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_xs', $card_min_height_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_size', $card_font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_color', $card_font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_card_common 30'/*.$e->getMessage()*/);}

        echo '{
        '.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id)    {
        $conf = $this->get_el_settings($cols_els_id, $site_id);

        echo('{
        "status":"done",
        "cols_els_id":"' . $cols_els_id . '",
        "card_background_color":"'.$conf->card_background_color.'",
        "card_min_height_lg":'.$conf->card_min_height_lg.',
        "card_min_height_md":'.$conf->card_min_height_md.',
        "card_min_height_sm":'.$conf->card_min_height_sm.',
        "card_min_height_xs":'.$conf->card_min_height_xs.',
        "card_font_size":'.$conf->card_font_size.',
        "card_font_color":"'.$conf->card_font_color.'",
        
        "card_img_url":"'.$conf->card_img_url.'",
        "card_img_size":'.$conf->card_img_size.',
        "card_img_position":'.$conf->card_img_position.',
        "card_text":"'.rawurlencode($conf->card_text).'"
        }');
    }

    private function save_card_conf($cols_els_id) {
        if(isset($_POST['card_background_color'])) {
            $card_background_color=str_replace("#","",trim($_POST['card_background_color']));
            if(!uString::isHexColor($card_background_color)) $card_background_color="";
        }
        else $card_background_color="000";

        if(isset($_POST['card_font_color'])) {
            $card_font_color=str_replace("#","",trim($_POST['card_font_color']));
            if(!uString::isHexColor($card_font_color)) $card_font_color="";
        }
        else $card_font_color="fff";

        if(isset($_POST['card_img_url'])) {
            $card_img_url=str_replace("#","",trim($_POST['card_img_url']));
//            require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

            $config = \HTMLPurifier_Config::createDefault();
            if(!isset($this->purifier)) $this->purifier = new \HTMLPurifier($config);

            $card_img_url=$this->purifier->purify(htmlspecialchars(trim($card_img_url)));
        }
        else $card_img_url="";


        if(isset($_POST['card_img_size'])) {
            if((int)$_POST['card_img_size']) $card_img_size=1;
            else $card_img_size=0;
        }
        else $card_img_size=0;

        if(isset($_POST['card_min_height_lg'])) {
            $card_min_height_lg=(int)$_POST['card_min_height_lg'];
        }
        else $card_min_height_lg=0;


        if(isset($_POST['card_min_height_md'])) {
            $card_min_height_md=(int)$_POST['card_min_height_md'];
        }
        else $card_min_height_md=0;


        if(isset($_POST['card_min_height_sm'])) {
            $card_min_height_sm=(int)$_POST['card_min_height_sm'];
        }
        else $card_min_height_sm=0;


        if(isset($_POST['card_min_height_xs'])) {
            $card_min_height_xs=(int)$_POST['card_min_height_xs'];
        }
        else $card_min_height_xs=0;


        if(isset($_POST['card_font_size'])) {
            $card_font_size=(float)$_POST['card_font_size'];
        }
        else $card_font_size=1;

        if(isset($_POST['card_img_position'])) {
            if(uString::isDigits($_POST['card_img_position'])) {
                if($_POST['card_img_position']>=0&&$_POST['card_img_position']<16) $card_img_position=$_POST['card_img_position'];
                else $card_img_position=0;
            }
            else $card_img_position=0;
        }
        else $card_img_position=0;


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_card
                SET 
                card_background_color=:card_background_color,
                card_img_url=:card_img_url,
                card_img_size=:card_img_size,
                card_img_position=:card_img_position,
                card_min_height_lg=:card_min_height_lg,
                card_min_height_md=:card_min_height_md,
                card_min_height_sm=:card_min_height_sm,
                card_min_height_xs=:card_min_height_xs,
                card_font_size=:card_font_size,
                card_font_color=:card_font_color
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_background_color', $card_background_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_url', $card_img_url,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_size', $card_img_size,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_img_position', $card_img_position,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_lg', $card_min_height_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_md', $card_min_height_md,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_sm', $card_min_height_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_min_height_xs', $card_min_height_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_size', $card_font_size,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':card_font_color', $card_font_color,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_card_common 40'.$e->getMessage());}

        echo '{
            "cols_els_id":"'.$cols_els_id.'",
            "card_background_color":"'.$card_background_color.'",
            "status":"done"
            }';

    }
    private function save_card_text($cols_els_id) {
        if(isset($_POST['card_text'])) {
            $card_text=trim($_POST['card_text']);
            if(!strlen($card_text)) $card_text="<p></p>";
        }
        else $this->uFunc->error("uPage_elements_card_common 50");

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_card
                SET 
                card_text=:card_text 
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            $stm->bindParam(':card_text', $card_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_card_common 60'.$e->getMessage());}

        echo '{
            "cols_els_id":"'.$cols_els_id.'",
            "card_text":"'.rawurlencode($card_text).'",
            "status":"done"
            }';
    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['card_text'])) $this->save_card_text($cols_els_id);
        else $this->save_card_conf($cols_els_id);


        $page_id=$this->uPage->get_page_id('el',$cols_els_id);
        $this->uPage->clear_cache($page_id);
        exit;
    }

        function __construct (&$uPage) {
        $this->uPage=&$uPage;
        $this->uCore=&$this->uPage->uCore;
        if(!isset($this->uPage)) $this->uPage=new common($this->uCore);
        $this->uFunc=&$this->uPage->uFunc;
    }
}
