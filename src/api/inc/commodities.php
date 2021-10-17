<?php
if (
    $this->url_prop[1] === "commodities" &&
    $_SERVER['REQUEST_METHOD'] == 'GET'
) {
    echo "Поиск по штрихкоду"; // GET
    // Данная функция пока не используется
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
