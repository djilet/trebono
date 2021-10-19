<?php

class Statistics
{
    /** @property Employee */
    private static $employee;
    private static $statisticsMap;

    public static function GetStatistics(
        Employee $employee,
        $monthlyStatisticsDate,
        $productGroupID = null,
        $optionList = [
            "available_units_month",
            "available_units_year",
            "approved_units_month",
            "approved_units_year",
            "approved_month",
            "approved_year"
        ],
        $fullYearlyStatistics = false,
        $isAPI = false
    ) {
        self::$employee = $employee;

        $user = new User();
        $user->LoadBySession();

        if (!$monthlyStatisticsDate) {
            $monthlyStatisticsDate = GetCurrentDate();
        } else {
            $monthlyStatisticsDate = date("Y-m-t", strtotime($monthlyStatisticsDate));
        }

        $monthMapKey = date("Ym", strtotime($monthlyStatisticsDate));

        if (!isset(self::$statisticsMap[$employee->GetProperty("employee_id")][$monthMapKey])) {
            $stmt = GetStatement();

            $groups = [];
            $groupList = new ProductGroupList("product");

            if ($productGroupID == null) {
                $groupList->LoadProductGroupListForApi(
                    $employee,
                    $monthlyStatisticsDate,
                    $fullYearlyStatistics,
                    $isAPI
                );
            } else {
                $productGroup = new ProductGroup("product");
                $productGroup->LoadByID($productGroupID);
                $groupList->AppendItem($productGroup->GetProperties());
            }

            $totalMonth = 0;
            $totalYear = 0;
            $approveProposedTotalMonth = 0;
            $approveProposedTotalYear = 0;
            $availableTotalMonth = 0;
            $availableTotalYear = 0;

            $currentMonth = date("n", strtotime($monthlyStatisticsDate));
            $currentYear = date("Y", strtotime($monthlyStatisticsDate));

            $monthDateFrom = date("Y-m-01", strtotime($monthlyStatisticsDate));
            $yearDateFrom = date("Y-01-01", strtotime($monthlyStatisticsDate));
            $statisticsDateTo = date("Y-m-t", strtotime($monthlyStatisticsDate));

            foreach ($groupList->GetItems() as &$item) {
                if (isset($item["active"]) && $item["active"] === false) {
                    // Service is disabled, we skip it
                    continue;
                }

                //fill statistics with zeros first
                $item["available_month"] = 0;
                $item["available_month_left"] = 0;
                $item["available_year"] = 0;
                $item["approved_month"] = 0;
                $item["approved_year"] = 0;
                $item["approve_proposed_month"] = 0;
                $item["approve_proposed_year"] = 0;

                $item["available_units_month"] = 0;
                $item["available_units_month_left"] = 0;
                $item["available_units_year"] = 0;
                $item["approved_units_month"] = 0;
                $item["approved_units_year"] = 0;
                $item["approve_proposed_units_month"] = 0;
                $item["approve_proposed_units_year"] = 0;

                $specificProductGroup = SpecificProductGroupFactory::Create($item["group_id"]);
                if ($specificProductGroup === null) {
                    continue;
                }
                $mainProductID = Product::GetProductIDByCode(
                    $specificProductGroup->GetMainProductCode()
                );

                $contract = new Contract("product");
                $contract->LoadContractForDate(
                    OPTION_LEVEL_EMPLOYEE,
                    $employee->GetProperty("employee_id"),
                    $mainProductID,
                    $monthlyStatisticsDate
                );

                $monthlyStatisticsContract = new Contract("product");
                $monthlyStatisticsContractExists = $monthlyStatisticsContract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    $mainProductID,
                    $employee->GetProperty("employee_id"),
                    $monthlyStatisticsDate
                );

                $contractStartMonth = date("n", strtotime($contract->GetProperty("start_date")));
                $contractStartYear = date("Y", strtotime($contract->GetProperty("start_date")));
                $yearlyStatisticsStartMonth = $contractStartYear < $currentYear ? 1 : $contractStartMonth;
                $yearlyStatisticsMonthCount = $contractStartYear < $currentYear
                    ? $currentMonth : $currentMonth - $contractStartMonth + 1;

                switch ($item["code"]) {
                    case PRODUCT_GROUP__FOOD:
                        $specificFood = new SpecificProductGroupFood();

                        $monthlyUnitReceipt = new Receipt("receipt", [
                            "document_date" => date("Y-m-d H:i:s", strtotime($monthlyStatisticsDate)),
                            "employee_id" => $employee->GetIntProperty("employee_id"),
                        ]);
                        $yearlyUnitReceipt = new Receipt("receipt", [
                            "document_date" => date("Y-m-d H:i:s", strtotime($monthlyStatisticsDate)),
                            "employee_id" => $employee->GetIntProperty("employee_id"),
                        ]);

                        $monthlyApiUnitEuro = $specificFood->GetMealValue($monthlyUnitReceipt)
                            + $specificFood->GetEmployerMealGrant($monthlyUnitReceipt);
                        $yearlyApiUnitEuro = $specificFood->GetMealValue($yearlyUnitReceipt)
                            + $specificFood->GetEmployerMealGrant($yearlyUnitReceipt);

                        if (
                            in_array("available_units_month", $optionList)
                            || in_array("available_month", $optionList)
                        ) {
                            $monthUnitsWithTransfer = $specificFood->GetUnitCountLimitForMonth(
                                $employee->GetIntProperty("employee_id"),
                                $item["group_id"],
                                null,
                                $monthlyStatisticsDate,
                                true
                            );
                            $item["available_units_month"] = $monthUnitsWithTransfer;

                            $item["available_month"] = $item["available_units_month"] * floatval($monthlyApiUnitEuro);
                        }
                        if (
                            in_array("available_units_year", $optionList)
                            || in_array("available_year", $optionList)
                        ) {
                            $transferredUnitCountYear = $specificFood->GetTransferredUnitCountForYear(
                                $employee->GetIntProperty("employee_id"),
                                $currentYear
                            );
                            $item["available_units_year"] += $transferredUnitCountYear;
                            for ($i = $yearlyStatisticsStartMonth; $i <= $currentMonth; $i++) {
                                //fixes the problem with end date calculated as 2020-02-31, 2020-06-31 ect.
                                $monthDate = date("Y-" . $i . "-d");
                                $item["available_units_year"] += $specificFood->GetUnitCountLimitForMonth(
                                    $employee->GetIntProperty("employee_id"),
                                    $item["group_id"],
                                    null,
                                    date("Y-m-t", strtotime($monthDate)),
                                    false
                                );
                            }

                            $item["available_year"] = $item["available_units_year"] * floatval($yearlyApiUnitEuro);
                        }
                        if (
                            in_array("approved_units_month", $optionList)
                            || in_array("approved_month", $optionList)
                        ) {
                            $used = $specificFood->GetUsed(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                0,
                                [
                                    $monthDateFrom,
                                    $contract->GetProperty("start_date"),
                                ],
                                $statisticsDateTo,
                                "api",
                                ["approved"]
                            );
                            $item["approved_month"] += $used["approved_euro_amount"];
                            $item["approved_units_month"] += $used["used_unit_count"];
                        }
                        if (
                            in_array("approved_units_year", $optionList)
                            || in_array("approved_year", $optionList)
                        ) {
                            $used = $specificFood->GetUsed(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                0,
                                [$yearDateFrom],
                                $statisticsDateTo,
                                "api",
                                ["approved"]
                            );

                            $item["approved_year"] += $used["approved_euro_amount"];
                            $item["approved_units_year"] += $used["used_unit_count"];
                        }
                        if (
                            in_array("approve_proposed_month", $optionList)
                            || in_array("approve_proposed_units_month", $optionList)
                        ) {
                            $used = $specificFood->GetUsed(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                0,
                                [
                                    $monthDateFrom,
                                    $contract->GetProperty("start_date"),
                                ],
                                $statisticsDateTo,
                                "api",
                                ["approve_proposed"]
                            );
                            $item["approve_proposed_month"] += $used["approved_euro_amount"];
                            $item["approve_proposed_units_month"] += $used["used_unit_count"];
                        }
                        if (
                            in_array("approve_proposed_year", $optionList)
                            || in_array("approve_proposed_units_year", $optionList)
                        ) {
                            $item["approve_proposed_units_year"] = 0;
                            $item["approve_proposed_year"] = 0;

                            $used = $specificFood->GetUsed(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                0,
                                [$yearDateFrom],
                                $statisticsDateTo,
                                "api",
                                ["approve_proposed"]
                            );
                            $item["approve_proposed_year"] += $used["approved_euro_amount"];
                            $item["approve_proposed_units_year"] += $used["used_unit_count"];
                        }

                        $item["type"] = "food";
                        break;

                    case PRODUCT_GROUP__FOOD_VOUCHER:
                        $specificFoodVoucher = new SpecificProductGroupFoodVoucher();

                        self::SetApprovedAmountsForVoucherService(
                            $item,
                            $monthlyStatisticsDate,
                            $contract,
                            $optionList,
                            $employee
                        );

                        if (
                            in_array("available_units_month", $optionList)
                            || in_array("available_month", $optionList)
                        ) {
                            $monthUnitsWithTransfer = $specificFoodVoucher->GetAvailableForStatistics(
                                $employee->GetIntProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate
                            );
                            $item["available_units_month"] = $monthUnitsWithTransfer["count_begin_month"];
                            $item["available_units_month_left"] = $monthUnitsWithTransfer["count"];
                            $item["available_month"] = $monthUnitsWithTransfer["amount_begin_month"];
                            $item["available_month_left"] = $monthUnitsWithTransfer["amount"];
                        }
                        if (
                            in_array("available_units_year", $optionList)
                            || in_array("available_year", $optionList)
                        ) {
                            $yearUnitsWithTransfer = $specificFoodVoucher->GetAvailableForStatistics(
                                $employee->GetIntProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                true
                            );
                            $item["available_units_year"] = $yearUnitsWithTransfer["count"];
                            $item["available_year"] = $yearUnitsWithTransfer["amount"];
                        }

                        $item["type"] = "food";
                        $item["code"] = "food_voucher";
                        break;

                    case PRODUCT_GROUP__BENEFIT:
                        $currentReceiptOption = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
                            $employee->GetProperty("employee_id"),
                            $monthlyStatisticsDate
                        );

                        if ($currentReceiptOption == "yearly") {
                            /* Month */
                            if (in_array("approved_month", $optionList)) {
                                $lastReceipt = new Receipt("receipt");
                                $where = [];
                                $where[] = "employee_id=" . $employee->GetIntProperty("employee_id");
                                $where[] = "group_id=" . intval($item["group_id"]);
                                $where[] = "DATE(document_date + INTERVAL \"+1 year +3 month\") >= 
                                    " . Connection::GetSQLDate($monthlyStatisticsDate);
                                $where[] = "DATE(document_date + INTERVAL \"+1 day\") <= 
                                    " . Connection::GetSQLDate($monthlyStatisticsDate);
                                $where[] = "(status=\"approved\" OR status=\"approve_proposed\")";
                                $where[] = "archive=\"N\"";
                                $query = "SELECT receipt_id, employee_id, group_id, created,
                                            amount_approved, real_amount_approved, document_date 
    									FROM receipt "
                                    . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "") . " 
    									ORDER BY document_date DESC
    									LIMIT 1";
                                $lastReceipt->LoadFromSQL($query);
                                if ($lastReceipt->GetProperty("receipt_id")) {
                                    $item["approved_month"] = $lastReceipt->GetProperty("real_amount_approved") / 12;
                                }
                            }

                            /* Year */
                            if (in_array("approved_year", $optionList)) {
                                $lastReceipt = new Receipt("receipt");
                                $where = [];
                                $where[] = "employee_id=" . $employee->GetIntProperty("employee_id");
                                $where[] = "group_id=" . intval($item["group_id"]);
                                $where[] = "DATE(document_date + INTERVAL \"+1 year +3 month\") >= 
                                    " . Connection::GetSQLDate($monthlyStatisticsDate);
                                $where[] = "DATE(document_date + INTERVAL \"+1 day\") <= 
                                    " . Connection::GetSQLDate($monthlyStatisticsDate);
                                $where[] = "(status=\"approved\" OR status=\"approve_proposed\")";
                                $where[] = "archive=\"N\"";
                                $query = "SELECT receipt_id, employee_id, group_id, created,
                                            amount_approved, real_amount_approved, document_date 
    									FROM receipt "
                                    . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "") . "
    									ORDER BY document_date DESC
    									LIMIT 1";
                                $lastReceipt->LoadFromSQL($query);
                                if ($lastReceipt->GetProperty("receipt_id")) {
                                    $item["approved_year"] =
                                        $lastReceipt->GetProperty("real_amount_approved") / 12
                                        * $yearlyStatisticsMonthCount;
                                }
                            }
                        } else {
                            /* Month */
                            if (in_array("approved_month", $optionList)) {
                                $query = "SELECT receipt_id  
    							FROM receipt 
    							WHERE DATE(document_date) >= 
    							  " . Connection::GetSQLString(date("Y-m-01", strtotime($monthlyStatisticsDate))) . " 
    								AND DATE(document_date) >= 
    								" . Connection::GetSQLString($contract->GetProperty("start_date")) . "
    								AND DATE(document_date) <= 
    								" . Connection::GetSQLString(date("Y-m-t", strtotime($monthlyStatisticsDate))) . " 
    								AND (status='approved' OR status='approve_proposed')
    								AND employee_id=" . $employee->GetIntProperty("employee_id") . "
    								AND group_id=" . intval($item["group_id"]) . " 
    								AND archive='N'";
                                $receiptList = $stmt->FetchList($query);
                                if (count($receiptList) > 0) {
                                    $receiptIDs = array_column($receiptList, "receipt_id");
                                    foreach ($receiptIDs as $receiptID) {
                                        $receipt = new Receipt("receipt");
                                        $receipt->LoadByID($receiptID);

                                        if ($currentReceiptOption != "monthly") {
                                            break;
                                        }
                                        $item["approved_month"] += $receipt->GetProperty("real_amount_approved");
                                    }
                                }
                            }

                            /* Year */
                            if (in_array("approved_year", $optionList)) {
                                $query = "SELECT receipt_id  
    							FROM receipt 
    							WHERE DATE(document_date) >= 
    							  " . Connection::GetSQLString(date("Y-01-01", strtotime($monthlyStatisticsDate))) . "
    								AND DATE(document_date) >= 
    								" . Connection::GetSQLString($contract->GetProperty("start_date")) . "
                                    AND DATE(document_date) <= 
                                    " . Connection::GetSQLString(date("Y-m-d", strtotime($monthlyStatisticsDate))) . " 
    								AND (status='approved' OR status='approve_proposed')
    								AND employee_id=" . $employee->GetIntProperty("employee_id") . "
    								AND group_id=" . intval($item["group_id"]) . " 
    								AND archive='N'";
                                $receiptList = $stmt->FetchList($query);
                                if (count($receiptList) > 0) {
                                    $receiptIDs = array_column($receiptList, "receipt_id");
                                    foreach ($receiptIDs as $receiptID) {
                                        $receipt = new Receipt("receipt");
                                        $receipt->LoadByID($receiptID);

                                        if ($currentReceiptOption != "monthly") {
                                            break;
                                        }
                                        $item["approved_year"] += $receipt->GetProperty("real_amount_approved");
                                    }
                                }
                            }
                        }

                        if ($monthlyStatisticsContractExists || $fullYearlyStatistics) {
                            $item["available_month"] = self::GetOptionValue(
                                OPTION__BENEFIT__MAIN__EMPLOYER_GRANT
                            );
                        }

                        $item["hide_approve_proposed_month"] = $item["hide_approve_proposed_year"] = 1;
                        $item["available_year"] = $item["available_month"] * $yearlyStatisticsMonthCount;
                        break;

                    case PRODUCT_GROUP__BENEFIT_VOUCHER:
                        $specificBenefitVoucher = new SpecificProductGroupBenefitVoucher();

                        self::SetApprovedAmountsForVoucherService(
                            $item,
                            $monthlyStatisticsDate,
                            $contract,
                            $optionList,
                            $employee
                        );

                        if (
                            in_array("available_month", $optionList)
                            || in_array("available_units_month", $optionList)
                        ) {
                            $availableMonth = $specificBenefitVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $monthDateFrom,
                                $statisticsDateTo,
                                null,
                                $user
                            );
                            $item["available_month"] = $availableMonth["amount_begin_month"];
                            $item["available_month_left"] = $availableMonth["amount"];
                            $item["available_units_month"] = $availableMonth["count_begin_month"];
                            $item["available_units_month_left"] = $availableMonth["count"];
                        }

                        if (
                            in_array("available_year", $optionList)
                            || in_array("available_units_year", $optionList)
                        ) {
                            $availableYear = $specificBenefitVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $yearDateFrom,
                                $statisticsDateTo,
                                null,
                                $user,
                                false,
                                false,
                                true
                            );
                            $item["available_year"] = $availableYear["amount"];
                            $item["available_units_year"] = $availableYear["count"];
                        }

                        break;

                    case PRODUCT_GROUP__INTERNET:
                        $specificInternet = new SpecificProductGroupInternet();

                        if (in_array("available_month", $optionList) || in_array("available_year", $optionList)) {
                            $optionReceipt = new Receipt(
                                "company",
                                ["employee_id" => $employee->GetProperty("employee_id"),
                                    "document_date" => $monthlyStatisticsDate]
                            );
                            $maxMonthly = $specificInternet->GetUnit($optionReceipt);

                            if ($monthlyStatisticsContractExists) {
                                $item["available_month"] = $maxMonthly;
                            }

                            $item["available_year"] = $maxMonthly * $yearlyStatisticsMonthCount;
                        }

                        if (in_array("approved_month", $optionList)) {
                            $item["approved_month"] = $specificInternet->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }
                        if (in_array("approved_year", $optionList)) {
                            $item["approved_year"] = $specificInternet->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }

                        if (in_array("approve_proposed_month", $optionList)) {
                            $item["approve_proposed_month"] = $specificInternet->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }
                        if (in_array("approve_proposed_year", $optionList)) {
                            $item["approve_proposed_year"] = $specificInternet->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }

                        break;

                    case PRODUCT_GROUP__AD:
                        $specificAd = new SpecificProductGroupAd();

                        if (in_array("available_month", $optionList)) {
                            $item["available_month"] = $specificAd->GetAmountAvailableMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate
                            );
                        }
                        if (in_array("available_year", $optionList)) {
                            $item["available_year"] = $specificAd->GetAmountAvailableYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                $yearlyStatisticsMonthCount
                            );
                        }

                        if (in_array("approved_month", $optionList)) {
                            $item["approved_month"] = $specificAd->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }
                        if (in_array("approved_year", $optionList)) {
                            $item["approved_year"] = $specificAd->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }

                        if (in_array("approve_proposed_month", $optionList)) {
                            $item["approve_proposed_month"] = $specificAd->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }
                        if (in_array("approve_proposed_year", $optionList)) {
                            $item["approve_proposed_year"] = $specificAd->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }

                        break;

                    case PRODUCT_GROUP__RECREATION:
                        $maxValue = self::GetOptionValue(OPTION__RECREATION__MAIN__MAX_VALUE);
                        $yearlyLimit = $maxValue > 0 ? $maxValue : 0;

                        $yearRealApprovedAmount = 0;
                        if (in_array("approved_year", $optionList) || in_array("available_year", $optionList)) {
                            $yearRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
                                $employee->GetIntProperty("employee_id"),
                                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION),
                                0,
                                $yearDateFrom,
                                $statisticsDateTo,
                                ["approved"]
                            );
                            $availableMonth = $yearlyLimit;
                            $item["available_year"] = $yearlyLimit;
                        }

                        $yearRealApproveProposedAmount = 0;
                        if (in_array("approve_proposed_year", $optionList)) {
                            $yearRealApproveProposedAmount = ReceiptList::GetRealApprovedAmount(
                                $employee->GetIntProperty("employee_id"),
                                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION),
                                0,
                                $yearDateFrom,
                                $statisticsDateTo,
                                ["approve_proposed"]
                            );
                        }

                        if (
                            ($yearRealApprovedAmount > 0 || $yearRealApproveProposedAmount > 0)
                            && ($monthlyStatisticsContractExists || $fullYearlyStatistics)
                        ) {
                            $item["approved_year"] = $yearRealApprovedAmount;
                            $item["approve_proposed_year"] = $yearRealApproveProposedAmount;
                            $availableMonth = 0;
                        } else {
                            $item["approved_year"] = 0;
                            $item["approve_proposed_year"] = 0;
                        }

                        $monthRealApprovedAmount = 0;
                        if (in_array("approved_month", $optionList)) {
                            $monthRealApprovedAmount = ReceiptList::GetRealApprovedAmount(
                                $employee->GetIntProperty("employee_id"),
                                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION),
                                0,
                                $monthDateFrom,
                                $statisticsDateTo,
                                ["approved"]
                            );
                        }

                        $monthRealApproveProposedAmount = 0;
                        if (in_array("approve_proposed_month", $optionList)) {
                            $monthRealApproveProposedAmount = ReceiptList::GetRealApprovedAmount(
                                $employee->GetIntProperty("employee_id"),
                                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__RECREATION),
                                0,
                                $monthDateFrom,
                                $statisticsDateTo,
                                ["approve_proposed"]
                            );
                        }

                        if (
                            ($monthRealApprovedAmount > 0 || $monthRealApproveProposedAmount > 0)
                            && ($monthlyStatisticsContractExists || $fullYearlyStatistics)
                        ) {
                            $item["available_month"] = $yearlyLimit;
                            $item["approved_month"] = $monthRealApprovedAmount;
                            $item["approve_proposed_month"] = $monthRealApproveProposedAmount;
                        } else {
                            $item["available_month"] = $availableMonth;
                            $item["approved_month"] = 0;
                            $item["approve_proposed_month"] = 0;
                        }

                        break;

                    case PRODUCT_GROUP__MOBILE:
                        $specificMobile = new SpecificProductGroupMobile();
                        if (in_array("available_month", $optionList) || in_array("available_year", $optionList)) {
                            $optionReceipt = new Receipt(
                                "company",
                                ["employee_id" => $employee->GetProperty("employee_id"),
                                    "document_date" => $monthlyStatisticsDate]
                            );
                            $maxMonthly = $specificMobile->GetUnit($optionReceipt);

                            if ($monthlyStatisticsContractExists) {
                                $item["available_month"] = $maxMonthly;
                            }

                            $item["available_year"] = $maxMonthly * $yearlyStatisticsMonthCount;
                        }

                        if (in_array("approved_month", $optionList)) {
                            $item["approved_month"] = $specificMobile->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }
                        if (in_array("approved_year", $optionList)) {
                            $item["approved_year"] = $specificMobile->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approved"]
                            );
                        }

                        if (in_array("approve_proposed_month", $optionList)) {
                            $item["approve_proposed_month"] = $specificMobile->GetAmountApprovedMonth(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }
                        if (in_array("approve_proposed_year", $optionList)) {
                            $item["approve_proposed_year"] = $specificMobile->GetAmountApprovedYear(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthlyStatisticsDate,
                                ["approve_proposed"]
                            );
                        }

                        break;

                    case PRODUCT_GROUP__GIFT:
                        $specificGift = new SpecificProductGroupGift();

                        if (in_array("approved_year", $optionList)) {
                            $item["approved_year"] = $specificGift->GetAmountApproved(
                                $employee->GetProperty("employee_id"),
                                $yearDateFrom,
                                $statisticsDateTo
                            );
                        }
                        if (in_array("approved_month", $optionList)) {
                            $item["approved_month"] = $specificGift->GetAmountApproved(
                                $employee->GetProperty("employee_id"),
                                $monthDateFrom,
                                $statisticsDateTo
                            );
                        }

                        if (in_array("available_year", $optionList)) {
                            $availableYear = VoucherList::GetAvailableVoucherAmount(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $yearDateFrom,
                                $statisticsDateTo,
                                false,
                                false,
                                true,
                                $user
                            );
                            $item["available_year"] = $availableYear["amount"];
                        }
                        if (in_array("available_month", $optionList)) {
                            $availableMonth = VoucherList::GetAvailableVoucherAmount(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthDateFrom,
                                $statisticsDateTo,
                                false,
                                false,
                                true,
                                $user
                            );
                            $item["available_month"] = $availableMonth["amount"];
                        }

                        $item["hide_approve_proposed_month"] = $item["hide_approve_proposed_year"] = 1;

                        break;

                    case PRODUCT_GROUP__GIFT_VOUCHER:
                        $specificGiftVoucher = new SpecificProductGroupGiftVoucher();

                        self::SetApprovedAmountsForVoucherService(
                            $item,
                            $monthlyStatisticsDate,
                            $contract,
                            $optionList,
                            $employee
                        );

                        if (
                            in_array("available_year", $optionList)
                            || in_array("available_units_year", $optionList)
                        ) {
                            $availableYear = $specificGiftVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $yearDateFrom,
                                $statisticsDateTo,
                                null,
                                $user,
                                false,
                                false,
                                true
                            );
                            $item["available_year"] = $availableYear["amount"];
                            $item["available_units_year"] = $availableYear["count"];
                        }

                        if (in_array("available_month", $optionList)) {
                            $availableMonth = $specificGiftVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $monthDateFrom,
                                $statisticsDateTo,
                                null,
                                $user
                            );
                            $item["available_month"] = $availableMonth["amount_begin_month"];
                            $item["available_month_left"] = $availableMonth["amount"];
                            $item["available_units_month"] = $availableMonth["count_begin_month"];
                            $item["available_units_month_left"] = $availableMonth["count"];
                        }

                        break;

                    case PRODUCT_GROUP__BONUS:
                        $specificBonus = new SpecificProductGroupBonus();

                        if (in_array("approved_year", $optionList)) {
                            $item["approved_year"] = $specificBonus->GetAmountApproved(
                                $employee->GetProperty("employee_id"),
                                $yearDateFrom,
                                $statisticsDateTo
                            );
                        }
                        if (in_array("approved_month", $optionList)) {
                            $item["approved_month"] = $specificBonus->GetAmountApproved(
                                $employee->GetProperty("employee_id"),
                                $monthDateFrom,
                                $statisticsDateTo
                            );
                        }

                        if (in_array("available_year", $optionList)) {
                            $availableYear = VoucherList::GetAvailableVoucherAmount(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $yearDateFrom,
                                $statisticsDateTo,
                                false,
                                false,
                                true,
                                $user
                            );
                            $item["available_year"] = $availableYear["amount"];
                        }
                        if (in_array("available_month", $optionList)) {
                            $availableMonth = VoucherList::GetAvailableVoucherAmount(
                                $employee->GetProperty("employee_id"),
                                $item["group_id"],
                                $monthDateFrom,
                                $statisticsDateTo,
                                false,
                                false,
                                true,
                                $user
                            );
                            $item["available_month"] = $availableMonth["amount"];
                        }

                        $item["hide_approve_proposed_month"] = $item["hide_approve_proposed_year"] = 1;

                        break;

                    case PRODUCT_GROUP__BONUS_VOUCHER:
                        $specificBonusVoucher = new SpecificProductGroupBonusVoucher();

                        self::SetApprovedAmountsForVoucherService(
                            $item,
                            $monthlyStatisticsDate,
                            $contract,
                            $optionList,
                            $employee
                        );

                        if (
                            in_array("available_year", $optionList)
                            || in_array("available_units_year", $optionList)
                        ) {
                            $availableYear = $specificBonusVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $yearDateFrom,
                                $statisticsDateTo,
                                null,
                                $user,
                                false,
                                false,
                                true
                            );
                            $item["available_year"] = $availableYear["amount"];
                            $item["available_units_year"] = $availableYear["count"];
                        }

                        if (in_array("available_month", $optionList)) {
                            $availableMonth = $specificBonusVoucher->GetAvailableAmount(
                                $employee->GetProperty("employee_id"),
                                $monthDateFrom,
                                $statisticsDateTo,
                                null,
                                $user
                            );
                            $item["available_month"] = $availableMonth["amount_begin_month"];
                            $item["available_month_left"] = $availableMonth["amount"];
                            $item["available_units_month"] = $availableMonth["count_begin_month"];
                            $item["available_units_month_left"] = $availableMonth["count"];
                        }

                        break;

                    case PRODUCT_GROUP__TRANSPORT:
                        self::SetApprovedAmounts($item, $monthlyStatisticsDate, $contract, $optionList, $employee);

                        if ($monthlyStatisticsContractExists || $fullYearlyStatistics) {
                            $item["available_month"] = self::GetOptionValue(
                                OPTION__TRANSPORT__MAIN__AMOUNT_PER_MONTH
                            );
                        }

                        if (in_array("approved_year", $optionList)) {
                            $item["available_year"] = $item["available_month"] * $yearlyStatisticsMonthCount;
                        }

                        break;
                    case PRODUCT_GROUP__CHILD_CARE:
                        self::SetApprovedAmounts($item, $monthlyStatisticsDate, $contract, $optionList, $employee);

                        if ($monthlyStatisticsContractExists || $fullYearlyStatistics) {
                            $item["available_month"] = self::GetOptionValue(
                                OPTION__CHILD_CARE__MAIN__MAX_MONTHLY
                            );
                        }
                        $item["available_year"] = $item["available_month"] * $yearlyStatisticsMonthCount;

                        break;

                    case PRODUCT_GROUP__TRAVEL:
                        self::SetApprovedAmounts($item, $monthlyStatisticsDate, $contract, $optionList, $employee);

                        if ($monthlyStatisticsContractExists || $fullYearlyStatistics) {
                            $item["available_month"] = self::GetOptionValue(
                                OPTION__TRAVEL__MAIN__AMOUNT_PER_MONTH
                            );
                        }
                        if (in_array("approved_year", $optionList)) {
                            $item["available_year"] = self::GetOptionValue(
                                OPTION__TRAVEL__MAIN__AMOUNT_PER_YEAR
                            );
                        }

                        $item["type"] = "travel";
                        break;
                    case PRODUCT_GROUP__CORPORATE_HEALTH_MANAGEMENT:
                        $specificCorporateHealth = new SpecificProductGroupCorporateHealthManagement();

                        self::SetApprovedAmounts($item, $monthlyStatisticsDate, $contract, $optionList, $employee);

                        if ($monthlyStatisticsContractExists || $fullYearlyStatistics) {
                            $optionReceipt = new Receipt(
                                "company",
                                ["employee_id" => $employee->GetProperty("employee_id"),
                                    "document_date" => $monthlyStatisticsDate]
                            );
                            $maxMonthly = $specificCorporateHealth->GetUnit($optionReceipt);

                            $item["available_year"] = $specificCorporateHealth->GetMaxYearly($optionReceipt);
                            if ($currentMonth > 1) {
                                $lastMonthDate = date_create(
                                    date("Y-m-1", strtotime($monthlyStatisticsDate))
                                );
                                $lastMonthDate->modify("-1 month");
                                $approved = ReceiptList::GetRealApprovedAmount(
                                    $employee->GetProperty("employee_id"),
                                    $item["group_id"],
                                    null,
                                    $yearDateFrom,
                                    $lastMonthDate->format("Y-m-t"),
                                    ["approved", "approve_proposed"]
                                );
                                $available = $item["available_year"] - $approved;
                                $item["available_month"] = $available > $maxMonthly ? $maxMonthly : $available;
                            } else {
                                $item["available_month"] = $maxMonthly;
                            }
                        }

                        break;

                    case PRODUCT_GROUP__GIVVE:
                        continue 2;

                    default:
                        continue;
                }

                $item["available_month"] = self::GetPriceFormat($item["available_month"]);
                $item["available_month_left"] = self::GetPriceFormat($item["available_month_left"]);
                $item["available_year"] = self::GetPriceFormat($item["available_year"]);
                $item["approved_month"] = self::GetPriceFormat($item["approved_month"]);
                $item["approved_year"] = self::GetPriceFormat($item["approved_year"]);
                $item["approve_proposed_month"] = self::GetPriceFormat($item["approve_proposed_month"]);
                $item["approve_proposed_year"] = self::GetPriceFormat($item["approve_proposed_year"]);

                $item["available_units_month"] = self::GetPriceFormat($item["available_units_month"]);
                $item["available_units_month_left"] = self::GetPriceFormat($item["available_units_month_left"]);
                $item["available_units_year"] = self::GetPriceFormat($item["available_units_year"]);
                $item["approved_units_month"] = self::GetPriceFormat($item["approved_units_month"]);
                $item["approved_units_year"] = self::GetPriceFormat($item["approved_units_year"]);
                $item["approve_proposed_units_month"] = self::GetPriceFormat($item["approve_proposed_units_month"]);
                $item["approve_proposed_units_year"] = self::GetPriceFormat($item["approve_proposed_units_year"]);

                if ($productGroupID !== null && $productGroupID !== false) {
                    return $item;
                }

                $groups[$item["group_id"]] = $item;
            }

            foreach ($groups as $key => $group) {
                $groups[$key]["stats"] = [
                    "approved_month" => $group["approved_month"],
                    "approved_year" => $group["approved_year"],

                    "approved_units_month" => $group["approved_units_month"] ?? 0,
                    "approved_units_year" => $group["approved_units_year"] ?? 0,

                    "approve_proposed_month" => $group["approve_proposed_month"],
                    "approve_proposed_year" => $group["approve_proposed_year"],

                    "approve_proposed_units_month" => $group["approve_proposed_units_month"] ?? 0,
                    "approve_proposed_units_year" => $group["approve_proposed_units_year"] ?? 0,

                    "available_units_month" => $group["available_units_month"] ?? 0,
                    "available_units_month_left" => $group["available_units_month_left"] ?? 0,
                    "available_units_year" => $group["available_units_year"] ?? 0,

                    "available_month" => $group["available_month"] ?? 0,
                    "available_month_left" => $group["available_month_left"] ?? 0,
                    "available_year" => $group["available_year"] ?? 0,
                ];

                $totalMonth += floatval($group["approved_month"] ?? 0);
                $totalYear += floatval($group["approved_year"] ?? 0);
                $approveProposedTotalMonth += floatval($group["approve_proposed_month"] ?? 0);
                $approveProposedTotalYear += floatval($group["approve_proposed_year"] ?? 0);
                $availableTotalMonth += floatval($group["available_month"] ?? 0);
                $availableTotalYear += floatval($group["available_year"] ?? 0);
            }

            self::$statisticsMap[$employee->GetProperty("employee_id")][$monthMapKey] = [
                "product_groups" => array_values($groups),
                "total_month" => self::GetPriceFormat($totalMonth),
                "total_year" => self::GetPriceFormat($totalYear),
                "total_approve_proposed_month" => self::GetPriceFormat($approveProposedTotalMonth),
                "total_approve_proposed_year" => self::GetPriceFormat($approveProposedTotalYear),
                "total_available_month" => self::GetPriceFormat($availableTotalMonth),
                "total_available_year" => self::GetPriceFormat($availableTotalYear),
            ];
        }

        return self::$statisticsMap[$employee->GetProperty("employee_id")][$monthMapKey];
    }

    private static function GetOptionValue($code, $date = null)
    {
        if (!$date) {
            $date = GetCurrentDate();
        }

        $optionValue = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            $code,
            self::$employee->GetIntProperty("employee_id"),
            $date
        );

        return floatval($optionValue);
    }

    private static function GetPriceFormat($number)
    {
        return number_format($number, 2, ".", "");
    }

    private static function SetApprovedAmounts(&$item, $monthlyStatisticsDate, $contract, $options, $employee)
    {
        $statisticsDateTo = date("Y-m-t", strtotime($monthlyStatisticsDate));
        $monthDateFrom = date("Y-m-01", strtotime($monthlyStatisticsDate));
        if (strtotime($contract->GetProperty("start_date")) > strtotime($monthDateFrom)) {
            $monthDateFrom = $contract->GetProperty("start_date");
        }

        if (in_array("approved_month", $options)) {
            $item["approved_month"] = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $monthDateFrom,
                $statisticsDateTo,
                ["approved"]
            );
        }
        if (in_array("approve_proposed_month", $options)) {
            $item["approve_proposed_month"] = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $monthDateFrom,
                $statisticsDateTo,
                ["approve_proposed"]
            );
        }

        $yearDateFrom = date("Y-01-01", strtotime($monthlyStatisticsDate));
        if (strtotime($contract->GetProperty("start_date")) > strtotime($yearDateFrom)) {
            $yearDateFrom = $contract->GetProperty("start_date");
        }
        if (in_array("approved_year", $options)) {
            $item["approved_year"] = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $yearDateFrom,
                $statisticsDateTo,
                ["approved"]
            );
        }
        if (in_array("approve_proposed_year", $options)) {
            $item["approve_proposed_year"] = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $yearDateFrom,
                $statisticsDateTo,
                ["approve_proposed"]
            );
        }
    }

    private static function SetApprovedAmountsForVoucherService(
        &$item,
        $monthlyStatisticsDate,
        $contract,
        $options,
        $employee
    ) {
        $statisticsDateTo = date("Y-m-t", strtotime($monthlyStatisticsDate));
        $monthDateFrom = date("Y-m-01", strtotime($monthlyStatisticsDate));
        if (strtotime($contract->GetProperty("start_date")) > strtotime($monthDateFrom)) {
            $monthDateFrom = $contract->GetProperty("start_date");
        }

        if (in_array("approve_proposed_month", $options)) {
            $approvedMonth = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                null,
                $statisticsDateTo,
                ["approve_proposed"],
                "monthly",
                true
            );
            $item["approve_proposed_month"] = $item["approve_proposed_year"]
                = $approvedMonth["amount"];
            $item["approve_proposed_units_month"] = $approvedMonth["count"];
        }

        if (in_array("approved_month", $options)) {
            $approvedMonth = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $monthDateFrom,
                $statisticsDateTo,
                ["approved"],
                "monthly"
            );
            $item["approved_month"] = $approvedMonth["amount"];
            $item["approved_units_month"] = $approvedMonth["count"];
        }
        $yearDateFrom = date("Y-01-01", strtotime($monthlyStatisticsDate));
        if (strtotime($contract->GetProperty("start_date")) > strtotime($yearDateFrom)) {
            $yearDateFrom = $contract->GetProperty("start_date");
        }
        if (in_array("approved_year", $options)) {
            $approvedYear = ReceiptList::GetRealApprovedAmount(
                $employee->GetProperty("employee_id"),
                $item["group_id"],
                null,
                $yearDateFrom,
                $statisticsDateTo,
                ["approved"],
                "yearly"
            );
            $item["approved_year"] = $approvedYear["amount"];
            $item["approved_units_year"] = $approvedYear["count"];
        }

        $item["hide_approve_proposed_year"] = 1;
    }
}