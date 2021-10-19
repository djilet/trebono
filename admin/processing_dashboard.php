<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

if (!$request->GetProperty("Filter")) {
    $request->SetProperty("yearly_statistics_date", date("Y-01-01"));
    $date = new DateTime();
    $date->sub(new DateInterval("P31D"));
    $request->SetProperty("filter_statistics_range", $date->format("m/d/Y") . " - " . date("m/d/Y"));
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, ["Date"]);

$adminPage = new AdminPage();

$dashboard = new Dashboard();

$navigation = [
    ["Title" => GetTranslation("title-processing_dashboard"), "Link" => "processing_dashboard.php"],
];

$header = [
    "Title" => GetTranslation("title-processing_dashboard"),
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
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
    ],
    "JavaScripts" => [
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/vendor/d3.v3.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/rickshaw-chart/js/Rickshaw.All.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/morris-chart/js/morris.min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/morris-chart/js/raphael-min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-ui/smoothness/jquery-ui.min.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
    ],
];
$content = $adminPage->Load("processing_dashboard.html", $header);

$link = "processing_dashboard.php";
Operation::Save($link, "dashboard", "dashboard");

$request->SetProperty("LanguageCode", $content->GetVar("INTERFACE_LANGCODE"));

if ($request->GetProperty("action") == "show") {
    $dashboard->LoadProcessingDashboard($request);
    $content->LoadFromObject($dashboard);
}

$content->LoadFromObject($request);

$content->SetVar("ParamsForFilter", $urlFilter->GetForForm());

$adminPage->Output($content);
