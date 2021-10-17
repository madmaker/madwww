<?php
require_once "processors/classes/uFunc.php";
$uFunc = new \processors\uFunc($this);

header('Accept: application/json');
header('Content-Type: application/json');
$conn_token = "41e035ac-00f9-4deb-932f-f7871965de7b";
$authorization_header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
$authorization_header = str_replace("Bearer", '', $authorization_header);
$authorization_header = trim($authorization_header);

if ($this->url_prop[2] === "verify" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($authorization_header === $conn_token) {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata, true);
        $userUuid = $postdata["userUuid"];
        $activation_code = $postdata["activation"];
        $userId = $postdata["userId"];

        try {
            $stm = $uFunc->pdo("pages")->prepare("SELECT
            value,
            site_id
            FROM
            u235_conf
            WHERE 
            field='activation_token'
            ");

            $stm->execute();

            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($activation_code == $row["value"]) {
                    $site_id = $row["site_id"];
                    $uFunc->setConf(
                        $userId,
                        "evotor_user_id",
                        "uCat",
                        PDO::PARAM_STR,
                        $site_id
                    );
                }
            }

            if (isset($site_id)) {
                try {
                    $stm = $uFunc->pdo("pages")->prepare("SELECT
                    value
                    FROM
                    u235_conf
                    WHERE 
                    field='site_uuid' AND 
                    site_id=:site_id
                    ");

                    $stm->bindParam(':site_id', $site_id, PDO::PARAM_INT);
                    $stm->execute();

                    while ($rows = $stm->fetch(PDO::FETCH_ASSOC)) {
                        $usertoken = $rows["value"];
                    }
                } catch (PDOException $e) {
                    $uFunc->error('10' /*.$e->getMessage()*/);
                }

                if (isset($usertoken)) {
                    $response = [
                        'userId' => $userId,
                        'hasBilling' => false,
                        'token' => $usertoken,
                    ];
                    http_response_code(200);
                    echo json_encode($response, true);
                }
            }
        } catch (PDOException $e) {
            $uFunc->error('20' /*.$e->getMessage()*/);
        }
    } else {
        http_response_code(401);
        $errors = [
            "code" => 1001,
        ];
        $tmp = [];
        $tmp[] = $errors;
        $response_array = [
            "errors" => $tmp,
        ];
        echo json_encode($response_array, true);
    }
} elseif (
    $this->url_prop[2] === "create" &&
    $_SERVER['REQUEST_METHOD'] == 'POST'
) {
    // Функция не доделана, реализован краткий пример
    if ($authorization_header === $conn_token) {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata, true);

        $userId = $postdata["userId"];
        $activation_code = $postdata["activation"];

        $token = "41e035ac-00f9-4deb-932f-f7871965de7b";

        $response = [
            'userId' => $userId,
            'token' => $token,
        ];
        http_response_code(200);
        echo json_encode($response, true);
    } else {
        http_response_code(401);
        $errors = [
            "code" => 1001,
        ];
        $tmp = [];
        $tmp[] = $errors;
        $response_array = [
            "errors" => $tmp,
        ];
        echo json_encode($response_array, true);
    }
} elseif (
    $this->url_prop[2] === "token" &&
    $_SERVER['REQUEST_METHOD'] == 'POST'
) {
    if ($authorization_header === $conn_token) {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata, true);

        $userId = $postdata["userId"];
        $token = $postdata["token"];

        try {
            $stm = $uFunc->pdo("pages")->prepare("SELECT
            value,
            site_id
            FROM
            u235_conf
            WHERE 
            field='evotor_user_id'
            ");

            $stm->execute();

            while ($row = $stm->fetch(PDO::FETCH_ASSOC)) {
                if ($userId == $row["value"]) {
                    $site_id = $row["site_id"];
                    $uFunc->setConf(
                        $token,
                        "evotor_token",
                        "uCat",
                        PDO::PARAM_STR,
                        $site_id
                    );
                    http_response_code(200);
                }
            }
        } catch (PDOException $e) {
            $uFunc->error('30' /*.$e->getMessage()*/);
        }
    } else {
        http_response_code(401);
        $errors = [
            "code" => 1001,
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
