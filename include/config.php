<?php

class Config extends LocalObject
{
    const CODE_VAT_EXCEPTION_7_SHOP = "vat_exception_7_shop";
    const CODE_VAT_EXCEPTION_19_SHOP = "vat_exception_19_shop";
    const CODE_VAT_EXCEPTION_RESTAURANT = "vat_exception_restaurant";

    private static $historyCodeList = array(
        "app_license",
        "app_guideline",
        "export_datev_lug_ini",
        "app_org_guideline",
        "business_terms_1",
        "business_terms_2",
        "business_terms_3",
        "business_terms_4",
        "business_terms_5",
        "business_terms_6",
        "receipt_verificator_payment_review",
        "receipt_verificator_payment_supervisor",
        "receipt_verificator_payment_approve_proposed",
        "receipt_verificator_payment_denied",
        "invoice_vat",
        "voucher_invoice_vat",
        "agreement_of_sending_pdf_invoice",
    );

    /**
     * Loads config by its config_id
     *
     * @param int $id config_id
     *
     * @return bool true if loaded successfully or false otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT config_id, code, value, group_code, editor, updated FROM config WHERE config_id=" . intval($id);
        $this->LoadFromSQL($query);

        if (in_array($this->GetProperty("code"), $this::$historyCodeList)) {
            $currentOptionValue = self::GetConfigValueByDate($this->GetProperty("code"), GetCurrentDate());
            $this->SetProperty("value", $currentOptionValue);
        }

        if ($this->GetProperty("config_id")) {
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Prepares config's title translation
     */
    private function PrepareBeforeShow()
    {
        if ($this->GetProperty("group_code") == "o_option") {
            $optionTypes = array("__monthly_price", "__implementation_price");
            $product = str_replace($optionTypes, "", $this->GetProperty("code"));
            $this->SetProperty("title_translation", GetTranslation(
                "product-" . $product,
                "product"
            ) . " " . GetTranslation("option-" . $this->GetProperty("code"), "product"));
        } else {
            $this->SetProperty("title_translation", GetTranslation("config-" . $this->GetProperty("code")));
        }

        if ($this->GetProperty("editor") == "file") {
            if (strpos($this->GetProperty("value"), ".pdf") !== false) {
                $path = ADMIN_PATH . "/config.php?config_id=" . $this->GetProperty("config_id") . "&Action=DownloadPDF";
                $this->SetProperty("value_download_url", $path);
            } else {
                PrepareDownloadPath($this->_properties, "value", CONFIG_FILE_DIR, CONTAINER__CORE);
                $this->SetProperty("value_download_url", $this->GetProperty("value_download_url"));
            }
        }
        $this->SetProperty("editor_type", explode("-", $this->GetProperty("editor"))[0]);
    }

    /**
     * Updates config's value. Config object bust be loaded before this function will be called.
     * Required properties are: config_id, value.
     *
     * @return bool true if updated successfully or false otherwise
     */
    public function Save()
    {
        $stmt = GetStatement();

        if (!$this->PrepareBeforeSave()) {
            return false;
        }

        $currentValue = in_array($this->GetProperty("code"), $this::$historyCodeList) && $this->IsPropertySet("date_from")
            ? self::GetConfigValueByDate($this->GetProperty("code"), $this->GetProperty("date_from"))
            : self::GetConfigValue($this->GetProperty("code"));

        $query = "UPDATE config 
					SET value=" . $this->GetPropertyForSQL("value") . ",
						updated=" . Connection::GetSQLString(GetCurrentDateTime()) . "
					WHERE config_id=" . $this->GetIntProperty("config_id");

        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        // save only license and datev lug ini history
        if ($this->GetProperty("value") != $currentValue && self::DoesConfigHaveHistory($this->GetProperty("code"))) {
            $this->saveHistory();
        }
        $this->PrepareBeforeShow();

        return true;
    }

    /**
     * Sort config value before save.
     */
    private function PrepareBeforeSave()
    {
        $editor = explode("-", $this->GetProperty("editor"));
        if ($this->GetProperty("editor") == "plain-sort") {
            $valueArray = explode(PHP_EOL, $this->GetProperty("value"));
            sort($valueArray, SORT_STRING | SORT_FLAG_CASE);
            $trimmedArray = array_map('trim', $valueArray);

            $value = implode(PHP_EOL, array_filter($trimmedArray));
            $this->SetProperty("value", $value);
        } elseif ($this->GetProperty("editor") == "file") {
            if (!$this->SaveFile()) {
                return false;
            }
        } elseif ($this->GetProperty("editor") == "flag") {
            if ($this->GetProperty("value") == "") {
                $this->SetProperty("value", "N");
            }
        } elseif ($editor[0] == "field") {
            switch ($editor[1]) {
                case "float":
                    //try to convert value to float
                    $value = preg_match("/[,]/", $this->GetProperty("value")) ? preg_replace("/[^0-9,]/", "", $this->GetProperty("value")) : preg_replace("/[^0-9.]/", "", $this->GetProperty("value"));
                    $value = str_replace(",", ".", $value);
                    $result = abs(floatval($value));
                    $this->SetProperty("value", $result);

                    if (!$this->ValidateFloat("value")) {
                        $this->AddError("error-validation-float");

                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Returns value of config by its code
     *
     * @param string $code code of config which value should be returned
     *
     * @return string|NULL config's value
     */
    public static function GetConfigValue($code)
    {
        $stmt = GetStatement();
        $query = "SELECT value FROM config WHERE code=" . Connection::GetSQLString($code);

        return $stmt->FetchField($query);
    }

    /**
     * Returns value of config by its code and date
     *
     * @param string $code code of config which value should be returned
     * @param string $date
     *
     * @return string|NULL config's value
     */
    public static function GetConfigValueByDate($code, $date)
    {
        if (!$date) {
            return null;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value, DATE(date_from) AS date_from FROM config_history WHERE config_id=" . Connection::GetSQLString(self::GetIDByCode($code)) . " ORDER BY date_from ASC, id ASC";
        $historyList = $stmt->FetchList($query);

        $result = null;
        foreach ($historyList as $value) {
            if (strtotime($value["date_from"]) > strtotime($date)) {
                continue;
            }

            $result = $value["value"];
        }

        return $result;
    }

    /**
     * Returns value of config_history by its id
     *
     * @param string $id id of config_history which value should be returned
     *
     * @return false|array
     */
    public static function GetConfigHistoryValue($id)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM config_history WHERE id=" . intval($id);

        return $stmt->FetchRow($query);
    }

    /**
     * Returns type of excepsion by id
     *
     * @param int $id id of config
     *
     * @return string type of exception
     */
    public static function GetTypeByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT code FROM config WHERE config_id=" . intval($id);
        if ($stmt->FetchField($query) == "vat_exception_7_shop") {
            $exception = "7%";
        } elseif ($stmt->FetchField($query) == "vat_exception_19_shop" || $stmt->FetchField($query) == "vat_exception_restaurant") {
            $exception = "19%";
        }

        return $exception;
    }

    /**
     * Returns id of excepsion by code
     *
     * @param string $code code of config
     *
     * @return int id of exception
     */
    public static function GetIDByCode($code)
    {
        $stmt = GetStatement();
        $query = "SELECT config_id FROM config WHERE code=" . Connection::GetSQLString($code);

        return $stmt->FetchField($query);
    }

    public function GetLicenseTermsOrGuidelineByCode($code)
    {
        $where = [
            "code = " . Connection::GetSQLString($code),
        ];

        $stmt = GetStatement();
        $query = "SELECT config_id,code,value,updated::TIMESTAMP(0) FROM config WHERE " . implode(' AND ', $where);
        if ($value = $stmt->FetchRow($query)) {
            $stmt_control = GetStatement(DB_CONTROL);
            $licenseConfigId = $value['code'] == 'app_license'
                ? $value['config_id']
                : Config::GetIDByCode("app_license");
            $guidelineConfigId = $value['code'] == 'app_guideline'
                ? $value['config_id']
                : Config::GetIDByCode("app_guideline");
            $orgGuidelineConfigId = $value['code'] == 'app_org_guideline'
                ? $value['config_id']
                : Config::GetIDByCode("app_org_guideline");

            $query = "SELECT id FROM config_history WHERE config_id=" . intval($licenseConfigId) . " ORDER BY id DESC LIMIT 1";
            $lastVersionLicense = $stmt_control->FetchField($query);
            $value['license_version'] = intval($lastVersionLicense);

            $query = "SELECT id FROM config_history WHERE config_id=" . intval($guidelineConfigId) . " ORDER BY id DESC LIMIT 1";
            $lastVersionGuideline = $stmt_control->FetchField($query);
            $value['guideline_version'] = intval($lastVersionGuideline);

            $query = "SELECT id FROM config_history WHERE config_id=" . intval($orgGuidelineConfigId) . " ORDER BY id DESC LIMIT 1";
            $lastVersionOrgGuideline = $stmt_control->FetchField($query);
            $value['org_guideline_version'] = intval($lastVersionOrgGuideline);

            return $value;
        }

        return [];
    }

    private function saveHistory()
    {
        $dateFrom = !$this->IsPropertySet("date_from") || $this->GetProperty("date_from") == null
            ? GetCurrentDateTime()
            : $this->GetProperty("date_from");

        $user = new User();
        $user->LoadBySession();
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO config_history (user_id, config_id, value, date_from, created) VALUES (" .
            $user->GetIntProperty('user_id') . ", " .
            $this->GetIntProperty("config_id") . ", " .
            $this->GetPropertyForSQL("value") . ", " .
            Connection::GetSQLDateTime($dateFrom) . ", " .
            Connection::GetSQLString(GetCurrentDateTime()) . ")";
        $stmt->Execute($query);
    }

    public static function GetConfigHistory($code, $desc = false)
    {
        $order = $desc ? " ORDER BY date_from DESC" : " ORDER BY date_from ASC";

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM config_history WHERE config_id=" . intval(self::GetIDByCode($code)) . $order;

        return $stmt->FetchList($query);
    }

    /**
     * Uploads file to storage
     */
    public function SaveFile()
    {
        $fileStorage = GetFileStorage(CONTAINER__CORE);

        foreach ($_FILES as $key => $file) {
            if ($key != "config_file") {
                continue;
            }

            if ($file["size"] == 0) {
                $this->AddError("no-file-uploaded", "core");
                continue;
            }

            $newConfigFile = $fileStorage->Upload("config_file", CONFIG_FILE_DIR, false, array(
                'application/pdf',
                'image/png',
                'image/x-png',
                'image/gif',
                'image/jpeg',
                'image/pjpeg',
                'text/plain',
                'application/octet-stream'
            ));

            if ($newConfigFile) {
                $this->SetProperty("value", $newConfigFile["FileName"]);
            } else {
                if ($this->GetProperty("saved_config_file") != "") {
                    $this->SetProperty("value", $this->GetProperty("saved_config_file"));
                } else {
                    $this->AddError("error-file-upload", "core");
                    $this->SetProperty("value", null);
                }
            }

            $this->AppendErrorsFromObject($fileStorage);
        }

        return $this->HasErrors() ? false : true;
    }

    public static function GetConfigHistoryByID($id)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM config_history WHERE config_id=" . intval($id) . " ORDER BY date_from DESC";
        if ($historyList = $stmt->FetchList($query)) {
            for ($i = 0; $i < count($historyList); $i++) {
                $historyList[$i]["user_name"] = User::GetNameByID($historyList[$i]["user_id"]);
            }

            return $historyList;
        }

        return false;
    }

    public static function DoesConfigHaveHistory($code)
    {
        return in_array($code, self::$historyCodeList);
    }
}
