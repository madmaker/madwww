<?php
if ($this->url_prop[1] === "receipts") {
    require "inc/receipts.php";
} elseif ($this->url_prop[1] === "installation") {
    require "inc/installation.php";
} else {
    header('HTTP/1.1 404 Not Found');
    exit();
}
