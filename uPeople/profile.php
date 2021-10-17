<?
class uPeople_profile {
    private $uCore;
    public $user_id,$q_fields,$user;

    private function check_data() {
        if(!isset($this->uCore->url_prop[1])) header('Location: '.u_sroot);
        if(!uString::isDigits($this->uCore->url_prop[1])) header('Location: '.u_sroot);
        $this->user_id=$this->uCore->url_prop[1];
    }
    private function get_user() {
        //get site's fields
        if(!$this->q_fields=$this->uCore->query("uPeople","SELECT
        `field_id`,
        `label`,
        `field_type`
        FROM
        `u235_fields`
        WHERE
        `show_on_page`='1' AND
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

        $this->user->firstname=uString::sql2text($this->user->firstname);
        $this->user->secondname=uString::sql2text($this->user->secondname);
        $this->user->lastname=uString::sql2text($this->user->lastname);

        $name_first_field=$this->uCore->uFunc->getConf("name_first_field","uPeople");

        if($name_first_field!='firstname'&&$name_first_field!='lastname') $name_first_field='lastname';

        if($name_first_field=='firstname') $this->uCore->page['page_title']=$this->user->firstname.' '.$this->user->secondname.' '.$this->user->lastname;
        else $this->uCore->page['page_title']=$this->user->lastname.' '.$this->user->firstname.' '.$this->user->secondname;
    }
    function __construct (&$uCore) {
        $this->uCore=&$uCore;

        $this->check_data();
        $this->get_user();
    }
}
$uPeople=new uPeople_profile($this);
$this->uFunc->incCss(u_sroot.'uPeople/css/default.min.css');
$this->uFunc->incCss(u_sroot.'templates/site_'.site_id.'/css/uPeople/uPeople.css');
ob_start();?>
    <div class="uPeople_profile row">
        <div class="col-md-4 avatar">
            <img class="avatar" src="<?=u_sroot.'uPeople/avatars/'.($uPeople->user->avatar_timestamp=='0'?'default':(site_id.'/'.$uPeople->user_id))?>_big.jpg?<?=$uPeople->user->avatar_timestamp?>">
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <h3><?=$this->page['page_title']?></h3>
                </div>
            </div>
            <?
            mysqli_data_seek($uPeople->q_fields,0);
            while($field=$uPeople->q_fields->fetch_object()) {
                $field_id='field_'.$field->field_id;
                    if($field->field_type=='2') $field_val= nl2br(uString::sql2text($uPeople->user->$field_id));
                    elseif($field->field_type=='3') $field_val=uString::sql2text($uPeople->user->$field_id,true);
                    else $field_val=uString::sql2text($uPeople->user->$field_id);
                    if(!empty($field_val)) {?>
                        <div class="row">
                            <div class="col-md-3 field_label"><?=uString::sql2text($field->label)?>:</div>
                            <div class="col-md-9"><?=$field_val?></div>
                        </div>
                    <?}
            }?>
        </div>
    </div>
<?
$this->page_content=ob_get_contents();
ob_end_clean();
include "templates/template.php";
