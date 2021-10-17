<?php
namespace obooking;
use processors\uFunc;
use uSes;

require_once "processors/uSes.php";
require_once "processors/classes/uFunc.php";
require_once "obooking/classes/common.php";

class new_class_bg {
    private $office_id;
    private $obooking;
    private $class_name;
    private $uFunc;

    private function check_data() {
        if(!isset($_POST["class_name"],$_POST["office_id"])) {
            $this->uFunc->error(10);
        }
        $this->office_id=(int)$_POST["office_id"];
        if(!$this->obooking->get_office_info("office_id",$this->office_id)) {
            $this->uFunc->error(20);
        }

        $this->class_name=trim($_POST["class_name"]);
        if($this->class_name === '') {
            print json_encode([
            'status'=>'error',
            'msg'=>'class name is empty'
            ]);
            exit;
        }
    }

    private function create_new_class() {
        $class_id=$this->obooking->create_new_class($this->office_id,$this->class_name);

        print json_encode([
        'status'=>'done',
        'class_id'=>$class_id
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
        $this->create_new_class();
    }
}
new new_class_bg($this);
