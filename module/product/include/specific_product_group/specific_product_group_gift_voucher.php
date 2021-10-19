<?php

/**
 * User: der
 * Date: 24.08.18
 * Time: 17:22
 */

class SpecificProductGroupGiftVoucher extends AbstractSpecificProductGroup
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
        if ($receipt->GetProperty("amount_approved") == 0) {
            $receipt->AddError("receipt-empty-real-approved-value", "receipt");

            return false;
        }

        $voucherAvailableList = VoucherList::GetAvailableVoucherListForReceipt($receipt, true);

        $errorObject = new LocalObject();

        if (count($voucherAvailableList) <= 0) {
            $errorObject->AddError("receipt-voucher-not-found", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        $leftToApprove = $receipt->GetProperty("amount_approved");
        $receiptRealAmountApproved = 0;
        foreach ($voucherAvailableList as $voucher) {
            if ($leftToApprove <= 0) {
                continue;
            }

            if ($leftToApprove < $voucher["available_amount"]) {
                $receiptRealAmountApproved += $leftToApprove;
                break;
            }

            $leftToApprove -= $voucher["available_amount"];
            $receiptRealAmountApproved += $voucher["available_amount"];
        }

        $receipt->SetProperty("real_amount_approved", round($receiptRealAmountApproved, 2));

        if ($this->CheckNeedToUpdateRealAmountAndLinks($receipt)) {
            Receipt::RemoveReceiptVoucherLinks($receipt->GetProperty("receipt_id"));
            $leftToApprove = $receipt->GetProperty("amount_approved");
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
                    break;
                }

                Voucher::SetVoucherReceipt($voucher["voucher_id"], $receipt->GetProperty("receipt_id"));
                Voucher::SetVoucherReceiptAmount(
                    $voucher["voucher_id"],
                    $receipt->GetProperty("receipt_id"),
                    $voucher["available_amount"]
                );
                $leftToApprove -= $voucher["available_amount"];
            }
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

        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("service_mapping");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID(
            $optionReceipt->GetProperty("employee_id"),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER),
            false,
            false,
            true
        );

        $voucherListArray = $voucherList->GetItems();

        foreach ($voucherListArray as $key => $voucher) {
            if ($voucher["file"] === null) {
                continue;
            }

            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $optionReceipt->GetProperty("employee_id"),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER),
            $optionReceipt
        );

        $optionReceipt->SetProperty(
            "document_date",
            date("Y-m-d H:i:s", strtotime($optionReceipt->GetProperty("document_date")))
        );
        $key = array_search($optionReceipt->GetProperty("receipt_id"), array_column($receiptListArray, "receipt_id"));
        if ($key !== false) {
            $receiptListArray[$key] = $optionReceipt->GetProperties();
        } else {
            $receiptListArray[] = $optionReceipt->GetProperties();
        }
        array_multisort(
            array_column($receiptListArray, "document_date"),
            array_column($receiptListArray, "receipt_id"),
            $receiptListArray
        );

        $available = $this->GetAvailableAmountForReceipt(
            $receiptListArray,
            $voucherListArray,
            $optionReceipt->GetProperty("receipt_id")
        );
        $available = empty($available) ? "0€" : GetPriceFormat($available) . "€";
        $availableArray = $this->GetAvailableAmountForReceipt(
            $receiptListArray,
            $voucherListArray,
            $optionReceipt->GetProperty("receipt_id"),
            true
        );

        $availableStr = "";
        foreach ($availableArray as $key => $voucher) {
            $availableStr = (!empty($availableStr) ? $availableStr : "")
                . $voucher["voucher_id"] . "(" . GetPriceFormat($voucher["amount"]) . "€); ";
        }

        $receiptMap = $this->GetVoucherMappedReceiptList($optionReceipt->GetProperty("employee_id"));

        $approvedStr = "";
        $receiptMapKey = array_search(
            $optionReceipt->GetProperty("receipt_id"),
            array_column($receiptMap, "receipt_id")
        );
        if ($receiptMapKey !== false && isset($receiptMap[$receiptMapKey]["voucher_list"])) {
            foreach ($receiptMap[$receiptMapKey]["voucher_list_array"] as $key => $voucher) {
                $approvedStr = (!empty($approvedStr) ? $approvedStr : "")
                    . $voucher["voucher_id"] . "(" . GetPriceFormat($voucher["amount"]) . "€); ";
            }
        }

        $result[]["VoucherCategoryList"][] = array(
            "reason" => $key,
            "available_amount" => $available,
            "available_vouchers" => $availableStr,
            "approved_vouchers" => $approvedStr
        );

        return $result;
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
            OPTION__GIFT_VOUCHER__MAIN__PAYROLL_EXPORT,
            $companyUnitID,
            $payrollDate
        );
        if ($payrollExport == "N" && $exportType == "datev") {
            return array();
        }

        $accKey = "acc_gift";

        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("voucher_date_asc");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListForAddison($companyUnitID, $groupID, $payrollDate, $exportType);

        $monthBegin = date("Y-m-1", strtotime($payrollDate));

        $lineList = array();
        $companyAcc = CompanyUnit::GetInheritablePropertyCompanyUnit($companyUnitID, $accKey);
        $employeeList = [];
        foreach ($voucherList->GetItems() as $voucher) {
            if (!isset($employeeList[$voucher["employee_id"]])) {
                $employee = new Employee("company");
                $employee->LoadByID($voucher["employee_id"]);
                $employeeList[$voucher["employee_id"]] = $employee->GetProperties();
            }

            $employee = $employeeList[$voucher["employee_id"]];

            $lineList[$voucher["employee_id"]]["title"] = "Positive";
            $lineList[$voucher["employee_id"]]["acc_key"] = $accKey;
            if (isset($lineList[$voucher["employee_id"]]["amount"])) {
                $lineList[$voucher["employee_id"]]["amount"] += $voucher["amount"];
            } else {
                $lineList[$voucher["employee_id"]]["amount"] = $voucher["amount"];
            }
            $lineList[$voucher["employee_id"]]["service_voucher_ids"][] = $voucher["voucher_id"];
            $lineList[$voucher["employee_id"]]["line_key"] = "positive_line";
            $lineList[$voucher["employee_id"]]["acc"] = $employee[$accKey] ?: $companyAcc;
            $lineList[$voucher["employee_id"]]["group_id"] = $voucher["group_id"];
            $lineList[$voucher["employee_id"]]["employee_id"] = $voucher["employee_id"];
            $lineList[$voucher["employee_id"]]["employee_guid"] = $employee["employee_guid"];
            $lineList[$voucher["employee_id"]]["cost_center_number"] = $employee["cost_center_number"];
            $lineList[$voucher["employee_id"]]["month_key"] = strtotime($voucher["voucher_date"]) < strtotime($monthBegin) ? date("Ym", strtotime($payrollDate)) : date("Ym", strtotime($voucher["voucher_date"]));
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
            $voucherList->SetOrderBy("service_mapping");
            $voucherList->SetItemsOnPage(0);
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER),
                false,
                false,
                true
            );

            $voucherListArray = $voucherList->GetItems();
        }

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["empty"] = false;
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER)
        );

        $voucherMap = $this->MapReceiptToVoucher($receiptListArray, $voucherListArray);

        array_multisort(array_column($voucherMap, "empty"), array_column($voucherMap, "voucher_date"), $voucherMap);

        return $voucherMap;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param array $receiptList array of receipts (need to have properties: real_amount_approved, receipt_id, document_date and order by document_date asc)
     * @param array $voucherList array of vouchers (need to have properties: amount, amount_approved, voucher_date, end_date and order by end_date asc)
     *
     * @return array $voucherList array of vouchers with amount = amount - amount_approved, amount_approved = amount aprroved with receipts, receipt_list = array of receipts with approved amounts
     */
    private function MapReceiptToVoucher($receiptList, $voucherList, $returnReceiptMap = false)
    {
        $voucherList = VoucherList::GetLinksForVoucherList($voucherList);

        foreach ($receiptList as $receiptKey => &$receipt) {
            foreach ($voucherList as $voucherKey => &$voucher) {
                if (!isset($voucher["receipt_ids"])) {
                    $voucher["receipt_ids"] = array_column($voucher["link_list"], "receipt_id");
                }

                if (($receipt["status"] == "approve_proposed" || $receipt["status"] == "approved") && strtotime($voucher["created"]) > strtotime($receipt["status_updated"])) {
                    continue;
                }

                $key = array_search($receipt["receipt_id"], $voucher["receipt_ids"]);
                if ($key === false) {
                    continue;
                }

                $link = $voucher["link_list"][$key];
                $voucher["receipt_list"][] = array(
                    "receipt_id" => $receipt["receipt_id"],
                    "legal_receipt_id" => $receipt["legal_receipt_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"],
                    "document_date" => $receipt["document_date"],
                    "status" => $receipt["status"],
                    "status_updated" => $receipt["status_updated"],
                    "creditor_export_id" => $receipt["creditor_export_id"]
                );
                $voucher["amount_approved"] += $link["amount"];
                $voucher["amount_left"] = bcsub($voucher["amount_left"], $link["amount"], 2);
                $voucher["used_date"] = $receipt["document_date"];
                $receipt["real_amount_approved"] = bcsub($receipt["real_amount_approved"], $link["amount"], 2);
                $receipt["voucher_list_array"][] = array(
                    "voucher_id" => $voucher["voucher_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"]
                );
                $receipt["voucher_list"][] = $voucher["voucher_id"];
                if ($voucher["amount_left"] != 0) {
                    continue;
                }

                $voucher["empty"] = true;
            }
        }

        return !$returnReceiptMap ? $voucherList : $receiptList;
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
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER)
        );

        foreach ($receiptList as $key => $receipt) {
            $voucherList = Receipt::GetReceiptVoucherList($receipt["receipt_id"]);
            $receiptList[$key]["voucher_list_array"] = $voucherList;
            $receiptList[$key]["voucher_list"] = array_column($voucherList, "voucher_id");
        }

        return $receiptList;
    }

    /**
     * Gets amount approved for receipts and mapped to vouchers
     *
     * @param int $employeeID employee_id
     * @param string $dateFrom start date for selection
     * @param string $dateTo end date for selection
     * @param array $statusList
     *
     * @return array $amountApproved sum of approved_amount for employee vouchers within date
     */
    public function GetAmountApproved($employeeID, $dateFrom, $dateTo, $statusList = ["approved", "approve_proposed"])
    {
        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("service_mapping");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER),
            false,
            false,
            true,
            true,
            null,
            $dateFrom,
            $dateTo
        );

        $voucherListArray = $voucherList->GetItems();
        $voucherMap = $this->GetReceiptMappedVoucherList($employeeID, $voucherListArray);

        $amountApproved = 0;
        $countApproved = 0;
        foreach ($voucherMap as $voucher) {
            if (!isset($voucher["receipt_list"])) {
                continue;
            }

            foreach ($voucher["receipt_list"] as $receipt) {
                if (!in_array($receipt["status"], $statusList)) {
                    continue;
                }
                if ($dateFrom && $dateTo && strtotime($receipt["document_date"]) <= strtotime($dateTo) && strtotime($receipt["document_date"]) >= strtotime($dateFrom)) {
                    $amountApproved += $receipt["amount"];
                    $countApproved += $receipt["amount"] / $voucher["amount"];
                } elseif ($dateFrom && strtotime($receipt["document_date"]) >= strtotime($dateFrom) && !($dateFrom && $dateTo)) {
                    $amountApproved += $receipt["amount"];
                    $countApproved += $receipt["amount"] / $voucher["amount"];
                } elseif ($dateTo && strtotime($receipt["document_date"]) <= strtotime($dateTo) && !($dateFrom && $dateTo)) {
                    $amountApproved += $receipt["amount"];
                    $countApproved += $receipt["amount"] / $voucher["amount"];
                }
            }
        }

        return ["amount" => $amountApproved, "count" => number_format($countApproved, 10, ".", "")];
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
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER),
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

    /**
     * @param int $employeeID
     * @param false $dateFrom
     * @param false $dateTo
     * @param string $category
     * @param User $user
     * @param false $noTransfer
     * @param false $returnCount
     * @param false $forYearlyStatistics
     *
     * @return array|int
     */
    public function GetAvailableAmount(
        $employeeID,
        $dateFrom = false,
        $dateTo = false,
        $category = null,
        $user = null,
        $noTransfer = false,
        $returnCount = false,
        $forYearlyStatistics = false
    ) {
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__GIFT_VOUCHER);

        $voucherAmount = 0;
        $voucherCount = 0;
        $voucherAmountBeginMonth = 0;
        $voucherCountBeginMonth = 0;

        //admin can't see used amounts
        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }
        $noTransfer = $noTransfer
            || $user->Validate(array("employee" => null))
            && !$user->Validate(array("root"))
            || $returnCount;

        //find sum of vouchers that were issued in the current period of time
        if ($noTransfer) {
            $voucherList = new VoucherList("company");
            $voucherList->SetOrderBy("service_mapping");
            $voucherList->SetItemsOnPage(0);
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                $groupID,
                false,
                false,
                true,
                false,
                null,
                null,
                $dateFrom,
                $dateTo,
                false
            );

            foreach ($voucherList->_items as $voucher) {
                if (
                    $voucher["file"] === null
                    || $category !== null
                    && $voucher["reason"] != $category
                ) {
                    continue;
                }
                $voucherAmount += $voucher["amount"];
                $voucherCount++;
            }

            if ($returnCount) {
                return $voucherCount;
            }

            return ["amount" => $voucherAmount,
                "count" => number_format($voucherCount, 10, ".", ""),
            ];
        }

        //find sum of vouchers that weren't used in the previous and current periods
        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("service_mapping");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID(
            $employeeID,
            $groupID,
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

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            $groupID
        );

        $date = $forYearlyStatistics ? $dateFrom : $dateTo;
        $voucherMap = $this->MapReceiptToVoucher($receiptListArray, $voucherListArray);
        if ($voucherMap) {
            foreach ($voucherMap as $key => $voucher) {
                if (
                    $category !== null && $voucher["reason"] != $category ||
                    strtotime($voucher["voucher_date"]) >= strtotime($dateTo) ||
                    $voucher["file"] === null
                ) {
                    continue;
                }

                $voucherMap[$key]["amount_left_begin_month"] = $voucherMap[$key]["amount_left"];

                if (isset($voucher["receipt_list"])) {
                    foreach ($voucher["receipt_list"] as $receipt) {
                        if (strtotime($receipt["status_updated"]) >= strtotime($dateFrom)) {
                            $voucherMap[$key]["amount_left_begin_month"] += $receipt["amount"];
                        }

                        if (strtotime($receipt["status_updated"]) > strtotime($date)) {
                            $voucherMap[$key]["amount_left"] += $receipt["amount"];
                        }
                    }
                }

                $voucherAmountBeginMonth +=  $voucherMap[$key]["amount_left_begin_month"];
                $voucherCountBeginMonth += $voucherMap[$key]["amount_left_begin_month"] / $voucher["amount"];;

                $voucherAmount += $voucherMap[$key]["amount_left"];
                $voucherCount += $voucherMap[$key]["amount_left"] / $voucher["amount"];
            }
        }

        return [
            "amount" => $voucherAmount,
            "count" => number_format($voucherCount, 10, ".", ""),
            "amount_begin_month" => $voucherAmountBeginMonth,
            "count_begin_month" => number_format($voucherCountBeginMonth, 10, ".", ""),
        ];
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
                OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR,
                $employeeID,
                $document_date
            );

            $maxAmount = $optionValue = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER,
                $employeeID,
                $document_date
            );

            $values["amount_per_month"] = GetPriceFormat($qtyPerYear * $maxAmount);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    /**
     * Gets amount approved for receipts and mapped to vouchers
     * Before found current receipt map receipt to vouchers, than map receipt to voucher in revert direction
     * Than sum free amount for current receipt document_date
     *
     * @param array $receiptList array of receipts (need to have properties: real_amount_approved, receipt_id, document_date and order by document_date asc)
     * @param array $voucherList array of vouchers (need to have properties: amount, amount_approved, voucher_date, end_date and order by end_date asc)
     * @param int $receiptID current receipt_id
     *
     * @return float|array|false $availableAmount = free amount for current receipt document_date or false
     */
    private function GetAvailableAmountForReceipt($receiptList, $voucherList, $receiptID, $returnArray = false)
    {
        $voucherList = VoucherList::GetLinksForVoucherList($voucherList);

        $availableAmount = array();
        $availableAmountArray = array();

        $receiptKey = array_search($receiptID, array_column($receiptList, "receipt_id"));
        if ($receiptKey !== false) {
            $receiptList[] = $receiptList[$receiptKey];
            unset($receiptList[$receiptKey]);
        }

        foreach ($receiptList as $receiptKey => &$receipt) {
            if ($receipt["receipt_id"] == $receiptID) {
                $receiptList = array_reverse($receiptList);
                foreach ($receiptList as $receiptKey => &$receipt) {
                    if ($receipt["receipt_id"] == $receiptID) {
                        foreach ($voucherList as $voucherKey => $voucher) {
                            if (
                                $voucher["amount_left"] <= 0
                                || strtotime($voucher["voucher_date"]) > strtotime($receipt["document_date"])
                                || strtotime($voucher["end_date"]) < strtotime($receipt["document_date"])
                            ) {
                                continue;
                            }

                            $availableAmount = (!empty($availableAmount) ? $availableAmount : 0) + $voucher["amount_left"];
                            $availableAmountArray[] = array(
                                "voucher_id" => $voucher["voucher_id"],
                                "amount" => $voucher["amount_left"],
                                "reason" => $voucher["reason"],
                                "created" => $voucher["created"]
                            );
                        }

                        return $returnArray ? $availableAmountArray : $availableAmount;
                    }

                    foreach ($voucherList as $voucherKey => $voucher) {
                        $voucher["receipt_ids"] = array_column($voucher["link_list"], "receipt_id");
                        if ($receipt["real_amount_approved"] <= 0) {
                            break;
                        }

                        if (
                            ($receipt["status"] == "approve_proposed"
                                || $receipt["status"] == "approved")
                            && strtotime($voucher["created"]) > strtotime($receipt["status_updated"])
                        ) {
                            continue;
                        }

                        $key = array_search($receipt["receipt_id"], $voucher["receipt_ids"]);
                        if ($key === false) {
                            continue;
                        }

                        $link = $voucher["link_list"][$key];
                        $voucherList[$voucherKey]["receipt_list"][] = array(
                            "receipt_id" => $receipt["receipt_id"],
                            "amount" => $link["amount"],
                            "reason" => $voucher["reason"],
                            "status_updated" => $receipt["status_updated"]
                        );
                        $voucherList[$voucherKey]["amount_left"] = bcsub(
                            $voucherList[$voucherKey]["amount_left"],
                            $link["amount"],
                            2
                        );
                        $receipt["real_amount_approved"] = bcsub(
                            $receipt["real_amount_approved"],
                            $link["amount"],
                            2
                        );
                    }
                }

                return false;
            }

            foreach ($voucherList as $voucherKey => $voucher) {
                $voucher["receipt_ids"] = array_column($voucher["link_list"], "receipt_id");

                if ($receipt["real_amount_approved"] <= 0 && !in_array($receipt["receipt_id"], $voucher["receipt_ids"])) {
                    break;
                }

                if (($receipt["status"] == "approve_proposed" || $receipt["status"] == "approved") && strtotime($voucher["created"]) > strtotime($receipt["status_updated"])) {
                    continue;
                }

                $key = array_search($receipt["receipt_id"], $voucher["receipt_ids"]);
                if ($key === false) {
                    continue;
                }

                $link = $voucher["link_list"][$key];
                $voucherList[$voucherKey]["receipt_list"][] = array(
                    "receipt_id" => $receipt["receipt_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"]
                );
                $voucherList[$voucherKey]["amount_left"] = bcsub(
                    $voucherList[$voucherKey]["amount_left"],
                    $link["amount"],
                    2
                );
                $receipt["real_amount_approved"] = bcsub($receipt["real_amount_approved"], $link["amount"], 2);
            }
        }

        return false;
    }

    function GetAdvancedSecurityProductCode()
    {
        return null;
    }

    function GetMainProductCode()
    {
        return PRODUCT__GIFT_VOUCHER__MAIN;
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__GIFT_VOUCHER;
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

    /** Returns mapped statistics about vouchers that already exist and are expected to be generated
     *
     * @param $employeeID
     * @param $groupID
     * @param Voucher|null $newVoucher new voucher to check this voucher's amount validity
     *
     * @return array
     */
    public function MapVoucherListToMonth($employeeID, $groupID, ?Voucher $newVoucher = null)
    {
        $voucherList = new VoucherList("company");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID($employeeID, $groupID, false, false, true, true);

        if ($newVoucher != null) {
            $newVoucher->SetProperty("voucher_id", null);
            $isVoucherCron = $newVoucher->GetProperty("IsVoucherCron");
            $newVoucherEndDate = $newVoucher->GetProperty("recurring") == "Y" && $newVoucher->GetProperty("recurring_end_date") != null ?
                $newVoucher->GetProperty("recurring_end_date") : date(
                    '31.12.Y',
                    strtotime($newVoucher->GetProperty("voucher_date"))
                );
            $newVoucher->SetProperty("amount", $newVoucher->GetProperty("amount"));
            $voucherList->AppendItem($newVoucher->GetProperties());
        } else {
            $isVoucherCron = false;
            $newVoucherEndDate = null;
        }

        //forming the map itself
        $voucherMap = array();
        foreach ($voucherList->_items as $voucher) {
            $date = $voucher["voucher_date"];
            $voucherEndDate = $voucher["recurring"] == "Y" && $voucher["recurring_end_date"] != null && !$isVoucherCron ? $voucher["recurring_end_date"] : date(
                '31.12.Y',
                strtotime($date)
            );
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

    public function GetUnitCount($employeeID = null, $dateFrom = null, $dateTo = null)
    {
        if ($employeeID == null || $dateFrom == null || $dateTo == null) {
            return 0;
        }

        return $this->GetAvailableAmount($employeeID, $dateFrom, $dateTo, null, null, true, true);
    }

    public function GetFlexOptionUnitPrice($employeeID = null, $date = null)
    {
        if ($employeeID == null || $date == null) {
            return 0;
        }

        return Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PRICE,
            $employeeID,
            $date
        );
    }

    public function GetFlexOptionUnitPercentage($employeeID = null, $date = null)
    {
        if ($employeeID == null || $date == null) {
            return 0;
        }

        return Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__GIFT_VOUCHER__MAIN__FLEX_UNIT_PERCENTAGE,
            $employeeID,
            $date
        );
    }

    private function CheckNeedToUpdateRealAmountAndLinks($receipt)
    {
        if ($receipt->GetProperty("Save") && $receipt->GetProperty("status") == "approve_proposed") {
            $receiptBefore = new Receipt("receipt");
            $receiptBefore->LoadByID($receipt->GetProperty("receipt_id"));

            if (
                in_array($receiptBefore->GetProperty("status"), ["approve_proposed", "approved"]) &&
                Voucher::VoucherReceiptExists($receipt->GetProperty("receipt_id"))
            ) {
                $documentDateBefore = date("Y-m-d", strtotime($receiptBefore->GetProperty("document_date")));
                $documentDateAfter = date("Y-m-d", strtotime($receipt->GetProperty("document_date")));
                $isDateChanged = $documentDateBefore != $documentDateAfter;
                $isAmountChanged = $receiptBefore->GetProperty("amount_approved") != $receipt->GetProperty("amount_approved");
                $isRealAmountChanged = $receiptBefore->GetProperty("real_amount_approved") != $receipt->GetProperty("real_amount_approved");

                if (!$isDateChanged && !$isAmountChanged && !$isRealAmountChanged) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
