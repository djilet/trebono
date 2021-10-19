<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

$adminPage = new AdminPage();

$sectionList = ["push", "email", "import"];

if (!$request->GetProperty("Section")) {
    $request->SetProperty("Section", $sectionList[0]);
}

$templateSectionList = [];
foreach ($sectionList as $section) {
    $sectionTitle = GetTranslation("section-" . $section);
    $templateSectionList[] = [
        "Section" => $section,
        "Title" => $sectionTitle,
        "Selected" => ($request->GetProperty("Section") == $section ? 1 : 0),
    ];
}

$urlFilter = new URLFilter();

$urlString = $urlFilter->GetForURL();

$navigation = [
    ["Title" => GetTranslation("title-push-log"), "Link" => "push_history.php"],
];
$header = [
    "Title" => GetTranslation("title-push-log"),
    "Navigation" => $navigation,
    "StyleSheets" => [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
        ['StyleSheetFile' => ADMIN_PATH . 'template/plugins/typeahead/css/typeahead.css'],
    ],
    "JavaScripts" => [
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
        ['JavaScriptFile' => ADMIN_PATH . 'template/plugins/typeahead/handlebars.min.js'],
        ['JavaScriptFile' => ADMIN_PATH . 'template/plugins/typeahead/typeahead.bundle.js'],
    ],
];

$content = $adminPage->Load("push_history.html", $header);

$filterParams = ["FilterDateRange", "FilterName", "FilterCompanyUnitTitle"];

$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet($key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty($key));
    } else {
        $session->SetProperty($key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

if (!$request->GetProperty("FilterDateRange")) {
    $request->SetProperty("FilterDateRange", date("m/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
}

$content->LoadFromObject($request, $filterParams);

if ($request->GetProperty("Section") == "push") {
    $pushList = new PushList();

    $urlFilter->LoadFromObject($request, [$pushList->GetPageParam(), $pushList->GetOrderByParam()]);
    $urlString = $urlFilter->GetForURL();

    $pushList->LoadPushList($request);
    $content->LoadFromObjectList("PushList", $pushList);

    $pagingULRString = $urlFilter->GetForURL([$pushList->GetPageParam()]);

    $urlPush = "push_history.php?Section=push&FilterDateRange=" . $request->GetProperty("FilterDateRange") . ($pagingULRString ? "&" . $pagingULRString : "");
    $content->SetVar("Paging", $pushList->GetPagingAsHTML($urlPush));

    $content->SetVar("ListInfo", GetTranslation(
        "list-info1",
        ["Page" => $pushList->GetItemsRange(), "Total" => $pushList->GetCountTotalItems()]
    ));
} elseif ($request->GetProperty("Section") == "email") {
    $emailList = new EmailList();

    $urlFilter->LoadFromObject($request, [$emailList->GetPageParam(), $emailList->GetOrderByParam()]);
    $urlString = $urlFilter->GetForURL();

    $emailList->LoadEmailList($request);
    $content->LoadFromObjectList("EmailList", $emailList);

    $pagingULRString = $urlFilter->GetForURL([$emailList->GetPageParam()]);

    $urlEmail = "push_history.php?Section=email&FilterDateRange=" . $request->GetProperty("FilterDateRange") . ($pagingULRString ? "&" . $pagingULRString : "");
    $content->SetVar("Paging", $emailList->GetPagingAsHTML($urlEmail));

    $content->SetVar("EmailListInfo", GetTranslation(
        "list-info1",
        ["Page" => $emailList->GetItemsRange(), "Total" => $emailList->GetCountTotalItems()]
    ));

    $emailStatusInfo = [
        "QueueCount" => RabbitMQ::GetQueueCount("send_mail"),
        "SendedLastHour" => EmailList::GetLastHourCountEmail(),
        "DoesStopped" => Config::GetConfigValue("send_mail_stop"),
        "Limit" => Config::GetConfigValue("send_mail_hour_limit"),
    ];
    $content->LoadFromArray($emailStatusInfo);
} elseif ($request->GetProperty("Section") == "import") {
    $companyUnitImportList = new CompanyUnitImportList();

    $urlFilter->LoadFromObject(
        $request,
        [$companyUnitImportList->GetPageParam(), $companyUnitImportList->GetOrderByParam()]
    );
    $urlString = $urlFilter->GetForURL();

    $companyUnitImportList->LoadCompanyUnitImportList($request);
    $content->LoadFromObjectList("ImportList", $companyUnitImportList);

    $pagingULRString = $urlFilter->GetForURL([$companyUnitImportList->GetPageParam()]);

    $urlEmail = "push_history.php?Section=import&FilterDateRange=" . $request->GetProperty("FilterDateRange") . ($pagingULRString ? "&" . $pagingULRString : "");
    $content->SetVar("Paging", $companyUnitImportList->GetPagingAsHTML($urlEmail));

    $content->SetVar("ImportListInfo", GetTranslation("list-info1", [
        "Page" => $companyUnitImportList->GetItemsRange(),
        "Total" => $companyUnitImportList->GetCountTotalItems(),
    ]));
}

if ($request->GetProperty("Section") != "import") {
    $companyUnitList = new CompanyUnitList("company");
    $companyUnitList->LoadCompanyUnitListForTree(
        null,
        "company_unit",
        false,
        false,
        false,
        $auth
    );
    $content->LoadFromObjectList('CompanyUnitList', $companyUnitList);
}

$link = "push_history.php";

Operation::Save($link, "push_history", "push_list");

$content->SetVar("Section", $request->GetProperty("Section"));
$content->SetLoop("PushSectionList", $templateSectionList);

$adminPage->Output($content);
