<?php
namespace obooking;

use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class get_rec_types_bg{
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


        $show_select_btn=0;
        if(isset($_POST["show_select_btn"])) {
            $show_select_btn=(int)$_POST["show_select_btn"];
            $show_select_btn=(int)(bool)$show_select_btn;
        }

        $obooking->rec_types_list($show_select_btn);
    }
}
new get_rec_types_bg($this);
