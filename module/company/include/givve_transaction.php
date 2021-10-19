<?php

class GivveTransaction extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of contact properties to be loaded instantly
     */
    public function GivveTransaction($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Loads transaction by its transaction_id
     *
     * @param string $id transaction_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $query = "SELECT transaction_id, voucher_id, description, booked, amount
					FROM givve_voucher_transaction 
					WHERE transaction_id='" . $id . "'";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        return $this->GetProperty("transaction_id") ? true : false;
    }

    /**
     * Loads transaction by data obtained during import
     *
     * @param $id string transaction_id
     * @param $voucherID string voucher_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByImportData($id, $voucherID)
    {
        $query = "SELECT transaction_id
					FROM givve_voucher_transaction
					WHERE transaction_id='" . $id . "' AND voucher_id='" . $voucherID . "'";
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));

        return $this->GetProperty("transaction_id") ? true : false;
    }

    /**
     * Creates the transaction using data obtained during import
     *
     * @return bool true if transaction is created successfully or false on failure
     */
    public function SaveFromImportData($data)
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "INSERT INTO givve_voucher_transaction (transaction_id, voucher_id, description, booked, amount) 
                        VALUES (
						'" . $data['id'] . "', 						
						'" . $data['voucher_id'] . "', 						
						'" . $data['description'] . "',
						'" . $data['booked_at'] . "',
						'" . $data['amount']['cents'] . "')";
        if ($stmt->Execute($query)) {
            return true;
        }

        $this->AddError("sql-error");

        return false;
    }
}
