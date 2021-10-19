<?php

/**
 * Reboot worker if it is not running
 */

set_time_limit(60 * 60);

define("IS_ADMIN", true);

require_once dirname(__FILE__) . "/../../include/init.php";

$cronLog = "Started check workers</br>";
$type = "check_workers";
$outputCron = false;
$errorList = "";

function GetEnvCount($output, $path): int
{
    $count = 0;
    foreach ($output as $script) {
        if (strpos($script, $path) === false) {
            continue;
        }
        $count++;
    }

    return $count;
}

/*
["name" => "send_mail", "path" => "admin/workers/send_mail.php"],
["name" => "error_handler", "path" => "admin/workers/error_handler.php"],
["name" => "line_recognize", "path" => "module/receipt/workers/line_recognize.php"],
["name" => "signature_create", "path" => "module/receipt/workers/signature_create.php"],
["name" => "signature_verify", "path" => "module/receipt/workers/signature_verify.php"],
["name" => "check_limits", "path" => "module/receipt/workers/check_limits.php"]
*/

$queueList = [
    ["name" => "send_mail", "path" => "admin/workers/send_mail.php"],
    ["name" => "line_recognize", "path" => "module/receipt/workers/line_recognize.php"],
    ["name" => "signature_create", "path" => "module/receipt/workers/signature_create.php"],
    ["name" => "signature_verify", "path" => "module/receipt/workers/signature_verify.php"],
];

foreach ($queueList as $queue) {
    $path = "";
    if (IsReleaseEnvironment()) {
        $path = "/var/www/clients/client1/service.trebono.de/web/" . $queue["path"];
    } elseif (IsDemoEnvironment()) {
        $path = "/var/www/clients/client1/demo.trebono.de/web/" . $queue["path"];
    } elseif (IsTestEnvironment()) {
        $path = "/var/www/clients/client1/test.trebono.de/web/" . $queue["path"];
    }

    $output = [];
    exec("ps ax | grep " . $queue["name"] . ".php | grep -v grep", $output);
    //if it's fallback server, script needs to be run for all environments
    $checkAllEnv = GetFromConfig("ForceNotLocal", "env") == 1 && count($output) < 3;
    if (!empty($output) && !$checkAllEnv) {
        continue;
    }

    if (!empty(getenv("APP_ENV"))) {
        exec("php /srv/app/" . $queue["path"] . " > /dev/null &");
    } elseif (!empty(getenv("VCAP_APPLICATION"))) {
        exec("php /app/htdocs/" . $queue["path"] . " > /dev/null &");
    } elseif ($checkAllEnv) {
        if (empty($output)) {
            $output[] = "";
        }
        $count = GetEnvCount($output, $path);

        if ($count != 0) {
            continue;
        }
        exec("php " . $path . " > /dev/null &");
    }

    sleep(10);
    $output = [];
    exec("ps ax | grep " . $queue["name"] . ".php | grep -v grep", $output);
    if (empty($output)) {
        $result = "failed";
        $errorList .= "Queue " . $queue["name"] . " failed to start";
    } else {
        if (!empty(getenv("APP_ENV")) || !empty(getenv("VCAP_APPLICATION"))) {
            $result = "succeded";
        } else {
            $count = GetEnvCount($output, $path);
            if ($count == 0) {
                $result = "failed";
                $errorList .= "Queue " . $queue["name"] . " failed to start";
            } else {
                $result = "succeded";
            }
        }
    }
    $cronLog .= "Found " . $queue["name"] . " queue stopped. Restart " . $result . "</br>";
    $outputCron = true;
}

if ($outputCron && $cronLog != "Started check workers</br>") {
    Operation::SaveCron(null, $cronLog, $type, $errorList, null, true);
}
