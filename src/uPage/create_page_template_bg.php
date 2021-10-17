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

class create_page_template_bg {
    private $timestamp;
    private $page_data;
    private $page_id;
    private $uPage;
    private $template_name;
    private $scr_height;
    private $scr_width;
    private $scr_y;
    private $location;
    private $scr_x;
    private $screenshot_milliseconds;
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
        if(!isset($_POST["page_id"],
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
        $this->page_id=(int)$_POST["page_id"];
        $this->screenshot_milliseconds=(int)$_POST["screenshot_milliseconds"];
        $this->location=$_POST["location"];
        $this->scr_x=(int)$_POST["scr_x"];
        $this->scr_y=(int)$_POST["scr_y"];
        $this->scr_width=(int)$_POST["scr_width"];
        $this->scr_height=(int)$_POST["scr_height"];
        $this->template_name=trim($_POST["template_name"]);

        //check all variables data
        $u_sroot=u_sroot;
//        $u_sroot=str_replace(".local","",$u_sroot);//Закомментить в production
        if(strpos($this->location,$u_sroot)===false) return 2;
        if($this->template_name==="") $this->template_name=$this->text("Template default name"/*Мой шаблон */)." ".time();
        if($this->scr_x<0) $this->scr_x=0;
        if($this->scr_y<0) $this->scr_y=0;
        if($this->scr_width<1) return 5;
        if($this->scr_height<1) return 6;
        if(!$this->page_data=$this->uPage->page_id2data($this->page_id,"*")) return 7;

        //If everything is OK - pass true
        return 1;
    }

    private function create_template($page_id,$site_id=site_id) {
        $language=$this->uFunc->getConf("site_lang","content",0,$site_id);
        $page_template_id=$this->uPage->get_new_page_template_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO 
            pages_templates (
            page_template_id, 
            page_template_name, 
            site_id, 
            page_id,
            language
            ) VALUES (
            :page_template_id, 
            :page_template_name, 
            :site_id, 
            :page_id,
            :language
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_template_id', $page_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_template_name', $this->template_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':language', $language,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        return $page_template_id;
    }

    private function load_screenshot($page_template_id) {
        $dir='uPage/templates/page_templates/'.site_id.'/'.$page_template_id;
        if(!file_exists($dir)) mkdir($dir,0755,true);


        $source_dir="screenshoter/images/".site_id;

        try {$im = new Imagick($source_dir."/".$this->timestamp);} catch (\ImagickException $e) {
            echo json_encode(array(
            'status'=>'error',
            'msg'=>'could not make an image',
            'addr'=>$source_dir."/".$this->timestamp,
                'exception'=>print_r($e,1)
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
        return $this->uCore->text(array('uPage','create_page_template_bg'),$str);
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

        $page_data=$this->page_data;
        $page_data->page_url=$page_data->page_url."_".site_id.time();
        $page_data->page_title=$this->template_name;
        if(!isset($this->uDrive)) {
            require_once "uDrive/classes/common.php";
            $this->uDrive=new \uDrive\common($this->uCore);
        }
        if (!isset($this->uEditor)) {
            require_once "uEditor/classes/common.php";
            $this->uEditor = new \uEditor\common($this->uCore);
        }
        $uDrive_uPage_folder_id = $this->uDrive->get_module_folder_id("uPage");
        $page_data->uDrive_folder_id=$this->uDrive->create_folder($this->template_name,$uDrive_uPage_folder_id);
        $page_data->text_folder_id = $this->uEditor->create_folder($this->template_name, 0);
        $page_data->folder_id=$this->uPage->get_system_folder("templates");

        //copy page
        if(!$page_data=$this->uPage->copy_page($this->page_data)) $this->uFunc->error(60);
        $page_id=$page_data->page_id;

        //create new template
        $page_template_id=$this->create_template($page_id);

        //load a screenshot
        $img_addr=$this->load_screenshot($page_template_id);

        echo json_encode(array(
        'status'=>'done',
        'page_id'=>$page_id
        ));
        exit;
    }
}
new create_page_template_bg($this);