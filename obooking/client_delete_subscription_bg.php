<?php
namespace obooking;

use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Deletes selected client's subscription
 * ## Request:
 * POST
 * - int rec_id - id of client's subscription
 * ## Response:
 * - status
 *      - success
 *      - error
 *      - forbidden
 * - msg - in case of error
 *      - wrong request - if haven't received requested parameters in request
 * @package obooking
 */
class client_delete_subscription_bg{
    private function check_data() {
        if(!isset(
            $_POST["rec_id"]
        )) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }

        return (int)$_POST["rec_id"];
    }


    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }
        $obooking=new common($uCore);
        $user_id=(int)$uSes->get_val('user_id');
        $is_admin=$obooking->is_admin($user_id);

        if(!$is_admin) {
            print json_encode([
                'status' => 'forbidden'
            ]);
            exit;
        }

        $rec_id=$this->check_data();

        $obooking->deleteClientSubscription($rec_id);

        print json_encode(array(
            "status"=>"success"
        ));
    }
}
new client_delete_subscription_bg($this);
