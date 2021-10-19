<?php

class RecreationConfirmationList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function RecreationConfirmationList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields([
            "date_asc" => "created ASC",
            "date_desc" => "created DESC",
        ]);
        $this->SetOrderBy("date_desc");
        $this->SetItemsOnPage(10);
    }

    /**
     * Load list of all confirmations
     *
     * @param LocalObject $request object of parameters data
     */
    public function LoadAll($request)
    {
        if ($request->IsPropertySet("ItemsOnPage")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPage"));
        }

        $companyUnitID = $request->GetIntProperty("CompanyUnitID");
        if ($companyUnitID <= 0) {
            $this->_items = [];

            return;
        }

        $query = "SELECT rce.* FROM recreation_confirmation_employee AS rce
                    LEFT JOIN recreation_confirmation AS rc ON rc.confirmation_id=rce.confirmation_id 
                WHERE rc.company_unit_id=" . Connection::GetSQLString($companyUnitID);
        $this->SetCurrentPage();
        $this->LoadFromSQL($query);
        $this->PrepareBeforeShow();
    }

    protected function PrepareBeforeShow()
    {
        foreach ($this->_items as $key => $item) {
            $this->_items[$key]["employee_name"] = Employee::GetNameByID($item["employee_id"]);
            $this->_items[$key]["legal_receipt_id"] = Receipt::GetReceiptLegalID($item["receipt_id"]);
        }
    }

    /**
     * Get confirmations by employee ID
     *
     * @param int $id Employee id
     */
    public function LoadByEmployeeID($id)
    {
        $query = "SELECT * FROM recreation_confirmation_employee WHERE employee_id=" . intval($id);
        $this->SetOrderBy("date_asc");
        $this->LoadFromSQL($query);
    }

    /**
     * Get confirmation list for generate stored data
     *
     * @param string $dateFrom beginning of period stored data
     * @param string $dateTo end of period stored data
     * @param array $employees employees IDs
     *
     * @return array|false|null confirmation list
     */
    public static function GetConfirmationListForStoredData($dateFrom, $dateTo, $employees)
    {
        $where = [];
        $where[] = "DATE(created) >= " . Connection::GetSQLDate($dateFrom);
        $where[] = "DATE(created) <= " . Connection::GetSQLDate($dateTo);
        $where[] = "employee_id IN(" . implode(",", $employees) . ")";

        $stmt = GetStatement();
        $query = "SELECT employee_id, pdf_file FROM recreation_confirmation_employee WHERE " . implode(" AND ", $where);

        return $stmt->FetchList($query);
    }
}
