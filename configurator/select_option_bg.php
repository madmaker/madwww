<?php
namespace configurator;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "configurator/classes/common.php";

class select_option_bg {
    private $pr_id;
    private $antifreeze_counter_max;
    private $antifreeze_counter;
    private $action;
    private $conf;
    private $opt_info;
    private $configurator;
    private $uFunc;
    private $uSes;
    private $opt_id;
    private $uCore;
    private function check_data() {
        if(!isset($_SESSION["configurator"])) {
            echo json_encode(array(
                "status"=>"no conf"
            ));
            exit;
        }
        $this->conf=&$_SESSION["configurator"];
        if(!isset($_POST["opt_id"],$_POST["action"])) $this->uFunc->error(10,1);
        $this->opt_id=(int)$_POST["opt_id"];
        $this->action=$_POST["action"]==="select"?1:0;

        if(!$this->opt_info=$this->configurator->get_opt_info($this->opt_id,"
        sect_id,
        opt_price_type,
        opt_price,
        opt_replacements,
        opt_incompatibles,
        opt_removables,
        opt_joinables,
        opt_required
        ")) $this->uFunc->error(20,1);

        if(!$sect_info=$this->configurator->get_sect_info($this->opt_info->sect_id,"page_id")) $this->uFunc->error(0,1);
        if(!$page_info=$this->configurator->get_page_info($sect_info->page_id,"pr_id")) $this->uFunc->error(0,1);
        $this->pr_id=(int)$page_info->pr_id;
    }

    private function select_option($opt_id,$opt_info=0) {
        if($this->antifreeze_counter++>$this->antifreeze_counter_max) $this->exit_antifreeze();

        $opt_id=(int)$opt_id;

        if(!$opt_info) {
            if(!$opt_info=$this->configurator->get_opt_info($opt_id,"
            opt_price_type,
            opt_price,
            opt_replacements,
            opt_incompatibles,
            opt_removables,
            opt_joinables,
            opt_required
            ")) return 0;
        }


        if(!isset($this->conf["options"])) {
            $this->conf["options"]=[];
            $this->conf["option_id2key"]=[];
        }
        $opt_replacements_ar=explode(" ",$opt_info->opt_replacements);
        $opt_replacements_ar_count=count($opt_replacements_ar);
        for($i=0;$i<$opt_replacements_ar_count;$i++) {
            $opt_replacements_ar[$i]=(int)$opt_replacements_ar[$i];
        }

        $opt_removables_ar=explode(" ",$opt_info->opt_removables);
        $opt_removables_ar_count=count($opt_removables_ar);
        for($i=0;$i<$opt_removables_ar_count;$i++) {
            $opt_removables_ar[$i]=(int)$opt_removables_ar[$i];
        }


        foreach ($this->conf["options"] as $key=>$value) {
            if(
                in_array($this->conf["options"][$key]["opt_id"],$opt_replacements_ar)||
                in_array($this->conf["options"][$key]["opt_id"],$opt_removables_ar)||
                $this->conf["options"][$key]["opt_id"]===$opt_id
            ) $this->unselect_option((int)$this->conf["options"][$key]["opt_id"]);
        }


        $opt_joinables_ar=explode(" ",$opt_info->opt_joinables);
        $opt_joinables_ar_count=count($opt_joinables_ar);
        for($i=0;$i<$opt_joinables_ar_count;$i++) {
            $opt_joinables_ar[$i]=(int)$opt_joinables_ar[$i];

            $this->select_option($opt_joinables_ar[$i]);
        }


        $incompatible_found=0;
        $opt_incompatibles_ar=explode(" ",$opt_info->opt_incompatibles);
        $opt_found_incompatibles_ar=[];
        $opt_incompatibles_ar_count=count($opt_incompatibles_ar);
        for($i=0;$i<$opt_incompatibles_ar_count;$i++) {
            $opt_incompatibles_ar[$i]=(int)$opt_incompatibles_ar[$i];
            if(isset($this->conf["option_id2key"][$opt_incompatibles_ar[$i]])) {
                $key=$this->conf["option_id2key"][$opt_incompatibles_ar[$i]];
                if(isset($this->conf["options"][$key])) {
                    $opt_found_incompatibles_ar[]=$opt_incompatibles_ar[$i];
                    $incompatible_found=1;
                    continue;
                }
            }
        }

        if($incompatible_found) $this->exit_incompatible($opt_found_incompatibles_ar);


        $required_found=0;
        $required_is_real=0;
        $opt_required_ar=explode(" ",$opt_info->opt_required);
        $opt_required_ar_count=count($opt_required_ar);
        for($i=0;$i<$opt_required_ar_count;$i++) {
            $opt_required_ar[$i]=(int)$opt_required_ar[$i];
            if($opt_required_ar[$i]) $required_is_real=1;
            if(isset($this->conf["option_id2key"][$opt_required_ar[$i]])) {
                $key=$this->conf["option_id2key"][$opt_required_ar[$i]];
                if(isset($this->conf["options"][$key])) {
                    $required_found=1;
                    break;
                }
            }
        }

        if(!$required_found&&$required_is_real) $this->exit_required($opt_required_ar);



        if(isset($this->conf["option_id2key"][$opt_id])) {
            $key=$this->conf["option_id2key"][$opt_id];
            if(isset($this->conf["options"][$key])) {
                $this->conf["options"][$key]["opt_info"]=$opt_info;
                return 0;
            }
        }

        $opt_id=(int)$opt_id;
        if(!isset($this->conf["options"])) $this->conf["options"]=[];
        if(!isset($this->conf["option_id2key"])) $this->conf["option_id2key"]=[];
        end($this->conf["options"]);         // move the internal pointer to the end of the array
        $key = key($this->conf["options"]);  // fetches the key of the element pointed to by the internal pointer
        $key=$key+1;
        $this->conf["options"][$key]=[];//TODO-nik87 В object_save менять эти значения Иначе такие нежданы будут
        $this->conf["options"][$key]["opt_id"]=$opt_id;
        $this->conf["options"][$key]["opt_info"]=$opt_info;
        $this->conf["option_id2key"][$opt_id]=$key;
        return 1;
    }

    private function unselect_option($opt_id) {
        $opt_id=(int)$opt_id;

        if($this->antifreeze_counter++>$this->antifreeze_counter_max) $this->exit_antifreeze();

        foreach ($this->conf["options"] as $key=>$value) {
            if(mb_strpos("&".$this->conf["options"][$key]["opt_info"]->opt_required."&","&".$this->opt_id."&")!==false) {
                $this->exit_incompatible(array($this->conf["options"][$key]["opt_id"]));
            }
        }

        if(!isset($this->conf["options"])) $this->conf["options"]=[];
        if(!isset($this->conf["option_id2key"])) $this->conf["option_id2key"]=[];

        foreach ($this->conf["options"] as $key=>$value) {
            if($this->conf["options"][$key]["opt_id"]===$opt_id) {
                unset($this->conf["option_id2key"][$opt_id]);
                unset($this->conf["options"][$key]);
                break;
            }
        }
    }

    private function exit_antifreeze() {
        $this->configurator->recalculate_options($this->pr_id);

        echo json_encode(array(
            "status"=>"freeze",
            "selected_options_ar"=>$this->conf["options"],
            "base_price"=>$this->conf["base_price"],
            "opts_price"=>$this->conf["opts_price"],
            "antifreeze_counter"=>$this->antifreeze_counter,
            "try_again_opt_id"=>$this->opt_id,
        ));
        exit;
    }
    private function exit_required($opt_required_ar) {
        $this->configurator->recalculate_options($this->pr_id);
        $opt_required_ar_count=count($opt_required_ar);

        $required_opts_ar=[];
        for($i=0;$i<$opt_required_ar_count;$i++) {
            $opt_id=(int)$opt_required_ar[$i];
            $opt_info=$this->configurator->get_opt_info($opt_id,"
            opt_id,
            opt_name,
            opt_price,
            opt_price_type,
            opt_img_timestamp,
            opt_text,
            opt_replacements,
            opt_required
            ");

            $required_opts_ar[$i]=$opt_info;
        }

        echo json_encode(array(
            "status"=>"required",
            "selected_options_ar"=>$this->conf["options"],
            "base_price"=>$this->conf["base_price"],
            "opts_price"=>$this->conf["opts_price"],
            "required_opts_ar"=>$required_opts_ar,
            "antifreeze_counter"=>$this->antifreeze_counter,
            "try_again_opt_id"=>$this->opt_id
        ));
        exit;
    }
    private function exit_incompatible($opt_found_incompatibles_ar) {
        $this->configurator->recalculate_options($this->pr_id);
        $opt_incompatibles_ar_count=count($opt_found_incompatibles_ar);

        for($i=$j=0;$i<$opt_incompatibles_ar_count;$i++) {
            $opt_id=(int)$opt_found_incompatibles_ar[$i];
            $opt_info=$this->configurator->get_opt_info($opt_id,"
            opt_id,
            opt_name,
            opt_price,
            opt_price_type,
            opt_img_timestamp,
            opt_text,
            opt_replacements,
            opt_required
            ");

            $opt_found_incompatibles_ar[$i]=$opt_info;
        }

        echo json_encode(array(
            "status"=>"incompatible",
            "selected_options_ar"=>$this->conf["options"],
            "base_price"=>$this->conf["base_price"],
            "opts_price"=>$this->conf["opts_price"],
            "opt_incompatibles_ar"=>$opt_found_incompatibles_ar,
            "antifreeze_counter"=>$this->antifreeze_counter,
            "try_again_opt_id"=>$this->opt_id
        ));
        exit;
    }

//    private function recalculate_options() {
//        $pr_info=$this->configurator->get_pr_info($this->pr_id,"pr_price");
//        $pr_price=$pr_info->pr_price;
//        $this->conf["base_price"]=$pr_price;
//        $this->conf["opts_price"]=0;
//        foreach ($this->conf["options"] as $key=>$value) {
//            $opt_price_type=(int)$this->conf["options"][$key]["opt_info"]->opt_price_type;
//            $opt_price=$this->conf["options"][$key]["opt_info"]->opt_price;
//
//            if($opt_price_type===4) {//Заменяет цену
//                $this->conf["base_price"]=$opt_price;
//            }
//            elseif($opt_price_type===3) {//Увеличивает цену
//                $this->conf["opts_price"]+=$opt_price;
//            }
//        }
//    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);

        $this->configurator=new common($this->uCore);

        $this->check_data();

        $this->antifreeze_counter=0;
        $this->antifreeze_counter_max=500;

        if($this->action) $this->select_option($this->opt_id,$this->opt_info);
        else $this->unselect_option($this->opt_id);

        $this->configurator->recalculate_options($this->pr_id);

        echo json_encode(array(
            "status"=>"done",
            "selected_options_ar"=>$this->conf["options"],
            "base_price"=>$this->conf["base_price"],
            "opts_price"=>$this->conf["opts_price"],
            "antifreeze_counter"=>$this->antifreeze_counter
        ));
    }
}
new select_option_bg($this);