<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class new_manager_bg {
    private $obooking;
    private $manager_name;
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["manager_name"])) {
            $this->uFunc->error(10);
        }
        $this->manager_name=trim($_POST["manager_name"]);
        if($this->manager_name === '') {
            print json_encode([
                'status'=>'error',
                'msg'=>'manager name is empty'
            ]);
            exit;
        }
    }

    private function create_new_manager() {
        $manager_id=$this->obooking->create_new_manager($this->manager_name);

        print json_encode([
        'status'=>'done',
        'manager_id'=>$manager_id
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
        $this->create_new_manager();
    }
}
new new_manager_bg($this);
