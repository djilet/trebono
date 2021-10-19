<?php

class UserList extends LocalObjectList
{
    var $module;
    var $params;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function UserList($data = array())
    {
        parent::LocalObjectList($data);

        $this->SetItemsOnPage(abs(intval(GetFromConfig("UsersPerPage"))));

        $this->SetSortOrderFields(array(
            "UserIDAsc" => "user_id ASC",
            "UserIDDesc" => "user_id DESC",
            "CreatedAsc" => "created ASC",
            "CreatedDesc" => "created DESC",
            "NameAsc" => Connection::GetSQLDecryption("first_name") . " ASC, " . Connection::GetSQLDecryption("last_name") . " ASC",
            "NameDesc" => Connection::GetSQLDecryption("first_name") . " DESC, " . Connection::GetSQLDecryption("last_name") . " DESC",
            "LastLoginAsc" => "last_login ASC",
            "LastLoginDesc" => "last_login DESC"
        ));
        $this->SetDefaultOrderByKey(GetFromConfig("UsersOrderBy"));
    }

    /**
     * Returns common query prefix containing set of fields to select
     *
     * @return string
     */
    function GetQueryPrefix()
    {
        return "SELECT i.user_id, i.email, " . Connection::GetSQLDecryption("i.first_name") . " AS first_name, " . Connection::GetSQLDecryption("i.last_name") . " AS last_name, 
						i.phone, i.created, i.last_login, i.last_ip, i.user_image, i.archive, i.belongs_to_company
					FROM user_info AS i ";
    }

    /**
     * Loads user list using filter params
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>OrderBy</u> - string - order type (see constructor for available order types)</li>
     *        <li><u>SearchString</u> - string - property for user name/phone/email filtration</li>
     *        </ul>
     */
    function LoadUserList($request)
    {
        $this->SetOrderBy($_REQUEST[$this->GetOrderByParam()] ?? GetFromConfig("UsersOrderBy"));

        $where = array();

        if ($request->GetProperty("SearchString")) {
            $subWhere = array();
            $subWhere[] = "CONCAT(" . Connection::GetSQLDecryption("i.first_name") . ", ' ', " . Connection::GetSQLDecryption("i.last_name") . ") ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("SearchString"));
            $subWhere[] = "i.email ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("SearchString"));
            $phoneSearch = preg_replace("/[^\d]/", "", $request->GetProperty("SearchString"));
            if ($phoneSearch) {
                $subWhere[] = "REGEXP_REPLACE(i.phone, '[^\d]', '', 'g') ILIKE('%" . Connection::GetSQLLike($phoneSearch) . "%')";
            }
            $where[] = "( " . implode(" OR ", $subWhere) . " )";
        }

        $join[] = "LEFT JOIN user_permissions AS up ON i.user_id=up.user_id ";
        $group[] = "i.user_id";
        $having = array();
        if ($request->GetProperty("filter_user") == "employee") {
            $join[] = "INNER JOIN user_permissions AS p ON i.user_id=p.user_id AND p.permission_id=4";
            $having[] = "COUNT(i.user_id)=1";
        } elseif ($request->GetProperty("filter_user") == "administrator") {
            $where[] = "up.permission_id!=4";

            if ($request->GetProperty("filter_company_unit")) {
                $stmt = GetStatement();
                $request->SetProperty(
                    "filter_company_unit",
                    htmlspecialchars_decode($request->GetProperty("filter_company_unit"))
                );
                $query = "SELECT company_unit_id FROM company_unit 
                          WHERE " . Connection::GetSQLDecryption("title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("filter_company_unit"));
                $tmpCompanyUnitIDs = array_keys($stmt->FetchIndexedList($query));

                $companyUnitIDs = array();
                foreach ($tmpCompanyUnitIDs as $companyUnitID) {
                    $companyUnitIDs = array_merge(
                        $companyUnitIDs,
                        CompanyUnitList::GetCompanyUnitPath2Root($companyUnitID, true)
                    );
                }

                if (count($companyUnitIDs) > 0) {
                    $where[] = "(up.link_id IN (" . implode(", ", Connection::GetSQLArray($companyUnitIDs)) . ") OR
                    (up.link_id IS NULL AND up.permission_id IN (" . implode(
                        ", ",
                        Connection::GetSQLArray(User::GetPermissionListByLinkTo("company_unit"))
                    ) . " )))";
                } else {
                    $where[] = "(up.link_id IS NULL AND up.permission_id IN (" . implode(
                        ", ",
                        Connection::GetSQLArray(User::GetPermissionListByLinkTo("company_unit"))
                    ) . " ))";
                }
            }

            if ($request->GetProperty("filter_from_company_unit")) {
                $where[] = "i.belongs_to_company ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("filter_from_company_unit"));
            }
        }

        if (!$request->IsPropertySet("FilterArchive")) {
            $where[] = "i.archive='N'";
        } elseif ($request->GetProperty("FilterArchive")) {
            $where[] = "i.archive=" . $request->GetPropertyForSQL("FilterArchive");
        }

        $query = $this->GetQueryPrefix() . (count($join) > 0 ? " " . implode(
            " ",
            $join
        ) : "") . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "")
            . (count($group) > 0 ? " GROUP BY " . implode(
                ",",
                $group
            ) : "") . (count($having) > 0 ? " HAVING " . implode(" ", $having) : "");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
    }

    /**
     * NOT Removes users from database by provided ids.
     * Just make them inactive.
     * Also inactivate concerned employees.
     * Removes their sessions.
     *
     * @param array $ids array of user_id's
     */
    function Remove($request)
    {
        $ids = $request->GetProperty("UserIDs");
        if (is_array($ids) && count($ids) > 0) {
            $where = array();

            $where[] = "user_id IN (" . implode(",", Connection::GetSQLArray($ids)) . ")";

            if ($request->GetIntProperty("CurrentUserID") > 0) {
                $where[] = "user_id<>" . $request->GetIntProperty("CurrentUserID");
            } elseif ($request->GetIntProperty("CurrentUserID") == 0) {
                $request->SetProperty("CurrentUserID", SERVICE_USER_ID);
            }

            $stmt = GetStatement(DB_PERSONAL);

            $removed = array();
            $removedIDs = array();

            if (!$request->IsPropertySet("CreatedFrom")) {
                $request->SetProperty("CreatedFrom", "admin");
            }

            $query = $this->GetQueryPrefix() . " WHERE " . implode(" AND ", $where);
            $values = array();
            $employeeValues = array();
            $employee = new Employee("company");
            if ($result = $stmt->FetchList($query)) {
                for ($i = 0; $i < count($result); $i++) {
                    $removed[] = $result[$i]['first_name'] . " " . $result[$i]['last_name'];
                    $removedIDs[] = $result[$i]['user_id'];
                    $values[] = "(" . $result[$i]['user_id'] . ",'archive','Y','" . GetCurrentDateTime() . "'," . $request->GetIntProperty("CurrentUserID") . ", " . Connection::GetSQLString($request->GetProperty("CreatedFrom")) . ")";

                    $employee->LoadByUserID($result[$i]['user_id']);
                    $employeeValues[] = "(" . $employee->GetIntProperty("employee_id") . ",'archive','Y','" . GetCurrentDateTime() . "'," . $request->GetIntProperty("CurrentUserID") . ", " . Connection::GetSQLString($request->GetProperty("CreatedFrom")) . ")";
                }
            }

            $count = count($removed);

            if ($count > 0) {
                // Delete user sessions
                $query = "DELETE FROM user_session WHERE user_id IN (" . implode(
                    ",",
                    Connection::GetSQLArray($removedIDs)
                ) . ")";
                $stmt->Execute($query);

                // Update employees
                $query = "UPDATE employee SET archive='Y' WHERE user_id IN (" . implode(
                    ",",
                    Connection::GetSQLArray($removedIDs)
                ) . ")";
                $stmt->Execute($query);

                // Delete user
                $query = "UPDATE user_info SET archive='Y' WHERE user_id IN (" . implode(
                    ",",
                    Connection::GetSQLArray($removedIDs)
                ) . ")";
                $stmt->Execute($query);

                //Save history
                $stmt1 = GetStatement(DB_CONTROL);
                $query = "INSERT INTO user_history (end_user_id, property_name, value, created, start_user_id, created_from) VALUES" . implode(
                    ",",
                    $values
                );
                $stmt1->Execute($query);
                $query = "INSERT INTO employee_history (employee_id, property_name, value, created, user_id, created_from) VALUES" . implode(
                    ",",
                    $employeeValues
                );
                $stmt1->Execute($query);

                $key = $count > 1 ? "users-are-disactivated" : "user-is-disactivated";

                $this->AddMessage(
                    $key,
                    array("UserList" => "\"" . implode("\", \"", $removed) . "\"", "UserCount" => $count)
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Revert the operation of remove/inactivate users.
     * Also do it with concerned employees.
     *
     * @param array $ids array of user_id's
     *
     * @return int activated users count
     */
    function Activate($request)
    {
        $ids = $request->GetProperty("UserIDs");
        if (!is_array($ids) || count($ids) <= 0) {
            return 0;
        }

        $where = array();

        $where[] = "user_id IN (" . implode(",", Connection::GetSQLArray($ids)) . ")";

        if ($request->GetIntProperty("CurrentUserID") > 0) {
            $where[] = "user_id<>" . $request->GetIntProperty("CurrentUserID");
        }

        $stmt = GetStatement(DB_PERSONAL);

        $processed = array();
        $processIDs = array();

        $query = $this->GetQueryPrefix() . " WHERE " . implode(" AND ", $where);
        $values = array();
        $employeeValues = array();
        $employee = new Employee("company");
        if ($result = $stmt->FetchList($query)) {
            for ($i = 0; $i < count($result); $i++) {
                $processed[] = $result[$i]['first_name'] . " " . $result[$i]['last_name'];
                $processIDs[] = $result[$i]['user_id'];
                $values[] = "(" . $result[$i]['user_id'] . ",'archive','N','" . GetCurrentDateTime() . "'," . $request->GetIntProperty("CurrentUserID") . ")";

                $employee->LoadByUserID($result[$i]['user_id']);
                $employeeValues[] = "(" . $employee->GetIntProperty("employee_id") . ",'archive','N','" . GetCurrentDateTime() . "'," . $request->GetIntProperty("CurrentUserID") . ")";
            }
        }

        $count = count($processed);

        if ($count <= 0) {
            return 0;
        }

        // Update employees
        $query = "UPDATE employee SET archive='N' WHERE user_id IN (" . implode(
            ",",
            Connection::GetSQLArray($processIDs)
        ) . ")";
        $stmt->Execute($query);

        // Delete user
        $query = "UPDATE user_info SET archive='N' WHERE user_id IN (" . implode(
            ",",
            Connection::GetSQLArray($processIDs)
        ) . ")";
        $stmt->Execute($query);

        //Save history
        $stmt1 = GetStatement(DB_CONTROL);
        $query = "INSERT INTO user_history (end_user_id, property_name, value, created, start_user_id) VALUES" . implode(
            ",",
            $values
        );
        $stmt1->Execute($query);
        $query = "INSERT INTO employee_history (employee_id, property_name, value, created, user_id) VALUES" . implode(
            ",",
            $employeeValues
        );
        $stmt1->Execute($query);

        $key = $count > 1 ? "users-are-activated" : "user-is-activated";

        $this->AddMessage(
            $key,
            array("UserList" => "\"" . implode("\", \"", $processed) . "\"", "UserCount" => $count)
        );

        return $count;
    }

    /**
     * Gets users with permissions
     *
     * @param array $permissions - array of permission names
     *
     * @return array|bool|null
     */
    public function GetUserListByPermissions($permissions) {
        $stmt = GetStatement(DB_PERSONAL);

        $query = $this->GetQueryPrefix() . "
            LEFT JOIN user_permissions AS up ON i.user_id=up.user_id 
            LEFT JOIN permission AS p ON up.permission_id=p.permission_id 
            WHERE p.name IN ('" . implode("', '", $permissions) . "') AND i.archive='N'
            GROUP BY i.user_id
            ORDER BY " . Connection::GetSQLDecryption("first_name") . " ASC, 
            " . Connection::GetSQLDecryption("last_name") . " ASC";

        return $stmt->FetchList($query);
    }
}
