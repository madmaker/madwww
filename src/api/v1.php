<?php
use processors\uFunc;
if ($this->url_prop[1] === "inventories") {
    require "inc/inventories.php";
} elseif ($this->url_prop[1] === "user") {
    require "inc/user.php";
} elseif ($this->url_prop[1] === "commodities") {
    require "inc/commodities.php";
} elseif ($this->url_prop[1] === "subscription") {
    require "inc/subscription.php";
} elseif ($this->url_prop[1] === "ipn") {
    require "inc/ipn.php";
} elseif ($this->url_prop[1] === "admin") {
    require "classes/api_functions.php";
    $api = new apiEvotor($this);
    if ($this->url_prop[1] === "admin" && $this->url_prop[2] === "uuid") {
        echo $api->generate_uuid();
    } elseif ($this->url_prop[1] === "admin" && $this->url_prop[2] === "this") {
        require "classes/data_processing.php";
        $apidata = new dataProc($this);
        $reserv = [
            "21026673-0c22-49b0-9adc-bd4f16d11836" => 1,
            "68f67f14-c60a-4ca7-9618-3925739312bf" => 1,
        ];
        $apidata->cart_proc($reserv, "create");
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "evotor"
    ) {
        // Тестирование загрузки данных в кассу
        require "classes/data_processing.php";
        $apidata = new dataProc($this);
        $apidata->create_full_object_and_load_in_terminal();
    } elseif ($this->url_prop[1] === "admin" && $this->url_prop[2] === "cron") {
        echo $_SERVER['DOCUMENT_ROOT'];
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "gentoken"
    ) {
        echo $api->gen_token_activation(5, site_id);
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listmarket"
    ) {
        print_r($api->list_markets());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listterminal"
    ) {
        print_r($api->list_terminals());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listsotrudnik"
    ) {
        print_r($api->list_employees());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listtovar"
    ) {
        print_r($api->list_items());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listdocument"
    ) {
        print_r($api->list_documents());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listschemes"
    ) {
        print_r($api->list_schemes());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listadditionalfields"
    ) {
        print_r($api->list_additional_fields());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "gettoken"
    ) {
        print_r($api->get_keytoken());
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "deltovar"
    ) {
        $del = [
            "uuid" => "",
        ];
        $deltov = [];
        //$deltov[] = $del;

        echo $api->del_items(json_encode($deltov, true));
    } elseif (
        $this->url_prop[1] === "admin" &&
        $this->url_prop[2] === "listostatok"
    ) {
        $product_uuid = "bcde992d-e931-4115-8cca-cb8a2e49cef5";
        $api->list_ostatki($product_uuid);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
