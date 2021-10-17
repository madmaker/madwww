<?php
namespace obooking;
use DateTime;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Creates new Client Card
 * ## Request:
 * POST
 * - int client_id
 * - int card_type - card type id
 * - string card_valid_thru - formatted date (dd.mm.YYYY)
 * - string start_date - formatted date (dd.mm.YYYY)
 * - string card_number
 * - int price
 * - int paid_amount
 * - int payment_method
 * - int office_id
 * ## Response:
 * - status
 *      - success
 *      - error
 *      - forbidden
 * - msg - in case of error
 *      - wrong request - if have not received requested parameters
 *      - office is not found
 *      - payment method is not found
 *      - client is not found
 *      - valid thru date is wrong
 *      - start date is wrong
 * - rec_id - id of new record just created
 * - client_balance - client account balance after buying a card
 *
 * @package obooking
 */
class client_add_new_card_bg {
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
    private $card_number;
    private $card_valid_thru;
    /**
     * @var int
     */
    private $card_type;
    /**
     * @var int
     */
    private $client_id;

    private function check_data() {
        if(!isset(
            $_POST["client_id"],
            $_POST["card_type"],
            $_POST["card_valid_thru"],
            $_POST["start_date"],
            $_POST["card_number"],
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

        $this->paid_amount=trim($_POST["paid_amount"]);
        $this->paid_amount=(int)$this->paid_amount;
        if($this->paid_amount<0) {
            $this->paid_amount = 0;
        }

        $this->price=trim($_POST["price"]);
        $this->price=(int)$this->price;
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

        $this->card_type=(int)$_POST["card_type"];
        $this->start_date=$_POST["start_date"];

        $this->card_valid_thru=trim($_POST["card_valid_thru"]);
        $card_valid_thru_isDate=uString::isDate($this->card_valid_thru);
        if($this->card_valid_thru!==""&&$this->card_valid_thru!=="0"&&!$card_valid_thru_isDate) {
            print json_encode([
                'status'=>'error',
                'msg'=>'valid thru date is wrong'
            ]);
            exit;
        }
        if($card_valid_thru_isDate) {
            $date_formatted=DateTime::createFromFormat("d.m.Y",$this->card_valid_thru)->format("U");
            $this->card_valid_thru=$date_formatted;
        }
        else {
            $this->card_valid_thru = 0;
        }

        $this->start_date=trim($_POST["start_date"]);
        $start_date_isDate=uString::isDate($this->start_date);
        if($this->start_date!==""&&$this->start_date!=="0"&&!$start_date_isDate) {
            print json_encode([
                'status'=>'error',
                'msg'=>'start date is wrong'
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

        $this->card_number=$_POST["card_number"];
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

        $rec_id=$this->obooking->createNewClientCard($this->card_type,$this->card_number,$this->card_valid_thru,$this->start_date,$this->client_id);

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
            "Продажа клубной карты #".$this->card_number,
            $this->price,
            100
        );

        /** @noinspection PhpWrongStringConcatenationInspection */
        echo json_encode(array(
            "status"=>"success",
            "rec_id"=>$rec_id,
            'client_balance'=>($this->client_balance+$this->paid_amount-$this->price)
        ));

    }
}
new client_add_new_card_bg($this);
