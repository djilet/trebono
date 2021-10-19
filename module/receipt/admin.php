<?php

if (!defined('IS_ADMIN')) {
    echo "Incorrect call to admin interface";
    exit();
}

require_once(dirname(__FILE__) . "/init.php");

$user = new User();

$module = $request->GetProperty("load");
$adminPage = new AdminPage($module);
$navigation = array();

if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", "receipt");
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, array("Section"));

require_once(dirname(__FILE__) . "/controller/" . $request->GetProperty("Section") . ".php");

if (isset($content)) {
    $content->SetLoop("Navigation", $navigation);
    $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
    $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
    $adminPage->Output($content);
}
