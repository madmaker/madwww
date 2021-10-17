<?php
namespace uAuth;
use PDO;
use PDOException;
use processors\uFunc;
use uAuth_avatar;
use uCore;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'uAuth/inc/avatar.php';

class users_list_admin {
    /**
     * @var uFunc
     */
    public $uFunc;
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var uAuth_avatar
     */
    public $avatar;
    /**
     * @var uCore
     */
    private $uCore;
    private $user_grId2Title;
    public $groups;
    public $users;
    /**
     * @var string
     */
    private $statusRequest;
    /**
     * @var bool
     */
    public $inactive;
    /**
     * @var bool
     */
    public $isTrash;

    public function text($str) {
        return $this->uCore->text(array('uAuth','users_list_admin'),$str);
    }
    private function checkData() {
        //Define user status
        $this->isTrash=false;
        $this->inactive=false;
        $this->statusRequest='active';
        if(isset($_GET['trash']) && $_GET['trash'] === 'yes') {
            $this->isTrash=true;
            $this->statusRequest='banned';
        }
        if(isset($_GET['inactive']) && $_GET['inactive'] === 'yes') {
            $this->inactive=true;
            $this->statusRequest='activation_needed';
        }

    }
    private function getUserList() {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT DISTINCT
            u235_users.user_id,
            avatar_timestamp,
            firstname,
            secondname,
            lastname,
            email,
            regDate
            FROM
            u235_users
            JOIN
            u235_usersinfo
            ON
            u235_usersinfo.user_id=u235_users.user_id
            WHERE
            u235_usersinfo.site_id=:site_id AND
            u235_usersinfo.status=:status  AND
            u235_users.status=:users_status
            ORDER BY
            firstname DESC
            ');
            $users_status='active';
            $status=$this->statusRequest;
            $site_id=site_id;
            $stm->bindParam(':users_status', $users_status,PDO::PARAM_STR);
            $stm->bindParam(':status', $status,PDO::PARAM_STR);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('10'/*.$e->getMessage()*/);}

        //Get total users number
        /** @noinspection PhpUndefinedVariableInspection */
        for($i=0; $this->users[$i]=$stm->fetch(PDO::FETCH_OBJ); $i++) {
            continue;
        }

        //Get user groups
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
            module,
            user_group_id
            FROM
            u235_groups
            ');
            $site_id=site_id;
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('20'/*.$e->getMessage()*/);}

        for($i=0;$this->groups[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++) {
            if($this->uFunc->mod_installed($this->groups[$i]->module)) {
                $this->user_grId2Title[$this->groups[$i]->user_group_id] = 1;
            }
        }
    }
    public function user_id2groups($user_id) {
        try {
            $stm=$this->uFunc->pdo('uAuth')->prepare('SELECT
            group_id
            FROM
            u235_usersinfo_groups
            WHERE
            user_id=:user_id AND
            site_id=:site_id
            ');
            $site_id=site_id;
            $stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            $stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            $stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        $groups='';
        /** @noinspection PhpUndefinedVariableInspection */
        while($group=$stm->fetch(PDO::FETCH_OBJ)) {
            if(isset($this->user_grId2Title[$group->group_id])) {
                $groups .= $this->uCore->uInt->text(array('processors', 'acl_group_name'), 'acl_group_name_' . $group->group_id) . '<br>';
            }
        }
        return $groups;
    }

    public function __construct(&$uCore) {
        if(!isset($uCore)) {$uCore = new uCore();}

        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $this->uSes=new uSes($uCore);

        $this->uCore->page['page_title']=$this->text('Page name'/*Администрирование пользователей сайта*/);

        $this->uCore->uInt_js('uAuth','users_list_admin');

        $this->uFunc->incJs(staticcontent_url.'js/lib/phpjs/functions/datetime/date.js');
        $this->uFunc->incCss(staticcontent_url.'css/uAuth/uAuth.min.css');
        $this->uFunc->incCss(staticcontent_url.'css/templates/u235/uAuth/uAuth.min.css');
        $this->uFunc->incJs(staticcontent_url.'js/uAuth/users_list_admin.min.js');

        $this->checkData();
        $this->getUserList();

        $this->avatar=new uAuth_avatar($this->uCore);
    }
}
$uAuth=new users_list_admin($this);

$pnl=&$GLOBALS['TEMPLATE']['mod_panel'];

$pnl='<ul class="u235_top_menu">';
if($uAuth->isTrash) {
    $pnl .= '
<li><a href="javascript:void(0);" id="uAuth_restoreBtn">' . $uAuth->text('Restore selected users - btn txt'/*Восстановить выбранных*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '?inactive=yes">' . $uAuth->text('Not activated users - btn txt'/*Не активированные*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '">' . $uAuth->text('Active users - btn txt'/*Активные*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '?trash=yes" id="uAuth_watchBtn">' . $uAuth->text('Banned users - btn txt'/*Заблокированные*/) . '</a></li>';
}
else if($uAuth->inactive) {
    $pnl .= '
<li><a href="javascript:void(0);" id="uAuth_deleteBtn">' . $uAuth->text('Ban users - btn txt'/*Заблокировать*/) . '</a></li>
<li><a href="javascript:void(0);"  id="uAuth_activateBtn">' . $uAuth->text('Activate selected users - btn txt'/*Активировать выбранных*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '?inactive=yes" id="uAuth_watchBtn">' . $uAuth->text('Not activated users - btn txt'/*Не активированные*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '">' . $uAuth->text('Active users - btn txt'/*Активные*/) . '</a></li>
<li><a href="' . u_sroot . 'uAuth/' . $this->page_name . '?trash=yes">' . $uAuth->text('Banned users - btn txt'/*Заблокированные*/) . '</a></li>';
}
else {
    if($uAuth->uSes->access(30)) {
        $pnl .= '<li><a href="javascript:void(0);" onclick="uAuth.new_user_init()" >' . $uAuth->text('Create user - btn txt'/*Создать*/) . '</a></li>';
    }
    $pnl.='
<li><a href="javascript:void(0);" id="uAuth_deleteBtn">'.$uAuth->text('Ban users - btn txt'/*Заблокировать*/).'</a></li>
<li><a href="'.u_sroot.'uAuth/'.$this->page_name.'?inactive=yes">'.$uAuth->text('Not activated users - btn txt'/*Не активированные*/).'</a></li>
<li><a href="'.u_sroot.'uAuth/'.$this->page_name.'" id="uAuth_watchBtn">'.$uAuth->text('Active users - btn txt'/*Активные*/).'</a></li>
<li><a href="'.u_sroot.'uAuth/'.$this->page_name.'?trash=yes">'.$uAuth->text('Banned users - btn txt'/*Заблокированные*/).'</a></li>';
}

$pnl.='</ul>';
ob_start();

?>

<div><?=$GLOBALS['TEMPLATE']['mod_panel'] ?></div>

<div class="uAuth u235_admin" id="uAuth_users">
    <div class="pages_number"><?=$uAuth->text('Total users amount'/*Всего пользователей: */)?><?=count($uAuth->users)-1?></div>
    <h1><?=$this->page['page_title'] ?></h1>
    <div class="uAuth_users list"></div>
</div>

    <script type="text/javascript">
        if(typeof uAuth==="undefined") {
            uAuth = {};
            uAuth.users_list = [];

            uAuth.user_id = [];
            uAuth.avatar_timestamp = [];
            uAuth.avatar_src = [];
            uAuth.firstname = [];
            uAuth.secondname = [];
            uAuth.lastname = [];
            uAuth.email = [];
            uAuth.regDate = [];
            uAuth.groups = [];
            uAuth.user_show = [];
            uAuth.user_sel = [];
            uAuth.user_id2index = [];
        }
        //groups
        uAuth.group_id=[];
        uAuth.group_title=[];
        uAuth.group_id2ind=[];

        <?php
        for($i=0;$group=$uAuth->groups[$i];$i++) {
        if($this->uFunc->mod_installed($group->module)){?>
            uAuth.group_id[<?=$i?>]=<?=$group->user_group_id?>;
            uAuth.group_title[<?=$i?>]="<?=$this->uInt->text(array('processors','acl_group_name'), 'acl_group_name_' .$group->user_group_id)?>";
            uAuth.group_id2ind[<?=$group->user_group_id?>]=<?=$i?>;
            <?}
        }?>

        uAuth.group_id[<?=$i?>]=13;
        uAuth.group_title[<?=$i?>]="Super Admin";
        uAuth.group_id2ind[13]=<?=$i?>;

        //user's information

        <?php
        for($i=0;$data=$uAuth->users[$i];$i++) { ?>
        uAuth.user_id[<?=$i?>]=<?=$data->user_id?>;
        uAuth.avatar_timestamp[<?=$i?>]="<?=$data->avatar_timestamp?>";
        uAuth.avatar_src[<?=$i?>]="<?=$uAuth->avatar->get_avatar('admin_list',$data->user_id,$data->avatar_timestamp)?>";
        uAuth.firstname[<?=$i?>]="<?=rawurlencode(uString::sql2text($data->firstname))?>";
        uAuth.secondname[<?=$i?>]="<?=rawurlencode(uString::sql2text($data->secondname))?>";
        uAuth.lastname[<?=$i?>]="<?=rawurlencode(uString::sql2text($data->lastname))?>";
        uAuth.email[<?=$i?>]="<?=$data->email?>";
        uAuth.regDate[<?=$i?>]=<?=$data->regDate?>;
        uAuth.groups[<?=$i?>]="<?=rawurlencode($uAuth->user_id2groups($data->user_id))?>";
        uAuth.user_show[<?=$i?>]=true;
        uAuth.user_sel[<?=$i?>]=false;
        uAuth.user_id2index[<?=$data->user_id?>]=<?=$i?>;
        <?}?>
    </script>

<?php if($uAuth->uSes->access(30)) {?>
    <!-- Modals -->
    <div class="modal fade" id="uAuth_new_user_dg" tabindex="-1" role="dialog" aria-labelledby="uAuth_new_user_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uAuth_new_user_dgLabel"><?=$uAuth->text('New user - dg title'/*Новый пользователь*/)?></h4>
                </div>
                <div class="modal-body">
                    <div class="text-info" id="uAuth_new_user_text_info" style="display: none"></div>
                    <div class="text-danger" id="uAuth_new_user_text_danger" style="display: none"></div>
                    <div class="form-group">
                        <label for="uAuth_new_user_firstname"><?=$uAuth->text('Name - input label'/*Имя:*/)?><sup>*</sup></label>
                        <input type="text" id="uAuth_new_user_firstname" name="uAuth_new_user_firstname" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="uAuth_new_user_secondname"><?=$uAuth->text('Second name - input label'/*Отчество:*/)?></label>
                        <input type="text" id="uAuth_new_user_secondname" name="uAuth_new_user_secondname" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="uAuth_new_user_lastname"><?=$uAuth->text('Last name - input label'/*Фамилия:*/)?><sup>*</sup></label>
                        <input type="text" id="uAuth_new_user_lastname" name="uAuth_new_user_lastname" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="uAuth_new_user_email">E-mail*:</label>
                        <input type="text" id="uAuth_new_user_email" name="uAuth_new_user_email" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?=$uAuth->text('Close - dg btn'/*Закрыть*/)?></button>
                    <button type="button" class="btn btn-primary" onclick="uAuth.new_user();"><?=$uAuth->text('Create - dg btn'/*Создать*/)?></button>
                </div>
            </div>
        </div>
    </div>
<?}?>

<?$this->page_content=ob_get_clean();
include 'templates/u235/template.php';?>
