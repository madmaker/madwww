<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class delete_office_bg {
    private $obooking;
    private $office_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["office_id"])) {
            $this->uFunc->error(10);
        }
        $this->office_id=(int)$_POST["office_id"];
    }
    private function delete_office() {
        $this->obooking->delete_office($this->office_id);
        echo "{
        'status':'done'
        }";
        exit;
    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print 'forbidden';
            exit;
        }

        $user_id=(int)$uSes->get_val('user_id');
        $this->obooking=new common($uCore);
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print 'forbidden';
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $this->delete_office();
    }
}
new delete_office_bg($this);
