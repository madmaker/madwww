<?php
namespace uCat\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'uCat/classes/common.php';

class avails_edit{
    private $uCore,
        $avail_id,$avail_label,$avail_descr,$avail_type_id;
    private function check_data() {
        if(!isset($_POST['avail_id'],$_POST['avail_label'],$_POST['avail_descr'],$_POST['avail_type_id'])) $this->uFunc->error(10);
        $this->avail_id=$_POST['avail_id'];
        $this->avail_label=trim($_POST['avail_label']);
        $this->avail_descr=$_POST['avail_descr'];
        $this->avail_type_id=$_POST['avail_type_id'];

        if(!uString::isDigits($this->avail_id)) $this->uFunc->error(20);
        if(!strlen($this->avail_label)) die("{'status' : 'error', 'msg' : 'title is empty'}");
        if(!uString::isDigits($this->avail_type_id)) $this->uFunc->error(30);

        //check if this avail_type_id exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("SELECT
            avail_type_id
            FROM
            u235_items_avail_types
            WHERE
            avail_type_id=:avail_type_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_type_id', $this->avail_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(40);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
    }
    private function save_avail() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uCat")->prepare("UPDATE
            u235_items_avail_values
            SET
            avail_label=:avail_label,
            avail_descr=:avail_descr,
            avail_type_id=:avail_type_id
            WHERE
            avail_id=:avail_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $avail_label=uString::text2sql($this->avail_label);
            $avail_descr=uString::text2sql($this->avail_descr);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_type_id', $this->avail_type_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_id', $this->avail_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_label', $avail_label,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':avail_descr', $avail_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(25)) die("{'status' : 'forbidden'}");
        $this->uCat=new \uCat\common($this->uCore);

        $this->check_data();
        $this->save_avail();

        echo "{
        'status' : 'done',
        'avail_id' : '".$this->avail_id."',
        'avail_label' : '".rawurlencode($this->avail_label)."',
        'avail_descr' : '".rawurlencode($this->avail_descr)."',
        'avail_class' : '".$this->uCat->avail_type_id2class($this->avail_type_id)."',
        }";
    }
}
/*$uCat=*/new avails_edit($this);