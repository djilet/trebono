<?php

class RecreationConfirmation extends LocalObject
{
    private $module;

    /**
     * RecreationConfirmation constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }


    /**
     * Load confirmation by ID
     *
     * @param int $id Confirmation id
     */
    public function LoadByID($id)
    {
        $query = "SELECT * FROM recreation_confirmation WHERE confirmation_id=" . intval($id);
        $this->LoadFromSQL($query);
    }

    /**
     * Load confirmation by company unit ID
     *
     * @param int $id Company unit id
     */
    public function LoadByCompanyUnitID($id)
    {
        $query = "SELECT * FROM recreation_confirmation WHERE company_unit_id=" . intval($id);
        $this->LoadFromSQL($query);
    }

    /**
     * Request Verification
     *
     * @param LocalObject $request
     *
     * @return bool
     */
    private function Validate(LocalObject $request)
    {
        if (!$request->ValidateNotEmpty("content")) {
            $this->AddError("error-save-content-empty", $this->module);
        }

        return !$this->HasErrors();
    }


    /**
     * Save confirmation
     *
     * @param LocalObject $request
     *
     * @return bool|RecordSet|null
     */
    public function Save(LocalObject $request)
    {
        if (!$this->Validate($request)) {
            return false;
        }

        $stmt = GetStatement();
        $id = $request->GetIntProperty("confirmation_id");
        $isNeedSaveHistory = true;

        if ($id > 0) {
            $this->LoadByID($id);
            if ($this->GetProperty("content") == $request->GetProperty("content")) {
                $isNeedSaveHistory = false;
            }

            $query = "UPDATE recreation_confirmation SET
                content=" . $request->GetPropertyForSQL("content") . ",
                updated_at=" . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . "
                WHERE confirmation_id=" . $id;
            $stmt->Execute($query);
            if (!$stmt->Execute($query)) {
                $this->AddError("1sql-error");

                return false;
            }
        } else {
            $query = "INSERT INTO recreation_confirmation(content, company_unit_id, updated_at) VALUES (
                    " . $request->GetPropertyForSQL("content") . ",
                    " . $request->GetPropertyForSQL("company_unit_id") . ",
                    " . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . "
                )
                RETURNING confirmation_id";
            if (!$stmt->Execute($query)) {
                $this->AddError("2sql-error");

                return false;
            }

            $id = $stmt->GetLastInsertID();
            $request->SetProperty("confirmation_id", $id);
        }

        if ($isNeedSaveHistory && $id > 0) {
            if (!$this->SaveHistory($request, $id)) {
                $this->AddError("sql-error");

                return false;
            }
        }

        return true;
    }


    /**
     * Save history
     *
     * @param LocalObject $request
     * @param int $id
     *
     * @return bool
     */
    protected function SaveHistory(LocalObject $request, $id)
    {
        $stmtControl = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();
        $query = "INSERT INTO recreation_confirmation_history(confirmation_id, content, user_id, created) VALUES(
                " . intval($id) . ",
                " . $request->GetPropertyForSQL("content") . ",
                " . $user->GetIntProperty("user_id") . ",
                " . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . "
            )";

        return $stmtControl->Execute($query);
    }


    /**
     * @param Employee $employee
     * @param CompanyUnit $company
     * @param string $filename
     * @param string $flag Output type
     *                      I - Send to standard output
     *                      D - Download file
     *                      F - Save to local file
     *                      S - Return as a string
     *                      send - Save to storage and send out
     * @param int $receiptID
     *
     * @return string
     */
    public function GenerateConfirmationToPdf(
        Employee $employee,
        CompanyUnit $company,
        $filename = "confirmation.pdf",
        $flag = "F",
        $receiptID = null
    ) {
        $adminPage = new PopupPage($this->module, true);
        $content = $adminPage->Load("mail_pdf.html");
        if ($receiptID > 0) {
            $this->PrepareForPublic($employee, $receiptID);
        }
        $content->LoadFromObject($this);
        $content->LoadFromObject($employee);
        $content->LoadFromObject($company);

        $receipt = new Receipt("receipt");
        $receipt->LoadByID($receiptID);

        $content->SetVar("Date", $receipt->GetProperty("status_updated"));
        $content->SetVar("IsConfirmation", 1);

        $mpdf = new Mpdf("utf-8", "A4", "11", "dejavusans", 25, 20, 30, 25, 10, 10);
        $mpdf->PDFA = true;
        $mpdf->PDFAauto = true;
        $mpdf->setTitle($filename);
        $css = file_get_contents(PROJECT_DIR . "module/" . $this->module . "/template/css/mail_pdf.css");
        $html = $adminPage->Grab($content);
        $mpdf->WriteHTML($css, 1);
        $mpdf->WriteHTML($html, 2);

        if ($flag === "F") {
            $pathToPdfFile = PROJECT_DIR . "var/cache/" . $filename;
            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
            $fileStorage->MoveToStorage($pathToPdfFile, PAYROLL_DIR, $filename);
            $mpdf->Output($pathToPdfFile, "F");

            return $pathToPdfFile;
        }

        if ($flag === "send") {
            if ($employee->IsPropertySet("employee_id")) {
                $stmt = GetStatement(DB_MAIN);
                $query = "INSERT INTO recreation_confirmation_employee 
                    (confirmation_id, employee_id, receipt_id, created, pdf_file, status) VALUES
                    (" . $this->GetPropertyForSQL("confirmation_id") . ",
                    " . $employee->GetPropertyForSQL("employee_id") . ",
                    " . $receiptID . ",
                    " . Connection::GetSQLDateTime(GetCurrentDateTime()) . ",
                    " . Connection::GetSQLString($filename) . ",
                    'new'
                    )";
                if ($stmt->Execute($query)) {
                    $pdfPath = PROJECT_DIR . "var/log/" . $filename;
                    $mpdf->Output($pdfPath, "F");
                    $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);
                    $fileStorage->MoveToStorage($pdfPath, PAYROLL_DIR, $filename);

                    //temporarily not usable
                    //$this->SendConfirmationToEmail($employee, $this->GetProperty("confirmation_id"));
                    return true;
                }

                $this->AddError("sql-error");

                return false;
            }
        } else {
            $mpdf->Output($filename, $flag);
        }

        return null;
    }

    /**
     * Sending confirmation PDFs by company e-mail
     *
     * @param Employee $employee
     * @param int $confirmationID
     */
    public function SendConfirmationToEmail(Employee $employee, $confirmationID)
    {
        $contactList = new ContactList("company");
        $contactList->LoadContactList($employee->GetIntProperty("company_unit_id"));

//        $email = [];
//        $salutationContact = [];
//        $firstNameContact = [];
//        $lastNameContact = [];
        foreach ($contactList->GetItems() as $item) {
            if ($item["contact_for_payroll_export"] != "Y") {
                continue;
            }

//            $email[] = $item["email"];
//            $salutationContact[] = $item["salutation"];
//            $firstNameContact[] = $item["first_name"];
//            $lastNameContact[] = $item["last_name"];
        }

        //temporarily not usable
        /*for ($i = 0; $i < count($email); $i++) {

            if (!empty($email[$i]) AND filter_var($email[$i], FILTER_VALIDATE_EMAIL)) {
                $popupPage = new PopupPage($this->module, true);
                $content = $popupPage->Load("confirmation_mail.html");

                $linkToConfirmationList = GetUrlPrefix(DATA_LANGCODE, false).ADMIN_FOLDER.
                    "/module.php?load=".$this->module."&Section=confirmation&CompanyUnitID=".
                    $employee->GetIntProperty("company_unit_id");
                $linkToConfirmation = GetUrlPrefix(DATA_LANGCODE, false).ADMIN_FOLDER."/module.php?load=".
                    $this->module."&Section=confirmation&CompanyUnitID=".$employee->GetIntProperty("company_unit_id").
                    "&Action=GetConfirmationPDF".
                    "&ConfirmationID=".intval($confirmationID);

                $message = GetTranslation(
                    "send-email-confirmation-message",
                    $this->module,
                    [
                        "Salutation" => $salutationContact[$i],
                        "FirstName" => $firstNameContact[$i],
                        "LastName" => $lastNameContact[$i],
                        "EmployeeFirstName" => $employee->GetProperty("first_name"),
                        "EmployeeLastName" => $employee->GetProperty("last_name"),
                        "CompanyUnitTitle" => CompanyUnit::GetTitleByID($employee->GetProperty("company_unit_id")),

                        "LinkToTheAgreement" => "<a href="".$linkToConfirmation."?disposition=inline" target="blank">".
                        GetTranslation("send-email-confirmation-link-agreement", $this->module)."</a>",
                        "LinkToTheAgreementList" => "<a href="".$linkToConfirmationList."" target="blank">".
                        GetTranslation("send-email-confirmation-link-agreement-list", $this->module)."</a>"
                    ]
                );
                $content->SetVar("Message", $message);
                $html = $popupPage->Grab($content);

                $subject = GetTranslation(
                    "send-email-confirmation-subject",
                    $this->module,
                    [
                        "FirstName" => $employee->GetProperty("first_name"),
                        "LastName" => $employee->GetProperty("last_name"),
                        "CompanyUnitTitle" => CompanyUnit::GetTitleByID($employee->GetProperty("company_unit_id")),
                    ]
                );
                $embeddedImages = [["CID" => "logo.png", "Path" => ADMIN_PATH_ABSOLUTE .
                "template/images/email-footer-logo.png"]];

                SendMailFromAdminTask($email[$i], $subject, $html, [], $embeddedImages);
            }
        }*/
    }

    /**
     * Get change history
     *
     * @param int $confirmationID
     *
     * @return array|bool|null
     */
    public function GetHistoryList($confirmationID)
    {
        $where = ["WHERE confirmation_id=" . intval($confirmationID)];

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT value_id, confirmation_id, created, user_id
                FROM recreation_confirmation_history
                " . implode(" AND ", $where) . "
                ORDER BY created DESC";
        $historyList = $stmt->FetchList($query);
        if ($historyList) {
            $user = new User();
            foreach ($historyList as $key => $history) {
                $user->LoadByID($history["user_id"]);
                $historyList[$key]["user_first_name"] = $user->GetProperty("first_name");
                $historyList[$key]["user_last_name"] = $user->GetProperty("last_name");
            }

            return $historyList;
        }

        return false;
    }

    /**
     * Load confirmation from history
     *
     * @param int $historyID
     */
    public function LoadHistoryVersion($historyID)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT *
                FROM recreation_confirmation_history
                WHERE value_id=" . intval($historyID);
        $this->LoadFromSQL($query, $stmt);
    }

    public function PrepareForPublic(Employee $employee, $receiptID)
    {
        $company = new CompanyUnit("company");
        $company->LoadByID($employee->GetIntProperty("company_unit_id"));

        $receipt = new Receipt($this->module);
        $receipt->LoadByID($receiptID);

        $contract = new Contract("product");
        $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employee->GetIntProperty("employee_id"),
            Product::GetProductIDByCode(PRODUCT__RECREATION__MAIN),
            $receipt->GetProperty("document_date")
        );

        $replacementsTmp = $employee->GetReplacementsList(true);
        $replacements = $replacementsTmp["ValueList"];

        $replacementsTmp = $company->GetReplacementsList();
        $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

        $replacementsTmp = $contract->GetReplacementsList();
        $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__RECREATION);

        if ($specificProductGroup) {
            $replacementsTmp = $specificProductGroup->GetReplacementsList(
                $employee->GetProperty("employee_id"),
                $receipt->GetProperty("document_date")
            );
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);
        }

        $specificProductGroup = SpecificProductGroupFactory::CreateByCode(PRODUCT_GROUP__MOBILE);
        if ($specificProductGroup) {
            $replacementsTmp = $specificProductGroup->GetReplacementsList(
                $employee->GetProperty("employee_id"),
                $receipt->GetProperty("document_date"),
                false
            );
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);
        }

        $text = GetLanguage()->ReplacePairs($this->GetProperty("content"), $replacements);
        $this->SetProperty("content", $text);
    }
}
