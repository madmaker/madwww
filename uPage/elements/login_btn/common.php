<?php
namespace uPage\admin;
use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use uPage\common;

class login_btn{
    private $uFunc;
    private $uPage;
    private $uCore;

    public function text($str) {
        return $this->uCore->text(array('uPage','login_btn'),$str);
    }

    public function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_login_btn 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_login_btn_common 10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->colsels_id,$source_site_id)) return 0;

        //attach login button to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'login_btn',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_login_btn (
            cols_els_id, 
            site_id, 
            btn_primary, 
            btn_info, 
            btn_warning, 
            btn_danger, 
            btn_sm, 
            btn_xs, 
            btn_lg, 
            btn_text, 
            replace_with_logout, 
            logout_text, 
            btn_success
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :btn_primary, 
            :btn_info, 
            :btn_warning, 
            :btn_danger, 
            :btn_sm, 
            :btn_xs, 
            :btn_lg, 
            :btn_text, 
            :replace_with_logout, 
            :logout_text, 
            :btn_success          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_primary', $el_settings->btn_primary,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_info', $el_settings->btn_info,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_warning', $el_settings->btn_warning,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_danger', $el_settings->btn_danger,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_sm', $el_settings->btn_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_xs', $el_settings->btn_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_lg', $el_settings->btn_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_text', $el_settings->btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':replace_with_logout', $el_settings->replace_with_logout,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':logout_text', $el_settings->logout_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_success', $el_settings->btn_success,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_login_btn_common 20'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach login button to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'login_btn',$col_id,$el_id);

        if(isset($_POST['btn_primary'])) {
            if((int)$_POST['btn_primary']) $btn_primary=1;
            else $btn_primary=0;
        }
        else $btn_primary=0;
        $res[0]='"btn_primary":"'.$btn_primary.'",'.$res[0];

        if(isset($_POST['btn_info'])) {
            if((int)$_POST['btn_info']) $btn_info=1;
            else $btn_info=0;
        }
        else $btn_info=0;
        $res[0]='"btn_info":"'.$btn_info.'",'.$res[0];

        if(isset($_POST['btn_success'])) {
            if((int)$_POST['btn_success']) $btn_success=1;
            else $btn_success=0;
        }
        else $btn_success=0;
        $res[0]='"btn_success":"'.$btn_success.'",'.$res[0];

        if(isset($_POST['btn_warning'])) {
            if((int)$_POST['btn_warning']) $btn_warning=1;
            else $btn_warning=0;
        }
        else $btn_warning=0;
        $res[0]='"btn_warning":"'.$btn_warning.'",'.$res[0];

        if(isset($_POST['btn_danger'])) {
            if((int)$_POST['btn_danger']) $btn_danger=1;
            else $btn_danger=0;
        }
        else $btn_danger=0;
        $res[0]='"btn_danger":"'.$btn_danger.'",'.$res[0];

        if(isset($_POST['btn_sm'])) {
            if((int)$_POST['btn_sm']) $btn_sm=1;
            else $btn_sm=0;
        }
        else $btn_sm=0;
        $res[0]='"btn_sm":"'.$btn_sm.'",'.$res[0];

        if(isset($_POST['btn_xs'])) {
            if((int)$_POST['btn_xs']) $btn_xs=1;
            else $btn_xs=0;
        }
        else $btn_xs=0;
        $res[0]='"btn_xs":"'.$btn_xs.'",'.$res[0];

        if(isset($_POST['btn_lg'])) {
            if((int)$_POST['btn_lg']) $btn_lg=1;
            else $btn_lg=0;
        }
        else $btn_lg=0;
        $res[0]='"btn_lg":"'.$btn_lg.'",'.$res[0];

        if(isset($_POST['replace_with_logout'])) {
            if((int)$_POST['replace_with_logout']) $replace_with_logout=1;
            else $replace_with_logout=0;
        }
        else $replace_with_logout=0;
        $res[0]='"replace_with_logout":"'.$replace_with_logout.'",'.$res[0];

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['btn_text'])) {
            $btn_text=$purifier->purify(htmlspecialchars(trim($_POST['btn_text'])));

            if(!strlen($btn_text)) $btn_text=$this->uPage->text("Login btn text"/*Вход*/);
        }
        else $btn_text=$this->uPage->text("Login btn text"/*Вход*/);
        $res[0]='"btn_text":"'.rawurlencode($btn_text).'",'.$res[0];

        if(isset($_POST['logout_text'])) {
            $logout_text=$purifier->purify(htmlspecialchars(trim($_POST['logout_text'])));

            if(!strlen($logout_text)) $logout_text=$this->uPage->text("Logout btn text"/*Выход*/);
        }
        else $logout_text=$this->uPage->text("Logout btn text"/*Выход*/);
        $res[0]='"logout_text":"'.rawurlencode($logout_text).'",'.$res[0];

        //save login_btn config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_login_btn (
                cols_els_id,
                site_id,
                btn_primary,
                btn_info,
                btn_success,
                btn_danger,
                btn_sm,
                btn_xs,
                btn_lg,
                btn_text,
                replace_with_logout,
                logout_text,
                btn_warning
            ) VALUES (
              :cols_els_id,
              :site_id,
              :btn_primary,
              :btn_info,
              :btn_success,
              :btn_danger,
              :btn_sm,
              :btn_xs,
              :btn_lg,
              :btn_text,
              :replace_with_logout,
              :logout_text,
              :btn_warning
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_primary', $btn_primary,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_info', $btn_info,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_success', $btn_success,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_danger', $btn_danger,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_warning', $btn_warning,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_sm', $btn_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_xs', $btn_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_lg', $btn_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':replace_with_logout', $replace_with_logout,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_text', $btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':logout_text', $logout_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_login_btn_common 30'/*.$e->getMessage()*/);}

        echo '{'.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);

//        if(!$cols_el=$this->uPage->cols_els_id2data($cols_els_id,"el_css")) $this->uFunc->error("uPage_elements_login_btn_common 500");
//        $el_css=uString::sql2text($cols_el->el_css,1);

        print '{
        "btn_primary":"'.$conf->btn_primary.'",
        "btn_info":"'.$conf->btn_info.'",
        "btn_success":"'.$conf->btn_success.'",
        "btn_warning":"'.$conf->btn_warning.'",
        "btn_danger":"'.$conf->btn_danger.'",
        "btn_sm":"'.$conf->btn_sm.'",
        "btn_xs":"'.$conf->btn_xs.'",
        "btn_lg":"'.$conf->btn_lg.'",
        "replace_with_logout":"'.$conf->replace_with_logout.'",
        "btn_text":"'.rawurlencode($conf->btn_text).'",
        "logout_text":"'.rawurlencode($conf->logout_text).'",

        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "code":"'.rawurlencode($conf->code).'",
        "do_not_run_in_editor":"'.$conf->do_not_run_in_editor.'"
        }';

    }

    public function save_el_conf($cols_els_id) {
        if(isset($_POST['btn_primary'])) {
            if((int)$_POST['btn_primary']) $btn_primary=1;
            else $btn_primary=0;
        }
        else $btn_primary=0;

        if(isset($_POST['btn_info'])) {
            if((int)$_POST['btn_info']) $btn_info=1;
            else $btn_info=0;
        }
        else $btn_info=0;

        if(isset($_POST['btn_success'])) {
            if((int)$_POST['btn_success']) $btn_success=1;
            else $btn_success=0;
        }
        else $btn_success=0;

        if(isset($_POST['btn_warning'])) {
            if((int)$_POST['btn_warning']) $btn_warning=1;
            else $btn_warning=0;
        }
        else $btn_warning=0;

        if(isset($_POST['btn_danger'])) {
            if((int)$_POST['btn_danger']) $btn_danger=1;
            else $btn_danger=0;
        }
        else $btn_danger=0;

        if(isset($_POST['btn_sm'])) {
            if((int)$_POST['btn_sm']) $btn_sm=1;
            else $btn_sm=0;
        }
        else $btn_sm=0;

        if(isset($_POST['btn_xs'])) {
            if((int)$_POST['btn_xs']) $btn_xs=1;
            else $btn_xs=0;
        }
        else $btn_xs=0;

        if(isset($_POST['btn_lg'])) {
            if((int)$_POST['btn_lg']) $btn_lg=1;
            else $btn_lg=0;
        }
        else $btn_lg=0;

        if(isset($_POST['replace_with_logout'])) {
            if((int)$_POST['replace_with_logout']) $replace_with_logout=1;
            else $replace_with_logout=0;
        }
        else $replace_with_logout=0;

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['btn_text'])) {
            $btn_text=$purifier->purify(htmlspecialchars(trim($_POST['btn_text'])));

            if(!strlen($btn_text)) $btn_text=$this->text("Login btn - btn text"/*Вход*/);
        }
        else $btn_text=$this->text("Login btn - btn text"/*Вход*/);

        if(isset($_POST['logout_text'])) {
            $logout_text=$purifier->purify(htmlspecialchars(trim($_POST['logout_text'])));

            if(!strlen($logout_text)) $logout_text=$this->text("Logout btn - btn text"/*Выход*/);
        }
        else $logout_text=$this->text("Logout btn - btn text"/*Выход*/);


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_login_btn 
                SET 
                btn_primary=:btn_primary,
                btn_info=:btn_info,
                btn_success=:btn_success,
                btn_danger=:btn_danger,
                btn_sm=:btn_sm,
                btn_xs=:btn_xs,
                btn_lg=:btn_lg,
                btn_text=:btn_text,
                replace_with_logout=:replace_with_logout,
                logout_text=:logout_text,
                btn_warning=:btn_warning
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_primary', $btn_primary,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_info', $btn_info,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_success', $btn_success,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_danger', $btn_danger,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_warning', $btn_warning,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_sm', $btn_sm,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_xs', $btn_xs,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_lg', $btn_lg,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':replace_with_logout', $replace_with_logout,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':btn_text', $btn_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':logout_text', $logout_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}

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