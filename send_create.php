<?php
require_once(dirname(__FILE__) . "/include/init.php");

if (!isset($_GET["receipt_file_id"])) {
    $_GET["receipt_file_id"] = 1;
}

RabbitMQ::Send("signature_create", array("receipt_file_id" => $_GET["receipt_file_id"], "verify" => false));
print_r("ok");
?>