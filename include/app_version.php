<?php

class AppVersion extends LocalObject
{
    function __construct($data = [])
    {
        parent::LocalObject($data);
    }

    public function LoadByID($id)
    {
        $query = "SELECT app_version_id, app_version, client, critical
                    FROM app_version WHERE app_version_id=" . intval($id);
        $this->LoadFromSQL($query);

        return $this->GetProperty("app_version_id") ? true : false;
    }

    /**
     * Creates or updates app_version. Object must be loaded from request before the method will be called.
     * Required properties are: version, critical
     *
     * @return bool true if app_version is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }

        $user = new User();
        $user->LoadBySession();

        $stmt = GetStatement();
        if ($this->GetIntProperty("app_version_id") > 0) {
            $query = "UPDATE app_version SET
                        app_version=" . $this->GetPropertyForSQL("app_version") . ",
                        client=" . $this->GetPropertyForSQL("client") . ",
                        critical=" . $this->GetPropertyForSQL("critical") . "
                WHERE app_version_id=" . $this->GetIntProperty("app_version_id");
        } else {
            $query = "INSERT INTO app_version (app_version, client, critical, created, created_by) VALUES (
                        " . $this->GetPropertyForSQL("app_version") . ",
                        " . $this->GetPropertyForSQL("client") . ",
                        " . $this->GetPropertyForSQL("critical") . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $user->GetIntProperty("user_id") . ")
                    RETURNING app_version_id";
        }

        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("app_version_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("app_version_id") > 0) {
            $this->SetProperty("app_version_id", $stmt->GetLastInsertID());
        }

        if (!$this->SaveHistory($currentPropertyList)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Validates input data when trying to create/update app_version from admin panel.
     * Also turns incorrect int/float properties into null.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        if (!($this->GetIntProperty("app_version_id") > 0)) {
            $this->RemoveProperty("app_version_id");
        }

        if (!$this->ValidateNotEmpty("app_version")) {
            $this->AddError("app_version-empty");
        }

        if ($this->GetProperty("critical") != "Y") {
            $this->SetProperty("critical", "N");
        }

        if ($this->GetProperty("client") != "android" && $this->GetProperty("client") != "ios") {
            $this->AddError("client-empty");
        }

        return !$this->HasErrors();
    }

    /**
     * Gets last version for client
     *
     * @param string $client client
     * @param bool $critical get critical or not version
     */
    public static function GetLastVersion($client, $critical = false)
    {
        $stmt = GetStatement();

        $query = "SELECT app_version
                FROM app_version 
                WHERE client=" . Connection::GetSQLString($client) . " 
                    " . ($critical ? "AND critical='Y'" : "") . "
                    AND archive='N'
                ORDER BY string_to_array(app_version, '.')::int[] DESC
				LIMIT 1";

        return $stmt->FetchField($query);
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

        $propertyList = ["app_version", "client", "critical"];

        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }

        foreach ($propertyList as $key) {
            if (!$this->IsPropertySet($key) || $currentPropertyList[$key] == $this->GetProperty($key)) {
                continue;
            }

            $query = "INSERT INTO app_version_history (app_version_id, property_name, value, created, user_id)
                VALUES (
                " . $this->GetIntProperty("app_version_id") . ",
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
     * @param int $id app_version_id whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT app_version, client, critical
					FROM app_version
					WHERE app_version_id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $app_version_ID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListAppVersion($property, $app_version_id, $orderASC = false)
    {
        $orderBy = $orderASC ? "ORDER BY created ASC" : "ORDER BY created DESC";

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value_id, user_id, created, value, property_name
					FROM app_version_history
					WHERE property_name=" . Connection::GetSQLString($property) . "
					    AND app_version_id=" . intval($app_version_id) . " " . $orderBy;
        $valueList = $stmt->FetchList($query);

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["first_name"] = User::GetNameByID($valueList[$i]["user_id"]);
        }

        return $valueList;
    }
}
