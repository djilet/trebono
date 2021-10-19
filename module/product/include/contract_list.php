<?php

class ContractList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ContractList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;

        $this->SetSortOrderFields(array(
            "start_date_asc" => "start_date ASC, created ASC",
            "start_date_desc" => "start_date DESC, created DESC"
        ));
        $this->SetOrderBy("start_date_asc");
    }

    /**
     * Loads contact list of company_unit/employee for selected product
     * @param string $level OPTION_LEVEL_* constant means what entity's contracts should be loaded
     * @param int $entityID company_unit_id or employee_id of contracts owner
     * @param int $productID product which contracts should be loaded
     */
    public function LoadContractListByProductID($level, $entityID, $productID)
    {
        $table = null;
        $key = null;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract";
            $key = "company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract";
            $key = "employee_id";
        }

        $query = "SELECT contract_id, " . $key . ", product_id, created, start_date, end_date, start_user_id, end_user_id, end_date_created, start_from, end_from
					FROM " . $table . " 
					WHERE " . $key . "=" . intval($entityID) . " AND product_id=" . intval($productID);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();
    }

    public static function GetEmployeeContractListByProductID($entityID, $productID)
    {
        static $employeeContractListCacheMap;

        if (count($employeeContractListCacheMap) >= 1000) {
            $employeeContractListCacheMap = array();
        }

        if (is_array($employeeContractListCacheMap) && isset($employeeContractListCacheMap[$entityID][$productID])) {
            return $employeeContractListCacheMap[$entityID][$productID];
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT contract_id, product_id, created, start_date, end_date, start_user_id, end_user_id, end_date_created, start_from, end_from
					FROM employee_contract
					WHERE employee_id=" . intval($entityID) . " AND product_id=" . intval($productID);

        if (!($result = $stmt->FetchList($query))) {
            $result = array();
        }

        $employeeContractListCacheMap[$entityID][$productID] = $result;

        return $employeeContractListCacheMap[$entityID][$productID];
    }

    /**
     * Loads list of active contracts for all company_units/employees for selected product
     * @param string $level OPTION_LEVEL_* constant means what entity's contracts should be loaded
     * @param int $productID product which contracts should be loaded
     * @param string $date
     * @param bool $excludeFutureContracts exclude future contracts
     */
    public function LoadActiveContractListByProductID($level, $productID, $date = null, $excludeFutureContracts = false)
    {
        if ($date == null) {
            $date = date("Y-m-d");
        }

        $select = "c.contract_id, c.product_id, c.created, c.start_date, c.end_date, c.start_user_id, c.end_user_id, c.end_date_created";

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $select .= ", p.partner_id";
            $table = "company_unit_contract c LEFT JOIN partner_contract p 
                    ON c.company_unit_id=p.company_unit_id AND c.product_id=p.product_id AND p.start_date<='" . $date . "' AND (p.end_date>='" . $date . "' OR p.end_date IS NULL)";
            $key = "c.company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract c";
            $key = "c.employee_id";
        } else {
            exit();
        }

        $select .= ", " . $key;
        $where = array();
        $where[] = "c.product_id=" . intval($productID);
        $where[] = "(c.end_date>=" . Connection::GetSQLDate($date) . " OR c.end_date IS NULL)";

        if ($excludeFutureContracts) {
            $where[] = "c.start_date<=" . Connection::GetSQLDate($date);
        }

        $query = "SELECT " . $select . " 
					FROM " . $table . " 
					WHERE " . implode(" AND ", $where);

        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $stmt = GetStatement(DB_PERSONAL);

        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $startUserInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($this->_items[$i]["start_user_id"]));
            if ($startUserInfo) {
                $this->_items[$i]["start_first_name"] = $startUserInfo["first_name"];
                $this->_items[$i]["start_last_name"] = $startUserInfo["last_name"];
            }

            $endUserInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($this->_items[$i]["end_user_id"]));
            if ($endUserInfo) {
                $this->_items[$i]["end_first_name"] = $endUserInfo["first_name"];
                $this->_items[$i]["end_last_name"] = $endUserInfo["last_name"];
            }
        }
    }

    /**
     * Returns map of employee_id => dates by contracts were active in selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @param string $invoiceDate date of invoice for commission report
     * @return array employee_id => array of dates
     */
    public static function GetEmployeeContractDateList(
        $companyUnitID,
        $productID,
        $dateFrom,
        $dateTo,
        $invoiceDate = null
    ) {
        $employeeMapResult = array();
        //$employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        if (count($employeeIDs) > 0) {
            $contractListBaseModule = new ContractList("company");
            $contractListBaseModule->LoadActiveContractListByProductID(OPTION_LEVEL_EMPLOYEE,
                Product::GetProductIDByCode(PRODUCT__BASE__MAIN), $dateFrom);
            $employeeMapResult = self::GetEmployeeContractMapForDateList($employeeIDs,
                array_column($contractListBaseModule->GetItems(), "employee_id"), $productID, $dateFrom, $dateTo,
                $invoiceDate);

            //for stored data, if "employee option" == Y, we take all employees with active base module
            if ($productID == Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN)) {
                $employeeMapBaseModule = self::GetEmployeeContractMapForDateList($employeeIDs,
                    array_column($contractListBaseModule->GetItems(), "employee_id"),
                    Product::GetProductIDByCode(PRODUCT__BASE__MAIN), $dateFrom, $dateTo, $invoiceDate);

                $companyUnitContract = new Contract("product");
                $employeeMapStoredData = array();
                foreach ($employeeMapBaseModule as $employeeID => $dateList) {
                    foreach ($dateList as $date) {
                        if (!$companyUnitContract->LoadContractForDate(OPTION_LEVEL_COMPANY_UNIT, $companyUnitID,
                            $productID, $date)) {
                            continue;
                        }

                        if (Option::GetInheritableOptionValue(OPTION_LEVEL_COMPANY_UNIT,
                                OPTION__STORED_DATA__MAIN__EMPLOYEES, $companyUnitID, $date) == "N") {
                            if (!isset($employeeMapResult[$employeeID]) || !in_array($date,
                                    $employeeMapResult[$employeeID])) {
                                continue;
                            }
                        }

                        $employeeMapStoredData[$employeeID][] = $date;
                    }
                }
                $employeeMapResult = $employeeMapStoredData;
            }

        }
        return $employeeMapResult;
    }

    /**
     * Returns map of employee_id => dates by contracts were active in selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @param string $invoiceDate date of invoice for commission report
     * @return array employee_id => array of dates
     */
    public static function GetEmployeeInheritableContractDateList(
        $companyUnitID,
        $productID,
        $dateFrom,
        $dateTo,
        $invoiceDate = null
    ) {
        $employeeMap = array();

        //$employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        $contractList = new ContractList("company");
        $contractList->LoadActiveContractListByProductID(OPTION_LEVEL_EMPLOYEE,
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN), $dateFrom);

        //contracts inherit from parent unit ids
        $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($companyUnitID, true);

        if (count($employeeIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $where = array();
            $where[] = "c.product_id=" . intval($productID);
            $where[] = "c.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
            $where[] = "c.start_date <= " . Connection::GetSQLDate($dateTo);
            $where[] = "((c.end_date >= " . Connection::GetSQLDate($dateFrom) . " OR c.end_date IS NULL) AND (e.end_date >= " . Connection::GetSQLDate($dateFrom) . " OR e.end_date IS NULL))";

            $product = new Product("product");
            $product->LoadByID($productID);
            $productGroup = new ProductGroup("product");
            $productGroup->LoadByID($product->GetProperty("group_id"));
            $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));
            $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

            $whereJoin = array();
            if (($productID == Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN))) {
                $baseProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__BASE);
                $baseMainProductID = Product::GetProductIDByCode($baseProductGroup->GetMainProductCode());
                $whereJoin[] = "e.product_id=" . intval($baseMainProductID);
            } elseif (Product::IsProductInheritable($productID)) {
                $whereJoin[] = "e.product_id=" . intval($mainProductID);
            } else {
                $whereJoin[] = "e.product_id=" . intval($productID);
            }

            $whereJoin[] = "e.employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $whereJoin[] = "e.start_date <= " . Connection::GetSQLDate($dateTo);
            //map employees contracts to company contracts (dates have to be intersect)
            $whereJoin[] = "((e.start_date >= c.start_date AND (c.end_date IS NULL OR c.end_date >= e.start_date)) OR (c.start_date >= e.start_date AND (e.end_date IS NULL OR e.end_date >= c.start_date)))";
            if ($contractList->GetCountItems() > 0) {
                $whereJoin[] = "employee_id IN(" . implode(", ",
                        array_column($contractList->GetItems(), "employee_id")) . ")";
            }

            //select diatinct on employee contract to prevent double price for company and parent company
            $query = "SELECT DISTINCT ON (e.contract_id) e.employee_id, c.end_date as company_end_date, c.end_date_created as company_end_date_created,
	                        e.end_date as employee_end_date, e.end_date_created as employee_end_date_created,
							GREATEST(c.start_date, e.start_date, " . Connection::GetSQLDate($dateFrom) . ") AS intersection_from,
							LEAST(c.end_date, e.end_date, " . Connection::GetSQLDate($dateTo) . ") AS intersection_to
						FROM company_unit_contract AS c
                            JOIN employee_contract AS e ON " . implode(" AND ", $whereJoin) . "
						WHERE " . implode(" AND ", $where) . "
                    ORDER BY e.contract_id, c.start_date";
            $contractList = $stmt->FetchList($query);

            foreach ($contractList as $contract) {
                if ($invoiceDate > 0) {
                    $employeeEndDate = strtotime($contract["employee_end_date"]) < strtotime($contract["company_end_date"]) || is_null($contract["company_end_date"]);
                    $companyEndDate = strtotime($contract["company_end_date"]) < strtotime($contract["employee_end_date"]);
                    if (($employeeEndDate && strtotime($contract["employee_end_date_created"]) >= strtotime($invoiceDate)) ||
                        ($companyEndDate && strtotime($contract["company_end_date_created"]) >= strtotime($invoiceDate))) {
                        $contract["intersection_to"] = $dateTo;
                    }
                }

                $employeeID = $contract["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employeeMap[$employeeID] = array();
                }
                if ($productID !== Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN)) {
                    $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID],
                        GetDateRange($contract["intersection_from"], $contract["intersection_to"]));
                } else {
                    $dateList = array();
                    $date = $dateFrom;
                    do {
                        $contractCheck = new Contract("product");
                        if ((Option::GetInheritableOptionValue(OPTION_LEVEL_COMPANY_UNIT,
                                    OPTION__STORED_DATA__MAIN__EMPLOYEES, $companyUnitID, $date) == "Y" ||
                                $contractCheck->ContractExist(OPTION_LEVEL_EMPLOYEE,
                                    Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN), $employeeID, $date))
                            && $contractCheck->ContractExist(OPTION_LEVEL_EMPLOYEE,
                                Product::GetProductIDByCode(PRODUCT__BASE__MAIN), $employeeID, $date)) {
                            $dateList[] = $date;
                        }

                        $date = date("Y-m-d", strtotime($date . " +1 day"));
                    } while (strtotime($date) <= strtotime($dateTo));

                    if (count($dateList) > 0) {
                        $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID], $dateList);
                    } else {
                        unset($employeeMap[$employeeID]);
                    }
                }
            }

            return $employeeMap;
        } else {
            return array();
        }
    }

    /**
     * Returns map of employee_id => dates by contracts were created inside selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @return array dates of created contracts
     */
    public static function GetEmployeeContractCreatedCount($companyUnitID, $productID, $dateFrom, $dateTo)
    {
        $employeeMap = array();

        $allEmployees = false;
        $storedDataOptionDate = null;
        if ($productID == Product::GetProductIDByCode(PRODUCT__STORED_DATA__MAIN)) {
            //if option Employees was switched on this month, we need to charge employees fpr implementation fee
            $date = date("Y-m-d", strtotime($dateFrom . " - 1 day"));
            $storedDataOptionDate = $date;
            $previousValue = Option::GetInheritableOptionValue(OPTION_LEVEL_COMPANY_UNIT,
                OPTION__STORED_DATA__MAIN__EMPLOYEES, $companyUnitID, $date);
            do {
                $date = date("Y-m-d", strtotime($date . " + 1 day"));
                $value = Option::GetInheritableOptionValue(OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__STORED_DATA__MAIN__EMPLOYEES, $companyUnitID, $date);
                if ($value == "Y") {
                    $allEmployees = true;
                    if ($value != $previousValue) {
                        $storedDataOptionDate = $date;
                    }
                    break;
                }
                $previousValue = $value;
            } while (strtotime($date) < strtotime($dateTo));
        }

        //$employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        if (count($employeeIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $where = array();
            //if Employees option was turned on even for one day, charge all employees who have base module
            if ($allEmployees) {
                $baseProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__BASE);
                $baseMainProductID = Product::GetProductIDByCode($baseProductGroup->GetMainProductCode());
                $where[] = "product_id=" . intval($baseMainProductID);
                $where[] = "(end_date >=" . Connection::GetSQLDate($storedDataOptionDate) . " OR end_date IS NULL)"; //if base contract ended before switching option, don't charge employee
            } else {
                $where[] = "product_id=" . intval($productID);
                $where[] = "start_date >= " . Connection::GetSQLDate($dateFrom);
                $where[] = "start_date <= " . Connection::GetSQLDate($dateTo);
            }
            $where[] = "employee_id IN(" . implode(", ", $employeeIDs) . ")";

            $query = "SELECT employee_id, start_date FROM employee_contract	WHERE " . implode(" AND ", $where);
            $contractList = $stmt->FetchList($query);

            foreach ($contractList as $contract) {
                $employeeID = $contract["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employeeMap[$employeeID] = array();
                }
                if ($allEmployees) {
                    $contract = new Contract("product");
                    $contract->ContractExist(OPTION_LEVEL_EMPLOYEE, $productID, $employeeID, $storedDataOptionDate);
                    //if employee already payed impl. fee, don't include them
                    if ($contract->IsPropertySet("start_date") && strtotime($contract->GetProperty("start_date")) < strtotime($dateFrom)) {
                        unset($employeeMap[$employeeID]);
                    } else {
                        $where = array();
                        $where[] = "product_id =" . intval($productID);
                        $where[] = "start_date >= " . Connection::GetSQLDate($dateFrom);
                        $where[] = "start_date <= " . Connection::GetSQLDate($dateTo);
                        $where[] = "company_unit_id =" . Connection::GetSQLString($companyUnitID);

                        $query = "SELECT start_date FROM company_unit_contract	WHERE " . implode(" AND ", $where);
                        $companyContractDate = $stmt->FetchField($query);

                        $contract->ContractExist(OPTION_LEVEL_COMPANY_UNIT, $productID, $companyUnitID,
                            $storedDataOptionDate);

                        //we need to either include date of contract start or date of option change
                        if ($companyContractDate != false && $companyContractDate != null) {
                            $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID],
                                array($companyContractDate));
                        } elseif ($contract->IsPropertySet("start_date") && strtotime($contract->GetProperty("start_date")) < strtotime($storedDataOptionDate) && strtotime($storedDataOptionDate) >= strtotime($dateFrom)) {
                            $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID],
                                array($storedDataOptionDate));
                        } else {
                            unset($employeeMap[$employeeID]);
                        }
                    }
                } else {
                    $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID], array($contract["start_date"]));
                }
            }
            return $employeeMap;
        } else {
            return array();
        }
    }

    /**
     * Returns map of employee_id => dates by contracts were created inside selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @return array dates of created contracts
     */
    public static function GetEmployeeInheritableContractCreatedCount($companyUnitID, $productID, $dateFrom, $dateTo)
    {
        $employeeMap = array();

        //$employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        //contracts inherit from parent unit ids
        $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($companyUnitID, true);

        if (count($employeeIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $where = array();
            $where[] = "c.product_id=" . intval($productID);
            $where[] = "c.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
            $where[] = "c.start_date <= " . Connection::GetSQLDate($dateTo);
            //result contracts map start_date of employee contract or of company contract should be in invoice date list
            $where[] = "(c.start_date >= " . Connection::GetSQLDate($dateFrom) . " OR e.start_date >= " . Connection::GetSQLDate($dateFrom) . ")";

            $product = new Product("product");
            $product->LoadByID($productID);
            $productGroup = new ProductGroup("product");
            $productGroup->LoadByID($product->GetProperty("group_id"));
            $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));
            $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

            $whereJoin = array();
            $whereJoin[] = "e.product_id=" . intval($mainProductID);
            $whereJoin[] = "e.employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $whereJoin[] = "e.start_date <= " . Connection::GetSQLDate($dateTo);
            //map employees contracts to company contracts (dates have to be intersect)
            $whereJoin[] = "((e.start_date >= c.start_date AND (c.end_date IS NULL OR c.end_date >= e.start_date)) OR (c.start_date >= e.start_date AND (e.end_date IS NULL OR e.end_date >= c.start_date)))";

            //select diatinct on employee contract to prevent double price for company and parent company
            $query = "SELECT DISTINCT ON(e.contract_id) e.employee_id,
                        CASE
                          WHEN (e.start_date >= c.start_date AND (c.end_date IS NULL OR c.end_date >= e.start_date)) THEN e.start_date
                          WHEN (c.start_date >= e.start_date AND (e.end_date IS NULL OR e.end_date >= c.start_date)) THEN c.start_date
                         END AS start_date
                    FROM company_unit_contract AS c
                        JOIN employee_contract AS e ON " . implode(" AND ", $whereJoin) . "         
                     WHERE " . implode(" AND ", $where) . "
                    ORDER BY e.contract_id, c.start_date";

            $contractList = $stmt->FetchList($query);
            foreach ($contractList as $contract) {
                $employeeID = $contract["employee_id"];
                if (!isset($employeeMap[$employeeID])) {
                    $employeeMap[$employeeID] = array();
                }
                $employeeMap[$employeeID] = array_merge($employeeMap[$employeeID], array($contract["start_date"]));
            }
            return $employeeMap;
        } else {
            return array();
        }
    }

    /**
     * Returns min and max date by contracts were created inside selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @return string min date
     */
    public static function GetDateContractCreated($companyUnitID, $productID, $dateFrom, $dateTo)
    {
        $employeeMap = array();

        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);
        if (count($employeeIDs) > 0) {
            $stmt = GetStatement(DB_CONTROL);
            $where = array();
            $where[] = "product_id=" . intval($productID);
            $where[] = "employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $where[] = "start_date >= " . Connection::GetSQLDate($dateFrom);
            $where[] = "start_date <= " . Connection::GetSQLDate($dateTo);

            $query = "SELECT MIN(start_date) as date_from, MAX(start_date) as date_to FROM employee_contract	WHERE " . implode(" AND ",
                    $where);

            return $stmt->FetchRow($query);
        } else {
            return null;
        }
    }

    /**
     * Returns min and max date by contracts were created inside selected period of time
     * @param int $companyUnitID only employees of selected company_unit and its children will be counted
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @return string min date
     */
    public static function GetInheritableDateContractCreated($companyUnitID, $productID, $dateFrom, $dateTo)
    {
        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitID);

        $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($companyUnitID, true);

        $stmt = GetStatement(DB_CONTROL);
        $where = array();
        $where[] = "c.product_id=" . intval($productID);
        $where[] = "c.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
        $where[] = "c.start_date <= " . Connection::GetSQLDate($dateTo);
        $where[] = "(c.start_date >= " . Connection::GetSQLDate($dateFrom) . " OR e.start_date >= " . Connection::GetSQLDate($dateFrom) . ")";

        $product = new Product("product");
        $product->LoadByID($productID);
        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($product->GetProperty("group_id"));
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));
        $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

        $whereJoin = array();
        $whereJoin[] = "e.product_id=" . intval($mainProductID);
        $whereJoin[] = "e.employee_id IN(" . implode(", ", $employeeIDs) . ")";
        $whereJoin[] = "e.start_date <= " . Connection::GetSQLDate($dateTo);
        $whereJoin[] = "((e.start_date >= c.start_date AND (c.end_date IS NULL OR c.end_date >= e.start_date)) OR (c.start_date >= e.start_date AND (e.end_date IS NULL OR e.end_date >= c.start_date)))";

        $query = "SELECT MIN(start_date) as date_from, MAX(start_date) as date_to 
                    FROM(SELECT DISTINCT ON (e.contract_id) e.employee_id,
                            CASE
                              WHEN (e.start_date >= c.start_date AND (c.end_date IS NULL OR c.end_date >= e.start_date)) THEN e.start_date
                              WHEN (c.start_date >= e.start_date AND (e.end_date IS NULL OR e.end_date >= c.start_date)) THEN c.start_date
                             END AS start_date
                        FROM company_unit_contract AS c
                            JOIN employee_contract AS e ON " . implode(" AND ", $whereJoin) . "
                         WHERE " . implode(" AND ", $where) . "
                        ORDER BY e.contract_id, c.start_date) AS s";

        return $stmt->FetchRow($query);
    }

    /**
     * Returns property value history
     * @param string $level OPTION_LEVEL_* constant means what entity's contracts should be loaded
     * @param string $propertyName property name (ex. start_date)
     * @param int $entityID company_unit_id or employee_id of contracts owner
     * @param int $productID product which contracts should be loaded
     * @return array list of values
     */
    public static function GetPropertyValueList($level, $propertyName, $entityID, $productID)
    {
        $table = null;
        $key = null;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract";
            $key = "c.company_unit_id";
            $level = "company_unit";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract";
            $key = "c.employee_id";
            $level = "employee";
        }

        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT h.value_id, h.user_id, h.created, h.value, h.property_name, h.contract_id
                    FROM contract_history AS h
                    JOIN " . $table . " AS c ON h.contract_id = c.contract_id
					WHERE " . $key . "=" . intval($entityID) . " AND 
					    c.product_id=" . intval($productID) . " AND 
                        h.property_name=" . Connection::GetSQLString($propertyName) . " AND
                        h.level=" . Connection::GetSQLString($level) .
            "ORDER BY h.created DESC, h.value_id ASC";

        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, 
												" . Connection::GetSQLDecryption("last_name") . " AS last_name 
										FROM user_info 
										WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];
        }

        return $valueList;
    }

    /**
     * Returns employee IDs or company unit IDs by contracts were created inside selected period of time
     * @param int $level employee or company_unit
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @return array entity IDs and users IDs of created contracts
     */
    public static function GetContractsCreatedByDate($level, $productID, $dateFrom, $dateTo)
    {
        if ($level == "company_unit") {
            $table = "company_unit_contract";
            $column = "company_unit_id";
        } else {
            $table = "employee_contract";
            $column = "employee_id";
        }

        $stmt = GetStatement(DB_CONTROL);
        $where = array();
        $where[] = "product_id=" . intval($productID);
        $where[] = "start_date >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "start_date <= " . Connection::GetSQLDate($dateTo);

        $query = "SELECT " . $column . ", start_date AS created, start_user_id AS user_id FROM " . $table . " WHERE " . implode(" AND ",
                $where);
        $contractList = $stmt->FetchList($query);

        return $contractList ?: array();
    }

    /**
     * Returns contract map for employees were active in selected period of time
     * @param array $employeeIDsByCompany only employees of selected company_unit and its children will be counted
     * @param array $employeeIDsByProduct only employees of selected product
     * @param int $productID of product contracts should be linked for
     * @param string $dateFrom start of period
     * @param string $dateTo end of period
     * @param string $invoiceDate date of invoice for commission report
     * @return array employee_id => array of contracts
     */
    public static function GetEmployeeContractMapForDateList(
        $employeeIDsByCompany,
        $employeeIDsByProduct,
        $productID,
        $dateFrom,
        $dateTo,
        $invoiceDate
    ) {
        $stmt = GetStatement(DB_CONTROL);
        $where = array();
        $contractList = array();

        if ($employeeIDsByProduct > 0) {
            $where[] = "employee_id IN(" . implode(", ", $employeeIDsByProduct) . ")";
        }

        $where[] = "product_id=" . intval($productID);
        $where[] = "employee_id IN(" . implode(", ", $employeeIDsByCompany) . ")";
        $where[] = "start_date <= " . Connection::GetSQLDate($dateTo);
        $where[] = "(end_date >= " . Connection::GetSQLDate($dateFrom) . " OR end_date IS NULL)";

        $query = "SELECT employee_id, end_date_created,
                        GREATEST(start_date, " . Connection::GetSQLDate($dateFrom) . ") AS intersection_from, 
                        LEAST(end_date, " . Connection::GetSQLDate($dateTo) . ") AS intersection_to 
                    FROM employee_contract 
                    WHERE " . implode(" AND ", $where);
        $contractList = $stmt->FetchList($query);

        foreach ($contractList as $contract) {
            if ($invoiceDate > 0 && strtotime($contract["end_date_created"]) >= strtotime($invoiceDate)) {
                $contract["intersection_to"] = $dateTo;
            }

            $employeeID = $contract["employee_id"];
            if (!isset($employeeMap[$employeeID])) {
                $employeeMap[$employeeID] = array();
            }
            $employeeMap[$employeeID] = array_unique(array_merge($employeeMap[$employeeID],
                GetDateRange($contract["intersection_from"], $contract["intersection_to"])));
        }

        return $employeeMap;
    }
}
