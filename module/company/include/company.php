<?php

class Company extends LocalObject
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
     *
     * @param string $module Name of context module
     * @param array $data Array of company properties to be loaded instantly
     */
    public function Company($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
        $this->params = array();
        $this->params["company"] = LoadImageConfig("company_image", $this->module, COMPANY_COMPANY_IMAGE);
    }

    /**
     * Prepares company image paths for different resize settings
     */
    private function _PrepareContentBeforeShow()
    {
        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__COMPANY, "company/");
        }
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
                "X1" => $this->GetIntProperty($key . "Image" . $this->params[$key][$i]['SourceName'] . "X1"),
                "Y1" => $this->GetIntProperty($key . "Image" . $this->params[$key][$i]['SourceName'] . "Y1"),
                "X2" => $this->GetIntProperty($key . "Image" . $this->params[$key][$i]['SourceName'] . "X2"),
                "Y2" => $this->GetIntProperty($key . "Image" . $this->params[$key][$i]['SourceName'] . "Y2")
            );
        }

        return $paramList;
    }

    /**
     * Creates or updates the company. Object must be loaded from request before the method will be called.
     * Required properties are: colorscheme
     *
     * @return bool true if company is created/updated successfully or false on failure
     */
    public function Save()
    {
        if (!$this->SaveCompanyImage($this->GetProperty("saved_company_image"), "company")) {
            $this->_PrepareContentBeforeShow();

            return false;
        }

        if (!$this->IsPropertySet("colorscheme")) {
            $this->SetProperty("colorscheme", "");
        }

        $stmt = GetStatement();

        $query = $this->GetIntProperty("company_id") > 0 ? "UPDATE company SET
						colorscheme=" . $this->GetPropertyForSQL("colorscheme") . ", 
						company_image=" . $this->GetPropertyForSQL("company_image") . ", 
						company_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("company_image_config"))) . "  
					WHERE company_id=" . $this->GetIntProperty("company_id") : "INSERT INTO company (created, colorscheme, company_image, company_image_config) VALUES (
						" . Connection::GetSQLString(GetCurrentDateTime()) . ", 						
						" . $this->GetPropertyForSQL("colorscheme") . ", 
						" . $this->GetPropertyForSQL("company_image") . ",
						" . Connection::GetSQLString(json_encode($this->GetProperty("company_image_config"))) . ") 
					RETURNING company_id";
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if (!$this->GetIntProperty("company_id") > 0) {
            $this->SetProperty("company_id", $stmt->GetLastInsertID());
        }

        return true;
    }

    /**
     * Tries to upload new $type image and initialize its config.
     * Resets current object $type image property by previously uploaded file if new file is not uploaded.
     *
     * @param string $savedImage previously uploaded filename
     * @param string $type image key
     *
     * @return bool false if error is occured during new image uploading or true if its uploaded successfully or no new image provided
     */
    private function SaveCompanyImage($savedImage = "", $type = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__COMPANY);

        $newChannelImage = $fileStorage->Upload(
            $type . "_image",
            COMPANY_IMAGE_DIR . "company/",
            false,
            $this->_acceptMimeTypes
        );
        if ($newChannelImage) {
            $this->SetProperty($type . "_image", $newChannelImage["FileName"]);

            // Remove old image if it has different name
            if ($savedImage && $savedImage != $newChannelImage["FileName"]) {
                $fileStorage->Remove(COMPANY_IMAGE_DIR . "company/" . $savedImage);
            }
        } else {
            if ($savedImage) {
                $this->SetProperty($type . "_image", $savedImage);
            } else {
                $this->SetProperty($type . "_image", null);
            }
        }

        $this->_properties[$type . "_image_config"]["Width"] = 0;
        $this->_properties[$type . "_image_config"]["Height"] = 0;

        if ($this->GetProperty($type . '_image')) {
            if ($info = @getimagesize(COMPANY_IMAGE_DIR . "company/" . $this->GetProperty($type . '_image'))) {
                $this->_properties[$type . "_image_config"]["Width"] = $info[0];
                $this->_properties[$type . "_image_config"]["Height"] = $info[1];
            }
        }

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }

    /**
     * Removes $type image from database record of company and file system
     *
     * @param int $companyID company_id of company image to be removed
     * @param string $savedImage filename of image was uploaded but not saved to company database record yet
     * @param string $type image key
     */
    public function RemoveCompanyImage($companyID, $savedImage, $type = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__COMPANY);

        if ($savedImage) {
            $fileStorage->Remove(COMPANY_IMAGE_DIR . "company/" . $savedImage);
        }
        $key = substr($type, 0, strlen($type) - 6);
        if ($channelID <= 0) {
            return;
        }

        $stmt = GetStatement();
        $imageFile = $stmt->FetchField("SELECT " . $key . "_image FROM company WHERE company_id=" . $companyID);

        if ($imageFile) {
            $fileStorage->Remove(COMPANY_IMAGE_DIR . "company/" . $imageFile);
        }

        $stmt->Execute("UPDATE company SET " . $key . "_image=NULL, " . $key . "image_config=NULL WHERE company_id=" . $companyID);
    }

    /**
     * Validates user company_unit Role
     *
     * @param int $companyID company_id
     * @param int $userID user_id
     * @param bool $withContractPermission add access for users with contract role
     *
     * @return true|false
     */
    public static function ValidateAccess($companyID, $userID = null, $withContractPermission = false)
    {
        if (!$companyID) {
            return true;
        }

        $permissions = $withContractPermission ? ["company_unit", "contract"] : ["company_unit"];

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        if ($user->Validate($permissions, "or")) {
            return true;
        } else {
            $companyUnitIDs = $user->GetPermissionLinkIDs($permissions[0]);
            if ($withContractPermission) {
                $companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs($permissions[1]));
            }
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) == 0) {
                return false;
            }
        }

        return in_array($companyID, $companyUnitIDs) ? true : false;
    }
}
