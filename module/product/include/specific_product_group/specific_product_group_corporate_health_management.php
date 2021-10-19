<?php

class SpecificProductGroupCorporateHealthManagement extends AbstractSpecificProductGroup
{
    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetUnit()
     */
    public function GetUnit($receipt)
    {
        $maxMonthly = floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        ));

        return round($maxMonthly, 2);
    }

    public function GetMaxYearly($receipt)
    {
        $maxMonthly = floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY,
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

        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $monthDateFrom = date("Y-m-01", strtotime($receipt->GetProperty("document_date")));
        $monthDateTo = date("Y-m-t", strtotime($receipt->GetProperty("document_date")));
        $yearDateFrom = date('Y-01-01', strtotime($receipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($receipt->GetProperty("document_date")));

        $amountApprovedMonth = ReceiptList::GetRealApprovedAmount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $monthDateFrom,
            $monthDateTo
        );
        $amountApprovedYear = ReceiptList::GetRealApprovedAmount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $yearDateFrom,
            $yearDateTo
        );

        $amountAvailableMonth = $this->GetUnit($receipt);
        $amountAvailableYear = $this->GetMaxYearly($receipt);

        $amountApproved = $receipt->GetProperty("amount_approved");
        if ($amountApproved > $amountAvailableMonth || $amountAvailableMonth - $amountApprovedMonth == 0) {
            $errorObject->AddError("receipt-monthly-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue('receipt_autodeny_month_limit'));
        }
        if ($amountApprovedYear + $amountApproved > $amountAvailableYear) {
            $errorObject->AddError("receipt-yearly-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue('receipt_autodeny_year_limit'));
        }

        if ($errorObject->HasErrors()) {
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        $realAmountApproved = min(round($amountAvailableMonth - $amountApprovedMonth, 2), $amountApproved);
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

        $optionList = array();
        $optionCodes = array(
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_MONTHLY,
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__MAX_YEARLY
        );

        foreach ($optionCodes as $code) {
            $optionList[] = array(
                "title_translation" => GetTranslation("option-" . $code, "product"),
                "value" => GetPriceFormat(Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $code,
                    $optionReceipt->GetIntProperty("employee_id"),
                    $optionReceipt->GetProperty("document_date")
                )) . "€"
            );
        }

        $employee = new Employee("company");
        $employee->LoadByID($receipt->GetProperty("employee_id"));

        $monthDateFrom = date("Y-m-01", strtotime($optionReceipt->GetProperty("document_date")));
        $monthDateTo = date("Y-m-t", strtotime($optionReceipt->GetProperty("document_date")));
        $yearDateFrom = date('Y-01-01', strtotime($optionReceipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($optionReceipt->GetProperty("document_date")));

        $amountApprovedMonth = ReceiptList::GetRealApprovedAmount(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("group_id"),
            null,
            $monthDateFrom,
            $monthDateTo
        );
        $amountApprovedYear = ReceiptList::GetRealApprovedAmount(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("group_id"),
            null,
            $yearDateFrom,
            $yearDateTo
        );

        $amountAvailableMonth = $this->GetUnit($optionReceipt);
        $amountAvailableYear = $this->GetMaxYearly($optionReceipt);

        $optionList[] = array(
            "title_translation" => GetTranslation("available-receipt-value-month", "product"),
            "value" => GetPriceFormat($amountAvailableMonth - $amountApprovedMonth)."€"
        );
        $optionList[] = array(
            "title_translation" => GetTranslation("available-receipt-value-year", "product"),
            "value" => GetPriceFormat($amountAvailableYear - $amountApprovedYear)."€"
        );

        return $optionList;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $payrollExport = Option::GetInheritableOptionValue(
            OPTION_LEVEL_COMPANY_UNIT,
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__PAYROLL_EXPORT,
            $companyUnitID,
            $payrollDate
        );
        if ($exportType == "pdf" || $payrollExport == "N") {
            return array();
        }

        return $this->GetCommonAddisonExportLineList(
            $companyUnitID,
            $groupID,
            $payrollDate,
            $exportType,
            OPTION__CORPORATE_HEALTH_MANAGEMENT__MAIN__SALARY_OPTION,
            "acc_corporate_health_management"
        );
    }

    function GetMainProductCode()
    {
        return PRODUCT__CORPORATE_HEALTH_MANAGEMENT__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return null;
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
            $optionReceipt = new Receipt(
                "company",
                ["employee_id" => $employeeID,
                    "document_date" => $document_date]
            );
            $optionValue = $this->GetUnit($optionReceipt);

            $values["amount_per_month"] = GetPriceFormat($optionValue);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__CORPORATE_HEALTH_MANAGEMENT;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
