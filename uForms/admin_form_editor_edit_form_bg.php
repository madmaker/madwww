<?php
namespace uForms\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

//require_once 'lib/htmlpurifier/library/HTMLPurifier.auto.php';

require_once "processors/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor_edit_form_bg{
    public $uFunc;
    public $uSes;
    public $uForms;
    public $submit_btn_txt;
    public $purifier;
    private $uCore,$form_id,$form_title,$form_descr,$result_msg;
    private function check_data() {
        if(!isset($_POST['form_id'],$_POST['form_title'],$_POST['form_descr'],$_POST['result_msg'],$_POST['submit_btn_txt'])) $this->uFunc->error(10);
        $this->form_id=$_POST['form_id'];
        $this->form_title=$_POST['form_title'];
        $this->form_descr=$_POST['form_descr'];
        $this->result_msg=$this->purifier->purify(trim($_POST['result_msg']));
        $this->submit_btn_txt=$this->purifier->purify(trim($_POST['submit_btn_txt']));

        if(!uString::isDigits($this->form_id)) $this->uFunc->error(20);
    }
    private function update_form() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_forms
            SET
            form_title=:form_title,
            form_descr=:form_descr,
            result_msg=:result_msg,
            submit_btn_txt=:submit_btn_txt
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_title', $this->form_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_descr', $this->form_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':result_msg', $this->result_msg,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_txt', $this->submit_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);

        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);

        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_form();

        //clear cache
        $this->uForms->clear_cache($this->form_id);

        echo "{
        'status' : 'done',
        'form_title' : '".rawurlencode($this->form_title)."',
        'form_descr' : '".rawurlencode($this->form_descr)."',
        'result_msg' : '".rawurlencode($this->result_msg)."',
        'submit_btn_txt' : '".rawurlencode($this->submit_btn_txt)."'
        }";
    }
}
new admin_form_editor_edit_form_bg($this);
