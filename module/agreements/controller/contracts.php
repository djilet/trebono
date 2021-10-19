<?php

/**
 *  A controller for managing service user agreements
 */

require_once(__DIR__ . "/../include/contract.php");
require_once(__DIR__ . "/../include/contract_list.php");

$navigation[] = ["Title" => GetTranslation("module-title", $module), "Link" => $moduleURL];

$header = [
    "Title" => GetTranslation("module-title", $module),
    "Navigation" => $navigation,
    "JavaScripts" => [["JavaScriptFile" => CKEDITOR_PATH . "ckeditor.js"]],
];

$content = $adminPage->Load("contracts.html", $header);

$companyUnit = new  CompanyUnit("company");
$companyUnit->LoadByID($request->GetIntProperty("OrganizationID"));
$content->SetVar("CompanyUnitTitle", $companyUnit->GetProperty("title"));

if ($request->IsPropertySet("ServiceID") || $request->IsPropertySet("AgreementID")) {
    if (
        !Company::ValidateAccess($request->GetProperty("OrganizationID")) &&
        (!$request->IsPropertySet("EmployeeID") || !Employee::ValidateAccess($request->GetProperty("EmployeeID")))
    ) {
        Send403();
    }
    $contract = new AgreementsContract($module);
    $productGroup = new ProductGroup("product");

    $link = $request->IsPropertySet("ServiceID")
        ? $moduleURL . "&" . $urlFilter->GetForURL() . "&ServiceID=" . $request->GetProperty("ServiceID")
        : $moduleURL . "&" . $urlFilter->GetForURL();

    if ($request->GetProperty("Save")) {
        if ($contract->Save($request)) {
            Operation::Save($link, "agreements", "agreement_id_save", $request->GetProperty("AgreementID"));

            $urlFilter->SetProperty("OrganizationID", $request->GetIntProperty("OrganizationID"));
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());

            $content->LoadFromObject($request);

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);

            $productGroup->LoadByID($request->GetIntProperty("group_id"));
            $request->SetProperty("ServiceID", $request->GetIntProperty("group_id"));
            //exit(0);
        } else {
            $productGroup->LoadByID($request->GetIntProperty("group_id"));
            $request->SetProperty("ServiceID", $request->GetIntProperty("group_id"));
            $content->LoadFromObject($request);
            $content->LoadErrorsFromObject($contract);
        }
    } elseif ($request->GetProperty("Action") == "Preview") {
        $contract = new AgreementsContract("agreements");
        $employee = new Employee("company");

        $contract->SetProperty("content", $request->GetProperty("content"));
        $contract->SetProperty("updated_at", GetCurrentDateTime());
        $contract->GenerateContractToPdf($contract, $employee, $companyUnit, "agreement.pdf", "I");
    } elseif ($request->GetIntProperty("AgreementID")) {
        if ($request->GetIntProperty("Version")) {
            Operation::Save(
                $link,
                "agreements",
                "agreement_id_view_version",
                $request->GetProperty("AgreementID") . "(" . $request->GetIntProperty("Version") . ")"
            );

            $contract->LoadHistoryVersion($request->GetIntProperty("AgreementID"), $request->GetIntProperty("Version"));
            $content->SetVar("ReadOnly", 1);

            $parentAgreement = new AgreementsContract("agreements");
            $parentAgreement->LoadByID($request->GetIntProperty("AgreementID"));

            if ($request->IsPropertySet("EmployeeID")) {
                $employee = new Employee("company");
                $employee->LoadByID($request->GetProperty("EmployeeID"));
                $contract->LoadForApi($parentAgreement->GetProperty("group_id"), $employee);
            }

            $contract->SetProperty("group_id", $parentAgreement->GetProperty("group_id"));
        } else {
            Operation::Save($link, "agreements", "agreement_id", $request->GetProperty("AgreementID"));

            $contract->LoadByID($request->GetIntProperty("AgreementID"));
        }
        $content->LoadFromObject($contract);

        $productGroup->LoadByID($contract->GetIntProperty("group_id"));
        $request->SetProperty("ServiceID", $contract->GetIntProperty("group_id"));
    } else {
        Operation::Save($link, "agreements", "agreement_id");

        $contract->LoadByService(
            $request->GetIntProperty("OrganizationID"),
            $request->GetIntProperty("ServiceID")
        );
        $content->LoadFromObject($contract);

        $productGroup->LoadByID($request->GetIntProperty("ServiceID"));
    }

    $content->SetVar("isViewAgreementEdit", 1);
    $content->SetVar("group_id", $request->GetIntProperty("ServiceID"));
    $content->SetVar("organization_id", $request->GetIntProperty("OrganizationID"));
    $content->SetVar("ProductGroupCode", $productGroup->GetProperty("code"));
    $content->SetVar("ProductGroupTitle", $productGroup->GetProperty("title_translation"));

    $employee = new Employee("company");
    $productContract = new Contract("product");

    $replacementsTmp = $employee->GetReplacementsList();
    $replacements = $replacementsTmp["ReplacementList"];

    $replacementsTmp = $companyUnit->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $replacementsTmp = $productContract->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));

    $replacementsTmp = $specificProductGroup->GetReplacementsList();
    $replacements = array_merge($replacements, $replacementsTmp["ReplacementList"]);

    $content->SetLoop("AgreementReplacements", $replacements);
} else {
    if (
        !Company::ValidateAccess($request->GetProperty("OrganizationID"), null, true) &&
        (!$request->IsPropertySet("EmployeeID") || !Employee::ValidateAccess($request->GetProperty("EmployeeID")))
    ) {
        Send403();
    }

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "agreements", "agreement_list");

    $contracts = new AgreementsContractList();
    $contracts->LoadAll($request->GetIntProperty("OrganizationID"));

    $content->LoadFromObjectList("ServicesList", $contracts);
    $content->SetVar("organization_id", $request->GetIntProperty("OrganizationID"));
}
