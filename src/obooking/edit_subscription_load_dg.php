<?php
namespace obooking;
use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Retrieves data of subscription by rec_id (record id in database) and client_id
 * ## Request:
 * POST
 * - int rec_id
 * ## Response
 * - status
 *      - success
 *      - error
 *      - forbidden
 * - msg - in case of error
 *      - wrong request - if haven't received requested parameters
 *      - subscription is not found - if subscription with provided rec_id in request is not found
 * - array subscriptionTypesAr - array of subscriptions exists in system
 *      - subscription_type_id
 *      - subscription_type_name
 *      - validity
 *      - price
 * - int subscriptionTypeId - subscription_type_id of subscription passed in request (rec_id)
 * - string subscriptionStartDateFormatted - formatted date (dd.mm.YYYY) of subscription start date
 * - string subscriptionValidThruDateFormatted  - formatted date (dd.mm.YYYY) of subscription end date
 * @package obooking
 * @api
 */
class edit_subscription_load_dg {
    private function check_data() {
        if(!isset($_POST["rec_id"])) {
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

        if(!$subscriptionInfo=$obooking->getClientSubscriptionInfo('subscription_type_id,start_date,valid_thru,visits_left',$rec_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'subscription is not found'
            ]);
            exit;
        }

        $subscriptionTypesAr=$obooking->getSubscriptionTypes('subscription_type_id,subscription_type_name,price,validity');

        print json_encode([
            'status'=>'success',
            'subscriptionTypesAr'=>$subscriptionTypesAr,
            'subscriptionTypeId'=>(int)$subscriptionInfo->subscription_type_id,
            'subscriptionStartDateFormatted'=>date('d.m.Y',$subscriptionInfo->start_date),
            'subscriptionValidThruDateFormatted'=>date('d.m.Y',$subscriptionInfo->valid_thru),
            'visits_left'=>$subscriptionInfo->visits_left
        ]);
        exit;
    }
}
new edit_subscription_load_dg($this);
