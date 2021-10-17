<?php

namespace uForms\admin;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "inc/common.php";

class admin_form_messages {
    public $uFunc;
    public $uForms;
    private $uCore;
    public $form_id,$form_name,$form_title,$q_fields;

    public function text($str) {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->uCore->text(array('uForms','admin_form_messages'),$str);
    }

    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->uFunc->error(1);
        $this->form_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->form_id)) {
            header('Location: '.u_sroot.'admin_forms');
            die();
        };
    }

    private function get_fields() {
        try {
            $site_id = site_id;
            /** @noinspection PhpUndefinedMethodInspection */
            $get_fields = $this->uFunc->pdo("uForms")->prepare("SELECT DISTINCT
            /*results*/
            field_value,
            u235_fields.field_type,
            u235_form_results.field_id,
            
            /*records*/
            rec_timestamp,
            u235_records.rec_id
            
            FROM
            u235_form_results
            JOIN
            u235_records
              ON
                u235_form_results.rec_id=u235_records.rec_id AND
                u235_form_results.site_id=u235_records.site_id
            JOIN
            u235_fields
              ON
                u235_form_results.field_id=u235_fields.field_id AND
                u235_form_results.site_id=u235_fields.site_id
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
              u235_records.form_id=:form_id AND
              u235_records.site_id=:site_id AND 
              u235_records.rec_status='active'
            
            ORDER BY
            row_pos,
            col_pos,
            field_pos
            ");

            $finish_data = array();
            /** @noinspection PhpUndefinedMethodInspection */
            $get_fields->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_fields->bindParam(':form_id', $this->form_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_fields->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while($row = $get_fields->fetch(PDO::FETCH_OBJ)) {
                $recid = (int)$row->rec_id;
                $row->field_type=(int)$row->field_type;
                if($row->field_type===4||$row->field_type===5||$row->field_type===6) $strtmp=nl2br($this->uForms->selectbox_value_id2label($row->field_value));
                elseif($row->field_type===3) $strtmp='<a target="_blank" href="'.u_sroot.'uForms/field_files/'.site_id.'/'.$this->form_id.'/'.$row->rec_id.'/'.uString::sql2text($row->field_value,1).'">'.uString::sql2text($row->field_value,1).'</a>';
                else $strtmp = nl2br(uString::sql2text($row->field_value,1));

                $strtmp = rawurlencode($strtmp);
                $finish_data[$recid][]=$strtmp;
                $finish_data[$recid]["rec_timestamp"]=$row->rec_timestamp;
            }
            $this->q_fields = $finish_data;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
    }
    private function get_title() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $get_title = $this->uFunc->pdo("uForms")->prepare("SELECT DISTINCT
            /*results*/
            field_id,
            /*fields*/
            field_label,
            field_placeholder,
            field_tooltip,
            field_descr,
            field_show_in_results,
            is_checked
            
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
            u235_rows.form_id=:form_id AND
            u235_rows.site_id=:site_id
            ORDER BY
            row_pos,
            col_pos,
            field_id
            ");

            $site_id = site_id;

            /** @noinspection PhpUndefinedMethodInspection */
            $get_title->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_title->bindParam(':form_id', $this->form_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_title->execute();
            $rowarrtmp = array();
            $row_arr_res = array();
            /** @noinspection PhpUndefinedMethodInspection */
            while ($row = $get_title->fetch(PDO::FETCH_ASSOC)) {
                $row_field_id = $row["field_id"];
                $row_field_show_in_result = $row["field_show_in_results"];
                $row_is_checked = $row["is_checked"];

                if(!empty($row["field_label"])) {
                    $rowarrtmp["field_name"] = rawurlencode($row["field_label"]);
                }
                elseif(!empty($row["field_placeholder"])) {
                    $rowarrtmp["field_name"] = rawurlencode($row["field_placeholder"]);
                }
                elseif(!empty($row["field_tooltip"])) {
                    $rowarrtmp["field_name"] = rawurlencode($row["field_tooltip"]);
                }
                elseif(!empty($row["field_descr"])) {
                    $rowarrtmp["field_name"] = rawurlencode($row["field_descr"]);
                }
                else {
                    $rowarrtmp["field_name"] = "#".$row_field_id;
                }
                $rowarrtmp["field_show"] = $row_field_show_in_result;
                $rowarrtmp["field_id"] = $row_field_id;
                $rowarrtmp["is_checked"] = $row_is_checked;
                $row_arr_res[]=$rowarrtmp;
            }
            $this->form_title = $row_arr_res;
        }
        catch(PDOException $e) {$this->uFunc->error('11'/*.$e->getMessage()*/);}
    }

    private function get_form_title() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $get_form_title = $this->uFunc->pdo("uForms")->prepare("SELECT
            form_title
            FROM
            u235_forms
            WHERE
            form_id=:form_id AND
            site_id=:site_id
            LIMIT 1
            ");

            $site_id = site_id;

            /** @noinspection PhpUndefinedMethodInspection */
            $get_form_title->bindParam(':site_id', $site_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_form_title->bindParam(':form_id', $this->form_id, PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */
            $get_form_title->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            while ($row = $get_form_title->fetch(PDO::FETCH_ASSOC)) {
                $get_form_name = $row["form_title"];
            }
            $this->form_name = $get_form_name;
        }
        catch(PDOException $e) {$this->uFunc->error('12'/*.$e->getMessage()*/);}
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        $this->uForms=new \uForms($this->uCore);

        $this->uCore->page['page_title']=$this->text("Page name"/*Записи формы*/);

        /** @noinspection PhpUndefinedMethodInspection */
        $this->uCore->uInt_js('uForms','admin_form_messages');
        $this->uFunc->incJs(u_sroot.'js/DataTables/date-euro.js', 1);

        $this->check_data();
        $this->get_fields();
        $this->get_title();
        $this->get_form_title();


        $this->uFunc->incJs(u_sroot.'/js/DataTables/DataTables-1.10.16/js/jquery.dataTables.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/DataTables-1.10.16/js/dataTables.bootstrap.min.js');

        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/jszip.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/pdfmake.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/vfs_fonts.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/dataTables.buttons.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/buttons.bootstrap.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/buttons.html5.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/buttons/buttons.print.min.js');
        $this->uFunc->incJs(u_sroot.'/js/DataTables/date-euro.js');
    }
}
$uForms=new admin_form_messages ($this);
ob_start();
$this->uFunc->incJs(u_sroot.'js/phpjs/functions/datetime/date.js');
$this->uFunc->incJs(u_sroot.'uForms/js/'.$this->page_name.'.js');
$this->uFunc->incJs(u_sroot.'js/bootstrap_plugins/PopConfirm/jquery.popconfirm.js');
?>
    <div class="uForms u235_admin">
        <div class="list"></div>
    </div>

    <?
    $js_field_title = json_encode($uForms->form_title,JSON_UNESCAPED_UNICODE);
    $js_field_data = json_encode($uForms->q_fields,JSON_UNESCAPED_UNICODE);

    $f_name = uString::rus2eng($uForms->form_name);
    $f_name = uString::text2filename($f_name);
    $f_name = $f_name."_".date("d-m-Y_H-m-s");
    $js_form_name = json_encode($f_name,JSON_UNESCAPED_UNICODE);
    print "<script language='javascript'>
    var js_form_name = $js_form_name; 
    var fieldjsontitle=$js_field_title;
    var fieldjsondata=$js_field_data;
    
    if(typeof uForms==='undefined') uForms={};
    uForms.form_id=$uForms->form_id;
    </script>";
    ?>
<?
$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/u235/template.php';