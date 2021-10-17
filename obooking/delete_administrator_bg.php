<?php
namespace obooking;

use uSes;

require_once "processors/uSes.php";
require_once 'obooking/classes/common.php';

class delete_administrator_bg {
    private $obooking;
    private $administrator_id;
    private function check_data() {
        if(!isset($_POST['administrator_id'])) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }
        $this->administrator_id=(int)$_POST['administrator_id'];
    }
    private function delete_administrator() {
        $this->obooking->delete_administrator($this->administrator_id);
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

        $this->check_data();
        $this->delete_administrator();
    }
}
new delete_administrator_bg($this);
