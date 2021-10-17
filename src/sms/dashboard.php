<?php
namespace sms;
use processors\uFunc;
use translator\translator;
use uAuth\common;
use uAuth_avatar;
use uSes;
use uString;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';
require_once 'uAuth/classes/common.php';
require_once 'translator/translator.php';
require_once 'uAuth/inc/avatar.php';

class dashboard {
    /**
     * @var int
     */
    public $print_data;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var string|string[]
     */
    public $firstname;
    /**
     * @var string|string[]
     */
    public $lastname;
    public $email;
    public $cellphone;
    /**
     * @var bool|string
     */
    public $avatar_url;
    private function check_data() {

    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        $uFunc=new uFunc($uCore);
        $this->translator=new translator(site_lang, 'sms/dashboard.php');

        $uCore->page['page_title']=$this->translator->txt('Page Title');

        ob_start();
        if (!$uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('You are not authorized'); print '</h1>
                <p>'; print $this->translator->txt('Please sign in'); print '</p>
                <p><!--suppress HtmlUnknownAnchorTarget --><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt('Sign in'); print '</a></p>
            </div>';
            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }

        if(!$uFunc->mod_installed('sms')) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('Forbidden'); print '</h1>
                <p>'; print $this->translator->txt('You do not have sufficient permissions to access this page'); print '</p>
            </div>';
            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }

        $this->print_data = 1;

        $this->check_data();

        $uFunc->incJs('sms/js/dashboard.min.js');
        $uFunc->incCss('sms/css/sms.min.css',1);
        $uFunc->incCss('sms/css/dashboard.min.css',1);

        $uAuth=new common($uCore);
        $uAuth_avatar=new uAuth_avatar($uCore);

        $user_id=$uSes->get_val('user_id');
        if(!$user_data=$uAuth->user_id2user_data($user_id, 'firstname, lastname, email, cellphone, avatar_timestamp')) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt('Uncaught Exception'); print ' 1585249372</h1>
                <p>'; print $this->translator->txt('Uncaught Exception txt'); print '</p>
            </div>';
            /** @noinspection MagicMethodsValidityInspection */
            return 0;
        }
        $this->firstname= uString::sql2text($user_data->firstname,1);
        $this->lastname= uString::sql2text($user_data->lastname,1);
        $this->email= $user_data->email;
        $this->cellphone= $user_data->cellphone;
        $avatar_timestamp=(int)$user_data->avatar_timestamp;
        $this->avatar_url=$uAuth_avatar->get_avatar('120',$user_id,$avatar_timestamp);

        /** @noinspection MagicMethodsValidityInspection */
        return 1;
    }
}
$sms=new dashboard($this);

if($sms->print_data) {?>
    <div class="container-fluid sms dashboard">
        <h1 class="page-header"><?=$sms->translator->txt('Dashboard')?> <small>MAD sms</small> <div class="pull-right"><small><span class="text-success"><?=$sms->translator->txt('Switched on')?></span> <button class="btn btn-danger btn-outline"><?=$sms->translator->txt('Switch off btn')?></button></small></div></h1>
        <div><p><?=$sms->translator->txt('MAD SMS Description 1')?><br><?=$sms->translator->txt('MAD SMS Description 2')?></p></div>
        <div class="tiles">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url= '/uAuth/profile_edit' ?>
                    <div class="card color1" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="<?=$sms->avatar_url?>" alt="<?=$sms->translator->txt('Profile')?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$sms->translator->txt('Profile')?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$sms->firstname?> <?=$sms->lastname?></a></div>
                        <div class="text"><?=$sms->cellphone?><br><?=$sms->email?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url= '/sms/api_doc' ?>
                    <div class="card color2" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="sms/img/api_doc.png" alt="<?=$sms->translator->txt('API Doc')?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$sms->translator->txt('API Doc')?></a></div>
<!--                        <div class="subtitle"><a href="<?=$url?>">Subtitle</a></div>-->
                        <div class="text"><?=$sms->translator->txt('API Doc description')?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url= '/sms/tokens' ?>
                    <div class="card color3" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="sms/img/api_tokens.jpg" alt="<?=$sms->translator->txt('Tokens')?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$sms->translator->txt('Tokens')?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$sms->translator->txt('Tokens description')?></a></div>
                        <div class="text"><?=$sms->translator->txt('Tokens txt')?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url= '/sms/profile_edit' ?>
                    <div class="card color4" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="sms/img/messages.jpg" alt="<?=$sms->translator->txt('Emails')?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$sms->translator->txt('Emails')?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$sms->translator->txt('Sent sms')?></a></div>
                        <div class="text"><?=$sms->translator->txt('Sent sms txt')?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url= '/sms/profile_edit' ?>
                    <div class="card color5" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="sms/img/billing.jpg" alt="<?=$sms->translator->txt('Your account')?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$sms->translator->txt('Your account')?></a></div>
                        <div class="subtitle"><a href="<?=$url?>">Осталось 1500 писем</a></div>
                        <div class="text"><?=$sms->translator->txt('Your account txt 1')?> 0.01 <span class="icon-rouble"></span> <?=$sms->translator->txt('Your account txt 2')?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/u235/template.php';
