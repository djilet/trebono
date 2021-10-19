<?php

$user->LoadBySession();
if (!$user->Validate(array("root"))) {
    $user->ValidateAccess(array("bookkeeping_export" => null, "tax_auditor" => null), "or");
} else {
    $isAdmin = true;
}

if ($user->Validate(array("bookkeeping_export" => null))) {
    $isBookkeepingExport = true;
}

$navigation[] = array(
    "Title" => GetTranslation("section-bookkeeping-export", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("FilterCreatedRange", "FilterTitle", "ItemsOnPage");

$urlFilter->AppendFromObject($request, $filterParams);

if (!Payroll::ValidateAccess($request->GetProperty("payroll_id"))) {
    Send403();
}

$link = $moduleURL . "&" . $urlFilter->GetForURL();

$header = array(
    "Title" => GetTranslation("title-bookkeeping-export", $module),
    "Navigation" => $navigation,
    "StyleSheets" => array(
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"),
        array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
    ),
    "JavaScripts" => array(
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"),
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"),
        array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js")
    )
);

$content = $adminPage->Load("bookkeeping_export_list.html", $header);

$content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));

if ($request->GetProperty('Action') == 'Remove' && $request->GetProperty("ExportIDs")) {
    $exportList = new BookkeepingExportList($module);
    $exportList->Remove($request->GetProperty("ExportIDs"));
    $content->LoadMessagesFromObject($exportList);
    $content->LoadErrorsFromObject($exportList);
    Operation::Save($link, "billing", "bookkeeping_export_delete");
} elseif ($request->GetProperty("Action") == "Export") {
    Operation::Save($link, "billing", "bookkeeping_export", $request->GetProperty("export_id"));

    $export = new BookkeepingExport($module);
    $export->LoadFromObject($request);
    $export->SetProperty("user_name", $user->GetProperty("first_name") . " " . $user->GetProperty("last_name"));
    $export->SetProperty("user_id", $user->GetProperty("user_id"));
    $export->GenerateExportZIP();
} elseif ($request->GetProperty("Action") == "GetBookkeepingExport") {
    Operation::Save($link, "billing", "bookkeeping_export_id", $request->GetProperty("export_id"));

    $export = new BookkeepingExport($module);
    $export->LoadByID($request->GetProperty("export_id"));
    $export->OutputZipFile();
}

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "billing", "bookkeeping_export_list");

//Company list
$companyList = new CompanyUnitList($module);
if (isset($isBookkeepingExport)) {
    $companyList->LoadCompanyUnitListForTree(null, "bookkeeping_export");
} elseif (isset($isAdmin)) {
    $companyList->LoadCompanyUnitListForTree();
}
$companyListHtml = "";
foreach ($companyList->GetItems() as $item) {
    $companyListHtml .= "<option value='" . $item['company_unit_id'] . "' data-title='" . $item['title'] . "'>" . $item['select_prefix'] . $item['title'] . ", " . GetTranslation("remove-company-unit-id") . " " . $item['company_unit_id'] . "</option>";
}
$content->SetVar("CompanyListHtml", $companyListHtml);

//load filter data from session and to session
$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet("Payroll" . $key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty("Payroll" . $key));
    } else {
        $session->SetProperty("Payroll" . $key, $request->GetProperty($key));
    }
}
$request->SetProperty("BookkeepingExport", true);
$session->SaveToDB();

$exportList = new BookkeepingExportList($module);
$exportList->LoadBookkeepingExportList($request);
$content->LoadFromObjectList("ExportList", $exportList);

$content->SetVar("Paging", $exportList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
$content->SetVar("ListInfo", GetTranslation(
    'list-info1',
    array('Page' => $exportList->GetItemsRange(), 'Total' => $exportList->GetCountTotalItems())
));
$content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
$content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage")));
$content->LoadFromObject($request, $filterParams);

$itemsOnPageList = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $exportList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageList", $itemsOnPageList);

if (isset($isAdmin)) {
    $content->SetVar("Admin", 1);
}

if (isset($isAdmin) || isset($isBookkeepingExport)) {
    $content->SetVar("BookkeepingAdmin", 1);
}

if ($user->Validate(array("root"))) {
    $content->SetVar("HistoryAdmin", 'Y');
}
