<?php

error_reporting(E_ALL);
require_once(dirname(__FILE__) . "/../../../include/init.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;

$credentials = RabbitMQ::GetCredentials();
$connection = new AMQPStreamConnection(
    $credentials["Host"],
    $credentials["Port"],
    $credentials["User"],
    $credentials["Password"],
    $credentials["Vhost"],
    $insist = false,
    $login_method = 'AMQPLAIN',
    $login_response = null,
    $locale = 'en_US',
    $connection_timeout = 300,
    $read_write_timeout = 300,
    $context = null,
    $keepalive = true,
    $heartbeat = 120
);

$channel = $connection->channel();
$channel->queue_declare('signature_verify', false, true, false, false);


$callback = static function ($msg) {

    $receiptFile = new ReceiptFile("receipt");
    $message = json_decode($msg->body, true);

    if (isset($message["die"])) {
        die();
    }

    $receiptFile->LoadByID($message["receipt_file_id"]);

    if ($status = Mentana::Verify($message["receipt_file_id"])) {
        $receiptFile->SetSignatureReportFile($receiptFile->GetProperty("file_image") . ".xml");
        $receiptFile->SetSignatureStatus($receiptFile->GetProperty("receipt_file_id"), "signature_verify_" . $status);
    } else {
        $receiptFile->SetSignatureStatus($receiptFile->GetProperty("receipt_file_id"), "signature_verify_error");
    }

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

    //print_r("ok");
    //die();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('signature_verify', '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
