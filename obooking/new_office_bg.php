<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class new_office_bg {
    private $obooking;
    private $office_name;
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["office_name"])) {
            $this->uFunc->error(10);
        }
        $this->office_name=trim($_POST["office_name"]);
        if($this->office_name === '') {
            print json_encode([
            'status'=>'error',
            'msg'=>'office name is empty'
            ]);
            exit;
        }
    }

    private function create_new_office() {
        $office_id=$this->obooking->create_new_office($this->office_name);

        print json_encode([
            'status'=>'done',
            'office_id'=>$office_id
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
        $this->create_new_office();
    }
}
new new_office_bg($this);
