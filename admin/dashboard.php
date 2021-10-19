<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

if (!$request->GetProperty("Filter")) {
    $request->SetProperty("DateFrom", date("Y-m-01"));
    $request->SetProperty("DateTo", date("Y-m-d"));
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, ["DateFrom", "DateTo"]);

$adminPage = new AdminPage();

$dashboard = new Dashboard();

$navigation = [
    ["Title" => GetTranslation("title-dashboard"), "Link" => "dashboard.php"],
];
$header = [
    "Title" => GetTranslation("title-dashboard"),
    "Navigation" => $navigation,
    "StyleSheets" => [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/graph.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/detail.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/legend.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/extensions.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/lines.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/css/rickshaw.min.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/morris-chart/css/morris.css"],
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/jquery-ui/smoothness/jquery-ui.min.css"],

    ],
    "JavaScripts" => [
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/vendor/d3.v3.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/js/Rickshaw.All.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/morris-chart/js/morris.min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/morris-chart/js/raphael-min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-ui/smoothness/jquery-ui.min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
    ],
];
$content = $adminPage->Load("dashboard.html", $header);

$link = "dashboard.php";
Operation::Save($link, "dashboard", "dashboard");

$dashboard->LoadDashboard($request);
$content->LoadFromObject($dashboard);
$content->LoadFromObject($request);

$content->SetVar("ParamsForFilter", $urlFilter->GetForForm());

$adminPage->Output($content);
