<?php
require_once 'processors/classes/uFunc.php';
class uForms {
    public $uFunc;
    private $uCore;

    private function copy_selectbox_value($new_field_id,$val,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_value_id=$this->define_new_selectbox_value_id($dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO u235_selectbox_values (
            value_id, 
            field_id, 
            label, 
            pos, 
            site_id
            ) VALUES (
            :value_id, 
            :field_id, 
            :label, 
            :pos, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $new_value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $new_field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':label', $val->label,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':pos', $val->pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 10'/*.$e->getMessage()*/);}
    }
    private function copy_field($new_col_id,$field,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;
        $new_field_id=$this->define_new_field_id($dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO u235_fields (
            field_id, 
            col_id, 
            field_pos, 
            field_label, 
            field_descr, 
            field_placeholder, 
            field_tooltip, 
            field_type, 
            obligatory, 
            value_type, 
            value_show_style, 
            min_length, 
            max_length, 
            send_result_email, 
            site_id, 
            field_show_in_results, 
            is_checked
            ) VALUES (
            :field_id, 
            :col_id, 
            :field_pos, 
            :field_label, 
            :field_descr, 
            :field_placeholder, 
            :field_tooltip, 
            :field_type, 
            :obligatory, 
            :value_type, 
            :value_show_style, 
            :min_length, 
            :max_length, 
            :send_result_email, 
            :site_id, 
            :field_show_in_results, 
            :is_checked          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $new_field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $new_col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_pos', $field->field_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_label', $field->field_label,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_descr', $field->field_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_placeholder', $field->field_placeholder,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_tooltip', $field->field_tooltip,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_type', $field->field_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':obligatory', $field->obligatory,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_type', $field->value_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_show_style', $field->value_show_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_length', $field->min_length,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':max_length', $field->max_length,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':send_result_email', $field->send_result_email,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':send_result_email', $field->send_result_email,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_show_in_results', $field->field_show_in_results,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':is_checked', $field->is_checked,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 20'/*.$e->getMessage()*/);}

        //copy field's selectbox values
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            * 
            FROM 
            u235_selectbox_values 
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field->field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($val=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_selectbox_value($new_field_id,$val,$source_site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 30'/*.$e->getMessage()*/);}
    }

    private function copy_col($new_row_id,$col,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $new_col_id=$this->define_new_col_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO u235_columns (
            row_id, 
            col_id, 
            col_header, 
            col_descr, 
            col_pos, 
            site_id
            ) VALUES (
            :row_id, 
            :col_id, 
            :col_header, 
            :col_descr, 
            :col_pos, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $new_row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $new_col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_header', $col->col_header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_descr', $col->col_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col->col_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 40'/*.$e->getMessage()*/);}

        //copy col's fields
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            * 
            FROM 
            u235_fields 
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col->col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($field=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_field($new_col_id,$field,$field->site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 50'/*.$e->getMessage()*/);}
    }

    private function copy_row($new_form_id,$row,$source_site_id=site_id,$dest_site_id=0) {
        if(!$dest_site_id) $dest_site_id=$source_site_id;

        $new_row_id=$this->define_new_row_id($dest_site_id);
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO u235_rows (
            form_id, 
            row_id, 
            row_header, 
            row_descr, 
            row_pos, 
            site_id
            ) VALUES (
            :form_id, 
            :row_id, 
            :row_header, 
            :row_descr, 
            :row_pos, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $new_form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $new_row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_header', $row->row_header,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_descr', $row->row_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row->row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 60'/*.$e->getMessage()*/,1);}

        //copy row's cols
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            * 
            FROM 
            u235_columns
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row->row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($col=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_col($new_row_id,$col,$col->site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 70'/*.$e->getMessage()*/,1);}
    }

    public function copy_form($form_id,$source_site_id=site_id,$dest_site_id=0) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            * 
            FROM 
            u235_forms 
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 80'/*.$e->getMessage()*/,1);}

        /** @noinspection PhpUndefinedMethodInspection */
        /** @noinspection PhpUndefinedVariableInspection */
        if(!$form=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error("uForms common 85",1);

        $new_form_id=$this->define_new_form_id($dest_site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO u235_forms (
            form_id, 
            form_title, 
            form_descr, 
            submit_btn_txt, 
            result_msg, 
            email_subject, 
            email_text, 
            msg_count, 
            status, 
            timestamp, 
            site_id
            ) VALUES (
            :form_id, 
            :form_title, 
            :form_descr, 
            :submit_btn_txt, 
            :result_msg, 
            :email_subject, 
            :email_text, 
            :msg_count, 
            :status, 
            :timestamp, 
            :site_id          
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $new_form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_title', $form->form_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_descr', $form->form_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_txt', $form->submit_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':result_msg', $form->result_msg,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email_subject', $form->email_subject,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':email_text', $form->email_text,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':msg_count', $form->msg_count,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':status', $form->status,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':timestamp', $form->timestamp,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $dest_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 90'/*.$e->getMessage()*/,1);}


        //Copy form rows
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            * 
            FROM 
            u235_rows 
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $source_site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($row=$stm->fetch(PDO::FETCH_OBJ)) {
                $this->copy_row($new_form_id,$row,$row->site_id,$dest_site_id);
            }
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 100'/*.$e->getMessage()*/,1);}

        return $new_form_id;
    }


    public function row_id2form_id($row_id) {
        if(!uString::isDigits($row_id)) $this->uFunc->error('uForms common 110');
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            form_id
            FROM
            u235_rows
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 120'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uForms common 130');

        return $qr->form_id;
    }
    public function col_id2form_id($col_id) {
        if(!uString::isDigits($col_id)) $this->uFunc->error('uForms common 140');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            row_id
            FROM
            u235_columns
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 150'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uForms common 160 ('.$col_id.')');
        $row_id=$qr->row_id;

        return $this->row_id2form_id($row_id);
    }
    public function field_id2form_id($field_id) {
        if(!uString::isDigits($field_id)) $this->uFunc->error('uForms common 170');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            col_id
            FROM
            u235_fields
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 180'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uForms common 190');
        $col_id=$qr->col_id;

        return $this->col_id2form_id($col_id);
    }
    public function field_id2col_id($field_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            col_id
            FROM
            u235_fields
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->col_id;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 200'/*.$e->getMessage()*/);}
        return 0;
    }
    public function value_id2form_id($value_id) {
        if(!uString::isDigits($value_id)) $this->uFunc->error('uForms common 210');

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            field_id
            FROM
            u235_selectbox_values
            WHERE
            value_id=:value_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_id', $value_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 220'/*.$e->getMessage()*/);}


        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(!$qr=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error('uForms common 230');
        $field_id=$qr->field_id;

        return $this->field_id2form_id($field_id);
    }
    private $selectbox_values_ar;
    public function selectbox_value_id2label($value_ids/*ID значений, разделенные запятой*/,$site_id=site_id) {
        $value_ar=explode(',',$value_ids);
        $q_value='(';
        for($i=0;$i<count($value_ar);$i++) {
            if(uString::isDigits($value_ar[$i])) {
                $q_value.="value_id=".$value_ar[$i]." OR ";
            }
        }
        $q_value.=" 1=0)";

        if(!isset($this->selectbox_values_ar[$site_id][$value_ids])) {
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
                label 
                FROM 
                u235_selectbox_values 
                WHERE
                ".$q_value." AND
                site_id=:site_id
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            }
            catch(PDOException $e) {$this->uFunc->error('uForms common 235'/*.$e->getMessage()*/);}

            $value_str='';
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */
            while($qr=$stm->fetch(PDO::FETCH_OBJ)) $value_str.=uString::text2screen(uString::sql2text($qr->label,1)).'<br>';

            if(isset($value_str)) $this->selectbox_values_ar[$site_id][$value_ids]=$value_str;
            else $this->selectbox_values_ar[$site_id][$value_ids]="n/a";
        }
        return $this->selectbox_values_ar[$site_id][$value_ids];
    }
    public function get_new_rec_id() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            rec_id
            FROM
            u235_records
            WHERE
            site_id=:site_id
            ORDER BY
            rec_id DESC
            LIMIT 1
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$stm->fetch(PDO::FETCH_OBJ);
            if($qr) return $qr->rec_id+1;
            return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 240'/*.$e->getMessage()*/);}
        return 1;
    }
    public function clear_cache($form_id) {
        if(!uString::isDigits($form_id)) $this->uFunc->error('uForms common 250');
        uFunc::rmdir('uForms/cache/'.site_id.'/'.$form_id);
    }

    public function update_form_records_count($form_id,$site_id=site_id) {
        //update form msgs count
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_forms
            SET
            msg_count=(SELECT
            COUNT(rec_id)
            FROM
            u235_records
            WHERE
            form_id=:form_id AND
            rec_status='active' AND
            site_id=:site_id
            )
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 260'/*.$e->getMessage()*/);}
    }

    public function get_form_data($form_id,$q_data="form_title",$site_id=site_id) {
        //get form's attributes
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            ".$q_data."
            FROM
            u235_forms
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 270'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        return $stm->fetch(PDO::FETCH_OBJ);
    }
    public function get_rows($form_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            row_id,
            row_header,
            row_descr,
            row_pos
            FROM
            u235_rows
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ORDER BY
            row_pos
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $row_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 280'/*.$e->getMessage()*/);}
        /** @noinspection PhpUndefinedVariableInspection */
        return $row_ar;
    }
    public function get_columns($row_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            col_id,
            col_header,
            col_descr,
            col_pos
            FROM
            u235_columns
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ORDER BY
            col_pos
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $cols_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 290'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $cols_ar;
    }
    public function get_fields($col_id,$q_data="field_id",$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            ".$q_data."
            FROM
            u235_fields
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ORDER BY
            field_pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection */
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0; $fields_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 300'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $fields_ar;
    }
    public function get_selectbox_values($field_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            value_id,
            label,
            pos
            FROM
            u235_selectbox_values
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ORDER BY
            pos ASC
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $values_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 310'/*.$e->getMessage()*/);}

        /** @noinspection PhpUndefinedVariableInspection */
        return $values_ar;
    }

    public function define_new_form_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT 
            form_id 
            FROM 
            u235_forms 
            WHERE 
            site_id=:site_id
            ORDER BY 
            form_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();


            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->form_id+1;
            else return 1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 320'/*.$e->getMessage()*/);}
        return 0;
    }
    public function define_new_row_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            row_id
            FROM
            u235_rows
            WHERE
            site_id=:site_id
            ORDER BY
            row_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->row_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 330'/*.$e->getMessage()*/);}
        return 1;
    }
    public function define_new_col_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            col_id
            FROM
            u235_columns
            WHERE
            site_id=:site_id
            ORDER BY
            col_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->col_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 340'/*.$e->getMessage()*/);}
        return 1;
    }
    public function define_new_field_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            field_id
            FROM
            u235_fields
            WHERE
            site_id=:site_id
            ORDER BY
            field_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->field_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 350'/*.$e->getMessage()*/);}
        return 1;
    }
    public function define_new_selectbox_value_id($site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            value_id
            FROM
            u235_selectbox_values
            WHERE
            site_id=:site_id
            ORDER BY
            value_id DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->value_id+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 355'/*.$e->getMessage()*/);}
        return 1;
    }
    public function define_new_row_pos($form_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            row_pos
            FROM
            u235_rows
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            ORDER BY row_pos DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->row_pos+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 360'/*.$e->getMessage()*/);}
        return 0;
    }
    public function define_new_col_pos($row_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            col_pos
            FROM
            u235_columns
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ORDER BY col_pos DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->col_pos+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 370'/*.$e->getMessage()*/);}
        return 0;
    }
    public function define_new_field_pos($col_id,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            field_pos
            FROM
            u235_fields
            WHERE
            col_id=:col_id AND
            site_id=:site_id
            ORDER BY field_pos DESC
            LIMIT 1
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if($qr=$stm->fetch(PDO::FETCH_OBJ)) return $qr->field_pos+1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 380'/*.$e->getMessage()*/);}
        return 0;
    }

    public function text($script,$str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uForms',$script),$str);
    }

    public function create_form($form_title,$site_id=site_id) {
        $form_id=$this->define_new_form_id($site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
            u235_forms (
            form_id,
            form_title,
            submit_btn_txt,
            site_id
            ) VALUES (
            :form_id,
            :form_title,
            :submit_btn_txt,
            :site_id
            )
            ");
            $submit_btn_txt=$this->text("form_builder","Send form - btn");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_title', $form_title,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':submit_btn_txt', $submit_btn_txt,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 390'/*.$e->getMessage()*/);}

        return $form_id;
    }
    public function create_row($form_id,$site_id=site_id) {
        $row_id=$this->define_new_row_id($site_id);
        $row_pos=$this->define_new_row_pos($form_id,$site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
            u235_rows (
            form_id,
            row_id,
            row_pos,
            site_id
            ) VALUES (
            :form_id,
            :row_id,
            :row_pos,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_id', $form_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_pos', $row_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $result=array();
            $result["row_id"]=$row_id;
            $result["row_pos"]=$row_pos;

            return $result;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 400'/*.$e->getMessage()*/);}
        return 0;
    }
    public function create_col($row_id,$site_id=site_id) {
        $col_pos=$this->define_new_col_pos($row_id,$site_id);
        $col_id=$this->define_new_col_id($site_id);

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
            u235_columns (
            row_id,
            col_id,
            col_pos,
            site_id
            ) VALUES (
            :row_id,
            :col_id,
            :col_pos,
            :site_id
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_pos', $col_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $result=array();
            $result["col_id"]=$col_id;
            $result["col_pos"]=$col_pos;

            return $result;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 410'/*.$e->getMessage()*/);}
        return 0;
    }
    public function create_field($col_id,$site_id=site_id) {
        $field_pos=$this->define_new_field_pos($col_id,$site_id);
//        $field_id=$this->define_new_field_id($site_id);

        try {
            $form_id=$this->col_id2form_id($col_id);
            /** @noinspection PhpUndefinedMethodInspection */
            $update_is_checked = $this->uFunc->pdo("uForms")->prepare("SELECT DISTINCT
            rec_id
            FROM
            u235_records
            WHERE
            site_id=:site_id and
            form_id=:form_id
            order by rec_id desc
            limit 1
            ");

            /** @noinspection PhpUndefinedMethodInspection */$update_is_checked->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$update_is_checked->bindParam(':form_id', $form_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$update_is_checked->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            if($rec_id = $update_is_checked->fetch(PDO::FETCH_OBJ)) {
                $is_checked_val = $rec_id->rec_id + 1;
            }
            else $is_checked_val=1;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 420'/*.$e->getMessage()*/);}

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("INSERT INTO
            u235_fields (
            col_id,
            field_pos,
            site_id,
            is_checked
            ) VALUES (
            :col_id,
            :field_pos,
            :site_id,
            :is_checked
            )
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':col_id', $col_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_pos', $field_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            /** @noinspection PhpUndefinedVariableInspection */$stm->bindParam(':is_checked', $is_checked_val,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */$field_id=$this->uFunc->pdo("uForms")->LastInsertId();

            $result=array();
            $result["field_id"]=$field_id;
            $result["field_pos"]=$field_pos;

            return $result;
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 425'/*.$e->getMessage()*/);}
        return 0;
    }

    public function update_field($field_id,$field_pos,$field_type,$obligatory,$value_type,$value_show_style,$min_length,$max_length,$field_label,$field_descr,$field_placeholder,$field_tooltip,$site_id=site_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("UPDATE
            u235_fields
            SET
            field_pos=:field_pos,
            field_type=:field_type,
            obligatory=:obligatory,
            value_type=:value_type,
            value_show_style=:value_show_style,
            min_length=:min_length,
            max_length=:max_length,
            field_label=:field_label,
            field_descr=:field_descr,
            field_placeholder=:field_placeholder,
            field_tooltip=:field_tooltip
            WHERE
            field_id=:field_id AND
            site_id=:site_id
            ");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_id', $field_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_pos', $field_pos,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_type', $field_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':obligatory', $obligatory,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_type', $value_type,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':value_show_style', $value_show_style,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':min_length', $min_length,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':max_length', $max_length,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_label', $field_label,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_descr', $field_descr,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_placeholder', $field_placeholder,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':field_tooltip', $field_tooltip,PDO::PARAM_STR);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('uForms common 430'/*.$e->getMessage()*/);}
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);
    }
}
