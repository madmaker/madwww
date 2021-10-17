<?
namespace uPeople\admin;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

use PDO;
use PDOException;
use processors\uFunc;

class get_users_list_bg {
    private $uFunc;
    private $uSes;
    private $uCore,$orderBy,$orderDir;
    public $qUsers,$users_num,$qUser_groups,$user_grId2Title,$isTrash,$inactive,$sort_by;
    private function check_data() {
        //FILTER
        if(isset($_POST['sort_by'])) {
            if($_POST['sort_by']=='id_desc') {
                $this->sort_by='id_desc';
                $this->orderBy='user_id';
                $this->orderDir='ASC';
            }
            elseif($_POST['sort_by']=='firstname_asc') {
                $this->sort_by='firstname_asc';
                $this->orderBy='firstname';
                $this->orderDir='DESC';
            }
            elseif($_POST['sort_by']=='firstname_desc') {
                $this->sort_by='firstname_desc';
                $this->orderBy='firstname';
                $this->orderDir='ASC';
            }
            elseif($_POST['sort_by']=='secondname_asc') {
                $this->sort_by='secondname_asc';
                $this->orderBy='secondname';
                $this->orderDir='DESC';
            }
            elseif($_POST['sort_by']=='secondname_desc') {
                $this->sort_by='secondname_desc';
                $this->orderBy='secondname';
                $this->orderDir='ASC';
            }
            elseif($_POST['sort_by']=='lastname_asc') {
                $this->sort_by='lastname_asc';
                $this->orderBy='lastname';
                $this->orderDir='DESC';
            }
            elseif($_POST['sort_by']=='lastname_desc') {
                $this->sort_by='lastname_desc';
                $this->orderBy='lastname';
                $this->orderDir='ASC';
            }
            else {
                $this->sort_by='id_asc';
                $this->orderBy='user_id';
                $this->orderDir='DESC';
            }
        }
        else {
            $this->sort_by='id_asc';
            $this->orderBy='user_id';
            $this->orderDir='DESC';
        }
    }
    private function getUserList() {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT
            user_id,
            avatar_timestamp,
            firstname,
            secondname,
            lastname
            FROM
            u235_people
            WHERE
            site_id=:site_id AND
            (status='' OR status IS NULL)
            ORDER BY
            ".$this->orderBy." ".$this->orderDir."
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            /** @noinspection PhpUndefinedMethodInspection */
            $this->qUsers=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        //Get total users number
        $this->users_num=count($this->qUsers);

        //Get user groups
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT
            gr_id,
            gr_title
            FROM
            u235_groups
            WHERE
            site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
            /** @noinspection PhpUndefinedMethodInspection */
            $this->qUser_groups=$stm->fetchAll(PDO::FETCH_OBJ);
        }
        catch(PDOException $e) {$this->uFunc->error('50'/*.$e->getMessage()*/);}

        $qUser_groups_num=count($this->qUser_groups);
        for($i=0;$i<$qUser_groups_num;$i++) {
            $group=$this->qUser_groups[$i];
            $this->user_grId2Title[$group->gr_id]=$group->gr_title;
        }
    }
    public function user_id2groups($user_id) {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT
            u235_groups.gr_id,
            gr_title
            FROM
            u235_people_groups
            JOIN
            u235_groups
            ON 
            u235_groups.gr_id=u235_people_groups.gr_id AND
            u235_groups.site_id=u235_people_groups.site_id
            WHERE
            user_id=:user_id AND
            u235_groups.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();

            $groups='';
            /** @noinspection PhpUndefinedMethodInspection */
            while($group=$stm->fetch(PDO::FETCH_OBJ)) {
                $groups.='<a href="'.u_sroot.'uCat/users_by_gr/'.$group->gr_id.'" target="_blank">'.$group->gr_title.'</a><br>';
            }
            return $groups;
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}
        return "";
    }
    private function print_list() {
        $this->getUserList();?>
        <div>Всего людей: <span><?=$this->users_num?></span></div>

        <table class="table table-striped table-hover table-condensed uPeople_users_list">
        <tr>
            <th>#
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('id_desc')"  class="btn btn-xs btn-default <?=$this->sort_by==='id_desc'?'active':''?> uTooltip" title="Сортировать по убыванию"><span class="icon-sort-alt-up"></span></a>
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('id_asc')" class="btn btn-xs btn-default <?=$this->sort_by==='id_asc'?'active':''?> uTooltip" title="Сортировать по возрастанию"><span class="icon-sort-alt-down"></span></a>
                </th>
            <th></th>
            <th>Имя
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('firstname_desc')"  class="btn btn-xs btn-default <?=$this->sort_by==='firstname_desc'?'active':''?> uTooltip" title="Сортировать по убыванию"><span class="icon-sort-alt-up"></span></a>
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('firstname_asc')" class="btn btn-xs btn-default <?=$this->sort_by==='firstname_asc'?'active':''?> uTooltip" title="Сортировать по возрастанию"><span class="icon-sort-alt-down"></span></a>
                </th>
            <th>Отчество
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('secondname_desc')"  class="btn btn-xs btn-default <?=$this->sort_by==='secondname_desc'?'active':''?> uTooltip" title="Сортировать по убыванию"><span class="icon-sort-alt-up"></span></a>
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('secondname_asc')" class="btn btn-xs btn-default <?=$this->sort_by==='secondname_asc'?'active':''?> uTooltip" title="Сортировать по возрастанию"><span class="icon-sort-alt-down"></span></a>
                </th>
            <th>Фамилия
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('lastname_desc')"  class="btn btn-xs btn-default <?=$this->sort_by==='lastname_desc'?'active':''?> uTooltip" title="Сортировать по убыванию"><span class="icon-sort-alt-up"></span></a>
                <a href="javascript:void(0);" onclick="uPeople_users_list_admin.load_user_list('lastname_asc')" class="btn btn-xs btn-default <?=$this->sort_by==='lastname_asc'?'active':''?> uTooltip" title="Сортировать по возрастанию"><span class="icon-sort-alt-down"></span></a>
                </th>
            <th>Группа</th>
            <th></th>
        </tr>
        <?for($i=0;$data=$i<$this->users_num;$i++) {
            $user=$this->qUsers[$i];?>
        <tr id="uPeople_users_list_admin_rec_<?=$user->user_id?>" class="uPeople_users_list_tr">
            <td><?=$user->user_id?></td>
            <td class="clickable">
                <a href="<?=u_sroot?>uPeople/profile_admin/<?=$user->user_id?>">
                    <img alt="" class="avatar" src="<?=u_sroot.'uPeople/avatars/'.(!(int)$user->avatar_timestamp?'default':(site_id.'/'.$user->user_id.'_sm.jpg?'.$user->avatar_timestamp))?>">
                </a>
            </td>
            <td class="clickable">
                <a href="<?=u_sroot?>uPeople/profile_admin/<?=$user->user_id?>"><?=$user->firstname?></a>
            </td>
            <td class="clickable">
                <a href="<?=u_sroot?>uPeople/profile_admin/<?=$user->user_id?>"><?=$user->secondname?></a>
            </td>
            <td class="clickable">
                <a href="<?=u_sroot?>uPeople/profile_admin/<?=$user->user_id?>"><?=$user->lastname?></a>
            </td>
            <td><?=$this->user_id2groups($user->user_id)?></td>
            <td><button class="btn btn-danger uPeople_users_list_admin_delete_btn" onclick="uPeople_users_list_admin.delete_user(<?=$user->user_id?>)" title="Удалить"><span class="icon-cancel"></span></button></td>
            </tr>
        <?}?>
        </table>
    <?}
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new \uSes($this->uCore);
        if(!$this->uSes->access(10)) die("forbidden");

        $this->uFunc=new uFunc($this->uCore);

        $this->check_data();

        $this->print_list();
    }
}
new get_users_list_bg($this);