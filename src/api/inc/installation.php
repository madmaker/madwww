<?php
require_once "processors/classes/uFunc.php";
$uFunc = new \processors\uFunc($this);

header('Accept: application/json');
header('Content-Type: application/json');
if ($this->url_prop[2] === "event" && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn_token = "41e035ac-00f9-4deb-932f-f7871965de7b";
    $authorization_header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
    $authorization_header = str_replace("Bearer", '', $authorization_header);
    $authorization_header = trim($authorization_header);
    if ($authorization_header === $conn_token) {
        $postdata = file_get_contents("php://input");
        $postdata = json_decode($postdata, true);

        $id = $postdata["id"];
        $timestamp = $postdata["timestamp"];
        $type = $postdata["type"];
        $version = $postdata["version"];
        $data = $postdata["data"];

        if ($type === "ApplicationInstalled") {
            $productId = $data["productId"];
            $userId = $data["userId"];

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
                            1,
                            "used_activation_token",
                            "uCat",
                            PDO::PARAM_INT,
                            $site_id
                        );
                        http_response_code(200);
                    }
                }
            } catch (PDOException $e) {
                $uFunc->error('10' /*.$e->getMessage()*/);
            }
        } elseif ($type === "ApplicationUninstalled") {
            $productId = $data["productId"];
            $userId = $data["userId"];

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
                            0,
                            "used_activation_token",
                            "uCat",
                            PDO::PARAM_INT,
                            $site_id
                        );
                        http_response_code(200);
                    }
                }
            } catch (PDOException $e) {
                $uFunc->error('20' /*.$e->getMessage()*/);
            }
        }
    } else {
        http_response_code(401);
    }
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
