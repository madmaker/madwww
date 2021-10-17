<?php
namespace smtp;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class smtp {
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

        if(isset($_SESSION['smtp']['access_token'])) {
            $timestamp=$_SESSION['smtp']['access_token']['timestamp'];
            if($timestamp<time()-570000) {//9.5 minutes
                unset($_SESSION['smtp']['access_token']);
                return $this->registerToken();
            }
            $user_hash=$_SESSION['smtp']['access_token']['user_hash'];
            $user_key=$_SESSION['smtp']['access_token']['user_key'];
        }
        else {
            $user_hash = $this->uFunc->genHash();
            $user_key = $this->uFunc->genHash();

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => madsmtp_protocol . '://' . madsmtp_host_backend . ':' . madsmtp_port,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode(array(
                    'task' => 'registerHash',
                    'hash' => madsmtp_hash,
                    'key' => madsmtp_key,
                    'user_id' => $user_id,
                    'user_hash' => $user_hash,
                    'user_key' => $user_key,
                    'IP' => $_SERVER['REMOTE_ADDR']
                )),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/x-www-form-urlencoded"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            if (!$response) {
                print madsmtp_protocol . '://' . madsmtp_host . ':' . madsmtp_port;
//                print_r($server_output);
                return false;
            }
        }

        return array(
            'user_hash'=>$user_hash,
            'user_key'=>$user_key,
            'user_id'=>$user_id,
            'IP' => $_SERVER['REMOTE_ADDR']
        );
    }

    public function __construct (&$uCore) {
        $this->uSes=new uSes($uCore);
        $this->uFunc=new uFunc($uCore);
    }
}
