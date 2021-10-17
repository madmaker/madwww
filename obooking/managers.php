<?php

use obooking\common;
use processors\uFunc;

require_once 'processors/uSes.php';
require_once 'processors/classes/uFunc.php';
require_once "obooking/classes/common.php";

class managers {
    /**
     * @var uSes
     */
    public $uSes;
    /**
     * @var bool
     */
    public $is_admin;

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        if(!$this->uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$this->uSes->get_val('user_id');

        if($user_id) {
            $this->is_admin = $obooking->is_admin($user_id);
        }
        else {
            $this->is_admin = 0;
        }

        $uFunc=new uFunc($uCore);

        if($this->is_admin) {
            $uFunc->incJs(staticcontent_url . 'js/translator/translator.min.js');
            $uFunc->incJs(staticcontent_url . 'js/obooking/inline_create.min.js');
            $uFunc->incJs(staticcontent_url . 'js/obooking/managers.min.js');
            $uFunc->incCss(staticcontent_url . 'css/obooking/common.min.css');
            $uFunc->incCss(staticcontent_url . 'css/obooking/managers.min.css');

            $uCore->page['page_width'] = 1;
        }
    }
}
$obooking=new managers($this);
ob_start();

if($obooking->uSes->access(2)) {
    if($obooking->is_admin) {
        require_once 'dialogs/inline_edit_dialogs.php';
        require_once 'dialogs/inline_create_dialogs.php';
        ?>
        <div id="obooking">
            <div id="obooking_calendar">
                <div>
                    <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_manager_init()">
                        <span class="icon-plus"></span> Добавить наставника
                    </button>
                </div>
                <div id="managers_list"></div>
            </div>
        </div>

        <?php
    }
    else {?>
        <div class="jumbotron">
            <h1 class="page-header">Школа Рока</h1>
            <p>У вас нет доступа к этой странице</p>
        </div>
    <?}
}
else {?>
    <div class="jumbotron">
        <h1 class="page-header">Школа Рока</h1>
        <p>Пожалуйста, авторизуйтесь</p>
        <p><a href="javascript:void(0)" class="btn btn-primary btn-lg"  onclick="uAuth_form.open()">Авторизоваться</a></p>
    </div>
<?}

$this->page_content=ob_get_clean();

include 'templates/template.php';
