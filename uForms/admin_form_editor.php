<?php
namespace uForms\admin;
use processors\uFunc;
use uForms;
use uSes;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uForms/inc/common.php";

class admin_form_editor {
    public $uFunc;
    public $uSes;
    public $uForms;
    public $submit_btn_txt;
    private $uCore;
    public $form_id,$form_title,$form_descr,$result_msg,$email_text,$email_subject;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uForms','admin_form_editor'),$str);
    }

    private function error($err_code) {
        header('Location: '.u_sroot.'uForms/admin_forms');
        die();
//        echo $err_code."err";
//        die($err_code);
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->error(10);
        if(!uString::isDigits($this->uCore->url_prop[1])) $this->error(20);

        $this->form_id=(int)$this->uCore->url_prop[1];
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if(!$this->uSes->access(5)) {
            header('Location: '.u_sroot.'uAuth/enter');
            exit;
        }

        $this->uForms=new uForms($this->uCore);

        $this->check_data();
        /** @noinspection PhpUndefinedVariableInspection PhpUndefinedMethodInspection */
        if(!$form=$this->uForms->get_form_data($this->form_id,"form_title,form_descr,result_msg,email_subject,email_text,submit_btn_txt")) $this->error(40);
        $this->form_title=uString::sql2text($form->form_title,1);
        $this->form_descr=uString::sql2text($form->form_descr,true);
        $this->submit_btn_txt=$form->submit_btn_txt;
        $this->result_msg=uString::sql2text($form->result_msg,true);
        $this->email_text=trim(uString::sql2text($form->email_text,true));
        $this->email_subject=trim(uString::sql2text($form->email_subject,1));

        $this->uCore->page['page_title']=$this->text("Page name"/*Конструктор формы*/);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->uCore->uInt_js('uForms','admin_form_editor');
    }
}
$uForms=new admin_form_editor($this);

//tinymce
$this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');
$this->uFunc->incJs(u_sroot.'js/bootstrap_plugins/PopConfirm/jquery.popconfirm.js');
$this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.js');
$this->uFunc->incJs(u_sroot.'uForms/js/admin_form_editor.min.js');
$this->uFunc->incCss(u_sroot.'uForms/css/uForms.min.css');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uForms/uForms.css');

ob_start();?>

<div class="container uForms_editor"></div>

    <div class="container">
        <h3><?=$uForms->text("Notification email block header")?></h3>
        <label><?=$uForms->text("Emails to send notification - field label"/*На какие email отправлять?*/)?></label>
        <div id="uForms_emails_fields_list"></div>
        <span class="help-block"><?=$uForms->text("Email to send notification - descr"/*В форме должны быть поля с разрешенными значениями <b>Email</b> - они отображаются здесь.*/)?></span>
        <div class="form-group">
            <label><?=$uForms->text("Email subject - input label"/*Тема email сообщения*/)?></label>
            <div class="input-group">
                <input id="uForms_email_subject" class="form-control" placeholder="<?=$uForms->text("Email subject - input placeholder"/*Тема сообщения email*/)?>" value="<?=addslashes($uForms->email_subject)?>">
                <span class="input-group-btn">
                    <button class="btn btn-primary uTooltip" onclick="uForms.email_subject_save()" title="<?=$uForms->text("Save subject - btn txt"/*Сохранить тему сообщения*/)?>" type="button"><span class="glyphicon glyphicon-ok"></span></button>
                </span>
            </div><!-- /input-group -->
            <p class="help-block"><?=$uForms->text("Subject - descr"/*Если тема или текст не заполнены, то сообщение не будет отправляться!*/)?></p>
        </div>
        <div class="form-group">
            <label><?=$uForms->text("Email text - input label"/*Текст сообщения*/)?></label>
            <p class="text-muted"><span><?=$uForms->text("Email text - descr"/*Все ссылки здесь должны быть абсолютными, то есть начинаться с http://сайт.com. Если будут относительные, то в письме они будут битыми.*/)?></span></p>
            <div id="uForms_email_text_editor"><?=empty($uForms->email_text)?'<p>&nbsp;</p>':$uForms->email_text?></div>
        </div>
</div>

<script type="text/javascript">
    if(typeof uForms==="undefined") {
        uForms = {};

        uForms.row_id = [];
        uForms.row_header = [];
        uForms.row_descr = [];
        uForms.row_pos = [];
        uForms.row_id2ind = [];
        uForms.row_cols_ids = [];

        uForms.col_id = [];
        uForms.col_row_id = [];
        uForms.col_header = [];
        uForms.col_descr = [];
        uForms.col_pos = [];
        uForms.col_id2ind = [];
        uForms.col_fields_ids = [];

        uForms.field_id = [];
        uForms.field_col_id = [];
        uForms.field_pos = [];
        uForms.field_label = [];
        uForms.field_descr = [];
        uForms.field_placeholder = [];
        uForms.field_tooltip = [];
        uForms.field_type = [];
        uForms.obligatory = [];
        uForms.value_type = [];
        uForms.value_show_style = [];
        uForms.min_length = [];
        uForms.max_length = [];
        uForms.send_result_email = [];
        uForms.field_values_ids = [];
        uForms.field_id2ind = [];
        uForms.value_id = [];
        uForms.value_value = [];
        uForms.value_label = [];
        uForms.value_pos = [];
        uForms.value_field_id = [];
        uForms.value_id2ind = [];

        uForms.min_col_pos = [];
        uForms.max_col_pos = [];

        uForms.min_field_pos = [];
        uForms.max_field_pos = [];

        uForms.min_value_pos = [];
        uForms.max_value_pos = [];
    }

    uForms.form_id=<?=$uForms->form_id?>;
    uForms.form_title="<?=rawurlencode($uForms->form_title)?>";
    uForms.form_descr="<?=rawurlencode($uForms->form_descr)?>";
    uForms.submit_btn_txt="<?=rawurlencode($uForms->submit_btn_txt)?>";
    uForms.result_msg="<?=rawurlencode($uForms->result_msg)?>";

    var i=0;
    <?$rows=$uForms->uForms->get_rows($uForms->form_id);
    for($i=0;$row=$rows[$i];$i++) {?>
    i=<?=$i?>;
    uForms.row_id[i]=<?=$row->row_id?>;
    uForms.row_header[i]="<?=rawurlencode(uString::sql2text($row->row_header))?>";
    uForms.row_descr[i]="<?=rawurlencode(uString::sql2text($row->row_descr,1))?>";
    uForms.row_pos[i]=<?=$row->row_pos?>;
    if(uForms.min_row_pos>uForms.row_pos[i]) uForms.min_row_pos=uForms.row_pos[i];
    if(uForms.max_row_pos<uForms.row_pos[i]) uForms.max_row_pos=uForms.row_pos[i];
    uForms.row_id2ind[<?=$row->row_id?>]=i;
    uForms.row_cols_ids[i]=[];

    uForms.min_col_pos[i]=11;
    uForms.max_col_pos[i]=0;
    var j=0;
    <?$cols=$uForms->uForms->get_columns($row->row_id);
        for($j=0;$col=$cols[$j];$j++) {?>
        j=<?=$j?>;
        uForms.row_cols_ids[i][j]=<?=$col->col_id?>;
        <?}

        for($j=0;$col=$cols[$j];$j++) {?>
        j=uForms.col_id.length;

        uForms.col_id[j]=<?=$col->col_id?>;
        uForms.col_row_id[j]=<?=$row->row_id?>;
        uForms.col_header[j]="<?=rawurlencode(uString::sql2text($col->col_header))?>";
        uForms.col_descr[j]="<?=rawurlencode(uString::sql2text($col->col_descr,true))?>";
        uForms.col_pos[j]=<?=$col->col_pos?>;
        if(uForms.min_col_pos[i]>uForms.col_pos[j]) uForms.min_col_pos[i]=uForms.col_pos[j];
        if(uForms.max_col_pos[i]<uForms.col_pos[j]) uForms.max_col_pos[i]=uForms.col_pos[j];
        uForms.col_id2ind[<?=$col->col_id?>]=j;
        uForms.col_fields_ids[j]=[];
        var k=0;

        uForms.min_field_pos[j]=50;
        uForms.max_field_pos[j]=0;
        <?$fields=$uForms->uForms->get_fields($col->col_id,"field_id,
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
            send_result_email");?>

            <?for($k=0;$field=$fields[$k];$k++) {?>
            k=<?=$k?>;
            uForms.col_fields_ids[j][k]=<?=$field->field_id?>;
            <?}
            for($k=0;$field=$fields[$k];$k++) {?>
            k=uForms.field_id.length;

            uForms.field_id[k]=<?=$field->field_id?>;
            uForms.field_col_id[k]=<?=$col->col_id?>;
            uForms.field_pos[k]=<?=$field->field_pos?>;
            if(uForms.min_field_pos[j]>uForms.field_pos[k]) uForms.min_field_pos[j]=uForms.field_pos[k];
            if(uForms.max_field_pos[j]<uForms.field_pos[k]) uForms.max_field_pos[j]=uForms.field_pos[k];
            uForms.field_label[k]="<?=rawurlencode($field->field_label)?>";
            uForms.field_descr[k]="<?=rawurlencode(uString::sql2text($field->field_descr,1))?>";
            uForms.field_placeholder[k]="<?=rawurlencode($field->field_placeholder)?>";
            uForms.field_tooltip[k]="<?=rawurlencode($field->field_tooltip)?>";
            uForms.field_type[k]=<?=$field->field_type?>;
            uForms.obligatory[k]=<?=$field->obligatory=='1'?'true':'false'?>;
            uForms.value_type[k]=<?=$field->value_type?>;
            uForms.value_show_style[k]=<?=$field->value_show_style?>;
            uForms.min_length[k]=<?=$field->min_length?>;
            uForms.max_length[k]=<?=$field->max_length?>;
            uForms.send_result_email[k]=<?=$field->send_result_email?>;
            uForms.field_id2ind[<?=$field->field_id?>]=k;

            uForms.min_value_pos[k]=50;
            uForms.max_value_pos[k]=0;
                <? if($field->field_type=='4'||$field->field_type=='5'||$field->field_type=='6'){?>
                    uForms.field_values_ids[k]=[];
                    let l = 0;
                    <?$values=$uForms->uForms->get_selectbox_values($field->field_id);
                    for($l=0;$value=$values[$l];$l++) {?>
                    l=<?=$l?>;
                    uForms.field_values_ids[k][l]=<?=$value->value_id?>;
                    <?}
                    for($l=0;$value=$values[$l];$l++) {?>
                    l=uForms.value_id.length;
                    uForms.value_id[l]=<?=$value->value_id?>;
                    uForms.value_label[l]="<?=rawurlencode(uString::sql2text($value->label,true))?>";
                    uForms.value_pos[l]=<?=$value->pos?>;
                    if(uForms.min_value_pos[k]>uForms.value_pos[l]) uForms.min_value_pos[k]=uForms.value_pos[l];
                    if(uForms.max_value_pos[k]<uForms.value_pos[l]) uForms.max_value_pos[k]=uForms.value_pos[l];
                    uForms.value_field_id[l]=<?=$field->field_id?>;
                    uForms.value_id2ind[<?=$value->value_id?>]=l;
                    <?}
                }
            }
        }
    }?>
</script>

    <!-- Modals -->
    <? include 'inc/admin_form_editor_dialogs.php';?>

<div style="display: none">
    <div title="<?=$uForms->text("Row editor - dg title"/*Редактор блока*/)?>" id="uForms_row_edit_dg"></div>
    <div title="<?=$uForms->text("Col editor - dg title"/*Редактор колонки*/)?>" id="uForms_col_edit_dg"></div>

    <div title="<?=$uForms->text("Block deletion - dg title"/*Удаление блока*/)?>" id="uForms_row_delete_dg">
        <p><?=$uForms->text("Block deletion confirm - txt"/*Вы действительно хотите удалить этот блок?<br>Все колонки и поля, прикрепленные к этому блоку, будут удалены.*/)?></p>
    </div>
    <div title="<?=$uForms->text("Col deletion - dg title"/*Удаление колонки*/)?>" id="uForms_col_delete_dg">
        <p><?=$uForms->text("Col deletion - confirm"/*Вы действительно хотите удалить эту колонку?<br>Все поля, прикрепленные к этой колонке, будут удалены.*/)?></p>
    </div>
    <div title="<?=$uForms->text("Field deletion - dg title"/*Удаление поля*/)?>" id="uForms_field_delete_dg">
        <p><?=$uForms->text("Field deletion - confirm"/*Вы действительно хотите удалить это поле?*/)?></p>
    </div>
</div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();

/** @noinspection PhpIncludeInspection */
include "templates/u235/template.php";
