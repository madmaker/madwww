<?php
namespace obooking;
use PDO;
use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class get_offices_list_bg{
    private $obooking;

    private function get_offices_select_options() {
        $q_offices=$this->obooking->get_offices();
        while($office=$q_offices->fetch(PDO::FETCH_OBJ)) {?>
            <option value="<?=$office->office_id?>"><?=$office->office_name?></option>
        <?}
    }
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->get_offices_select_options();
    }
}
new get_offices_list_bg($this);
