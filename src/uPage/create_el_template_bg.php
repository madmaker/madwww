<?php
namespace uPage\admin;
use Imagick;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

require_once "uPage/inc/common.php";

class create_el_template_bg {
    private $timestamp;
    private $page_width;
    private $el_data;
    private $uPage;
    private $template_name;
    private $scr_height;
    private $scr_width;
    private $scr_y;
    private $location;
    private $scr_x;
    private $screenshot_milliseconds;
    private $el_id;
    private $uFunc;
    private $uSes;
    private $uCore;

    private function pass_error($er_code) {
        //Passes an error message and exists script
        print "{
        'status':'error',
        'code':'".$er_code."'
        }";
        exit;
    }
    private function check_data() {
        //Check if all variables are received
        if(!isset($_POST["page_width"],
            $_POST["el_id"],
            $_POST["screenshot_milliseconds"],
            $_POST["location"],
            $_POST["scr_x"],
            $_POST["scr_y"],
            $_POST["scr_width"],
            $_POST["scr_height"],
            $_POST["template_name"]
        )) $this->uFunc->error(10);

        if(isset($_POST["timestamp"])) if(\uString::isDigits($_POST["timestamp"])) $this->timestamp=$_POST["timestamp"];
        else $this->timestamp=time();

        //cast all variables to needed type
        $this->page_width=(int)$_POST["page_width"];
        $this->el_id=(int)$_POST["el_id"];
        $this->screenshot_milliseconds=(int)$_POST["screenshot_milliseconds"];
        $this->location=$_POST["location"];
        $this->scr_x=(int)$_POST["scr_x"];
        $this->scr_y=(int)$_POST["scr_y"];
        $this->scr_width=(int)$_POST["scr_width"];
        $this->scr_height=(int)$_POST["scr_height"];
        $this->template_name=trim($_POST["template_name"]);

        //check all variables data
        $this->page_width=$this->page_width?1:0;
        $u_sroot=u_sroot;
//        $u_sroot=str_replace(".local","",$u_sroot);//Закомментить в production
        if(strpos($this->location,$u_sroot)===false) return 2;
        if($this->template_name==="") $this->template_name=$this->text("Template default name"/*Мой шаблон */)." ".$this->timestamp;
        if($this->scr_x<0) $this->scr_x=0;
        if($this->scr_y<0) $this->scr_y=0;
        if($this->scr_width<1) return 5;
        if($this->scr_height<1) return 6;
        if(!$this->el_data=$this->uPage->cols_els_id2data($this->el_id,"el_pos,col_id")) return 7;

        //If everything is OK - pass true
        return 1;
    }



    private function create_template($page_id,$site_id=site_id) {
        $language=$this->uFunc->getConf("site_lang","content",0,$site_id);
        $el_template_id=$this->uPage->get_new_el_template_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            els_templates (
            el_template_id, 
            el_template_name, 
            site_id, 
            page_id,
            language
            ) VALUES (
            :el_template_id, 
            :el_template_name, 
            :site_id, 
            :page_id,
            :language
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_template_id', $el_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':el_template_name', $this->template_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':language', $language,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'.$e->getMessage());}

        return $el_template_id;
    }

    private function load_screenshot($el_template_id) {
        $dir='uPage/templates/el_templates/'.site_id.'/'.$el_template_id;
        if(!file_exists($dir)) mkdir($dir,0755,true);

        $source_dir="screenshoter/images/".site_id;

        try {$im = new Imagick($source_dir."/".$this->timestamp);} catch (\ImagickException $e) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'could not make an image'/*,
                'addr'=>$source_dir."/".$this->timestamp*/
            ));
            exit;
        }

        $im->cropImage($this->scr_width,$this->scr_height,$this->scr_x,$this->scr_y);

        $im->writeImage($dir."/crop.jpg");

        $im->clear();
        $im->destroy();

        unlink($source_dir."/".$this->timestamp);

        return $source_dir."/".$this->timestamp;
    }

    private function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uPage','create_el_template_bg'),$str);
    }

    function __construct (&$uCore) {
        //Prepare needed variables and classes
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new common($this->uCore);

        //Check received post data
        if(($res_code=$this->check_data())!==1) $this->pass_error('1-'.$res_code);

        //get col_info_of el
        $col_id=(int)$this->el_data->col_id;
        if(!$col_data=$this->uPage->col_id2data($col_id,"md_width")) $this->uFunc->error(30);
        $col_width=$col_data->md_width;
        if(!$row_id=(int)$this->uPage->col_id2row_id($col_id)) $this->uFunc->error(40);
        if(!$row_data=$this->uPage->row_id2data($row_id,"page_id,row_content_centered")) $this->uFunc->error(50);
        if(!$page_id=(int)$row_data->page_id) $this->uFunc->error(60);
        $row_content_centered=$row_data->row_content_centered;

        //get page_data
        if(!$page_data=$this->uPage->page_id2data($page_id,"page_width")) $this->uFunc->error(70);
        $page_width=$page_data->page_width;
        //create_page_for_template
        $page_data=$this->uPage->create_page_for_template($this->template_name,$page_width);
        $page_id=$page_data["page_id"];
        //create 1 row on page
        $row_id=$this->uPage->get_new_row_id();
        $this->uPage->create_row($row_id,$page_id,1,$row_content_centered);

        //get cols_els_info
        if(!$cols_el=$this->uPage->cols_els_id2data($this->el_id,"*")) $this->uFunc->error(80);

        //create 1 col on page
        $col_id=$this->uPage->get_new_col_id();
        $this->uPage->create_col($col_id,$row_id,1,$col_width,$col_width,$col_width,$col_width);

        $this->uPage->copy_cols_el($page_data,$col_id,$cols_el);

        //create new template
        $el_template_id=$this->create_template($page_id);

        //load a screenshot
        $this->load_screenshot($el_template_id);

        echo "{
        'status':'done',
        'page_id':'".$page_id."'
        }";
        exit;
    }
}
new create_el_template_bg($this);