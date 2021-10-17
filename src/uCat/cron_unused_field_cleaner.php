<?php
class uCat_cron_unused_field_cleaner{
    private $uCore,
        $secret,$lifetime,
        $q_fields_update;
    private function check_data() {
        if(!isset($_POST['uSecret'])) $this->uCore->error(1);
        if($this->secret!=$_POST['uSecret']) $this->uCore->error(2);
    }

    private function delete_field($field) {
        if(!$this->uCore->query("uCat","DELETE FROM
        `u235_fields`
        WHERE
        `field_id`='".$field->field_id."' AND
        `site_id`='".$field->site_id."'
        ")) $this->uCore->error(3);

        uFunc::rmdir($this->uCore->mod.'/field_files/'.$field->site_id.'/'.$field->field_id);
    }
    private function update_field_usage_check_timestamp() {
        if(!$query=$this->uCore->query("uCat","UPDATE
            `u235_fields`
            SET
            `usage_check_timestamp`='".time()."'
            WHERE
            ".$this->q_fields_update
        )) $this->uCore->error(4);
    }
    private function check_field($field) {
        if(!$query=$this->uCore->query("uCat","SELECT
        `field_id`
        FROM
        `u235_cats_fields`
        WHERE
        `field_id`='".$field->field_id."' AND
        `site_id`='".$field->site_id."'
        ")) $this->uCore->error(5);
        if(!mysqli_num_rows($query)) {//field is UNused
            //echo 'field:'.$field->field_id.'. site:'.$field->site_id.'<br>';
            $this->delete_field($field);
        }
    }
    private function get_fields() {
        //get last 100 fields
        if(!$query=$this->uCore->query("uCat","SELECT
        `field_id`,
        `site_id`
        FROM
        `u235_fields`
        WHERE
        usage_check_timestamp<".(time()-$this->recheck_time)."
        LIMIT 100
        ")) $this->uCore->error(6);
        while($field=$query->fetch_object()) {
            if(time()>($this->startime+$this->lifetime)) {
                $this->update_field_usage_check_timestamp();
                exit;
            }
            $this->check_field($field);
            if(isset($this->q_fields_update)) $this->q_fields_update.=' OR ';
            else $this->q_fields_update='';

            $this->q_fields_update.=" (`field_id`='".$field->field_id."' AND `site_id`='".$field->site_id."') ";
        }
    }
    private function clean_fields() {
        $this->get_fields();
        $this->update_field_usage_check_timestamp();
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;

        $this->secret='LKklsdf098238092-0340981knjlLKJsd69f8hkj2nm3klsdfoisdf789LKJljmk2873942lkjIOsd9f82lkj2l3kj4987';
        $this->startime=time();
        $this->lifetime=20;//20 seconds
        $this->recheck_time=259200;//3 days

        $this->check_data();

        $this->clean_fields();
    }
}
$uCat=new uCat_cron_unused_field_cleaner($this);
