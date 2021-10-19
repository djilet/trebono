<?php

class Contact extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contact properties to be loaded instantly
     */
    public function Contact($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads contact by its contact_id
     *
     * @param int $id contact_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT c.contact_id, c.company_unit_id, c.created, c.contact_type, 
						c.position, c.department, c.first_name, c.last_name,
						c.email, c.phone, c.phone_job, c.comment, 
                        c.contact_for_invoice, c.contact_for_contract, c.contact_for_service, c.contact_for_support, 
                        c.contact_for_payroll_export, c.contact_for_stored_data, c.contact_for_company_unit_admin, 
                        c.contact_for_employee_admin, c.salutation, c.sending_pdf_invoice, 
                        ui.user_id AS linked_user_id, " . Connection::GetSQLDecryption("ui.first_name") . "||' '||" . Connection::GetSQLDecryption("ui.first_name") . " AS user_name 
					FROM contact AS c
					LEFT JOIN user_info ui ON c.user_id=ui.user_id 
					WHERE c.contact_id=" . intval($id);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if (!$this->ValidateNotEmpty("salutation")) {
            $this->SetProperty("salutation", "Frau");
        }

        return $this->GetProperty("contact_id") ? true : false;
    }

    /**
     * Creates or updates the contact. Object must be loaded from request before the method will be called.
     * Required properties are: contact_type, position, department, first_name, last_name, email, phone, phone_job
     * If new company unit has no parent then its parent company entity will be created.
     *
     * @param bool $isImport true if contact is saved via AZ import
     *
     * @return bool true if contact is created/updated successfully or false on failure
     */
    public function Save($isImport = false)
    {
        $result1 = true;

        $user = new User();
        $employee = new Employee($this->module);
        $createUser = ($this->GetProperty("contact_for_invoice") == "Y" ||
            $this->GetProperty("contact_for_payroll_export") == "Y" ||
            $this->GetProperty("contact_for_company_unit_admin") == "Y" ||
            $this->GetProperty("contact_for_employee_admin") == "Y" ||
            $this->GetProperty("contact_for_stored_data") == "Y" ||
            $this->GetProperty("contact_for_contract") == "Y"
        );

        if ($createUser || $this->GetProperty("linked_user_id")) {
            if ($user->LoadByID($this->GetProperty("linked_user_id"))) {
                $employee->LoadByUserID($this->GetProperty("linked_user_id"));
            } else {
                $user->LoadByEmail($this->GetProperty("email"));
                if ($user->GetIntProperty("user_id")) {
                    $employee->LoadByUserID($user->GetIntProperty("user_id"));
                    $this->SetProperty("linked_user_id", $user->GetIntProperty("user_id"));
                }
            }

            if ($user->GetIntProperty("user_id")) {
                if (!$this->GetProperty("first_name")) {
                    $this->SetProperty("first_name", $user->GetProperty("first_name"));
                }
                if (!$this->GetProperty("last_name")) {
                    $this->SetProperty("last_name", $user->GetProperty("last_name"));
                }
            }

            $user->AppendFromObject($this);
            $result1 = $user->ValidateFromContact();
        }

        $result2 = $this->Validate();

        if (!$result1 || !$result2) {
            $this->AppendErrorsFromObject($user);

            return false;
        }

        $stmt = GetStatement(DB_PERSONAL);

        $query = "SELECT contact_for_invoice, contact_for_contract, contact_for_service, contact_for_support, contact_for_payroll_export,
                    contact_for_stored_data, contact_for_company_unit_admin, contact_for_employee_admin FROM contact WHERE contact_id=" . $this->GetIntProperty("contact_id");
        $this->SetProperty("OldContactFor", $stmt->FetchRow($query));

        $link_user_id = $this->GetProperty("linked_user_id") ?: "null";
        $query = $this->GetIntProperty("contact_id") > 0 ? "UPDATE contact SET
						contact_type=" . $this->GetPropertyForSQL("contact_type") . ",
						position=" . $this->GetPropertyForSQL("position") . ", 
						department=" . $this->GetPropertyForSQL("department") . ", 
						first_name=" . $this->GetPropertyForSQL("first_name") . ", 
						last_name=" . $this->GetPropertyForSQL("last_name") . ", 
						email=TRIM(" . $this->GetPropertyForSQL("email") . "),
						phone=" . $this->GetPropertyForSQL("phone") . ", 
						phone_job=" . $this->GetPropertyForSQL("phone_job") . ",
						comment=" . $this->GetPropertyForSQL("comment") . ",
                        contact_for_invoice=" . $this->GetPropertyForSQL("contact_for_invoice") . ",
                        contact_for_contract=" . $this->GetPropertyForSQL("contact_for_contract") . ", 
                        contact_for_service=" . $this->GetPropertyForSQL("contact_for_service") . ", 
                        contact_for_support=" . $this->GetPropertyForSQL("contact_for_support") . ",
                        contact_for_payroll_export=" . $this->GetPropertyForSQL("contact_for_payroll_export") . ",
                        contact_for_stored_data=" . $this->GetPropertyForSQL("contact_for_stored_data") . ",
                        contact_for_company_unit_admin=" . $this->GetPropertyForSQL("contact_for_company_unit_admin") . ",
                        contact_for_employee_admin=" . $this->GetPropertyForSQL("contact_for_employee_admin") . ",
                        salutation=" . $this->GetPropertyForSQL("salutation") . ",
                        user_id=" . $link_user_id . ",
                        sending_pdf_invoice=" . $this->GetPropertyForSQL("sending_pdf_invoice")  . "
					WHERE contact_id=" . $this->GetIntProperty("contact_id") : "INSERT INTO contact (company_unit_id, created, contact_type, position, department, first_name, last_name, 
                        email, phone, phone_job, comment, contact_for_invoice, contact_for_contract, contact_for_service, 
                        contact_for_support, contact_for_payroll_export, contact_for_stored_data, contact_for_company_unit_admin, 
                        contact_for_employee_admin, salutation, user_id, sending_pdf_invoice) 
                        VALUES (
						" . $this->GetPropertyForSQL("company_unit_id") . ", 						
						" . Connection::GetSQLString(GetCurrentDateTime()) . ", 						
						" . $this->GetPropertyForSQL("contact_type") . ",
						" . $this->GetPropertyForSQL("position") . ",
						" . $this->GetPropertyForSQL("department") . ",
						" . $this->GetPropertyForSQL("first_name") . ",
						" . $this->GetPropertyForSQL("last_name") . ",
						" . $this->GetPropertyForSQL("email") . ",
						" . $this->GetPropertyForSQL("phone") . ",
						" . $this->GetPropertyForSQL("phone_job") . ",
						" . $this->GetPropertyForSQL("comment") . ",
                        " . $this->GetPropertyForSQL("contact_for_invoice") . ",
                        " . $this->GetPropertyForSQL("contact_for_contract") . ", 
                        " . $this->GetPropertyForSQL("contact_for_service") . ", 
                        " . $this->GetPropertyForSQL("contact_for_support") . ",
                        " . $this->GetPropertyForSQL("contact_for_payroll_export") . ",
                        " . $this->GetPropertyForSQL("contact_for_stored_data") . ",
                        " . $this->GetPropertyForSQL("contact_for_company_unit_admin") . ",
                        " . $this->GetPropertyForSQL("contact_for_employee_admin") . ",
                        " . $this->GetPropertyForSQL("salutation") . ",
                        " . $link_user_id . ",
                        " . $this->GetPropertyForSQL("sending_pdf_invoice") . ") 
					RETURNING contact_id";
        $currentPropertyList = $this->GetCurrentPropertyList();

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("contact_id") > 0) {
            $this->SetProperty("contact_id", $stmt->GetLastInsertID());
        }

        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }

        //if contact is supposed to have user, update/create employee
        if ($createUser || $this->GetProperty("linked_user_id")) {
            $employee->AppendFromObject($this);
            if (!$employee->GetProperty("working_days_per_week")) {
                $employee->SetProperty("working_days_per_week", 5);
            }

            $employee->SetProperty("permission_company_unit_id", $this->GetProperty("company_unit_id"));
            $employeeCheck = new Employee($this->module);
            if ($employeeCheck->LoadByUserID($this->GetProperty("linked_user_id")) === false) {
                $employee->SetProperty("company_unit_id", $this->GetProperty("company_unit_id"));
            } else {
                $employee->SetProperty("company_unit_id", $employeeCheck->GetProperty("company_unit_id"));
            }
            $employee->SetProperty("user_id", $this->GetProperty("linked_user_id"));

            if (!$employee->Save(true, $isImport)) {
                $this->AppendErrorsFromObject($employee);

                return false;
            }

            $query = "UPDATE contact SET user_id=" . $employee->GetProperty("user_id") . " WHERE contact_id=" . $this->GetIntProperty("contact_id");
            $stmt->Execute($query);
            $this->SetProperty("linked_user_id", $employee->GetProperty("user_id"));
        }

        return true;
    }

    /**
     * Validates input data when trying to create/update contact.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        if (!$this->ValidateNotEmpty("first_name")) {
            $this->AddError("contact-first-name-empty", $this->module);
        }

        if (!$this->ValidateNotEmpty("last_name")) {
            $this->AddError("contact-last-name-empty", $this->module);
        }

        if (!$this->ValidateNotEmpty("contact_type")) {
            $this->AddError("contact-type-empty", $this->module);
        }

        if (!$this->ValidateEmail("email")) {
            $this->AddError("incorrect-email-format");
        }

        $contactForList = array(
            "contact_for_invoice",
            "contact_for_contract",
            "contact_for_service",
            "contact_for_support",
            "contact_for_payroll_export",
            "contact_for_stored_data",
            "contact_for_company_unit_admin",
            "contact_for_employee_admin"
        );

        foreach ($contactForList as $contactFor) {
            if ($this->GetProperty($contactFor) == 'Y') {
                continue;
            }

            $this->SetProperty($contactFor, 'N');
        }

        if (!$this->IsPropertySet('contact_for')) {
            $this->SetProperty("contact_for", array());
        }

        if (!$this->IsPropertySet('position')) {
            $this->SetProperty("position", "");
        }
        if (!$this->IsPropertySet('department')) {
            $this->SetProperty("department", "");
        }
        if (!$this->IsPropertySet('phone')) {
            $this->SetProperty("phone", "");
        }
        if (!$this->IsPropertySet('phone_job')) {
            $this->SetProperty("phone_job", "");
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        if ($this->GetProperty("sending_pdf_invoice") != "Y") {
            $this->SetProperty("sending_pdf_invoice", "N");
        }

        return !$this->HasErrors();
    }

    /**
     * Returns list of replacements
     *
     * @return array
     */
    public function GetReplacementsList()
    {
        $properties = array(
            "salutation",
            "first_name",
            "last_name",
            "birthday",
            "street",
            "house",
            "zip_code",
            "city"
        );

        $replacements = array();
        $values = array();
        foreach ($properties as $property) {
            $replacements[] = array(
                "template" => "%contact_" . $property . "%",
                "translation" => GetTranslation("replacement-" . $property, $this->module)
            );
            $values["contact_" . $property] = $this->GetProperty($property);
        }

        return array("ReplacementList" => $replacements, "ValueList" => $values);
    }

    /**
     * Return name of contact by id.
     *
     * @param int $id of required contact
     *
     * @return string
     */
    static function GetNameByID($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT first_name, last_name FROM contact WHERE contact_id=" . intval($id);

        return implode(" ", $stmt->FetchRow($query));
    }

    static function GetPropertyByID($id, $property)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . $property . " FROM contact WHERE contact_id=" . intval($id);

        return $stmt->FetchField($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $contactID
     * @param null $languageCode
     *
     * @return array list of values
     */
    public static function GetPropertyValueListContact($property, $contactID, $languageCode = null)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value_id, user_id, created, value, property_name, created_from, agreed_text
					FROM contact_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND contact_id=" . intval($contactID) . " ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["user_name"] = User::GetNameByID($valueList[$i]["user_id"]);

            if ($valueList[$i]['property_name'] == "contact_type") {
                $valueList[$i]['value'] = GetTranslation(
                    "contact-type-" . $valueList[$i]["value"],
                    "company",
                    null,
                    $languageCode
                );
            }

            if ($property == "contact_for") {
                $contactForList = array(
                    "invoice",
                    "contract",
                    "service",
                    "support",
                    "payroll_export",
                    "stored_data",
                    "company_unit_admin",
                    "employee_admin",
                    "company_unit",
                    "employee"
                );
                foreach ($contactForList as $cf) {
                    $valueList[$i]['value'] = str_replace(
                        $cf,
                        GetTranslation("contact-for-" . $cf, "company", null, $languageCode),
                        $valueList[$i]['value']
                    );
                }
            }

            if ($property != "linked_user_id") {
                continue;
            }

            $valueList[$i]['value'] = $stmt->FetchField("SELECT " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("lst_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["value"]));
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

        $propertyList = array(
            "contact_type",
            "salutation",
            "first_name",
            "last_name",
            "position",
            "department",
            "email",
            "phone",
            "phone_job",
            "comment",
            "contact_for",
            "linked_user_id",
            "sending_pdf_invoice"
        );
        $contactForList = array(
            "contact_for_invoice",
            "contact_for_contract",
            "contact_for_service",
            "contact_for_support",
            "contact_for_payroll_export",
            "contact_for_stored_data",
            "contact_for_company_unit_admin",
            "contact_for_employee_admin"
        );
        $propertyList = array_merge($propertyList, $contactForList);
        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }

        if (count($this->GetProperty("contact_for")) == 0) {
            $contactFor = array();
            foreach ($contactForList as $key) {
                if (
                    (gettype($currentPropertyList[$key]) != "array" || !symm_diff(
                        $currentPropertyList[$key],
                        $this->GetProperty($key)
                    )) &&
                    ($currentPropertyList[$key] == $this->GetProperty($key) || ($currentPropertyList[$key] == null && $this->GetProperty($key) != "Y"))
                ) {
                    continue;
                }

                $contactFor[] = str_replace("contact_for_", "", $key);
            }
            $this->SetProperty("contact_for", implode(", ", $contactFor));
        }

        $agreedText = Config::GetConfigValue("agreement_of_sending_pdf_invoice");
        foreach ($propertyList as $key) {
            if (
                (gettype($currentPropertyList[$key]) == "array" && symm_diff(
                    $currentPropertyList[$key],
                    $this->GetProperty($key)
                )) ||
                (gettype($this->GetProperty($key)) == "array" && $currentPropertyList[$key] == null)
            ) {
                $query = "INSERT INTO contact_history (contact_id, property_name, value, created, user_id, created_from)
                    VALUES (
                    " . $this->GetIntProperty("contact_id") . ",
                    " . Connection::GetSQLString($key) . ",
                    " . Connection::GetSQLString(implode(", ", $this->GetProperty($key))) . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . $user->GetIntProperty("user_id") . ",
                    " . Connection::GetSQLString($this->GetProperty("created_from")) . ")
                    RETURNING value_id";
                if (!$stmt->Execute($query)) {
                    return false;
                }
            } elseif ($currentPropertyList[$key] != $this->GetProperty($key)) {
                $agreedText = $key === 'sending_pdf_invoice'
                    ? Config::GetConfigValue("agreement_of_sending_pdf_invoice")
                    : null;

                $query = "INSERT INTO contact_history (contact_id, property_name, value, created, user_id, created_from, agreed_text)
					VALUES (
					" . $this->GetIntProperty("contact_id") . ",
					" . Connection::GetSQLString($key) . ",
					" . Connection::GetSQLString($this->GetProperty($key)) . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
					" . $user->GetIntProperty("user_id") . ",
					" . Connection::GetSQLString($this->GetProperty("created_from")) . ",
					" . Connection::GetSQLString($agreedText) . ")
					RETURNING value_id";
                if (!$stmt->Execute($query)) {
                    return false;
                }
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
     * @param int $id contact_id whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT contact_type, salutation, first_name, last_name, position, department, email, 
            phone, phone_job, comment, contact_for_invoice, contact_for_contract, contact_for_service,
            contact_for_support, contact_for_payroll_export, contact_for_stored_data, 
            contact_for_company_unit_admin, contact_for_employee_admin, user_id AS linked_user_id, sending_pdf_invoice
					FROM contact					  
					WHERE contact_id=" . $this->GetIntProperty("contact_id");
        $currentPropertyList = $stmt->FetchRow($query);

        if ($currentPropertyList) {
            $currentPropertyList['contact_for'] = array();
            $contactForList = array(
                "contact_for_invoice",
                "contact_for_contract",
                "contact_for_service",
                "contact_for_support",
                "contact_for_payroll_export",
                "contact_for_stored_data"
            );
            foreach ($contactForList as $cf) {
                if ($currentPropertyList[$cf] != "Y") {
                    continue;
                }

                $currentPropertyList['contact_for'][] = str_replace("contact_for_", "", $cf);
            }
        }

        return $currentPropertyList;
    }

    /**
     * Sends email to contact
     *
     * @param $subject string  email subject
     * @param $text string  message text
     *
     * @return true|false
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

    public static function GetIDByEmailAndCompanyUnit($email, $companyUnitID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT contact_id FROM contact
WHERE LOWER(email)=LOWER(" . Connection::GetSQLString($email) . ")
AND company_unit_id	= " . Connection::GetSQLString($companyUnitID);

        return $stmt->FetchField($query);
    }
}
