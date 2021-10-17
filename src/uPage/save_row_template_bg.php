<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uPage\common;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class save_row_template_bg {
    private $action;
    private $row_template_id;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset($_POST["row_template_id"],$_POST["action"])) $this->uFunc->error(10);
        $this->row_template_id=(int)$_POST['row_template_id'];

        $this->action=$_POST["action"];
    }

    private function apply_row_template($site_id=site_id) {
        if(site_id!=8) die("{'status' : 'forbidden'}");
        if(!isset($_POST["language"])) $this->uFunc->error(20);
        $language=$_POST["language"];
        $allowed_langs=array("en_US","ru_RU");
        if(!in_array($language,$allowed_langs)) $this->uFunc->error(30);

        if(!isset($this->uPage)) {
            require_once "uPage/inc/common.php";
            $this->uPage=new common($this->uCore);
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE  
            rows_templates 
            SET
            row_template_status=0 
            WHERE
            row_template_id=:row_template_id                          
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $this->row_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        if(!$row_template_data=$this->uPage->row_template_id2data($this->row_template_id,"row_template_name,page_id,site_id")) $this->uFunc->error(50);

        $page_data=$this->uPage->page_id2data($row_template_data->page_id,"*",$row_template_data->site_id);
        $page_data->page_url=$page_data->page_url."_".$site_id.time();

        if(!isset($this->uDrive)) {
            require_once "uDrive/classes/common.php";
            $this->uDrive=new \uDrive\common($this->uCore);
        }
        if (!isset($this->uEditor)) {
            require_once "uEditor/classes/common.php";
            $this->uEditor = new \uEditor\common($this->uCore);
        }
        $uDrive_uPage_folder_id = $this->uDrive->get_module_folder_id("uPage");
        $page_data->uDrive_folder_id=$this->uDrive->create_folder($row_template_data->row_template_name,$uDrive_uPage_folder_id);
        $page_data->text_folder_id = $this->uEditor->create_folder($row_template_data->row_template_name, 0);
        $page_data->folder_id=$this->uPage->get_system_folder("templates");

        $page_data=$this->uPage->copy_page($page_data,$row_template_data->site_id,$site_id);


        $new_row_template_id=$this->uPage->get_new_row_template_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("INSERT INTO rows_templates (
            row_template_id, 
            row_template_name, 
            site_id, 
            page_id, 
            row_template_status, 
            language
            ) VALUES (
            :row_template_id, 
            :row_template_name, 
            :site_id, 
            :page_id, 
            2, 
            :language                                                                                                             
            )");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $new_row_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_name', $row_template_data->row_template_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_data->page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':language', $language,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        $source_dir='uPage/templates/row_templates/'.$row_template_data->site_id.'/'.$this->row_template_id;
        $source_file=$source_dir."/crop.jpg";
        if(file_exists($source_file)) {
            $dest_dir = 'uPage/templates/row_templates/' . $site_id . '/' . $new_row_template_id;
            $dest_file = $dest_dir. "/crop.jpg";

            if (!file_exists($dest_dir)) mkdir($dest_dir, 0755, true);

            copy($source_file,$dest_file);
        }
    }

    private function decline_row_template() {
        if(site_id!=8) die("{'status' : 'forbidden'}");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("UPDATE  
            rows_templates 
            SET
            row_template_status=0 
            WHERE
            row_template_id=:row_template_id                          
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_template_id', $this->row_template_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        if($this->action==="apply_row_template") $this->apply_row_template();
        elseif($this->action==="decline_row_template") $this->decline_row_template();
        else $this->uFunc->error(80);
        
        print "{'status':'done'}";
        exit;
    }
}
new save_row_template_bg($this);