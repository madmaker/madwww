<?php
class uConf_get_aliases {
    private $uCore,$site_id,$q_aliases;
    private function check_data() {
        if(!isset($_POST['site_id'])) $this->uCore->error(1);
        $this->site_id=$_POST['site_id'];
        if(!uString::isDigits($this->site_id)) $this->uCore->error(2);
    }
    private function get_aliases() {
        if(!$this->q_aliases=$this->uCore->query("common","SELECT
        `site_name`
        FROM
        `u235_sites`
        WHERE
        `site_id`='".$this->site_id."' AND
        `main`='0'
        ")) $this->uCore->error(3);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(17)) die('forbidden');

        $this->check_data();
        $this->get_aliases();?>

        <h3>Текущие зеркала выбранного сайта:</h3>
            <ul class="list-unstyled">
            <? while($alias=$this->q_aliases->fetch_object()) {?>
                <li>
                    <button class="btn btn-danger btn-xs uTooltip" onclick="uConf.delete_alias('<?=$alias->site_name?>',<?=$this->site_id?>)" title="Удалить зеркало">
                        <span class="glyphicon glyphicon-remove"></span>
                    </button> <?=$alias->site_name?>
                </li>
            <?}?>
            </ul>
        <?exit;
    }
}
$uConf=new uConf_get_aliases($this);
