<?php
class uPeople_load_user_groups {
    private $uCore,$user_id,$q_groups,$cur_groups;
    private function checkData() {
        if(!isset($_POST['user_id'])) $this->uCore->error(1);
        $this->user_id=$_POST['user_id'];
        if(!uString::isDigits($this->user_id)) $this->uCore->error(2);
    }
    private function getCurrentGroups(){
        if(!$query=$this->uCore->query("uPeople","SELECT DISTINCT
        `u235_groups`.`gr_id`
        FROM
        `u235_groups`,
        `u235_people_groups`
        WHERE
        `u235_people_groups`.`user_id`='".$this->user_id."' AND
        `u235_people_groups`.`site_id`='".site_id."' AND
        `u235_people_groups`.`gr_id`=`u235_groups`.`gr_id` AND
        `u235_groups`.`site_id`='".site_id."'
        ")) $this->uCore->error(3);
        while($qr=$query->fetch_object()) {
            if(!isset($this->cur_groups[$qr->gr_id])) $this->cur_groups[$qr->gr_id]=1;
        }
    }
    private function getAllGroups() {
        if(!$this->q_groups=$this->uCore->query("uPeople","SELECT
        `gr_id`,
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `gr_title` ASC
        ")) $this->uCore->error(4);
    }
    private function print_groups() {
        ob_start();

        echo '<table>';
        while($group=$this->q_groups->fetch_object()) {
            echo '<tr><td>#'.$group->gr_id.' '.uString::sql2text($group->gr_title).'</td><td style="padding-left:10px">';
            if(isset($this->cur_groups[$group->gr_id])) echo '<a href="javascript:void(0);" onclick="uPeople.attach_group(\'detach\','.$group->gr_id.')">Убрать</a>';
            else echo '<a href="javascript:void(0);" onclick="uPeople.attach_group(\'attach\','.$group->gr_id.')">Добавить</a>';
            echo '<button type="button" class="btn btn-xs btn-link" onclick="uPeople.delete_group('.$group->gr_id.')"><span class="glyphicon glyphicon-remove"></span></button>
            </td></tr>';
        }
        echo '<table>';
        $grs4dialog=ob_get_contents();
        ob_end_clean();

        ob_start();
        mysqli_data_seek($this->q_groups,0);
        while($group=$this->q_groups->fetch_object()) {
            if(isset($this->cur_groups[$group->gr_id]))
                echo '<a href="'.u_sroot.$this->uCore->mod.'/users_by_gr/'.$group->gr_id.'" target="_blank">'.$group->gr_title.'</a><br>';
                //echo $group->gr_title.'<br>';
        }
        $grs4profile=ob_get_contents();
        ob_end_clean();

        echo "{'status' : 'done', 'grs4dialog' : '".rawurlencode($grs4dialog)."','grs4profile' : '".rawurlencode($grs4profile)."'}";
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->uCore->access(10)) die("{'status' : 'forbidden'}");

        $this->checkData();
        $this->getCurrentGroups();
        $this->getAllGroups();
        $this->print_groups();
    }
}
$uPeople=new uPeople_load_user_groups($this);
