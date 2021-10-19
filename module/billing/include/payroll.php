<?php

class Payroll extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of payroll properties to be loaded instantly
     */
    public function Payroll($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new payroll record in database
     *
     * @param int $companyUnitID
     * @param string $payrollDate
     *
     * @return int|bool payroll_id of new record on success or false on sql-failure
     */
    public function Create($companyUnitID, $payrollDate)
    {
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($companyUnitID);

        $date = date_create($payrollDate);
        $day = $date->format("j");
        $dayTomorrow = $date->modify("+ 1 day")->format("j");
        if ($dayTomorrow == 1) {
            $allowableFinancialDates = range($day, 31);
        } else {
            $allowableFinancialDates = [$day];
        }
        if (!in_array($companyUnit->GetIntProperty("financial_statement_date"), $allowableFinancialDates)) {
            return false;
        }

        $payrollMonthYear = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "current_month"
            ? date_create($payrollDate)->format("Ym")
            : date_create($payrollDate)->modify("first day of this month")
                ->modify("last month")->format("Ym");

        $stmt = GetStatement();
        $query = "INSERT INTO payroll (company_unit_id, payroll_month, created, status) 
						VALUES(" . $companyUnitID . "," . $payrollMonthYear . "," . Connection::GetSQLString(GetCurrentDateTime()) . ", 'new')
					RETURNING payroll_id";
        if ($stmt->Execute($query)) {
            return $stmt->GetLastInsertID();
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads payroll by its payroll_id
     *
     * @param int $payrollID payroll_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($payrollID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT p.payroll_id, p.company_unit_id, p.payroll_month, p.created, p.pdf_file, p.lodas_file, p.lug_file, p.logga_file, p.topas_file, p.perforce_file, p.addison_file, p.lexware_file, p.sage_file,
						" . Connection::GetSQLDecryption("c.title") . " AS title
					FROM payroll AS p 
						LEFT JOIN company_unit AS c ON p.company_unit_id=c.company_unit_id  
					WHERE p.payroll_id=" . intval($payrollID);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("payroll_id") ? true : false;
    }

    /**
     * Change Payroll record in DB
     *
     * @param string $field Name of param to change
     * @param mixed $value New value of param
     */
    public function UpdateField($field, $value)
    {
        $availableFields = array(
            "status",
            "pdf_file",
            "lodas_file",
            "lug_file",
            "logga_file",
            "topas_file",
            "perforce_file",
            "addison_file",
            "lexware_file",
            "sage_file"
        );
        if (!in_array($field, $availableFields)) {
            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE payroll SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE payroll_id=" . $this->GetIntProperty("payroll_id");

        return $stmt->Execute($query);
    }

    /**
     * Sends email with link to payroll pdf to its company_unit contact for payroll.
     */
    public function Send()
    {
        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT salutation, first_name, last_name, email, contact_id FROM contact WHERE contact_for_payroll_export='Y' AND company_unit_id=" . $this->GetIntProperty("company_unit_id");
        $contactList = $stmt->FetchList($query);

        if ($contactList === false) {
            $this->AddError("sql-error");

            return false;
        }

        $monthYear = date_create_from_format("Ym", $this->GetProperty("payroll_month"));
        $monthYear = GetGermanMonthName($monthYear->format("n")) . " " . $monthYear->format("Y");

        $countSent = 0;
        foreach ($contactList as $contact) {
            $popupPage = new PopupPage($this->module);
            $content = $popupPage->Load("payroll_email.html");
            $content->LoadFromArray($this->GetProperties());
            $content->LoadFromArray($contact);
            $content->SetVar("month_year", $monthYear);
            $html = $popupPage->Grab($content);
            $subject = $this->GetProperty("title") . ": Ihre Lohnabrechnungsdatei für den Monat " . $monthYear;

            $result = SendMailFromAdminTask(
                $contact["email"],
                $subject,
                $html,
                array(),
                array(array("Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo")),
                array(),
                "trebono Buchhaltung - 2KS Cloud Services GmbH"
            );
            if ($result === true) {
                $countSent++;
                $this->UpdateField("status", "sent");
            } else {
                $this->UpdateField("status", "error");
                $this->AddError($result);
            }
        }

        return $countSent;
    }

    /**
     * Checks if payroll for given date and company unit exists
     *
     * @param int $companyUnitId
     * @param string $date
     *
     * @return bool true if payroll exists, false otherwise
     */
    public static function PayrollExists($companyUnitID, $date)
    {
        $payrollMonth = CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "current_month"
            ? date_create($date)->format("Ym")
            : date_create($date)->modify("first day of this month")
                ->modify("last month")->format("Ym");

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT payroll_id FROM payroll 
					WHERE company_unit_id=" . intval($companyUnitID) . " AND payroll_month=" . Connection::GetSQLString($payrollMonth);

        return boolval($stmt->FetchRow($query));
    }

    /**
     * Reset company unit's payroll for given date
     *
     * @param int $companyUnitID
     * @param string $payrollDate
     *
     * @return bool
     */
    public function ResetPayroll($companyUnitID, $payrollDate)
    {
        if (CompanyUnit::GetPropertyValue("payroll_month", $companyUnitID) == "last_month") {
            $payrollDate = date("Y-m-d", strtotime($payrollDate . " -1 month"));
        }

        $payrollMonth = date_create($payrollDate);

        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT payroll_id FROM payroll 
					WHERE company_unit_id=" . intval($companyUnitID) . " AND payroll_month=" . Connection::GetSQLString($payrollMonth->format("Ym"));
        $payrollID = $stmt->FetchField($query);
        $this->LoadByID($payrollID);

        $receiptLineList = array();
        $voucherLineList = array();

        $productGroupList = new ProductGroupList("product");
        $productGroupList->LoadProductGroupListForAdmin();
        for ($i = 0; $i < $productGroupList->GetCountItems(); $i++) {
            $receiptList = new ReceiptList("receipt");
            $receiptList->LoadReceiptListForAddison(
                $companyUnitID,
                $productGroupList->_items[$i]["group_id"],
                $payrollDate,
                "reset"
            );
            $receiptLineList = array_merge($receiptLineList, $receiptList->_items);

            if ($productGroupList->_items[$i]["voucher"] != "Y") {
                continue;
            }

            $voucherList = new VoucherList("company");
            $voucherList->LoadVoucherListForAddison(
                $companyUnitID,
                $productGroupList->_items[$i]["group_id"],
                $payrollDate,
                "reset"
            );
            if ($voucherList->GetCountItems() <= 0) {
                continue;
            }

            $voucherLineList = array_merge($voucherLineList, $voucherList->GetItems());
        }

        foreach ($receiptLineList as $line) {
            $query = "UPDATE receipt SET pdf_export='0', datev_export='0' WHERE receipt_id=" . Connection::GetSQLString($line["receipt_id"]);
            $stmt->execute($query);
        }

        foreach ($voucherLineList as $line) {
            $query = "UPDATE voucher SET pdf_export='0', datev_export='0' WHERE voucher_id=" . Connection::GetSQLString($line["voucher_id"]);
            $stmt->execute($query);
        }

        $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("pdf_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("lodas_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("lug_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("logga_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("topas_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("perforce_file"));
        $fileStorage->Remove(PAYROLL_DIR . $this->GetProperty("sage_file"));

        $query = "DELETE FROM payroll WHERE payroll_id=" . Connection::GetSQLString($payrollID);

        return $stmt->execute($query);
    }

    /**
     * Validates user Role
     *
     * @param int $companyID company_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($payrollID, $userID = null, $companyUnitID = null)
    {
        if (!$payrollID && $companyUnitID === null) {
            return true;
        }

        if ($companyUnitID === null) {
            $payroll = new Payroll("billing");
            $payroll->LoadByID($payrollID);
            $companyUnitID = $payroll->GetProperty("company_unit_id");
        }

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        $permissionNames = array("payroll", "tax_auditor", "bookkeeping_export");

        if ($user->Validate($permissionNames, "or")) {
            return true;
        } else {
            $companyUnitIDs = array();
            foreach ($permissionNames as $permissionName => $value) {
                $companyUnitIDs = array_merge($companyUnitIDs, $user->GetPermissionLinkIDs($permissionName));
            }
            $companyUnitIDs = CompanyUnitList::AddChildIDs($companyUnitIDs);
            if (count($companyUnitIDs) == 0) {
                return false;
            }
        }

        return in_array($companyUnitID, $companyUnitIDs) ? true : false;
    }
}
