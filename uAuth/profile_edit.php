<?php
namespace uAuth;
use processors\uFunc;
use translator\translator;
use uAuth_avatar;
use uSes;
use uString;

require_once 'inc/avatar.php';
require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'uAuth/classes/common.php';
require_once 'translator/translator.php';

class profile_edit {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var int
     */
    public $user_id;
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
    public $use_MadSMS;
    /**
     * @var uAuth_avatar
     */
    public $avatar;
    public $ses_hack;
    /**
     * @var common
     */
    public $uAuth;
    /**
     * @var bool
     */
    public $is_mp_admin;
    /**
     * @var bool
     */
    public $is_admin;
    /**
     * @var int
     */
    public $current_user;
    /**
     * @var int
     */
    public $type;
    /**
     * @var int
     */
    public $is_profile_owner;
    /**
     * @var array
     */
    public $user_groups;
    /**
     * @var translator
     */
    public $acl_group_name_translator;

    /**
     * profile_edit constructor.
     * @param $uCore
     */
    public function __construct(&$uCore) {
        $uFunc=new uFunc($uCore);
        $uSes=new uSes($uCore);

        $this->translator=new translator(site_lang, 'uAuth/profile_edit.php');
        $this->acl_group_name_translator=new translator(site_lang, 'acl_group_names.php');

        ob_start();

        //Check if user is signed on
        if(!$uSes->access(2)) {
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

        //retrieving current user id
        $this->current_user=(int)$uSes->get_val('user_id');

        //checking if user is admin or mp admin
        if(!$this->is_mp_admin=$this->is_admin=(bool)$uSes->access(28)) {
            $this->is_admin = (bool)$uSes->access(13);
        }

        //check if user_id parameter is passed in request
        if(isset($uCore->url_prop[1])&&uString::isDigits($uCore->url_prop[1])) {
            $this->user_id=(int)$uCore->url_prop[1];
        }
        else {
            //If not passed - assign id of current user
            $this->user_id=$this->current_user;
        }

        //uAuth common class
        $this->uAuth=new common($uCore);
        $this->avatar=new uAuth_avatar($uCore);

        //Accessible for:
        //profile owner
        //site admin
        //not accessible for mp admin
        if($this->is_profile_owner||$this->is_admin) {
            $this->user_groups = $this->uAuth->user_id2user_groups($this->user_id);
        }

        //Validating user to be registered on this website
        if(!$this->uAuth->userExistsOnSite($this->user_id)) {
            //User is not exists on this website - follow him to homepage
            header('Location: ' . u_sroot);
            exit;
        }

        $user_data=$this->uAuth->user_id2user_data($this->user_id,'avatar_timestamp,firstname,secondname,lastname,cellphone,email,regDate,type');
        $this->avatar_timestamp=(int)$user_data->avatar_timestamp;
        $this->firstname=uString::sql2text($user_data->firstname,1);
        $this->secondname=uString::sql2text($user_data->secondname,1);
        $this->lastname=uString::sql2text($user_data->lastname,1);
        $this->cellphone=$user_data->cellphone;
        $this->email=$user_data->email;
        if($this->is_mp_admin) {
            $this->type=(int)$user_data->type;
        }
        if($this->user_id===$this->current_user) {
            $this->is_profile_owner = 1;
        }
        else {
            $this->is_profile_owner = 0;
        }

//        $this->is_mp_admin=0;//for testing access
//        $this->is_admin=0;//for testing access

        if(!$this->is_profile_owner&&!$this->is_admin&&!$this->is_mp_admin) {
            //user has no access to edit this profile
            header('Location: ' . u_sroot.'uAuth/profile/'.$this->user_id);
            exit;
        }

        $uCore->page['page_title']=$this->firstname.' '.$this->secondname.' '.$this->lastname;

        $this->use_MadSMS=(int)$uFunc->getConf('use MAD SMS to send SMS','content',false);

        $uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
        $uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');
        $uFunc->incJs(staticcontent_url.'js/lib/tinymce/tinymce.min.js');
        $uFunc->incJs(staticcontent_url.'js/uAuth/profile_edit.min.js');
        $uFunc->incCss(staticcontent_url.'css/uAuth/profile_edit.min.css');

        $this->ses_hack=$uFunc->sesHack();

        /** @noinspection MagicMethodsValidityInspection */
        return 1;
    }
}

$uAuth=new profile_edit($this);?>

<?php
if($uAuth->print_data) {?>
    <div class="uAuth_profile uAuth_profile_edit row">
        <div class="col-xs-6 col-md-3" id="uAuth_profile_upload_avatar_container">
            <img id="uAuth_avatar_img" class="img-thumbnail" src="<?=$uAuth->avatar->get_avatar('profile',$uAuth->user_id,$uAuth->avatar_timestamp)?>" alt="<?=htmlspecialchars($uAuth->firstname.' '.$uAuth->lastname)?>">
            <?php
            //Accessible for:
            //profile owner
            //mp admin
            //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
            <p class="uploadBtn"><button class="btn btn-sm btn-primary" id="uAuth_profile_upload_avatar_btn"><?=$uAuth->translator->txt('Upload avatar'/*Загрузить фото*/)?></button></p>
            <div id="filelist"></div>
            <?}?>
        </div>
        <div class="col-xs-6 col-md-9 info">
            <fieldset>
                <div class="row form-group">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_lastname"><?=$uAuth->translator->txt('Last name - field label'/*Фамилия:*/)?></label></div>
                    <div class="col-md-4 field_val">
                        <?php
                        //Accessible for:
                        //profile owner
                        //mp admin
                        //not accessible for site admin
                        if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                        <input type="text" id="uAuth_profile_lastname" class="form-control" value="<?=$uAuth->lastname?>">
                        <?}
                        //Accessible for:
                        //site admin ONLY
                        else {?>
                            <div><?=$uAuth->lastname?></div>
                        <?}?>
                    </div>
                </div>
                <div class="row form-group"  id="uAuth_profile_firstname_form_group">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_firstname"><?=$uAuth->translator->txt('First name - field label'/*Имя:*/)?></label></div>
                    <div class="col-md-4 field_val">
                        <?php
                        //Accessible for:
                        //profile owner
                        //mp admin
                        //not accessible for site admin
                        if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                            <input type="text" id="uAuth_profile_firstname" class="form-control" value="<?=$uAuth->firstname?>">
                            <div id="uAuth_profile_firstname_help_block" class="help-block hidden"></div>
                        <?}
                        //Accessible for:
                        //site admin ONLY
                        else {?>
                            <div><?=$uAuth->firstname?></div>
                        <?}?>
                    </div>
                </div>
                <div class="row form-group"  id="uAuth_profile_secondname_form_group">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_secondname"><?=$uAuth->translator->txt('Second Name - field label'/*Отчество:*/)?></label></div>
                    <div class="col-md-4 field_val">
                        <?php
                        //Accessible for:
                        //profile owner
                        //mp admin
                        //not accessible for site admin
                        if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                            <input type="text" id="uAuth_profile_secondname" class="form-control" value="<?=$uAuth->secondname?>">
                            <div id="uAuth_profile_secondname_help_block" class="help-block hidden"></div>
                        <?}
                        //Accessible for:
                        //site admin ONLY
                        else {?>
                            <div><?=$uAuth->secondname?></div>
                        <?}?>
                    </div>
                </div>

                <?if($uAuth->is_mp_admin) {?>
                <div class="row form-group">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_type"><?=$uAuth->translator->txt('is root')?></label></div>
                    <div class="col-md-4 field_val">
                        <select id="uAuth_profile_type" class="form-control">
                            <option value="0" <?=$uAuth->type===0?' selected ':''?>><?=$uAuth->translator->txt('no')?></option>
                            <option value="1" <?=$uAuth->type===1?' selected ':''?>><?=$uAuth->translator->txt('yes')?></option>
                        </select>
                    </div>
                </div>
                <?}

                $fields=$uAuth->uAuth->get_uAuth_usersinfo_fields();
                foreach ($fields as $iValue) {
                    $editable=(int)$iValue['editable'];
                    $field_type=(int)$iValue['field_type'];
                    $visible=(int)$iValue['visible'];
                    $field_id=(int)$iValue['field_id'];
                    $label=uString::sql2text($iValue['label']);
                    $field_val=uString::sql2text($uAuth->uAuth->usersinfo_field_id2val($field_id, $uAuth->user_id),1 );

                    if($editable) {
                        if($field_type===1) {
                            $field_val = '<input type="text" id="uAuth_profile_field_' . $field_id . '" class="form-control" value="' . $field_val . '">';
                        }
                        elseif($field_type===2) {
                            $field_val = "<textarea id='uAuth_profile_field_$field_id' class='form-control'>$field_val</textarea>";
                        }
                        elseif($field_type===3) {
                            $field_val = "<div id='uAuth_profile_field_$field_id'>$field_val</div>
                            <script type='text/javascript'>
                                $(document).ready(function() {
                                    uAuth.profile_edit.init_editor_fields($field_id);
                                });
                            </script>";
                        }
                    }
                    else if($field_type===2) {
                        $field_val = nl2br($field_val);
                    }

                    if($visible===1) {?>
                        <div class="row form-group">
                            <div class="col-md-3 field_title"><label for="field_<?=$field_id?>"><?=$label?>:</label></div>
                            <div class="col-md-4 field_val" id="field_<?=$field_id?>"><?=$field_val?></div>
                        </div>
                    <?}
                    elseif($visible===3) {?>
                        <div class="row form-group">
                            <div class="col-md-3 field_title"><label for="field_<?=$field_id?>"><?=$label?>:</label></div>
                            <div class="col-md-4 field_val" id="field_<?=$field_id?>"><?=$field_val?></div>
                            <div class="col-md-5 field_descr">(<?=$uAuth->translator->txt('Visible only for admin - field hint')?>)</div>
                        </div>
                    <?}
                }
                ?>
                <script type="text/javascript">
                    if(typeof uAuth==="undefined") uAuth={};
                    if(typeof uAuth.profile_edit==="undefined") uAuth.profile_edit={};

                    if(typeof uAuth.profile_edit.fields==="undefined") uAuth.profile_edit.fields=[];
                    if(typeof uAuth.profile_edit.fields_ids==="undefined") uAuth.profile_edit.fields_ids=[];

                    <?php foreach ($fields as $i => $iValue) {
                    $field_id=(int)$iValue['field_id'];?>
                    uAuth.profile_edit.fields[<?=$i?>]='uAuth_profile_field_<?=$field_id?>';
                    uAuth.profile_edit.fields_ids[<?=$i?>]=<?=$field_id?>;
                    <?}?>
                </script>
                <?php
                //Accessible for:
                //profile owner
                //site admin
                if($uAuth->is_profile_owner||$uAuth->is_admin) {?>
                <div class="row form-group">
                    <div class="col-md-3 field_title">
                        <?php
                        //Accessible for:
                        //site admin ONLY
                        if($uAuth->is_admin) {?>
                            <a href="javascript:void(0)" title="<?=$uAuth->translator->txt("Edit user's groups - btn txt"/*Редактировать группы пользователя*/)?>" onclick="uAuth.profile_edit.load_user_groups();" data-toggle="modal"><span class="icon-pencil"></span></a>
                        <?}?>
                        <?=$uAuth->translator->txt('Groups - field label')?>
                    </div>
                    <div class="col-md-9 field_val user_groups"><?php
                        foreach ($uAuth->user_groups as $group_id) {
                            if($group_id!==13) {
                                print $uAuth->acl_group_name_translator->txt($group_id) . '<br>';
                            }
                        }
                        //Accessible for:
                        //site admin ONLY
                        if($uAuth->is_profile_owner||$uAuth->is_admin) {?>
                            <p class="text-muted"><?=$uAuth->translator->txt("Edit user's groups hint"/*Чтобы новые группы вступили в силу пользователю нужно выйти и заново авторизоваться*/)?></p>
                        <?}?>
                    </div>
                </div>
                <?}

                //Accessible for:
                //profile owner
                //mp admin
                //not accessible for site admin
                if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                <p><button class="btn btn-sm btn-primary saveProfileBtn" onclick="uAuth.profile_edit.save('update firstname secondname lastname fields type');"><?=$uAuth->translator->txt('Save changes - btn txt')?></button></p>
                <?}?>

            </fieldset>


            <?php
            //Accessible for:
            //profile owner
            //mp admin
            //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
            <fieldset>
                <legend><?=$uAuth->translator->txt('Change email - section title')?></legend>
            <?}?>
                <div class="row form-group">
                    <div class="col-md-3 field_title"><label><?php
                            //Accessible for:
                            //profile owner
                            //mp admin
                            //not accessible for site admin
                            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {
                                print $uAuth->translator->txt('Current email - field label');
                            } else {
                                //Accessible for:
                                //site admin ONLY
                                print $uAuth->translator->txt('Email - field label');
                            }
                            ?></label></div>
                    <div class="col-md-4 field_val" id="uAuth_profile_email_current"><?=$uAuth->email ?></div>
                </div>
                <?//Accessible for:
                //profile owner
                //mp admin
                //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                <div class="row form-group" id="uAuth_profile_email_formGroup">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_email"><?=$uAuth->translator->txt('New email - field label')?></label></div>
                    <div class="col-md-4 field_val"><input type="text" id="uAuth_profile_email" class="form-control"></div>
                    <div class="help-block hidden" id="uAuth_profile_email_helpBlock"></div>
                </div>
                <?php
                //Accessible for:
                //profile owner ONLY
                if($uAuth->is_profile_owner) {?>
                <div class="row form-group" id="uAuth_profile_curPass_formGroup">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_curPass"><?=$uAuth->translator->txt('Current password - field label'/*Текущий пароль:*/)?></label></div>
                    <div class="col-md-4 field_val"><input type="password" id="uAuth_profile_curPass" class="form-control"></div>
                    <div class="help-block hidden" id="uAuth_profile_curPass_helpBlock"></div>
                </div>
                <?}?>
                <p><button class="btn btn-sm btn-primary saveProfileBtn" onclick="uAuth.profile_edit.save('update email');"><?=$uAuth->translator->txt('Change email - btn txt'/*Сменить email*/)?></button></p>
            </fieldset>
            <?}?>

            <?php
            if($uAuth->use_MadSMS) {
                //Accessible for:
                //profile owner
                //mp admin
                //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                <legend><?=$uAuth->translator->txt('Change phone - section title')?></legend>
            <?}?>
                <div class="row form-group">
                    <div class="col-md-3 field_title"><label><?php
                        //Accessible for:
                        //profile owner
                        //mp admin
                        //not accessible for site admin
                        if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {
                            print $uAuth->translator->txt('Current phone');
                        } else {
                        //Accessible for:
                        //site admin ONLY
                            print $uAuth->translator->txt('Phone - field label');
                        }?></label>
                    </div>
                    <div class="col-md-4 field_val" id="uAuth_profile_phone_current"><?=$uAuth->cellphone ?></div>
                </div>
                <?php
                //Accessible for:
                //profile owner
                //mp admin
                //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
                <div class="row form-group" id="uAuth_profile_phone_formGroup">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_phone"><?=$uAuth->translator->txt('New phone')?></label></div>
                    <div class="col-md-4 field_val">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <input type="text" id="uAuth_profile_phone" class="form-control">
                        <div class="help-block hidden" id="uAuth_profile_phone_helpBlock"></div>
                    </div>
                </div>
                <?php
                //Accessible for:
                //profile owner ONLY
                if($uAuth->is_profile_owner) {?>
                <div class="row form-group" id="uAuth_profile_phone_curPass_formGroup">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_phone_curPass"><?=$uAuth->translator->txt('Current password - field label')?></label></div>
                    <div class="col-md-4 field_val">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <input type="password" id="uAuth_profile_phone_curPass" class="form-control">
                        <div class="help-block hidden" id="uAuth_profile_phone_curPass_helpBlock"></div>
                    </div>
                </div>
                <?}?>
                <p><button class="btn btn-sm btn-primary saveProfileBtn" onclick="uAuth.profile_edit.save('update phone');"><?=$uAuth->translator->txt('Change phone')?></button></p>
            <?}
            }

            //Accessible for:
            //profile owner
            //mp admin
            //not accessible for site admin
            if($uAuth->is_profile_owner||$uAuth->is_mp_admin) {?>
            <fieldset>
                <legend><?=$uAuth->translator->txt('Change password - section title'/*Сменить пароль*/)?></legend>
                <div class="row form-group" id="uAuth_profile_newpass_formGroup">
                    <div class="col-md-3 field_title"><label for="uAuth_profile_newpass"><?=$uAuth->translator->txt('New password - field label'/*Новый пароль:*/)?></label></div>
                    <div class="col-md-4 field_val">
                        <input type="password" id="uAuth_profile_newpass" class="form-control">
                        <div class="help-block hidden" id="uAuth_profile_newpass_helpBlock"></div>
                    </div>
                </div>
                <?php
                //Accessible for:
                //profile owner
                //mp admin
                //not accessible for site admin
                if($uAuth->is_profile_owner) {?>
                <div class="row form-group">
                    <div class="col-md-3 field_title"><?=$uAuth->translator->txt('Confirm password - field label'/*Повторите пароль:*/)?></div>
                    <div class="col-md-4 field_val">
                        <!--suppress HtmlFormInputWithoutLabel -->
                        <input type="password" id="uAuth_profile_newpass2" class="form-control">
                    </div>
                </div>
                <?}?>
                <div class="row form-group newPass_value" style="display:none;">
                    <div class="col-md-3 field_title"><?=$uAuth->translator->txt('New password - field label'/*Новый пароль:*/)?></div>
                    <div class="col-md-4 field_val"></div>
                </div>
                <p class="btn-group">
                    <button class="btn btn-sm btn-default" onclick="uAuth.profile_edit.genPass();"><?=$uAuth->translator->txt('Generate new password - btn txt'/*Сгенерировать*/)?></button>
                    <button class="btn btn-sm btn-primary saveProfileBtn" onclick="uAuth.profile_edit.save('update password');"><?=$uAuth->translator->txt('Change password - btn txt'/*Сменить пароль*/)?></button>
                </p>
            </fieldset>
            <?}?>
        </div>
    </div>

    <div class="modal fade" id="uAuth_profile_edit_dg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer hidden">
<!--                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>-->
<!--                    <button type="button" class="btn btn-primary" onclick="">Создать</button>-->
                </div>
            </div>
        </div>
    </div>
    <?php
    //Accessible for:
    //site admin  ONLY
    if($uAuth->is_admin) {?>
    <div class="modal fade" id="uAuth_groups_dg" tabindex="-1" role="dialog" aria-labelledby="uAuth_groups_dgLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title" id="uAuth_groups_dgLabel"><?=$uAuth->translator->txt("User's groups - dg title"/*Группы пользователя*/)?></h4>
                </div>
                <div class="modal-body" id="uAuth_groups_dg_cnt"></div>
            </div>
        </div>
    </div>
    <?}?>


    <script type="text/javascript">
        if(typeof uAuth==="undefined") uAuth={};
        if(typeof uAuth.profile_edit==="undefined") uAuth.profile_edit={};

        uAuth.profile_edit.user_id=<?=$uAuth->user_id?>;
        uAuth.profile_edit.is_profile_owner=<?=(int)$uAuth->is_profile_owner?>;
        uAuth.profile_edit.is_admin=<?=(int)$uAuth->is_admin?>;
        uAuth.profile_edit.is_mp_admin=<?=(int)$uAuth->is_mp_admin?>;
        uAuth.profile_edit.is_mad_root=<?=(int)$uAuth->is_mp_admin?>;
        uAuth.profile_edit.ses_hack=[];
        uAuth.profile_edit.ses_hack['id']=<?=$uAuth->ses_hack['id']?>;
        uAuth.profile_edit.ses_hack['hash']="<?=$uAuth->ses_hack['hash']?>";
    </script>

<?}

$this->page_content=ob_get_clean();
include 'templates/template.php';
