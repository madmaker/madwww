<?php
namespace uForms\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uForms/inc/common.php';

class form_edit {
    private $uCore,$form_id,$field_id;
    private function check_data() {
        if(!isset($_POST['form_id'])) $this->uFunc->error(10);
        $this->form_id=&$_POST['form_id'];

        if(!uString::isDigits($this->form_id)) $this->uFunc->error(20);
    }
    private function save_form_fields() {
        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        $field=&$_POST['field'];
        $value=&$_POST['value'];

        if($field=='title') {
            $field='form_title';
            $value=uString::text2sql($value);
        }
        elseif($field=='form_descr') $value=uString::text2sql($value);
        elseif($field=='email_text') $value=uString::text2sql($value);
        elseif($field=='email_subject') $value=uString::text2sql($value);
        else	 $this->uFunc->error(30);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_forms
            SET
            ".$field."=:".$field."
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':'.$field, $value,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        echo 'done';
    }
    private function delete_rec() {
        if(!$this->uSes->access(6)) die("{'status' : 'forbidden'}");

        $rec_id=$_POST['rec_id'];
        if(!uString::isDigits($rec_id)) $this->uFunc->error(50);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("DELETE
            FROM
            u235_records
            WHERE
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        $this->uFunc->rmdir('uForms/field_files/'.site_id.'/'.$this->form_id.'/'.$rec_id);

        $this->uForms->update_form_records_count($this->form_id,site_id);

        echo '{
        "status":"done",
        "rec_id":"'.$rec_id.'"
        }';
    }
    private function update_status_column() {
        $field_id=$_POST['field_id'];
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_fields
            SET
            field_show_in_results = (1-field_show_in_results)
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}

        echo '{
        "status":"done",
        "field_id":"'.$field_id.'"
        }';
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);

        if(!isset($_POST['field_id'])) {
            $this->check_data();
        }

        if(isset($_POST['field'],$_POST['value'])) $this->save_form_fields();
        elseif(isset($_POST['rec_id'],$_POST['rec_delete'])) $this->delete_rec();
        elseif(isset($_POST['field_id'])) {
            $this->update_status_column();
        }
        else die("{'status' : 'forbidden'}");

        //clear cache
        if(!isset($_POST['field_id'])) {
            $this->uForms->clear_cache($this->form_id);
        }
    }
}
/*$uForms=*/new form_edit($this);
