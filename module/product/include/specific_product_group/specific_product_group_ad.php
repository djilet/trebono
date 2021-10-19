<?php

/**
 * User: der
 * Date: 24.08.18
 * Time: 17:22
 */

class SpecificProductGroupAd extends AbstractSpecificProductGroup
{
    /**
     * Returns cost of unit for passed receipt based on its owner and receipt date
     * Currently unit is a maximum sum can be approved for one receipt
     *
     * @param Receipt $receipt
     *
     * @return float cost of unit
     */
    public function GetUnit($receipt)
    {
        return floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__MAX_MONTHLY,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        ));
    }

    public function GetMaxYearly($employeeID, $date)
    {
        return Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__MAX_YEARLY,
            $employeeID,
            $date
        );
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ValidateReceiptApprove()
     */
    public function ValidateReceiptApprove($receipt)
    {
        //only 12 receipts can be approved per year
        $yearDateFrom = date("Y-01-01", strtotime($receipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($receipt->GetProperty("document_date")));
        $receiptApprovedReceiptCount = ReceiptList::GetApprovedReceiptCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $yearDateFrom,
            $yearDateTo
        );
        if ($receiptApprovedReceiptCount >= 12) {
            $receipt->AddError("receipt-yearly-limit-exceed", "receipt");

            return false;
        }

        //if there was a receipt in current month, we need to include this month in used
        $lastReceiptList = ReceiptList::GetLastApprovedReceipt(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("document_date"),
            ["approved", "approve_proposed"],
            1
        );
        $lastReceipt = null;
        if (!empty($lastReceiptList)) {
            $lastReceipt = $lastReceiptList[0];
        }
        $approvedDateCheck = $receipt->GetProperty("document_date");
        if (
            $lastReceipt != null
            && date("Ym", strtotime($lastReceipt["document_date"])) != date("Ym", strtotime($approvedDateCheck))
        ) {
            $approvedDateCheck = date(
                "Y-m-d",
                strtotime($receipt->GetProperty("document_date") . "- 1 month")
            );
        }
        if (
            date("Y", strtotime($approvedDateCheck)) !=
            date("Y", strtotime($receipt->GetProperty("document_date")))
        ) {
            $usedAmount = 0;
        } else {
            $usedAmount = $this->GetAmountApprovedYear(
                $receipt->GetProperty("employee_id"),
                $receipt->GetProperty("group_id"),
                $approvedDateCheck,
                ["approved", "approve_proposed"],
                true
            );
        }

        //real amount approved depends on payment type
        $paymentType = $this->GetPaymentType(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        switch ($paymentType) {
            case "yearly":
                $paymentMonth = $this->GetPaymentMonthForYearly(
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("document_date")
                );
                if ($paymentMonth == null) {
                    $receipt->AddError(
                        "error-ad-receipt-yearly-payment-month-empty",
                        "receipt"
                    );

                    return false;
                }
                $yearlyLimit = $this->GetMaxYearly(
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("document_date")
                );
                $realAmountApproved = max(floatval($yearlyLimit - $usedAmount), 0);
                break;
            case "monthly":
                $monthlyLimit = $this->GetUnit($receipt);
                $yearlyLimit = $monthlyLimit * 12;

                $realAmountApprovedArray = [];
                $realAmountApprovedArray[] = date("Ym", strtotime($lastReceipt["document_date"]))
                        == date("Ym", strtotime($receipt->GetProperty("document_date")))
                    ? floatval($monthlyLimit - $lastReceipt["real_amount_approved"])
                    : $monthlyLimit;
                $realAmountApprovedArray[] = max(floatval($yearlyLimit - $usedAmount), 0);
                $realAmountApproved = max(min($realAmountApprovedArray), 0);
                break;
            default:
                $realAmountApproved = 0;
        }

        $receipt->SetProperty("amount_approved", $realAmountApproved);
        $receipt->SetProperty("real_amount_approved", $realAmountApproved);

        return true;
    }

    /**
     * Appends additional info specific for current product group
     *
     * @param Receipt $receipt
     */
    public function AppendAdditionalInfo($receipt)
    {
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetOptions()
     */
    public function GetOptions($receipt)
    {
        $optionReceipt = clone $receipt;
        if (!$optionReceipt->GetProperty("document_date")) {
            $optionReceipt->SetProperty("document_date", GetCurrentDate());
        }

        $paymentType = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__RECEIPT_OPTION,
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date")
        );

        $optionList = [];
        switch ($paymentType) {
            case "yearly":
                $optionCodes = [OPTION__AD__MAIN__MAX_YEARLY, OPTION__AD__MAIN__PAYMENT_MONTH];
                break;
            case "monthly":
                $optionCodes = [OPTION__AD__MAIN__MAX_MONTHLY, OPTION__AD__MAIN__PAYMENT_MONTH_QTY];
                break;
            default:
                $optionCodes = [];
        }

        foreach ($optionCodes as $code) {
            $optionList[] = [
                "title_translation" => GetTranslation("option-" . $code, "product"),
                "value" =>
                    $code == OPTION__AD__MAIN__MAX_YEARLY || $code == OPTION__AD__MAIN__MAX_MONTHLY
                        ? GetPriceFormat(Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            $code,
                            $optionReceipt->GetIntProperty("employee_id"),
                            $optionReceipt->GetProperty("document_date")
                        )) . "€"
                        : Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            $code,
                            $optionReceipt->GetIntProperty("employee_id"),
                            $optionReceipt->GetProperty("document_date")
                        ),
            ];
        }

        $yearDateFrom = date("Y-01-01", strtotime($optionReceipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($optionReceipt->GetProperty("document_date")));
        $receiptApprovedReceiptCount = ReceiptList::GetApprovedReceiptCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $yearDateFrom,
            $yearDateTo
        );
        $optionList[] = [
            "title_translation" => GetTranslation("info-current-year-receipt-count-left", "product"),
            "value" => max(12 - $receiptApprovedReceiptCount, 0),
        ];

        return $optionList;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $lineList = [];
        $employeeMap = [];
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);

        $dateTo = date_create($payrollDate)->format("Y-m-t");
        $exportMonth = date("n", strtotime($dateTo));
        $exportYear = date("Y", strtotime($dateTo));

        $mainProductID = Product::GetProductIDByCode($this->GetMainProductCode());

        $contract = new Contract("product");
        $activeEmployeeIDs = $contract->GetEmployeeIDsWithContractForDate($mainProductID, $dateTo);

        $activeEmployeeIDs = is_array($activeEmployeeIDs) ? array_intersect($employeeIDs, $activeEmployeeIDs) : [];

        foreach ($activeEmployeeIDs as $employeeID) {
            $paymentMonth = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__PAYMENT_MONTH,
                $employeeID,
                $dateTo
            );
            $paymentType = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__RECEIPT_OPTION,
                $employeeID,
                $dateTo
            );

            $lastReceiptList = ReceiptList::GetLastApprovedReceipt(
                $employeeID,
                $groupID,
                $dateTo,
                ["approved"],
                1
            );
            $lastReceipt = null;
            if (!empty($lastReceiptList)) {
                $lastReceipt = new Receipt("receipt", $lastReceiptList[0]);
            }

            if (empty($lastReceipt)) {
                continue;
            }

            $contract->LoadLatestActiveContract(OPTION_LEVEL_EMPLOYEE, $employeeID, $mainProductID);
            $isContractActiveForPaymentMonth = strtotime(date(
                "Y-m-01",
                strtotime($contract->GetProperty("start_date"))
            )) <= strtotime($exportYear . "-" . $paymentMonth . "-01");
            $maxMonthly = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__MAX_MONTHLY,
                $employeeID,
                $dateTo
            );
            $maxYearly = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__MAX_YEARLY,
                $employeeID,
                $dateTo
            );

            if (!isset($employeeMap[$employeeID])) {
                $employee = new Employee("company");
                $employee->LoadByID($employeeID);

                $employeeMap[$employeeID] = [
                    "employee_property_list" => $employee->GetProperties(),
                    "salary_option" => Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__AD__MAIN__SALARY_OPTION,
                        $employeeID,
                        $dateTo
                    ),
                    "month_map" => [],
                ];
            }

            $monthMapKey = date("Ym", strtotime($dateTo));
            if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                $employeeMap[$employeeID]["month_map"][$monthMapKey] = [
                    "positive_line" => [
                        "title" => "Positive",
                        "acc_key" => "acc_net_income",
                        "amount" => null,
                        "receipt_ids" => [],
                        "legal_receipt_ids" => [],
                    ],
                    "negative_line" => [
                        "title" => "Negative",
                        "acc_key" => "acc_gross_salary",
                        "amount" => null,
                        "receipt_ids" => [],
                        "legal_receipt_ids" => [],
                    ],
                ];
            }
            $receiptMonth = date("n", strtotime($lastReceipt->GetProperty("document_date")));
            $receiptYear = date("Y", strtotime($lastReceipt->GetProperty("document_date")));

            if (
                $paymentType == "yearly"
                && $isContractActiveForPaymentMonth
                && ($exportMonth == $paymentMonth
                    || ($exportMonth == $receiptMonth && $receiptYear == $exportYear && $paymentMonth < $receiptMonth))
            ) {
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] += $maxYearly;
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][]
                    = $lastReceipt->GetProperty("receipt_id");
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][]
                    = $lastReceipt->GetProperty("legal_receipt_id");
            }
            if ($paymentType != "monthly" || !$contract->GetProperty("contract_id")) {
                continue;
            }

            $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] += $maxMonthly;
            $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][]
                = $lastReceipt->GetProperty("receipt_id");
            $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][]
                = $lastReceipt->GetProperty("legal_receipt_id");
        }

        foreach ($employeeMap as $employeeID => $employee) {
            foreach ($employee["month_map"] as $monthMapKey => $month) {
                foreach ($month as $lineKey => $line) {
                    if (count($line["receipt_ids"]) == 0) {
                        continue;
                    }

                    $lineList[] = array_merge($line, [
                        "line_key" => $lineKey,
                        "acc" => $employee["employee_property_list"][$line["acc_key"]]
                            ?: CompanyUnit::GetInheritablePropertyCompanyUnit(
                                $employee["employee_property_list"]["company_unit_id"],
                                $line["acc_key"]
                            ),
                        "group_id" => $groupID,
                        "employee_id" => $employeeID,
                        "employee_guid" => $employee["employee_property_list"]["employee_guid"],
                        "cost_center_number" => $employee["employee_property_list"]["cost_center_number"],
                        "month_key" => $monthMapKey,
                    ]);
                }
            }
        }

        return $lineList;
    }

    public function GetNumberOfPaymentMonth($employeeID, $date)
    {
        $paymentType = $this->GetPaymentType($employeeID, $date);

        return $paymentType == "yearly" ? 12 : Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__PAYMENT_MONTH_QTY,
            $employeeID,
            $date
        );
    }

    public function GetPaymentType($employeeID, $date)
    {
        return Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__RECEIPT_OPTION,
            $employeeID,
            $date
        );
    }

    public function GetPaymentMonthForYearly($employeeID, $date)
    {
        return Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__AD__MAIN__PAYMENT_MONTH,
            $employeeID,
            $date
        );
    }

    /**
     * @param $employeeID
     * @param $groupID
     * @param $monthlyStatisticsDate
     * @param $statusList
     * @param bool $forReceiptVerification
     *
     * @return int
     */
    public function GetAmountApprovedMonth(
        $employeeID,
        $groupID,
        $monthlyStatisticsDate,
        $statusList,
        $forReceiptVerification = false
    ) {
        $lastReceiptList = ReceiptList::GetLastApprovedReceipt(
            $employeeID,
            $groupID,
            $monthlyStatisticsDate,
            ["approved", "approve_proposed"],
            1
        );
        $paymentType = $this->GetPaymentType($employeeID, $monthlyStatisticsDate);
        $optionReceipt = new Receipt(
            "company",
            ["employee_id" => $employeeID,
                "document_date" => $monthlyStatisticsDate]
        );
        $maxMonthly = $this->GetUnit($optionReceipt);

        $approvedAmount = 0;
        foreach ($lastReceiptList as $receipt) {
            if (
                !in_array($receipt["status"], $statusList) ||
                date("Y", strtotime($monthlyStatisticsDate)) !=
                date("Y", strtotime($receipt["document_date"]))
            ) {
                continue;
            }

            $currentMonth = date("Y-m", strtotime($monthlyStatisticsDate));
            $lastReceiptMonth = date("Y-m", strtotime($receipt["document_date"]));
            if ($paymentType == "yearly") {
                if (
                    !$forReceiptVerification
                    && strtotime($lastReceiptMonth) != strtotime($currentMonth)
                ) {
                    continue;
                }
                $approvedAmount += $receipt["real_amount_approved"];
            } else {
                if ($receipt["real_amount_approved"] > $maxMonthly) {
                    if (strtotime($lastReceiptMonth) != strtotime($currentMonth)) {
                        continue;
                    }

                    $approvedAmount += $receipt["real_amount_approved"];
                } else {
                    $approvedAmount += $receipt["real_amount_approved"];
                }
            }
        }

        return $approvedAmount;
    }

    public function GetAmountAvailableMonth(
        $employeeID,
        $groupID,
        $monthlyStatisticsDate
    ) {
        $paymentType = $this->GetPaymentType($employeeID, $monthlyStatisticsDate);
        $paymentMonth = $this->GetPaymentMonthForYearly($employeeID, $monthlyStatisticsDate);

        $usedAmount = 0;
        $lastMonthDate = date_create(
            date("Y-m-1", strtotime($monthlyStatisticsDate))
        );
        if ($lastMonthDate->format("m") !== "01") {
            $lastMonthDate->modify("-1 month");
            $usedAmount = $this->GetAmountApprovedYear(
                $employeeID,
                $groupID,
                $lastMonthDate->format("Y-m-t"),
                ["approve_proposed", "approved"]
            );
        }
        if ($paymentType == "yearly" && $paymentMonth != null) {
            $available = $this->GetMaxYearly($employeeID, $monthlyStatisticsDate);
            $available -= $usedAmount;
        } else {
            $optionReceipt = new Receipt(
                "company",
                ["employee_id" => $employeeID,
                    "document_date" => $monthlyStatisticsDate]
            );
            $unit = $this->GetUnit($optionReceipt);

            $available = $usedAmount > $unit * $lastMonthDate->format("m") ? 0 : $unit;
        }

        return max($available, 0);
    }

    public function GetAmountApprovedYear($employeeID, $groupID, $date, $statusList, $forReceiptVerification = false)
    {
        $currentMonth = date("n", strtotime($date));
        $currentYear = date("Y", strtotime($date));

        $amountApproved = 0;
        for ($i = 1; $i <= $currentMonth; $i++) {
            $approvedMonthDate = date("Y-m-t", strtotime($currentYear . "-" . $i, "-1"));
            $amountApproved += $this->GetAmountApprovedMonth(
                $employeeID,
                $groupID,
                $approvedMonthDate,
                $statusList,
                $forReceiptVerification
            );
        }

        return $amountApproved;
    }

    public function GetAmountAvailableYear($employeeID, $groupID, $date, $monthCount)
    {
        $currentMonth = date("n", strtotime($date));
        $currentYear = date("Y", strtotime($date));

        $amountAvailable = 0;
        for ($i = $currentMonth - $monthCount + 1; $i <= $currentMonth; $i++) {
            $availableMonthDate = date("Y-m-t", strtotime($currentYear . "-" . $i, "-1"));
            $result = $this->GetAmountAvailableMonth(
                $employeeID,
                $groupID,
                $availableMonthDate
            );
            $amountAvailable += $result;
        }

        $paymentType = $this->GetPaymentType($employeeID, $date);
        if ($paymentType == "yearly") {
            $maxYearly = $this->GetMaxYearly($employeeID, $date);
            $maxYearly = min($maxYearly, $amountAvailable);
        } else {
            $optionReceipt = new Receipt(
                "company",
                ["employee_id" => $employeeID,
                    "document_date" => $date]
            );
            $maxYearly = $this->GetUnit($optionReceipt) * $monthCount;
            $maxYearly = max($maxYearly, $amountAvailable);
        }

        return $maxYearly;
    }

    function GetMainProductCode()
    {
        return PRODUCT__AD__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__AD__ADVANCED_SECURITY;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "")
    {
        $properties = [
            "amount_per_month",
            "payment_month",
            "payment_month_qty",
        ];

        $replacements = [];
        $values = [];
        foreach ($properties as $property) {
            $replacements[] = [
                "template" => "%" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, "product"),
            ];
        }

        if ($employeeID) {
            $paymentType = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__RECEIPT_OPTION,
                $employeeID,
                $document_date
            );

            switch ($paymentType) {
                case "yearly":
                    $optionCode = OPTION__AD__MAIN__MAX_YEARLY;
                    break;
                case "monthly":
                    $optionCode = OPTION__AD__MAIN__MAX_MONTHLY;
                    break;
                default:
                    $optionCode = null;
            }

            $maxMonthly = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                $optionCode,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($maxMonthly);
            $values["payment_month"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__PAYMENT_MONTH,
                $employeeID,
                $document_date
            );

            $values["payment_month_qty"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__AD__MAIN__PAYMENT_MONTH_QTY,
                $employeeID,
                $document_date
            );
        }

        return ["ReplacementList" => $replacements, "ValueList" => $values];
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__AD;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
