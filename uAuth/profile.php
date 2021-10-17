<?php
namespace uAuth;

use translator\translator;
use uAuth_avatar;
use uSes;
use uString;

if(isset($_GET['session_alive'])) {die('1');}

require_once 'inc/avatar.php';
require_once 'uAuth/classes/common.php';
require_once 'translator/translator.php';

class profile {
    /**
     * @var common
     */
    public $uAuth;
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var int
     */
    public $type;
    /**
     * @var int
     */
    public $cur_user_id;
    /**
     * @var bool
     */
    public $is_admin;
    /**
     * @var bool
     */
    public $is_mp_admin;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var int
     */
    public $avatar_timestamp;
    /**
     * @var string|string[]
     */
    public $firstname;
    /**
     * @var string|string[]
     */
    public $secondname;
    /**
     * @var string|string[]
     */
    public $lastname;
    /**
     * @var string|string[]
     */
    public $cellphone;
    /**
     * @var string|string[]
     */
    public $email;
    /**
     * @var int
     */
    public $regDate;
    /**
     * @var int
     */
    public $user_id;
    /**
     * @var uAuth_avatar
     */
    public $avatar;
    /**
     * @var array
     */
    public $groups;
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var int
     */
    public $is_profile_owner;
    /**
     * @var translator
     */
    public $mod_names_translator;
    /**
     * @var translator
     */
    public $acl_group_names_translator;

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $this->translator=new translator(site_lang,'uAuth/profile.php');
        $this->mod_names_translator=new translator(site_lang,'mod_names.php');
        $this->acl_group_names_translator=new translator(site_lang,'acl_group_names.php');

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('You are not authorized'); print '</h1>
                <p>'; print $this->translator->txt('Please sign in'); print '</p>
                <p><!--suppress HtmlUnknownAnchorTarget --><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print  $this->translator->txt('Sign in'); print '</a></p>
            </div>';
            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }
        $this->print_data = 1;

        $this->uAuth=new common($uCore);
        $this->avatar=new uAuth_avatar($uCore);


        $this->cur_user_id=(int)$this->uSes->get_val('user_id');

        if(!isset($uCore->url_prop[1])||!uString::isDigits($uCore->url_prop[1])) {
            $this->user_id=$this->cur_user_id;
        }
        else {
            $this->user_id=(int)$uCore->url_prop[1];
        }

        if(!$user_data=$this->uAuth->user_id2user_data($this->user_id,'avatar_timestamp,firstname,secondname,lastname,regDate,cellphone,email,type')) {
            header('Location: ' . u_sroot);
            exit;
        }

        if(!$this->is_mp_admin=$this->is_admin=(bool)$this->uSes->access(28)) {
            $this->is_admin = (bool)$this->uSes->access(13);
        }

        $this->avatar_timestamp=(int)$user_data->avatar_timestamp;
        $this->firstname=uString::sql2text($user_data->firstname);
        $this->secondname=uString::sql2text($user_data->secondname);
        $this->lastname=uString::sql2text($user_data->lastname);
        $this->regDate=(int)$user_data->regDate;

        if($this->cur_user_id===$this->user_id) {
            $this->is_profile_owner = 1;
        }
        else {
            $this->is_profile_owner = 0;
        }

//        $this->is_admin=0;
//        $this->is_mp_admin=0;

        //Accessible for:
        //profile owner
        //site admin
        if($this->is_profile_owner||$this->is_admin) {
            $this->cellphone = $user_data->cellphone;
            $this->email = $user_data->email;
        }

        //Accessible for:
        //mp admin
        if($this->is_mp_admin) {
            $this->type = (int)$user_data->type;
        }

        $uCore->page['page_title']=$this->firstname.' '.$this->secondname.' '.$this->lastname;


        //Accessible for:
        //profile owner
        //site admin
        if($this->is_profile_owner||$this->is_admin) {
            //get user's groups
            $this->groups = $this->uAuth->user_id2user_groups($this->user_id);
        }
        /** @noinspection MagicMethodsValidityInspection */
        return 1;
    }
}
$uAuth=new profile ($this);

if($uAuth->print_data) {?>
    <div class="uAuth_profile row">

        <div class="col-xs-6 col-md-3">
            <img alt="<?=addslashes(strip_tags($uAuth->firstname.' '.$uAuth->secondname.' '.$uAuth->lastname))?>" class="avatar img-thumbnail" src="<?=$uAuth->avatar->get_avatar('profile',$uAuth->user_id,$uAuth->avatar_timestamp)?>">
        </div>

        <div class="col-xs-6 col-md-9 info">

            <h3><?=$this->page['page_title']?></h3>

            <?php
            //Accessible for:
            //profile owner
            //site admin
            if($uAuth->is_profile_owner||$uAuth->is_admin) {?>
                <div class="row">
                    <div class="col-md-3 field_title">Email:</div>
                    <div class="col-md-4 field_val"><?=$uAuth->email?></div>
                </div>

                <div class="row">
                    <div class="col-md-3 field_title"><?=$uAuth->translator->txt('Phone - field label')?>:</div>
                    <div class="col-md-4 field_val"><?=$uAuth->cellphone?></div>
                </div>
                <div class="row">
                    <div class="col-md-3 field_title"><?=$uAuth->translator->txt('Groups - field label')?></div>
                    <div class="col-md-4 field_val"><?php
                        foreach ($uAuth->groups as $group_id) {
                            if($group_id!==13) {
                                print $uAuth->acl_group_names_translator->txt($group_id) . '<br>';
                            }
                        } ?>
                    </div>
                </div>
                <?php
                //Accessible for:
                //mp admin
                if($uAuth->is_mp_admin){?>
                    <div class="row">
                        <div class="col-md-3 field_title"><?=$uAuth->translator->txt('User role - field label')?></div
                        <div class="col-md-4 field_val"><?=($uAuth->type===1?'root':'user')?></div>
                    </div>
                    <?}?>
            <?}?>

            <?php
            $fields=$uAuth->uAuth->get_uAuth_usersinfo_fields();
            foreach ($fields as $iValue) {
                $visible=(int)$iValue['visible'];
                $field_type=(int)$iValue['field_type'];
                $field_id=(int)$iValue['field_id'];
                $label=uString::sql2text($iValue['label'],1);

                if($field_type===2) {
                    $field_val = nl2br(uString::sql2text($uAuth->uAuth->usersinfo_field_id2val($field_id, $this->user_id)));
                }
                elseif($field_type===3) {
                    $field_val = uString::sql2text($uAuth->uAuth->usersinfo_field_id2val($field_id, $this->user_id), 1);
                }
                else {
                    $field_val = uString::sql2text($uAuth->uAuth->usersinfo_field_id2val($field_id, $this->user_id));
                }

                if($visible===1&&!empty($field_val)) {?>
                    <div class="row">
                        <div class="col-md-3 field_title"><?=$label?>:</div>
                        <div class="col-md-4 field_val"><?=$field_val?></div>
                    </div>
                <?}
                //Accessible for:
                //site admin
                if($uAuth->is_admin && $visible===2&&!empty($field_val)) {?>
                    <div class="row">
                        <div class="col-md-3 field_title"><?=$label?>:</div>
                        <div class="col-md-4 field_val"><?=$field_val?></div>
                        <div class="col-md-5 field_descr">(<?=$uAuth->translator->txt('Visible only for admin - field hint')?>)</div>
                    </div>
                <?}
                //Accessible for:
                //profile owner
                //site admin
                if(($uAuth->is_profile_owner||$uAuth->is_admin) &&
                    $visible === 3 && !empty($field_val)
                ) { ?>
                    <div class="row">
                        <div class="col-md-3 field_title"><?= $label ?>:</div>
                        <div class="col-md-4 field_val"><?= $field_val ?></div>
                        <div class="col-md-5 field_descr">
                            (<?= $uAuth->translator->txt('Visible only for admin and profile owner') ?>
                            )
                        </div>
                    </div>
                <?}
            }?>
            <div class="row">
                <div class="col-md-3 field_title"><?=$uAuth->translator->txt('regDate - field label')?></div>
                <div class="col-md-4 field_val"><?=date('d.m.Y',$uAuth->regDate)?></div>
            </div>
            <?php
            //Accessible for:
            //profile owner
            //mp admin
            //site admin
        if($uAuth->is_profile_owner||$uAuth->is_admin||$uAuth->is_mp_admin) {?>
        <p class="buttonset"><a class="btn btn-sm btn-primary" href="<?=u_sroot?>uAuth/profile_edit/<?=$uAuth->user_id?>"><?=$uAuth->translator->txt('Edit profile - btn text')?></a></p>
        <?}?>
        </div>
</div>
<?}
$this->page_content=ob_get_clean();
include 'templates/template.php';
