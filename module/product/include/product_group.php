<?php

class ProductGroup extends LocalObject
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
    public function ProductGroup($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = array();
        $this->params["product_group"] = LoadImageConfig("product_group_image", $this->module, PRODUCT_GROUP_IMAGE);
    }

    /**
     * Loads product group by its group_id
     * @param int $id group_id
     * @return boolean true if loaded successfully or else otherwise
     */
    public function LoadByID($id)
    {
        $query = "SELECT group_id, created, code, title, product_group_image, product_group_image_config, sort_order, need_check_image, voucher
					FROM product_group 
					WHERE group_id=" . intval($id);
        $this->LoadFromSQL($query);

        if ($this->GetProperty("group_id")) {
            $this->LoadReceiptTypes();
            $this->PrepareContentBeforeShow();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Load receipt type list. Object must contain group_id property receipt type records to be filtered by.
     */
    function LoadReceiptTypes()
    {
        $stmt = GetStatement();

        $query = "SELECT rt.receipt_type_id, rt.code
			FROM product_group_2_receipt_type pr
                LEFT JOIN receipt_type rt ON rt.code = pr.code
			WHERE pr.group_id = " . $this->GetIntProperty("group_id");

        $receiptTypes = $stmt->FetchList($query);
        $this->SetProperty("ReceiptTypeList", $receiptTypes);
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        $this->SetProperty("title_translation",
            GetTranslation("product-group-" . $this->GetProperty("code"), $this->module));

        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__PRODUCT, "product_group/");
        }
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
     * Updates product group's image
     * Required properties are: group_id
     * @return boolean true if product group is updated successfully or false on failure
     */
    public function Save()
    {
        if ($this->SaveProductGroupImage($this->GetProperty("saved_product_group_image"))) {
            $stmt = GetStatement();
            $query = "UPDATE product_group SET 
						product_group_image=" . $this->GetPropertyForSQL("product_group_image") . ", 
						product_group_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("product_group_image_config"))) . "
					WHERE group_id=" . $this->GetIntProperty("group_id");
            if ($stmt->Execute($query)) {
                $this->UpdateReceiptTypes($this->GetProperty("ReceiptTypes"));
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
    function SaveProductGroupImage($savedImage = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__PRODUCT);
        $newProductGroupImage = $fileStorage->Upload("product_group_image", PRODUCT_IMAGE_DIR . "product_group/", false,
            $this->_acceptMimeTypes);
        if ($newProductGroupImage) {
            $this->SetProperty("product_group_image", $newProductGroupImage["FileName"]);

            // Remove old image if it has different name
            if ($savedImage && $savedImage != $newProductGroupImage["FileName"]) {
                $fileStorage->Remove(PRODUCT_IMAGE_DIR . "product_group/" . $savedImage);
            }
        } else {
            if ($savedImage) {
                $this->SetProperty("product_group_image", $savedImage);
            } else {
                $this->SetProperty("product_group_image", null);
            }
        }

        if (!is_array($this->GetProperty("product_group_image_config")) || $newProductGroupImage) {
            $this->SetProperty("product_group_image_config", array());
        }
        $this->_properties["product_group_image_config"]["Width"] = 0;
        $this->_properties["product_group_image_config"]["Height"] = 0;

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }

    /**
     * Removes product group image from database record of product group and file system
     * @param int $groupID group_id of product group image to be removed
     * @param string $savedImage filename of image was uploaded but not saved to user database record yet
     */
    function RemoveProductGroupImage($groupID, $savedImage)
    {
        $fileStorage = GetFileStorage(CONTAINER__PRODUCT);

        if ($savedImage) {
            $fileStorage->Remove(PRODUCT_IMAGE_DIR . "product_group/" . $savedImage);
        }

        if ($groupID > 0) {
            $stmt = GetStatement();
            $imageFile = $stmt->FetchField("SELECT product_group_image FROM product_group WHERE group_id=" . intval($groupID));

            if ($imageFile) {
                $fileStorage->Remove(PRODUCT_IMAGE_DIR . "product_group/" . $imageFile);
            }

            $stmt->Execute("UPDATE product_group SET product_group_image=NULL, product_group_image_config=NULL WHERE group_id=" . $groupID);
        }
    }

    /**
     * Returns group_id by its unique code
     * @param string $code
     * @return NULL|int group_id
     */
    public static function GetProductGroupIDByCode($code)
    {
        $stmt = GetStatement();
        $query = "SELECT group_id FROM product_group WHERE code=" . Connection::GetSQLString($code);
        return $stmt->FetchField($query);
    }

    /**
     * Returns group_id by its unique code
     * @param string $code
     * @return NULL|int group_id
     */
    public static function GetProductGroupCodeByID($id)
    {
        $stmt = GetStatement();
        $query = "SELECT code FROM product_group WHERE group_id=" . intval($id);
        return $stmt->FetchField($query);
    }

    /**
     * @param $groupId
     * @param $employeeId
     *
     * @return boolean
     */
    public static function DoesEmployeeProductGroupHaveAdvancedSecurity($groupId, $employeeId, $date)
    {

        $specificProductGroup = SpecificProductGroupFactory::Create($groupId);

        $advancedSecurityProductCode = $specificProductGroup->GetAdvancedSecurityProductCode();
        $productID = Product::GetProductIDByCode($advancedSecurityProductCode);

        $contract = new Contract("product");

        return $contract->InheritableContractExist($productID, $employeeId, $date);
    }

    /**
     * Updates product group receipt types in database.
     * @param array $receiptTypes correct structure is array({code}, {code}, {code})
     */
    function UpdateReceiptTypes($receiptTypes)
    {
        $stmt = GetStatement();
        $query = "DELETE FROM product_group_2_receipt_type WHERE group_id = " . $this->GetIntProperty("group_id");
        $stmt->Execute($query);

        if (is_array($receiptTypes)) {
            foreach ($receiptTypes as $code) {
                $query = "INSERT INTO product_group_2_receipt_type (group_id, code) VALUES(" . $this->GetIntProperty("group_id") . "," . Connection::GetSQLString($code) . ")";
                $stmt->Execute($query);
            }
        }
    }

}