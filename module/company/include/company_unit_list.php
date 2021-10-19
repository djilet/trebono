<?php

class CompanyUnitList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function CompanyUnitList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "title_asc" => Connection::GetSQLDecryption("u.title") . " ASC",
            "title_desc" => Connection::GetSQLDecryption("u.title") . " DESC",
        ));
        $this->SetOrderBy("title_asc");
    }

    /**
     * Loads available company_unit list ordering them as tree (to be able to output correct tree-table or select).
     * Using acl to select available company units only.
     *
     * @param int $companyID company_id of company whose branch should be loaded. If is not set then all the available branches will be loaded.
     * @param string|array $permissions names of permissions linked to company units to be validated.
     * @param string $archive set for filtering active or inactive company units
     * @param bool $appendProductGroupList if true appends active product group list to each company unit
     * @param bool $forAdmin if true don't check permission validation
     */
    public function LoadCompanyUnitListForTree(
        $companyID = null,
        $permissions = "company_unit",
        $archive = "",
        $appendProductGroupList = false,
        $forAdmin = false,
        User $user = null,
        $mode = "and"
    ) {
        $stmt = GetStatement();
        $where = array();
        if ($companyID) {
            $where[] = "u.company_id=" . intval($companyID);
        }
        if ($archive) {
            $where[] = "u.archive=" . Connection::GetSQLString($archive);
        }

        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }

        if ($user->Validate((array) $permissions, $mode) || $forAdmin === true) {
            $preWhere = "parent_unit_id IS NULL";
        } else {
            $companyUnitIDs = [];
            foreach ((array) $permissions as $pname) {
                $companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs($pname));
            }
            $where[] = "company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
            $companyUnitIDs = self::RemoveChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) <= 0) {
                return;
            }

            $preWhere = "company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
        }

        $query = "WITH RECURSIVE u AS (
						SELECT *, 0 AS level, '#' AS path_to_root, 0 AS tree_parent_id FROM company_unit WHERE " . $preWhere . "

						UNION ALL

						SELECT c.*, u.level + 1 AS level, CONCAT(u.path_to_root, u.company_unit_id, '#') AS path_to_root, c.parent_unit_id AS tree_parent_id
						FROM company_unit AS c
							JOIN u ON c.parent_unit_id = u.company_unit_id
					)
				SELECT u.*, " . Connection::GetSQLDecryption("u.title") . " AS title, " . Connection::GetSQLDecryption("u.street") . " AS street
				FROM u "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
				ORDER BY u.company_id ASC, u.path_to_root ASC, " . Connection::GetSQLDecryption("u.title") . " ASC";

        $src = $stmt->FetchList($query);
        if ($appendProductGroupList) {
            $src = $this->LoadEmployeeCountStatistics($src);
        }
        $result = $this->Prepare($src);
        $this->LoadFromArray($result);

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["select_prefix"] = str_repeat("----", $this->_items[$i]["level"]);
        }
        if (!$appendProductGroupList) {
            return;
        }

        $moduleProduct = "product";

        $groupList = new ProductGroupList($moduleProduct);
        $groupList->LoadProductGroupListForAdmin();
        $productIDs = array();
        $productGroupMap = array();

        for ($i = 0; $i < $groupList->GetCountItems(); $i++) {
            $specificProductGroup = SpecificProductGroupFactory::CreateByCode($groupList->_items[$i]["code"]);
            if ($specificProductGroup == null) {
                continue;
            }
            if (!is_object($specificProductGroup)) {
                continue;
            }

            $groupList->_items[$i]["main_product_code"] = $specificProductGroup->GetMainProductCode();
            $groupList->_items[$i]["main_product_id"] = Product::GetProductIDByCode($groupList->_items[$i]["main_product_code"]);
            $productIDs[] = $groupList->_items[$i]["main_product_id"];
            $productGroupMap[$groupList->_items[$i]["main_product_id"]] = $groupList->_items[$i]["title_translation"];
            $productGroupSortMap[$groupList->_items[$i]["main_product_id"]] = $groupList->_items[$i]["sort_order"];
        }

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $employeeList = EmployeeList::GetEmployeeIDsByCompanyUnitID($this->_items[$i]["company_unit_id"]);
            $employeeListWithReceipt = ReceiptList::GetApprovedReceiptEmployeeIDs($employeeList);
            $contract = new Contract($moduleProduct);
            if (
                !$companyUnitActiveProductList = $contract->GetCompanyUnitActiveProductList(
                    $this->_items[$i]["company_unit_id"],
                    $productIDs,
                    $employeeList,
                    $employeeListWithReceipt
                )
            ) {
                continue;
            }

            foreach ($companyUnitActiveProductList as &$companyUnitActiveProduct) {
                $companyUnitActiveProduct["title_translation"] = $productGroupMap[$companyUnitActiveProduct["product_id"]];
                $companyUnitActiveProduct["sort_order"] = $productGroupSortMap[$companyUnitActiveProduct["product_id"]];
            }

            array_multisort(
                array_column($companyUnitActiveProductList, "sort_order"),
                SORT_NUMERIC,
                $companyUnitActiveProductList
            );
            $this->_items[$i]["product_group_list"] = $companyUnitActiveProductList;
        }
    }

    /**
     * Recursively runs through company_unit list reordering them to be able to output correct tree-table or select.
     *
     * @param array $src list of company units
     * @param int $parentID company_unit_id of company unit child of which should be found. If is not set then root company units will be searched.
     *
     * @return array reordered company unit list
     */
    private function Prepare($src, $parentID = 0)
    {
        $result = array();
        foreach ($src as $companyUnit) {
            if ($companyUnit["tree_parent_id"] != $parentID) {
                continue;
            }

            $result[] = $companyUnit;
            $result = array_merge($result, $this->Prepare($src, $companyUnit["company_unit_id"]));
        }

        return $result;
    }

    /**
     * Returns array of company_unit_id's from passed to root
     *
     * @param int $id company_unit_id
     * @param bool $includeSelf true if passed company_unit_id should be included too or else otherwise
     *
     * @return array result company_unit_id's
     */
    public static function GetCompanyUnitPath2Root($id, $includeSelf)
    {
        $stmt = GetStatement();
        $result = array();
        $where = array();
        if (!$includeSelf) {
            $where[] = "u.company_unit_id<>" . intval($id);
        }

        $query = "WITH RECURSIVE u AS (
					SELECT company_unit_id, parent_unit_id, '#' AS path_to_child FROM company_unit WHERE company_unit_id=" . intval($id) . "

					UNION ALL

					SELECT c.company_unit_id, c.parent_unit_id, CONCAT(u.path_to_child, c.company_unit_id, '#') AS path_to_child
					FROM company_unit AS c
						JOIN u ON c.company_unit_id = u.parent_unit_id
				)
			SELECT u.*
			FROM u
			" . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . "
			ORDER BY u.path_to_child ASC";

        $companyUnitList = $stmt->FetchList($query);

        foreach ($companyUnitList as $companyUnit) {
            $result[] = $companyUnit["company_unit_id"];
        }

        return $result;
    }

    /**
     * Returns all company_unit_id's by given company or general list if company is not specified
     *
     * @param int $companyID company_id of specified company
     * @param string $order specifies list order
     *
     * @return array of company_unit_id's
     */
    public static function GetAllCompanyUnitIDs($companyID = null, $order = null)
    {
        $where = $companyID ? " WHERE company_id=" . intval($companyID) : "";

        $orderBy = "ORDER BY ";
        switch ($order) {
            case 'title_asc':
                $orderBy .= Connection::GetSQLDecryption("title") . " ASC";
                break;
            case 'title_desc':
                $orderBy .= Connection::GetSQLDecryption("title") . " DESC";
                break;
            default:
                $orderBy = "";
        }

        if ($order != "level") {
            $stmt = GetStatement();
            $query = "SELECT company_unit_id FROM company_unit" . $where . $orderBy;

            return array_keys($stmt->FetchIndexedList($query));
        }

        $companyUnitList = new self("company");
        $companyUnitList->LoadCompanyUnitListForTree($companyID);
        $result = array_column($companyUnitList->GetItems(), "company_unit_id");

        return array_reverse($result);
    }

    /**
     * Returns all active company_unit_id's
     *
     * @param bool $archive if true, return NOT active company_unit_ids instead
     * @param integer $productID if not 0, search only for comany units with booked product
     * @param bool $date date for check contacts
     * @param bool $excludeFutureContracts exclude future contracts
     * @return array of company_unit_id's
     */
    public static function GetActiveCompanyUnitIDs(
        $archive = false,
        $productID = 0,
        $date = null,
        $excludeFutureContracts = false
    )
    {
        $where = array();
        $where[] = $archive ? "archive = 'Y'" : "archive != 'Y'";

        if (intval($productID) > 0) {
            $contractList = new ContractList("company");
            $contractList->LoadActiveContractListByProductID(
                OPTION_LEVEL_COMPANY_UNIT,
                $productID,
                $date,
                $excludeFutureContracts
            );
            if ($contractList->GetCountItems() > 0) {
                $where[] = "company_unit_id IN(" . implode(
                    ", ",
                    array_column($contractList->GetItems(), "company_unit_id")
                ) . ")";
            }
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT company_unit_id FROM company_unit " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "");

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Returns array of company_unit_id's which are direct children of selected one
     *
     * @param int $id company_unit_id of parent
     *
     * @return array result company_unit_id's of direct children
     */
    public static function GetDirectChildrenIDs($id)
    {
        $stmt = GetStatement();
        $query = "SELECT company_unit_id FROM company_unit WHERE parent_unit_id=" . intval($id);

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Adds all child company_unit_id for every company_unit_id of input array
     *
     * @param array $ids source company_unit_id's
     *
     * @return array result company_unit_id's
     */
    public static function AddChildIDs($ids)
    {
        $stmt = GetStatement();
        $childIDs = array();
        foreach ($ids as $id) {
            $query = "WITH RECURSIVE u AS (
						SELECT company_unit_id FROM company_unit WHERE company_unit_id=" . intval($id) . "

						UNION ALL

						SELECT c.company_unit_id
						FROM company_unit AS c
							JOIN u ON c.parent_unit_id = u.company_unit_id
					)
				SELECT u.* FROM u";
            $childIDs = array_merge($childIDs, array_keys($stmt->FetchIndexedList($query)));
        }

        return array_unique($childIDs);
    }

    /**
     * Removes all company_unit_id from array if their parent_unit_id's exist in input array
     *
     * @param array $ids source company_unit_id's
     *
     * @return array result company_unit_id's
     */
    public static function RemoveChildIDs($ids)
    {
        $stmt = GetStatement();
        $childIDs = array();
        foreach ($ids as $id) {
            $query = "WITH RECURSIVE u AS (
						SELECT company_unit_id, parent_unit_id FROM company_unit WHERE parent_unit_id=" . intval($id) . "

						UNION ALL

						SELECT c.company_unit_id, c.parent_unit_id
						FROM company_unit AS c
							JOIN u ON c.parent_unit_id = u.company_unit_id
					)
				SELECT u.* FROM u ";
            $childIDs = array_merge($childIDs, array_keys($stmt->FetchIndexedList($query)));
        }

        return array_values(array_diff($ids, $childIDs));
    }

    /**
     * Removes all company_unit_id from array if eny of their child company_unit_id's exist in input array
     *
     * @param array $ids source company_unit_id's
     *
     * @return array result company_unit_id's
     */
    public static function RemoveParentIDs($ids)
    {
        $stmt = GetStatement();
        $parentIDs = array();
        foreach ($ids as $id) {
            $query = "WITH RECURSIVE u AS (
						SELECT company_unit_id, parent_unit_id FROM company_unit WHERE company_unit_id=" . intval($id) . "

						UNION ALL

						SELECT c.company_unit_id, c.parent_unit_id
						FROM company_unit AS c
							JOIN u ON c.company_unit_id = u.parent_unit_id
					)
				SELECT u.* FROM u WHERE u.company_unit_id != " . intval($id);
            $parentIDs = array_merge($parentIDs, array_keys($stmt->FetchIndexedList($query)));
        }

        return array_values(array_diff($ids, $parentIDs));
    }

    public static function GetCompanyUnitIDsForInvoiceCreation($date, $typeOfInvoice)
    {
        $result = array();

        $stmt = GetStatement();
        $where = array();
        $where[] = "parent_unit_id IS NULL";
        $where[] = "archive = 'N'";
        $where[] = "invoice_date=" . Connection::GetSQLString(date("j", strtotime($date)));

        $query = "SELECT company_unit_id FROM company_unit " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "") . " ORDER BY " . Connection::GetSQLDecryption("title") . " ASC";

        $companyUnitIDs = array_keys($stmt->FetchIndexedList($query));

        foreach ($companyUnitIDs as $companyUnitID) {
            if (Invoice::InvoiceExists($companyUnitID, $date, $typeOfInvoice)) {
                continue;
            }

            $result[] = $companyUnitID;
        }

        return $result;
    }

    /**
     * Returns array of company_unit_id's for payroll creation
     *
     * @param string $payrollDate date of payroll Y-m-d
     *
     * @return array result company_unit_id's
     */
    public static function GetCompanyUnitIDsForPayrollCreation($payrollDate)
    {
        $companyUnitIDs = array();
        $result = array();

        $date = date_create($payrollDate);
        $day = $date->format("j");
        $dayTomorrow = $date->modify("+ 1 day")->format("j");
        if ($dayTomorrow == 1) {
            $allowableFinancialDates = range($day, 31);
        } else {
            $allowableFinancialDates = [$day];
        }
        if ($date) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT company_unit_id FROM company_unit 
                WHERE financial_statement_date IN ('" . implode("', '", $allowableFinancialDates) . "')";
            $companyUnits = $stmt->FetchList($query);

            if ($companyUnits) {
                $companyUnitIDs = array_column($companyUnits, "company_unit_id");
            }
        }

        if (count($companyUnitIDs) > 0) {
            $payrollCurrentMonth = $date;
            $payrollLastMonth = date_create(date($payrollDate))
                ->modify("first day of this month")
                ->modify("-1 month");

            $contract = new Contract("product");
            $baseMainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);

            foreach ($companyUnitIDs as $companyUnitID) {
                $payrollMonth = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "current_month"
                    ? $payrollCurrentMonth
                    : $payrollLastMonth;

                $archiveHistory = CompanyUnit::GetPropertyValueListCompanyUnit("archive", $companyUnitID);

                for ($i = 1; $i <= $payrollMonth->format("t"); $i++) {
                    $d = $i > 9 ? $i : "0" . $i;
                    $day = $payrollMonth->format("Y-m-" . $d);
                    $archive = "N";
                    $minDiff = PHP_INT_MAX;

                    foreach ($archiveHistory as $item) {
                        if (strtotime($item["created"]) >= strtotime($day)) {
                            continue;
                        }

                        if (strtotime($day) - strtotime($item["created"]) >= $minDiff) {
                            continue;
                        }

                        $archive = $item["value"];
                        $minDiff = strtotime($day) - strtotime($item["created"]);
                    }

                    if ($archive != "N") {
                        continue;
                    }

                    if (
                        $contract->ContractExist(
                            OPTION_LEVEL_COMPANY_UNIT,
                            $baseMainProductID,
                            $companyUnitID,
                            $day
                        )
                    ) {
                        $result[] = $companyUnitID;
                        break;
                    }
                }
            }
        }

        return $result;
    }

    public static function GetCompanyUnitDataForStoredDataCreation($storedDataDate, $companyUnitId = null)
    {
        $payrollDate = date_create($storedDataDate)->modify("-1 day");
        $timestamp = strtotime($storedDataDate);
        $month = date("n", $timestamp);

        if ($payrollDate) {
            $stmt = GetStatement(DB_MAIN);

            if ($companyUnitId) {
                $query = "SELECT company_unit_id, payroll_month FROM company_unit 
                WHERE company_unit_id=" . Connection::GetSQLString($companyUnitId);
            } else {
                $query = "SELECT company_unit_id, payroll_month FROM company_unit 
                WHERE financial_statement_date=" . Connection::GetSQLString($payrollDate->format("j"));
            }

            $companyUnits = $stmt->FetchList($query);
        }

        $frequenciesForLastPayrollMonth = [];
        if ($month == 1) {
            $frequenciesForLastPayrollMonth = ['monthly', 'quarterly', 'yearly'];
        } elseif (in_array($month, [4, 7, 10])) {
            $frequenciesForLastPayrollMonth = ['monthly', 'quarterly'];
        } else {
            $frequenciesForLastPayrollMonth = ['monthly'];
        }

        $frequenciesForCurrentPayrollMonth = [];
        if ($month == 12) {
            $frequenciesForCurrentPayrollMonth = ['monthly', 'quarterly', 'yearly'];
        } elseif (in_array($month, [3, 6, 9])) {
            $frequenciesForCurrentPayrollMonth = ['monthly', 'quarterly'];
        } else {
            $frequenciesForCurrentPayrollMonth = ['monthly'];
        }

        $result = [];
        foreach ($companyUnits as $companyUnit) {
            $contract = new Contract('product');
            if (!$companyUnitId &&
                !$contract->LoadLatestActiveContract(
                    OPTION_LEVEL_COMPANY_UNIT,
                    $companyUnit["company_unit_id"],
                    Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN)
                )
            ) {
                continue;
            }

            $frequency = Option::GetInheritableOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                OPTION__STORED_DATA__MAIN__FREQUENCY,
                $companyUnit["company_unit_id"],
                $storedDataDate
            );

            if (!$companyUnitId && !(
                (in_array($frequency, $frequenciesForLastPayrollMonth) && $companyUnit["payroll_month"] == "last_month") ||
                (in_array($frequency, $frequenciesForCurrentPayrollMonth) && $companyUnit["payroll_month"] == "current_month")
            )) {
                continue;
            }

            $employeeIDsForGeneral = [];
            $employeeIDsForIndividual = [];

            $employeeIDsByCompany = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnit["company_unit_id"]);
            $isAllEmployees = Option::GetInheritableOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                OPTION__STORED_DATA__MAIN__EMPLOYEES,
                $companyUnit["company_unit_id"],
                $storedDataDate
            );

            $contract = new Contract("product");
            if ($isAllEmployees == "Y") {
                $employeeIDs = $employeeIDsByCompany;
            } else {
                $employeeIDsWithContract = $contract->GetEmployeeIDsWithContractForDate(
                    Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN),
                    $storedDataDate
                );
                $employeeIDs = array_intersect($employeeIDsByCompany, $employeeIDsWithContract);
            }

            if ($employeeIDs) {
                $isIndividualArchives = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES,
                    $companyUnit["company_unit_id"],
                    $storedDataDate
                );
                if ($isIndividualArchives == "Y") {
                    $employeeIDsForIndividual = $employeeIDs;
                } else {
                    foreach ($employeeIDs as $employeeID) {
                        $isActiveContract = $contract->LoadLatestActiveContract(
                            OPTION_LEVEL_EMPLOYEE,
                            $employeeID,
                            Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN),
                            $companyUnitId == null
                        );
                        $isIndividualArchive = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__STORED_DATA__MAIN__INDIVIDUAL_FILES,
                            $employeeID,
                            $storedDataDate
                        );
                        if ($isActiveContract && $isIndividualArchive == "Y") {
                            $employeeIDsForIndividual[] = $employeeID;
                        } else {
                            $employeeIDsForGeneral[] = $employeeID;
                        }
                    }
                }
            }

            $result[] = [
                'company_unit_id' => $companyUnit["company_unit_id"],
                'payroll_month' => $companyUnit["payroll_month"],
                'frequency' => $frequency,
                'employees_for_general' => $employeeIDsForGeneral,
                'employees_for_individual' => $employeeIDsForIndividual
            ];
        }

        return $result;
    }

    /**
     * Returns company_id's of root companies with payroll day equals to the passed one
     *
     * @param int $day payroll day to filter company units
     *
     * @return array of company_unit_id's
     */
    public static function GetRootCompanyUnitIDsByPayrollDay($day)
    {
        $stmt = GetStatement();
        $where = array();
        $where[] = "financial_statement_date=" . Connection::GetSQLString($day);
        $where[] = "parent_unit_id IS NULL";

        $query = "SELECT company_unit_id FROM company_unit " . (count($where) > 0 ? " WHERE " . implode(
            " AND ",
            $where
        ) : "");
        $companyUnitList = $stmt->FetchList($query);

        return array_column($companyUnitList, "company_unit_id");
    }

    /**
     * NOT Removes company units from database by provided ids.
     * Just make the inactive.
     * If root company unit is inactive then its children will be inactivated too.
     *
     * @param array $ids array of company_unit_id's
     * @param string $createdFrom indicates removal via web_api
     * @param string $endDate removed date (default current)
     *
     * @return bool
     */
    public function Remove($ids, $createdFrom = "admin", $endDate = false)
    {
        if (is_array($ids) && count($ids) > 0) {
            $stmt = GetStatement();

            $childIDs = array();
            foreach ($ids as $id) {
                $query = "WITH RECURSIVE u AS (
						SELECT company_unit_id, parent_unit_id FROM company_unit WHERE parent_unit_id=" . intval($id) . "

						UNION ALL

						SELECT c.company_unit_id, c.parent_unit_id
						FROM company_unit AS c
							JOIN u ON c.parent_unit_id = u.company_unit_id
					)
				SELECT u.* FROM u ";
                $childIDs = array_merge($childIDs, array_keys($stmt->FetchIndexedList($query)));
            }
            $ids = array_merge($ids, $childIDs);
            if (!$ids) {
                $this->AddError("company-unit-list-incorrect-ids");

                return false;
            }

            $query = "UPDATE company_unit SET archive='Y' WHERE company_unit_id IN (" . implode(
                ", ",
                Connection::GetSQLArray($ids)
            ) . ")";
            $values = array();

            foreach ($ids as $id) {
                if ($createdFrom != "web_api") {
                    $user = $GLOBALS['user'] ?? SERVICE_USER_ID;
                    $values[] = "(" . $id . ",'archive','Y','" . GetCurrentDateTime() . "',
                        " . Connection::GetSQLString($user) . ",
                         " . Connection::GetSQLString($createdFrom) . ")";
                } else {
                    $user = new User();
                    $user->LoadBySession();
                    $values[] = "(" . $id . ",'archive','Y','" . GetCurrentDateTime() . "',
                        " . $user->GetIntProperty("user_id") . ",
                        " . Connection::GetSQLString($createdFrom) . ")";
                }
            }

            $endDate = !empty($endDate) && $endDate !== false ? $endDate : GetCurrentDate();

            if ($stmt->Execute($query)) {
                //deactivate services
                $companyUnit = new CompanyUnit($this->module);
                foreach ($ids as $companyUnitID) {
                    $companyUnit->EndByCron($companyUnitID, $endDate, true, false);
                }

                //Save history
                $stmt1 = GetStatement(DB_CONTROL);
                $query = "INSERT INTO company_unit_history (company_unit_id, property_name, value, created, user_id, created_from) VALUES" . implode(
                    ",",
                    $values
                );
                $stmt1->Execute($query);

                if ($stmt->GetAffectedRows() > 0) {
                    $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));

                    return true;
                }
            } else {
                $this->AddError("sql-error-removing");
            }
        } else {
            $this->AddError("company-unit-list-no-ids-provided");
        }

        return false;
    }

    /**
     * Revert operation of Remove company units by provided ids.
     *
     * If root company unit is activated then its children are activating too.
     *
     * @param array $ids array of company_unit_id's
     * @param int $userId user id, who activate company units
     *
     * @return int activated company units count
     */
    public function Activate($ids, $userId = null)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return 0;
        }

        $stmt = GetStatement();

        $childIDs = array();
        $parentIDs = array();
        foreach ($ids as $id) {
            $query = "WITH RECURSIVE u AS (
                    SELECT company_unit_id, parent_unit_id FROM company_unit WHERE parent_unit_id=" . intval($id) . "

                    UNION ALL

                    SELECT c.company_unit_id, c.parent_unit_id
                    FROM company_unit AS c
                        JOIN u ON c.parent_unit_id = u.company_unit_id
                )
            SELECT u.* FROM u ";
            $childIDs = array_merge($childIDs, array_keys($stmt->FetchIndexedList($query)));
        }
        foreach ($ids as $id) {
            $query = "WITH RECURSIVE u AS (
                    SELECT company_unit_id, parent_unit_id FROM company_unit WHERE company_unit_id=" . intval($id) . "

                    UNION ALL

                    SELECT c.company_unit_id, c.parent_unit_id
                    FROM company_unit AS c
                        JOIN u ON c.company_unit_id = u.parent_unit_id
                )
            SELECT u.* FROM u ";
            $parentIDs = array_merge($parentIDs, array_keys($stmt->FetchIndexedList($query)));
        }

        $ids = array_merge($parentIDs, $childIDs);
        if (!$ids) {
            return 0;
        }

        $user = new User();
        if ($userId != null) {
            $user->LoadByID($userId);
        } else {
            $user->LoadBySession();
        }

        $values = array();
        foreach ($ids as $id) {
            $values[] = "(" . $id . ",'archive','N','" . GetCurrentDateTime() . "'," . $user->GetIntProperty("user_id") . ")";
        }

        $query = "UPDATE company_unit SET archive='N' WHERE company_unit_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        if ($stmt->Execute($query)) {
            //Save history
            $stmt1 = GetStatement(DB_CONTROL);
            $query = "INSERT INTO company_unit_history (company_unit_id, property_name, value, created, user_id) VALUES" . implode(
                ",",
                $values
            );
            $stmt1->Execute($query);

            if ($stmt->GetAffectedRows() > 0) {
                $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
            }

            return count($ids);
        }

        $this->AddError("sql-error-activating");

        return 0;
    }

    private function LoadEmployeeCountStatistics($src)
    {
        $keysCompany = [];
        $stmt = GetStatement(DB_PERSONAL);

        $contract = new Contract('product');
        $employeeIDsWithActiveBaseContract = $contract->GetEmployeeIDsWithContractForDate(
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            date("Y-m-d")
        );
        $employeeIDsForSearch = array_flip($employeeIDsWithActiveBaseContract);

        foreach ($src as $key => &$item) {
            $keysCompany[$item['company_unit_id']] = $key;

            $item['employees_all'] = 0;
            $item['employees_used_mobile'] = 0;
            $item['employees_unused_mobile'] = 0;
            $item['employees_with_active_base_contract'] = 0;

            $query = "SELECT e.user_id, (e.uses_application = 'Y')::INT is_mobile, employee_id
				FROM employee AS e
    				JOIN user_info AS u ON u.user_id=e.user_id
				WHERE e.company_unit_id = " . intval($item['company_unit_id']) . "
					AND e.archive='N'";
            $result = $stmt->FetchList($query);
            $item['employees_all'] += count($result);

            foreach ($result as $stat) {
                if ($stat['is_mobile'] == 1) {
                    $item['employees_used_mobile'] += 1;
                } else {
                    $item['employees_unused_mobile'] += 1;
                }

                if (!isset($employeeIDsForSearch[$stat['employee_id']])) {
                    continue;
                }

                $item['employees_with_active_base_contract'] += 1;
            }
        }

        return $src;
    }

    /*
     * Load some info about Company Units as linear list, not tree.
     * Method is auxiliary.
     * @param LocalObject $request
     * @param boolean $fullList
     * */
    public function LoadCompanyUnitLinearList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();
        if ($request->GetProperty("FilterTitle")) {
            $where[] = Connection::GetSQLDecryption("u.title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
        }
        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "u.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "u.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }

        $user = new User();
        $user->LoadBySession();
        if ($request->GetProperty("FilterPartner")) {
            $stmt1 = GetStatement(DB_CONTROL);
            $query = "SELECT company_unit_id FROM partner_contract
                        WHERE partner_id=" . $request->GetIntProperty("FilterPartner") . "
                          AND end_date IS NULL";
            $partnerList = $stmt1->FetchList($query);
            if (!$partnerList) {
                $this->_items = array();

                return;
            }

            $where[] = "u.company_unit_id IN(" . implode(",", array_column($partnerList, "company_unit_id")) . ")";
        } elseif (!$user->Validate(array("company_unit"))) {
            $companyUnitIDs = $user->GetPermissionLinkIDs("company_unit");
            if (count($companyUnitIDs) <= 0) {
                return;
            }

            $where[] = "u.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
        }

        $query = "SELECT u.company_unit_id, " . Connection::GetSQLDecryption("u.title") . " AS title, u.created FROM company_unit u
                    " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->SetCurrentPage();
        $this->LoadFromSQL($query);

        $this->_items = $this->LoadEmployeeCountStatistics($this->_items);
    }

    /**
     * Loads a list of props for company units satisfying specified option values
     *
     * @param int $optionID option_id of option to filter
     * @param string $value value of option to filter
     * @param string $date date of option
     *
     * @return array|bool list of company_unit params or false on failure
     */
    public function LoadByOptionFilter($optionID, $value, $date = null, $optionOperation)
    {
        $option = new Option("product");
        $option->LoadByID($optionID);
        $value = $option->PrepareValueBeforeSave(null, $value, OPTION_LEVEL_COMPANY_UNIT);
        $date = date_create($date)->format('Y-m-d');

        $companyList = array();
        $companyUnitIDs = $this::GetActiveCompanyUnitIDs();

        foreach ($companyUnitIDs as $id) {
            $value = $option->PrepareValueBeforeSave(null, $value, OPTION_LEVEL_COMPANY_UNIT);
            $optionValue = Option::GetCurrentValue(OPTION_LEVEL_COMPANY_UNIT, $optionID, $id);
            if (!$optionValue) {
                $optionValue = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_GLOBAL,
                    $option->GetProperty("code"),
                    0,
                    $date
                );
            }

            if (OperationSwitch($optionValue, $value, $optionOperation)) {
                continue;
            }

            $companyList[] = array("company_unit_id" => $id);
        }

        return $companyList;
    }


    /**
     * Returns array of company_unit_id's filtering by passed date of contract start or end
     *
     * @param $date string date of contract start/end
     * @param $type string start_date or end_date
     *
     * @return array|bool|null
     */
    public function GetIDsByContractDate($date, $type, $productID, $optionOperation)
    {
        $where = $type == "start_date"
            ? "start_date" . $optionOperation . Connection::GetSQLDate($date)
            : "end_date" . $optionOperation . Connection::GetSQLDate($date);

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT company_unit_id FROM company_unit_contract WHERE " . $where . " AND product_id=" . $productID;

        return $stmt->FetchList($query);
    }

    /**
     * Returns array of changes of company units archive property
     *
     * @param $request
     *
     * @return array|bool|null
     */
    public static function GetArchivePropertyHistory($request, $prepare = false)
    {
        $where = array();
        if ($request->GetProperty("FilterCreatedFrom")) {
            $where[] = "DATE(created) >= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedFrom"));
        }
        if ($request->GetProperty("FilterCreatedTo")) {
            $where[] = "DATE(created) <= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedTo"));
        }
        if ($request->GetProperty("FilterArchive")) {
            $where[] = "property_name='archive' AND value=" . Connection::GetSQLString($request->GetProperty("FilterArchive"));
        }

        $archive = $request->GetProperty("FilterArchive") == 'Y';
        $companyUnitList = self::GetActiveCompanyUnitIDs($archive, $request->GetProperty("ProductID"));
        $where[] = "company_unit_id IN(" . implode(", ", $companyUnitList) . ")";

        $stmt = GetStatement(DB_CONTROL);
        /* $query = "SELECT h.company_unit_id, h.created, h.user_id
                        FROM company_unit_history AS h
                        ".(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." ORDER BY h.created DESC";*/

        $query = "SELECT * FROM
            (SELECT DISTINCT ON (company_unit_id) *
            FROM company_unit_history
            " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY company_unit_id, created DESC ) t
            ORDER BY created DESC";

        $historyList = $stmt->FetchList($query);

        if ($prepare) {
            for ($i = 0; $i < count($historyList); $i++) {
                $historyList[$i]["user_name"] = User::GetNameByID($historyList[$i]["user_id"]);
                $historyList[$i]["title"] = CompanyUnit::GetTitleByID($historyList[$i]["company_unit_id"]);
            }
        }

        return $historyList ?: null;
    }

    /**
     * Returns company units list for master data creation
     *
     * @param $type string type of master data (service or voucher)
     * @param $isNew boolean is master data for new or update employees
     *
     * @return array of employees
     */
    public static function GetCompanyUnitListForMasterDataCreation($type, $isNew)
    {
        $sepaColumn = "";

        switch ($type) {
            case "company_unit_service":
                $typeOfProduct = array(PRODUCT__BASE__MAIN);
                $exportColumn = "master_data_service_id";
                if ($isNew == "N") {
                    $exportColumnUpdate = "master_data_service_update_id";
                }
                break;
            case "company_unit_voucher":
                $typeOfProduct = array(PRODUCT__FOOD_VOUCHER__MAIN, PRODUCT__BENEFIT_VOUCHER__MAIN);
                $exportColumn = "master_data_voucher_id";
                if ($isNew == "N") {
                    $exportColumnUpdate = "master_data_voucher_update_id";
                }
                break;
            case "sepa_service":
                $typeOfProduct = array(PRODUCT__BASE__MAIN);
                $exportColumn = "master_data_sepa_service_id";
                $sepaColumn = "sepa_service";
                if ($isNew == "N") {
                    $exportColumnUpdate = "master_data_sepa_service_update_id";
                }
                break;
            case "sepa_voucher":
                $typeOfProduct = array(PRODUCT__FOOD_VOUCHER__MAIN, PRODUCT__BENEFIT_VOUCHER__MAIN);
                $exportColumn = "master_data_sepa_voucher_id";
                $sepaColumn = "sepa_voucher";
                if ($isNew == "N") {
                    $exportColumnUpdate = "master_data_sepa_voucher_update_id";
                }
                break;
        }

        $companyUnits = array();
        $sepaNumberWithAs = $sepaColumn != "" ? ", " . $sepaColumn . " AS sepa_number" : "";
        $sepaDateWithAs = $sepaColumn != "" ? ", " . $sepaColumn . "_date AS sepa_date_sign" : "";

        $where = $isNew == "Y"
            ? $exportColumn . " is NULL"
            : $exportColumnUpdate . " is NULL AND " . $exportColumn . " is NOT NULL";

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT company_unit_id, customer_guid, bank_details, " .
            Connection::GetSQLDecryption("title") . " AS title, " .
            Connection::GetSQLDecryption("iban") . " AS iban" .
            $sepaNumberWithAs .
            $sepaDateWithAs .
            " FROM company_unit WHERE " . $where;
        $companyUnits = $stmt->FetchList($query);

        $result = array();
        foreach ($companyUnits as $companyUnit) {
            $companyUnitContract = new Contract('product');

            foreach ($typeOfProduct as $code) {
                if (
                    !$companyUnitContract->LoadLatestActiveContract(
                        OPTION_LEVEL_COMPANY_UNIT,
                        $companyUnit["company_unit_id"],
                        Product::GetProductIDByCode($code)
                    )
                ) {
                    continue;
                }

                if ($sepaColumn != "") {
                    $propertyValueList = CompanyUnit::GetPropertyValueListCompanyUnit(
                        $sepaColumn,
                        $companyUnit["company_unit_id"]
                    );
                    $companyUnit["sepa_first_created"] = array_pop($propertyValueList)["created"];
                }
                $result[] = $companyUnit;
                break;
            }
        }

        return $result;
    }
}
