<?php

abstract class AbstractSpecificProductGroup
{
    /**
     * Returns cost of unit for passed receipt based on its owner and receipt date
     * Currently unit is a maximum sum can be approved for one receipt
     *
     * @param Receipt $receipt
     *
     * @return float cost of unit
     */
    abstract public function GetUnit($receipt);

    /**
     * Validate receipt saving for the product group specific parameters.
     * Also sets real_amount_approved field to receipt based on group specific parameters.
     *
     * @param Receipt $receipt
     *
     * @return bool true if receipt is valid or false otherwise
     */
    abstract public function ValidateReceiptApprove($receipt);

    /**
     * Makes specific actions after receipt save.
     *
     * @param Receipt $receiptAfter
     * @param Receipt $receiptBefore receipt before update
     */
    public function ProcessAfterReceiptSave(Receipt $receiptAfter, Receipt $receiptBefore)
    {
        if ($receiptAfter->GetProperty("group_id") == $receiptBefore->GetProperty("group_id")) {
            return;
        }

        $stmt = GetStatement();
        $receiptFileList = new ReceiptFileList("receipt");
        $receiptFileList->LoadFileList($receiptAfter->GetProperty("receipt_id"));
        $itemList = $receiptFileList->GetItems();
        foreach ($itemList as $receiptFile) {
            $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"];
            if (!empty($company["signature_file"])) {
                $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_file"];
            }
            if (empty($company["signature_report_file"])) {
                continue;
            }

            $fileList[] = RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"];
        }

        $query = "SELECT comment_file FROM receipt_comment WHERE receipt_id=" . intval($receiptAfter->GetProperty("receipt_id"));
        if ($commentList = $stmt->FetchList($query)) {
            foreach ($commentList as $comment) {
                if (empty($comment["comment_file"])) {
                    continue;
                }

                $fileList[] = RECEIPT_IMAGE_DIR . "comment/" . $comment["comment_file"];
            }
        }

        $specificProductGroupBefore = SpecificProductGroupFactory::Create($receiptBefore->GetProperty("group_id"));
        $containerFrom = $specificProductGroupBefore->GetContainer();
        $containerTo = $this->GetContainer();

        $fileStorage = GetFileStorage($containerTo);
        foreach ($fileList as $filePath) {
            $fileStorage->MoveBetweenContainers($containerFrom, $containerTo, $filePath);
        }
    }

    /**
     * Appends additional info specific for current product group
     *
     * @param Receipt $receipt
     */
    abstract public function AppendAdditionalInfo($receipt);

    /**
     * Returns real_amount_approved of receipt should be showed to empoloyee in mobile app
     *
     * @param Receipt $receipt
     *
     * @return float|null
     */
    public function GetApiRealAmountApproved($receipt)
    {
        return $receipt->GetFloatProperty("real_amount_approved");
    }

    /**
     * Returns list of options and its values for current product group and specified receipt
     *  based on receipt's data
     *
     * @param Receipt $receipt
     *
     * @return array of options
     */
    public function GetOptions($receipt)
    {
    }

    /**
     * Returns list of lines for Addison export file
     *
     * @param ReceiptList $receiptList
     *
     * @return array
     */
    abstract public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType);

    /**
     * Return line list for Addison export file generated by the common way
     *
     * @param ReceiptList $receiptList
     * @param string $salaryOptionCode
     * @param string $positiveLineAccKey
     *
     * @return array
     */
    protected function GetCommonAddisonExportLineList(
        $companyUnitID,
        $groupID,
        $payrollDate,
        $exportType,
        $salaryOptionCode,
        $positiveLineAccKey
    ) {
        $receiptList = new ReceiptList("receipt");
        $receiptList->LoadReceiptListForAddison($companyUnitID, $groupID, $payrollDate, $exportType);

        $lineList = array();
        if ($receiptList->GetCountItems() > 0) {
            $employeeMap = array();

            foreach ($receiptList->GetItems() as $receipt) {
                $employeeID = $receipt["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employee = new Employee("company");
                    $employee->LoadByID($employeeID);

                    $employeeMap[$employeeID] = array(
                        "employee_property_list" => $employee->GetProperties(),
                        "salary_option" => Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            $salaryOptionCode,
                            $employeeID,
                            $receipt["document_date"]
                        )
                    );

                    if ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)) {
                        $employeeMap[$employeeID]["daily_allowance"] = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
                            $employeeID,
                            $receipt["document_date"]
                        );
                        if ($employeeMap[$employeeID]["daily_allowance"] == "Y") {
                            $positiveLineAccKey = "acc_daily_allowance";
                        }
                    }
                    if ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER)) {
                        $employeeMap[$employeeID]["payment_approved"] = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED,
                            $employeeID,
                            $receipt["document_date"]
                        );
                        if ($employeeMap[$employeeID]["payment_approved"] == "Y") {
                            $positiveLineAccKey = "acc_net_income";
                        }
                    }
                }

                $monthMapKey = date("Ym", strtotime($receipt["document_date"]));
                if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey] = array(
                        "positive_line" => array(
                            "title" => "Positive",
                            "acc_key" => $positiveLineAccKey,
                            "amount" => null,
                            "receipt_ids" => array(),
                            "legal_receipt_ids" => array()
                        ),
                        "negative_line" => array(
                            "title" => "Negative",
                            "acc_key" => "acc_gross_salary",
                            "amount" => null,
                            "receipt_ids" => array(),
                            "legal_receipt_ids" => array()
                        )
                    );
                }

                if ($groupID != ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL) || ($employeeMap[$employeeID]["daily_allowance"] == "N" || $receipt["receipt_from"] != "meal")) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] += $receipt["real_amount_approved"];
                }
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][] = $receipt["receipt_id"];
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][] = $receipt["legal_receipt_id"];

                if ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL)) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["trip_id"] = $receipt["trip_id"];
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["trip_id"] = $receipt["trip_id"];
                    if (isset($employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["receipt_ids"])) {
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["receipt_ids"] .= ", " . $receipt["receipt_id"];
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["legal_receipt_ids"] .= ", " . $receipt["legal_receipt_id"];
                    } else {
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["receipt_ids"] = $receipt["receipt_id"];
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["trip_ids"][$receipt["trip_id"]]["legal_receipt_ids"] = $receipt["legal_receipt_id"];
                    }

                    if ($employeeMap[$employeeID]["daily_allowance"] == "Y" && $receipt["receipt_from"] == "meal") {
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] +=
                            $receipt["days_amount_under_16"] * Option::GetInheritableOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                OPTION__TRAVEL__MAIN__HOURS_UNDER,
                                $employeeID,
                                $receipt["document_date"]
                            )
                            + $receipt["days_amount_over_16"] * Option::GetInheritableOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                OPTION__TRAVEL__MAIN__HOURS_OVER,
                                $employeeID,
                                $receipt["document_date"]
                            );
                    }
                }
            }

            foreach ($employeeMap as $employeeID => $employee) {
                foreach ($employee["month_map"] as $monthMapKey => $month) {
                    foreach ($month as $lineKey => $line) {
                        if (count($line["receipt_ids"]) == 0) {
                            continue;
                        }

                        $lineList[] = array_merge($line, array(
                            "line_key" => $lineKey,
                            "acc" => $employee["employee_property_list"][$line["acc_key"]]
                                ?: CompanyUnit::GetInheritablePropertyCompanyUnit(
                                    $employee["employee_property_list"]["company_unit_id"],
                                    $line["acc_key"]
                                ),
                            "group_id" => $receiptList->_items[0]["group_id"],
                            "employee_id" => $employeeID,
                            "employee_guid" => $employee["employee_property_list"]["employee_guid"],
                            "cost_center_number" => $employee["employee_property_list"]["cost_center_number"],
                            "month_key" => $monthMapKey
                        ));
                    }
                }
            }
        }

        return $lineList;
    }

    /**
     * Return main product code
     *
     * @return string
     */
    abstract function GetMainProductCode();

    /**
     * Return advanced security product code
     *
     * @return string
     */
    abstract function GetAdvancedSecurityProductCode();

    /**
     * Returns list of replacements
     *
     * @param $specificProductGroup AbstractSpecificProductGroup
     *
     * @return array
     */
    abstract public function GetReplacementsList($employeeID = false, $document_date = "");

    /**
     * Return container
     *
     * @return string
     */
    abstract function GetContainer();

    /**
     * Return generation voucher option code
     *
     * @return string
     */
    abstract function GetGenerationVoucherOptionCode();

    /** Returns mapped statistics about vouchers that already exist and are expected to be generated
     *
     * @param $employeeID
     * @param $groupID
     * @param Voucher|null $voucher new voucher to check this voucher's amount validity
     *
     * @return array
     */
    public function MapVoucherListToMonth($employeeID, $groupID, ?Voucher $voucher = null)
    {
        $excludeVoucherID = null;

        $voucherList = new VoucherList("company");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID($employeeID, $groupID, false, false, true, true);

        if ($voucher != null) {
            $excludeVoucherID = $voucher->GetProperty("voucher_id");
            $newVoucher = clone $voucher;
            $newVoucher->SetProperty("voucher_id", null);

            $isVoucherCron = $newVoucher->GetProperty("IsVoucherCron");

            if ($newVoucher->GetProperty("recurring") == "Y" && $newVoucher->GetProperty("recurring_end_date") != null) {
                $newVoucherEndDate = $newVoucher->GetProperty("recurring_end_date");
            } elseif ($newVoucher->GetProperty("recurring") == "Y") {
                $newVoucherEndDate = date('31.12.Y', strtotime($newVoucher->GetProperty("voucher_date") . " + 1 year"));
            } else {
                $newVoucherEndDate = date('t.m.Y', strtotime($newVoucher->GetProperty("voucher_date")));
            }

            $newVoucher->SetProperty("amount", $newVoucher->GetProperty("amount"));
            $voucherList->AppendItem($newVoucher->GetProperties());
        } else {
            $isVoucherCron = false;
            $newVoucherEndDate = null;
        }

        //forming the map itself
        $voucherMap = array();
        foreach ($voucherList->_items as $voucher) {
            if ($excludeVoucherID != null && $voucher["voucher_id"] == $excludeVoucherID) {
                continue;
            }

            $date = $voucher["voucher_date"];
            if ($voucher["recurring"] == "Y" && $voucher["recurring_end_date"] != null && !$isVoucherCron) {
                $voucherEndDate = $voucher["recurring_end_date"];
            } elseif ($voucher["recurring"] == "Y") {
                $voucherEndDate = date('31.12.Y', strtotime($date . " + 1 year"));
            } else {
                $voucherEndDate = date('t.m.Y', strtotime($date));
            }

            $endDate = $newVoucherEndDate != null && strtotime($newVoucherEndDate) <= strtotime($voucherEndDate) || $voucher["voucher_id"] == null
                ? $newVoucherEndDate
                : $voucherEndDate;

            $recurring = false;
            do {
                if (!$recurring || strtotime($date) > strtotime(GetCurrentDate())) {
                    $month = date('Y-m', strtotime($date));
                    $voucherMap[$month]["voucher_ids"][] = $voucher["voucher_id"];

                    if (isset($voucherMap[$month]["count"])) {
                        $voucherMap[$month]["count"]++;
                    } else {
                        $voucherMap[$month]["count"] = 1;
                    }

                    if (isset($voucherMap[$month]["amount"])) {
                        $voucherMap[$month]["amount"] += $voucher["amount"];
                    } else {
                        $voucherMap[$month]["amount"] = $voucher["amount"];
                    }
                }

                if ($voucher["recurring"] == "Y") {
                    $recurring = true;
                    switch ($voucher["recurring_frequency"]) {
                        case "yearly":
                            $date = date('d.m.Y', strtotime($date . " +1 year"));
                            break;
                        case "quarterly":
                            $date = date('d.m.Y', strtotime($date . " +3 month"));
                            break;
                        default:
                            $date = date('d.m.Y', strtotime($date . " +1 month"));
                    }
                } else {
                    $date = date('d.m.Y', strtotime($endDate . " +1 month"));
                }
            } while (strtotime($date) <= strtotime($endDate));
        }
        ksort($voucherMap);

        return $voucherMap;
    }

    public function GetUnitCount()
    {
        return 0;
    }

    public function GetFlexOptionFreeUnits()
    {
        return 0;
    }

    public function GetFlexOptionUnitPrice()
    {
        return 0;
    }

    public function GetFlexOptionUnitPercentage()
    {
        return 0;
    }

    public function GetNumberOfPaymentMonth($employeeID, $date)
    {
        return 0;
    }
}
