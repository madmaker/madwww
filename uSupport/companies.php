<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uSupport_companies_admin {
    public $uFunc;
    public $uSes;
    private $uCore,
        $qu_comps;
    public $qComList,$status,$status_sql,
        $is_com_client,$is_com_admin,$is_consultant,$is_operator;

    private function check_access() {
        $this->qu_comps='';
        $this->is_com_client=$this->is_com_admin=$this->is_consultant=$this->is_operator=false;
        //consultant or operator
        if($this->uCore->access(9)) {
            $this->is_operator=true;
            return true;
        }
        if($this->uCore->access(8)) {
            $this->is_consultant=true;
            return true;
        }
        //check if client of any company or admin
        if(!$query=$this->uCore->query("uSup","SELECT
        `com_id`,
        `admin`
        FROM
        `u235_com_users`
        WHERE
        `user_id`='".$this->uSes->get_val("user_id")."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(3);
        if(mysqli_num_rows($query)) {
            $this->qu_comps="(";
            while($com=$query->fetch_object()) {
                if($com->admin=='1') $this->is_com_admin=true;
                $this->qu_comps.="`com_id`='".$com->com_id."' OR ";
            }
            $this->qu_comps.="1=0) AND";
            if(!$this->is_com_admin) $this->is_com_client=true;
            return true;
        }

        return false;
    }


    private function defStatus(){
        if(isset($_GET['deleted'])) {
            $this->status="deleted";
            $this->status_sql="`com_status`='deleted'";
            $this->headerStatus=' (Удаленные)';
        }
        else {
            $this->status="";
            $this->status_sql="(`com_status`='' OR `com_status` IS NULL)";
            $this->headerStatus='';
        }
    }
    private function getComList() {
        if(!$this->qComList=$this->uCore->query("uSup","SELECT
        `com_id`,
        `com_title`
        FROM
        `u235_comps`
        WHERE
        ".$this->qu_comps."
        `site_id`='".site_id."'
        ORDER BY `com_title`
        ")) $this->uCore->error(1);
    }
    function __construct ($uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if($this->uCore->access(2)) {
            if($this->check_access()) {

                $this->defStatus();
                $this->getComList();
            }
            else {
                header('Location: '.u_sroot.$this->uCore->mod.'/requests');
                exit;
            }
        }
    }
}
$uSup=new uSupport_companies_admin($this);
if(!isset($_POST['in_dialog'])) ob_start();

if($this->access(2)) {
    if(!isset($_POST['in_dialog'])){?>
<div class="uSup_companies">
        <div><a href="<?=u_sroot.$this->mod?>/requests" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> К запросам</a></div>
<h1><?
    if($uSup->is_consultant||$uSup->is_operator) {?>Компании-клиенты техподдержки<?}
    else {?>Мои компании<?}?>
</h1>
        <div id="uSup_companies_list">
    <?}?>
    <table class="table table-condensed table-striped table-hover">
        <?while($com=$uSup->qComList->fetch_object()){?>
            <tr>
                <td><a href="<?=u_sroot.$this->mod?>/company_info/<?=$com->com_id?>"><?=$com->com_id?></a></td>
                <td><a href="<?=u_sroot.$this->mod?>/company_info/<?=$com->com_id?>"><?=uString::sql2text($com->com_title,1)?></a></td>
            </tr>
        <?}?>
    </table>
    <?if(!isset($_POST['in_dialog'])){?>
        </div>
</div><?}?>
    <?} else {
        ?>
        <div class="jumbotron">
            <h1 class="page-header">Техническая поддержка</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
    <?}
if(!isset($_POST['in_dialog'])){
    $this->page_content=ob_get_contents();
    ob_end_clean();

    include "templates/template.php";
}?>
