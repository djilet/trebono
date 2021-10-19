<?php

class GivveVoucherList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function GivveVoucherList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "id_asc" => "v.voucher_id ASC",
            "id_desc" => "v.voucher_id DESC",
        ));
        $this->SetOrderBy("id_asc");
        //$this->SetItemsOnPage(20);
    }

    /**
     * Loads voucher list using filter params
     *
     * @param int $employeeID employee_id
     */
    public function LoadVoucherList($employeeID)
    {
        $where = array();

        $where[] = "v.employee_id=" . $employeeID;

        $query = "SELECT voucher_id, employee_id, balance, updated, address_line_1, address_line_2 
					FROM givve_voucher v"
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
    }
}
