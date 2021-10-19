<?php

/**
 * Implementation of [company_unit+product] and [employee+product] links
 */
class Contract extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contract properties to be loaded instantly
     */
    public function Contract($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads contract by its contract_id
     *
     * @param int $id contract_id
     * @param string $level OPTION_LEVEL_* constant means what is entity's contract
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id, $level)
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
        $query = "SELECT contract_id, " . $key . ", product_id, created, start_date, start_user_id, start_from, end_date, end_user_id, end_date_created, end_from
					FROM " . $table . "
					WHERE contract_id=" . intval($id);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        return $this->GetProperty("contract_id") ? true : false;
    }

    /**
     * Creates or updates contract. Object must be loaded from request before the method will be called.
     * Required properties are: company_unit_id or employee_id, product_id, start_date
     *
     * @return bool true if contract is created/updated successfully or false on failure
     */
    public function Save($level)
    {
        $user = new User();
        $user->LoadBySession();

        $stmt = GetStatement(DB_CONTROL);
        $table = null;
        $key = null;

        $newStartDate = false;
        $newEndDate = false;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract";
            $key = "company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract";
            $key = "employee_id";
        }

        if (!$this->IsPropertySet("start_from")) {
            $this->SetProperty("start_from", "admin");
        }
        if (!$this->IsPropertySet("end_from")) {
            $this->SetProperty("end_from", "admin");
        }

        if ($this->GetIntProperty("contract_id") > 0) {
            $contract = new Contract($this->module);
            $contract->LoadByID($this->GetIntProperty("contract_id"), $level);

            $endDateCreatedStr = "";
            $endUserIDStr = "";

            if ($this->GetProperty("end_date")) {
                //insert end date
                if (!$contract->GetProperty("end_date")) {
                    $newEndDate = true;
                    $endDateCreatedStr = ",end_date_created=" . Connection::GetSQLString(GetCurrentDateTime());
                    $endUserIDStr = ",end_user_id=" . $user->GetPropertyForSQL("user_id");
                } //update end date
                else {
                    if (strtotime($this->GetProperty("end_date")) != strtotime($contract->GetProperty("end_date"))) {
                        $newEndDate = true;
                    }
                }
            } else {
                //delete end date
                if ($contract->GetProperty("end_date")) {
                    $newEndDate = true;
                    $endDateCreatedStr = ",end_date_created=NULL";
                    $endUserIDStr = ",end_user_id=NULL";
                }
            }
            //update start date
            if (strtotime($this->GetProperty("start_date")) != strtotime($contract->GetProperty("start_date"))) {
                $newStartDate = true;
            }

            $query = "UPDATE " . $table . " SET
                        start_date=" . Connection::GetSQLDate($this->GetProperty("start_date")) . ",
                        end_date=" . Connection::GetSQLDate($this->GetProperty("end_date")) .
                $endUserIDStr .
                $endDateCreatedStr . "
                WHERE contract_id=" . $this->GetPropertyForSQL("contract_id");
        } else {
            $newStartDate = true;

            $endDateCreatedStr = $this->GetProperty("end_date") ? GetCurrentDateTime() : null;
            $endUserIDStr = $this->GetProperty("end_date") ? $user->GetIntProperty("user_id") : null;

            $query = "INSERT INTO " . $table . " (" . $key . ", product_id, created, start_date, start_user_id, start_from, end_date, end_user_id, end_date_created, end_from) VALUES (
                                " . $this->GetPropertyForSQL($key) . ",
                                " . $this->GetPropertyForSQL("product_id") . ",
                                " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                                " . Connection::GetSQLDate($this->GetProperty("start_date")) . ",
                                " . $user->GetPropertyForSQL("user_id") . ",
                                " . Connection::GetSQLString($this->GetProperty("start_from")) . ",
                                " . Connection::GetSQLDate($this->GetProperty("end_date")) . ",
                                " . Connection::GetSQLString($endUserIDStr) . ",
                                " . Connection::GetSQLString($endDateCreatedStr) . ",
                                " . Connection::GetSQLString($this->GetProperty("end_from")) . ")
                          RETURNING contract_id";
        }

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("contract_id") > 0) {
            $this->SetProperty("contract_id", $stmt->GetLastInsertID());
        }
        if ($newStartDate) {
            $this->SaveHistory(
                $level,
                "start_date",
                $this->GetProperty("start_date") ?? "",
                $user->GetIntProperty("user_id")
            );
        }
        if ($newEndDate) {
            $this->SaveHistory(
                $level,
                "end_date",
                $this->GetProperty("end_date") ?? "",
                $user->GetIntProperty("user_id")
            );
        }

        return true;
    }

    /**
     * Loads the latest active contract for selected company_unit/employee and product
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's contract should be loaded
     * @param int $entityID company_unit_id or employee_id of contract owner
     * @param int $productID product_id of contract's product
     * @param bool $onlyActive contract should be only active or not
     * @param bool $excludeFutureContracts exclude contracts with future start date
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function LoadLatestActiveContract(
        $level,
        $entityID,
        $productID,
        $onlyActive = true,
        $excludeFutureContracts = false
    ) {
        $table = null;
        $key = null;
        $select = "c.contract_id, c.created, c.product_id, c.start_date, c.end_date";
        $currentDate = Connection::GetSQLDateTime(GetCurrentDateTime());

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $select .= ", p.partner_id";
            $table = "company_unit_contract c LEFT JOIN partner_contract p
ON c.company_unit_id=p.company_unit_id AND c.product_id=p.product_id AND p.start_date<=" . $currentDate . " AND (p.end_date>=" . $currentDate . " OR p.end_date IS NULL)";
            $key = "c.company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract c";
            $key = "c.employee_id";
        }

        $where = array();
        $where[] = "c.product_id=" . intval($productID);
        $where[] = $key . "=" . intval($entityID);

        if ($onlyActive) {
            $where[] = "(c.end_date>=" . $currentDate . " OR c.end_date IS NULL)";
        }

        if ($excludeFutureContracts) {
            $where[] = "c.start_date<=" . $currentDate;
        }

        $query = "SELECT DISTINCT ON (" . $key . ") " . $key . ", " . $select . "
					FROM " . $table . "
					WHERE " . implode(" AND ", $where) . "
					ORDER BY " . $key . ", c.created DESC";
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        if ($this->GetProperty("contract_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Get the product list, count of employees and count of active employees with active contract for selected company_unit and product_id list
     *
     * @param int $companyUnitID company_unit_id
     * @param array $productIDs array of product_id
     * @param array $employeeIDs array of employee_id
     * @param array $activeEmployeeIDs array of employee_id
     *
     * @return array of product_id, count employee, count active employee group by product_id
     */
    public function GetCompanyUnitActiveProductList($companyUnitID, $productIDs, $employeeIDs, $activeEmployeeIDs)
    {
        $stmt = GetStatement(DB_CONTROL);

        $where = array();
        $where[] = "c.product_id IN (" . implode(", ", Connection::GetSQLArray($productIDs)) . ")";
        $where[] = "c.company_unit_id=" . intval($companyUnitID);
        $where[] = "(c.end_date>=" . Connection::GetSQLString(GetCurrentDate()) . " OR c.end_date IS NULL)";

        $query = "SELECT c.product_id, COUNT(ec.employee_id) employee_count, COUNT(ec2.employee_id) active_employee_count
					FROM company_unit_contract AS c

                    LEFT JOIN employee_contract AS ec
                    ON ec.product_id = c.product_id
                        AND ec.employee_id IN (" . (count($employeeIDs) > 0 ? implode(
            ", ",
            Connection::GetSQLArray($employeeIDs)
        ) : "'0'") . ")
                        AND (ec.end_date>=" . Connection::GetSQLString(GetCurrentDate()) . " OR ec.end_date IS NULL)

                    LEFT JOIN employee_contract AS ec2
                    ON ec2.product_id = c.product_id
                        AND ec2.employee_id = ec.employee_id
                        AND ec2.employee_id IN (" . (count($activeEmployeeIDs) > 0 ? implode(
                            ", ",
                            Connection::GetSQLArray($activeEmployeeIDs)
                        ) : "'0'") . ")
                        AND (ec2.end_date>=" . Connection::GetSQLString(GetCurrentDate()) . " OR ec2.end_date IS NULL)

					WHERE " . implode(" AND ", $where) . "
                    GROUP BY c.product_id";

        return $stmt->FetchList($query);
    }

    /**
     * Get the product group list with active contract for selected employee and product list
     *
     * @param array $productIDs array of product_id
     * @param int $employeeID employee_id
     *
     * @return array of product_id
     */
    public function GetEmployeeActiveProductList($productIDs, $employeeID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $where = array();
        $where[] = "e.employee_id=" . intval($employeeID);
        $where[] = "e.product_id IN (" . implode(", ", Connection::GetSQLArray($productIDs)) . ")";
        $where[] = "(e.end_date>=" . Connection::GetSQLString(GetCurrentDate()) . " OR e.end_date IS NULL)";

        $query = "SELECT e.product_id
					FROM employee_contract AS e
					WHERE " . implode(" AND ", $where) . "
                    GROUP BY e.product_id";

        return $stmt->FetchList($query);
    }

    /**
     * Loads the contract for selected company_unit/employee, product and date
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's contract should be loaded
     * @param int $entityID company_unit_id or employee_id of contract owner
     * @param int $productID product_id of contract's product
     * @param int $date date should be inside of contract period
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function LoadContractForDate($level, $entityID, $productID, $date)
    {
        $table = null;
        $key = null;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract AS c";
            $key = "c.company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract AS c";
            $key = "c.employee_id";
        }

        $where = array();
        $where[] = "c.product_id=" . intval($productID);
        $where[] = $key . "=" . intval($entityID);
        $where[] = "c.start_date <= " . Connection::GetSQLDate($date);
        $where[] = "(c.end_date IS NULL OR c.end_date >= " . Connection::GetSQLDate($date) . ")";

        $query = "SELECT c.contract_id, c.created, c.product_id, c.start_date, c.end_date
					FROM " . $table . "
					WHERE " . implode(" AND ", $where);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        if ($this->GetProperty("contract_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    public function GetEmployeeIDsWithContract($productID)
    {
        $where = array();
        $where[] = "product_id=" . intval($productID);

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT employee_id
					FROM employee_contract
					WHERE " . implode(" AND ", $where);

        return array_keys($stmt->FetchIndexedList($query));
    }

    public function GetEmployeeIDsWithContractForDate($productID, $date)
    {
        $where = array();
        $where[] = "product_id=" . intval($productID);
        $where[] = "start_date <= " . Connection::GetSQLDate($date);
        $where[] = "(end_date IS NULL OR end_date >= " . Connection::GetSQLDate($date) . ")";

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT employee_id
					FROM employee_contract
					WHERE " . implode(" AND ", $where);

        return array_keys($stmt->FetchIndexedList($query));
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareBeforeShow()
    {
        if (!$this->GetProperty("partner_id")) {
            return;
        }

        $stmt = GetStatement();
        $this->SetProperty(
            "Partner",
            $stmt->FetchField("SELECT title FROM partner WHERE \"PartnerID\"=" . $this->GetPropertyForSQL("partner_id"))
        );
    }

    /*
     * Creates new contract or closes existing when company_unit/employee 's option form is submitted
     * @param string $level OPTION_LEVEL_* constant means what entity's contract should be updated
     * @param int $productID product_id of contract's product
     * @param int $entityID company_unit_id or employee_id of contract owner
     * @param int $contractID contract_id
     * @param int $startDate start date from request
     * @param int $endDate end date from request
     */
    public function OnOptionUpdate($level, $productID, $entityID, $contractID, $startDate, $endDate, $isImport = false)
    {
        if (!$isImport) {
            $user = new User();
            $user->LoadBySession();
            $userID = $user->GetIntProperty("user_id");
        } else {
            $userID = AZ_IMPORT;
        }

        $stmt = GetStatement(DB_CONTROL);

        $table = null;
        $key = null;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract";
            $key = "company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract";
            $key = "employee_id";
        }

        if ($productID == null) {
            $this->AddError("contract-incorrect-product-id", $this->module);

            return false;
        }

        $product = new Product("product");
        $product->LoadByID($productID);
        $startDate = empty($startDate) ? null : $startDate;
        $endDate = empty($endDate) ? null : $endDate;
        $contract = new Contract($this->module);

        /*
        * Root can change or clear end date for all services, if end date < current date
        * Root can change start date of interruption service, so for this service should be another logic
        */

        //NOT INTERRUPTION SERVICE
        if ($productID != Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)) {
            // 1. Request has start date (and, may be, end date)-> try to create new contract or update contract
            if ($startDate) {
                $activeContract = new Contract($this->module);

                if (!$activeContract->LoadByID($contractID, $level)) {
                    $activeContract->LoadLatestActiveContract($level, $entityID, $productID);
                }

                // Exist active contract
                if ($activeContract->GetProperty("contract_id")) {
                    // Try to update contract
                    $activeContract->SetProperty("start_date", $startDate);

                    if ($endDate) {
                        //check start date should be < end date
                        if (strtotime($endDate) < strtotime($startDate)) {
                            $this->AddError("contract-enddate-is-less-than-startdate", $this->module);
                            $this->AddErrorField("Product[" . $productID . "][end_date]");

                            return false;
                        }
                        $activeContract->SetProperty("end_date", $endDate);
                    } else {
                        if ($activeContract->GetProperty("end_date")) {
                            $activeContract->SetProperty("end_date", null);
                        }
                    }

                    //check intersection with another contracts
                    if (
                        $contract->ContractExist(
                            $level,
                            $productID,
                            $entityID,
                            $startDate,
                            null,
                            $activeContract->GetProperty("contract_id")
                        )
                    ) {
                        $this->AddError(
                            "contract-intersection-found",
                            $this->module,
                            ['product' => $product->GetProperty("title_translation")]
                        );
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }

                    if (!$activeContract->Save($level)) {
                        return false;
                    }
                } // Don't exist active contract
                else {
                    //check intersection with another contracts
                    if ($contract->ContractExist($level, $productID, $entityID, $startDate)) {
                        $this->AddError(
                            "contract-intersection-found",
                            $this->module,
                            ['product' => $product->GetProperty("title_translation")]
                        );
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }

                    //new contracts shouldn't have intersection with interruption contract
                    $interruptionContract = new Contract($this->module);
                    if (
                        $interruptionContract->ContractExist(
                            $level,
                            Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION),
                            $entityID,
                            $startDate
                        )
                    ) {
                        $this->AddError(
                            "contract-intersection-with-interruption-found",
                            $this->module,
                            ['product' => $product->GetProperty("title_translation")]
                        );
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }

                    $contract = new Contract($this->module);
                    $contract->SetProperty($key, $entityID);
                    $contract->SetProperty("product_id", $productID);
                    $contract->SetProperty("start_date", $startDate);
                    $contract->SetProperty("end_date", $endDate);

                    if (!$contract->Save($level)) {
                        return false;
                    }

                    $voucherProductList = ProductList::GetVoucherProductList(true);
                    $productCodeList = array_column($voucherProductList, "code");
                    if (in_array(Product::GetProductCodeByID($productID), $productCodeList)) {
                        if (
                            $level == OPTION_LEVEL_EMPLOYEE && Employee::GetEmployeeField(
                                $entityID,
                                "creditor_number"
                            ) == null
                        ) {
                            Employee::SetCreditorNumber($entityID);
                        } elseif (
                            $level == OPTION_LEVEL_COMPANY_UNIT && CompanyUnit::GetPropertyValue(
                                "creditor_number",
                                $entityID
                            ) == null
                        ) {
                            CompanyUnit::SetCreditorNumber($entityID);
                        }
                    }
                }
            }

            // 2. Request has ONLY end date -> try insert or update end date of current active contract
            if (!$startDate && $endDate) {
                if (!$contract->LoadByID($contractID, $level)) {
                    if (!$contract->LoadLatestActiveContract($level, $entityID, $productID)) {
                        $this->AddError("start-date-is-empty", $this->module);
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }
                }

                if (strtotime($contract->GetProperty("end_date")) != strtotime($endDate)) {
                    if ($contract->GetProperty("end_date") && (!$user->Validate(array("root")) || $isImport)) {
                        $this->AddError('add-past-date-error', "product");
                        $this->AddErrorField("Product[" . $productID . "][end_date]");

                        return false;
                    }

                    //check start date should be < end date
                    if (strtotime($endDate) < strtotime($contract->GetProperty("start_date"))) {
                        $this->AddError("contract-enddate-is-less-than-startdate", $this->module);
                        $this->AddErrorField("Product[" . $productID . "][end_date]");

                        return false;
                    }

                    $contract->SetProperty("end_date", $endDate);

                    if (!$contract->Save($level)) {
                        return false;
                    }

                    //if it is base module, we should change end date for another services
                    if ($productID == Product::GetProductIDByCode(PRODUCT__BASE__MAIN)) {
                        $query = "SELECT contract_id FROM " . $table .
                            " WHERE (end_date > " . Connection::GetSQLDate($endDate) . " OR end_date is NULL) AND " . $key . "=" . intval($entityID);

                        $contractIDs = array_keys($stmt->FetchIndexedList($query));

                        foreach ($contractIDs as $contractID) {
                            $contract->LoadByID($contractID, $level);
                            $contract->SetProperty("end_date", $endDate);
                            $contract->Save($level);
                        }
                    }
                }
            }

            // 3. Request don't has dates -> try clear end date or delete current active contract
            if (!$startDate && !$endDate) {
                if (
                    $contract->LoadLatestActiveContract(
                        $level,
                        $entityID,
                        $productID
                    ) && $user->Validate(array("root"))
                ) {
                    $endDateContract = $contract->GetProperty("end_date");

                    if (strtotime($contract->GetProperty("start_date")) > strtotime(date("Y-m-d"))) {
                        if (!$contract->DeleteContract($contract->GetProperty("contract_id"), $level)) {
                            return false;
                        }
                    } else {
                        $contract->SetProperty("end_date", null);

                        if (!$contract->Save($level)) {
                            return false;
                        }

                        //if it is base module, we should clear end date for another services
                        if ($productID == Product::GetProductIDByCode(PRODUCT__BASE__MAIN)) {
                            $query = "SELECT contract_id FROM " . $table .
                                " WHERE end_date>=" . Connection::GetSQLDate($endDateContract) . " AND " . $key . "=" . intval($entityID);
                            $contractIDs = array_keys($stmt->FetchIndexedList($query));

                            foreach ($contractIDs as $contractID) {
                                $contract = new Contract($this->module);
                                $contract->LoadByID($contractID, $level);
                                $contract->SetProperty("end_date", null);
                                $contract->Save($level);
                            }
                        }
                    }
                }
            }
        } // INTERRUPTION SERVICE
        else {
            $activeContract = new Contract($this->module);

            if (!$activeContract->LoadByID($contractID, $level)) {
                $activeContract->LoadLatestActiveContract($level, $entityID, $productID);
            }

            // Exist active contract
            if ($activeContract->GetProperty("contract_id")) {
                // 1. Request has start date (and, may be, end date)-> try to update contract
                if ($startDate) {
                    if ($endDate) {
                        //check start date should be < end date
                        if (strtotime($endDate) < strtotime($startDate)) {
                            $this->AddError("contract-enddate-is-less-than-startdate", $this->module);
                            $this->AddErrorField("Product[" . $productID . "][end_date]");

                            return false;
                        }
                    }

                    //check intersection with another contracts
                    if (
                        $contract->ContractExist(
                            $level,
                            $productID,
                            $entityID,
                            $startDate,
                            null,
                            $activeContract->GetProperty("contract_id")
                        )
                    ) {
                        $this->AddError(
                            "contract-intersection-found",
                            $this->module,
                            ['product' => $product->GetProperty("title_translation")]
                        );
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }

                    $activeContract->SetProperty("start_date", $startDate);
                    $activeContract->SetProperty("end_date", $endDate);

                    if (!$activeContract->Save($level)) {
                        return false;
                    }
                }
                // 2. Request has ONLY end date
                if (!$startDate && $endDate) {
                    $this->AddError("start-date-is-empty", $this->module);
                    $this->AddErrorField("Product[" . $productID . "][start_date]");

                    return false;
                }
                // 3. Request has not dates
                if (!$startDate && !$endDate) {
                    if (!$contract->DeleteContract($activeContract->GetProperty("contract_id"), $level)) {
                        return false;
                    }
                }
            } // Don't exist active contract
            else {
                // 1. Request has start date (and, may be, end date)-> try to create new contract
                if ($startDate) {
                    if ($endDate) {
                        //check start date should be < end date
                        if (strtotime($endDate) < strtotime($startDate)) {
                            $this->AddError("contract-enddate-is-less-than-startdate", $this->module);
                            $this->AddErrorField("Product[" . $productID . "][end_date]");

                            return false;
                        }
                    }

                    //check intersection with another contracts
                    if ($contract->ContractExist($level, $productID, $entityID, $startDate)) {
                        $this->AddError(
                            "contract-intersection-found",
                            $this->module,
                            ['product' => $product->GetProperty("title_translation")]
                        );
                        $this->AddErrorField("Product[" . $productID . "][start_date]");

                        return false;
                    }

                    $contract = new Contract($this->module);
                    $contract->SetProperty($key, $entityID);
                    $contract->SetProperty("product_id", $productID);
                    $contract->SetProperty("start_date", $startDate);
                    $contract->SetProperty("end_date", $endDate);

                    if (!$contract->Save($level)) {
                        return false;
                    }
                }
                // 2. Request has ONLY end date
                if (!$startDate && $endDate) {
                    $this->AddError("start-date-is-empty", $this->module);
                    $this->AddErrorField("Product[" . $productID . "][start_date]");

                    return false;
                }
            }
        }

        return true;
    }


    /**
     * Checking the existence of a contract for a specific date
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's contract should be loaded
     * @param int $productID product_id of contract's product
     * @param int $entityID company_unit_id or employee_id of contract owner
     * @param string $date Date for verification
     * @param string $startDate
     * @param int $excludeContractID exclude contract
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function ContractExist($level, $productID, $entityID, $date, $startDate = null, $excludeContractID = null)
    {
        if (empty($date)) {
            return false;
        }

        $table = null;
        $key = null;

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $table = "company_unit_contract";
            $key = "company_unit_id";
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $table = "employee_contract";
            $key = "employee_id";
        }

        $where = array();
        $where[] = "product_id=" . intval($productID);
        $where[] = $key . "=" . intval($entityID);
        $where[] = $startDate
            ? "start_date <= " . Connection::GetSQLDate($startDate)
            : "start_date <= " . Connection::GetSQLDate($date);
        $where[] = "(end_date >=" . Connection::GetSQLDate($date) . " OR end_date IS NULL)";

        if ($excludeContractID) {
            $where[] = "contract_id <> " . intval($excludeContractID);
        }

        $query = "SELECT contract_id, created, product_id, start_date, end_date
					FROM " . $table . "
					WHERE " . implode(" AND ", $where) . "
					ORDER BY created DESC";
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        return $this->GetProperty("contract_id") ? true : false;
    }

    /**
     * Checking the existence of an active contract
     *
     * @param array $employeeIds
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function ExistsActiveForEmployees(array $employeeIds): bool
    {
        if (count($employeeIds) < 1) {
            throw new InvalidArgumentException("employeeIds must be not empty array");
        }

        $employeeIds = implode(", ", array_map(function ($employeeId) {
            return intval($employeeId);
        }, $employeeIds));

        $query = "SELECT contract_id
FROM employee_contract
WHERE employee_id IN ({$employeeIds})
AND (end_date >= CURRENT_DATE OR end_date IS NULL)
";

        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        return (bool) $this->GetProperty("contract_id");
    }

    /**
     * Checking the existence of inheritable (from company units) contract for a specific date
     *
     * @param int $productID product_id of contract's product
     * @param int $employeeID employee_id
     * @param string $date Date for verification
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function InheritableContractExist($productID, $employeeID, $date)
    {
        $employee = new Employee("company");
        $employee->LoadByID($employeeID);

        $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($employee->GetProperty("company_unit_id"), true);
        foreach ($companyUnitIDs as $companyUnitID) {
            $result = $this->ContractExist(OPTION_LEVEL_COMPANY_UNIT, $productID, $companyUnitID, $date);
            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns list of replacements
     *
     * @return array
     */
    public function GetReplacementsList()
    {
        $properties = array("start_date", "end_date");

        $language = GetLanguage();
        if ($this->IsPropertySet("start_date")) {
            $this->SetProperty(
                "start_date",
                date($language->GetDateFormat(), strtotime($this->GetProperty('start_date')))
            );
        }
        if ($this->IsPropertySet("end_date")) {
            $this->SetProperty("end_date", date($language->GetDateFormat(), strtotime($this->GetProperty('end_date'))));
        }

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%contract_" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, $this->module)
            );
            $values["contract_" . $property] = $this->GetProperty($property);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    /**
     * Checking the existence of a contract without date
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's contract should be loaded
     * @param int $entityID company_unit_id or employee_id of contract owner
     * @param int $productID product_id of contract's product
     *
     * @return bool true if contract was loaded successfully or false otherwise
     */
    public function ContractExistWithoutDate($level, $entityID, $productID)
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

        $where = array();
        $where[] = "product_id=" . intval($productID);
        $where[] = $key . "=" . intval($entityID);

        $query = "SELECT contract_id, created, product_id, start_date, end_date
					FROM " . $table . "
					WHERE " . implode(" AND ", $where) . "
					ORDER BY created DESC";
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        return $this->GetProperty("contract_id") ? true : false;
    }

    /**
     * Save the modified fields.
     *
     * @param string $level OPTION_LEVEL_* constant means what is entity's contract
     * @param string $propertyName property name (start_date or end_date)
     * @param string $value property value for save
     * @param int $userID user, who modified fields
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */

    public function SaveHistory($level, $propertyName, $value, $userID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $levelValue = $level == OPTION_LEVEL_COMPANY_UNIT ? "company_unit" : "employee";

        $query = "INSERT INTO contract_history (level, contract_id, property_name, value, created, user_id)
        VALUES (
        " . Connection::GetSQLString($levelValue) . ",
        " . $this->GetIntProperty("contract_id") . ",
        " . Connection::GetSQLString($propertyName) . ",
        " . Connection::GetSQLString($value) . ",
        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
        " . $userID . ")
        RETURNING value_id";


        if (!$stmt->Execute($query)) {
            return false;
        }

        return true;
    }

    public function DeleteContract($contractID, $level)
    {
        $user = new User();
        $user->LoadBySession();

        $table = $level == OPTION_LEVEL_COMPANY_UNIT ? "company_unit_contract" : "employee_contract";

        $stmt = GetStatement(DB_CONTROL);
        $query = "DELETE FROM " . $table . " WHERE contract_id = " . Connection::GetSQLString($contractID);
        $stmt->Execute($query);

        if (!$stmt->Execute($query)) {
            return false;
        }

        $this->SaveHistory($level, "start_date", null, $user->GetProperty("user_id"));
        $this->SaveHistory($level, "end_date", null, $user->GetProperty("user_id"));

        return true;
    }
}
