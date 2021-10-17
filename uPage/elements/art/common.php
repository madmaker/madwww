<?php
namespace uPage\admin;
use PDO;
use PDOException;
use uEditor_page_avatar;
use uPage\common;
use uString;

class art {
    private $uFunc;
    private $uPage;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_art 
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
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/10'/*.$e->getMessage()*/);}

        return 0;
    }

    public function copy_el($page_data,$cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!isset($page_data["page_id"])) return 0;
        $page_id=(int)$page_data["page_id"];

        if(!isset($page_data["page_title"])) {
            $data=$this->uPage->page_id2data($page_id,"page_title",$dest_site_id);
            $page_data["page_title"]=$data->page_title;
        }
        $page_title=$page_data["page_title"];

        if(!isset($page_data["text_folder_id"])) {
            $data=$this->uPage->page_id2data($page_id,"text_folder_id",$dest_site_id);
            $page_data["text_folder_id"]=$this->uPage->define_text_folder_id($page_id,$page_title,$data->text_folder_id);
        }
        $text_folder_id=(int)$page_data["text_folder_id"];

        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        if(!isset($this->uEditor)) {
            require_once "uEditor/classes/common.php";
            $this->uEditor=new \uEditor\common($this->uCore);
        }
        $new_el_id=$this->uEditor->copy_text($el->el_id,$text_folder_id,$source_site_id,$dest_site_id);

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'art',$el->el_pos,$el->el_style,$new_el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_art (
            cols_els_id, 
            site_id, 
            show_title, 
            title_is_link2art, 
            show_avatar, 
            short_text_is_link2art, 
            show_short_text, 
            show_more_btn, 
            show_text
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :show_title, 
            :title_is_link2art, 
            :show_avatar, 
            :short_text_is_link2art, 
            :show_short_text, 
            :show_more_btn, 
            :show_text 
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $el_settings->show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title_is_link2art', $el_settings->title_is_link2art,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatar', $el_settings->show_avatar,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':short_text_is_link2art', $el_settings->short_text_is_link2art,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_short_text', $el_settings->show_short_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_more_btn', $el_settings->show_more_btn,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_text', $el_settings->show_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_text($el_id,$col_id) {
        if(!isset($this->uEditor)) {
            require_once 'uEditor/classes/common.php';
            $this->uEditor=new \uEditor\common($this->uCore);
        }

        //check if this art_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            page_id
            FROM
            u235_pages_html
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("/uPage/elements/art/common/30");
        }
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/40'/*.$e->getMessage()*/);}

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach art to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'art',$col_id,$el_id);

        $conf=$this->get_el_config_art($cols_els_id);

        $q_get="page_name,page_alias,page_avatar_time".
            ($conf->show_title?",page_title":"").
            ($conf->show_short_text?",page_short_text":"").
            ($conf->show_text?",page_text":"");

        $text_data=$this->uEditor->page_id2info($el_id,$q_get);

        $page_name=uString::sql2text($text_data->page_name,1);

        if(isset($text_data->page_short_text)) {
            $short_text = uString::sql2text($text_data->page_short_text, 1);
            if ($conf->short_text_is_link2art) $short_text = uString::removeHTML($short_text);
        }
        else $short_text="";

        if(isset($text_data->page_text)) {
            $text=uString::repairHtml(uString::sql2text($text_data->page_text,1));
        }
        else $text="";

        if(isset($text_data->page_title)) {
            $page_title=uString::sql2text($text_data->page_title,1);
        }
        else $page_title="";

        $page_avatar="";
        if($conf->show_avatar&&$text_data->page_avatar_time) {
            if(!isset($this->uEditor_page_avatar)) {
                require_once 'uEditor/inc/page_avatar.php';
                $this->uEditor_page_avatar=new \uEditor_page_avatar($this->uCore);
            }
            $page_avatar=$this->uEditor_page_avatar->get_avatar(450, $el_id);
        }

        echo '{
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",';

        echo '"show_title":"'.$conf->show_title.'",';
        echo '"title_is_link2art":"'.$conf->title_is_link2art.'",';
        echo '"show_avatar":"'.$conf->show_avatar.'",';
        echo '"show_short_text":"'.$conf->show_short_text.'",';
        echo '"short_text_is_link2art":"'.$conf->short_text_is_link2art.'",';
        echo '"show_more_btn":"'.$conf->show_more_btn.'",';
        echo '"show_text":"'.$conf->show_text.'",';


        echo '"page_name":"'.rawurlencode($page_name).'",
        "page_title":"'.rawurlencode($page_title).'",
        "page_alias":"'.rawurlencode($text_data->page_alias).'",
        "short_text":"'.rawurlencode($short_text).'",
        "text":"'.rawurlencode($text).'",
        "page_avatar_time":"'.$text_data->page_avatar_time.'",
        "page_avatar":"'.$page_avatar.'",';

        echo $res[0].'}';
        exit;
    }

    public function get_art($el_id, $q_get="page_name") {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            ".$q_get."
            FROM
            u235_pages_html
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $el_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/50'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }

    private function create_default_el_config_art($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO
            el_config_art(
            cols_els_id, 
            site_id
            ) VALUES (
            :cols_els_id, 
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/60'/*.$e->getMessage()*/);}
    }
    public function get_el_config_art($cols_els_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            show_title,
            title_is_link2art,
            show_avatar,
            show_short_text,
            short_text_is_link2art,
            show_more_btn,
            show_text
            FROM
            el_config_art
            WHERE 
            cols_els_id=:cols_els_id AND 
            site_id=:site_id 
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$conf=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->create_default_el_config_art($cols_els_id);
                $conf=$this->get_el_config_art($cols_els_id);
            }
            return $conf;
        }
        catch(PDOException $e) {$this->uFunc->error('/uPage/elements/art/common/70'/*.$e->getMessage()*/);}
        return 0;
    }

    public function load_text_content($cols_els_id,$el_id) {
        if(!isset($this->uEditor)) {
            require_once 'uEditor/classes/common.php';
            $this->uEditor=new \uEditor\common($this->uCore);
        }
        $conf=$this->get_el_config_art($cols_els_id);

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("/uPage/elements/art/common/75");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        $q_get="page_name,page_alias,page_avatar_time,page_title,page_short_text,page_text,page_id";

        $text_data=$this->uEditor->page_id2info($el_id,$q_get);

        if(!$text_data) $this->uFunc->error("/uPage/elements/art/common/80");

        $page_name=uString::sql2text($text_data->page_name,1);

        if(isset($text_data->page_short_text)) {
            $short_text = uString::sql2text($text_data->page_short_text, 1);
            if ($conf->short_text_is_link2art) $short_text = uString::removeHTML($short_text);
        }
        else $short_text="";

        if(isset($text_data->page_text)) {
            $text=uString::repairHtml(uString::sql2text($text_data->page_text,1));
        }
        else $text="";

        if(isset($text_data->page_title)) {
            $page_title=uString::sql2text($text_data->page_title,1);
        }
        else $page_title="";

        $page_avatar="";
        if($conf->show_avatar&&$text_data->page_avatar_time) {
            require_once 'uEditor/inc/page_avatar.php';
            $avatar=new uEditor_page_avatar($this->uCore);
            $page_avatar=$avatar->get_avatar(450, $el_id);
        }

        echo('{
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        "show_title":"'.$conf->show_title.'",
        "title_is_link2art":"'.$conf->title_is_link2art.'",
        "show_avatar":"'.$conf->show_avatar.'",
        "show_short_text":"'.$conf->show_short_text.'",
        "short_text_is_link2art":"'.$conf->short_text_is_link2art.'",
        "show_more_btn":"'.$conf->show_more_btn.'",
        "show_text":"'.$conf->show_text.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "el_id":"'.rawurlencode($el_id).'",
        
        "page_name":"'.rawurlencode($page_name).'",
        "page_title":"'.rawurlencode($page_title).'",
        "page_alias":"'.rawurlencode($text_data->page_alias).'",
        "short_text":"'.rawurlencode($short_text).'",
        "text":"'.rawurlencode($text).'",
        "page_avatar_time":"'.$text_data->page_avatar_time.'",
        "page_avatar":"'.$page_avatar.'"
        }');

    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['show_title'])) {
            if((int)$_POST['show_title']) $show_title=1;
            else $show_title=0;
        }
        else $show_title=0;

        if(isset($_POST['title_is_link2art'])) {
            if((int)$_POST['title_is_link2art']) $title_is_link2art=1;
            else $title_is_link2art=0;
        }
        else $title_is_link2art=0;

        if(isset($_POST['show_avatar'])) {
            if((int)$_POST['show_avatar']) $show_avatar=1;
            else $show_avatar=0;
        }
        else $show_avatar=1;

        if(isset($_POST['show_short_text'])) {
            if((int)$_POST['show_short_text']) $show_short_text=1;
            else $show_short_text=0;
        }
        else $show_short_text=0;

        if(isset($_POST['short_text_is_link2art'])) {
            if((int)$_POST['short_text_is_link2art']) $short_text_is_link2art=1;
            else $short_text_is_link2art=0;
        }
        else $short_text_is_link2art=0;

        if(isset($_POST['show_more_btn'])) {
            if((int)$_POST['show_more_btn']) $show_more_btn=1;
            else $show_more_btn=0;
        }
        else $show_more_btn=0;

        if(isset($_POST['show_text'])) {
            if((int)$_POST['show_text']) $show_text=1;
            else $show_text=0;
        }
        else $show_text=1;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_art 
                SET 
                show_title=:show_title,
                title_is_link2art=:title_is_link2art,
                show_avatar=:show_avatar,
                show_short_text=:show_short_text,
                short_text_is_link2art=:short_text_is_link2art,
                show_more_btn=:show_more_btn,
                show_text=:show_text
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
                ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_title', $show_title,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':title_is_link2art', $title_is_link2art,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_avatar', $show_avatar,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_short_text', $show_short_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':short_text_is_link2art', $short_text_is_link2art,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_more_btn', $show_more_btn,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':show_text', $show_text,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('150'/*.$e->getMessage()*/);}

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
