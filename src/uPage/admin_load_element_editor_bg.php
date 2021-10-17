<?php
namespace uPage\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uPage/inc/common.php';;

class admin_load_element_editor_bg {
    private $uPage;
    private $uFunc;
    private $uSes;
    private $el_id;
    private $el_type;
    private $cols_els_id;
    private $uCore;
    private function check_data() {
        if(!isset($_POST['cols_els_id'])) $this->uFunc->error(10);
        $this->cols_els_id=$_POST['cols_els_id'];
        if(!uString::isDigits($this->cols_els_id)) $this->uFunc->error(20);
    }
    private function get_el_type() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPage")->prepare("SELECT
            el_type,
            el_id
            FROM
            u235_cols_els
            WHERE
            cols_els_id=:cols_els_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':cols_els_id', $this->cols_els_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->el_type=$qr->el_type;
                $this->el_id=$qr->el_id;
            }
            else $this->uFunc->error(30);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new \uCore();
        $this->uSes=new uSes($this->uCore);
        if(!$this->uSes->access(7)) die("{'status' : 'forbidden'}");

        $this->uFunc=new uFunc($this->uCore);
        $this->uPage=new \uPage\common($this->uCore);


        $this->check_data();
        $this->get_el_type();
        //!!!!ТИПЫ ЭЛЕМЕНТОВ ТУТ
        if($this->el_type=='page_filter') {
            require_once "uPage/elements/page_filter/common.php";
            $el=new page_filter($this->uPage);
            $el->load_el_editor($this->cols_els_id/*,$this->el_id*/);
        }
        elseif($this->el_type=='tabs') {
            require_once "uPage/elements/tabs/common.php";
            $el=new tabs($this->uPage);
            $el->load_el_editor($this->cols_els_id/*,$this->el_id*/);
        }
        else exit;
    }
}
new admin_load_element_editor_bg($this);