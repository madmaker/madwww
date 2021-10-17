<?php
namespace obooking;
use DateTime;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Creates new subscription for client
 * ## Request:
 * POST
 * - int client_id
 * - int subscription_type
 * - string (formatted date) subscription_valid_thru
 * - string (formatted date) start_date
 * - int price
 * - int paid_amount
 * - int payment_method
 * - int office_id
 *
 * ## Response:
 * - status
 *      - success
 *      - error - in case of error.
 *      - forbidden - if you have not sufficient rights
 * - msg - error message in case of error
 *      - wrong request - if haven't received required parameters in request
 *      - office is not found - if office_id that is provided in request is not found
 *      - payment method is not found - if payment_method that is provided in request is not found
 *      - client is not found - if client_id that is provided in request is not found
 *      - wrong valid thru date - if subscription_valid_thru has wrong format. It should be formatted date (dd.mm.YYYY)
 *      - wrong start date - if start_date has wrong format. It should be formatted date (dd.mm.YYYY)
 *
 * - rec_id - id of record that has benn created
 * - classes_included - number of visits included to selected subscription type
 * - client_balance - current client balance after buying this subscription
 * @package obooking
 */
class client_add_new_subscription_bg{
    /**
     * @var string
     */
    private $classes_included;
    /**
     * @var string
     */
    private $subscription_type_name;
    /**
     * @var int
     */
    private $client_balance;
    /**
     * @var string
     */
    private $price;
    /**
     * @var int
     */
    private $payment_method;
    /**
     * @var int
     */
    private $office_id;
    /**
     * @var string
     */
    private $paid_amount;
    /**
     * @var common
     */
    private $obooking;
    private $start_date;
    private $subscription_valid_thru;
    /**
     * @var int
     */
    private $subscription_type;
    /**
     * @var int
     */
    private $client_id;

    private function check_data() {
        if(!isset(
            $_POST["client_id"],
            $_POST["subscription_type"],
            $_POST["subscription_valid_thru"],
            $_POST["start_date"],
            $_POST["price"],
            $_POST["paid_amount"],
            $_POST["payment_method"],
            $_POST["office_id"]
        )) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong request'
            ]);
            exit;
        }

        $this->paid_amount=(int)trim($_POST["paid_amount"]);
        if($this->paid_amount<0) {
            $this->paid_amount = 0;
        }

        $this->price=(int)trim($_POST["price"]);
        if($this->price<0) {
            $this->price = 0;
        }

        $this->office_id=(int)$_POST["office_id"];
        if(!$this->obooking->get_office_info("office_id",$this->office_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'office is not found'
            ]);
            exit;
        }


        $this->payment_method=(int)$_POST["payment_method"];
        if($this->payment_method<0||$this->payment_method>2) {
            print json_encode([
                'status'=>'error',
                'msg'=>'payment method is not found'
            ]);
            exit;
        }

        $this->client_id=(int)$_POST["client_id"];
        if(!$client=$this->obooking->get_client_info("client_balance",$this->client_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'client is not found'
            ]);
            exit;
        }
        $this->client_balance=(int)$client->client_balance;

        $this->subscription_type=(int)$_POST["subscription_type"];
        $this->subscription_type_name=$this->obooking->subscription_type_id2name($this->subscription_type);
        $this->classes_included=$this->obooking->subscription_type_id2classes_included($this->subscription_type);


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

        $rec_id=$this->obooking->createNewClientSubscription($this->subscription_type,$this->subscription_valid_thru,$this->start_date,$this->classes_included,$this->client_id);

        $this->obooking->update_client_balance($this->client_id,$this->paid_amount-$this->price);
        $this->obooking->save_balance_history(
            time(),
            $this->client_id,
            $this->office_id,
            "Внесение оплаты",
            $this->paid_amount,
            $this->payment_method
        );
        $this->obooking->save_balance_history(
            time(),
            $this->client_id,
            $this->office_id,
            "Продажа абонемента ".$this->subscription_type_name,
            $this->price,
            100
        );

        print json_encode(array(
            "status"=>"success",
            "rec_id"=>$rec_id,
            "classes_included"=>$this->classes_included,
            'client_balance'=>($this->client_balance+(int)$this->paid_amount-$this->price)
        ));
    }
}
new client_add_new_subscription_bg($this);
