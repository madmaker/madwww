<?php
require_once 'processors/classes/uFunc.php';
require_once "uForms/inc/common.php";
class uForms_form {
    public $uFunc;
    public $uForms;
    public $submit_btn_txt;
    private $uCore,
        $form_title,$form_descr,$result_msg;
    public $form_id;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uForms','form_builder'),$str);
    }

    public function check_data($form_id,$return=0) {
        $this->form_id=$form_id;
        if(!uString::isDigits($form_id)) {
            $form_id=uString::text2sql($form_id);
            //check if id is alias
            try {
                /** @noinspection PhpUndefinedMethodInspection */
                $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
                form_id
                FROM
                u235_forms
                WHERE
                form_alias=:form_alias AND
                site_id=:site_id AND
                status IS NULL
                ");
                $site_id=site_id;
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':form_alias', $form_id,PDO::PARAM_STR);
                /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
                /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

                /** @noinspection PhpUndefinedMethodInspection */
                if($qr=$stm->fetch(PDO::FETCH_OBJ)) $this->form_id=(int)$qr->form_id;
                else {
                    if($return) {
                        return 0;
                    }
                    else {
                    header('Location: '.u_sroot);
                    exit;
                    }
                }
            }
            catch(PDOException $e) {$this->uFunc->error('uForms form builder 10'/*.$e->getMessage()*/);}
        }
        return 1;
    }
    private function get_data($return=0) {
        //get form's attributes
        $form=$this->uForms->get_form_data($this->form_id,"form_title,form_descr,result_msg,submit_btn_txt");
        if($form) {
            $this->form_title=uString::sql2text($form->form_title,1);
            $this->form_descr=uString::sql2text($form->form_descr,1);
            $this->result_msg=uString::sql2text($form->result_msg,1);
            $this->submit_btn_txt=$form->submit_btn_txt;
        }
        else {
            if($return) {
                return 0;
            }
            else {
                //$this->uFunc->error('uForms form builder 20 -'.$this->form_id);
                header('Location: '.u_sroot);
                exit;
            }
        }
        return 1;
    }

    private function print_field($field) {
        $field->field_type=(int)$field->field_type;
        $field->obligatory=(int)$field->obligatory;
        $field->max_length=(int)$field->max_length;
        $field->min_length=(int)$field->min_length;
        $field->value_show_style=(int)$field->value_show_style;

        $html='<div 
        class="'.($field->field_type==6?'checkbox':'form-group').($field->obligatory?' has-feedback ':'').'"
        >
        <label><b>'.$field->field_label;
        if($field->obligatory&&$field->field_type==6) $html.=' *';
        $html.='</b></label><br>';

        if($field->field_type==1) {//inputtext
            $html.='<input 
            type="text" 
            '.($field->max_length?'maxlength="'.$field->max_length.'"':'').' 
            title="'.$field->field_tooltip.'" 
            placeholder="'.$field->field_placeholder.'" 
            class="form-control uTooltip uForms_form_field" 
            id="uForms_field_'.$field->field_id.'"
            
            data-max_length="'.$field->max_length.'"
            data-min_length="'.$field->min_length.'"
            data-value_type="'.$field->value_type.'"
            data-label="'.rawurlencode($field->field_label).'"
            data-placeholder="'.rawurlencode($field->field_placeholder).'"
            data-tooltip="'.rawurlencode($field->field_tooltip).'"
            data-descr="'.rawurlencode(uString::sql2text($field->field_descr,1)).'"
            data-obligatory="'.$field->obligatory.'"
            data-id="'.$field->field_id.'"
            data-type="'.$field->field_type.'"
            >';
        }
        else if($field->field_type==2) {//textarea
            $html.='<textarea 
            title="'.$field->field_tooltip.'" 
            placeholder="'.$field->field_placeholder.'" 
            class="form-control uTooltip uForms_form_field" 
            id="uForms_field_'.$field->field_id.'"
            
            data-max_length="'.$field->max_length.'"
            data-min_length="'.$field->min_length.'"
            data-value_type="'.$field->value_type.'"
            data-label="'.rawurlencode($field->field_label).'"
            data-placeholder="'.rawurlencode($field->field_placeholder).'"
            data-tooltip="'.rawurlencode($field->field_tooltip).'"
            data-descr="'.rawurlencode(uString::sql2text($field->field_descr,1)).'"
            data-obligatory="'.$field->obligatory.'"
            data-id="'.$field->field_id.'"
            data-type="'.$field->field_type.'"
            ></textarea>';
        }
        else if($field->field_type==3) {//file
            $html.='<div 
            class="file_uploader uForms_form_field uForms_field_uploader" 
            id="uForms_field_'.$field->field_id.'" 
            title="'.$field->field_tooltip.'"
            
            data-value_type="'.$field->value_type.'"
            data-label="'.rawurlencode($field->field_label).'"
            data-placeholder="'.rawurlencode($field->field_placeholder).'"
            data-tooltip="'.rawurlencode($field->field_tooltip).'"
            data-descr="'.rawurlencode(uString::sql2text($field->field_descr,1)).'"
            data-obligatory="'.$field->obligatory.'"
            data-field_uploaded="0"
            data-id="'.$field->field_id.'"
            data-type="'.$field->field_type.'"
            >'.$this->text("Choose file - btn"/*Выбрать файл*/).'</div>
            <div 
            class="file_list" 
            id="uForms_filelist_'.$field->field_id.'"></div>';
        }
        else if($field->field_type==4) {//selectbox
            $html.='<select 
            title="'.$field->field_tooltip.'" 
            class="form-control uTooltip uForms_form_field" 
            id="uForms_field_'.$field->field_id.'"
            
            data-id="'.$field->field_id.'"
            data-field_type="'.$field->field_type.'"
            >';
            $values=$this->uForms->get_selectbox_values($field->field_id);
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$val=$values[$i];$i++) $html.='<option value="'.$val->value_id.'">'.$val->label.'</option>';
            $html.='</select>';
        }
        else if($field->field_type==5) {//radio
            $values=$this->uForms->get_selectbox_values($field->field_id);
            $html.='<span 
            class="uForms_form_field"
            
            data-value_type="'.$field->value_type.'"
            data-label="'.rawurlencode($field->field_label).'"
            data-placeholder="'.rawurlencode($field->field_placeholder).'"
            data-tooltip="'.rawurlencode($field->field_tooltip).'"
            data-descr="'.rawurlencode(uString::sql2text($field->field_descr,1)).'"
            data-obligatory="'.$field->obligatory.'"
            data-id="'.$field->field_id.'"
            data-type="'.$field->field_type.'"
            >';
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$val=$values[$i];$i++) {
                $html.=($field->value_show_style?'<div class="radio">':'').
                '<label 
                class="radio-custom '.($field->value_show_style?'':'radio-inline').'" 
                data-initialize="radio"
                >
                
                <input 
                class="sr-only" 
                type="radio" 
                name="uForms_field_'.$field->field_id.'[]" 
                value="'.$val->value_id.'"
                >'.uString::sql2text($val->label,1).'</label>'.
                ($field->value_show_style?'</div>':'');
            }
            $html.='</span>';
        }
        else if($field->field_type==6) {//checkbox
            $values=$this->uForms->get_selectbox_values($field->field_id);
            $html.='<span 
            class="uForms_form_field"
            
            data-value_type="'.$field->value_type.'"
            data-label="'.rawurlencode($field->field_label).'"
            data-placeholder="'.rawurlencode($field->field_placeholder).'"
            data-tooltip="'.rawurlencode($field->field_tooltip).'"
            data-descr="'.rawurlencode(uString::sql2text($field->field_descr,1)).'"
            data-obligatory="'.$field->obligatory.'"
            data-id="'.$field->field_id.'"
            data-type="'.$field->field_type.'"
            >';
            /** @noinspection PhpUndefinedMethodInspection */
            for($i=0;$val=$values[$i];$i++) {
                $html.=($field->value_show_style?'<div class="checkbox">':'').
                '<label
                class="'.($field->value_show_style?'':' checkbox-inline ').'" 
                data-initialize="checkbox"
                >
                
                <input 
                type="checkbox" 
                class="uForms_field_'.$field->field_id.'" 
                name="uForms_field_'.$field->field_id.'[]" 
                id="uForms_field_'.$field->field_id.'-'.$val->value_id.'" 
                value="'.$val->value_id.'"
                ><span >'.uString::sql2text($val->label,1).'</span></label>'.
                ($field->value_show_style?'</div>':'');
            }
            $html.='</span>';
        }

        if($field->obligatory&&$field->field_type!=6) $html.='<span class="form-control-feedback">*</span>';
        $html.='</div>';
        if($field->field_descr!='') $html.='<p class="help-block">'.nl2br(uString::sql2text($field->field_descr,1)).'</p>';
        return $html;
    }
    private function print_col($col,$col_width) {
        $html='<div class="col col-md-'.$col_width.'">';
        if($col->col_header!='') $html.='<h4 class="col_header">'.uString::sql2text($col->col_header,1).'</h4>';
        if($col->col_descr!='') $html.='<div class="col_descr">'.nl2br(uString::sql2text($col->col_descr,1)).'</div>';

        $fields=$this->uForms->get_fields($col->col_id,"field_id,
            field_label,
            field_descr,
            field_placeholder,
            field_tooltip,
            field_type,
            obligatory,
            value_type,
            value_show_style,
            min_length,
            max_length");
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$field=$fields[$i];$i++) $html.=$this->print_field($field);
        $html.='</div>';
        return $html;
    }
    private function get_col_count($row_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uForms")->prepare("SELECT
            COUNT(col_id)
            FROM
            u235_columns
            WHERE
            row_id=:row_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':row_id', $row_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $result=$stm->fetch(PDO::FETCH_ASSOC);
            return $result['COUNT(col_id)'];
        }
        catch(PDOException $e) {$this->uFunc->error('uForms form builder 100'/*.$e->getMessage()*/);}
        return 0;
    }
    private function print_row($row) {
        $html='<div class="uForms_row">';
        if($row->row_header!='') $html.='<h3 class="row_header">'.uString::sql2text($row->row_header,1).'</h3>';
        if($row->row_descr!='') $html.='<div class="row_descr">'.nl2br(uString::sql2text($row->row_descr,1)).'</div>';

        $col_count=$this->get_col_count($row->row_id);
        if($col_count) $col_width=(int)(12/$col_count);
        else $col_width=12;

        $cols=$this->uForms->get_columns($row->row_id);
        $html.='<div class="cols row">';
        if($col_width<1) $col_width=1;
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$col=$cols[$i];$i++) {
            $html.=$this->print_col($col,$col_width);
        }
        $html.='</div>
        </div>';
        return $html;
    }
    private function print_form() {
        $html='<h2 class="form_title">'.$this->form_title.'</h2>
            <div class="uForms_form_descr">'.nl2br($this->form_descr).'</div>
        <div class="uForms_rows">';

        $q_rows=$this->uForms->get_rows($this->form_id);
        /** @noinspection PhpUndefinedMethodInspection */
        for($i=0;$row=$q_rows[$i];$i++) $html.=$this->print_row($row);

        $terms_link=$terms_link_closer="";

        $terms_page_id=(int)$this->uFunc->getConf("privacy_terms_text_id","content");
        if($terms_page_id) {
            $txt_obj=$this->uFunc->getStatic_data_by_id($terms_page_id,"page_name");
            if($txt_obj) {
                $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
                $terms_link_closer = "</a>";
            }
        }

        $html.='</div>
        <p class="help-block">'.(site_id==31?'* Merkityt kohdat pakollisia':$this->text("Obligatory field - hint"/** Поля, отмеченные звездочкой, обязательны для заполнения*/)).'</p>
        
        
        <p>'.$terms_link.(site_id==31?'Painamalla "':$this->text("privacy policy agreement notice pt1"/*Нажимая на кнопку "*/)).$this->submit_btn_txt.(site_id==31?'" hyväksyn käyttöehdot ja tietosuojaselosteen ':$this->text("privacy policy agreement notice pt2"/*", вы даете согласие на обработку своих персональных данных*/)).$terms_link_closer.'</p>
        
        <button class="btn btn-primary uForms_send_btn" id="uForms_send_btn_'.$this->form_id.'" onclick="uForms.send_form('.$this->form_id.')">'.$this->submit_btn_txt.'</button>&nbsp;
        <button class="btn btn-default uForms_reset_btn" id="uForms_reset_btn_'.$this->form_id.'" onclick="document.location=document.location" style="display:none;">'.$this->text("Reset form - btn"/*Заполнить форму заново*/).'</button>
        
        <p>&nbsp;</p>
        
        <script type="text/javascript">
        $(document).ready(function() {
            uForms.activate_uploaders('.$this->form_id.');//Send form - btn
        });
        </script>';

        return $html;
    }
    private function build_form_html($dir) {
        $file = fopen($dir.'/form.html', 'w');
        ob_start();

        /** @noinspection PhpUndefinedMethodInspection */
        echo $this->uCore->uInt_print_js("uForms","form");?>

        <div
                class="container-fluid uForms_form"
                id="uForms_form_<?=$this->form_id?>"
                data-id="<?=$this->form_id?>"
                data-rec_id="new"
        ><?=$this->print_form();?></div>

        <?
        fwrite($file, ob_get_contents());
        fclose($file);
        ob_end_clean();
    }
    public function build_form_php($dir,$form_id,$return=0) {
        $this->check_data($form_id);
        if(!$this->get_data($return)) return 0;
        if(!file_exists($dir)) mkdir($dir,0755,true);
        if(!file_exists($dir.'/form.html')) $this->build_form_html($dir);

        $php = fopen($dir.'/form.php', 'w');
        $code_width_template='<?
        include_once "uForms/inc/common.php";
        $dir="uForms/cache/'.site_id.'/'.$this->form_id.'";

        if(method_exists($this,"query")) $form=new uForms($this);
        else $form=new uForms($this->uCore);

        $this->page["page_title"]="'.str_replace('"','',stripslashes(strip_tags($this->form_title))).'";

        $this->page_content=file_get_contents($dir."/form.html");
        ?>';
        fwrite($php, $code_width_template);
        fclose($php);
        return 1;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uForms=new uForms($this->uCore);
    }
}