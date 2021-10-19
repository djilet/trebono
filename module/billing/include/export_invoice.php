<?php

class ExportInvoice extends LocalObject
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of master data properties to be loaded instantly
     */
    public function ExportInvoice($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new export invoice record in database. Object must be loaded before the method will be called.
     *
     * @return bool export_id of new record on success or false on sql-failure
     */
    public function Create()
    {
        $user = new User();
        $user->LoadBySession();

        $stmt = GetStatement();
        $query = "INSERT INTO invoice_export_datev (user_id, created, export_number, date_from, date_to, type) VALUES (
						" . $user->GetIntProperty("user_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ", 
						(	
							SELECT COALESCE(MAX(export_number), 0) + 1 
							FROM invoice_export_datev 
							WHERE EXTRACT(YEAR FROM created) =" . Connection::GetSQLString(date("Y")) . "
						),
						" . Connection::GetSQLDate($this->GetProperty("date_from")) . ",
						" . Connection::GetSQLDate($this->GetProperty("date_to")) . ",
						" . $this->GetPropertyForSQL("type") . "
					)
					RETURNING export_id";

        if ($stmt->Execute($query)) {
            return $stmt->GetLastInsertID();
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads export invoice by export_invoice_id
     *
     * @param int $exportInvoiceID export_invoice_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($exportInvoiceID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT *
					FROM invoice_export_datev
					WHERE export_id=" . intval($exportInvoiceID);

        if (!$this->LoadFromSQL($query, $stmt)) {
            return false;
        }

        return true;
    }

    /**
     * Puts datev invoice export of given date into archive
     *
     * @param string $created date of export creation
     * @param string $type type of invoice, "invoice" for regular and "voucher_invoice" for voucher invoices
     *
     * @return bool
     */
    public function DeactivateExportDatev($created, $type = "invoice")
    {
        $exportCreated = date_create($created)->format("Y-m-d");

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT export_id FROM invoice_export_datev 
					WHERE DATE(created)=" . Connection::GetSQLString($exportCreated) . "
					    AND type=" . Connection::GetSQLString($type) . " AND archive != 'Y' ORDER BY export_id DESC";
        $exportID = $stmt->FetchField($query);
        $this->LoadByID($exportID);

        if (intval($exportID) > 0) {
            $query = "SELECT invoice_id FROM invoice WHERE export_id=" . Connection::GetSQLString($exportID);
            $invoiceList = array_keys($stmt->FetchIndexedList($query));
            if (count($invoiceList) > 0) {
                $query = "UPDATE invoice SET export_id=NULL WHERE invoice_id IN (" . implode(
                    ", ",
                    Connection::GetSQLArray($invoiceList)
                ) . ")";
                $stmt->Execute($query);
            }

            $query = "UPDATE invoice_export_datev SET archive='Y' WHERE export_id=" . Connection::GetSQLString($exportID);

            return $stmt->Execute($query);
        }

        return false;
    }
}
