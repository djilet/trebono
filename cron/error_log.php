<?php

require_once(dirname(__FILE__) . "/../include/init.php");

$fileStorage = GetFileStorage(CONTAINER__CORE);

$logPath = PROJECT_DIR . "var/log/error.log";
$data = $fileStorage->GetFileContent($logPath);

if (strlen($data) < 2097152) {
    return 0;
}

$num = 1;
while ($fileStorage->FileExists($logPath . $num)) {
    $num++;
}
for ($n = $num; $n > 1; $n--) {
    $fileStorage->CopyFile($logPath . ($n - 1), $logPath . $n);
}
$fileStorage->CopyFile($logPath, $logPath . "1");
$fileStorage->Remove($logPath);

return 0;
