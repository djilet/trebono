<?php

class ReceiptType extends LocalObject
{
    private $_acceptMimeTypes = array(
        'image/png',
        'image/x-png',
        'image/gif',
        'image/jpeg',
        'image/pjpeg'
    );
    private $module;
    private $params;

    /**
     * Constructor
     * @param string $module Name of context module
     * @param array $data Array of product group properties to be loaded instantly
     */
    public function ReceiptType($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = array();
        $this->params["receipt_type"] = LoadImageConfig("receipt_type_image", $this->module, RECEIPT_TYPE_IMAGE);
    }

    public function LoadByCode($code)
    {
        $query = "SELECT receipt_type_id, created, created_by, code, receipt_type_image, receipt_type_image_config
					FROM receipt_type
					WHERE code=" . Connection::GetSQLString($code);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("receipt_type_id")) {
            $this->PrepareContentBeforeShow();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loads product group by its receipt_type_id
     * @param int $id receipt_type_id
     * @return boolean true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT receipt_type_id, created, created_by, code, receipt_type_image, receipt_type_image_config
					FROM receipt_type 
					WHERE receipt_type_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("receipt_type_id")) {
            $this->PrepareContentBeforeShow();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $this->SetProperty("title_translation",
            GetTranslation("receipt-type-" . $this->GetProperty("code"), $this->module));

        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__PRODUCT, "receipt_type/");
        }
    }

    /**
     * Update/create receipt_type
     * Required properties are: code
     * @return boolean true if product group is updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->Validate()) {
            return false;
        }
        if ($this->SaveReceiptTypeImage($this->GetProperty("saved_receipt_type_image"))) {
            $stmt = GetStatement();
            if ($this->GetProperty("receipt_type_id")) {
                $query = "UPDATE receipt_type SET
    						receipt_type_image=" . $this->GetPropertyForSQL("receipt_type_image") . ",
    						receipt_type_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("receipt_type_image_config"))) . "
					   WHERE receipt_type_id=" . $this->GetIntProperty("receipt_type_id");
            } else {
                $user = new User();
                $user->LoadBySession();

                $query = "INSERT INTO receipt_type (code, created, created_by, receipt_type_image, receipt_type_image_config) 
                        VALUES (
                        " . $this->GetPropertyForSQL("code") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $user->GetIntProperty("user_id") . ", 
						" . $this->GetPropertyForSQL("receipt_type_image") . ",
    					" . Connection::GetSQLString(json_encode($this->GetProperty("receipt_type_image_config"))) . ") RETURNING receipt_type_id";
            }
            if ($stmt->Execute($query)) {
                if (!$this->GetProperty("receipt_type_id")) {
                    $this->SetProperty("receipt_type_id", $stmt->GetLastInsertID());
                    $translationList = $this->GetProperty("translation_list");
                    foreach ($translationList as $langCode => $translation) {
                        $langVar = new LangVar($langCode, "php", $this->module, "common",
                            "receipt-type-" . $this->GetProperty("code"), $translation);
                        $query = $langVar->GetInsertQuery();
                        if (!$stmt->Execute($query)) {
                            $query = $langVar->GetUpdateQuery();
                            $stmt->Execute($query);
                        }
                    }
                    CleanCache("xml");
                }
                return true;
            } else {
                $this->AddError("sql-error");
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Tries to upload new product group image and initialize its config.
     * Resets current object product_group_image property by previously uploaded file if new file is not uploaded.
     * @param string $savedImage previously uploaded filename
     * @return boolean false if error is occured during new image uploading or true if its uploaded successfully or no new image provided
     */
    function SaveReceiptTypeImage($savedImage = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__PRODUCT);
        $newReceiptTypeImage = $fileStorage->Upload("receipt_type_image", PRODUCT_IMAGE_DIR . "receipt_type/", false,
            $this->_acceptMimeTypes);
        if ($newReceiptTypeImage) {
            $this->SetProperty("receipt_type_image", $newReceiptTypeImage["FileName"]);

            // Remove old image if it has different name
            if ($savedImage && $savedImage != $newReceiptTypeImage["FileName"]) {
                $fileStorage->Remove(PRODUCT_IMAGE_DIR . "receipt_type/" . $savedImage);
            }
        } else {
            if ($savedImage) {
                $this->SetProperty("receipt_type_image", $savedImage);
            } else {
                $this->SetProperty("receipt_type_image", null);
            }
        }

        if (!is_array($this->GetProperty("receipt_type_image_config")) || $newReceiptTypeImage) {
            $this->SetProperty("receipt_type_image_config", array());
        }
        $this->_properties["receipt_type_image_config"]["Width"] = 0;
        $this->_properties["receipt_type_image_config"]["Height"] = 0;

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }

    /**
     * Returns array of image resize settings for $key image necessary for admin image edit component initializing
     * @param string $key image key
     * @return mixed[][]
     */
    function GetImageParams($key)
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
     * Validates input data when trying to create/update receipt_type from admin panel. Also turns incorrect int/float properties into null.
     * @return boolean true if data is correct or false if any field is filled incorrectly
     */
    private function Validate()
    {
        if (!$this->ValidateNotEmpty("code")) {
            $this->AddError("code-required", $this->module);
        } else {
            $stmt = GetStatement();

            $query = "SELECT COUNT(*) FROM receipt_type 
                    WHERE code=" . $this->GetPropertyForSQL("code") . "
                        AND receipt_type_id!=" . $this->GetIntProperty("receipt_type_id");

            if ($stmt->FetchField($query)) {
                $this->AddError("code-is-not-unique", $this->module);
            }
        }

        if (!$this->GetProperty("receipt_type_id")) {
            $translationList = $this->GetProperty("translation_list");
            $language = GetLanguage();
            foreach ($language->GetInterfaceLanguageList() as $interfaceLanguage) {
                if (empty($translationList[$interfaceLanguage["Folder"]])) {
                    $this->AddError("translation-required", $this->module,
                        array("language" => $interfaceLanguage["Name"]));
                }
            }
        }

        return !$this->HasErrors();
    }
}