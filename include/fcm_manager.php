<?php

class FCMManager
{
    private const SERVER_KEY = "AAAAsPtKG28:APA91bFCVj1JhfgU2_-Ld7seYoa1uaeiHr10ca_98KO-6MtINERYUCnSgiyPqXeLBrQucJ-wBtaJ-tOpp4DtceDNvmCO4cWC_f_cEotKdqJBolkL3w9WmLianc88VtCZpE2dL1qJVGzf";
    private const DEBUG = 0;
    public const DEEPLINK_KEY = "deeplink";
    public const DEEPLINK_PREFIX = "lst://app/";

    /**
     * Send push notification to device owning passed token
     *
     * @param string $client platform of device owning passed token - "android" or "ios"
     * @param string $token push token of receiver device
     * @param string $title message header
     * @param string $text message text
     * @param array $data additional params to be passed to mobile application
     *
     * @return bool true if at least one of the messages was sent successfully or false otherwise
     */
    public static function Send($client, $token, $title, $text, $data = array(), $userID = 0, $deviceMap = array())
    {
        if (Config::GetConfigValue("log_push_notification") == "N") {
            return true;
        }

        if (IsLocalEnvironment()) {
            return true;
        }

        if (IsDemoEnvironment()) {
            $text = "DEMO " . $text;
        }

        $result = false;
        $isSendedMap = array();
        $errorMap = array();
        $errorMessage = "";

        $requestHeaders = array(
            "Authorization: key=" . self::SERVER_KEY,
            "Content-Type: application/json"
        );

        $requestBody = array(
            "priority" => "high",
            "mutable_content" => true,
            "content_available" => true,
            "notification" => array(
                "title" => $title,
                "body" => $text,
                "sound" => "default"
            ),
            "data" => !empty($data) ? $data : new stdClass()
        );

        if (!is_array($token)) {
            $token = array($token);
        }

        $requestBody["registration_ids"] = $token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/fcm/send");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestBody));
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response !== false) {
            $responseObject = json_decode($response);
            if ($responseObject !== null) {
                if (Config::GetConfigValue("log_push_notification") == "Y") {
                    foreach ($responseObject->results as $key => $result) {
                        if (isset($result->message_id)) {
                            $isSendedMap[$token[$key]] = true;
                        } else {
                            $isSendedMap[$token[$key]] = false;
                            $errorMap[$token[$key]] = "Push notification sending error: " . $result->error;
                        }
                    }
                }

                if ($responseObject->failure > 0) {
                    if (self::DEBUG) {
                        ErrorHandler::TriggerError("Push notification sending error: " . implode(
                            ", ",
                            array_column($responseObject->results, "error")
                        ), E_USER_WARNING);
                    }
                }
                $result = $responseObject->success > 0;
            } else {
                $errorMessage = "Push notification sending error: " . $response;

                if (self::DEBUG) {
                    $response = strip_tags($response);
                    $response = preg_replace("/[\r\n]+/", "\n", $response);
                    ErrorHandler::TriggerError("Push notification sending error: " . $response, E_USER_WARNING);
                }
            }
        } else {
            $errorMessage = "Push notification sending error: " . $curlError;

            if (self::DEBUG) {
                ErrorHandler::TriggerError("Push notification sending error: " . $curlError, E_USER_WARNING);
            }
        }

        if (Config::GetConfigValue("log_push_notification") == "Y") {
            foreach ($token as $key => $value) {
                $isSended = $isSendedMap != array() ? $isSendedMap[$value] : false;

                $errorMessage = $errorMap != array() && isset($errorMap[$value]) ? $errorMap[$value] : "";

                Push::Save($userID, $isSended, $text, $deviceMap[$value], $errorMessage);
            }
        }

        return $result;
    }
}
