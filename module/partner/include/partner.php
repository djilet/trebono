<?php

class Partner extends LocalObject
{
    private $_acceptMimeTypes = array(
        'image/png',
        'image/x-png'
    );
    private $module;
    private $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of partner properties to be loaded instantly
     */
    public function Partner($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = [];
    }

    /**
     * Loads partner by its PartnerID
     *
     * @param int $id PartnerID
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT u.\"PartnerID\" AS partner_id, u.created,
                        u.company_unit_id,
						u.title, u.phone, u.email, u.zip_code, u.country, u.city, u.street, u.house, 
						u.comment, u.bank_details, u.bic, u.register,
                        u.tax_number, u.tax_consultant,
                        " . Connection::GetSQLDecryption("u.iban") . " AS iban
					FROM partner u
					WHERE u.\"PartnerID\"=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("partner_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareBeforeShow()
    {
        if (!$this->GetProperty("company_unit_id")) {
            return;
        }

        $stmt = GetStatement();
        $this->SetProperty(
            "company_title",
            $stmt->FetchField("SELECT " . Connection::GetSQLDecryption("title") . " AS title FROM company_unit WHERE company_unit_id=" . $this->GetPropertyForSQL("company_unit_id"))
        );
    }

    /**
     * Creates or updates the partner. Object must be loaded from request before the method will be called.
     * Required properties are: title, phone, email, zip_code, country, city, street, house, vat_payer_id, comment
     *
     * @return bool true if partner is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }

        $stmt = GetStatement();

        $company_unit_id = $this->GetProperty("company_unit_id") ?: "null";
        $query = $this->GetIntProperty("partner_id") > 0 ? "UPDATE partner SET 											  
						title=" . $this->GetPropertyForSQL("title") . ",
						company_unit_id=" . $company_unit_id . ",
						phone=" . $this->GetPropertyForSQL("phone") . ",
						email=" . $this->GetPropertyForSQL("email") . ",
						zip_code=" . $this->GetPropertyForSQL("zip_code") . ",
						country=" . $this->GetPropertyForSQL("country") . ",
						city=" . $this->GetPropertyForSQL("city") . ",
						street=" . $this->GetPropertyForSQL("street") . ",
						house=" . $this->GetPropertyForSQL("house") . ",										  
						comment=" . $this->GetPropertyForSQL("comment") . ",
                        bank_details=" . $this->GetPropertyForSQL("bank_details") . ",
                        iban=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("iban")) . ",
                        bic=" . $this->GetPropertyForSQL("bic") . ",
                        register=" . $this->GetPropertyForSQL("register") . ",
                        tax_number=" . $this->GetPropertyForSQL("tax_number") . ",
                        tax_consultant=" . $this->GetPropertyForSQL("tax_consultant") . "                        
					WHERE \"PartnerID\"=" . $this->GetIntProperty("partner_id") : "INSERT INTO partner (created, company_unit_id, title, phone, email, zip_code, country, city, street, house, comment,
                        bank_details, iban, bic, register, tax_number, tax_consultant) 
                        VALUES (					  
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $company_unit_id . ",
						" . $this->GetPropertyForSQL("title") . ",
						" . $this->GetPropertyForSQL("phone") . ", 
						" . $this->GetPropertyForSQL("email") . ",
						" . $this->GetPropertyForSQL("zip_code") . ",
						" . $this->GetPropertyForSQL("country") . ",
						" . $this->GetPropertyForSQL("city") . ",
						" . $this->GetPropertyForSQL("street") . ",
						" . $this->GetPropertyForSQL("house") . ",											  
						" . $this->GetPropertyForSQL("comment") . ",
                        " . $this->GetPropertyForSQL("bank_details") . ",
                        " . Connection::GetSQLEncryption($this->GetPropertyForSQL("iban")) . ",
                        " . $this->GetPropertyForSQL("bic") . ",
                        " . $this->GetPropertyForSQL("register") . ",
                        " . $this->GetPropertyForSQL("tax_number") . ",
                        " . $this->GetPropertyForSQL("tax_consultant") . ")                        
					RETURNING \"PartnerID\"";
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("partner_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("partner_id") > 0) {
            $this->SetProperty("partner_id", $stmt->GetLastInsertID());
        }
        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Validates input data when trying to create/update partner from admin panel. Also turns incorrect int/float properties into null.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {

        if (!$this->ValidateNotEmpty("title")) {
            $this->AddError("partner-title-empty", $this->module);
        }

        return !$this->HasErrors();
    }

    /**
     * Returns value of selected property
     *
     * @param string $property property of option which value to be returned
     * @param int $partnerID PartnerID whose value to be returned
     *
     * @return string $value of property
     */
    public static function GetPropertyValue($property, $partnerID)
    {
        $stmt = GetStatement();

        $query = "SELECT " . $property . " FROM partner WHERE \"PartnerID\"=" . $partnerID;

        return $stmt->FetchField($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $partnerID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListPartner($property, $partnerID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name
					FROM partner_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND partner_id=" . intval($partnerID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        $stmtMain = GetStatement(DB_MAIN);
        for ($i = 0; $i < count($valueList); $i++) {
            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];
            if ($property != "company_unit_id") {
                continue;
            }

            $valueList[$i]['value'] = $stmtMain->FetchField("SELECT " . Connection::GetSQLDecryption("title") . " AS title FROM company_unit WHERE company_unit_id=" . intval($valueList[$i]["value"]));
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
            "title",
            "company_unit_id",
            "street",
            "house",
            "zip_code",
            "city",
            "country",
            "phone",
            "email",
            "bank_details",
            "iban",
            "bic",
            "register",
            "tax_number",
            "comment",
            "tax_consultant"
        );
        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }

        foreach ($propertyList as $key) {
            if ($currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            $query = "INSERT INTO partner_history (partner_id, property_name, value, created, user_id)
            VALUES (
            " . $this->GetIntProperty("partner_id") . ",
            " . Connection::GetSQLString($key) . ",
            " . Connection::GetSQLString($this->GetProperty($key)) . ",
            " . Connection::GetSQLString(GetCurrentDateTime()) . ",
            " . $user->GetIntProperty("user_id") . ")
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
     * @param int $id PartnerID whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement();
        $query = "SELECT u.company_unit_id,
						u.title, u.phone, u.email, u.zip_code, u.country, u.city, u.street, u.house, 
						u.comment, u.bank_details, u.bic, u.register,
                        u.tax_number, u.tax_consultant,
                        " . Connection::GetSQLDecryption("u.iban") . " AS iban
					FROM partner AS u 					   
					WHERE u.\"PartnerID\"=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Return title of partner by id.
     *
     * @param int $id of required partner
     *
     * @return string
     */
    static function GetTitleByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT title FROM partner WHERE \"PartnerID\"=" . intval($id);

        return $stmt->FetchField($query);
    }
}
