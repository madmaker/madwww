<?php
namespace obooking;
use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class get_offices_bg{
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

        $obooking->offices_list();
    }
}
new get_offices_bg($this);
