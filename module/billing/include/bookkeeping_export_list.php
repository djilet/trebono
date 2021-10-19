<?php

class BookkeepingExportList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function BookkeepingExportList($module, $data = array())
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
     * Loads bookkeeping export list
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadBookkeepingExportList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $where = array();
        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "e.created >= " . Connection::GetSQLDateTime($from);
            $where[] = "e.created <= " . Connection::GetSQLDateTime($to);
        }
        if ($request->GetProperty("FilterTitle")) {
            $where[] = Connection::GetSQLDecryption("c.title") . " ~* " . Connection::GetSQLSearchRegexp($request->GetProperty("FilterTitle"));
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
            if ($permission['name'] != "bookkeeping_export" && $permission['name'] != "tax_auditor") {
                continue;
            }

            $permissionLinks[] = $permission['link_id'];
        }

        $permissionLinks = array_filter($permissionLinks);
        if ($permissionLinks) {
            $where[] = "c.company_unit_id IN (" . implode(",", $permissionLinks) . ")";
        }

        $query = "SELECT e.*, " . Connection::GetSQLDecryption("c.title") . " AS title
                  	FROM bookkeeping_export e
                  	LEFT JOIN company_unit c ON e.company_unit_id=c.company_unit_id
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
        $usernameList = [];
        foreach ($this->GetItems() as $key => $item) {
            if (!isset($usernameList[$item["user_id"]])) {
                $usernameList[$item["user_id"]] = User::GetNameByID($item["user_id"]);
            }
            $this->_items[$key]["created_by"] = $usernameList[$item["user_id"]];
        }
    }

    /**
     * Reset export
     *
     * @param array $ids array of bookkeeping_export_id's
     * @param string $createdFrom
     */
    public function Remove($ids, $createdFrom = "admin")
    {
        if (!is_array($ids) || count($ids) <= 0) {
            return;
        }

        $stmt = GetStatement();

        $query = "UPDATE bookkeeping_export SET archive='Y' WHERE bookkeeping_export_id IN (" . implode(
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
        $stmtControl = GetStatement(DB_CONTROL);
        $query = "INSERT INTO bookkeeping_export_history (bookkeeping_export_id, property_name, value, user_id, created, created_from) VALUES" . implode(
            ",",
            $values
        );
        $stmtControl->Execute($query);

        $query = "SELECT receipt_id FROM receipt WHERE bookkeeping_export_id IN (" . implode(
            ", ",
            Connection::GetSQLArray($ids)
        ) . ")";
        $receiptIDs = $stmt->FetchList($query);

        if ($receiptIDs !== false && !empty($receiptIDs)) {
            $receiptIDs = array_column($receiptIDs, "receipt_id");
            $query = "UPDATE receipt SET bookkeeping_export_id='0' WHERE receipt_id IN (" . implode(
                ", ",
                Connection::GetSQLArray($receiptIDs)
            ) . ")";
            $stmt->Execute($query);
        }

        if ($stmtControl->GetAffectedRows() <= 0 || $receiptIDs === false) {
            return;
        }

        $this->AddMessage("object-disactivated", $this->module, array("Count" => $stmt->GetAffectedRows()));
    }
}
