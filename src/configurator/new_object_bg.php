<?php
namespace configurator;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "configurator/classes/common.php";

class new_object_bg {
    private $sect_id;
    private $page_id;
    private $pr_id;
    private $obj_type;
    private $configurator;
    private $obj_name;
    private $uFunc;
    private $uSes;
    private $uCore;
    private function check_data($site_id=site_id) {
        if(!isset($_POST["obj_type"],$_POST["obj_name"])) $this->uFunc->error(10,1);
        $this->obj_type=$_POST["obj_type"];
        $this->obj_name=trim($_POST["obj_name"]);
        if($this->obj_name==="") $this->uFunc->error(20,1);

        if($this->obj_type==="page") {
            if(!isset($_POST["pr_id"])) $this->uFunc->error(30,1);
            $this->pr_id=(int)$_POST["pr_id"];
            if(!$this->configurator->get_pr_info($this->pr_id,"pr_id",$site_id)) $this->uFunc->error(40,1);
        }
        elseif($this->obj_type==="sect") {
            if(!isset($_POST["page_id"])) $this->uFunc->error(50,1);
            $this->page_id=(int)$_POST["page_id"];
            if(!$this->configurator->get_page_info($this->page_id,"page_id",$site_id)) $this->uFunc->error(60,1);
        }
        elseif($this->obj_type==="opt") {
            if(!isset($_POST["sect_id"])) $this->uFunc->error(70,1);
            $this->sect_id=(int)$_POST["sect_id"];
            if(!$this->configurator->get_sect_info($this->sect_id,"sect_id",$site_id)) $this->uFunc->error(80,1);
        }
    }
    function __construct (&$uCore,$site_id=site_id) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->configurator=new common($this->uCore);

        $this->check_data($site_id);

        if($this->obj_type==="product") /*$product=*/$this->configurator->create_new_product($this->obj_name,$site_id);
        elseif($this->obj_type==="page") /*$page=*/$this->configurator->create_new_page($this->obj_name,$this->pr_id,$site_id);
        elseif($this->obj_type==="sect") {
            $sect=$this->configurator->create_new_sect($this->obj_name,$this->page_id,$site_id);
            echo json_encode(array(
                "status"=>"done",
                "sect_id"=>$sect["sect_id"],
                "sect_name"=>$sect["sect_name"]
            ));
            exit;
        }
        elseif($this->obj_type==="opt") {
            $opt=$this->configurator->create_new_opt($this->obj_name,$this->sect_id,$site_id);
            echo json_encode(array(
                "status"=>"done",
                "opt_id"=>$opt["opt_id"],
                "opt_name"=>$opt["opt_name"],
                "sect_id"=>$this->sect_id
            ));
            exit;
        }
        else $this->uFunc->error(90,1);

        echo json_encode(array(
           "status"=>"done"
        ));

    }
}
new new_object_bg($this);