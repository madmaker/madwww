<?php
require_once "processors/classes/uFunc.php";

class apiEvotor
{
    public $site_id, $keytoken, $storeUuid, $token_activation;

    private $uCore, $uFunc;

    public function add_items($tovardata)
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $tovardata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Authorization: ' . $this->keytoken,
        ]);

        return $result = curl_exec($ch);
    }

    public function del_items($deltovar)
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products/delete'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $deltovar);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Authorization: ' . $this->keytoken,
        ]);

        return $result = curl_exec($ch);
    }

    public function send_ostatki($ostatki)
    {
        //        $ostatki = array(
        //            "21026673-0c22-49b0-9adc-bd4f16d11836" => 19,
        //            "68f67f14-c60a-4ca7-9618-3925739312bf" => 11
        //        );

        $ostatki = json_encode($ostatki, true);

        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products/quantities'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $ostatki);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Authorization: ' . $this->keytoken,
        ]);

        return $result = curl_exec($ch);
    }

    public function list_ostatki($product_uuid)
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products/' .
                $product_uuid .
                '/quantities'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function create_or_die_reserv_wait($reserv, $action)
    {
        // Функция ожидает публикации от ЭВОТОРА
        //        $reserv = array(
        //            "bcde992d-e931-4115-8cca-cb8a2e49cef5" => 19,
        //            "68f67f14-c60a-4ca7-9618-3925739312bf" => 11
        //        );
        foreach ($reserv as $key => $value) {
            $arr_tmp = [];
            $arr = $this->list_ostatki($key);
            $old_quantity = $arr["quantity"]; // Нужно уточнить в документации, после ответа техподдержки
            if ($action == "create") {
                $new_quantity = (float) $old_quantity - (float) $value;
            } elseif ($action == "die") {
                $new_quantity = (float) $old_quantity + (float) $value;
            } else {
                echo "error";
                exit();
            }
            $arr_tmp[$key] = $new_quantity;
            $this->send_ostatki($arr_tmp);
            unset($arr_tmp);
        }
    }

    public function create_or_die_reserv($reserv, $action)
    {
        //        $reserv = array(
        //            "bcde992d-e931-4115-8cca-cb8a2e49cef5" => 19,
        //            "68f67f14-c60a-4ca7-9618-3925739312bf" => 11
        //        );
        //        $action = create or die;
        $finish_data = [];

        foreach ($reserv as $key => $value) {
            $arr = $this->list_items();
            foreach ($arr as $keys => $val) {
                if ($val["uuid"] == $key) {
                    if ($action == "create") {
                        $new_quantity =
                            (float) $val["quantity"] - (float) $value;
                    } elseif ($action == "die") {
                        $new_quantity =
                            (float) $val["quantity"] + (float) $value;
                    } else {
                        echo "error";
                        exit();
                    }
                    $finish_data[$key] = $new_quantity;
                }
            }
        }

        return $finish_data;
    }

    public function list_items()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_documents()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/documents'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_markets()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/search'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_terminals()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/devices/search'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_employees()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/employees/search'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_schemes()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products/schemes'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function list_additional_fields()
    {
        $ch = curl_init(
            'https://api.evotor.ru/api/v1/inventories/stores/' .
                $this->storeUuid .
                '/products/extras'
        );

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization: ' . $this->keytoken,
        ]);
        $result = curl_exec($ch);
        return json_decode($result, true);
    }

    public function generate_uuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function create_vars()
    {
        $this->site_id = site_id;
        $this->keytoken = $this->get_keytoken(); // "0b7f8113-e359-4346-8a9c-1f8d11dd2853";
        $this->storeUuid = $this->uFunc->getConf(
            'store_uuid',
            'uCat',
            'return false',
            $this->site_id
        );
        if (strlen($this->storeUuid) < 32) {
            $storeUuid = $this->list_markets();
            $this->storeUuid = $storeUuid[0]["uuid"];
            $this->uFunc->setConf(
                trim($this->storeUuid),
                "store_uuid",
                "uCat",
                PDO::PARAM_STR,
                $this->site_id
            );
        }
    }

    public function get_keytoken()
    {
        return $this->uFunc->getConf(
            'evotor_token',
            'uCat',
            'return false',
            $this->site_id
        );
    }

    public function gen_token_activation($count, $site_id)
    {
        $token = "";
        for ($k = 0; $k < $count; $k++) {
            $token .= $this->uFunc->genPass() . "-";
            sleep(1);
        }
        $token = substr($token, 0, -1);

        $this->uFunc->setConf(
            $token,
            "activation_token",
            "uCat",
            PDO::PARAM_STR,
            $site_id
        );

        return $token;
    }

    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        $this->uFunc = new \processors\uFunc($this->uCore);
        $this->create_vars();
    }
}
