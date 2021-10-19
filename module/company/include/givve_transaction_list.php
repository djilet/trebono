<?php

class GivveTransactionList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function GivveTransactionList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "date_asc" => "t.booked ASC",
            "date_desc" => "t.booked DESC",
        ));
        $this->SetOrderBy("date_asc");
        //$this->SetItemsOnPage(20);
    }

    /**
     * Loads transaction list using filter params
     *
     * @param string $voucherID voucher_id
     */
    public function LoadTransactionList($voucherID)
    {
        $where = array();

        $where[] = "t.voucher_id='" . $voucherID . "'";

        $query = "SELECT t.transaction_id, t.voucher_id, t.description, t.booked, t.amount 
					FROM givve_voucher_transaction t"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
    }
}
