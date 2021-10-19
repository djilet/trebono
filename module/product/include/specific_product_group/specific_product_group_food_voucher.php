<?php

class SpecificProductGroupFoodVoucher extends AbstractSpecificProductGroup
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

    public function GetAvailableForStatistics(
        $employeeID,
        $groupID,
        $date,
        $forYearlyStatistics = false
    ) {
        $dateTo = $forYearlyStatistics ? date("01-01-Y", strtotime($date)) : date("t-m-Y", strtotime($date));
        $dateFrom = date("01-m-Y", strtotime($date));
        $date = date_create($date);
        $voucherList = $this->GetVoucherListByEmployeeID(
            $employeeID,
            $date->format("m"),
            false,
            $date,
            false
        );
        foreach ($voucherList as $key => $voucher) {
            $voucherList[$key]["amount_approved"] = 0;
            $voucherList[$key]["amount_left"] = $voucherList[$key]["amount"];
        }
        $receiptList = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            $groupID
        );
        $voucherMap = $this->MapReceiptToVoucher($receiptList, $voucherList);
        $voucherAmount = 0;
        $voucherCount = 0;
        $voucherAmountBeginMonth = 0;
        $voucherCountBeginMonth = 0;

        $monthlyLimitUnits = Employee::GetEmployeeField($employeeID, "working_days_per_week") * 4 + 2;
        $usedUnits = $this->GetUsedUnitCountByMonth($employeeID, $date->format("Y-m-d"), 0);

        if ($voucherMap) {
            foreach ($voucherMap as $key => $voucher) {
                if (date_create($voucher["voucher_date"]) > $date) {
                    continue;
                }

                $voucherMap[$key]["amount_left_begin_month"] = $voucherMap[$key]["amount_left"];

                if (isset($voucher["receipt_list"])) {
                    foreach ($voucher["receipt_list"] as $receipt) {
                        if (strtotime($receipt["status_updated"]) >= strtotime($dateFrom)) {
                            $voucherMap[$key]["amount_left_begin_month"] += $receipt["amount"];
                            $voucherMap[$key]["amount_left_begin_month"] = round($voucherMap[$key]["amount_left_begin_month"], 5);
                        }

                        if (strtotime($receipt["status_updated"]) > strtotime($dateTo)) {
                            $voucherMap[$key]["amount_left"] += $receipt["amount"];
                            $voucherMap[$key]["amount_left"] = round($voucherMap[$key]["amount_left"], 5);
                        }
                    }
                    $voucherMap[$key]["used_date"] = date_create($voucher["used_date"]);
                }

                if (
                    !empty($voucherMap[$key]["used_date"]) &&
                    $voucherMap[$key]["used_date"]->format("Y-m-d") < $date->format("Y-m-01")
                ) {
                    $unitCount = intval($voucherMap[$key]["amount_left"] / $voucher["amount"]);
                    $unitCountBeginMonth = intval($voucherMap[$key]["amount_left_begin_month"] / $voucher["amount"]);
                } else {
                    $unitCount = $voucherMap[$key]["amount_left"] / $voucher["amount"];
                    $unitCountBeginMonth = $voucherMap[$key]["amount_left_begin_month"] / $voucher["amount"];
                }

                if (!$forYearlyStatistics) {
                    $monthUnits = $voucherCount + $usedUnits;
                    if ($monthUnits < $monthlyLimitUnits) {
                        $voucherAmount += $unitCount * $voucher["amount"];
                        $voucherCount += $unitCount;
                    } elseif ($monthUnits > $monthlyLimitUnits) {
                        $voucherAmount = $voucherAmount - (($monthUnits - $monthlyLimitUnits) * $voucher["amount"]);
                        $voucherCount = $monthlyLimitUnits - $usedUnits;
                    }

                    if ($voucherCountBeginMonth < $monthlyLimitUnits) {
                        $voucherAmountBeginMonth += $unitCountBeginMonth * $voucher["amount"];
                        $voucherCountBeginMonth += $unitCountBeginMonth;
                    } elseif ($voucherCountBeginMonth > $monthlyLimitUnits) {
                        $voucherAmountBeginMonth = $voucherAmountBeginMonth -
                            (($voucherAmountBeginMonth - $monthlyLimitUnits) * $voucher["amount"]);
                        $voucherCountBeginMonth = $monthlyLimitUnits;
                    }
                } else {
                    $voucherAmount += $unitCount * $voucher["amount"];
                    $voucherCount += $unitCount;
                }
            }
        }

        return [
            "amount" => $voucherAmount,
            "count" => number_format($voucherCount, 2, ".", ""),
            "amount_begin_month" => $voucherAmountBeginMonth,
            "count_begin_month" => number_format($voucherCountBeginMonth, 2, ".", ""),
        ];
    }

    /**
     * Returns final unit count limit for month of passed date
     *
     * @param int $employeeID
     * @param int $groupID
     * @param int|null $exceptReceiptID
     * @param string $date
     * @param bool $useTransfer
     * @param string $dateFrom
     *
     * @return array
     */
    public function GetUnitCountLimitForMonth(
        $employeeID,
        $groupID,
        $exceptReceiptID,
        $date,
        $useTransfer,
        $dateFrom = null
    ) {
        $lastProductContract = new Contract("contract");
        $existActiveProductContract = false;
        $existNextPayrollForLastProductContract = false;

        $monthlyLimit = Employee::GetEmployeeField($employeeID, "working_days_per_week") * 4 + 2;

        if (
            $lastProductContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN),
                false
            )
        ) {
            $endDateOfLastProductContract = $lastProductContract->GetProperty("end_date");

            if (
                $endDateOfLastProductContract == null
                || strtotime($endDateOfLastProductContract) > strtotime(GetCurrentDate())
            ) {
                $existActiveProductContract = true;
            } else {
                $monthNextPayroll = date("m", strtotime($endDateOfLastProductContract)) + 1;
                $monthNextPayroll = $monthNextPayroll < 10 ? "0" . $monthNextPayroll : $monthNextPayroll;
                $monthAndYearNextPayroll = date("Y", strtotime($endDateOfLastProductContract)) . $monthNextPayroll;

                $employee = new Employee("company");
                $employee->LoadByID($employeeID);

                $stmt = GetStatement();
                $query = "SELECT payroll_id FROM payroll 
                            WHERE company_unit_id=" . intval($employee->GetProperty("company_unit_id")) . " AND 
                            payroll_month=" . Connection::GetSQLString($monthAndYearNextPayroll);

                if (boolval($stmt->FetchRow($query))) {
                    $existNextPayrollForLastProductContract = true;
                }

                $deactivationReason = Option::GetCurrentValue(
                    OPTION_LEVEL_EMPLOYEE,
                    Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
                    $employee->GetIntProperty('employee_id')
                );
            }
        }

        if (!$existActiveProductContract && $existNextPayrollForLastProductContract && $deactivationReason === 'end') {
            return ["unit_amount" => 0, "unit_count" => 0];
        }

        $dateForTransferred = $date;
        if ($dateFrom == null) {
            $dateFrom = date("1-m-Y", strtotime($date));
            $isYearlyStatistics = false;
        } else {
            $dateForTransferred = $dateFrom;
            $isYearlyStatistics = true;
        }

        $voucherList = $this->GetVoucherListByEmployeeID(
            $employeeID,
            date("m", strtotime($date)),
            $dateFrom,
            date("t-m-Y", strtotime($date)),
            false
        );

        $optionReceipt = new Receipt("product");
        $optionReceipt->SetProperty("employee_id", $employeeID);

        $voucherAmountPerMonth = 0;
        $voucherUnitsPerMonth = 0;
        foreach ($voucherList as $voucher) {
            if (!$isYearlyStatistics && $voucherUnitsPerMonth >= $monthlyLimit) {
                break;
            }

            if ($voucher["file"] === null) {
                continue;
            }
            $optionReceipt->SetProperty("document_date", $voucher["created"]);
            $voucherAmountPerMonth += $voucher["amount"];
            $voucherUnitsPerMonth++;
        }

        $user = new User();
        $user->LoadBySession();
        if (
            $useTransfer
            && ($voucherUnitsPerMonth < $monthlyLimit || $isYearlyStatistics)
            && (!$user->Validate(["employee" => null])
                || $user->Validate(["root"])
                || $user->Validate(["receipt"])
            )
        ) {
            if ($isYearlyStatistics) {
                $transferredUnitArray = $this->GetTransferredUnitCountForMonth(
                    $employeeID,
                    $dateForTransferred,
                    $exceptReceiptID
                );
            } else {
                $transferredUnitArray = $this->GetTransferredUnitCountForMonth(
                    $employeeID,
                    $dateForTransferred,
                    $exceptReceiptID,
                    $voucherUnitsPerMonth
                );
            }

            $voucherAmountPerMonth += $transferredUnitArray["unit_amount"];
            $voucherUnitsPerMonth += $transferredUnitArray["unit_count"];
        }

        return ["unit_amount" => $voucherAmountPerMonth, "unit_count" => $voucherUnitsPerMonth];
    }

    /**
     * Return count of units transferred to passed month
     *
     * @param int $employeeID
     * @param string $date
     * @param int $exceptReceiptID
     * @param ?int $notTransferred amount of not transferred units for montly limit check
     *
     * @return array
     */
    private function GetTransferredUnitCountForMonth($employeeID, $date, $exceptReceiptID, $notTransferred = null)
    {
        //calculate count of transferred units
        $lastProductContract = new Contract("contract");
        $existActiveProductContract = false;
        $existNextPayrollForLastProductContract = false;

        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        $monthlyLimit = $workingDaysPerWeekResult * 4 + 2;

        if (
            $lastProductContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $employeeID,
                Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN),
                false
            )
        ) {
            $endDateOfLastProductContract = $lastProductContract->GetProperty("end_date");

            if (
                $endDateOfLastProductContract == null
                || strtotime($endDateOfLastProductContract) > strtotime(GetCurrentDate())
            ) {
                $existActiveProductContract = true;
            } else {
                $monthNextPayroll = date("m", strtotime($endDateOfLastProductContract)) + 1;
                $monthNextPayroll = $monthNextPayroll < 10 ? "0" . $monthNextPayroll : $monthNextPayroll;
                $monthAndYearNextPayroll = date("Y", strtotime($endDateOfLastProductContract)) . $monthNextPayroll;

                $employee = new Employee("company");
                $employee->LoadByID($employeeID);

                $stmt = GetStatement();
                $query = "SELECT payroll_id FROM payroll 
                            WHERE company_unit_id=" . intval($employee->GetProperty("company_unit_id")) . "
                            AND payroll_month=" . Connection::GetSQLString($monthAndYearNextPayroll);

                if (boolval($stmt->FetchRow($query))) {
                    $existNextPayrollForLastProductContract = true;
                }

                $deactivationReason = Option::GetCurrentValue(
                    OPTION_LEVEL_EMPLOYEE,
                    Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
                    $employee->GetIntProperty('employee_id')
                );
            }
        }

        if (!$existActiveProductContract && $existNextPayrollForLastProductContract && $deactivationReason === 'end') {
            return ["unit_amount" => 0, "unit_count" => 0];
        }


        $optionReceipt = new Receipt("product");
        $optionReceipt->SetProperty("document_date", $date);
        $optionReceipt->SetProperty("employee_id", $employeeID);

        $voucherListArray = $this->GetVoucherListByEmployeeID(
            $employeeID,
            date("m", strtotime($date)),
            false,
            false,
            false
        );

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["used_date"] = null;
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)
        );
        $receiptListIDs = array_column($receiptListArray, "receipt_id");

        if (count($receiptListIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $query = "SELECT MAX(created) as created, receipt_id
                    FROM receipt_history
                    WHERE property_name = 'status'
                      AND value = 'approve_proposed'
                      AND receipt_id in (" . implode(", ", $receiptListIDs) . ")
                    GROUP BY receipt_id";
            $historyList = $stmt->FetchList($query);

            foreach ($historyList as $history) {
                $key = array_search($history["receipt_id"], $receiptListIDs);
                if ($key === false) {
                    continue;
                }

                $receiptListArray[$key]["status_updated"] = $history["created"];
            }

            foreach ($receiptListArray as $key => $receipt) {
                $weekDay = date("w", strtotime($receipt["document_date"]));
                if (
                    ($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult
                    && $workingDaysPerWeekResult < 7
                ) {
                    $receiptListArray[$key]["document_date"]
                        = date("Y-m-d", strtotime("next Monday", strtotime($receipt["document_date"])));
                }
            }
        }

        $unitsForTransfer = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_WEEK_TRANSFER,
            $employeeID,
            $date
        );
        $transferredUnitCount = 0;
        $transferredUnitAmount = 0;
        $monthStart = date("Y-m-1", strtotime($date));
        $monthEnd = date("Y-m-t", strtotime($date));
        $voucherMap = $this->MapReceiptToVoucher($receiptListArray, $voucherListArray);
        if ($voucherMap) {
            foreach ($voucherMap as $key => $voucher) {
                if (
                    $notTransferred !== null
                    && $transferredUnitCount + $notTransferred >= $monthlyLimit
                    || $transferredUnitCount == $unitsForTransfer
                ) {
                    break;
                }

                if (isset($voucher["receipt_list"])) {
                    foreach ($voucher["receipt_list"] as $receipt) {
                        if (
                            strtotime(date("Y-m-d", strtotime($receipt["status_updated"])))
                            < strtotime($monthStart)
                            || strtotime(date("Y-m-d", strtotime($receipt["document_date"])))
                            > strtotime($monthEnd)
                        ) {
                            continue;
                        }

                        $voucherMap[$key]["amount_left"] -= $receipt["amount"];
                    }
                }

                if (
                    strtotime($voucher["voucher_date"]) >= strtotime($monthStart)
                    || $voucher["file"] === null
                    || $voucherMap[$key]["amount_left"] <= 0
                ) {
                    continue;
                }

                $apiUnit = $voucherMap[$key]["amount"];
                $unitCount = intval($voucherMap[$key]["amount_left"] / $apiUnit);
                $transferredUnitCount += $unitCount;
                $transferredUnitAmount += $unitCount * $apiUnit;
            }
        }

        $transferredUnitCount = number_format($transferredUnitCount, 10, ".", "");

        return [
            "unit_count" => max([$transferredUnitCount, 0]),
            "unit_amount" => max([$transferredUnitAmount, 0]),
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::ValidateReceiptApprove()
     */
    public function ValidateReceiptApprove($receipt)
    {
        $errorObject = new LocalObject();

        /**
         * Check active base contract
         */
        $contract = new Contract("contract");
        if (
            !$contract->ContractExist(
                OPTION_LEVEL_EMPLOYEE,
                Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                $receipt->GetProperty("employee_id"),
                GetCurrentDate()
            )
        ) {
            $deactivationReason = Option::GetCurrentValue(
                OPTION_LEVEL_EMPLOYEE,
                Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
                $receipt->GetProperty("employee_id")
            );
            if ($deactivationReason == "end") {
                $errorObject->AddError("no-valid-employment-contract", "receipt");
                $receipt->AppendErrorsFromObject($errorObject);

                return false;
            }
        }

        $voucherListArray = $this->GetVoucherListByEmployeeID(
            $receipt->GetProperty("employee_id"),
            date("m", strtotime($receipt->GetProperty("document_date"))),
            false,
            false,
            false
        );

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["used_date"] = null;
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $receipt->GetProperty("employee_id"),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
            $receipt
        );

        $receipt->SetProperty("document_date", date("Y-m-d H:i:s", strtotime($receipt->GetProperty("document_date"))));
        $key = array_search($receipt->GetProperty("receipt_id"), array_column($receiptListArray, "receipt_id"));
        if ($key !== false) {
            $receiptListArray[$key] = $receipt->GetProperties();
        } else {
            $receiptListArray[] = $receipt->GetProperties();
        }
        array_multisort(array_column($receiptListArray, "document_date"), $receiptListArray);

        $available = $this->GetAvailableAmountForReceipt(
            $receiptListArray,
            $voucherListArray,
            $receipt->GetProperty("receipt_id")
        );
        $availableArray = $this->GetAvailableAmountForReceipt(
            $receiptListArray,
            $voucherListArray,
            $receipt->GetProperty("receipt_id"),
            true
        );

        if (empty($available)) {
            //if there are no vouchers, show error
            $errorObject->AddError("receipt-voucher-not-found", "receipt");
            $receipt->AppendErrorsFromObject($errorObject);

            return false;
        }

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

        //if receipt date is weekend set receipt date = next monday
        if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
            $receiptDate = date("Y-m-d", strtotime("next Monday", strtotime($receiptDate)));
        }

        $receiptDateUsedUnitCount = $this->GetUsedUnitCount(
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("group_id"),
            $receipt->GetProperty("receipt_id"),
            $receiptDate,
            $receiptDate
        );
        if ($receiptDateUsedUnitCount >= 1) {
            $errorObject->AddError("receipt-daily-unit-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue("receipt_autodeny_day_limit"));
        }

        /**
         * Weekly limit
         */

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
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue("receipt_autodeny_week_limit"));
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
        $finalMonthlyLimit = $finalMonthlyLimit["unit_count"];

        if ($receiptMonthUsedUnitCount >= $finalMonthlyLimit) {
            $errorObject->AddError("receipt-monthly-unit-limit-exceed", "receipt");
            $receipt->SetProperty("proposed_denial_reason", Config::GetConfigValue("receipt_autodeny_month_limit"));
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

        $realAmountApprovedLimits = [];
        $realAmountApprovedLimits[] = $unit * ($finalMonthlyLimit - $receiptMonthUsedUnitCount);
        $realAmountApprovedLimits[] = $amountApproved;

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
        $closestVoucher = $this->GetClosestVoucher($availableArray, $receipt);
        if ($closestVoucher["amount"] == 0) {
            $receipt->AddError("receipt-voucher-not-found", "receipt");
        }
        $realAmountApprovedLimits[] = $unit;
        $realAmountApprovedLimits[] = $available;
        $realAmountApprovedLimits[] = $closestVoucher["amount"];

        $realAmountApproved = min($realAmountApprovedLimits);
        $realAmountApproved = max([$realAmountApproved, 0]);

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

        if ($this->CheckNeedToUpdateLinks($receipt)) {
            Receipt::RemoveReceiptVoucherLinks($receipt->GetProperty("receipt_id"));
            Voucher::SetVoucherReceipt($closestVoucher["voucher_id"], $receipt->GetProperty("receipt_id"));
            Voucher::SetVoucherReceiptAmount(
                $closestVoucher["voucher_id"],
                $receipt->GetProperty("receipt_id"),
                $receipt->GetProperty("real_amount_approved")
            );
        }

        return true;
    }

    private function GetClosestVoucher($voucherList, $receipt)
    {
        $this->SortVoucherList(
            $voucherList,
            date("n", strtotime($receipt->GetProperty("document_date"))),
            "used_date"
        );

        foreach ($voucherList as $voucherKey => $voucher) {
            if (isset($voucher["used_date"]) && $voucher["used_date"] != null) {
                $voucherDate = date("Y-m-d", strtotime($voucher["used_date"]));
                $receiptDate = date("Y-m-d", strtotime($receipt->GetProperty("document_date")));

                $voucherWeekDay = date("w", strtotime($voucherDate));
                $voucherWeekDay = $voucherWeekDay == 0 ? 7 : $voucherWeekDay;

                $receiptWeekDay = date("w", strtotime($voucherDate));
                $receiptWeekDay = $receiptWeekDay == 0 ? 7 : $receiptWeekDay;

                $workingDaysPerWeek = intval(Employee::GetEmployeeField(
                    $receipt->GetProperty("employee_id"),
                    "working_days_per_week"
                ));

                $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
                $workingDaysPerWeekLimits[] = 5;

                $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

                //if date range ends with weekend - move end date range to last previous working date
                if ($voucherWeekDay > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $voucherDateToMonday = date("Y-m-d", strtotime("next Monday", strtotime($voucherDate)));
                    if (
                        ($receiptWeekDay <= $workingDaysPerWeekResult && strtotime($receiptDate) < strtotime($voucherDate))
                        || strtotime($receiptDate) > strtotime($voucherDateToMonday)
                    ) {
                        continue;
                    }
                } elseif (strtotime($voucherDate) != strtotime($receiptDate)) {
                    continue;
                }
            }

            return ["amount" => $voucher["amount"], "voucher_id" => $voucher["voucher_id"]];
        }

        return ["amount" => 0, "voucher_id" => 0];
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
            && ($receiptAfter->GetProperty("status") != $receiptBefore->GetProperty("status")
                || $receiptAfter->GetProperty("amount_approved") != $receiptBefore->GetProperty("amount_approved")
            )
        ) {
            $employeeId = $receiptAfter->GetProperty("employee_id");
            $date = $receiptAfter->GetProperty("document_date");

            //if receipt date is weekend set receipt date = next monday
            $weekDay = date("w", strtotime($date));
            $workingDaysPerWeek = intval(Employee::GetEmployeeField(
                $receiptAfter->GetProperty("employee_id"),
                "working_days_per_week"
            ));

            $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
            $workingDaysPerWeekLimits[] = 5;

            $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $date = date("Y-m-d", strtotime("next Monday", strtotime($date)));
            }
            $mealGrantMandatory = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
                $employeeId,
                $date
            );

            /*in case when employee has "employee meal grant mandatory",
            he can approve only >= 1 unit, see ValidateReceiptApprove*/
            if ($mealGrantMandatory != "Y") {
                $result = true;

                /*when weekly shopping is disabled, employee can fill daily unit
                by the multiple receipt - so we check the total approved unit count for receipt"s date*/
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

                if ($result == false) {
                    $pushReceipt = new Receipt("receipt");
                    $pushReceipt->LoadByID($receiptAfter->GetProperty("receipt_id"));

                    $receiptAfter->SendReceiptCommentApprovedLessThanUnit();
                    $pushReceipt->SendReceiptCommentPushNotification();
                }
            }
        }

        if (
            in_array($receiptAfter->GetProperty("status"), ["approve_proposed", "approved"])
            || ($receiptBefore->GetProperty("status") != "approve_proposed"
                && $receiptBefore->GetProperty("status") != "approved")
        ) {
            return;
        }

        Receipt::RemoveReceiptVoucherLinks($receiptAfter->GetProperty("receipt_id"));
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
    public function GetUsedUnitCount(
        $employeeID,
        $groupID,
        $exceptReceiptID,
        $dateFrom,
        $dateTo,
        $mode = "admin"
    ) {
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
        $origDateTo = $dateTo;

        $dateFrom = !is_array($dateFrom) ? [$dateFrom] : $dateFrom;
        $dateTo = !is_array($dateTo) ? [$dateTo] : $dateTo;

        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        $usedUnitCount = 0;
        $usedEuroAmount = 0;
        $untransferableUnitMap = [];

        $stmt = GetStatement();
        $where = [];
        $where[] = "receipt_id!=" . intval($exceptReceiptID);
        $where[] = "employee_id=" . intval($employeeID);
        $where[] = "group_id=" . intval($groupID);
        $where[] = "status IN(" . implode(", ", Connection::GetSQLArray($statusList)) . ")";
        $where[] = "archive='N'";

        foreach ($dateFrom as $date) {
            if ($date == null) {
                continue;
            }
            $weekDay = date("w", strtotime($date));
            //if date range starts from monday - move start date range to previous weekends start date
            if ($weekDay == 1 && $workingDaysPerWeekResult < 7) {
                $weekFromObject = new DateTime($date);
                $weekFromObject->modify("-" . (7 - $workingDaysPerWeekResult) . " days");
                $date = $weekFromObject->format("Y-m-d");
            } elseif (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                //if date range starts from weekend date - move start date range to current weekends start date
                $weekFromObject = new DateTime($date);
                $weekFromObject->modify("-" . ($weekDay == 0
                        ? 7 - $workingDaysPerWeekResult - 1
                        : $weekDay - $workingDaysPerWeekResult - 1) . " days");
                $date = $weekFromObject->format("Y-m-d");
            }
            $where[] = $mode == "statistics" ? "DATE(status_updated) >= " . Connection::GetSQLDate($date)
                : "DATE(document_date) >= " . Connection::GetSQLDate($date);
        }
        foreach ($dateTo as $date) {
            $weekDay = date("w", strtotime($date));
            //if date range ends with weekend - move end date range to last previous working date
            if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                $weekFromObject = new DateTime($date);
                $weekFromObject->modify("-" . ($weekDay == 0
                        ? 7 - $workingDaysPerWeekResult
                        : $weekDay - $workingDaysPerWeekResult) . " days");
                $date = $weekFromObject->format("Y-m-d");
            }
            $where[] = $mode == "statistics" ? "DATE(status_updated) <= " . Connection::GetSQLDate($date)
                : "DATE(document_date) <= " . Connection::GetSQLDate($date);
        }

        $query = "SELECT receipt_id FROM receipt
                    WHERE " . implode(" AND ", $where);
        $receiptList = $stmt->FetchList($query);
        if (count($receiptList) > 0) {
            $receiptIDs = array_column($receiptList, "receipt_id");
            foreach ($receiptIDs as $receiptID) {
                $receipt = new Receipt("receipt");
                $receipt->LoadByID($receiptID);

                $endReceipt = new Receipt("receipt");
                $endReceipt->LoadFromObject($receipt);
                $endReceipt->SetProperty("document_date", $origDateTo);

                $receiptTime = strtotime($receipt->GetProperty("document_date"));
                $receiptDate = date("Y-m-d", $receiptTime);
                $receiptWeekDay = date("w", $receiptTime);
                //move receipts from weekends to next monday
                if (
                    ($receiptWeekDay == 0 ? 7 : $receiptWeekDay) > $workingDaysPerWeekResult
                    && $workingDaysPerWeekResult < 7
                ) {
                    $receiptDate = date("Y-m-d", strtotime("next Monday", $receiptTime));
                }

                $voucherLinks = Receipt::GetReceiptVoucherList($endReceipt->GetProperty("receipt_id"));
                $unitCount = 0;
                if (!empty($voucherLinks)) {
                    foreach ($voucherLinks as $link) {
                        $usedEuroAmount += $link["amount"];
                        $unitCount += $link["amount"] / Voucher::GetPropertyByID("amount", $link["voucher_id"]);
                    }
                }
                $usedUnitCount += $unitCount;

                if (isset($untransferableUnitMap[$receiptDate])) {
                    $untransferableUnitMap[$receiptDate] += $unitCount;
                } else {
                    $untransferableUnitMap[$receiptDate] = $unitCount;
                }
            }
        }

        return [
            "approved_euro_amount" => $usedEuroAmount,
            "used_unit_count" => $usedUnitCount,
            "untransferable_unit_count" => array_sum(array_map("ceil", $untransferableUnitMap)),
        ];
    }

    /**
     * Returns count of units used on the week by passed date.
     * Includes used units transfer for employees with weekly purchase service.
     *
     * @param int $employeeID
     * @param string $date
     *
     * @return number
     */
    public function GetUsedUnitCountByWeek($employeeID, $date, $exceptReceiptID)
    {
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);

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

        return $this->GetUsedUnitCount($employeeID, $groupID, $exceptReceiptID, $weekFrom, $weekTo);
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
        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);

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

        $unit = $this->GetUnit($receipt);
        $approvedUnitCount = $unit > 0 ? $receipt->GetFloatProperty("real_amount_approved") / $unit : 0;

        $mealValueFilled = $amountApprovedRest > $mealValue ? $mealValue : $amountApprovedRest;
        $amountApprovedRest -= $mealValueFilled;

        $employerMealGrantFilled = $amountApprovedRest > $employerMealGrant
            ? $employerMealGrant
            : $amountApprovedRest;
        $amountApprovedRest -= $employerMealGrantFilled;

        $employeeMealGrantFilled = $amountApprovedRest > $employeeMealGrant
            ? $employeeMealGrant
            : $amountApprovedRest;

        $data = [
            "INFO_meal_value" => $mealValue,
            "INFO_meal_value_filled" => $mealValueFilled,
            "INFO_employer_meal_grant" => $employerMealGrant,
            "INFO_employer_meal_grant_filled" => $employerMealGrantFilled,
            "INFO_employee_meal_grant" => $employeeMealGrant,
            "INFO_employee_meal_grant_filled" => $employeeMealGrantFilled,

            "INFO_approved_unit_count" => in_array(
                $receipt->GetProperty("status"),
                ["approve_proposed", "approved"]
            ) ? round($approvedUnitCount, 2) : 0,
        ];
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

        return $receipt->GetFloatProperty("real_amount_approved")
            - $receiptClone->GetFloatProperty("INFO_employee_meal_grant_filled");
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
            OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE,
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
            OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );

        return $result ? floatval($result) : $result;
    }

    /**
     * Returns value of EmployeeMealGrant option for receipt"s owner and date
     * ONLY if EmployeeMealGrantMandatory option is enabled
     * @param Receipt $receipt
     *
     * @return string|NULL
     */
    public function GetEmployeeMealGrant($receipt)
    {
        $result = 0;

        $employeeMealGrantMandatory = Option::GetInheritableOptionValue(
            OPTION_LEVEL_EMPLOYEE,
            OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
            $receipt->GetProperty("employee_id"),
            $receipt->GetProperty("document_date")
        );
        if ($employeeMealGrantMandatory == "Y") {
            $result = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT,
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

        //get available voucher list before checking if receipt date needs to be moved
        $voucherList = $this->GetVoucherListByEmployeeID(
            $optionReceipt->GetProperty("employee_id"),
            date("m", strtotime($receipt->GetProperty("document_date"))),
            false,
            false,
            false
        );
        $voucherListArray = [];
        foreach ($voucherList as $key => $voucher) {
            if ($voucher["file"] === null) {
                continue;
            }

            $voucherListArray[] = $voucher;
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["used_date"] = null;
        }
        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $optionReceipt->GetProperty("employee_id"),
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
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
            array_column($receiptListArray, "status_updated"),
            array_column($receiptListArray, "receipt_id"),
            $receiptListArray
        );

        $availableArray = $this->GetAvailableAmountForReceipt(
            $receiptListArray,
            $voucherListArray,
            $optionReceipt->GetProperty("receipt_id"),
            true
        );

        //if receipt date is weekend set receipt date = next monday
        $weekDay = date("w", strtotime($optionReceipt->GetProperty("document_date")));
        $workingDaysPerWeek = intval(Employee::GetEmployeeField(
            $optionReceipt->GetProperty("employee_id"),
            "working_days_per_week"
        ));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
            $optionReceipt->SetProperty(
                "document_date",
                date("Y-m-d", strtotime("next Monday", strtotime($optionReceipt->GetProperty("document_date"))))
            );
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
        $finalMonthlyLimit = $finalMonthlyLimit["unit_count"];
        $currentMonthUnitsLeft = max([$finalMonthlyLimit - $monthUsedUnitCount, 0]);

        $monthTransferredUnitCount = $this->GetTransferredUnitCountForMonth(
            $optionReceipt->GetProperty("employee_id"),
            $optionReceipt->GetProperty("document_date"),
            0
        );
        $monthTransferredUnitCount = $monthTransferredUnitCount["unit_count"];

        $result = [
            [
                "value" => $workingDaysPerWeek,
                "title_translation" => GetTranslation("employee-working-days-per-week", "company"),
            ],
            [
                "value" => GetPriceFormat($this->GetUnit($optionReceipt)) . "€",
                "title_translation" => GetTranslation("option-food_voucher__main__unit", "product"),
            ],
            [
                "value" => GetPriceFormat($this->GetMealValue($optionReceipt)) . "€",
                "title_translation" => GetTranslation("option-" . OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE, "product"),
            ],
            [
                "value" => GetPriceFormat($this->GetEmployerMealGrant($optionReceipt)) . "€",
                "title_translation" => GetTranslation(
                    "option-" . OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT,
                    "product"
                ),
            ],
            [
                "value" => GetPriceFormat($this->GetEmployeeMealGrant($optionReceipt)) . "€",
                "title_translation" => GetTranslation(
                    "option-" . OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT,
                    "product"
                ),
            ],
            [
                "value" => GetPriceFormat(max([round($currentWeekUnitsLeft, 2), 0])),
                "title_translation" => GetTranslation("info-current-week-units-left", "product"),
            ],
            [
                "value" => GetPriceFormat(max([round($currentMonthUnitsLeft, 2), 0])),
                "title_translation" => GetTranslation("info-current-month-units-left", "product"),
            ],
            [
                "value" => GetPriceFormat(max([round($monthTransferredUnitCount, 2), 0])),
                "title_translation" => GetTranslation("info-current-month-transferred-units", "product"),
            ],
        ];

        $contract = new Contract("product");
        if (
            $contract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $optionReceipt->GetIntProperty("employee_id"),
                Product::GetProductIDByCode(PRODUCT__FOOD__WEEKLY_SHOPPING)
            )
        ) {
            if ($employee->GetIntProperty("working_days_per_week") > 0) {
                $result[] = [
                    "value" => intval($employee->GetProperty("working_days_per_week")) . " " . GetTranslation(
                        "info-working-days-per-week-suffix",
                        "product"
                    ),
                    "title_translation" => GetTranslation("info-weekly-purchase-active", "product"),
                ];
            } else {
                $result[] = [
                    "value" => GetTranslation("info-working-days-per-week-empty", "product"),
                    "title_translation" => GetTranslation("info-weekly-purchase-active", "product"),
                ];
            }
        }

        $available = 0;
        $availableStr = "";
        foreach ($availableArray as $key => $voucher) {
            if ($voucher["used_date"] != null) {
                $date = $voucher["used_date"];
                $weekDay = date("w", strtotime($date));
                if (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
                    $voucher["used_date"] = date("Y-m-d", strtotime("next Monday", strtotime($date)));
                }
            }

            if (
                $voucher["used_date"] != null
                && strtotime($voucher["used_date"]) != strtotime($optionReceipt->GetProperty("document_date"))
            ) {
                continue;
            }

            $available += $voucher["amount"];
            $availableStr = (!empty($availableStr) ? $availableStr : "")
                . $voucher["voucher_id"] . "(" . GetPriceFormat($voucher["amount"]) . "€); ";
        }
        $available = empty($available) ? "0€" : GetPriceFormat($available) . "€";

        $receiptMap = $this->GetVoucherMappedReceiptList($optionReceipt->GetProperty("employee_id"), $voucherListArray);

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

        $result[]["VoucherCategoryList"][] = [
            "reason" => $key,
            "available_amount" => $available,
            "available_vouchers" => $availableStr,
            "approved_vouchers" => $approvedStr,
        ];

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @see AbstractSpecificProductGroup::GetAddisonExportLineList()
     */
    public function GetAddisonExportLineList(
        $companyUnitID,
        $groupID,
        $payrollDate,
        $exportType
    ) {
        $payrollExport = Option::GetInheritableOptionValue(
            OPTION_LEVEL_COMPANY_UNIT,
            OPTION__FOOD_VOUCHER__MAIN__PAYROLL_EXPORT,
            $companyUnitID,
            $payrollDate
        );
        if ($payrollExport == "N" && $exportType == "datev") {
            return [];
        }

        $payrollMonth = date_create($payrollDate);
        $dateFrom = $payrollMonth->format("Y-m-01");
        $dateTo = $payrollMonth->format("Y-m-t");

        $lineList = [];
        $employeeMap = [];

        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("voucher_date_asc");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListForAddison($companyUnitID, $groupID, $payrollDate, $exportType);
        $toExport = $voucherList->GetItems();

        if (count($toExport) > 0) {
            foreach ($toExport as $key => $voucher) {
                $employeeID = $voucher["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employee = new Employee("company");
                    $employee->LoadByID($employeeID);

                    $employeeMap[$employeeID] = [
                        "employee_property_list" => $employee->GetProperties(),
                        "salary_option" => Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__FOOD_VOUCHER__MAIN__SALARY_OPTION,
                            $employeeID,
                            $voucher["voucher_date"]
                        ),
                    ];
                }

                $monthMapKey = strtotime($voucher["voucher_date"]) < strtotime($dateFrom)
                    ? date("Ym", strtotime($payrollDate))
                    : date("Ym", strtotime($voucher["voucher_date"]));
                if (!isset($employeeMap[$employeeID]["month_map"][$monthMapKey])) {
                    $employeeMap[$employeeID]["month_map"][$monthMapKey] = [
                        "tax_flat" => [
                            "title" => "A",
                            "acc_key" => "acc_meal_value_tax_flat",
                            "amount" => null,
                            "service_voucher_ids" => [],
                        ],
                        "tax_free" => [
                            "title" => "B",
                            "acc_key" => "acc_food_subsidy_tax_free",
                            "amount" => null,
                            "service_voucher_ids" => [],
                        ],
                        "negative_line" => [
                            "title" => "Negative",
                            "acc_key" => "acc_gross_salary",
                            "amount" => null,
                            "service_voucher_ids" => [],
                        ],
                    ];
                }

                $receiptObject = new Receipt("receipt");
                $receiptObject->SetProperty("employee_id", $employeeID);
                $receiptObject->SetProperty("document_date", $voucher["voucher_date"]);

                $mealValue = $this->GetMealValue($receiptObject);
                $employerMealGrant = $this->GetEmployerMealGrant($receiptObject);
                //$employeeMealGrant = $this->GetEmployeeMealGrant($receiptObject);

                $exportA = $mealValue;
                $exportB = $employerMealGrant;
                $exportC = 0;

                $taxFlat = $exportA - $exportC;
                $taxFree = $exportB + $exportC;

                $taxFlat = round($taxFlat, 10);
                $taxFree = round($taxFree, 10);

                $exportNegative = 0;

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["amount"] += $taxFlat;
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["amount"] += $taxFree;
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["amount"] -= $exportNegative;

                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_flat"]["service_voucher_ids"][]
                    = $voucher["voucher_id"];
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["tax_free"]["service_voucher_ids"][]
                    = $voucher["voucher_id"];
                $employeeMap[$employeeID]["month_map"][$monthMapKey]["negative_line"]["service_voucher_ids"][]
                    = $voucher["voucher_id"];
            }
        }

        foreach ($employeeMap as $employeeID => $employee) {
            foreach ($employee["month_map"] as $monthMapKey => $month) {
                foreach ($month as $lineKey => $line) {
                    if (count($line["service_voucher_ids"]) == 0) {
                        continue;
                    }

                    $line["service_voucher_ids"] = array_values(array_unique($line["service_voucher_ids"]));
                    $lineList[] = array_merge($line, [
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
                        "month_key" => $monthMapKey,
                    ]);
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

    public function GetMainProductCode()
    {
        return PRODUCT__FOOD_VOUCHER__MAIN;
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

        $unitDateList = [];

        for ($i = strtotime($dateFrom); $i <= strtotime($dateTo); $i = strtotime("+1 day", $i)) {
            $unitAdmin = $this->GetUnit(new Receipt(
                "receipt",
                ["document_date" => date("d-m-Y", $i), "employee_id" => $employeeID]
            ));
            $unitApi = $this->GetApiUnit(new Receipt(
                "receipt",
                ["document_date" => date("d-m-Y", $i), "employee_id" => $employeeID]
            ));

            $receiptDate = date("Y-m-d", $i);
            $receiptDateApprovedAmountAdmin = $this->GetUsed(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                false,
                $receiptDate,
                $receiptDate,
                "admin"
            )["approved_euro_amount"];
            $receiptDateApprovedAmountApi = $this->GetUsed(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                false,
                $receiptDate,
                $receiptDate,
                "api"
            )["approved_euro_amount"];

            $unitDateList[] = [
                "date" => date("Y-m-d", $i),
                "unit" => number_format($unitAdmin, 2, ".", ","),
                "used" => number_format($receiptDateApprovedAmountAdmin, 2, ".", ","),
                "unit_api" => number_format($unitApi, 2, ".", ","),
                "used_api" => number_format($receiptDateApprovedAmountApi, 2, ".", ","),
            ];
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
        $receiptList = [];
        $workingDaysPerWeek = intval(Employee::GetEmployeeField($employeeID, "working_days_per_week"));

        $workingDaysPerWeekLimits[] = $workingDaysPerWeek;
        $workingDaysPerWeekLimits[] = 5;

        $workingDaysPerWeekResult = max($workingDaysPerWeekLimits);

        $weekDay = date("w", strtotime($date));

        $dateFrom = false;
        //if date is monday add dates from previous weekends
        if ($weekDay == 1 && $workingDaysPerWeek < 7) {
            $weekFromObject = new DateTime($date);
            $weekFromObject->modify("-" . (7 - $workingDaysPerWeek) . " days");
            $dateFrom = $weekFromObject->format("Y-m-d");
        } elseif (($weekDay == 0 ? 7 : $weekDay) > $workingDaysPerWeekResult && $workingDaysPerWeekResult < 7) {
            //if date is weekend return empty array
            return $receiptList;
        }

        $stmt = GetStatement();
        $where = [];
        $where[] = "employee_id=" . intval($employeeID);
        $where[] = "group_id=" . intval(ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER));
        $where[] = "status IN('approved', 'approve_proposed')";
        $where[] = "archive='N'";
        if ($dateFrom) {
            $where[] = "DATE(document_date) >= " . Connection::GetSQLDate($dateFrom);
            $where[] = "DATE(document_date) <= " . Connection::GetSQLDate($date);
        } else {
            $where[] = "DATE(document_date) = " . Connection::GetSQLDate($date);
        }

        $query = "SELECT receipt_id, real_amount_approved
                    FROM receipt WHERE " . implode(" AND ", $where);
        $receiptIDs = $stmt->FetchList($query);

        $receipt = new Receipt("receipt");
        foreach ($receiptIDs as $receiptID) {
            $receipt->LoadForApi($receiptID["receipt_id"]);
            $receiptList[] = $receipt->GetProperties();
        }

        return $receiptList;
    }

    public function GetAdvancedSecurityProductCode()
    {
        return null;
    }

    public function GetReplacementsList($employeeID = false, $document_date = "")
    {
        $properties = [
            "units_per_month",
            "meal_value",
            "employer_meal_grant",
            "meal_value_employer_meal_grant",
            "amount_per_month",
        ];

        $replacements = [];
        $values = [];
        foreach ($properties as $property) {
            $replacements[] = [
                "template" => "%" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, "product"),
            ];
        }

        if ($employeeID) {
            $receipt = new Receipt("receipt", [
                "document_date" => $document_date,
                "employee_id" => $employeeID,
            ]);

            $unitsPerMonth = $this->GetUnitCountLimitForMonth(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                0,
                $document_date,
                false
            );
            $unitsPerMonth = $unitsPerMonth["unit_count"];
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

        return ["ReplacementList" => $replacements, "ValueList" => $values];
    }

    public function GetContainer()
    {
        return CONTAINER__RECEIPT__FOOD_VOUCHER;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param int $employee_id employee_id
     */
    public function GetReceiptMappedVoucherList($employeeID, $voucherListArray = null)
    {
        if ($voucherListArray == null) {
            $voucherListArray = $this->GetVoucherListByEmployeeID($employeeID);
        }

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["empty"] = false;
            $voucherListArray[$key]["receipt_list"] = [];
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)
        );

        $voucherMap = $this->MapReceiptToVoucher($receiptListArray, $voucherListArray);

        array_multisort(array_column($voucherMap, "empty"), array_column($voucherMap, "voucher_date"), $voucherMap);

        return $voucherMap;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param array $receiptList array of receipts (need to have properties:
     * real_amount_approved, receipt_id, document_date and order by document_date asc)
     * @param array $voucherList array of vouchers (need to have properties:
     * amount, amount_approved, voucher_date, end_date and order by end_date asc)
     *
     * @return array $voucherList array of vouchers with amount = amount - amount_approved,
     * amount_approved = amount aprroved with receipts,
     * receipt_list = array of receipts with approved amounts
     */
    private function MapReceiptToVoucher($receiptList, $voucherList, $returnReceiptMap = false)
    {
        $voucherList = VoucherList::GetLinksForVoucherList($voucherList);

        foreach ($receiptList as $receiptKey => &$receipt) {
            foreach ($voucherList as $voucherKey => &$voucher) {
                if (!isset($voucher["receipt_ids"])) {
                    $voucher["receipt_ids"] = array_column($voucher["link_list"], "receipt_id");
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
                $voucher["receipt_list"][] = [
                    "legal_receipt_id" => $receipt["legal_receipt_id"],
                    "receipt_id" => $receipt["receipt_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"],
                    "document_date" => $receipt["document_date"],
                    "status" => $receipt["status"],
                    "status_updated" => $receipt["status_updated"],
                    "creditor_export_id" => $receipt["creditor_export_id"],
                ];
                $voucher["amount_approved"] += $link["amount"];
                $voucher["amount_left"] = bcsub($voucher["amount_left"], $link["amount"], 2);
                $voucher["used_date"] = $receipt["document_date"];
                $receipt["real_amount_approved"] = bcsub($receipt["real_amount_approved"], $link["amount"], 2);
                $receipt["voucher_list_array"][] = [
                    "voucher_id" => $voucher["voucher_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"],
                ];
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
     * Gets amount approved for receipts and mapped to vouchers
     * Before found current receipt map receipt to vouchers, than map receipt to voucher in revert direction
     * Than sum free amount for current receipt document_date
     *
     * @param array $receiptList array of receipts (need to have properties:
     * real_amount_approved, receipt_id, document_date and order by document_date asc)
     * @param array $voucherList array of vouchers (need to have properties:
     * amount, amount_approved, voucher_date, end_date and order by end_date asc)
     * @param int $receiptID current receipt_id
     *
     * @return float|array|false $availableAmount = free amount for current receipt document_date or false
     */
    private function GetAvailableAmountForReceipt(
        $receiptList,
        $voucherList,
        $receiptID,
        $returnArray = false
    ) {
        $voucherList = VoucherList::GetLinksForVoucherList($voucherList);

        $availableAmount = [];
        $availableAmountArray = [];

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

                            $availableAmount = (!empty($availableAmount)
                                    ? $availableAmount
                                    : 0) + $voucher["amount_left"];
                            $availableAmountArray[] = [
                                "voucher_id" => $voucher["voucher_id"],
                                "full_amount" => $voucher["amount"],
                                "amount" => $voucher["amount_left"],
                                "reason" => $voucher["reason"],
                                "used_date" => $voucher["used_date"],
                                "status_of_used_date" => $voucher["status_of_used_date"],
                                "created" => $voucher["created"],
                                "voucher_date" => $voucher["voucher_date"],
                            ];
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
                        $voucherList[$voucherKey]["receipt_list"][] = [
                            "receipt_id" => $receipt["receipt_id"],
                            "amount" => $link["amount"],
                            "reason" => $voucher["reason"],
                            "status_updated" => $receipt["status_updated"],
                        ];
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
                        $voucherList[$voucherKey]["used_date"] = $receipt["document_date"];
                        $voucherList[$voucherKey]["status_of_used_date"] = $receipt["status_updated"];
                    }
                }

                return false;
            }

            foreach ($voucherList as $voucherKey => $voucher) {
                $voucher["receipt_ids"] = array_column($voucher["link_list"], "receipt_id");

                if (
                    $receipt["real_amount_approved"] <= 0
                    && !in_array($receipt["receipt_id"], $voucher["receipt_ids"])
                ) {
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
                $voucherList[$voucherKey]["receipt_list"][] = [
                    "receipt_id" => $receipt["receipt_id"],
                    "amount" => $link["amount"],
                    "reason" => $voucher["reason"],
                ];
                $voucherList[$voucherKey]["amount_left"] = bcsub(
                    $voucherList[$voucherKey]["amount_left"],
                    $link["amount"],
                    2
                );
                $receipt["real_amount_approved"] = bcsub($receipt["real_amount_approved"], $link["amount"], 2);
                $voucherList[$voucherKey]["used_date"] = $receipt["document_date"];
                $voucherList[$voucherKey]["status_of_used_date"] = $receipt["status_updated"];
            }
        }

        return false;
    }

    /**
     * Maps receipts to vouchers
     *
     * @param int $employee_id employee_id
     */
    public function GetVoucherMappedReceiptList($employeeID, $voucherListArray = null)
    {
        if ($voucherListArray == null) {
            $voucherListArray = $this->GetVoucherListByEmployeeID($employeeID);
        }

        foreach ($voucherListArray as $key => $voucher) {
            $voucherListArray[$key]["amount_approved"] = 0;
            $voucherListArray[$key]["amount_left"] = $voucherListArray[$key]["amount"];
            $voucherListArray[$key]["used_date"] = null;
        }

        $receiptListArray = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)
        );
        array_multisort(
            array_column($receiptListArray, "status_updated"),
            array_column($receiptListArray, "receipt_id"),
            $receiptListArray
        );

        return $this->MapReceiptToVoucher($receiptListArray, $voucherListArray, true);
    }

    private function GetVoucherListByEmployeeID(
        $employeeID,
        $month = false,
        $dateFrom = false,
        $dateTo = false,
        $needsPreparation = true
    ) {
        $voucherList = new VoucherList("company");
        $voucherList->SetOrderBy("service_mapping");
        $voucherList->SetItemsOnPage(0);
        if ($dateFrom === false && $dateTo === false) {
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                false,
                false,
                true,
                false,
                null,
                null,
                false,
                false,
                $needsPreparation
            );
        } else {
            $voucherList->LoadVoucherListByEmployeeID(
                $employeeID,
                ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                false,
                false,
                true,
                false,
                null,
                null,
                $dateFrom,
                $dateTo,
                $needsPreparation
            );
        }

        $voucherList = $voucherList->_items;

        if ($month !== false) {
            $this->SortVoucherList($voucherList, $month);
        }

        return $voucherList;
    }

    private function SortVoucherList(
        &$voucherList,
        $month,
        $column = false
    ) {
        $voucherInMonth = [];
        $voucherOutsideMonth = [];

        foreach ($voucherList as $voucher) {
            if (date("n", strtotime($voucher["voucher_date"])) == $month) {
                $voucherInMonth[] = $voucher;
            } else {
                $voucherOutsideMonth[] = $voucher;
            }
        }

        if ($column !== false) {
            array_multisort(array_column($voucherInMonth, $column), SORT_DESC, $voucherInMonth);
            array_multisort(array_column($voucherOutsideMonth, $column), SORT_DESC, $voucherOutsideMonth);
        }

        $voucherList = array_merge($voucherInMonth, $voucherOutsideMonth);
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
        $receiptMap = ReceiptList::GetReceiptListForVoucherMap(
            $employeeID,
            ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)
        );

        $amountApproved = 0;
        foreach ($receiptMap as $receipt) {
            if ($receipt["real_amount_approved"] <= 0) {
                continue;
            }

            if ($dateFrom && $dateTo && strtotime($receipt["document_date"]) <= strtotime($dateTo) && strtotime($receipt["document_date"]) >= strtotime($dateFrom)) {
                $amountApproved += $receipt["real_amount_approved"];
            } elseif ($dateFrom && strtotime($receipt["document_date"]) >= strtotime($dateFrom) && !($dateFrom && $dateTo)) {
                $amountApproved += $receipt["real_amount_approved"];
            } elseif ($dateTo && strtotime($receipt["document_date"]) <= strtotime($dateTo) && !($dateFrom && $dateTo)) {
                $amountApproved += $receipt["real_amount_approved"];
            }
        }

        return $amountApproved;
    }

    /** Returns mapped statistics about vouchers that already exist and are expected to be generated
     *
     * @param $employeeID
     * @param $groupID
     * @param Voucher|null $newVoucher new voucher to check this voucher"s amount validity
     *
     * @return array
     */
    public function MapVoucherListToMonth($employeeID, $groupID, ?Voucher $voucher = null)
    {
        $excludeVoucherID = null;

        $voucherList = new VoucherList("company");
        $voucherList->SetItemsOnPage(0);
        $voucherList->LoadVoucherListByEmployeeID($employeeID, $groupID, false, false, true, true);

        if ($voucher != null) {
            $excludeVoucherID = $voucher->GetProperty("voucher_id");
            $newVoucher = clone $voucher;
            $newVoucher->SetProperty("voucher_id", null);

            $isVoucherCron = $newVoucher->GetProperty("IsVoucherCron");

            if (
                $newVoucher->GetProperty("recurring") == "Y"
                && $newVoucher->GetProperty("recurring_end_date") != null
            ) {
                $newVoucherEndDate = $newVoucher->GetProperty("recurring_end_date");
            } elseif ($newVoucher->GetProperty("recurring") == "Y") {
                $newVoucherEndDate = date("31.12.Y", strtotime($newVoucher->GetProperty("voucher_date") . " + 1 year"));
            } else {
                $newVoucherEndDate = date("t.m.Y", strtotime($newVoucher->GetProperty("voucher_date")));
            }

            for ($i = 0; $i < $newVoucher->GetProperty("count"); $i++) {
                $voucherList->AppendItem($newVoucher->GetProperties());
            }

            $newVoucher->SetProperty("amount", $newVoucher->GetProperty("amount"));
            $endDate = $newVoucherEndDate;
        } else {
            $isVoucherCron = false;
            $newVoucherEndDate = $newVoucher = null;
            $endDate = date("Y-12-31", strtotime("+ 1 year"));
        }

        //if it"s not monthly food voucher cron, we need to include monthly generated vouchers into mapping
        if ($newVoucher !== null || !$isVoucherCron) {
            $contract = new Contract("product");
            //not including current month because voucher either should be generated already
            $date = date("Y-m-01", strtotime(" +1 month"));
            $countFromCron = Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH,
                $employeeID,
                $date
            );
            do {
                //if auto generation is off, we will not generate new voucher
                $autoGeneration = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $this->GetGenerationVoucherOptionCode(),
                    $employeeID,
                    $date
                );
                if ($autoGeneration !== "Y") {
                    $date = date("Y-m-01", strtotime($date . " +1 month"));
                    continue;
                }
                //if contract ended, we will not generate new voucher
                $contractExists = $contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode(PRODUCT__FOOD_VOUCHER__MAIN),
                    $employeeID,
                    $date
                );
                if ($contractExists) {
                    $unit = $this->GetApiUnit(new Receipt(
                        "receipt",
                        [
                            "document_date" => $date,
                            "employee_id" => $employeeID,
                        ]
                    ));

                    for ($i = 0; $i < $countFromCron; $i++) {
                        $voucherList->AppendItem([
                                "voucher_id" => "monthly_generated",
                                "employee_id" => $employeeID,
                                "group_id" => ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                                "amount" => $unit,
                                "voucher_date" => date("Y-m-01", strtotime($date)),
                                "reason" => "Essensmarken",
                            ]);
                    }
                }
                $date = date("Y-m-01", strtotime($date . " +1 month"));
            } while (strtotime($date) < strtotime($endDate));
        }

        //forming the map itself
        $voucherMap = [];
        foreach ($voucherList->_items as $voucher) {
            if ($excludeVoucherID != null && $voucher["voucher_id"] == $excludeVoucherID) {
                continue;
            }

            if (!isset($voucher["recurring"])) {
                $voucher["recurring"] = "N";
            }
            $date = $voucher["voucher_date"];

            if ($voucher["recurring"] == "Y" && $voucher["recurring_end_date"] != null && !$isVoucherCron) {
                $voucherEndDate = $voucher["recurring_end_date"];
            } elseif ($voucher["recurring"] == "Y") {
                $voucherEndDate = date("31.12.Y", strtotime($date . " + 1 year"));
            } else {
                $voucherEndDate = date("t.m.Y", strtotime($date));
            }

            $endDate = $newVoucherEndDate != null
                && strtotime($newVoucherEndDate) <= strtotime($voucherEndDate)
                || $voucher["voucher_id"] == null
                ? $newVoucherEndDate
                : $voucherEndDate;

            do {
                $month = date("Y-m", strtotime($date));
                $voucherMap[$month]["voucher_ids"][] = $voucher["voucher_id"];

                if (isset($voucherMap[$month]["count"])) {
                    $voucherMap[$month]["count"]++;
                } else {
                    $voucherMap[$month]["count"] = 1;
                }

                if ($voucher["voucher_id"] !== null) {
                    if (isset($voucherMap[$month]["count_without_new"])) {
                        $voucherMap[$month]["count_without_new"]++;
                    } else {
                        $voucherMap[$month]["count_without_new"] = 1;
                    }
                }

                if ($voucher["recurring"] == "Y") {
                    switch ($voucher["recurring_frequency"]) {
                        case "yearly":
                            $date = date("d.m.Y", strtotime($date . " +1 year"));
                            break;
                        case "quarterly":
                            $date = date("d.m.Y", strtotime($date . " +3 month"));
                            break;
                        default:
                            $date = date("d.m.Y", strtotime($date . " +1 month"));
                    }
                } else {
                    $date = date("d.m.Y", strtotime($date . " +1 month"));
                }
            } while (strtotime($date) <= strtotime($endDate));
        }

        return $voucherMap;
    }

    public function GetGenerationVoucherOptionCode()
    {
        return OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION;
    }

    private function CheckNeedToUpdateLinks($receipt)
    {
        if ($receipt->GetProperty("Save") == 1 && $receipt->GetProperty("status") == "approve_proposed") {
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
