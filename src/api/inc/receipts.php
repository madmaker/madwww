<?php
require_once "processors/classes/uFunc.php";
require_once "api/classes/data_processing.php";
$uFunc = new \processors\uFunc($this);
$api_data = new dataProc($this);

header('Accept: application/json');
header('Content-Type: application/json');
$conn_token = "41e035ac-00f9-4deb-932f-f7871965de7b";

$authorization_header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
$authorization_header = str_replace("Bearer", '', $authorization_header);
$authorization_header = trim($authorization_header);

if ($this->url_prop[1] === "receipts" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stm = $uFunc->pdo("pages")->prepare("SELECT
        value,
        site_id
        FROM
        u235_conf
        WHERE 
        field='site_uuid' AND 
        value=:site_uuid
        ");
        $stm->bindParam(':site_uuid', $authorization_header, PDO::PARAM_STR);
        $stm->execute();

        if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
            $token_servisa = $row["value"];
            $site_id = $row["site_id"];
        } else {
            http_response_code(401);
            exit();
        }
    } catch (PDOException $e) {
        $uFunc->error('10' /*.$e->getMessage()*/);
    }

    if (isset($token_servisa)) {
        $postdatajson = file_get_contents("php://input");
        //$uFunc->journal($postdatajson,'evotor_documents');
        $postdata = json_decode($postdatajson, true);

        $event_id = $postdata["id"];
        $timestamp = $postdata["timestamp"];
        $userId = $postdata["userId"];
        $type = $postdata["type"];
        $version = $postdata["version"];
        $data = $postdata["data"];

        $id_data = $data["id"];
        $deviceId = $data["deviceId"];
        $storeId = $data["storeId"];
        $dateTime = $data["dateTime"];
        $type_data = $data["type"];
        $shiftId = $data["shiftId"];
        $employeeId = $data["employeeId"];
        $paymentSource = $data["paymentSource"];
        $infoCheck = $data["infoCheck"];
        $egais = $data["egais"];
        $items = $data["items"];

        $totalTax = $data["totalTax"];
        $totalDiscount = $data["totalDiscount"];
        $totalAmount = $data["totalAmount"];
        $extras = $data["extras"];

        if (isset($site_id)) {
            try {
                $stm = $uFunc->pdo("uCat")->prepare("SELECT
                    receipt_id
                    FROM
                    receipts
                    WHERE
                    receipt_id=:receipt_id AND
                    site_id=:site_id
                    ");
                $stm->bindParam(':receipt_id', $id_data, PDO::PARAM_STR);
                $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $stm->execute();

                if ($rows = $stm->fetch(PDO::FETCH_ASSOC)) {
                    http_response_code(200);
                    exit();
                }
            } catch (PDOException $e) {
                $uFunc->error('20' /*.$e->getMessage()*/);
            }

            try {
                $ins_receipt = $uFunc->pdo("uCat")->prepare("INSERT INTO
                    receipts
                    SET
                    receipt_uuid=:receipt_uuid,
                    event_id=:event_id,
                    timestamp=:timestamp_ev,
                    user_id=:user_id,
                    event_type=:event_type,
                    deviceId=:deviceId,
                    storeId=:storeId,
                    dateTime=:dateTime,
                    receipt_type=:receipt_type,
                    site_id=:site_id
                    ");

                $ins_receipt->bindParam(
                    ':receipt_uuid',
                    $id_data,
                    PDO::PARAM_STR
                );
                $ins_receipt->bindParam(':event_id', $event_id, PDO::PARAM_STR);
                $ins_receipt->bindParam(
                    ':timestamp_ev',
                    $timestamp,
                    PDO::PARAM_INT
                );
                $ins_receipt->bindParam(':user_id', $userId, PDO::PARAM_STR);
                $ins_receipt->bindParam(':event_type', $type, PDO::PARAM_STR);
                $ins_receipt->bindParam(':deviceId', $deviceId, PDO::PARAM_STR);
                $ins_receipt->bindParam(':storeId', $storeId, PDO::PARAM_STR);
                $ins_receipt->bindParam(':dateTime', $dateTime, PDO::PARAM_STR);
                $ins_receipt->bindParam(
                    ':receipt_type',
                    $type_data,
                    PDO::PARAM_STR
                );
                $ins_receipt->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                $ins_receipt->execute();

                $receipt_id = $uFunc->pdo("uCat")->LastInsertId();
            } catch (PDOException $e) {
                $uFunc->error('30' /*.$e->getMessage()*/);
            }

            foreach ($items as $key => $value) {
                $id_item = $value["id"];
                $name_item = $value["name"];
                $itemType_item = $value["itemType"];
                $measureName_item = $value["measureName"];
                $quantity_item = $value["quantity"];
                $price_item = $value["price"];
                $costPrice_item = $value["costPrice"];
                $sumPrice_item = $value["sumPrice"];
                $tax_item = $value["tax"];
                $taxPercent_item = $value["taxPercent"];
                $discount_item = $value["discount"];

                try {
                    $stm = $uFunc->pdo("uCat")->prepare("SELECT
                        evotor_uuid
                        FROM
                        u235_items
                        WHERE
                        evotor_uuid=:evotor_uuid
                        LIMIT 1
                        ");
                    $stm->bindParam(':evotor_uuid', $id_item, PDO::PARAM_STR);
                    $stm->execute();

                    if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $item_uuid = $row["evotor_uuid"];
                        $step_two = false;
                        $item_create = false;
                    } else {
                        $item_create = true;
                        $step_two = true;
                    }
                } catch (PDOException $e) {
                    $uFunc->error('40' /*.$e->getMessage()*/);
                }

                if ($step_two) {
                    try {
                        $stm = $uFunc->pdo("uCat")->prepare("SELECT
                            uuid_variant
                            FROM
                            items_variants
                            WHERE
                            uuid_variant=:evotor_uuid
                            LIMIT 1
                            ");
                        $stm->bindParam(
                            ':evotor_uuid',
                            $id_item,
                            PDO::PARAM_STR
                        );
                        $stm->execute();

                        if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                            $item_uuid = $row["uuid_variant"];
                            $item_create = false;
                        } else {
                            $item_create = true;
                        }
                    } catch (PDOException $e) {
                        $uFunc->error('50' /*.$e->getMessage()*/);
                    }
                }

                if ($item_create) {
                    $shtrihkoditem = [];
                    $quantity_tmp = 0;
                    $tovardataitem = [
                        'uuid' => $id_item,
                        'name' => $name_item,
                        'group' => false,
                        'parentUuid' => null,
                        'hasVariants' => false,
                        'type' => $itemType_item,
                        'quantity' => $quantity_tmp,
                        'measureName' => $measureName_item,
                        'tax' => $tax_item,
                        'price' => (float) $price_item,
                        'allowToSell' => true,
                        'costPrice' => (float) $costPrice_item,
                        'description' => 'Товар с пометкой',
                        'articleNumber' => '',
                        'code' => '',
                        'barCodes' => $shtrihkoditem,
                    ];
                    $item_id = $api_data->upload_items_in_internet_market(
                        $tovardataitem,
                        $site_id,
                        0
                    );
                    $item_uuid = $id_item;
                }

                try {
                    $insert_receipt_item = $uFunc->pdo("uCat")
                        ->prepare("INSERT INTO
                        receipts_items
                        SET
                        receipt_id=:receipt_id,
                        item_uuid=:item_uuid,
                        item_quantity=:item_quantity,
                        site_id=:site_id
                        ");

                    $insert_receipt_item->bindParam(
                        ':site_id',
                        $site_id,
                        PDO::PARAM_INT
                    );
                    $insert_receipt_item->bindParam(
                        ':receipt_id',
                        $receipt_id,
                        PDO::PARAM_STR
                    );
                    $insert_receipt_item->bindParam(
                        ':item_uuid',
                        $item_uuid,
                        PDO::PARAM_STR
                    );
                    $insert_receipt_item->bindParam(
                        ':item_quantity',
                        $quantity_item,
                        PDO::PARAM_STR
                    );
                    $insert_receipt_item->execute();
                } catch (PDOException $e) {
                    $uFunc->error('60' /*.$e->getMessage()*/);
                }

                try {
                    $stm = $uFunc->pdo("uCat")->prepare("SELECT
                        quantity
                        FROM
                        u235_items
                        WHERE
                        evotor_uuid=:evotor_uuid AND
                        site_id=:site_id
                        LIMIT 1
                        ");
                    $stm->bindParam(':evotor_uuid', $id_item, PDO::PARAM_STR);
                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();

                    if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $quantity = $row["quantity"];
                        $step_two = false;
                    } else {
                        $step_two = true;
                    }
                } catch (PDOException $e) {
                    $uFunc->error('70' /*.$e->getMessage()*/);
                }

                if ($step_two) {
                    try {
                        $stm = $uFunc->pdo("uCat")->prepare("SELECT
                            var_quantity
                            FROM
                            items_variants
                            WHERE
                            uuid_variant=:evotor_uuid AND
                            site_id=:site_id
                            LIMIT 1
                            ");
                        $stm->bindParam(
                            ':evotor_uuid',
                            $id_item,
                            PDO::PARAM_STR
                        );
                        $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                        $stm->execute();

                        if ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                            $quantity = $row["var_quantity"];
                        }
                    } catch (PDOException $e) {
                        $uFunc->error('80' /*.$e->getMessage()*/);
                    }
                }

                if (isset($quantity)) {
                    if ($type_data == "SELL") {
                        $quantity_new =
                            (float) $quantity - (float) $quantity_item;
                    } elseif ($type_data == "PAYBACK") {
                        $quantity_new =
                            (float) $quantity + (float) $quantity_item;
                    } else {
                        $quantity_new = (float) $quantity;
                    }

                    try {
                        $search_var_item = $uFunc->pdo("uCat")->prepare("SELECT
                            default_var
                            FROM
                            items_variants
                            WHERE
                            uuid_variant=:evotor_uuid AND
                            site_id=:site_id
                            ");

                        $search_var_item->bindParam(
                            ':site_id',
                            $site_id,
                            PDO::PARAM_INT
                        );
                        $search_var_item->bindParam(
                            ':evotor_uuid',
                            $id_item,
                            PDO::PARAM_STR
                        );
                        $search_var_item->execute();

                        if (
                            $result = $search_var_item->fetch(PDO::FETCH_ASSOC)
                        ) {
                            $flag_variants = true;
                            $default_var = (bool) $result["default_var"];
                        } else {
                            $flag_variants = false;
                        }
                    } catch (PDOException $e) {
                        $uFunc->error('90' /*.$e->getMessage()*/);
                    }

                    if ($flag_variants) {
                        // Поменять quantity в обоих таблицах, если default_var=1
                        try {
                            $upd_var = $uFunc->pdo("uCat")->prepare("UPDATE
                                items_variants
                                SET
                                var_quantity=:quantity
                                WHERE
                                site_id=:site_id AND
                                uuid_variant=:evotor_uuid
                                ");

                            $upd_var->bindParam(
                                ':quantity',
                                $quantity_new,
                                PDO::PARAM_STR
                            );
                            $upd_var->bindParam(
                                ':evotor_uuid',
                                $id_item,
                                PDO::PARAM_STR
                            );
                            $upd_var->bindParam(
                                ':site_id',
                                $site_id,
                                PDO::PARAM_INT
                            );
                            $upd_var->execute();
                        } catch (PDOException $e) {
                            $uFunc->error('100' /*.$e->getMessage()*/);
                        }

                        if ($default_var) {
                            try {
                                $upd_item = $uFunc->pdo("uCat")->prepare("UPDATE
                                    u235_items
                                    SET
                                    quantity=:quantity
                                    WHERE
                                    evotor_uuid=:evotor_uuid AND
                                    site_id=:site_id
                                    ");

                                $upd_item->bindParam(
                                    ':site_id',
                                    $site_id,
                                    PDO::PARAM_INT
                                );
                                $upd_item->bindParam(
                                    ':quantity',
                                    $quantity_new,
                                    PDO::PARAM_STR
                                );
                                $upd_item->bindParam(
                                    ':evotor_uuid',
                                    $id_item,
                                    PDO::PARAM_STR
                                );
                                $upd_item->execute();
                            } catch (PDOException $e) {
                                $uFunc->error('110' /*.$e->getMessage()*/);
                            }
                        }
                    } else {
                        // Поменять quantity только в таблице u235_items
                        try {
                            $upd_item = $uFunc->pdo("uCat")->prepare("UPDATE
                                u235_items
                                SET
                                quantity=:quantity
                                WHERE
                                evotor_uuid=:evotor_uuid AND
                                site_id=:site_id
                                ");

                            $upd_item->bindParam(
                                ':site_id',
                                $site_id,
                                PDO::PARAM_INT
                            );
                            $upd_item->bindParam(
                                ':quantity',
                                $quantity_new,
                                PDO::PARAM_STR
                            );
                            $upd_item->bindParam(
                                ':evotor_uuid',
                                $id_item,
                                PDO::PARAM_STR
                            );
                            $upd_item->execute();
                        } catch (PDOException $e) {
                            $uFunc->error('120' /*.$e->getMessage()*/);
                        }
                    }
                }
            }
            http_response_code(200);
        } else {
            http_response_code(404);
            exit();
        }
    } else {
        http_response_code(401);
        $errors = [
            "code" => 1003,
        ];
        $tmp = [];
        $tmp[] = $errors;
        $response_array = [
            "errors" => $tmp,
        ];
        echo json_encode($response_array, true);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
