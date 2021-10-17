<?php
class uSubscr_gr_editor{
    private $uCore;
    public $gr_id,$gr_title,$rec_html,$q_files,$q_users,$assigned_users_ar;
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) header('Location: '.u_sroot.$this->uCore->mod.'/records');
        $this->gr_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->gr_id)) header('Location: '.u_sroot.$this->uCore->mod.'/records');
    }
    private function get_gr() {
        if(!$query=$this->uCore->query("uSubscr","SELECT
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(4);
        $gr=$query->fetch_object();

        $this->gr_title=uString::sql2text($gr->gr_title);
    }

    private function get_users() {
        //get users
        if(!$this->q_users=$this->uCore->query("uSubscr","SELECT DISTINCT
        `u235_users`.`user_id`,
        `user_name`,
        `user_email`,
        `admin_made`
        FROM
        `u235_users`,
        `u235_users_groups`
        WHERE
        (`status` IS NULL OR `status`='active') AND
        `u235_users`.`site_id`='".site_id."' AND
        `u235_users`.`user_id`=`u235_users_groups`.`user_id` AND
        `u235_users_groups`.`site_id`='".site_id."' AND
        `gr_id`='".$this->gr_id."' AND
        `unsubscribed`='0'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->checkData();
        $this->get_gr();
        $this->get_users();
    }
}
$uSubscr=new uSubscr_gr_editor($this);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.min.js',false);
$this->uFunc->incCss(u_sroot.'uSubscr/css/default.min.css');

$this->page['page_title']=$uSubscr->gr_title;
ob_start();?>
<div class="row">
    <div class="col-md-12 uSubscr">
        <a href="<?=u_sroot.$this->mod?>/groups" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-arrow-left"></span> Назад к группам</a>
        <h1 class="page-header"><span id="uSubscr_gr_title"><?=$uSubscr->gr_title?></span> <button class="btn btn-sm btn-default" onclick="uSubscr.edit_title()"><span class="glyphicon glyphicon-pencil"></span></button></h1>

        <div class="clearfix">&nbsp;</div>

        <h4>Подписчики: <small><a href="<?=u_sroot.$this->mod?>/users">Открыть страницу со всеми подписчиками</a><br>
            Кто подписан на эту группу рассылки</small></h4>

        <div class="form-horizontal">
            <div class="form-group">
                <div class="col-sm-5 col-md-3">
                    <div class="input-group">
                        <input type="text" id="uSubscr_gr_users_filter" class="form-control" placeholder="Фильтр" onkeyup="uSubscr.users_filter()">
                    <span class="input-group-btn">
                        <button id="uSubscr_gr_users_filter_btn" class="btn btn-default" type="button"><span class="glyphicon glyphicon-search" onclick="uSubscr.users_filter()"></span></button>
                    </span>
                    </div>
                </div>
            </div>
        </div>

        <table id="uSubscr_gr_users_list" class="table table-condensed table-hover table-striped">
        <?while($user=$uSubscr->q_users->fetch_object()) {?>
            <tr id="uSubscr_user_id_<?=$user->user_id?>">
                <td>
                    <button type="button" class="btn btn-danger btn-xs uTooltip" title="Отписать пользователя от этой новостной группы" onclick="uSubscr.assign_gr2user(<?=$user->user_id?>)"><span class="glyphicon glyphicon-remove"></span></button>
                </td>
                <td><?=uString::sql2text($user->user_name)?></td>
                <td><?=($user->admin_made=='1')?$user->user_email:'<span class="uTooltip" title="Пользователь сам подписался на рассылку. Его email скрыт.">'.uString::hide_email_part($user->user_email).'</small>'?></td>
            </tr>
        <?}?>
        </table>
    </div>
</div>


<script type="text/javascript">
    if(typeof uSubscr==="undefined") uSubscr={};
    uSubscr.gr_id=<?=$uSubscr->gr_id?>;
    uSubscr.gr_title="<?=rawurlencode($uSubscr->gr_title)?>";
</script>

    <div class="modal fade" id="uSubscr_edit_title_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_edit_title_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_edit_title_dgLabel">Изменить название группы</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_edit_title_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_edit_title_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label>Новое название группы:</label>
                        <input type="text" id="uSubscr_edit_title_title" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.edit_title_save()">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

<?$this->page_content=ob_get_contents();
ob_end_clean();

include "templates/u235/template.php";
