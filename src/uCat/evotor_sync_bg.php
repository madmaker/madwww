<?php
require_once "processors/uSes.php";
require_once "api/classes/data_processing.php";


class evotor_sync_bg {
    private $uCore, $uSes, $api_data;

    private function check_data() {
        if ($this->uSes->access(25)) {
            if (isset($_POST['upload_method'])) {
                $upload_method = trim($_POST['upload_method']);
                if ($upload_method == "mad") {
                    $this->api_data->create_full_object_and_load_in_terminal();

                    $result = array(
                        'status' => 'done',
                        'error' => ""
                    );
                    return json_encode($result, true);
                }
                else if ($upload_method == "evotor") {
                    $itemlist = $this->api_data->apiEvotor->list_items();
                    $this->api_data->upload_items_in_internet_market($itemlist);

                    $result = array(
                        'status' => 'done',
                        'error' => false
                    );
                    return json_encode($result, true);
                }
                else {
                    $result = array(
                        'status' => 'error',
                        'error' => "unknown method"
                    );
                    return json_encode($result, true);
                }
            }
            else {
                $result = array(
                    'status' => 'error',
                    'error' => "not exist post"
                );
                return json_encode($result, true);
            }
        }
        else {
            $result = array(
                'status' => 'forbidden',
                'error' => "not access"
            );
            return json_encode($result, true);
        }
    }

    function __construct (&$uCore) {
        $this->uCore=&$uCore;
        $this->uSes=new uSes($this->uCore);
        $this->api_data = new dataProc($this->uCore);

        echo $this->check_data();
    }
}

$evotor_sync_bg = new evotor_sync_bg($this);
