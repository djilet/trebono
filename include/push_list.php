<?php

class PushList extends LocalObjectList
{
    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    function PushList($data = [])
    {
        parent::LocalObjectList($data);

        $this->SetSortOrderFields([
            "date_asc" => "created ASC",
            "date_desc" => "created DESC",
        ]);
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(20);
    }

    /**
     * Loads operation list using filter params
     *
     * @param LocalObject $request . Can contain following properties:
     *        <ul>
     *        <li><u>ItemsOnPage</u> - int - size of page when paging is used</li>
     *      <li><u>FilterDateRange</u> - string - property for "date" field filtration. format is "{any_date_format_from} - {any_date_format_to}"</li>
     *        <li><u>FilterUser</u> - string - property for user name filtration</li>
     *      <li><u>FilterSection</u> - string - property for section name filtration</li>
     *        </ul>
     * @param bool $fullList If is set to true, then all objects will be loaded at once without paging
     */
    public function LoadPushList($request)
    {
        $where = [];
        //process filter params
        if ($request->GetProperty("FilterDateRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterDateRange"));
            $where[] = "created >= " . Connection::GetSQLDateTime($from);
            $where[] = "created <= " . Connection::GetSQLDateTime($to);
        }

        $userList = [];
        $stmtPersonal = GetStatement(DB_PERSONAL);
        if ($request->GetProperty("FilterName")) {
            $requestWhere = [];
            $requestWhere[] = "CONCAT(" . Connection::GetSQLDecryption("first_name")
                . ", ' ', " . Connection::GetSQLDecryption("last_name") . ")
                     ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterName"));
            $query = "SELECT user_id FROM user_info "
                . (!empty($requestWhere) ? " WHERE " . implode(" AND ", $requestWhere) : "");
            $result = $stmtPersonal->FetchList($query);
            $userList = array_column($result, "user_id");
        }
        if ($request->GetProperty("FilterCompanyUnitTitle")) {
            $requestWhere = [];
            $companyUnitID = CompanyUnit::GetIDByTitle(
                $request->GetProperty("FilterCompanyUnitTitle")
            );
            if ($companyUnitID === false || $companyUnitID === null) {
                $stmt = GetStatement();
                $query = "SELECT u.company_unit_id FROM company_unit AS u
                    JOIN company_unit AS c ON c.company_id=u.company_id  
                    WHERE " . Connection::GetSQLDecryption("c.title") . "
                    ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterCompanyUnitTitle"));
                $companyUnitIDs = array_keys($stmt->FetchIndexedList($query));
                if (!empty($companyUnitIDs)) {
                    $requestWhere[] = "company_unit_id IN("
                        . implode(", ", $companyUnitIDs) . ")";
                }
            } else {
                $requestWhere[] = "company_unit_id =" . Connection::GetSQLString($companyUnitID);
            }
            $query = "SELECT user_id FROM employee "
                . (!empty($requestWhere) ? " WHERE " . implode(" AND ", $requestWhere) : "");
            $result = $stmtPersonal->FetchList($query);
            $userList = empty($userList)
                ? array_column($result, "user_id")
                : array_intersect($userList, array_column($result, "user_id"));
        }

        if (!empty($userList)) {
            $where[] = "user_id IN(" . implode(", ", $userList) . ")";
        } elseif ($request->GetProperty("FilterName") || $request->GetProperty("FilterCompanyUnitTitle")) {
            return;
        }

        $query = "SELECT push_id, user_id, device_id, is_sended, text, created, error_message
                    FROM push_history"
                . (!empty($where) ? " WHERE " . implode(" AND ", $where) : "");
        $this->SetCurrentPage();
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));
        $this->PrepareContentBeforeShow();
    }

    private function PrepareContentBeforeShow()
    {
        $usernameList = [];
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $device = Device::GetLastVersionByDeviceIDBeforeDate(
                $this->_items[$i]["device_id"],
                $this->_items[$i]["user_id"],
                $this->_items[$i]["created"]
            );

            if ($device) {
                $this->_items[$i]["client"] = $device["client"];
                $this->_items[$i]["version"] = $device["version"];
            }

            if (!isset($usernameList[$this->_items[$i]["user_id"]])) {
                $usernameList[$this->_items[$i]["user_id"]] = User::GetNameByID($this->_items[$i]["user_id"]);
            }
            $this->_items[$i]["user_name"] = $usernameList[$this->_items[$i]["user_id"]];
        }
    }
}
