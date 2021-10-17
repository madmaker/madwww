<?php
class uConf_get_installed_mods {
    private $uCore,$site_id,$q_mods;
    private function check_data() {
        if(!isset($_POST['site_id'])) $this->uCore->error(10);
        $this->site_id=$_POST['site_id'];
        if(!uString::isDigits($this->site_id)) $this->uCore->error(20);
    }
    private function get_mods() {
        if(!$this->q_mods=$this->uCore->query("common","SELECT
        `mod_id`,
        `mod_name`
        FROM
        `u235_sites_modules`
        WHERE
        `site_id`='".$this->site_id."' AND
        `installed`='1'
        ")) $this->uCore->error(30);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(17)) die("{'status' : 'forbidden'}");

        $this->check_data();
        $this->get_mods();

        echo "{'status' : 'done',
        'site_id':'".$this->site_id."'";
        while($mod=$this->q_mods->fetch_object()) {
            if($mod->mod_name!='common'&&$mod->mod_name!='content'&&$mod->mod_name!='mainpage'&&$mod->mod_name!='uAuth'&&$mod->mod_name!='uConf'&&$mod->mod_name!='uCore'&&$mod->mod_name!='uEditor'&&$mod->mod_name!='uForms'&&$mod->mod_name!='uRubrics'&&$mod->mod_name!='uSlider') echo ", 'mod_".$mod->mod_id."' : '1'";
        }
        echo "}";
        exit;
    }
}
$uConf=new uConf_get_installed_mods($this);
