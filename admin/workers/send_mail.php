<?php

error_reporting(E_ALL);
echo "send_mail echo";
require_once(dirname(__FILE__) . "/../../include/init.php");

use PhpAmqpLib\Connection\AMQPStreamConnection;

$credentials = RabbitMQ::GetCredentials();
$connection = new AMQPStreamConnection(
    $credentials["Host"],
    $credentials["Port"],
    $credentials["User"],
    $credentials["Password"],
    $credentials["Vhost"],
    $insist = false,
    $login_method = "AMQPLAIN",
    $login_response = null,
    $locale = "en_US",
    $connection_timeout = 300,
    $read_write_timeout = 300,
    $context = null,
    $keepalive = true,
    $heartbeat = 120
);

$channel = $connection->channel();
$channel->queue_declare("send_mail", false, true, false, false);

$callback = static function ($msg) {

    $message = json_decode($msg->body, true);

    ErrorHandler::TriggerError(
        "RabbitMQ send_mail task catched, to: " . $message["to"] . " subject: " . $message["subject"],
        E_USER_NOTICE
    );

    if (isset($message["die"])) {
        die;
    }

    if (
        Config::GetConfigValue("send_mail_stop") == "Y"
        || EmailList::GetLastHourCountEmail() >= Config::GetConfigValue("send_mail_hour_limit")
    ) {
        ErrorHandler::TriggerError(
            "RabbitMQ sleeped " . Config::GetConfigValue("send_mail_stop") . " 
            " . EmailList::GetLastHourCountEmail() . " " . Config::GetConfigValue("send_mail_hour_limit"),
            E_USER_NOTICE
        );
    }

    while (
        Config::GetConfigValue("send_mail_stop") == "Y"
        || EmailList::GetLastHourCountEmail() >= Config::GetConfigValue("send_mail_hour_limit")
    ) {
        sleep(10);
    }

    SendMailFromAdmin(
        $message["to"],
        $message["subject"],
        $message["text"],
        $message["attachments"],
        $message["embeddedImages"],
        $message["remoteAttachments"],
        $message["fromName"]
    );
    ErrorHandler::TriggerError("RabbitMQ send_mail task finisded", E_USER_NOTICE);
    $msg->delivery_info["channel"]->basic_ack($msg->delivery_info["delivery_tag"]);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume("send_mail", "", false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
