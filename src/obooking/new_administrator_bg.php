<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once 'processors/classes/uFunc.php';
require_once 'obooking/classes/common.php';

class new_administrator_bg {
    private $obooking;
    private $administrator_name;
    private $uFunc;
    private function check_data() {
        if(!isset($_POST['administrator_name'])) {
            $this->uFunc->error(10);
        }
        $this->administrator_name=trim($_POST['administrator_name']);
        if($this->administrator_name === '') {
            print json_encode([
            'status'=>'error',
            'msg'=>'administrator name is empty'
            ]);
            exit;
        }
    }

    private function create_new_administrator() {
        $administrator_id=$this->obooking->create_new_administrator($this->administrator_name);

        print json_encode([
        'status'=>'done',
        'administrator_id'=>$administrator_id
        ]);
        exit;
    }
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }
        $this->obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$this->obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status'=>'forbidden'
            ]);
            exit;
        }

        $this->uFunc=new uFunc($uCore);

        $this->check_data();
        $this->create_new_administrator();
    }
}
new new_administrator_bg($this);
