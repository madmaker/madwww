<?php
namespace uForms\admin;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor_edit_field_bg {
    public $uFunc;
    public $uSes;
    public $uForms;
    private $uCore,$field_id,$field_pos,$field_label,$field_descr,$field_placeholder,$field_tooltip,$field_type,$value_show_style,$obligatory,$value_type,$min_length,$max_length,
    $send_result_email;
    private function check_data() {
        if(!isset($_POST['field_id'],$_POST['field_pos'],$_POST['field_label'],$_POST['field_descr'],$_POST['field_placeholder'],$_POST['field_tooltip'],$_POST['field_type'],$_POST['obligatory'],$_POST['value_type'],$_POST['min_length'],$_POST['max_length'])) $this->uFunc->error(10);

        $this->field_id=$_POST['field_id'];
        $this->field_pos=$_POST['field_pos'];
        $this->field_label=$_POST['field_label'];
        $this->field_descr=$_POST['field_descr'];
        $this->field_placeholder=$_POST['field_placeholder'];
        $this->field_tooltip=$_POST['field_tooltip'];
        $this->field_type=$_POST['field_type'];
        $this->value_show_style=$_POST['value_show_style'];
        $this->obligatory=$_POST['obligatory'];
        $this->value_type=$_POST['value_type'];
        $this->min_length=$_POST['min_length'];
        $this->max_length=$_POST['max_length'];

        if(!uString::isDigits($this->field_id)) $this->uFunc->error(20);
        if(!uString::isDigits($this->field_pos)) $this->uFunc->error(30);
        if(!uString::isDigits($this->obligatory)) $this->uFunc->error(40);
        if(!uString::isDigits($this->value_show_style)) $this->uFunc->error(50);

        if(!uString::isDigits($this->field_type)) $this->uFunc->error(60);
        if($this->field_type=='1') {
            if(!uString::isDigits($this->value_type)) $this->uFunc->error(70);
        }
        else $this->value_type=1;
        if($this->field_type=='1'||$this->field_type=='2') {
            if(!uString::isDigits($this->min_length)) $this->uFunc->error(80);
            if(!uString::isDigits($this->max_length)) $this->uFunc->error(90);
        }
        else {
            $this->min_length=0;
            $this->max_length=0;
        }
    }
    private function update_send_result() {
        if(!isset($_POST['field_id'])) $this->uFunc->error(100);
        $this->field_id=$_POST['field_id'];
        if(!uString::isDigits($this->field_id)) $this->uFunc->error(110);

        $this->send_result_email=$_POST['send_result_email']=='1'?1:0;

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_fields
            SET
            send_result_email=:send_result_email
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':send_result_email', $this->send_result_email,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}


        echo "{
            'status' : 'done',
            'send_result_email' : '".$this->send_result_email."'
            }";
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uForms=new \uForms($this->uCore);

        if(!$this->uSes->access(5)) die("{'status' : 'forbidden'}");

        if(!isset($_POST['send_result_email'])) {
            $this->check_data();

            $col_id=$this->uForms->field_id2col_id($this->field_id);
            if(!$col_id) $this->uFunc->error(130);

            $this->uForms->update_field($this->field_id,$this->field_pos,$this->field_type,$this->obligatory,$this->value_type,$this->value_show_style,$this->min_length,$this->max_length,uString::text2sql($this->field_label),uString::text2sql($this->field_descr),uString::text2sql($this->field_placeholder),uString::text2sql($this->field_tooltip),site_id);

            //clear cache
            $form_id=$this->uForms->col_id2form_id($col_id);
            $this->uForms->clear_cache($form_id);

            echo "{
            'status' : 'done',
            'col_id' : '".$col_id."',
            'field_id' : '".$this->field_id."',
            'field_pos' : '".$this->field_pos."',
            'field_type' : '".$this->field_type."',
            'obligatory' : '".$this->obligatory."',
            'value_type' : '".$this->value_type."',
            'value_show_style' : '".$this->value_show_style."',
            'min_length' : '".$this->min_length."',
            'max_length' : '".$this->max_length."',
            'field_label' : '".rawurlencode($this->field_label)."',
            'field_descr' : '".rawurlencode($this->field_descr)."',
            'field_placeholder' : '".rawurlencode($this->field_placeholder)."',
            'field_tooltip' : '".rawurlencode($this->field_tooltip)."'
            }";
        }
        else $this->update_send_result();
    }
}
new admin_form_editor_edit_field_bg ($this);