<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";

class uCat_admin_arts_edit {
    private $uCore,$uFunc,$field,$value,$art_id;

    private function update_uDrive_folder_name() {
        //get uDrive_folder_id
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        `uDrive_folder_id`
        FROM
        `u235_articles`
        WHERE
        `art_id`='".$this->art_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(10);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $file_name=trim(uString::sanitize_filename($this->value));
            if(!strlen($file_name)) $file_name='Статья '.$this->art_id;

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `file_name`='".$file_name."'
            WHERE
            `file_id`='".$qr->uDrive_folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(20);
        }
    }

    private function check_data() {
        if(!isset($_POST['field'],$_POST['value'],$_POST['art_id'])) $this->uCore->error(30);

        $this->art_id=$_POST['art_id'];
        $this->field=$_POST['field'];
        $this->value=trim($_POST['value']);

        if(!uString::isDigits($this->art_id)) $this->uCore->error(40);

        if($this->field!='art_text'&&$this->field!='art_title'&&$this->field!='art_author') $this->uCore->error(50);

        if($this->field=='art_title') {
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'title is empty'}");
            $this->update_uDrive_folder_name();
        }
    }
    private function save() {
        if(!$this->uCore->query('uCat',"UPDATE
        `u235_articles`
        SET
        `".$this->field."`='".uString::text2sql($this->value)."'
        WHERE
        `art_id`='".$this->art_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(60);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->save();

        if($this->field=='art_text') {
            $pos=mb_strpos($this->value,'<!-- my page break -->',0, 'UTF-8');
            if(!$pos) {
                $pos=mb_strpos($this->value,'<!-- pagebreak -->',0, 'UTF-8');
                if(!$pos) {
                    $this->value=mb_substr(strip_tags($this->value),0,600,'UTF-8').'...';
                }
                else $this->value=mb_substr($this->value,0,$pos,'UTF-8');
            }
            else $this->value=mb_substr($this->value,0,$pos,'UTF-8');
        }

        $this->uFunc->set_flag_update_sitemap(1, site_id);

        echo "{
        'status' : 'done',
        'field':'".$this->field."',
        'art_id':'".$this->art_id."',
        'value' : '".rawurlencode($this->value)."'
        }";
    }
}
$uCat=new uCat_admin_arts_edit($this);
