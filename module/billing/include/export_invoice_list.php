<?php

class ExportInvoiceList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ExportInvoiceList($module, $data = array())
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
     * Loads export invoice list
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadExportInvoiceList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPageExportInvoice")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPageExportInvoice"));
        }

        $where = array();

        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "created >= " . Connection::GetSQLDateTime($from);
            $where[] = "created <= " . Connection::GetSQLDateTime($to);
        }

        $where[] = "type IS NOT NULL";

        $query = "SELECT * FROM invoice_export_datev
                    " . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "");

        if ($request->IsPropertySet("PageExportInvoice")) {
            $this->SetCurrentPage($request->GetProperty("PageExportInvoice"));
        } else {
            $this->SetCurrentPage();
        }

        $this->LoadFromSQL($query);
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that cannot be loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["created_user_name"] = User::GetNameByID($this->_items[$i]["user_id"]);
        }
    }
}
