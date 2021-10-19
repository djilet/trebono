<?php

class SpecificProductGroupFood extends AbstractSpecificProductGroup
{
    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetUnit()
     */
    public function GetUnit($receipt)
    {
        $result = "";

        if ($receipt->GetProperty("document_date")) {
            $mealValue = $this->GetMealValue($receipt);
            $employerMealGrant = $this->GetEmployerMealGrant($receipt);
            $employeeMealGrant = $this->GetEmployeeMealGrant($receipt);
            $result = $mealValue + $employerMealGrant + $employeeMealGrant;
        }

        return $result;
    }

    /**
     * Returns cost of unit for api (no employee meal grant) for passed receipt based on its owner and receipt date
     *
     * @param Receipt $receipt
     *
     * @return float cost of unit
     */
    public function GetApiUnit($receipt)
    {
        $result = "";

        if ($receipt->GetProperty("document_date")) {
            $mealValue = $this->GetMealValue($receipt);
            $employerMealGrant = $this->GetEmployerMealGrant($receipt);

            $result = $mealValue + $employerMealGrant;
        }

        return $result;
    }

    /**
     * Returns final unit count limit for month of passed date
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int|null $exceptReceiptID
     * @param string $date
     * @param bool $useTransfer
     *
     * @return float
     */
    public function GetUnitCountLimitForMonth($employeeID, $groupID, $exceptReceiptID, $date, $useTransfer)
    {
        $contract = new Contract("product");
        $contractExists = $contract->ContractExist(
            OPTION_LEVEL_EMPLOYEE,
            Product::GetProductIDByCode($this->GetMainProductCode()),
            $employeeID,
            $date
        );
        if (!$contractExists) {
            return 0;
        }

        $receiptMonthUnitsPerMonth = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__UNITS_PER_MONTH,
            $employeeID,
            $date
        );

        $transferredUnitCount = $useTransfer
            ? $this->GetTransferredUnitCountForMonth($employeeID, $date, $exceptReceiptID)
            : 0;

        $monthlyLimits = array();
        $monthlyLimits[] = $receiptMonthUnitsPerMonth + $transferredUnitCount;
        $monthlyLimits[] = Employee::GetEmployeeField($employeeID, "working_days_per_week") * 4 + 2;

        return min($monthlyLimits);
    }

    public function GetTransferredUnitCountForYear($employeeID, $year)
    {
        return $this->GetTransferredUnitCountForMonth($employeeID, $year . "-01-01", 0);
    }

    /**
     * Return count of units transferred to passed month
     *
     * @param int $employeeID
     * @param string $date
     * @param int $exceptReceiptID
     *
     * @return number
     */
    private function GetTransferredUnitCountForMonth($employeeID, $date, $exceptReceiptID)
    {
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD);

        //calculate count of transferred units
        $contract = new Contract("product");
        if (
            !$contract->LoadContractForDate(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode($this->GetMainProductCode()),
                $date
            )
        ) {
            return 0;
        }

        $contractStartDate = $contract->GetProperty("start_date");
        $yearFrom = date("Y", strtotime($contractStartDate));
        $yearTo = date("Y", strtotime($date));

        $transferredUnitCount = 0;
        $usedTransferredUnitCount = 0;

        $month = date("n", strtotime($date));
        $year = date("Y", strtotime($date));

        for ($y = $yearFrom; $y <= $yearTo; $y++) {
            $monthFrom = $y == $yearFrom ? date("n", strtotime($contractStartDate)) : 1;
            $monthTo = $y == $yearTo ? date("n", strtotime($date)) - 1 : 12;

            for ($m = $monthFrom; $m <= $monthTo; $m++) {
                $monthStart = date("Y-m-01", strtotime($y . "-" . $m . "-01"));
                $monthEnd = date("Y-m-t", strtotime($monthStart));

                $untransferableUnitCount = $this->GetUntransferableUnitCount(
                    $employeeID,
                    $groupID,
                    $exceptReceiptID,
                    $monthStart,
                    $monthEnd
                );

                $unitsPerMonth = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__FOOD__MAIN__UNITS_PER_MONTH,
                    $employeeID,
                    $monthEnd
                );

                $limits = array();
                $limits[] = $unitsPerMonth;
                $limits[] = Employee::GetEmployeeField($employeeID, "working_days_per_week") * 4 + 2;
                $monthlyLimit = min($limits);
                $unitsToTransfer = $monthlyLimit - $untransferableUnitCount;
                if ($unitsToTransfer > 0) {
                    $transferredUnitCount += $unitsToTransfer;
                }

                if ($untransferableUnitCount <= $unitsPerMonth) {
                    continue;
                }

                $transferredUnitCount -= $untransferableUnitCount - $unitsPerMonth;
            }
        }

        //option is called "per week", but since it was renamed after discussion it was decided to count it as general
        $unitsForTransfer = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER,
            $employeeID,
            $date
        );

        $transferredUnitCount = min($transferredUnitCount, $unitsForTransfer);

        return max(array(intval($transferredUnitCount), 0));
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ValidateReceiptApprove()
     */
    public function ValidateReceiptApprove($receipt)
    {
        $errorObject = new LocalObject();

        $workingDaysPerWeek = intval(Employee::GetEmployeeField(
            $receipt->GetProperty("employee_id"),
            "working_days_per_week"
        ));
        $receiptDate = date("Y-m-d", strtotime($receipt->GetProperty("document_date")));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        //restaurant receipt can't approve on weekends
        $weekDay = date("w", strtotime($receiptDate));
        if ($receipt->GetProperty("receipt_from") == "restaurant") {
            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $errorObject->AddError("receipt-restaurant-weekend", "receipt");
                $receipt->AppendErrorsFromObject($errorObject);

                return false;
            }
        }

        /**
         * Daily limit
         */
        $contract = new Contract("product");
        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $receipt->GetProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $receipt->GetProperty("document_date")
        );

        //if not weekly shopping and receipt date is weekend set receipt date = next monday
        if (!$weeklyShopping) {
            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $receiptDate = date('Y-m-d', strtotime('next Monday', strtotime($receiptDate)));
            }
        }

        $receiptDateUsedUnitCount = $this->GetUsedUnitCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $receiptDate,
            $receiptDate,
            "admin",
            true
        );
        if ($receiptDateUsedUnitCount >= 1) {
            $errorObject->AddError("receipt-daily-unit-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue('receipt_autodeny_day_limit'));
        }

        /**
         * Weekly limit
         */
        if (!$weeklyShopping) {
            $weekDay = date("w", strtotime($receiptDate));

            //get used unit count for passed week. force monday to be start of the week
            $weekFromObject = new DateTime($receiptDate);
            $weekFromObject->modify("-" . ($weekDay == 0 ? 6 : $weekDay - 1) . " days");
            $weekFrom = $weekFromObject->format("Y-m-d");

            $weekToObject = new DateTime($receiptDate);
            $weekToObject->modify("+" . ($weekDay == 0 ? 0 : 7 - $weekDay) . " days");
            $weekTo = $weekToObject->format("Y-m-d");

            $receiptWeekUntransferableUnitCount = $this->GetUntransferableUnitCount(
                $receipt->GetProperty("employee_id"),
                $receipt->GetProperty("group_id"),
                $receipt->GetProperty("receipt_id"),
                $weekFrom,
                $weekTo
            );

            $receiptWeekUsedUnitCount = $this->GetUsedUnitCountByWeek(
                $receipt->GetProperty("employee_id"),
                $receiptDate,
                $receipt->GetProperty("receipt_id")
            );
            if ($receiptWeekUsedUnitCount >= $workingDaysPerWeek) {
                $errorObject->AddError("receipt-weekly-unit-limit-exceed", "receipt");
                $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue('receipt_autodeny_week_limit'));
            }
        }

        /**
         * Monthly limit
         */
        $monthFrom = date("Y-m-01", strtotime($receiptDate));
        $monthTo = date("Y-m-t", strtotime($receiptDate));
        $receiptMonthUntransferableUnitCount = $this->GetUntransferableUnitCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $monthFrom,
            $monthTo
        );

        $receiptMonthUsedUnitCount = $this->GetUsedUnitCountByMonth(
            $receipt->GetProperty("employee_id"),
            $receiptDate,
            $receipt->GetProperty("receipt_id")
        );
        $finalMonthlyLimit = $this->GetUnitCountLimitForMonth(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $receiptDate,
            true
        );
        if ($receiptMonthUsedUnitCount >= $finalMonthlyLimit) {
            $errorObject->AddError("receipt-monthly-unit-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue('receipt_autodeny_month_limit'));
        }
        /*
         * Limits checked-------------------------------------
         */

        if ($errorObject->HasErrors()) {
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        //calculate real amount approved
        $amountApproved = $receipt->GetProperty("amount_approved");
        $unit = $this->GetApiUnit($receipt);
        $fullUnit = $this->GetUnit($receipt);

        $realAmountApprovedLimits = array();
        $realAmountApprovedLimits[] = $unit * ($finalMonthlyLimit - $receiptMonthUsedUnitCount);
        $realAmountApprovedLimits[] = $amountApproved;
        if ($weeklyShopping && $receiptDateUsedUnitCount == 0) {
            $realAmountApprovedLimits[] = $unit * $workingDaysPerWeek;
            $realAmountApprovedLimits[] = $amountApproved >= $unit
                ? intval(round($amountApproved / $unit, 5)) * $unit
                : $unit;

            /**
             * New rule start
             */

            //2139: employee cannot approve more than 1 unit for restaurant receipt if weeklyShopping
            if ($receipt->GetProperty("receipt_from") == "restaurant") {
                $realAmountApprovedLimits[] = $unit;
            }

            //get receipt period (it is period from {receipt date} to {receipt_date + (6-8) days} depends on option value)
            $receiptPeriod = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD__WEEKLY_SHOPPING__RECEIPT_PERIOD,
                $receipt->GetProperty("employee_id"),
                $receiptDate
            );
            $receiptPeriodFrom = date("Y-m-d", strtotime($receiptDate));
            $receiptPeriodTo = date(
                "Y-m-d",
                strtotime($receiptPeriodFrom . " +" . intval($receiptPeriod - 1) . " days")
            );
            $receiptPeriodDateList = GetDateRange($receiptPeriodFrom, $receiptPeriodTo);

            //define a set of employee's working days of week (1 - monday, 7 - sunday)
            $workingDaysOfWeek = array();
            for ($i = 1; $i <= $workingDaysPerWeek && $i <= 7; $i++) {
                $workingDaysOfWeek[] = $i;
            }

            //define what dates of receipt period are working days
            $workingDateList = array();
            foreach ($receiptPeriodDateList as $date) {
                if (!in_array(date("N", strtotime($date)), $workingDaysOfWeek)) {
                    continue;
                }

                $workingDateList[] = $date;
            }

            $dateToUnitMap = $this->GetWeeklyShoppingDateToUnitMap(
                $receipt->GetProperty("employee_id"),
                $receipt->GetProperty("group_id"),
                $receipt->GetProperty("receipt_id"),
                array("approve_proposed", "approved")
            );
            $usedDateList = array_keys($dateToUnitMap);
            $intersect = array_intersect($workingDateList, $usedDateList);
            $maxUnitsToApprove = count($workingDateList) - count($intersect);

            if ($maxUnitsToApprove <= 0) {
                $errorObject->AddError("receipt-weekly-unit-limit-exceed", "receipt");
                $receipt->SetProperty(
                    "proposed_denial_reason",
                    Config::GetConfigValue('receipt_autodeny_week_limit')
                );
                $receipt->AppendErrorsFromObject($errorObject);

                return false;
            }

            $realAmountApprovedLimits[] = $maxUnitsToApprove * $unit;

            /**
             * New rule end
             */
        } else {
            if (!isset($receiptWeekUsedUnitCount)) {
                $receiptWeekUsedUnitCount = $this->GetUsedUnitCountByWeek(
                    $receipt->GetProperty("employee_id"),
                    $receiptDate,
                    $receipt->GetProperty("receipt_id")
                );
            }
            $realAmountApprovedLimits[] = $unit * ($workingDaysPerWeek - $receiptWeekUsedUnitCount);
            if ($receiptDateUsedUnitCount > 0) {
                //approving to partially filled date
                $realAmountApprovedLimits[] = $unit * (1 - $receiptDateUsedUnitCount);
            } else {
                //approving to empty date
                $realAmountApprovedLimits[] = $unit * ($workingDaysPerWeek - $receiptWeekUntransferableUnitCount);

                //if only parts of units left then you can only approve to partially filled dates
                if ($receiptMonthUntransferableUnitCount >= $finalMonthlyLimit) {
                    $receipt->AddError("receipt-only-parts-of-units-left", "receipt");

                    return false;
                }
            }
            $realAmountApprovedLimits[] = $unit;
        }

        $realAmountApproved = min($realAmountApprovedLimits);
        $realAmountApproved = max(array($realAmountApproved, 0));

        if ($this->GetEmployeeMealGrant($receipt) > 0 && ($realAmountApproved < $unit || $amountApproved < $fullUnit)) {
            $errorObject->AddError("receipt-unit-is-not-filled", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

        if ($realAmountApproved == 0) {
            $errorObject->AddError("receipt-empty-real-approved-value", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

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

        if ($receiptAfter->GetProperty("status") != "approve_proposed" || ($receiptAfter->GetProperty("status") == $receiptBefore->GetProperty("status") && $receiptAfter->GetProperty("amount_approved") == $receiptBefore->GetProperty("amount_approved"))) {
            return;
        }

        $contract = new Contract("product");
        $employeeId = $receiptAfter->GetProperty("employee_id");
        $date = $receiptAfter->GetProperty("document_date");

        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employeeId,
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $date
        );
        //if not weekly shopping and receipt date is weekend set receipt date = next monday
        if (!$weeklyShopping) {
            $weekDay = date("w", strtotime($date));
            $workingDaysPerWeek = intval(Employee::GetEmployeeField(
                $receiptAfter->GetProperty("employee_id"),
                "working_days_per_week"
            ));

            $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
            $workingDaysPerWeekLimits[] = 5;

            $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $date = date('Y-m-d', strtotime('next Monday', strtotime($date)));
            }
        }
        $mealGrantMandatory = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            Option::GetOptionIDByCode(OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY),
            $employeeId,
            $date
        );

        //in case when employee has "employee meal grant mandatory" he can approve only >= 1 unit, see ValidateReceiptApprove
        if ($mealGrantMandatory == "Y") {
            return;
        }

        $result = true;

        if ($weeklyShopping) {
            //when weekly shopping is enabled we just check current receipt amount because in this case daily unit cannot be filled by the multiple receipts
            if ($receiptAfter->GetProperty("real_amount_approved") < $this->GetApiUnit($receiptAfter)) {
                $result = false;
            }
        } else {
            //when weekly shopping is disabled employee can fill daily unit by the multiple receipt - so we check the total approved unit count for receipt's date
            $receiptDate = date("Y-m-d", strtotime($date));
            $receiptDateUsedUnitCount = $this->GetUsedUnitCount(
                $receiptAfter->GetProperty("employee_id"),
                $receiptAfter->GetProperty("group_id"),
                0,
                $receiptDate,
                $receiptDate
            );
            if ($receiptDateUsedUnitCount < 1) {
                $result = false;
            }
        }

        if ($result != false) {
            return;
        }

        $pushReceipt = new Receipt("receipt");
        $pushReceipt->LoadByID($receiptAfter->GetProperty("receipt_id"));

        $receiptAfter->SendReceiptCommentApprovedLessThanUnit();
        $pushReceipt->SendReceiptCommentPushNotification();
    }

    /**
     * Returns map of date=>unit_count matched from receipts with one of the passed statusList
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param array $statusList
     *
     * @return array
     */
    public function GetWeeklyShoppingDateToUnitMap($employeeID, $groupID, $exceptReceiptID, $statusList)
    {
        static $resultCacheMap;

        $cacheKey = $employeeID . "_" . $groupID . "_" . $exceptReceiptID . "_" . implode("_", $statusList);
        if (is_array($resultCacheMap) && isset($resultCacheMap[$cacheKey])) {
            return $resultCacheMap[$cacheKey];
        }

        $map = array();

        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));
        if ($workingDaysPerWeek < 1 || $workingDaysPerWeek > 7) {
            return $map;
        }

        //define a set of employee's working days of week (1 - monday, 7 - sunday)
        $workingDaysOfWeek = array();
        for ($i = 1; $i <= $workingDaysPerWeek; $i++) {
            $workingDaysOfWeek[] = $i;
        }

        //load all receipts of current contract
        $stmt = GetStatement();
        $where = array();
        $where[] = "receipt_id!=" . intval($exceptReceiptID);
        $where[] = "employee_id=" . intval($employeeID);
        $where[] = "group_id=" . intval($groupID);
        $where[] = "status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")";
        $where[] = "archive='N'";

        $query = "SELECT receipt_id, employee_id, document_date, real_amount_approved, receipt_from
					FROM receipt " . (count($where) > 0 ? "
					WHERE " . implode(" AND ", $where) : "") . "
					ORDER BY document_date ASC, receipt_id ASC";
        $receiptList = $stmt->FetchList($query);
        $receiptListIDs = array_column($receiptList, "receipt_id");

        //sort by datetime of "approve_proposed" status
        if (count($receiptListIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $query = "SELECT MAX(created) as created, receipt_id
                    FROM receipt_history
                    WHERE property_name = 'status' AND value = 'approve_proposed' AND receipt_id in (" . implode(
                ", ",
                $receiptListIDs
            ) . ")
                    GROUP BY receipt_id";
            $historyList = $stmt->FetchList($query);
            $historyReceiptID = array_column($historyList, "receipt_id");

            foreach ($receiptListIDs as $receiptKey => $receiptID) {
                $key = array_search($receiptID, $historyReceiptID);
                $receiptList[$receiptKey]["status_updated"] = $key !== false
                    ? strtotime($historyList[$key]["created"])
                    : 0;
            }
            $statusUpdated = array_column($receiptList, "status_updated");
            array_multisort($statusUpdated, SORT_ASC, $receiptList);
        }

        //match restaurant receipts
        foreach ($receiptList as $receipt) {
            if ($receipt["receipt_from"] != "restaurant") {
                continue;
            }

            //get weekly shopping contract for receipt's date
            $weeklyShopping = false;
            $weeklyShoppingContractList = ContractList::GetEmployeeContractListByProductID(
                $receipt["employee_id"],
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)
            );

            foreach ($weeklyShoppingContractList as $weeklyShoppingContract) {
                if (
                    (empty($weeklyShoppingContract["end_date"]) && strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"])) ||
                    (strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"]) && strtotime($weeklyShoppingContract["end_date"]) >= strtotime($receipt["document_date"]))
                ) {
                    $weeklyShopping = true;
                    break;
                }
            }

            if (!$weeklyShopping) {
                continue;
            }

            $apiUnit = $this->GetApiUnit(new Receipt("receipt", $receipt));
            $unit = $this->GetUnit(new Receipt("receipt", $receipt));
            $approvedUnitCount = $receipt["real_amount_approved"] / $apiUnit;
            $approvedUnitCount = round($approvedUnitCount, 5); //fixing float
            if ($approvedUnitCount == 0 || is_infinite($approvedUnitCount)) {
                continue;
            }

            //restaurant receipts always has max 1 unit and should be matched to their document date
            $dateToMatch = date("Y-m-d", strtotime($receipt["document_date"]));
            if (!isset($map[$dateToMatch])) {
                $map[$dateToMatch] = array(
                    "receipt_id" => [$receipt["receipt_id"]],
                    "approved_unit_count" => $approvedUnitCount,
                    "approved_unit_count_by_receipt" => [$receipt["receipt_id"] => $approvedUnitCount],
                    "receipt_from" => $receipt["receipt_from"],
                    "unit_api" => $apiUnit,
                    "unit" => $unit
                );
            } else {
                $map[$dateToMatch]["receipt_id"][] = $receipt["receipt_id"];
                $map[$dateToMatch]["approved_unit_count"] += $approvedUnitCount;
                $map[$dateToMatch]["approved_unit_count_by_receipt"][$receipt["receipt_id"]] = $approvedUnitCount;
            }
        }

        //match shop receipts
        foreach ($receiptList as $receipt) {
            if ($receipt["receipt_from"] != "shop") {
                continue;
            }

            //get weekly shopping contract for receipt's date
            $weeklyShopping = false;
            $weeklyShoppingContractList = ContractList::GetEmployeeContractListByProductID(
                $receipt["employee_id"],
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)
            );

            foreach ($weeklyShoppingContractList as $weeklyShoppingContract) {
                if (
                    (empty($weeklyShoppingContract["end_date"]) && strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"])) ||
                    (strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"]) && strtotime($weeklyShoppingContract["end_date"]) >= strtotime($receipt["document_date"]))
                ) {
                    $weeklyShopping = true;
                    break;
                }
            }

            if (!$weeklyShopping) {
                continue;
            }

            $apiUnit = $this->GetApiUnit(new Receipt("receipt", $receipt));
            $unit = $this->GetUnit(new Receipt("receipt", $receipt));
            $approvedUnitCount = $receipt["real_amount_approved"] / $apiUnit;
            $approvedUnitCount = round($approvedUnitCount, 5); //fixing float
            if ($approvedUnitCount == 0 || is_infinite($approvedUnitCount)) {
                continue;
            }

            $dateToMatch = date("Y-m-d", strtotime($receipt["document_date"]));

            $to = ceil($approvedUnitCount);

            for ($i = 0; $i < $to; $i++) {
                $unitCount = $approvedUnitCount - floor($approvedUnitCount);
                $unitCount = $i == ($to - 1) && ($unitCount) != 0 ? $unitCount : 1;

                //we can match receipt unit only to employee's working day not used by another receipt
                while (
                    !in_array(
                        date("N", strtotime($dateToMatch)),
                        $workingDaysOfWeek
                    ) || isset($map[$dateToMatch]) && ($map[$dateToMatch]["approved_unit_count"] == 1 || ($map[$dateToMatch]["approved_unit_count"] + $unitCount) > 1)
                ) {
                    $dateToMatch = date("Y-m-d", strtotime($dateToMatch . " +1 day"));
                }

                if (!isset($map[$dateToMatch])) {
                    $map[$dateToMatch] = array(
                        "receipt_id" => [$receipt["receipt_id"]],
                        "approved_unit_count" => $unitCount,
                        "approved_unit_count_by_receipt" => [$receipt["receipt_id"] => $unitCount],
                        "receipt_from" => $receipt["receipt_from"],
                        "unit_api" => $apiUnit,
                        "unit" => $unit
                    );
                } else {
                    $map[$dateToMatch]["receipt_id"][] = $receipt["receipt_id"];
                    $map[$dateToMatch]["approved_unit_count"] += $unitCount;
                    $map[$dateToMatch]["approved_unit_count_by_receipt"][$receipt["receipt_id"]] = $unitCount;
                }
            }
        }

        ksort($map);

        $resultCacheMap[$cacheKey] = $map;

        return $map;
    }

    /**
     * Returns count of used units calculated by receipts with status approved/approve_proposed
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param string|array $dateFrom
     * @param string|array $dateTo
     * @param string $mode
     *
     * @return number
     */
    public function GetUsedUnitCount($employeeID, $groupID, $exceptReceiptID, $dateFrom, $dateTo, $mode = "admin")
    {
        $used = $this->GetUsed($employeeID, $groupID, $exceptReceiptID, $dateFrom, $dateTo, $mode);

        return $used["used_unit_count"];
    }

    /**
     * Returns count of ceil'ed used units calculated by receipts with status approved/approve_proposed
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param string|array $dateFrom
     * @param string|array $dateTo
     * @param string $mode
     *
     * @return number
     */
    public function GetUntransferableUnitCount(
        $employeeID,
        $groupID,
        $exceptReceiptID,
        $dateFrom,
        $dateTo,
        $mode = "admin"
    ) {
        $used = $this->GetUsed($employeeID, $groupID, $exceptReceiptID, $dateFrom, $dateTo, $mode);

        return $used["untransferable_unit_count"];
    }

    /**
     * Returns count of units and amount of euros calculated by receipts with status approved/approve_proposed
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int $exceptReceiptID
     * @param string|array $dateFrom
     * @param string|array $dateTo
     * @param string $mode
     * @param array $statusList
     *
     * @return array
     */
    public function GetUsed(
        $employeeID,
        $groupID,
        $exceptReceiptID,
        $dateFrom,
        $dateTo,
        $mode = "admin",
        $statusList = ["approved", "approve_proposed"]
    ) {
        $dateFrom = !is_array($dateFrom) ? array($dateFrom) : $dateFrom;
        $dateTo = !is_array($dateTo) ? array($dateTo) : $dateTo;

        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        $usedUnitCount = 0;
        $usedEuroAmount = 0;
        $untransferableUnitCount = 0;
        $untransferableUnitMap = array();

        /**
         * Step 1 - process receipts without weekly shopping
         */
        $stmt = GetStatement();
        $where = array();
        $where[] = "receipt_id!=" . intval($exceptReceiptID);
        $where[] = "employee_id=" . intval($employeeID);
        $where[] = "group_id=" . intval($groupID);
        $where[] = "status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")";
        $where[] = "archive='N'";

        $contract = new Contract("product");
        foreach ($dateFrom as $date) {
            $weeklyShopping = $contract->LoadContractForDate(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
                $date
            );

            if (!$weeklyShopping) {
                $weekDay = date("w", strtotime($date));
                //if date range starts from monday - move start date range to previous weekends start date
                if ($weekDay == 1 && $workingDaysPerWeekResult < 7) {
                    $weekFromObject = new DateTime($date);
                    $weekFromObject->modify("-" . (7 - $workingDaysPerWeekResult) . " days");
                    $date = $weekFromObject->format("Y-m-d");
                } //if date range starts from weekend date - move start date range to current weekends start date
                elseif (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $weekFromObject = new DateTime($date);
                    $weekFromObject->modify("-" . ($weekDay == 0 ? 7 - $workingDaysPerWeekResult - 1 : $weekDay - $workingDaysPerWeekResult - 1) . " days");
                    $date = $weekFromObject->format("Y-m-d");
                }
            }
            $where[] = "DATE(document_date) >= " . Connection::GetSQLDate($date);
        }
        foreach ($dateTo as $date) {
            $weeklyShopping = $contract->LoadContractForDate(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
                $date
            );
            if (!$weeklyShopping) {
                $weekDay = date("w", strtotime($date));
                //if date range ends with weekend - move end date range to last previous working date
                if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $weekFromObject = new DateTime($date);
                    $weekFromObject->modify("-" . ($weekDay == 0 ? 7 - $workingDaysPerWeekResult : $weekDay - $workingDaysPerWeekResult) . " days");
                    $date = $weekFromObject->format("Y-m-d");
                }
            }
            $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($date);
        }

        $query = "SELECT receipt_id FROM receipt WHERE " . implode(" AND ", $where);
        $receiptList = $stmt->FetchList($query);
        if (count($receiptList) > 0) {
            $receiptIDs = array_column($receiptList, "receipt_id");
            foreach ($receiptIDs as $receiptID) {
                $receipt = new Receipt("receipt");
                $receipt->LoadByID($receiptID);

                $weeklyShoppingContract = new Contract("product");
                $weeklyShopping = $weeklyShoppingContract->LoadContractForDate(
                    OPTION_LEVEL_EMPLOYEE,
                    $receipt->GetProperty("employee_id"),
                    Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
                    $receipt->GetProperty("document_date")
                );
                if ($weeklyShopping) {
                    continue;
                }

                $receiptTime = strtotime($receipt->GetProperty("document_date"));
                $receiptDate = date("Y-m-d", $receiptTime);
                $receiptWeekDay = date("w", $receiptTime);
                //move receipts from weekends to next monday
                if (($receiptWeekDay == 0 ? 7 : $receiptWeekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $receiptDate = date('Y-m-d', strtotime('next Monday', $receiptTime));
                }
                if ($mode == "admin") {
                    $usedEuroAmount += $receipt->GetProperty("real_amount_approved");
                    $adminUnitEuro = $this->GetApiUnit($receipt);
                    $unitCount = $adminUnitEuro > 0
                        ? $receipt->GetProperty("real_amount_approved") / $adminUnitEuro
                        : 0;
                    $unitCount = number_format($unitCount, 6, ".", "");
                    $usedUnitCount += $unitCount;

                    if (isset($untransferableUnitMap[$receiptDate])) {
                        $untransferableUnitMap[$receiptDate] += $unitCount;
                    } else {
                        $untransferableUnitMap[$receiptDate] = $unitCount;
                    }
                } elseif ($mode == "api") {
                    $apiRealAmountApproved = $this->GetApiRealAmountApproved($receipt);
                    $apiUnitEuro = $this->GetMealValue($receipt) + $this->GetEmployerMealGrant($receipt);

                    $usedEuroAmount += $apiRealAmountApproved;
                    $unitCount = $apiUnitEuro > 0 ? $apiRealAmountApproved / $apiUnitEuro : 0;
                    $unitCount = number_format($unitCount, 6, ".", "");
                    $usedUnitCount += $unitCount;

                    if (isset($untransferableUnitMap[$receiptDate])) {
                        $untransferableUnitMap[$receiptDate] += $unitCount;
                    } else {
                        $untransferableUnitMap[$receiptDate] = $unitCount;
                    }
                }
            }
        }

        /**
         * Step 2 - process receipts with weekly shopping
         */
        $dateToUnitMap = $this->GetWeeklyShoppingDateToUnitMap($employeeID, $groupID, $exceptReceiptID, $statusList);
        $usedDateList = array_keys($dateToUnitMap);

        $dateList = GetDateRange(
            date("Y-m-d", max(array_map("strtotime", $dateFrom))),
            date("Y-m-d", min(array_map("strtotime", $dateTo)))
        );
        $intersectionDateList = array_intersect($dateList, $usedDateList);
        foreach ($intersectionDateList as $date) {
            if ($mode == "admin") {
                $usedEuroAmount += $dateToUnitMap[$date]["unit"] * $dateToUnitMap[$date]["approved_unit_count"];
                $usedUnitCount += $dateToUnitMap[$date]["approved_unit_count"];
            } elseif ($mode == "api") {
                $usedEuroAmount += $dateToUnitMap[$date]["unit_api"] * $dateToUnitMap[$date]["approved_unit_count"];
                $usedUnitCount += $dateToUnitMap[$date]["approved_unit_count"];
            }

            foreach ($dateToUnitMap[$date]["approved_unit_count_by_receipt"] as $receiptID => $unitCount) {
                $receiptDate = date("Y-m-d", strtotime(Receipt::GetReceiptFieldByID("document_date", $receiptID)));
                if (isset($untransferableUnitMap[$receiptDate])) {
                    $untransferableUnitMap[$receiptDate] += $unitCount;
                } else {
                    $untransferableUnitMap[$receiptDate] = $unitCount;
                }
            }
        }

        return array(
            "approved_euro_amount" => $usedEuroAmount,
            "used_unit_count" => $usedUnitCount,
            "untransferable_unit_count" => array_sum(array_map("ceil", $untransferableUnitMap))
        );
    }

    /**
     * Returns count of units used on the week by passed date. Includes used units transfer for employees with weekly purchase service.
     *
     * @param int $employeeID
     * @param string $date
     *
     * @return number
     */
    public function GetUsedUnitCountByWeek($employeeID, $date, $exceptReceiptID)
    {
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD);

        $contract = new Contract("product");
        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employeeID,
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $date
        );

        $employee = new Employee("company");
        $employee->LoadByID($employeeID);

        $weekDay = date("w", strtotime($date));

        //get used unit count for passed week. force monday to be start of the week
        $weekFromObject = new DateTime($date);
        $weekFromObject->modify("-" . ($weekDay == 0 ? 6 : $weekDay - 1) . " days");
        $weekFrom = $weekFromObject->format("Y-m-d");

        $weekToObject = new DateTime($date);
        $weekToObject->modify("+" . ($weekDay == 0 ? 0 : 7 - $weekDay) . " days");
        $weekTo = $weekToObject->format("Y-m-d");

        $weekUsedUnitCount = $this->GetUsedUnitCount($employeeID, $groupID, $exceptReceiptID, $weekFrom, $weekTo);

        //add unit overcap from prev week
        if ($weeklyShopping) {
            $prevWeekFromObject = clone $weekFromObject;
            $prevWeekFromObject->modify("-7 days");
            $prevWeekFrom = $prevWeekFromObject->format("Y-m-d");

            $prevWeekToObject = clone $weekToObject;
            $prevWeekToObject->modify("-7 days");
            $prevWeekTo = $prevWeekToObject->format("Y-m-d");

            $prevWeekUsedUnitCount = $this->GetUsedUnitCount(
                $employeeID,
                $groupID,
                $exceptReceiptID,
                $prevWeekFrom,
                $prevWeekTo
            );
            if ($prevWeekUsedUnitCount > $employee->GetIntProperty("working_days_per_week")) {
                $weekUsedUnitCount += $prevWeekUsedUnitCount - $employee->GetIntProperty("working_days_per_week");
            }
        }

        return $weekUsedUnitCount;
    }

    /**
     * Returns count of units used on the month by passed date
     *
     * @param int $employeeID
     * @param string $date
     *
     * @return number
     */
    public function GetUsedUnitCountByMonth($employeeID, $date, $exceptReceiptID)
    {
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD);

        $monthFrom = date("Y-m-01", strtotime($date));
        $monthTo = date("Y-m-t", strtotime($date));

        return $this->GetUsedUnitCount($employeeID, $groupID, $exceptReceiptID, $monthFrom, $monthTo);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::AppendAdditionalInfo()
     */
    public function AppendAdditionalInfo($receipt)
    {
        $amountApprovedRest = $receipt->GetFloatProperty("real_amount_approved");

        $mealValue = $this->GetMealValue($receipt);
        $employerMealGrant = $this->GetEmployerMealGrant($receipt);
        $employeeMealGrant = $this->GetEmployeeMealGrant($receipt);

        $contract = new Contract("product");
        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $receipt->GetProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $receipt->GetProperty("document_date")
        );

        $unit = $this->GetUnit($receipt);
        $approvedUnitCount = $unit > 0 ? $receipt->GetFloatProperty("real_amount_approved") / $unit : 0;

        if ($weeklyShopping && $approvedUnitCount > 1) {
            $mealValue *= $approvedUnitCount;
            $employerMealGrant *= $approvedUnitCount;
            $employeeMealGrant *= $approvedUnitCount;
        }

        $mealValueFilled = $amountApprovedRest > $mealValue ? $mealValue : $amountApprovedRest;
        $amountApprovedRest -= $mealValueFilled;

        $employerMealGrantFilled = $amountApprovedRest > $employerMealGrant
            ? $employerMealGrant
            : $amountApprovedRest;
        $amountApprovedRest -= $employerMealGrantFilled;

        $employeeMealGrantFilled = $amountApprovedRest > $employeeMealGrant
            ? $employeeMealGrant
            : $amountApprovedRest;
        $amountApprovedRest -= $employeeMealGrantFilled;

        $data = array(
            "INFO_meal_value" => $mealValue,
            "INFO_meal_value_filled" => $mealValueFilled,
            "INFO_employer_meal_grant" => $employerMealGrant,
            "INFO_employer_meal_grant_filled" => $employerMealGrantFilled,
            "INFO_employee_meal_grant" => $employeeMealGrant,
            "INFO_employee_meal_grant_filled" => $employeeMealGrantFilled,

            "INFO_approved_unit_count" => in_array(
                $receipt->GetProperty("status"),
                array("approve_proposed", "approved")
            ) ? round($approvedUnitCount, 2) : 0,
        );
        $receipt->AppendFromArray($data);
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetApiRealAmountApproved()
     */
    public function GetApiRealAmountApproved($receipt)
    {
        $receiptClone = clone $receipt;
        $this->AppendAdditionalInfo($receiptClone);

        return $receipt->GetFloatProperty("real_amount_approved") - $receiptClone->GetFloatProperty("INFO_employee_meal_grant_filled");
    }

    /**
     * Returns value of MealValue option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetMealValue($receipt)
    {
        $result = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__MEAL_VALUE,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return $result ? floatval($result) : $result;
    }

    /**
     * Returns value of EmployerMealGrant option for receipt's owner and date
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetEmployerMealGrant($receipt)
    {
        $result = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return $result ? floatval($result) : $result;
    }

    /**
     * Returns value of EmployeeMealGrant option for receipt's owner and date ONLY if EmployeeMealGrantMandatory option is enabled
     *
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetEmployeeMealGrant($receipt)
    {
        $result = 0;

        $employeeMealGrantMandatory = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        if ($employeeMealGrantMandatory == "Y") {
            $result = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT,
                $receipt->GetProperty("employee_id"),
                $receipt->GetProperty("document_date")
            );
        }

        return $result ? floatval($result) : $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetOptions()
     */
    public function GetOptions($receipt)
    {
        //clone object to prevent original object modification
        $optionReceipt = clone $receipt;
        if (!$optionReceipt->GetProperty("document_date")) {
            $optionReceipt->SetProperty("document_date", GetCurrentDate());
        }

        $contract = new Contract("product");
        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $optionReceipt->GetProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $optionReceipt->GetProperty("document_date")
        );
        $workingDaysPerWeek = intval(Employee::GetEmployeeField(
            $optionReceipt->GetProperty("employee_id"),
            "working_days_per_week"
        ));
        //if not weekly shopping and receipt date is weekend set receipt date = next monday
        if (!$weeklyShopping) {
            $weekDay = date("w", strtotime($optionReceipt->GetProperty("document_date")));

            $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
            $workingDaysPerWeekLimits[] = 5;

            $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $optionReceipt->SetProperty(
                    "document_date",
                    date('Y-m-d', strtotime('next Monday', strtotime($optionReceipt->GetProperty("document_date"))))
                );
            }
        }

        $employee = new Employee("company");
        $employee->LoadByID($optionReceipt->GetProperty("employee_id"));

        $weekUsedUnitCount = $this->GetUsedUnitCountByWeek(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date"),
            0
        );
        $currentWeekUnitsLeft = $employee->GetIntProperty("working_days_per_week") - $weekUsedUnitCount;

        $monthUsedUnitCount = $this->GetUsedUnitCountByMonth(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date"),
            0
        );
        $finalMonthlyLimit = $this->GetUnitCountLimitForMonth(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("group_id"),
            0,
            $optionReceipt->GetProperty("document_date"),
            true
        );
        $currentMonthUnitsLeft = $finalMonthlyLimit - $monthUsedUnitCount;

        $monthTransferredUnitCount = $this->GetTransferredUnitCountForMonth(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date"),
            0
        );

        $result = array(
            array(
                "value" => $workingDaysPerWeek,
                "title_translation" => GetTranslation("employee-working-days-per-week", "company")
            ),
            array(
                "value" => $this->GetUnit($optionReceipt),
                "title_translation" => GetTranslation("option-food__main__unit", "product")
            ),
            array(
                "value" => $this->GetMealValue($optionReceipt),
                "title_translation" => GetTranslation("option-" . OPTION__FOOD__MAIN__MEAL_VALUE, "product")
            ),
            array(
                "value" => $this->GetEmployerMealGrant($optionReceipt),
                "title_translation" => GetTranslation("option-" . OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT, "product")
            ),
            array(
                "value" => $this->GetEmployeeMealGrant($optionReceipt),
                "title_translation" => GetTranslation("option-" . OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT, "product")
            ),
            array(
                "value" => max(array(round($currentWeekUnitsLeft, 2), 0)),
                "title_translation" => GetTranslation("info-current-week-units-left", "product")
            ),
            array(
                "value" => max(array(round($currentMonthUnitsLeft, 2), 0)),
                "title_translation" => GetTranslation("info-current-month-units-left", "product")
            ),
            array(
                "value" => max(array(round($monthTransferredUnitCount, 2), 0)),
                "title_translation" => GetTranslation("info-current-month-transferred-units", "product")
            )
        );

        $contract = new Contract("product");
        if (
            $contract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $optionReceipt->GetIntProperty("employee_id"),
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)
            )
        ) {
            if ($employee->GetIntProperty("working_days_per_week") > 0) {
                $result[] = array(
                    "value" => intval($employee->GetProperty("working_days_per_week")) . " " . GetTranslation(
                        "info-working-days-per-week-suffix",
                        "product"
                    ),
                    "title_translation" => GetTranslation("info-weekly-purchase-active", "product")
                );
            } else {
                $result[] = array(
                    "value" => GetTranslation("info-working-days-per-week-empty", "product"),
                    "title_translation" => GetTranslation("info-weekly-purchase-active", "product")
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $receiptList = new ReceiptList("receipt");
        $receiptList->LoadReceiptListForAddison($companyUnitID, $groupID, $payrollDate, $exportType);
        $receiptList = $receiptList->GetItems();
        $payrollMonth = date_create($payrollDate);
        $dateFrom = $payrollMonth->format('Y-m-01');
        $dateTo = $payrollMonth->format('Y-m-t');

        $lineList = array();
        $employeeMap = array();

        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);

        $toExport = array();
        $receiptIDs = array_column($receiptList, "receipt_id");

        $weeklyShoppingContract = new Contract("product");
        $weeklyShoppingEmployeeIDs = $weeklyShoppingContract->GetEmployeeIDsWithContract(Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING));

        $weeklyShoppingEmployeeIDs = is_array($weeklyShoppingEmployeeIDs)
            ? array_intersect($employeeIDs, $weeklyShoppingEmployeeIDs)
            : array();

        /**
         * Step 1 - collect receipts without weekly shopping
         */
        if (count($receiptList) > 0) {
            foreach ($receiptList as $key => $receipt) {
                if (!in_array($receipt["employee_id"], $weeklyShoppingEmployeeIDs)) {
                    $weeklyShopping = false;
                } else {
                    $weeklyShopping = false;
                    $weeklyShoppingContractList = ContractList::GetEmployeeContractListByProductID(
                        $receipt["employee_id"],
                        Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)
                    );

                    foreach ($weeklyShoppingContractList as $weeklyShoppingContract) {
                        if (
                            (empty($weeklyShoppingContract["end_date"]) && strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"])) ||
                            (strtotime($weeklyShoppingContract["start_date"]) <= strtotime($receipt["document_date"]) && strtotime($weeklyShoppingContract["end_date"]) >= strtotime($receipt["document_date"]))
                        ) {
                            $weeklyShopping = true;
                            break;
                        }
                    }
                }

                $receiptID = $receipt["receipt_id"];

                if ($weeklyShopping) {
                    continue;
                }

                $receipt = new Receipt("receipt");
                $receipt->LoadByID($receiptID);

                $receipt->SetProperty("weekly_shopping", 0);
                $date = $receipt->GetProperty("document_date");
                $workingDaysPerWeek = intval(Employee::GetEmployeeField(
                    $receipt->GetProperty("employee_id"),
                    "working_days_per_week"
                ));

                $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
                $workingDaysPerWeekLimits[] = 5;

                $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

                $weekDay = date("w", strtotime($date));
                //move receipts from weekends to next monday
                if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $receipt->SetProperty(
                        "document_date",
                        date('Y-m-d', strtotime('next Monday', strtotime($date)))
                    );
                }
                if (strtotime($receipt->GetProperty("document_date")) > strtotime($dateTo)) {
                    continue;
                }

                $toExport[] = $receipt->GetProperties();
            }
        }

        /*
         * Step 2 - collect receipts with weekly shopping
         */
        foreach ($weeklyShoppingEmployeeIDs as $key => $employeeID) {
            $dateToUnitMap = $this->GetWeeklyShoppingDateToUnitMap($employeeID, $groupID, 0, array("approved"));
            $usedDateList = array_keys($dateToUnitMap);
            $receiptDates = array();
            foreach ($dateToUnitMap as $k => $v) {
                foreach ($v['receipt_id'] as $receiptID) {
                    if (!in_array($receiptID, $receiptIDs) || $k >= $dateFrom) {
                        continue;
                    }

                    $receiptDates[] = $k;
                }
            }

            $exportDateList = GetDateRange($dateFrom, $dateTo);
            $intersectionDateList = array_intersect($exportDateList, $usedDateList);
            $intersectionDateList = array_merge($receiptDates, $intersectionDateList);
            foreach ($intersectionDateList as $date) {
                foreach ($dateToUnitMap[$date]["approved_unit_count_by_receipt"] as $receiptID => $uniCount) {
                    $receipt = new Receipt("receipt");
                    $receipt->LoadByID($receiptID);

                    $receipt->SetProperty("weekly_shopping", 1);
                    $receipt->SetProperty("weekly_shopping_date", $date);
                    $receipt->SetProperty(
                        "amount_approved",
                        $receipt->GetProperty("amount_approved") / $dateToUnitMap[$date]["approved_unit_count"]
                    );
                    $receipt->SetProperty("real_amount_approved", $dateToUnitMap[$date]["unit_api"] * $uniCount);

                    $toExport[] = $receipt->GetProperties();
                }
            }
        }

        /**
         * Step 3 - group receipts by document date/weekly shopping date
         */
        $groupedToExport = [];
        foreach ($toExport as $receipt) {
            if ($receipt["document_date"] < $dateFrom) {
                continue;
            }

            $date = $receipt["weekly_shopping"] ? $receipt["weekly_shopping_date"] : $receipt["document_date"];

            $keysByEmployee = array_keys(array_column($groupedToExport, "employee_id"), $receipt["employee_id"]);
            $key = false;
            foreach ($keysByEmployee as $employeeKey) {
                if (strtotime($groupedToExport[$employeeKey]["document_date"]) != strtotime($date)) {
                    continue;
                }

                $key = $employeeKey;
            }
            if ($key === false) {
                $groupedToExport[] = [
                    "document_date" => $date,
                    "employee_id" => $receipt["employee_id"],
                    "legal_receipt_ids" => [],
                    "receipt_ids" => [],
                    "amount_approved" => 0,
                    "real_amount_approved" => 0,
                    "receipt_from" => "",
                ];
                $key = count($groupedToExport) - 1;
            }
            $groupedToExport[$key]["legal_receipt_ids"] = array_merge(
                $groupedToExport[$key]["legal_receipt_ids"],
                [$receipt["legal_receipt_id"]]
            );
            $groupedToExport[$key]["receipt_ids"] = array_merge(
                $groupedToExport[$key]["receipt_ids"],
                [$receipt["receipt_id"]]
            );
            $groupedToExport[$key]["amount_approved"] += $receipt["amount_approved"];
            $groupedToExport[$key]["real_amount_approved"] += $receipt["real_amount_approved"];

            if ($groupedToExport[$key]["receipt_from"] !== "" && $groupedToExport[$key]["receipt_from"] === "restaurant" || $receipt["receipt_from"] != "restaurant") {
                continue;
            }

            $groupedToExport[$key]["receipt_from"] = $receipt["receipt_from"];
        }

        /**
         * Step 4 - process collected receipts depending on their types
         */
        if (count($groupedToExport) > 0) {
            foreach ($groupedToExport as $key => $receiptGroup) {
                $employeeID = $receiptGroup["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employee = new Employee("company");
                    $employee->LoadByID($employeeID);

                    $employeeMap[$employeeID] = array(
                        "employee_property_list" => $employee->GetProperties(),
                        "salary_option" => Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__FOOD__MAIN__SALARY_OPTION,
                            $employeeID,
                            $receiptGroup["document_date"]
                        )
                    );
                }

                $employeeMap[$employeeID]["receipt_ids"] = $receiptGroup["receipt_ids"];

                $receiptObject = new Receipt("receipt");
                $receiptObject->SetProperty("document_date", $receiptGroup["document_date"]);
                $receiptObject->SetProperty("employee_id", $receiptGroup["employee_id"]);

                $monthMapKey = date("Ym", strtotime($receiptGroup["document_date"]));

                if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey] = array(
                        "tax_flat" => array(
                            "title" => "A",
                            "acc_key" => "acc_meal_value_tax_flat",
                            "amount" => null,
                            "receipt_ids" => array(),
                            "legal_receipt_ids" => array()
                        ),
                        "tax_free" => array(
                            "title" => "B",
                            "acc_key" => "acc_food_subsidy_tax_free",
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

                //preparing A, B and C values
                $mealValue = $this->GetMealValue($receiptObject);
                $employerMealGrant = $this->GetEmployerMealGrant($receiptObject);
                $employeeMealGrant = $this->GetEmployeeMealGrant($receiptObject);
                $unit = $mealValue + $employerMealGrant;

                $contract = new Contract("product");
                $flatRateTaxation =
                    $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION),
                        $employeeID,
                        $receiptGroup["document_date"]
                    )
                 ? 1 : 0;

                //intermediate calculations
                if ($receiptGroup["real_amount_approved"] > $mealValue) {
                    $exportA = $mealValue;
                    $exportB = $receiptGroup["real_amount_approved"] - $exportA;
                } else {
                    $exportA = $receiptGroup["real_amount_approved"];
                    $exportB = 0;
                }

                $difference = $receiptGroup["amount_approved"] - ($exportA + $exportB);
                if ($difference > 0) {
                    $employeeMealGrantForCalc = $employeeMealGrant > 0 ? $employeeMealGrant : Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT,
                        $employeeID,
                        $receiptGroup["document_date"]
                    );
                    $exportC = $difference >= $employeeMealGrantForCalc ? $employeeMealGrantForCalc : $difference;
                } else {
                    $exportC = 0;
                }

                //tax flat and tax free calculations
                if ($employeeMealGrant > 0) { //if mandatory option is on
                    $taxFlat = 0;
                    $taxFree = $exportA + $exportB;
                } elseif ($flatRateTaxation == 1 && $receiptGroup["receipt_from"] == "restaurant") { //if validation flat tax in on
                    $taxFlat = $receiptGroup["amount_approved"] >= $unit && $exportA - $exportC <= 0
                        ? 0
                        : $exportA - $exportC;

                    $taxFree = $receiptGroup["real_amount_approved"] >= $unit
                        ? $unit - $taxFlat
                        : $receiptGroup["real_amount_approved"] - $taxFlat;
                } else //if it's a regular food
                {
                    $taxFlat = $receiptGroup["real_amount_approved"] >= $mealValue ? $mealValue : $exportA;

                    if ($receiptGroup["real_amount_approved"] < $unit) {
                        $taxFree = $receiptGroup["real_amount_approved"] - $exportA < 0
                            ? 0
                            : $receiptGroup["real_amount_approved"] - $exportA;
                    } else {
                        $taxFree = $employerMealGrant;
                    }
                }

                $taxFlat = round($taxFlat, 5);
                $taxFree = round($taxFree, 5);

                $exportNegative = 0;

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["amount"] += $taxFlat;
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["amount"] += $taxFree;
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["amount"] -= $exportNegative;

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["receipt_ids"],
                    $receiptGroup["receipt_ids"]
                );
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["receipt_ids"],
                    $receiptGroup["receipt_ids"]
                );
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["receipt_ids"],
                    $receiptGroup["receipt_ids"]
                );

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["legal_receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["legal_receipt_ids"],
                    $receiptGroup["legal_receipt_ids"]
                );
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["legal_receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["legal_receipt_ids"],
                    $receiptGroup["legal_receipt_ids"]
                );
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["legal_receipt_ids"] = array_merge(
                    $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["legal_receipt_ids"],
                    $receiptGroup["legal_receipt_ids"]
                );
            }
        }

        foreach ($employeeMap as $employeeID => $employee) {
            foreach ($employee["month_map"] as $monthMapKey => $month) {
                foreach ($month as $lineKey => $line) {
                    if (count($line["receipt_ids"]) == 0) {
                        continue;
                    }

                    $line["receipt_ids"] = array_values(array_unique($line["receipt_ids"]));
                    $line["legal_receipt_ids"] = array_values(array_unique($line["legal_receipt_ids"]));
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
                    if ($exportType != "pdf") {
                        continue;
                    }

                    $tmpKey = count($lineList) - 1;
                    $lineList[$tmpKey]["tax_flat"] = $employee["month_map"][$monthMapKey]["tax_flat"];
                    $lineList[$tmpKey]["tax_free"] = $employee["month_map"][$monthMapKey]["tax_free"];
                }
            }
        }

        return $lineList;
    }

    function GetMainProductCode()
    {
        return PRODUCT__FOOD__MAIN;
    }

    /**
     * Returns list of dates with unit and used value (euro) for food unit calendar
     *
     * @param int $employeeID employee_id
     * @param string $dateFrom date of start list
     * @param string $dateTo date of end list
     *
     * @return array
     */
    public function GetUnitDateList($employeeID, $dateFrom, $dateTo)
    {
        if (!$dateFrom) {
            $dateFrom = date("01-m-Y");
        }

        if (!$dateTo) {
            $dateTo = date("t-m-Y", strtotime($dateFrom));
        }

        $unitDateList = array();
        $contract = new Contract("product");

        $weeklyShoppingReceiptMap = $this->GetWeeklyShoppingDateToUnitMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD),
            false,
            array("approve_proposed", "approved")
        );

        for ($i = strtotime($dateFrom); $i <= strtotime($dateTo); $i = strtotime("+1 day", $i)) {
            $weeklyShopping = $contract->LoadContractForDate(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
                date("d-m-Y", $i)
            );
            $unitAdmin = $this->GetUnit(new Receipt(
                "receipt",
                array("document_date" => date("d-m-Y", $i), "employee_id" => $employeeID)
            ), "admin");
            $unitApi = $this->GetApiUnit(new Receipt(
                "receipt",
                array("document_date" => date("d-m-Y", $i), "employee_id" => $employeeID)
            ), "api");
            if (!$weeklyShopping) {
                $receiptDate = date("Y-m-d", $i);
                $receiptDateApprovedAmountAdmin = $this->GetUsed(
                    $employeeID,
                    ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD),
                    false,
                    $receiptDate,
                    $receiptDate,
                    "admin"
                )["approved_euro_amount"];
                $receiptDateApprovedAmountApi = $this->GetUsed(
                    $employeeID,
                    ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD),
                    false,
                    $receiptDate,
                    $receiptDate,
                    "api"
                )["approved_euro_amount"];

                $unitDateList[] = array(
                    "date" => date("Y-m-d", $i),
                    "unit" => number_format($unitAdmin, 2, ".", ","),
                    "used" => number_format($receiptDateApprovedAmountAdmin, 2, ".", ","),
                    "unit_api" => number_format($unitApi, 2, ".", ","),
                    "used_api" => number_format($receiptDateApprovedAmountApi, 2, ".", ","),
                );
            } else {
                $usedAdmin = 0;
                $usedApi = 0;

                if (array_key_exists(date("Y-m-d", $i), $weeklyShoppingReceiptMap)) {
                    $mapByDate = $weeklyShoppingReceiptMap[date("Y-m-d", $i)];
                    foreach ($mapByDate["approved_unit_count_by_receipt"] as $unitCount) {
                        $usedAdmin += $unitCount * $mapByDate["unit"];
                        $usedApi += $unitCount * $mapByDate["unit_api"];
                    }
                }

                $unitDateList[] = array(
                    "date" => date("Y-m-d", $i),
                    "unit" => number_format($unitAdmin, 2, ".", ","),
                    "used" => number_format($usedAdmin, 2, ".", ","),
                    "unit_api" => number_format($unitApi, 2, ".", ","),
                    "used_api" => number_format($usedApi, 2, ".", ",")
                );
            }
        }

        return $unitDateList;
    }

    /**
     * Returns list of receipts for date (document_date or mapped to date for weeklyShopping)
     *
     * @param int $employeeID employee_id
     * @param string $date date
     *
     * @return array
     */
    public function GetReceiptListForDate($employeeID, $date)
    {
        $contract = new Contract("product");
        $weeklyShopping = $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employeeID,
            Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING),
            $date
        );

        $receiptList = array();
        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        $weekDay = date("w", strtotime($date));

        if (!$weeklyShopping) {
            $dateFrom = false;
            //if date is monday add dates from previous weekends
            if ($weekDay == 1 && $workingDaysPerWeek < 7) {
                $weekFromObject = new DateTime($date);
                $weekFromObject->modify("-" . (7 - $workingDaysPerWeek) . " days");
                $dateFrom = $weekFromObject->format("Y-m-d");
            } //if date is weekend return empty array
            elseif (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                return $receiptList;
            }

            $stmt = GetStatement();
            $where = array();
            $where[] = "employee_id=" . intval($employeeID);
            $where[] = "group_id=" . intval(ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD));
            $where[] = "status IN('approved', 'approve_proposed')";
            $where[] = "archive='N'";
            if ($dateFrom) {
                $where[] = "DATE(document_date) >= " . Connection::GetSQLDate($dateFrom);
                $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($date);
            } else {
                $where[] = "DATE(document_date) = " . Connection::GetSQLDate($date);
            }

            $query = "SELECT receipt_id, real_amount_approved FROM receipt WHERE " . implode(" AND ", $where);
            $receiptIDs = $stmt->FetchList($query);

            $receipt = new Receipt("receipt");
            foreach ($receiptIDs as $receiptID) {
                $receipt->LoadForApi($receiptID["receipt_id"]);
                $receiptList[] = $receipt->GetProperties();
            }
        } else {
            $weeklyShoppingReceiptMap = $this->GetWeeklyShoppingDateToUnitMap(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD),
                false,
                array("approve_proposed", "approved")
            );
            if (isset($weeklyShoppingReceiptMap[date("Y-m-d", strtotime($date))])) {
                foreach ($weeklyShoppingReceiptMap[date("Y-m-d", strtotime($date))]["receipt_id"] as $receiptID) {
                    $receipt = new Receipt("receipt");
                    if (!$receipt->LoadForApi($receiptID)) {
                        continue;
                    }

                    $receiptList[] = $receipt->GetProperties();
                }
            }
        }

        return $receiptList;
    }

    function GetAdvancedSecurityProductCode()
    {
        return PRODUCT__FOOD__ADVANCED_SECURITY;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "")
    {
        $properties = array(
            "units_per_month",
            "meal_value",
            "employer_meal_grant",
            "meal_value_employer_meal_grant",
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
            $receipt = new Receipt("receipt", array(
                "document_date" => $document_date,
                "employee_id" => $employeeID,
            ));

            $unitsPerMonth = $this->GetUnitCountLimitForMonth(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD),
                0,
                $document_date,
                false
            );
            $mealValue = $this->GetMealValue($receipt);
            $employerMealGrant = $this->GetEmployerMealGrant($receipt);
            $mealValueEmployerMealGrant = $mealValue + $employerMealGrant;
            $maxPerMonth = GetPriceFormat($unitsPerMonth * floatval($mealValueEmployerMealGrant));

            $values["units_per_month"] = GetPriceFormat($unitsPerMonth);
            $values["meal_value"] = GetPriceFormat($mealValue);
            $values["employer_meal_grant"] = GetPriceFormat($employerMealGrant);
            $values["meal_value_employer_meal_grant"] = GetPriceFormat($mealValueEmployerMealGrant);
            $values["amount_per_month"] = $maxPerMonth;
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    function GetContainer()
    {
        return CONTAINER__RECEIPT__FOOD;
    }

    function GetGenerationVoucherOptionCode()
    {
        return null;
    }

    public function GetUnitCount($employeeID = null, $dateFrom = null, $dateTo = null)
    {
        if ($employeeID == null || $dateTo == null) {
            return 0;
        }

        return $this->GetUnitCountLimitForMonth($employeeID, ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD), null, $dateTo, false);
    }

    public function GetFlexOptionFreeUnits($employeeID = null, $date = null)
    {
        if ($employeeID == null || $date == null) {
            return 0;
        }

        return Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, OPTION__FOOD__MAIN__FLEX_FREE_UNITS, $employeeID, $date);
    }

    public function GetFlexOptionUnitPrice($employeeID = null, $date = null)
    {
        if ($employeeID == null || $date == null) {
            return 0;
        }

        return Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, OPTION__FOOD__MAIN__FLEX_UNIT_PRICE, $employeeID, $date);
    }

    public function GetFlexOptionUnitPercentage($employeeID = null, $date = null)
    {
        if ($employeeID == null || $date == null) {
            return 0;
        }

        return Option::GetInheritableOptionValue(OPTION_LEVEL_EMPLOYEE, OPTION__FOOD__MAIN__FLEX_UNIT_PERCENTAGE, $employeeID, $date);
    }

    public function IsValidationFlatTaxRateActive(Receipt $receipt): bool
    {
        $contract = new Contract("product");
        return $contract->ContractExist(
            OPTION_LEVEL_EMPLOYEE,
            Product::GetProductIDByCode(PRODUCT__FOOD__LUMP_SUM_TAX_EXAMINATION),
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
    }
}
