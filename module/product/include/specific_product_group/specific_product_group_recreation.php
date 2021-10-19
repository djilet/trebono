<?php

class SpecificProductGroupRecreation extends AbstractSpecificProductGroup
{
    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetUnit()
     */
    public function GetUnit($receipt)
    {
        $result = 0;

        if ($receipt->GetProperty("document_date")) {
            $employeeValue = $this->GetEmployeeValue($receipt);
            $spouseValue = $this->GetSpouseValue($receipt);
            $childValue = $this->GetChildValue($receipt);
            $childCount = $this->GetChildCount($receipt);

            $result = $employeeValue + $spouseValue + $childCount * $childValue;
        }

        return $result;
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
        $yearlyLimit = floatval(Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__RECREATION__MAIN__MAX_VALUE,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        ));

        $yearDateFrom = date("Y-01-01", strtotime($receipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($receipt->GetProperty("document_date")));
        $receiptYearRealApprovedReceiptCount = ReceiptList::GetApprovedReceiptCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $yearDateFrom,
            $yearDateTo
        );

        if ($receiptYearRealApprovedReceiptCount > 0 && $receipt->GetProperty("receipt_from") != "doc") {
            $errorObject->AddError("receipt-yearly-limit-exceed", "receipt");
        }

        if ($errorObject->HasErrors()) {
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        if ($receipt->GetProperty("receipt_from") == "doc") {
            $unit = 0;
        }

        $realAmountApproved = min(array($yearlyLimit, $unit));
        $receipt->SetProperty("amount_approved", $realAmountApproved);
        $receipt->SetProperty("real_amount_approved", $realAmountApproved);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ProcessAfterReceiptSave()
     */
    public function ProcessAfterReceiptSave(Receipt $receiptAfter, Receipt $receiptBefore)
    {
        parent::ProcessAfterReceiptSave($receiptAfter, $receiptBefore);

        if (
            $receiptAfter->GetProperty("status") == "approve_proposed"
            && $receiptAfter->GetProperty("receipt_from") == "doc"
        ) {
            Receipt::UpdateField("status", $receiptAfter->GetProperty("receipt_id"), "approved");
        } elseif ($receiptAfter->GetProperty("status") == "denied") {
            self::RemoveConfirmation($receiptAfter);
        }
    }

    public static function RemoveConfirmation($receipt)
    {
        if (
            Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__CONFIRMATION_WITH_PICTURE,
                $receipt->GetIntProperty("employee_id"),
                $receipt->GetProperty("document_date")
            ) != "N"
        ) {
            return;
        }

        $confirmation = new ConfirmationEmployee("agreements");
        $confirmation->LoadByReceiptID($receipt->GetProperty("receipt_id"));
        $confirmation->Remove();
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::AppendAdditionalInfo()
     */
    public function AppendAdditionalInfo($receipt)
    {
    }

    /**
     * Returns value of Max Value Employee option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetEmployeeValue($receipt)
    {
        $result = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__RECREATION__MAIN__MAX_EMPLOYEE,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return floatval($result);
    }

    /**
     * Returns value of  Max Value Spouse option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetSpouseValue($receipt)
    {
        $receiptMaterialStatus = Employee::GetPropertyHistoryValueEmployee(
            "material_status",
            $receipt->GetIntProperty("employee_id"),
            $receipt->GetProperty("created")
        );
        $martialStatus = ($receiptMaterialStatus["value"] == "married" ? 1 : 0);
        $result = $martialStatus * Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__RECREATION__MAIN__MAX_SPOUSE,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return floatval($result);
    }

    /**
     * Returns value of  Max Value Child option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetChildValue($receipt)
    {
        $result = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__RECREATION__MAIN__MAX_CHILD,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return floatval($result);
    }

    /**
     * Returns number of children option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return int
     */
    public function GetChildCount($receipt)
    {
        $receiptChildCount = Employee::GetPropertyHistoryValueEmployee(
            "child_count",
            $receipt->GetIntProperty("employee_id"),
            $receipt->GetProperty("created")
        );

        return intval($receiptChildCount["value"]);
    }

    /**
     * Returns material status option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetMaterialStatus($receipt)
    {
        $stmt = GetStatement(DB_PERSONAL);

        $query = "SELECT material_status FROM employee WHERE employee_id=" . $receipt->GetProperty("employee_id");

        return $stmt->FetchField($query);
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
        $optionCodes = array(OPTION__RECREATION__MAIN__MAX_VALUE);

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

        $yearDateFrom = date("Y-01-01", strtotime($receipt->GetProperty("document_date")));
        $yearDateTo = date("Y-12-31", strtotime($receipt->GetProperty("document_date")));

        $approvedReceiptCount = ReceiptList::GetApprovedReceiptCount(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("group_id"),
            0,
            $yearDateFrom,
            $yearDateTo
        );

        $optionList[] = array(
            "title_translation" => GetTranslation("info-current-year-receipt-count-left", "product"),
            "value" => $approvedReceiptCount > 0 ? 0 : 1
        );

        $materialStatus = '';
        if ($this->GetMaterialStatus($receipt) == "single") {
            $materialStatus = GetTranslation("material-status-single", "company");
        }
        if ($this->GetMaterialStatus($receipt) == "married") {
            $materialStatus = GetTranslation("material-status-married", "company");
        }

        $optionList[] = array(
            "title_translation" => GetTranslation("info-material-status", "product"),
            "value" => $materialStatus
        );

        $optionList[] = array(
            "title_translation" => GetTranslation("info-сhild-сount", "product"),
            "value" => $this->GetChildCount($receipt)
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
        return $this->GetCommonAddisonExportLineList(
            $companyUnitID,
            $groupID,
            $payrollDate,
            $exportType,
            OPTION__RECREATION__MAIN__SALARY_OPTION,
            "acc_recreation_subsidy_tax_flat"
        );
    }

    function GetMainProductCode()
    {
        return PRODUCT__RECREATION__MAIN;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__RECREATION__ADVANCED_SECURITY;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "")
    {
        $properties = array(
            "amount_per_month",
            "confirmation_message",
            "confirmation_transaction_message"
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
            $unitEuro = $this->GetUnit(new Receipt('receipt', [
                "document_date" => GetCurrentDateTime(),
                "employee_id" => $employeeID,
            ]));

            $maxValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__MAX_VALUE,
                $employeeID,
                $document_date
            );

            $limits = array();
            if ($unitEuro > 0) {
                $limits[] = $unitEuro;
            }
            if ($maxValue > 0) {
                $limits[] = $maxValue;
            }

            $yearlyLimit = count($limits) > 0 ? min($limits) : 0;

            $values["amount_per_month"] = GetPriceFormat($yearlyLimit);

            $values["confirmation_message"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__CONFIRMATION_MESSAGE,
                $employeeID,
                $document_date
            );
            $values["confirmation_transaction_message"] = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__RECREATION__MAIN__CONFIRMATION_TRANSACTION_MESSAGE,
                $employeeID,
                $document_date
            );
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__RECREATION;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }
}
