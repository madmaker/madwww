<?php
namespace obooking;
use DateTime;
use uSes;
use uString;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

/**
 * Updates information about client's card
 * ## Request:
 * POST
 * - int rec_id
 * - int card_type
 * - string card_valid_thru - formatted date (dd.mm.YYYY)
 * - string start_date  - formatted date (dd.mm.YYYY)
 * - string card_number
 * ## Response:
 * - status
 *      - success
 *      - error
 *      - forbidden
 * - msg - in case of error
 *      - wrong request - if haven't received required parameters
 *      - wrong valid thru date
 *      - wrong start date
 * - int valid_thru_timestamp
 * @package obooking
 */
class client_edit_card_bg {
    private $start_date;
    private $card_number;
    private $card_valid_thru;
    /**
     * @var int
     */
    private $card_type;

    private function check_data() {
        if(!isset(
            $_POST["rec_id"],
            $_POST["card_type"],
            $_POST["card_valid_thru"],
            $_POST["start_date"],
            $_POST["card_number"]
        )) {
            print json_encode([
                'status' => 'error',
                'msg' => 'wrong request'
            ]);
            exit;
        }

        $this->card_type=(int)$_POST["card_type"];
        $this->start_date=$_POST["start_date"];

        $this->card_valid_thru=trim($_POST["card_valid_thru"]);
        $card_valid_thru_isDate=uString::isDate($this->card_valid_thru);
        if($this->card_valid_thru!==""&&$this->card_valid_thru!=="0"&&!$card_valid_thru_isDate) {
            print json_encode([
                'status'=>'error',
                'msg'=>'wrong valid thru date'
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

        $this->card_number=$_POST["card_number"];

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

        $obooking->updateClientCard([
            'card_type_id'=>$this->card_type,
            'card_number'=>$this->card_number,
            'valid_thru'=>$this->card_valid_thru,
            'start_date'=>$this->start_date
        ],$rec_id);

        echo json_encode(array(
            'status'=>'success',
            'valid_thru_timestamp'=>$this->card_valid_thru
        ));

    }
}
new client_edit_card_bg($this);
