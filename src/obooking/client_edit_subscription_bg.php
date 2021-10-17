<?php
namespace obooking;
use DateTime;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Updates selected client's subscription
 * ## Request:
 * POST
 * - int rec_id
 * - int subscription_type
 * - string subscription_valid_thru - formatted date
 * - string start_date - formatted date
 * - int visits_left
 * ## Response:
 * - status
 *      - success
 *      - error - in case of error.
 *      - forbidden - if you have not sufficient rights
 * - msg - error message in case of error
 *      - wrong request - if haven't received required parameters in request
 *      - wrong valid thru date - if subscription_valid_thru has wrong format. It should be formatted date (dd.mm.YYYY)
 *      - wrong start date - if start_date has wrong format. It should be formatted date (dd.mm.YYYY)
 * @package obooking
 */
class client_edit_subscription_bg{
    /**
     * @var int
     */
    private $visits_left;
    private $start_date;
    private $subscription_valid_thru;
    /**
     * @var int
     */
    private $subscription_type;

    private function check_data() {
        if(!isset(
            $_POST["rec_id"],
            $_POST["subscription_type"],
            $_POST["subscription_valid_thru"],
            $_POST["start_date"],
            $_POST["visits_left"]
        )) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }

        $this->visits_left=(int)$_POST["visits_left"];

        $rec_id=(int)$_POST["rec_id"];

        $this->subscription_type=(int)$_POST["subscription_type"];

        $this->start_date=$_POST["start_date"];
        $this->subscription_valid_thru=trim($_POST["subscription_valid_thru"]);
        $subscription_valid_thru_isDate=uString::isDate($this->subscription_valid_thru);
        if($this->subscription_valid_thru!==""&&$this->subscription_valid_thru!=="0"&&!$subscription_valid_thru_isDate) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong valid thru date'
            ]);
            exit;
        }
        if($subscription_valid_thru_isDate) {
            $date_formatted=DateTime::createFromFormat("d.m.Y",$this->subscription_valid_thru)->format("U");
            $this->subscription_valid_thru=$date_formatted;
        }
        else {
            $this->subscription_valid_thru = 0;
        }

        $this->start_date=trim($_POST["start_date"]);
        $start_date_isDate=uString::isDate($this->start_date);
        if($this->start_date!==""&&$this->start_date!=="0"&&!$start_date_isDate) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong start date'
            ]);
            exit;
        }
        if($start_date_isDate) {
            $date_formatted=DateTime::createFromFormat("d.m.Y",$this->start_date)->format("U");
            $this->start_date=$date_formatted;
        }
        else {
            $this->start_date = 0;
        }

        return $rec_id;
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

        $obooking->updateClientSubscription([
            'subscription_type_id'=>$this->subscription_type,
            'valid_thru'=>$this->subscription_valid_thru,
            'start_date'=>$this->start_date,
            'visits_left'=>$this->visits_left
        ],$rec_id);

        print json_encode(array(
            "status"=>"success"
        ));
    }
}
new client_edit_subscription_bg($this);
