<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../include/init.php");

$auth = new User();
$auth->ValidateAccess(["root"]);

$request = new LocalObject(array_merge($_GET, $_POST));

if (!$request->GetProperty("FilterDateRange")) {
    $request->SetProperty("FilterDateRange", date("m/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
}

$adminPage = new AdminPage();

$operationList = new OperationList();

$urlFilter = new URLFilter();
$urlFilter->LoadFromObject(
    $request,
    [$operationList->GetPageParam(), $operationList->GetOrderByParam(), "SearchString"]
);
$filterParams = ["FilterDateRange", "FilterUser", "FilterSection", "ItemsOnPage", "FilterGroupBy"];

$navigation = [
    ["Title" => GetTranslation("title-logging"), "Link" => "logging.php"],
];
$header = [
    "Title" => GetTranslation("title-logging"),
    "Navigation" => $navigation,
    "StyleSheets" => [
        ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
    ],
    "JavaScripts" => [
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
        ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
    ],
];

$content = $adminPage->Load("logging.html", $header);

$link = "logging.php";
Operation::Save($link, "logging", "operation_list");

$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet($key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty($key));
    } else {
        $session->SetProperty($key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

$operationList->LoadOperationList($request);
$content->LoadFromObjectList("OperationList", $operationList);

$content->LoadFromObject($request, $filterParams);
$content->SetVar("ParamsForForm", $urlFilter->GetForForm());
$content->LoadFromObject($urlFilter);

$pagingULRString = $urlFilter->GetForURL([$operationList->GetPageParam()]);
$url = "logging.php?FilterDateRange=" . $request->GetProperty("FilterDateRange")
    . ($pagingULRString ? "&" . $pagingULRString : "");
$content->SetVar("Paging", $operationList->GetPagingAsHTML($url));

$content->SetVar("ListInfo", GetTranslation(
    "list-info1",
    ["Page" => $operationList->GetItemsRange(), "Total" => $operationList->GetCountTotalItems()]
));

$itemsOnPageList = [];
foreach ([10, 20, 50, 100] as $v) {
    $itemsOnPageList[] = ["Value" => $v, "Selected" => $v == $operationList->GetItemsOnPage() ? 1 : 0];
}
$content->SetLoop("ItemsOnPageList", $itemsOnPageList);

$adminPage->Output($content);
