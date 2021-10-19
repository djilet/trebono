<?php

class VoucherExportList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function VoucherExportList($module, $data = array())
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
     * Loads voucher export list
     *
     * @param LocalObject $request object of parameters data
     * @param bool $fullList load the full list or use pagination
     */
    public function LoadVoucherExportList($request, $fullList = false)
    {
        if ($fullList) {
            $this->SetItemsOnPage(0);
        } elseif ($request->IsPropertySet("ItemsOnPageVoucher")) {
            $this->SetItemsOnPage($request->GetIntProperty("ItemsOnPageVoucher"));
        }

        $where = array();

        if ($request->GetProperty("FilterCreatedRange")) {
            [$from, $to] = explode(" - ", $request->GetProperty("FilterCreatedRange"));
            $where[] = "created >= " . Connection::GetSQLDateTime($from);
            $where[] = "created <= " . Connection::GetSQLDateTime($to);
        }

        $where[] = "export_month IS NOT NULL";

        $query = "SELECT * FROM voucher_export_datev
                    " . (count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "");

        if ($request->IsPropertySet("PageVoucher")) {
            $this->SetCurrentPage($request->GetProperty("PageVoucher"));
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
            $period = date_create_from_format("Ym", $this->_items[$i]['export_month']);
            if (!$period) {
                $period = date_create("1970-01-01");
            }
            $this->_items[$i]['Period'] = GetTranslation("date-" . $period->format("F")) . " " . $period->format("Y");

            $this->_items[$i]['created_user_name'] = User::GetNameByID($this->_items[$i]['user_id']);
        }
    }
}
