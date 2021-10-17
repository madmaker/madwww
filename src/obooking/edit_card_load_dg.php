<?php
namespace obooking;
use uSes;

require_once "processors/uSes.php";
require_once "obooking/classes/common.php";

class edit_card_load_dg {
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

        if(!$cardInfo=$obooking->getClientCardInfo('card_type_id,card_number,start_date,valid_thru',$rec_id)) {
            print json_encode([
                'status'=>'error',
                'msg'=>'card is not found'
            ]);
            exit;
        }

        $cardTypesAr=$obooking->getCardTypes('card_type_id,card_type_name,validity,price');

        print json_encode([
            'status'=>'success',
            'cardTypesAr'=>$cardTypesAr,
            'cardTypeId'=>(int)$cardInfo->card_type_id,
            'card_number'=>$cardInfo->card_number,
            'cardStartDateFormatted'=>date('d.m.Y',$cardInfo->start_date),
            'cardValidThruDateFormatted'=>date('d.m.Y',$cardInfo->valid_thru)
        ]);
        exit;
    }
}
new edit_card_load_dg($this);
