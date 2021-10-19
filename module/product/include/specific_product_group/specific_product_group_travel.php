<?php

class SpecificProductGroupTravel extends AbstractSpecificProductGroup
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
        $dailyAllowance = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__TRAVEL__MAIN__FIXED_DAILY_ALLOWANCE,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        if ($dailyAllowance == "Y" && $receipt->GetProperty("receipt_from") == "meal") {
            $realAmountApproved =
                $receipt->GetProperty("days_amount_under_16") * Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__TRAVEL__MAIN__HOURS_UNDER,
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("document_date")
                )
                + $receipt->GetProperty("days_amount_over_16") * Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__TRAVEL__MAIN__HOURS_OVER,
                    $receipt->GetProperty("employee_id"),
                    $receipt->GetProperty("document_date")
                );
            $receipt->SetProperty("real_amount_approved", $realAmountApproved);
        } else {
            $receipt->SetProperty("real_amount_approved", $receipt->GetProperty("amount_approved"));
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
        $optionCodes = array(OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH, OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR);

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

        return $optionList;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        return array();
    }

    function GetMainProductCode()
    {
        return PRODUCT__TRAVEL__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__TRAVEL__ADVANCED_SECURITY;
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
                OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($optionValue);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__TRAVEL;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
