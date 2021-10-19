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

$urlFilter = new URLFilter();
require_once(dirname(__FILE__) . "/controller/partner.php");

if (isset($content)) {
    $content->SetLoop("Navigation", $navigation);
    $content->SetVar("ParamsForURL", $urlFilter->GetForURL());
    $content->SetVar("ParamsForForm", $urlFilter->GetForForm());
    $adminPage->Output($content);
}
