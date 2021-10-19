<?php

class Option extends LocalObject
{
    private $module;
    private static $codeToOptionIDMap;
    private static $optionIDToCodeMap;
    private static $optionValueCacheMap = array();
    private static $inheritableOptionValueSearchListMap = array();

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of option properties to be loaded instantly
     */
    public function Option($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads option by its option_id
     *
     * @param int $id option_id
     *
     * @return bool true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT option_id, type, code, title, sort_order, product_id, group_id, 
						level_global, level_company_unit, level_employee 
					FROM option 
					WHERE option_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("option_id")) {
            $this->PrepareContentBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Loads code->option_id and option_id->code maps from database to static field
     */
    private static function LoadOptionCodeMaps()
    {
        self::$codeToOptionIDMap = array();

        $stmt = GetStatement();
        $query = "SELECT option_id, code FROM option ORDER BY option_id ASC";
        $optionList = $stmt->FetchAssocIndexedList($query, "code");

        foreach ($optionList as $code => $option) {
            self::$codeToOptionIDMap[$code] = $option["option_id"];
        }

        self::$optionIDToCodeMap = array_flip(self::$codeToOptionIDMap);
    }

    /**
     * Returns code->option_id map
     *
     * @return array
     */
    private static function GetCodeToOptionIDMap()
    {
        if (self::$codeToOptionIDMap === null) {
            self::LoadOptionCodeMaps();
        }

        return self::$codeToOptionIDMap;
    }

    /**
     * Returns option_id->code map
     *
     * @return array
     */
    private static function GetOptionIDToCodeMap()
    {
        if (self::$optionIDToCodeMap === null) {
            self::LoadOptionCodeMaps();
        }

        return self::$optionIDToCodeMap;
    }

    /**
     * Returns option_id by its unique code
     *
     * @param string $code
     *
     * @return int|NULL option_id
     */
    public static function GetOptionIDByCode($code)
    {
        $map = self::GetCodeToOptionIDMap();

        return $map[$code] ?? null;
    }

    /**
     * Returns unique code by its option_id
     *
     * @param int $id
     *
     * @return string|NULL code
     */
    public static function GetCodeByOptionID($id)
    {
        $map = self::GetOptionIDToCodeMap();

        return $map[$id] ?? null;
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $this->SetProperty("title_translation", GetTranslation("option-" . $this->GetProperty("code"), $this->module));
    }

    /**
     * Sets new value of option if it is not equal to old one
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value should be saved
     * @param int $optionID option_id of option which value to be saved
     * @param string $value value to be saved
     * @param int $entityID company_unit_id or employee_id whose value to be saved
     * @param string|NULL $dateFrom set if you need specify start date of option value using
     * @param User|NULL $user author's of the action.
     *
     * @return bool if value is successfully updated or shouldn't be updated. false if sql error occured during updating
     */
    public function SaveOptionValue($level, $optionID, $value, $entityID, $dateFrom = null, $user = null)
    {
        if ($user == null) {
            $user = new User();
            $user->LoadBySession();
        }
        $userID = $user->GetProperty("user_id");

        if ($value !== null) {
            $value = trim($value);
        }

        $option = new Option($this->module);
        $option->LoadByID($optionID);
        $type = $option->GetProperty("type");

        $newValue = $this->PrepareValueBeforeSave($type, $value, $level);

        if ($this->IsRecordExists($level, $optionID, $newValue, $entityID, $userID, $dateFrom)) {
            return true;
        }

        if ($optionID == null) {
            $this->AddError("option-incorrect-id", $this->module);

            return false;
        }

        $currentValue = self::GetCurrentValue($level, $optionID, $entityID);

        if (
            $newValue != $currentValue &&
            (strpos($option->GetProperty("code"), "max") ||
            strpos(
                $option->GetProperty("code"),
                "grant"
            ) ||
                    $option->GetProperty("group_id") == 2) &&
            $type != "flag" &&
            $level != OPTION_LEVEL_GLOBAL
        ) {
            $global = Option::GetCurrentValue(OPTION_LEVEL_GLOBAL, $optionID, 0);
            if ($global != null && intval($value) > intval($global)) {
                $this->AddError("option-exceeds-global-value", $this->module);
                $this->AddErrorField("Product[" . $option->GetProperty("product_id") . "][Option][" . $optionID . "]");

                return false;
            }
        }

        $dateFrom = $dateFrom == null ? GetCurrentDateTime() : $dateFrom;

        if ($newValue != $currentValue &&
            in_array($option->GetProperty("code"), OPTIONS_SAVING_FIRST_DAY_OF_NEXT_MONTH)) {
            $monthStart = date("Y-m-1", strtotime($dateFrom));

            if (
                strtotime($monthStart) !== strtotime($dateFrom) &&
                ($level == OPTION_LEVEL_EMPLOYEE &&
                    $user->Validate(array("employee" => null)) &&
                    !$user->Validate(array("root")) ||
                    $level == OPTION_LEVEL_COMPANY_UNIT &&
                    $user->Validate(array("company_unit" => null)) &&
                    !$user->Validate(array("root")))
            ) {
                $dateFrom = date("Y-m-d H:i:s", strtotime($monthStart . " + 1 month"));
                $this->AddMessage("option-value-next-month", $this->module);
            }
        }

        if (!$this->Validate($level, $optionID, $newValue, $entityID)) {
            return false;
        }

        if ((string)$newValue == (string)$currentValue && (!$dateFrom || $newValue === null)) {
            return true;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, date_from, user_id, value, created_from, created)
                    VALUES (" . Connection::GetSQLString($level) . ",
                    " . intval($entityID) . ",
                    " . intval($optionID) . ",
                    " . Connection::GetSQLDateTime($dateFrom) . ",
                    " . $userID . ",
                    " . Connection::GetSQLString($newValue) . ",
                    " . Connection::GetSQLString($this->GetProperty("created_from")) . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ")
                    RETURNING value_id";

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("value_id") > 0) {
            $this->SetProperty("value_id", $stmt->GetLastInsertID());
        }

        if ($option->GetProperty("code") == OPTION__FOOD__MAIN__FLEX_OPTION && $level == OPTION_LEVEL_COMPANY_UNIT && $newValue == "Y") {
            $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($entityID);
            $unitsForTransfer = null;
            $disabledEmployees = [];
            foreach ($employeeIDs as $employeeID) {
                if (
                    Option::GetOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__FOOD__MAIN__FLEX_OPTION,
                        $employeeID,
                        $dateFrom
                    ) !== "N"
                ) {
                    $this->SaveOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        Option::GetOptionIDByCode(OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER),
                        $unitsForTransfer,
                        $employeeID,
                        $dateFrom
                    );
                } else {
                    $disabledEmployees[] = Employee::GetNameByID($employeeID);
                }
            }

            if (!empty($disabledEmployees)) {
                $this->AddMessage(
                    "option-flex-disabled-for-employees",
                    $this->module,
                    ["employee_list" => implode(", ", $disabledEmployees)]
                );
            }
        }

        self::InvalidateOptionValueCache();

        if ($option->GetProperty("code") == OPTION__FOOD_VOUCHER__MAIN__AUTO_GENERATION && $newValue == "Y" && $level !== OPTION_LEVEL_GLOBAL) {
            $employeeIDs = $level === OPTION_LEVEL_EMPLOYEE
                ? [$entityID]
                : EmployeeList::GetEmployeeIDsByCompanyUnitID($entityID);

            $productGroupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER);
            $specificProductFood = new SpecificProductGroupFoodVoucher();
            $monthErrorList = [];
            $employeeNameList = [];
            foreach ($employeeIDs as $employeeID) {
                $voucherMap = $specificProductFood->MapVoucherListToMonth($employeeID, $productGroupID);
                foreach ($voucherMap as $date => $array) {
                    $date = date("d.m.Y", strtotime($date));
                    $maxCount = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH,
                        $employeeID,
                        $date
                    );
                    $exceeding = abs($maxCount - $array["count"]);
                    if ($array["count"] <= $maxCount) {
                        continue;
                    }

                    if (!isset($employeeNameList[$employeeID])) {
                        $employeeNameList[$employeeID] = Employee::GetNameByID($employeeID);
                    }

                    $monthErrorList[] = [
                        "employee_name" => $employeeNameList[$employeeID],
                        "month" => GetGermanMonthName(date("m", strtotime($date))) . " " . date(
                            "Y",
                            strtotime($date)
                        ),
                        "count" => $exceeding,
                    ];
                }
            }

            if (!empty($monthErrorList)) {
                $this->AddMessage("fvs-auto-generation-recurring-warning", $this->module);
                foreach ($monthErrorList as $message) {
                    $this->AddMessage(
                        "fvs-auto-generation-recurring-warning-employee",
                        $this->module,
                        [
                            "month" => $message["month"],
                            "count" => $message["count"],
                            "employee_name" => $message["employee_name"],
                        ]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */

    private function SaveHistory($level, $optionID, $value, $entityID, $dateFrom, $user_id, $created)
    {
        $dateFrom = $dateFrom == null
            ? Connection::GetSQLString(GetCurrentDateTime())
            : Connection::GetSQLDateTime($dateFrom);

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, date_from, user_id, value, created) VALUES (
                        " . Connection::GetSQLString($level) . ",
                        " . intval($entityID) . ",
						" . intval($optionID) . ",
						" . $dateFrom . ",
						" . $user_id . ",
						" . Connection::GetSQLString($value) . ")
					RETURNING value_id";
        if (!$stmt->Execute($query)) {
            return false;
        }

        if (!$this->GetIntProperty("value_id") > 0) {
            $this->SetProperty("value_id", $stmt->GetLastInsertID());
        }

        self::InvalidateOptionValueCache();

        return true;
    }

    /**
     * Checks if option value can be saved
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value tries be saved
     * @param int $optionID option_id of option which value tries to be saved
     * @param string $newValue prepared value tries to be saved
     * @param int $entityID company_unit_id or employee_id whose value tries to be saved
     *
     * @return bool if value can be saved or false otherwise
     */
    private function Validate($level, $optionID, $newValue, $entityID)
    {
        $option = new Option($this->module);
        $option->LoadByID($optionID);
        $code = $option->GetProperty("code");

        if (preg_match("/discount/", $code) && $newValue > 100) {
            $this->AddError("option-value-discaunt-value", $this->module);
        }

        if ($code == OPTION__AD__MAIN__PAYMENT_MONTH) {
            if ((int)$newValue > 12) {
                $this->AddError("option-correct-month", $this->module);
            }
        }

        if (
            $level != OPTION_LEVEL_GLOBAL && in_array($code, array(
                OPTION__AD__MAIN__PAYMENT_MONTH_QTY,
                OPTION__MOBILE__MAIN__PAYMENT_MONTH_QTY,
                OPTION__INTERNET__MAIN__PAYMENT_MONTH_QTY
            ))
        ) {
            $globalValue = Option::GetOptionValue(OPTION_LEVEL_GLOBAL, $code, null, GetCurrentDateTime());
            if ((int)$newValue > $globalValue) {
                $this->AddError("option-incorrect-value-greater-global", $this->module, array(
                    "option" => GetTranslation("option-" . $code, $this->module),
                    "global_value" => intval($globalValue)
                ));
            }
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        if ($this->HasErrors()) {
            $this->AddErrorField("Product[" . $option->GetProperty("product_id") . "][Option][" . $optionID . "]");
        }

        return !$this->HasErrors();
    }

    /**
     * Executes the actions which should be done automatically after some option's value was changed
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value was changed
     * @param int $optionID option_id of option which value was changed
     * @param string $currentValue actual value before saving
     * @param string $newValue saved value
     * @param int $entityID company_unit_id or employee_id whose value was changed
     */
    private function ProcessAfterSave($level, $optionID, $currentValue, $newValue, $entityID, $dateFrom = null)
    {
        $option = new Option($this->module);
        $option->LoadByID($optionID);
        $code = $option->GetProperty("code");

        /*      if($level == OPTION_LEVEL_COMPANY_UNIT && $code == OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY && $newValue == "N")
                {
                    $argumentList = self::GetCompanyUnitDirectChildArgumentList($entityID);
                    foreach($argumentList as $argument)
                    {
                        $optionToSave = new Option($this->module);
                        $optionToSave->SaveOptionValue($argument["level"], $optionID, $newValue, $argument["entity_id"]);
                    }
                }
                else*/
        if ($level != OPTION_LEVEL_GLOBAL || $code != OPTION__FOOD__MAIN__MEAL_VALUE) {
            return;
        }

        $argumentList = array();

        $companyUnitIDs = CompanyUnitList::GetAllCompanyUnitIDs();
        foreach ($companyUnitIDs as $companyUnitID) {
            $argumentList[] = array(
                "level" => OPTION_LEVEL_COMPANY_UNIT,
                "entity_id" => $companyUnitID
            );
        }

        $employeeIDs = EmployeeList::GetAllEmployeeIDs();
        foreach ($employeeIDs as $employeeID) {
            $argumentList[] = array(
                "level" => OPTION_LEVEL_EMPLOYEE,
                "entity_id" => $employeeID
            );
        }

        foreach ($argumentList as $argument) {
            $autoAdoption = self::GetOptionValue(
                $argument["level"],
                OPTION__FOOD__MAIN__AUTO_ADOPTION,
                $argument["entity_id"],
                $dateFrom
            );
            $employerMealGrant = self::GetOptionValue(
                $argument["level"],
                OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT,
                $argument["entity_id"],
                $dateFrom
            );

            if ($autoAdoption == "Y" || $employerMealGrant <= 0) {
                continue;
            }

            $newEmployerMealGrant = $employerMealGrant - ($newValue - $currentValue);
            $optionToSave = new Option($this->module);
            $optionToSave->SaveOptionValue(
                $argument["level"],
                self::GetOptionIDByCode(OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT),
                $newEmployerMealGrant,
                $argument["entity_id"],
                $dateFrom
            );
        }
    }

    /**
     * Returns list of arguments with parent company_unit_id's
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option is processing now
     * @param int $entityID company_unit_id or employee_id whose option is processing
     *
     * @return array of ["level" => "", "entity_id" => ""] arrays
     */
    private static function GetHigherLevelArgumentList($level, $entityID)
    {
        $companyUnitIDs = array();

        if ($level == OPTION_LEVEL_COMPANY_UNIT) {
            $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($entityID, false);
        } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
            $employee = new Employee("employee");
            $employee->LoadByID($entityID);
            $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($employee->GetProperty("company_unit_id"), true);
        }

        $argumentList = array();

        foreach ($companyUnitIDs as $companyUnitID) {
            $argumentList[] = array(
                "level" => OPTION_LEVEL_COMPANY_UNIT,
                "entity_id" => $companyUnitID
            );
        }

        return $argumentList;
    }

    /**
     * Returns list of direct children of company_unit for their option values update
     *
     * @param int $companyUnitID
     *
     * @return array of ["level" => "", "entity_id" => ""] arrays of entities whose option values to be updated
     */
    private static function GetCompanyUnitDirectChildArgumentList($companyUnitID)
    {
        $argumentList = array();

        $directChildrenCompanyUnitIDs = CompanyUnitList::GetDirectChildrenIDs($companyUnitID);
        foreach ($directChildrenCompanyUnitIDs as $id) {
            $argumentList[] = array(
                "level" => OPTION_LEVEL_COMPANY_UNIT,
                "entity_id" => $id
            );
        }

        $employeeIDs = EmployeeList::GetEmployeeIDsByCompanyUnitID($companyUnitID);
        foreach ($employeeIDs as $id) {
            $argumentList[] = array(
                "level" => OPTION_LEVEL_EMPLOYEE,
                "entity_id" => $id
            );
        }

        return $argumentList;
    }

    /**
     * Returns the latest value of option for admin edit pages
     *
     * @param int $optionID
     *
     * @return bool|string|null value string if exists, null if value doesnt exist, false on sql failure
     */
    public static function GetCurrentValue($level, $optionID, $entityID)
    {
        return self::GetOptionValue($level, self::GetCodeByOptionID($optionID), $entityID, GetCurrentDateTime());
    }

    /**
     * Returns option value history
     *
     * @param string $level
     * @param int $optionID
     * @param int $entityID
     * @param string $languageCode
     *
     * @return array list of values
     */
    public static function GetOptionValueList($level, $optionID, $entityID, $languageCode = null)
    {
        $stmt = GetStatement(DB_CONTROL);
        $table = null;
        $where = array();
        $where[] = "option_id=" . intval($optionID);
        $where[] = "level=" . Connection::GetSQLString($level);
        $where[] = "entity_id=" . intval($entityID);

        $query = "SELECT value_id, user_id, date_from, value, created_from, created
					FROM option_value_history
					WHERE " . implode(" AND ", $where) . " 
					ORDER BY date_from DESC, value_id DESC";
        $valueList = $stmt->FetchList($query);

        $deactivationReasonOptionID = Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON);
        if ($optionID == $deactivationReasonOptionID) {
            for ($i = 0; $i < count($valueList); $i++) {
                if ($valueList[$i]["value"] == "end") {
                    $valueList[$i]["value"] = GetTranslation(
                        "end-employment-contract",
                        "company",
                        array(),
                        $languageCode
                    );
                }
                if ($valueList[$i]["value"] != "continue") {
                    continue;
                }

                $valueList[$i]["value"] = GetTranslation(
                    "continue-employment-contract",
                    "company",
                    array(),
                    $languageCode
                );
            }
        }

        $isVoucherReason = false;
        $reasonList = [];
        $optionCode = Option::GetCodeByOptionID($optionID);
        if (in_array($optionCode, OPTIONS_VOUCHER_DEFAULT_REASON)) {
            $isVoucherReason = true;
            $reasonList = Voucher::GetVoucherReasonList(null, "voucher_sets_of_goods");
        }
        if (in_array($optionCode, OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO)) {
            $isVoucherReasonScenario = true;
            $scenarioList = Option::GetVoucherReasonScenarioList();
        }

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            if ($isVoucherReason) {
                $valueList[$i]["value"] = $reasonList[$valueList[$i]["value"]]["Reason"];
            }
            if ($isVoucherReasonScenario) {
                $valueList[$i]["value"] = $scenarioList[$valueList[$i]["value"]]["Reason"];
            }

            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];
        }

        return $valueList;
    }

    /**
     * Formats value before save
     *
     * @param string $type
     * @param string $value
     *
     * @return string|NULL formatted value or null if incorrect type is passed
     */
    public function PrepareValueBeforeSave($type, $value, $level)
    {
        if ($type == null) {
            $type = $this->GetProperty("type");
        }
        $result = null;
        if (($value === "" || $value === null) && ($type !== OPTION_TYPE_FLAG || $level == OPTION_LEVEL_EMPLOYEE)) {
            return null;
        }
        switch ($type) {
            case OPTION_TYPE_INT:
                $value = preg_match("/[,]/", $value) ? preg_replace("/[^0-9,]/", "", $value) : preg_replace("/[^0-9.]/", "", $value);
                $value = str_replace(",", ".", $value);
                $result = abs(intval($value));
                break;
            case OPTION_TYPE_FLOAT:
                $value = preg_match("/[,]/", $value) ? preg_replace("/[^0-9,]/", "", $value) : preg_replace("/[^0-9.]/", "", $value);
                $value = str_replace(",", ".", $value);
                $result = abs(floatval($value));
                break;
            case OPTION_TYPE_STRING:
                $result = trim($value);
                break;
            case OPTION_TYPE_CURRENCY:
                $value = preg_match("/[,]/", $value) ? preg_replace("/[^0-9,]/", "", $value) : preg_replace("/[^0-9.]/", "", $value);
                $value = str_replace(",", ".", $value);
                $result = abs(round($value, 2, PHP_ROUND_HALF_DOWN));
                break;
            case OPTION_TYPE_FLAG:
                $result = $value != "Y" && $value != "N" ? null : $value;
                break;
        }

        return $result;
    }

    /**
     * Clears option value cache
     */
    private static function InvalidateOptionValueCache()
    {
        self::$optionValueCacheMap = array();
    }

    /**
     * Returns value of option actual to selected date
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value should be returned
     * @param int $optionCode code of option which value to be returned
     * @param int $entityID company_unit_id or employee_id whose value to be returned
     * @param string $date date value should be actual for
     *
     * @return string|null string if value is found or null otherwise
     */
    public static function GetOptionValue($level, $optionCode, $entityID, $date)
    {
        if (!$date) {
            return null;
        }

        if (count(self::$optionValueCacheMap) >= 1000) {
            self::InvalidateOptionValueCache();
        }

        $cacheKey = $level . "_" . $optionCode . "_" . $entityID;

        if (!isset(self::$optionValueCacheMap[$cacheKey])) {
            $stmt = GetStatement(DB_CONTROL);
            $where = array();
            $where[] = "option_id=" . intval(Option::GetOptionIDByCode($optionCode));
            $where[] = "level=" . Connection::GetSQLString($level);
            $where[] = "entity_id=" . intval($entityID);

            $query = "SELECT value_id, value, DATE(date_from) AS date_from, created_from, created
						FROM option_value_history
						WHERE " . implode(" AND ", $where) . "
						ORDER BY date_from ASC, value_id ASC";
            self::$optionValueCacheMap[$cacheKey] = $stmt->FetchList($query);
        }

        $result = null;

        foreach (self::$optionValueCacheMap[$cacheKey] as $value) {
            if (strtotime($value["date_from"]) > strtotime($date)) {
                continue;
            }

            $result = $value["value"];
        }

        return $result;
        /*
        if (!$date)
            return null;
        $stmt = GetStatement(DB_CONTROL);
        $where = array();
        $where[] = "DATE(created) <= ".Connection::GetSQLDate($date);
        $optionID = Option::GetOptionIDByCode($optionCode);
        $where[] = "option_id=".intval($optionID);
        $where[] = "level=".Connection::GetSQLString($level);
        $where[] = "entity_id=".intval($entityID);

        $query = "SELECT value
                    FROM option_value_history
                    WHERE ".implode(" AND ", $where)."
                    ORDER BY created DESC, value_id DESC";
        return $stmt->FetchField($query);
        */
    }

    /**
     * Returns value of option actual to selected date.
     * If value is not set for selected entity exactly then function will try to find the value for higher-level entities
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value should be returned
     * @param int $optionCode code of option which value to be returned
     * @param int $entityID company_unit_id or employee_id whose value to be returned
     * @param string $date date value should be actual for
     *
     * @return string|null string if value is found or null otherwise
     */
    public static function GetInheritableOptionValue($level, $optionCode, $entityID, $date)
    {
        $searchList = self::GetInheritableOptionValueSearchList($level, $entityID);

        foreach ($searchList as $search) {
            $value = self::GetOptionValue($search["level"], $optionCode, $search["entity_id"], $date);
            if ($value !== null && $value !== false) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Returns source of inherited option value actual to selected date.
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value is searched
     * @param int $optionCode code of option which value is searched
     * @param int $entityID company_unit_id or employee_id whose value is searched
     * @param string $date date value should be actual for
     *
     * @return string|null string if value is found or null otherwise
     */
    public static function GetInheritableOptionValueSource($level, $optionCode, $entityID, $date)
    {
        $searchList = self::GetInheritableOptionValueSearchList($level, $entityID);

        foreach ($searchList as $search) {
            $value = self::GetOptionValue($search["level"], $optionCode, $search["entity_id"], $date);
            if ($value !== null && $value !== false) {
                return $search["level"];
            }
        }

        return null;
    }

    /**
     * Returns array of level-entityID pairs where inheritable option value should be searched
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value is searched
     * @param int $entityID company_unit_id or employee_id whose value is searched
     *
     * @return array
     */
    private static function GetInheritableOptionValueSearchList($level, $entityID)
    {
        /*
         * Important! We expect that option values would not be requested after structure of company units and employees was changed during current script execution
         * So the only source of cache invalidation is there
         */
        if (count(self::$inheritableOptionValueSearchListMap) > 1000) {
            self::$inheritableOptionValueSearchListMap = array();
        }

        $cacheKey = $level . "_" . $entityID;

        if (!isset(self::$inheritableOptionValueSearchListMap[$cacheKey])) {
            $searchList = array();

            $searchList[] = array(
                "level" => $level,
                "entity_id" => $entityID
            );

            if ($level == OPTION_LEVEL_COMPANY_UNIT) {
                $path2root = CompanyUnitList::GetCompanyUnitPath2Root($entityID, true);
                foreach ($path2root as $companyUnitID) {
                    $searchList[] = array(
                        "level" => OPTION_LEVEL_COMPANY_UNIT,
                        "entity_id" => $companyUnitID
                    );
                }

                $searchList[] = array(
                    "level" => OPTION_LEVEL_GLOBAL,
                    "entity_id" => null
                );
            } elseif ($level == OPTION_LEVEL_EMPLOYEE) {
                $employeeCompanyUnitID = Employee::GetEmployeeField($entityID, "company_unit_id");

                $path2root = CompanyUnitList::GetCompanyUnitPath2Root($employeeCompanyUnitID, true);
                foreach ($path2root as $companyUnitID) {
                    $searchList[] = array(
                        "level" => OPTION_LEVEL_COMPANY_UNIT,
                        "entity_id" => $companyUnitID
                    );
                }

                $searchList[] = array(
                    "level" => OPTION_LEVEL_GLOBAL,
                    "entity_id" => null
                );
            }

            self::$inheritableOptionValueSearchListMap[$cacheKey] = $searchList;
        }

        return self::$inheritableOptionValueSearchListMap[$cacheKey];
    }

    /**
     *Checks if the same option history record already exists.
     *
     * @param string $level OPTION_LEVEL_* constant means what entity's option value should be saved
     * @param int $option_id option_id of option which value to be saved
     * @param string $value value to be saved
     * @param int $entity_id company_unit_id or employee_id whose value to be saved
     * @param int $user_id author's of the action user_id.
     * @param string|NULL $dateFrom set if you need specify start date of option value using
     *
     * @return int|bool value_id of existing record or false otherwise.
     */
    private function IsRecordExists($level, $option_id, $value, $entity_id, $user_id, $dateFrom = null)
    {
        $stmt = GetStatement(DB_CONTROL);

        if ($dateFrom) {
            $condition = $value === null ? "value IS NULL" : "value=" . Connection::GetSQLString($value);

            $query = "SELECT value_id FROM option_value_history WHERE 
                level='" . $level . "' AND 
                entity_id=" . $entity_id . " AND 
                option_id=" . intval($option_id) . " AND 
                " . $condition . " AND 
                date_from=" . Connection::GetSQLDate($dateFrom) . " AND 
                user_id=" . $user_id;

            return $stmt->FetchField($query);
        }

        $currentValue = self::GetCurrentValue($level, $option_id, $entity_id);

        return $value == 0 && $currentValue === null ? $value === $currentValue : $value == $currentValue;
    }

    /**
     * Modify option values due the AutomaticAdoption rules.
     *
     * @param LocalObject $request
     */
    public static function AutomaticAdoption($request)
    {
        $level = OPTION_LEVEL_GLOBAL;
        $module = $request->GetProperty('load');
        $groupCodeList = [
            [
                'code' => PRODUCT__FOOD__MAIN,
                'adoption' => OPTION__FOOD__MAIN__AUTO_ADOPTION,
                'mandatory' => OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
                'A' => OPTION__FOOD__MAIN__MEAL_VALUE,
                'B' => OPTION__FOOD__MAIN__EMPLOYER_MEAL_GRANT,
                'C' => OPTION__FOOD__MAIN__EMPLOYEE_MEAL_GRANT,
            ],
            [
                'code' => PRODUCT__FOOD_VOUCHER__MAIN,
                'adoption' => OPTION__FOOD_VOUCHER__MAIN__AUTO_ADOPTION,
                'mandatory' => OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT_MANDATORY,
                'A' => OPTION__FOOD_VOUCHER__MAIN__MEAL_VALUE,
                'B' => OPTION__FOOD_VOUCHER__MAIN__EMPLOYER_MEAL_GRANT,
                'C' => OPTION__FOOD_VOUCHER__MAIN__EMPLOYEE_MEAL_GRANT,
            ],
        ];

        $employeeIDs = [];
        $companyUnitIDs = [];
        $queryList = [];
        foreach ($groupCodeList as $group) {
            $productFoodID = Product::GetProductIDByCode($group['code']);
            $option = new Option($module);
            $dateOfParams = $request->GetProperty('Product')[$productFoodID]['date_of_params'];
            if (!$dateOfParams) {
                $dateOfParams = date("Y-m-d H:i:s");
            }

            $A = self::GetOptionValue($level, $group['A'], 0, $dateOfParams);
            $C = self::GetOptionValue($level, $group['C'], 0, $dateOfParams);
            $newA = $request->GetProperty('Product')[$productFoodID]['Option'][self::GetOptionIDByCode($group['A'])];
            $newA = $option->PrepareValueBeforeSave('currency', $newA, $level);
            $newC = $request->GetProperty('Product')[$productFoodID]['Option'][self::GetOptionIDByCode($group['C'])];
            $newC = $option->PrepareValueBeforeSave('currency', $newC, $level);

            if ($newA == $A && $newC == $C) {
                continue;
            }

            $employerMealGrantID = self::GetOptionIDByCode($group['B']);
            $newB = $request->GetProperty('Product')[$productFoodID]['Option'][$employerMealGrantID];
            $newB = $option->PrepareValueBeforeSave('currency', $newB, $level);

            $argumentList = [];

            if (empty($companyUnitIDs)) {
                $companyUnitIDs = CompanyUnitList::GetAllCompanyUnitIDs(null, "level");
            }
            $mandatoryList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_COMPANY_UNIT,
                $group['mandatory'],
                $companyUnitIDs,
                $dateOfParams
            );
            $autoAdoptionList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_COMPANY_UNIT,
                $group['adoption'],
                $companyUnitIDs,
                $dateOfParams
            );
            $employerMealGrantList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_COMPANY_UNIT,
                $group['B'],
                $companyUnitIDs,
                $dateOfParams
            );
            foreach ($companyUnitIDs as $companyUnitID) {
                $argumentList[] = array(
                    "level" => OPTION_LEVEL_COMPANY_UNIT,
                    "entity_id" => $companyUnitID,
                    "mandatory" => $mandatoryList[$companyUnitID],
                    "auto_adoption" => $autoAdoptionList[$companyUnitID],
                    "employer_meal_grant" => $employerMealGrantList[$companyUnitID]
                );
            }
            if (empty($employeeIDs)) {
                $employeeIDs = EmployeeList::GetAllEmployeeIDs();
            }
            $mandatoryList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_EMPLOYEE,
                $group['mandatory'],
                $employeeIDs,
                $dateOfParams
            );
            $autoAdoptionList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_EMPLOYEE,
                $group['adoption'],
                $employeeIDs,
                $dateOfParams
            );
            $employerMealGrantList = self::GetInheritableOptionValueForMultipleEntities(
                OPTION_LEVEL_EMPLOYEE,
                $group['B'],
                $employeeIDs,
                $dateOfParams
            );
            foreach ($employeeIDs as $employeeID) {
                $argumentList[] = array(
                    "level" => OPTION_LEVEL_EMPLOYEE,
                    "entity_id" => $employeeID,
                    "mandatory" => $mandatoryList[$employeeID],
                    "auto_adoption" => $autoAdoptionList[$employeeID],
                    "employer_meal_grant" => $employerMealGrantList[$employeeID]
                );
            }

            foreach ($argumentList as $argument) {
                if ($argument["auto_adoption"] != "Y") {
                    if ($argument["mandatory"] == 'Y') {
                        $unit = $A + $argument["employer_meal_grant"] + $C;
                        $newEmployerMealGrant = $unit - $newA - $newC;
                    } else {
                        $unit = $A + $argument["employer_meal_grant"];
                        $newEmployerMealGrant = $unit - $newA;
                    }
                    $valueForSave = $option->PrepareValueBeforeSave(
                        'currency',
                        $newEmployerMealGrant,
                        $argument["level"]
                    );
                } else {
                    $valueForSave = $newB;
                }

                //this condition doesn't work if company has auto adoption = N and employee = Y, while inheriting meal grant
                //if($valueForSave != $argument["employer_meal_grant"])
                {
                    $queryList[] = "(" . Connection::GetSQLString($argument["level"]) . ",
                        " . intval($argument["entity_id"]) . ",
                        " . intval($employerMealGrantID) . ",
                        " . Connection::GetSQLDateTime($dateOfParams) . ",
                        " . AUTO_ADOPTION . ",
                        " . Connection::GetSQLString($valueForSave) . ",
                        " . Connection::GetSQLString("admin") . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ")";
                }
            }
        }
        if (empty($queryList)) {
            return;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO option_value_history (level, entity_id, option_id, date_from, user_id, value, created_from, created)
                    VALUES " . implode(", ", $queryList) . " 
                    RETURNING value_id";
        $stmt->Execute($query);
    }

    public static function GetInheritableOptionValueForMultipleEntities($level, $optionCode, $entityIDs, $date)
    {
        $searchListCompanyUnit = [];
        $searchListEmployee = [];
        $employeeToCompany = [];
        $valueList = [];
        $nullCount = count($entityIDs);
        foreach ($entityIDs as $entityID) {
            $resultList = self::GetInheritableOptionValueSearchList($level, $entityID);
            foreach ($resultList as $result) {
                if ($result["level"] == "employee") {
                    $searchListEmployee[$entityID] = $result["entity_id"];
                } elseif ($result["level"] == "company_unit" && $level == OPTION_LEVEL_COMPANY_UNIT) {
                    $searchListCompanyUnit[$entityID] = $result["entity_id"];
                } elseif ($result["level"] == "company_unit" && $level == OPTION_LEVEL_EMPLOYEE) {
                    $searchListCompanyUnit[$entityID] = $result["entity_id"];
                    $employeeToCompany[$result["entity_id"]][] = $entityID;
                }
            }

            $valueList[$entityID] = null;
        }

        if ($level == OPTION_LEVEL_EMPLOYEE) {
            $employeeValueList = self::GetOptionValueForMultipleEntities(
                OPTION_LEVEL_EMPLOYEE,
                $optionCode,
                $searchListEmployee,
                $date
            );
            foreach ($employeeValueList as $entityID => $value) {
                if ($value == null) {
                    continue;
                }

                $valueList[$entityID] = $value;
                $nullCount--;
            }
        }

        if ($nullCount > 0) {
            $companyValueList = self::GetOptionValueForMultipleEntities(
                OPTION_LEVEL_COMPANY_UNIT,
                $optionCode,
                $searchListCompanyUnit,
                $date
            );
            foreach ($companyValueList as $entityID => $value) {
                if ($value == null) {
                    continue;
                }

                if ($level == OPTION_LEVEL_EMPLOYEE) {
                    foreach ($employeeToCompany[$entityID] as $employeeID) {
                        if ($valueList[$employeeID] != null) {
                            continue;
                        }

                        $valueList[$employeeID] = $value;
                        $nullCount--;
                    }
                } else {
                    if ($valueList[$entityID] == null) {
                        $valueList[$entityID] = $value;
                        $nullCount--;
                    }
                }
            }
        }

        if ($nullCount > 0) {
            $globalValue = self::GetOptionValue(OPTION_LEVEL_GLOBAL, $optionCode, null, $date);
            foreach ($entityIDs as $entityID) {
                if ($valueList[$entityID] != null) {
                    continue;
                }

                $valueList[$entityID] = $globalValue;
            }
        }

        return $valueList;
    }

    public static function GetOptionValueForMultipleEntities($level, $optionCode, $entityIDs, $date)
    {
        if (!$date) {
            return null;
        }

        $stmt = GetStatement(DB_CONTROL);
        $where = array();
        $where[] = "option_id=" . intval(Option::GetOptionIDByCode($optionCode));
        $where[] = "level=" . Connection::GetSQLString($level);
        $where[] = "entity_id IN (" . implode(", ", $entityIDs) . ")";

        $query = "SELECT value_id, value, DATE(date_from) AS date_from, created_from, created, entity_id
						FROM option_value_history
						WHERE " . implode(" AND ", $where) . "
						ORDER BY date_from ASC, value_id ASC";
        $valueList = $stmt->FetchList($query);

        $result = [];
        foreach ($valueList as $value) {
            if (strtotime($value["date_from"]) > strtotime($date)) {
                continue;
            }

            $result[$value["entity_id"]] = $value["value"];
        }
        foreach ($entityIDs as $entityID) {
            if (isset($result[$entityID])) {
                continue;
            }

            $result[$entityID] = null;
        }

        return $result;
    }

    public static function GetVoucherReasonScenarioList($selected = null)
    {
        $selectOption = "voucher-category-scenario-";
        $scenarioList = ["exchangeable", "company_flex", "company", "employee_flex", "employee"];
        $result = [];
        foreach ($scenarioList as $key) {
            $result[$key] = [
                "Key" => $key,
                "Reason" => GetTranslation($selectOption . $key, "company"),
            ];
            if ($selected != $key) {
                continue;
            }

            $result[$key]["Selected"] = 1;
        }

        return $result;
    }
}
