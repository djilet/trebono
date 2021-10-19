<?php

class GivveVoucher extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contact properties to be loaded instantly
     */
    public function GivveVoucher($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads voucher by its voucher_id
     *
     * @param string $id voucher_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT voucher_id, employee_id, balance, updated, address_line_1, address_line_2
					FROM givve_voucher 
					WHERE voucher_id='" . $id . "'";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        return $this->GetProperty("voucher_id") ? true : false;
    }

    /**
     * Loads voucher by data obtained during import
     *
     * @param $id string voucher_id
     * @param $employeeID int employee_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByImportData($id, $employeeID)
    {
        $query = "SELECT voucher_id
					FROM givve_voucher
					WHERE voucher_id='" . $id . "' AND employee_id=" . intval($employeeID);
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        return $this->GetProperty("voucher_id") ? true : false;
    }

    /**
     * Creates the transaction using data obtained during import
     *
     * @return bool true if transaction is created successfully or false on failure
     */
    public function SaveFromImportData($data)
    {

        $updated = str_replace("T", " ", $data["updated_at"]);
        $updated = strstr($updated, '.', true);

        $stmt = GetStatement(DB_PERSONAL);
        $query = "INSERT INTO givve_voucher (voucher_id, employee_id, balance, updated, address_line_1, address_line_2) 
                        VALUES (
						'" . $data['id'] . "', 						
						'" . $data['employee_id'] . "', 						
						'" . $data['balance']['cents'] . "',
						'" . $updated . "',
						'" . $data['owner']['address_line_1'] . "',
						'" . $data['owner']['address_line_2'] . "')";

        if ($stmt->Execute($query)) {
            return true;
        }

        $this->AddError("sql-error");

        return false;
    }
}
