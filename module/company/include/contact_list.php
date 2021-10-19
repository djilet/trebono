<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ContactList extends LocalObjectList
{
    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of items to be loaded instantly
     */
    public function ContactList($module, $data = array())
    {
        parent::LocalObjectList($data);

        $this->module = $module;
        $this->SetSortOrderFields(array(
            "type_asc" => "	CASE
								WHEN contact_type='management' THEN 1
								WHEN contact_type='hr' THEN 2
								WHEN contact_type='other' THEN 3
							END, contact_id ASC",
        ));
        $this->SetOrderBy("type_asc");
    }

    /**
     * Loads company unit's contacts
     *
     * @param int $companyUnitID company_unit_id of company_unit which contacts should be loaded
     */
    public function LoadContactList($companyUnitID)
    {
        $where = array();
        $where[] = "c.company_unit_id=" . intval($companyUnitID);

        $query = "SELECT c.contact_id, c.company_unit_id, c.created, c.contact_type, 
						c.position, c.department, c.first_name, c.last_name, 
						c.email, c.phone, c.phone_job, c.comment, c.user_id, 
						c.contact_for_invoice, c.contact_for_contract, c.contact_for_service, c.contact_for_support, c.contact_for_payroll_export, 
						c.contact_for_stored_data, c.contact_for_company_unit_admin, c.contact_for_employee_admin, c.salutation, c.sending_pdf_invoice 
					FROM contact AS c "
            . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $this->LoadFromSQL($query, GetStatement(DB_PERSONAL));
        $this->PrepareContentBeforeShow();
    }

    /**
     * Puts additional fields that are not loaded by main sql-query
     */
    private function PrepareContentBeforeShow()
    {
        for ($i = 0; $i < $this->GetCountItems(); $i++) {
            $this->_items[$i]["contact_type_title"] = GetTranslation(
                "contact-type-" . $this->_items[$i]["contact_type"],
                $this->module
            );

            $contactFor = [];
            $contactForList = array(
                "invoice",
                "contract",
                "service",
                "support",
                "payroll_export",
                "stored_data",
                "company_unit_admin",
                "employee_admin",
                "company_unit",
                "employee"
            );
            foreach ($contactForList as $cf) {
                if (
                    !isset($this->_items[$i]["contact_for_" . $cf])
                    || $this->_items[$i]["contact_for_" . $cf] != "Y"
                ) {
                    continue;
                }

                $contactFor[] = GetTranslation("contact-for-" . $cf, $this->module);
            }
            $this->_items[$i]["contact_for"] = implode(", ", $contactFor);
        }
    }

    /**
     * Removes company unit's contacts from database by provided ids.
     *
     * @param array $ids array of contact_id's
     */
    public function Remove($ids)
    {
        if (is_array($ids) && count($ids) > 0) {
            foreach ($ids as $id) {
                $user = new User();
                $user->LoadByID(Contact::GetPropertyByID($id, "user_id"));

                $user->SetProperty("contact_for_invoice", "N");
                $user->SetProperty("contact_for_contract", "N");
                $user->SetProperty("contact_for_service", "N");
                $user->SetProperty("contact_for_support", "N");
                $user->SetProperty("contact_for_payroll_export", "N");
                $user->SetProperty("contact_for_stored_data", "N");
                $user->SetProperty("contact_for_company_unit_admin", "N");
                $user->SetProperty("contact_for_employee_admin", "N");
                $user->SetProperty("permission_company_unit_id", Contact::GetPropertyByID($id, "company_unit_id"));

                $stmt = GetStatement(DB_PERSONAL);
                $query = "SELECT contact_for_invoice, contact_for_contract, contact_for_service, contact_for_support, contact_for_payroll_export,
                    contact_for_stored_data, contact_for_company_unit_admin, contact_for_employee_admin FROM contact WHERE contact_id=" . Connection::GetSQLString($id);
                $user->SetProperty("OldContactFor", $stmt->FetchRow($query));

                $user->SaveFromContact();
            }

            $stmt = GetStatement(DB_PERSONAL);

            $query = "DELETE FROM contact WHERE contact_id IN (" . implode(", ", Connection::GetSQLArray($ids)) . ")";

            if ($stmt->Execute($query)) {
                if ($stmt->GetAffectedRows() > 0) {
                    $this->AddMessage("object-removed", $this->module, array("Count" => $stmt->GetAffectedRows()));
                }

                return true;
            } else {
                $this->AddError("sql-error-removing");
            }
        } else {
            $this->AddError("contact-list-no-ids-provided");
        }

        return false;
    }

    /** Gets an array of contact persons IDs and list of types/for's
     *
     * @param $companyUnitIDs array company_unit_id's
     * @param $contactType array contact type list
     * @param $contactFor array contact for list
     *
     * @return array
     */
    public static function GetContactListByCompanyUnitIDs(
        $companyUnitIDs,
        $contactType = array(),
        $contactFor = array()
    ) {
        //if company unit list is empty, get all contact persons
        $where = array();
        if (is_array($companyUnitIDs) && count($companyUnitIDs) > 0) {
            $companyUnitList = array_merge($companyUnitIDs, CompanyUnitList::AddChildIDs($companyUnitIDs));
            $where[] = " c.company_unit_id IN(" . implode(", ", $companyUnitList) . ")";
        }

        if (is_array($contactType) && count($contactType) > 0) {
            $where[] = "c.contact_type IN (" . implode(", ", Connection::GetSQLArray($contactType)) . ")";
        }

        if (is_array($contactFor) && count($contactFor) > 0) {
            $contactForWhere = array();
            foreach ($contactFor as $forType) {
                if ($forType == "") {
                    continue;
                }
                //in case empty option somehow gets into select
                $contactForWhere[] = "c.contact_for_" . $forType . " = 'Y'";
            }
            $where[] = "(" . implode(" OR ", $contactForWhere) . ")";
        }

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT c.contact_id, c.first_name, c.last_name, c.email, c.contact_type, c.contact_for_invoice, c.contact_for_contract,
                    c.contact_for_service, c.contact_for_support, c.contact_for_payroll_export, c.contact_for_stored_data, 
                    c.contact_for_company_unit_admin, c.contact_for_employee_admin, c.company_unit_id, c.sending_pdf_invoice
					  FROM contact AS c 
                    " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
        $contactList = $stmt->FetchList($query);

        //need to leave only relevant contact types
        $contactType = array();
        $contactFor = array();
        foreach ($contactList as $contact) {
            if (!in_array($contact["contact_type"], $contactType)) {
                $contactType[] = $contact["contact_type"];
            }

            if (!in_array("invoice", $contactFor) && $contact["contact_for_invoice"] == "Y") {
                $contactFor[] = "invoice";
            }
            if (!in_array("contract", $contactFor) && $contact["contact_for_contract"] == "Y") {
                $contactFor[] = "contract";
            }
            if (!in_array("service", $contactFor) && $contact["contact_for_service"] == "Y") {
                $contactFor[] = "service";
            }
            if (!in_array("support", $contactFor) && $contact["contact_for_support"] == "Y") {
                $contactFor[] = "support";
            }
            if (!in_array("payroll_export", $contactFor) && $contact["contact_for_payroll_export"] == "Y") {
                $contactFor[] = "payroll_export";
            }
            if (!in_array("stored_data", $contactFor) && $contact["contact_for_stored_data"] == "Y") {
                $contactFor[] = "stored_data";
            }
            if (!in_array("company_unit", $contactFor) && $contact["contact_for_company_unit_admin"] == "Y") {
                $contactFor[] = "company_unit";
            }
            if (in_array("employee", $contactFor) || $contact["contact_for_employee_admin"] != "Y") {
                continue;
            }

            $contactFor[] = "employee";
        }

        sort($contactType);
        sort($contactFor);

        return array("ContactList" => $contactList, "ContactTypeList" => $contactType, "ContactForList" => $contactFor);
    }

    /**
     * Send push notifications for contacts who are also employees or send email
     *
     * @param string $template notification text
     * @param array $contactType array of company_unit_id's
     * @param int $contactType , array of contact_type's
     * @param bool $isPush determines whenever push or email must be send
     * @param string $subject email subject
     */
    public static function SendMessageForContacts(
        $template,
        $contactIDs = array(),
        $isPush = true,
        $companyUnitIDs = array(),
        $contactType = array(),
        $contactFor = array(),
        $subject = ""
    ) {
        if (!is_array($contactIDs) && count($contactIDs) == 0) {
            $where = array();
            if (count($companyUnitIDs) > 0) {
                $where[] = "c.company_unit_id IN (" . implode(", ", $companyUnitIDs) . ")";
            }
            if (count($contactType) > 0) {
                $where[] = "c.contact_type IN (" . implode(", ", Connection::GetSQLArray($contactType)) . ")";
            }

            if (count($contactFor) > 0) {
                $contactForWhere = array();
                foreach ($contactFor as $forType) {
                    if ($forType == "") {
                        continue;
                    }
                    //in case empty option somehow gets into select
                    $contactForWhere[] = "c.contact_for_" . $forType . " = 'Y'";
                }
                $where[] = "(" . implode(" OR ", $contactForWhere) . ")";
            }

            $stmt = GetStatement(DB_PERSONAL);
            $query = "SELECT c.contact_id FROM contact AS c "
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");
            $contactList = $stmt->FetchList($query);

            $contactIDs = array();
            foreach ($contactList as $contact) {
                $contactIDs[] = $contact["contact_id"];
            }
        }

        foreach ($contactIDs as $contactID) {
            $contact = new Contact("company");
            $contact->LoadByID($contactID);

            //if project is local or placed on meshcloud test environment then send pushed only to predefined users
            if (IsLocalEnvironment() || IsTestEnvironment()) {
                $emailList = array("t.stein@2kscs.de", "j.klingler@3kglobaltrading.com", "test@employee.com");
                $email = trim(mb_strtolower($contact->GetProperty("email"), "utf-8"));

                if (!in_array($email, $emailList)) {
                    continue;
                }
            }

            $companyUnit = new CompanyUnit("company");
            $companyUnit->LoadByID($contact->GetProperty("company_unit_id"));

            $replacementsTmp = $contact->GetReplacementsList();
            $replacements = $replacementsTmp["ValueList"];

            $replacementsTmp = $companyUnit->GetReplacementsList();
            $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

            $text = GetLanguage()->ReplacePairs($template, $replacements);
            $data = array(
                FCMManager::DEEPLINK_KEY => FCMManager::DEEPLINK_PREFIX . "product_group_list",
                "popup_message" => $text
            );

            if (!$isPush) {
                $contact->SendEmail($subject, $text);
            } elseif ($contact->GetProperty("linked_user_id") != null) {
                $employee = new Employee("company");
                $employee->LoadByUserID($contact->GetProperty("linked_user_id"));
                Employee::SendPushNotification($employee->GetProperty("employee_id"), null, $text, $data);
            }
        }
    }

    /**
     * Prepares and outputs excel file with info about contact persons emails
     *
     * @param $contact_ids array list of contact ids
     * @param $versionList array filter for employee list
     * @param $companyUnitIDs array filter for employee list
     */
    public static function ExportEmailList($contact_ids, $companyUnitIDs, $contactType, $contactFor)
    {
        if (is_array($contact_ids) && count($contact_ids) > 0) {
            $contact = new Contact("company");
            $contactList = array();
            foreach ($contact_ids as $contact_id) {
                $contact->LoadByID($contact_id);
                $contactList[] = $contact->GetProperties();
            }
        } else {
            $contactList = self::GetContactListByCompanyUnitIDs($companyUnitIDs, $contactType, $contactFor);
            $contactList = $contactList["ContactList"];
        }

        $companyUnit = new CompanyUnit("company");
        $contact = new Contact("company");

        for ($i = 0; $i < count($contactList); $i++) {
            $contact->LoadByID($contactList[$i]["company_unit_id"]);
            $contactList[$i]["salutation"] = $contact->GetProperty("salutation");

            $companyUnit->LoadByID($contactList[$i]["company_unit_id"]);
            $contactList[$i]["title"] = $companyUnit->GetProperty("title");
            $contactList[$i]["street"] = $companyUnit->GetProperty("street");
            $contactList[$i]["house"] = $companyUnit->GetProperty("house");
            $contactList[$i]["zip_code"] = $companyUnit->GetProperty("zip_code");
            $contactList[$i]["city"] = $companyUnit->GetProperty("city");
            $contactList[$i]["country"] = $companyUnit->GetProperty("country");
        }

        //build email table header
        $emailTableHeader = explode(
            ";",
            "Salutation;First Name;Last Name;E-Mail;Company name;Street;Building Number;ZIP Code;City;Country"
        );


        //build email table body
        $emailTableBody = array();
        foreach ($contactList as $contact) {
            $row = array(
                $contact["salutation"],
                $contact["first_name"],
                $contact["last_name"],
                $contact["email"],
                $contact["title"],
                $contact["street"],
                $contact["house"],
                $contact["zip_code"],
                $contact["city"],
                $contact["country"]
            );
            $emailTableBody[] = $row;
        }

        //create spreadsheet and write the data
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray($emailTableHeader, null, "A1");
        $spreadsheet->getActiveSheet()->fromArray($emailTableBody, null, "A2");

        foreach (range('A', 'D') as $columnID) {
            $spreadsheet->getActiveSheet()->getColumnDimension($columnID)
                ->setAutoSize(true);
        }

        //save and output the file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->setSpreadsheet($spreadsheet);

        $filename = "email_export_" . date("Ymd") . ".xlsx";

        header("Cache-Control: max-age=0");
        header("Content-type: application/vnd.ms-excel");
        header('Content-Disposition: attachment;filename="' . $filename . '"');

        $tempFilePath = PROJECT_DIR . "var/log/email_export_" . date("U") . "_" . rand(100, 999) . ".xlsx";
        $writer->save($tempFilePath);
        echo mb_convert_encoding(utf8_encode(file_get_contents($tempFilePath)), "windows-1252", "utf-8");
        unlink($tempFilePath);

        exit();
    }
}
