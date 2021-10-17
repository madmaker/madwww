<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class delete_class_bg {
    private $obooking;
    private $class_id;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST["class_id"])) {
            $this->uFunc->error(10);
        }
        $this->class_id=(int)$_POST["class_id"];
    }
    private function delete_class() {
        $this->obooking->delete_class($this->class_id);
        echo "{
        'status':'done'
        }";
        exit;
    }

    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $this->delete_class();
    }
}
new delete_class_bg($this);
