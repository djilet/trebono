<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
    /**
     * Send message to rabbit mq queue
     *
     * @param string $queue queue
     * @param string $message message
     */
    public static function Send($queue, $message)
    {
        $credentials = self::GetCredentials();

        try {
            $connection = new AMQPStreamConnection(
                $credentials["Host"],
                $credentials["Port"],
                $credentials["User"],
                $credentials["Password"],
                $credentials["Vhost"]
            );

            $channel = $connection->channel();
            $channel->queue_declare($queue, false, true, false, false);

            $message = json_encode($message);

            $msg = new AMQPMessage($message, array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));
            $channel->basic_publish($msg, '', $queue);

            $channel->close();
            $connection->close();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns credentials for rabbit mq service from eviroment variables if exist or from config
     */
    public static function GetCredentials()
    {
        $credentials = array(

            "Host" => "",
            "Port" => "",
            "User" => "",
            "Password" => "",
            "Vhost" => ""

        );

        if ($services = getenv("VCAP_SERVICES")) {
            $services = json_decode($services, true);
            if (isset($services["osb-rabbitmq"])) {
                foreach ($services["osb-rabbitmq"] as $pg) {
                    $params = parse_url($pg["credentials"]["uri"]);
                    $credentials["Host"] = $params["host"];
                    $credentials["User"] = $pg["credentials"]["user"];
                    $credentials["Password"] = $pg["credentials"]["password"];
                    $credentials["Port"] = $pg["credentials"]["port"];
                    $credentials["Vhost"] = $pg["credentials"]["vhost"];
                }
            }
        } else {
            $credentials["Host"] = GetFromConfig("Host", "rabbitmq");
            $credentials["User"] = GetFromConfig("User", "rabbitmq");
            $credentials["Password"] = GetFromConfig("Password", "rabbitmq");
            $credentials["Port"] = GetFromConfig("Port", "rabbitmq");
            if (IsTestEnvironment()) {
                $credentials["Vhost"] = GetFromConfig("Vhost", "rabbitmq") . "_test";
            } elseif (IsDemoEnvironment()) {
                $credentials["Vhost"] = GetFromConfig("Vhost", "rabbitmq") . "_demo";
            } elseif (IsReleaseEnvironment()) {
                $credentials["Vhost"] = GetFromConfig("Vhost", "rabbitmq") . "_service";
            } else {
                $credentials["Vhost"] = GetFromConfig("Vhost", "rabbitmq");
            }
        }

        return $credentials;
    }

    public static function GetQueueCount($queue)
    {
        try {
            $credentials = self::GetCredentials();
            $connection = new AMQPStreamConnection(
                $credentials["Host"],
                $credentials["Port"],
                $credentials["User"],
                $credentials["Password"],
                $credentials["Vhost"]
            );

            $channel = $connection->channel();
            $declare = $channel->queue_declare($queue, true, true, false, false);

            return $declare[1] ?? false;
        } catch (Exception $e) {
            return false;
        }
    }
}
