<?php

use obooking\common;
use processors\uFunc;
use translator\translator;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once 'translator/translator.php';
require_once "obooking/classes/common.php";

class administrators {
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var translator
     */
    public $translator;
    /**
     * @var bool
     */
    public $is_admin;

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $uFunc=new uFunc($uCore);
        $obooking=new common($uCore);

        $this->translator=new translator(site_lang,'obooking/administrators.php');

        if($this->uSes->access(2)) {
            $user_id=$this->uSes->get_val('user_id');
            $this->is_admin=$obooking->is_admin($user_id);

            if($this->is_admin) {
                $uFunc->incJs(staticcontent_url . 'js/translator/translator.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/inline_create.min.js');
                $uFunc->incJs(staticcontent_url . 'js/obooking/administrators.min.js');
                $uFunc->incCss(staticcontent_url . 'css/obooking/common.min.css');
                $uFunc->incCss(staticcontent_url . 'css/obooking/administrators.min.css');

                $uCore->page['page_width'] = 1;
            }
        }
    }
}
$obooking=new administrators($this);
ob_start();

if($obooking->uSes->access(2)) {
    if($obooking->is_admin) {
        require_once 'dialogs/inline_edit_dialogs.php';
        require_once 'dialogs/inline_create_dialogs.php';
        ?>
        <div id="obooking">
            <div id="obooking_calendar">
                <div>
                    <button type="button" class="btn btn-primary"
                            onclick="obooking_inline_create.new_administrator_init()"><span
                                class="icon-plus"></span> <?= $obooking->translator->txt('Create new administrator') ?>
                    </button>
                </div>
                <div id="administrators_list"></div>
            </div>
        </div>

        <?php
    }
    else {?>
        <div class="jumbotron">
            <h1 class="page-header"><?=$obooking->translator->txt('Access denied')?></h1>
        </div>
    <?}
}
else {?>
    <div class="jumbotron">
        <h1 class="page-header"><?=$obooking->translator->txt('Access denied')?></h1>
        <p><?=$obooking->translator->txt('Sign in please')?></p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()"><?=$obooking->translator->txt('Sign in')?></a></p>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';
