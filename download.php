<?php

require_once(dirname(__FILE__) . "/include/init.php");

$request = new LocalObject($_GET);
$path = $request->GetProperty("Path");

//currently allows to download only files of modules inside /webiste/WEBSITE_FOLDER/var/
$module = new Module();
$moduleList = $module->GetModuleList();
//add config folder
$moduleList[]["Folder"] = "config";

$isPathValid = false;
foreach ($moduleList as $m) {
    $pattern = "^" . preg_quote("website/" . WEBSITE_FOLDER . "/var/" . $m["Folder"] . "/", "/");
    $patternSecurity = "^(?!.*\/payroll\/|.*\/invoice\/|.*\/agreements\/).*$";
    if (preg_match("/" . $pattern . "/", $path) && preg_match("/" . $patternSecurity . "/", $path)) {
        $isPathValid = true;
        break;
    }
}

if ($isPathValid) {
    $fileStorage = GetFileStorage($request->GetProperty("container"));

    $path = PROJECT_DIR . $path;
    $chunks = explode("/", $path);
    $filename = end($chunks);

    $content = $fileStorage->GetFileContent($path);
    if ($content !== false) {
        header("Cache-Control: max-age=0");
        header('Content-Transfer-Encoding: binary');
        if (preg_match('/[.](xlsx)$/', $filename)) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        } elseif (preg_match('/[.](pdf)$/', $filename)) {
            header("Content-Type: application/pdf");
        }

        if ($request->GetProperty("disposition") == "inline") {
            header("Content-Disposition: inline;filename=\"" . $filename . "\"");
        } else {
            header("Content-Disposition: attachment;filename=\"" . $filename . "\"");
        }
        echo $content;
    } else {
        Send404();
    }
} else {
    Send403();
}
