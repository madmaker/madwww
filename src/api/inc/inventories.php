<?php

use processors\uFunc;

require_once "processors/classes/uFunc.php";
require_once "api/classes/data_processing.php";

//header('Accept: application/json');
//header('Content-Type: application/json');

class inventories
{
    private $uCore, $api;
    public $authorization_header, $site_id, $token_servisa, $storeuuid;

    private function check_data()
    {
        $this->authorization_header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
        $this->authorization_header = str_replace(
            "Bearer",
            '',
            $this->authorization_header
        );
        $this->authorization_header = trim($this->authorization_header);

        try {
            $stm = $this->uFunc->pdo("pages")->prepare("SELECT
            value,
            site_id
            FROM
            u235_conf
            WHERE 
            field='site_uuid' AND 
            value=:site_uuid
            ");
            $stm->bindParam(
                ':site_uuid',
                $this->authorization_header,
                PDO::PARAM_STR
            );
            $stm->execute();

            if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                $this->token_servisa = $row["value"];
                $this->site_id = $row["site_id"];
            } else {
                http_response_code(401);
                exit();
            }
        } catch (PDOException $e) {
            $this->uFunc->error('10' /*.$e->getMessage()*/);
        }

        $this->check_url_and_action();
    }

    public function check_url_and_action()
    {
        if (
            $this->uCore->url_prop[2] === "stores" &&
            $this->uCore->url_prop[4] === "products" &&
            $_SERVER['REQUEST_METHOD'] == 'POST'
        ) {
            if (isset($this->token_servisa)) {
                $postdatajson = file_get_contents("php://input");
                $postdata = json_decode($postdatajson, true);

                $this->storeuuid = $this->uCore->url_prop[3];

                try {
                    $stm = $this->uFunc->pdo("pages")->prepare("SELECT
                    site_id
                    FROM
                    u235_conf
                    WHERE 
                    field='store_uuid' AND 
                    value=:store_uuid
                    LIMIT 1
                    ");
                    $stm->bindParam(
                        ':store_uuid',
                        $this->storeuuid,
                        PDO::PARAM_STR
                    );
                    $stm->execute();

                    while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $site_id = $row["site_id"];
                        $this->api->upload_items_in_internet_market(
                            $postdata,
                            $site_id,
                            0
                        );
                        http_response_code(200);
                    }
                } catch (PDOException $e) {
                    $this->uFunc->error('20' /*.$e->getMessage()*/);
                }
            } else {
                http_response_code(401);
                $errors = [
                    "code" => 1002,
                ];
                $tmp = [];
                $tmp[] = $errors;
                $response_array = [
                    "errors" => $tmp,
                ];
                echo json_encode($response_array, true);
            }
        } elseif (
            $this->uCore->url_prop[2] === "stores" &&
            $this->uCore->url_prop[4] === "documents" &&
            $_SERVER['REQUEST_METHOD'] == 'PUT'
        ) {
            $this->documents();
        } elseif (
            $this->uCore->url_prop[2] === "devices" &&
            $_SERVER['REQUEST_METHOD'] == 'PUT'
        ) {
            // Создан смарт-терминал
            // Данная функция пока не используется
        } elseif (
            $this->uCore->url_prop[2] === "employees" &&
            $_SERVER['REQUEST_METHOD'] == 'PUT'
        ) {
            // Создан сотрудник
            // Данная функция пока не используется
        } elseif (
            $this->uCore->url_prop[2] === "stores" &&
            $_SERVER['REQUEST_METHOD'] == 'PUT'
        ) {
            // Создан магазин
            // Данная функция пока не используется
        } else {
            header('HTTP/1.1 404 Not Found');
            exit();
        }
    }

    public function documents()
    {
        if (isset($this->token_servisa)) {
            $putdata = file_get_contents("php://input");
            //uFunc::journal($putdata, "evotor_documents");
            $req_data = json_decode($putdata, true);
            $this->storeuuid = $this->uCore->url_prop[3];

            $uuid = $req_data[0]["uuid"];
            $type = $req_data[0]["type"];
            $transactions = $req_data[0]["transactions"];
            $number = $req_data[0]["number"];
            //        $deviceUuid = $req_data[0]["deviceUuid"];
            //        $closeDate = $req_data[0]["closeDate"];
            //        $openDate = $req_data[0]["openDate"];
            //        $openUserCode = $req_data[0]["openUserCode"];
            //        $openUserUuid = $req_data[0]["openUserUuid"];
            //        $closeUserCode = $req_data[0]["closeUserCode"];
            //        $closeUserUuid = $req_data[0]["closeUserUuid"];
            //        $sessionUUID = $req_data[0]["sessionUUID"];
            //        $sessionNumber = $req_data[0]["sessionNumber"];
            //        $closeResultSum = $req_data[0]["closeResultSum"];
            //        $closeSum = $req_data[0]["closeSum"];
            //        $extras = $req_data[0]["extras"];
            //        $version = $req_data[0]["version"];

            if (isset($this->site_id)) {
                // Начинается обработка документов
                try {
                    $stm = $this->uFunc->pdo("uCat")->prepare("SELECT
                    document_id
                    FROM
                    evotor_documents
                    WHERE
                    document_id=:document_id AND
                    number=:number AND 
                    site_id=:site_id
                    ");
                    $stm->bindParam(':document_id', $uuid, PDO::PARAM_STR);
                    $stm->bindParam(':number', $number, PDO::PARAM_INT);
                    $stm->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
                    $stm->execute();

                    if ($rows = $stm->fetch(PDO::FETCH_ASSOC)) {
                        http_response_code(200);
                        exit();
                    }
                } catch (PDOException $e) {
                    $this->uFunc->error('30' /*.$e->getMessage()*/);
                }

                try {
                    $ins_doc = $this->uFunc->pdo("uCat")->prepare("INSERT INTO
                    evotor_documents
                    SET
                    document_id=:document_id,
                    type=:type,
                    number=:number,
                    site_id=:site_id
                    ");

                    $ins_doc->bindParam(':document_id', $uuid, PDO::PARAM_STR);
                    $ins_doc->bindParam(':type', $type, PDO::PARAM_STR);
                    $ins_doc->bindParam(':number', $number, PDO::PARAM_INT);
                    $ins_doc->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $ins_doc->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error('40' /*.$e->getMessage()*/);
                }

                if ($type == "ACCEPT") {
                    // Приёмка
                    foreach ($transactions as $keys => $value) {
                        if ($value["type"] == "REGISTER_POSITION") {
                            $quantity_new =
                                (float) $value["balanceQuantity"] +
                                (float) $value["quantity"];

                            $this->update_variants_and_items(
                                $value["commodityUuid"],
                                $quantity_new,
                                "ACCEPT"
                            );
                        }
                    }
                    http_response_code(200);
                } elseif ($type == "INVENTORY") {
                    // Инвентаризация
                    foreach ($transactions as $keys => $value) {
                        if ($value["type"] == "INVENTORY") {
                            $quantity_new = (float) $value["quantity"];

                            $this->update_variants_and_items(
                                $value["commodityUuid"],
                                $quantity_new,
                                "INVENTORY"
                            );
                        }
                    }
                    http_response_code(200);
                } elseif ($type == "WRITE_OFF" || $type == "RETURN") {
                    // Списание товара и Возврат товара поставщику
                    foreach ($transactions as $keys => $value) {
                        if ($value["type"] == "REGISTER_POSITION") {
                            $quantity_new =
                                (float) $value["balanceQuantity"] -
                                (float) $value["quantity"];
                            $this->update_variants_and_items(
                                $value["commodityUuid"],
                                $quantity_new,
                                "WRITE_OFF or RETURN"
                            );
                        }
                    }
                    http_response_code(200);
                } elseif ($type == "REVALUATION") {
                    // Переоценка
                    foreach ($transactions as $keys => $value) {
                        if ($value["type"] == "REVALUATION") {
                            try {
                                $search_var_item = $this->uFunc->pdo("uCat")
                                    ->prepare("SELECT
                                default_var
                                FROM
                                items_variants
                                WHERE
                                uuid_variant=:evotor_uuid AND
                                site_id=:site_id
                                ");

                                $search_var_item->bindParam(
                                    ':site_id',
                                    $this->site_id,
                                    PDO::PARAM_INT
                                );
                                $search_var_item->bindParam(
                                    ':evotor_uuid',
                                    $value["commodityUuid"],
                                    PDO::PARAM_STR
                                );
                                $search_var_item->execute();

                                if (
                                    $result = $search_var_item->fetch(
                                        PDO::FETCH_ASSOC
                                    )
                                ) {
                                    $flag_variants = true;
                                    $default_var =
                                        (bool) $result["default_var"];
                                } else {
                                    $flag_variants = false;
                                }
                            } catch (PDOException $e) {
                                $this->uFunc->error('50' /*.$e->getMessage()*/);
                            }

                            if ($flag_variants) {
                                // Поменять quantity в обоих таблицах, если default_var=1
                                try {
                                    $upd_var = $this->uFunc->pdo("uCat")
                                        ->prepare("UPDATE
                                    items_variants
                                    SET
                                    price=:price
                                    WHERE
                                    site_id=:site_id AND
                                    uuid_variant=:evotor_uuid
                                    ");

                                    $upd_var->bindParam(
                                        ':price',
                                        $value["newPrice"],
                                        PDO::PARAM_STR
                                    );
                                    $upd_var->bindParam(
                                        ':evotor_uuid',
                                        $value["commodityUuid"],
                                        PDO::PARAM_STR
                                    );
                                    $upd_var->bindParam(
                                        ':site_id',
                                        $this->site_id,
                                        PDO::PARAM_INT
                                    );
                                    $upd_var->execute();
                                } catch (PDOException $e) {
                                    $this->uFunc->error(
                                        '60' /*.$e->getMessage()*/
                                    );
                                }

                                if ($default_var) {
                                    try {
                                        $upd_item = $this->uFunc->pdo("uCat")
                                            ->prepare("UPDATE
                                        u235_items
                                        SET
                                        item_price=:price,
                                        item_cost_price=:cost_price,
                                        item_status=1
                                        WHERE
                                        evotor_uuid=:evotor_uuid AND
                                        site_id=:site_id
                                        ");

                                        $upd_item->bindParam(
                                            ':site_id',
                                            $this->site_id,
                                            PDO::PARAM_INT
                                        );
                                        $upd_item->bindParam(
                                            ':price',
                                            $value["newPrice"],
                                            PDO::PARAM_STR
                                        );
                                        $upd_item->bindParam(
                                            ':cost_price',
                                            $value["acceptPrice"],
                                            PDO::PARAM_STR
                                        );
                                        $upd_item->bindParam(
                                            ':evotor_uuid',
                                            $value["commodityUuid"],
                                            PDO::PARAM_STR
                                        );
                                        $upd_item->execute();
                                    } catch (PDOException $e) {
                                        $this->uFunc->error(
                                            '70' /*.$e->getMessage()*/
                                        );
                                    }
                                }
                            } else {
                                // Поменять quantity только в таблице u235_items
                                try {
                                    $upd_item = $this->uFunc->pdo("uCat")
                                        ->prepare("UPDATE
                                    u235_items
                                    SET
                                    item_price=:price,
                                    item_cost_price=:cost_price,
                                    item_status=1
                                    WHERE
                                    evotor_uuid=:evotor_uuid AND
                                    site_id=:site_id
                                    ");

                                    $upd_item->bindParam(
                                        ':site_id',
                                        $this->site_id,
                                        PDO::PARAM_INT
                                    );
                                    $upd_item->bindParam(
                                        ':price',
                                        $value["newPrice"],
                                        PDO::PARAM_STR
                                    );
                                    $upd_item->bindParam(
                                        ':cost_price',
                                        $value["acceptPrice"],
                                        PDO::PARAM_STR
                                    );
                                    $upd_item->bindParam(
                                        ':evotor_uuid',
                                        $value["commodityUuid"],
                                        PDO::PARAM_STR
                                    );
                                    $upd_item->execute();
                                } catch (PDOException $e) {
                                    $this->uFunc->error(
                                        '80' /*.$e->getMessage()*/
                                    );
                                }
                            }
                        }
                    }
                    http_response_code(200);
                } else {
                    http_response_code(200);
                    exit();
                }
            }
        } else {
            http_response_code(401);
            $errors = [
                "code" => 1002,
            ];
            $tmp = [];
            $tmp[] = $errors;
            $response_array = [
                "errors" => $tmp,
            ];
            echo json_encode($response_array, true);
        }
    }

    public function update_variants_and_items(
        $uuid,
        $quantity_new,
        $type_action = "default"
    ) {
        try {
            $search_var_item = $this->uFunc->pdo("uCat")->prepare("SELECT
            default_var
            FROM
            items_variants
            WHERE
            uuid_variant=:evotor_uuid AND
            site_id=:site_id
            ");

            $search_var_item->bindParam(
                ':site_id',
                $this->site_id,
                PDO::PARAM_INT
            );
            $search_var_item->bindParam(':evotor_uuid', $uuid, PDO::PARAM_STR);
            $search_var_item->execute();

            if ($result = $search_var_item->fetch(PDO::FETCH_ASSOC)) {
                $flag_variants = true;
                $default_var = (bool) $result["default_var"];
            } else {
                $flag_variants = false;
            }
        } catch (PDOException $e) {
            $this->uFunc->error('90' . $type_action /*.$e->getMessage()*/);
        }

        if ($flag_variants) {
            // Поменять quantity в обоих таблицах, если default_var=1
            try {
                $upd_var = $this->uFunc->pdo("uCat")->prepare("UPDATE
                items_variants
                SET
                var_quantity=:quantity
                WHERE
                site_id=:site_id AND
                uuid_variant=:evotor_uuid
                ");

                $upd_var->bindParam(':quantity', $quantity_new, PDO::PARAM_STR);
                $upd_var->bindParam(':evotor_uuid', $uuid, PDO::PARAM_STR);
                $upd_var->bindParam(':site_id', $this->site_id, PDO::PARAM_INT);
                $upd_var->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('100' . $type_action /*.$e->getMessage()*/);
            }

            if ($default_var) {
                try {
                    $upd_item = $this->uFunc->pdo("uCat")->prepare("UPDATE
                    u235_items
                    SET
                    quantity=:quantity,
                    item_status=1
                    WHERE
                    evotor_uuid=:evotor_uuid AND
                    site_id=:site_id
                    ");

                    $upd_item->bindParam(
                        ':site_id',
                        $this->site_id,
                        PDO::PARAM_INT
                    );
                    $upd_item->bindParam(
                        ':quantity',
                        $quantity_new,
                        PDO::PARAM_STR
                    );
                    $upd_item->bindParam(':evotor_uuid', $uuid, PDO::PARAM_STR);
                    $upd_item->execute();
                } catch (PDOException $e) {
                    $this->uFunc->error(
                        '110' . $type_action /*.$e->getMessage()*/
                    );
                }
            }
        } else {
            // Поменять quantity только в таблице u235_items
            try {
                $upd_item = $this->uFunc->pdo("uCat")->prepare("UPDATE
                u235_items
                SET
                quantity=:quantity,
                item_status=1
                WHERE
                evotor_uuid=:evotor_uuid AND
                site_id=:site_id
                ");

                $upd_item->bindParam(
                    ':site_id',
                    $this->site_id,
                    PDO::PARAM_INT
                );
                $upd_item->bindParam(
                    ':quantity',
                    $quantity_new,
                    PDO::PARAM_STR
                );
                $upd_item->bindParam(':evotor_uuid', $uuid, PDO::PARAM_STR);
                $upd_item->execute();
            } catch (PDOException $e) {
                $this->uFunc->error('120' . $type_action /*.$e->getMessage()*/);
            }
        }
    }

    function __construct(&$uCore)
    {
        $this->uCore = &$uCore;
        $this->uFunc = new uFunc($this->uCore);
        $this->api = new dataProc($this->uCore);
        $this->check_data();
    }
}

new inventories($this);
