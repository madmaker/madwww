<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class new_rec_type_bg {
    private $obooking;
    private $rec_type_name;
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["rec_type_name"])) {
            $this->uFunc->error(10);
        }
        $this->rec_type_name=trim($_POST["rec_type_name"]);

        if($this->rec_type_name === '') {
            print json_encode([
                'status'=>'error',
                'msg'=>'rec_type name is empty'
            ]);
            exit;
        }
    }

    private function create_new_rec_type() {
        $rec_type_id=$this->obooking->create_new_rec_type($this->rec_type_name);

        print json_encode([
            'status'=>'done',
            'rec_type_id'=>$rec_type_id
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
        $this->create_new_rec_type();
    }
}
new new_rec_type_bg($this);
