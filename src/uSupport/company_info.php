<?php
namespace uSupport;
use PDO;
use PDOException;
use processors\uFunc;
use uString;

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once 'uAuth/inc/avatar.php';
require_once 'inc/com_avatar.php';
require_once "uSupport/classes/common.php";

class admin_com_info {
    public $user_id2notify_about_new_requests;
    public $uSup;
    private $uCore;
    public $uFunc;
    public $uSes;
    public $companies_ar;
    public $com;
    public $users_ar;
    public $unattachedUsers_ar,
        $is_com_admin,
    $com_id, $com_title, $logo_timestamp, $qAdmins, $qDomains,
        $avatar,$user_avatar;
    private function checkData() {
        if(!isset($this->uCore->url_prop[1])) return false;
        $this->com_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->com_id)) return false;
        return true;
    }
    private function check_access() {
        $this->is_com_admin=false;
        //consultant or operator
        if($this->uSes->access(9)||$this->uSes->access(8)) return true;

        //check if client or admin of this company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            admin
            FROM
            u235_com_users
            WHERE
            user_id=:user_id AND
            com_id=:com_id AND
            site_id=:site_id
            ");
            $site_id=site_id;
            $user_id=$this->uSes->get_val("user_id");
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            if(!$com=$stm->fetch(PDO::FETCH_OBJ)) return false;
            if((int)$com->admin===1) return $this->is_com_admin=true;
            else return true;
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}
        return 0;
    }
    public function getComInfo() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uSup")->prepare("SELECT
            com_title,
            two_level,
            logo_timestamp
             FROM
             u235_comps
             WHERE
             com_id=:com_id AND
             site_id=:site_id 
             ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            return $stm->fetch(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}
        return array();
    }
    private function getData() {
        //company title
        $this->com_title=uString::sql2text($this->com->com_title,1);
        $this->logo_timestamp=$this->com->logo_timestamp;

        //get company's admins
        $stm=$this->uSup->get_com_users("user_id,
            notify_about_new_requests,
            admin",$this->com_id);

        $q_admins=$q_users='1=0';
        $q_unattached_users='1=1';
        /** @noinspection PhpUndefinedMethodInspection */
        while($qr=$stm->fetch(PDO::FETCH_OBJ)) {
            if($qr->admin=='1') $q_admins.=" OR u235_users.user_id=".$qr->user_id." ";
            else $q_users.=" OR u235_users.user_id=".$qr->user_id." ";
            $this->user_id2notify_about_new_requests[$qr->user_id]=$qr->notify_about_new_requests;

            $q_unattached_users.=" AND u235_users.user_id!=".$qr->user_id." ";
        }

        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->qAdmins=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname,
            avatar_timestamp
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id AND
            u235_usersinfo.status=u235_users.status
            WHERE
            u235_users.status='active' AND
            (".$q_admins.") AND
            u235_usersinfo.site_id=:site_id
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->qAdmins->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->qAdmins->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}


        //get company's email domains
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->qDomains=$this->uFunc->pdo("uSup")->prepare("SELECT
            domain_id,
            domain
            FROM
            u235_com_email_domains
            WHERE
            com_id=:com_id AND
            site_id=:site_id
            ORDER BY
            domain ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$this->qDomains->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->qDomains->bindParam(':com_id', $this->com_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$this->qDomains->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        //get company's users
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname,
            avatar_timestamp
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.status=u235_users.status AND
            u235_usersinfo.user_id=u235_users.user_id
            WHERE
            u235_users.status='active' AND
            (".$q_users.") AND
            u235_usersinfo.site_id=:site_id
            ORDER BY
            firstname ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->users_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        //get site's users not attached to company
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uAuth")->prepare("SELECT DISTINCT
            u235_users.user_id,
            firstname,
            secondname,
            lastname,
            avatar_timestamp
            FROM
            u235_users
            JOIN 
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id AND
            u235_usersinfo.status=u235_users.status
            WHERE
            u235_users.status='active' AND
            (".$q_unattached_users.") AND
            u235_usersinfo.site_id=:site_id
            ORDER BY
            `firstname` ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection PhpUndefinedMethodInspection */
            for($i=0; $this->unattachedUsers_ar[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {};
        }
        catch(PDOException $e) {$this->uFunc->error('70'/*.$e->getMessage()*/);}
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new \uSes($this->uCore);
        $this->uSup=new common($this->uCore);

        if($this->uSes->access(2)) {
            if(!$this->checkData()) {
                header('Location: '.u_sroot.$this->uCore->mod.'/companies');
                exit;
            }
            if(!$this->check_access()) {
                header('Location: '.u_sroot.$this->uCore->mod.'/requests');
                exit;
            }
            if(!$this->com=$this->getComInfo()) {
                header('Location: '.u_sroot.$this->uCore->mod.'/companies');
                exit;
            }
            $this->getData();

            $this->avatar=new \uSup_com_avatar($this->uCore);
            $this->user_avatar=new \uAuth_avatar($this->uCore);
        }
    }
}
$uSup=new admin_com_info($this);
if(!isset($_POST['update_page'])) {
    $this->uFunc->incJs(u_sroot.'uSupport/js/company_info.min.js');
    if($uSup->uSes->access(201)||$uSup->is_com_admin) {
        $this->uFunc->incJs(u_sroot.'uSupport/js/company_info_admin.min.js');
        $this->uFunc->incJs(u_sroot.'js/u235/jquery/jquery.uranium235plugins.min.js');
    }
    ob_start();
}
if($uSup->uSes->access(2)) {
    if(!isset($_POST['update_page'])) {?>
    <div class="container-fluid" id="uSup_company_info_container">
    <?}?>
        <div><a href="<?=u_sroot?>uSupport/requests" class="btn btn-default"><span class="icon-left"></span> К запросам</a></div>

        <h1><span id="uSup_company_info_com_title_header"><?=$uSup->com_title?><?if($uSup->is_com_admin||$uSup->uSes->access(201)){?></span> <button class="btn btn-default btn-sm uTooltip" title="Изменить название компании" onclick="uSup.change_title_init()"><span class="icon-pencil"></span></button><?}?>

            <?if($uSup->uSes->access(201)){?>
                <button class="btn btn-danger pull-right" onclick="uSup.remove_com_init()"><span class="icon-cancel"></span> Удалить компанию</button>
            <?}?>
        </h1>
        <?if($uSup->uSes->access(201)) {?>
        <div><label><input type="checkbox" id="uSup_two_level" <?=(int)$uSup->com->two_level?'checked':''?>>&nbsp;</label></div>
        <?}?>
    <div class="row">
        <div class="col-md-3" id="uSup_com_upload_avatar_container">
            <img id="uSup_com_upload_avatar_img" class="img-thumbnail" src="<?=$uSup->avatar->get_avatar('com_page',$uSup->com_id,$uSup->logo_timestamp)?>">
            <? if($uSup->is_com_admin||$uSup->uSes->access(201)) {?>
                <div class="" id="uSup_com_upload_avatar_btn_group">
                    <button class="btn btn-default btn-sm" id="uSup_com_upload_avatar_btn">Загрузить лого</button>
                    <button class="btn btn-danger  btn-sm" title="Удалить лого компании" onclick="uSup.del_com_avatar()"><span class="icon-cancel"></span></button>
                </div>
                <div id="filelist"></div>
            <?}?>
        </div>
        <div class="domains col-md-3">
            <h3>Почтовые домены <?if($uSup->uSes->access(201)||$uSup->is_com_admin) {?><button class="btn btn-success btn-xs uTooltip" title="Добавить домен" onclick="jQuery('#uSup_newDomain_dg').modal('show');"><span class="icon-plus"></span></button><?}?></h3>
            <p class="text-muted">Пользователи с email в этих доменах автоматически добавляются к компании</p>
            <table class="table table-hover table-striped">
            <? while($data=$uSup->qDomains->fetch(PDO::FETCH_ASSOC)) {?>
                <tr><td><?=$data['domain']?></td>
                    <? if($uSup->is_com_admin||$uSup->uSes->access(201)) {?>
                        <td><button class="btn btn-danger btn-xs uTooltip" title="Удалить домен" onclick="uSup.del_domain(<?=$data['domain_id']?>)"><span class="icon-cancel"></span></button></td>
                    <?}?>
                </tr>
            <?}?>
            </table>
        </div>
        <div class="users col-md-6">
            <h3>Администраторы</h3>
            <p class="text-muted">Администраторы компании имеют функции управления компанией</p>
            <div class="row">
            <? while($user=$uSup->qAdmins->fetch(PDO::FETCH_OBJ)) {?>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <div class="thumbnail">
                            <a href="<?=u_sroot?>uAuth/profile/<?=$user->user_id?>"  target="_blank">
                                <img src="<?=$uSup->user_avatar->get_avatar('uSup_com_users_list',$user->user_id,$user->avatar_timestamp)?>"  style="height: 50px">
                            </a>
                        <div class="caption" style="text-align: center">
                                    <a href="<?=u_sroot?>uAuth/profile/<?=$user->user_id?>"  target="_blank">
                                        <?=$user->firstname?> <?=$user->secondname?> <?=$user->lastname?>
                                    </a>
                                    <? if($uSup->is_com_admin||$uSup->uSes->access(201)) {?>
                                        <div><small class="text-muted">#<?=$user->user_id?></small> <button class="btn btn-xs btn-danger uTooltip" title="Разжаловать до обычного пользователя" onclick="uSup.delUser(<?=$user->user_id?>,'admin')"><span class="icon-down-hand"></span></button>
                                            <button id="notify_about_new_requests_user_<?=$user->user_id?>" class="btn btn-xs btn-<?=(int)$uSup->user_id2notify_about_new_requests[$user->user_id]?'success':'danger'?> uTooltip" title="Уведомлять о новых запросах" onclick="uSup.notify_about_new_requests(<?=$user->user_id?>)"><span class="icon-mail-alt"></span></button></div>
                                    <?}?>
                        </div>
                    </div>
                </div>
            <?}?>
            </div>
        </div>
    </div>


                <h2>Пользователи<? if($uSup->uSes->access(201)) {?> <button class="btn btn-success btn-xs uTooltip" title="Добавить пользователя из зарегистрированных на сайте" onclick="uSup.new_user_init()"><span class="icon-plus"></span> из зарегистрированных</button><?}?>
                        <? if($uSup->is_com_admin||$uSup->uSes->access(201)) {?><button class="btn btn-success btn-xs uTooltip" title="Добавить пользователя по email" onclick="uSup.new_user_by_email_init();"><span class="icon-plus"></span> по email</button><?}?>
                </h2>
        <? if(
            count($uSup->users_ar)>1
        ) {?>
            <div class="input-group">
                <input id="uSup_com_users_filter" class="form-control" placeholder="Фильтр пользователей" onkeyup="uSup.com_users_filter()">
                <span class="input-group-btn">
                    <button id="uSup_com_users_filter_btn" class="btn btn-default" type="button"><span class="icon-search" onclick="uSup.com_users_filter()"></span></button>
                </span>
            </div>
            <div class="row">&nbsp;</div>
        <?}?>
        <div class="row" id="uSup_com_users_list">
                <? for($i=0;$user=$uSup->users_ar[$i];$i++) {?>
                    <div class="col-md-3 col-sm-6 col-xs-12 uSup_com_user_item">
                        <div class="thumbnail uSup_company_info_user">
                            <a href="<?=u_sroot?>uAuth/profile/<?=$user->user_id?>"  target="_blank">
                                <img src="<?=$uSup->user_avatar->get_avatar('uSup_com_users_list',$user->user_id,$user->avatar_timestamp)?>"  style="height: 50px; margin-top: 10px;">
                            </a>
                            <div class="caption" style="text-align: center">
                                <a href="<?=u_sroot?>uAuth/profile/<?=$user->user_id?>"  target="_blank">
                                    <?=$user->firstname?> <?=$user->secondname?> <?=$user->lastname?>
                                </a>
                                    <? if($uSup->uSes->access(201)||$uSup->is_com_admin) {?>
                                        <div>
                                            <small class="text-muted">#<?=$user->user_id?></small>
                                            <button class="btn btn-xs btn-success uTooltip" title="Повысить до администратора компании" onclick="uSup.new_admin(<?=$user->user_id?>)"><span class="icon-up-hand"></span></button>
                                            <button class="btn btn-xs btn-danger uTooltip" title="Убрать из компании" onclick="uSup.delUser(<?=$user->user_id?>,'user')"><span class="icon-cancel"></span></button>
                                        </div>
                                    <?}?>
                            </div>
                        </div>
                    </div>
                <?}?>
        </div>
    <?if(!isset($_POST['update_page'])) {?>
    </div>

    <script type="text/javascript">
        <? if($uSup->is_com_admin||$uSup->uSes->access(201)) {?>
        if(typeof uSup==="undefined") {
            uSup={};
        }
        uSup.com_id=<?=$uSup->com_id?>;
        uSup.com_title="<?=rawurlencode(uString::sql2text($uSup->com_title,1))?>";
        <?}?>
    </script>

    <? if($uSup->is_com_admin||$uSup->uSes->access(201)) include_once 'inc/company_info_admin_dialogs.php';
    }
} else {
    if(!isset($_POST['update_page'])) {?>
    <div class="jumbotron">
        <h1 class="page-header">Техническая поддержка</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}
    else {echo 'forbidden';}
}
if(!isset($_POST['update_page'])) {
    $this->page_content=ob_get_contents();
    ob_end_clean();

    /** @noinspection PhpIncludeInspection */
    include "templates/template.php";
}
?>
