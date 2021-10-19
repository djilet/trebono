<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class VoucherList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array|mixed[] $data Array of items to be loaded instantly
     */
    public function VoucherList($module, $data = [])
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(
            ["created_asc" => "v.created ASC",
            "created_desc" => "v.created DESC",
            "voucher_date_asc" => "v.voucher_date ASC",
            "voucher_date_desc" => "v.voucher_dated DESC",
            "voucher_id_asc" => "v.voucher_id ASC",
            "voucher_id_desc" => "v.voucher_id DESC",
            "voucher_end_date_desc" => "v.end_date ASC",
            "export_voucher" => "v.created ASC, v.voucher_id ASC",
            "service_mapping" => "v.voucher_date ASC, v.voucher_id ASC"]
        );
        $this->SetOrderBy("voucher_id_asc");
        $this->SetItemsOnPage(10);
        $this->SetPageParam("VoucherPage");
    }

    /**
     * Loads voucher list for admin panel filtered by employee id
     *
     * @param int $employeeID - employee_id
     * @param string|bool $groupID - recurring can be false, N or Y
     * @param bool $recurring
     * @param bool $needToGenerate
     * @param bool $excludeArchive
     * @param bool $appendAmountLeft
     * @param ?string $endDate
     * @param ?string $endType
     * @param bool|string $monthDayFrom start date for selection
     * @param bool|string $monthDayTo end date for selection
     * @param bool $needsPreparation
     * @param ?string $languageCode
     */
    public function LoadVoucherListByEmployeeID(
        $employeeID,
        $groupID = false,
        $recurring = false,
        $needToGenerate = false,
        $excludeArchive = false,
        $appendAmountLeft = false,
        $endDate = null,
        $endType = null,
        $monthDayFrom = false,
        $monthDayTo = false,
        $needsPreparation = true,
        $languageCode = null
    ) {
        $where = array();
        $where[] = "v.employee_id=" . intval($employeeID);

        if ($groupID) {
            $where[] = "v.group_id=" . intval($groupID);
        }

        if ($recurring) {
            $where[] = "v.recurring=" . Connection::GetSQLString($recurring);
        }

        if ($needToGenerate) {
            $where[] = "(v.file IS NULL AND v.voucher_date<=" . Connection::GetSQLDate(GetCurrentDate()) . ")";
        }

        if ($excludeArchive) {
            $where[] = "v.archive!='Y'";
        }

        if ($endDate != null) {
            if ($endType == "common") {
                $where[] = "v.end_date=" . Connection::GetSQLDate($endDate);
            } elseif ($endType == "recurring") {
                $where[] = "v.recurring_end_date=" . Connection::GetSQLDate($endDate);
            }
        }

        if ($monthDayFrom && $monthDayTo) {
            $where[] = "(v.voucher_date<=" . Connection::GetSQLDate($monthDayTo) . " AND v.voucher_date>=" . Connection::GetSQLDate($monthDayFrom) . ")";
        }

        $query = "SELECT v.voucher_id, v.employee_id, v.group_id, v.amount, v.created, v.created_user_id, v.voucher_date, v.reason, v.recurring, v.archive, v.end_date, v.recurring_frequency, v.recurring_end_date, v.file, v.invoice_export_id FROM voucher AS v
                    " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);

        $reuseVoucherList = false;
        if ($monthDayFrom === false && $monthDayTo === false) {
            $reuseVoucherList = true;
        }

        if (!$needsPreparation) {
            return;
        }

        $this->PrepareContentBeforeShow($employeeID, $groupID, $appendAmountLeft, $reuseVoucherList, $languageCode);
    }

    public function LoadVoucherListForAddison($companyUnitID, $groupID, $payrollDate, $exportType)
    {
        $this->SetItemsOnPage(0);

        if (CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "last_month" || $exportType == "reset") {
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " +1 month"));
            $createdTo = date("Y-m-d 17:59:00", strtotime($payrollDate));
        } else {
            $createdTo = date("Y-m-d 17:59:00", strtotime($payrollDate));
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " +1 month"));
        }
        $monthBegin = date_create($payrollDate)->format("Y-m-01");

        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);

        if (count($employeeIDs) <= 0) {
            return;
        }

        $where = array();
        $where[] = "v.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        $where[] = "v.group_id=" . intval($groupID);
        $where[] = "DATE(v.created) <= " . Connection::GetSQLDateTime($createdTo);
        $where[] = "DATE(v.voucher_date) < " . Connection::GetSQLDate($monthBegin);
        $where[] = "v.archive!='Y'";
        $where[] = "v.file IS NOT NULL";
        if ($exportType == "pdf") {
            $where[] = "v.pdf_export='0'";
        } elseif ($exportType == "datev") {
            $where[] = "v.datev_export='0'";
        } elseif ($exportType == "reset") {
            $flag = date("Ym", strtotime($payrollDate . " -1 month"));
            $where[] = "(v.datev_export=" . Connection::GetSQLString($flag) . " OR v.pdf_export=" . Connection::GetSQLString($flag) . ")";
        }

        $query = "SELECT v.voucher_id, v.employee_id, v.group_id, v.amount, v.created, v.created_user_id,
                        v.voucher_date, v.reason, v.recurring, v.archive, v.end_date, v.recurring_frequency,
                        v.recurring_end_date, v.file FROM voucher AS v
                " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
    }

    /**
     * Loads voucher list for admin panel filtered by employee id
     *
     * @param int $companyUnitID - company unit id
     * @param bool|int $groupID
     * @param bool|int $notExported - return only not-exported vouchers
     * @param bool $onlyGenerated - return only generated vouchers
     * @param string $dateTo creation date to
     */
    public function LoadVoucherListByCompanyUnitID(
        $companyUnitID,
        $groupID = false,
        $notExported = false,
        $onlyGenerated = false,
        $dateTo = null
    ) {
        $this->SetItemsOnPage(0);
        $where = [];

        $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        if (empty($employeeList)) {
            return null;
        }

        $where[] = "v.employee_id IN (" . implode(", ", $employeeList) . ")";

        $where[] = "v.archive!='Y'";

        if ($groupID) {
            $where[] = "v.group_id=" . intval($groupID);
        }
        if ($notExported) {
            $where[] = "v.invoice_export_id IS NULL";
        }
        if ($onlyGenerated) {
            $where[] = "v.file IS NOT NULL";
        }
        if ($dateTo != null) {
            $where[] = "DATE(v.voucher_date) <= " . Connection::GetSQLDate($dateTo);
        }

        $query = "SELECT v.voucher_id, v.employee_id, v.group_id, v.amount, v.created, v.created_user_id,
        		v.voucher_date, v.reason, v.recurring, v.archive, v.end_date, v.recurring_frequency,
        		v.recurring_end_date, v.file
				FROM voucher AS v
				" . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query);
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     *
     * @param int $employeeID
     * @param int $groupID
     * @param bool $appendAmountLeft
     * @param bool $reuseVoucherList
     * @param ?string $languageCode
     */
    private function PrepareContentBeforeShow(
        $employeeID,
        $groupID,
        $appendAmountLeft,
        $reuseVoucherList,
        $languageCode = null
    ) {
        $mappedVoucherList = [];

        if ($appendAmountLeft) {
            $productGroup = SpecificProductGroupFactory::Create($groupID);
            if ($productGroup !== null) {
                if ($groupID && $reuseVoucherList) {
                    $mappedVoucherList = $productGroup->GetReceiptMappedVoucherList($employeeID, $this->GetItems());
                } elseif ($groupID) {
                    $mappedVoucherList = $productGroup->GetReceiptMappedVoucherList($employeeID);
                }
            }
        }

        $usernameList = [];
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            if (!isset($usernameList[$this->_items[$i]["created_user_id"]])) {
                $usernameList[$this->_items[$i]["created_user_id"]] = User::GetNameByID($this->_items[$i]["created_user_id"]);
            }
            $this->_items[$i]["created_user_name"] = $usernameList[$this->_items[$i]["created_user_id"]];

            if ($this->_items[$i]["recurring_frequency"] == "monthly") {
                $this->_items[$i]["recurring_frequency_title"] = GetTranslation(
                    "voucher-frequency-monthly",
                    "company",
                    [],
                    $languageCode
                );
            } elseif ($this->_items[$i]["recurring_frequency"] == "quarterly") {
                $this->_items[$i]["recurring_frequency_title"] = GetTranslation(
                    "voucher-frequency-quarterly",
                    "company",
                    [],
                    $languageCode
                );
            } elseif ($this->_items[$i]["recurring_frequency"] == "yearly") {
                $this->_items[$i]["recurring_frequency_title"] = GetTranslation(
                    "voucher-frequency-yearly",
                    "company",
                    [],
                    $languageCode
                );
            }

            if (!$appendAmountLeft) {
                continue;
            }

            $key = array_search($this->_items[$i]["voucher_id"], array_column($mappedVoucherList, "voucher_id"));
            if ($key !== false) {
                $this->_items[$i]["amount_left"] = $mappedVoucherList[$key]["amount_left"];
                $this->_items[$i]["amount_approved"] = $mappedVoucherList[$key]["amount_approved"];
            } else {
                $this->_items[$i]["amount_left"] = 0;
                $this->_items[$i]["amount_approved"] = 0;
            }
        }
    }

    public function AppendCanRemove(bool $canRemoveVouchers = true): void
    {
        foreach ($this->_items as &$item) {
            $item["can_remove"] = $canRemoveVouchers && $item["invoice_export_id"] === null;
        }
    }

    /**
     * NOT Removes vouchers from database by provided ids.
     * Just make the inactive.
     *
     * @param array|int[] $ids array of voucher_id's
     */
    public function Remove($ids): void
    {
        if (!is_array($ids) || empty($ids)) {
            return;
        }

        $stmt = GetStatement();

        $idsString = implode(", ", Connection::GetSQLArray($ids));

        $query = "SELECT COUNT(*) FROM voucher WHERE voucher_id IN ({$idsString}) AND invoice_export_id IS NOT NULL";
        if ($res = $stmt->Execute($query)) {
            if ($res->AllRows()[0]["count"] > 0) {
                $this->AddError("voucher-already-invoiced", "core");
                return;
            }
        } else {
            $this->AddError("sql-error-removing");
            return;
        }

        $query = "UPDATE voucher SET archive='Y' WHERE voucher_id IN ({$idsString})";

        if ($stmt->Execute($query)) {
            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage(
                    "object-disactivated",
                    $this->module,
                    ["Count" => $stmt->GetAffectedRows()]
                );
            }
        } else {
            $this->AddError("sql-error-removing");
        }
    }

    /**
     * Revert operation of Remove voucher by provided ids.
     *
     * @param array|int[] $ids array of voucher_id's
     */
    public function Activate(array $ids): void
    {
        if (!is_array($ids) || empty($ids)) {
            return;
        }

        $stmt = GetStatement();
        $ids = implode(", ", Connection::GetSQLArray($ids));
        $query = "SELECT * FROM voucher WHERE voucher_id IN ({$ids})";
        $vouchers = $stmt->FetchList($query);
        if (!$vouchers) {
            $this->AddError("sql-error-activating");
            return;
        }

        foreach ($vouchers as $voucherData) {
            $voucher = new Voucher("company", $voucherData);
            if (!$voucher->Validate()) {
                $this->LoadErrorsFromObject($voucher);
                return;
            }
        }

        $stmt = GetStatement();
        $query = "UPDATE voucher SET archive='N' WHERE voucher_id IN ({$ids})";
        if ($stmt->Execute($query)) {
            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage(
                    "object-activated",
                    $this->module,
                    ["Count" => $stmt->GetAffectedRows()]
                );
            }
        } else {
            $this->AddError("sql-error-activating");
        }
    }

    /**
     * Gets sum of vouchers for year by provided date for employee
     *
     * @param int $employeeID employee_id
     * @param string $date date for year compare with voucher_date
     * @param int $exceptVoucherID voucher_id which will be excepted from selection
     */
    public static function GetYearlyVoucherListAmount($employeeID, $groupID, $date, $exceptVoucherID)
    {
        $stmt = GetStatement();
        $query = "SELECT SUM(v.amount) FROM voucher AS v
                    WHERE EXTRACT(YEAR FROM v.voucher_date)=" . date("Y", strtotime($date)) . "
                        AND v.employee_id=" . intval($employeeID) . "
                        AND v.group_id=" . intval($groupID) . "
                        AND v.voucher_id!=" . intval($exceptVoucherID) . "
                        AND v.archive='N'";

        return $stmt->FetchField($query);
    }

    /**
     * Gets count of vouchers for year by provided date for employee
     *
     * @param int $employeeID employee_id
     * @param string $date date for year compare with voucher_date
     * @param int $exceptVoucherID voucher_id which will be excepted from selection
     */
    public static function GetYearlyVoucherListCount($employeeID, $groupID, $date, $exceptVoucherID)
    {
        $stmt = GetStatement();
        $query = "SELECT COUNT(v.voucher_id)
                    FROM voucher AS v
                    WHERE EXTRACT(YEAR FROM v.voucher_date)=" . date("Y", strtotime($date)) . "
                        AND v.employee_id=" . $employeeID . "
                        AND v.group_id=" . intval($groupID) . "
                        AND v.voucher_id!=" . intval($exceptVoucherID) . "
                        AND v.archive='N'";

        return intval($stmt->FetchField($query));
    }

    /**
     * Gets summary of vouchers amount
     *
     * @param int $employeeID employee_id
     * @param int $groupID group_id
     * @param bool|string $dateFrom start date for selection
     * @param bool|string $dateTo end date for selection
     * @param bool|string $monthDayFrom start date for selection
     * @param bool|string $monthDayTo end date for selection
     * @param bool $generated for selection
     * @param User $user
     */
    public static function GetAvailableVoucherAmount(
        $employeeID,
        $groupID,
        $dateFrom = false,
        $dateTo = false,
        $monthDayFrom = false,
        $monthDayTo = false,
        $generated = false,
        $user = null
    ) {
        $stmt = GetStatement();
        $where = [];
        $where[] = "v.employee_id=" . intval($employeeID);
        $where[] = "v.group_id=" . intval($groupID);
        $where[] = "v.archive!='Y'";

        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }

        if ($dateFrom && $dateTo) {
            $where[] = $user->Validate(["employee" => null]) && !$user->Validate(["root"])
                ? "(v.voucher_date<=" . Connection::GetSQLDate($dateTo) . "
                 AND v.voucher_date>=" . Connection::GetSQLDate($dateFrom) . "
                  AND v.end_date>=" . Connection::GetSQLDate($dateFrom) . ")"
                : "(v.voucher_date<=" . Connection::GetSQLDate($dateTo) . "
                AND v.end_date>=" . Connection::GetSQLDate($dateFrom) . ")";
        } elseif ($dateFrom) {
            $where[] = "v.end_date>=" . Connection::GetSQLDate($dateFrom);
        } elseif ($dateTo) {
            $where[] = "v.voucher_date<=" . Connection::GetSQLDate($dateTo);
        }
        if ($monthDayFrom && $monthDayTo) {
            $where[] = "(v.voucher_date<=" . Connection::GetSQLDate($monthDayTo) . "
             AND v.voucher_date>=" . Connection::GetSQLDate($monthDayFrom) . ")";
        }
        if ($generated) {
            $where[] = "v.file IS NOT NULL";
        }

        $query = "SELECT SUM(v.amount) as amount, COUNT(*) as count FROM voucher AS v
                    " . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $result = $stmt->FetchRow($query);
        if (isset($result["count"])) {
            $result["count"] = number_format($result["count"], 10, ".", "");
        }

        return $result;
    }

    public static function GetInvoicedAndDeactivatedAmount($date, $endDate, $employeeID)
    {
        $dontCheckVoucherDate = false;
        if ($date == null) {
            $date = GetCurrentDate();
            $dontCheckVoucherDate = true;
        }

        $expiredAmount = 0;
        $expiredVoucherList = array();
        $deactivationReason = Option::GetCurrentValue(
            OPTION_LEVEL_EMPLOYEE,
            Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
            $employeeID
        );

        if ($deactivationReason == "end") {
            $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__FOOD_VOUCHER);
            $voucherList = $specificProductGroup->GetReceiptMappedVoucherList($employeeID);

            foreach ($voucherList as $voucher) {
                if (
                    $voucher["invoice_export_id"] == null ||
                    (!$dontCheckVoucherDate && strtotime($voucher["voucher_date"]) < strtotime($date)) ||
                    ($endDate != null && strtotime($voucher["voucher_date"]) > strtotime($endDate)) ||
                    strtotime($voucher["end_date"]) < strtotime($date) || $voucher["amount_left"] <= 0
                ) {
                    continue;
                }

                $expiredAmount += $voucher["amount_left"];
                $expiredVoucherList[] = [
                    "voucher_id" => $voucher["voucher_id"],
                    "amount" => $voucher["amount_left"],
                ];
            }
        }

        return ["amount" => $expiredAmount, "list" => $expiredVoucherList];
    }

    /** Returns the sum of vouchers with set parameters
     *
     * @param bool $returnArray if true, return list of vouchers
     * @param int $groupID product group id
     * @param string $date start date
     * @param bool|string $endDate end date
     * @param string $type open - not paid open amount;
     * issued - issued this month
     * invoiced - invoiced amount
     * paid - exported;
     * to_be_paid - will be exported;
     * expired - expired vouchers
     * @param int|null $employeeID
     *
     * @return float|array|null
     */
    public static function GetVoucherDashboardAmount(
        $returnArray,
        $groupID,
        $date,
        $endDate = false,
        $type = "paid",
        $employeeID = null
    ) {
        $stmt = GetStatement();
        $where = [];
        $where[] = "v.group_id = " . Connection::GetSQLString($groupID);
        if ($type != "expired") {
            $where[] = "v.archive='N'";
        }

        if ($endDate === false) {
            if ($type == "invoiced") {
                $where[] = "i.created <= " . Connection::GetSQLString(date("Y-m-t 23:59:59", strtotime($date)));
                $where[] = "i.created >= " . Connection::GetSQLString(date("Y-m-1", strtotime($date)));
                //$where[] = "v.file IS NOT NULL";
            } elseif ($type == "issued") {
                $where[] = "v.created <= " . Connection::GetSQLString(date("Y-m-t 23:59:59", strtotime($date)));
                $where[] = "v.created >= " . Connection::GetSQLString(date("Y-m-1", strtotime($date)));
                $where[] = "v.file IS NOT NULL";
            } else {
                $where[] = "v.voucher_date <= " . Connection::GetSQLString(date("Y-m-t", strtotime($date)));
                $where[] = "v.voucher_date >= " . Connection::GetSQLString(date("Y-m-1", strtotime($date)));
            }
        } else {
            if ($date != null) {
                $where[] = "v.voucher_date >= " . Connection::GetSQLString(date("Y-m-d", strtotime($date)));
            }

            if ($endDate != null) {
                $where[] = "v.voucher_date <= " . Connection::GetSQLString(date("Y-m-d", strtotime($endDate)));
            }
        }

        if ($type == "open") {
            if ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
                $deactivationReason = Option::GetCurrentValue(
                    OPTION_LEVEL_EMPLOYEE,
                    Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
                    $employeeID
                );
                if ($deactivationReason == "end") {
                    if ($returnArray) {
                        return ["amount" => 0, "list" => []];
                    }

                    return 0;
                }
            }

            if ($date == null && $endDate == null) {
                $where[] = "v.end_date >= " . Connection::GetSQLString(date("Y-m-d"));
            }

            if ($date != null) {
                $where[] = "v.end_date >= " . Connection::GetSQLString(date("Y-m-d", strtotime($date)));
            }

            if ($endDate != false && $endDate != null) {
                $where[] = "v.end_date >= " . Connection::GetSQLString(date("Y-m-d", strtotime($endDate)));
            }
        } elseif ($type == "expired") {
            if ($date == null && $endDate == null) {
                $where[] = "v.end_date < " . Connection::GetSQLString(date("Y-m-d"));
            }

            if ($date != null) {
                $where[] = "v.end_date < " . Connection::GetSQLString(date("Y-m-d", strtotime($date)));
            }

            if ($endDate != false && $endDate != null) {
                $where[] = "v.end_date < " . Connection::GetSQLString(date("Y-m-d", strtotime($endDate)));
            }
        }

        if ($employeeID !== null) {
            $where[] = "v.employee_id = " . Connection::GetSQLString($employeeID);
        }

        if ($type == "issued") {
            $query = "SELECT SUM(v.amount::numeric) FROM voucher AS v "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
            $amount = $stmt->FetchField($query);
            if ($returnArray) {
                $query = "SELECT * FROM voucher AS v "
                    . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
                $issuedList = $stmt->FetchList($query);

                return ["amount" => $amount, "list" => $issuedList];
            }

            return $amount;
        }

        if ($type == "invoiced") {
            $where[] = "v.invoice_export_id IS NOT NULL";

            $query = "SELECT v.amount::numeric, v.voucher_date, v.created,
                        i.date_from as invoice_date_from, i.date_to as invoice_date_to
                        FROM voucher AS v
                        LEFT JOIN invoice i ON i.invoice_id::int=v.invoice_export_id::int"
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
            $voucherList = $stmt->FetchList($query);

            $invoicedList = [];
            $issuedMonthList = [];
            $notIssuedMonthList = [];

            foreach ($voucherList as $voucher) {
                $invoicedList[] = $voucher;

                if (
                    strtotime($voucher["created"]) >= strtotime(date("Y-m-01", strtotime($date)))
                    && strtotime($voucher["created"]) <= strtotime(date("Y-m-t 23:59:59", strtotime($date)))
                ) {
                    $issuedMonthList[] = $voucher;
                } else {
                    $notIssuedMonthList[] = $voucher;
                }
            }
            $amount = array_sum(array_column($invoicedList, "amount"));

            if (!$returnArray) {
                $amountIssuedMonth = array_sum(array_column($issuedMonthList, "amount"));
                $amountNotIssuedMonth = array_sum(array_column($notIssuedMonthList, "amount"));

                return [
                    "amount" => $amount,
                    "issued_month" => $amountIssuedMonth,
                    "not_issued_month" => $amountNotIssuedMonth,
                ];
            }

            return ["amount" => $amount, "list" => $invoicedList];
        }

        if ($type == "paid" || $type == "to_be_paid") {
            if ($type == "paid") {
                $where[] = "r.creditor_export_id IS NOT NULL";
            } else {
                $where[] = "r.creditor_export_id IS NULL";
            }

            $query = "SELECT SUM(vr.amount::numeric) AS amount
					FROM receipt AS r
						LEFT JOIN voucher_receipt AS vr ON r.receipt_id=vr.receipt_id
						LEFT JOIN voucher AS v ON vr.voucher_id=v.voucher_id "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
            $amount = $stmt->FetchField($query);

            if ($returnArray) {
                $query = "SELECT DISTINCT vr.amount, vr.voucher_id
					FROM receipt AS r
						LEFT JOIN voucher_receipt AS vr ON r.receipt_id=vr.receipt_id
						LEFT JOIN voucher AS v ON vr.voucher_id=v.voucher_id "
                    . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
                $result = $stmt->FetchList($query);
                $list = [];
                foreach ($result as $voucher) {
                    if ($voucher["amount"] <= 0) {
                        continue;
                    }

                    $key = array_search($voucher["voucher_id"], array_column($list, "voucher_id"));
                    if ($key === false) {
                        $list[] = array("voucher_id" => $voucher["voucher_id"], "amount" => $voucher["amount"]);
                    } else {
                        $list[$key]["amount"] += $voucher["amount"];
                    }
                }

                return ["amount" => $amount, "list" => $list];
            }

            return $amount;
        }

        $where[] = "v.file IS NOT NULL";
        if ($type == "expired") {
            $where[] = "v.invoice_export_id IS NOT NULL";
        }

        $query = "SELECT v.voucher_id, v.amount, v.employee_id, v.voucher_date
                  FROM voucher AS v " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") .
            " ORDER BY v.voucher_id ASC ";
        $voucherList = $stmt->FetchList($query);

        foreach ($voucherList as $vkey => $voucher) {
            $voucherList[$vkey]["amount_left"] = $voucherList[$vkey]["amount"];
            $voucherList[$vkey]["amount_approved"] = 0;
        }
        if ($type == "open" && !empty($voucherList)) {
            $voucherIDs = array_column($voucherList, "voucher_id");
            $query = "SELECT vr.amount, vr.voucher_id, r.creditor_export_id
                        FROM voucher_receipt vr
                        LEFT JOIN receipt r ON vr.receipt_id=r.receipt_id
                        WHERE vr.voucher_id IN (" . implode(", ", $voucherIDs) . ")
                        ORDER BY vr.voucher_id ASC";
            $links = $stmt->FetchList($query);

            $voucherKeys = array_flip($voucherIDs);
            foreach ($links as $link) {
                if (!isset($voucherKeys[$link["voucher_id"]]) || $link["creditor_export_id"] == null) {
                    continue;
                }
                $vkey = $voucherKeys[$link["voucher_id"]];
                $voucherList[$vkey]["amount_left"] = bcsub(
                    $voucherList[$vkey]["amount_left"],
                    $link["amount"],
                    2
                );
            }
        }

        $amount = $type == "open" || $type == "expired" ? array_sum(array_column($voucherList, "amount_left")) : array_sum(array_column($voucherList, "amount_approved"));

        $notUsedVouchers = [];
        $partiallyUsedVouchers = [];
        foreach ($voucherList as $voucher) {
            if ($voucher["amount_left"] == $voucher["amount"]) {
                $notUsedVouchers[] = $voucher;
            } elseif ($voucher["amount_left"] > 0) {
                $partiallyUsedVouchers[] = $voucher;
            }
        }

        $notUsedAmount = array_sum(array_column($notUsedVouchers, "amount_left"));
        $partiallyUsedAmount = array_sum(array_column($partiallyUsedVouchers, "amount_left"));

        if ($returnArray) {
            return [
                "amount" => $amount,
                "list" => $voucherList,
                "not_used_amount" => $notUsedAmount,
                "not_used_list" => $notUsedVouchers,
                "partially_used_amount" => $partiallyUsedAmount,
                "partially_used_list" => $partiallyUsedVouchers,
            ];
        }
        if ($type == "open") {
            return [
                "amount" => $amount,
                "not_used_amount" => $notUsedAmount,
                "partially_used_amount" => $partiallyUsedAmount,
            ];
        }

        return $amount;
    }

    /**
     * Outputs csv file with company unit's voucher list formatted for Datev
     *
     * @param string $dateFrom start of export period filters vouchers by "created" field
     * @param string $dateTo end of export period filters vouchers by "created" field
     */
    public function ExportToDatev($dateFrom, $dateTo)
    {
        $dateTo = $dateTo ?: date("t.m.Y");
        $dateFrom = $dateFrom ?: date("1.m.Y", strtotime($dateTo));

        $dateFromObject = new DateTime($dateFrom);
        $endOfMonthObject = new DateTime(date("t.m.Y", strtotime($dateTo)));

        $voucherProductGroupList = ProductGroupList::GetProductGroupList(false, true, false, true);
        $groupIDs = array_column($voucherProductGroupList, "group_id");

        $stmt = GetStatement(DB_MAIN);
        if ($dateTo) {
            $where[] = "DATE(r.document_date) <= " . Connection::GetSQLDate($dateTo);
        }
        $where[] = "r.creditor_export_id IS NULL";
        $where[] = "r.status='approved'";
        $where[] = "r.archive!='Y'";
        $where[] = "r.group_id IN(" . implode(", ", $groupIDs) . ")";
        $query = "SELECT r.receipt_id, r.employee_id, r.creditor_export_id, r.real_amount_approved, r.group_id FROM receipt r"
            . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $receiptList = $stmt->FetchList($query);


        $voucherList = [];
        $approvedVoucherList = [];
        $employeeList = [];
        foreach ($receiptList as $receipt) {
            $specificProductGroup = SpecificProductGroupFactory::Create($receipt["group_id"]);
            $employeeID = $receipt["employee_id"];
            $employeeList[$employeeID] = $specificProductGroup->GetVoucherMappedReceiptList($employeeID);
            $companyUnitID = Employee::GetEmployeeField($employeeID, "company_unit_id");

            $approvedByCustomer = "";
            if ($receipt["group_id"] == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER)) {
                $approvedByCustomer = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    OPTION__BENEFIT_VOUCHER__MAIN__PAYMENT_APPROVED,
                    $employeeID,
                    $dateTo
                );
            }

            if ($approvedByCustomer == "Y") {
                if (!isset($voucherList[$companyUnitID]["voucher_ids"])) {
                    $voucherList[$companyUnitID]["voucher_ids"] = [];
                }
                if (!isset($voucherList[$companyUnitID]["amount"])) {
                    $voucherList[$companyUnitID]["amount"] = 0;
                }

                $receiptKey = array_search(
                    $receipt["receipt_id"],
                    array_column($employeeList[$employeeID], "receipt_id")
                );
                $voucherIDs = $employeeList[$employeeID][$receiptKey]["voucher_list"] ?? [];
                foreach ($voucherIDs as $voucher) {
                    if (in_array($voucher, $voucherList[$companyUnitID]["voucher_ids"])) {
                        continue;
                    }

                    $voucherList[$companyUnitID]["voucher_ids"][] = $voucher;
                }

                $voucherList[$companyUnitID]["amount"] += $receipt["real_amount_approved"];
                $voucherList[$companyUnitID]["approved_by_customer"] = $approvedByCustomer;
                $voucherList[$companyUnitID]["company_unit_id"] = $companyUnitID;
                $voucherList[$companyUnitID]["company_unit_title"] = CompanyUnit::GetPropertyValue(
                    "title",
                    $companyUnitID
                );
                $voucherList[$companyUnitID]["customer_guid"] = CompanyUnit::GetPropertyValue(
                    "creditor_number",
                    $companyUnitID
                );
            } else {
                if (!isset($approvedVoucherList[$employeeID]["voucher_ids"])) {
                    $approvedVoucherList[$employeeID]["voucher_ids"] = [];
                }
                if (!isset($approvedVoucherList[$employeeID]["amount"])) {
                    $approvedVoucherList[$employeeID]["amount"] = 0;
                }

                $receiptKey = array_search(
                    $receipt["receipt_id"],
                    array_column($employeeList[$employeeID], "receipt_id")
                );
                $voucherIDs = $employeeList[$employeeID][$receiptKey]["voucher_list"] ?? array();
                foreach ($voucherIDs as $voucher) {
                    if (in_array($voucher, $approvedVoucherList[$employeeID]["voucher_ids"])) {
                        continue;
                    }

                    $approvedVoucherList[$employeeID]["voucher_ids"][] = $voucher;
                }

                $approvedVoucherList[$employeeID]["amount"] += $receipt["real_amount_approved"];
                $approvedVoucherList[$employeeID]["approved_by_customer"] = $approvedByCustomer;
                $approvedVoucherList[$employeeID]["company_unit_id"] = $companyUnitID;
                $approvedVoucherList[$employeeID]["employee_id"] = $employeeID;
                $approvedVoucherList[$employeeID]["company_unit_title"] = CompanyUnit::GetPropertyValue(
                    "title",
                    $companyUnitID
                );
                $approvedVoucherList[$employeeID]["customer_guid"] = Employee::GetEmployeeField(
                    $employeeID,
                    "creditor_number"
                );
            }
        }

        $this->LoadFromArray(array_merge($voucherList, $approvedVoucherList));
        array_multisort(array_column($this->_items, "company_unit_id"), SORT_ASC, $this->_items);

        //build file header
        $fileHeader = [
            "EXTF", //Dateibezeichnung Always (EXTF)
            "510", //DATEV PRO (Always 510)
            "21", //Versionsnummer (Always 21)
            "Buchungsstapel", //Formatname
            "7", //Formatversion
            "=" . date("YmdHisv"), //Date+ Time. "=" is needed phpspreadsheet to process this value as a string
            "", //empty
            "BH", //Initial source (Always BH)
            "", //empty
            "", //empty
            Config::GetConfigValue("export_datev_tax_consultant_id"), //Tax Consultant ID
            Config::GetConfigValue("export_datev_voucher_client_id"), //Customer ID Datev
            date("Y", strtotime($dateFrom)) . "0101", //Start Business Year (always 20180101)
            "4", //Account length (always 4)
            $dateFromObject->format("Ymd"), //Start booking period of this file
            $endOfMonthObject->format("Ymd"), //End booking period of this file
            "", //empty
            "", //empty
            "1", //Always like this
            "0", //Always like this
            "0", //(always 0) Festschreibungskennzahl 0=nein
        ];

        //build voucher table header
        $voucherTableHeader = explode(
            ";",
            "Umsatz (ohne Soll/Haben-Kz);Soll/Haben-Kennzeichen;WKZ Umsatz;Kurs;Basis-Umsatz;WKZ Basis-Umsatz;Konto;Gegenkonto (ohne BU-Schlџssel);BU-Schlџssel;Belegdatum;Belegfeld 1;Belegfeld 2;Skonto;Buchungstext;Postensperre;Diverse Adressnummer;Geschäftspartnerbank;Sachverhalt;Zinssperre;Beleglink;Beleginfo - Art 1;Beleginfo - Inhalt 1;Beleginfo - Art 2;Beleginfo - Inhalt 2;Beleginfo - Art 3;Beleginfo - Inhalt 3;Beleginfo - Art 4;Beleginfo - Inhalt 4;Beleginfo - Art 5;Beleginfo - Inhalt 5;Beleginfo - Art 6;Beleginfo - Inhalt 6;Beleginfo - Art 7;Beleginfo - Inhalt 7;Beleginfo - Art 8;Beleginfo - Inhalt 8;KOST1 - Kostenstelle;KOST2 - Kostenstelle;Kost-Menge;EU-Land u. UStID;EU-Steuersatz;Abw. Versteuerungsart;Sachverhalt L+L;FunktionsergЉnzung L+L;BU 49 Hauptfunktionstyp;BU 49 Hauptfunktionsnummer;BU 49 FunktionsergЉnzung;Zusatzinformation - Art 1;Zusatzinformation- Inhalt 1;Zusatzinformation - Art 2;Zusatzinformation- Inhalt 2;Zusatzinformation - Art 3;Zusatzinformation- Inhalt 3;Zusatzinformation - Art 4;Zusatzinformation- Inhalt 4;Zusatzinformation - Art 5;Zusatzinformation- Inhalt 5;Zusatzinformation - Art 6;Zusatzinformation- Inhalt 6;Zusatzinformation - Art 7;Zusatzinformation- Inhalt 7;Zusatzinformation - Art 8;Zusatzinformation- Inhalt 8;Zusatzinformation - Art 9;Zusatzinformation- Inhalt 9;Zusatzinformation - Art 10;Zusatzinformation- Inhalt 10;Zusatzinformation - Art 11;Zusatzinformation- Inhalt 11;Zusatzinformation - Art 12;Zusatzinformation- Inhalt 12;Zusatzinformation - Art 13;Zusatzinformation- Inhalt 13;Zusatzinformation - Art 14;Zusatzinformation- Inhalt 14;Zusatzinformation - Art 15;Zusatzinformation- Inhalt 15;Zusatzinformation - Art 16;Zusatzinformation- Inhalt 16;Zusatzinformation - Art 17;Zusatzinformation- Inhalt 17;Zusatzinformation - Art 18;Zusatzinformation- Inhalt 18;Zusatzinformation - Art 19;Zusatzinformation- Inhalt 19;Zusatzinformation - Art 20;Zusatzinformation- Inhalt 20;Stџck;Gewicht;Zahlweise;Forderungsart;Veranlagungsjahr;Zugeordnete FЉlligkeit;Skontotyp;Auftragsnummer;Buchungstyp;Ust-Schlџssel (Anzahlungen);EU-Land (Anzahlungen);Sachverhalt L+L (Anzahlungen);EU-Steuersatz (Anzahlungen);Erlљskonto (Anzahlungen);Herkunft-Kz;Leerfeld;KOST-Datum;Mandatsreferenz;Skontosperre;Gesellschaftername;Beteiligtennummer;Identifikationsnummer;Zeichnernummer;Postensperre bis;Bezeichnung SoBil-Sachverhalt;Kennzeichen SoBil-Buchung;Festschreibung;Leistungsdatum;Datum Zuord.Steuerperiode"
        );

        //build voucher table body
        $voucherTableBody = [];
        foreach ($this->GetItems() as $voucher) {
            if ($voucher["amount"] <= 0) {
                continue;
            }

            if ($voucher["approved_by_customer"] == "Y") {
                $voucher["buchungstext"] = $voucher["company_unit_title"] . " Gutschrift trebono Gutscheine " . (count($voucher["voucher_ids"]) > 0 ? implode(
                    " ",
                    $voucher["voucher_ids"]
                ) : "");
            } else {
                $voucher["buchungstext"] = "trebono Gutscheine: " . (count($voucher["voucher_ids"]) > 0 ? implode(
                    " ",
                    $voucher["voucher_ids"]
                ) : "");
            }

            $row = [
                str_replace(".", ",", $voucher["amount"]), //Total Amount of voucher
                "H", //always H
                "EUR", //always EUR
                "", //empty
                "", //empty
                "", //empty
                $voucher["customer_guid"], //Customer ID without the first 4 digits of the year OR creditor number
                "3200", //Always 3200
                "", //empty
                date("jm", strtotime($dateTo)), //day and month of export
                implode(" ", $voucher["voucher_ids"]), //voucher number
                "", //empty
                "", //empty
                $voucher["buchungstext"], //<customer name> Rechnungs Nr.: <Voucher Number>
            ];
            for ($i = count($row); $i <= 112; $i++) {
                $row[] = "";
            }
            $row[113] = "0"; //Always 0
            $voucherTableBody[] = $row;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($fileHeader, null, "A1");
        $spreadsheet->getActiveSheet()->fromArray($voucherTableHeader, null, "A2");
        $spreadsheet->getActiveSheet()->fromArray($voucherTableBody, null, "A3");

        //save and output the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
        $writer->setDelimiter(";");
        $writer->setEnclosure("");
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $voucherExport = new VoucherExport("billing");
        $voucherExportID = $voucherExport->Create($dateTo);
        $voucherExport->LoadByID($voucherExportID);

        if (count($receiptList) > 0) {
            $query = "UPDATE receipt SET creditor_export_id=" . intval($voucherExportID) . " WHERE receipt_id IN(" . implode(
                ", ",
                array_column($receiptList, "receipt_id")
            ) . ")";
            $stmt->Execute($query);
        }

        $tempFilePath = PROJECT_DIR . "var/log/export_datev_creditor_" . date("U") . "_" . rand(100, 999) . ".csv";

        $writer->save($tempFilePath);

        $filename = "extf_buchungsstapel_creditor_" .
            date_create($voucherExport->GetProperty("created"))->format("Ymd") . "_" .
            $voucherExport->GetProperty("export_number") . ".csv";

        $fileStorage = GetFileStorage(CONTAINER__BILLING__VOUCHER_EXPORT);
        $fileStorage->MoveToStorage($tempFilePath, VOUCHER_EXPORT_DIR, $filename);

        unlink($tempFilePath);
    }

    /**
     * Get available voucher list for approve receipt
     *
     * @param Receipt $receipt
     * @param bool $excludeCurrentReceiptLinks
     */
    public static function GetAvailableVoucherListForReceipt($receipt, $excludeCurrentReceiptLinks = false)
    {
        $stmt = GetStatement();

        $query = "SELECT voucher_id, amount, voucher_date, amount, reason FROM voucher
					WHERE employee_id=" . intval($receipt->GetProperty("employee_id")) . " AND
						  group_id=" . intval($receipt->GetProperty("group_id")) . " AND
						  archive='N' AND
						  voucher_date<=" . Connection::GetSQLDate($receipt->GetProperty("document_date")) . " AND
						  end_date>=" . Connection::GetSQLDate($receipt->GetProperty("document_date")) . "
					ORDER BY voucher_date, voucher_id";

        $voucherList = $stmt->FetchList($query);

        $voucherListResult = [];
        foreach ($voucherList as $voucher) {
            $receiptList = Voucher::GetVoucherReceiptLinks($voucher["voucher_id"]);

            $approvedAmount = 0;
            foreach ($receiptList as $receiptItem) {
                if ($excludeCurrentReceiptLinks && $receiptItem["receipt_id"] == $receipt->GetProperty("receipt_id")) {
                    continue;
                }

                $approvedAmount += $receiptItem["amount"];
            }

            if ($voucher["amount"] <= $approvedAmount) {
                continue;
            }

            $voucher["approved_amount"] = $approvedAmount;
            $voucher["available_amount"] = $voucher["amount"] - $approvedAmount;
            $voucherListResult[] = $voucher;
        }

        return $voucherListResult;
    }

    public static function GetLinksForVoucherList($voucherList)
    {
        $voucherColumnID = array_column($voucherList, "voucher_id");

        if (count($voucherColumnID) > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT vr.receipt_id, vr.voucher_id, vr.amount, vr.created,
                            r.status, r.status_updated
                        FROM voucher_receipt vr
                            LEFT JOIN receipt r ON r.receipt_id=vr.receipt_id
                        WHERE voucher_id IN
                              (" . implode(", ", $voucherColumnID) . ")";
            $linkList = $stmt->FetchList($query);

            foreach ($linkList as $link) {
                $key = array_search($link["voucher_id"], $voucherColumnID);
                if ($key === false) {
                    continue;
                }

                $voucherList[$key]["link_list"][] = $link;
            }

            foreach ($voucherList as $key => $voucher) {
                if (isset($voucher["link_list"])) {
                    continue;
                }

                $voucherList[$key]["link_list"] = [];
            }
        }

        return $voucherList;
    }

    /**
     * Function returns statistics for employee of vouchers (existing ones)
     *
     * @param int $employeeID
     * @param string $groupID
     * @param null $year
     * @param null $languageCode
     *
     * @return array|mixed[]
     */
    public function GetVoucherStatisticsForEmployee($employeeID, $groupID, $year = null, $languageCode = null)
    {
        $foodVoucherID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);
        $specificProductGroup = SpecificProductGroupFactory::Create($groupID);
        $voucherList = $specificProductGroup !== null
            ? $specificProductGroup->GetReceiptMappedVoucherList($employeeID) : [];

        if (intval($year) == 0) {
            $startOfYear = date("Y-01-01");
            $endOfYear = date("Y-12-31");
        } else {
            $startOfYear = date("Y-01-01", strtotime("01/01/" . $year));
            $endOfYear = date("Y-12-31", strtotime("01/01/" . $year));
        }

        //form month list with german/english month names
        $monthList = GetMonthList($startOfYear, $endOfYear, $languageCode);
        //list of statuses that are counted in statistics
        $statusList = ["approved", "approve_proposed"];

        //building the statistics
        $voucherMap = [];
        foreach ($voucherList as &$voucher) {
            unset($voucher["receipt_ids"]);
            $isOldFVS = $voucher["group_id"] == $foodVoucherID
                && strtotime($voucher["voucher_date"]) < strtotime($startOfYear)
                && $voucher["amount_approved"] > 0;

            if (strtotime($voucher["voucher_date"]) > strtotime($endOfYear) || $voucher["file"] == null) {
                continue;
            }

            //exclude old vouchers if they were used last year and weren't exported current year
            if (strtotime($voucher["voucher_date"]) < strtotime($startOfYear) && isset($voucher["receipt_list"])) {
                $voucher["amount_this_year"] = $voucher["amount"];

                $exclude = $isOldFVS ? true : null;

                foreach ($voucher["receipt_list"] as $receipt) {
                    $isOld = strtotime($receipt["document_date"]) < strtotime($startOfYear);

                    if ($isOld) {
                        $voucher["amount_this_year"] -= $receipt["amount"];
                    }

                    if (
                        in_array($receipt["status"], $statusList)
                        && $exclude !== false && $voucher["amount_this_year"] == 0
                    ) {
                        $exclude = $isOld;
                    }

                    if (intval($receipt["creditor_export_id"]) <= 0) {
                        continue;
                    }

                    $exportMonth = VoucherExport::GetPropertyByID(
                        $receipt["creditor_export_id"],
                        "export_month"
                    );
                    $exportYear = date("Y-01-01", strtotime("01/01/" . substr($exportMonth, 0, 4)));
                    if (strtotime($startOfYear) != strtotime($exportYear)) {
                        continue;
                    }

                    $exclude = false;
                }

                if ($exclude == null) {
                    $exclude = false;
                }

                if ($exclude) {
                    continue;
                }
            }

            //build up info for total column and employee list
            if ($voucherMap == []) {
                $voucherMap[] = [
                    "amount" => 0,
                    "open_amount" => 0,
                    "paid" => 0,
                    "approved" => 0,
                    "month_list" => [],
                    "voucher_list" => [],
                ];
            }
            $key = count($voucherMap) - 1;

            if (isset($voucher["amount_this_year"])) {
                $voucherMap[$key]["amount"] += $voucher["amount_this_year"];
            } else {
                $voucherMap[$key]["amount"] += $voucher["amount"];
            }

            $voucherMap[$key]["open_amount"] += $voucher["amount_left"];

            $voucherMap[$key]["voucher_list"][] = $voucher;
            $voucherKey = count($voucherMap[$key]["voucher_list"]) - 1;

            //include all months for table consistency
            $date = $startOfYear;
            while (strtotime($date) < strtotime($endOfYear)) {
                if (count($voucherMap[$key]["month_list"]) < 12) {
                    $voucherMap[$key]["month_list"][] = [
                        "month_id" => date("m", strtotime($date)),
                        "paid" => 0,
                    ];
                }
                $voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][] = [
                    "month_id" => date("m", strtotime($date)),
                    "paid" => 0,
                ];
                $date = date("Y-m-01", strtotime($date . " + 1 month"));
            }

            //count paid amount by exported receipts
            if (!isset($voucher["receipt_list"])) {
                continue;
            }

            foreach ($voucher["receipt_list"] as $receipt) {
                if (intval($receipt["creditor_export_id"]) > 0) {
                    $exportMonth = VoucherExport::GetPropertyByID($receipt["creditor_export_id"], "export_month");
                    $exportYear = date("Y-01-01", strtotime("01/01/" . substr($exportMonth, 0, 4)));
                    if (strtotime($startOfYear) == strtotime($exportYear)) {
                        if (isset($voucherMap[$key]["voucher_list"][$voucherKey]["paid"])) {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["paid"] += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["paid"] = $receipt["amount"];
                        }

                        $exportMonth = substr($exportMonth, -2);
                        if (substr($exportMonth, 0, 1) == 0) {
                            $exportMonth = substr($exportMonth, -1);
                        }
                        $monthKey = array_search(
                            $exportMonth,
                            array_column($voucherMap[$key]["month_list"], "month_id")
                        );

                        if (isset($voucherMap[$key]["month_list"][$monthKey]["paid"])) {
                            $voucherMap[$key]["month_list"][$monthKey]["paid"] += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["month_list"][$monthKey]["paid"] = $receipt["amount"];
                        }

                        if (isset($voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["paid"])) {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["paid"]
                                += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["paid"]
                                = $receipt["amount"];
                        }

                        $voucherMap[$key]["paid"] += $receipt["amount"];
                    }
                } elseif (in_array($receipt["status"], $statusList)) {
                    if (
                        strtotime($receipt["document_date"]) >= strtotime($startOfYear)
                        && strtotime($receipt["document_date"]) <= strtotime($endOfYear)
                    ) {
                        if (isset($voucherMap[$key]["voucher_list"][$voucherKey]["approved"])) {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["approved"] += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["approved"] = $receipt["amount"];
                        }

                        $approveMonth = date("m", strtotime($receipt["document_date"]));
                        $monthKey = array_search(
                            $approveMonth,
                            array_column($voucherMap[$key]["month_list"], "month_id")
                        );

                        if (isset($voucherMap[$key]["month_list"][$monthKey]["approved"])) {
                            $voucherMap[$key]["month_list"][$monthKey]["approved"] += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["month_list"][$monthKey]["approved"] = $receipt["amount"];
                        }

                        if (isset($voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["approved"])) {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["approved"]
                                += $receipt["amount"];
                        } else {
                            $voucherMap[$key]["voucher_list"][$voucherKey]["month_list"][$monthKey]["approved"]
                                = $receipt["amount"];
                        }

                        $voucherMap[$key]["approved"] += $receipt["amount"];
                    }
                }
            }
        }

        return ["month_title_list" => $monthList, "voucher_list" => $voucherMap];
    }

    /**
     * Function returns statistics for the company of vouchers (existing and future ones)
     *
     * @param $companyUnitID
     * @param $groupID
     * @param $year
     * @param null $languageCode
     *
     * @return array|mixed[]
     */
    public function GetVoucherStatistics($companyUnitID, $groupID, $year = null, $languageCode = null)
    {
        $groupCode = ProductGroup::GetProductGroupCodeByID($groupID);
        $tmpVoucherList = new VoucherList("company");
        $tmpVoucherList->SetItemsOnPage(0);
        $tmpVoucherList->LoadVoucherListByCompanyUnitID($companyUnitID, $groupID);
        $voucherList = [];

        if (intval($year) == 0) {
            $startOfYear = date("Y-01-01");
            $endOfYear = date("Y-12-31");
        } else {
            $startOfYear = date("Y-01-01", strtotime("01/01/" . $year));
            $endOfYear = date("Y-12-31", strtotime("01/01/" . $year));
        }
        $today = GetCurrentDate();

        //form month list with german/english month names
        $monthList = GetMonthList($startOfYear, $endOfYear, $languageCode);

        //need to include vouchers that WILL be generated in following months and mark them with different color

        //first, include monthly generated vouchers
        $monthlyGenerationList = [PRODUCT_GROUP__FOOD_VOUCHER, PRODUCT_GROUP__BENEFIT_VOUCHER];
        if (in_array($groupCode, $monthlyGenerationList)) {
            $specificProductGroup = SpecificProductGroupFactory::Create($groupID);

            $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
            $contract = new Contract("product");

            foreach ($employeeList as $employeeID) {
                $date = date(
                    "Y-m-01",
                    strtotime($today . " +1 month")
                ); //not including current month because voucher should be generated already
                do {
                    //if auto generation is off, we will not generate new voucher
                    $autoGeneration = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $specificProductGroup->GetGenerationVoucherOptionCode(),
                        $employeeID,
                        $date
                    );
                    if ($autoGeneration !== "Y") {
                        $date = date("Y-m-01", strtotime($date . " +1 month"));
                        continue;
                    }

                    //if contract ended, we will not generate new voucher
                    $baseContractExists = $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                        $employeeID,
                        $date
                    );
                    $contractExists = $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT_GROUP__MAIN_PRODUCT[$groupCode]),
                        $employeeID,
                        $date
                    );
                    if ($contractExists && $baseContractExists) {
                        if ($groupCode == PRODUCT_GROUP__BENEFIT_VOUCHER) {
                            $voucherList[] = [
                                "voucher_id" => "monthly_generated",
                                "employee_id" => $employeeID,
                                "group_id" => ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER),
                                "amount" => Option::GetInheritableOptionValue(
                                    OPTION_LEVEL_EMPLOYEE,
                                    OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY,
                                    $employeeID,
                                    $date
                                ),
                                "open_amount" => Option::GetInheritableOptionValue(
                                    OPTION_LEVEL_EMPLOYEE,
                                    OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY,
                                    $employeeID,
                                    $date
                                ),
                                "voucher_date" => date("Y-m-01", strtotime($date)),
                            ];
                        } elseif ($groupCode == PRODUCT_GROUP__FOOD_VOUCHER) {
                            $countVouchers = Option::GetInheritableOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH,
                                $employeeID,
                                $date
                            );
                            for ($i = 1; $i <= $countVouchers; $i++) {
                                $voucherList[] = [
                                    "voucher_id" => "monthly_generated",
                                    "employee_id" => $employeeID,
                                    "group_id" => ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER),
                                    "amount" => $specificProductGroup->GetUnit(new Receipt(
                                        "receipt",
                                        ["document_date" => $date, "employee_id" => $employeeID]
                                    )),
                                    "voucher_date" => date("Y-m-01", strtotime($date)),
                                ];
                            }
                        }
                    }
                    $date = date("Y-m-01", strtotime($date . " +1 month"));
                } while (strtotime($date) < strtotime($endOfYear));
            }
        }

        //second, include recurring vouchers
        $voucherList = array_merge($voucherList, $tmpVoucherList->FormRecurringVoucherList($groupCode, $endOfYear));

        //building the statistics
        $voucherMap = [];
        foreach ($voucherList as $voucher) {
            if (strtotime($voucher["voucher_date"]) < strtotime($startOfYear) || strtotime($voucher["voucher_date"]) > strtotime($endOfYear)) {
                continue;
            }

            if ($voucher["voucher_id"] == "recurring" && strtotime($voucher["voucher_date"]) <= strtotime($today)) {
                continue;
            }

            //build up info for total column and employee list
            $key = array_search($voucher["amount"], array_column($voucherMap, "amount"));
            if ($key === false) {
                $voucherMap[] = [
                    "amount" => $voucher["amount"],
                    "count" => 0,
                    "employee_list" => [],
                ];
                $key = count($voucherMap) - 1;
            }

            $voucherMap[$key]["count"]++;
            $employeeKey = array_search(
                $voucher["employee_id"],
                array_column($voucherMap[$key]["employee_list"], "employee_id")
            );
            if ($employeeKey === false) {
                $voucherMap[$key]["employee_list"][] = [
                    "employee_id" => $voucher["employee_id"],
                    "employee_name" => Employee::GetNameByID($voucher["employee_id"]),
                    "count" => 0,
                    "amount" => $voucher["amount"],
                    "open_amount" => $voucher["open_amount"] ?? $voucher["amount"],
                    "month_list" => [],
                ];
                $employeeKey = count($voucherMap[$key]["employee_list"]) - 1;

                //want to include all months for table consistency
                $date = $startOfYear;
                while (strtotime($date) < strtotime($endOfYear)) {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][] = [
                        "month_id" => date("m", strtotime($date)),
                    ];
                    $date = date("Y-m-01", strtotime($date . " + 1 month"));
                }
            }

            $monthKey = array_search(
                date("m", strtotime($voucher["voucher_date"])),
                array_column($voucherMap[$key]["employee_list"][$employeeKey]["month_list"], "month_id")
            );
            if ($monthKey !== false) {
                $voucherMap[$key]["employee_list"][$employeeKey]["count"]++;

                if (isset($voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["count"])) {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["count"]++;
                } else {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["count"] = 1;
                }

                $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["voucher_list"][] = ["voucher_id" => $voucher["voucher_id"]];

                //is this voucher will be generated in the future?
                if (strtotime($voucher["voucher_date"]) > strtotime($today)) {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["hide"] = true;
                    if (isset($voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["future_count"])) {
                        $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["future_count"]++;
                    } else {
                        $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["future_count"] = 1;
                    }
                } else {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["hide"] = false;
                }

                //in case there's vouchers in current month that were not generated yet,
                //we want to visually separate them
                if (
                    isset($voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["future_count"])
                    && $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["future_count"] != $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["count"]
                ) {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["show_future_count"] = true;
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["hide"] = false;
                } else {
                    $voucherMap[$key]["employee_list"][$employeeKey]["month_list"][$monthKey]["show_future_count"] = false;
                }
            }

            //build up info for each month separately

            //want to include all months for table consistency
            $date = $startOfYear;
            while (strtotime($date) < strtotime($endOfYear)) {
                if (isset($voucherMap[$key]["month_list"])) {
                    $monthKey = array_search(
                        date("m", strtotime($date)),
                        array_column($voucherMap[$key]["month_list"], "month_id")
                    );
                } else {
                    $monthKey = false;
                }

                if ($monthKey === false) {
                    $voucherMap[$key]["month_list"][] = ["month_id" => date("m", strtotime($date))];
                }
                $date = date("Y-m-01", strtotime($date . " + 1 month"));
            }
            $monthKey = array_search(
                date("m", strtotime($voucher["voucher_date"])),
                array_column($voucherMap[$key]["month_list"], "month_id")
            );

            //filling total count  for each month separately
            if ($monthKey === false) {
                continue;
            }

            if (isset($voucherMap[$key]["month_list"][$monthKey]["count"])) {
                $voucherMap[$key]["month_list"][$monthKey]["count"]++;
            } else {
                $voucherMap[$key]["month_list"][$monthKey]["count"] = 1;
            }

            //is this voucher will be generated in the future?
            if (strtotime($voucher["voucher_date"]) > strtotime($today)) {
                if (isset($voucherMap[$key]["month_list"][$monthKey]["future_count"])) {
                    $voucherMap[$key]["month_list"][$monthKey]["future_count"]++;
                } else {
                    $voucherMap[$key]["month_list"][$monthKey]["future_count"] = 1;
                }
            }

            //in case there's vouchers in current month that were not generated yet, we want to visually separate them
            $voucherMap[$key]["month_list"][$monthKey]["show_future_count"] = isset($voucherMap[$key]["month_list"][$monthKey]["future_count"]) && $voucherMap[$key]["month_list"][$monthKey]["future_count"] != $voucherMap[$key]["month_list"][$monthKey]["count"];
        }

        //sorting by amount
        $sortArrayAmount = array_column($voucherMap, "amount");
        array_multisort($sortArrayAmount, SORT_DESC, $voucherMap);

        //sorting by employee name
        foreach ($voucherMap as $id => $voucher) {
            $sortArrayEmployee = array_column($voucher["employee_list"], "employee_name");
            array_multisort($sortArrayEmployee, SORT_ASC, $voucherMap[$id]["employee_list"]);
        }

        return ["month_title_list" => $monthList, "voucher_list" => $voucherMap];
    }

    /**
     * Function returns statistics for the company of vouchers (existing and future ones)
     *
     * @param $companyUnitID
     * @param $groupID
     * @param $year
     * @param null $languageCode
     *
     * @return array
     */
    public function GetVoucherDashboardDetails($voucherList, $year = null, $languageCode = null)
    {
        if (intval($year) == 0) {
            $startOfYear = date("Y-01-01");
            $endOfYear = date("Y-12-31");
        } else {
            $startOfYear = date("Y-01-01", strtotime("01/01/" . $year));
            $endOfYear = date("Y-12-31", strtotime("01/01/" . $year));
        }
        $today = GetCurrentDate();

        //form month list with german/english month names
        $monthList = GetMonthList($startOfYear, $endOfYear, $languageCode);

        //building the statistics
        $voucherMap = array();
        foreach ($voucherList as $voucher) {
            if (strtotime($voucher["voucher_date"]) < strtotime($startOfYear) || strtotime($voucher["voucher_date"]) > strtotime($endOfYear)) {
                continue;
            }

            //build up info for total column and employee list
            $employeeKey = array_search($voucher["employee_id"], array_column($voucherMap, "employee_id"));
            if ($employeeKey === false) {
                $voucherMap[] = array(
                    "employee_id" => $voucher["employee_id"],
                    "employee_name" => Employee::GetNameByID($voucher["employee_id"]),
                    "amount" => 0,
                    "amount_left" => 0,
                    "month_list" => array()
                );
                $employeeKey = count($voucherMap) - 1;

                //want to include all months for table consistency
                $date = $startOfYear;
                while (strtotime($date) < strtotime($endOfYear)) {
                    $voucherMap[$employeeKey]["month_list"][] = array(
                        "month_id" => date("m", strtotime($date)),
                        "amount_left" => 0
                    );
                    $date = date("Y-m-01", strtotime($date . " + 1 month"));
                }
            }

            $monthKey = array_search(
                date("m", strtotime($voucher["voucher_date"])),
                array_column($voucherMap[$employeeKey]["month_list"], "month_id")
            );
            if ($monthKey === false) {
                continue;
            }

            $voucherMap[$employeeKey]["month_list"][$monthKey]["voucher_list"][] = $voucher;
            $voucherMap[$employeeKey]["month_list"][$monthKey]["amount_left"] += $voucher["amount_left"];
            $voucherMap[$employeeKey]["amount"] += $voucher["amount"];
            $voucherMap[$employeeKey]["amount_left"] += $voucher["amount_left"];
        }

        //sorting by employee name
        $sortArrayEmployee = array_column($voucherMap, "employee_name");
        array_multisort($sortArrayEmployee, SORT_ASC, $voucherMap);

        return array("month_title_list" => $monthList, "voucher_list" => $voucherMap);
    }

    /**
     * @param $groupCode
     * @param $endOfYear
     *
     * @return array|mixed[]
     */
    public function FormRecurringVoucherList($groupCode, $endOfYear)
    {
        $today = GetCurrentDate();
        $voucherList = [];
        $contract = new Contract("product");
        foreach ($this->_items as $voucher) {
            if ($voucher["recurring"] == "Y") {
                $date = $voucher["voucher_date"];
                $endDate = $voucher["recurring_end_date"] ?? date("d.m.Y", strtotime($endOfYear));

                if ($voucher["recurring_frequency"] == "monthly") {
                    $date = date("d.m.Y", strtotime($date . " +1 month"));
                }
                if ($voucher["recurring_frequency"] == "quarterly") {
                    $date = date("d.m.Y", strtotime($date . " +3 month"));
                }
                if ($voucher["recurring_frequency"] == "yearly") {
                    $date = date("d.m.Y", strtotime($date . " +1 year"));
                }

                do {
                    //check that service is active
                    $baseContractExists = $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                        $voucher["employee_id"],
                        $date
                    );
                    $contractExists = $contract->ContractExist(
                        OPTION_LEVEL_EMPLOYEE,
                        Product::GetProductIDByCode(PRODUCT_GROUP__MAIN_PRODUCT[$groupCode]),
                        $voucher["employee_id"],
                        $date
                    );

                    $voucherDate = $date;
                    if ($contractExists && $baseContractExists && strtotime($voucherDate) > strtotime($today)) {
                        $voucherList[] = [
                            "voucher_id" => "recurring",
                            "employee_id" => $voucher["employee_id"],
                            "group_id" => $voucher["group_id"],
                            "amount" => $voucher["amount"],
                            "open_amount" => $voucher["amount"],
                            "voucher_date" => $voucherDate,
                            "reason" => $voucher["reason"],
                        ];
                    }

                    if ($voucher["recurring_frequency"] == "monthly") {
                        $date = date("d.m.Y", strtotime($date . " +1 month"));
                    }
                    if ($voucher["recurring_frequency"] == "quarterly") {
                        $date = date("d.m.Y", strtotime($date . " +3 month"));
                    }
                    if ($voucher["recurring_frequency"] != "yearly") {
                        continue;
                    }

                    $date = date("d.m.Y", strtotime($date . " +1 year"));
                } while (strtotime($date) <= strtotime($endDate));
            }
            $voucherList[] = $voucher;
        }

        return $voucherList;
    }
}
