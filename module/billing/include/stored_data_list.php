<?php

class StoredDataList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function StoredDataList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "date_asc" => "created ASC",
            "date_desc" => "created DESC"
        ));
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Loads stored data list
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadStoredDataList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();
        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "s.created >= " . Connection::GetSQLDateTime($from);
            $where[] = "s.created <= " . Connection::GetSQLDateTime($to);
        }
        if ($request->GetProperty("FilterCompanyUnitId")) {
            $where[] = "c.company_unit_id = " . $request->GetIntProperty("FilterCompanyUnitId");
        }

        //Permission filter
        $user = new User();
        $user->LoadBySession();
        $permissionList = $user->GetProperty("PermissionList");
        $permissionLinks = array();
        foreach ($permissionList as $permission) {
            if ($permission['name'] == "root") {
                $permissionLinks = array();
                break;
            }
            if ($permission['name'] != "stored_data") {
                continue;
            }

            $permissionLinks[] = $permission['link_id'];
        }

        if ($permissionLinks) {
            $where[] = "c.company_unit_id IN (" . implode(",", $permissionLinks) . ")";
        }

        $query = "SELECT s.stored_data_id, s.company_unit_id, s.created, s.date_from, s.date_to, s.status, s.employees, s.cron,
                      " . Connection::GetSQLDecryption("c.title") . " AS title, s.archive
                  	FROM stored_data s
						LEFT JOIN company_unit c ON s.company_unit_id=c.company_unit_id
                  	" . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "");

        $this->SetCurrentPage();

        $this->LoadFromSQL($query);

        $this->PrepareContentBeforeShow();
    }


    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["status_title"] = GetTranslation(
                "stored-data-status-" . $this->_items[$i]["status"],
                $this->module
            );
            $this->_items[$i]["employees_title"] = $this->_items[$i]["employees"] == "all"
                ? GetTranslation("all", $this->module)
                : Employee::GetNameByID($this->_items[$i]["employees"]);
        }
    }

    /**
     * Make stored data inactive.
     *
     * @param array $ids array of stored_data_id's
     * @param string $createdFrom
     */
    public function Remove($ids, $createdFrom = "admin")
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE stored_data SET archive='Y' WHERE stored_data_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        //Save history
        $user = new User();
        $user->LoadBySession();
        $userId = $user->GetProperty("user_id");

        $values = array();
        for ($i = 0; $i < count($ids); $i++) {
            $values[] = "(" . $ids[$i] . ",'archive','Y'," . $userId . ",'" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";
        }
        $stmt1 = GetStatement(DB_CONTROL);
        $query = "INSERT INTO stored_data_history (stored_data_id, property_name, value, user_id, created, created_from) VALUES" . implode(
            ",",
            $values
        );
        $stmt1->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }

    /**
     * Revert the operation of Remove stored data by provided ids.
     *
     * @param array $ids array of stored_data's_id's
     * @param string $createdFrom
     */
    public function Activate($ids, $createdFrom = "admin")
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE stored_data SET archive='N' WHERE stored_data_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $stmt->Execute($query);

        if ($stmt->GetAffectedRows() <= 0) {
            return;
        }

        //Save history
        $user = new User();
        $user->LoadBySession();
        $userId = $user->GetIntProperty("user_id");

        $values = array();
        for ($i = 0; $i < count($ids); $i++) {
            $values[] = "(" . $ids[$i] . ",'archive','N'," . $userId . ",'" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";
        }

        $stmtControl = GetStatement(DB_CONTROL);
        $query = "INSERT INTO stored_data_history (stored_data_id, property_name, value, user_id, created, created_from) VALUES" . implode(
            ",",
            $values
        );
        $stmtControl->Execute($query);

        $this->AddMessage("object-activated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }
}
