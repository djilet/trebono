<?php
require_once(dirname(__FILE__) . "/include/init.php");

$fileStorage = GetFileStorage(CONTAINER__CORE);
$fileSys = new FileSys();

$tmpPath = PROJECT_DIR . "var/log/";//swift_versioning_test.file


$newFile = $fileSys->Upload("file", $tmpPath, true, null);
if ($newFile) {
    print_r($newFile);

    $result = $fileStorage->PutFileContent($tmpPath . $newFile["FileName"],
        $fileSys->GetFileContent($tmpPath . $newFile["FileName"]), false);
    var_dump($result);


    $fileSys->Remove($tmpPath . $newFile["FileName"]);
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Swift demo application</title>
</head>
<body>
<form action="" method="post" enctype="multipart/form-data">
    <center>
        <div style="margin-top:200px;">
            <input type="file" name="file"/>
        </div>
        <div style="margin-top:20px;">
            <button type="submit">Upload file</button>
        </div>
        <div style="margin-top:20px;color:#ff0000;">
            <?php
            if ($fileStorage->HasErrors()) {
                echo $fileStorage->GetErrorsAsString("<br />");
            }
            ?>
        </div>
    </center>
</form>
</body>
</html>