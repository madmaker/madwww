<?php
namespace obooking;
use uSes;
use uString;

require_once 'processors/uSes.php';
require_once 'obooking/classes/common.php';

class invite_user {
    /**
     * @var invite_user
     */
    private $obooking;

    private function check_data() {
        if(!isset(
            $_POST['user_id'],
            $_POST['user_type']
        )) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }

        if(!uString::isDigits($_POST['user_id'])) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong user_id'
            ]);
            exit;
        }
        $user_id=(int)$_POST['user_id'];

        $user_type=$_POST['user_type'];

        if($user_type==='client') {
            if(!$user_data=$this->obooking->get_client_info('client_name AS firstname, client_lastname AS lastname, client_phone AS phone, client_email AS email',$user_id)) {
                print json_encode([
                    'status'=>'error',
                    'msg'=>'user is not found'
                ]);
                exit;
            }
        }
        elseif($user_type==='manager') {
            if(!$user_data=$this->obooking->get_manager_info('manager_name AS firstname, manager_lastname AS lastname, manager_phone AS phone, manager_email AS email',$user_id)) {
                print json_encode([
                    'status'=>'error',
                    'msg'=>'user is not found'
                ]);
                exit;
            }
        }
        elseif($user_type==='manager') {
            if(!$user_data=$this->obooking->get_manager_info('manager_name AS firstname, manager_lastname AS lastname, manager_phone AS phone, manager_email AS email',$user_id)) {
                print json_encode([
                    'status'=>'error',
                    'msg'=>'user is not found'
                ]);
                exit;
            }
        }
        else {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong user_type'
            ]);
            exit;
        }
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

        $this->check_data();
    }
}
new invite_user($this);
