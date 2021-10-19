<?php

class CompanyUnitDocument extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of company unit properties to be loaded instantly
     */
    public function CompanyUnitDocument($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads contract by its id
     *
     * @param int $id
     *
     * @return bool true if loaded successfully or false otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT * FROM company_unit_document WHERE document_id=" . intval($id);
        $this->LoadFromSQL($query);

        return $this->GetProperty("document_id") ? true : false;
    }

    /**
     * Returns contract file by its history id
     *
     * @param int $id
     *
     * @return string
     */
    public function DownloadByHistoryID($id)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT h.value FROM company_unit_document_history h WHERE h.id=" . intval($id);

        return $stmt->FetchField($query);
    }

    public static function GetDocumentCount($companyUnitID)
    {
        $stmt = GetStatement();
        $query = "SELECT count(*) FROM company_unit_document 
                  WHERE company_unit_id=" . Connection::GetSQLString($companyUnitID) . " AND archive != 'Y'";

        return $stmt->FetchField($query);
    }

    /**
     * Save document
     *
     * @return bool true if saved successfully or false otherwise
     */
    public function Save()
    {
        $stmt = GetStatement();
        $new = !$this->IsPropertySet("document_id") || $this->GetIntProperty("document_id") <= 0;

        $rename = $this->IsPropertySet("rename") && $this->GetProperty("rename");

        if ($new) {
            if (self::GetDocumentCount($this->GetProperty("company_unit_id")) >= 10) {
                $this->AddError("contract-limit-reached", $this->module);
            }

            if (!$this->IsPropertySet("contract_file_name") || $this->GetProperty("contract_file_name") == null) {
                $this->AddError("contract-empty-name", $this->module);
            }
        }

        if (!$rename) {
            if (!$this->SaveFile($new)) {
                return false;
            }
        } else {
            if (!$this->IsPropertySet("contract_file_name") || $this->GetProperty("contract_file_name") == null) {
                $this->AddError("contract-empty-name", $this->module);
            }
        }

        if ($this->HasErrors()) {
            return false;
        }

        if (!$new) {
            $query = $rename ? "UPDATE company_unit_document SET title=" . $this->GetPropertyForSQL("contract_file_name") . "
                WHERE document_id=" . $this->GetPropertyForSQL("document_id") . " RETURNING document_id" : "UPDATE company_unit_document SET value=" . $this->GetPropertyForSQL("value") . " 
                WHERE document_id=" . $this->GetPropertyForSQL("document_id") . " RETURNING document_id";
        } else {
            $query = "INSERT INTO company_unit_document (company_unit_id, title, value, created, archive) VALUES 
            (" . $this->GetPropertyForSQL("company_unit_id") . ",
            " . $this->GetPropertyForSQL("contract_file_name") . ",
            " . $this->GetPropertyForSQL("value") . ",
            NOW(),
            'Y') RETURNING document_id";
        }

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if ($new) {
            $this->SetProperty("document_id", $stmt->GetLastInsertID());
            $this->Activate($this->GetProperty("document_id"));
        }

        if ($rename) {
            $this->SaveHistory(
                $this->GetProperty("document_id"),
                "title",
                $this->GetProperty("contract_file_name")
            );
        } else {
            $this->SaveHistory($this->GetProperty("document_id"), "value", $this->GetProperty("value"));
        }

        return true;
    }

    private function SaveHistory($documentID, $property, $value)
    {
        $user = new User();
        $user->LoadBySession();
        $userID = $user->GetProperty("user_id");

        $user = new User();
        $user->LoadBySession();
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO company_unit_document_history (document_id, property_name, value, user_id, created) VALUES
                (" . $documentID . "," . Connection::GetSQLString($property) . "," . Connection::GetSQLString($value) . "," . $userID . ",NOW())";

        return $stmt->Execute($query);
    }

    public function Remove($documentID)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value FROM company_unit_document_history
                    WHERE document_id=" . Connection::GetSQLString($documentID) . " AND property_name='value'";
        $fileList = $stmt->FetchList($query);

        $fileStorage = GetFileStorage(CONTAINER__COMPANY);
        foreach ($fileList as $file) {
            $fileStorage->Remove(COMPANY_UNIT_DOCUMENT_DIR . $file["value"]);
        }

        $query = "DELETE FROM company_unit_document_history WHERE document_id=" . Connection::GetSQLString($documentID);
        $stmt->execute($query);

        $this->LoadByID($documentID);
        if ($fileStorage->FileExists(COMPANY_UNIT_DOCUMENT_DIR . $this->GetProperty("value"))) {
            $fileStorage->Remove(COMPANY_UNIT_DOCUMENT_DIR . $this->GetProperty("value"));
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "DELETE FROM company_unit_document WHERE document_id=" . Connection::GetSQLString($documentID);

        return $stmt->execute($query);
    }

    public function Deactivate($documentID)
    {
        if (self::GetPropertyByID("archive", $documentID) == "Y") {
            return true;
        }

        $stmt = GetStatement();
        $query = "UPDATE company_unit_document SET archive='Y' WHERE document_id=" . Connection::GetSQLString($documentID);
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if ($this->SaveHistory($documentID, "archive", "Y")) {
            $this->AddMessage("object-disactivated", $this->module, array("Count" => 1));
        }

        return true;
    }

    public function Activate($documentID)
    {
        if (self::GetPropertyByID("archive", $documentID) == "N") {
            return true;
        }

        $stmt = GetStatement();
        $query = "UPDATE company_unit_document SET archive='N' WHERE document_id=" . Connection::GetSQLString($documentID);
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if ($this->SaveHistory($documentID, "archive", "N")) {
            $this->AddMessage("object-activated", $this->module, array("Count" => 1));
        }

        return true;
    }

    /**
     * Uploads file to storage
     */
    public function SaveFile($new)
    {
        $fileStorage = GetFileStorage(CONTAINER__COMPANY);
        foreach ($_FILES as $key => $file) {
            if (($key != "contract_file" || $new != true) && ($key != "update_contract_file" || $new != false)) {
                continue;
            }

            if ($file["size"] == 0) {
                $this->AddError("no-file-uploaded", $this->module);
                continue;
            }

            $newFile = $fileStorage->Upload($key, COMPANY_UNIT_DOCUMENT_DIR, false, array('application/pdf'));

            if ($newFile) {
                $this->SetProperty("value", $newFile["FileName"]);
            } else {
                $this->AddError("error-file-upload", $this->module);
            }

            $this->AppendErrorsFromObject($fileStorage);
        }

        return $this->HasErrors() ? false : true;
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $documentID
     * @param string $lang
     *
     * @return array list of values
     */
    public static function GetPropertyValueList($property, $documentID, $lang = null)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT id as value_id, user_id, created, value, property_name
					FROM company_unit_document_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND document_id=" . intval($documentID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["value_type"] = GetTranslation(
                "company-unit-document-" . $property,
                "company",
                array(),
                $lang
            );
            $valueList[$i]["user_name"] = User::GetNameByID($valueList[$i]["user_id"]);

            if ($property == "archive") {
                $valueList[$i]["value_text"] = $valueList[$i]["value"] == "N"
                    ? GetTranslation("Active")
                    : GetTranslation("Cancel");
            } elseif ($property == "value") {
                $valueList[$i]["value"] = $valueList[$i]["value_id"];
            }
        }

        return $valueList;
    }

    /**
     * Returns property value
     *
     * @param string $property
     * @param int $documentID
     *
     * @return string value
     */
    public static function GetPropertyByID($property, $documentID)
    {
        $stmt = GetStatement();
        $query = "SELECT " . $property . " FROM company_unit_document WHERE document_id=" . intval($documentID);

        return $stmt->FetchField($query);
    }
}
