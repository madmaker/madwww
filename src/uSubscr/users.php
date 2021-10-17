<?php
class uSubscr_users {
    private $uCore;
    public $q_users;
    private function get_users() {
            if(!$this->q_users=$this->uCore->query("uSubscr","SELECT
            `user_id`,
            `user_name`,
            `user_email`,
            `status`,
            `unsubscribed`,
            `admin_made`
            FROM
            `u235_users`
            WHERE
            `site_id`='".site_id."'
            ORDER BY
            `user_id` ASC
            ")) $this->uCore->error(1);
    }
    private function del_droped_users() {
            $droped_user_lifetime=time()-604800;//1 week

            if(!$query=$this->uCore->query("uSubscr","SELECT
             `user_id`
             FROM
             `u235_users`
             WHERE
             `timestamp`<'".$droped_user_lifetime."' AND
             `unsubscribed`='0' AND
             `status`='deleted' AND
             `site_id`='".site_id."'
             ")) $this->uCore->error(2);
            while($user=$query->fetch_object()) {
                if(!$this->uCore->query("uSubscr","DELETE FROM
                `u235_users_groups`
                WHERE
                `user_id`='".$user->user_id."' AND
                `site_id`='".site_id."'
                ")) $this->uCore->error(3);
            }
            if(!$this->uCore->query("uSubscr","DELETE FROM
            `u235_users`
            WHERE
            `timestamp`<'".$droped_user_lifetime."' AND
            `unsubscribed`='0' AND
            `status`='deleted' AND
            `site_id`='".site_id."'
            ")) $this->uCore->error(4);

        //get total user's number
        if(!$query=$this->uCore->query("uSubscr","SELECT
        COUNT(`user_id`)
        FROM
        `u235_users`
        WHERE
        `admin_made`='1' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(5);
        $qr=$query->fetch_assoc();

        //update total user's count
        if(!$this->uCore->query("uSubscr","UPDATE
        `u235_limits`
        SET
        `users_count`='".$qr["COUNT(`user_id`)"]."'
        ")) $this->uCore->error(6);
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->del_droped_users();

        $this->get_users();
    }
}
$uSubscr=new uSubscr_users($this);

$this->uFunc->incJs(u_sroot.'uSubscr/js/'.$this->page_name.'.js');

ob_start();
?>
<h1><?=$this->page['page_title']?></h1>
<div style="float: right; display: table">
    <div class="btn-group">
        <button class="btn btn-default btn-sm" id="uSubscr_createBtn" onclick="uSubscr.new_user_dg()">Добавить получателя</button>
        <button class="btn btn-default btn-sm uTooltip" id="uSubscr_restoreBtn" title="Вы можете восстановить удаленного пользователя">Восстановить</button>
        <button class="btn btn-default btn-sm uTooltip" id="uSubscr_deleteBtn" title="Если пользователь удален, то он не будет получать рассылки">Удалить</button>
    </div>
</div>

<p class="clearfix">&nbsp;</p>

<div class="uSubscr row">
    <div id="uSubscr_list" class="col-md-12"></div>
</div>

    <script type="text/javascript">
        if(typeof uSubscr==="undefined") {
            uSubscr={};
            uSubscr.user_id=[];
            uSubscr.user_name=[];
            uSubscr.user_email=[];
            uSubscr.unsubscribed=[];
            uSubscr.admin_made=[];
            uSubscr.status=[];
            uSubscr.user_show=[];
            uSubscr.user_sel=[];
            uSubscr.user_id2index=[];
        }
        <? for($i=0;$data=$uSubscr->q_users->fetch_object();$i++) {?>
        i=<?=$i?>;
        uSubscr.user_id[i]=<?=$data->user_id?>;
        uSubscr.user_name[i]="<?=rawurlencode(uString::sql2text($data->user_name))?>";
        uSubscr.user_email[i]="<?=rawurlencode($data->admin_made=='1'?uString::sql2text($data->user_email):uString::hide_email_part($data->user_email))?>";
        uSubscr.status[i]="<?=$data->status?>";
        uSubscr.unsubscribed[i]=<?=$data->unsubscribed?>;
        uSubscr.admin_made[i]=<?=$data->admin_made?>;
        uSubscr.user_show[i]=true;
        uSubscr.user_sel[i]=false;
        uSubscr.user_id2index[uSubscr.user_id[i]]=i;
        <?}?>
    </script>

    <div class="modal fade" id="uSubscr_new_user_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_new_user_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_new_user_dgLabel">Новый получатель</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_new_user_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_new_user_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label>Имя получателя:</label>
                        <input type="text" id="uSubscr_new_user_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email получателя:</label>
                        <input type="email" id="uSubscr_new_user_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.new_user()">Создать</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSubscr_edit_user_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_edit_user_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_edit_user_dgLabel">Правка получателя</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_edit_user_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_edit_user_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label>Имя получателя:</label>
                        <input type="text" id="uSubscr_edit_user_name" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Email получателя:</label>
                        <input type="email" id="uSubscr_edit_user_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Отмена</button>
                    <button type="button" class="btn btn-primary" onclick="uSubscr.edit_user()">Сохранить</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uSubscr_user_groups_dg" tabindex="-1" role="dialog" aria-labelledby="uSubscr_user_groups_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uSubscr_user_groups_dgLabel">Группы, на который подписан пользователь</h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uSubscr_user_groups_text_info" style="display: none"></div>
                    <div class="text-danger" id="uSubscr_user_groups_text_danger" style="display: none"></div>
                    <div class="row" id="uSubscr_user_groups_cnt"></div>

                </div>
            </div>
        </div>
    </div>

<? $this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
?>
