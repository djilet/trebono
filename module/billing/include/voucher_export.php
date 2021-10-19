<?php

class VoucherExport extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of master data properties to be loaded instantly
     */
    public function VoucherExport($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new voucher export record in database. Object must be loaded before the method will be called.
     *
     * @param string $exportDate
     *
     * @return bool export_id of new record on success or false on sql-failure
     */
    public function Create($exportDate)
    {
        $user = new User();
        $user->LoadBySession();

        $exportMonthYear = date_create($exportDate)->format("Ym");

        $stmt = GetStatement();
        $query = "INSERT INTO voucher_export_datev (user_id, created, export_number, export_month) VALUES (
						" . $user->GetIntProperty("user_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ", 
						(	
							SELECT COALESCE(MAX(export_number), 0) + 1 
							FROM voucher_export_datev 
							WHERE EXTRACT(YEAR FROM created) =" . Connection::GetSQLString(date("Y")) . "
						),
						" . $exportMonthYear . "
					)
					RETURNING export_id";

        if ($stmt->Execute($query)) {
            return $stmt->GetLastInsertID();
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads voucher export its id
     *
     * @param int $exportID export export_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($exportID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT *
					FROM voucher_export_datev
					WHERE export_id=" . intval($exportID);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("export_id") ? true : false;
    }

    /**
     * Loads voucher export its id
     *
     * @param int $exportID export export_id
     * @param string $property
     *
     * @return bool true if loaded successfully or false on failure
     */
    public static function GetPropertyByID($exportID, $property)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT " . $property . "
					FROM voucher_export_datev
					WHERE export_id=" . intval($exportID);

        return $stmt->FetchField($query);
    }

    /**
     * Resets employee payment export on given date
     *
     * @param string $exportDate
     *
     * @return bool
     */
    public function ResetExport($exportDate)
    {
        $exportMonthYear = date_create($exportDate)->format("Ym");

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT export_id FROM voucher_export_datev 
					WHERE export_month=" . Connection::GetSQLString($exportMonthYear) . " ORDER BY export_id DESC";
        $exportID = $stmt->FetchField($query);
        $this->LoadByID($exportID);

        if (intval($exportID) > 0) {
            $query = "SELECT receipt_id FROM receipt WHERE creditor_export_id=" . Connection::GetSQLString($exportID);
            $receiptList = array_keys($stmt->FetchIndexedList($query));
            if (count($receiptList) > 0) {
                $query = "UPDATE receipt SET creditor_export_id=NULL WHERE receipt_id IN (" . implode(
                    ", ",
                    Connection::GetSQLArray($receiptList)
                ) . ")";
                $stmt->Execute($query);
            }

            $filename = "extf_buchungsstapel_creditor_" . date_create($this->GetProperty("created"))->format("Ymd") . "_" . $this->GetProperty("export_number") . ".csv";
            $fileStorage = GetFileStorage(CONTAINER__BILLING__VOUCHER_EXPORT);
            $fileStorage->Remove(VOUCHER_EXPORT_DIR . $filename);

            $query = "DELETE FROM voucher_export_datev WHERE export_id=" . Connection::GetSQLString($exportID);

            return $stmt->Execute($query);
        }

        return false;
    }
}
