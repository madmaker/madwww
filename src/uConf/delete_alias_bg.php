<?php
class uConf_delete_alias {
    private $uCore,$site_name,$site_id;
    private function check_data(){
        if(!isset($_POST['site_id'],$_POST['site_name'])) $this->uCore->error(1);
        $this->site_id=$_POST['site_id'];
        $this->site_name=$_POST['site_name'];

        if(!uString::isDigits($this->site_id)) $this->uCore->error(2);
        if(!uString::isDomain_name($this->site_name)) $this->uCore->error(3);
    }

    private function delete_alias() {
        if(!$this->uCore->query("common","DELETE FROM
        `u235_sites`
        WHERE
        `site_id`='".$this->site_id."' AND
        `site_name`='".$this->site_name."'
        ")) $this->uCore->error(4);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->delete_alias();

        echo "{'status' : 'done'}";
    }
}
$uConf=new uConf_delete_alias($this);
