<?php

class ConfirmationEmployee extends LocalObject
{
    private $module;

    /**
     * AgreementEmployee constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }


    public function LoadByID($confirmationID)
    {
        $query = "SELECT *
            FROM recreation_confirmation_employee
            WHERE id=" . Connection::GetSQLString($confirmationID);

        $this->LoadFromSQL($query);
    }

    public function LoadByReceiptID($receiptID)
    {
        $query = "SELECT *
            FROM recreation_confirmation_employee
            WHERE receipt_id =" . Connection::GetSQLString($receiptID)
            . " ORDER BY id DESC LIMIT 1";

        $this->LoadFromSQL($query);
    }

    public function Remove()
    {
        if (!$this->ValidateNotEmpty("id")) {
            return false;
        }

        $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("pdf_file"));

        $stmt = GetStatement();
        $query = "DELETE FROM recreation_confirmation_employee WHERE id=" . $this->GetPropertyForSQL("id");

        return $stmt->execute($query);
    }

    public function GetPdfLink(int $companyUnitId): ?string
    {
        if (!$this->IsPropertySet("id")) {
            return null;
        }

        return AdminUrl("module.php", [
            "load" => "agreements",
            "Section" => "confirmation",
            "Action" => "GetConfirmationPDF",
            "ConfirmationID" => $this->GetIntProperty("id"),
            "CompanyUnitID" => $companyUnitId,
        ]);
    }
}
