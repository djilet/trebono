<?php

/**
 * User: der
 * Date: 24.08.18
 * Time: 17:22
 */

class SpecificProductGroupGift extends AbstractSpecificProductGroup
{
    /**
     * Returns cost of unit for passed receipt based on its owner and receipt date
     * Currently unit is a maximum sum can be approved for one receipt
     *
     * @param Receipt $receipt
     *
     * @return null
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

        if ($receipt->GetProperty("Save") == 1 && Receipt::GetReceiptVoucherList($receipt->GetProperty("receipt_id"))) {
            return true;
        }

        $voucherAvailableList = VoucherList::GetAvailableVoucherListForReceipt($receipt);

        if (count($voucherAvailableList) <= 0) {
            $errorObject->AddError("receipt-voucher-not-found", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        if ($receipt->GetProperty("Save") == 1) {
            $leftToApprove = $receipt->GetProperty("amount_approved");
            $receiptRealAmountApproved = 0;
            foreach ($voucherAvailableList as $voucher) {
                if ($leftToApprove <= 0) {
                    continue;
                }

                if ($leftToApprove < $voucher["available_amount"]) {
                    Voucher::SetVoucherReceipt($voucher["voucher_id"], $receipt->GetProperty("receipt_id"));
                    Voucher::SetVoucherReceiptAmount(
                        $voucher["voucher_id"],
                        $receipt->GetProperty("receipt_id"),
                        $leftToApprove
                    );
                    $receiptRealAmountApproved += $leftToApprove;
                    break;
                }

                Voucher::SetVoucherReceipt($voucher["voucher_id"], $receipt->GetProperty("receipt_id"));
                Voucher::SetVoucherReceiptAmount(
                    $voucher["voucher_id"],
                    $receipt->GetProperty("receipt_id"),
                    $voucher["available_amount"]
                );
                $leftToApprove -= $voucher["available_amount"];
                $receiptRealAmountApproved += $voucher["available_amount"];
            }
        }

        $receipt->SetProperty("real_amount_approved", round($receiptRealAmountApproved, 2));

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
        $voucherAvailableList = VoucherList::GetAvailableVoucherListForReceipt($receipt);
        $voucherApprovedList = Receipt::GetReceiptVoucherList($receipt->GetProperty("receipt_id"));

        foreach ($voucherApprovedList as $voucher) {
            $key = array_search($voucher["voucher_id"], array_column($voucherAvailableList, "voucher_id"));
            if ($key !== false) {
                $voucherAvailableList[$key]["available_amount"] += $voucher["amount"];
            } else {
                $voucherAvailableList[] = array(
                    "voucher_id" => $voucher["voucher_id"],
                    "available_amount" => $voucher["amount"]
                );
            }
        }

        $availableSum = array_sum(array_column($voucherAvailableList, "available_amount"));

        $availableStr = "";
        foreach ($voucherAvailableList as $voucher) {
            $availableStr .= $voucher["voucher_id"] . "(" . GetPriceFormat($voucher["available_amount"]) . "€); ";
        }

        $approvedStr = "";
        foreach ($voucherApprovedList as $voucher) {
            $approvedStr .= $voucher["voucher_id"] . "(" . GetPriceFormat($voucher["amount"]) . "€); ";
        }

        $optionList = array();

        $optionList[] = array(
            "title_translation" => GetTranslation("info-available-amount-voucher", "product"),
            "value" => GetPriceFormat($availableSum)
        );
        $optionList[] = array(
            "title_translation" => GetTranslation("info-available-amount-voucher-list", "product"),
            "value" => $availableStr
        );
        $optionList[] = array(
            "title_translation" => GetTranslation("info-approved-amount-voucher-list", "product"),
            "value" => $approvedStr
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
        $lineList = $this->GetCommonAddisonExportLineList(
            $companyUnitID,
            $groupID,
            $payrollDate,
            $exportType,
            OPTION__GIFT__MAIN__SALARY_OPTION,
            "acc_gift"
        );

        foreach ($lineList as $lineKey => $line) {
            $lineList[$lineKey]["voucher_ids"] = array();

            $receiptMap = $this->GetVoucherMappedReceiptList($line["employee_id"]);
            foreach ($line["receipt_ids"] as $keyReceiptID => $receiptID) {
                $receiptMapKey = array_search($receiptID, array_column($receiptMap, "receipt_id"));
                if (!$receiptMap || !isset($receiptMap[$receiptMapKey]["voucher_list"])) {
                    continue;
                }

                $lineList[$lineKey]["voucher_ids"][$keyReceiptID] = "(" . implode(
                    ";",
                    $receiptMap[$receiptMapKey]["voucher_list"]
                ) . ")";
            }
        }

        return $lineList;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param int $employee_id employee_id
     */
    public function GetReceiptMappedVoucherList($employeeID, $voucherListArray = null)
    {
        if ($voucherListArray == null) {
            $voucherList = new VoucherList("company");
            $voucherList->SetOrderBy("voucher_end_date_desc");
            $voucherList->SetItemsOnPage(0);
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT),
                false,
                false,
                true
            );

            $voucherListArray = $voucherList->GetItems();
        }

        $voucherListArray = VoucherList::GetLinksForVoucherList($voucherListArray);

        foreach ($voucherListArray as $key => $voucher) {
            if ($voucher["link_list"]) {
                $voucherListArray[$key]["amount_approved"] = array_sum(array_column($voucher["link_list"], "amount"));
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"] - $voucherListArray[$key]["amount_approved"];
                $voucherListArray[$key]["empty"] = $voucherListArray[$key]["amount_left"] == 0;
                foreach ($voucher["link_list"] as $link) {
                    $voucherListArray[$key]["receipt_list"][] = array(
                        "legal_receipt_id" => Receipt::GetReceiptFieldByID("legal_receipt_id", $link["receipt_id"]),
                        "receipt_id" => $link["receipt_id"],
                        "amount" => $link["amount"],
                        "status_updated" => Receipt::GetReceiptFieldByID("status_updated", $link["receipt_id"])
                    );
                }
            } else {
                $voucherListArray[$key]["amount_approved"] = 0;
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
                $voucherListArray[$key]["empty"] = false;
            }
        }

        array_multisort(
            array_column($voucherListArray, "empty"),
            array_column($voucherListArray, "voucher_date"),
            $voucherListArray
        );

        return $voucherListArray;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param int $employee_id employee_id
     */
    public function GetVoucherMappedReceiptList($employeeID)
    {
        $receiptList = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT)
        );

        foreach ($receiptList as $key => $receipt) {
            $voucherList = Receipt::GetReceiptVoucherList($receipt);
            $receiptList[$key]["voucher_list_array"] = $voucherList;
            $receiptList[$key]["voucher_list"] = array_column($voucherList, "voucher_id");
        }

        return $receiptList;
    }

    /**
     * Gets amount approved for receipts and mapped to vouchers
     *
     * @param int $employee_id employee_id
     * @param string $dateFrom start date for selection
     * @param string $dateTo end date for selection
     *
     * @return float $amountApproved sum of approved_amount for employee vouchers within date
     */
    public function GetAmountApproved($employeeID, $dateFrom = false, $dateTo = false)
    {
        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("voucher_end_date_desc");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT),
            false,
            false,
            true,
            false,
            null,
            null,
            false,
            false,
            false
        );

        $voucherListArray = $voucherList->GetItems();

        $voucherListArray = VoucherList::GetLinksForVoucherList($voucherListArray);

        foreach ($voucherListArray as $key => $voucher) {
            if ($voucher["link_list"]) {
                $voucherListArray[$key]["amount_approved"] = array_sum(array_column($voucher["link_list"], "amount"));
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"] - $voucherListArray[$key]["amount_approved"];
                $voucherListArray[$key]["empty"] = $voucherListArray[$key]["amount_left"] == 0;
                foreach ($voucher["link_list"] as $link) {
                    $voucherListArray[$key]["receipt_list"][] = array(
                        "receipt_id" => $link["receipt_id"],
                        "amount" => $link["amount"],
                        "status_updated" => Receipt::GetReceiptFieldByID("status_updated", $link["receipt_id"])
                    );
                }
            } else {
                $voucherListArray[$key]["amount_approved"] = 0;
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
                $voucherListArray[$key]["empty"] = false;
            }
        }

        $amountApproved = 0;
        if ($voucherListArray) {
            if ($dateFrom || $dateTo) {
                foreach ($voucherListArray as $voucher) {
                    if ($dateFrom && $dateTo && strtotime($voucher["voucher_date"]) <= strtotime($dateTo) && strtotime($voucher["end_date"]) >= strtotime($dateFrom)) {
                        $amountApproved += $voucher["amount_approved"];
                    } elseif ($dateFrom && strtotime($voucher["end_date"]) >= strtotime($dateFrom) && !($dateFrom && $dateTo)) {
                        $amountApproved += $voucher["amount_approved"];
                    } elseif ($dateTo && strtotime($voucher["voucher_date"]) <= strtotime($dateTo) && !($dateFrom && $dateTo)) {
                        $amountApproved += $voucher["amount_approved"];
                    }
                }
            } else {
                $amountApproved = array_sum(array_column($voucherListArray, "amount_approved"));
            }
        }

        return $amountApproved;
    }

    /**
     * Gets amount approved for receipts and mapped to vouchers
     *
     * @param int $employee_id employee_id
     * @param string $dateFrom start date for selection
     * @param string $dateTo end date for selection
     *
     * @return array $voucherAvailableMap[voucher_id] = available_amount where available_amount = amount of voucher - approved_amount by receipts
     */
    public function GetAvailableAmountMap($employeeID, $dateFrom = false, $dateTo = false)
    {
        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("voucher_end_date_desc");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT),
            false,
            false,
            true
        );

        $voucherListArray = $voucherList->GetItems();

        $voucherListArray = VoucherList::GetLinksForVoucherList($voucherListArray);

        foreach ($voucherListArray as $key => $voucher) {
            if ($voucher["link_list"]) {
                $voucherListArray[$key]["amount_approved"] = array_sum(array_column($voucher["link_list"], "amount"));
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"] - $voucherListArray[$key]["amount_approved"];
                $voucherListArray[$key]["empty"] = $voucherListArray[$key]["amount_left"] == 0;
                foreach ($voucher["link_list"] as $link) {
                    $voucherListArray[$key]["receipt_list"][] = array(
                        "receipt_id" => $link["receipt_id"],
                        "amount" => $link["amount"],
                        "status_updated" => Receipt::GetReceiptFieldByID("status_updated", $link["receipt_id"])
                    );
                }
            } else {
                $voucherListArray[$key]["amount_approved"] = 0;
                $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
                $voucherListArray[$key]["empty"] = false;
            }
        }

        $voucherAvailableMap = array();
        if ($voucherListArray) {
            if ($dateFrom || $dateTo) {
                foreach ($voucherListArray as $voucher) {
                    if ($dateFrom && $dateTo && strtotime($voucher["voucher_date"]) <= strtotime($dateTo) && strtotime($voucher["end_date"]) >= strtotime($dateFrom) && $voucher["amount_left"] > 0) {
                        $voucherAvailableMap[$voucher["voucher_id"]] = $voucher["amount_left"];
                    } elseif ($dateFrom && strtotime($voucher["end_date"]) >= strtotime($dateFrom) && $voucher["amount_left"] > 0 && !($dateFrom && $dateTo)) {
                        $voucherAvailableMap[$voucher["voucher_id"]] = $voucher["amount_left"];
                    } elseif ($dateTo && strtotime($voucher["voucher_date"]) <= strtotime($dateTo) && $voucher["amount_left"] > 0 && !($dateFrom && $dateTo)) {
                        $voucherAvailableMap[$voucher["voucher_id"]] = $voucher["amount_left"];
                    }
                }
            } else {
                foreach ($voucherListArray as $voucher) {
                    if ($voucher["amount_left"] <= 0) {
                        continue;
                    }

                    $voucherAvailableMap[$voucher["voucher_id"]] = $voucher["amount_left"];
                }
            }
        }

        return $voucherAvailableMap;
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
            $qtyPerYear = $optionValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__GIFT__MAIN__QTY_PER_YEAR,
                $employeeID,
                $document_date
            );

            $maxAmount = $optionValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__GIFT__MAIN__AMOUNT_PER_VOUCHER,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($qtyPerYear * $maxAmount);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__GIFT__ADVANCED_SECURITY;
    }

    function GetMainProductCode()
    {
        return PRODUCT__GIFT__MAIN;
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__GIFT;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
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
            $receiptAfter->GetProperty("status") == "approve_proposed" ||
            ($receiptBefore->GetProperty("status") != "approve_proposed" && $receiptBefore->GetProperty("status") != "approved")
        ) {
            return;
        }

        Receipt::RemoveReceiptVoucherLinks($receiptAfter->GetProperty("receipt_id"));
    }
}
