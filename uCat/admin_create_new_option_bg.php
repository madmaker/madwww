<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_create_new_option_bg {
    private $option_id;
    private $uSes;
    private $uFunc;
    private $uCore;

    private function create_new_option() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            variant_options (
            option_name, 
            site_id
            ) VALUES (
            'Новая опция',
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $this->option_id=$stm=$this->uFunc->pdo("uCat")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function create_first_value() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("INSERT INTO 
            option_values (
            option_id, 
            value, 
            site_id
            ) VALUES (
            :option_id, 
            'Новое значение', 
            :site_id
            )
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

//            $this->value_id=$stm=$this->uFunc->pdo("uCat")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->create_new_option();
        $this->create_first_value();
        echo "{
        'status':'done',
        'option_id':'".$this->option_id."'
        }";
    }
}
new admin_create_new_option_bg($this);