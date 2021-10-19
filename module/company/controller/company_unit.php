<?php

$user->ValidateAccess(["company_unit" => null, "contract" => null], "or");

$navigation[] = array(
    "Title" => GetTranslation("section-company", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array("FilterTitle", "FilterArchive", "FilterActiveModule", "ItemsOnPage");

if ($request->IsPropertySet("contact_id")) {
    $urlFilter->AppendFromObject($request, array_merge(array('Page', 'company_unit_id'), $filterParams));

    $title = $request->GetProperty("contact_id") > 0 ? GetTranslation("title-contact-edit", $module) : GetTranslation("title-contact-add", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $request->GetProperty("contact_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/inputmask/jquery.inputmask.bundle.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-validation/js/jquery.validate.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/select2/select2.min.js")
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/select2/select2.css")
        )
    );

    $content = $adminPage->Load("contact_edit.html", $header);

    if ($request->GetProperty("contact_id")) {
        $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $request->GetProperty("contact_id");
        Operation::Save($link, "company", "contact_id", $request->GetProperty("contact_id"));
    }

    $contact = new Contact($module);

    if ($request->GetProperty("Save")) {
        $contactForList = $request->GetProperty("contact_for");
        for ($i = 0; $i < count($contactForList); $i++) {
            if ($contactForList[$i] == "invoice") {
                $request->SetProperty("contact_for_invoice", "Y");
            } elseif ($contactForList[$i] == "contract") {
                $request->SetProperty("contact_for_contract", "Y");
            } elseif ($contactForList[$i] == "support") {
                $request->SetProperty("contact_for_support", "Y");
            } elseif ($contactForList[$i] == "service") {
                $request->SetProperty("contact_for_service", "Y");
            } elseif ($contactForList[$i] == "payroll_export") {
                $request->SetProperty("contact_for_payroll_export", "Y");
            } elseif ($contactForList[$i] == "stored_data") {
                $request->SetProperty("contact_for_stored_data", "Y");
            } elseif ($contactForList[$i] == "company_unit_admin") {
                $request->SetProperty("contact_for_company_unit_admin", "Y");
            } elseif ($contactForList[$i] == "employee_admin") {
                $request->SetProperty("contact_for_employee_admin", "Y");
            }
        }
        $contact->LoadFromObject($request);
        if ($contact->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&contact_id=" . $contact->GetProperty("contact_id");
            Operation::Save($link, "company", "contact_id_save", $contact->GetProperty("contact_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            $content->LoadErrorsFromObject($contact);
        }
    } else {
        $contact->LoadByID($request->GetProperty("contact_id"));
    }
    $user->ValidateAccess(array(
        'company_unit' => $contact->GetProperty("company_unit_id"),
        'contract' => $contact->GetProperty("company_unit_id"),
    ), "or");
    $content->LoadFromObject($contact);

    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null, "contract" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }
    if ($user->Validate(array("company_unit" => null)) && !$user->Validate(array("employee" => null))) {
        $content->SetVar("CompanyAdminOnly", 'Y');
    }
    if (
        $user->Validate(array("contract" => null)) &&
        !$user->Validate(array("root", "company_unit" => null, "employee" => null), "or")
    ) {
        $content->SetVar("ContractUserOnly", 'Y');
    }

    //for modifying contact for list
    if ($user->Validate(array("company_unit" => null)) && !$user->Validate(array("employee" => null))) {
        $content->SetVar("CanNotPickEmployeeAdmin", 'Y');
        if ($user->GetProperty("user_id") == $contact->GetProperty("linked_user_id")) {
            $content->SetVar("IsCurrentUser", 'Y');
        }
    }

    $employeeList = array();
    $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($request->GetProperty("company_unit_id"));
    foreach ($employeeIDs as $employeeID) {
        $employeeList[] = array(
            "user_id" => Employee::GetEmployeeField($employeeID, "user_id"),
            "name" => Employee::GetNameByID($employeeID)
        );
    }

    $contactUser = new User();
    $contactUser->LoadByEmail($contact->GetProperty("email"));

    if (!in_array($contactUser->GetProperty("user_id"), array_column($employeeList, "user_id"))) {
        array_unshift($employeeList, array(
            "user_id" => $contactUser->GetProperty("user_id"),
            "name" => $contactUser->GetProperty("first_name") . " " . $contactUser->GetProperty("last_name")
        ));
    }

    $contactList = new ContactList($module);
    $contactList->LoadContactList($request->GetProperty("company_unit_id"));
    $contactList = $contactList->GetItems();

    $unsetKeys = array();
    for ($i = 0; $i < count($employeeList); $i++) {
        if ($employeeList[$i]['user_id'] == $contact->GetProperty('linked_user_id')) {
            $employeeList[$i]['Selected'] = true;
        } elseif (in_array($employeeList[$i]['user_id'], array_column($contactList, "user_id"))) {
            $unsetKeys[] = $i;
        }
    }

    foreach ($unsetKeys as $key) {
        unset($employeeList[$key]);
    }

    $employeeList = array_values($employeeList);
    $content->SetLoop("EmployeeList", $employeeList);

    $content->SetVar("company_unit_id", $request->GetProperty("company_unit_id"));

    $content->SetVar("agreement_of_sending_pdf_invoice", Config::GetConfigValue("agreement_of_sending_pdf_invoice"));
} elseif ($request->IsPropertySet("company_unit_id")) {
    $user->ValidateAccess([
        "company_unit" => $request->GetProperty("company_unit_id"),
        "contract" => $request->GetProperty("company_unit_id")
    ], "or");
    $urlFilter->AppendFromObject($request, array_merge(array('Page'), array('ActiveTab'), $filterParams));

    $title = $request->GetProperty("company_unit_id") > 0 ? GetTranslation("title-company-unit-edit", $module) : GetTranslation("title-company-unit-add", $module);

    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&company_unit_id=" . $request->GetProperty("company_unit_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "JavaScripts" => array(
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/inputmask/jquery.inputmask.bundle.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/jquery-validation/js/jquery.validate.min.js"),
            array("JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"),
            array('JavaScriptFile' => PROJECT_PATH . 'module/' . $module . '/template/js/company_edit.js?
                t=' . filemtime(PROJECT_DIR . 'module/' . $module . '/template/js/company_edit.js'))
        ),
        "StyleSheets" => array(
            array("StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css")
        )
    );

    $content = $adminPage->Load("company_unit_edit.html", $header);
    $content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));
    $content->SetVar("LNG_ActivateMessage", GetTranslation("confirm-activate", "core"));
    $content->SetVar("LNG_CompanyDocumentEmptyName", GetTranslation("contract-empty-name", $module));

    if ($request->GetProperty("company_unit_id")) {
        $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&company_unit_id=" . $request->GetProperty("company_unit_id");
        Operation::Save($link, "company", "company_id", $request->GetProperty("company_unit_id"));
    }

    if ($request->GetProperty("Do") == "RemoveContact" && $request->GetProperty("ContactIDs")) {
        $contactList = new ContactList($module);
        $contactList->Remove($request->GetProperty("ContactIDs"));
        $content->LoadMessagesFromObject($contactList);
        $content->LoadErrorsFromObject($contactList);
        Operation::Save($link, "company", "contact_delete");
    } elseif ($request->GetProperty("Do") == "ExportDatev") {
        $invoiceList = new InvoiceList("billing");
        $invoiceList->ExportToDatev(
            $request->GetProperty("DateFrom"),
            $request->GetProperty("DateTo"),
            $request->GetProperty("company_unit_id")
        );
    } elseif ($request->GetProperty("Do") == "ResetPayroll") {
        $payroll = new Payroll($module);
        if (
            Payroll::PayrollExists(
                $request->GetIntProperty("company_unit_id"),
                $request->GetProperty("reset_payroll_date")
            )
        ) {
            if (
                $payroll->ResetPayroll(
                    $request->GetIntProperty("company_unit_id"),
                    $request->GetProperty("reset_payroll_date")
                )
            ) {
                $payroll->AddMessage("payroll-reset-success", "company");
            } else {
                $payroll->AddError("payroll-reset-fail", "company");
            }
        } else {
            $payroll->AddMessage("payroll-does-not-exist", "company");
        }
        $content->LoadMessagesFromObject($payroll);
        $content->LoadErrorsFromObject($payroll);
    } elseif ($request->GetProperty("Do") == "InvoicePreview") {
        if ($request->GetProperty("invoice_for_period_after")) {
            $period = InvoiceHelper::GetInvoicePeriodAfter(
                $request->GetProperty("invoice_preview_date"),
                $request->GetProperty("payment_type")
            );
        } else {
            $period = InvoiceHelper::GetInvoicePeriodBefore(
                $request->GetProperty("invoice_preview_date"),
                $request->GetProperty("payment_type")
            );
        }

        [$dateFrom, $dateTo] = $period;

        $invoice = new Invoice("billing");
        $invoice->LoadFromArray(array(
            "company_unit_id" => $request->GetIntProperty("company_unit_id"),
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "created" => GetCurrentDate(),
            "for_period_after" => $request->GetProperty("invoice_for_period_after"),
            "invoice_type" => "invoice"
        ));

        if ($request->GetProperty("invoice_preview_date")) {
            $invoice->GenerateInvoicePDF($invoice);
        }
    } elseif ($request->GetProperty("Do") == "InvoiceVoucherPreview") {
        if ($request->GetProperty("invoice_voucher_for_period_after")) {
            $period = InvoiceHelper::GetInvoicePeriodAfter(
                $request->GetProperty("invoice_voucher_preview_date"),
                $request->GetProperty("payment_type")
            );
        } else {
            $period = InvoiceHelper::GetInvoicePeriodBefore(
                $request->GetProperty("invoice_voucher_preview_date"),
                $request->GetProperty("payment_type")
            );
        }

        [$dateFrom, $dateTo] = $period;

        $invoice = new Invoice("billing");
        $invoice->LoadFromArray(array(
            "company_unit_id" => $request->GetIntProperty("company_unit_id"),
            "date_from" => $dateFrom,
            "date_to" => $dateTo,
            "created" => GetCurrentDate(),
            "for_period_after" => $request->GetProperty("invoice_voucher_for_period_after"),
            "invoice_type" => "voucher_invoice"
        ));

        if ($request->GetProperty("invoice_voucher_preview_date")) {
            $invoice->GenerateInvoicePDF($invoice);
        }
    } elseif ($request->GetProperty("Do") == "UploadContractFile") {
        $document = new CompanyUnitDocument($module);
        $document->SetProperty("document_id", $request->GetIntProperty("update_contract_id"));
        $document->SetProperty("company_unit_id", $request->GetIntProperty("company_unit_id"));
        $document->SetProperty("contract_file_name", $request->GetProperty("contract_file_name"));
        $document->Save();
        $content->SetVar("ActiveTab", 5);
        $content->LoadErrorsFromObject($document);
        $content->LoadMessagesFromObject($document);
    } elseif ($request->GetProperty("Do") == "DeleteContractFile") {
        $document = new CompanyUnitDocument($module);
        $document->Remove($request->GetProperty("ContractDocumentID"));
        $content->LoadErrorsFromObject($document);
        $content->LoadMessagesFromObject($document);
    } elseif ($request->GetProperty("Do") == "DeactivateContractFile") {
        $document = new CompanyUnitDocument($module);
        $document->Deactivate($request->GetProperty("ContractDocumentID"));
        $content->LoadErrorsFromObject($document);
        $content->LoadMessagesFromObject($document);
    } elseif ($request->GetProperty("Do") == "ActivateContractFile") {
        $document = new CompanyUnitDocument($module);
        $document->Activate($request->GetProperty("ContractDocumentID"));
        $content->LoadErrorsFromObject($document);
        $content->LoadMessagesFromObject($document);
    } elseif ($request->GetProperty("Do") == "DownloadContractFile") {
        $document = new CompanyUnitDocument($module);
        if ($request->IsPropertySet("HistoryDocumentID")) {
            $fileName = $document->DownloadByHistoryID($request->GetProperty("HistoryDocumentID"));
        } else {
            $document->LoadByID($request->GetProperty("ContractDocumentID"));
            $fileName = $document->GetProperty("value");
        }

        $filePath = COMPANY_UNIT_DOCUMENT_DIR . $fileName;
        OutputFile($filePath, CONTAINER__COMPANY, $fileName);
    } elseif ($request->GetProperty("Do") == "RenameContractFile") {
        $document = new CompanyUnitDocument($module);
        $document->LoadByID($request->GetIntProperty("rename_contract_id"));
        $document->SetProperty("contract_file_name", $request->GetProperty("contract_file_name"));
        $document->SetProperty("rename", $request->GetProperty("rename"));
        $document->Save();
        $content->SetVar("ActiveTab", 5);
        $content->LoadErrorsFromObject($document);
        $content->LoadMessagesFromObject($document);
    } elseif ($request->GetProperty("Do") == "YearlyReport") {
        Operation::Save($link, "company", "yearly_report_generate");

        $report = new YearlyReport($module);
        $report->LoadFromObject($request);
        $report->SetProperty("user_name", $user->GetProperty("first_name") . " " . $user->GetProperty("last_name"));
        $report->SetProperty("user_id", $user->GetProperty("user_id"));
        $report->GenerateReportZIP();
        $request->SetProperty("ActiveTab", 6);
    } elseif ($request->GetProperty("Do") == "GetReport") {
        Operation::Save($link, "company", "yearly_report_download", $request->GetProperty("report_id"));

        $report = new YearlyReport($module);
        $report->LoadByID($request->GetProperty("report_id"));
        $report->OutputZipFile();
    } elseif ($request->GetProperty("Do") == "RemoveReport" && $request->GetProperty("ReportIDs")) {
        Operation::Save($link, "company", "yearly_report_remove");

        $reportList = new YearlyReportList($module);
        $reportList->Remove($request->GetProperty("ReportIDs"));
        $content->LoadMessagesFromObject($reportList);
        $content->LoadErrorsFromObject($reportList);
    } elseif ($request->GetProperty("Do") == "ActivateReport" && $request->GetProperty("ReportIDs")) {
        Operation::Save($link, "company", "yearly_report_activate");

        $reportList = new YearlyReportList($module);
        $reportList->Activate($request->GetProperty("ReportIDs"));
        $content->LoadMessagesFromObject($reportList);
        $content->LoadErrorsFromObject($reportList);
    }

    $companyUnit = new CompanyUnit($module);

    $wasSaved = false;
    if ($request->GetProperty("Save")) {
        $companyUnit->LoadFromObject($request);
        $companyUnit->SetProperty("Link", $link);
        if ($companyUnit->Save()) {
            if (isAjax()) {
                echo json_encode([
                    "result" => "success",
                    "company_unit_id" => $companyUnit->GetProperty("company_unit_id"),
                    "company_id" => $companyUnit->GetProperty("company_id"),
                ]);
                exit(0);
            }

            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&company_unit_id=" . $companyUnit->GetProperty("company_unit_id");
            Operation::Save($link, "company", "company_id_save", $companyUnit->GetProperty("company_unit_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $message->AppendMessagesFromObject($companyUnit);
            $content->LoadMessagesFromObject($message);
            $wasSaved = true;
            //exit();
            $companyUnit->LoadByID($companyUnit->GetProperty("company_unit_id"));
        } else {
            if (isAjax()) {
                echo json_encode([
                    "result" => "error",
                ]);
                exit(0);
            }

            $content->LoadErrorsFromObject($companyUnit);
            $content->LoadErrorFieldsFromObject($companyUnit);
            $content->LoadFromObject($request);
        }
    } else {
        $companyUnit->LoadByID($request->GetProperty("company_unit_id"));
    }

    if ($request->IsPropertySet("DatePayroll")) {
        $datePayroll = date_create($request->GetProperty("DatePayroll"));
        if ($request->GetProperty("Do") == "ExportAddison" && $datePayroll->format("j") == $companyUnit->GetIntProperty("financial_statement_date")) {
            $receiptList = new ReceiptList("receipt");
            $receiptList->ExportToAddison(
                $request->GetProperty("company_unit_id"),
                $request->GetProperty("DatePayroll")
            );
        } elseif ($datePayroll->format("j") != $companyUnit->GetIntProperty("financial_statement_date")) {
            $messageObject = new CommonObject();
            $messageObject->AddMessage("payroll-wrong-date", $module);
            $content->LoadMessagesFromObject($messageObject);
        }
    }

    $user->LoadBySession();
    $request->SetProperty("user_id", $user->GetProperty("user_id"));
    $user->LoadPermissions();
    $permissionList = $user->GetProperty("PermissionList");
    for ($i = 0; $i < count($permissionList); $i++) {
        if ($permissionList[$i]["name"] == "company_unit" && $permissionList[$i]["link_id"]) {
            $companyUnit->SetProperty("IsDepartment", "Y");
            $content->SetVar("OnlyDepartment", true);
        }

        if ($permissionList[$i]["name"] != "root") {
            continue;
        }

        $content->SetVar("Admin", 'Y');
    }

    if ($user->Validate(array("root", "company_unit" => null, "employee" => null, "contract" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    foreach ($user->GetProperty("PermissionList") as $permission) {
        if ($permission["name"] == "root") {
            break;
        }

        if ($permission["name"] == "company_unit") {
            $content->SetVar("IsCompanyUnitAdmin", 'Y');
            break;
        } elseif ($permission["name"] == "contract") {
            $content->SetVar("IsContractUser", 'Y');
            break;
        }
    }

    if (
        $content->GetVar("Admin") == 'Y' ||
        $content->GetVar("IsCompanyUnitAdmin") == 'Y' ||
        $content->GetVar("IsContractUser") == 'Y'
    ) {
        $content->SetVar("ChangingBankingDetails", 'Y');
    }

    $content->LoadFromObject($companyUnit);

    //parent selection
    $parentUnitAvailable = false;
    $companyUnitList = new CompanyUnitList($module);
    $companyUnitList->LoadCompanyUnitListForTree();
    for ($i = 0; $i < $companyUnitList->GetCountItems(); $i++) {
        if ($companyUnitList->_items[$i]["company_unit_id"] == $companyUnit->GetProperty("parent_unit_id")) {
            $companyUnitList->_items[$i]["Selected"] = 1;
            $parentUnitAvailable = true;
        }

        if (
            $companyUnitList->_items[$i]["company_unit_id"] != $companyUnit->GetProperty("company_unit_id") && !strpos(
                $companyUnitList->_items[$i]["path_to_root"],
                "#" . $companyUnit->GetProperty("company_unit_id") . "#"
            )
        ) {
            continue;
        }

        $companyUnitList->_items[$i]["Disabled"] = 1;
    }
    $content->LoadFromObjectList("CompanyUnitList", $companyUnitList);
    $content->SetVar("ParentUnitAvailable", $parentUnitAvailable);

    $contactList = new ContactList($module);
    $contactList->LoadContactList($companyUnit->GetProperty("company_unit_id"));
    $content->LoadFromObjectList("ContactList", $contactList);

    if ($request->GetProperty("company_unit_id") > 0) {
        $companyDocumentList = new CompanyUnitDocumentList($module);
        $companyDocumentList->LoadCompanyUnitDocumentList($request);
        $content->LoadFromObjectList("ContractDocumentList", $companyDocumentList);
        if (CompanyUnitDocument::GetDocumentCount($request->GetProperty("company_unit_id")) >= 10) {
            $content->SetVar("ContractLimitReached", 1);
        }
        $content->SetVar(
            "ContractDocumentPaging",
            $companyDocumentList->GetPagingAsHTML($moduleURL . "&" . $urlFilter->GetForURL(array(
                    "ActiveTab",
                    "Page"
            )) . "&company_unit_id=" . $request->GetProperty("company_unit_id") . "&ActiveTab=5")
        );

        $yearlyReportList = new YearlyReportList($module);
        $yearlyReportList->LoadYearlyReportList($request);
        $content->LoadFromObjectList("ReportList", $yearlyReportList);
        $content->SetVar("ListInfo", GetTranslation(
            'list-info1',
            array('Page' => $yearlyReportList->GetItemsRange(), 'Total' => $yearlyReportList->GetCountTotalItems())
        ));
        $content->SetVar(
            "YearlyReportPaging",
            $yearlyReportList->GetPagingAsHTML($moduleURL . "&" . $urlFilter->GetForURL(array(
                    "ActiveTab",
                    "Page"
            )) . "&company_unit_id=" . $request->GetProperty("company_unit_id") . "&ActiveTab=6")
        );
    }

    //product settings
    $moduleProduct = "product";
    $groupList = new ProductGroupList($moduleProduct);
    $groupList->LoadProductGroupListForAdmin();
    $requestProduct = $request->GetProperty("Product");
    $typesOptionsSelect = array(
        'monthly-yearly-select' => array(OPTION__BENEFIT__MAIN__RECEIPT_OPTION, OPTION__AD__MAIN__RECEIPT_OPTION),
        'monthly-quarterly-yearly-select' => array(OPTION__STORED_DATA__MAIN__FREQUENCY),
        "custom-select" => array_merge(
            array_values(OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO),
            array_values(OPTIONS_VOUCHER_DEFAULT_REASON)
        )
    );
    $typesOptionsTextarea = array(
        'textarea' => array(
            OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
            OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
            OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
            OPTION__MOBILE__MAIN__MOBILE_MODEL,
            OPTION__MOBILE__MAIN__MOBILE_NUMBER
        )
    );

    $hideProductsFromAdmin = array(
        PRODUCT__FOOD__PLAUSIBILITY,
        PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION,
        PRODUCT__FOOD__CANTEEN,
        PRODUCT__FOOD__ADVANCED_SECURITY,
        PRODUCT__FOOD_VOUCHER__ADVANCED_SECURITY,
        PRODUCT__BENEFIT__ADVANCED_SECURITY,
        PRODUCT__BENEFIT_VOUCHER__ADVANCED_SECURITY,
        PRODUCT__INTERNET__ADVANCED_SECURITY,
        PRODUCT__AD__ADVANCED_SECURITY,
        PRODUCT__RECREATION__ADVANCED_SECURITY,
        PRODUCT__MOBILE__ADVANCED_SECURITY,
        PRODUCT__GIFT__ADVANCED_SECURITY,
        PRODUCT__BONUS__ADVANCED_SECURITY,
        PRODUCT__TRANSPORT__ADVANCED_SECURITY,
        PRODUCT__CHILD_CARE__ADVANCED_SECURITY,
        PRODUCT__TRAVEL__ADVANCED_SECURITY,
        PRODUCT__CORPORATE_HEALTH_MANAGEMENT__ADVANCED_SECURITY
    );

    $hideOptionsFromAdmin = array(
        OPTION__BASE__FORCE_APPROVAL,
        OPTION__FOOD__MAIN__UNITS_PER_WEEK,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER,
        OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION,
        OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF,
        OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
        OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
        OPTION__TRAVEL__MAIN__CREDITOR_BOOKING,
    );

    $hideOptionsFromAdmin = array_merge($hideOptionsFromAdmin, OPTIONS_SALARY);

    $hideOptionsFromAdminNotVerificator = array(
        OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED,
        OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION
    );

    $flexOptionList = array_merge(OPTIONS_FLEX_OPTION, OPTIONS_VOUCHER_FLEX_OPTION);
    for ($i = 0; $i < $groupList->GetCountItems(); $i++) {
        $specificProductGroup = SpecificProductGroupFactory::Create($groupList->_items[$i]["group_id"]);
        if ($specificProductGroup == null) {
            continue;
        }
        $mainProductCode = $specificProductGroup->GetMainProductCode();

        $productList = new ProductList($moduleProduct);
        $productList->LoadProductListForAdmin($groupList->_items[$i]["group_id"]);
        for ($j = 0; $j < $productList->GetCountItems(); $j++) {
            if (($content->GetVar('IsCompanyUnitAdmin') == "Y" || $content->GetVar('IsContractUser') == "Y") &&
                in_array($productList->_items[$j]["code"], $hideProductsFromAdmin)) {
                $productList->_items[$j]["hide"] = true;
            }

            if ($productList->_items[$j]["product_id"] == Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)) {
                unset($productList->_items[$j]);
                continue;
            }

            $contract = new Contract($moduleProduct);
            $contract->LoadLatestActiveContract(
                OPTION_LEVEL_COMPANY_UNIT,
                $companyUnit->GetProperty("company_unit_id"),
                $productList->_items[$j]["product_id"]
            );
            if (
                $mainProductCode == Product::GetProductCodeByID($productList->_items[$j]["product_id"])
                && !$contract->IsPropertySet("contract_id")
            ) {
                $groupList->_items[$i]["no_company_contract"] = true;
            }

            if ($contract->GetProperty("end_date") >= date('Y-m-d') || is_null($contract->GetProperty("end_date"))) {
                $productList->_items[$j]["contract_start_date"] = $contract->GetProperty("start_date");
                $productList->_items[$j]["contract_end_date"] = $contract->GetProperty("end_date");
                $productList->_items[$j]["contract_id"] = $contract->GetProperty("contract_id");
            } else {
                $productList->_items[$j]["contract_id"] = $contract->GetProperty("contract_id");
            }

            if (
                $productList->_items[$j]["product_id"] != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION) &&
                !($user->Validate(array("root")) && $contract->GetProperty("start_date") > date('Y-m-d'))
            ) {
                $productList->_items[$j]["disabled_start_date"] = true;
            }

            if (
                $productList->_items[$j]["product_id"] != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION) &&
                !$user->Validate(array("root"))
            ) {
                $productList->_items[$j]["disabled_end_date"] = true;
            }

            if ($requestProduct) {
                foreach ($requestProduct as $key => $value) {
                    if ($key != $productList->_items[$j]["product_id"]) {
                        continue;
                    }

                    if (isset($requestProduct[$key]["end_date"]) && !$wasSaved) {
                        $productList->_items[$j]["contract_end_date"] = $requestProduct[$key]["end_date"];
                    }
                    if (isset($requestProduct[$key]["date_of_params"]) && !$wasSaved) {
                        $productList->_items[$j]["date_of_params"] = $requestProduct[$key]["date_of_params"];
                    }
                    if (isset($requestProduct[$key]["start_date"]) && !$wasSaved) {
                        $productList->_items[$j]["request_start_date"] = true;
                    }
                    if (!isset($requestProduct[$key]["end_date"]) || $wasSaved) {
                        continue;
                    }

                    $productList->_items[$j]["request_end_date"] = true;
                }
            }

            $optionList = new OptionList($moduleProduct);
            $optionList->LoadOptionListForAdmin($productList->_items[$j]["product_id"], OPTION_LEVEL_COMPANY_UNIT);

            for ($k = 0; $k < $optionList->GetCountItems(); $k++) {
                $option = new Option($moduleProduct);
                $option->LoadByID($optionList->_items[$k]["option_id"]);

                $optionValue = Option::GetCurrentValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $optionList->_items[$k]["option_id"],
                    $companyUnit->GetIntProperty("company_unit_id")
                );
                $optionList->_items[$k]["value"] = $optionValue;

                if ($optionValue === null) {
                    $optionList->_items[$k]["inherited_value"] = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $option->GetProperty("code"),
                        $companyUnit->GetIntProperty("company_unit_id"),
                        GetCurrentDate()
                    );
                    $optionList->_items[$k]["inherited_value_source"] = Option::GetInheritableOptionValueSource(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $option->GetProperty("code"),
                        $companyUnit->GetIntProperty("company_unit_id"),
                        GetCurrentDate()
                    );
                    $optionValue = $optionList->_items[$k]["inherited_value"];
                }

                $isDiscount = strpos($option->GetProperty("code"), "monthly_discount") !== false ||
                    strpos($option->GetProperty("code"), "implementation_discount") !== false;
                if (
                    !$user->Validate(array('root')) &&
                    (in_array(
                        $option->GetProperty("code"),
                        $hideOptionsFromAdmin
                    ) || $isDiscount && $optionValue <= 0) ||
                    !$user->Validate(array("root", "receipt"), "or") && in_array(
                        $option->GetProperty("code"),
                        $hideOptionsFromAdminNotVerificator
                    )
                ) {
                    $optionList->_items[$k]["hide"] = true;
                    if (
                        !empty($optionList->_items[$k]["show_group"])
                        && $optionList->_items[$k]["show_group"]
                        && isset($optionList->_items[$k + 1])
                        && $optionList->_items[$k]["group_id"] == $optionList->_items[$k + 1]["group_id"]
                    ) {
                        $optionList->_items[$k + 1]["show_group"] = 1;
                    }
                    $optionList->_items[$k]["show_group"] = 0;
                } else {
                    $optionList->_items[$k]["hide"] = false;
                }

                if (($content->GetVar('IsCompanyUnitAdmin') == "Y" || $content->GetVar('IsContractUser') == "Y") &&
                    $option->GetProperty("code") == OPTION__BASE__FORCE_APPROVAL) {
                    $optionList->_items[$k]["show_group"] = false;
                }

                if (
                    !$user->Validate(array("root"))
                    && $optionList->_items[$k]["group_id"] == 1
                    && !in_array($optionList->_items[$k]["code"], $flexOptionList)
                ) {
                    $optionList->_items[$k]["disabled"] = true;
                }

                foreach ($typesOptionsSelect as $key => $value) {
                    if (!in_array($optionList->_items[$k]["code"], $value)) {
                        continue;
                    }

                    $optionList->_items[$k]["type"] = $key;
                }

                foreach ($typesOptionsTextarea as $key => $value) {
                    if (!in_array($optionList->_items[$k]["code"], $value)) {
                        continue;
                    }

                    $optionList->_items[$k]["type"] = $key;
                }

                if ($requestProduct && !$wasSaved) {
                    foreach ($requestProduct as $key => $value) {
                        if ($key != $productList->_items[$j]["product_id"]) {
                            continue;
                        }

                        foreach ($value['Option'] as $optionKey => $optionValue) {
                            if ($optionKey != $optionList->_items[$k]["option_id"] || strlen($optionValue) <= 0) {
                                continue;
                            }

                            $optionValue = preg_match("/[,]/", $optionValue) ? preg_replace("/[^0-9,]/", "", $optionValue) : preg_replace("/[^0-9.]/", "", $optionValue);

                            $optionValue = str_replace(",", ".", $optionValue);
                            if (is_int($optionValue)) {
                                $optionValue = floatval($optionValue);
                            }
                            $optionList->_items[$k]["value"] = $optionValue;
                        }
                    }
                }

                if (in_array($optionList->_items[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO)) {
                    $optionList->_items[$k]["category_preference_select"] =
                        Option::GetOptionIDByCode(OPTIONS_VOUCHER_DEFAULT_REASON[$groupList->_items[$i]["code"]]);
                    $optionList->_items[$k]["inherited_value_no_translation"] =
                        $optionList->_items[$k]["inherited_value"];
                    $optionList->_items[$k]["SelectList"] =
                        Option::GetVoucherReasonScenarioList($optionList->_items[$k]["value"]);

                    $voucherScenario = $optionList->_items[$k]["value"] ?? $optionList->_items[$k]["inherited_value"];
                    $optionList->_items[$k]["inherited_value"] = GetTranslation(
                        "voucher-category-scenario-" . $optionList->_items[$k]["inherited_value"],
                        $module
                    );
                }

                if (!in_array($optionList->_items[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON)) {
                    continue;
                }

                $optionList->_items[$k]["SelectList"] = Voucher::GetVoucherReasonList(
                    $optionList->_items[$k]["value"],
                    "voucher_sets_of_goods"
                );

                if (empty($voucherScenario)) {
                    $voucherScenario = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$groupList->_items[$i]["code"]],
                        $request->GetProperty("company_unit_id"),
                        GetCurrentDate() //this option is not dependant on date and time (task #4009)
                    );
                }
                if ($voucherScenario == "exchangeable") {
                    $optionList->_items[$k]["disabled"] = true;
                }
                if (!isset($optionList->_items[$k]["inherited_value"])) {
                    if ($content->GetVar("Admin") != 'Y') {
                        unset($optionList->_items[$k]["SelectList"][0]);
                        $optionList->_items[$k]["SelectList"] = array_values($optionList->_items[$k]["SelectList"]);
                    }

                    continue;
                }

                $key = $optionList->_items[$k]["inherited_value"];
                $optionList->_items[$k]["inherited_value"]
                    = $optionList->_items[$k]["SelectList"][$key]["Reason"];
                if ($content->GetVar("Admin") == 'Y') {
                    continue;
                }

                unset($optionList->_items[$k]["SelectList"][0]);
                $optionList->_items[$k]["SelectList"] = array_values($optionList->_items[$k]["SelectList"]);
            }
            $productList->_items[$j]["OptionList"] = $optionList->GetItems();
            $productList->_items[$j]["Partner"] = $contract->GetProperty("Partner");
        }
        $groupList->_items[$i]["ProductList"] = $productList->GetItems();
    }
    $groupListInBenefitGrouping = array();
    $groupListBase = array();
    $groupListTravel = array();
    $groupListGivve = array();
    $groupListStoredData = array();

    foreach ($groupList->_items as $key => $item) {
        switch ($item["code"]) {
            case PRODUCT_GROUP__BASE:
                $groupListBase = $item;
                break;
            case PRODUCT_GROUP__TRAVEL:
                $groupListTravel = $item;
                break;
            case PRODUCT_GROUP__GIVVE:
                $groupListGivve = $item;
                break;
            case PRODUCT_GROUP__STORED_DATA:
                $groupListStoredData = $item;
                break;
            default:
                $groupListInBenefitGrouping['BenefitGrouping'][] = $item;
        }
    }
    $resultGroupList = $groupList;
    $resultGroupList->_items = [
        $groupListBase,
        $groupListInBenefitGrouping,
        $groupListTravel,
        $groupListGivve,
        $groupListStoredData,
    ];
    $content->LoadFromObjectList("ProductGroupList", $resultGroupList);

    // app logos
    $content->SetLoop("CompanyAppLogoBaseParamList", $companyUnit->GetImageParams("app_logo"));
    $content->SetLoop("CompanyAppLogoMiniParamList", $companyUnit->GetImageParams("app_logo_mini"));
    $content->SetLoop("CompanyVoucherLogoParamList", $companyUnit->GetImageParams("voucher_logo"));

    if ($archiveInfo = $companyUnit->LoadArchiveInfo()) {
        $messageObject = new CommonObject();
        foreach ($archiveInfo as $line) {
            if ($line['created_from'] == "web_api") {
                $message = $line['value'] == "Y" ? "user-archive-y-message-web-api" : "user-archive-n-message-web-api";
            } else {
                $message = $line['value'] == "Y" ? "user-archive-y-message" : "user-archive-n-message";
            }


            $messageObject->AddMessage(
                $message,
                null,
                array(
                    'entity' => GetTranslation("entity-company_unit"),
                    'username' => $line['username'],
                    'datetime' => (new DateTime($line['created']))->format("H:i:s d M Y")
                )
            );
        }
        $content->SetLoop("ArchiveMessage", $messageObject->GetMessagesAsArray());
    }
    $content->SetVar("DateOfParams", date("Y-m-d"));

    //statistics of voucher services
    $voucherProductGroups = ProductGroupList::GetProductGroupList(false, "Y");

    for ($i = 0; $i < count($voucherProductGroups); $i++) {
        $voucherProductGroups[$i]["statistics-translation"] = GetTranslation(
            $voucherProductGroups[$i]["code"] . "-statistics",
            $module
        );
        $voucherProductGroups[$i]["yearly-statistics-translation"] = GetTranslation(
            $voucherProductGroups[$i]["code"] . "-yearly-statistics",
            $module
        );
    }

    $content->SetLoop("VoucherProductGroupList", $voucherProductGroups);
    $content->SetVar("ActiveTab", $request->GetProperty("ActiveTab"));
} else {
    $urlFilter->AppendFromObject($request, $filterParams);

    $header = array(
        "Title" => GetTranslation("section-company", $module),
        "Navigation" => $navigation,
        "JavaScripts" => array(array("JavaScriptFile" => ADMIN_PATH . "template/plugins/tree-table/javascript.js"))
    );

    $content = $adminPage->Load("company_unit_list.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "company", "company_list");

    if ($request->GetProperty('Do') == 'RemoveCompany' && $request->GetProperty("CompanyIDs")) {
        $companyList = new CompanyList($module);
        $companyList->Remove($request->GetProperty("CompanyIDs"));
        $content->LoadMessagesFromObject($companyList);
        $content->LoadErrorsFromObject($companyList);
    } else {
        if ($request->GetProperty('Do') == 'RemoveCompanyUnit' && $request->GetProperty("CompanyUnitIDs")) {
            $companyUnitList = new CompanyUnitList($module);
            $companyUnitList->Remove($request->GetProperty("CompanyUnitIDs"));
            $content->LoadMessagesFromObject($companyUnitList);
            $content->LoadErrorsFromObject($companyUnitList);
            Operation::Save($link, "company", "company_delete");
        } else {
            if ($request->GetProperty('Do') == 'ActivateCompanyUnit' && $request->GetProperty("CompanyUnitIDs")) {
                $companyUnitList = new CompanyUnitList($module);
                $companyUnitList->Activate($request->GetProperty("CompanyUnitIDs"));
                $content->LoadMessagesFromObject($companyUnitList);
                $content->LoadErrorsFromObject($companyUnitList);
                Operation::Save($link, "company", "company_activate");
            } else {
                if ($request->GetProperty('Do') == 'RemoveImportedData' && $request->GetProperty("Template")) {
                    echo "Done";
                    die();
                }
            }
        }
    }

    //load filter data from session and to session
    $session = GetSession();
    foreach ($filterParams as $key) {
        if ($session->IsPropertySet("Company" . $key) && !$request->IsPropertySet($key)) {
            $request->SetProperty($key, $session->GetProperty("Company" . $key));
        } else {
            $session->SetProperty("Company" . $key, $request->GetProperty($key));
        }
    }
    $session->SaveToDB();

    $companyList = new CompanyList($module);
    $companyList->LoadCompanyList($request);
    $isArchive = $request->IsPropertySet("FilterArchive") ? $request->GetProperty("FilterArchive") : "N";

    for ($i = 0; $i < $companyList->GetCountItems(); $i++) {
        $companyUnitList = new CompanyUnitList($module);
        $companyUnitList->LoadCompanyUnitListForTree(
            $companyList->_items[$i]["company_id"],
            ["company_unit", "contract"],
            $isArchive,true, false, null, "or");

        for ($j = 0; $j < $companyUnitList->GetCountItems(); $j++) {
            $contactList = new ContactList($module);
            $contactList->LoadContactList($companyUnitList->_items[$j]["company_unit_id"]);
            $companyUnitList->_items[$j]["ContactList"] = $contactList->GetItems();
        }
        $companyList->_items[$i]["CompanyUnitList"] = $companyUnitList->GetItems();
    }
    $content->LoadFromObjectList("CompanyList", $companyList);

    //Product group list
    $productList = array();
    $productGroupList = new ProductGroupList("product");
    $productGroupList->LoadProductGroupListForAdmin();
    foreach ($productGroupList->_items as $productGroup) {
        $productListObject = new ProductList("product");
        $productListObject->LoadProductListForAdmin($productGroup['group_id']);
        $keySelected = array_search(
            $request->GetProperty("FilterActiveModule"),
            array_column($productListObject->_items, "product_id")
        );
        if ($keySelected !== false) {
            $productListObject->_items[$keySelected]["selected"] = "Y";
        }

        $productGroup["ProductList"] = $productListObject->_items;
        $productList[] = $productGroup;
    }
    $content->SetLoop("ProductGroupList", $productList);

    //Append global statistics (used mobile employees and employees with base module's contracts)
    $companyUnitList = new CompanyUnitList($module);
    $companyUnitList->LoadCompanyUnitLinearList($request, true);
    $content->SetVar("EmployeesAll", array_sum(array_column($companyUnitList->GetItems(), "employees_all")));
    $content->SetVar(
        "EmployeesUsedMobile",
        array_sum(array_column($companyUnitList->GetItems(), "employees_used_mobile"))
    );
    $content->SetVar(
        "EmployeesWithActiveBaseContract",
        array_sum(array_column($companyUnitList->GetItems(), "employees_with_active_base_contract"))
    );

    $content->SetVar("Paging", $companyList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
    $content->SetVar("ListInfo", GetTranslation(
        'list-info1',
        array('Page' => $companyList->GetItemsRange(), 'Total' => $companyList->GetCountTotalItems())
    ));
    $content->SetVar("ParamsForFilter", $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
    $content->SetVar("ParamsForItemsOnPage", $urlFilter->GetForForm(array("ItemsOnPage", "FilterActiveModule")));
    $content->LoadFromObject($request, $filterParams);
    if (!$request->IsPropertySet("FilterArchive")) {
        $content->SetVar("FilterArchive", "N");
    }
    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(array("root", "company_unit" => null, "employee" => null, "contract" => null), "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }
    if (
        $user->Validate(array("contract" => null)) &&
        !$user->Validate(array("root", "company_unit" => null, "employee" => null), "or")
    ) {
        $content->SetVar("ContractUserOnly", 'Y');
    }

    $itemsOnPageList = array();
    foreach (array(10, 20, 50, 100, 0) as $v) {
        $itemsOnPageList[] = array("Value" => $v, "Selected" => $v == $companyList->GetItemsOnPage() ? 1 : 0);
    }
    $content->SetLoop("ItemsOnPageList", $itemsOnPageList);
}
