<?php

use obooking\common;
use processors\uFunc;

require_once "obooking/classes/common.php";
require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";

class rec_types {
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $uFunc=new uFunc($uCore);
        $uFunc->incJs(staticcontent_url . 'js/translator/translator.min.js');
        $uFunc->incJs(staticcontent_url . 'js/obooking/inline_create.min.js');
        $uFunc->incJs(staticcontent_url . 'js/obooking/rec_types.min.js');
        $uFunc->incCss(staticcontent_url . "css/obooking/common.min.css");
        $uFunc->incCss(staticcontent_url . "css/obooking/rec_types.min.css");

        $uCore->page['page_width']=1;
    }
}
new rec_types($this);
ob_start();
require_once "obooking/dialogs/inline_edit_dialogs.php";
require_once "dialogs/inline_create_dialogs.php";
?>
    <div id="obooking">
        <div id="obooking_rec_types">
            <div>
                <button type="button" class="btn btn-primary" onclick="obooking_inline_create.new_rec_type_init()"><span class="icon-plus"></span> Добавить тип записи</button>
            </div>
            <div id="rec_types_list"></div>
        </div>
    </div>

<?$this->page_content=ob_get_clean();

include 'templates/template.php';
