<?php
use processors\uFunc;
require_once "processors/classes/uFunc.php";
class uCat_admin_sects_edit {
    private $uCore,$uFunc,
        $sect_id,$value,$field;
    private function check_data() {
        if(!isset($_POST['field'],$_POST['value'],$_POST['sect_id'])) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(10);
        $this->sect_id=&$_POST['sect_id'];
        $this->field=&$_POST['field'];
        $this->value=trim($_POST['value']);

        if(!uString::isDigits($this->sect_id)) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(20);
    }

    private function update_uDrive_folder_name() {
        //get uDrive_folder_id
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$query=$this->uCore->query("uCat","SELECT
        `uDrive_folder_id`
        FROM
        `u235_sects`
        WHERE
        `sect_id`='".$this->sect_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(30);
        if(mysqli_num_rows($query)) {
            /** @noinspection PhpUndefinedMethodInspection */
            $qr=$query->fetch_object();
            $file_name=trim(uString::sanitize_filename($this->value));
            if(!strlen($file_name)) $file_name='Раздел '.$this->sect_id;

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query("uDrive","UPDATE
            `u235_files`
            SET
            `file_name`='".$file_name."'
            WHERE
            `file_id`='".$qr->uDrive_folder_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(40);
        }
    }

    private function update_value() {
        if($this->field=='sect_title') {
            $this->update_uDrive_folder_name();
            if(!strlen($this->value)) die("{'status' : 'error', 'msg' : 'title is empty'}");
            $this->value=uString::text2sql($this->value);
        }
        elseif($this->field=='seo_title') {
            $this->value=uString::text2sql($this->value);
            if(!isset($_POST['seo_descr'])) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(50);
            $seo_descr=uString::text2sql($_POST['seo_descr']);

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query('uCat',"UPDATE
            `u235_sects`
            SET
            `".$this->field."`='".$this->value."',
            `seo_descr`='".$seo_descr."'
            WHERE
            `sect_id`='".$this->sect_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->error(60);

            die("{
            'status' : 'done',
            '".$this->field."':'".rawurlencode(uString::sql2text($this->value,true))."',
            'seo_descr':'".rawurlencode(uString::sql2text($seo_descr,true))."'
            }");
        }
        elseif($this->field=='sect_descr') $this->value=uString::text2sql($this->value);
        elseif($this->field=='sect_keywords') $this->value=uString::text2sql($this->value);
        elseif($this->field=='sect_url') {
            if(!strlen($this->value)) die("{'status' : 'done', 'msg' : 'url is empty'}");
            if(!uString::isFilename($this->value)) die("{'status' : 'done', 'msg' : 'url is wrong'}");
            $this->value=uString::text2sql($this->value);
        }
        elseif($this->field=='seo_descr') $this->value=uString::text2sql($this->value);
        elseif($this->field=='sect_pos') {
            if(!uString::isDigits($this->value)) die("{'status' : 'error', 'msg' : 'is not number'}");
        }
        elseif($this->field=='show_cats_descr') {
            if($this->value!='1') $this->value=0;
        }
        elseif($this->field=='show_in_menu') {
            /** @noinspection PhpUndefinedMethodInspection */
            if(!$this->uCore->query('uCat',"UPDATE
            `u235_sects`
            SET
            `show_in_menu`=1-`show_in_menu`
            WHERE
            `sect_id`='".$this->sect_id."' AND
            `site_id`='".site_id."'
            ")) /** @noinspection PhpUndefinedMethodInspection */
                $this->uCore->error(70);
            die("{'status' : 'done'}");
        }
        else /** @noinspection PhpUndefinedMethodInspection */
            $this->uCore->error(80);

        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->query('uCat',"UPDATE
        `u235_sects`
        SET
        `".$this->field."`='".$this->value."'
        WHERE
        `sect_id`='".$this->sect_id."' AND
        `site_id`='".site_id."'
        ")) /** @noinspection PhpUndefinedMethodInspection */
            $this->error(90);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc = new uFunc($this->uCore);
        /** @noinspection PhpUndefinedMethodInspection */
        if(!$this->uCore->access(25)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->update_value();
        $this->uFunc->set_flag_update_sitemap(1, site_id);
        echo "{
        'status' : 'done',
        '".$this->field."':'".rawurlencode(uString::sql2text($this->value,true))."'
        }";
    }
}
$uCat=new uCat_admin_sects_edit($this);
