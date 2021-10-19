<?php

require_once(dirname(__FILE__) . "/../../agreements/include/confirmation_list.php");
require_once(dirname(__FILE__) . "/../../agreements/include/confirmation.php");

class Employee extends LocalObject
{
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of employee properties to be loaded instantly
     */
    public function Employee($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = array();
        $this->params["user"] = LoadImageConfig('user_image', "user", GetFromConfig("UserImage"));
    }

    /**
     * Loads employee by its employee_id
     *
     * @param int $id employee_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT e.employee_id, e.user_id, e.company_unit_id,
						e.material_status, e.child_count, e.bic, e.bank_name, e.start_date, e.working_days_per_week, e.cost_center_number, e.employee_guid, e.active_contract_number, e.comment,
						e.acc_meal_value_tax_flat, e.acc_food_subsidy_tax_free, e.acc_gross_salary, e.acc_grant_of_materials, e.creditor_number,
						e.acc_internet_subsidy_tax, e.acc_mobile_subsidy_tax_free, e.acc_recreation_subsidy_tax_flat, e.acc_net_income, e.acc_bonus_tax_flat, e.acc_transport_tax_free,  e.acc_child_care_tax_free, e.acc_travel_tax_free,
						u.salutation, u.birthday, u.email, u.phone,
						u.zip_code, u.country, u.city, u.house, u.user_image, u.user_image_config, e.license_version, e.guideline_version, e.org_guideline_version, e.work_place,
						" . Connection::GetSQLDecryption("u.first_name") . " AS first_name,
						" . Connection::GetSQLDecryption("u.last_name") . " AS last_name,
						" . Connection::GetSQLDecryption("u.street") . " AS street,
						" . Connection::GetSQLDecryption("e.iban") . " AS iban,
						e.acc_daily_allowance, e.acc_gift, e.acc_corporate_health_management,
						e.acc_ticket, e.acc_accommodation, e.acc_hospitality,
						e.acc_parking, e.acc_other, e.acc_travel_costs, e.acc_creditor,
						e.yearly_total_benefits
					FROM employee AS e 
						JOIN user_info AS u ON u.user_id=e.user_id  
					WHERE e.employee_id=" . intval($id);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if (!$this->ValidateNotEmpty("salutation")) {
            $this->SetProperty("salutation", "Frau");
        }
        if (!$this->ValidateNotEmpty("working_days_per_week")) {
            $this->SetProperty("working_days_per_week", 5);
        }

        if ($this->GetProperty("employee_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Loads employee by its user_id
     *
     * @param int $userID user_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByUserID($userID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT employee_id FROM employee WHERE user_id=" . intval($userID);
        $employeeID = intval($stmt->FetchField($query));

        return $this->LoadByID($employeeID);
    }

    /**
     * Puts additional fields that are not loaded by main sql-query and prepares user image paths for different resize settings
     */
    private function PrepareBeforeShow()
    {
        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__CORE);
        }

        $stmt = GetStatement();
        $query = "SELECT " . Connection::GetSQLDecryption("u.title") . " AS company_unit_title, " . Connection::GetSQLDecryption("c.title") . " AS root_company_unit_title,
						u.app_logo_image AS company_app_logo_image,
						u.app_logo_mini_image AS company_app_logo_mini_image,
						c.app_logo_image AS root_company_app_logo_image,
						c.app_logo_mini_image AS root_company_app_logo_mini_image
					FROM company_unit AS u 
						LEFT JOIN company_unit AS c ON c.company_id=u.company_id AND c.parent_unit_id IS NULL AND c.company_unit_id != u.company_unit_id 
					WHERE u.company_unit_id=" . $this->GetIntProperty("company_unit_id");
        if (!$company = $stmt->FetchRow($query)) {
            return;
        }

        $params = [
            "app_logo" => LoadImageConfig('app_logo_image', "company", GetFromConfig("AppLogoBase")),
            "app_logo_mini" => LoadImageConfig('app_logo_mini_image', "company", GetFromConfig("AppLogoMini")),
        ];

        $company['company_app_logo_image_url'] = '';
        if (!empty($company['company_app_logo_image']) or !empty($company['root_company_app_logo_image'])) {
            $img = $company['company_app_logo_image'] ?? $company['root_company_app_logo_image'];
            foreach ($params['app_logo'] as $key => $param) {
                if ($param['SourceName'] != 'api') {
                    continue;
                }

                $path = $param["Path"] . 'apps/' . $img;
                $company['company_app_logo_image_url'] = preg_replace("/^(" . preg_quote(
                    PROJECT_PATH,
                    "/"
                ) . ")/", GetUrlPrefix(DATA_LANGCODE, false), $path);
            }
        }
        unset($company['company_app_logo_image']);
        unset($company['root_company_app_logo_image']);

        $company['company_app_logo_mini_image_url'] = '';
        if (!empty($company['company_app_logo_mini_image']) or !empty($company['root_company_app_logo_mini_image'])) {
            $img = $company['company_app_logo_mini_image'] ?? $company['root_company_app_logo_mini_image'];
            foreach ($params['app_logo'] as $key => $param) {
                if ($param['SourceName'] != 'api') {
                    continue;
                }

                $path = $param["Path"] . 'apps/' . $img;
                $company['company_app_logo_mini_image_url'] = preg_replace("/^(" . preg_quote(
                    PROJECT_PATH,
                    "/"
                ) . ")/", GetUrlPrefix(DATA_LANGCODE, false), $path);
            }
        }
        unset($company['company_app_logo_mini_image']);
        unset($company['root_company_app_logo_mini_image']);


        $this->AppendFromArray($company);
    }

    /**
     * Returns array of image resize settings for $key image necessary for admin image edit component initializing
     *
     * @param string $key image key
     *
     * @return mixed[][]
     */
    public function GetImageParams($key)
    {
        $paramList = array();
        for ($i = 0; $i < count($this->params[$key]); $i++) {
            $paramList[] = array(
                "Name" => $this->params[$key][$i]['Name'],
                "SourceName" => $this->params[$key][$i]['SourceName'],
                "Width" => $this->params[$key][$i]['Width'],
                "Height" => $this->params[$key][$i]['Height'],
                "Resize" => $this->params[$key][$i]['Resize'],
                "X1" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "X1"),
                "Y1" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "Y1"),
                "X2" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "X2"),
                "Y2" => $this->GetIntProperty($key . "_image" . $this->params[$key][$i]['SourceName'] . "Y2")
            );
        }

        return $paramList;
    }

    /**
     * Creates or updates the emplloyee. Object must be loaded from request before the method will be called.
     * Required properties are: company_unit_id, material_status, child_count, start_date, working_days_per_week, cost_center_number, employee_guid,
     * comment, acc_meal_value_tax_flat, acc_food_subsidy_tax_free, acc_gross_salary, acc_grant_of_materials,
     * acc_internet_subsidy_tax, acc_mobile_subsidy_tax_free, acc_recreation_subsidy_tax_flat, acc_net_income, acc_bonus_tax_flat, <br />
     * email, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house
     * If new employee is created then new user with api-permission will be created.
     *
     * @param bool $saveFromContact true if user should be saved as contact
     * @param bool $isImport true if employee is saved via AZ import
     *
     * @return bool true if employee and its user are created/updated successfully or false on failure
     */
    public function Save($saveFromContact = false, $isImport = false)
    {
        $user = new User();
        $user->LoadFromObject($this);
        $new = !boolval($this->GetProperty("employee_id"));
        $activateString = "";

        $result1 = $this->Validate();
        $result2 = $user->ValidateFromEmployee();
        if (!$result1 || !$result2) {
            $this->AppendErrorsFromObject($user);
            $this->AppendErrorFieldsFromObject($user);

            return false;
        }

        $stmt = GetStatement(DB_PERSONAL);

        if (!$new) {
            $baseMainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);

            $contractBefore = new Contract("product");
            $existBaseContractBefore = $contractBefore->ContractExistWithoutDate(
                OPTION_LEVEL_EMPLOYEE,
                $this->GetProperty("employee_id"),
                $baseMainProductID
            );

            //save voucher service contracts existing before saving (check for master data)
            $contract = new Contract("product");
            $voucherProductExistBefore = $voucherProductExistAfter = array(
                PRODUCT__FOOD_VOUCHER__MAIN => false,
                PRODUCT__BENEFIT_VOUCHER__MAIN => false,
                PRODUCT__GIFT_VOUCHER__MAIN => false
            );
            foreach ($voucherProductExistBefore as $code => $existBefore) {
                if (
                    !$contract->LoadLatestActiveContract(
                        OPTION_LEVEL_EMPLOYEE,
                        $this->GetProperty("employee_id"),
                        Product::GetProductIDByCode($code),
                        false
                    )
                ) {
                    continue;
                }

                $voucherProductExistBefore[$code] = true;
            }

            $this->SaveOptions($this->GetProperty('Product') ?? []);

            $query = "SELECT archive FROM employee WHERE employee_id=" . $this->GetProperty("employee_id");
            $archiveValue = $stmt->FetchField($query);

            //if employee receives a contract for the first time, he receives permissions
            $contractAfter = new Contract("product");
            $existBaseContractAfter = $contractAfter->ContractExistWithoutDate(
                OPTION_LEVEL_EMPLOYEE,
                $this->GetProperty("employee_id"),
                $baseMainProductID
            );
            $basePermissionNeeded = !$existBaseContractBefore && $existBaseContractAfter;

            foreach ($voucherProductExistBefore as $code => $existBefore) {
                if (
                    !$contract->ContractExistWithoutDate(
                        OPTION_LEVEL_EMPLOYEE,
                        $this->GetProperty("employee_id"),
                        Product::GetProductIDByCode($code)
                    )
                ) {
                    continue;
                }

                $voucherProductExistAfter[$code] = true;
            }
            $webShopPermissionNeeded = !$voucherProductExistBefore[PRODUCT__BENEFIT_VOUCHER__MAIN] && $voucherProductExistAfter[PRODUCT__BENEFIT_VOUCHER__MAIN];
            if ($basePermissionNeeded || $webShopPermissionNeeded) {
                //load user permissions so we don't lose them
                $userForPermissions = new User();
                $userForPermissions->LoadByID($user->GetProperty("user_id"));

                $userPermissionList = $userForPermissions->GetProperty("PermissionList");

                $userPermissionIDs = array();
                foreach ($userPermissionList as $permission) {
                    $userPermissionIDs[] = $permission["permission_id"];
                }

                $linkIDs = array();
                foreach ($userPermissionList as $permission) {
                    if (!$permission["link_id"]) {
                        continue;
                    }

                    $linkIDs[$permission["permission_id"]][] = $permission["link_id"];
                }

                $permissionIDs = array_unique($userPermissionIDs);
                if ($basePermissionNeeded) {
                    if (count($permissionIDs) > 0) {
                        $permissionIDs[] = User::GetPermissionID("api");
                    } else {
                        $permissionIDs = array(User::GetPermissionID("api"));
                    }
                }
                if ($webShopPermissionNeeded) {
                    if (count($permissionIDs) > 0) {
                        $permissionIDs[] = User::GetPermissionID("web_shop");
                    } else {
                        $permissionIDs = array(User::GetPermissionID("web_shop"));
                    }
                }

                $user->UpdatePermissions(User::ValidatePermissionList($permissionIDs), $linkIDs);

                $this->SetProperty("archive", "N");
                $activateString = "archive='N',";
            }

            if ($this->HasErrors()) {
                return false;
            }

            $baseContract = new Contract("product");
            $baseContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $this->GetProperty("employee_id"),
                $baseMainProductID,
                false
            );
            //deactivate user only if base module end_date is the past date. otherwise it will be deactivated by cron on the next day after end_date
            if (
                isset($baseContract)
                && !empty($baseContract->GetProperty("end_date"))
                && strtotime($baseContract->GetProperty("end_date")) < strtotime(GetCurrentDate())
                && $archiveValue == "N"
                && (!$isImport || !$saveFromContact)
            ) {
                $this->End($baseContract->GetProperty("end_date"));
            }

            //activate user if base module start_date is the past date (only manually)
            if (
                isset($baseContract)
                && (empty($baseContract->GetProperty("end_date"))
                    || strtotime($baseContract->GetProperty("end_date")) >= strtotime(GetCurrentDate())
                )
                && !empty($baseContract->GetProperty("start_date"))
                && strtotime($baseContract->GetProperty("start_date")) < strtotime(GetCurrentDate())
                && $archiveValue == "Y"
            ) {
                $employeeList = new EmployeeList($this->module);
                $employeeList->Activate(array($this->GetProperty("employee_id")));
            }

            //save employee banking details before saving
            $employeeBeforeSave = new Employee($this->module);
            $employeeBeforeSave->LoadByID($this->GetProperty("employee_id"));
            $ibanBeforeSave = trim($employeeBeforeSave->GetProperty("iban"));
            $binBeforeSave = trim($employeeBeforeSave->GetProperty("bic"));
            $bankNameBeforeSave = trim($employeeBeforeSave->GetProperty("bank_name"));
        }

        if ($this->HasErrors()) {
            return false;
        }

        $query = $this->GetIntProperty("employee_id") > 0 ? "UPDATE employee SET
						company_unit_id=" . $this->GetPropertyForSQL("company_unit_id") . ",
						material_status=" . $this->GetPropertyForSQL("material_status") . ", 
						child_count=" . $this->GetPropertyForSQL("child_count") . ",
						iban=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("iban")) . ", 
						bic=" . $this->GetPropertyForSQL("bic") . ", 
						bank_name=" . $this->GetPropertyForSQL("bank_name") . ", 
						start_date=" . Connection::GetSQLDate($this->GetProperty("start_date")) . ", 
						working_days_per_week=" . $this->GetPropertyForSQL("working_days_per_week") . ", 
						cost_center_number=" . $this->GetPropertyForSQL("cost_center_number") . ",
						employee_guid=" . $this->GetPropertyForSQL("employee_guid") . ",
						active_contract_number=" . $this->GetPropertyForSQL("active_contract_number") . ",						
						comment=" . $this->GetPropertyForSQL("comment") . ",
						work_place=" . $this->GetPropertyForSQL("work_place") . ",
						" . $activateString . "
						acc_meal_value_tax_flat=" . $this->GetPropertyForSQL("acc_meal_value_tax_flat") . ",
						acc_food_subsidy_tax_free=" . $this->GetPropertyForSQL("acc_food_subsidy_tax_free") . ", 
						acc_gross_salary=" . $this->GetPropertyForSQL("acc_gross_salary") . ", 
						acc_grant_of_materials=" . $this->GetPropertyForSQL("acc_grant_of_materials") . ", 
						acc_internet_subsidy_tax=" . $this->GetPropertyForSQL("acc_internet_subsidy_tax") . ", 
						acc_mobile_subsidy_tax_free=" . $this->GetPropertyForSQL("acc_mobile_subsidy_tax_free") . ", 
						acc_recreation_subsidy_tax_flat=" . $this->GetPropertyForSQL("acc_recreation_subsidy_tax_flat") . ", 
						acc_net_income=" . $this->GetPropertyForSQL("acc_net_income") . ",
                        acc_bonus_tax_flat=" . $this->GetPropertyForSQL("acc_bonus_tax_flat") . ",
                        acc_transport_tax_free=" . $this->GetPropertyForSQL("acc_transport_tax_free") . ",
                        acc_child_care_tax_free=" . $this->GetPropertyForSQL("acc_child_care_tax_free") . ",
                        acc_travel_tax_free=" . $this->GetPropertyForSQL("acc_travel_tax_free") . ",
                        acc_ticket=" . $this->GetPropertyForSQL("acc_ticket") . ",
                        acc_accommodation=" . $this->GetPropertyForSQL("acc_accommodation") . ",
                        acc_hospitality=" . $this->GetPropertyForSQL("acc_hospitality") . ",
                        acc_parking=" . $this->GetPropertyForSQL("acc_parking") . ",
                        acc_other=" . $this->GetPropertyForSQL("acc_other") . ",
                        acc_travel_costs=" . $this->GetPropertyForSQL("acc_travel_costs") . ",
                        acc_creditor=" . $this->GetPropertyForSQL("acc_creditor") . ",
                        acc_daily_allowance=" . $this->GetPropertyForSQL("acc_daily_allowance") . ",
                        acc_gift=" . $this->GetPropertyForSQL("acc_gift") . ",
                        acc_corporate_health_management=" . $this->GetPropertyForSQL("acc_corporate_health_management") . ",
                        yearly_total_benefits=" . $this->GetPropertyForSQL("yearly_total_benefits") . "
					WHERE employee_id=" . $this->GetIntProperty("employee_id") : "INSERT INTO employee (company_unit_id, material_status, child_count, iban, bic, bank_name, start_date, working_days_per_week, cost_center_number, employee_guid, active_contract_number, comment, acc_meal_value_tax_flat, 
							acc_food_subsidy_tax_free, acc_gross_salary, acc_grant_of_materials, acc_internet_subsidy_tax, acc_mobile_subsidy_tax_free, acc_recreation_subsidy_tax_flat, acc_net_income, acc_bonus_tax_flat, acc_transport_tax_free, acc_child_care_tax_free, acc_travel_tax_free, work_place,
							acc_daily_allowance, acc_gift, acc_corporate_health_management,
							acc_ticket, acc_accommodation, acc_hospitality, acc_parking, acc_other, acc_travel_costs, acc_creditor,
							yearly_total_benefits) VALUES (
						" . $this->GetPropertyForSQL("company_unit_id") . ", 						
						" . $this->GetPropertyForSQL("material_status") . ",
						" . $this->GetPropertyForSQL("child_count") . ",
						" . Connection::GetSQLEncryption($this->GetPropertyForSQL("iban")) . ",
						" . $this->GetPropertyForSQL("bic") . ",
						" . $this->GetPropertyForSQL("bank_name") . ",
						" . Connection::GetSQLDate($this->GetProperty("start_date")) . ",
						" . $this->GetPropertyForSQL("working_days_per_week") . ",
						" . $this->GetPropertyForSQL("cost_center_number") . ",
						" . $this->GetPropertyForSQL("employee_guid") . ",
						" . $this->GetPropertyForSQL("active_contract_number") . ",					
						" . $this->GetPropertyForSQL("comment") . ",  
						" . $this->GetPropertyForSQL("acc_meal_value_tax_flat") . ",
						" . $this->GetPropertyForSQL("acc_food_subsidy_tax_free") . ",
						" . $this->GetPropertyForSQL("acc_gross_salary") . ",
						" . $this->GetPropertyForSQL("acc_grant_of_materials") . ",
						" . $this->GetPropertyForSQL("acc_internet_subsidy_tax") . ",
						" . $this->GetPropertyForSQL("acc_mobile_subsidy_tax_free") . ",
						" . $this->GetPropertyForSQL("acc_recreation_subsidy_tax_flat") . ",
						" . $this->GetPropertyForSQL("acc_net_income") . ",
                        " . $this->GetPropertyForSQL("acc_bonus_tax_flat") . ",
                        " . $this->GetPropertyForSQL("acc_transport_tax_free") . ",
                        " . $this->GetPropertyForSQL("acc_child_care_tax_free") . ",
                        " . $this->GetPropertyForSQL("acc_travel_tax_free") . ",
                        " . $this->GetPropertyForSQL("work_place") . ",
                        " . $this->GetPropertyForSQL("acc_daily_allowance") . ",
                        " . $this->GetPropertyForSQL("acc_gift") . ",
                        " . $this->GetPropertyForSQL("acc_corporate_health_management") . ",
                        " . $this->GetPropertyForSQL("acc_ticket") . ",
                        " . $this->GetPropertyForSQL("acc_accommodation") . ",
                        " . $this->GetPropertyForSQL("acc_hospitality") . ",
                        " . $this->GetPropertyForSQL("acc_parking") . ",
                        " . $this->GetPropertyForSQL("acc_other") . ",
                        " . $this->GetPropertyForSQL("acc_travel_costs") . ",
                        " . $this->GetPropertyForSQL("acc_creditor") . ",
                        " . $this->GetPropertyForSQL("yearly_total_benefits") . ")
					RETURNING employee_id";
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("employee_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        $this->AddMessage("saved");

        if (!$this->GetIntProperty("employee_id") > 0) {
            $this->SetProperty("employee_id", $stmt->GetLastInsertID());
        }
        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }
        //create or update linked user
        if (!$this->GetIntProperty("user_id") && !$user->GetProperty("password1")) {
            //if password is not set then save empty password and generate it later
            $user->SetProperty("password1", "");
            $user->SetProperty("password2", "");
        }

        $companyUnit = new CompanyUnit($this->module);
        $companyUnit->LoadByID($this->GetProperty("company_unit_id"));
        $user->SetProperty("belongs_to_company", $companyUnit->GetProperty("title"));

        $userSave = $saveFromContact ? $user->SaveFromContact() : $user->SaveFromEmployee();

        if (!$userSave) {
            $this->AppendErrorsFromObject($user);
            $this->AppendErrorFieldsFromObject($user);

            return false;
        }

        if (!$this->GetIntProperty("user_id") || $this->GetIntProperty("linked_user_id")) {
            $user->SetProperty("company_unit_title", $companyUnit->GetProperty("title"));
            $user->SetProperty("company_unit_reg_email_text", $companyUnit->GetProperty("reg_email_text"));

            if (!$this->GetIntProperty("linked_user_id") && $saveFromContact) {
                $user->SendPasswordToEmail(true, true);
            } elseif (!$user->GetProperty("password1") && !$this->GetIntProperty("user_id")) {
                $user->SendPasswordToEmail(true);
            }

            $query = "UPDATE employee SET user_id=" . $user->GetIntProperty("user_id") . " WHERE employee_id=" . $this->GetIntProperty("employee_id");
            $stmt->Execute($query);
        }
        $this->SetProperty("user_id", $user->GetProperty("user_id"));
        $this->SetProperty("user_image", $user->GetProperty("user_image"));
        if ($new) {
            $option = new Option("product");
            $option->SaveOptionValue(
                OPTION_LEVEL_EMPLOYEE,
                Option::GetOptionIDByCode(OPTION__BENEFIT__MAIN__RECEIPT_OPTION),
                Option::GetOptionValue(
                    OPTION_LEVEL_COMPANY_UNIT,
                    OPTION__BENEFIT__MAIN__RECEIPT_OPTION,
                    $this->GetIntProperty("company_unit_id"),
                    GetCurrentDateTime()
                ),
                $this->GetIntProperty("employee_id")
            );
        } else {
            //sent email, if employee banking details changed
            $adminUser = new User();
            $adminUser->LoadBySession();
            if ($adminUser->IsPropertySet("user_id")) {
                $createdFrom = User::GetNameByID($adminUser->GetProperty("user_id"));
            } else {
                $createdFrom = User::GetNameByID($user->GetProperty("user_id"));
            }

            $created = GetCurrentDate();
            $bankingDetailsInfo = array();

            if ($bankNameBeforeSave != trim($this->GetProperty("bank_name"))) {
                $bankingDetailsInfo[] = array(
                    "field_name" => "Bank Name",
                    "created_from" => $createdFrom,
                    "created" => $created
                );
            }
            if ($ibanBeforeSave != trim($this->GetProperty("iban"))) {
                $bankingDetailsInfo[] = array(
                    "field_name" => "IBAN",
                    "created_from" => $createdFrom,
                    "created" => $created
                );
            }
            if ($binBeforeSave != trim($this->GetProperty("bic"))) {
                $bankingDetailsInfo[] = array(
                    "field_name" => "BIC",
                    "created_from" => $createdFrom,
                    "created" => $created
                );
            }
            if (count($bankingDetailsInfo) > 0) {
                if ($user->SendBankingDetailsEmail($bankingDetailsInfo)) {
                    $this->AddMessage("notification-sent");
                }

                //clear column for update master data export
                $query = "UPDATE employee SET master_data_export_update_id=NULL WHERE employee_id=" . $this->GetIntProperty("employee_id");
                $stmt->Execute($query);
            }

            $contract = new Contract("product");
            foreach ($voucherProductExistBefore as $code => $existBefore) {
                if (
                    !$existBefore && $contract->LoadLatestActiveContract(
                        OPTION_LEVEL_EMPLOYEE,
                        $this->GetProperty("employee_id"),
                        Product::GetProductIDByCode($code),
                        false
                    )
                ) {
                    //clear column for new master data export
                    $query = "UPDATE employee SET master_data_export_id=NULL WHERE employee_id=" . $this->GetIntProperty("employee_id");
                    $stmt->Execute($query);
                    break;
                }
            }
        }
        $this->PrepareBeforeShow();

        return true;
    }

    /**
     * Validates input data when trying to create/update contact.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        if ($this->GetIntProperty("company_unit_id") <= 0) {
            $this->AddError("employee-company-unit-empty", $this->module);
            $this->AddErrorField("company_unit_id");
        }
        if (!$this->ValidateNotEmpty("working_days_per_week")) {
            $this->SetProperty("working_days_per_week", 5);
        }
        if (!$this->ValidateNotEmpty("material_status")) {
            $this->SetProperty("material_status", "married");
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        return !$this->HasErrors();
    }

    /**
     * Returns value of employee's fields from database
     *
     * @param int $employeeID
     * @param string $field
     *
     * @return mixed
     */
    public static function GetEmployeeField($employeeID, $field)
    {
        $stmt = GetStatement(DB_PERSONAL);

        return $stmt->FetchField("SELECT " . $field . " FROM employee WHERE employee_id=" . intval($employeeID));
    }

    /**
     * Generates and saves to DB creditor_number
     *
     * @param $employeeID
     */
    public static function SetCreditorNumber($employeeID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $guidSuffix = 80000000 - 1;

        $query = "SELECT MAX(RIGHT(creditor_number, 8)::int) FROM employee WHERE creditor_number IS NOT NULL";
        $max = $stmt->FetchField($query);
        if ($max != null && $max <= 99999998) {
            $guidSuffix = $max;
        }

        $creditorNumber = $guidSuffix + 1;

        $query = "UPDATE employee SET creditor_number=" . Connection::GetSQLString($creditorNumber) . " 
					WHERE employee_id=" . intval($employeeID) . " AND creditor_number IS NULL";
        $stmt->Execute($query);
    }

    /**
     * Sets the new value of employee's fields in database
     *
     * @param int $employeeID
     * @param string $field
     * @param string $value
     *
     * @return bool|NULL
     */
    public static function SetEmployeeField($employeeID, $field, $value)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = in_array($field, ["iban"])
            ? "UPDATE employee SET " . $field . "=" . Connection::GetSQLEncryption(Connection::GetSQLString($value)) . " WHERE employee_id=" . intval($employeeID)
            : "UPDATE employee SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE employee_id=" . intval($employeeID);

        return $stmt->Execute($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $employeeID
     * @param bool $orderDesc
     * @param string $languageCode
     *
     * @return array list of values
     */
    public static function GetPropertyValueListEmployee(
        $property,
        $employeeID,
        $orderDesc = false,
        $languageCode = null,
        $appendName = true
    ) {
        if ($property == "password1") {
            $property = "password";
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id FROM employee WHERE employee_id=" . intval($employeeID);
        $userID = $stmt->FetchField($query);

        $stmt = GetStatement(DB_CONTROL);

        $orderBy = $orderDesc ? " ORDER BY created DESC" : " ORDER BY created ASC";

        $query = "SELECT value_id, user_id, created, value, property_name, created_from
					FROM employee_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND employee_id=" . intval($employeeID) . "
				  UNION
				  SELECT value_id, start_user_id AS user_id, created, value, property_name, created_from
					FROM user_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND end_user_id=" . intval($userID) . " 
					" . $orderBy;
        $valueList = $stmt->FetchList($query);
        $stmt = GetStatement(DB_MAIN);
        for ($i = 0; $i < count($valueList); $i++) {
            if ($valueList[$i]["property_name"] == "birthday" && $valueList[$i]["value"]) {
                $valueList[$i]["value"] = date("d.m.Y", strtotime($valueList[$i]["value"]));
            }

            if ($valueList[$i]["property_name"] == "start_date" && $valueList[$i]["value"]) {
                $valueList[$i]["value"] = date("d.m.Y", strtotime($valueList[$i]["value"]));
            }

            if ($valueList[$i]["property_name"] == "company_unit_id") {
                $valueList[$i]["value"] = $stmt->FetchField("SELECT " . Connection::GetSQLDecryption("title") . " as title FROM company_unit WHERE company_unit_id=" . intval($valueList[$i]["value"]));
            }

            if ($valueList[$i]["property_name"] == "material_status") {
                if ($valueList[$i]["value"] == "single") {
                    $valueList[$i]["value"] = GetTranslation(
                        "material-status-single",
                        "company",
                        array(),
                        $languageCode
                    );
                }
                if ($valueList[$i]["value"] == "married") {
                    $valueList[$i]["value"] = GetTranslation(
                        "material-status-married",
                        "company",
                        array(),
                        $languageCode
                    );
                }
            }

            if (!$appendName) {
                continue;
            }

            $valueList[$i]["user_name"] = User::GetNameByID($valueList[$i]["user_id"]);
        }

        return $valueList;
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
        if (!$user->IsPropertySet("user_id")) {
            $user->SetProperty("user_id", $this->GetProperty("user_id"));
        }

        $propertyList = array(
            "material_status",
            "child_count",
            "iban",
            "bic",
            "bank_name",
            "company_unit_id",
            "start_date",
            "cost_center_number",
            "working_days_per_week",
            "employee_guid",
            "active_contract_number",
            "comment",
            "acc_meal_value_tax_flat",
            "acc_food_subsidy_tax_free",
            "acc_gross_salary",
            "acc_grant_of_materials",
            "acc_internet_subsidy_tax",
            "acc_mobile_subsidy_tax_free",
            "acc_recreation_subsidy_tax_flat",
            "acc_net_income",
            "acc_bonus_tax_flat",
            "acc_transport_tax_free",
            "acc_child_care_tax_free",
            "acc_travel_tax_free",
            "work_place",
            "license_version",
            "guideline_version",
            "org_guideline_version",
            "archive",
            "acc_daily_allowance",
            "acc_gift",
            "acc_corporate_health_management",
            "acc_ticket",
            "acc_accommodation",
            "acc_hospitality",
            "acc_parking",
            "acc_other",
            "acc_travel_costs",
            "acc_creditor",
            "yearly_total_benefits"
        );

        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }
        if (!$this->GetProperty("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        foreach ($propertyList as $key) {
            if (($key == "birthday" || $key == "start_date") && $this->GetProperty($key)) {
                $this->SetProperty($key, date("Y-m-d", strtotime($this->GetProperty($key))));
            }

            if (!$this->IsPropertySet($key) || $currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            $query = "INSERT INTO employee_history (employee_id, property_name, value, created, user_id, created_from)
                VALUES (
                " . $this->GetIntProperty("employee_id") . ",
                " . Connection::GetSQLString($key) . ",
                " . Connection::GetSQLString($this->GetProperty($key)) . ",
                " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                " . $user->GetIntProperty("user_id") . ",
                " . Connection::GetSQLString($this->GetProperty("created_from")) . ")
                RETURNING value_id";
            if (!$stmt->Execute($query)) {
                return false;
            }
        }

        if (!$this->GetIntProperty("value_id") > 0) {
            $this->SetProperty("value_id", $stmt->GetLastInsertID());
        }

        return true;
    }

    /**
     * Returns array of current value of properties
     *
     * @param int $id employee_id whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT e.company_unit_id,
						e.material_status, e.child_count, e.bic, e.bank_name, e.start_date, e.working_days_per_week, e.cost_center_number, e.employee_guid, e.active_contract_number, e.comment,
						e.acc_meal_value_tax_flat, e.acc_food_subsidy_tax_free, e.acc_gross_salary, e.acc_grant_of_materials,
						e.acc_internet_subsidy_tax, e.acc_mobile_subsidy_tax_free, e.acc_recreation_subsidy_tax_flat, e.acc_net_income, e.acc_bonus_tax_flat, e.acc_transport_tax_free, e.acc_child_care_tax_free, e.acc_travel_tax_free,
						u.salutation, u.birthday, u.email, u.phone, e.work_place,
						e.license_version, e.guideline_version, e.org_guideline_version,
						u.zip_code, u.country, u.city, u.house, 
						" . Connection::GetSQLDecryption("u.first_name") . " AS first_name,
						" . Connection::GetSQLDecryption("u.last_name") . " AS last_name,
						" . Connection::GetSQLDecryption("u.street") . " AS street,
						" . Connection::GetSQLDecryption("e.iban") . " AS iban,
						e.acc_daily_allowance, e.acc_gift, e.acc_corporate_health_management,
						e.acc_ticket, e.acc_accommodation, e.acc_hospitality,
						e.acc_parking, e.acc_other, e.acc_travel_costs, e.acc_creditor,
						e.yearly_total_benefits
					FROM employee AS e
						JOIN user_info AS u ON u.user_id=e.user_id
					WHERE e.employee_id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $employeeID
     * @param int $dateTime
     *
     * @return array value properties
     */
    public static function GetPropertyHistoryValueEmployee($property, $employeeID, $dateTime)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name, created_from
					FROM employee_history
					WHERE property_name=" . Connection::GetSQLString($property) . "
                        AND employee_id=" . intval($employeeID) . "
                        AND created<=" . Connection::GetSQLDateTime($dateTime) . "
					ORDER BY created DESC";

        return $stmt->FetchRow($query);
    }

    /**
     * Return name of employee by id.
     *
     * @param int $id of required employee
     *
     * @return string
     */
    static function GetNameByID($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . Connection::GetSQLDecryption("u.first_name") . " AS first_name, " . Connection::GetSQLDecryption("u.last_name") . " AS last_name FROM employee AS e JOIN user_info AS u ON u.user_id=e.user_id WHERE employee_id=" . intval($id);
        $result = $stmt->FetchRow($query);

        return trim($result['first_name']) . " " . trim($result['last_name']);
    }

    private function SaveOptions(array $products)
    {
        $moduleProduct = "product";
        $saved = 0;

        $billingUser = new User();
        $billingUser->LoadByID(BILLING_USER_ID);

        $user = new User();
        $user->LoadBySession();
        if (!$user->Validate(["root"])) {
            foreach ($products as $id => $product) {
                if (!empty($product["start_date"])) {
                    $currentMonthFirstDayTime = strtotime(date("Y-m-01"));
                    $startDateTime = strtotime($product['start_date']);
                    $currentDateTime = strtotime(GetCurrentDate());

                    if ($id == Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)) {
                        if ($startDateTime >= $currentDateTime) {
                            continue;
                        }

                        $this->AddError(
                            "interruption-start-date-not-future",
                            $this->module,
                            ["product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                        );
                    } else {
                        if (
                            $startDateTime == $currentMonthFirstDayTime ||
                            ($startDateTime > $currentDateTime && date("d", $startDateTime) == '01')
                        ) {
                            continue;
                        }

                        $this->AddError(
                            "voucher-start-date-not-1st",
                            $this->module,
                            ["product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                        );
                    }
                    $this->AddErrorField("Product[" . $id . "][start_date]", "product");

                    return false;
                }

                if (!empty($product["end_date"])) {
                    $endDateMonthEnd = strtotime(date("Y-m-t", strtotime($product['end_date'])));
                    $currentMonthEnd = strtotime(date("Y-m-t"));
                    $endDateTime = strtotime($product['end_date']);

                    if ($endDateTime == $endDateMonthEnd && $endDateTime >= $currentMonthEnd) {
                        continue;
                    }

                    $this->AddError(
                        "contract-end-date-not-future",
                        $this->module,
                        ["product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                    );
                    $this->AddErrorField("Product[" . $id . "][end_date]", "product");

                    return false;
                }

                if (!empty($product["date_of_params"])) {
                    $dateOfParams = strtotime($product["date_of_params"]);
                    $firstDayCurrentMonth = strtotime(date("Y-m-1"));
                    $currentDay = strtotime(date("Y-m-d"));

                    if (
                        $dateOfParams < $firstDayCurrentMonth ||
                        (date("d", $dateOfParams) != 1 && $currentDay != $dateOfParams)
                    ) {
                        $this->AddError(
                            "date-of-params-is-wrong",
                            $this->module,
                            ["product" => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                        );
                        $this->AddErrorField("Product[" . $id . "][date_of_params]", "product");

                        return false;
                    }
                }
            }
        }

        $contractList = [];

        $baseModuleID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
        $baseModuleKey = $baseModuleID;

        $mainProductID = Product::GetProductIDByCode(PRODUCT__BASE__MAIN);
        $deactivationReasonOptionID = Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON);
        if ($products[$mainProductID]["end_date"] != "" && $products[$mainProductID]["Option"][$deactivationReasonOptionID] == "") {
            $this->AddError("deactivation-reason-is-empty", $this->module);
            $this->AddErrorField("Product[" . $mainProductID . "][Option][" . $deactivationReasonOptionID . "]", "product");

            return false;
        }
        $prevDeactivationReason = Option::GetCurrentValue(
            OPTION_LEVEL_EMPLOYEE,
            $deactivationReasonOptionID,
            $this->GetProperty("employee_id")
        );

        //move base module to end of array "products" for correct update end_date and start_date
        if (count($products) > 0) {
            $productsWithoutBaseModule = $products;
            unset($productsWithoutBaseModule[$baseModuleID]);
            $productsWithoutBaseModule[$baseModuleID] = $products[$baseModuleID];
            $products = $productsWithoutBaseModule;
        }

        foreach ($products as $id => $product) {
            $product['start_date'] = $product["start_date"] ?? null;
            $product['end_date'] = $product["end_date"] ?? null;

            $companyUnitIDs = CompanyUnitList::GetCompanyUnitPath2Root($this->GetProperty("company_unit_id"), true);
            $companyContract = new Contract($moduleProduct);
            foreach ($companyUnitIDs as $companyUnitID) {
                if ($companyContract->LoadLatestActiveContract(OPTION_LEVEL_COMPANY_UNIT, $companyUnitID, $id)) {
                    break;
                }
            }

            if (Product::GetProductCodeByID($id) != PRODUCT__BASE__INTERRUPTION) {
                if (
                    !$companyContract->GetProperty("start_date") &&
                    isset($product['start_date']) &&
                    $product['start_date']
                ) {
                    $this->AddError(
                        "error-product-notset",
                        "product",
                        ['product' => GetTranslation("product-" . Product::GetProductCodeByID($id), "product")]
                    );
                    $this->AddErrorField("Product[" . $id . "][start_date]");
                    $products[$id]["error"] = "error-product-notset";
                    continue;
                }

                if (
                    isset($product['start_date']) && $product['start_date'] &&
                    (strtotime($companyContract->GetProperty("start_date")) > strtotime($product['start_date']))
                ) {
                    $this->AddError(
                        "error-product-start_date",
                        "product",
                        [
                            'product' => GetTranslation("product-" . Product::GetProductCodeByID($id), "product"),
                            'start_date' => FormatDate("d.m.Y", $companyContract->GetProperty("start_date")),
                        ]
                    );
                    $this->AddErrorField("Product[" . $id . "][start_date]");
                    $products[$id]["error"] = "error-product-start_date";
                    continue;
                }
            }

            if (Product::GetProductCodeByID($id) != PRODUCT__BASE__MAIN) {
                $baseContract = new Contract($moduleProduct);
                $result = $baseContract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $this->GetProperty("employee_id"),
                    Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
                );

                $baseContract = $result == false ? $products[$baseModuleID] : $baseContract->GetProperties();

                if (!$baseContract["start_date"] && $product['start_date']) {
                    $this->AddError(
                        "error-employee-base-product-notset",
                        "product",
                        ['base_product' => GetTranslation("product-" . PRODUCT__BASE__MAIN, "product")]
                    );
                    $this->AddErrorField("Product[" . Product::GetProductIDByCode(PRODUCT__BASE__MAIN) . "][start_date]");
                    break;
                }

                if (
                    $product['start_date'] &&
                    (strtotime($baseContract["start_date"]) > strtotime($product['start_date']))
                ) {
                    $this->AddError(
                        "error-employee-base-product-start_date",
                        "product",
                        [
                            'product' => GetTranslation("product-" . Product::GetProductCodeByID($id), "product"),
                            'base_product' => GetTranslation("product-" . PRODUCT__BASE__MAIN, "product"),
                            'start_date' => FormatDate("d.m.Y", $baseContract["start_date"]),
                        ]
                    );
                    $this->AddErrorField("Product[" . $id . "][start_date]");
                    continue;
                }
            }

            $optionList = new OptionList($moduleProduct);
            $optionList->LoadOptionListForAdmin($id, OPTION_LEVEL_EMPLOYEE);

            $created = null;
            $duplicate = false;
            if (isset($product['date_of_params']) && $product['date_of_params']) {
                $created = $product['date_of_params'];
            } elseif (
                isset($product["start_date"]) &&
                $product["start_date"] &&
                (strtotime($product["start_date"]) < time())
            ) {
                $created = $product['start_date'];
                $duplicate = true;
            }

            if (Product::GetProductCodeByID($id) == PRODUCT__FOOD__MAIN) {
                $foodFlexOption = Option::GetOptionIDByCode(OPTION__FOOD__MAIN__FLEX_OPTION);
                if (isset($product["Option"][$foodFlexOption])) {
                    $unitsForTransfer = Option::GetOptionIDByCode(OPTION__FOOD__MAIN__UNITS_FOR_TRANSFER);
                    $currentFlex = Option::GetOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $foodFlexOption,
                        $this->GetProperty("employee_id"),
                        $created
                    );
                    if ($currentFlex != $product["Option"][$foodFlexOption]) {
                        $currentTransfer = Option::GetOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            $unitsForTransfer,
                            $this->GetProperty("employee_id"),
                            $created
                        );
                        if ($product["Option"][$foodFlexOption] == "Y") {
                            $product["Option"][$unitsForTransfer] = 0;
                        } elseif ($currentTransfer == $product["Option"][$foodFlexOption]) {
                            $product["Option"][$unitsForTransfer] = null;
                        }
                    }
                }
            }

            if (Product::GetProductCodeByID($id) == PRODUCT__AD__MAIN) {
                $adContract = new Contract($moduleProduct);
                $activeContract = $adContract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $this->GetProperty("employee_id"),
                    $id,
                    true
                );
                if (
                    $activeContract
                    || !empty($product['start_date'])
                    && (empty($product['end_date'])
                    || strtotime($product['end_date']) >= GetCurrentDate())
                ) {
                    $adReceiptOption = Option::GetOptionIDByCode(OPTION__AD__MAIN__RECEIPT_OPTION);
                    if (isset($product["Option"][$adReceiptOption])) {
                        $yearlyPaymentMonth = Option::GetOptionIDByCode(OPTION__AD__MAIN__PAYMENT_MONTH);
                        $yearlyPaymentMonthValue = $product["Option"][$yearlyPaymentMonth] != null
                            ? $product["Option"][$yearlyPaymentMonth]
                            : Option::GetInheritableOptionValue(
                                OPTION_LEVEL_COMPANY_UNIT,
                                OPTION__AD__MAIN__PAYMENT_MONTH,
                                $this->GetProperty("company_unit_id"),
                                $created ?? GetCurrentDate()
                            );
                        $currentReceiptOption = Option::GetInheritableOptionValue(
                            OPTION_LEVEL_EMPLOYEE,
                            OPTION__AD__MAIN__RECEIPT_OPTION,
                            $this->GetProperty("employee_id"),
                            $created ?? GetCurrentDate()
                        );
                        if (
                            ($product["Option"][$adReceiptOption] == "yearly"
                                || $product["Option"][$adReceiptOption] == null && $currentReceiptOption == "yearly")
                            && $yearlyPaymentMonthValue == null
                        ) {
                            $this->AddError(
                                "error-ad-yearly-payment-month-empty",
                                "product"
                            );
                            $this->AddErrorField("Product[" . $id . "][Option][" . $yearlyPaymentMonth . "]");
                            continue;
                        }
                    }
                }
            }

            foreach ($optionList->GetItems() as $item) {
                $option = new Option($moduleProduct);
                $validFromCurrentDate = false;

                if ($item["option_id"] == $deactivationReasonOptionID) {
                    $mainContract = new Contract($moduleProduct);
                    $activeMainContract = $mainContract->LoadLatestActiveContract(
                        OPTION_LEVEL_EMPLOYEE,
                        $this->GetProperty("employee_id"),
                        $id,
                        true
                    );

                    // save deactivation reason only for active contracts
                    if (!$activeMainContract && empty($product["start_date"])) {
                        continue;
                    }

                    // deactivation reason should be valid from current date
                    $validFromCurrentDate = true;
                }

                if ($duplicate) {
                    $result = $option->SaveOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $item["option_id"],
                        $product["Option"][$item["option_id"]] ?? null,
                        $this->GetProperty("employee_id"),
                        $validFromCurrentDate ? null : $created,
                        $billingUser
                    );
                } else {
                    $result = $option->SaveOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        $item["option_id"],
                        $product["Option"][$item["option_id"]] ?? null,
                        $this->GetProperty("employee_id"),
                        $validFromCurrentDate ? null : $created,
                        $user
                    );
                }
                if (!$result) {
                    $this->AppendErrorsFromObject($option);
                    $this->AppendErrorFieldsFromObject($option);
                }
                $this->AppendMessagesFromObject($option);
            }
            $saved++;
            $contract = new Contract($moduleProduct);
            $result = $contract->OnOptionUpdate(
                OPTION_LEVEL_EMPLOYEE,
                $id,
                $this->GetIntProperty("employee_id"),
                $product["contract_id"] ?? null,
                $product["start_date"] ?? null,
                $product["end_date"] ?? null
            );

            $voucherProductList = ProductList::GetVoucherProductList(true);
            $productCodeList = array_column($voucherProductList, "code");
            if (!$result) {
                $this->AppendErrorsFromObject($contract);
                $this->AppendErrorFieldsFromObject($contract);
                break;
            }

            if (
                in_array(
                    Product::GetProductCodeByID($id),
                    $productCodeList
                ) && $this->GetProperty("creditor_number") == null
            ) {
                $this->SetProperty(
                    "creditor_number",
                    Employee::GetEmployeeField($this->GetProperty("employee_id"), "creditor_number")
                );
            }
            $contractList[] = $contract->GetProperties();
        }

        //since base module is being saved the last,
        // if it wasn't saved then we need to delete all created contracts as well
        $baseContract = new Contract($moduleProduct);
        $result = $baseContract->LoadLatestActiveContract(
            OPTION_LEVEL_EMPLOYEE,
            $this->GetProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN)
        );
        if (!empty($products[$baseModuleKey]["error"]) && $result == false) {
            foreach ($contractList as $contractData) {
                $contract = new Contract($moduleProduct);
                if (!isset($contractData["contract_id"])) {
                    continue;
                }

                $contract->DeleteContract($contractData["contract_id"], OPTION_LEVEL_EMPLOYEE);
            }
        }

        //new contracts shouldn't have intersection with interruption contract
        $interruptionContract = new Contract($moduleProduct);
        if (
            $interruptionContract->LoadLatestActiveContract(
                OPTION_LEVEL_EMPLOYEE,
                $this->GetProperty("employee_id"),
                Product::GetProductIDByCode(PRODUCT__BASE__INTERRUPTION)
            )
        ) {
            $stmt = GetStatement(DB_CONTROL);
            $where = [];

            if ($interruptionContract->GetProperty("end_date")) {
                $where[] = "start_date <= " . Connection::GetSQLDate($interruptionContract->GetProperty("end_date"));
            }

            $where[] = "start_date > " . Connection::GetSQLDate($interruptionContract->GetProperty("start_date"));
            $where[] = "start_date > " . Connection::GetSQLDate(GetCurrentDate());
            $where[] = "employee_id =" . intval($this->GetProperty("employee_id"));

            $query = "SELECT contract_id FROM employee_contract WHERE " . implode(" AND ", $where);

            $contractIDs = array_keys($stmt->FetchIndexedList($query));

            $contract = new Contract($moduleProduct);

            foreach ($contractIDs as $contractID) {
                $contract->DeleteContract($contractID, OPTION_LEVEL_EMPLOYEE);
            }
        }

        if ($saved > 0) {
            $link = $this->GetProperty('Link');
            Operation::Save($link, "employee", "employee_save_option", $this->GetProperty("employee_id"));
        }

        //Send email about expired food vouchers, if employee fired
        $currentDeactivationReason = Option::GetCurrentValue(
            OPTION_LEVEL_EMPLOYEE,
            Option::GetOptionIDByCode(OPTION__BASE__MAIN__DEACTIVATION_REASON),
            $this->GetIntProperty('employee_id')
        );

        if ($currentDeactivationReason == "end" && $prevDeactivationReason <> $currentDeactivationReason) {
            $this->SendEmailExpiredVouchers();
        }

        return true;
    }

    /**
     * Action on employee's contract end.
     *
     * @param String $end_date - date of employee's contract end
     */
    private function End($end_date)
    {
        $employeeList = new EmployeeList($this->module);
        $employeeList->Remove(array($this->GetProperty("employee_id")), "admin", $end_date);
    }

    /**
     * Finishes all the contracts of employee and deactivates him when end_date of base module contract was passed
     *
     * @param int $employeeID
     * @param string $endDate
     * @param bool $endBase do we need to end base module?
     * @param bool $removeList for cases when $employeeList->Remove isn't needed
     */
    public function EndByCron($employeeID, $endDate, $endBase = false, $removeList = true)
    {
        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
            $productList = new ProductList("product");
            $productList->LoadProductListForAdmin($productGroupList->_items[$i]["group_id"]);
            for ($j = 0; $j < $productList->GetCountItems(); $j++) {
                $contract = new Contract("product");
                $contract->LoadLatestActiveContract(
                    OPTION_LEVEL_EMPLOYEE,
                    $employeeID,
                    $productList->_items[$j]["product_id"]
                );

                if (
                    ($productList->_items[$j]["code"] == PRODUCT__BASE__MAIN && !$endBase) ||
                    !$contract->GetProperty("contract_id")
                ) {
                    continue;
                }

                $contract->OnOptionUpdate(
                    OPTION_LEVEL_EMPLOYEE,
                    $productList->_items[$j]["product_id"],
                    $employeeID,
                    $contract->GetProperty("contract_id"),
                    null,
                    $endDate
                );
            }

            if (isset($voucherListExpired[$voucher["amount_left"]])) {
                $voucherListExpired[$voucher["amount_left"]]["count"] += 1;
            } else {
                $voucherListExpired[$voucher["amount_left"]]["count"] = 0;
            }

            $voucherListExpired[$voucher["amount_left"]]["voucher_ids"][] = $voucher["voucher_id"];
        }

        foreach ($voucherListExpired as $amount => $vouchers) {
            $voucherListExpiredResult[] = array(
                "open_amount" => $amount,
                "count" => $vouchers["count"],
                "voucher_ids" => implode(", ", $vouchers["voucher_ids"]),
                "product_name" => GetTranslation("product-group-" . PRODUCT_GROUP__FOOD_VOUCHER, "product")
            );
        }

        if (!$removeList) {
            return;
        }

        $employeeList = new EmployeeList($this->module);
        $employeeList->Remove([$employeeID], "admin", $endDate);
    }

    /**
     * Loads info about activations and inactivations of this employee from employee_history
     *
     * @return mixed array as result or false on fail.
     */
    public function LoadArchiveInfo()
    {
        if (!$this->GetProperty("employee_id")) {
            return false;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM employee_history 
                    WHERE employee_id=" . $this->GetProperty("employee_id") . " AND property_name='archive'
                    ORDER BY created DESC";
        if (!$result = $stmt->FetchList($query)) {
            return false;
        }

        $userList = array_column($result, "user_id");
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " AS username, user_id FROM user_info WHERE user_id IN (" . implode(
            ",",
            $userList
        ) . ")";
        $userList = $stmt->FetchIndexedList($query, "user_id");
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['username'] = $userList[$result[$i]['user_id']]['username'];
        }

        return $result;
    }

    /**
     * Updates employee's fields "license_version" and "guideline_version" after their accepting in mobile application
     *
     * @param int $licenseVersion
     * @param int $guidelineVersion
     *
     * @return bool|NULL
     */
    public function SetAcceptedDocumentsVersions($licenseVersion, $guidelineVersion, $orgGuidelineVersion)
    {
        $stmt = GetStatement(DB_PERSONAL);

        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("employee_id"));

        $query = "UPDATE employee
					SET license_version=" . intval($licenseVersion) . ",
						guideline_version=" . intval($guidelineVersion) . ",
                        org_guideline_version=" . intval($orgGuidelineVersion) . "
			  		WHERE employee_id=" . $this->GetIntProperty("employee_id");

        if ($stmt->Execute($query)) {
            $this->SetProperty("license_version", $licenseVersion);
            $this->SetProperty("guideline_version", $guidelineVersion);
            $this->SetProperty("org_guideline_version", $orgGuidelineVersion);
            $this->SaveHistory($currentPropertyList);

            return true;
        }

        return false;
    }

    /**
     * Returns array of accepted documents ("license and terms", "guidelines", "organizational guidelines")
     *
     * @return array
     */
    public function GetAcceptedDocumentList($employeeViewer = false)
    {
        $documentList = array();

        // config
        $typeList = array(
            array("code" => "app_license", "last_version_property" => "license_version"),
            array("code" => "app_guideline", "last_version_property" => "guideline_version"),
            array("code" => "app_org_guideline", "last_version_property" => "org_guideline_version")
        );
        foreach ($typeList as $type) {
            $versionList = Config::GetConfigHistory($type["code"], true);
            foreach ($versionList as $key => $version) {
                $versionList[$key]["created"] = $version["date_from"];
            }

            $documentList[] = array(
                "code" => $type["code"],
                "title_translation" => GetTranslation("config-" . $type["code"]),
                "accepted_version_list" => self::GetPropertyValueListEmployee(
                    $type["last_version_property"],
                    $this->GetProperty("employee_id"),
                    true,
                    null,
                    false
                ),
                "version_list" => $versionList
            );
        }

        // agreements
        $productGroupList = new ProductGroupList('product');
        $productGroupList->LoadProductGroupListForAdmin();
        foreach ($productGroupList->getItems() as $productGroup) {
            // accepted agreements
            $agreementsEmployee = new AgreementEmployeeList('agreements');
            $agreementsEmployee->LoadByEmployeeID($this, $productGroup["group_id"]);

            $agreementsEmployeeResult = array();
            foreach ($agreementsEmployee->GetItems() as $agreement) {
                $agreement['pdf_link'] = ADMIN_PATH . 'module.php?load=' . "agreements" .
                    '&Section=Employees&OrganizationID=' . $this->GetIntProperty('company_unit_id') .
                    '&Employee=' . intval($this->GetIntProperty('employee_id')) .
                    '&PdfAgreementId=' . intval($agreement['agreement_id']);
                $agreement["created"] = $agreement["updated_at"];
                unset($agreement["updated_at"]);
                $agreementsEmployeeResult[] = $agreement;
            }

            if ($employeeViewer) {
                $specificProductGroup = SpecificProductGroupFactory::Create($productGroup["group_id"]);
                if ($specificProductGroup == null) {
                    continue;
                }
                $mainProductCode = $specificProductGroup->GetMainProductCode();

                $contract = new Contract($this->module);
                if (
                    !$contract->LoadLatestActiveContract(
                        OPTION_LEVEL_EMPLOYEE,
                        $this->GetProperty("employee_id"),
                        Product::GetProductIDByCode($mainProductCode)
                    )
                ) {
                    continue;
                }

                // all versions agreements
                $agreementCompanyUnit = new AgreementsContract('agreements');
                $agreementCompanyUnit->LoadForApi($productGroup["group_id"], $this);

                $agreementHistoryListResult = array();
                $agreementHistoryListResult[] =
                    [
                        'agreement' => 1,
                        'edit_link' => ADMIN_PATH . 'module.php?load=' . "agreements" .
                            '&OrganizationID=' . $this->GetIntProperty('company_unit_id') .
                            '&AgreementID=' . intval($agreementCompanyUnit->GetProperty('agreement_id')) .
                            '&Version=' . intval($agreementCompanyUnit->GetProperty('version')) .
                            '&EmployeeID=' . $this->GetIntProperty('employee_id'),
                    ];

                if ($agreementCompanyUnit->IsPropertySet('version')) {
                    $documentList[] = array(
                        "agreement" => true,
                        "code" => $productGroup["code"],
                        "title_translation" => $productGroup["title_translation"],
                        "accepted_version_list" => $agreementsEmployeeResult,
                        "version_list" => $agreementHistoryListResult
                    );
                }
            } else {
                if (count($agreementsEmployeeResult) > 0) {
                    $documentList[] = array(
                        "agreement" => true,
                        "code" => $productGroup["code"],
                        "title_translation" => $productGroup["title_translation"],
                        "accepted_version_list" => $agreementsEmployeeResult,
                        "version_list" => false
                    );
                }
            }
        }

        // confirmation

        // accepted confirmation
        $confirmationsEmployee = new RecreationConfirmationList("agreements");
        $confirmationsEmployee->LoadByEmployeeID($this->GetIntProperty('employee_id'));
        $confirmationsEmployeeResult = array();

        foreach ($confirmationsEmployee->getItems() as $confirmation) {
            $confirmation['pdf_link'] = ADMIN_PATH . 'module.php?load=' . "agreements" .
                '&Section=confirmation&CompanyUnitID=' . $this->GetIntProperty('company_unit_id') .
                '&Action=GetConfirmationPDF' .
                '&ConfirmationID=' . intval($confirmation['id']);
            $confirmation['version'] = $confirmation['id'];
            unset($confirmation["id"]);
            $confirmationsEmployeeResult[] = $confirmation;
        }
        /*
                // all versions confirmation
                $confirmationCompanyUnit = new RecreationConfirmation("agreements");
                $confirmationCompanyUnit->LoadByCompanyUnitID($this->GetIntProperty('company_unit_id'));
                $confirmationCompanyUnitResult = $confirmationCompanyUnit->GetProperties();
                $confirmationCompanyUnitResult['edit_link'] = ADMIN_PATH.'module.php?load='."agreements".
                        '&Section=confirmation&CompanyUnitID='.$this->GetIntProperty('company_unit_id').
                        '&ConfirmationID='.intval($confirmationCompanyUnitResult['confirmation_id']);
                $confirmationCompanyUnitResult['version'] = $confirmationCompanyUnitResult['confirmation_id'];
                $confirmationCompanyUnitResult['created'] = $confirmationCompanyUnitResult['updated_at'];
                unset($confirmationCompanyUnitResult["confirmation_id"]);
                unset($confirmationCompanyUnitResult["updated_at"]);
        */
        if (count($confirmationsEmployeeResult) > 0) {
            $documentList[] = array(
                "confirmation" => true,
                "code" => "confirmation",
                "title_translation" => GetTranslation("recreation-confirmations", "company"),
                "accepted_version_list" => $confirmationsEmployeeResult,
//                "version_list" => array($confirmationCompanyUnitResult)
                "version_list" => false
            );
        }

        return $documentList;
    }

    /**
     * Updates info about last client and version of mobile application
     *
     * @param string $client
     * @param string $version
     *
     * @return bool|NULL
     */
    public function SetMobileApplicationVersion($client, $version)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "UPDATE employee 
					SET last_mobile_application_client=" . Connection::GetSQLString($client) . ", 
						last_mobile_application_version=" . Connection::GetSQLString($version) . " 
					WHERE employee_id=" . $this->GetIntProperty("employee_id");

        return $stmt->Execute($query);
    }

    /**
     * Updates uses_application flag of employee
     *
     * @param string $usesApplication Y or N
     *
     * @return bool|NULL
     */
    public function SetUsesApplication($usesApplication)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "UPDATE employee 
					SET uses_application=" . Connection::GetSQLString($usesApplication) . "  
					WHERE employee_id=" . $this->GetIntProperty("employee_id");

        return $stmt->Execute($query);
    }

    /**
     * @param $accessToken string access token given by givve
     * @param $refreshToken string refresh token given by givve
     *
     * @return bool|NULL
     */
    public static function SetGivveAccessToken($employeeID, $accessToken = null, $refreshToken = null)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "UPDATE employee 
					SET
					  givve_access_token=" . Connection::GetSQLString($accessToken) . ",
					  givve_refresh_token=" . Connection::GetSQLString($refreshToken) . "
					WHERE employee_id=" . $employeeID;

        return $stmt->Execute($query);
    }

    /**
     * Sends push notification to all devices currently authorized under passed employee_id
     *
     * @param string $title message header
     * @param string $text message text
     * @param array $data additional params to be passed to mobile application
     * @param int $employeeID employee_id of receiver
     *
     * @return int $count returns number of successfully sent pushes for logging
     */
    public static function SendPushNotification($employeeID, $title, $text, $data = array(), $versionList = array())
    {
        if (IsLocalEnvironment()) {
            return true;
        }

        $employee = new Employee("company");
        $employee->LoadByID($employeeID);
        $count = 0;

        $tokenMap = array(
            "ios" => array(),
            "android" => array()
        );
        $deviceMap = array();

        $deviceList = new DeviceList();
        if (is_array($versionList) && count($versionList) > 0) {
            $deviceList->LoadDeviceListByVersion($employee->GetProperty("user_id"), $versionList);
        } else {
            $deviceList->LoadDeviceListByUserID($employee->GetProperty("user_id"));
        }

        for ($i = 0; $i < $deviceList->GetCountItems(); $i++) {
            if (strlen($deviceList->_items[$i]["push_token"]) <= 0) {
                continue;
            }

            $tokenMap[$deviceList->_items[$i]["client"]][] = $deviceList->_items[$i]["push_token"];
            $deviceMap[$deviceList->_items[$i]["push_token"]] = $deviceList->_items[$i]["device_id"];
        }

        foreach ($tokenMap as $client => $tokens) {
            if (count($tokens) <= 0) {
                continue;
            }

            if (
                !FCMManager::Send(
                    $client,
                    $tokens,
                    $title,
                    $text,
                    $data,
                    $employee->GetProperty("user_id"),
                    $deviceMap
                )
            ) {
                continue;
            }

            $count++;
        }

        return $count;
    }

    /**
     * Sends email to employee that goes by passed employee_id
     *
     * @param  $text string message text
     * * @return true|false
     */
    public function SendEmail($subject, $text)
    {
        if ($this->ValidateEmail("email")) {
            $result = SendMailFromAdminTask(
                $this->GetProperty("email"),
                $subject,
                $text
            );

            return $result === true;
        }

        return false;
    }

    /**
     * Validates user employee Role
     *
     * @param int $employeeID employee_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($employeeID, $userID = null)
    {
        if (!$employeeID) {
            return true;
        }

        $employee = new Employee("company");
        $employee->LoadByID($employeeID);

        $permissionName = "employee";

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        if ($user->Validate(array("employee_view"))) {
            $hasEditPermission = false;
            if ($user->Validate(array("employee" => null))) {
                foreach ($user->GetProperty("PermissionList") as $permission) {
                    if ($permission["name"] != "employee" || ($permission["link_id"] != $employee->GetProperty("company_unit_id") && $permission["link_id"] != null)) {
                        continue;
                    }

                    $hasEditPermission = true;
                }
            }

            if (!$hasEditPermission) {
                $stmt = GetStatement(DB_PERSONAL);
                $query = "SELECT employee_id FROM employee WHERE user_id=" . Connection::GetSQLString($user->GetProperty("user_id"));
                $employeeOfUser = $stmt->FetchField($query);

                return $employeeID == $employeeOfUser;
            }

            return true;
        }

        if ($user->Validate(array($permissionName))) {
            return true;
        } else {
            $companyUnitIDs = $user->GetPermissionLinkIDs($permissionName);
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) == 0) {
                return false;
            }
        }

        return in_array($employee->GetIntProperty("company_unit_id"), $companyUnitIDs) ? true : false;
    }

    /**
     * Returns list of replacements
     *
     * @param bool $confirmation are these replacements for recreation confirmation?
     *
     * @return array
     */
    public function GetReplacementsList($confirmation = false)
    {
        if ($confirmation) {
            $properties = array(
                "salutation",
                "first_name",
                "last_name",
                "birthday",
                "street",
                "house",
                "zip_code",
                "city",
                "work_place",
                "material_status",
                "child_count"
            );
        } else {
            $properties = array(
                "salutation",
                "first_name",
                "last_name",
                "birthday",
                "street",
                "house",
                "zip_code",
                "city",
                "work_place"
            );
        }

        $language = GetLanguage();
        if ($this->ValidateNotEmpty('birthday')) {
            $this->SetProperty("birthday", date($language->GetDateFormat(), strtotime($this->GetProperty('birthday'))));
        }

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%employee_" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, $this->module)
            );
            if ($property == "material_status") {
                if ($this->GetProperty($property) == "married") {
                    $values["employee_" . $property] = GetTranslation(
                        "material-status-married",
                        "company",
                        array(),
                        "de"
                    );
                } else {
                    $values["employee_" . $property] = GetTranslation(
                        "material-status-single",
                        "company",
                        array(),
                        "de"
                    );
                }
            } else {
                $values["employee_" . $property] = $this->GetProperty($property);
            }
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    /**
     * Requests access and refresh tokens from givve using provided identifier and password
     *
     * @param $givveLogin string identifier id
     * @param $givveAccess string password
     *
     * @return bool
     */
    public function GetGivveAccess($givveLogin, $givveAccess)
    {
        $import = new GivveTransactionImport($this->module);

        $employeeData = array();
        $employeeData["identifier"] = $givveLogin;
        $employeeData["password"] = $givveAccess;
        $employeeData["accessors"] = array();
        $employeeData["accessors"][] = "voucher_owner"; //TODO multiplier
        $dataString = json_encode($employeeData);

        $result = $import->GetAccessToken($dataString);

        if (isset($result["data"]["access_token"])) {
            Employee::SetGivveAccessToken(
                $this->GetProperty("employee_id"),
                $result["data"]["access_token"],
                $result["data"]["refresh_token"]
            );

            return true;
        }

        if (isset($result["errors"])) {
            foreach ($result["errors"] as $error) {
                if ($error["code"] == "is_missing") {
                    $this->AddError("api-password-is-missing", $this->module);
                }
                if ($error["code"] != "you_are_not_authorized") {
                    continue;
                }

                $this->AddError("api-incorrect-data", $this->module);
            }
        }

        return false;
    }

    /**
     * Gets list of employee's givve vouchers
     *
     * @return array
     */
    public function GetGivveVoucherList()
    {
        $token = array(
            "givve_access_token" => Employee::GetEmployeeField($this->GetProperty("employee_id"), "givve_access_token"),
            "givve_refresh_token" => Employee::GetEmployeeField(
                $this->GetProperty("employee_id"),
                "givve_refresh_token"
            )
        );

        $import = new GivveTransactionImport($this->module);
        $result = $import->GetVoucherList($token["givve_access_token"]);

        if (isset($result["code"]) && $result["code"] == "token_expired") {
            $refresh = array();
            $refresh["identifier"] = $token["givve_refresh_token"];
            $newToken = $import->GetAccessToken(json_encode($refresh));
            Employee::SetGivveAccessToken(
                $this->GetProperty("employee_id"),
                $newToken["data"]["access_token"],
                $newToken["data"]["refresh_token"]
            );
            $result = $import->GetVoucherList($newToken["data"]["access_token"]);
            $token = array(
                "givve_access_token" => $newToken["data"]["access_token"],
                "givve_refresh_token" => $newToken["data"]["refresh_token"]
            );
        }

        $voucherList = array();
        if (isset($result["data"])) {
            foreach ($result["data"] as $voucher) {
                $givveVoucher = $import->GetVoucher($voucher["id"], $token["givve_access_token"]);
                $givveVoucher = $givveVoucher["data"];
                $voucherList[] = array(
                    "voucher_id" => $voucher["id"],
                    "employee_id" => $this->GetProperty("employee_id"),
                    "balance" => round($givveVoucher["balance"]["cents"] / 100, 2),
                    "updated" => date("Y-m-d H:i:s", strtotime($givveVoucher["updated_at"])),
                    "address_line_1" => $givveVoucher["owner"]["address_line_1"],
                    "address_line_2" => $givveVoucher["owner"]["address_line_2"]
                );
            }

            $hasBalanceColumn = array();
            foreach ($voucherList as $voucher) {
                $hasBalanceColumn[] = $voucher["balance"] > 0 ? 1 : 0;
            }
            $updatedColumn = array_column($voucherList, "updated");

            array_multisort(
                $hasBalanceColumn,
                SORT_NUMERIC,
                SORT_DESC,
                $updatedColumn,
                SORT_STRING,
                SORT_DESC,
                $voucherList
            );
        }

        return $voucherList;
    }

    /**
     * Gets list of employee's givve transaction list from requested voucher
     *
     * @param $voucherID int voucher_id
     *
     * @return array
     */
    public function GetGivveTransactionList($voucherID)
    {
        $token = array(
            "givve_access_token" => Employee::GetEmployeeField($this->GetProperty("employee_id"), "givve_access_token"),
            "givve_refresh_token" => Employee::GetEmployeeField(
                $this->GetProperty("employee_id"),
                "givve_refresh_token"
            )
        );

        $import = new GivveTransactionImport($this->module);
        $result = $import->GetTransactionList($voucherID, $token["givve_access_token"]);

        if (isset($result["code"]) && $result["code"] == "token_expired") {
            $refresh = array();
            $refresh["identifier"] = $token["givve_refresh_token"];
            $newToken = $import->GetAccessToken(json_encode($refresh));
            Employee::SetGivveAccessToken(
                $this->GetProperty("employee_id"),
                $newToken["data"]["access_token"],
                $newToken["data"]["refresh_token"]
            );
            $result = $import->GetTransactionList($voucherID, $newToken["data"]["access_token"]);
        }

        $sourceTransactionList = $result["data"];

        //remove authorization records which have transactions
        foreach ($sourceTransactionList as $key => $outerTransaction) {
            if ($outerTransaction["type"] != "authorisation") {
                continue;
            }

            foreach ($sourceTransactionList as $innerTransaction) {
                if ($innerTransaction["link_id"] == $outerTransaction["link_id"] && $innerTransaction["id"] != $outerTransaction["id"]) {
                    unset($sourceTransactionList[$key]);
                    break;
                }
            }
        }

        $transactionList = array();
        foreach ($sourceTransactionList as $transaction) {
            $transactionList[] = array(
                "transaction_id" => $transaction["id"],
                "voucher_id" => $voucherID,
                "description" => $transaction["description"],
                "booked" => date("Y-m-d H:i:s", strtotime($transaction["booked_at"])),
                "amount" => round($transaction["amount"]["cents"] / 100, 2),
                "status" => $transaction["status"]
            );
        }

        return $transactionList;
    }

    /**
     * Send email about expired food vouchers, if employee fired
     *
     * @return bool
     */
    private function SendEmailExpiredVouchers()
    {
        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__FOOD_VOUCHER);
        if (!$specificProductGroup) {
            return false;
        }

        $lastContract = new Contract("contract");
        if (!$lastContract->LoadLatestActiveContract(
            OPTION_LEVEL_EMPLOYEE,
            $this->GetIntProperty("employee_id"),
            Product::GetProductIDByCode($specificProductGroup->GetMainProductCode()),
            false,
            true)
        ) {
            return false;
        }

        $lastContract->LoadLatestActiveContract(
            OPTION_LEVEL_EMPLOYEE,
            $this->GetIntProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__BASE__MAIN),
            false
        );

        $email = $this->GetProperty("email");
        $endDateBaseContract = date('d.m.Y', strtotime($lastContract->GetProperty('end_date')));
        $subject = "Ihr Essen Gutschein Service wurde zum " . $endDateBaseContract . " eingestellt";
        $emailTemplate = new PopupPage($this->module);
        $tmpl = $emailTemplate->Load("voucher_deactivation_notification_email.html");

        $voucherList = $specificProductGroup->GetReceiptMappedVoucherList($this->GetIntProperty("employee_id"));
        $voucherListExpired = [];
        $voucherListExpiredResult = [];

        foreach ($voucherList as $voucher) {
            if (strtotime($voucher["end_date"]) < strtotime(GetCurrentDate()) || $voucher["amount_left"] <= 0) {
                continue;
            }

            if (isset($voucherListExpired[$voucher["amount_left"]])) {
                $voucherListExpired[$voucher["amount_left"]]["count"] += 1;
            } else {
                $voucherListExpired[$voucher["amount_left"]]["count"] = 1;
            }

            $voucherListExpired[$voucher["amount_left"]]["voucher_ids"][] = $voucher["voucher_id"];
        }

        foreach ($voucherListExpired as $amount => $vouchers) {
            $voucherListExpiredResult[] = [
                "open_amount" => $amount,
                "count" => $vouchers["count"],
                "voucher_ids" => implode(", ", $vouchers["voucher_ids"]),
                "product_name" => GetTranslation("product-group-" . PRODUCT_GROUP__FOOD_VOUCHER, "product", [], "de"),
            ];
        }

        $tmpl->LoadFromObject($this);
        $tmpl->SetLoop("voucher_list", $voucherListExpiredResult);

        return SendMailFromAdmin(
            $email,
            $subject,
            $emailTemplate->Grab($tmpl),
            [],
            [["Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo"]],
            [],
            "trebono Buchhaltung - 2KS Cloud Services GmbH"
        );
    }
}
