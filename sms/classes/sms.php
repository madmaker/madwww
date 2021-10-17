<?php
namespace sms;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class sms {
    /**
     * @var uSes
     */
    private $uSes;
    /**
     * @var uFunc
     */
    private $uFunc;

    public function registerToken() {
        $user_id=$this->uSes->get_val('user_id');

        if(isset($_SESSION['sms']['access_token'])) {
            $timestamp=$_SESSION['sms']['access_token']['timestamp'];
            if($timestamp<time()-570000) {//9.5 minutes
                unset($_SESSION['sms']['access_token']);
                return $this->registerToken();
            }
            $user_hash=$_SESSION['sms']['access_token']['user_hash'];
            $user_key=$_SESSION['sms']['access_token']['user_key'];
        }
        else {
            $user_hash = $this->uFunc->genHash();
            $user_key = $this->uFunc->genHash();

            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, madsms_protocol . '://' . madsms_host . ':' . madsms_port);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'task' => 'registerHash',
                'hash' => madsms_hash,
                'key' => madsms_key,
                'user_id' => $user_id,
                'user_hash' => $user_hash,
                'user_key' => $user_key,
                'browser_ip' => $_SERVER['REMOTE_ADDR']
            )));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            if (!$server_output = curl_exec($ch)) {
//                print_r($server_output);
                curl_close($ch);
                return false;
            }
            curl_close($ch);
        }

        return array(
            'user_hash'=>$user_hash,
            'user_key'=>$user_key,
            'user_id'=>$user_id
        );
    }

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $this->uFunc=new uFunc($uCore);
    }
}
