<?php
namespace imap;
use uSes;

require_once 'processors/uSes.php';
require_once 'imap/classes/imap.php';

class renewKey_bg {
    public function __construct (&$uCore) {
        $uSes=new uSes($uCore);
        if(!$uSes->access(2)) {
            print json_encode(array(
                'status' => 'forbidden'
            ));
            exit;
        }

        $imap=new imap($uCore);
        $access_token=$imap->registerToken();

        print json_encode(array(
            'status'=> 'done',
            'access_token' =>$access_token
        ));
    }
}
new renewKey_bg($this);
