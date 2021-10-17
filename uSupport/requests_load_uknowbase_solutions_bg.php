<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSup_requests_load_uknowbase_solutions {
    public $uFunc;
    public $uSes;
    private $uCore,$tic_id;
    public $q_records,$assigned_id2set,$assigned_only;
    private function check_data() {
        if(!isset($_POST['tic_id'])) $this->uCore->error(1);
        $this->tic_id=$_POST['tic_id'];
        if(!uString::isDigits($this->tic_id)) $this->uCore->error(2);
        if(isset($_POST['assigned'])) $this->assigned_only=true;
        else $this->assigned_only=false;
    }
    private function check_access() {
        if($this->uCore->access(8)||$this->uCore->access(9)) return true;
        //get tic company
        if(!$query=$this->uCore->query("uSup","SELECT
        `company_id`,
        `user_id`
        FROM
        `u235_requests`
        WHERE
        `tic_id`='".$this->tic_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(!mysqli_num_rows($query)) return false;
        $com=$query->fetch_object();
        if($com->user_id==$this->uSes->get_val("user_id")) return true;

        if($com->company_id!='0') {
            //check if this user belongs to company
            if(!$query=$this->uCore->query("uSup","SELECT
            `user_id`
            FROM
            `u235_com_users`
            WHERE
            `user_id`='".$this->uSes->get_val("user_id")."' AND
            `com_id`='".$com->company_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);
            if(mysqli_num_rows($query)) return true;
        }
        return false;
    }
    private function getRecords() {
            if(!$this->q_records=$this->uCore->query("uKnowbase","SELECT
            `rec_id`,
            `rec_status`,
            `is_section`,
            `rec_title`,
            `rec_indent`,
            `access_limited`,
            `rec_position`
            FROM
            `u235_records`
            WHERE
            "./*`rec_status`='active' AND*/"
            `site_id`='".site_id."'
            ORDER BY
            `rec_position` ASC
            ")) $this->uCore->error(5);
    }
    private function get_assigned_records() {
            if(!$query=$this->uCore->query("uSup","SELECT
            `sol_id`
            FROM
            `u235_uKnowbase_solutions_requests`
            WHERE
            `tic_id`='".$this->tic_id."' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(6);
        while($sol=$query->fetch_object()) $this->assigned_id2set[$sol->sol_id]=true;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        $this->check_data();
        if($this->check_access()) {
            $this->getRecords();
            $this->get_assigned_records();
        }
        else die('forbidden');
    }
}
$uSup=new uSup_requests_load_uknowbase_solutions($this);?>

    <div class="uKnowbase_records">
        <div class="uKnowbase records row">
            <div class="list col-md-12">
               <ul>
                   <?
                   if(!$uSup->assigned_only) {
                       while($sol=$uSup->q_records->fetch_object()) {?>
                        <li id="uSup_set_rec_id_solutions_sol_<?=$sol->rec_id?>" class="lvl<?=$sol->rec_indent?> <?=($sol->is_section=='1'?'rec_section':'')?> <?=isset($uSup->assigned_id2set[$sol->rec_id])?'bg-primary':''?>">
                            <?if($sol->rec_status=='new'&&$this->access(33)){?><span class="glyphicon glyphicon-eye-close uTooltip" title="Эта запись еще не опубликована"></span><?}?>
                            <?if($sol->access_limited=='1'&&$this->uFunc->getConf("rec_com_lock","uKnowbase")=='1'&&$this->access(33)){?><span class="glyphicon glyphicon-lock uTooltip" title="К этой записи есть доступ только некоторым компаниям"></span><?}?>
                            <?if($sol->is_section!='1'){?><a href="javascript:void(0)" onclick="uSup_req_show_common.set_rec_id(<?=$sol->rec_id?>)"><?}?>
                                <?=uString::sql2text($sol->rec_title,true)?>
                            <?if($sol->is_section!='1'){?></a><?}?>
                        </li>
                       <?}?>
                   <?} else {
                       while($sol=$uSup->q_records->fetch_object()) {?>
                           <?if(isset($uSup->assigned_id2set[$sol->rec_id])){?><li><a href="<?=u_sroot?>uKnowbase/solution/<?=$sol->rec_id?>" target="_blank"><?=uString::sql2text($sol->rec_title,true)?></a></li><?}?>
                   <?}?>
                   <?}?>
               </ul>
            </div>
        </div>
    </div>
