<?php
namespace uCat\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class admin_create_new_option_value_bg {
    private $option_type;
    private $option_id;
    private $uSes;
    private $uFunc;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['option_id'])) $this->uFunc->error(10);
        $this->option_id=(int)$_POST['option_id'];

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT 
            option_type
            FROM 
            variant_options 
            WHERE 
            option_id=:option_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':option_id', $this->option_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$res=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(20);
            $this->option_type=(int)$res->option_type;
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    private function create_new_value() {
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
            /** @noinspection PhpUndefinedMethodInspection */
            return $value_id=$this->uFunc->pdo("uCat")->lastInsertId();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
        return 0;
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $value_id=$this->create_new_value();
        echo "{
        'status':'done',
        'option_type':'".$this->option_type."',
        'value_id':'".$value_id."'
        }";
        exit;
    }
}
new admin_create_new_option_value_bg($this);