<?php

error_reporting(E_ALL);
echo "signature_create echo";
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
$channel->queue_declare('signature_create', false, true, false, false);

$callback = static function ($msg) {

    $receiptFile = new ReceiptFile("receipt");
    $message = json_decode($msg->body, true);

    if (isset($message["die"])) {
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        die();
    }

    $receiptFile->LoadByID($message["receipt_file_id"]);

    if (Mentana::Sign($message["receipt_file_id"])) {
        $receiptFile->SetSignatureFile($receiptFile->GetProperty("file_image") . ".p7s");
        $receiptFile->SetSignatureStatus($receiptFile->GetProperty("receipt_file_id"), "signature_created");

        if ($message["verify"]) {
            if (
                RabbitMQ::Send(
                    "verify_signature",
                    array("receipt_file_id" => $receiptFile->GetProperty("receipt_file_id"))
                )
            ) {
                ReceiptFile::SetSignatureStatus(
                    $receiptFile->GetProperty("receipt_file_id"),
                    "signature_verify_started"
                );
                ReceiptFile::WriteLog($receiptFile->GetProperty("receipt_file_id"), "--------", "info");
                ReceiptFile::WriteLog(
                    $receiptFile->GetProperty("receipt_file_id"),
                    "add rabbit mq task on verify",
                    "info"
                );
            } else {
                ReceiptFile::WriteLog($receiptFile->GetProperty("receipt_file_id"), "--------", "error");
                ReceiptFile::WriteLog(
                    $receiptFile->GetProperty("receipt_file_id"),
                    "add rabbit mq task on verify error",
                    "error"
                );
            }
        }
    } else {
        $receiptFile->SetSignatureStatus($receiptFile->GetProperty("receipt_file_id"), "signature_create_error");
        ReceiptFile::WriteLog($receiptFile->GetProperty("receipt_file_id"), "signature create error", "error");
    }

    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

    //print_r("ok");
    //die();
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('signature_create', '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
