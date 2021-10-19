<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess();

$request = new LocalObject(array_merge($_GET, $_POST));

$adminPage = new AdminPage();
$navigation = [
    [
        "Title" => "under construction",
        "Link" => "underconstruction.php?p=" . $request->GetProperty("p"),
    ],
];

$header = [
    "Navigation" => $navigation,
    "Title" => "under construction",
];
$content = $adminPage->Load("underconstruction.html", $header);

$adminPage->Output($content);
