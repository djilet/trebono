<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class EmployeeList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function EmployeeList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "name_asc" => Connection::GetSQLDecryption("u.last_name") . " ASC, " . Connection::GetSQLDecryption("u.first_name") . " ASC",
            "name_desc" => Connection::GetSQLDecryption("u.last_name") . " DESC, " . Connection::GetSQLDecryption("u.first_name") . " DESC",
            "id_asc" => "e.employee_id ASC"
        ));
        $this->SetOrderBy("name_asc");
        $this->SetItemsOnPage(20);
    }

    /**
     * Loads employee list using filter params and curent user's acl
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *        <li><u>FilterCompanyTitle</u> - int - property for employee's root company_unit's title filtration</li>
     *        <li><u>FilterName</u> - string - property for employee name filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     * @param bool $appendProductGroupList if true appends active product group list for each employee
     */

    public function LoadEmployeeList($request, $fullList = false, $appendProductGroupList = false, $user = null)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();
        $whereOr = array();

        //check for employee permissions
        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }

        // filter by company unit id, title and permissions
        $existCompanyUnitFilter = false;
        $companyUnitIDs = [];
        if ($request->GetProperty("FilterCompanyUnitID")) {
            $companyUnitIDs[] = $request->GetIntProperty("FilterCompanyUnitID");
            $existCompanyUnitFilter = true;
        } else {
            if ($request->GetProperty("FilterCompanyTitle")) {
                $existCompanyUnitFilter = true;
                $stmt = GetStatement();
                $query = "SELECT company_unit_id FROM company_unit
						WHERE " . Connection::GetSQLDecryption("title") . "
						= " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterCompanyTitle"));
                $companyUnitIDsByTitle = array_keys($stmt->FetchIndexedList($query));
                if (count($companyUnitIDsByTitle) == 0) {
                    $query = "SELECT company_unit_id FROM company_unit
						WHERE " . Connection::GetSQLDecryption("title") . "
						~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterCompanyTitle"));
                    $companyUnitIDsByTitle = array_keys($stmt->FetchIndexedList($query));
                }
                if (count($companyUnitIDsByTitle) == 0) {
                    return;
                }
                $companyUnitIDs = $companyUnitIDsByTitle;
            }

            if (!$user->Validate(array("employee"))) {
                $existCompanyUnitFilter = true;
                $existEmployeeCompanyUnitId = false;
                if ($user->Validate(array("employee_view"))) {
                    $stmt = GetStatement(DB_PERSONAL);
                    $query = "SELECT employee_id FROM employee WHERE user_id=" . Connection::GetSQLString($user->GetProperty("user_id"));
                    $employeeOfUser = $stmt->FetchField($query);
                    $employeeCompanyUnitId = Employee::GetEmployeeField($employeeOfUser, "company_unit_id");
                    if (
                        !$request->GetProperty("FilterCompanyTitle") ||
                        ($request->GetProperty("FilterCompanyTitle") && in_array($employeeCompanyUnitId, $companyUnitIDsByTitle))
                    ) {
                        $existEmployeeCompanyUnitId = true;
                    }
                }

                if ($user->Validate(array("employee" => null))) {
                    $adminCompanyUnitIDs = $user->GetPermissionLinkIDs("employee");
                }

                if ($request->GetProperty("FilterCompanyTitle")) {
                    $companyUnitIDs = array_intersect($companyUnitIDs, $adminCompanyUnitIDs);
                } else {
                    $companyUnitIDs = $adminCompanyUnitIDs;
                }

                if (count($companyUnitIDs) == 0 && !$existEmployeeCompanyUnitId) {
                    return;
                }
            }
        }

        $employeeIDsByCompany = [];
        if ($existCompanyUnitFilter) {
            if (count($companyUnitIDs) > 0) {
                $where[] = "e.company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";
                foreach ($companyUnitIDs as $companyUnitID) {
                    $employeeIDsByCompany = array_merge($employeeIDsByCompany,
                        $this::GetEmployeeIDsByCompanyUnitID($companyUnitID));
                }
                if ($existEmployeeCompanyUnitId) {
                    $whereOr[] = "e.user_id=" . $user->GetProperty("user_id");
                }
            } elseif ($existEmployeeCompanyUnitId) {
                $where[] = "e.user_id=" . $user->GetProperty("user_id");
            }
        }

        //process filter params
        if ($request->GetProperty("FilterName")) {
            $where[] = "CONCAT(" . Connection::GetSQLDecryption("u.first_name") . ", ' ', " . Connection::GetSQLDecryption("u.last_name") . ") ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterName"));
        }

        if ($isApp = $request->GetProperty("FilterApplicationUsed")) {
            $where[] = "e.uses_application=" . Connection::GetSQLString($isApp);
        }

        if ($request->GetProperty("FilterCreatedFrom")) {
            $where[] = "DATE(u.created) >= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedFrom"));
        }

        if ($request->GetProperty("FilterCreatedTo")) {
            $where[] = "DATE(u.created) <= " . Connection::GetSQLDate($request->GetProperty("FilterCreatedTo"));
        }

        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "e.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "e.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }

        //filter booked service
        if ($request->GetProperty("FilterBookedModule")) {
            $contractList = new ContractList("product");
            $contractList->LoadActiveContractListByProductID(
                OPTION_LEVEL_EMPLOYEE,
                $request->GetProperty("FilterBookedModule")
            );
            $employeeIDs = array_column($contractList->GetItems(), "employee_id");

            if (count($employeeIDsByCompany) > 0) {
                $employeeIDs = array_intersect($employeeIDsByCompany, $employeeIDs);
            }

            $where[] = $contractList->GetCountItems() > 0
                ? "e.employee_id IN(" . implode(", ", $employeeIDs) . ")"
                : "e.employee_id IN(NULL)";
        }

        //filter fields
        if ($user->Validate(array("root"))) {
            $optionID = $request->GetProperty("FilterOption");
            $filterReceipt1 = $request->GetProperty("FilterOption") && (strpos(
                $optionID,
                "receipt_value"
            ) !== false || strpos($optionID, "available_units") !== false);
            $filterReceipt2 = $request->GetProperty("FilterProductReceiptSearch") && $request->GetProperty("FilterReceiptOptionValueOne") && $request->GetProperty("FilterReceiptOptionOperation") && $request->GetProperty("FilterReceiptOptionValueTwo");
            //try to narrow search
            if (($filterReceipt1 || $filterReceipt2) && !$request->GetProperty("FilterBookedModule")) {
                $contractList = new ContractList("product");
                if ($filterReceipt1) {
                    $contractList->LoadActiveContractListByProductID(
                        OPTION_LEVEL_EMPLOYEE,
                        $request->GetProperty("FilterOptionProduct")
                    );
                    $employeeIDs = array_column($contractList->GetItems(), "employee_id");

                    if (count($employeeIDsByCompany) > 0) {
                        $employeeIDs = array_intersect($employeeIDsByCompany, $employeeIDs);
                    }

                    $where[] = $contractList->GetCountItems() > 0
                        ? "e.employee_id IN(" . implode(", ", $employeeIDs) . ")"
                        : "e.employee_id IN(NULL)";
                }

                if ($filterReceipt2) {
                    $specificProductGroup = SpecificProductGroupFactory::Create($request->GetProperty("FilterProductReceiptSearch"));
                    $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
                    $contractList->LoadActiveContractListByProductID(OPTION_LEVEL_EMPLOYEE, $mainProductID);
                    $employeeIDs = array_column($contractList->GetItems(), "employee_id");

                    if (count($employeeIDsByCompany) > 0) {
                        $employeeIDs = array_intersect($employeeIDsByCompany, $employeeIDs);
                    }

                    $where[] = $contractList->GetCountItems() > 0
                        ? "e.employee_id IN(" . implode(", ", $employeeIDs) . ")"
                        : "e.employee_id IN(NULL)";
                }
            }

            if ($request->GetProperty("FilterOption")) {
                //filter for start and end date of contract
                if ($optionID == "start_date" || $optionID == "end_date") {
                    $whereField = $optionID == "start_date"
                        ? "start_date" . $request->GetProperty("FilterOptionOperation") . Connection::GetSQLDate($request->GetProperty("FilterOptionValueDatepicker"))
                        : "end_date" . $request->GetProperty("FilterOptionOperation") . Connection::GetSQLDate($request->GetProperty("FilterOptionValueDatepicker"));

                    $stmt = GetStatement(DB_CONTROL);
                    $query = "SELECT employee_id FROM employee_contract WHERE " . $whereField . " AND product_id = " . Connection::GetSQLString($request->GetProperty("FilterOptionProduct"));
                    $employeeIDs = array_keys($stmt->FetchIndexedList($query));

                    if (count($employeeIDsByCompany) > 0) {
                        $employeeIDs = array_intersect($employeeIDsByCompany, $employeeIDs);
                    }

                    $where[] = "e.employee_id IN(" . implode(", ", $employeeIDs) . ")";
                } //filter for rest of options
                elseif (!$filterReceipt1) {
                    $stmt = GetStatement(DB_PERSONAL);
                    $query = "SELECT e.employee_id
                    FROM employee AS e
                    JOIN user_info AS u ON u.user_id=e.user_id
					"
                        . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
                    $employeeIDs = array_keys($stmt->FetchIndexedList($query));

                    $option = new Option("product");
                    $option->LoadByID($optionID);

                    $removeIDs = array();
                    foreach ($employeeIDs as $id) {
                        $value = $option->PrepareValueBeforeSave(
                            null,
                            $request->GetProperty("FilterOptionValue"),
                            OPTION_LEVEL_EMPLOYEE
                        );
                        $optionValue = Option::GetCurrentValue(OPTION_LEVEL_EMPLOYEE, $optionID, $id);
                        if ($optionValue === null && $option->GetProperty("type") != OPTION_TYPE_FLAG) {
                            $optionValue = Option::GetInheritableOptionValue(
                                OPTION_LEVEL_EMPLOYEE,
                                $option->GetProperty("code"),
                                $id,
                                GetCurrentDate()
                            );
                        }

                        if (!OperationSwitch($optionValue, $value, $request->GetProperty("FilterOptionOperation"))) {
                            continue;
                        }

                        $removeIDs[] = $id;
                    }
                    if (count($removeIDs) > 0) {
                        $where[] = "e.employee_id NOT IN(" . implode(", ", $removeIDs) . ")";
                    }
                }
            }
        }

        //filter for receipt value
        if ($user->Validate(array("root")) && ($filterReceipt1 || $filterReceipt2)) {
            $this->SetItemsOnPage(0);
            $this->SetOrderBy('id_asc');

            $query = "SELECT e.employee_id
					FROM employee AS e
						JOIN user_info AS u ON u.user_id=e.user_id "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "")
                . (count($whereOr) > 0 ? " OR " . implode(" OR ", $whereOr) : "");
        } else {
            $query = "SELECT e.employee_id, e.user_id, e.company_unit_id, e.last_api_call,
						u.phone, u.email, u.last_login, e.archive, e.uses_application,
						" . Connection::GetSQLDecryption("first_name") . " AS first_name,
						" . Connection::GetSQLDecryption("last_name") . " AS last_name
					FROM employee AS e
						JOIN user_info AS u ON u.user_id=e.user_id "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "")
                . (count($whereOr) > 0 ? " OR " . implode(" OR ", $whereOr) : "");
        }

        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        //filter for receipt value [2]
        if ($user->Validate(array("root")) && ($filterReceipt1 || $filterReceipt2)) {
            $statistics = new Statistics();
            $employee = new Employee($this->module);
            $value = $request->GetProperty("FilterOptionValue");
            $removeIDs = array();

            $employeeFilterCount = EMPLOYEE_FILTER_COUNT;

            if ($this->GetCountItems() > $employeeFilterCount) {
                //remove employees which won't go through filter at all
                $this->_items = array_slice($this->_items, 0, $employeeFilterCount);
                $this->AddMessage(
                    "employee-filter-restriction",
                    $this->module,
                    ['employee_filter_count' => $employeeFilterCount]
                );
            }
            for ($i = 0; $i < $this->GetCountItems(); $i++) {
                $employee->SetProperty("employee_id", $this->_items[$i]["employee_id"]);

                if ($filterReceipt1) {
                    $data = $statistics->GetStatistics(
                        $employee,
                        false,
                        $request->GetProperty("FilterOptionProductGroup"),
                        [str_replace("receipt_value_", "", $request->GetProperty("FilterOption"))],
                        false,
                        $user
                    );
                    $optionValue = $data[str_replace("receipt_value_", "", $optionID)];

                    if (OperationSwitch($optionValue, $value, $request->GetProperty("FilterOptionOperation"))) {
                        $removeIDs[] = $i;
                    }
                }
                if (!$filterReceipt2) {
                    continue;
                }

                $data = $statistics->GetStatistics(
                    $employee,
                    false,
                    $request->GetProperty("FilterProductReceiptSearch"),
                    [
                        str_replace("receipt_value_", "", $request->GetProperty("FilterReceiptOptionValueOne")),
                        str_replace("receipt_value_", "", $request->GetProperty("FilterReceiptOptionValueTwo")),
                    ],
                    false,
                    $user
                );
                $optionValueOne = $data[str_replace(
                    "receipt_value_",
                    "",
                    $request->GetProperty("FilterReceiptOptionValueOne")
                )];
                $optionValueTwo = $data[str_replace(
                    "receipt_value_",
                    "",
                    $request->GetProperty("FilterReceiptOptionValueTwo")
                )];

                if (
                    !OperationSwitch(
                        $optionValueOne,
                        $optionValueTwo,
                        $request->GetProperty("FilterReceiptOptionOperation")
                    )
                ) {
                    continue;
                }

                $removeIDs[] = $i;
            }
            for ($i = count($removeIDs) - 1; $i >= 0; $i--) {
                $this->RemoveItem($removeIDs[$i]);
            }

            if (count($removeIDs) > 0) {
                if ($request->IsPropertySet("ItemsOnPage")) {
                    $this->SetItemsOnPage($request->GetProperty("ItemsOnPage"));
                } else {
                    $this->SetItemsOnPage(20);
                }

                $employeeIDs = array_column($this->_items, "employee_id");
                $this->SetOrderBy("name_asc");
                $query = "SELECT e.employee_id, e.user_id, e.company_unit_id, e.last_api_call,
						u.phone, u.email, u.last_login, e.archive, e.uses_application,
						" . Connection::GetSQLDecryption("first_name") . " AS first_name,
						" . Connection::GetSQLDecryption("last_name") . " AS last_name
					FROM employee AS e
						JOIN user_info AS u ON u.user_id=e.user_id
                    WHERE employee_id IN (" . implode(", ", $employeeIDs) . ")";
                $this->SetCurrentPage();
                $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
            } else {
                $this->_items = [];
            }

            $this->_SetCountTotalItems($this->GetCountItems());
            $this->_GeneratePaging();
        }

        $this->PrepareContentBeforeShow($appendProductGroupList);
    }

    /**
     * Returns all employee_id's
     *
     * @return array of employee_id's
     */
    public static function GetAllEmployeeIDs()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id FROM employee";

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Returns array of employee_id's filtering by passed date of contract start or end
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
        $query = "SELECT employee_id FROM employee_contract WHERE " . $where . " AND product_id=" . $productID;

        return $stmt->FetchList($query);
    }

    /**
     * Loads a list of props for employee satisfying specified option values
     *
     * @param int $optionID option_id of option to filter
     * @param string $value value of option to filter
     * @param string $date date of option
     * @param string $optionOperation operation in condition
     *
     * @return array|bool list of employee params or false on failure
     */
    public function LoadByOptionFilter($optionID, $value, $date = null, $optionOperation)
    {
        $option = new Option("product");
        $option->LoadByID($optionID);
        $value = $option->PrepareValueBeforeSave(null, $value, OPTION_LEVEL_EMPLOYEE);
        $date = date_create($date)->format('Y-m-d');

        $employeeList = array();
        $employeeIDs = $this::GetActiveEmployeeIDs(false, $option->GetProperty("product_id"));
        if (count($employeeIDs) > EMPLOYEE_FILTER_COUNT) {
            //remove employees which won't go through filter at all
            $employeeIDs = array_slice($employeeIDs, 0, EMPLOYEE_FILTER_COUNT);
            $this->AddMessage(
                "employee-filter-restriction-misc",
                $this->module,
                ['employee_filter_count' => EMPLOYEE_FILTER_COUNT]
            );
        }

        foreach ($employeeIDs as $id) {
            $value = $option->PrepareValueBeforeSave(null, $value, OPTION_LEVEL_EMPLOYEE);
            $optionValue = Option::GetCurrentValue(OPTION_LEVEL_EMPLOYEE, $optionID, $id);
            if (!$optionValue) {
                $optionValue = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $option->GetProperty("code"),
                    $id,
                    $date
                );
            }

            if (OperationSwitch($optionValue, $value, $optionOperation)) {
                continue;
            }

            $employeeList[] = array("employee_id" => $id);
        }

        return $employeeList;
    }

    /**
     * Returns all active employee_id's (not archive)
     *
     * @param bool $archive if true, return NOT active employee_ids instead
     * @param int $productID if not 0, search only for employees with booked product
     * @param int $company_unit_id company_unit_id
     * @param bool $excludeInterruption exclude employees with active interruption contract
     * @param bool $excludeContractCheck don't run contract check
     * @param bool $date date for check contacts
     * @param bool $excludeFutureContracts exclude future contracts
     *
     * @return array of employee_id's
     */
    public static function GetActiveEmployeeIDs(
        $archive = false,
        $productID = null,
        $company_unit_id = false,
        $excludeInterruption = false,
        $excludeContractCheck = false,
        $date = null,
        $excludeFutureContracts = false
    ) {
        $where = [];
        $where[] = $archive ? "archive = 'Y'" : "archive != 'Y'";

        if ($company_unit_id !== false) {
            $where[] = "company_unit_id = " . Connection::GetSQLString($company_unit_id);
        }

        if (!$excludeContractCheck) {
            $employeeIDs = [];

            $contractList = new ContractList("company");
            $contractList->LoadActiveContractListByProductID(
                OPTION_LEVEL_EMPLOYEE,
                Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
                $date,
                $excludeFutureContracts
            );
            if ($contractList->GetCountItems() > 0) {
                $employeeIDs = array_column($contractList->GetItems(), "employee_id");
            }

            if (intval($productID) > 0) {
                $contractList->LoadActiveContractListByProductID(
                    OPTION_LEVEL_EMPLOYEE,
                    $productID,
                    $date,
                    $excludeFutureContracts
                );
                if ($contractList->GetCountItems() > 0) {
                    $employeeIDs = array_intersect(
                        array_column($contractList->GetItems(), "employee_id"),
                        $employeeIDs
                    );
                }
            }

            $where[] = "employee_id IN(" . implode(", ", $employeeIDs) . ")";

            if ($excludeInterruption) {
                $contractList->LoadActiveContractListByProductID(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)
                );
                if ($contractList->GetCountItems() > 0) {
                    $where[] = "employee_id NOT IN(" . implode(
                        ", ",
                        array_column($contractList->GetItems(), "employee_id")
                    ) . ")";
                }
            }
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id FROM employee " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Returns array of employee_id's which are linked to selected company_unit
     *
     * @param int $id company_unit_id
     *
     * @return array result employee_id's
     */
    public static function GetEmployeeIDsByCompanyUnitID($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id FROM employee WHERE company_unit_id=" . intval($id);

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Returns array of employee_id's which are linked to selected company_unit or its children company units
     *
     * @param int $ids company_unit_id
     *
     * @return array result employee_id's
     */
    public static function GetFullHierarchyEmployeeIDsByCompanyUnitID($ids)
    {
        $ids = is_array($ids) ? $ids : array($ids);
        $stmt = GetStatement(DB_PERSONAL);
        $companyUnitIDs = CompanyUnitList::AddChildIDs($ids);
        $query = "SELECT employee_id FROM employee WHERE company_unit_id IN(" . implode(", ", $companyUnitIDs) . ")";

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Returns array of employees who have givve accesstoken
     *
     * @return array result employees
     */
    public static function GetEmployeeListGivveToken()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id, givve_access_token, givve_refresh_token FROM employee WHERE givve_access_token!='NULL' AND archive!='Y'";

        return $stmt->FetchList($query);
    }

    /**
     * Returns array of employees who only have givve login information in their info, but no token
     *
     * @return array result employees
     */
    public static function GetEmployeeListGivveLogin()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id, givve_login, givve_password FROM employee WHERE givve_login!='NULL' AND givve_password!='NULL' AND givve_access_token IS NULL AND archive!='Y'";

        return $stmt->FetchList($query);
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareContentBeforeShow($appendProductGroupList = false)
    {
        $stmt = GetStatement();
        if ($this->GetCountItems() > 0) {
            $query = "SELECT u.company_unit_id AS company_unit_id, " . Connection::GetSQLDecryption("u.title") . " AS company_unit_title,
                    		c.company_unit_id AS root_company_unit_id, " . Connection::GetSQLDecryption("c.title") . " AS root_company_unit_title
                    	FROM company_unit AS u
                    		LEFT JOIN company_unit AS c ON c.company_id=u.company_id AND c.parent_unit_id IS NULL AND c.company_unit_id != u.company_unit_id
                    	WHERE u.company_unit_id IN (" . implode(
                                ",",
                                Connection::GetSQLArray(array_column($this->_items, "company_unit_id"))
                            ) . ")";
            $companyUnitList = $stmt->FetchList($query);
            for ($i = 0; $i < $this->GetCountItems(); $i++) {
                if (is_array($companyUnitList)) {
                    for ($j = 0; $j < count($companyUnitList); $j++) {
                        if ($this->_items[$i]["company_unit_id"] != $companyUnitList[$j]["company_unit_id"]) {
                            continue;
                        }

                        $this->_items[$i] = array_merge($this->_items[$i], $companyUnitList[$j]);
                    }
                }

                $this->_items[$i]["device_version_list"] = Device::GetDeviceVersionList($this->_items[$i]["user_id"]);
            }
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
            $contract = new Contract($moduleProduct);
            if (
                !$employeeActiveProductList = $contract->GetEmployeeActiveProductList(
                    $productIDs,
                    $this->_items[$i]["employee_id"]
                )
            ) {
                continue;
            }

            foreach ($employeeActiveProductList as &$employeeActiveProduct) {
                $employeeActiveProduct["title_translation"] = $productGroupMap[$employeeActiveProduct["product_id"]];
                $employeeActiveProduct["sort_order"] = $productGroupSortMap[$employeeActiveProduct["product_id"]];
            }
            array_multisort(
                array_column($employeeActiveProductList, "sort_order"),
                SORT_NUMERIC,
                $employeeActiveProductList
            );
            $this->_items[$i]["product_group_list"] = $employeeActiveProductList;
        }
    }

    /**
     * NOT Removes employees from database by provided ids.
     * Just make them inactive.
     *
     * @param array $ids array of employee_id's
     * @param string $createdFrom indicates if employees are deactivated using API or admin
     * @param string $endDate removed date (default current)
     *
     * @return bool
     */
    public function Remove($ids, $createdFrom = "admin", $endDate = false)
    {
        if (is_array($ids) && count($ids) > 0) {
            $stmt = GetStatement(DB_PERSONAL);

            $userIDs = array_keys($stmt->FetchIndexedList("SELECT user_id FROM employee WHERE employee_id IN (" . implode(
                ", ",
                Connection::GetSQLArray($ids)
            ) . ")"));

            //remove users
            $user = new User();
            $user->LoadBySession();

            $userRequest = new LocalObject();
            $userRequest->SetProperty("UserIDs", $userIDs);
            $userRequest->SetProperty("CurrentUserID", $user->GetProperty("user_id"));
            $userRequest->SetProperty("CreatedFrom", $createdFrom);

            $userList = new UserList();

            $endDate = !empty($endDate) && $endDate !== false ? $endDate : GetCurrentDate();

            if ($userList->Remove($userRequest)) {
                $employee = new Employee($this->module);
                foreach ($ids as $employeeID) {
                    $employee->EndByCron($employeeID, $endDate, true, false);
                }
                $this->LoadMessagesFromObject($userList);

                return true;
            }

            $this->AddError("employee-list-remove-error", $this->module);
        } else {
            $this->AddError("employee-list-no-ids-provided", $this->module);
        }

        return false;
    }

    /**
     * Revert the operation Remove of employees.
     *
     * @param array $ids array of employee_id's
     * @param int $userId user id, who activate employee
     *
     * @return int activated employees count
     */
    public function Activate($ids, $userId = null)
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return 0;
        }

        $stmt = GetStatement(DB_PERSONAL);

        $userIDs = array_keys($stmt->FetchIndexedList("SELECT user_id FROM employee WHERE employee_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")"));

        $user = new User();
        if ($userId != null) {
            $user->LoadByID($userId);
        } else {
            $user->LoadBySession();
        }

        $userRequest = new LocalObject();
        $userRequest->SetProperty("UserIDs", $userIDs);
        $userRequest->SetProperty("CurrentUserID", $user->GetProperty("user_id"));

        $userList = new UserList();
        $activatedUsersCount = $userList->Activate($userRequest);

        $this->LoadMessagesFromObject($userList);

        return $activatedUsersCount;
    }

    /**
     * Returns array of changes of employees archive property
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
        $employeeList = self::GetActiveEmployeeIDs(
            $archive,
            $request->GetProperty("ProductID"),
            false,
            false,
            true
        );
        $where[] = "employee_id IN(" . implode(", ", $employeeList) . ")";

        $stmt = GetStatement(DB_CONTROL);
        /*$query = "SELECT h.employee_id, h.created, h.user_id
                        FROM employee_history AS h
                        ".(count($where) > 0 ? " WHERE ".implode(" AND ", $where) : "")." ORDER BY h.created DESC";*/
        $query = "SELECT * FROM
            (SELECT DISTINCT ON (employee_id) *
            FROM employee_history
            " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY employee_id, created DESC ) t
            ORDER BY created DESC";

        $historyList = $stmt->FetchList($query);

        if ($request->GetProperty("FilterArchive") == "N") {
            //add new employee, which were add with future date in base module
            $baseMainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
            $contractList = ContractList::GetContractsCreatedByDate(
                "employee",
                $baseMainProductID,
                $request->GetProperty("FilterCreatedFrom"),
                $request->GetProperty("FilterCreatedTo")
            );

            foreach ($contractList as $contract) {
                if (in_array($contract["employee_id"], array_column($historyList, "employee_id"))) {
                    continue;
                }

                $historyList[] = $contract;
            }
        }
        if ($prepare) {
            for ($i = 0; $i < count($historyList); $i++) {
                $historyList[$i]["employee_name"] = Employee::GetNameByID($historyList[$i]["employee_id"]);
                $historyList[$i]["user_name"] = User::GetNameByID($historyList[$i]["user_id"]);
                $historyList[$i]["company_unit_id"] = Employee::GetEmployeeField(
                    $historyList[$i]["employee_id"],
                    "company_unit_id"
                );
                $historyList[$i]["title"] = CompanyUnit::GetTitleByID($historyList[$i]["company_unit_id"]);
            }
        }

        return $historyList ?: null;
    }

    /**
     * Send push notifications for employees or email
     *
     * @param string $template notification text
     * @param array $employeeIDs array of employee_id's
     * @param bool $isPush determines whenever push or email must be send
     * @param string $subject email subject
     */
    public static function SendMessageForEmployees(
        $template,
        $employeeIDs = array(),
        $isPush = true,
        $versionList = array(),
        $subject = ""
    ) {
        if (count($employeeIDs) == 0 || !is_array($employeeIDs)) {
            $employeeIDs = EmployeeList::GetActiveEmployeeIDs();
        }

        foreach ($employeeIDs as $employeeID) {
            $employee = new Employee("company");
            $employee->LoadByID($employeeID);

            //if project is local or placed on meshcloud test environment then send pushed only to predefined users
            if (IsLocalEnvironment() || IsTestEnvironment()) {
                $emailList = array("t.stein@2kscs.de", "j.klingler@3kglobaltrading.com", "test@employee.com");
                $email = trim(mb_strtolower($employee->GetProperty("email"), "utf-8"));

                if (!in_array($email, $emailList)) {
                    continue;
                }
            }

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

            $replacementsTmp = $employee->GetReplacementsList();
            $replacements = $replacementsTmp["ValueList"];

            $replacementsTmp = $companyUnit->GetReplacementsList();
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

            $replacementsTmp = $companyUnit->GetReplacementsList();
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

            $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
            $replacementsTmp = $specificProductGroup->GetReplacementsList(
                $employee->GetProperty('employee_id'),
                GetCurrentDate(),
                false
            );
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

            $text = GetLanguage()->ReplacePairs($template, $replacements);
            $data = array(
                FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "product_group_list",
                "popup_message" => $text
            );

            if ($isPush) {
                Employee::SendPushNotification($employeeID, null, $text, $data, $versionList);
            } else {
                $employee->SendEmail($subject, $text);
            }
        }
    }

    /** Gets an array of employee IDs and device versions these employees have
     *
     * @param $companyUnitIDs array company_unit_id's
     *
     * @return array
     */
    public static function GetEmployeeListByCompanyUnitIDs($companyUnitIDs)
    {
        //if company unit list is empty, get all employees
        $where = array();
        if (is_array($companyUnitIDs) && count($companyUnitIDs) > 0) {
            $companyUnitList = array_merge($companyUnitIDs, CompanyUnitList::AddChildIDs($companyUnitIDs));
            $where[] = " e.company_unit_id IN(" . implode(", ", $companyUnitList) . ")";
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT e.employee_id, e.user_id, e.company_unit_id, u.email,
                    " . Connection::GetSQLDecryption("u.first_name") . " AS first_name,
                    " . Connection::GetSQLDecryption("u.last_name") . " AS last_name
					  FROM employee AS e
                    JOIN user_info AS u ON u.user_id=e.user_id " . (count($where) > 0 ? " WHERE " . implode(
                        " AND ",
                        $where
                    ) : "");
        $employeeList = $stmt->FetchList($query);

        $tmpVersionList = array();
        foreach ($employeeList as $employee) {
            $tmpVersionList = array_merge($tmpVersionList, Device::GetDeviceVersionList($employee["user_id"]));
        }

        //want to leave only version number and client, for duplicate removal and sorting purposes
        $versionList = array();
        foreach ($tmpVersionList as $version) {
            if (in_array(array("version" => $version["version"], "client" => $version["client"]), $versionList)) {
                continue;
            }

            $versionList[] = array("version" => $version["version"], "client" => $version["client"]);
        }

        //sorting version list for more convenience
        $versionNumber = array_column($versionList, "version");
        $client = array_column($versionList, "client");
        array_multisort($versionNumber, SORT_DESC, $client, SORT_ASC, $versionList);

        return array("EmployeeList" => $employeeList, "VersionList" => $versionList);
    }

    /** Gets an array of employee IDs based on passed list of company untis and device versions
     *
     * @param $versionList array device versions
     * @param $companyUnitIDs array company_unit_id's
     * @param $versionOperation string
     *
     * @return array
     */
    public static function GetEmployeeListByVersionList($versionList, $companyUnitIDs, $versionOperation)
    {
        $employeeListByCompanyUnit = self::GetEmployeeListByCompanyUnitIDs($companyUnitIDs);
        $employeeListByCompanyUnit = $employeeListByCompanyUnit["EmployeeList"];
        $noResults = false;

        if (is_array($versionList) && count($versionList) > 0) {
            $noResults = true;
            if ($versionOperation !== "=") {
                $versionList = DeviceList::GetExtendedDeviceListByVersion($versionList, $versionOperation);
            }
        }

        if (!is_array($versionList) || count($versionList) == 0) {
            return array("EmployeeList" => $employeeListByCompanyUnit, "NoResults" => $noResults);
        }

        for ($i = 0; $i < count($employeeListByCompanyUnit); $i++) {
            $employeeListByCompanyUnit[$i]["device_version_list"] = Device::GetDeviceVersionList($employeeListByCompanyUnit[$i]["user_id"]);
        }

        $employeeIDs = array();
        $employeeList = array();
        foreach ($employeeListByCompanyUnit as $employee) {
            foreach ($employee["device_version_list"] as $deviceVersion) {
                foreach ($versionList as $version) {
                    $version = explode("-", $version);
                    if (
                        $version[0] != $deviceVersion["client"] || $version[1] != $deviceVersion["version"] || in_array(
                            $employee["employee_id"],
                            $employeeIDs
                        )
                    ) {
                        continue;
                    }

                    $employeeIDs[] = $employee["employee_id"];
                    $employeeList[] = $employee;
                }
            }
        }

        return array("EmployeeList" => $employeeList);
    }

    /**
     * Prepares and outputs excel file with info about employees emails
     *
     * @param $employee_ids array list of employee ids
     * @param $versionList array filter for employee list
     * @param $companyUnitIDs array filter for employee list
     * @param $versionOperation string filter for employee list
     */
    public static function ExportEmailList($employee_ids, $versionList, $companyUnitIDs, $versionOperation = "=")
    {
        if (is_array($employee_ids) && count($employee_ids) > 0) {
            $employee = new Employee("company");
            $employeeList = array();
            foreach ($employee_ids as $employee_id) {
                $employee->LoadByID($employee_id);
                $employeeList[] = $employee->GetProperties();
            }
        } else {
            $employeeList = self::GetEmployeeListByVersionList($versionList, $companyUnitIDs, $versionOperation);
            $employeeList = $employeeList["EmployeeList"];
        }

        $user = new User();
        $companyUnit = new CompanyUnit("company");

        for ($i = 0; $i < count($employeeList); $i++) {
            $user->LoadByID($employeeList[$i]["user_id"]);
            $employeeList[$i]["salutation"] = $user->GetProperty("salutation");

            $companyUnit->LoadByID($employeeList[$i]["company_unit_id"]);
            $employeeList[$i]["title"] = $companyUnit->GetProperty("title");
            $employeeList[$i]["street"] = $companyUnit->GetProperty("street");
            $employeeList[$i]["house"] = $companyUnit->GetProperty("house");
            $employeeList[$i]["zip_code"] = $companyUnit->GetProperty("zip_code");
            $employeeList[$i]["city"] = $companyUnit->GetProperty("city");
            $employeeList[$i]["country"] = $companyUnit->GetProperty("country");
        }

        //build email table header
        $emailTableHeader = explode(
            ";",
            "Salutation;First Name;Last Name;E-Mail;Company name;Street;Building Number;ZIP Code;City;Country"
        );

        //build email table body
        $emailTableBody = array();
        foreach ($employeeList as $employee) {
            $row = array(
                $employee["salutation"],
                $employee["first_name"],
                $employee["last_name"],
                $employee["email"],
                $employee["title"],
                $employee["street"],
                $employee["house"],
                $employee["zip_code"],
                $employee["city"],
                $employee["country"]
            );
            $emailTableBody[] = $row;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($emailTableHeader, null, "A1");
        $spreadsheet->getActiveSheet()->fromArray($emailTableBody, null, "A2");

        foreach (range('A', 'D') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        //save and output the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setSpreadsheet($spreadsheet);

        $filename = "email_export_" . date("Ymd") . ".xlsx";

        header("Cache-Control: max-age=0");
        header("Content-type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $tempFilePath = PROJECT_DIR . "var/log/email_export_" . date("U") . "_" . rand(100, 999) . ".xlsx";
        $writer->save($tempFilePath);
        echo mb_convert_encoding(utf8_encode(file_get_contents($tempFilePath)), "windows-1252", "utf-8");
        unlink($tempFilePath);

        exit();
    }

    /**
     * Returns all employees for master data creation
     *
     * @param $isNew boolean is master data for new or update employees
     *
     * @return array of employees
     */
    public static function GetEmployeeListForMasterDataCreation($isNew)
    {
        $where = $isNew == "Y"
            ? "master_data_export_id is NULL"
            : "master_data_export_update_id is NULL AND master_data_export_id is NOT NULL";

        $employees = array();
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id, creditor_number, " . Connection::GetSQLDecryption("iban") . " AS iban, bank_name FROM employee WHERE " . $where;
        $employees = $stmt->FetchList($query);

        $result = array();
        foreach ($employees as $employee) {
            $employeeContract = new Contract('product');
            $voucherProductCodes = array(PRODUCT__FOOD_VOUCHER__MAIN, PRODUCT__BENEFIT_VOUCHER__MAIN);
            foreach ($voucherProductCodes as $code) {
                if (
                    $employeeContract->LoadLatestActiveContract(
                        OPTION_LEVEL_EMPLOYEE,
                        $employee["employee_id"],
                        Product::GetProductIDByCode($code)
                    )
                ) {
                    $result[] = $employee;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Returns all employee_id's for generation vouchers
     *
     * @param int $productGroupID if not 0, search only for employees with booked product
     * @param int $company_unit_id company_unit_id
     *
     * @return array of employee_id's
     */
    public static function GetEmployeeIDsForGenerationVouchers($productGroupID = false, $companyUnitID = false)
    {
        $specificProductGroup = SpecificProductGroupFactory::Create($productGroupID);
        $productID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());
        $activeEmployeeIDs = EmployeeList::GetActiveEmployeeIDs(false, $productID, $companyUnitID, true);

        $result = array();

        if (count($activeEmployeeIDs) > 0) {
            $optionCode = $specificProductGroup->GetGenerationVoucherOptionCode();

            foreach ($activeEmployeeIDs as $employeeID) {
                $optionValue = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_EMPLOYEE,
                    $optionCode,
                    $employeeID,
                    GetCurrentDate()
                );
                if ($optionValue != "Y") {
                    continue;
                }

                $result[] = $employeeID;
            }
        }

        return $result;
    }
}
