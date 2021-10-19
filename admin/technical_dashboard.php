<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

if ($request->GetProperty("Section") != "storage" && !$request->GetProperty("DateRange")) {
    $request->SetProperty("DateRange", date("m/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
}

if (!$request->GetProperty("TimeGroup")) {
    $request->SetProperty("TimeGroup", "hour");
}

$sectionList = ["receipt", "storage"];
if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", $sectionList[0]);
}

$templateSectionList = [];
foreach ($sectionList as $section) {
    $sectionTitle = GetTranslation("technical-dashboard-section-" . $section);
    $templateSectionList[] = [
        "Section" => $section,
        "Title" => $sectionTitle,
        "Selected" => ($request->GetProperty("Section") == $section ? 1 : 0),
    ];
}

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject($request, ["DateRange", "TimeGroup"]);

$adminPage = new AdminPage();

$dashboard = new Dashboard();

$navigation = [
    ["Title" => GetTranslation("title-dashboard"), "Link" => "technical_dashboard.php"],
];
$header = [
    "Title" => GetTranslation("title-dashboard"),
    "Navigation" => $navigation,
    "StyleSheets" => [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
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
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
    ],
];
$content = $adminPage->Load("technical_dashboard.html", $header);

$link = "technical_dashboard.php";
Operation::Save($link, "dashboard", "dashboard");

if ($request->GetProperty("Section") == "receipt") {
    $dashboard->LoadTechnicalDashboard($request);
    $content->LoadFromObject($dashboard);
    $content->LoadMessagesFromObject($dashboard);
    $content->LoadFromObject($request);
} elseif ($request->GetProperty("Section") == "storage") {
    $storage = new FileStorageSwift();
    $containerList = $storage->GetContainerListInfo();
    $content->SetLoop("ContainerList", $containerList);

    $databaseListInfo = [];
    $databaseList = [DB_CONTROL, DB_MAIN, DB_PERSONAL];

    foreach ($databaseList as $database) {
        $stmt = GetStatement($database);
        $credentials = GetDatabaseCredentials($database);
        $query = "SELECT pg_size_pretty( pg_database_size(
            " . Connection::GetSQLString($credentials["Database"]) . ") )";
        $databaseListInfo[] = [
            "db_name" => GetTranslation("database-" . $database),
            "db_size" => $stmt->FetchField($query),
        ];
    }
    $content->SetLoop("DatabaseList", $databaseListInfo);
}

$content->SetVar("ParamsForFilter", $urlFilter->GetForForm());
$content->SetVar("Section", $request->GetProperty("Section"));
$content->SetLoop("TechDashboardSectionList", $templateSectionList);

$adminPage->Output($content);
