<?php

class SpecificProductGroupTransport extends AbstractSpecificProductGroup
{
    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetUnit()
     */
    public function GetUnit($receipt)
    {
        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ValidateReceiptApprove()
     */
    public function ValidateReceiptApprove($receipt)
    {
        $errorObject = new LocalObject();

        $monthDateFrom = date("Y-m-01", strtotime($receipt->GetProperty("document_date")));
        $monthDateTo = date("Y-m-t", strtotime($receipt->GetProperty("document_date")));
        $receiptMonthRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $monthDateFrom,
            $monthDateTo
        );

        $maxMonthlyValue = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        if ($maxMonthlyValue - $receiptMonthRealApprovedAmount <= 0) {
            $errorObject->AddError("receipt-monthly-limit-exceed", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        $realAmountApproved = min(
            $maxMonthlyValue - $receiptMonthRealApprovedAmount,
            $receipt->GetProperty("amount_approved")
        );

        if ($realAmountApproved == 0) {
            $errorObject->AddError("receipt-empty-real-approved-value", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

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
        $optionCodes = array(OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH);

        foreach ($optionCodes as $code) {
            $optionList[] = array(
                "title_translation" => GetTranslation("option-" . $code, "product"),
                "value" => GetPriceFormat(Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $code,
                    $optionReceipt->GetIntProperty("employee_id"),
                    $optionReceipt->GetProperty("document_date")
                )) . "???"
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
        return $this->GetCommonAddisonExportLineList(
            $companyUnitID,
            $groupID,
            $payrollDate,
            $exportType,
            OPTION__TRANSPORT__MAIN__SALARY_OPTION,
            "acc_transport_tax_free"
        );
    }

    function GetMainProductCode()
    {
        return PRODUCT__TRANSPORT__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__TRANSPORT__ADVANCED_SECURITY;
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
                OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($optionValue);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__TRANSPORT;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
