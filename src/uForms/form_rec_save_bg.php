<?php
namespace uForms\form;

use PDO;
use PDOException;
use processors\uFunc;
use uForms;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'uForms/inc/common.php';

class rec_save_bg {
    private $uCore,$form_id,$rec_id,$field_val_id2label,$chbx_val2label;
    private $field;
    private $form_title;
    private $form_descr;
    private $result_msg;

    public function text($str) {
        return $this->uCore->text(array('uForms','form_rec_save_bg'),$str);
    }

    private function check_data() {
        if(!isset($_POST['form_id'],$_POST['rec_id'])) $this->uFunc->error(10);
        $this->form_id=$_POST['form_id'];
        $this->rec_id=$_POST['rec_id'];



        if(!uString::isDigits($this->form_id)) $this->uFunc->error(40);
        //check if this form exists
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            form_id 
            FROM 
            u235_forms
            WHERE 
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(20);
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        if($this->rec_id=='new') {
            $this->rec_id=$this->uForms->get_new_rec_id();

            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
                u235_records (
                rec_id,
                form_id,
                rec_status,
                rec_timestamp,
                refer_url,
                site_id
                ) VALUES (
                :rec_id,
                :form_id,
                'active',
                ".time().",
                :refer_url,
                :site_id
                )
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':refer_url', $_POST['url'],PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}
        }
        else {
            if (!uString::isDigits($this->rec_id)) $this->uFunc->error(60);

            //check if this rec_id is new
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
                rec_status
                FROM
                u235_records
                WHERE
                rec_id=:rec_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                $qr=$stm->fetch(PDO::FETCH_OBJ);
                if(!$qr) $this->uFunc->error(70);

                if($qr->rec_status!='new') $this->uFunc->error(80);
            }
            catch(PDOException $e) {$this->uFunc->error('90'/*.$e->getMessage()*/);}
        }
    }
    private function check_fields() {
        //get form's fields
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT DISTINCT
            field_id,
            field_type,
            field_label,
            field_placeholder,
            obligatory,
            value_type,
            min_length,
            max_length
            FROM
            u235_fields
            JOIN
            u235_columns
            ON
            u235_fields.col_id=u235_columns.col_id AND
            u235_fields.site_id=u235_columns.site_id
            JOIN
            u235_rows
            ON
            u235_columns.row_id=u235_rows.row_id AND
            u235_columns.site_id=u235_rows.site_id
            WHERE
            u235_fields.site_id=:site_id AND
            u235_rows.form_id=:form_id
            ORDER BY
            row_pos ASC,
            col_pos ASC,
            field_pos ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('100'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
        for($i=0;$field=$stm->fetch(PDO::FETCH_OBJ);) {
            if (!isset($_POST['field_' . $field->field_id])){
                die("{
                    'status' : 'error',
                    'msg' : '".$this->text('Field is not received - error msg'/*Проверьте все поля и попробуйте отправить форму заново*/)."'
                    }");

            }

            $field_val = $_POST['field_'.$field->field_id];
            $value_type=$field->value_type=(int)$field->value_type;
            $max_length=$field->max_length=(int)$field->max_length;
            $min_length=$field->min_length=(int)$field->min_length;

            $this->field[$i]=$field;
            //obligatory
            if ($field->obligatory && empty($field_val)) {
                die("{
                'status' : 'error',
                'error' : 'obligatory',
                'field_id' : '" . $field->field_id . "'
                }");
            }
            //field_type
            if ($value_type) {
                if ($value_type == 2 && !uString::isFloat($field_val) && $field_val != "") {
                    die("{
                    'status' : 'error',
                    'error' : 'value_type',
                    'msg' : '".$this->text('Price value is wrong - error msg'/*Это должна быть цена*/)."',
                    'field_id' : '" . $field->field_id . "'
                    }");
                }
                if ($value_type == 3 && !uString::isFloat($field_val) && $field_val != "") {
                    die("{
                    'status' : 'error',
                    'error' : 'value_type',
                    'msg' : '".$this->text('Number value is wrong - error msg'/*Это должно быть число*/)."',
                    'field_id' : '" . $field->field_id . "'
                    }");
                }
                if ($value_type == 4 && !uString::isDigits($field_val) && $field_val != "") {
                    die("{
                    'status' : 'error',
                    'error' : 'value_type',
                    'msg' : '".$this->text('Digital value is wrong - error msg'/*Это должны быть только Цифры*/)."',
                    'field_id' : '" . $field->field_id . "'
                    }");
                }
                if ($value_type == 5 && !uString::isEmail($field_val) && $field_val != "") {
                    die("{
                    'status' : 'error',
                    'error' : 'value_type',
                    'msg' : '".$this->text('E-mail value is wrong - error msg'/*Это должен быть адрес электронной почты*/)."',
                    'field_id' : '" . $field->field_id . "'
                    }");
                }
                if ($value_type == 6 && !uString::isPhone($field_val) && $field_val != "") {
                    die("{
                    'status' : 'error',
                    'error' : 'value_type',
                    'msg' : '".$this->text('Phone value is wrong - error msg'/*Это должен быть номер телефон в международном формате, например +79211234567*/)."',
                    'field_id' : '" . $field->field_id . "'
                    }");
                }
            }
            //max_length
            if ($max_length&&strlen($field_val) > $max_length) {
                die("{
                'status' : 'error',
                'error' : 'length',
                'msg' : '".$this->text('Field value must be shorter than - error msg. Part 1'/*Поле должно быть не длиннее */)."" . $max_length . $this->text('Field value must be shorter than - error msg. Part 2'/* символов*/)."',
                'field_id' : '" . $field->field_id . "'
                }");
            }
            //min_length
            if ($min_length&& strlen($field_val) < $min_length) {
                die("{
                'status' : 'error',
                'error' : 'length',
                'msg' : '".$this->text('Field value must be longer than - error msg. Part 1'/*Поле должно быть не короче */). $min_length .$this->text('Field value must be longer than - error msg. Part 2'/* символов*/)."',
                'field_id' : '" . $field->field_id . "'
                }");
            }
            $i++;
        }
    }
    private function insert2db() {
        //insert field's values 2 database
        for($i=0;$i<count($this->field);$i++) {
            $field=$this->field[$i];
            if($field->field_type!=3) {//not file
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uForms")->prepare("INSERT
                    IGNORE INTO
                    u235_form_results (
                    rec_id,
                    field_id,
                    field_value,
                    site_id
                    ) VALUES (
                    :rec_id,
                    :field_id,
                    :field_value,
                    :site_id
                    )
                    ");
                    $field_value=uString::text2sql($_POST['field_'.$field->field_id]);
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $field_value,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('110'/*.$e->getMessage()*/);}
            }
            else {
                $field_value=uString::text2sql($_POST['field_'.$field->field_id]);
                if ($field_value == 0) {
                    $field_value = "";
                }
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uForms")->prepare("INSERT
                    IGNORE INTO
                    u235_form_results (
                    rec_id,
                    field_id,
                    field_value,
                    site_id
                    ) VALUES (
                    :rec_id,
                    :field_id,
                    :field_value,
                    :site_id
                    )
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_value', $field_value,PDO::PARAM_STR);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
                }
                catch(PDOException $e) {$this->uFunc->error('120'/*.$e->getMessage()*/);}
            }
        }

        //update record status
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_records
            SET
            rec_status='active'
            WHERE
            rec_id=:rec_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('130'/*.$e->getMessage()*/);}

        $this->uForms->update_form_records_count($this->form_id,site_id);
        //update form msgs count
//        try {
//            /** @noinspection PhpUndefinedMethodInspection */
//            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
//            u235_forms
//            SET
//            msg_count=(SELECT
//            COUNT(rec_id)
//            FROM
//            u235_records
//            WHERE
//            form_id=:form_id AND
//            rec_status='active' AND
//            site_id=:site_id
//            )
//            WHERE
//            form_id=:form_id AND
//            site_id=:site_id
//            ");
//            $site_id=site_id;
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
//            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
//        }
//        catch(PDOException $e) {$this->uFunc->error('140'.$e->getMessage());}
    }
    private function get_form_data() {
        //get form_title, descr, result_msg
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            form_title,
            form_descr,
            result_msg
            FROM
            u235_forms
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($form=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->form_title=trim(uString::sql2text($form->form_title,1));
                $this->form_descr=trim(uString::sql2text($form->form_descr,1));
                $this->result_msg=trim(uString::sql2text($form->result_msg,1));
            }
            else {
                $this->form_title=$this->text('Form default title'/*Форма*/);
                $this->form_descr="";
                $this->result_msg=$this->text('Form has been sent - msg'/*Форма успешно отправлена*/);
            }

            if(!strlen($this->form_title)) $this->form_title=$this->text('Form default title'/*Форма*/);
            if(!strlen($this->form_descr)) $this->form_descr="";
            if(!strlen($this->result_msg)) $this->result_msg=$this->text('Form has been sent - msg'/*Форма успешно отправлена*/);

        }
        catch(PDOException $e) {$this->uFunc->error('180'/*.$e->getMessage()*/);}
    }

    private function field_val_id2label($field_id,$value_id) {
        if(!isset($this->field_val_id2label[$field_id][$value_id])) {
            if(!$query=$this->uCore->query("uForms","SELECT
            `label`
            FROM
            `u235_selectbox_values`
            WHERE
            `field_id`='".$field_id."' AND
            `value_id`='".$value_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(160);
            if(!mysqli_num_rows($query)) return false;
            $qr=$query->fetch_object();
            return $this->field_val_id2label[$field_id][$value_id]=uString::text2screen(uString::sql2text($qr->label,true));
        }
        return $this->field_val_id2label[$field_id][$value_id];
    }
    private function chbx_val2label($field_id,$value) {
        $value_ar=explode(',',$value);
        $q_value='(';
        for($i=0;$i<count($value_ar);$i++) {
            if(uString::isDigits($value_ar[$i])) {
                $q_value.="value_id=".$value_ar[$i]." OR ";
            }
        }
        $q_value.=" 1=0)";
        if(!isset($this->chbx_val2label[$field_id][$value])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
                label
                FROM
                u235_selectbox_values
                WHERE
                (".$q_value.") AND
                field_id=:field_id AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                $value_str='';
                /** @noinspection PhpUndefinedMethodInspection */
                while($qr=$stm->fetch(PDO::FETCH_OBJ)) $value_str.=uString::text2screen(uString::sql2text($qr->label,1)).'<br>';

                return $this->chbx_val2label[$field_id][$value]=$value_str;
            }
            catch(PDOException $e) {$this->uFunc->error('170'/*.$e->getMessage()*/);}
        }
        return $this->chbx_val2label[$field_id][$value];
    }
    private function send_invoice() {
        $msg="<h1>".$this->form_title."</h1>\n
        <p>".$this->form_descr."</p>\n
        <p><a href='".u_sroot."uForms/admin_form_messages/".$this->form_id."'>".$this->text('Watch messages here'/*Сообщения формы можно посмотреть здесь.*/)."</a></p>

        <p>".$this->text('Record number - label'/*Запись #*/).$this->rec_id."</p>\n";

        for($i=0;$i<count($this->field);$i++) {
            $field=$this->field[$i];
            $field_value=$_POST['field_'.$field->field_id];

            if($field->field_type==1) $field_value=uString::text2mail($field_value);
            elseif($field->field_type==2) $field_value=uString::text2mail(nl2br($field_value));
            elseif($field->field_type==3) {//file
                //get field value
                try {
                    /** @noinspection PhpUndefinedMethodInspection */
                    $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
                    field_value
                    FROM
                    u235_form_results
                    WHERE
                    rec_id=:rec_id AND
                    site_id=:site_id AND
                    field_id=:field_id
                    ");
                    $site_id=site_id;
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field->field_id,PDO::PARAM_INT);
                    /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                    /** @noinspection PhpUndefinedMethodInspection */
                    if($val=$stm->fetch(PDO::FETCH_OBJ)) {
                        $field_value='<a href="'.u_sroot.$this->uCore->mod.'/field_files/'.site_id.'/'.$this->form_id.'/'.$this->rec_id.'/'.uString::sql2text($val->field_value).'">'.uString::sql2text($val->field_value).'</a>';
                    }
                    else $field_value="";
                }
                catch(PDOException $e) {$this->uFunc->error('190'/*.$e->getMessage()*/);}
            }
            elseif($field->field_type==4) $field_value=uString::text2mail($this->field_val_id2label($field->field_id,$field_value));
            elseif($field->field_type==5||$field->field_type==6) $field_value=uString::text2mail($this->chbx_val2label($field->field_id,$field_value));
            else continue;

            if(!empty($field->field_label)) $field_title=uString::sql2text($field->field_label);
            else $field_title=uString::sql2text($field->field_placeholder);
            $msg.='<p><label>'.$field_title.'</label>: '.$field_value.'</p>'."\n";

        }
        $emails_list=$this->uFunc->getConf("invoice_emails","content");
        $emails_ar=explode(',',$emails_list);
        for($i=0;$i<count($emails_ar);$i++) {
            $email=$emails_ar[$i];
            if(uString::isEmail($email)) {
                $this->uFunc->sendMail($msg,$this->text('New form message - notification email subject'/*Новое сообщение в форме */).uString::sql2text($this->form_title),$email);
            }
        }
    }
    private function send_result_email() {
        //get result text and subject
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            email_subject,
            email_text
            FROM
            u235_forms
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $this->form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_OBJ);

            $email_text=uString::sql2text(trim($qr->email_text),1);
            $email_subject=uString::sql2text(trim($qr->email_subject),1);
        }
        catch(PDOException $e) {$this->uFunc->error('200'/*.$e->getMessage()*/);}

        //get form's fields email to what we must sent
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            field_value
            FROM
            u235_form_results
            JOIN
            u235_fields
            ON
            u235_form_results.field_id=u235_fields.field_id AND
            u235_form_results.site_id=u235_fields.site_id
            WHERE
            rec_id=:rec_id AND
            u235_fields.value_type=5 AND
            u235_fields.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':rec_id', $this->rec_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('210'/*.$e->getMessage()*/);}

        if(!empty($email_subject)&&!empty($email_subject)) {
            /** @noinspection PhpUndefinedMethodInspection PhpUndefinedVariableInspection */
            while($email=$stm->fetch(PDO::FETCH_OBJ)) {
                if(uString::isEmail($email->field_value)) {
                    /** @noinspection PhpUndefinedVariableInspection */
                    $this->uFunc->sendMail($email_text,$email_subject,$email->field_value);
                }
            }
        }
    }
    private function send2slack() {
        \processors\uFunc::slack("Новое сообщение в форме ".$this->form_title.": ".u_sroot."uForms/admin_form_messages/".$this->form_id);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uForms=new uForms($this->uCore);

        $this->check_data();
        $this->check_fields();
        $this->insert2db();
        $this->get_form_data();
        $this->send_invoice();
        $this->send_result_email();
        if(site_id==8) $this->send2slack();

        echo "{
        'status' : 'done',
        'form_id' : '".$this->form_id."',
        'result_msg' : '".rawurlencode($this->result_msg)."',
        'rec_id' : '".$this->rec_id."'
        }";
    }
}
/*$newClass=*/new rec_save_bg($this);
