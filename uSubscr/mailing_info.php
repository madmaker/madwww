<?php
class uSubscr_mailing_info {
    private $uCore;
    public $m_id,$mailing,$q_results,$status2txt,$result2txt;
    private function err() {
        header('Location: '.u_sroot.$this->uCore->mod.'/mailings');
        die();
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->err();
        $this->m_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->m_id)) $this->err();
    }
    private function get_mailing_info() {
        if(!$query=$this->uCore->query("uSubscr","SELECT DISTINCT
        `u235_mailing`.`rec_id`,
        `u235_mailing`.`timestamp`,
        `u235_mailing`.`status`,
        `progress`,
        `rec_title`
        FROM
        `u235_mailing`,
        `u235_records`
        WHERE
        `m_id`='".$this->m_id."' AND
        `u235_mailing`.`site_id`='".site_id."' AND
        `u235_mailing`.`rec_id`=`u235_records`.`rec_id` AND
        `u235_records`.`site_id`='".site_id."'
        ")) $this->uCore->error(1);
        if(mysqli_num_rows($query)) $this->mailing=$query->fetch_object();
        else $this->err();
    }
    private function get_mailing_results() {
        if(!$this->q_results=$this->uCore->query("uSubscr","SELECT DISTINCT
        `u235_mailing_results`.`user_id`,
        `u235_mailing_results`.`timestamp`,
        `result`,
        `user_name`,
        `user_email`,
        `admin_made`
        FROM
        `u235_mailing_results`,
        `u235_users`
        WHERE
        `m_id`='".$this->m_id."' AND
        `u235_mailing_results`.`site_id`='".site_id."' AND
        `u235_mailing_results`.`user_id`=`u235_users`.`user_id` AND
        `u235_users`.`site_id`='".site_id."'
        ORDER BY
        `u235_mailing_results`.`timestamp` DESC
        ")) $this->uCore->error(2);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->check_data();
        $this->get_mailing_info();
        $this->get_mailing_results();

        $this->status2txt['running']='В процессе';
        $this->status2txt['preparing']='Подготовка к отправке';
        $this->status2txt['finished']='Завершена';
        $this->status2txt['stopped']='Остановлена';

        $this->result2txt['not sent']='Ожидает отправки';
        $this->result2txt['sent']='Отправлен';
        $this->result2txt['read']='Прочитан';
    }
}
$uSubscr=new uSubscr_mailing_info($this);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');

ob_start();?>

<div class="uSubscr">
    <div style="float: right; display: table">
        <?if($uSubscr->mailing->status=='running') {?>
        <button id="uSubscr_mailing_stop_btn" type="button" class="btn btn-danger" onclick="uSubscr.stop_mailing_dg()">Остановить рассылку</button>
        <?}?>
    </div>
    <p class="clearfix">&nbsp;</p>
    <h1 class="page-header"><a href="<?=u_sroot.$this->mod?>/rec_editor/<?=$uSubscr->mailing->rec_id?>"><?=uString::sql2text($uSubscr->mailing->rec_title)?></a><br><small><?=date('d.m.Y H:i:s',$uSubscr->mailing->timestamp)?> <span id="uSubscr_mailing_status"><?=$uSubscr->status2txt[$uSubscr->mailing->status]?></span> <?=$uSubscr->mailing->progress?>%</small></h1>

    <h3>Результаты рассылки</h3>
    <table class="table table-stried table-condensed table-hovered">
    <?while($result=$uSubscr->q_results->fetch_object()) {?>
        <tr>
            <td><?=uString::sql2text($result->user_name)?> <span class="text-muted"><?=$result->admin_made?$result->user_email:uString::hide_email_part($result->user_email)?></span></td>
            <td><?=$uSubscr->result2txt[$result->result]?> <?=date('d.m.Y H:i:s',$result->timestamp)?></td>
        </tr>
    <?}?>
    </table>
</div>


<script type="text/javascript">
    if(typeof uSubscr==="undefined") uSubscr={};
uSubscr.m_id=<?=$uSubscr->m_id?>;
</script>
<?if($uSubscr->mailing->status=='running') {?>
    <div class="modal fade" id="uSubscr_stop_mailing_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_stop_mailing_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_stop_mailing_dgLabel">Остановить рассылку?</h4>
                </div>
                <div class="modal-body">
                    <p>Все письма, которые еще не отправлены, в этой рассылке будут остановлены.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-danger" onclick="uSubscr.stop_mailing()    ">Остановить!</button>
                </div>
            </div>
        </div>
    </div>
<?}?>

<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
