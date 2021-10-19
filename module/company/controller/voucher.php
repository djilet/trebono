<?php
$user->ValidateAccess(array('employee' => null), 'or');

$navigation[] = array(
    'Title' => GetTranslation('section-voucher', $module),
    'Link' => $moduleURL . '&' . $urlFilter->GetForURL()
);

if ($request->IsPropertySet('voucher_id')) {
    if (!Employee::ValidateAccess($request->GetProperty('employee_id'))) {
        Send403();
    }

    $urlFilter->AppendFromObject($request, array_merge(array('employee_id', 'group_id', 'ActiveTab')));

    $productGroup = new ProductGroup('product');
    $productGroup->LoadByID($request->GetProperty('group_id'));

    if ($request->GetProperty('voucher_id') > 0) {
        $title = GetTranslation('voucher-edit', $module,
            array('product_group' => $productGroup->GetProperty('title_translation')));
    } else {
        $title = GetTranslation('voucher-add', $module,
            array('product_group' => $productGroup->GetProperty('title_translation')));
    }

    $navigation[] = array(
        'Title' => $title,
        'Link' => $moduleURL . '&' . $urlFilter->GetForURL() . '&voucher_id=' . $request->GetProperty('voucher_id')
    );
    $header = array(
        'Title' => $title,
        'Navigation' => $navigation,
        'StyleSheets' => array(
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/datepicker/css/datepicker.css'),
            array('StyleSheetFile' => ADMIN_PATH . 'template/plugins/fancybox/jquery.fancybox.min.css')
        ),
        'JavaScripts' => array(
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/inputmask/jquery.inputmask.bundle.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/jquery-validation/js/jquery.validate.min.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/datepicker/js/datepicker.js'),
            array('JavaScriptFile' => ADMIN_PATH . 'template/plugins/fancybox/jquery.fancybox.min.js')
        )
    );

    $content = $adminPage->Load('voucher_edit.html', $header);
    $content->SetVar('Title', $title);

    $link = $moduleURL . '&' . $urlFilter->GetForURL() . '&voucher_id=' . $request->GetProperty('voucher_id');
    Operation::Save($link, 'employee', 'voucher_id');

    $voucher = new Voucher($module);
    $voucherGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
    $voucherGroupList = array_column($voucherGroupList, 'group_id');

    if ($request->GetProperty('Save')) {
        $voucher->LoadFromObject($request);
        $countVouchers = 1;
        if ($voucher->Validate()) {
            if ($voucher->IsPropertySet('count')) {
                $countVouchers = $voucher->GetProperty('count');
                $voucher->RemoveProperty('count');

                $specificProductGroup = SpecificProductGroupFactory::Create($voucher->GetProperty('group_id'));

                $unit = $specificProductGroup->GetUnit(new Receipt(
                    'receipt',
                    array(
                        'document_date' => $voucher->GetProperty('voucher_date'),
                        'employee_id' => $voucher->GetProperty('employee_id')
                    )
                ), 'admin');

                $voucher->SetProperty('amount', $unit);
            }

            $generatePDFCount = 0;
            $voucherId = 0;
            for ($i = 0; $i < $countVouchers; $i++) {
                if ($voucher->Save()) {
                    $voucherId = $voucher->GetProperty('voucher_id');
                    $voucher->RemoveProperty('voucher_id');

                    if ($voucher->GetProperty('file')) {
                        $generatePDFCount++;
                    }

                    Operation::Save($link, 'employee', 'voucher_id_save');

                    $message = new CommonObject();
                    $message->AddMessage('saved');
                    $content->LoadMessagesFromObject($message);
                    $request->LoadFromObject($voucher);
                } else {
                    $content->LoadErrorsFromObject($voucher);
                    $content->LoadErrorFieldsFromObject($voucher);
                }
            }

            if ($generatePDFCount > 0 && $voucher->GetProperty('group_id') == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
                $employee = new Employee($module);
                $employee->LoadByID($voucher->GetProperty('employee_id'));
                $voucher->SendVoucherToEmail($employee, $voucher->GetProperty('group_id'), $generatePDFCount);
            }
        } else {
            $content->LoadErrorsFromObject($voucher);
            $content->LoadErrorFieldsFromObject($voucher);
        }

        if ($voucherId > 0) {
            $voucher->LoadByID($voucherId);
        }

        if ($request->IsPropertySet('count')) {
            $voucher->SetProperty("count", $request->GetProperty('count'));
            $content->SetVar('Count', 'Y');
            $content->SetVar('DisabledSaveButton', 'Y');
        }
    } else {
        if (!$voucher->LoadByID($request->GetProperty('voucher_id'))) {
            if (in_array($request->GetProperty('group_id'), $voucherGroupList)) {
                $voucher->SetProperty('end_date', date('31.12.Y', strtotime('+ 3 year')));
            } else {
                $voucher->SetProperty('end_date', date('31.12.Y'));
                $voucher->SetProperty('recurring_end_date', date('31.12.Y'));
            }

            if ($request->GetProperty('group_id') == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
                $content->SetVar('Count', 'Y');
            }
        } else {
            $receiptList = new ReceiptList('receipt');
            $voucherReceiptList = $receiptList->GetReceiptListForVoucher($request->GetProperty('voucher_id'));

            if (count($voucherReceiptList) > 0) {
                $content->SetVar('HasReceipts', 'Y');
            }

            $content->SetLoop('ReceiptList', $voucherReceiptList);
        }
    }

    if ($user->Validate(array('root'))) {
        $content->SetVar('Admin', 'Y');
    }
    if ($user->Validate(array('root', 'company_unit' => null, 'employee' => null), 'or')) {
        $content->SetVar('HistoryAdmin', 'Y');
    }
    if ($user->Validate(array('root')) || $request->GetProperty('group_id') == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER)) {
        $content->SetVar('ShowRecurring', 1);
    }

    $groupCode = ProductGroup::GetProductGroupCodeByID($request->GetProperty('group_id'));
    $setsOfGoodsServices = [PRODUCT_GROUP__BENEFIT_VOUCHER, PRODUCT_GROUP__BONUS_VOUCHER];
    if (in_array($groupCode, $setsOfGoodsServices)) {
        if ($request->GetIntProperty('voucher_id') > 0 || $request->IsPropertySet('reason')) {
            $selectedReason = $voucher->GetProperty('reason');
        } else {
            $selectedReason = Voucher::GetDefaultVoucherReason(
                OPTION_LEVEL_EMPLOYEE,
                $request->GetProperty("employee_id"),
                $groupCode,
                GetCurrentDate()
            );
        }
        $reasonList = Voucher::GetVoucherReasonList($selectedReason, 'voucher_sets_of_goods');
        $voucherScenario = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$groupCode],
            $request->GetProperty("employee_id"),
            GetCurrentDate() //this option is not dependant on date and time (task #4009)
        );
        if ($voucherScenario == "exchangeable") {
            $reasonList = [$reasonList[0]];
        } else {
            if (strpos($voucherScenario, "_flex") === false) {
                foreach ($reasonList as $reason) {
                    if (empty($reason["Selected"]) || !$reason["Selected"]) {
                        continue;
                    }

                    $reasonList = [$reason];
                    break;
                }
            } elseif (!$user->Validate(['root'])) {
                unset($reasonList[0]);
                $reasonList = array_values($reasonList);
            }
        }
        $content->SetLoop(
            'SetsOfGoodsList',
            $reasonList
        );
        $voucher->SetProperty("group_id", $request->GetProperty("group_id"));
    } elseif ($groupCode == PRODUCT_GROUP__FOOD_VOUCHER) {
        $content->SetLoop('VoucherReasonList', array(array('Reason' => 'Essensmarken', 'Selected' => 1)));
        $content->SetVar('IsFoodVoucher', 1);
    } else {
        $content->SetLoop('VoucherReasonList', Voucher::GetVoucherReasonList($voucher->GetProperty('reason')));
    }

    if (in_array($request->GetProperty('group_id'), $voucherGroupList)) {
        $content->SetVar('SpecialEndDate', 'Y');
    }
    $content->SetVar('employee_id', $request->GetProperty('employee_id'));

    if ($request->GetProperty('voucher_id') > 0 || $request->GetProperty('Save')) {
        $content->SetVar('NewVoucherBotton', 'Y');
    }

    $content->SetVar('ParamForURL', "Section=employee&" . $urlFilter->GetForURL(["Section"]));
    $content->LoadFromObject($voucher);
}