<?php

define("IS_ADMIN", true);
require_once(dirname(__FILE__) . "/../../include/init.php");
require_once(dirname(__FILE__) . "/init.php");

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

$module = "company";
$moduleURL = "module.php?load=" . $module;

$result = [];
$request = new LocalObject(array_merge($_GET, $_POST));
$user = new User();
$user->LoadBySession();

$companyUnitIDs = $user->GetPermissionLinkIDs("employee");
$companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs("company_unit"));
$companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs("contract"));
$companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);

if (!$user->LoadBySession()) {
    $result["SessionExpired"] = GetTranslation("your-session-expired");
    exit();
}

if (
    !$user->Validate(array("root")) &&
    !$user->Validate(array("company_unit")) &&
    !$user->Validate(array("contract")) &&
    !$user->Validate(array("employee")) &&
    !$user->Validate(array("company_unit" => $companyUnitIDs), "or") &&
    !$user->Validate(array("contract" => $companyUnitIDs), "or") &&
    !$user->Validate(array("employee" => $companyUnitIDs), "or") &&
    !$user->Validate(array("employee_view"), "or")
) {
    Send403();
} else {
    switch ($request->GetProperty("Action")) {
        //history related
        case "GetPropertyHistoryContactHTML":
            $property =  $request->GetProperty("property_name");
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = Contact::GetPropertyValueListContact(
                $request->GetProperty("property_name"),
                $request->GetProperty("contact_id"),
                $content->GetVar("INTERFACE_LANGCODE")
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("ShowValue", "Y");

            if ($property === 'sending_pdf_invoice') {
                $content->SetVar('WithAgreedText', 1);
            }

            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetPropertyHistoryCompanyUnitHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            if (
                $request->GetProperty("property_name") == "company_document_archive"
                && $request->GetIntProperty("document_id") > 0
            ) {
                $valueList = CompanyUnitDocument::GetPropertyValueList(
                    "archive",
                    $request->GetProperty("document_id"),
                    $content->GetVar("INTERFACE_LANGCODE")
                );
                $valueList = array_merge(
                    $valueList,
                    CompanyUnitDocument::GetPropertyValueList(
                        "value",
                        $request->GetProperty("document_id"),
                        $content->GetVar("INTERFACE_LANGCODE")
                    )
                );
                $valueList = array_merge(
                    $valueList,
                    CompanyUnitDocument::GetPropertyValueList(
                        "title",
                        $request->GetProperty("document_id"),
                        $content->GetVar("INTERFACE_LANGCODE")
                    )
                );
                array_multisort(array_column($valueList, "created"), SORT_DESC, $valueList);
                $content->SetVar("IsDocumentProperty", 1);
                $content->SetVar(
                    "PropertyNameTranslation",
                    CompanyUnitDocument::GetPropertyByID("title", $request->GetProperty("document_id"))
                );
                $content->SetVar("company_unit_id", $request->GetProperty("company_unit_id"));
            } else {
                $valueList = CompanyUnit::GetPropertyValueListCompanyUnit(
                    $request->GetProperty("property_name"),
                    $request->GetProperty("company_unit_id"),
                    $content->GetVar("INTERFACE_LANGCODE")
                );
                $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));
            }

            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetReportPropertyHistoryCompanyUnitHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $valueList = YearlyReport::GetPropertyValueListReport(
                $request->GetProperty("property_name"),
                $request->GetProperty("report_id"),
                true,
                $content->GetVar("INTERFACE_LANGCODE")
            );
            $content->SetVar(
                "PropertyNameTranslation",
                YearlyReport::GetPropertyByID("file", $request->GetProperty("report_id"))
            );
            $content->SetLoop("ValueList", $valueList);

            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetPropertyHistoryEmployeeHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = Employee::GetPropertyValueListEmployee(
                $request->GetProperty("property_name"),
                $request->GetProperty("employee_id"),
                true,
                $content->GetVar("INTERFACE_LANGCODE")
            );
            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("HideValue", $request->GetProperty("property_name") == "password");
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetVersionHistoryDeviceHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("device_version_history.html");

            $versionList = Device::GetDeviceVersionListByDeviceID(
                $request->GetProperty("device_id"),
                $request->GetProperty("user_id")
            );
            $content->SetLoop("VersionList", $versionList);
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetPropertyHistoryVoucherHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_property_history.html");

            $content->SetVar("PropertyNameTranslation", $request->GetProperty("property_name_translation"));

            $valueList = Voucher::GetPropertyValueListVoucher(
                $request->GetProperty("property_name"),
                $request->GetProperty("voucher_id")
            );
            $content->SetLoop("ValueList", $valueList);
            $result["HTML"] = $popupPage->Grab($content);
            break;

        //get list
        case "GetCompanyListByOptionFilter":
            $companyList = new CompanyUnitList($module);

            if ($request->GetProperty("type") == "date") {
                $result = $companyList->GetIDsByContractDate(
                    $request->GetProperty("value"),
                    $request->GetProperty("option"),
                    $request->GetProperty("product_id"),
                    $request->GetProperty("option_operation")
                );
            } else {
                $result = $companyList->LoadByOptionFilter(
                    $request->GetProperty("option"),
                    $request->GetProperty("value"),
                    null,
                    $request->GetProperty("option_operation")
                );
            }

            if (is_array($result)) {
                $result["Data"] = array_column($result, "company_unit_id");
            } else {
                $result = false;
            }
            break;

        case "GetEmployeeListByOptionFilter":
            $employeeList = new EmployeeList($module);

            if ($request->GetProperty("type") == "date") {
                $result = $employeeList->GetIDsByContractDate(
                    $request->GetProperty("value"),
                    $request->GetProperty("option"),
                    $request->GetProperty("product_id"),
                    $request->GetProperty("option_operation")
                );
            } else {
                $result = $employeeList->LoadByOptionFilter(
                    $request->GetProperty("option"),
                    $request->GetProperty("value"),
                    null,
                    $request->GetProperty("option_operation")
                );
            }

            if (is_array($result)) {
                $result["Data"] = array_column($result, "employee_id");
                $result["MessageList"] = $employeeList->GetMessagesAsString();
            } else {
                $result = false;
            }
            break;

        case "GetEmployeeListByCompanyIDs":
            $result = EmployeeList::GetEmployeeListByCompanyUnitIDs($request->GetProperty("company_unit_ids"));
            break;

        case "GetEmployeeListByVersion":
            $result = EmployeeList::GetEmployeeListByVersionList(
                $request->GetProperty("version_list"),
                $request->GetProperty("company_unit_ids"),
                $request->GetProperty("version_operation")
            );
            break;

        case "GetContactPersonListByCompanyIDs":
            $result = ContactList::GetContactListByCompanyUnitIDs($request->GetProperty("company_unit_ids"));
            break;

        case "GetContactPersonListByContactType":
            $result = ContactList::GetContactListByCompanyUnitIDs(
                $request->GetProperty("company_unit_ids"),
                $request->GetProperty("contact_type"),
                $request->GetProperty("contact_for")
            );
            break;

        case "GetSendPushOrEmailUserHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_user_list_for_push.html");

            $valueList = array();
            if ($request->GetProperty("type") == "employee") {
                $employee_ids = $request->GetProperty("user_ids");
                if (is_array($employee_ids) && count($employee_ids) > 0) {
                    $employee = new Employee("company");
                    foreach ($employee_ids as $employee_id) {
                        $employee->LoadByID($employee_id);
                        $valueList[] = $employee->GetProperties();
                    }
                } else {
                    $valueList = EmployeeList::GetEmployeeListByVersionList(
                        $request->GetProperty("version"),
                        $request->GetProperty("company_unit_id"),
                        $request->GetProperty("version_operation")
                    );
                    $valueList = $valueList["EmployeeList"];
                }
            } else {
                $contact_ids = $request->GetProperty("user_ids");
                if (is_array($contact_ids) && count($contact_ids) > 0) {
                    $contact = new Contact("company");
                    foreach ($contact_ids as $contact_id) {
                        $contact->LoadByID($contact_id);
                        $valueList[] = $contact->GetProperties();
                    }
                } else {
                    $valueList = ContactList::GetContactListByCompanyUnitIDs(
                        $request->GetProperty("company_unit_id"),
                        $request->GetProperty("contact_type"),
                        $request->GetProperty("contact_for")
                    );
                    $valueList = $valueList["ContactList"];
                }
            }

            for ($i = 0; $i < count($valueList); $i++) {
                $valueList[$i]["title"] = CompanyUnit::GetTitleByID($valueList[$i]["company_unit_id"]);
            }

            $content->SetLoop("ValueList", $valueList);
            $content->SetVar("type", $request->GetProperty("type"));
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetEntityListHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_dashboard_entity_list.html");

            $filterArchive = $request->GetProperty("Type") == "created" ? "N" : "Y";

            $employeeList = array();
            $companyUnitList = array();
            switch ($request->GetProperty("Entity")) {
                case "company-unit":
                    $companyUnitList = CompanyUnitList::GetArchivePropertyHistory(new LocalObject(array(
                        "FilterArchive" => $filterArchive,
                        "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
                        "FilterCreatedTo" => $request->GetProperty("DateTo"),
                        "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
                    )), true);
                    break;
                case "employee":
                    $employeeList = EmployeeList::GetArchivePropertyHistory(new LocalObject(array(
                        "FilterArchive" => $filterArchive,
                        "FilterCreatedFrom" => $request->GetProperty("DateFrom"),
                        "FilterCreatedTo" => $request->GetProperty("DateTo"),
                        "ProductID" => Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
                    )), true);
                    break;
            }

            $content->SetLoop("EmployeeList", $employeeList);
            $content->SetLoop("CompanyUnitList", $companyUnitList);

            $result["Status"] = "success";
            $result["HTML"] = $popupPage->Grab($content);
            break;

        //company unit related
        case "UploadFile":
            $importID = CompanyUnitImport::WriteLog("Starting import", "info");
            if (!$importID) {
                return false;
            }
            $fileSys = new FileSys();
            $newFile = $fileSys->Upload('File', COMPANY_IMAGE_DIR . "file/");
            if ($newFile) {
                $filePath = COMPANY_IMAGE_DIR . "file/" . $newFile["FileName"];
                CompanyUnitImport::WriteLog("File '" . $newFile["name"] . "' uploaded", "info", $importID);

                $reader = new Xlsx();
                $reader = IOFactory::createReaderForFile($filePath);
                $spreadsheet = $reader->load($filePath);

                $companyUnitImport = new CompanyUnitImport($module);

                try {
                    $cells = $spreadsheet->getSheetByName("Auftragszusammenfassung")->toArray(null, false);
                    $companyUnitImport->SetStartDate($cells, $startDate);
                } catch (Exception $e) {
                }

                $cells = $spreadsheet->getSheetByName("Stammdaten")->toArray();

                $dataCompanyUnit = array();
                $companyUnitImport->SetCompanyUnitData($cells, $dataCompanyUnit, $parentCompanyUnitData, $startDate);

                $dataContact = array();
                $countContacts = 0;
                $companyUnitImport->SetContactData($cells, $countContacts, $dataContact);

                if ($countContacts == 0) {
                    $request->AddError("company-file-contact-list-empty", $module);
                    CompanyUnitImport::WriteLog(
                        GetTranslation("company-file-contact-list-empty", $module),
                        "error",
                        $importID
                    );
                }

                $cells = $spreadsheet->getSheetByName("Details Bestelldaten")->toArray();

                $dataEmployee = array();
                $countEmployees = 0;
                $companyUnitImport->SetEmployeeData(
                    $cells,
                    $dataEmployee,
                    $dataCompanyUnit,
                    $startDate,
                    $countEmployees
                );

                $user = new User();
                $user->LoadByID(AZ_IMPORT);

                $created = strtotime($startDate) < time() ? $startDate : null;

                CompanyUnitImport::WriteLog("Import data collected", "info", $importID);
                OutputErrorsIfExist($request, $importID);

                $companyUnit = new CompanyUnit($module, $dataCompanyUnit);

                $isLoaded = false;
                if (!empty($parentCompanyUnitData["title"]) || !empty($parentCompanyUnitData["customer_guid"])) {
                    $parentCompanyUnit = new CompanyUnit($module, $parentCompanyUnitData);
                    if ($parentCompanyUnit->LoadByTitleAndGuid()) {
                        $companyUnit->SetProperty("parent_unit_id", $parentCompanyUnit->GetProperty("company_unit_id"));
                    } else {
                        $request->AddError("parent-company-unit-not-exist", $module);
                        CompanyUnitImport::WriteLog(
                            GetTranslation("parent-company-unit-not-exist", $module),
                            "error",
                            $importID
                        );
                    }
                } elseif ($companyUnit->GetProperty("customer_guid")) {
                    if ($companyUnit->LoadByTitleAndGuid()) {
                        $isLoaded = true;
                    } else {
                        $request->AddError("company-unit-not-exist", $module);
                        CompanyUnitImport::WriteLog(
                            GetTranslation("company-unit-not-exist", $module),
                            "error",
                            $importID
                        );
                    }
                }

                OutputErrorsIfExist($request, $importID);

                if (!$isLoaded) {
                    if (!$companyUnit->Save()) {
                        $request->AddError("company-unit-save-errors", $module);
                        $request->AppendErrorsFromObject($companyUnit);

                        CompanyUnitImport::WriteLog(
                            GetTranslation("company-unit-save-errors", $module),
                            "error",
                            $importID
                        );
                        CompanyUnitImport::WriteLog(
                            print_r($companyUnit->GetErrorsAsString(), true),
                            "error",
                            $importID
                        );
                    } else {
                        CompanyUnitImport::WriteLog(
                            "Created company unit "
                                . $companyUnit->GetProperty("title") . " #"
                                . $companyUnit->GetIntProperty("company_unit_id"),
                            "info",
                            $importID
                        );
                    }
                }
                OutputErrorsIfExist($request, $importID);
                CompanyUnitImport::SetCompanyUnitToHistory($importID, $companyUnit->GetIntProperty("company_unit_id"));

                if (isset($dataCompanyUnit["ContractList"]) && !$isLoaded) {
                    for ($i = 0; $i < count($dataCompanyUnit["ContractList"]); $i++) {
                        $contract = new Contract("product");
                        $contract->OnOptionUpdate(
                            OPTION_LEVEL_COMPANY_UNIT,
                            $dataCompanyUnit["ContractList"][$i]["product_id"],
                            $companyUnit->GetProperty("company_unit_id"),
                            null,
                            $dataCompanyUnit["ContractList"][$i]["start_date"],
                            null,
                            true
                        );
                    }
                }
                if (isset($dataCompanyUnit["OptionList"]) && !$isLoaded) {
                    for ($i = 0; $i < count($dataCompanyUnit["OptionList"]); $i++) {
                        if (!$dataCompanyUnit["OptionList"][$i]["value"]) {
                            continue;
                        }

                        $option = new Option("product");
                        $option->SaveOptionValue(
                            OPTION_LEVEL_COMPANY_UNIT,
                            $dataCompanyUnit["OptionList"][$i]["option_id"],
                            $dataCompanyUnit["OptionList"][$i]["value"],
                            $companyUnit->GetProperty("company_unit_id"),
                            $created,
                            $user
                        );
                    }
                }
                $bonusProductGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BONUS);
                for ($i = 0; $i < count($dataEmployee); $i++) {
                    $dataEmployee[$i]["company_unit_id"] = $companyUnit->GetProperty("company_unit_id");
                    $employee = new Employee($module, $dataEmployee[$i]);

                    if (!$employee->GetProperty("email")) {
                        continue;
                    }

                    if (!$employee->Save()) {
                        $request->AddError("employee-save-errors", $module, array("employee_xml_id" => $i + 1));
                        $request->AppendErrorsFromObject($employee);

                        CompanyUnitImport::WriteLog(GetTranslation(
                            "employee-save-errors",
                            $module,
                            array("employee_xml_id" => $i + 1)
                        ), "error", $importID);
                        CompanyUnitImport::WriteLog(print_r($employee->GetErrorsAsString(), true), "error", $importID);
                        continue;
                    } else {
                        CompanyUnitImport::IncrementEmployeeCounterToHistory($importID);
                    }

                    if (isset($dataEmployee[$i]["OptionList"])) {
                        for ($j = 0; $j < count($dataEmployee[$i]["OptionList"]); $j++) {
                            if (!$dataEmployee[$i]["OptionList"][$j]["value"]) {
                                continue;
                            }

                            $option = new Option("product");
                            if (
                                $option->SaveOptionValue(
                                    OPTION_LEVEL_EMPLOYEE,
                                    $dataEmployee[$i]["OptionList"][$j]["option_id"],
                                    $dataEmployee[$i]["OptionList"][$j]["value"],
                                    $employee->GetProperty("employee_id"),
                                    $created,
                                    $user
                                )
                            ) {
                                continue;
                            }

                            $request->AppendErrorsFromObject($option);
                            CompanyUnitImport::WriteLog(
                                print_r($option->GetErrorsAsString(), true),
                                "error",
                                $importID
                            );
                        }
                    }
                    if (isset($dataEmployee[$i]["ContractList"])) {
                        for ($j = 0; $j < count($dataEmployee[$i]["ContractList"]); $j++) {
                            $contract = new Contract("product");
                            $resultContract = $contract->OnOptionUpdate(
                                OPTION_LEVEL_EMPLOYEE,
                                $dataEmployee[$i]["ContractList"][$j]["product_id"],
                                $employee->GetProperty("employee_id"),
                                null,
                                $dataEmployee[$i]["ContractList"][$j]["start_date"],
                                null,
                                true
                            );

                            if (
                                !$resultContract || $dataEmployee[$i]["ContractList"][$j]["product_id"]
                                != Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
                            ) {
                                continue;
                            }

                            $newUser = new User();
                            $newUser->LoadByID($employee->GetProperty("user_id"));
                            $permissionList = User::ValidatePermissionList(array(User::GetPermissionID("api")));
                            $newUser->UpdatePermissions($permissionList, array());
                        }
                    }
                    if (!isset($dataEmployee[$i]["bonusVoucher"])) {
                        continue;
                    }

                    if (intval($dataEmployee[$i]["bonusVoucher"]["amount"]) <= 0) {
                        continue;
                    }

                    $dataEmployee[$i]["bonusVoucher"]["amount"] = preg_replace(
                        "/[^0-9.]/",
                        "",
                        $dataEmployee[$i]["bonusVoucher"]["amount"]
                    );

                    if (
                        $dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] == "1"
                        || $dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] == "2"
                    ) {
                        $dataEmployee[$i]["bonusVoucher"]["recurring"] = "Y";
                    }

                    if ($dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] == "1") {
                        $dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] = "monthly";
                    } elseif ($dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] == "2") {
                        $dataEmployee[$i]["bonusVoucher"]["recurring_frequency"] = "yearly";
                    }

                    $voucher = new Voucher("company");
                    $voucher->AppendFromArray($dataEmployee[$i]["bonusVoucher"]);

                    $voucher->SetProperty("employee_id", $employee->GetProperty("employee_id"));
                    $voucher->SetProperty("group_id", $bonusProductGroupID);
                    $voucher->SetProperty("created_user_id", $user->GetProperty("user_id"));
                    $voucher->SetProperty(
                        "end_date",
                        date("Y-12-31", strtotime($dataEmployee[$i]["bonusVoucher"]["voucher_date"]))
                    );

                    if (!$voucher->GetProperty("amount")) {
                        continue;
                    }

                    if ($voucher->Save()) {
                        continue;
                    }

                    $request->AddError(
                        "employee-voucher-save-errors",
                        $module,
                        array("employee_xml_id" => $i + 1)
                    );
                    $request->AppendErrorsFromObject($voucher);

                    CompanyUnitImport::WriteLog(GetTranslation(
                        "employee-voucher-save-errors",
                        $module,
                        array("employee_xml_id" => $i + 1)
                    ), "error", $importID);
                    CompanyUnitImport::WriteLog(
                        print_r($voucher->GetErrorsAsString(), true),
                        "error",
                        $importID
                    );
                }

                for ($i = 0; $i < $countContacts; $i++) {
                    $dataContact[$i]["company_unit_id"] = $companyUnit->GetProperty("company_unit_id");

                    if ($isLoaded) {
                        $contactID = Contact::GetIDByEmailAndCompanyUnit(
                            $dataContact[$i]["email"],
                            $dataContact[$i]["company_unit_id"]
                        );
                        if (intval($contactID) > 0) {
                            $dataContact[$i]["contact_id"] = $contactID;
                        }
                    }
                    $contact = new Contact($module, $dataContact[$i]);
                    if ($contact->Save(true)) {
                        continue;
                    }

                    $request->AddError("contact-save-errors", $module, array("contact_xml_id" => $i + 1));
                    $request->AppendErrorsFromObject($contact);

                    CompanyUnitImport::WriteLog(GetTranslation(
                        "contact-save-errors",
                        $module,
                        array("contact_xml_id" => $i + 1)
                    ), "error", $importID);
                    CompanyUnitImport::WriteLog(print_r($contact->GetErrorsAsString(), true), "error", $importID);
                }

                $request->AddMessage("file-uploaded");

                $popupPage = new PopupPage($module, true);
                $content = $popupPage->Load("block_import_message.html");

                $content->LoadMessagesFromObject($request);
                $content->LoadErrorsFromObject($request);

                CompanyUnitImport::WriteLog("Import completed", "info", $importID, true);

                $result["HTML"] = $popupPage->Grab($content);

                //$result = array("Status" => "success", "ErrorList" => $request->GetErrorsAsArray());
            } else {
                $request->AppendErrorsFromObject($fileSys);
                CompanyUnitImport::WriteLog(print_r($fileSys->GetErrorsAsString(), true), "error", $importID);
                OutputErrorsIfExist($request, $importID);
            }
            break;

        case 'RemoveCompanyAppLogo':
            $companyUnitId = $request->GetIntProperty('ItemID');
            $imageName = $request->GetProperty('ImageName');
            $savedImageName = $request->GetProperty('SavedImage');
            $imagePath = COMPANY_APP_LOGO_IMAGE_DIR . $savedImageName;

            if (!in_array($imageName, ['app_logo_image', 'app_logo_mini_image', 'voucher_logo_image'])) {
                $request->AddError('unknown-image-name', $module);
                $result = array(
                    "Status" => "error",
                    "ErrorList" => $request->GetErrorsAsArray()
                );
                break;
            }

            $fileStorage = GetFileStorage(CONTAINER__COMPANY);
            if (!$fileStorage->FileExists($imagePath)) {
                $request->AddError('file-not-found', $module);
                $result = array(
                    "Status" => "error",
                    "ErrorList" => $request->GetErrorsAsArray()
                );
            } else {
                $fileStorage->Remove($imagePath);
                if (!$fileStorage->FileExists($imagePath)) {
                    $stmt = GetStatement();
                    $query = 'UPDATE company_unit SET ' . $imageName . '
                                =NULL WHERE company_unit_id=' . intval($companyUnitId);
                    $stmt->Execute($query);

                    $result = array(
                        "Status" => "success"
                    );
                } else {
                    $request->AddError('file-not-removed', $module);
                    $result = array(
                        "Status" => "error",
                        "ErrorList" => $request->GetErrorsAsArray()
                    );
                }
            }
            break;

        case 'SetEnableAgreementCompanyUnit':
            $companyUnitId = $request->GetIntProperty('company_unit_id');
            $value = $request->GetProperty('value');

            $companyUnit = new CompanyUnit($module);
            $result['result'] = $companyUnit->SetAgreementEnabled($companyUnitId, $value);
            break;

        //employee related
        case "GetEmployeeUnitMap":
            $specificFood = new SpecificProductGroupFood();
            $month = $request->GetProperty("month");
            $dateFrom = date("d-m-Y", strtotime("-7 days", strtotime($month . "01")));
            $dateTo = date("d-m-Y", strtotime("+ 7 days", strtotime(date("t-m-Y", strtotime($month . "01")))));
            $unitDateListFood = $specificFood->GetUnitDateList(
                $request->GetProperty("employee_id"),
                $dateFrom,
                $dateTo
            );

            for ($i = 0; $i < count($unitDateListFood); $i++) {
                $result[$i]['date'] = $unitDateListFood[$i]['date'];

                $result[$i]['unit'] = $unitDateListFood[$i]['unit'];
                $result[$i]['unit_api'] = $unitDateListFood[$i]['unit_api'];
                $result[$i]['used'] = $unitDateListFood[$i]['used'];
                $result[$i]['used_api'] = $unitDateListFood[$i]['used_api'];
            }

            if ($request->GetProperty("is_employee_admin") !== "Y") {
                $specificFoodVoucher = new SpecificProductGroupFoodVoucher();
                $unitDateListFoodVoucher = $specificFoodVoucher->GetUnitDateList(
                    $request->GetProperty("employee_id"),
                    $dateFrom,
                    $dateTo
                );
                for ($i = 0; $i < count($unitDateListFoodVoucher); $i++) {
                    $result[$i]['unit_voucher'] = $unitDateListFoodVoucher[$i]['unit'];
                    $result[$i]['unit_api_voucher'] = $unitDateListFoodVoucher[$i]['unit_api'];
                    $result[$i]['used_voucher'] = $unitDateListFoodVoucher[$i]['used'];
                    $result[$i]['used_api_voucher'] = $unitDateListFoodVoucher[$i]['used_api'];
                }
            }
            break;

        case "GetEmployeeReceiptListForDate":
            $specificFood = new SpecificProductGroupFood();
            $unit = $specificFood->GetUnit(new Receipt("receipt", array(
                "document_date" => date("Y-m-d", strtotime($request->GetProperty("date"))),
                "employee_id" => $request->GetProperty("employee_id")
            )));
            $receiptList = $specificFood->GetReceiptListForDate(
                $request->GetProperty("employee_id"),
                date("d-m-Y", strtotime($request->GetProperty("date")))
            );

            if ($request->GetProperty("is_employee_admin") !== "Y") {
                $specificFoodVoucher = new SpecificProductGroupFoodVoucher();
                $unitVoucher = $specificFoodVoucher->GetUnit(new Receipt("receipt", array(
                    "document_date" => date("Y-m-d", strtotime($request->GetProperty("date"))),
                    "employee_id" => $request->GetProperty("employee_id")
                )));
                $receiptListVoucher = $specificFoodVoucher->GetReceiptListForDate(
                    $request->GetProperty("employee_id"),
                    date("d-m-Y", strtotime($request->GetProperty("date")))
                );
                $receiptList = array_merge($receiptList, $receiptListVoucher);
            }

            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_calendar.html");

            $receiptCompanyUnitIDs = $user->GetPermissionLinkIDs("receipt");
            $receiptCompanyUnitIDs = CompanyUnitList::AddChildIDs($receiptCompanyUnitIDs);

            $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, "Y");
            $voucherProductGroupList = array_column($voucherProductGroupList, "group_id");
            if (
                $user->Validate(array("receipt")) || in_array(
                    $request->GetProperty("company_unit_id"),
                    $receiptCompanyUnitIDs
                )
            ) {
                $content->SetVar("ReceiptListShowLinks", 1);
            } else {
                for ($i = 0; $i < count($receiptList); $i++) {
                    if (
                        $request->GetProperty("is_employee_self") == "Y" || ($request->GetProperty("is_employee_admin") == "Y" && !in_array(
                            $receiptList[$i]["group_id"],
                            $voucherProductGroupList
                        ))
                    ) {
                        $receiptFileList = new ReceiptFileList("receipt");
                        $receiptFileList->LoadFileList($receiptList[$i]["receipt_id"]);
                        $receiptList[$i]["FileList"] = $receiptFileList->GetItems();
                    } else {
                        $receiptList[$i]["show_id"] = 1;
                    }
                }
            }

            $content->SetLoop("ReceiptList", $receiptList);

            $foodServiceID = Product::GetProductIDByCode(PRODUCT__FOOD__MAIN);
            $foodVoucherServiceID = Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN);

            $contract = new Contract("product");
            if (
                $contract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $request->GetProperty("employee_id"),
                    $foodServiceID
                )
            ) {
                $content->SetVar("Unit", $unit);
            }
            if (
                $contract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $request->GetProperty("employee_id"),
                    $foodVoucherServiceID
                )
            ) {
                $content->SetVar("UnitVoucher", $unitVoucher);
            }

            $content->SetVar("Date", $request->GetProperty("date"));

            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "RemoveEmployee":
            if ($employeeIds = $request->GetProperty("employee_ids")) {
                if ((new Contract($module))->ExistsActiveForEmployees((array) $employeeIds)) {
                    $result = [
                        "status" => "error",
                        "error" => count((array) $employeeIds) > 1
                            ? GetTranslation("deactivate-employee-error-employees-have-active-contracts", $module)
                            : GetTranslation("deactivate-employee-error-employee-has-active-contracts", $module),
                    ];
                    break;
                }

                $employeeList = new EmployeeList($module);
                if ($employeeList->Remove((array) $employeeIds)) {
                    $link = $moduleURL;
                    if (($referrer = $_SERVER["HTTP_REFERER"]) && ($components = parse_url($referrer))) {
                        $path = ltrim($components["path"], "/");
                        $query = $components["query"];
                        $link = "{$path}?{$query}";
                    }

                    if (is_array($employeeIds) && count($employeeIds) > 0) {
                        foreach ($employeeIds as $id) {
                            Operation::Save($link, "employee", "employee_delete", $id);
                        }
                    } else {
                        Operation::Save($link, "employee", "employee_delete", $employeeIds);
                    }
                }

                $result = [
                    "status" => $employeeList->HasErrors() ? "error" : "success",
                    "message" => $employeeList->GetMessagesAsString(),
                    "error" => $employeeList->GetErrorsAsString(),
                ];
            }
            break;

        //voucher related
        case "GetVoucherStatisticsHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_voucher_statistics.html");

            $voucherList = new VoucherList($module);
            if ($request->IsPropertySet("employee_id")) {
                $voucherStatistics = $voucherList->GetVoucherStatisticsForEmployee(
                    $request->GetProperty("employee_id"),
                    $request->GetProperty("product_group_id"),
                    $request->GetProperty("yearly_statistics_date"),
                    $content->GetVar("INTERFACE_LANGCODE")
                );
                $content->SetVar("ShowVoucherIDs", 1);
                $content->SetVar("Year", $request->GetProperty("yearly_statistics_date"));
            } else {
                $voucherStatistics = $voucherList->GetVoucherStatistics(
                    $request->GetProperty("company_unit_id"),
                    $request->GetProperty("product_group_id"),
                    $request->GetProperty("yearly_statistics_date"),
                    $content->GetVar("INTERFACE_LANGCODE")
                );
            }

            $content->SetLoop("MonthTitleList", $voucherStatistics["month_title_list"]);
            $content->SetLoop("StatisticsList", $voucherStatistics["voucher_list"]);
            $content->SetVar("ProductGroupID", $request->GetProperty("product_group_id"));

            $result["ProductGroupID"] = $request->GetProperty("product_group_id");
            $result["HTML"] = $popupPage->Grab($content);
            break;

        case "GetVoucherReceipts":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_voucher_receipts.html");

            $voucherID = $request->GetProperty("voucher_id");
            $receiptList = new ReceiptList("receipt");
            $voucherReceiptList = $receiptList->GetReceiptListForVoucher(
                $voucherID,
                $content->GetVar("INTERFACE_LANGCODE")
            );

            for ($i = 0; $i < count($voucherReceiptList); $i++) {
                $receiptFileList = new ReceiptFileList("receipt");
                $receiptFileList->LoadFileList($voucherReceiptList[$i]["receipt_id"]);
                $voucherReceiptList[$i]["FileList"] = $receiptFileList->GetItems();
            }

            $content->SetLoop("ReceiptList", $voucherReceiptList);
            $content->SetVar("voucher_id", $voucherID);
            $content->SetVar("created", Voucher::GetPropertyByID("created", $voucherID));
            if ($user->Validate(array("root"))) {
                $content->SetVar("Admin", 'Y');
            }
            $result["HTML"] = $popupPage->Grab($content);
            break;

            // not voucher services 
            case "GetNotVoucherStatisticsHTML":
                $popupPage = new PopupPage($module, true);
                $content = $popupPage->Load("block_not_voucher_statistics.html");
                $content->SetVar("ProductGroupID", $request->GetProperty("product_group_id"));
                $content->SetVar("Year", $request->GetProperty("yearly_statistics_date"));

                $year = $request->GetProperty("yearly_statistics_date");
                // $content->SetVar("ShowReceiptIDs", 1);
                $monthList = GetMonthList($startOfYear, $endOfYear, $languageCode);
                $foodService = new SpecificProductGroupFood;
                $employeeListClass = new EmployeeList($module);
                $employeeList = $employeeListClass->getEmployeeIdWithActiveService($request->GetProperty("company_unit_id"), 1, $year);
            
                // statistics of one employee

                $employeeStatisticsPerYear= [];
            
                foreach($employeeList as $employeeId) {
            
                        $yearData = [];
            
                            $dateFrom = "01-01-$year";
                            $dateEnd = "31-12-$year";
                                
                                    $yearFoodServiceData = $foodService->GetUnitDateList($employeeId, $dateFrom, $dateEnd);        
            
                    $employeeStatisticsPerYear[] = ['employeeId' => $employeeId, 'yearData' => $yearFoodServiceData];
                }
            
            
                // select unique unitPrice and make normal format for spent units
                $arrayOfUnitPrice = [];
                foreach($employeeStatisticsPerYear as &$array) {
                    foreach ($array['yearData'] as &$value) {
                        if (!in_array($value['unit'], $arrayOfUnitPrice)) {
                            $arrayOfUnitPrice[] = $value['unit'];
                        }
                            $value['used'] = round($value['used'] / $value['unit'], 2);
                        }
                        
                    }   
                
                // arrya for SetLoop
                $resultOfFoodStatistics = [];
                    foreach($arrayOfUnitPrice as $unit) {
                        $resultOfFoodStatistics[] = ['unitPrice' => $unit, 'employeeData' => []];
                    }
            
            
                $firstDayOfCurrentMonth = date('Y-m-01');
                $lastDayOfCurrentMonth =  date('Y-m-t');
                
            
                    // get total for one Unit
                    // unit
                    foreach ($resultOfFoodStatistics as &$resultValue) {
                    
                    
                        $totalPerMonth = [];
                        $totalPerYear = 0;
            
                        // user
                        foreach($employeeStatisticsPerYear as &$array) {
                            $employeeData = [];
                            $totalCount = 0;
                            $hasVoucher = false;
                            // months
                            $date = "01-01-$year";
                            for ($i = 0; $i < 12; $i++) {
            
                                $dateFrom = strtotime("+$i MONTH", strtotime($date));
                                $dateEnd = strtotime("+" . ($i+1) . "MONTH", strtotime($date));
                    
                                $newDateFrom = date('Y-m-d', $dateFrom);
                                $newDateEnd = date('Y-m-d', $dateEnd);
                                $usedCount = 0;
                                $time = '';
                               
                
                                    // days
                                    foreach($array['yearData'] as $value) {
            
                                        if ($value['date'] >= $newDateFrom && $value['date'] < $newDateEnd) {
            
                                                if ($value['unit'] == $resultValue['unitPrice']) {
                                                    $hasVoucher = true;
                                                    $usedCount += $value['used'];
                                                    $totalCount += $value['used'];
                                                }
                                                
                                                if ($value['date'] < $firstDayOfCurrentMonth) {
                                                    $time = 0;
                                                }   
                                                else if ($value['date'] > $lastDayOfCurrentMonth) 
                                                {
                                                    $time = 2;
                                                }
                                                else {
                                                    $time = 1;
                                                }
                                        }
            
                                    }
            
                                    // future date
                                    if ($usedCount == 0 && $time == 2 && $hasVoucher) {
                                        $usedCount = $foodService->GetUnitCountLimitForMonth($array['employeeId'], 1, null, $newDateFrom, true);
                                        $totalCount += $usedCount;
                                    }
            
                                $employeeData[$i] = ['monthCount' => $usedCount, 'time' => $time];
            
                                $totalPerMonth[$i]['monthCount'] += $usedCount;
                                $totalPerMonth[$i]['time'] = $time;
                            }
                            if ($hasVoucher) {
                                $resultValue['employeeData'][] = ['employeeId'=> $array['employeeId'], 'employeeName' => Employee::GetNameByID($array['employeeId']), 'totalCount' => $totalCount, 'monthStatistics' => $employeeData];
                                $totalPerYear += $totalCount;
                            }
                        }
            
                        $resultValue['totalPerYear'] = $totalPerYear;
                        $resultValue['totalPerMonth'] = $totalPerMonth;        
                    }
            
            
                $content->SetLoop('MonthTitleList', GetMonthList(date("Y-01-01"), date("Y-12-31"), $content->GetVar("INTERFACE_LANGCODE")));

                $content->SetLoop('StatisticsList', $resultOfFoodStatistics);
                $result["ProductGroupID"] = $request->GetProperty("product_group_id");
                $result["HTML"] = $popupPage->Grab($content);
                break;



        //  billable list
        case "GetBillableStatisticsHTML":
            $popupPage = new PopupPage($module, true);
            $content = $popupPage->Load("block_billable_statistics.html");

            $currentPage = $request->GetProperty('PageId');
            if (!$currentPage){
                $currentPage = 1;
            }

            $companyId = $request->GetProperty("company_unit_id");

            $billableList = new BillableItemList('company');
            $billableItemsData = $billableList->getCompanyBillableList($companyId, $currentPage);

            $content->SetLoop('Statistics', $billableItemsData);
            $content->SetVar("billablePaging", $billableList->GetPagingAsHTML($moduleURL));
            
            $content->SetVar('companyId', $companyId);
            $result["HTML"] = $popupPage->Grab($content);
            break;     
        
        case "DisableBillableItem":
            $billableItemId = $request->GetProperty('billable_item_id');
            $billableItem = new BillableItem;
        
            $result['disabled'] = $billableItem->changeArchiveToY($billableItemId);
            break;

        case "ActiveBillableItem":
            $billableItemId = $request->GetProperty('billable_item_id');
            $billableItem = new BillableItem;
        
            $result['activated'] = $billableItem->changeArchiveToN($billableItemId);
            break;

        case "DeleteBillableItem":
            $billableItemId = $request->GetProperty('billable_item_id');
            $billableItem = new BillableItem;
        
            $result['deleted'] = $billableItem->deleteBillableItem($billableItemId);
            break;

        case "EditBill":
            $billableItem = new BillableItem();
            $billData = $request->GetProperties();
                if ($billData['billable_item_id']) {
                    if ($billableItem->checkBillById($billData['billable_item_id'], $billData['company_unit_id'])) {
                        $result['edit'] = $billableItem->editBillableItem($billData);
                    }
                }
                else {
                    $result['add'] = $billableItem->addBillableItem($billData);
                }
            break;


        case "CheckShowRecurring":
            $date = $request->ValidateNotEmpty("voucher_date")
                ? $request->GetProperty("voucher_date")
                : GetCurrentDate();
            $result = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION,
                $request->GetProperty("employee_id"),
                $date
            );
            break;
        case "UpdateDefaultReason":
            $date = $request->ValidateNotEmpty("voucher_date")
                ? $request->GetProperty("voucher_date")
                : GetCurrentDate();
            $groupCode = ProductGroup::GetProductGroupCodeByID($request->GetProperty("group_id"));
            $voucherScenario = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$groupCode],
                $request->GetProperty("employee_id"),
                GetCurrentDate() //this option is not dependant on date and time (task #4009)
            );

            if (
                $request->GetProperty('reason') != null
                && strpos($voucherScenario, "_flex") !== false
            ) {
                $selectedReason = $request->GetProperty('reason');
            } else {
                $selectedReason = Voucher::GetDefaultVoucherReason(
                    OPTION_LEVEL_EMPLOYEE,
                    $request->GetProperty("employee_id"),
                    $groupCode,
                    $date
                );
            }
            $result["ReasonList"] = Voucher::GetVoucherReasonList(
                $selectedReason,
                "voucher_sets_of_goods"
            );
            if ($voucherScenario == "exchangeable") {
                $result["ReasonList"] = [$result["ReasonList"][0]];
            } elseif (strpos($voucherScenario, "_flex") === false) {
                foreach ($result["ReasonList"] as $reason) {
                    if (empty($reason["Selected"]) || !$reason["Selected"]) {
                        continue;
                    }

                    $result["ReasonList"] = [$reason];
                    break;
                }
            } elseif (!$user->Validate(['root'])) {
                unset($result["ReasonList"][0]);
                $result["ReasonList"] = array_values($result["ReasonList"]);
            }
            break;

        //misc
        case "SearchUser":
            $res_user = new User();
            $res_user->LoadByID($request->GetIntProperty("user_id"));
            $result = $res_user->GetProperties();
            break;

        case 'UploadReceipt':
            $userID = $request->GetIntProperty('user_id');
            $groupID = $request->GetIntProperty('selected_group_id');

            $user = new User();
            if ($user->LoadByID($userID)) {
                $employee = new Employee("company");
                if ($employee->LoadByUserID($userID)) {
                    $receipt = new Receipt('receipt');
                    $receipt->LoadFromObject($employee, ["employee_id", "user_id"]);
                    $receipt->SetProperty('is_web_upload', $request->GetProperty('is_web_upload'));

                    $productGroup = new ProductGroup("product");
                    if ($productGroup->LoadByID($groupID)) {
                        $receipt->SetProperty("group_id", $groupID);
                    } else {
                        $receipt->RemoveProperty("group_id");
                    }

                    /* deny receipts right away, if employee is deactivated OR product's contract is deativatied */
                    $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
                    $productCode = $specificProductGroup->GetMainProductCode();

                    $contract = new Contract("contract");
                    $existProductContract = false;

                    $existBaseContract = $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                        $employee->GetIntProperty('employee_id'),
                        GetCurrentDate()
                    );
                    if ($existBaseContract) {
                        $existProductContract = $contract->ContractExist(
                            OPTION_LEVEL_EMPLOYEE,
                            Product::GetProductIDByCode($productCode),
                            $employee->GetIntProperty('employee_id'),
                            GetCurrentDate()
                        );
                    }

                    if (!$existBaseContract) {
                        $receipt->SetProperty(
                            "denial_reason",
                            Config::GetConfigValue('receipt_autodeny_employee_deactivated')
                        );
                        $receipt->SetProperty("status", "denied");
                    } elseif (!$existProductContract) {
                        $receipt->SetProperty(
                            "denial_reason",
                            Config::GetConfigValue('receipt_autodeny_no_active_contract')
                        );
                        $receipt->SetProperty("status", "denied");
                    }

                    if ($receipt->Create()) {
                        $receiptFile = new ReceiptFile('receipt');
                        $receiptFile->SetProperty("receipt_id", $receipt->GetProperty("receipt_id"));
                        $receiptFile->SetProperty("hash", $request->GetProperty("hash"));

                        if ($receiptFile->Create($userID)) {
                            $result['code'] = 201;
                            $result['receipt_id'] = $receipt->GetIntProperty('receipt_id');

                            break;
                        }

                        $result['errors'] = $receiptFile->GetErrors();
                        $result['code'] = 400;

                        break;
                    }

                    $result['errors'] = $receipt->GetErrors();
                    $result['code'] = 400;
                }
            }
    }
}

function OutputErrorsIfExist(LocalObject $request, $importID)
{
    if ($request->HasErrors()) {
        $popupPage = new PopupPage("company", true);
        $content = $popupPage->Load("block_import_message.html");

        $content->LoadErrorsFromObject($request);
        $result["HTML"] = $popupPage->Grab($content);

        CompanyUnitImport::WriteLog("Import completed", "info", $importID, true);

        //$result = array("Status" => "error", "ErrorList" => $request->GetErrorsAsArray());
        echo json_encode($result);
        exit();
    }
}

echo json_encode($result);
