<?php
namespace uPage\admin;
use HTMLPurifier;
use HTMLPurifier_Config;
use PDO;
use PDOException;
use uPage\common;

class ticker{
    private $uPage;
    private $uFunc;
    private $uCore;

    private function get_el_settings($cols_els_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            el_config_effects_ticker 
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
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_ticker_common 10'/*.$e->getMessage()*/);}

        return 0;
    }
    public function copy_el($cols_els_id,$new_col_id,$el,$source_site_id=site_id,$dest_site_id=0) {
        if(!$el_settings=$this->get_el_settings($el->cols_els_id,$source_site_id)) return 0;

        //attach art to col
        $this->uPage->create_el($cols_els_id,$new_col_id,'ticker',$el->el_pos,$el->el_style,$el->el_id,$dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO el_config_effects_ticker (
            cols_els_id, 
            site_id, 
            text
            ) VALUES (
            :cols_els_id, 
            :site_id, 
            :text
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text', $el_settings->text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_ticker_common 2'/*.$e->getMessage()*/);}

        return $cols_els_id;
    }

    public function attach_el2col($col_id,$el_id) {
//        require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

        $el_pos=$this->uPage->define_new_el_pos($col_id);

        //get new cols_els_id
        $cols_els_id=$this->uPage->get_new_cols_els_id();

        //attach element to col
        $res=$this->uPage->add_el2db($cols_els_id,$el_pos,'ticker',$col_id,$el_id);

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['text'])) {
            $text=$purifier->purify(htmlspecialchars(trim($_POST['text'])));

            if(!strlen($text)) $text="Sed ut perspiciatis unde omnis iste natus \nquae ab illo inventore veritatis";
        }
        else $text="Lorem ipsum dolor sit amet\nconsectetur adipiscing elit";
        $res[0]='"text":"'.rawurlencode($text).'",'.$res[0];

        //save element config
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("
            INSERT INTO
            el_config_effects_ticker (
                cols_els_id,
                site_id,
                text
            ) VALUES (
              :cols_els_id,
              :site_id,
              :text
            )
            ");
            $cols_els_id=$res[1];
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text', $text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
        }
        catch(PDOException $e) {$this->uFunc->error('uPage_elements_ticker_common 30'/*.$e->getMessage()*/);}

        echo '{'.$res[0].'}';
        exit;
    }

    public function load_el_content($cols_els_id,$site_id=site_id) {
        $conf=$this->get_el_settings($cols_els_id,$site_id);

        echo('{
        "status":"done",
        "cols_els_id":"'.$cols_els_id.'",
        
        '/*"el_css":"'.rawurlencode($el_css).'",*/.'
        
        "text":"'.rawurlencode($conf->text).'",
        }');

    }

    public function save_el_conf($cols_els_id) {
//        require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        if(isset($_POST['text'])) {
            $text=$purifier->purify(htmlspecialchars(trim($_POST['text'])));

            if(!strlen($text)) $text="Lorem ipsum dolor sit amet\nconsectetur adipiscing elit";
        }
        else $text="Lorem ipsum dolor sit amet\nconsectetur adipiscing elit";

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE
                el_config_effects_ticker
                SET 
                text=:text
                WHERE 
                cols_els_id=:cols_els_id AND
                site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':text', $text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}

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
