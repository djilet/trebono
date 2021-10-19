<?php

/**
 * User: der
 * Date: 24.08.18
 * Time: 17:22
 */

class SpecificProductGroupBenefit extends AbstractSpecificProductGroup
{
    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetUnit()
     */
    public function GetUnit($receipt)
    {
        return floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__BENEFIT__MAIN__EMPLOYER_GRANT,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("created")
        ));
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
        $receiptOption = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("created")
        );

        if ($receiptOption != "monthly" && $receiptOption != "yearly") {
            $errorObject->AddError("receipt-benefit-unknown-receipt-option-value", "receipt");
        }

        if ($receiptOption == "yearly") {
            /*
            if(!$receipt->ValidateNotEmpty("document_date_from") || !$receipt->ValidateNotEmpty("document_date_to"))
            {
                $errorObject->AddError("receipt-period-empty", "receipt");
            }
            else
            {
                $stmt = GetStatement();
                $where = array();
                $where[] = "receipt_id!=".$receipt->GetIntProperty("receipt_id");
                $where[] = "employee_id=".$receipt->GetIntProperty("employee_id");
                $where[] = "group_id=".$receipt->GetIntProperty("group_id");
                $where[] = "(".Connection::GetSQLDate($receipt->GetProperty("document_date_from"))." BETWEEN document_date_from AND document_date_to
                                OR ".Connection::GetSQLDate($receipt->GetProperty("document_date_to"))." BETWEEN document_date_from AND document_date_to)";
                $query = "SELECT COUNT(*) FROM receipt WHERE ".implode(" AND ", $where);
                if($stmt->FetchField($query) > 0)
                    $errorObject->AddError("receipt-period-intersection-found", "receipt");
            }

            if($errorObject->HasErrors())
            {
                $receipt->AppendErrorsFromObject($errorObject);
                return false;
            }
            */
            $stmt = GetStatement();
            $where = array();
            $where[] = "receipt_id!=" . $receipt->GetIntProperty("receipt_id");
            $where[] = "employee_id=" . $receipt->GetIntProperty("employee_id");
            $where[] = "group_id=" . $receipt->GetIntProperty("group_id");
            $where[] = "status IN('approve_proposed', 'approved')";
            $where[] = "archive='N'";
            $where[] = "DATE(document_date + INTERVAL '+1 year -1 day') >= " . Connection::GetSQLDate($receipt->GetProperty("document_date"));
            $query = "SELECT COUNT(*) FROM receipt WHERE " . implode(" AND ", $where);
            if ($stmt->FetchField($query) > 0) {
                $errorObject->AddError("receipt-case-count-limit-exceed", "receipt");
            }

            $realAmountApproved = 0;
            $max = max(array($unit * 12, 0));
            $realAmountApproved = min(array($receipt->GetProperty("amount_approved"), $max));
            $receipt->SetProperty("real_amount_approved", $realAmountApproved);
        }

        if ($receiptOption == "monthly") {
            $monthDateFrom = date("Y-m-01", strtotime($receipt->GetProperty("document_date")));
            $monthDateTo = date("Y-m-t", strtotime($receipt->GetProperty("document_date")));
            $receiptMonthRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
                $receipt->GetProperty("employee_id"),
                $receipt->GetProperty("group_id"),
                $receipt->GetProperty("receipt_id"),
                $monthDateFrom,
                $monthDateTo
            );

            if ($receiptMonthRealApprovedAmount >= $unit) {
                $errorObject->AddError("receipt-monthly-limit-exceed", "receipt");
            }

            $realAmountApproved = 0;
            $max = max(array($unit - $receiptMonthRealApprovedAmount, 0));
            $realAmountApproved = min(array($receipt->GetProperty("amount_approved"), $max));
            $receipt->SetProperty("real_amount_approved", $realAmountApproved);
        }

        if ($errorObject->HasErrors()) {
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

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

        $optionList = array();
        $optionCodes = array(OPTION__BENEFIT__MAIN__EMPLOYER_GRANT);

        foreach ($optionCodes as $code) {
            $optionList[] = array(
                "title_translation" => GetTranslation("option-" . $code, "product"),
                "value" => Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $code,
                    $receipt->GetIntProperty("employee_id"),
                    $optionReceipt->GetProperty("document_date")
                )
            );
        }

        $unit = $this->GetUnit($optionReceipt);
        $receiptOption = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date")
        );
        if ($receiptOption == "yearly") {
            $yearDateFrom = date("Y-01-01", strtotime($optionReceipt->GetProperty("document_date")));
            $yearDateTo = date("Y-12-31", strtotime($optionReceipt->GetProperty("document_date")));
            $receiptYearRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
                $optionReceipt->GetProperty("employee_id"),
                $optionReceipt->GetProperty("group_id"),
                0,
                $yearDateFrom,
                $yearDateTo
            );
            $yearEuroLeft = max(array($unit * 12 - $receiptYearRealApprovedAmount, 0));

            $optionList[] = array(
                "title_translation" => GetTranslation("info-current-year-euro-left", "product"),
                "value" => GetPriceFormat($yearEuroLeft) . "€"
            );
        } elseif ($receiptOption == "monthly") {
            $monthDateFrom = date("Y-m-01", strtotime($optionReceipt->GetProperty("document_date")));
            $monthDateTo = date("Y-m-t", strtotime($optionReceipt->GetProperty("document_date")));
            $receiptMonthRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
                $optionReceipt->GetProperty("employee_id"),
                $optionReceipt->GetProperty("group_id"),
                0,
                $monthDateFrom,
                $monthDateTo
            );
            $monthEuroLeft = max(array($unit - $receiptMonthRealApprovedAmount, 0));

            $optionList[] = array(
                "title_translation" => GetTranslation("info-current-month-euro-left", "product"),
                "value" => GetPriceFormat($monthEuroLeft) . "€"
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
        $dateTo = date_create($payrollDate)->format("Y-m-t");

        $statusUpdatedTo = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "current_month" ? date("Y-m-d 17:59:00", strtotime($payrollDate)) : date("Y-m-d 17:59:00", strtotime($payrollDate . " + 1 month"));

        $lineList = array();

        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);

        $mainProductID = Product::GetProductIDByCode($this->GetMainProductCode());
        $contract = new Contract("product");
        $activeEmployeeIDs = $contract->GetEmployeeIDsWithContractForDate($mainProductID, $dateTo);

        $activeEmployeeIDs = is_array($activeEmployeeIDs) ? array_intersect($employeeIDs, $activeEmployeeIDs) : array();

        if (count($activeEmployeeIDs) > 0) {
            $monthlyEmployeeIDs = array();
            $yearlyEmployeeIDs = array();

            foreach ($activeEmployeeIDs as $employeeID) {
                $receiptOption = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
                    $employeeID,
                    $dateTo
                );

                if ($receiptOption == "monthly") {
                    $monthlyEmployeeIDs[] = $employeeID;
                } elseif ($receiptOption == "yearly") {
                    $yearlyEmployeeIDs[] = $employeeID;
                }
            }

            $receiptList = new ReceiptList("receipt");

            //load receipt list for employees with monthly receipt option
            //
            // document_date here is the last date of service (real, not cloud), not the day of payment.
            $flag = date_create($dateTo)->format("Ym");
            if (count($monthlyEmployeeIDs) > 0) {
                $monthlyReceiptList = new ReceiptList("receipt");
                $where = array();
                $where[] = "r.employee_id IN(" . implode(", ", $monthlyEmployeeIDs) . ")";
                $where[] = "r.group_id=" . intval($groupID);
                $where[] = "DATE(r.document_date) <= " . Connection::GetSQLDate($dateTo);
                $where[] = "DATE(r.status_updated) <= " . Connection::GetSQLDateTime($statusUpdatedTo);
                if ($exportType == "pdf") {
                    $where[] = "r.pdf_export = '0'";
                } elseif ($exportType == "datev") {
                    $where[] = "r.datev_export = '0'";
                }
                $where[] = "r.status='approved'";
                $where[] = "r.archive='N'";

                $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.amount_approved, r.real_amount_approved, r.document_date 
							FROM receipt AS r "
                    . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
                $monthlyReceiptList->SetOrderBy("document_date_asc");
                $monthlyReceiptList->SetItemsOnPage(0);
                $monthlyReceiptList->LoadFromSQL($query);

                $receiptList->AppendFromObjectList($monthlyReceiptList);
            }

            //load receipt list for employees with yearly receipt option. their document_date is the last day of paid period
            if (count($yearlyEmployeeIDs) > 0) {
                $yearlyReceiptList = new ReceiptList("receipt");
                $stmt = GetStatement();
                $where = array();
                $where[] = "r.employee_id IN(" . implode(", ", $yearlyEmployeeIDs) . ")";
                $where[] = "r.group_id=" . intval($groupID);
                $where[] = "DATE(r.document_date + INTERVAL '+1 year +3 month') >= " . Connection::GetSQLDate($dateTo);
                $where[] = "DATE(r.document_date + INTERVAL '+1 day') <= " . Connection::GetSQLDate($dateTo);
                $where[] = "DATE(r.status_updated) <= " . Connection::GetSQLDateTime($statusUpdatedTo);
                $where[] = "r.status='approved'";
                $where[] = "r.archive='N'";
                if ($exportType == "pdf") {
                    $where[] = "r.pdf_export <= " . Connection::GetSQLString($flag);
                } elseif ($exportType == "datev") {
                    $where[] = "r.datev_export <= " . Connection::GetSQLString($flag);
                }
                $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.amount_approved, r.real_amount_approved, r.document_date, 
								CASE WHEN " . Connection::GetSQLDate($dateTo) . " > r.document_date + INTERVAL '+1 year' THEN 1 ELSE 0 END AS grace 
							FROM receipt AS r "
                    . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " 
							ORDER BY (CASE WHEN " . Connection::GetSQLDate($dateTo) . " > r.document_date + INTERVAL '+1 year' THEN 1 ELSE 0 END) ASC, r.document_date DESC";
                $yearlyReceiptList->LoadFromArray($stmt->FetchList($query));

                $receiptList->AppendFromObjectList($yearlyReceiptList);
            }

            //this code is like common addison export except of yearly receipt export
            if ($receiptList->GetCountItems() > 0) {
                $employeeMap = array();

                foreach ($receiptList->GetItems() as $receipt) {
                    $employeeID = $receipt["employee_id"];
                    if (!isset($employeeMap[$employeeID])) {
                        $employee = new Employee("company");
                        $employee->LoadByID($employeeID);

                        if (in_array($employeeID, $yearlyEmployeeIDs)) {
                            $receiptOption = "yearly";
                        } elseif (in_array($employeeID, $monthlyEmployeeIDs)) {
                            $receiptOption = "monthly";
                        } else {
                            continue;
                        }

                        $employeeMap[$employeeID] = array(
                            "employee_property_list" => $employee->GetProperties(),
                            "salary_option" => Option::GetInheritableOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                OPTION__BENEFIT__MAIN__SALARY_OPTION,
                                $employeeID,
                                $receiptOption == "monthly" ? $receipt["document_date"] : $dateTo
                            ),
                            "receipt_option" => $receiptOption
                        );
                    }

                    if ($employeeMap[$employeeID]["receipt_option"] == "monthly") {
                        $monthMapKey = date("Ym", strtotime($receipt["document_date"]));
                        if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                            $employeeMap[$employeeID]["month_map"][$monthMapKey] = array(
                                "positive_line" => array(
                                    "title" => "Positive",
                                    "acc_key" => "acc_grant_of_materials",
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

                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] += $receipt["real_amount_approved"];
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][] = $receipt["receipt_id"];
                        $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][] = $receipt["legal_receipt_id"];
                    }

                    if ($employeeMap[$employeeID]["receipt_option"] != "yearly") {
                        continue;
                    }

                    //export to requested month - not to receipt's month
                    $monthMapKey = date("Ym", strtotime($dateTo));
                    if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                        $employeeMap[$employeeID]["month_map"][$monthMapKey] = array(
                            "positive_line" => array(
                                "title" => "Positive",
                                "acc_key" => "acc_grant_of_materials",
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
                            ),
                            "non_grace_receipt_processed" => false
                        );
                    }

                    //important! if non-grace-period receipt is exporting then ignore all grace-period receipts
                    if ($receipt["grace"] && $employeeMap[$employeeID]["month_map"][$monthMapKey]["non_grace_receipt_processed"]) {
                        continue;
                    }

                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["amount"] += round(
                        $receipt["real_amount_approved"] / 12,
                        2
                    );
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["receipt_ids"][] = $receipt["receipt_id"];
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["positive_line"]["legal_receipt_ids"][] = $receipt["legal_receipt_id"];

                    if ($receipt["grace"]) {
                        continue;
                    }

                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["non_grace_receipt_processed"] = true;
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
        }

        return $lineList;
    }

    function GetMainProductCode()
    {
        return PRODUCT__BENEFIT__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__BENEFIT__ADVANCED_SECURITY;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "")
    {
        $properties = array(
            "amount_per_month"
        );

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, "product")
            );
        }

        if ($employeeID) {
            $optionValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__BENEFIT__MAIN__EMPLOYER_GRANT,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($optionValue);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__BENEFIT;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
