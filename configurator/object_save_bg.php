<?php
namespace configurator;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class object_save_bg {
    private $obj_id;
    private $uFunc;
    private $data_type;
    private $uSes;
    private $uCore;
    private function check_data() {
        if(!isset(
            $_POST["obj_id"],
            $_POST["data_type"]
        )) $this->uFunc->error(11,1);
        $this->obj_id=(int)$_POST["obj_id"];
        $this->data_type=$_POST["data_type"];
    }

    private function update_pr_text($pr_id,$pr_text,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            products
            SET
            pr_text=:pr_text
            WHERE
            pr_id=:pr_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_text', $pr_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_pr_name($pr_id,$pr_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            products
            SET
            pr_name=:pr_name
            WHERE
            pr_id=:pr_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_name', $pr_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_pr_pos($pr_id,$pr_pos,$site_id=site_id) {
        $pr_pos=(int)$pr_pos;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            products
            SET
            pr_pos=:pr_pos
            WHERE
            pr_id=:pr_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_pos', $pr_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "pr_pos"=>$pr_pos*/
        ));
        exit;
    }
    private function update_pr_price($pr_id,$pr_price,$site_id=site_id) {
        $pr_price=(int)$pr_price;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            products
            SET
            pr_price=:pr_price
            WHERE
            pr_id=:pr_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_price', $pr_price,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "pr_price"=>$pr_price*/
        ));
        exit;
    }
    private function delete_pr($pr_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("DELETE FROM  
            products
            WHERE
            pr_id=:pr_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pr_id', $pr_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    private function update_page_text($page_id,$page_text,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            pages
            SET
            page_text=:page_text
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_text', $page_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_page_name($page_id,$page_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            pages
            SET
            page_name=:page_name
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_name', $page_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_page_pos($page_id,$page_pos,$site_id=site_id) {
        $page_pos=(int)$page_pos;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            pages
            SET
            page_pos=:page_pos
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_pos', $page_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_must_choose_option($page_id,$must_choose_option,$site_id=site_id) {
        $must_choose_option=(int)$must_choose_option;
        if($must_choose_option) $must_choose_option=1;
        else $must_choose_option=0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            pages
            SET
            must_choose_option=:must_choose_option
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':must_choose_option', $must_choose_option,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('80'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function delete_page($page_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("DELETE FROM  
            pages
            WHERE
            page_id=:page_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':page_id', $page_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    private function update_sect_text($sect_id,$sect_text,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            sections
            SET
            sect_text=:sect_text
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_text', $sect_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_sect_pos($sect_id,$sect_pos,$site_id=site_id) {
        $sect_pos=(int)$sect_pos;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            sections
            SET
            sect_pos=:sect_pos
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_pos', $sect_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "sect_pos"=>$sect_pos*/
        ));
        exit;
    }
    private function update_sect_name($sect_id,$sect_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            sections
            SET
            sect_name=:sect_name
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_name', $sect_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function delete_sect($sect_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("DELETE FROM  
            sections
            WHERE
            sect_id=:sect_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':sect_id', $sect_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    private function update_opt_name($opt_id,$opt_name,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_name=:opt_name
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_name', $opt_name,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_opt_text($opt_id,$opt_text,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_text=:opt_text
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_text', $opt_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }
    private function update_opt_price($opt_id,$opt_price,$site_id=site_id) {
        $opt_price=(float)$opt_price;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_price=:opt_price
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_price', $opt_price,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_price"=>$opt_price*/
        ));
        exit;
    }
    private function update_opt_pos($opt_id,$opt_pos,$site_id=site_id) {
        $opt_pos=(int)$opt_pos;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_pos=:opt_pos
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_pos', $opt_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_pos"=>$opt_pos*/
        ));
        exit;
    }
    private function update_opt_style($opt_id,$opt_style,$site_id=site_id) {
        $opt_style=(int)$opt_style;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_style=:opt_style
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_style', $opt_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_style"=>$opt_style*/
        ));
        exit;
    }
    private function update_opt_price_type($opt_id,$opt_price_type,$site_id=site_id) {
        $opt_price_type=(int)$opt_price_type;
        if($opt_price_type<0||$opt_price_type>4) $this->uFunc->error(120,1);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_price_type=:opt_price_type
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_price_type', $opt_price_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_price_type"=>$opt_price_type*/
        ));
        exit;
    }
    private function update_opt_relations($opt_id,$site_id=site_id) {
        if(!isset(
            $_POST["opt_replacements"],
            $_POST["opt_incompatibles"],
            $_POST["opt_removables"],
            $_POST["opt_joinables"],
            $_POST["opt_required"]
        )) $this->uFunc->error(0,1);

        $opt_replacements=trim(preg_replace("/[^\d|^\s]*/", "", $_POST["opt_replacements"]));
        $opt_incompatibles=trim(preg_replace("/[^\d|^\s]*/", "", $_POST["opt_incompatibles"]));
        $opt_removables=trim(preg_replace("/[^\d|^\s]*/", "", $_POST["opt_removables"]));
        $opt_joinables=trim(preg_replace("/[^\d|^\s]*/", "", $_POST["opt_joinables"]));
        $opt_required=trim(preg_replace("/[^\d|^\s|^&]*/", "", $_POST["opt_required"]));


        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_replacements=:opt_replacements,
            opt_incompatibles=:opt_incompatibles,
            opt_removables=:opt_removables,
            opt_joinables=:opt_joinables,
            opt_required=:opt_required
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_replacements', $opt_replacements,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_incompatibles', $opt_incompatibles,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_removables', $opt_removables,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_joinables', $opt_joinables,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_required', $opt_required,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_replacements"=>$opt_replacements*/
        ));
        exit;
    }
    private function update_opt_is_default($opt_id,$opt_is_default,$site_id=site_id) {
        $opt_is_default=(int)$opt_is_default?1:0;
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("UPDATE  
            options
            SET
            opt_is_default=:opt_is_default
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_is_default', $opt_is_default,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"/*,
            "opt_is_default"=>$opt_is_default*/
        ));
        exit;
    }
    private function delete_opt($opt_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("configurator")->prepare("DELETE FROM  
            options
            WHERE
            opt_id=:opt_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':opt_id', $opt_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('140'/*.$e->getMessage()*/,1);}
        echo json_encode(array(
            "status"=>"done"
        ));
        exit;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");
        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        if($this->data_type==="pr_text") $this->update_pr_text($this->obj_id,$_POST["pr_text"]);
        elseif($this->data_type==="pr_name") $this->update_pr_name($this->obj_id,$_POST["pr_name"]);
        elseif($this->data_type==="pr_pos") $this->update_pr_pos($this->obj_id,$_POST["pr_pos"]);
        elseif($this->data_type==="pr_price") $this->update_pr_price($this->obj_id,$_POST["pr_price"]);
        elseif($this->data_type==="delete pr") $this->delete_pr($this->obj_id);

        elseif($this->data_type==="page_text") $this->update_page_text($this->obj_id,$_POST["page_text"]);
        elseif($this->data_type==="page_name") $this->update_page_name($this->obj_id,$_POST["page_name"]);
        elseif($this->data_type==="must_choose_option") $this->update_must_choose_option($this->obj_id,$_POST["must_choose_option"]);
        elseif($this->data_type==="page_pos") $this->update_page_pos($this->obj_id,$_POST["page_pos"]);
        elseif($this->data_type==="delete page") $this->delete_page($this->obj_id);

        elseif($this->data_type==="sect_text") $this->update_sect_text($this->obj_id,$_POST["sect_text"]);
        elseif($this->data_type==="sect_pos") $this->update_sect_pos($this->obj_id,$_POST["sect_pos"]);
        elseif($this->data_type==="sect_name") $this->update_sect_name($this->obj_id,$_POST["sect_name"]);
        elseif($this->data_type==="delete sect") $this->delete_sect($this->obj_id);

        elseif($this->data_type==="opt_name") $this->update_opt_name($this->obj_id,$_POST["opt_name"]);
        elseif($this->data_type==="opt_text") $this->update_opt_text($this->obj_id,$_POST["opt_text"]);
        elseif($this->data_type==="opt_price") $this->update_opt_price($this->obj_id,$_POST["opt_price"]);
        elseif($this->data_type==="opt_pos") $this->update_opt_pos($this->obj_id,$_POST["opt_pos"]);
        elseif($this->data_type==="opt_style") $this->update_opt_style($this->obj_id,$_POST["opt_style"]);
        elseif($this->data_type==="opt_price_type") $this->update_opt_price_type($this->obj_id,$_POST["opt_price_type"]);
        elseif($this->data_type==="opt_relations") $this->update_opt_relations($this->obj_id);
        elseif($this->data_type==="opt_is_default") $this->update_opt_is_default($this->obj_id,$_POST["opt_is_default"]);
        elseif($this->data_type==="delete opt") $this->delete_opt($this->obj_id);

        else $this->uFunc->error(500,1);
    }
}
new object_save_bg($this);
