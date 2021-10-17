<?php
namespace smtp;
use processors\uFunc;
use translator\translator;
use uAuth\common;
use uAuth_avatar;
use uCore;
use uSes;
use uString;

//BPbaaApsx3n6qn88nPCKsb2SdsFGDN4t6pmmFwmwUU

require_once "processors/classes/uFunc.php";
require_once "processors/uSes.php";
require_once "uAuth/classes/common.php";
require_once "translator/translator.php";
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
    /**
     * @var uAuth_avatar
     */
    private $uAuth_avatar;
    /**
     * @var common
     */
    private $uAuth;
    /**
     * @var uFunc
     */
    private $uFunc;
    /**
     * @var uSes
     */
    private $uSes;
    private $uCore;
    private function check_data() {

    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        if(!isset($this->uCore)) $this->uCore=new uCore();
        $this->uSes=new uSes($this->uCore);
        $this->uFunc=new uFunc($this->uCore);
        $this->translator=new translator(site_lang,"smtp/dashboard.php");

        $this->uCore->page["page_title"]=$this->translator->txt("Page Title");

        ob_start();
        if(!$this->uSes->access(2)) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt("You are not authorized"); print '</h1>
                <p>'; print $this->translator->txt("Please sign in"); print '</p>
                <p><!--suppress HtmlUnknownAnchorTarget --><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">'; print $this->translator->txt("Sign in"); print '</a></p>
            </div>';
            return 0;
        }
        elseif(!$this->uFunc->mod_installed("smtp")) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt("Forbidden"); print '</h1>
                <p>'; print $this->translator->txt("You do not have sufficient permissions to access this page"); print '</p>
            </div>';
            return 0;
        }
        else $this->print_data=1;

        $this->check_data();

        $this->uFunc->incJs("smtp/js/dashboard.min.js");
        $this->uFunc->incCss("smtp/css/smtp.min.css",1);
        $this->uFunc->incCss("smtp/css/dashboard.min.css",1);

        $this->uAuth=new common($this->uCore);
        $this->uAuth_avatar=new uAuth_avatar($this->uCore);

        $user_id=$this->uSes->get_val("user_id");
        if(!$user_data=$this->uAuth->user_id2user_data($user_id,"firstname, lastname, email, cellphone, avatar_timestamp")) {
            $this->print_data=0;
            print '<div class="jumbotron">
            <h1 class="page-header">'; print $this->translator->txt("Uncaught Exception"); print ' 1585249372</h1>
                <p>'; print $this->translator->txt("Uncaught Exception txt"); print '</p>
            </div>';
            return 0;
        }
        $this->firstname= uString::sql2text($user_data->firstname,1);
        $this->lastname= uString::sql2text($user_data->lastname,1);
        $this->email= $user_data->email;
        $this->cellphone= $user_data->cellphone;
        $avatar_timestamp=(int)$user_data->avatar_timestamp;
        $this->avatar_url=$this->uAuth_avatar->get_avatar('120',$user_id,$avatar_timestamp);

        return 1;
    }
}
$smtp=new dashboard($this);

if($smtp->print_data) {?>
    <div class="container-fluid smtp dashboard">
        <h1 class="page-header"><?=$smtp->translator->txt("Dashboard")?> <small>MAD SMTP</small> <div class="pull-right"><small><span class="text-success"><?=$smtp->translator->txt("Switched on")?></span> <button class="btn btn-danger btn-outline"><?=$smtp->translator->txt("Switch off btn")?></button></small></div></h1>
        <div><p><?=$smtp->translator->txt("MAD SFTP Description 1")?><br><?=$smtp->translator->txt("MAD SFTP Description 2")?></p></div>
        <div class="tiles">
            <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url="/uAuth/profile_edit"?>
                    <div class="card color1" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="<?=$smtp->avatar_url?>" alt="<?=$smtp->translator->txt("Profile")?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$smtp->translator->txt("Profile")?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$smtp->firstname?> <?=$smtp->lastname?></a></div>
                        <div class="text"><?=$smtp->cellphone?><br><?=$smtp->email?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url="/smtp/api_doc"?>
                    <div class="card color2" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="smtp/img/api_doc.png" alt="<?=$smtp->translator->txt("API Doc")?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$smtp->translator->txt("API Doc")?></a></div>
<!--                        <div class="subtitle"><a href="<?=$url?>">Subtitle</a></div>-->
                        <div class="text"><?=$smtp->translator->txt("API Doc description")?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url="/smtp/tokens"?>
                    <div class="card color3" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="smtp/img/api_tokens.jpg" alt="<?=$smtp->translator->txt("Tokens")?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$smtp->translator->txt("Tokens")?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$smtp->translator->txt("Tokens description")?></a></div>
                        <div class="text"><?=$smtp->translator->txt("Tokens txt")?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url="/smtp/profile_edit"?>
                    <div class="card color4" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="smtp/img/messages.jpg" alt="<?=$smtp->translator->txt("Emails")?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$smtp->translator->txt("Emails")?></a></div>
                        <div class="subtitle"><a href="<?=$url?>"><?=$smtp->translator->txt("Sent emails")?></a></div>
                        <div class="text"><?=$smtp->translator->txt("Send emails txt")?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?$url="/smtp/profile_edit"?>
                    <div class="card color5" data-url="<?=$url?>">
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <a href="<?=$url?>"><img src="smtp/img/billing.jpg" alt="<?=$smtp->translator->txt("Your account")?>"></a>
                        </div>
                        <div class="title"><a href="<?=$url?>"><?=$smtp->translator->txt("Your account")?></a></div>
                        <div class="subtitle"><a href="<?=$url?>">Осталось 1500 писем</a></div>
                        <div class="text"><?=$smtp->translator->txt("Your account txt 1")?> 0.01 <span class="icon-rouble"></span> <?=$smtp->translator->txt("Your account txt 2")?></div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
                    <?/*$url="/smtp/profile_edit"*/?>
                    <div class="card color6 disabled"<?/* data-url="<?=$url?>"*/?>>
                        <div class="image-container-bg">&nbsp;</div>
                        <div class="image-container">
                            <?/*<a href="<?=$url?>">*/?><img src="smtp/img/constructor.jpg" alt="<?=$smtp->translator->txt("Emails constructor")?>"><?/*</a>*/?>
                        </div>
                        <div class="title"><?/*<a href="<?=$url?>">*/?><?=$smtp->translator->txt("Emails constructor")?><?/*</a>*/?></div>
                        <div class="subtitle"><?/*<a href="<?=$url?>">*/?><?=$smtp->translator->txt("Soon")?><?/*</a>*/?></div>
                        <div class="text"><?=$smtp->translator->txt("Emails constructor txt")?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?}

$this->page_content=ob_get_contents();
ob_end_clean();

include 'templates/u235/template.php';
