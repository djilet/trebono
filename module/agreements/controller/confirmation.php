<?php

/**
 *  A controller for managing recreation confirmations
 */

require_once(__DIR__ . "/../include/confirmation.php");
require_once(__DIR__ . "/../include/confirmation_list.php");

$navigation[] = [
    "Title" => GetTranslation("module-title", $module),
    "Link" => $moduleURL,
];

$header = [
    "Title" => GetTranslation("module-title", $module),
    "Navigation" => $navigation,
    "JavaScripts" => [
        ["JavaScriptFile" => CKEDITOR_PATH . "ckeditor.js"],
    ],
];

$urlFilter->LoadFromObject($request, ["Section"]);
$content = $adminPage->Load("confirmation_list.html", $header);

$companyUnit = new  CompanyUnit("company");
$companyUnit->LoadByID($request->GetIntProperty("CompanyUnitID"));
$content->SetVar("CompanyUnitTitle", $companyUnit->GetProperty("title"));

$link = $moduleURL . "&" . $urlFilter->GetForURL();

if ($request->GetProperty("Action") == "GetConfirmationPDF") {
    $user = new User();
    $user->LoadBySession();

    $employee = new Employee("company");
    $employee->LoadByUserID($user->GetProperty("user_id"));

    $hasPermission = false;
    if (
        $request->GetProperty("CompanyUnitID") == $employee->GetProperty("company_unit_id") ||
        $user->Validate(["root"])
    ) {
        $hasPermission = true;
    } else {
        if ($user->Validate(["employee" => null])) {
            foreach ($user->GetProperty("PermissionList") as $permission) {
                if (
                    $permission["link_id"] == $request->GetProperty("CompanyUnitID") ||
                    $permission["link_id"] == null
                ) {
                    $hasPermission = true;
                    break;
                }
            }
        }
    }

    if (!$hasPermission) {
        Send403();
    }

    Operation::Save($link, "confirmation", "confirmation_id_download", $request->GetProperty("ConfirmationID"));

    $confirmation = new ConfirmationEmployee($module);
    $confirmation->LoadByID($request->GetProperty("ConfirmationID"));

    $fileName = $confirmation->GetProperty("pdf_file");
    $filePath = PAYROLL_DIR . $fileName;
    OutputFile($filePath, CONTAINER__BILLING__PAYROLL, $fileName);
}

if ($request->IsPropertySet("ConfirmationID")) {
    $confirmationId = $request->GetProperty("ConfirmationID");
    if ($confirmationId === "null") {
        $confirmationId = null;
    }

    if (!Company::ValidateAccess($request->GetProperty("CompanyUnitID"))) {
        Send403();
    }

    $confirmation = new RecreationConfirmation($module);

    if ($request->GetProperty("Save")) {
        if ($confirmation->Save($request)) {
            Operation::Save($link, "confirmation", "confirmation_id_save", $confirmationId);
            $urlFilter->SetProperty("CompanyUnitID", $request->GetIntProperty("CompanyUnitID"));
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());

            $content->LoadFromObject($request);

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit(0);
        } else {
            $content->LoadFromObject($request);
            $content->LoadErrorsFromObject($confirmation);
        }
    } else {
        if ($request->GetProperty("Action") == "Preview") {
            $confirmation = new RecreationConfirmation("agreements");
            $employee = new Employee("company");

            $confirmation->SetProperty("content", $request->GetProperty("content"));
            $confirmation->SetProperty("updated_at", GetCurrentDateTime());
            $confirmation->GenerateConfirmationToPdf($employee, $companyUnit, "confirmation.pdf", "I");
        } elseif ($request->GetProperty("Action") == "HistoryView") {
            $confirmation = new RecreationConfirmation($module);
            $confirmation->LoadHistoryVersion($request->GetIntProperty("ConfirmationID"));
            $content->LoadFromObject($confirmation);
            $content->SetVar("ReadOnly", 1);
        } elseif ($request->GetIntProperty("ConfirmationID")) {
            Operation::Save($link, "confirmation", "confirmation_id", $confirmationId);
            $confirmation->LoadByID($request->GetIntProperty("ConfirmationID"));
            $content->LoadFromObject($confirmation);
        } else {
            Operation::Save($link, "confirmation", "confirmation_id");
        }
    }

    $content->SetVar("isViewConfirmationEdit", 1);
    $content->SetVar("CompanyUnitID", $request->GetIntProperty("CompanyUnitID"));

    $employee = new Employee("company");
    $productContract = new Contract("product");

    $replacementsTmp = $employee->GetReplacementsList(true);
    $replacements = $replacementsTmp["ReplacementList"];

    $replacementsTmp = $companyUnit->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $replacementsTmp = $productContract->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__RECREATION);
    $replacementsTmp = $specificProductGroup->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
    $replacementsTmp = $specificProductGroup->GetReplacementsList(false, "", false);
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $content->SetLoop("AgreementReplacements", $replacements);
} else {
    if (!Company::ValidateAccess($request->GetProperty("CompanyUnitID"), null, true)) {
        Send403();
    }

    $confirmation = new RecreationConfirmation($module);
    $confirmation->LoadByCompanyUnitID($request->GetIntProperty("CompanyUnitID"));
    $content->LoadFromObject($confirmation);

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "confirmation", "confirmation_list");

    $confirmationList = new RecreationConfirmationList($module);
    $confirmationList->LoadAll($request);

    $content->LoadFromObjectList("ConfirmationList", $confirmationList);
    $content->SetVar("CompanyUnitID", $request->GetIntProperty("CompanyUnitID"));

    $content->SetVar("Paging", $confirmationList->GetPagingAsHTML($moduleURL . "&" . $urlFilter->GetForURL()));
    $content->SetVar("ListInfo", GetTranslation(
        "list-info1",
        [
            "Page" => $confirmationList->GetItemsRange(),
            "Total" => $confirmationList->GetCountTotalItems(),
        ]
    ));

    $itemsOnPageList = [];
    foreach ([10, 20, 50, 100, 0] as $v) {
        $itemsOnPageList[] = ["Value" => $v, "Selected" => $v == $confirmationList->GetItemsOnPage() ? 1 : 0];
    }
    $content->SetLoop("ItemsOnPageList", $itemsOnPageList);
}
