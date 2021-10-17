<?php
namespace sms;
use uSes;

require_once 'processors/uSes.php';
require_once 'sms/classes/sms.php';

class renewKey_bg {
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        $sms=new sms($uCore);
        $access_token=$sms->registerToken();

        print json_encode(array(
            'status'=> 'done',
            'access_token' =>$access_token
        ));
    }
}
new renewKey_bg($this);
