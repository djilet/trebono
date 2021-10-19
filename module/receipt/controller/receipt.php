<?php

$user->ValidateAccess(["receipt" => null, "tax_auditor" => null, "payroll" => null], "or");
$navigation[] = array(
    "Title" => GetTranslation("section-receipt", $module),
    "Link" => $moduleURL . "&" . $urlFilter->GetForURL()
);

$filterParams = array(
    "FilterLegalReceiptID",
    "FilterCreditorNumber",
    "FilterVoucherID",
    "FilterCreatedRange",
    "FilterStatus",
    "UserReceiptConfirm",
    "FilterName",
    "FilterCompanyTitle",
    "FilterProductGroup",
    "FilterArchive",
    "FilterTripID",
    "FilterHasUnreadMessagesAdmin",
    "FilterHasUnreadMessagesEmployee",
    "FilterHasChat",
    "FilterAutomaticProcessed",
    "ItemsOnPage",
    "FilterNotBooked",
    "FilterUserLastChangedStatus",
);

if ($request->IsPropertySet("config_version_id")) {
    $configHistory = Config::GetConfigHistoryValue($request->GetProperty("config_version_id"));
    $config = new Config();
    $config->LoadByID($configHistory["config_id"]);

    $title = GetTranslation("title-config-value-view-" . $config->GetProperty("code"), $module);
    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&config_version_id=" . $request->GetProperty("config_version_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
    );

    $content = $adminPage->Load("config_history_value.html", $header);
    $content->LoadFromArray($configHistory);
    $content->SetVar("config_title_translation", $config->GetProperty("title_translation"));
} elseif ($request->IsPropertySet("line_id")) {
    $urlFilter->AppendFromObject($request, array_merge(['Page', 'receipt_id'], $filterParams));

    $title = GetTranslation("title-receipt-line-edit", $module);
    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&line_id=" . $request->GetProperty("line_id")
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
    );

    $content = $adminPage->Load("receipt_line_edit.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&line_id=" . $request->GetProperty("line_id");
    Operation::Save($link, "receipt", "receipt_line_id", $request->GetProperty("line_id"));

    $receiptLine = new ReceiptLine($module);

    if (!Receipt::ValidateAccess($request->GetProperty("receipt_id"))) {
        Send403();
    }

    if ($request->GetProperty("Save")) {
        $receiptLine->LoadFromObject($request);
        if ($receiptLine->Save()) {
            //header("Location: ".$moduleURL."&".$urlFilter->GetForURL());
            Operation::Save($link, "receipt", "receipt_line_id_save", $request->GetProperty("line_id"));

            $message = new CommonObject();
            $message->AddMessage("saved");
            $content->LoadMessagesFromObject($message);
            //exit();
        } else {
            $content->LoadErrorsFromObject($receiptLine);
        }
    } else {
        $receiptLine->LoadByID($request->GetProperty("line_id"));
    }

    $content->LoadFromObject($receiptLine);
} elseif ($request->IsPropertySet("receipt_file_id") && $request->GetProperty("Action") == "BrokeImage") {
    $receiptFile = new ReceiptFile($module);
    $receiptFile->LoadByID($request->GetProperty("receipt_file_id"));

    $receipt->LoadByID($receiptFile->GetProperty("receipt_id"));

    $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));

    $fileStorage = GetFileStorage($specificProductGroup->GetContainer());

    $im = imagecreatefromstring($fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile->GetProperty("file_image")));
    $rotated = imagerotate($im, 45, 0);
    imagejpeg($rotated);
    $contentRotated = ob_get_clean();

    $fileStorage->PutFileContent(
        RECEIPT_IMAGE_DIR . "file/" . $receiptFile->GetProperty("file_image"),
        $contentRotated
    );

    echo json_encode([
        "result" => "success",
    ]);
    exit(0);
} elseif ($request->IsPropertySet("receipt_id") && $request->GetProperty("Action") == "GetEvidencePack") {
    $receipt = new Receipt($module);
    $receipt->LoadByID($request->GetProperty("receipt_id"));

    if (!Receipt::ValidateAccess($request->GetProperty("receipt_id"))) {
        Send403();
    }

    $receipt->CreateAndOutputEvidencePack();
} elseif ($receiptId = $request->GetProperty("receipt_id")) {
    if (!Receipt::ValidateAccess($receiptId)) {
        Send403();
    }

    $urlFilter->AppendFromObject($request, array_merge(['Page'], $filterParams));

    $title = GetTranslation("title-receipt-edit", $module);
    $navigation[] = array(
        "Title" => $title,
        "Link" => $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_id=" . $receiptId
    );
    $header = array(
        "Title" => $title,
        "Navigation" => $navigation,
        "StyleSheets" => array(
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datetimepicker/css/datetimepicker.css"],
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/datepicker/css/datepicker.css"],
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
        ),
        "JavaScripts" => array(
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datetimepicker/js/datetimepicker.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datetimepicker/js/locales/bootstrap-datetimepicker.de.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/datepicker/js/datepicker.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/autosize/autosize.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
        )
    );

    $content = $adminPage->Load("receipt_edit.html", $header);

    $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_id=" . $receiptId;

    if (
        $user->Validate(["tax_auditor" => null])
        || (
            $user->Validate([
                "payroll" => null, "employee" => null
            ], "and")
            && !$user->Validate([
                "root" => null, "receipt" => null, "tax_auditor" => null, "service" => null
            ], "or")
        )
    ) {
        $content->SetVar("close", 1);
        $content->SetVar("tax_auditor", 1);
    }

    //receipt saving
    $receipt = new Receipt($module);
    $receipt->LoadByID($receiptId);

    if ($receipt->GetProperty('is_web_upload')) {
        $content->SetVar('isWebUpload', 1);
    }

    if ($request->GetProperty("Save")) {
        //Append instead of Load to keep loaded fields like employee_id
        $receipt->AppendFromObject($request);

        if ($request->GetProperty("booked") != "Y") {
            $receipt->SetProperty("booked", "N");
        }

        if ($receipt->Update()) {
            Operation::Save(
                $link,
                "receipt",
                "receipt_id_update" .
                ($request->GetProperty("next_receipt_id") ? "_next_receipt" : "_save"),
                $receipt->GetProperty("legal_receipt_id")
            );
            if ($request->GetProperty("next_receipt_id")) {
                header("Location: " . $moduleURL . "&" . $urlFilter->GetForURL() . "&receipt_id=" . $request->GetProperty("next_receipt_id"));
                exit();
            }
            $content->SetVar("Saved", 1);
            $message = new CommonObject();
            $message->AddMessage("receipt-saved", $module);
            $content->LoadMessagesFromObject($message);
        } else {
            $content->LoadErrorsFromObject($receipt);
        }
    } elseif ($request->GetProperty("ApproveReceipt") && $user->Validate(["root"])) {
        if ($receipt->ApproveByEmployee()) {
            Operation::Save($link, "receipt", "receipt_id_update", $receipt->GetProperty("legal_receipt_id"));
            $content->SetVar("Saved", 1);
            $message = new CommonObject();
            $message->AddMessage("receipt-saved", $module);
            $content->LoadMessagesFromObject($message);
            $receipt->LoadByID($receiptId);
        } else {
            $content->LoadErrorsFromObject($receipt);
        }
    } else {
        Operation::Save($link, "receipt", "receipt_id", $receipt->GetProperty("legal_receipt_id"));
        $statusReceipt = new Receipt($module);
        $statusReceipt->LoadByID($receiptId);
        $statusReceipt->StartReview();
    }

    //block receipt edition if its status is approved/denied
    //temporarily commented due to #2007
    //if($receipt->GetProperty("status") == "approved" || $receipt->GetProperty("status") == "denied")
    //$receipt->SetProperty("close", true);

    if ($receipt->GetProperty("datev_export") != 0 || $receipt->GetProperty("creditor_export_id") != null) {
        $receipt->SetProperty("close", true);
    }

    $content->LoadFromObject($receipt);

    //receipt's employee info
    $employee = new Employee($module);
    $employee->LoadByID($receipt->GetIntProperty("employee_id"));
    foreach ($employee->GetProperties() as $key => $value) {
        $content->SetVar("EMPLOYEE_" . $key, $value);
    }
    if ($receipt->GetProperty("owner_id") == $employee->GetProperty("user_id")) {
        $content->SetVar("is_owner", 1);
    }

    $productGroup = new ProductGroup("product");
    $productGroup->LoadByID($receipt->GetProperty("group_id"));
    $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));

    //specific tab order for voucher services
    if ($productGroup->GetProperty("voucher") == "Y") {
        $content->SetVar("IsVoucher", 1);
    }

    //additional field for all product group
    $optionCode = OPTIONS_INTERNAL_VERIFICATION_INFO[$specificProductGroup->GetMainProductCode()];
    $optionValue = Option::GetInheritableOptionValue(
        OPTION_LEVEL_EMPLOYEE,
        $optionCode,
        $receipt->GetIntProperty("employee_id"),
        GetCurrentDate()
    );
    $content->SetVar("EMPLOYEE_internal_verification_info", $optionValue);
    $content->SetVar("EMPLOYEE_internal_verification_info_id", Option::GetOptionIDByCode($optionCode));

    //additional fields specific for selected product group
    $setsOfGoodsServices = [PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__BONUS_VOUCHER];
    if ($productGroup->GetProperty("code") == PRODUCT_GROUP__TRAVEL) {
        $content->SetVar("ShowBooked", 1);
        $content->SetVar("ShowTravel", 1);
        $content->SetVar("ShowCurrency", 1);
        $content->SetVar("ShowVAT", 1);
        $dailyAllowance = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
            $employee->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        if ($receipt->GetProperty("receipt_from") == "meal" && $dailyAllowance == "Y") {
            $content->SetVar("TravelDailyAllowanceOn", 1);
        }
        if (!$receipt->IsPropertySet("vat")) {
            $content->SetVar("vat", 19);
        }

        //currency list
        $currencyList = new CurrencyList();
        $currencyList->LoadCurrencyList($receipt->GetProperty("currency_id"));
        for ($i = 0; $i < $currencyList->GetCountItems(); $i++) {
            if ($currencyList->_items[$i]["id"] != $receipt->GetProperty("currency_id")) {
                continue;
            }

            $currencyList->_items[$i]["selected"] = 1;
        }
        $content->LoadFromObjectList("CurrencyList", $currencyList);
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__BENEFIT) {
        $content->SetVar("ShowDocumentDateFromField", 1);
        $content->SetVar("ShowDocumentDateToField", 1);
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__RECREATION) {
        $withPicture = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
            $receipt->GetIntProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        $content->SetVar("ConfirmationWithPicture", $withPicture);
        if ($withPicture == "N" && $receipt->GetProperty("status") == "denied") {
            $content->SetVar("close", 1);
        }

        $historyProperty = Employee::GetPropertyHistoryValueEmployee(
            "material_status",
            $receipt->GetIntProperty("employee_id"),
            $receipt->GetProperty("created")
        );

        if ($historyProperty["value"] == "single") {
            $content->SetVar("RECEIPT_material_status", GetTranslation("material-status-single", "company"));
        }
        if ($historyProperty["value"] == "married") {
            $content->SetVar("RECEIPT_material_status", GetTranslation("material-status-married", "company"));
        }

        $historyProperty = Employee::GetPropertyHistoryValueEmployee(
            "child_count",
            $receipt->GetIntProperty("employee_id"),
            $receipt->GetProperty("created")
        );
        $content->SetVar("RECEIPT_child_count", $historyProperty["value"]);
    } elseif (in_array($productGroup->GetProperty("code"), $setsOfGoodsServices)) {
        $result = Receipt::GetSetsOfGoodsList($receipt, true);
        $content->SetLoop("SetsOfGoodsList", $result["sets_of_goods"]);
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__INTERNET) {
        $contractualInfo = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
            $receipt->GetIntProperty("employee_id"),
            GetCurrentDate()
        );
        $content->SetVar("EMPLOYEE_" . OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION, $contractualInfo);
        $content->SetVar(
            "EMPLOYEE_" . OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION . "_id",
            Option::GetOptionIDByCode(OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION)
        );
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__MOBILE) {
        $optionCodes = array(
            OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
            OPTION__MOBILE__MAIN__MOBILE_MODEL,
            OPTION__MOBILE__MAIN__MOBILE_NUMBER
        );

        foreach ($optionCodes as $optionCode) {
            $optionValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                $optionCode,
                $receipt->GetIntProperty("employee_id"),
                GetCurrentDate()
            );
            $content->SetVar("EMPLOYEE_" . $optionCode, $optionValue);
            $content->SetVar("EMPLOYEE_" . $optionCode . "_id", Option::GetOptionIDByCode($optionCode));
        }
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__FOOD) {
        $optionValue = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__IMPORTANT_INFO,
            $receipt->GetIntProperty("employee_id"),
            GetCurrentDate()
        );
        $content->SetVar("EMPLOYEE_" . OPTION__FOOD__MAIN__IMPORTANT_INFO, $optionValue);
        $content->SetVar(
            "EMPLOYEE_" . OPTION__FOOD__MAIN__IMPORTANT_INFO . "_id",
            Option::GetOptionIDByCode(OPTION__FOOD__MAIN__IMPORTANT_INFO)
        );
    } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__FOOD_VOUCHER) {
        $optionValue = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO,
            $receipt->GetIntProperty("employee_id"),
            GetCurrentDate()
        );
        $content->SetVar("EMPLOYEE_" . OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO, $optionValue);
        $content->SetVar(
            "EMPLOYEE_" . OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO . "_id",
            Option::GetOptionIDByCode(OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO)
        );
    }

    //show line list only to root
    $productGroupCodes = [PRODUCT_GROUP__TRAVEL];
    if (in_array($productGroup->GetProperty("code"), $productGroupCodes) && !$user->Validate(["root"])) {
        $content->SetVar("HiddenLineList", 1);
    }
    if ($productGroup->GetProperty("voucher") == "Y") {
        $content->SetVar("ShowCurrency", 1);
        $content->SetVar("ShowVAT", 1);
        $content->SetVar("MoveRealAmountApproved", 1);

        //currency list
        $currencyList = new CurrencyList();
        $currencyList->LoadCurrencyList($receipt->GetProperty("currency_id"));
        for ($i = 0; $i < $currencyList->GetCountItems(); $i++) {
            if ($currencyList->_items[$i]["id"] == $receipt->GetProperty("currency_id")) {
                $currencyList->_items[$i]["selected"] = 1;
            }
        }
        $content->LoadFromObjectList("CurrencyList", $currencyList);
    }

    //product group selection
    $productGroupList = new ProductGroupList("product");
    $productGroupList->LoadProductGroupListForAdmin(true);
    for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
        if ($productGroupList->_items[$i]["group_id"] != $receipt->GetProperty("group_id")) {
            continue;
        }

        $productGroupList->_items[$i]["Selected"] = 1;
        if ($productGroupList->_items[$i]['code'] != PRODUCT_GROUP__FOOD) {
            continue;
        }

        $contract = new Contract("product");
        $contract->LoadLatestActiveContract(
            OPTION_LEVEL_EMPLOYEE,
            $receipt->GetIntProperty("employee_id"),
            Product::GetProductIDByCode("food__weekly_shopping")
        );
        $content->SetVar("WeeklyPurchase", $contract->GetProperty("contract_id"));
    }
    $content->LoadFromObjectList("ProductGroupList", $productGroupList);

    //receipt type selection
    $receiptTypeList = new ReceiptTypeList("product");
    $receiptTypeList->LoadReceiptTypeListForProductGroup($receipt->GetProperty("group_id"));
    $existInSelect = false;
    foreach ($receiptTypeList->_items as $key => $receiptType) {
        if ($receiptType["code"] != $receipt->GetProperty("receipt_from")) {
            continue;
        }

        $receiptTypeList->_items[$key]["selected"] = 1;
        $existInSelect = true;
    }
    if (!$existInSelect) {
        $receiptType = new ReceiptType("product");
        if ($receiptType->LoadByCode($receipt->GetProperty("receipt_from"))) {
            $receiptType->SetProperty("selected", 1);
            $receiptTypeList->_items[] = $receiptType->GetProperties();
        }
    }
    $content->LoadFromObjectList("ReceiptTypeList", $receiptTypeList);

    //load receipt file list
    $receiptFileList = new ReceiptFileList($module);
    $receiptFileList->LoadFileList($request->GetProperty("receipt_id"));
    $content->LoadFromObjectList("ReceiptFileList", $receiptFileList);
    $content->SetVar("ReceiptFileListCount", $receiptFileList->GetCountItems());

    //load filter data from session
    $session = GetSession();
    foreach ($filterParams as $key) {
        if (!$session->IsPropertySet("Receipt" . $key)) {
            continue;
        }

        $request->SetProperty($key, $session->GetProperty("Receipt" . $key));
    }
    if ($user->Validate(["root"])) {
        $content->SetVar("Admin", 'Y');
    }
    if ($user->Validate(["root", "company_unit" => null, "employee" => null], "or")) {
        $content->SetVar("HistoryAdmin", 'Y');
    }

    //load denial reason list for popup
    if (
        $productGroup->GetProperty("code") == PRODUCT_GROUP__TRAVEL &&
        $user->Validate(["receipt" => null]) &&
        !$user->Validate(["root"])
    ) {
        $content->SetVar("HideDenialReasons", 'Y');
    } else {
        $content->SetLoop("DenialReasonList", Receipt::GetDenialReasonList());
    }

    //we should save next_receipt_id generated before current receipt is changed (receipt processing can change its position in receipt list)
    if (!$request->IsPropertySet("next_receipt_id")) {
        $receiptList = new ReceiptList($module);
        $content->SetVar("next_receipt_id", $receiptList->GetNextReceiptID($request));
    }

    $confirmationEmployee = new ConfirmationEmployee($module);
    $confirmationEmployee->LoadByReceiptID($receiptId);
    if ($pdfLink = $confirmationEmployee->GetPdfLink($employee->GetIntProperty('company_unit_id'))) {
        $content->SetVar("ConfirmationEmployeePDFLink", $pdfLink);
    }
} else {
    $header = [
        "Title" => GetTranslation("section-receipt", $module),
        "Navigation" => $navigation,
        "StyleSheets" => array(
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/daterangepicker/css/daterangepicker-bs3.css"],
            ["StyleSheetFile" => ADMIN_PATH . "template/plugins/typeahead/css/typeahead.css"]
        ),
        "JavaScripts" => array(
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/moment.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/daterangepicker/js/daterangepicker.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/handlebars.min.js"],
            ["JavaScriptFile" => ADMIN_PATH . "template/plugins/typeahead/typeahead.bundle.js"]
        )
    ];

    $content = $adminPage->Load("receipt_list.html", $header);
    $content->SetVar("LNG_RemoveMessage", GetTranslation("confirm-remove", "core"));
    $content->SetVar("LNG_ActivateMessage", GetTranslation("confirm-activate", "core"));

    $link = $moduleURL . "&" . $urlFilter->GetForURL();
    Operation::Save($link, "receipt", "receipt_list");

    $companyUnitList = new CompanyUnitList($module);
    $companyUnitList->LoadCompanyUnitListForTree(null, "employee");
    $content->LoadFromObjectList("CompanyUnitList", $companyUnitList);

    if ($request->GetProperty('Do') == 'Remove' && $request->GetProperty("ReceiptIDs")) {
        $receiptList = new ReceiptList($module);
        $receiptList->Remove($request->GetProperty("ReceiptIDs"));
        $content->LoadMessagesFromObject($receiptList);
        $content->LoadErrorsFromObject($receiptList);
        Operation::Save($link, "receipt", "receipt_delete");
    }
    if ($request->GetProperty('Do') == 'Activate' && $request->GetProperty("ReceiptIDs")) {
        $receiptList = new ReceiptList($module);
        $receiptList->Activate($request->GetProperty("ReceiptIDs"));
        $content->LoadMessagesFromObject($receiptList);
        $content->LoadErrorsFromObject($receiptList);
        Operation::Save($link, "receipt", "receipt_activate");
    }

    $travelProductGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL);
    $content->SetVar("TravelGroupID", $travelProductGroupID);
    //product group selection
    $filterProductGroup = new ProductGroupList("product");
    $filterProductGroup->LoadProductGroupListForAdmin(true);
    for ($i = 0; $i < $filterProductGroup->GetCountItems(); $i++) {
        if ($filterProductGroup->_items[$i]['group_id'] != $request->GetIntProperty("FilterProductGroup") && $filterProductGroup->GetCountItems() != 1) {
            continue;
        }

        $filterProductGroup->_items[$i]['Selected'] = true;
    }
    $content->SetLoop("ProductGroupList", $filterProductGroup->GetItems());

    //load filter data from session and to session
    $session = GetSession();
    foreach ($filterParams as $key) {
        if ($key == "ItemsOnPage") {
            if ($request->IsPropertySet($key)) {
                $session->SetProperty("Receipt" . $key, $request->GetProperty($key));
            } else {
                $request->SetProperty($key, $session->GetProperty("Receipt" . $key));
            }
        } elseif ($key == "FilterTripID" || $key == "FilterNotBooked") {
            if ($request->GetProperty("FilterProductGroup") == $travelProductGroupID) {
                if ($request->GetProperty("FilterSubmitted")) {
                    $session->SetProperty("Receipt" . $key, $request->GetProperty($key));
                } else {
                    $request->SetProperty($key, $session->GetProperty("Receipt" . $key));
                }
            } else {
                $session->SetProperty("Receipt" . $key, "");
            }
        } else {
            if ($request->GetProperty("FilterSubmitted")) {
                $session->SetProperty("Receipt" . $key, $request->GetProperty($key));
            } else {
                $request->SetProperty($key, $session->GetProperty("Receipt" . $key));
            }
        }
    }
    $session->SaveToDB();

    $content->SetVar("Page", $request->GetProperty("Page"));
    $content->SetVar("ParamsForFilter", $urlFilter->GetForForm($filterParams));
    if (strlen($request->GetProperty("FilterCreatedRange")) == 0) {
        $request->SetProperty("FilterCreatedRange", date("01/01/Y 00:00") . " - " . date("m/d/Y 23:59"));
    }
    $content->LoadFromObject($request, $filterParams);

    $filterStatusList = [];
    foreach (["new", "review", "approve_proposed", "approved", "denied", "supervisor"] as $status) {
        $filterStatusList[] = [
            "title_translation" => GetTranslation("receipt-status-" . $status, $module),
            "value" => $status,
            "selected" => $request->GetProperty("FilterStatus") && in_array(
                $status,
                $request->GetProperty("FilterStatus")
            )
        ];
    }
    $content->SetLoop("FilterStatusList", $filterStatusList);

    $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true);
    $voucherProductGroupIDs = [];
    foreach ($voucherProductGroupList as $productGroup) {
        $voucherProductGroupIDs[] = ["group_id" => $productGroup["group_id"]];
    }
    $content->SetLoop("VoucherProductGroupList", $voucherProductGroupIDs);

    if ($user->Validate(array("root"))) {
        $content->SetVar("Admin", 'Y');
    }

    //user who last changed status selection
    $userList = new UserList("company");
    $users = $userList->GetUserListByPermissions(["root", "receipt"]);

    foreach ($users as $key => $user) {
        if ($users[$key]['user_id'] == $request->GetIntProperty("FilterUserLastChangedStatus")) {
            $users[$key]['Selected'] = true;
        }
    }

    $content->SetLoop("UserLastChangedStatusList", $users);

    if (!$request->IsPropertySet("FilterArchive")) {
        $content->SetVar("FilterArchive", "N");
    }
}
