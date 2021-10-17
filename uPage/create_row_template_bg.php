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

class create_row_template_bg {
    private $timestamp;
    private $page_width;
    private $last_row_data;
    private $first_row_data;
    private $uPage;
    private $template_name;
    private $scr_height;
    private $scr_width;
    private $scr_y;
    private $location;
    private $scr_x;
    private $screenshot_milliseconds;
    private $last_row_id;
    private $first_row_id;
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
            $_POST["first_row_id"],
            $_POST["last_row_id"],
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
        $this->first_row_id=(int)$_POST["first_row_id"];
        $this->last_row_id=(int)$_POST["last_row_id"];
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
        if(!$this->first_row_data=$this->uPage->row_id2data($this->first_row_id,"row_pos,page_id")) return 7;
        if(!$this->last_row_data=$this->uPage->row_id2data($this->last_row_id,"row_pos,page_id")) return 8;
        //check if both first and last rows are belongs to same page
        if($this->first_row_data->page_id!==$this->last_row_data->page_id) return 9;

        //If everything is OK - pass true
        return 1;
    }

    private function get_all_rows_in_selection() {
        //get all rows in selection
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT 
            * 
            FROM 
            u235_rows 
            WHERE
            (
              (row_pos>=:first_row_pos AND row_pos<=:last_row_pos) OR 
              (row_pos>=:last_row_pos AND row_pos<=:first_row_pos) 
            ) AND
            page_id=:page_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $this->first_row_data->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':first_row_pos', $this->first_row_data->row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':last_row_pos', $this->last_row_data->row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        return $stm;
    }

    private function create_template($page_id,$site_id=site_id) {
        $language=$this->uFunc->getConf("site_lang","content",0,$site_id);
        $row_template_id=$this->uPage->get_new_row_template_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            rows_templates (
            row_template_id, 
            row_template_name, 
            site_id, 
            page_id,
            language
            ) VALUES (
            :row_template_id, 
            :row_template_name, 
            :site_id, 
            :page_id,
            :language
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $row_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_name', $this->template_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':language', $language,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        return $row_template_id;
    }

    private function load_screenshot($row_template_id) {
        $dir='uPage/templates/row_templates/'.site_id.'/'.$row_template_id;
        if(!file_exists($dir)) mkdir($dir,0755,true);

        $source_dir="screenshoter/images/".site_id;

        try {$im = new Imagick($source_dir."/".$this->timestamp);} catch (\ImagickException $e) {
            echo json_encode(array(
                'status'=>'error',
                'msg'=>'could not make an image',
                'addr'=>$source_dir."/".$this->timestamp
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
        return $this->uCore->text(array('uPage','create_row_template_bg'),$str);
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

        //get all rows in selection
        $rows_in_selection_stm=$this->get_all_rows_in_selection();

        //create_page_for_template
        $page_data=$this->uPage->create_page_for_template($this->template_name,$this->page_width);
        $page_id=$page_data["page_id"];

        //copy every row to page
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0; $row=$rows_in_selection_stm->fetch(PDO::FETCH_OBJ); $i++) {
            $this->uPage->copy_row($page_data,$row,$row->row_pos,site_id);
        }
        if($i<1) $this->pass_error(2);

        //create new template
        $row_template_id=$this->create_template($page_id);

        //load a screenshot
        $this->load_screenshot($row_template_id);

        echo "{
        'status':'done',
        'page_id':'".$page_id."'
        }";
        exit;
    }
}
new create_row_template_bg($this);