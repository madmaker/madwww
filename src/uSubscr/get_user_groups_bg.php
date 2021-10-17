
<?php
class uSubscr_get_user_groups {
    private $uCore,$user_id;
    public $q_groups,$assigned_groups_ar;
    private function check_data() {
        if(!isset($_POST['user_id'])) $this->uCore->error(1);
        $this->user_id=$_POST['user_id'];
        if(!uString::isDigits($this->user_id)) $this->uCore->error(2);
    }
    private function get_groups() {
        //get all groups
        if(!$this->q_groups=$this->uCore->query("uSubscr","SELECT
        `gr_id`,
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        (`status` IS NULL OR `status`='active') AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);

        //get assigned groups
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `gr_id`
        FROM
        `u235_users_groups`
        WHERE
        `user_id`='".$this->user_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        while($gr=$query->fetch_object()) {
            $this->assigned_groups_ar[$gr->gr_id]=1;
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(23)) die('forbidden');

        $this->check_data();
        $this->get_groups();
    }
}
$uSubscr=new uSubscr_get_user_groups($this);?>

        <?while($gr=$uSubscr->q_groups->fetch_object()) {?>
            <div class="col-md-6">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="uSubscr_gr_id_<?=$gr->gr_id?>" <?=isset($uSubscr->assigned_groups_ar[$gr->gr_id])?' checked ':''?> onchange="uSubscr.assign_gr2rec(<?=$gr->gr_id?>)">
                         <span><?=uString::sql2text($gr->gr_title)?></span>
                    </label>
                </div>
            </div>
        <?}?>
