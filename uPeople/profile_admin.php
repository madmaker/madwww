<?
class uPeople_profile_admin {
    private $uCore,$field_id2val;
    public $user,$user_id,$q_user_groups,$q_fields;
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) $this->uCore->error(1);
        if(!uString::isDigits($this->uCore->url_prop[1])) $this->uCore->error(2);
        $this->user_id=$this->uCore->url_prop[1];
    }
    private function get_user_info() {
        //get site's fields
        if(!$this->q_fields=$this->uCore->query("uPeople","SELECT
        `field_id`,
        `label`,
        `field_type`
        FROM
        `u235_fields`
        WHERE
        `site_id`='".site_id."'
        ORDER BY
        `sort` ASC
        ")) $this->uCore->error(3);

        //get user's data
        $qu="SELECT ";
        while($field=$this->q_fields->fetch_object()) {
            $qu.="`field_".$field->field_id."`,\n";
        }
        $qu.="`firstname`,
        `secondname`,
        `lastname`,
        `avatar_timestamp`
        FROM
        `u235_people`
        WHERE
        `user_id`='".$this->user_id."' AND
        (`status`='' OR `status` IS NULL) AND
        `site_id`='".site_id."'
        ";

        //get user's data
        if(!$query=$this->uCore->query('uPeople',$qu)) $this->uCore->error(4);
        if(!mysqli_num_rows($query)>0) $this->uCore->error(5);
        $this->user=$query->fetch_object();

        //get user's groups
        if(!$this->q_user_groups=$this->uCore->query("uPeople","SELECT DISTINCT
        `gr_title`
        FROM
        `u235_people_groups`,
        `u235_groups`
        WHERE
        `user_id`='".$this->user_id."' AND
        `u235_people_groups`.`site_id`='".site_id."' AND
        `u235_people_groups`.`gr_id`=`u235_groups`.`gr_id` AND
        `u235_groups`.`site_id`
        ")) $this->uCore->error(6);
    }
    public function usersinfo_field_id2val($field_id) {
        if(!isset($this->field_id2val[$field_id])) {
            $fields=$this->uCore->uFunc->get_uPeople_usersinfo_fields();
            $sqlAdd='';
            for($i=0;$i<count($fields);$i++) $sqlAdd.="`field_".$fields[$i]['field_id']."`,";
            if(!$query=$this->uCore->query("uPeople","SELECT
            ".$sqlAdd."
            `site_id`
            FROM
            `u235_usersinfo`
            WHERE
            `site_id`='".site_id."' AND
            `user_id`='".$this->user_id."'
            ")) $this->uCore->error(7);
            $qr=$query->fetch_assoc();
            for($i=0;$i<count($fields);$i++) $this->field_id2val[$fields[$i]['field_id']]=uString::sql2text($qr['field_'.$fields[$i]['field_id']]);
        }
        return $this->field_id2val[$field_id];
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->check_data();
        $this->get_user_info();
    }
}
$uPeople=new uPeople_profile_admin($this);
$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incCss(u_sroot.'templates/u235/css/uPeople/uPeople.css');
ob_start();?>
<div class="container">
<div class="uPeople_profile row">
    <div class="col-md-4 avatar">
        <img class="avatar" src="<?=u_sroot.$this->mod?>/avatars/<?=($uPeople->user->avatar_timestamp=='0'?'default':(site_id.'/'.$uPeople->user_id))?>_big.jpg?<?=$uPeople->user->avatar_timestamp?>">
    </div>
    <div class="col-md-8 info">

        <h3><?=uString::sql2text($uPeople->user->firstname)?> <?=uString::sql2text($uPeople->user->secondname)?> <?=uString::sql2text($uPeople->user->lastname)?></h3>

        <?if(mysqli_num_rows($uPeople->q_user_groups)) {?>
        <div class="row">
            <div class="col-md-3 field_title">Группы:</div>
            <div class="col-md-4 field_val">
                <?while($group=$uPeople->q_user_groups->fetch_object()) {
                    echo uString::sql2text($group->gr_title).'<br>';
                }?>
            </div>
        </div>
        <?}
        while($field=$uPeople->q_fields->fetch_object()) {
            $field_id='field_'.$field->field_id;
            if($field->field_type=='2') $field_val=nl2br(uString::sql2text($uPeople->user->$field_id));
            elseif($field->field_type=='3') $field_val=uString::sql2text($uPeople->user->$field_id,true);
            else $field_val=uString::sql2text($uPeople->user->$field_id);

            if(!empty($field_val)) {?>
                <div class="row">
                    <div class="col-md-3 field_title"><?=$field->label?>:</div>
                    <div class="col-md-4 field_val"><?=$field_val?></div>
                </div>
            <?}
        }?>
        <p>&nbsp;</p>
        <a class="btn btn-default" href="<?=u_sroot.$this->mod?>/profile_admin_edit/<?=$uPeople->user_id?>">Редактировать профиль</a>
    </div>
</div>
</div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/u235/template.php";
