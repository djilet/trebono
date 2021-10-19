<?php

class PartnerContact extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contact properties to be loaded instantly
     */
    public function PartnerContact($module, $data = array())
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
        $query = "SELECT c.partner_contact_id AS contact_id, c.partner_id, c.created, c.contact_type, 
						c.position, c.department, c.first_name, c.last_name,
						c.email, c.phone, c.phone_job, c.comment,
						c.contact_for_commission,c.contact_for_service, c.contact_for_support, c.contact_for_contract,   
                        c.salutation, ui.user_id AS linked_user_id, " . Connection::GetSQLDecryption("ui.first_name") . "||' '||" . Connection::GetSQLDecryption("ui.last_name") . " AS user_name 
					FROM partner_contact AS c
					LEFT JOIN user_info ui ON c.user_id=ui.user_id 
					WHERE c.partner_contact_id=" . intval($id);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if (!$this->ValidateNotEmpty("salutation")) {
            $this->SetProperty("salutation", "Frau");
        }

        return $this->GetProperty("contact_id") ? true : false;
    }

    /**
     * Creates or updates the contact. Object must be loaded from request before the method will be called.
     * Required properties are: contact_type, position, department, first_name, last_name, email, phone, phone_job
     *
     * @return bool true if contact is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }

        $stmt = GetStatement(DB_PERSONAL);
        $link_user_id = $this->GetProperty("linked_user_id") ?: "null";
        $query = $this->GetIntProperty("contact_id") > 0 ? "UPDATE partner_contact SET
						contact_type=" . $this->GetPropertyForSQL("contact_type") . ",
						position=" . $this->GetPropertyForSQL("position") . ", 
						department=" . $this->GetPropertyForSQL("department") . ", 
						first_name=" . $this->GetPropertyForSQL("first_name") . ", 
						last_name=" . $this->GetPropertyForSQL("last_name") . ", 
						email=" . $this->GetPropertyForSQL("email") . ", 
						phone=" . $this->GetPropertyForSQL("phone") . ", 
						phone_job=" . $this->GetPropertyForSQL("phone_job") . ",
						comment=" . $this->GetPropertyForSQL("comment") . ",                      
                        salutation=" . $this->GetPropertyForSQL("salutation") . ",
                        contact_for_commission=" . $this->GetPropertyForSQL("contact_for_commission") . ",
                        contact_for_contract=" . $this->GetPropertyForSQL("contact_for_contract") . ", 
                        contact_for_service=" . $this->GetPropertyForSQL("contact_for_service") . ", 
                        contact_for_support=" . $this->GetPropertyForSQL("contact_for_support") . ",
                        user_id=" . $link_user_id . "
					WHERE partner_contact_id=" . $this->GetIntProperty("contact_id") : "INSERT INTO partner_contact (partner_id, created, contact_type, position, department, first_name, last_name, email, phone, phone_job, comment, 
                        salutation, contact_for_commission, contact_for_contract, contact_for_service, contact_for_support, user_id) 
                        VALUES (
						" . $this->GetPropertyForSQL("partner_id") . ", 						
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
                        " . $this->GetPropertyForSQL("salutation") . ",
                        " . $this->GetPropertyForSQL("contact_for_commission") . ",
                        " . $this->GetPropertyForSQL("contact_for_contract") . ", 
                        " . $this->GetPropertyForSQL("contact_for_service") . ", 
                        " . $this->GetPropertyForSQL("contact_for_support") . ",
                        " . $link_user_id . ") 
					RETURNING partner_contact_id";
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

        if ($this->GetProperty('contact_for_commission') != 'Y') {
            $this->SetProperty("contact_for_commission", 'N');
        }

        if ($this->GetProperty("contact_for_contract") != "Y") {
            $this->SetProperty("contact_for_contract", "N");
        }

        if ($this->GetProperty("contact_for_service") != "Y") {
            $this->SetProperty("contact_for_service", "N");
        }

        if ($this->GetProperty("contact_for_support") != "Y") {
            $this->SetProperty("contact_for_support", "N");
        }

        return !$this->HasErrors();
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
        $query = "SELECT first_name, last_name FROM partner_contact WHERE partner_contact_id=" . intval($id);

        return implode(" ", $stmt->FetchRow($query));
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $contactID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListContact($property, $contactID)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value_id, user_id, created, value, property_name
					FROM partner_contact_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND contact_id=" . intval($contactID);
        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];

            if ($valueList[$i]['property_name'] == "contact_type") {
                $valueList[$i]['value'] = GetTranslation("contact-type-" . $valueList[$i]["value"], "company");
            }

            if ($property == "contact_for") {
                $contactForList = array("commission", "contract", "service", "support");
                foreach ($contactForList as $cf) {
                    $valueList[$i]['value'] = str_replace(
                        $cf,
                        GetTranslation("contact-for-" . $cf, "partner"),
                        $valueList[$i]['value']
                    );
                }
            }

            if ($property != "linked_user_id") {
                continue;
            }

            $valueList[$i]['value'] = $stmt->FetchField("SELECT " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " FROM user_info WHERE user_id=" . intval($valueList[$i]["value"]));
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
            "linked_user_id"
        );
        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
            $currentPropertyList['contact_for'] = array();
        }
        foreach ($propertyList as $key) {
            if (
                gettype($currentPropertyList[$key]) == "array" && symm_diff(
                    $currentPropertyList[$key],
                    $this->GetProperty($key)
                )
            ) {
                $query = "INSERT INTO partner_contact_history (contact_id, property_name, value, created, user_id)
					VALUES (
					" . $this->GetIntProperty("contact_id") . ",
					" . Connection::GetSQLString($key) . ",
					" . Connection::GetSQLString(implode(", ", $this->GetProperty($key))) . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
					" . $user->GetIntProperty("user_id") . ")
					RETURNING value_id";
                if (!$stmt->Execute($query)) {
                    return false;
                }
            } elseif ($currentPropertyList[$key] != $this->GetProperty($key)) {
                $query = "INSERT INTO partner_contact_history (contact_id, property_name, value, created, user_id)
					VALUES (
					" . $this->GetIntProperty("contact_id") . ",
					" . Connection::GetSQLString($key) . ",
					" . Connection::GetSQLString($this->GetProperty($key)) . ",
					" . Connection::GetSQLString(GetCurrentDateTime()) . ",
					" . $user->GetIntProperty("user_id") . ")
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
            phone, phone_job, comment, contact_for_commission, contact_for_contract, contact_for_service,
            contact_for_support, user_id AS linked_user_id 
					FROM partner_contact					  
					WHERE partner_contact_id=" . $this->GetIntProperty("contact_id");
        $currentPropertyList = $stmt->FetchRow($query);

        if ($currentPropertyList) {
            $currentPropertyList['contact_for'] = array();
            $contactForList = array(
                "contact_for_commission",
                "contact_for_contract",
                "contact_for_service",
                "contact_for_support"
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
}
