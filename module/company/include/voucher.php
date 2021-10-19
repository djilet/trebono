<?php

class Voucher extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of product properties to be loaded instantly
     */
    public function Voucher($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads voucher by its voucher_id
     *
     * @param int $id voucher_id
     *
     * @return bool true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT voucher_id, employee_id, group_id, amount, created, voucher_date, reason, recurring, created_user_id, archive, end_date, recurring_frequency, recurring_end_date, file, receipt_ids
					FROM voucher
					WHERE voucher_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("voucher_id")) {
            $this->PrepareContentBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $employee = new Employee($this->module);
        $employee->LoadByID($this->GetProperty("employee_id"));

        $keys = array("salutation", "first_name", "last_name", "employee_guid");
        foreach ($keys as $key) {
            $this->SetProperty("employee_" . $key, $employee->GetProperty($key));
        }

        $companyUnit = new CompanyUnit($this->module);
        $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

        $keys = array_keys($companyUnit->GetProperties());
        foreach ($keys as $key) {
            $this->SetProperty("company_" . $key, $companyUnit->GetProperty($key));
        }
    }

    public static function SetVoucherReason($voucherID, $reason)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT reason FROM voucher WHERE voucher_id=" . Connection::GetSQLString($voucherID);
        $result = $stmt->FetchRow($query);

        $query = "UPDATE voucher SET
                        reason=" . Connection::GetSQLString($reason) . "
                WHERE voucher_id=" . Connection::GetSQLString($voucherID);

        if ($stmt->Execute($query)) {
            if ($reason != $result["reason"]) {
                $user = new User();
                $user->LoadBySession();

                if (!self::SaveHistoryRow($user->GetProperty("user_id"), $voucherID, "reason", $reason)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    public static function VoucherReceiptExists($receiptID, $voucherID = null)
    {

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT voucher_id, receipt_id
                FROM voucher_receipt
                WHERE receipt_id=" . Connection::GetSQLString($receiptID) . ($voucherID != null ? " AND voucher_id=" . Connection::GetSQLString($voucherID) : "");
        $links = $stmt->FetchList($query);

        return count($links) > 0;
    }

    public static function SetVoucherReceipt($voucherID, $receiptID)
    {
        //don't insert duplicates
        if (Voucher::VoucherReceiptExists($receiptID, $voucherID)) {
            return true;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "INSERT INTO voucher_receipt (voucher_id,receipt_id,created) VALUES ("
            . Connection::GetSQLString($voucherID) . ", "
            . Connection::GetSQLString($receiptID) . ", "
            . Connection::GetSQLString(GetCurrentDateTime()) . ")";

        if ($stmt->Execute($query)) {
            return true;
        }

        return false;
    }

    public static function SetVoucherReceiptAmount($voucherID, $receiptID, $amount)
    {
        if ($amount > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "UPDATE voucher_receipt SET
                        amount=" . Connection::GetSQLString($amount) . "
                WHERE voucher_id=" . Connection::GetSQLString($voucherID) . " AND receipt_id=" . Connection::GetSQLString($receiptID);
            if ($stmt->Execute($query)) {
                return true;
            }
        }

        return false;
    }

    public static function GetVoucherReceiptLinks($voucherID)
    {
        if (intval($voucherID) > 0) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT receipt_id, amount, created FROM voucher_receipt WHERE voucher_id=" . Connection::GetSQLString($voucherID);

            return $stmt->FetchList($query);
        }

        return array();
    }

    /**
     * Creates or updates voucher. Object must be loaded from request before the method will be called.
     * Required properties are: employee_id, value, created, voucher_date, reason, is_reccurent, created_user_id
     *
     * @return bool true if voucher is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }
        $stmt = GetStatement();
        $currentPropertyList = false;

        if ($this->GetIntProperty("voucher_id") > 0) {
            $voucher = new Voucher($this->module);
            $voucher->LoadByID($this->GetIntProperty("voucher_id"));
            $currentPropertyList = $voucher->GetProperties();
            $query = "UPDATE voucher SET
                        amount=" . $this->GetPropertyForSQL("amount") . ",
                        voucher_date=" . Connection::GetSQLDate($this->GetProperty("voucher_date")) . ",
                        reason=" . $this->GetPropertyForSQL("reason") . ",
                        recurring=" . $this->GetPropertyForSQL("recurring") . ",
                        end_date=" . Connection::GetSQLDate($this->GetProperty("end_date")) . ",
                        recurring_frequency=" . $this->GetPropertyForSQL("recurring_frequency") . ",
                        recurring_end_date=" . Connection::GetSQLDate($this->GetProperty("recurring_end_date")) . "
                WHERE voucher_id=" . $this->GetIntProperty("voucher_id");
        } else {
            $query = "INSERT INTO voucher (employee_id, group_id, amount, created, created_user_id, voucher_date, reason, recurring, end_date, recurring_frequency, recurring_end_date) VALUES (
                        " . $this->GetIntProperty("employee_id") . ",
                        " . $this->GetIntProperty("group_id") . ",
                        " . $this->GetPropertyForSQL("amount") . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $this->GetIntProperty("created_user_id") . ",
                        " . Connection::GetSQLDate($this->GetProperty("voucher_date")) . ",
                        " . $this->GetPropertyForSQL("reason") . ",
                        " . $this->GetPropertyForSQL("recurring") . ",
                        " . Connection::GetSQLDate($this->GetProperty("end_date")) . ",
                        " . $this->GetPropertyForSQL("recurring_frequency") . ",
                        " . Connection::GetSQLDate($this->GetProperty("recurring_end_date")) . ")
                    RETURNING voucher_id";
        }
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("voucher_id") > 0) {
            $this->SetProperty("voucher_id", $stmt->GetLastInsertID());

            $employee = new Employee($this->module);
            $employee->LoadByID($this->GetProperty("employee_id"));
            if (strtotime($this->GetProperty("voucher_date")) <= strtotime(GetCurrentDate())) {
                $this->GenerateVoucherAndSendToEmail($employee);
            }
        }
        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Validates input data when trying to create/update voucher from admin panel. Also turns incorrect int/float properties into null.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    public function Validate()
    {
        $interruptionContract = new Contract("product");
        if (
            $interruptionContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $this->GetProperty("employee_id"),
                Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)
            )
        ) {
            $this->AddError("voucher-interruption-contract", $this->module);
        }

        $isNewVoucher = !($this->GetIntProperty("voucher_id") > 0);
        if($isNewVoucher) {
            $this->RemoveProperty("voucher_id");
        } elseif ($this->IsPropertySet("FileGenerated")) {
            $voucher = new Voucher("company");
            $voucher->LoadByID($this->GetIntProperty("voucher_id"));

            $this->SetProperty("amount", $voucher->GetProperty("amount"));
            $this->SetProperty("voucher_date", $voucher->GetProperty("voucher_date"));
            $this->SetProperty("reason", $voucher->GetProperty("reason"));
        }

        if (!$this->IsPropertySet("voucher_id") && !$this->IsPropertySet("created_user_id")) {
            $user = new User();
            $user->LoadBySession();
            $this->SetProperty("created_user_id", $user->GetProperty("user_id"));
        }

        $newVoucherProductGroupList = ProductGroupList::GetProductGroupList(false, false, false, true);
        $newVoucherProductGroupList = array_column($newVoucherProductGroupList, "group_id");
        if (in_array($this->GetProperty("group_id"), $newVoucherProductGroupList)) {
            $user = new User();
            $user->LoadBySession();
            if (!$user->Validate(array("root"))) {
                if (strtotime($this->GetProperty('voucher_date')) < strtotime(date('1.m.Y'))) {
                    $this->AddError("voucher-date-invalid", $this->module);
                    $this->AddErrorField("voucher_date", $this->module);
                }
            }
        }

        if ($this->IsPropertySet("count")) {
            if (!$this->ValidateNotEmpty("count")) {
                $this->AddError("voucher-count-empty", $this->module);
                $this->AddErrorField("count", $this->module);
            } else {
                $count = trim($this->GetProperty("count"));

                if (!preg_match("/^\d+$/", $count)) {
                    $this->AddError("voucher-count-is-not-number", $this->module);
                    $this->AddErrorField("count", $this->module);
                }
            }
        } else {
            if (!$this->ValidateNotEmpty("amount")) {
                $this->AddError("voucher-amount-empty", $this->module);
                $this->AddErrorField("amount", $this->module);
            } else {
                $amount = $this->GetProperty("amount");

                if (preg_match("/[,]/", $amount)) {
                    $amount = preg_replace("/[^0-9,]/", "", $amount);
                    $amount = str_replace(",", ".", $amount);
                } else {
                    $amount = preg_replace("/[^0-9.]/", "", $amount);
                    $amount = str_replace(",", ".", $amount);
                }
                $amount = abs(round($amount, 2, PHP_ROUND_HALF_DOWN));
                $this->SetProperty("amount", $amount);
            }
        }

        if (!$this->ValidateNotEmpty("voucher_date")) {
            $this->AddError("voucher-date-empty", $this->module);
            $this->AddErrorField("voucher_date", $this->module);
        }

        if (!$this->ValidateNotEmpty("end_date")) {
            $this->AddError("voucher-end-date-empty", $this->module);
            $this->AddErrorField("end_date", $this->module);
        }

        if (!$this->ValidateNotEmpty("reason")) {
            $this->AddError("voucher-reason-empty", $this->module);
            $this->AddErrorField("reason", $this->module);
        }

        if ($this->GetProperty("recurring") != "Y") {
            $this->SetProperty("recurring", "N");
            $this->RemoveProperty("recurring_frequency");
            $this->RemoveProperty("recurring_end_date");
        } else {
            if (!$this->ValidateNotEmpty("recurring_frequency")) {
                $this->AddError("voucher-frequency-empty", $this->module);
                $this->AddErrorField("recurring_frequency", $this->module);
            }
        }

        if (!$this->HasErrors()) {
            $contract = new Contract("product");

            $productGroup = new ProductGroup("product");
            $productGroup->LoadByID($this->GetProperty("group_id"));

            $specificProductGroup = SpecificProductGroupFactory::Create($this->GetProperty("group_id"));
            $mainProductCode = $specificProductGroup->GetMainProductCode();

            if (
                !$contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode($mainProductCode),
                    $this->GetProperty("employee_id"),
                    $this->GetProperty("voucher_date")
                )
            ) {
                $this->AddError("voucher-date-incorrect", $this->module);
                $this->AddErrorField("voucher_date", $this->module);
            } elseif (
                !$contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode($mainProductCode),
                    $this->GetProperty("employee_id"),
                    $this->GetProperty("end_date")
                )
                && !in_array($this->GetProperty("group_id"), $newVoucherProductGroupList)
            ) {
                $this->AddError("voucher-date-incorrect", $this->module);
                $this->AddErrorField("end_date", $this->module);
            }

            if (
                !$contract->ContractExist(
                    OPTION_LEVEL_EMPLOYEE,
                    Product::GetProductIDByCode($mainProductCode),
                    $this->GetProperty("employee_id"),
                    date("Y-m-d")
                )
            ) {
                $this->AddError("voucher-contract-empty", $this->module);
            }

            $employee = new Employee($this->module);
            $employee->LoadByID($this->GetProperty("employee_id"));

            if ($productGroup->GetProperty("code") == PRODUCT_GROUP__BONUS) {
                $maxYearly = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__BONUS__MAIN__AMOUNT_PER_YEAR,
                    $employee->GetProperty("company_unit_id"),
                    $this->GetProperty("voucher_date")
                );
                $currentYearly = VoucherList::GetYearlyVoucherListAmount(
                    $this->GetProperty("employee_id"),
                    $this->GetProperty("group_id"),
                    $this->GetProperty("voucher_date"),
                    $this->GetProperty("voucher_id")
                ) + $this->GetProperty("amount");

                if ($currentYearly > $maxYearly) {
                    $this->AddError("voucher-yearly-limit-exceeded", $this->module);
                }
            } elseif($productGroup->GetProperty("code") == PRODUCT_GROUP__BONUS_VOUCHER) {
                $maxYearly = Option::GetInheritableOptionValue(
                	OPTION_LEVEL_EMPLOYEE,
                	OPTION__BONUS_VOUCHER__MAIN__AMOUNT_PER_YEAR,
                	$employee->GetProperty("employee_id"),
                	$this->GetProperty("voucher_date")
                	);

                $voucherMap = $specificProductGroup->MapVoucherListToMonth(
                $employee->GetProperty("employee_id"),
                $productGroup->GetProperty("group_id"),
                $this
                );

                $currentYearly = 0;
                $monthError = false;
                foreach ($voucherMap as $date => $array) {
                    $date = date("d.m.Y", strtotime($date));
                    $currentYearly += $array["amount"];
                    if ($currentYearly > $maxYearly) {
                        $monthError = GetGermanMonthName(date("m", strtotime($date))) . " " . date("Y", strtotime($date));
                        break;
                    }
                }

                if($monthError !== false) {
                    $this->AddError("voucher-yearly-limit-exceeded-with-month", $this->module, ["month" => $monthError]);
                }
            } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__GIFT) {
                $maxYearlyQty = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__GIFT__MAIN__QTY_PER_YEAR,
                    $employee->GetProperty("company_unit_id"),
                    $this->GetProperty("voucher_date")
                );
                $currentYearlyQty = VoucherList::GetYearlyVoucherListCount(
                    $this->GetProperty("employee_id"),
                    $this->GetProperty("group_id"),
                    $this->GetProperty("voucher_date"),
                    $this->GetProperty("voucher_id")
                ) + 1;

                $maxAmount = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__GIFT__MAIN__AMOUNT_PER_VOUCHER,
                    $employee->GetProperty("company_unit_id"),
                    $this->GetProperty("voucher_date")
                );
                $currentAmount = $this->GetProperty("amount");

                if ($currentYearlyQty > $maxYearlyQty) {
                    $this->AddError("voucher-yearly-qty-limit-exceeded", $this->module);
                }

                if ($currentAmount > $maxAmount) {
                    $this->AddError("voucher-amount-limit-exceeded", $this->module);
                }
            } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__GIFT_VOUCHER) {
                $maxYearlyQty = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__GIFT_VOUCHER__MAIN__QTY_PER_YEAR,
                    $employee->GetProperty("company_unit_id"),
                    $this->GetProperty("voucher_date")
                );
                $voucherMap = $specificProductGroup->MapVoucherListToMonth(
                    $employee->GetProperty("employee_id"),
                    $productGroup->GetProperty("group_id"),
                    $this
                );

                $currentYearlyQty = 0;
                $monthError = false;
                foreach ($voucherMap as $date => $array) {
                    if (date("Y") != date("Y", strtotime($this->GetProperty("voucher_date")))) {
                        continue;
                    }

                    $date = date("d.m.Y", strtotime($date));
                    $currentYearlyQty += $array["count"];
                    if ($currentYearlyQty > $maxYearlyQty) {
                        $monthError = GetGermanMonthName(date("m", strtotime($date))) . " " . date(
                            "Y",
                            strtotime($date)
                        );
                        break;
                    }
                }
                if ($monthError !== false) {
                    $this->AddError("voucher-yearly-qty-limit-exceeded-month", $this->module, ["month" => $monthError]);
                }

                $maxAmount = Option::GetInheritableOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__GIFT_VOUCHER__MAIN__AMOUNT_PER_VOUCHER,
                    $employee->GetProperty("company_unit_id"),
                    $this->GetProperty("voucher_date")
                );
                $currentAmount = $this->GetProperty("amount");

                if ($currentAmount > $maxAmount) {
                    $this->AddError("voucher-amount-limit-exceeded", $this->module);
                }
            } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__BENEFIT_VOUCHER) {
                $voucherMap = $specificProductGroup->MapVoucherListToMonth(
                    $employee->GetProperty("employee_id"),
                    $productGroup->GetProperty("group_id"),
                    $this
                );

                $monthErrorList = array();
                foreach ($voucherMap as $date => $array) {
                    $date = date("d.m.Y", strtotime($date));
                    $maxAmount = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        OPTION__BENEFIT_VOUCHER__MAIN__MAX_MONTHLY,
                        $employee->GetProperty("company_unit_id"),
                        $date
                    );
                    if (!in_array(null, $array["voucher_ids"]) || $array["amount"] <= $maxAmount) {
                        continue;
                    }

                    $monthErrorList[] = GetGermanMonthName(date("m", strtotime($date))) . " " . date(
                        "Y",
                        strtotime($date)
                    );
                }

                if (count($monthErrorList) > 0) {
                    $this->AddError(
                        "voucher-monthly-limit-exceeded",
                        $this->module,
                        ["months" => implode(", ", $monthErrorList)]
                    );
                }
            } elseif ($productGroup->GetProperty("code") == PRODUCT_GROUP__FOOD_VOUCHER) {
                $voucherMap = $specificProductGroup->MapVoucherListToMonth(
                    $employee->GetProperty("employee_id"),
                    $productGroup->GetProperty("group_id"),
                    $this
                );

                $monthErrorList = array();
                foreach ($voucherMap as $date => $array) {
                    $date = date("d.m.Y", strtotime($date));
                    $maxCount = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_COMPANY_UNIT,
                        OPTION__FOOD_VOUCHER__MAIN__UNITS_PER_MONTH,
                        $employee->GetProperty("company_unit_id"),
                        $date
                    );
                    $available = $maxCount - $array["count_without_new"];
                    $available = $available >= 0 ? $available : 0;
                    if (!in_array(null, $array["voucher_ids"]) || $array["count"] <= $maxCount) {
                        continue;
                    }

                    $monthErrorList[] = [
                        "month" => GetGermanMonthName(date("m", strtotime($date))) . " " . date(
                            "Y",
                            strtotime($date)
                        ),
                        "available" => $available,
                    ];
                }

                if (count($monthErrorList) > 0) {
                    foreach ($monthErrorList as $error) {
                        $this->AddError(
                            "voucher-monthly-count-exceeded",
                            $this->module,
                            ["month" => $error["month"], "count" => $error["available"]]
                        );
                    }
                }
            }
        }

        return !$this->HasErrors();
    }

    public static function GetDefaultVoucherReason($optionLevel, $entityID, $groupCode, $date)
    {
        $voucherScenario = Option::GetInheritableOptionValue(
            $optionLevel,
            OPTIONS_VOUCHER_DEFAULT_REASON_SCENARIO[$groupCode],
            $entityID,
            GetCurrentDate() //this option is not dependant on date and time (task #4009)
        );

        //employee level scenario
        if (strpos($voucherScenario, "employee") !== false) {
            return Option::GetInheritableOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                OPTIONS_VOUCHER_DEFAULT_REASON[$groupCode],
                $entityID,
                $date
            );
        }
        //company level scenario
        if (strpos($voucherScenario, "company") !== false) {
            if ($optionLevel == OPTION_LEVEL_EMPLOYEE) {
                $entityID = Employee::GetEmployeeField($entityID, "company_unit_id");
            }

            return Option::GetInheritableOptionValue(
                OPTION_LEVEL_COMPANY_UNIT,
                OPTIONS_VOUCHER_DEFAULT_REASON[$groupCode],
                $entityID,
                $date
            );
        }

        return 0; //exchangeable scenario
    }

    /**
     * Get and parce voucher reason list from config
     *
     * @param string $reason selected reason
     *
     * @return array of reasons
     */
    public static function GetVoucherReasonList($selectedReason, $config = "voucher_reason")
    {
        $callback = static function ($str) {
            $str = trim($str);

            return $str;
        };

        $reasonList = Config::GetConfigValue($config);
        $reasonList = preg_split("/\r\n|\r|\n/", $reasonList);
        $reasonList = array_map($callback, $reasonList);

        $selectedIsInList = false;
        foreach ($reasonList as $key => $reason) {
            $reasonList[$key] = array("Reason" => $reason, "Key" => $key);
            if ($selectedReason === $reason || $selectedReason === strval($key)) {
                $reasonList[$key]["Selected"] = 1;
                $selectedIsInList = true;
            }
        }

        if (!$selectedIsInList && strlen($selectedReason) > 0) {
            if (!is_int($selectedReason)) {
                $reasonList[] = array("Reason" => $selectedReason, "Selected" => 1);
            } else {
                $reasonList[0]["Selected"] = 1;
            }
        }

        return $reasonList;
    }

    /**
     * Send voucher to email
     *
     * @return bool true if voucher is sended successfully or false on failure
     */
    function SendVoucherToEmail(Employee $employee, $groupID, $count = 1)
    {
        if ($employee->ValidateEmail('email')) {
            $emailTemplate = new PopupPage($this->module);
            $tmpl = $emailTemplate->Load("voucher_email.html");

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($employee->GetProperty("company_unit_id"));

            $voucherTotalAmount = $this->GetProperty("amount") * $count;

            $replacements = array(
                "salutation" => $employee->GetProperty("salutation"),
                "first_name" => $employee->GetProperty("first_name"),
                "last_name" => $employee->GetProperty("last_name"),
                "product_group" => GetTranslation(
                    "product-group-" . ProductGroup::GetProductGroupCodeByID($groupID),
                    "product",
                    null,
                    "de"
                ),
                "voucher_amount" => GetPriceFormat($this->GetProperty("amount")),
                "company name" => $companyUnit->GetProperty('title'),
                "count" => $count,
                "voucher_total_amount" => GetPriceFormat($voucherTotalAmount)
            );

            if ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__BENEFIT_VOUCHER)) {
                $tmpl->SetVar("Content", GetTranslation("send-email-voucher-new", $this->module, $replacements));
                $fromName = GetTranslation('send-email-from-name-bvs', $this->module);
            } elseif ($groupID == ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
                $tmpl->SetVar("Content", GetTranslation("send-email-food-voucher-new", $this->module, $replacements));
                $fromName = GetTranslation('send-email-from-name-bvs', $this->module);
            } else {
                $tmpl->SetVar("Content", GetTranslation("send-email-for-all", $this->module, $replacements));
                $fromName = null;
            }

            $result = SendMailFromAdminTask(
                $employee->GetProperty("email"),
                GetTranslation("voucher-header", $this->module, $replacements),
                $emailTemplate->Grab($tmpl),
                array(),
                array(array("Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo")),
                array(),
                $fromName
            );

            if ($result === true) {
                return true;
            }

            $this->AddError("error-sending-email");
            $this->AddError($result);
        } else {
            $this->AddError("incorrect-email-format");
        }

        return false;
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */
    public function SaveHistory($currentPropertyList)
    {
        $stmt = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();

        $propertyList = array(
            "amount",
            "voucher_date",
            "reason",
            "recurring",
            "end_date",
            "recurring_frequency",
            "recurring_end_date"
        );
        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }
        foreach ($propertyList as $key) {
            if ((($key == "voucher_date") || ($key == "end_date") || ($key == "recurring_end_date")) && $this->GetProperty($key)) {
                $this->SetProperty($key, date("Y-m-d", strtotime($this->GetProperty($key))));
            }

            if ($currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            if (
                !self::SaveHistoryRow(
                    $user->GetProperty("user_id"),
                    $this->GetProperty("voucher_id"),
                    $key,
                    $this->GetProperty($key)
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Puts the record about changed field to database
     *
     * @param int $userID user_id of user who makes the changes
     * @param int $voucherID voucher_id of changed voucher
     * @param string $key key of changed property
     * @param string $value new value
     *
     * @return bool|NULL true if inserted successfully or false|null otherwise
     */
    public static function SaveHistoryRow($userID, $voucherID, $key, $value)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO voucher_history (voucher_id, property_name, value, created, user_id)
					VALUES (
						" . intval($voucherID) . ",
						" . Connection::GetSQLString($key) . ",
						" . Connection::GetSQLString($value) . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . intval($userID) . ")
					RETURNING value_id";

        return $stmt->Execute($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $voucherID
     * @param bool $returnOne
     *
     * @return array list of values
     */
    public static function GetPropertyValueListVoucher($property, $voucherID, $returnOne = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name
					FROM voucher_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND voucher_id=" . intval($voucherID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        if (!$valueList) {
            return $valueList;
        }

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["user_name"] = User::GetNameByID($valueList[$i]['user_id']);
            if ($returnOne) {
                return $valueList[$i];
            }
        }

        return $valueList;
    }

    /**
     * Get voucher property
     *
     * @param string $property
     * @param int $voucherID
     *
     * @return int|bool
     */
    public static function GetPropertyByID($property, $voucherID)
    {
        $stmt = GetStatement();

        return $stmt->FetchField("SELECT " . $property . " FROM voucher WHERE voucher_id=" . intval($voucherID));
    }

    /**
     * We used to generate PDF for vouchers here. Now we only mark file as generated and send it to employee
     * Object should be loaded by id before this method will be called
     *
     * @param Object $employee Employee
     *
     * @return bool
     */
    public function GenerateVoucherAndSendToEmail(Employee $employee)
    {
        $voucher = new Voucher($this->module);
        $voucher->LoadByID($this->GetProperty("voucher_id"));

        //we don't generate files here anymore, but I decided to leave "file" name as it. check task 3544 for comments
        $fileName = "fake_" . date(
            'ym',
            strtotime($voucher->GetProperty("created"))
        ) . "_" . $voucher->GetProperty("company_customer_guid") . "_" . $this->GetProperty("employee_employee_guid") . "_" . $this->GetProperty("voucher_id") . ".pdf";
        $this->SetProperty("file", $fileName);

        $result = true;

        if ($this->GetProperty("group_id") != ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__FOOD_VOUCHER)) {
            $result = $this->SendVoucherToEmail($employee, $this->GetProperty("group_id"));
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE voucher SET file=" . $this->GetPropertyForSQL("file") . " WHERE voucher_id=" . $this->GetIntProperty("voucher_id");
        $stmt->Execute($query);

        return $result;
    }

    /**
     * Checking the existence of a voucher for a specific date
     *
     * @param int $productGroupID product_group_id of voucher's product
     * @param int $employeeID employee_id of voucher owner
     * @param string $date date for verification
     *
     * @return bool true if voucher was loaded successfully or false otherwise
     */
    public function VoucherExist($productGroupCode, $employeeID, ?string $date)
    {
        if (empty($date)) {
            return false;
        }

        $where = array();
        $where[] = "group_id=" . intval(ProductGroup::GetProductGroupIDByCode($productGroupCode));
        $where[] = "employee_id=" . intval($employeeID);
        $where[] = "archive!='Y'";
        if ($productGroupCode == PRODUCT_GROUP__BENEFIT_VOUCHER) {
            $where[] = "date_part('month', voucher_date) = " . date(
                "m",
                strtotime($date)
            ) . " AND created_user_id=" . Connection::GetSQLString(SB_GUTSCHEINE);
        } elseif ($productGroupCode == PRODUCT_GROUP__FOOD_VOUCHER) {
            $where[] = "date_part('month', voucher_date) = " . date(
                "m",
                strtotime($date)
            ) . " AND created_user_id=" . Connection::GetSQLString(ESSEN_GUTSCHEINE);
        } else {
            $where[] = "voucher_date = " . Connection::GetSQLDate($date);
        }
        $where[] = "date_part('year', voucher_date) = " . date(
                "Y",
                strtotime($date)
            );

        $query = "SELECT voucher_id
					FROM voucher
					WHERE " . implode(" AND ", $where);

        $this->LoadFromSQL($query, GetStatement());

        return $this->GetProperty("voucher_id") ? true : false;
    }
}
