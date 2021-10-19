<?php

es_include("phpmailer/phpmailer.php");

class User extends LocalObject
{
    var $_acceptMimeTypes = array(
        'image/*',
        'image/png',
        'image/x-png',
        'image/gif',
        'image/jpeg',
        'image/pjpeg'
    );
    var $params;

    /**
     * Constructor
     *
     * @param array $data Array of user properties to be loaded instantly
     */
    function User($data = array())
    {
        parent::LocalObject($data);
        $this->params = array();
        $this->params["user"] = LoadImageConfig('user_image', "user", GetFromConfig("UserImage"));
    }

    /**
     * Returns common query prefix containing set of fields to select
     *
     * @return string
     */
    function GetQueryPrefix()
    {
        return "SELECT user_id, email, salutation, birthday, phone,
                    zip_code, country, city, house, belongs_to_company,
                    created, last_login, last_ip, user_image, user_image_config, archive,
                    " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                    " . Connection::GetSQLDecryption("last_name") . " AS last_name,
                    " . Connection::GetSQLDecryption("street") . " AS street
                    FROM user_info";
    }

    /**
     * Loads user by its user_id
     *
     * @param int $id user_id
     *
     * @return bool true if user is loaded successfully or false on failure
     */
    function LoadByID($id)
    {
        $query = $this->GetQueryPrefix() . " WHERE user_id=" . intval($id);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if (!$this->ValidateNotEmpty("salutation")) {
            $this->SetProperty("salutation", "Frau");
        }

        if ($this->GetIntProperty("user_id")) {
            $this->LoadPermissions();
            $this->PrepareBeforeShow();

            return true;
        }

        return false;
    }

    /**
     * Loads user session data if it is logged in
     *
     * @return bool true if user is logged in and loaded successfully or false otherwise
     */
    function LoadBySession()
    {
        // Clear properties before load
        $this->LoadFromArray(array());

        $session =& GetSession();

        if ($session->IsPropertySet("MustRelogin")) {
            $this->AddError("session-expired-must-relogin");

            return false;
        }

        if (is_array($session->GetProperty("LoggedInUser"))) {
            $user = $session->GetProperty("LoggedInUser");
            $this->LoadFromArray($user);
            $session->UpdateExpireDate();

            return true;
        }

        return false;
    }

    /**
     * User authorization function
     *
     * @param LocalObject $request Object must contain "email" and "password" properties
     * and can contain "RememberMe" property to stay logged between browser session
     *
     * @return bool true if logged in successfully of false otherwise
     */
    function LoadByRequest($request)
    {
        $session =& GetSession();
        if ($session->GetProperty('LoginAttempts') >= 3) {
            $this->AddError("no-more-login-attempts");

            return false;
        }

        $query = $this->GetQueryPrefix() . " WHERE
            LOWER(email)=LOWER(" . $request->GetPropertyForSQL("email") . ") AND
            password=MD5(" . $request->GetPropertyForSQL("password") . ")";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if ($this->GetIntProperty("user_id")) {
            $this->PrepareBeforeShow();

            self::UpdateLastLogin($this->GetProperty("user_id"));

            $this->LoadPermissions();

            $session->SetProperty("LoggedInUser", $this->GetProperties());
            $session->SaveToDB($request->GetIntProperty("RememberMe"));

            return true;
        }

        if ($session->IsPropertySet("LoginAttempts")) {
            $session->SetProperty("LoginAttempts", $session->GetProperty("LoginAttempts") + 1);
        } else {
            $session->SetProperty("LoginAttempts", 1);
        }
        $session->SaveToDB();

        if ($session->GetProperty('LoginAttempts') >= 3) {
            $this->AddError("no-more-login-attempts");
        } else {
            $this->AddError("wrong-login-password");
        }

        return false;
    }

    /**
     * Updates last_login and last_ip user fields
     *
     * @param int $userID
     */
    public static function UpdateLastLogin($userID)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "UPDATE user_info
            SET last_login=" . Connection::GetSQLString(GetCurrentDateTime()) . ",
                last_ip=" . Connection::GetSQLString(getenv("REMOTE_ADDR")) . "
            WHERE user_id=" . intval($userID);
        $stmt->Execute($query);
    }

    public function GetGroupedAvailablePermissions(bool $all = false): array
    {
        $permissions = $this->GetAvailablePermissions($all);
        $permissionDictionary = [];

        foreach ($permissions as &$permission) {
            $linkTo = $permission["link_to"];
            if (in_array($linkTo, ["company_unit", "product_group", "partner"])) {
                $linkIDList = [];
                $linkIDs = array_filter(explode(",", $permission["link_ids"] ?? ""));
                foreach ($linkIDs as $linkID) {
                    $linkIDList[] = ["link_id" => $linkID];
                }
                $permission["LinkIDList"] = $linkIDList;
            }
            $permission["title_translation"] = GetTranslation("permission-" . $permission["name"]);

            $groupId = $permission['group_id'];
            if (isset($permissionDictionary[$groupId])) {
                $permissionDictionary[$groupId][] = $permission;
            } else {
                $permissionDictionary[$groupId] = [$permission];
            }
        }
        $groupIds = array_filter(array_keys($permissionDictionary));

        $groups = [];
        if (count($groupIds) > 0) {
            $groupIds = implode(', ', $groupIds);

            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT *
FROM permission_group
WHERE permission_group.group_id IN ({$groupIds})
ORDER BY permission_group.sort_order";
            $groups = $stmt->FetchList($query) ?? [];
        }

        foreach ($groups as &$group) {
            $group["title_translation"] = GetTranslation("permission-group-" . $group["code"], "core");
            $group["permissions"] = $permissionDictionary[$group["group_id"]] ?? [];
        }

        $groups[] = [
            "permissions" => $permissionDictionary[null] ?? [],
        ];

        return $groups;
    }

    /**
     * Load user's permission list.
     * Object must contain user_id property permission records to be filtered by.
     */
    public function LoadPermissions(): void
    {
        $stmt = GetStatement(DB_PERSONAL);

        $query = "SELECT p.permission_id, p.name, p.link_to, up.link_id
FROM user_permissions up
LEFT JOIN permission p ON up.permission_id = p.permission_id
WHERE up.user_id = " . $this->GetPropertyForSQL("user_id")
        . " ORDER BY p.sort_order, p.permission_id";

        $permissions = $stmt->FetchList($query);

        $this->SetProperty("PermissionList", $permissions);
    }

    /**
     * Returns list of permissions with requested value of link_to
     *
     * @param $link_to string
     *
     * @return array
     */
    public static function GetPermissionListByLinkTo($link_to)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT permission_id
                  FROM permission WHERE link_to=" . Connection::GetSQLString($link_to);
        $permissionList = $stmt->FetchList($query);

        $result = array();
        foreach ($permissionList as $permission) {
            $result[] = $permission["permission_id"];
        }

        return $result;
    }

    /**
     * Prepares user image paths for different resize settings
     */
    function PrepareBeforeShow()
    {
        if ($this->GetIntProperty("user_id") > 0) {
            $this->SetProperty("RoleTitle", "");
        }//TODO: get roles title for display

        foreach ($this->params as $key => $value) {
            PrepareImagePath($this->_properties, $key, $this->params[$key], CONTAINER__CORE);
        }
    }

    /**
     * Returns array of image resize settings for $key image necessary for admin image edit component initializing
     *
     * @param string $key image key
     *
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
     * Validates user's permissions.
     *
     * @param array $permissions
     *
     * @return bool
     *
     * @example $this->Validate(array("root", "company" => 15, "employee" => array(15, 20), "receipt" => null))
     */
    function Validate($permissions = null, $mode = "and")
    {
        if ($this->GetIntProperty("user_id")) {
            $result = $mode == "and";
            if (is_array($permissions)) {
                foreach ($permissions as $key => $value) {
                    if (is_int($key)) {
                        //validate common permission
                        $found = false;
                        foreach ($this->GetProperty("PermissionList") as $permission) {
                            if ($permission["name"] != $value || $permission["link_id"]) {
                                continue;
                            }

                            $found = true;
                        }
                        $result = $mode == "and" ? $result && $found : $result || $found;
                    } else {
                        //validate linked permissions
                        if ($value !== null) {
                            if (!is_array($value)) {
                                $value = array($value);
                            }

                            foreach ($value as $linkID) {
                                $found = false;
                                foreach ($this->GetProperty("PermissionList") as $permission) {
                                    if ($permission["name"] != $key || ($permission["link_id"] && $permission["link_id"] != $linkID)) {
                                        continue;
                                    }

                                    $found = true;
                                }
                                $result = $mode == "and" ? $result && $found : $result || $found;
                            }
                        } else {
                            $found = false;
                            foreach ($this->GetProperty("PermissionList") as $permission) {
                                if ($permission["name"] != $key) {
                                    continue;
                                }

                                $found = true;
                            }
                            $result = $mode == "and" ? $result && $found : $result || $found;
                        }
                    }
                }
            }

            return $result;
        }

        return false;
    }

    /**
     * Validates logged in user permissions
     *
     * @param mixed $permissions permissions to be checked
     *
     * @return bool true if user is logged in and all the permissions are found or false otherwise
     */
    function ValidateAccess($permissions = null, $mode = "and")
    {
        if (!$this->LoadBySession()) {
            // Not logged in users redirect to home page
            if (defined('IS_ADMIN')) {
                header("Location: " . ADMIN_PATH . "index.php?ReturnPath=" . urlencode($_SERVER['REQUEST_URI']));
                exit();
            }

            return false;
        }

        $this->LoadPermissions();
        if ($this->GetProperty("archive") == "Y") {
            Send403();
        }
        if ($this->Validate($permissions, $mode)) {
            return true;
        }

        if (!defined('IS_ADMIN')) {
            return false;
        }

        Send403();
    }

    /**
     * Logs current user out by clearing related session property
     */
    function Logout($noMessage = false)
    {
        // Clear properties before logout
        $this->LoadFromArray(array());

        $session =& GetSession();
        $session->RemoveProperty("LoggedInUser");
        $session->SaveToDB();

        if (!$noMessage) {
            $this->AddMessage("logged-out");
        }
    }

    /**
     * Creates/updates user from users admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: email, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house
     * and password1 for new user
     * Also updates users permissions.
     *
     * @param int $authUserID user_id of authorized user for possibility to change his password himself
     * @param bool $updatePermissions update permissions or not
     *
     * @return bool true if user is created/updated successfully or false on failure
     */
    function Save($authUserID, $updatePermissions = true)
    {
        $stmt = GetStatement(DB_PERSONAL);

        if (!$this->ValidateEmail("email")) {
            $this->AddError("incorrect-email-format");
        }

        if (!$this->ValidateNotEmpty("first_name")) {
            $this->AddError("first-name-required");
        }

        if (!$this->ValidateNotEmpty("last_name")) {
            $this->AddError("last-name-required");
        }

        if ($authUserID == $this->GetProperty("user_id")) {
            if ($this->GetProperty("password1")) {
                $query = "SELECT COUNT(user_id) FROM user_info WHERE
                    user_id=" . $this->GetIntProperty("user_id") . " AND
                    password=MD5(" . $this->GetPropertyForSQL("OldPassword") . ")";
                if (!$stmt->FetchField($query)) {
                    $this->AddError("wrong-old-password");
                }
            }
        }

        if ($this->GetIntProperty("user_id") == 0 && !$this->GetProperty("password1")) {
            $this->AddError("password-empty");
        }

        if ($password = $this->GetProperty("password1")) {
            if ($password != $this->GetProperty("password2")) {
                $this->AddError("password-not-equal");
            }

            /*if(!preg_match("/[0-9]/", $password) || !preg_match("/[A-Z]/", $password) || strlen($password) < 8)
                $this->AddError("password-not-meeting-requirements");*/
        }

        $this->SetProperty("email", trim($this->GetProperty("email")));

        $this->SaveUserImage($this->GetProperty("saved_user_image"));

        if ($this->HasErrors()) {
            return false;
        }

        $query = "SELECT COUNT(*) FROM user_info WHERE
            LOWER(email)=LOWER(" . $this->GetPropertyForSQL("email") . ")
            AND user_id<>" . $this->GetIntProperty("user_id");

        if ($stmt->FetchField($query)) {
            $this->AddError("email-is-not-unique");

            return false;
        }

        if ($this->GetIntProperty("user_id") > 0) {
            $query = "UPDATE user_info SET
                    email=" . $this->GetPropertyForSQL("email") . ",
                    " . ($this->GetProperty("password1") ? "\"password\"=MD5(" . $this->GetPropertyForSQL("password1") . ")," : "") . "
                    salutation=" . $this->GetPropertyForSQL("salutation") . ",
                    first_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                    last_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                    birthday=" . Connection::GetSQLDate($this->GetProperty("birthday")) . ",
                    phone=" . $this->GetPropertyForSQL("phone") . ",
                    zip_code=" . $this->GetPropertyForSQL("zip_code") . ",
                    country=" . $this->GetPropertyForSQL("country") . ",
                    city=" . $this->GetPropertyForSQL("city") . ",
                    street=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("street")) . ",
                    house=" . $this->GetPropertyForSQL("house") . ",
                    user_image=" . $this->GetPropertyForSQL("user_image") . ",
                    user_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("user_image_config"))) . ",
                    belongs_to_company=" . $this->GetPropertyForSQL("belongs_to_company") . "
                WHERE user_id=" . $this->GetIntProperty("user_id");
        } else {
            $query = "INSERT INTO user_info (email, password, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house, user_image, user_image_config, created, belongs_to_company)
                VALUES (
                    " . $this->GetPropertyForSQL("email") . ",
                    MD5(" . $this->GetPropertyForSQL("password1") . "),
                    " . $this->GetPropertyForSQL("salutation") . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                    " . Connection::GetSQLDate($this->GetProperty("birthday")) . ",
                    " . $this->GetPropertyForSQL("phone") . ",
                    " . $this->GetPropertyForSQL("zip_code") . ",
                    " . $this->GetPropertyForSQL("country") . ",
                    " . $this->GetPropertyForSQL("city") . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("street")) . ",
                    " . $this->GetPropertyForSQL("house") . ",
                    " . $this->GetPropertyForSQL("user_image") . ",
                    " . Connection::GetSQLString(json_encode($this->GetProperty("user_image_config"))) . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . $this->GetPropertyForSQL("belongs_to_company") . ")
                RETURNING user_id";
        }

        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("user_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if ($this->GetIntProperty("user_id") == 0) {
            $this->SetProperty("user_id", $stmt->GetLastInsertID());
        }

        if ($updatePermissions) {
            $this->SetProperty(
                "PermissionIDs",
                User::ValidatePermissionList($this->GetProperty("PermissionIDs"))
            );
            $this->UpdatePermissions(
                $this->GetProperty("PermissionIDs"),
                $this->GetProperty("PermissionLinkIDs")
            );
        }
        $this->UpdateSession($this->GetProperty("user_id"));

        if (!$this->SaveHistory($currentPropertyList, 'admin', $authUserID)) {
            $this->AddError("sql-error");

            return false;
        }
        $this->AddMessage("user-is-updated");
        $this->PrepareBeforeShow();

        return true;
    }

    /**
     * Creates/updates user from employee admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: email, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house
     *
     * @return bool true if user is created/updated successfully or false on failure
     */
    function SaveFromEmployee()
    {
        if (!$this->SaveUserImage($this->GetProperty("saved_user_image"))) {
            return false;
        }

        $this->SetProperty("email", trim($this->GetProperty("email")));

        $stmt = GetStatement(DB_PERSONAL);
        if ($this->GetIntProperty("user_id") > 0) {
            $query = "UPDATE user_info SET
                    email=" . $this->GetPropertyForSQL("email") . ",
                    " . ($this->GetProperty("password1") ? "\"password\"=MD5(" . $this->GetPropertyForSQL("password1") . ")," : "") . "
                    salutation=" . $this->GetPropertyForSQL("salutation") . ",
                    first_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                    last_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                    birthday=" . Connection::GetSQLDate($this->GetProperty("birthday")) . ",
                    phone=" . $this->GetPropertyForSQL("phone") . ",
                    zip_code=" . $this->GetPropertyForSQL("zip_code") . ",
                    country=" . $this->GetPropertyForSQL("country") . ",
                    city=" . $this->GetPropertyForSQL("city") . ",
                    street=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("street")) . ",
                    house=" . $this->GetPropertyForSQL("house") . ",
                    user_image=" . $this->GetPropertyForSQL("user_image") . ",
                    user_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("user_image_config"))) . "
                WHERE user_id=" . $this->GetIntProperty("user_id");
        } else {
            $query = "INSERT INTO user_info (email, password, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house, user_image, user_image_config, created, belongs_to_company)
                VALUES (
                    " . $this->GetPropertyForSQL("email") . ",
                    MD5(" . $this->GetPropertyForSQL("password1") . "),
                    " . $this->GetPropertyForSQL("salutation") . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                    " . Connection::GetSQLDate($this->GetProperty("birthday")) . ",
                    " . $this->GetPropertyForSQL("phone") . ",
                    " . $this->GetPropertyForSQL("zip_code") . ",
                    " . $this->GetPropertyForSQL("country") . ",
                    " . $this->GetPropertyForSQL("city") . ",
                    " . Connection::GetSQLEncryption($this->GetPropertyForSQL("street")) . ",
                    " . $this->GetPropertyForSQL("house") . ",
                    " . $this->GetPropertyForSQL("user_image") . ",
                    " . Connection::GetSQLString(json_encode($this->GetProperty("user_image_config"))) . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . $this->GetPropertyForSQL("belongs_to_company") . ")
                RETURNING user_id";
        }
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("user_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        if ($this->GetIntProperty("user_id") == 0) {
            $this->SetProperty("user_id", $stmt->GetLastInsertID());
        } else {
            unset($currentPropertyList['PermissionIDs']);
            unset($currentPropertyList['PermissionList']);
        }
        if (!$this->SaveHistory($currentPropertyList, $this->GetProperty("created_from"))) {
            $this->AddError("sql-error");

            return false;
        }
        $this->PrepareBeforeShow();

        return true;
    }

    /**
     * Creates/updates user from contact admin panel section. Object must be loaded from request before the method will be called.
     * Required properties are: email, salutation, first_name, last_name, birthday, phone, zip_code, country, city, street, house
     *
     * @return bool true if user is created/updated successfully or false on failure
     */
    function SaveFromContact()
    {
        $this->SetProperty("email", trim($this->GetProperty("email")));

        $stmt = GetStatement(DB_PERSONAL);
        $query = $this->GetIntProperty("user_id") > 0 ? "UPDATE user_info SET
                email=" . $this->GetPropertyForSQL("email") . ",
                salutation=" . $this->GetPropertyForSQL("salutation") . ",
                first_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                last_name=" . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                phone=" . $this->GetPropertyForSQL("phone") . "
            WHERE user_id=" . $this->GetIntProperty("user_id") : "INSERT INTO user_info (email, password, salutation, first_name, last_name, phone, created, belongs_to_company)
            VALUES (
                " . $this->GetPropertyForSQL("email") . ",
                MD5(" . $this->GetPropertyForSQL("password1") . "),
                " . $this->GetPropertyForSQL("salutation") . ",
                " . Connection::GetSQLEncryption($this->GetPropertyForSQL("first_name")) . ",
                " . Connection::GetSQLEncryption($this->GetPropertyForSQL("last_name")) . ",
                " . $this->GetPropertyForSQL("phone") . ",
                " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                " . $this->GetPropertyForSQL("belongs_to_company") . ")
            RETURNING user_id";
        $currentPropertyList = $this->GetCurrentPropertyList($this->GetIntProperty("user_id"));
        if (!$stmt->Execute($query)) {
            $this->AddError("sql-error");

            return false;
        }

        $userID = $stmt->GetLastInsertID();

        $permissionIDsFromRequest = array();
        $linkIDsFromRequest = array();

        $permissionList = array(
            User::GetPermissionID('invoice') => "contact_for_invoice",
            User::GetPermissionID('payroll') => "contact_for_payroll_export",
            User::GetPermissionID('company_unit') => "contact_for_company_unit_admin",
            User::GetPermissionID('employee') => "contact_for_employee_admin",
            User::GetPermissionID('stored_data') => "contact_for_stored_data",
            User::GetPermissionID('contract') => "contact_for_contract"
        );

        $oldContactForList = $this->GetProperty("OldContactFor");
        $removePermissionIDs = array();
        $removeLinkIDs = array();
        foreach ($permissionList as $permissionID => $permissionField) {
            //if user had admin role, but isn't a contact for it, we should keep that role
            if ($this->GetProperty($permissionField) == "Y") {
                $permissionIDsFromRequest[] = $permissionID;
                $linkIDsFromRequest[$permissionID] = array($this->GetProperty("permission_company_unit_id"));
            } elseif ($this->GetProperty($permissionField) == "N" && $oldContactForList[$permissionField] == "Y") {
                $removePermissionIDs[] = $permissionID;
                $removeLinkIDs[$permissionID] = $this->GetProperty("permission_company_unit_id");
            }
        }

        if (!$this->GetProperty("user_id")) {
            $this->SetProperty("user_id", $userID);

            //put permissions to current object history to be saved correctly
            $this->SetProperty("PermissionIDs", $permissionIDsFromRequest);
            $this->SetProperty("PermissionLinkIDs", $linkIDsFromRequest);
            $this->SetProperty("PermissionIDs", User::ValidatePermissionList($this->GetProperty("PermissionIDs")));
            $this->UpdatePermissions($this->GetProperty("PermissionIDs"), $this->GetProperty("PermissionLinkIDs"));
        } else {
            $this->LoadPermissions();
            $currentPermissionIDs = $this->GetProperty("PermissionList");

            $resultPermissionIDs = $permissionIDsFromRequest;
            $resultLinkIDs = $linkIDsFromRequest;

            foreach ($currentPermissionIDs as $permission) {
                if (in_array($permission["permission_id"], array_keys($permissionList))) {
                    if (in_array($permission["permission_id"], $permissionIDsFromRequest)) {
                        if ($permission["link_id"] != "") {
                            $resultLinkIDs[$permission["permission_id"]][] = $permission["link_id"];
                        } else {
                            unset($resultLinkIDs[$permission["permission_id"]]);
                        }
                    } else {
                        //if user has global role/permissions to other companies, we need to keep them
                        if (
                            !in_array(
                                $permission["permission_id"],
                                $removePermissionIDs
                            ) && !in_array(
                                $permission["link_id"],
                                $removeLinkIDs[$permission["permission_id"]]
                            )
                            || ($permission["link_id"] == null || $permission["link_id"] != $this->GetProperty("permission_company_unit_id"))
                        ) {
                            if (!in_array($permission["permission_id"], $resultPermissionIDs)) {
                                $resultPermissionIDs[] = $permission["permission_id"];
                            }
                            if (
                                !in_array(
                                    $permission["link_id"],
                                    $resultLinkIDs[$permission["permission_id"]]
                                ) && $permission["link_id"] != null
                            ) {
                                $resultLinkIDs[$permission["permission_id"]][] = $permission["link_id"];
                            }
                        }
                    }
                } else {
                    $resultPermissionIDs[] = $permission["permission_id"];
                    if ($permission["link_id"]) {
                        $resultLinkIDs[$permission["permission_id"]][] = $permission["link_id"];
                    }
                }
            }

            //put permissions to current object history to be saved correctly
            $this->SetProperty("PermissionIDs", $resultPermissionIDs);
            $this->SetProperty("PermissionLinkIDs", $resultLinkIDs);

            $this->SetProperty("PermissionIDs", User::ValidatePermissionList($this->GetProperty("PermissionIDs")));
            $this->UpdatePermissions($this->GetProperty("PermissionIDs"), $this->GetProperty("PermissionLinkIDs"));
        }
        if (!$this->SaveHistory($currentPropertyList, $this->GetProperty("created_from"))) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }

    /**
     * Validates input data when trying to create/update user from employee admin panel section.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    function ValidateFromEmployee()
    {
        if (!$this->ValidateEmail("email")) {
            $this->AddError("incorrect-email-format");
            $this->AddErrorField("email");
        }

        if ($password = $this->GetProperty("password1")) {
            if ($password != $this->GetProperty("password2")) {
                $this->AddError("password-not-equal");
                $this->AddErrorField("password1");
            }

            /*if(!preg_match("/[0-9]/", $password) || !preg_match("/[A-Z]/", $password) || strlen($password) < 8)
                $this->AddError("password-not-meeting-requirements");*/
        }

        if (!$this->ValidateNotEmpty("first_name")) {
            $this->AddError("first-name-required");
            $this->AddErrorField("first_name");
        }

        if (!$this->ValidateNotEmpty("last_name")) {
            $this->AddError("last-name-required");
            $this->AddErrorField("last_name");
        }

        if (!$this->HasErrors()) {
            $stmt = GetStatement(DB_PERSONAL);

            $query = "SELECT COUNT(*) FROM user_info WHERE LOWER(email)=LOWER(" . $this->GetPropertyForSQL("email") . ") AND user_id<>" . $this->GetIntProperty("user_id");
            if ($stmt->FetchField($query)) {
                $this->AddError("email-is-not-unique");
                $this->AddErrorField("email");
            }
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        return !$this->HasErrors();
    }

    /**
     * Validates input data when trying to create/update user from contact admin panel section.
     *
     * @return bool true if data is correct or false if any field is filled incorrectly
     */
    function ValidateFromContact()
    {
        if (!$this->ValidateEmail("email")) {
            $this->AddError("incorrect-email-format");
        }

        if (!$this->ValidateNotEmpty("first_name")) {
            $this->AddError("first-name-required");
        }

        if (!$this->ValidateNotEmpty("last_name")) {
            $this->AddError("last-name-required");
        }

        if (!$this->HasErrors()) {
            $stmt = GetStatement(DB_PERSONAL);

            $query = "SELECT COUNT(*) FROM user_info WHERE LOWER(email)=LOWER(" . $this->GetPropertyForSQL("email") . ") AND user_id<>" . $this->GetIntProperty("user_id");
            if ($stmt->FetchField($query)) {
                $this->AddError("email-is-not-unique");
            }
        }

        if (!$this->IsPropertySet("created_from")) {
            $this->SetProperty("created_from", "admin");
        }

        return !$this->HasErrors();
    }

    /**
     * Tries to upload new user image and initialize its config.
     * Resets current object user_image property by previously uploaded file if new file is not uploaded.
     *
     * @param string $savedImage previously uploaded filename
     *
     * @return bool false if error is occured during new image uploading or true if its uploaded successfully or no new image provided
     */
    function SaveUserImage($savedImage = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__CORE);

        $newUserImage = $fileStorage->Upload("user_image", USER_IMAGE_DIR, false, $this->_acceptMimeTypes);
        if ($newUserImage) {
            $this->SetProperty("user_image", $newUserImage["FileName"]);

            // Remove old image if it has different name
            if ($savedImage && $savedImage != $newUserImage["FileName"]) {
                $fileStorage->Remove(USER_IMAGE_DIR . $savedImage);
            }
        } else {
            if ($savedImage) {
                $this->SetProperty("user_image", $savedImage);
            } else {
                $this->SetProperty("user_image", null);
            }
        }

        if (!is_array($this->GetProperty("user_image_config")) || $newUserImage) {
            $this->SetProperty("user_image_config", array());
        }
        $this->_properties["user_image_config"]["Width"] = 0;
        $this->_properties["user_image_config"]["Height"] = 0;

        if ($this->GetProperty('user_image')) {
            if ($info = @getimagesize(USER_IMAGE_DIR . $this->GetProperty('user_image'))) {
                $this->_properties["user_image_config"]["Width"] = $info[0];
                $this->_properties["user_image_config"]["Height"] = $info[1];
            }
        }

        $this->AppendErrorsFromObject($fileStorage);

        return !$fileStorage->HasErrors();
    }

    /**
     * Tries to upload new user image and initialize its config outside of user saving context.
     *
     * @param int $userID user_id of user which image to be updated
     * @param string $savedImage previously uploaded filename
     *
     * @return bool false if error is occured during new image uploading or no new image provided or true if its uploaded successfully
     */
    function UpdateUserImage($userID, $savedImage = "")
    {
        $fileStorage = GetFileStorage(CONTAINER__CORE);

        $newUserImage = $fileStorage->Upload("user_image", USER_IMAGE_DIR, false, $this->_acceptMimeTypes);
        if (!$newUserImage) {
            $this->AppendErrorsFromObject($fileStorage);

            return false;
        }

        $this->SetProperty("user_image", $newUserImage["FileName"]);
        $this->SetProperty("user_image_config", array("Width" => 0, "Height" => 0));
        if ($info = @getimagesize(USER_IMAGE_DIR . $this->GetProperty('user_image'))) {
            $this->_properties["user_image_config"]["Width"] = $info[0];
            $this->_properties["user_image_config"]["Height"] = $info[1];
        }

        // Remove old image if it has different name
        if ($savedImage && $savedImage != $newUserImage["FileName"]) {
            $fileStorage->Remove(USER_IMAGE_DIR . $savedImage);
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = "UPDATE user_info
                    SET user_image=" . $this->GetPropertyForSQL("user_image") . ",
                        user_image_config=" . Connection::GetSQLString(json_encode($this->GetProperty("user_image_config"))) . "
                    WHERE user_id=" . intval($userID);
        $stmt->Execute($query);
        $this->UpdateSession($userID);

        return true;
    }

    /**
     * Removes user image from database record of user and file system
     *
     * @param int $userID user_id of user image to be removed
     * @param string $savedImage filename of image was uploaded but not saved to user database record yet
     */
    function RemoveUserImage($userID, $savedImage)
    {
        $fileStorage = GetFileStorage(CONTAINER__CORE);

        if ($savedImage) {
            $fileStorage->Remove(USER_IMAGE_DIR . $savedImage);
        }

        $userID = intval($userID);
        if ($userID <= 0) {
            return;
        }

        $stmt = GetStatement(DB_PERSONAL);
        $imageFile = $stmt->FetchField("SELECT user_image FROM user_info WHERE user_id=" . $userID);

        if ($imageFile) {
            $fileStorage->Remove(USER_IMAGE_DIR . $imageFile);
        }

        $stmt->Execute("UPDATE user_info SET user_image=NULL WHERE user_id=" . $userID);
        $this->UpdateSession($userID);
    }

    /**
     * Update fields like email or phone for selected user.
     *
     * @param int $userID user_id of user field to be changed
     * @param string $field changing field
     * @param string $value new value of changind field
     *
     * @return bool true if field is changes successfully
     */

    function UpdateField($userID, $field, $value)
    {
        $userID = intval($userID);
        if ($userID <= 0) {
            return;
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = in_array($field, array("first_name", "lst_name", "street"))
            ? "UPDATE user_info SET " . $field . "=" . Connection::GetSQLEncryption(Connection::GetSQLString($value)) . " WHERE user_id=" . $userID
            : "UPDATE user_info SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE user_id=" . $userID;
        $stmt->Execute($query);
        $this->UpdateSession($userID);

        $this->SaveHistory(array($field => null));

        return true;
    }

    /**
     * Sets new password to selected user.
     *
     * @param int $userID user_id of user password to be changed
     * @param string $oldPassword old password
     * @param string $newPassword new password
     *
     * @return bool true if password is changes successfully or false on failure
     */
    function ChangePassword($userID, $oldPassword, $newPassword)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT COUNT(user_id) FROM user_info WHERE user_id=" . intval($userID) . " AND password=MD5(" . Connection::GetSQLString($oldPassword) . ")";
        if (!$stmt->FetchField($query)) {
            $this->AddError("wrong-old-password");
        }

        if (strlen($newPassword) == 0) {
            $this->AddError("password-empty");
        }

        if (!$this->HasErrors()) {
            $query = "UPDATE user_info SET password=MD5(" . Connection::GetSQLString($newPassword) . ") WHERE user_id=" . intval($userID);
            if (!$stmt->Execute($query)) {
                return false;
            }
            $this->SetProperty("password1", $newPassword);
            $this->SaveHistory(array('password' => md5($oldPassword)), 'admin', $userID);

            return true;
        }

        return false;
    }

    /**
     * Generates new password for user found by "email" property and sends it to this email
     *
     * @param $new bool
     * @param $saveFromContact bool
     *
     * @return bool true if password is changed successfully or false on failure
     */
    function SendPasswordToEmail($new = false, $saveFromContact = false)
    {
        if ($this->ValidateEmail('email')) {
            $stmt = GetStatement(DB_PERSONAL);
            $password = $this->GeneratePassword();
            $stmt->Execute("UPDATE user_info SET password=md5(" . Connection::GetSQLString($password) . ") WHERE LOWER(email)=LOWER(" . $this->GetPropertyForSQL('email') . ")");
            if ($stmt->GetAffectedRows()) {
                $this->SetProperty("password1", $password);
                $this->SaveHistory(array('password' => null));
                if ($this->SendPasswordEmail($this->GetProperty("email"), $password, $new, $saveFromContact)) {
                    $this->AddMessage("password-is-changed-and-sent");

                    $session =& GetSession();
                    $session->RemoveProperty('LoginAttempts');
                    $session->SaveToDB();

                    return true;
                }

                $this->AddError("error-sending-email");
            } else {
                $this->AddError("incorrect-email-address");
            }
        } else {
            $this->AddError("incorrect-email-format");
        }

        return false;
    }

    /**
     * Send password to user email
     *
     * @param $email string
     * @param $password string
     * @param $new bool
     * @param $saveFromContact bool
     *
     * @return bool true if password is sent successfully or false on failure
     */
    function SendPasswordEmail($email, $password, $new = false, $saveFromContact = false)
    {
        $emailTemplate = new PopupPage();

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT email, salutation,
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info WHERE LOWER(email)=LOWER(" . Connection::GetSQLString($email) . ")";
        $userProperties = $stmt->FetchRow($query);

        $baseUrl = IsDemoEnvironment() ? "demo.trebono.de" : "service.trebono.de";
        $logoPath = PROJECT_DIR . "admin/template/images/email-footer-logo.png";
        $subject = $new ? GetTranslation("registration-password") : GetTranslation("forgot-password");
        $attachments = [];

        if ($saveFromContact) {
            $tmpl = $emailTemplate->Load("registration_contact_email.html");
        } elseif (!$new) {
            $tmpl = $emailTemplate->Load("forgot_password_email.html");
        } else {
            $tmpl = $emailTemplate->Load("email_default_template.html");
        }

        $companyUnitTitle = $this->IsPropertySet("company_unit_title") ?
            $this->GetProperty("company_unit_title") : "";
        $companyUnitRegText = $this->IsPropertySet("company_unit_reg_email_text") ?
            $this->GetProperty("company_unit_reg_email_text") : "";
        if ($new && !$saveFromContact) {
            $textTemplate = Config::GetConfigValue("registration_email_text");

            $replacements = array_merge($userProperties, [
                "logo" => "cid:logo",
                "password" => $password,
                "base_url" => $baseUrl,
                "company-title" => $companyUnitTitle,
                "company-reg_email_text" => $companyUnitRegText,
            ]);

            $emailText = GetLanguage()->ReplacePairs($textTemplate, $replacements);

            $tmpl->SetVar("email_text", $emailText);
        } elseif ($saveFromContact) {
            $textTemplate = Config::GetConfigValue("contact_registration_email_text");

            $replacements = array_merge($userProperties, [
                "logo" => "cid:logo",
                "password" => $password,
                "base_url" => $baseUrl,
                "company-title" => $companyUnitTitle,
                "company-reg_email_text" => $companyUnitRegText,
            ]);

            $emailText = GetLanguage()->ReplacePairs($textTemplate, $replacements);

            $tmpl->SetVar("email_text", $emailText);
        } else {
            $tmpl->LoadFromArray($userProperties);
            $tmpl->SetVar("base_url", $baseUrl);
            $tmpl->SetVar("Password", $password);
        }

        if (!$saveFromContact) {
            $getStarted = array("value" => Config::GetConfigValue("get_started_document"));
            PrepareDownloadPath($getStarted, "value", CONFIG_FILE_DIR, CONTAINER__CORE);
            $attachments = [["URL" => $getStarted["value_download_url"], "FileName" => "trebono_get_started.pdf"]];
        }

        $result = SendMailFromAdmin(
            $email,
            $subject,
            $emailTemplate->Grab($tmpl),
            [],
            [["Path" => $logoPath, "CID" => "logo"]],
            $attachments
        );

        return $result === true;
    }

    /**
     * Sends notification about changing banking details to employee email
     *
     * @return bool true if password is changed successfully or false on failure
     */
    function SendBankingDetailsEmail($bankingDetailsInfo)
    {
        if ($this->ValidateEmail('email')) {
            $emailTemplate = new PopupPage();
            $attachments = array();
            $tmpl = $emailTemplate->Load("notification_banking_details_email.html");
            $tmpl->SetLoop("BankingDetailsInfo", $bankingDetailsInfo);

            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT email, salutation,
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info WHERE LOWER(email)=LOWER(" . Connection::GetSQLString($this->GetProperty('email')) . ")";
            $tmpl->LoadFromArray($stmt->FetchRow($query));

            $baseUrl = IsDemoEnvironment() ? "demo.trebono.de" : "service.trebono.de";
            $tmpl->SetVar("base_url", $baseUrl);

            $subject = "Änderung Ihrer Bank Daten";

            $result = SendMailFromAdmin(
                $this->GetProperty("email"),
                $subject,
                $emailTemplate->Grab($tmpl),
                array(),
                array(array("Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo")),
                $attachments
            );

            if ($result) {
                $this->AddMessage("notification-sent");

                return true;
            }

            $this->AddError("error-sending-email");
        } else {
            $this->AddError("incorrect-email-format");
        }

        return false;
    }

    /**
     * Generated random password
     *
     * @return string generated password
     */
    function GeneratePassword()
    {
        $arr = array(
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'j',
            'k',
            'm',
            'n',
            'p',
            'r',
            's',
            't',
            'u',
            'v',
            'x',
            'y',
            'z',
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'J',
            'K',
            'M',
            'N',
            'P',
            'R',
            'S',
            'T',
            'U',
            'V',
            'X',
            'Y',
            'Z',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
        );

        $number = mt_rand(6, 10);

        $pass = "";

        for ($i = 0; $i < $number; $i++) {
            $index = mt_rand(0, count($arr) - 1);
            $pass .= $arr[$index];
        }

        return $pass;
    }

    /**
     * Returns list of all system permissions for user edition. Also adds "checked" property and link ids for permissions granted for current user
     *
     * @return array permission list
     */
    function GetAvailablePermissions(bool $all = true)
    {
        $stmt = GetStatement(DB_PERSONAL);

        $join = $all ? 'LEFT' : 'INNER';
        $query = $this->GetIntProperty("user_id")
            ? "SELECT p.permission_id, p.name, p.title, p.link_to, p.group_id,
                    string_agg(CAST(up.link_id AS varchar), ',') AS link_ids,
                    CASE WHEN up.user_id IS NULL THEN 0 ELSE 1 END AS checked
                FROM permission p
                {$join} JOIN user_permissions up
                    ON p.permission_id = up.permission_id AND up.user_id = " . $this->GetIntProperty("user_id") . "
                GROUP BY p.permission_id, up.user_id
                ORDER BY p.sort_order, p.title"
            : "SELECT p.permission_id, p.name, p.title, p.link_to, p.group_id
                FROM permission p
                ORDER BY p.sort_order, p.title";

        return $stmt->FetchList($query);
    }

    public static function GetPermissionID($name)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT permission_id FROM permission WHERE name = " . Connection::GetSQLString($name);

        return $stmt->FetchField($query);
    }

    public static function ValidatePermissionList($permissionIDs)
    {
        $count = count($permissionIDs);
        for ($i = 0; $i < $count; $i++) {
            if ($permissionIDs[$i] == User::GetPermissionID('employee_view')) {
                if (array_search(User::GetPermissionID('api'), $permissionIDs) === false) {
                    $permissionIDs[$count] = User::GetPermissionID('api');
                }
            }
            if ($permissionIDs[$i] != User::GetPermissionID('api')) {
                continue;
            }

            if (array_search(User::GetPermissionID('employee_view'), $permissionIDs) !== false) {
                continue;
            }

            $permissionIDs[$count] = User::GetPermissionID('employee_view');
        }

        return $permissionIDs;
    }

    /**
     * Updates user permissions in database.
     *
     * @param array $permissionIDs correct structure is array({permission_id}, {permission_id}, {permission_id})
     * @param array $linkIDs correct structure is array({permission_id} => array({link_id}, {link_id}, {link_id}), {permission_id} => array({link_id}, {link_id}))
     */
    function UpdatePermissions($permissionIDs, $linkIDs)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "DELETE FROM user_permissions up WHERE up.user_id = " . $this->GetIntProperty("user_id");
        $stmt->Execute($query);

        if (!is_array($permissionIDs)) {
            return;
        }

        foreach ($permissionIDs as $permissionID) {
            if (isset($linkIDs[$permissionID]) && count($linkIDs[$permissionID]) > 0) {
                $linkIDs[$permissionID] = array_unique(array_filter($linkIDs[$permissionID]));

                //if permission is linked to company_unit ask it to clear ids from parent ones
                $query = "SELECT link_to FROM permission WHERE permission_id=" . intval($permissionID);

                foreach ($linkIDs[$permissionID] as $linkID) {
                    $query = "INSERT INTO user_permissions (user_id, permission_id, link_id) VALUES(" . $this->GetIntProperty("user_id") . "," . $permissionID . "," . $linkID . ")";
                    $stmt->Execute($query);
                }
            } else {
                $query = "INSERT INTO user_permissions (user_id, permission_id) VALUES(" . $this->GetIntProperty("user_id") . "," . $permissionID . ")";
                $stmt->Execute($query);
            }
        }
    }

    /**
     * Returns link ids for selected permission. Object must be fully loaded before this method will be called.
     *
     * @param string $permissionName name of permission link ids should be returned
     *
     * @return array
     */
    function GetPermissionLinkIDs($permissionName)
    {
        $result = array();
        foreach ($this->GetProperty("PermissionList") as $permission) {
            if ($permission["name"] != $permissionName) {
                continue;
            }

            $result[] = $permission["link_id"];
        }

        return array_filter($result);
    }

    /**
     * Updates LoggedInUser property of existing sessions to make user changes to be applied instantly.
     *
     * @param int $userID
     */
    function UpdateSession($userID)
    {
        $user = new User();
        $user->LoadByID($userID);
        $user->LoadPermissions();

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT * FROM user_session WHERE user_id=" . $user->GetPropertyForSQL("user_id");
        $sessionList = $stmt->FetchList($query);
        foreach ($sessionList as $s) {
            $s["session_data"] = unserialize($s["session_data"]);
            if (!isset($s["session_data"]["LoggedInUser"])) {
                continue;
            }

            $s["session_data"]["LoggedInUser"] = $user->GetProperties();
            $s["session_data"] = serialize($s["session_data"]);
            $query = "UPDATE user_session SET session_data=" . Connection::GetSQLString($s["session_data"]) . "
                        WHERE session_id=" . Connection::GetSQLString($s["session_id"]);
            $stmt->Execute($query);
        }
    }

    function DeleteSession($userID)
    {
        $user = new User();
        $user->LoadByID($userID);
        $user->LoadPermissions();

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT * FROM user_session WHERE user_id=" . $user->GetPropertyForSQL("user_id");
        $sessionList = $stmt->FetchList($query);
        foreach ($sessionList as $s) {
            $s["session_data"] = unserialize($s["session_data"]);
            if (isset($s["session_data"]["LoggedInUser"])) {
                unset($s["session_data"]["LoggedInUser"]);
            }

            $s["session_data"]["MustRelogin"] = 1;
            $s["session_data"] = serialize($s["session_data"]);
            $query = "UPDATE user_session SET session_data=" . Connection::GetSQLString($s["session_data"]) . "
                        WHERE session_id=" . Connection::GetSQLString($s["session_id"]);
            $stmt->Execute($query);
        }
    }

    /**
     * Return name of user by id.
     *
     * @param int $id of required user
     *
     * @return string
     */
    static function GetNameByID($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info WHERE user_id=" . intval($id);
        $result = $stmt->FetchRow($query);
        if ($result != null && $result != false) {
            return implode(" ", $result);
        }

        return $result;
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $userID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListuser($property, $userID)
    {
        if ($property == "password1") {
            $property = "password";
        }

        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, start_user_id AS user_id, created, value, property_name, created_from
                    FROM user_history
                    WHERE property_name=" . Connection::GetSQLString($property) . " AND end_user_id=" . intval($userID) . "
                    ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        if (!$valueList) {
            return $valueList;
        }

        $userIDs = array_column($valueList, "user_id");

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id,
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info
                    WHERE user_id IN(" . implode(",", $userIDs) . ")";
        $userInfo = $stmt->FetchIndexedList($query, "user_id");

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["first_name"] = $userInfo[$valueList[$i]['user_id']]['first_name'];
            $valueList[$i]["last_name"] = $userInfo[$valueList[$i]['user_id']]['last_name'];
        }

        return $valueList;
    }

    /**
     * Returns permission value history for selected user
     *
     * @param int $userID
     *
     * @return array list of values
     */
    public static function GetPermissionValueListUser($userID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, start_user_id AS user_id, created, value, permission_id
                    FROM user_permission_history
                    WHERE end_user_id=" . intval($userID) . "
                    ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        if (!$valueList) {
            return $valueList;
        }

        $userIDs = array_column($valueList, "user_id");
        $values = array_unique(array_filter(
            array_column($valueList, "value"),
            static function ($val) {
                switch ($val) {
                    case "Y":
                    case "N":
                        return false;
                }

                return boolval($val);
            }
        ));
        $permissionIDs = array_unique(array_column($valueList, "permission_id"));

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id,
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info
                    WHERE user_id IN(" . implode(",", $userIDs) . ")";
        $userInfo = $stmt->FetchIndexedList($query, "user_id");

        $query = "SELECT permission_id, name FROM permission WHERE permission_id IN(" . implode(
            ",",
            $permissionIDs
        ) . ")";
        $permissionInfo = $stmt->FetchIndexedList($query, "permission_id");

        if ($values) {
            $stmt = GetStatement(DB_MAIN);
            $query = "SELECT company_unit_id, " . Connection::GetSQLDecryption("title") . " AS title FROM company_unit WHERE company_unit_id IN(" . implode(
                ",",
                $values
            ) . ")";
            $companyInfo = $stmt->FetchIndexedList($query, "company_unit_id");
        }

        for ($i = 0; $i < count($valueList); $i++) {
            $valueList[$i]["first_name"] = $userInfo[$valueList[$i]['user_id']]['first_name'];
            $valueList[$i]["last_name"] = $userInfo[$valueList[$i]['user_id']]['last_name'];
            $valueList[$i]["property_name"] = GetTranslation("permission-" . $permissionInfo[$valueList[$i]['permission_id']]['name']);

            if ($valueList[$i]["value"] == "Y" || $valueList[$i]["value"] == "N" || !$valueList[$i]["value"]) {
                continue;
            }

            $value = explode(",", $valueList[$i]["value"]);
            for ($j = 0; $j < count($value); $j++) {
                $value[$j] = $companyInfo[$value[$j]]['title'];
            }
            $valueList[$i]["value"] = implode(", ", $value);
        }

        return $valueList;
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */

    public function SaveHistory($currentPropertyList, $createdFrom = "admin", $userId = null)
    {
        $stmt = GetStatement(DB_CONTROL);

        if (is_null($userId)) {
            $user = new User();
            $user->LoadBySession();
            $userId = $user->GetIntProperty("user_id");
        }

        $propertyList = array(
            "salutation",
            "first_name",
            "last_name",
            "birthday",
            "street",
            "house",
            "zip_code",
            "city",
            "country",
            "phone",
            "email",
            "password",
            "PermissionList",
            "belongs_to_company"
        );

        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        } else {
            $propertyList = array_intersect($propertyList, array_keys($currentPropertyList));
        }

        $updateSession = false;
        foreach ($propertyList as $key) {
            if (($key == "birthday") && $this->GetProperty($key)) {
                $this->SetProperty($key, date("Y-m-d", strtotime($this->GetProperty($key))));
            }

            if ($key == "password") {
                if (!$this->GetProperty("password1") || $currentPropertyList[$key] == md5($this->GetProperty("password1"))) {
                    continue;
                }

                $this->SetProperty($key, "");
            }

            if ($key == "PermissionList" && isset($currentPropertyList['PermissionIDs'])) {
                $deletedPerms = array_diff(
                    $currentPropertyList['PermissionIDs'],
                    (array)$this->GetProperty("PermissionIDs")
                );
                foreach ($deletedPerms as $perm) {
                    $query = "INSERT INTO user_permission_history (end_user_id, permission_id, value, created, start_user_id, created_from)
                    VALUES (
                    " . $this->GetIntProperty("user_id") . ",
                    " . intval($perm) . ",
                    'N',
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . $userId . ",
                    " . Connection::GetSQLString($createdFrom) . ")
                    RETURNING value_id";
                    if (!$stmt->Execute($query)) {
                        return false;
                    }
                    $updateSession = true;
                }

                if (is_array($this->GetProperty("PermissionIDs"))) {
                    $newPerms = array_diff($this->GetProperty("PermissionIDs"), $currentPropertyList['PermissionIDs']);
                    foreach ($newPerms as $perm) {
                        $query = "INSERT INTO user_permission_history (end_user_id, permission_id, value, created, start_user_id, created_from)
                        VALUES (
                        " . $this->GetIntProperty("user_id") . ",
                        " . intval($perm) . ",
                        'Y',
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $userId . ",
                        " . Connection::GetSQLString($createdFrom) . ")
                        RETURNING value_id";
                        if (!$stmt->Execute($query)) {
                            return false;
                        }
                        $updateSession = true;
                    }
                }

                $permissionLinkIDs = $this->IsPropertySet("PermissionLinkIDs")
                    ? $this->GetProperty("PermissionLinkIDs")
                    : array();
                $linkKeys = array_unique(array_merge(
                    array_keys($currentPropertyList['PermissionList']),
                    array_keys($permissionLinkIDs)
                ));

                foreach ($linkKeys as $key) {
                    if (
                        isset($currentPropertyList['PermissionList'][$key]) && isset($permissionLinkIDs[$key]) && !symm_diff(
                            $permissionLinkIDs[$key],
                            $currentPropertyList['PermissionList'][$key]
                        )
                    ) {
                        continue;
                    }

                    if (!in_array($key, $this->GetProperty("PermissionIDs"))) {
                        $value = "N";
                    } elseif (isset($permissionLinkIDs[$key])) {
                        $value = implode(",", array_filter($permissionLinkIDs[$key]));
                    } else {
                        $value = "";
                    }

                    $query = "INSERT INTO user_permission_history (end_user_id, permission_id, value, created, start_user_id, created_from)
                        VALUES (
                        " . $this->GetIntProperty("user_id") . ",
                        " . intval($key) . ",
                                " . Connection::GetSQLString($value) . ",
                        " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                        " . $userId . ",
                        " . Connection::GetSQLString($createdFrom) . ")
                        RETURNING value_id";
                    if (!$stmt->Execute($query)) {
                        return false;
                    }
                    $updateSession = true;
                }
            } else {
                if ($this->IsPropertySet($key) && ($currentPropertyList[$key] != $this->GetProperty($key) || $key == "password")) {
                    $query = "INSERT INTO user_history (end_user_id, property_name, value, created, start_user_id, created_from)
                    VALUES (
                    " . $this->GetIntProperty("user_id") . ",
                    " . Connection::GetSQLString($key) . ",
                    " . Connection::GetSQLString($this->GetProperty($key)) . ",
                    " . Connection::GetSQLString(GetCurrentDateTime()) . ",
                    " . $userId . ",
                    " . Connection::GetSQLString($createdFrom) . ")
                    RETURNING value_id";
                    if (!$stmt->Execute($query)) {
                        return false;
                    }
                }
            }
        }

        if ($updateSession) {
            $this->DeleteSession($this->GetIntProperty("user_id"));
        }
        if (!$this->GetIntProperty("value_id") > 0) {
            $this->SetProperty("value_id", $stmt->GetLastInsertID());
        }

        return true;
    }

    /**
     * Returns array of current value of properties
     *
     * @param int $id user_id whose values is searched
     *
     * @return array
     */

    public function GetCurrentPropertyList($id)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT salutation, birthday, house, zip_code, city, country, phone, email, password, belongs_to_company,
                        " . Connection::GetSQLDecryption("first_name") . " AS first_name,
                        " . Connection::GetSQLDecryption("last_name") . " AS last_name
                    FROM user_info
                    WHERE user_id=" . intval($id);
        $currentPropertyList = $stmt->FetchRow($query);
        if (!$currentPropertyList) {
            return array();
        }

        $query = "SELECT permission_id FROM user_permissions WHERE user_id=" . intval($id);
        $permissionIDs = $stmt->FetchList($query);
        $currentPropertyList['PermissionIDs'] = array_unique(array_column($permissionIDs, "permission_id"));

        $query = "SELECT permission_id, link_id FROM user_permissions WHERE user_id=" . intval($id) . " AND link_id IS NOT NULL";
        $permissionList = $stmt->FetchList($query);
        $permissons = array_unique(array_column($permissionList, "permission_id"));
        $currentPropertyList['PermissionList'] = array_fill_keys($permissons, array());
        foreach ($permissionList as $permission) {
            $currentPropertyList['PermissionList'][$permission['permission_id']][] = $permission['link_id'];
        }

        return $currentPropertyList;
    }

    /**
     * Loads info about activations and inactivations of this user from user_history
     *
     * @return array as result or false on fail.
     */
    public function LoadArchiveInfo()
    {
        if (!$this->GetProperty("user_id")) {
            return false;
        }

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM user_history
                    WHERE end_user_id=" . $this->GetProperty("user_id") . " AND property_name='archive'
                    ORDER BY created DESC";
        if (!$result = $stmt->FetchList($query)) {
            return false;
        }

        $userList = array_column($result, "start_user_id");
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT " . Connection::GetSQLDecryption("first_name") . "||' '||" . Connection::GetSQLDecryption("last_name") . " AS username, user_id FROM user_info WHERE user_id IN (" . implode(
            ",",
            $userList
        ) . ")";
        $userList = $stmt->FetchIndexedList($query, "user_id");
        for ($i = 0; $i < count($result); $i++) {
            $result[$i]['username'] = $userList[$result[$i]['start_user_id']]['username'];
        }

        return $result;
    }

    /**
     * Loads user by spicific email
     * Available for admin users only
     *
     * @param string $email email to search
     */
    public function LoadByEmail($email)
    {
        $query = $this->GetQueryPrefix() . " WHERE LOWER(email)=LOWER(" . Connection::GetSQLString($email) . ")";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
        $this->LoadPermissions();
    }

    /**
     * Generate and save acces token for loaded user
     */
    public function GenerateAccessToken()
    {
        if ($this->GetProperty("user_id")) {
            $accessToken = md5($this->GetProperty("email") . date("U") . rand(1000, 9999));

            $this->UpdateField($this->GetProperty("user_id"), "access_token", $accessToken);
            $this->UpdateField(
                $this->GetProperty("user_id"),
                "access_token_expire_date",
                Connection::GetSQLDateTime(date("Y-m-d H:i:s", strtotime("+ 300 seconds")))
            );

            return $accessToken;
        }

        return false;
    }

    public function LoadByAccessToken($accessToken)
    {
        $session =& GetSession();

        $query = $this->GetQueryPrefix() . " WHERE
            access_token=" . Connection::GetSQLString($accessToken) . " AND
            access_token_expire_date>=" . Connection::GetSQLDateTime(GetCurrentDateTime());

        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        if ($this->GetProperty("archive") == "Y") {
            $this->AddError("login-denied");

            return false;
        }

        if ($this->GetIntProperty("user_id")) {
            $this->PrepareBeforeShow();

            self::UpdateLastLogin($this->GetProperty("user_id"));

            $this->LoadPermissions();

            $session->SetProperty("LoggedInUser", $this->GetProperties());
            $session->SaveToDB(1);

            $this->UpdateField($this->GetProperty("user_id"), "access_token", null);
            $this->UpdateField($this->GetProperty("user_id"), "access_token_expire_date", null);

            return true;
        }

        $this->AddError("access-token-expire-or-wrong");

        return false;
    }

    /** Method for web API. Adds company unit to permission list
     *
     * @param $userID
     * @param $companyUnitID
     * @param $permissionCode
     * @param $createdFrom
     */
    public static function AddPermissionLink($userID, $companyUnitID, $permissionCode, $createdFrom = "admin")
    {
        $user = new User();
        $user->LoadByID($userID);
        $permissionIDs = array();

        $user->LoadPermissions();
        $userPermissionIDs = $user->GetProperty("PermissionList");

        //breaking PermissionList into IDs and LinkIDs for SaveHistory
        $linkIDs = array();
        foreach ($userPermissionIDs as $permission) {
            if (!$permission["link_id"]) {
                continue;
            }

            $linkIDs[$permission["permission_id"]][] = $permission["link_id"];
        }
        $currentPropertyList = array(
            "PermissionIDs" => array_unique(array_column($userPermissionIDs, "permission_id")),
            "PermissionList" => $linkIDs
        );

        $companyPermissionID = User::GetPermissionID($permissionCode);
        if ($key = array_search($companyPermissionID, array_column($userPermissionIDs, "permission_id")) !== false) {
            if ($userPermissionIDs[$key]["link_id"] != "") {
                $userPermissionIDs[] = array(
                    'permission_id' => $companyPermissionID,
                    'name' => 'company_unit',
                    'link_to' => 'company_unit',
                    'link_id' => $companyUnitID
                );
            }
        } else {
            $userPermissionIDs[] = array(
                'permission_id' => $companyPermissionID,
                'name' => 'company_unit',
                'link_to' => 'company_unit',
                'link_id' => $companyUnitID
            );
        }

        if ($permissionCode == "company_unit") {
            $payrollPermissionID = User::GetPermissionID('tax_auditor');
            if (
                $key = array_search(
                    $payrollPermissionID,
                    array_column($userPermissionIDs, "permission_id")
                ) !== false
            ) {
                if ($userPermissionIDs[$key]["link_id"] != "") {
                    $userPermissionIDs[] = array(
                        'permission_id' => $payrollPermissionID,
                        'name' => 'company_unit',
                        'link_to' => 'company_unit',
                        'link_id' => $companyUnitID
                    );
                }
            } else {
                $userPermissionIDs[] = array(
                    'permission_id' => $payrollPermissionID,
                    'name' => 'company_unit',
                    'link_to' => 'company_unit',
                    'link_id' => $companyUnitID
                );
            }
        }

        foreach ($permissionIDs as $permissionID) {
            if ($key = array_search($permissionID, array_column($userPermissionIDs, "permission_id"))) {
                if ($userPermissionIDs[$key]["link_id"] != "") {
                    $userPermissionIDs[] = array("permission_id" => $permissionID, "link_id" => $companyUnitID);
                }
            } else {
                $userPermissionIDs[] = array("permission_id" => $permissionID, "link_id" => $companyUnitID);
            }
        }
        $linkIDs = array();
        foreach ($userPermissionIDs as $permission) {
            if (!$permission["link_id"]) {
                continue;
            }

            $linkIDs[$permission["permission_id"]][] = $permission["link_id"];
        }
        $permissionIDs = array_unique(array_column($userPermissionIDs, "permission_id"));

        //put permissions to current object history to be saved correctly
        $user->SetProperty("PermissionIDs", $permissionIDs);
        $user->SetProperty("PermissionLinkIDs", $linkIDs);

        $user->SaveHistory($currentPropertyList, $createdFrom);
        $user->UpdatePermissions($user->GetProperty("PermissionIDs"), $user->GetProperty("PermissionLinkIDs"));
    }

    public function GetCompanyUnitLogo(): ?string
    {
        if (
            !$this->IsPropertySet("company_unit")
            || !($companyUnit = $this->GetProperty("company_unit"))
            || !$companyUnit->IsPropertySet("app_logo_image")
            || !$companyUnit->IsPropertySet("app_logo_mini_image")
        ) {
            $this->LoadCompanyUnitLogo();
        }

        $companyUnit = $this->GetProperty("company_unit");

        return $companyUnit->GetProperty("app_logo_mini_image_admin_url") ?? $companyUnit->GetProperty("app_logo_image_admin_url");
    }

    public function LoadCompanyUnitLogo(): void
    {
        if (!$this->IsPropertySet("company_unit_ids")) {
            $this->LoadCompanyUnitIds();
        }

        $companyUnitIds = $this->GetProperty("company_unit_ids");

        if (count($companyUnitIds) > 0) {
            $statement = GetStatement(DB_MAIN);
            $companyUnitIds = implode(", ", array_map(function($id) {
                return (int) $id;
            }, $companyUnitIds));
            $query = "SELECT cu.app_logo_image, cu.app_logo_mini_image
FROM company_unit cu
WHERE cu.company_unit_id IN ({$companyUnitIds})
LIMIT 1";
            $companyUnits = $statement->FetchList($query);
            $properties = $companyUnits[0];
        } else {
            $properties = [
                "app_logo_image" => null,
                "app_logo_mini_image" => null,
            ];
        }

        if ($this->IsPropertySet("company_unit")) {
            $companyUnit = $this->GetProperty("company_unit");
            $companyUnit->AppendFromArray($properties);
        } else {
            $companyUnit = new CompanyUnit("company", $properties);
            $this->SetProperty("company_unit", $companyUnit);
        }
        $companyUnit->PrepareImages();
    }

    public function LoadCompanyUnitIds(): void
    {
        if (!$this->IsPropertySet("user_id")) {
            throw new LogicException("User not loaded");
        }
        $userId = (int) $this->GetProperty("user_id");

        $statement = GetStatement(DB_PERSONAL);
        $query = "SELECT e.company_unit_id
FROM employee e
WHERE e.user_id = {$userId}";
        $companyUnits = $statement->FetchList($query);
        $companyUnitIds = array_map(function (array $company): int {
            return (int) $company["company_unit_id"];
        }, $companyUnits);

        $this->SetProperty("company_unit_ids", $companyUnitIds);
    }
}
