<?php

require_once(__DIR__ . '/../include/employees_list.php');
require_once(__DIR__ . '/../include/contract.php');

$navigation[] = [
    "Title" => GetTranslation("module-title", $module),
    "Link" => $moduleURL,
];

$header = [
    "Title" => GetTranslation("module-title", $module),
    "Navigation" => $navigation,
];

if ($request->IsPropertySet('PdfAgreementId') && $request->IsPropertySet("OrganizationID")) {
    $user = new User();
    $user->LoadBySession();

    $employee = new Employee("company");
    $employee->LoadByUserID($user->GetProperty("user_id"));

    $hasPermission = false;
    if ($request->GetIntProperty('Employee') == $employee->GetProperty("employee_id") || $user->Validate(["root"])) {
        $hasPermission = true;
    } else {
        if ($user->Validate(["employee" => null])) {
            foreach ($user->GetProperty("PermissionList") as $permission) {
                if (
                    $permission["link_id"] == $request->GetProperty("OrganizationID") ||
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

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "agreements", "employee_agreement_id", $request->GetProperty("PdfAgreementId"));

    $contract = new AgreementsContract($module);
    $contract->LoadLastAcceptedAgreement(
        $request->GetIntProperty('PdfAgreementId'),
        $request->GetIntProperty('Employee')
    );

    $fileName = $contract->GetProperty("file");
    $filePath = AGREEMENTS_DIR . $fileName;
    OutputFile($filePath, CONTAINER__AGREEMENTS, $fileName);
}

if (!Company::ValidateAccess($request->GetProperty("OrganizationID"), null, true)) {
    Send403();
}

$link = $moduleURL . "&" . $urlFilter->GetForURL();
Operation::Save($link, "agreements", "employee_agreement_list");

$content = $adminPage->Load('employees.html', $header);

$employees = new AgreementsEmployeesList($module);
$employees->LoadAll($request->GetIntProperty('OrganizationID'));
$content->LoadFromObjectList('EmployeeList', $employees);

$companyUnit = new  CompanyUnit('company');
$companyUnit->LoadByID($request->GetIntProperty('OrganizationID'));
$content->SetVar('CompanyUnitTitle', $companyUnit->GetProperty('title'));
