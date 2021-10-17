<?php
namespace configurator;
use PDO;
use PDOException;
use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class common {
    private $uSes;
    private $ses_conf;
    private $uFunc;
    private $uCore;

    private function get_new_product_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            pr_id 
            FROM 
            products
            ORDER BY 
            pr_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(\PDO::FETCH_OBJ)) return $qr->pr_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 10'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private function get_new_page_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            page_id 
            FROM 
            pages
            ORDER BY 
            page_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(\PDO::FETCH_OBJ)) return $qr->page_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 20'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private function get_new_sect_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            sect_id 
            FROM 
            sections
            ORDER BY 
            sect_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(\PDO::FETCH_OBJ)) return (int)($qr->sect_id+1);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 30'/*.$e->getMessage()*/,1);}
        return 1;
    }
    private function get_new_opt_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            opt_id 
            FROM 
            options
            ORDER BY 
            opt_id DESC 
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(\PDO::FETCH_OBJ)) return (int)($qr->opt_id+1);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 40'/*.$e->getMessage()*/,1);}
        return 1;
    }

    public function create_new_product($pr_name,$site_id=site_id) {
        $pr_id=$this->get_new_product_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO products (
            pr_id, 
            site_id, 
            pr_name, 
            pr_pos
            ) VALUES (
            :pr_id,
            :site_id,
            :pr_name,
            :pr_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_name', $pr_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 50'/*.$e->getMessage()*/,1);}

        return array(
            "pr_id"=>$pr_id,
            "pr_name"=>$pr_name,
            "pr_pos"=>$pr_id
        );
    }
    public function create_new_page($page_name,$pr_id,$site_id=site_id) {
        $page_id=$this->get_new_page_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO pages (
            page_id, 
            pr_id,
            site_id, 
            page_name, 
            page_pos
            ) VALUES (
            :page_id,
            :pr_id,
            :site_id,
            :page_name,
            :page_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 60'/*.$e->getMessage()*/,1);}

        return array(
            "page_id"=>$page_id,
            "page_name"=>$page_name,
            "page_pos"=>$page_id
        );
    }
    public function create_new_sect($sect_name,$page_id,$site_id=site_id) {
        $sect_id=$this->get_new_sect_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO sections (
            sect_id, 
            page_id,
            site_id, 
            sect_name, 
            sect_pos
            ) VALUES (
            :sect_id,
            :page_id,
            :site_id,
            :sect_name,
            :sect_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_name', $sect_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 70'/*.$e->getMessage()*/,1);}

        return array(
            "sect_id"=>$sect_id,
            "sect_name"=>$sect_name,
            "sect_pos"=>$sect_id
        );
    }
    public function create_new_opt($opt_name,$sect_id,$site_id=site_id) {
        $opt_id=$this->get_new_opt_id();
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO options (
            opt_id, 
            sect_id,
            site_id, 
            opt_name, 
            opt_pos
            ) VALUES (
            :opt_id,
            :sect_id,
            :site_id,
            :opt_name,
            :opt_id        
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_name', $opt_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 80'/*.$e->getMessage()*/,1);}

        return array(
            "opt_id"=>$opt_id,
            "opt_name"=>$opt_name,
            "opt_pos"=>$opt_id
        );
    }


    public function get_pr_info($pr_id,$q_select="pr_name",$site_id=site_id) {
        if(!isset($this->get_pr_info_ar)) $this->get_pr_info_ar=[];
        if(!isset($this->get_pr_info_ar[$site_id])) $this->get_pr_info_ar[$site_id]=[];
        if(!isset($this->get_pr_info_ar[$site_id][$pr_id])) $this->get_pr_info_ar[$site_id][$pr_id]=[];
        if(!isset($this->get_pr_info_ar[$site_id][$pr_id][$q_select])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm = $this->uFunc->pdo("configurator")->prepare("SELECT 
                " . $q_select . " 
                FROM 
                products 
                WHERE
                pr_id=:pr_id AND
                site_id=:site_id
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $this->get_pr_info_ar[$site_id][$pr_id][$q_select]=$stm->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {$this->uFunc->error('configurator common 90'/*.$e->getMessage()*/, 1);}
        }
        return $this->get_pr_info_ar[$site_id][$pr_id][$q_select];
    }
    public function get_page_info($page_id,$q_select="page_name",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            ".$q_select." 
            FROM 
            pages 
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 100'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function get_sect_info($sect_id,$q_select="sect_name",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            ".$q_select." 
            FROM 
            sections 
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 110'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function get_opt_info($opt_id,$q_select="opt_name",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            ".$q_select." 
            FROM 
            options 
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 115'/*.$e->getMessage()*/,1);}
        return 0;
    }
    public function get_pages_of_product($pr_id,$q_select="page_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            ".$q_select."
            FROM 
            pages 
            WHERE 
            pr_id=:pr_id AND
            site_id=:site_id
            ORDER BY
            page_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 120'/*.$e->getMessage()*/);}
        return 0;
    }
    public function get_first_page_id_of_product($pr_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            page_id
            FROM 
            pages 
            WHERE 
            pr_id=:pr_id AND
            site_id=:site_id
            ORDER BY
            page_pos ASC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->page_id;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 130'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_sects_of_page($page_id,$q_select="sect_id,
            sect_name,
            sect_text,
            sect_style,
            sect_selection_type,
            sect_pos",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            $q_select
            FROM 
            sections 
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ORDER BY
            sect_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 140'/*.$e->getMessage()*/);}
        return 0;
    }

    public function get_opts_of_sect($sect_id,$q_select="opt_id,
            opt_name,
            opt_price,
            opt_price_type,
            opt_img_timestamp,
            opt_pos,
            opt_style,
            opt_text,
            opt_replacements,
            opt_incompatibles,
            opt_removables,
            opt_joinables,
            opt_required,
            opt_is_default",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            $q_select
            FROM 
            `options`
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ORDER BY
            opt_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 150'/*.$e->getMessage()*/);}
        return 0;
    }

    public function set_ses_pr_id($pr_id,$pr_price) {
        if(!isset($_SESSION["configurator"])) $_SESSION["configurator"]=[];
        $this->ses_conf=&$_SESSION["configurator"];

        if(isset($this->ses_conf["pr_id"])) {
            if($this->ses_conf["pr_id"]!==$pr_id) {
                unset($_SESSION["configurator"]);
                $this->set_ses_pr_id($pr_id,$pr_price);
                return 0;
            }
        }
        else {
            $this->ses_conf["pr_id"]=(int)$pr_id;
            $this->ses_conf["base_price"]=$pr_price;
            $this->ses_conf["opts_price"]=0;
        }

        return 1;
    }

    public function conf_id2data($conf_id,$q_select="conf_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            $q_select
            FROM 
            configurations
            WHERE
            conf_id=:conf_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 160'/*.$e->getMessage()*/);}
        return 0;
    }
    public function conf_id2options($conf_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            opt_id 
            FROM 
            configurations_options 
            WHERE
            conf_id=:conf_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 170'/*.$e->getMessage()*/);}
        return 0;
    }
    private function get_new_conf_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            conf_id 
            FROM 
            configurations 
            ORDER BY 
            conf_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return (int)$qr->conf_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 180'/*.$e->getMessage()*/);}
        return 1;
    }
    public function save_new_conf($pr_id,$selected_opts_ar,$site_id=site_id) {
        $conf_id=$this->get_new_conf_id();
        $timestamp=time();
        $ses_id=$this->uSes->get_val("ses_id");
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO  
            configurations (
            conf_id, 
            site_id, 
            pr_id,
            timestamp,
            ses_id
            ) VALUES (
            :conf_id, 
            :site_id, 
            :pr_id,
            :timestamp,
            :ses_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $conf_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':ses_id', $ses_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 190'/*.$e->getMessage()*/);}

        $selected_opts_ar_count=count($selected_opts_ar);
        for($i=0;$i<$selected_opts_ar_count;$i++) {
            $opt_id=$selected_opts_ar[$i];
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("configurator")->prepare("INSERT INTO 
                configurations_options (
                conf_id, 
                opt_id, 
                site_id
                ) VALUES (
                :conf_id, 
                :opt_id, 
                :site_id          
                )
                ");
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':conf_id', $conf_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {continue;/*$this->uFunc->error('configurator common 200'.$e->getMessage());*/}
        }
        return $conf_id;
    }

    public function get_configurations($q_select="conf_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("SELECT 
            $q_select 
            FROM 
            configurations 
            WHERE
            site_id=:site_id
            ORDER BY conf_id DESC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            return $stm;
        }
        catch(PDOException $e) {$this->uFunc->error('configurator common 210'/*.$e->getMessage()*/);}
        return 0;
    }

    public function recalculate_options($pr_id,$site_id=site_id) {
        $conf=&$_SESSION["configurator"];
        $pr_info=$this->get_pr_info($pr_id,"pr_price",$site_id);
        $pr_price=$pr_info->pr_price;
        $conf["base_price"]=$pr_price;
        $conf["opts_price"]=0;
        if(isset($conf["options"])) {
            foreach ($conf["options"] as $key => $value) {
                $opt_price_type = (int)$conf["options"][$key]["opt_info"]->opt_price_type;
                $opt_price = $conf["options"][$key]["opt_info"]->opt_price;

                if ($opt_price_type === 4) {//Заменяет цену
                    $conf["base_price"] = $opt_price;
                } elseif ($opt_price_type === 3) {//Увеличивает цену
                    $conf["opts_price"] += $opt_price;
                }
            }
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new \uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
    }
}
