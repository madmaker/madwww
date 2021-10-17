<?php
require_once 'processors/classes/uFunc.php';
require_once 'translator/translator.php';

/** @noinspection PhpFullyQualifiedNameUsageInspection */
$translator=new \translator\translator(site_lang,'uAuth/dialogs/login.php');

if(!isset($this->uFunc)) {
    $this->uFunc = new \processors\uFunc($this->uCore);
}
$this->uFunc->incJs(staticcontent_url.'js/translator/translator.min.js');
$this->uFunc->incJs(staticcontent_url.'js/uAuth/auth_form.min.js');
$this->uFunc->incJs(staticcontent_url.'js/lib/u235/notificator.min.js');

$terms_link=$terms_link_closer= '';

$terms_page_id=(int)$this->uFunc->getConf('privacy_terms_text_id', 'content',1);
$use_MAD_SMS_to_send_SMS=(int)$this->uFunc->getConf('use MAD SMS to send SMS', 'content',0);

if($terms_page_id) {
    $txt_obj=$this->uFunc->getStatic_data_by_id($terms_page_id, 'page_name');
    if($txt_obj) {
        $terms_link = '<a target="_blank" href="' . u_sroot . 'page/' . $txt_obj->page_name . '">';
        $terms_link_closer = '</a>';
    }
} ?>

<script type="text/javascript">
    let use_MAD_SMS_to_send_SMS=<?=$use_MAD_SMS_to_send_SMS?>;
</script>

<div class="modal fade" id="uAuth_login_dg" tabindex="-1" role="dialog" aria-labelledby="uAuth_login_dgLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span><span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="uAuth_login_dgLabel"><?=$translator->txt('Login - dg title'/*Вход*/)?></h4>
            </div>
            <div class="modal-body">
                <?/*}*/?>
                <div id="uAuth_login_err" class="text-danger" style="display: none"></div>
                <div id="uAuth_login_info" class="bs-callout bs-callout-primary" style="display: none"></div>

                <form method="POST" onsubmit="uAuth_form.signIn(); return false;">
                    <div class="form-group">
                        <label for="uAuth_form_email" class="control-label"><?php
                            if($use_MAD_SMS_to_send_SMS) {
                                print $translator->txt('Email or phone number');
                            }
                            else {
                                print $translator->txt('Email');
                            }
                            ?>:</label>
                        <input aria-describedby="uAuth_form_email_help" placeholder="<?php
                        if($use_MAD_SMS_to_send_SMS) {
                            print $translator->txt('Email or phone number placeholder');
                        }
                        ?>" id="uAuth_form_email" class="form-control" type="text">
                        <span id="uAuth_form_email_help" class="help-block"></span>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="uAuth_form_pass"><?=$translator->txt('Password - field label'/*Пароль:*/)?></label>
                        <input aria-describedby="uAuth_form_pass_help" type="<?=(preg_match('/(?i)msie [1-8]/',$_SERVER['HTTP_USER_AGENT']))? 'text' : 'password' ?>" id="uAuth_form_pass" class="form-control" placeholder="<?=$translator->txt('Password - field placeholder'/*Пароль*/)?>">
                        <span id="uAuth_form_pass_help" class="help-block"></span>

                        <?/*<div id="g_recaptcha_div" <?=$this->uSes->get_val("captcha_needed")?"":'style="display: none"'?>>
                            <p>&nbsp;</p>
                            <div class="g-recaptcha" data-sitekey="<?=recaptcha_key?>"></div>
                            <input id="recaptcha_response_field" name="recaptcha_response_field" type="hidden" />
                        </div>*/?>
                    </div>
                    <p><?=$terms_link?><?=$translator->txt('privacy policy agreement notice for login')?><?=$terms_link_closer?></p>
                    <input type="submit" style="display:block; position:absolute; top:-100000px; left:-100000px;" value="<?=$translator->txt('Login - btn text')?>">
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger pull-left" onclick="uAuth_form.sendPassword()"><?=$translator->txt('Sign in without password')?></button>
                <button type="button" class="btn btn-primary" onclick="uAuth_form.signIn()"><?=$translator->txt('Login - btn text'/*Вход*/)?></button>
                <button type="button" class="btn btn-default" onclick="uAuth_form.register()"><?=$translator->txt('Register - btn text'/*Регистрация*/)?></button>
            </div>
        </div>
    </div>
</div>
