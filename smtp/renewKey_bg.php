<?php
namespace smtp;
use uSes;

require_once 'processors/uSes.php';
require_once 'smtp/classes/smtp.php';

class renewKey_bg {
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        $smtp=new smtp($uCore);
        $access_token=$smtp->registerToken();

        print json_encode(array(
            'status'=> 'done',
            'access_token' =>$access_token
        ));
    }
}
new renewKey_bg($this);
