<?php
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class uKnowbase_records {
    public $uFunc;
    public $uSes;
    private $uCore,$com_id;
    public $q_records,$q_companies,$q_com_filter;
    private function setFilter() {
        if($this->uCore->uFunc->getConf("rec_com_lock","uKnowbase")=='1') {
            if($this->uCore->access(33)) {
                $this->q_com_filter='';
                if(isset($_GET['com_id'])) {
                    $this->com_id=$_GET['com_id'];
                    if(uString::isDigits($this->com_id)) {
                        $this->q_com_filter=" AND `com_id`='".$this->com_id."'";
                    }
                }
            }
            else {//we must show only records of user's company
                //get companies, where user attached to
                if(!$query=$this->uCore->query("uSup","SELECT
                `com_id`
                FROM
                `u235_com_users`
                WHERE
                `user_id`='".$this->uSes->get_val("user_id")."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(1);
                $this->q_com_filter=" AND (`access_limited`='0'";
                while($com=$query->fetch_object()) {
                    $this->q_com_filter.=" OR `com_id`='".$com->com_id."'";
                }
                $this->q_com_filter.=")";
            }
        }
        else $this->q_com_filter='';
    }
    private function getRecords() {
        if(!empty($this->q_com_filter)) {
            if(!$this->q_records=$this->uCore->query("uKnowbase","SELECT DISTINCT
            `u235_records`.`rec_id`,
            `is_section`,
            `rec_title`,
            `rec_indent`,
            `rec_position`,
            `rec_status`,
            `access_limited`,
            `user_id`
            FROM
            `u235_records`,
            `u235_records_comps`
            WHERE
            (`u235_records`.`rec_id`=`u235_records_comps`.`rec_id` OR `access_limited`='0') AND
            `u235_records`.`site_id`='".site_id."' AND
            `u235_records_comps`.`site_id`='".site_id."'
            ".$this->q_com_filter."
            ORDER BY
            `rec_position` ASC
            ")) $this->uCore->error(2);
        }
        else {
            if(!$this->q_records=$this->uCore->query("uKnowbase","SELECT
            `rec_id`,
            `is_section`,
            `rec_title`,
            `rec_indent`,
            `rec_position`,
            `rec_status`,
            `access_limited`,
            `user_id`
            FROM
            `u235_records`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `rec_position` ASC
            ")) $this->uCore->error(3);
        }
    }
    private function get_companies() {
        if(!$this->q_companies=$this->uCore->query("uSup","SELECT
        `com_id`,
        `com_title`
        FROM
        `u235_comps`
        WHERE
        (`com_status` IS NULL OR `com_status`='') AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
    }
    private function del_droped_recs() {
        if($this->uCore->access(33)) {
            $droped_rec_lifetime=time()-604800;//1 week
            if(!$query=$this->uCore->query("uKnowbase","SELECT
             `rec_id`
             FROM
             `u235_records`
             WHERE
             `timestamp`<'".$droped_rec_lifetime."' AND
             `rec_status`='deleted' AND
             `site_id`='".site_id."'
             ")) $this->uCore->error(5);
            while($rec=$query->fetch_object()) {
                @uFunc::rmdir($_SERVER['DOCUMENT_ROOT'].'/'.$this->uCore->mod.'/files/'.site_id.'/'.$rec->rec_id);
                if(!$this->uCore->query("uKnowbase","DELETE FROM
                `u235_records_files`
                WHERE
                `rec_id`='".$rec->rec_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(6);
            }
            if(!$this->uCore->query("uKnowbase","DELETE FROM
            `u235_records`
            WHERE
            `timestamp`<'".$droped_rec_lifetime."' AND
            `rec_status`='deleted' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(7);
        }
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new \processors\uFunc($this->uCore);
        $this->uSes=new uSes($this->uCore);

        if($this->uCore->access(2)||$this->uCore->uFunc->getConf("uknowbase_access_only_4_auth","uKnowbase")=='0'){
            $this->del_droped_recs();

            $this->setFilter();
            $this->getRecords();
            $this->get_companies();
        }
    }
}
$uKnowbase=new uKnowbase_records($this);
if(!isset($_POST['in_dialog'])) {
    $this->uFunc->incJs(u_sroot.'uKnowbase/js/records.min.js');
    $this->uFunc->incCss(u_sroot.'uKnowbase/css/uKnowbase.min.css');

    ob_start();
}
if($this->access(2)||$this->uFunc->getConf("uknowbase_access_only_4_auth","uKnowbase")=='0'){
    if(!isset($_POST['in_dialog'])) {?>
        <h1 class="page-header"><?=$this->page['page_title']?></h1>

        <div class="uKnowbase_records">
            <div class="uKnowbase records row">
                <div class="list col-md-12" id="uKnowbase_records_list">
                    <?}?>
                    <ul><?
                        while($rec=$uKnowbase->q_records->fetch_object()){
                            if($rec->rec_status=='new'&&!$this->access(33)) continue; ?>
                            <li id="uKb_rec_<?=$rec->rec_id?>" class="lvl<?=$rec->rec_indent.($rec->is_section=='1'?' rec_section ':'').($rec->rec_status=='new'?' bg-info ':'')?>">
                                <?if($rec->rec_status=='new'){?><span class="glyphicon glyphicon-eye-close uTooltip" title="Эта запись еще не опубликована"></span><?}?>
                                <?if($rec->access_limited=='1'&&$this->uFunc->getConf("rec_com_lock","uKnowbase")=='1'&&$this->access(33)){?><span class="glyphicon glyphicon-lock uTooltip" title="К этой записи есть доступ только некоторым компаниям" onclick="uKnowbase.show_access(<?=$rec->rec_id?>)"></span><?}?>
                                <?if($rec->is_section=='0'){?><a href="<?=u_sroot.'uKnowbase/solution/'.$rec->rec_id?>"><?}?><?=uString::sql2text($rec->rec_title)?><?if($rec->is_section=='0'){?></a><?}?>
                                <?if($this->access(33)&&$rec->user_id==$uKnowbase->uSes->get_val("user_id")||$this->access(38)){?><span class="btn-group <?if(!isset($_POST['in_dialog'])) {?>u235_eip uKnowbase_records_edit_btns<?}?>">
                                    <button onclick="uKnowbase.change_pos(<?=$rec->rec_id?>)" class="btn btn-default btn-xs uTooltip" title="Изменить положение"><span class="glyphicon glyphicon-sort"></span></button>
                                    <?if($this->uFunc->getConf("rec_com_lock","uKnowbase")=='1'){?><button onclick="uKnowbase.set_access(<?=$rec->rec_id?>)" class="btn btn-default btn-xs <?=$rec->access_limited=='1'?' active ':''?> uTooltip" title="Разрешить видеть эту запись только выбранным компаниям"><span class="glyphicon glyphicon-lock"></span></button><?}?>
                                    <button onclick="uKnowbase.make_section(<?=$rec->rec_id?>)" class="btn btn-default btn-xs uTooltip <?=$rec->is_section=='1'?'active':''?>" title="<?=$rec->is_section=='1'?'Сделать обыччной записью':'Сделать заголовком'?>"><span class="glyphicon glyphicon-font"></span></button>
                                    <?if($rec->rec_status=='new'){?><button onclick="uKnowbase.publish(<?=$rec->rec_id?>)" class="btn btn-success btn-xs uTooltip <?=$rec->is_section=='1'?'active':''?>" title="Опубликовать запись"><span class="glyphicon glyphicon-eye-open"></span></button><?}?>
                                </span><?}?>
                            </li>
                        <?}?>
                    </ul>
                    <?if(!isset($_POST['in_dialog'])) {?>
                </div>
            </div>
        </div>
    <?
        if($this->access(33)){
            include_once 'inc/records_dialogs.php';?>
        <?}
    }
}
else {
    if(!isset($_POST['in_dialog'])) {?>
        <div class="jumbotron">
            <h1 class="page-header">База знаний</h1>
            <p>Пожалуйста, авторизуйтесь</p>
            <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
        </div>
    <?}
    else { echo 'forbidden';}
}


if(!isset($_POST['in_dialog'])) {
    $this->page_content=ob_get_contents();
    ob_end_clean();
    include "templates/template.php";
}
?>
