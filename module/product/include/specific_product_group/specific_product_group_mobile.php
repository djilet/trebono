<?php

/**
 * User: der
 * Date: 24.08.18
 * Time: 17:22
 */

class SpecificProductGroupMobile extends AbstractSpecificProductGroup
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
        $maxMonthly = floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__MOBILE__MAIN__EMPLOYER_GRANT,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        ));

        return round($maxMonthly, 2);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ValidateReceiptApprove()
     */
    public function ValidateReceiptApprove($receipt)
    {
        $errorObject = new LocalObject();

        $unit = $this->GetUnit($receipt);

        $monthDateFrom = date("Y-m-01", strtotime($receipt->GetProperty("document_date")));
        $monthDateTo = date("Y-m-t", strtotime($receipt->GetProperty("document_date")));
        $approvedReceiptAmount = ReceiptList::GetRealApprovedAmount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $monthDateFrom,
            $monthDateTo
        );

        if ($approvedReceiptAmount >= $unit) {
            $errorObject->AddError("receipt-monthly-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue("receipt_autodeny_month_limit"));
        }

        /*
        $request = new LocalObject();
        $request->SetProperty("FilterEmployeeID", $receipt->GetProperty("employee_id"));
        $request->SetProperty("FilterProductGroup", $receipt->GetProperty("group_id"));
        $request->SetProperty("FilterPayrollDate", $receipt->GetProperty("document_date"));
        $paymentMonth = $this->GetNumberOfPaymentMonth(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        $request->SetProperty(
            "FilterPayrollDateFrom",
            date("Y-m-1", strtotime($receipt->GetProperty("document_date") . " - " . $paymentMonth . " month"))
        );
        $receiptList = new ReceiptList("receipt");
        $receiptList = $receiptList->GetShortReceiptListForAdmin("receipt_id", $request);
        if (!empty($receiptList)) {
            $errorObject->AddError("receipt-payroll-exists", "receipt");
        }
        */
        if ($errorObject->HasErrors()) {
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        $ageDeduction = floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__MOBILE__MAIN__AGE_DEDUCTION,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        ));
        $amountApproved = $receipt->GetProperty("amount_approved") - ($receipt->GetProperty("amount_approved") / 100 * $ageDeduction);

        $realAmountApproved = min(round($unit - $approvedReceiptAmount, 2), $amountApproved);
        $realAmountApproved = max($realAmountApproved, 0);

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

        $monthlyLimit = $this->GetUnit($optionReceipt);

        $monthDateFrom = date("Y-m-01", strtotime($receipt->GetProperty("document_date")));
        $monthDateTo = date("Y-m-t", strtotime($receipt->GetProperty("document_date")));
        $approvedReceiptAmount = ReceiptList::GetRealApprovedAmount(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("group_id"),
            null,
            $monthDateFrom,
            $monthDateTo
        );
        $availableAmount = $monthlyLimit - $approvedReceiptAmount;

        $optionList = array();

        $optionList[] = array(
            "title_translation" => GetTranslation("option-" . OPTION__MOBILE__MAIN__EMPLOYER_GRANT, "product"),
            "value" => GetPriceFormat($monthlyLimit) . "€"
        );
        $optionList[] = array(
            "title_translation" => GetTranslation("available-receipt-value-month", "product"),
            "value" => GetPriceFormat($availableAmount) . "€"
        );

        $optionCodes = array(OPTION__MOBILE__MAIN__PAYMENT_MONTH_QTY);
        foreach ($optionCodes as $code) {
            $optionList[] = array(
                "title_translation" => GetTranslation("option-" . $code, "product"),
                "value" => Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $code,
                    $optionReceipt->GetIntProperty("employee_id"),
                    $optionReceipt->GetProperty("document_date")
                )
            );
        }

        return $optionList;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $lineList = array();
        $employeeMap = array();
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);

        $dateTo = date_create($payrollDate)->format("Y-m-t");
        $exportMonth = date("n", strtotime($dateTo));
        $exportYear = date("Y", strtotime($dateTo));

        $mainProductID = Product::GetProductIDByCode($this->GetMainProductCode());

        $contract = new Contract("product");
        $activeEmployeeIDs = $contract->GetEmployeeIDsWithContractForDate($mainProductID, $dateTo);

        $activeEmployeeIDs = is_array($activeEmployeeIDs) ? array_intersect($employeeIDs, $activeEmployeeIDs) : [];

        foreach ($activeEmployeeIDs as $employeeID) {
            $paymentMonthNumber = $this->GetNumberOfPaymentMonth($employeeID, $dateTo);

            $lastReceiptList = ReceiptList::GetLastApprovedReceipt(
                $employeeID,
                $groupID,
                $dateTo,
                ["approved"]
            );

            $stmt = GetStatement();
            $where = [];
            $where[] = "employee_id=" . intval($employeeID);
            $where[] = "group_id=" . intval($groupID);
            $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($dateTo);
            $where[] = "DATE(document_date) + " . $paymentMonthNumber . " * INTERVAL '1 month'
                        <= " . Connection::GetSQLDate($dateTo);
            $where[] = "status='approved'";
            $where[] = "archive='N'";
            if ($exportType == "pdf") {
                $where[] = "pdf_export='0'";
            } elseif ($exportType == "datev") {
                $where[] = "datev_export='0'";
            }
            $query = "SELECT receipt_id, legal_receipt_id, employee_id, group_id,
                            created, amount_approved, real_amount_approved, document_date,
                            pdf_export, datev_export
						FROM receipt "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
					   ORDER BY document_date DESC";
            $notExportedList = $stmt->FetchList($query);

            if ($notExportedList) {
                foreach ($notExportedList as $key => $value) {
                    $notExportedList[$key]["not_exported"] = 1;
                }
                $lastReceiptList = array_merge($lastReceiptList, $notExportedList);
            }

            if (!is_array($lastReceiptList) || empty($lastReceiptList)) {
                continue;
            }

            if (!isset($employeeMap[$employeeID])) {
                $employee = new Employee("company");
                $employee->LoadByID($employeeID);

                $employeeMap[$employeeID] = array(
                    "employee_property_list" => $employee->GetProperties(),
                    "salary_option" => Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__MOBILE__MAIN__SALARY_OPTION,
                        $employeeID,
                        $dateTo
                    ),
                    "month_map" => array()
                );
            }

            $lastReceiptMonth = null;
            $monthCount = 1;
            foreach ($lastReceiptList as $receipt) {
                $currentReceiptYearMonth = date("Y-m", strtotime($receipt["document_date"]));
                if (
                    $exportType == "pdf" && $receipt["pdf_export"] == 0
                    || $exportType == "datev" && $receipt["datev_export"] == 0
                ) {
                    $receipt["not_exported"] = 1;
                    if ($lastReceiptMonth == null) {
                        $currentReceiptMonth = date("m", strtotime($receipt["document_date"]));
                        $monthCount = $exportMonth < $currentReceiptMonth
                            ? $exportMonth - (12 - $currentReceiptMonth) + 1
                            : $exportMonth - $currentReceiptMonth + 1;
                        $monthCount = min($paymentMonthNumber, $monthCount);
                    }
                }

                if ($lastReceiptMonth == null) {
                    $lastReceiptMonth = date("Y-m", strtotime($receipt["document_date"]));
                }

                if (
                    strtotime($lastReceiptMonth) != strtotime($currentReceiptYearMonth)
                    && !isset($receipt["not_exported"])
                ) {
                    continue;
                }

                if (strtotime($lastReceiptMonth) == strtotime($currentReceiptYearMonth)) {
                    $receipt["real_amount_approved"] *= $monthCount;
                }

                $lastReceipt = new Receipt("company", $receipt);

                $monthMapKey = date("Ym", strtotime($dateTo));
                if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey] = array(
                        "positive_line" => array(
                            "title" => "Positive",
                            "acc_key" => "acc_mobile_subsidy_tax_free",
                            "amount" => null,
                            "receipt_ids" => [],
                            "legal_receipt_ids" => []
                        ),
                        "negative_line" => [
                            "title" => "Negative",
                            "acc_key" => "acc_gross_salary",
                            "amount" => null,
                            "receipt_ids" => [],
                            "legal_receipt_ids" => [],
                        ]
                    );
                }

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"]
                    += $lastReceipt->GetProperty("real_amount_approved");
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][]
                    = $lastReceipt->GetProperty("receipt_id");
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][]
                    = $lastReceipt->GetProperty("legal_receipt_id");
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
                        "group_id" => $groupID,
                        "employee_id" => $employeeID,
                        "employee_guid" => $employee["employee_property_list"]["employee_guid"],
                        "cost_center_number" => $employee["employee_property_list"]["cost_center_number"],
                        "month_key" => $monthMapKey
                    ));
                }
            }
        }

        return $lineList;
    }

    /**
     * @param $employeeID
     * @param $groupID
     * @param $monthlyStatisticsDate
     * @param $statusList
     *
     * @return int
     */
    public function GetAmountApprovedMonth(
        $employeeID,
        $groupID,
        $monthlyStatisticsDate,
        $statusList
    ) {
        $lastReceiptList = ReceiptList::GetLastApprovedReceipt(
            $employeeID,
            $groupID,
            $monthlyStatisticsDate,
            ["approved", "approve_proposed"]
        );

        $lastReceiptMonth = null;
        $approvedAmount = 0;
        foreach ($lastReceiptList as $receipt) {
            if ($lastReceiptMonth == null) {
                $lastReceiptMonth = date("Y-m", strtotime($receipt["document_date"]));
            }

            $currentReceiptMonth = date("Y-m", strtotime($receipt["document_date"]));
            if (
                strtotime($lastReceiptMonth) != strtotime($currentReceiptMonth)
                || !in_array($receipt["status"], $statusList)
            ) {
                continue;
            }

            $approvedAmount += $receipt["real_amount_approved"];
        }

        return $approvedAmount;
    }

    public function GetAmountApprovedYear($employeeID, $groupID, $date, $statusList)
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
                $statusList
            );
        }

        return $amountApproved;
    }

    function GetMainProductCode()
    {
        return PRODUCT__MOBILE__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__MOBILE__ADVANCED_SECURITY;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "", $agreements = true)
    {
        if ($agreements) {
            $properties = array(
                "amount_per_month",
                "mobile_model",
                "mobile_number"
            );
        } else {
            $properties = array(
                "mobile_model",
                "mobile_number"
            );
        }

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, "product")
            );
        }

        if ($employeeID) {
            if ($agreements) {
                $amountPerMonth = $this->GetUnit(new Receipt("receipt", [
                    "document_date" => $document_date,
                    "employee_id" => $employeeID,
                ]));

                $values["amount_per_month"] = GetPriceFormat($amountPerMonth);
            }

            $values["mobile_model"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__MOBILE__MAIN__MOBILE_MODEL,
                $employeeID,
                $document_date
            );
            $values["mobile_number"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__MOBILE__MAIN__MOBILE_NUMBER,
                $employeeID,
                $document_date
            );
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__MOBILE;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }

    public function GetNumberOfPaymentMonth($employeeID, $date)
    {
        return intval(
            Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__MOBILE__MAIN__PAYMENT_MONTH_QTY,
                $employeeID,
                $date
            )
        );
    }
}
