<?php

class BillableItemList extends LocalObjectList
{

    private $module;

    /**
     * Constructor
     *
     */
    public function BillableList()
    {
    }



    /**
     * Load company billable list
     *
     * @param int $companyId company's id
     * 
     * @param int current page
     * 
     */

    public function getCompanyBillableList($companyId, $currentPage)
    {
        $notesOnPage = 10;

        $stmt = GetStatement(DB_MAIN);
            $this->SetItemsOnPage($notesOnPage);
            $query0 = $query = "SELECT * FROM billable_item WHERE company_unit_id= " . Connection::GetSQLString($companyId);
                $this->LoadFromSQL($query0);
                $this->SetCurrentPage($currentPage);
            $start = ($currentPage - 1) * $notesOnPage;
            if ($currentPage)
            $query = "SELECT * FROM billable_item WHERE company_unit_id= " . Connection::GetSQLString($companyId)
                                . " ORDER BY created LIMIT $notesOnPage OFFSET $start";

                $result = $stmt->FetchList($query);
            
        return $this->prepareCompanyBillableListBeforShow($result, $currentPage);
    }

    /**
     * Load company unit billable items for invoice
     *
     * @param int $companyUnitId company_unit_id of company unit invoice is creating for
     * @param string $dateTo end of invoice period
     * @param bool $forPreview items for invoice preview
     *
     * @return bool loading result
     */
    public function loadBillableItemsForInvoice($companyUnitId, $dateTo, $forPreview = false)
    {
        if (empty($companyUnitId) || empty($dateTo)) {
            return false;
        }

        $whereInvoice = "invoice_id IS NULL";

        if ($forPreview) {
            $invoice = new Invoice("billing");
            $invoiceIdsForCurrentPeriod = $invoice->GetInvoiceIdsByDate($companyUnitId, $dateTo);
            if (count($invoiceIdsForCurrentPeriod) > 0) {
                $whereInvoice = "(invoice_id IS NULL OR invoice_id IN (" . implode(",", $invoiceIdsForCurrentPeriod) . "))";
            }
        }

        $query = "SELECT * FROM billable_item 
            WHERE company_unit_id = " . intval($companyUnitId) . " AND 
            date_start <= " . Connection::GetSQLString($dateTo) . " AND 
            " . $whereInvoice . " AND 
            archive = 'N'";

        $this->LoadFromSQL($query);

        foreach ($this->_items as $key => $item) {
            $this->_items[$key]["product_id"] = 0;
            $this->_items[$key]["type"] = INVOICE_LINE_TYPE_BILL;
            $this->_items[$key]["cost"] = $this->_items[$key]["price"] * $this->_items[$key]["quantity"] *
                ((100 - $this->_items[$key]["discount"]) / 100);
            $this->_items[$key]["billable_item_id"] = $this->_items[$key]["item_id"];
            $this->_items[$key]["sort_order"] = 0;
        }
    }

    /**
     * Set invoice id for loaded billable items
     *
     * @param int $invoiceId invoice_id
     *
     * @return bool export result
     */
    public function exportToInvoice($invoiceId)
    {
        if (empty($invoiceId)) {
            return false;
        }

        if ($this->GetCountItems() > 0) {
            $billableItemsIds = array_column($this->GetItems(), "item_id");
            $stmt = GetStatement();
            $query = "UPDATE billable_item SET invoice_id = " . intval($invoiceId) . "
                WHERE item_id IN (" . implode(",", $billableItemsIds) . ")";

            return $stmt->Execute($query);
        }

        return true;
    }

    /**
     * Remove items from invoice billable
     *
     * @param int $invoiceId invoice_id
     *
     * @return array|bool billable items
     */
    public static function removeFromInvoice($invoiceId)
    {
        if (empty($invoiceId)) {
            return false;
        }

        $stmt = GetStatement();
        $query = "UPDATE billable_item SET invoice_id = NULL
            WHERE invoice_id =" . intval($invoiceId);

        return $stmt->Execute($query);
    }

    /**
     * prepare company billable list before show
     *
     * @param array billable list from db
     * 
     * @param int current page
     * 
     */
    private function prepareCompanyBillableListBeforShow($result, $currentPage)
    {
        foreach ($result as $key => &$value) {
            $value['total'] = GetPriceFormat($value['price'] * $value['quantity'] * (1 - ($value['discount'] / 100) ) );
            $result[$key]['created_user_name'] = User::GetNameByID($value['created_user']);

            $value['date_start'] = implode(".", array_reverse(explode("-",($value['date_start']) ) ) );
            $value['date_end'] = implode(".", array_reverse(explode("-",($value['date_end']) ) ) );

            $createdDate = explode(" ", $value['created']);
                $createdDate[0] = implode(".", array_reverse(explode("-",($createdDate[0]) ) ) );
                $createdDate[1] = implode(":", array_reverse(explode(":",(substr($createdDate[1], 0 , -10)) ) ) );
                $createdDate = implode(" ", $createdDate);
            $value['created'] = $createdDate;
            $value['discount'] = GetPriceFormat($value['discount']);
            $value['price'] = GetPriceFormat($value['price']);
            $value['currentPage'] = $currentPage;
        }

        return $result;
    }

}