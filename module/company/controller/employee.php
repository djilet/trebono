<?php
/**
 * @var User $user
 * @var LocalObject $request
 * @var AdminPage $adminPage
 * @var URLFilter $urlFilter
 */

/**
 * @var User $user
 * @var LocalObject $request
 */

$user->ValidateAccess(array('employee' => null, 'employee_view' => null), 'or');

$navigation[] = array(
    'Title' => GetTranslation('section-employee', $module),
    'Link' => $moduleURL . '&' . $urlFilter->GetForURL()
);

$filterParams = array(
    'FilterName',
    'FilterCompanyTitle',
    'FilterArchive',
    'FilterOption',
    'FilterOptionOperation',
    'FilterOptionValue',
    'FilterOptionValueDatepicker',
    'FilterOptionProduct',
    'FilterOptionProductGroup',
    'FilterBookedModule',
    'ItemsOnPage',
    'FilterApplicationUsed',
    'FilterProductReceiptSearch',
    'FilterReceiptOptionValueOne',
    'FilterReceiptOptionOperation',
    'FilterReceiptOptionValueTwo',
    'FilterCompanyUnitID'
);

if ($request->IsPropertySet('employee_id')) {
    $employee = new Employee($module);
    if (!Employee::ValidateAccess($request->GetProperty('employee_id'), $user->GetProperty('user_id'))) {
        Send403();
    }

    $urlFilter->AppendFromObject($request, array_merge(array('Page'), array('ActiveTab'), $filterParams));
    $link = $moduleURL . '&' . $urlFilter->GetForURL(['Section']);
    if ($request->IsPropertySet('voucher_id')) {
        $voucher = new Voucher($module);
        $voucher->LoadByID($request->GetProperty('voucher_id'));
        header('Location: ' . $link . 'Section=voucher&employee_id=' . $voucher->GetProperty('employee_id') .
            '&group_id=' . $voucher->GetProperty('group_id') . '&ActiveTab=3&voucher_id=' . $voucher->GetProperty('voucher_id'));
    }

    $user->SetProperty('AvailablePermissions', $user->GetAvailablePermissions());

    $title = $request->GetProperty('employee_id') > 0 ? GetTranslation('title-employee-edit', $module) : GetTranslation('title-employee-add', $module);

    $moduleProduct = 'product';
    $groupList = new ProductGroupList($moduleProduct);
    $groupList->LoadProductGroupListForAdmin(false, false, $user);
    $groupList = $groupList->GetItems();
    $requestProduct = $request->GetProperty('Product');

    $voucherGroupList = [];
    foreach ($groupList as $key => $productGroup) {
        $productList = new ProductList($moduleProduct);
        $productList->LoadProductListForAdmin($productGroup['group_id']);
        $groupList[$key]['product_list'] = $productList->GetItems();

        if ($productGroup["voucher"] != 'Y') {
            continue;
        }

        $voucherGroupList[] = $groupList[$key];
        $urlFilter->AppendFromObject($request, array('VoucherPage_' . $productGroup['code']));
    }

    $navigation[] = array(
        'Title' => $title,
        'Link' => $moduleURL . '&' . $urlFilter->GetForURL() . '&employee_id=' . $request->GetProperty('employee_id')
    );
    $header = array(
        'Title' => $title,
        'Navigation' => $navigation,
        'StyleSheets' => array(
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/datepicker/css/datepicker.css'),
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/fancybox/jquery.fancybox.min.css'),
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/pbcalendar/pb.calendar.css')
        ),
        'JavaScripts' => array(
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/inputmask/jquery.inputmask.bundle.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/jquery-validation/js/jquery.validate.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/datepicker/js/datepicker.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/fancybox/jquery.fancybox.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/pbcalendar/pb.calendar.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/pbcalendar/moment-with-locales.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/crypto-js/rollups/sha256.js'),
            array('JavaScriptFile' => PROJECT_PATH . 'module/' . $module . '/template/js/employee_edit.js?
                t=' . filemtime(PROJECT_DIR . 'module/' . $module . '/template/js/employee_edit.js'))
        )
    );

    $content = $adminPage->Load('employee_edit.html', $header);
    $content->SetVar('LNG_RemoveMessage', GetTranslation('confirm-remove', 'core'));
    $content->SetVar('LNG_ActivateMessage', GetTranslation('confirm-activate', 'core'));
    $content->SetVar('LNG_MaterialStatusSingle', GetTranslation('material-status-single', 'company'));
    $content->SetVar('LNG_MaterialStatusMarried', GetTranslation('material-status-married', 'company'));
    $content->SetVar('LNG_EndEmploymentContract', GetTranslation('end-employment-contract', 'company'));
    $content->SetVar('LNG_ContinueEmploymentContract', GetTranslation('continue-employment-contract', 'company'));
    $content->SetVar('LNG_UploadReceiptRules', GetTranslation('upload_receipt_rule_text', 'company'));

    if ($request->GetProperty('employee_id')) {
        $link = $moduleURL . '&' . $urlFilter->GetForURL() . '&employee_id=' . $request->GetProperty('employee_id');
        Operation::Save($link, 'employee', 'employee_id', $request->GetProperty('employee_id'));
    }

    if ($request->GetProperty('Do') == 'GetVoucherPDF' && $request->GetProperty('VoucherID')) {
        $voucher = new Voucher($module);
        $voucher->LoadByID($request->GetProperty('VoucherID'));

        $fileName = $voucher->GetProperty('file');
        $filePath = COMPANY_VOUCHER_DIR . $fileName;
        OutputFile($filePath, CONTAINER__COMPANY, $fileName);
    }

    $wasSaved = false;
    if ($request->GetProperty('ResendEmail')) {
        $employee->LoadByID($request->GetProperty('employee_id'));
        $employeeUser = new User();
        $employeeUser->LoadByID($employee->GetProperty('user_id'));
        $companyUnitTitle = CompanyUnit::GetPropertyValue("title", $employee->GetIntProperty("company_unit_id"));
        $regEmailText = CompanyUnit::GetPropertyValue("reg_email_text", $employee->GetIntProperty("company_unit_id"));
        $employeeUser->SetProperty("company_unit_title", $companyUnitTitle);
        $employeeUser->SetProperty("company_unit_reg_email_text", $regEmailText);
        if ($employeeUser->SendPasswordToEmail(true)) {
            $content->LoadMessagesFromObject($employeeUser);
        } else {
            $content->LoadErrorsFromObject($employeeUser);
            $content->LoadErrorFieldsFromObject($employeeUser);
        }
        $link = $moduleURL . '&' . $urlFilter->GetForURL() . '&employee_id=' . $request->GetProperty('employee_id');
        Operation::Save($link, 'employee', 'employee_id_resend_email', $employee->GetProperty('employee_id'));
    } elseif ($request->GetProperty('Save')) {
        $employee->LoadFromObject($request);
        $employee->SetProperty('Link', $link ?? null);
        if ($employee->Save()) {
            if (isAjax()) {
                echo json_encode([
                    'result' => 'success',
                    'employee_id' => $employee->GetProperty('employee_id'),
                    'user_id' => $employee->GetProperty('user_id'),
                ]);
                exit(0);
            }

            $link = $moduleURL . '&' . $urlFilter->GetForURL() . '&employee_id=' . $employee->GetProperty('employee_id');
            Operation::Save($link, 'employee', 'employee_id_save', $employee->GetProperty('employee_id'));

            $content->LoadMessagesFromObject($employee);
            $wasSaved = true;
            $employee->LoadByID($employee->GetProperty('employee_id'));
        } else {
            if (isAjax()) {
                echo json_encode([
                    'result' => 'error',
                ]);
                exit(0);
            }

            $content->LoadErrorsFromObject($employee);
            $content->LoadErrorFieldsFromObject($employee);
            $content->LoadFromObject($request);
        }
    } else {
        $employee->LoadByID($request->GetProperty('employee_id'));
    }

    $isRoot = false;
    $isRootOrAdmin = false;
    $isRootOrEmployeeOrAdmin = false;
    $isEmployeeAdmin = false;
    if ($user->Validate(array('root'))) {
        $content->SetVar('Admin', 'Y');
        $isRoot = true;
    }
    if ($user->Validate(array('root', 'company_unit' => null, 'employee' => null), 'or')) {
        $content->SetVar('HistoryAdmin', 'Y');
        $isRootOrAdmin = true;
        $canRemoveVouchers = true;
        $excludeArchiveVouchers = false;
    }
    if ($user->Validate(array('root', 'employee_view', 'employee' => null), 'or')) {
        $content->SetVar('ShowBankData', 'Y');
        $isRootOrEmployeeOrAdmin = true;
    }
    if (!$isRoot && $user->Validate(array('employee' => null))) {
        $content->SetVar('IsEmployeeAdmin', 'Y');
        $isEmployeeAdmin = true;
    }

    $isEmployeeViewerSelf = false;
    if ($user->Validate(['employee_view'])) {
        $hasEditPermission = false;

        if (
            $employee->GetProperty('company_unit_id') == null ||
            $user->Validate(array('employee', 'employee' => $employee->GetProperty('company_unit_id')), 'or')
        ) {
            $hasEditPermission = true;
        }

        if (!$hasEditPermission) {
            $content->SetVar('EmployeeViewer', 'Y');
        }

        if ($employee->GetProperty('user_id') == $user->GetProperty('user_id')) {
            $content->SetVar('EmployeeViewerSelf', 'Y');
            $isEmployeeViewerSelf = true;
        }
    }

    if ($request->GetProperty("Do") == "Remove" && $request->GetProperty("VoucherIDs")) {
        if ($canRemoveVouchers ?? false) {
            $voucherList = new VoucherList($module);
            $voucherList->Remove($request->GetProperty("VoucherIDs"));
            $content->LoadMessagesFromObject($voucherList);
            $content->LoadErrorsFromObject($voucherList);

            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&employee_id=" . $request->GetProperty("employee_id");
            Operation::Save($link, "employee", "employee_voucher_remove");
        } else {
            $this->SetLoop("ErrorList", [
                GetTranslation("cannot-remove-voucher", "core"),
            ]);
        }
    }
    if ($request->GetProperty("Do") == "Activate" && $request->GetProperty("VoucherIDs")) {
        if ($canRemoveVouchers ?? false) {
            $voucherList = new VoucherList($module);
            $voucherList->Activate($request->GetProperty("VoucherIDs"));
            $content->LoadMessagesFromObject($voucherList);
            $content->LoadErrorsFromObject($voucherList);
            $link = $moduleURL . "&" . $urlFilter->GetForURL() . "&employee_id=" . $request->GetProperty("employee_id");
            Operation::Save($link, "employee", "employee_voucher_activate");
        } else {
            $this->SetLoop("ErrorList", [
                GetTranslation("cannot-activate-voucher", "core"),
            ]);
        }
    }

    if ($isRoot || $isEmployeeViewerSelf) {
        $content->SetVar('ShowStatistics', 'Y');
    }

    if ($employee->GetProperty('employee_id')) {
        $accProperties = array(
            'acc_meal_value_tax_flat',
            'acc_food_subsidy_tax_free',
            'acc_gross_salary',
            'acc_grant_of_materials',
            'acc_internet_subsidy_tax',
            'acc_mobile_subsidy_tax_free',
            'acc_recreation_subsidy_tax_flat',
            'acc_net_income',
            'acc_bonus_tax_flat',
            'acc_transport_tax_free',
            'acc_child_care_tax_free',
            'acc_travel_tax_free',
            'acc_daily_allowance',
            'acc_gift',
            'acc_corporate_health_management',
            'acc_ticket',
            'acc_accommodation',
            'acc_hospitality',
            'acc_parking',
            'acc_other',
            'acc_travel_costs',
            'acc_creditor'
        );

        foreach ($accProperties as $accProperty) {
            $inheritableProperty = CompanyUnit::GetInheritablePropertyCompanyUnit(
                $employee->GetProperty('company_unit_id'),
                $accProperty
            );
            if (!$inheritableProperty || $employee->GetProperty($accProperty)) {
                continue;
            }

            $employee->SetProperty('inherited_' . $accProperty, $inheritableProperty);
        }

        $employee->SetProperty('device_amount', Device::GetDeviceAmountByUserID($employee->GetProperty('user_id')));

        $voucherListArray = array();
        foreach ($voucherGroupList as $productGroup) {
            $noContract = false;
            $productList = $productGroup['product_list'];

            $mainProductCode = PRODUCT_GROUP__MAIN_PRODUCT[$productGroup['code']] ?? null;

            foreach ($productList as $product) {
                $contract = new Contract('product');
                $contract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $employee->GetProperty('employee_id'),
                    $product['product_id']
                );

                if ($mainProductCode == $product['code'] && !$contract->IsPropertySet('contract_id')) {
                    $noContract = true;
                }

                if (is_null($contract->GetProperty('end_date')) || $contract->GetProperty('end_date') >= date('Y-m-d')) {
                    continue;
                }

                $noContract = true;
            }

            $voucherList = new VoucherList($module);
            $voucherList->SetPageParam('VoucherPage_' . $productGroup['code']);
            $voucherList->SetCurrentPage($request->GetProperty('VoucherPage_' . $productGroup['code']));
            $voucherList->SetOrderBy('voucher_id_desc');
            $voucherList->LoadVoucherListByEmployeeID(
                $employee->GetProperty('employee_id'),
                $productGroup['group_id'],
                false,
                false,
                $excludeArchiveVouchers ?? true,
                true,
                null,
                null,
                false,
                false,
                true,
                $content->GetVar("INTERFACE_LANGCODE")
            );

            if (empty($voucherList->GetItems()) && $noContract && !$isRoot) {
                continue;
            }

            $voucherList->AppendCanRemove($canRemoveVouchers ?? false);

            $voucherListArray[] = array(
                'voucher_list' => $voucherList->GetItems(),
                'group_id' => $productGroup['group_id'],
                'code' => $productGroup['code'],
                'title_translation' => $productGroup['title_translation'],
                'replace_reasons_with_goods' => $productGroup['code'] == PRODUCT_GROUP__BENEFIT_VOUCHER ? 1 : 0,
                'no_contract' => $noContract,
                'show_benefit_voucher_generate' => $productGroup['code'] == PRODUCT_GROUP__BENEFIT_VOUCHER && (IsTestEnvironment() || IsLocalEnvironment()) ? 1 : 0,
                'paging' => $voucherList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL(array(
                        'ActiveTab',
                        'VoucherPage_' . $productGroup['code']
                    )) . '&employee_id=' . $employee->GetProperty('employee_id') . '&ActiveTab=3')
            );
        }

        $content->SetLoop('VoucherGroupList', $voucherListArray);
        $content->SetLoop(
            'AcceptedDocumentList',
            $employee->GetAcceptedDocumentList($content->GetVar('EmployeeViewer') == 'Y')
        );
    }

    $content->LoadFromObject($employee);
    $content->SetLoop('UserImageParamList', $employee->GetImageParams('user'));

    $companyUnitList = new CompanyUnitList($module);
    $permission = $content->GetVar('EmployeeViewer') == 'Y' ? 'employee_view' : 'employee';


    $companyUnitList->LoadCompanyUnitListForTree(null, $permission, '', false, false, $user);
    for ($i = 0; $i < $companyUnitList->GetCountItems(); $i++) {
        if ($companyUnitList->_items[$i]['company_unit_id'] != $employee->GetProperty('company_unit_id')) {
            continue;
        }

        $companyUnitList->_items[$i]['Selected'] = 1;
    }
    $content->LoadFromObjectList('CompanyUnitList', $companyUnitList);

    if ($employee->GetProperty('employee_id')) {
        $content->SetVar('CompanyUnitTitle', CompanyUnit::GetTitleByID($employee->GetProperty('company_unit_id')));

        $receiptList = new ReceiptList('receipt');

        $receiptRequest = new LocalObject();
        $receiptRequest->SetProperty('FilterEmployeeID', $employee->GetProperty('employee_id'));

        if (!$isRoot && !$isEmployeeViewerSelf) {
            $receiptRequest->SetProperty('FilterWithoutVoucherReceipts', true);
        }

        $receiptRequest->SetProperty($receiptList->GetOrderByParam(), 'created_desc');
        $receiptList->SetOrderBy('admin_desc');
        $receiptList->LoadReceiptListForAdmin($receiptRequest, false, $permission);

        /**
         * if current user has 'employee administrator' access but has not 'receipt administrator' access then
         * show him popup with photos instead of link to receipt page
         */
        $receiptCompanyUnitIDs = $user->GetPermissionLinkIDs('receipt');
        $receiptCompanyUnitIDs = CompanyUnitList::AddChildIDs($receiptCompanyUnitIDs);
        if (
            $user->Validate(array('receipt')) || in_array(
                $employee->GetProperty('company_unit_id'),
                $receiptCompanyUnitIDs
            )
        ) {
            $content->SetVar('ReceiptListShowLinks', 1);
        } else {
            for ($i = 0; $i < $receiptList->GetCountItems(); $i++) {
                $receiptFileList = new ReceiptFileList('receipt');
                $receiptFileList->LoadFileList($receiptList->_items[$i]['receipt_id']);
                $receiptList->_items[$i]['FileList'] = $receiptFileList->GetItems();
            }
        }

        $content->LoadFromObjectList('ReceiptList', $receiptList);
        $content->SetVar('ReceiptPaging', $receiptList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL(array(
                'ActiveTab',
                'Page'
            )) . '&employee_id=' . $employee->GetProperty('employee_id') . '&ActiveTab=3'));
    }

    //archive
    if ($archiveInfo = $employee->LoadArchiveInfo()) {
        $messageObject = new CommonObject();
        foreach ($archiveInfo as $line) {
            if ($line['created_from'] == 'web_api') {
                $message = $line['value'] == 'Y' ? 'user-archive-y-message-web-api' : 'user-archive-n-message-web-api';
            } else {
                $message = $line['value'] == 'Y' ? 'user-archive-y-message' : 'user-archive-n-message';
            }


            $messageObject->AddMessage(
                $message,
                null,
                array(
                    'entity' => GetTranslation('entity-employee'),
                    'username' => $line['username'],
                    'datetime' => (new DateTime($line['created']))->format('H:i:s d M Y')
                )
            );
        }
        $content->SetLoop('ArchiveMessage', $messageObject->GetMessagesAsArray());
    }

    $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($employee->GetProperty('company_unit_id'), true);
    $typesOptionsSelect = array(
        'monthly-yearly-select' => array(OPTION__BENEFIT__MAIN__RECEIPT_OPTION, OPTION__AD__MAIN__RECEIPT_OPTION),
        "custom-select" => array_merge(
            array_values(OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO),
            array_values(OPTIONS_VOUCHER_DEFAULT_REASON)
        )
    );
    $textAreaOptions = array(
        OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
        OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
        OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION,
        OPTION__FOOD__MAIN__IMPORTANT_INFO,
        OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO,
        OPTION__MOBILE__MAIN__MOBILE_MODEL,
        OPTION__MOBILE__MAIN__MOBILE_NUMBER
    );
    $textAreaOptions = array_merge($textAreaOptions, array_values(OPTIONS_INTERNAL_VERIFICATION_INFO));

    $hideProductsForAdmin = array(
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
        PRODUCT__TRAVEL__ADVANCED_SECURITY
    );

    $hideOptionsFromAdmin = array(
        OPTION__BASE__FORCE_APPROVAL,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER,
        OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION,
        OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF,
        OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
        OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE
    );

    $hideOptionsFromAdmin = array_merge($hideOptionsFromAdmin, OPTIONS_SALARY);

    $hideOptionsFromAdminNotVerificator = array(
        OPTION__FOOD__MAIN__IMPORTANT_INFO,
        OPTION__FOOD_VOUCHER__MAIN__IMPORTANT_INFO,
        OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED,
        OPTION__MOBILE__MAIN__CONTRACTUAL_INFORMATION
    );

    $hideOptionsFromEmployee = array(
        OPTION__BASE__FORCE_APPROVAL,
        OPTION__BASE__MAIN__DEACTIVATION_REASON,
        OPTION__FOOD__MAIN__UNITS_PER_WEEK,
        OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER,
        OPTION__FOOD__MAIN__AUTO_ADOPTION,
        OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
        OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK,
        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER,
        OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION,
        OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
        OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT,
        OPTION__FOOD_VOUCHER__MAIN__PAYROLL_EXPORT,
        OPTION__BENEFIT_VOUCHER__MAIN__PAYROLL_EXPORT,
        OPTION__BENEFIT_VOUCHER__MAIN__SHORT_TEXT_FOR_PDF,
        OPTION__BENEFIT_VOUCHER__MAIN__LONG_TEXT_FOR_PDF,
        OPTION__INTERNET__MAIN__CONTRACTUAL_INFORMATION,
        OPTION__AD__MAIN__RECEIPT_OPTION,
        OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
        OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT,
    );

    $hideOptionsFromAdmin = array_merge($hideOptionsFromAdmin, OPTIONS_SALARY);

    $hideOptionsFromAdminNotVerificator = array_merge(
        $hideOptionsFromAdminNotVerificator,
        array_values(OPTIONS_INTERNAL_VERIFICATION_INFO)
    );
    $hideOptionsFromEmployee = array_merge(
        $hideOptionsFromEmployee,
        array_values(OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO)
    );

    //for($i = 0; $i < count($groupList); $i++)
    foreach ($groupList as $i => $productGroup) {
        $productList = $productGroup['product_list'];

        $mainProductCode = PRODUCT_GROUP__MAIN_PRODUCT[$productGroup['code']] ?? null;

        foreach ($productList as $j => $product) {
            if ($isEmployeeAdmin && $isEmployeeViewerSelf && in_array($product['product_id'], $hideProductsForAdmin)) {
                $productList[$j]['hide'] = true;
            }

            $contract = new Contract($moduleProduct);
            $contract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $employee->GetProperty('employee_id'),
                $product['product_id']
            );

            $companyContract = new Contract($moduleProduct);
            foreach ($companyUnitIDs as $companyUnitID) {
                if (
                    $companyContract->LoadLatestActiveContract(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $employee->GetProperty('company_unit_id'),
                        $product['product_id']
                    )
                ) {
                    break;
                }
            }

            if ($product['product_id'] != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)) {
                if ($mainProductCode == $product['code'] && !$companyContract->IsPropertySet('contract_id')) {
                    $groupList[$i]['no_company_contract'] = true;
                }
                if (!$companyContract->IsPropertySet('contract_id')) {
                    $productList[$j]['no_company_contract'] = true;
                }
            }

            if ($mainProductCode == $product['code'] && !$contract->IsPropertySet('contract_id')) {
                $groupList[$i]['no_employee_contract'] = true;
            }
            if (
                $mainProductCode == $product['code'] && !$contract->IsPropertySet('contract_id') && !$user->Validate(array(
                    'root',
                    'employee' => null
                ), 'or')
            ) {
                $groupList[$i]['no_company_contract'] = true;
            }

            if ($contract->GetProperty('end_date') >= date('Y-m-d') || is_null($contract->GetProperty('end_date'))) {
                $productList[$j]['contract_start_date'] = $contract->GetProperty('start_date');
                $productList[$j]['contract_end_date'] = $contract->GetProperty('end_date');
                $productList[$j]['contract_id'] = $contract->GetProperty('contract_id');
            } else {
                $productList[$j]['contract_id'] = $contract->GetProperty('contract_id');
            }

            if (
                $productList[$j]['product_id'] != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION) &&
                !($isRoot && $contract->GetProperty('start_date') > date('Y-m-d'))
            ) {
                $productList[$j]['disabled_start_date'] = true;
            }

            if (
                $productList[$j]['product_id'] != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION) &&
                !$isRoot
            ) {
                $productList[$j]['disabled_end_date'] = true;
            }

            if ($requestProduct) {
                foreach ($requestProduct as $key => $value) {
                    if ($key != $productList[$j]['product_id']) {
                        continue;
                    }

                    if (isset($requestProduct[$key]['start_date']) && !$wasSaved) {
                        $productList[$j]['contract_start_date'] = $requestProduct[$key]['start_date'];
                    }
                    if (isset($requestProduct[$key]['end_date']) && !$wasSaved) {
                        $productList[$j]['contract_end_date'] = $requestProduct[$key]['end_date'];
                    }
                    if (isset($requestProduct[$key]['date_of_params']) && !$wasSaved) {
                        $productList[$j]['date_of_params'] = $requestProduct[$key]['date_of_params'];
                    }
                    if (isset($requestProduct[$key]['start_date']) && !$wasSaved) {
                        $productList[$j]['request_start_date'] = true;
                    }
                    if (!isset($requestProduct[$key]['end_date']) || $wasSaved) {
                        continue;
                    }

                    $productList[$j]['request_end_date'] = true;
                }
            }

            $optionList = new OptionList($moduleProduct);
            $optionList->LoadOptionListForAdmin($product['product_id'], OPTION_LEVEL_EMPLOYEE, $user);
            $optionList = $optionList->GetItems();
            foreach ($optionList as $k => $option) {
                $optionValue = Option::GetCurrentValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $option['option_id'],
                    $employee->GetIntProperty('employee_id')
                );
                $optionList[$k]['value'] = $optionValue;

                if ($optionValue === null) {
                    $optionList[$k]['inherited_value'] = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $option['code'],
                        $employee->GetIntProperty('employee_id'),
                        GetCurrentDate()
                    );
                    $optionList[$k]['inherited_value_source'] = Option::GetInheritableOptionValueSource(
                        OPTION_LEVEL_EMPLOYEE,
                        $option['code'],
                        $employee->GetIntProperty('employee_id'),
                        GetCurrentDate()
                    );
                }

                if (
                    !$user->Validate(array('root')) && in_array($option['code'], $hideOptionsFromAdmin) ||
                    !$user->Validate(array('root', 'receipt'), 'or') && in_array(
                        $option['code'],
                        $hideOptionsFromAdminNotVerificator
                    ) ||
                    !$user->Validate(array('root', 'employee' => null), 'or') && in_array(
                        $option['code'],
                        $hideOptionsFromEmployee
                    )
                ) {
                    $optionList[$k]['hide'] = true;
                    if (
                        $optionList[$k]["show_group"] ?? false && $optionList[$k]["show_group"]
                        && isset($optionList[$k + 1])
                        && $optionList[$k]["group_id"] == $optionList[$k + 1]["group_id"]
                    ) {
                        $optionList[$k + 1]["show_group"] = 1;
                    }
                    $optionList[$k]["show_group"] = 0;
                } else {
                    $optionList[$k]['hide'] = false;
                }

                foreach ($typesOptionsSelect as $key => $value) {
                    if (!in_array($optionList[$k]["code"], $value)) {
                        continue;
                    }

                    $optionList[$k]["type"] = $key;
                }

                if (in_array($optionList[$k]['code'], $textAreaOptions)) {
                    $optionList[$k]['type'] = 'textarea';
                }

                if (in_array($optionList[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON)) {
                    $voucherScenario = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$productGroup['code']],
                        $request->GetProperty("employee_id"),
                        GetCurrentDate() //this option is not dependant on date and time (task #4009)
                    );
                    if ($voucherScenario != "employee_flex" && $isEmployeeViewerSelf) {
                        $optionList[$k]['hide'] = true;
                        $optionList[$k]["show_group"] = 0;
                    }
                } elseif ($isEmployeeViewerSelf && !$isEmployeeAdmin) {
                    $optionList[$k]['disabled'] = true;
                }

                if ($requestProduct && !$wasSaved) {
                    foreach ($requestProduct as $key => $value) {
                        if ($key != $productList[$j]['product_id'] || !isset($value['Option'])) {
                            continue;
                        }

                        foreach ($value['Option'] as $optionKey => $optionValue) {
                            if ($optionKey != $optionList[$k]['option_id'] || strlen($optionValue) <= 0) {
                                continue;
                            }

                            $optionValue = preg_match('/[,]/', $optionValue) ? preg_replace('/[^0-9,]/', '', $optionValue) : preg_replace('/[^0-9.]/', '', $optionValue);

                            $optionValue = str_replace(',', '.', $optionValue);
                            if (is_int($optionValue)) {
                                $optionValue = floatval($optionValue);
                            }
                            $optionList[$k]['value'] = $optionValue;
                        }
                    }
                }

                if (in_array($optionList[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO)) {
                    $optionList[$k]["category_preference_select"] =
                        Option::GetOptionIDByCode(OPTIONS_VOUCHER_DEFAULT_REASON[$productGroup['code']]);
                    $optionList[$k]["inherited_value_no_translation"] =
                        $optionList[$k]["inherited_value"];
                    $optionList[$k]["SelectList"] =
                        Option::GetVoucherReasonScenarioList($optionList[$k]["value"]);

                    $voucherScenario = $optionList[$k]["value"] ?? $optionList[$k]["inherited_value"];
                    $optionList[$k]["inherited_value"] = GetTranslation(
                        "voucher-category-scenario-" . $optionList[$k]["inherited_value"],
                        $module
                    );
                }

                if (!in_array($optionList[$k]["code"], OPTIONS_VOUCHER_DEFAULT_REASON)) {
                    continue;
                }

                $optionList[$k]["SelectList"] = Voucher::GetVoucherReasonList(
                    $optionList[$k]["value"],
                    "voucher_sets_of_goods"
                );

                if (empty($voucherScenario)) {
                    $voucherScenario = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$productGroup['code']],
                        $request->GetProperty("employee_id"),
                        GetCurrentDate() //this option is not dependant on date and time (task #4009)
                    );
                }
                if (!in_array($voucherScenario, ["employee", "employee_flex"])) {
                    $optionList[$k]['disabled'] = true;
                }
                if (!isset($optionList[$k]["inherited_value"])) {
                    if (!$isRoot) {
                        unset($optionList[$k]["SelectList"][0]);
                        $optionList[$k]["SelectList"] = array_values($optionList[$k]["SelectList"]);
                    }

                    continue;
                }

                $key = $optionList[$k]["inherited_value"];
                $optionList[$k]["inherited_value"]
                    = $optionList[$k]["SelectList"][$key]["Reason"];
                if ($isRoot) {
                    continue;
                }

                unset($optionList[$k]["SelectList"][0]);
                $optionList[$k]["SelectList"] = array_values($optionList[$k]["SelectList"]);
            }
            $productList[$j]['OptionList'] = $optionList;
        }
        $groupList[$i]['ProductList'] = $productList;
    }

    $employmentAgreement = new AgreementEmployee('agreements');
    foreach ($groupList as $key => $item) {
        if ($item['receipts'] != 'Y' or !isset($groupList[$key]['ProductList'][0])) {
            continue;
        }

        $employmentAgreement->LoadForEmployeeID($employee, $item['group_id']);
        if (
            $employmentAgreement->CountProperties() > 0 && $user->Validate(array(
                'root',
                'receipt',
                'employee' => null
            ), 'or')
        ) {
            $groupList[$key]['ProductList'][0]['AgreementEmployment'] = array(
                $employmentAgreement->GetProperties()
            );
        } else {
            $groupList[$key]['ProductList'][0]['AgreementEmployment'] = null;
        }
    }

    $groupListInBenefitGrouping = array();
    $groupListBase = array();
    $groupListTravel = array();
    $groupListGivve = array();
    $groupListStoredData = array();

    foreach ($groupList as $key => $item) {
        switch ($item['code']) {
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

    $resultGroupList = array(
        'ProductGroupList' => [
            $groupListBase,
            $groupListInBenefitGrouping,
            $groupListTravel,
            $groupListGivve,
            $groupListStoredData,
        ]
    );

    // available code for web upload
    $productCodeForUploadReceipt = [
        'food',
        'ad',
        'mobile',
        'transport',
        'internet',
    ];

    $availableUploadedGroup = [];

    $availableUploadedGroup =
        array_filter($groupListInBenefitGrouping['BenefitGrouping'], static function ($group) use ($productCodeForUploadReceipt) {
            return in_array($group['code'], $productCodeForUploadReceipt);
        });


    $content->LoadFromArray($resultGroupList);
    $content->SetVar('DateOfParams', date('Y-m-d'));
    $content->SetVar('ActiveTab', $request->GetProperty('ActiveTab'));
    $content->SetLoop('AvailableServiceForUploadReceipt', array_values($availableUploadedGroup));

    //statistics of voucher services
    $newVoucherGroupList = ProductGroupList::GetProductGroupList(false, 'Y', false, true, $user);
    for ($i = 0; $i < count($newVoucherGroupList); $i++) {
        $newVoucherGroupList[$i]['statistics-translation'] = GetTranslation(
            $newVoucherGroupList[$i]['code'] . '-statistics',
            $module
        );
        $newVoucherGroupList[$i]['yearly-statistics-translation'] = GetTranslation(
            $newVoucherGroupList[$i]['code'] . '-yearly-statistics',
            $module
        );
    }
    $content->SetLoop('PaymentVoucherProductGroupList', $newVoucherGroupList);
} else {
    $urlFilter->AppendFromObject($request, $filterParams);

    $header = array(
        'Title' => GetTranslation('section-employee', $module),
        'Navigation' => $navigation,
        'StyleSheets' => array(
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/typeahead/css/typeahead.css'),
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/datepicker/css/datepicker.css')
        ),
        'JavaScripts' => array(
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/typeahead/handlebars.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/typeahead/typeahead.bundle.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/datepicker/js/datepicker.js')
        )
    );

    $content = $adminPage->Load('employee_list.html', $header);
    $content->SetVar('LNG_DeactivateMessage', GetTranslation('confirm-disactivate', 'core'));
    $content->SetVar('LNG_ActivateMessage', GetTranslation('confirm-activate', 'core'));

    $link = $moduleURL . '&' . $urlFilter->GetForURL();

    if ($user->Validate(array('employee_view')) && !$user->Validate(array('employee' => null))) {
        $employee = new Employee($module);
        $employee->LoadByUserID($user->GetProperty('user_id'));
        header('Location: ' . $link . '&employee_id=' . $employee->GetProperty('employee_id'));
    }
    if ($user->Validate(array('root'))) {
        $content->SetVar('Admin', 'Y');
    }
    Operation::Save($link, 'employee', 'employee_list');

    if ($request->GetProperty('Do') == 'Activate' && $request->GetProperty('EmployeeIDs')) {
        $employeeList = new EmployeeList($module);
        $employeeList->Activate($request->GetProperty('EmployeeIDs'));
        $content->LoadMessagesFromObject($employeeList);
        $content->LoadErrorsFromObject($employeeList);
        Operation::Save($link, 'employee', 'employee_activate');
    }

    $session = GetSession();
    //clean filters if employee list loading from company unit list (column Employees (x/x))
    $sessionFiltersForClean = [
        "FilterArchive",
        "FilterApplicationUsed",
    ];
    if ($request->IsPropertySet("FilterCompanyUnitID")) {
        foreach ($sessionFiltersForClean as $key) {
            $session->RemoveProperty("Employee" . $key);
        }
        // if company title is changed, reset id from filter
        $companyUnitTitle = CompanyUnit::GetTitleByID($request->GetProperty("FilterCompanyUnitID"));
        if ($companyUnitTitle && $companyUnitTitle != $request->GetProperty("FilterCompanyTitle")) {
            $request->RemoveProperty("FilterCompanyUnitID");
        }
    }

    //load filter data from session and to session for all roles
    $noSessionFilters = array(
        'FilterProductReceiptSearch',
        'FilterReceiptOptionValueOne',
        'FilterReceiptOptionOperation',
        'FilterReceiptOptionValueTwo',
        'FilterCompanyUnitID',
        'FilterOption',
        'FilterOptionOperation',
        'FilterOptionValueDatepicker',
        'FilterOptionValue',
        'FilterOptionProduct',
        'FilterOptionProductGroup',
    );
    foreach ($filterParams as $key) {
        if (in_array($key, $noSessionFilters)) {
            continue;
        }

        if ($session->IsPropertySet('Employee' . $key) && !$request->IsPropertySet($key)) {
            if ($user->Validate(array('employee' => null))) {
                $request->SetProperty($key, $session->GetProperty('Employee' . $key));
            }
        } else {
            if ($request->IsPropertySet($key)) {
                $request->SetProperty($key, urldecode($request->GetProperty($key)));
            }
            $session->SetProperty('Employee' . $key, $request->GetProperty($key));
        }
    }
    $session->SaveToDB();

    //start product group filter options --
    $optionListReceiptSearch = array();
    $optionListReceiptSearch[] = array(
        'option_id' => 'available_receipt_value_month',
        'title_translation' => GetTranslation('available-receipt-value-month'),
        'type' => 'currency'
    );
    $optionListReceiptSearch[] = array(
        'option_id' => 'available_receipt_value_year',
        'title_translation' => GetTranslation('available-receipt-value-year'),
        'type' => 'currency'
    );
    $optionListReceiptSearch[] = array(
        'option_id' => 'approved_receipt_value_month',
        'title_translation' => GetTranslation('approved-receipt-value-month'),
        'type' => 'currency'
    );
    $optionListReceiptSearch[] = array(
        'option_id' => 'approved_receipt_value_year',
        'title_translation' => GetTranslation('approved-receipt-value-year'),
        'type' => 'currency'
    );

    for ($i = 0; $i < count($optionListReceiptSearch); $i++) {
        if ($optionListReceiptSearch[$i]['option_id'] == $request->GetProperty('FilterReceiptOptionValueOne')) {
            $optionListReceiptSearch[$i]['receipt_value_one_selected'] = 'Y';
        }
        if ($optionListReceiptSearch[$i]['option_id'] != $request->GetProperty('FilterReceiptOptionValueTwo')) {
            continue;
        }

        $optionListReceiptSearch[$i]['receipt_value_two_selected'] = 'Y';
    }

    //Product group list and options list
    $productListFieldsSearch = array();
    $productListBookedSearch = array();
    $productGroupListReceipt = array();
    $productGroupList = new ProductGroupList('product');
    $productGroupList->LoadProductGroupListForAdmin(false, false, $user);
    foreach ($productGroupList->_items as $productGroup) {
        $productListObject = new ProductList('product');
        $productListObject->LoadProductListForAdmin($productGroup['group_id']);
        //options list
        foreach ($productListObject->_items as $product) {
            //product list for receipt filter
            if ($product['code'] != PRODUCT__BASE__MAIN && (strpos($product['code'], '__main') !== false)) {
                $productGroupListReceipt[] = $product;
            }

            $optionList = new OptionList('product');
            $optionList->LoadOptionListForAdmin($product['product_id'], OPTION_LEVEL_COMPANY_UNIT, $user);
            $product['OptionList'] = $optionList->GetItems();
            $product['OptionList'][] = array(
                'option_id' => 'start_date',
                'title_translation' => GetTranslation('start-date'),
                'type' => 'date',
                'product_id' => $product['product_id']
            );
            $product['OptionList'][] = array(
                'option_id' => 'end_date',
                'title_translation' => GetTranslation('end-date'),
                'type' => 'date',
                'product_id' => $product['product_id']
            );

            //add receipt related fileds to field search
            foreach ($optionListReceiptSearch as $option) {
                $product['OptionList'][] = array(
                    'option_id' => $option['option_id'],
                    'title_translation' => $option['title_translation'],
                    'type' => $option['type'],
                    'product_id' => $product['product_id'],
                    'group_id' => $product['group_id']
                );
            }

            if ($product['code'] == PRODUCT__FOOD__MAIN) {
                $product['OptionList'][] = array(
                    'option_id' => 'available_units_month',
                    'title_translation' => GetTranslation('food-voucher-left-month'),
                    'type' => 'currency',
                    'product_id' => $product['product_id'],
                    'group_id' => $product['group_id']
                );
                $product['OptionList'][] = array(
                    'option_id' => 'available_units_year',
                    'title_translation' => GetTranslation('food-voucher-left-year'),
                    'type' => 'currency',
                    'product_id' => $product['product_id'],
                    'group_id' => $product['group_id']
                );
            }

            for ($i = 0; $i < count($product['OptionList']); $i++) {
                $product['OptionList'][$i]['selected'] = $product['OptionList'][$i]['option_id'] == $request->GetProperty('FilterOption') && $product['OptionList'][$i]['product_id'] == $request->GetProperty('FilterOptionProduct')
                    ? 'Y'
                    : 'N';
            }
            $productListFieldsSearch[] = $product;
        }
        //product group list
        $keySelected = array_search(
            $request->GetProperty('FilterBookedModule'),
            array_column($productListObject->_items, 'product_id')
        );
        if ($keySelected !== false) {
            $productListObject->_items[$keySelected]['selected'] = 'Y';
        }
        $productGroup['ProductList'] = $productListObject->_items;
        $productListBookedSearch[] = $productGroup;
    }

    for ($i = 0; $i < count($productGroupListReceipt); $i++) {
        if ($productGroupListReceipt[$i]['group_id'] != $request->GetProperty('FilterProductReceiptSearch')) {
            continue;
        }

        $productGroupListReceipt[$i]['selected'] = 'Y';
    }

    $content->SetLoop('ProductListFieldsSearch', $productListFieldsSearch);
    $content->SetLoop('ProductListBookedSearch', $productListBookedSearch);
    $content->SetLoop('ProductGroupListReceipt', $productGroupListReceipt);
    $content->SetLoop('OptionListReceiptSearch', $optionListReceiptSearch);
    //-- end product group filter options

    $employeeList = new EmployeeList($module);
    $employeeList->LoadEmployeeList($request, false, true, $user);
    $content->LoadFromObjectList('EmployeeList', $employeeList);

    $content->SetVar('Paging', $employeeList->GetPagingAsHTML($moduleURL . '&' . $urlFilter->GetForURL()));
    $content->SetVar('ListInfo', GetTranslation(
        'list-info1',
        array('Page' => $employeeList->GetItemsRange(), 'Total' => $employeeList->GetCountTotalItems())
    ));
    $content->SetVar('ParamsForFilter', $urlFilter->GetForForm(array_merge(array('Page'), $filterParams)));
    $content->SetVar('ParamsForItemsOnPage', $urlFilter->GetForForm(array(
        'ItemsOnPage',
        'FilterOption',
        'FilterOptionOperation',
        'FilterOptionValue',
        'FilterOptionValueDatepicker',
        'FilterOptionProduct',
        'FilterOptionProductGroup',
        'FilterBookedModule',
        'FilterProductReceiptSearch',
        'FilterReceiptOptionValueOne',
        'FilterReceiptOptionOperation',
        'FilterReceiptOptionValueTwo',
        'FilterCompanyUnitID',
    )));
    $content->LoadFromObject($request, $filterParams);
    if (!$request->IsPropertySet('FilterArchive')) {
        $content->SetVar('FilterArchive', 'N');
    }

    $companyUnitList = new CompanyUnitList($module);
    $companyUnitList->LoadCompanyUnitListForTree(null, 'employee', false, false, false, $user);
    $content->LoadFromObjectList('CompanyUnitList', $companyUnitList);

    $itemsOnPageList = array();
    foreach (array(10, 20, 50, 100, 0) as $v) {
        $itemsOnPageList[] = array('Value' => $v, 'Selected' => $v == $employeeList->GetItemsOnPage() ? 1 : 0);
    }
    $content->SetLoop('ItemsOnPageList', $itemsOnPageList);
    $content->LoadMessagesFromObject($employeeList);
}
