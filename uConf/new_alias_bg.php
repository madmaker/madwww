<?php
class uConf_new_alias {
    private  $uCore,$site_domain,$site_id;

    private function check_data() {
        if(!isset($_POST['site_id'],$_POST['site_domain'])) $this->uCore->error(10);
        $this->site_domain=$_POST['site_domain'];
        $this->site_id=$_POST['site_id'];
        if(!uString::isDomain_name($this->site_domain)) die("{'status' : 'error', 'msg' : 'domain'}");
        if(!uString::isDigits($this->site_id)) $this->uCore->error(20);


        //check if this domain is already registered
        if(!$query=$this->uCore->query("common","SELECT
        `site_id`
        FROM
        `u235_sites`
        WHERE
        `site_name`='".$this->site_domain."'
        ")) $this->uCore->error(30);
        if(mysqli_num_rows($query)) die("{'status' : 'error', 'msg' : 'exists'}");
    }

    private function add_alias() {
        if(!$this->uCore->query("common","INSERT INTO
        `u235_sites` (
        `site_id`,
        `site_name`,
        `status`,
        `main`
        ) VALUES (
        '".$this->site_id."',
        '".$this->site_domain."',
        'active',
        '0'
        )
        ")) $this->uCore->error(40);
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->add_alias();

        echo "{'status' : 'done',
        'site_id' : '".$this->site_id."',
        'site_name' : '".$this->site_domain."'
        }";
    }
}
$uConf=new uConf_new_alias ($this);
