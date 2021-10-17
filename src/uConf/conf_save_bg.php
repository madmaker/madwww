<?php
namespace uConf;

use PDO;
use PDOException;
use processors\uFunc;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";

class conf_save_bg {
    private $uCore,$mod,
        $field_id,$value;
    private function checkData() {
        if(!isset($_POST['value'])) return false;
        $this->value=trim($_POST['value']);
        return true;
    }
    private function clear_caches() {
        if($this->field_id=='900') {
            uFunc::rmdir('uEvents/cache/event/'.site_id);
        }
        if($this->field_id=='845') {
            uFunc::rmdir('uForms/cache/'.site_id);
        }
    }
    private function save_field() {
        $this->field_id=$_POST['field_id'];
        if(!uString::isDigits($this->field_id)) $this->uFunc->error(10);
        //get field's parameters
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("pages")->prepare("SELECT
            min_length,
            max_length,
            field_type,
            `mod`
            FROM
            u235_conf
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
    $site_id=site_id;
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $this->field_id,PDO::PARAM_INT);
    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$field=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(30);

        $this->mod=$field->mod;

        if(
            $field->mod=='uSup'&&$this->uSes->access(200)||
            $field->mod=='content'&&$this->uSes->access(100)||
            $field->mod=='configurator'&&$this->uSes->access(100)||
            $field->mod=='uForms'&&$this->uSes->access(5)||
            $field->mod=='uCat'&&$this->uSes->access(25)||
            $field->mod=='uViblog'&&$this->uSes->access(4)||
            $field->mod=='uPeople'&&$this->uSes->access(10)||
            $field->mod=='uKnowbase'&&$this->uSes->access(200)||
            $field->mod=='uEvents'&&$this->uSes->access(300)
        ) true;
        else die("{'status' : 'forbidden'}");

        if($field->field_type=='1'||$field->field_type=='9') {//text line OR textarea
            if($field->min_length!='0') {
                if(mb_strlen($this->value)<$field->min_length) {
                    die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':min_length'
                    }");
                }
            }
            if($field->max_length!='0') {
                $cur_length=mb_strlen($this->value);
                if($cur_length>$field->max_length) {
                    die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'max_length',
                    'max_length':'".$field->max_length."',
                    'cur_length':'".$cur_length."'
                    }");
                }
            }
            $value=uString::text2sql($this->value);
        }
        elseif($field->field_type=='2') {//unsigned int
            if(!uString::isDigits($this->value)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'field_type'
                    }");
            if($field->min_length!='0') {
                if($this->value<$field->min_length) {
                    die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':min_length',
                    'min_length':'".$field->max_length."'
                    }");
                }
            }
            if($field->max_length!='0') {
                if($this->value>$field->max_length) {
                    die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':max_length',
                    'max_length':'".$field->max_length."'
                    }");
                }
            }
            $value=$this->value;
        }
        elseif($field->field_type=='3') {//email
            if(!uString::isEmail($this->value)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'field_type'
                    }");
            $value=$this->value;
        }
        elseif($field->field_type=='4') {//switcher
            $value=$this->value;
            if($value!='1') $value='0';
        }
        elseif($field->field_type=='5') {//password
            $value=uString::text2sql($this->value);
        }
        elseif($field->field_type=='6') {//password
            $value='';
            $value_ar=explode(',',$this->value);
            for($i=0;$i<count($value_ar);$i++) {
                $val=trim($value_ar[$i]);
                if(uString::isEmail($val)) $value.=$val.' ';
            }
            $this->value=$value;
        }
        elseif($field->field_type=='7') {//password
            $value='';
            $value_ar=explode(',',$this->value);
            for($i=0;$i<count($value_ar);$i++) {
                $val=trim($value_ar[$i]);
                $value.=$val.',';
            }
            $this->value=$value;
            $value=uString::text2sql($value);
        }
        elseif($field->field_type=='8') {//domain name
            if(!uString::isDomain_name($this->value)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'field_type'
                    }");
            $value=$this->value;
        }
        elseif($field->field_type=='10') {//url
            if(!uString::isUrl_rus($this->value)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'field_type'
                    }");
            $value=$this->value;
        }
        elseif($field->field_type=='11') {//art url
            if(!uString::isFilename_rus($this->value)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'field_type'
                    }");
            //check if page_id exists
            if(!$query=$this->uCore->query("pages","SELECT
            `page_id`
            FROM
            `u235_pages_html`
            WHERE
            `page_name`='".$this->value."' AND
            `site_id`='".site_id."'
            ")) $this->uFunc->error(40);
            if(!mysqli_num_rows($query)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'art_is_not_found'
                    }");
            $value=$this->value;
        }
        elseif($field->field_type=='12') {//selectbox
            if(!uString::isDigits($this->value)) $this->uFunc->error(60);
            //check if option_id exists
            if(!$query=$this->uCore->query("pages","SELECT
            `option_val`
            FROM
            `u235_conf_selectbox`
            WHERE
            `option_id`='".$this->value."' AND
            `field_id`='".$this->field_id."'
            ")) $this->uFunc->error(70);
            if(!mysqli_num_rows($query)) die("{
                    'status':'error',
                    'field_type':'".$field->field_type."',
                    'msg':'option val is not found'
                    }");
            $val=$query->fetch_object();
            $value=$val->option_val;
        }
        else $this->uFunc->error(80);

        if(!$this->uCore->query("pages","UPDATE
        `u235_conf`
        SET
        `value`='".$value."'
        WHERE
        `field_id`='".$this->field_id."' AND
        `site_id`='".site_id."'
        ")) $this->uFunc->error(90);

        if($field->field_type=='5') $this->value='******';

        $this->clear_caches();

        echo "{'status' : 'done',
        'field_id' : '".$this->field_id."',
        'field_type':'".$field->field_type."',
        'value' : '".rawurlencode($this->value)."'
        }";
        exit;
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        //check data
        if(!$this->checkData()) die("{'status' : 'forbidden'}");

        $this->save_field();
    }
}
new conf_save_bg($this);
