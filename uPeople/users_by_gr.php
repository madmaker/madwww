<?
class uPeople_users_by_gr {
    private $uCore,$gr_id;
    public $q_users,$q_fields,$gr_title,$name_first_field;
    private function error() {
        header('Location: '.u_sroot);
        return false;
    }
    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) return false;
        $this->gr_id=$this->uCore->url_prop[1];
        if(!uString::isDigits($this->gr_id)) return false;

        return true;
    }
    private function get_gr_title() {
        if(!$query=$this->uCore->query("uPeople","SELECT
        `gr_title`
        FROM
        `u235_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `site_id`='".site_id."'
        ")) $this->uCore->error(1);
        if(!mysqli_num_rows($query)) return false;
        $qr=$query->fetch_object();
        $this->gr_title=uString::sql2text($qr->gr_title);
        return true;
    }
    private function getUserList() {
        //get site's fields
        if(!$this->q_fields=$this->uCore->query("uPeople","SELECT
        `field_id`,
        `label`,
        `field_type`
        FROM
        `u235_fields`
        WHERE
        `show_on_list`='1' AND
        `site_id`='".site_id."'
        ORDER BY
        `sort` ASC
        ")) $this->uCore->error(2);
        $this->q_field_add="";

        $order_field=$this->uCore->uFunc->getConf("order_field","uPeople");
        $order_dir=$this->uCore->uFunc->getConf("order_dir","uPeople");

        if($order_field!='firstname'&&$order_field!='lastname'&&$order_field!='user_id') $order_field='firstname';
        if($order_dir!='ASC'&&$order_dir!='DESC') $order_field='ASC';

        $qu="SELECT DISTINCT
        `u235_people`.`user_id`,
        `firstname`,
        `secondname`,
        `lastname`,
        `avatar_timestamp`";
        while($field=$this->q_fields->fetch_object()) {
            $qu.=", `field_".$field->field_id."`";
        }
        $qu.="FROM
        `u235_people`,
        `u235_people_groups`
        WHERE
        `gr_id`='".$this->gr_id."' AND
        `u235_people`.`user_id`=`u235_people_groups`.`user_id` AND
        `u235_people`.`site_id`='".site_id."' AND
        `u235_people_groups`.`site_id`='".site_id."' AND
        (`status`='' OR `status` IS NULL)
        ORDER BY
        `".$order_field."` ".$order_dir;

        if(!$this->q_users=$this->uCore->query('uPeople',$qu)) $this->uCore->error(3);

        $this->name_first_field=$this->uCore->uFunc->getConf("name_first_field","uPeople");
        if($this->name_first_field!='firstname'&&$this->name_first_field!='lastname') $this->name_first_field='lastname';
    }
    function __construct(&$uCore) {
        $this->uCore=&$uCore;
        if(!$this->check_data()) $this->error();
        if(!$this->get_gr_title()) $this->error();
        $this->getUserList();
    }
}
$uPeople=new uPeople_users_by_gr($this);

$this->uFunc->incJs(u_sroot.'uPeople/js/users_by_gr.min.js');

$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incCss(u_sroot.'templates/site_'.site_id.'/css/uForms/uForms.css');

$this->page['page_title']=$uPeople->gr_title;
ob_start();?>

<div class="uPeople uPeople_users uPeople_users_by_gr">
    <?/*<div class="people_number">Всего пользователей: <?=mysqli_num_rows($uPeople->q_users)?></div>*/?>
    <h1 class="page-header"><?=$uPeople->gr_title?></h1>
    <div class="uPeople_users_by_gr_list"><?
        while($user=$uPeople->q_users->fetch_object()) {?>
            <div class="row">
                <div style="display:none" class="user_name_first_letter"><?=mb_substr(uString::sql2text($user->lastname),0,1,'UTF-8')?></div>
                <div class="col-md-4 avatar">
                    <a href="<?=u_sroot?>uPeople/profile/<?=$user->user_id?>" class="thumbnail">
                        <img class="avatar" src="<?=u_sroot.$this->mod?>/avatars/<?=($user->avatar_timestamp=='0'?'default':(site_id.'/'.$user->user_id))?>_big.jpg?<?=$user->avatar_timestamp?>">
                    </a>
                </div>
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-12 user_name">
                            <a href="<?=u_sroot.$this->mod?>/profile/<?=$user->user_id?>"><?
                            if($uPeople->name_first_field=='lastname') {?>
                                <?=uString::sql2text($user->lastname)?> <?=uString::sql2text($user->firstname)?> <?=uString::sql2text($user->secondname)?>
                            <?} else {?>
                                <?=uString::sql2text($user->firstname)?> <?=uString::sql2text($user->secondname)?> <?=uString::sql2text($user->lastname)?>
                            <?}?>
                            </a>
                        </div>
                    </div>
                <?if(!empty($user->email)) {?>
                    <div class="row">
                        <div class="col-md-3 field_label">E-mail:</div>
                        <div class="col-md-9"><?=uString::sql2text($user->email)?></div>
                    </div>
                <?}
                if(!empty($user->phone)) {?>
                    <div class="row">
                        <div class="col-md-3 field_label">Телефон:</div>
                        <div class="col-md-9"><?=uString::sql2text($user->phone)?></div>
                    </div>
                <?}
                mysqli_data_seek($uPeople->q_fields,0);
                while($field=$uPeople->q_fields->fetch_object()) {
                    $field_id='field_'.$field->field_id;
                    if(!empty($user->$field_id)) {
                        if($field->field_type=='3') {?>
                    <div class="row">
                        <div class="col-md-3 field_label"><?=$field->label?>:</div>
                        <div class="col-md-9"><?=uString::sql2text($user->$field_id,true)?></div>
                    </div>
                        <?}
                        else {?>
                    <div class="row">
                        <div class="col-md-3 field_label"><?=$field->label?>:</div>
                        <div class="col-md-9"><?=uString::sql2text($user->$field_id)?></div>
                    </div>
                        <?}
                    }
                }?>
                </div>
                <div class="col-md-12"><div class="separator"></div></div>
            </div>
            <!--<div class="row"></div>-->
        <?}
        ?>
</div></div>

<script type="text/javascript">
    // uPeople_users_by_gr.set_alphabet();
</script>

<?$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/template.php";
