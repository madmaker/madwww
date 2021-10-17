<?php
namespace imap;
use processors\uFunc;
use uSes;

require_once 'processors/classes/uFunc.php';
require_once 'processors/uSes.php';

class imap {
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

        if(isset($_SESSION['imap']['access_token'])) {
            $timestamp=$_SESSION['imap']['access_token']['timestamp'];
            if($timestamp<time()-570000) {//9.5 minutes
                unset($_SESSION['imap']['access_token']);
                return $this->registerToken();
            }
            $user_hash=$_SESSION['imap']['access_token']['user_hash'];
            $user_key=$_SESSION['imap']['access_token']['user_key'];
        }
        else {
            $user_hash = $this->uFunc->genHash();
            $user_key = $this->uFunc->genHash();

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => madimap_protocol . '://' . madimap_host_backend . ':' . madimap_port,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode(array(
                    'task' => 'registerHash',
                    'hash' => madimap_hash,
                    'key' => madimap_key,
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
                print madimap_protocol . '://' . madimap_host_backend . ':' . madimap_port;
                print_r($response);
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
