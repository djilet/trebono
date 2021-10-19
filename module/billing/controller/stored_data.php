<?php

$user->LoadBySession();
if (!$user->Validate(array("root"))) {
    $user->ValidateAccess(array("stored_data" => null));
} else {
    $isAdmin = true;
}

$navigation[] = array(
    "Title" => GetTranslation("section-stored-data", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("FilterCreatedRange", "FilterCompanyUnitId", "ItemsOnPage");

$urlFilter->AppendFromObject($request, $filterParams);

if (!StoredData::ValidateAccess($request->GetProperty("stored_data_id"))) {
    Send403();
}

$link = $moduleURL . "&" . $urlFilter->GetForURL();

if ($request->GetProperty("Action") == "GetStoredDataExport") {
    Operation::Save($link, "billing", "stored_data_id", $request->GetProperty("stored_data_id"));

    $storedData = new StoredData($module);
    $storedData->LoadByID($request->GetProperty("stored_data_id"));
    $storedData->OutputZipFile();
}

$header = array(
    "Title" => GetTranslation("title-stored-data", $module),
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

$content = $adminPage->Load("stored_data_list.html", $header);

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "billing", "stored_data_list");

//load filter data from session and to session
$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet("StoredData" . $key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty("StoredData" . $key));
    } else {
        $session->SetProperty("StoredData" . $key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

$storedDataList = new StoredDataList($module);

if ($request->GetProperty('Do') == 'Remove' && $request->GetProperty("StoredDataIDs")) {
    $storedDataList->Remove($request->GetProperty("StoredDataIDs"));
    $content->LoadMessagesFromObject($storedDataList);
    $content->LoadErrorsFromObject($storedDataList);
    Operation::Save($link, "billing", "stored_data_delete");
}
if ($request->GetProperty('Do') == 'Activate' && $request->GetProperty("StoredDataIDs")) {
    $storedDataList->Activate($request->GetProperty("StoredDataIDs"));
    $content->LoadMessagesFromObject($storedDataList);
    $content->LoadErrorsFromObject($storedDataList);
    Operation::Save($link, "billing", "stored_data_activate");
}

$storedDataList->LoadStoredDataList($request);
$content->LoadFromObjectList("StoredDataList", $storedDataList);

$content->SetVar("Paging", $storedDataList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
$content->SetVar("ListInfo", GetTranslation(
    'list-info1',
    array('Page' => $storedDataList->GetItemsRange(), 'Total' => $storedDataList->GetCountTotalItems())
));
$content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
$content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage")));
$content->LoadFromObject($request, $filterParams);
$itemsOnPageList = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $storedDataList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageList", $itemsOnPageList);

$companyUnitList = new CompanyUnitList($module);
$companyUnitList->LoadCompanyUnitListForTree();
$companyUnitListItems = $companyUnitList->GetItems();
if ($request->IsPropertySet("FilterCompanyUnitId")) {
    foreach ($companyUnitListItems as $key => $item) {
        $companyUnitListItems[$key]["Selected"] = $item["company_unit_id"] == $request->GetIntProperty("FilterCompanyUnitId");
    }
}
$content->SetLoop("CompanyUnitList", $companyUnitListItems);

if (isset($isAdmin)) {
    $content->SetVar("Admin", 1);
}

if ($user->Validate(array("root"))) {
    $content->SetVar("HistoryAdmin", 'Y');
}

$language =& GetLanguage();
$content->SetVar("LanguageCode", $language->_interfaceLanguageCode);
