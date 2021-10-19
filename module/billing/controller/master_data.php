<?php

$user->LoadBySession();
if (!$user->Validate(array("root"))) {
    Send403();
}

$navigation[] = array(
    "Title" => GetTranslation("section-master-data", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("ItemsOnPage");

$urlFilter->AppendFromObject($request, $filterParams);

$link = $moduleURL . "&" . $urlFilter->GetForURL();

$header = array(
    "Title" => GetTranslation("title-master-data", $module),
    "Navigation" => $navigation
);

$content = $adminPage->Load("master_data_list.html", $header);

if ($request->GetProperty("Do")) {
    $masterData = new MasterData($module);

    $isNew = $request->GetProperty("Type") == "New" ? "Y" : "N";
    $masterData->SetProperty("new", $isNew);

    switch ($request->GetProperty("Do")) {
        case "CreateEmployeeMasterData":
            $masterData->SetProperty("type", "employee");
            break;
        case "CreateCompanyUnitMasterDataService":
            $masterData->SetProperty("type", "company_unit_service");
            break;
        case "CreateCompanyUnitMasterDataVoucher":
            $masterData->SetProperty("type", "company_unit_voucher");
            break;
        case "CreateSepaServiceMasterData":
            $masterData->SetProperty("type", "sepa_service");
            break;
        case "CreateSepaVoucherMasterData":
            $masterData->SetProperty("type", "sepa_voucher");
            break;
    }

    $masterDataId = $masterData->Create();

    if ($masterDataId) {
        $masterDataToGenerate = new MasterData($module);
        $masterDataToGenerate->LoadByID($masterDataId);
        if ($masterDataToGenerate->GenerateMasterDataCSV()) {
            $message = new CommonObject();
            $message->AddMessage("master-data-success", "billing");
            $content->LoadMessagesFromObject($message);
        } else {
            $content->LoadErrorsFromObject($masterDataToGenerate);
        }
    } else {
        $content->LoadErrorsFromObject($masterData);
    }
} elseif ($request->GetProperty("Action") == "GetMasterDataExport") {
    Operation::Save($link, "billing", "master_data_id", $request->GetProperty("master_data_id"));

    $masterData = new MasterData($module);
    $masterData->LoadByID($request->GetProperty("master_data_id"));

    $partOfFilename = "";
    switch ($masterData->GetProperty("type")) {
        case "employee":
            $partOfFilename = "STD_Konto_Mitarbeiter_2KSG_";
            break;
        case "company_unit_service":
            $partOfFilename = "STD_Konto_Service_Kunden_";
            break;
        case "company_unit_voucher":
            $partOfFilename = "STD_Konto_Voucher_Kunden_";
            break;
        case "sepa_service":
            $partOfFilename = "STD_SEPA_Kunde_Service_Export_";
            break;
        case "sepa_voucher":
            $partOfFilename = "STD_SEPA_Kunde_Gutschein_Export_";
            break;
    }

    $fileName = $partOfFilename . date_create($masterData->GetProperty("created"))->format("Ymd") . "_" .
        $masterData->GetProperty("master_data_id") . ".csv";
    $filePath = MASTER_DATA_DIR . $fileName;

    OutputFile($filePath, CONTAINER__BILLING__MASTER_DATA, $fileName, true);
}

Operation::Save($link, "billing", "master_data_list");

//load filter data from session and to session
$session = GetSession();
foreach ($filterParams as $key) {
    if ($session->IsPropertySet("MasterData" . $key) && !$request->IsPropertySet($key)) {
        $request->SetProperty($key, $session->GetProperty("MasterData" . $key));
    } else {
        $session->SetProperty("MasterData" . $key, $request->GetProperty($key));
    }
}
$session->SaveToDB();

$masterDataList = new MasterDataList($module);

$masterDataList->LoadMasterDataList($request);
$content->LoadFromObjectList("MasterDataList", $masterDataList);

$content->SetVar("Paging", $masterDataList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
$content->SetVar("ListInfo", GetTranslation(
    'list-info1',
    array('Page' => $masterDataList->GetItemsRange(), 'Total' => $masterDataList->GetCountTotalItems())
));
$content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
$content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage")));
$content->LoadFromObject($request, $filterParams);
$content->SetVar("Admin", 1);
$itemsOnPageList = array();
foreach (array(10, 20, 50, 100, 0) as $v) {
    $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $masterDataList->GetItemsOnPage() ? 1 : 0);
}
$content->SetLoop("ItemsOnPageList", $itemsOnPageList);

if (isset($isAdmin)) {
    $content->SetVar("Admin", 1);
}
