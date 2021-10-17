<?
namespace uPeople;

use PDO;
use PDOException;
use processors\uFunc;
use uString;

//require_once "lib/htmlpurifier/library/HTMLPurifier.auto.php";
require_once "processors/classes/uFunc.php";

class profile_admin_edit{
    public $uFunc;
    public $purifier;
    public $fields_ar;
    private $uCore;
    public $user,$ses_hack,$user_id,$q_user_groups;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->uFunc->error(10);
        if(!uString::isDigits($this->uCore->url_prop[1])) $this->uFunc->error(20);
        $this->user_id=$this->uCore->url_prop[1];
    }
    private function get_user_data() {
        //get site's fields
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT
            field_id,
            label,
            field_comment,
            field_type
            FROM
            u235_fields
            WHERE
            site_id=:site_id
            ORDER BY
            sort ASC
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('30'/*.$e->getMessage()*/);}

        //get user's data
        $qu="SELECT ";
        for($i=0;$this->fields_ar[$i]=$stm->fetch(PDO::FETCH_OBJ);$i++) {
            $qu.="field_".$this->fields_ar[$i]->field_id.",\n";
        }
        $qu.="firstname,
        secondname,
        lastname,
        avatar_timestamp
        FROM
        u235_people
        WHERE
        user_id=:user_id AND
        (status='' OR status IS NULL) AND
        site_id=:site_id
        ";

        //get user's data
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare($qu);
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('40'/*.$e->getMessage()*/);}

        if(!$this->user=$stm->fetch(PDO::FETCH_OBJ)) $this->uFunc->error(50);

        $this->user->firstname=uString::sql2text($this->user->firstname);
        $this->user->secondname=uString::sql2text($this->user->secondname);
        $this->user->lastname=uString::sql2text($this->user->lastname);

        //get user's groups
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $stm=$this->uFunc->pdo("uPeople")->prepare("SELECT DISTINCT
            gr_title
            FROM
            u235_people_groups
            JOIN 
            u235_groups
            ON 
            u235_people_groups.site_id=u235_groups.site_id AND
            u235_people_groups.gr_id=u235_groups.gr_id
            WHERE
            user_id=:user_id AND
            u235_people_groups.site_id=:site_id
            ");
            $site_id=site_id;
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':user_id', $this->user_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->bindParam(':site_id', $site_id,PDO::PARAM_INT);
            /** @noinspection PhpUndefinedMethodInspection */$stm->execute();
        }
        catch(PDOException $e) {$this->uFunc->error('60'/*.$e->getMessage()*/);}

        if(!$this->q_user_groups=$stm) $this->uFunc->error(70);
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        $this->uFunc=new uFunc($this->uCore);
        $config = \HTMLPurifier_Config::createDefault();
        $this->purifier = new \HTMLPurifier($config);

        $this->check_data();
        $this->get_user_data();
    }
}

$uPeople=new profile_admin_edit($this);

//uEditor in place
$this->uFunc->incJs(u_sroot.'js/tinymce/tinymce.min.js');

$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uPeople/uPeople.css');
$this->uFunc->incJs(u_sroot.'uPeople/js/profile_admin_edit.min.js',2);

$uPeople->ses_hack=$this->uFunc->sesHack();
ob_start();
?>

    <div class="uPeople_profile uPeople_profile_edit">
        <div class="col-md-4 avatar" id="uPeople_profile_upload_avatar_container">
            <img class="avatar" src="<?=u_sroot.$this->mod?>/avatars/<?=($uPeople->user->avatar_timestamp=='0'?'default':(site_id.'/'.$uPeople->user_id))?>_big.jpg?<?=$uPeople->user->avatar_timestamp?>">
            <p class="clearfix">&nbsp;</p>
            <p class="uploadBtn btn btn-default" id="uPeople_profile_upload_avatar_btn">Загрузить фото</p>
            <div id="filelist"></div>
        </div>
        <div class="col-md-8 info">

            <h3 id="uPeople_username"><?=$uPeople->user->firstname?> <?=$uPeople->user->secondname?> <?=$uPeople->user->lastname?></h3>

            <div class="row form-group">
                <div class="col-md-3 field_title">Имя:</div>
                <div class="col-md-9 field_val">
                    <input type="text" id="uPeople_profile_firstname" class="form-control" value="<?=$uPeople->user->firstname?>">
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3 field_title">Отчество:</div>
                <div class="col-md-9 field_val">
                    <input type="text" id="uPeople_profile_secondname" class="form-control" value="<?=$uPeople->user->secondname?>">
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3 field_title">Фамилия:</div>
                <div class="col-md-9 field_val">
                    <input type="text" id="uPeople_profile_lastname" class="form-control" value="<?=$uPeople->user->lastname?>">
                </div>
            </div>

            <div class="row form-group">
                <div class="col-md-3 field_title">
                    <a href="javascript:void(0);" onclick="jQuery('#uPeople_groups_dg').dialog('open');">
                        <img src="<?=u_sroot?>templates/u235/images/edit_btn.png" onmouseover="this.src='<?=u_sroot?>templates/u235/images/edit_btn_hover.png'" onmouseout="this.src='<?=u_sroot?>templates/site_<?=site_id?>/images/edit_btn.png'">
                    </a>
                    Группы:
                </div>
                <div class="col-md-9 field_val user_groups"><?
                    while($group=$uPeople->q_user_groups->fetch(PDO::FETCH_OBJ)) {
                        echo $group->gr_title.'<br>';
                    }?>
                </div>
            </div>
            <?
            for($i=0;$field=$uPeople->fields_ar[$i];$i++) {
                //echo $field->label;
                $field_id='field_'.$field->field_id;
                if($field->field_type=='1') $field_val='<input type="text" id="uPeople_profile_field_'.$field->field_id.'" class="form-control" value="'.uString::sql2text($uPeople->user->$field_id).'">';
                elseif($field->field_type=='2') $field_val='<textarea id="uPeople_profile_field_'.$field->field_id.'" class="form-control">'.uString::sql2text($uPeople->user->$field_id).'</textarea>';
                elseif($field->field_type=='3') $field_val='<div id="uPeople_profile_field_'.$field->field_id.'">'.$uPeople->purifier->purify(uString::sql2text($uPeople->user->$field_id,true)).'</div>
                    <script type="text/javascript">
                    $(document).ready(function() {
                        if(typeof uPeople==="undefined") uPeople={};
                        uPeople.init_editor_fields('.$field->field_id.');
                    });
                    </script>';?>

                <div class="row form-group">
                    <div class="col-md-3 field_title"><?=uString::sql2text($field->label)?>:</div>
                    <div class="col-md-9 field_val"><?=$field_val?><p class="text-muted"><?=$uPeople->purifier->purify(nl2br(uString::sql2text($field->field_comment,true)))?></p></div>
                </div>
            <?}?>
            <script type="text/javascript">
                if(typeof uPeople==="undefined") uPeople={};

                uPeople.ses_hack=[];
                uPeople.ses_hack['id']=<?=$uPeople->ses_hack['id']?>;
                uPeople.ses_hack['hash']="<?=$uPeople->ses_hack['hash']?>";

                uPeople.user_id=<?=$uPeople->user_id?>;

                uPeople.fields=[];
                uPeople.fields_ids=[];
                <? for($i=0;$field=$uPeople->fields_ar[$i];$i++) {?>
                uPeople.fields[<?=$i?>]='uPeople_profile_field_<?=$field->field_id?>';
                uPeople.fields_ids[<?=$i?>]=<?=$field->field_id?>;
                <?}?>
            </script>

            <div class="row form-group">
                <div class="col-md-3 field_title"></div>
                <div class="col-md-9">
                    <p><a href="javascript:void(0);" onclick="uPeople.saveInfo();" class="btn btn-default">Сохранить изменения</a><span class="saving_1" style="display:none"> (Сохраняю...)</span></p>
                </div>
        </div>
    </div>

    <div style="display: none">
        <div id="uPeople_del_group_confirm_dg" title="Удалить группу?">
            <p>Вы действительно хотите удалить эту группу?</p>
        </div>
        <div id="uPeople_add_group_dg" title="Новая группа">
            <div class="form-group">
                <label for="uPeople_new_group_title">Название группы</label>
                <input type="text" class="form-control" id="uPeople_new_group_title" placeholder="Введите название новой группы">
            </div>
        </div>
        <div id="uPeople_groups_dg" title="Группы"></div>
    </div>

<?
$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
