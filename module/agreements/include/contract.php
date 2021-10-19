<?php

class AgreementsContract extends LocalObject
{
    private $module;

    /**
     * AgreementsContract constructor.
     *
     * @param $module
     */
    public function __construct($module)
    {
        $this->module = $module;
    }


    /**
     * User Agreement load
     *
     * @param int $id Agreement id
     */
    public function LoadByID($id)
    {
        $query = "SELECT * FROM agreements WHERE agreement_id=" . intval($id);
        $this->LoadFromSQL($query);
    }


    /**
     * User Agreement load
     *
     * @param int $organizationId Company unit id
     * @param int $serviceId Service group id
     */
    public function LoadByService($organizationId, $serviceId)
    {
        $query = "SELECT * FROM agreements WHERE 
                organization_id=" . intval($organizationId) . " AND 
                group_id=" . intval($serviceId);
        $this->LoadFromSQL($query);
    }

    /**
     * Load last accepted agreement
     *
     * @param int $agreementId Company unit id
     * @param int $employeeId Service group id
     */
    public function LoadLastAcceptedAgreement($agreementId, $employeeId)
    {
        $stmt = GetStatement();
        $query = "SELECT a.agreement_id, a.group_id, a.organization_id, e.version, e.updated_at, e.file
            FROM agreements AS a
            INNER JOIN (SELECT DISTINCT ON(e.agreement_id, e.employee_id) e.* 
            FROM agreements_employee AS e ORDER BY e.agreement_id, e.employee_id, e.version DESC) AS e 
            ON a.agreement_id=e.agreement_id AND employee_id=" . intval($employeeId) . "
            WHERE a.agreement_id=" . intval($agreementId);
        $row = $stmt->FetchRow($query);

        $query = "SELECT version, content, created_at, user_id FROM agreements_history 
            WHERE agreement_id=" . intval($agreementId) . " AND 
            version=" . intval($row["version"]);
        $this->LoadFromSQL($query, GetStatement(DB_CONTROL));

        $this->_properties = array_merge(
            $row,
            $this->_properties
        );
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
        if (!$request->GetProperty("new_only")) {
            $request->SetProperty("new_only", "N");
        }

        return !$this->HasErrors();
    }


    /**
     * Save User Agreement
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
        $id = $request->GetIntProperty("agreement_id");
        $version = 1;
        $isNeedSaveHistory = true;

        $currentPropertyList = false;

        if ($id > 0) {
            $this->LoadByID($id);
            if ($this->GetProperty("content") !== $request->GetProperty("content")) {
                $version = $this->GetIntProperty("version") + 1;
            } else {
                $version = $this->GetIntProperty("version");
                $isNeedSaveHistory = false;
            }

            $currentPropertyList = $this->GetProperties();

            $query = "UPDATE agreements SET 
                content=" . $request->GetPropertyForSQL("content") . ",
                version=" . intval($version) . ",
                confirm_message=" . $request->GetPropertyForSQL("confirm_message") . ",
                updated_at=" . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . ",
                new_only=" . $request->GetPropertyForSQL("new_only") . "
                WHERE agreement_id=" . $id;
            $stmt->Execute($query);
        } else {
            $query = "INSERT INTO agreements(group_id, content, confirm_message, organization_id, updated_at, new_only) 
                VALUES (
                    " . $request->GetIntProperty("group_id") . ",
                    " . $request->GetPropertyForSQL("content") . ",
                    " . $request->GetPropertyForSQL("confirm_message") . ",
                    " . $request->GetIntProperty("organization_id") . ",
                    " . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . ",
                    " . $request->GetPropertyForSQL("new_only") . "
                )
                RETURNING agreement_id";
            if ($stmt->Execute($query)) {
                $id = $stmt->GetLastInsertID();
                $request->SetProperty("agreement_id", $id);
            }
        }

        if ($isNeedSaveHistory && $id > 0 && $version > 0) {
            $this->SaveHistory($request, $id, $version);
        } else {
            $this->UpdateHistory($request, $id, $version);
        }

        if (!$this->SaveFieldHistory($currentPropertyList, $request)) {
            $this->AddError("sql-error");

            return false;
        }

        return true;
    }


    /**
     * Save history
     *
     * @param LocalObject $request
     * @param int $id
     * @param int $version
     *
     * @return bool|RecordSet|null
     */
    protected function SaveHistory(LocalObject $request, $id, $version)
    {
        $stmtControl = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();

        $query = "INSERT INTO agreements_history(agreement_id, version, content, user_id, created_at, new_only) VALUES(
            " . intval($id) . ",
            " . intval($version) . ",
            " . $request->GetPropertyForSQL("content") . ",
            " . $user->GetIntProperty("user_id") . ",
            " . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . ",
            " . $request->GetPropertyForSQL("new_only") . "
        )";

        return $stmtControl->Execute($query);
    }

    /**
     * Update history"s new-only
     *
     * @param LocalObject $request
     * @param int $id
     * @param int $version
     *
     * @return bool|RecordSet|null
     */
    protected function UpdateHistory(LocalObject $request, $id, $version)
    {
        $stmtControl = GetStatement(DB_CONTROL);

        $user = new User();
        $user->LoadBySession();

        $query = "UPDATE agreements_history SET 
            new_only=" . $request->GetPropertyForSQL("new_only") . "
            WHERE agreement_id=" . $id . " AND version=" . intval($version);

        return $stmtControl->Execute($query);
    }


    /**
     * User Agreement load for api
     *
     * @param int $groupId Service group id
     * @param Employee $employee
     */
    public function LoadForApi($groupId, Employee $employee)
    {
        $query = "SELECT * FROM agreements WHERE 
               group_id=" . intval($groupId) . " AND
               organization_id=" . $employee->GetIntProperty("company_unit_id");
        $this->LoadFromSQL($query);
        $this->SetProperty("group_id", $groupId);
        $this->PrepareForPublic($employee);
    }


    /**
     * User acceptance of user agreement
     *
     * @param int $agreementId Agreement id
     * @param Employee $employee Employee
     * @param string $deviceId Service group id
     * @param int $versionID agreement version id
     *
     * @return bool|RecordSet|null
     */
    public function UserAcceptedTheAgreement($agreementId, Employee $employee, $deviceId, $versionID)
    {
        $this->LoadByID($agreementId);
        if ($this->GetIntProperty("agreement_id") == 0) {
            return false;
        }
        if (intval($versionID) <= 0) {
            return false;
        }

        $this->SetProperty("version", $versionID);

        $deviceInfo = "";
        $client = Device::GetLastVersionByDeviceIDBeforeDate(
            $deviceId,
            $employee->GetIntProperty("user_id"),
            date("Y-m-d H:i:s")
        );
        if (!empty($client)) {
            $deviceInfo = "{$client['client']}, {$client['version']}";
        }

        $stmt = GetStatement();

        $query = "SELECT agreement_id FROM agreements_employee 
            WHERE agreement_id=" . intval($agreementId) . " AND 
            employee_id=" . $employee->GetIntProperty("employee_id") . " AND 
            version=" . $this->GetIntProperty("version");
        if ($stmt->FetchField($query)) {
            return true;
        }

        $query = "INSERT INTO agreements_employee
            (agreement_id, employee_id, version, updated_at, device_info, device_id) 
            VALUES(
                " . intval($agreementId) . ",
                " . $employee->GetIntProperty("employee_id") . ",
                " . $this->GetIntProperty("version") . ",
                " . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . ",
                " . Connection::GetSQLString($deviceInfo) . ",
                " . Connection::GetSQLString($deviceId) . "
            ) 
            ON CONFLICT(agreement_id, employee_id, version) DO UPDATE SET
                version=" . $this->GetIntProperty("version") . ",
                updated_at=" . Connection::GetSQLDateTime(date("Y-m-d H:i:s")) . ",
                device_info=" . Connection::GetSQLString($deviceInfo);
        if ($stmt->Execute($query)) {
            $employeeId = $employee->GetProperty("employee_id");
            $pdfAgreementId = $agreementId;

            $contract = new AgreementsContract("agreements");
            $contract->LoadLastAcceptedAgreement($pdfAgreementId, $employeeId);

            $product = new ProductGroup("product");
            $product->LoadByID($contract->GetIntProperty("group_id"));
            $contract->LoadFromObject($product, ["group_id", "code"]);
            $contract->PrepareForPublic($employee);

            $company = new CompanyUnit("company");
            $company->LoadByID($employee->GetProperty("company_unit_id"));

            $fileName = date("ym") . "_" . $company->GetProperty("customer_guid") . "_" .
                $employee->GetProperty("employee_guid") . "_" . $this->GetProperty("agreement_id") . "_" .
                $this->GetIntProperty("version") . ".pdf";

            $pathToFile = $contract->GenerateContractToPdf($contract, $employee, $company, $fileName, "F");

            $fileStorage = GetFileStorage(CONTAINER__AGREEMENTS);
            $fileStorage->MoveToStorage($pathToFile, AGREEMENTS_DIR, $fileName);

            $query = "UPDATE agreements_employee SET file=" . Connection::GetSQLString($fileName) . " 
                WHERE agreement_id=" . intval($agreementId) . " AND 
                employee_id=" . intval($employeeId) . " AND 
                version=" . $this->GetIntProperty("version");
            $stmt->Execute($query);

            $this->SendContractToEmail($employee, $pdfAgreementId);

            return true;
        }

        return false;
    }


    /**
     * Check to accept user agreement
     *
     * @param int $groupId Service group id
     * @param int $organizationId Company unit id
     * @param int $employee_id Employee id
     * @param bool $returnRow return row with last agreement or actual result
     *
     * @return bool|array
     */
    public function IsAgreementMustBeAccepted($groupId, $organizationId, $employee_id, $returnRow = false)
    {
        $query = "SELECT a.agreement_id, e.version, CASE WHEN e.version=a.version THEN 1 ELSE 0 END AS is_last_version
            FROM agreements AS a
            INNER JOIN company_unit AS c ON a.organization_id=c.company_unit_id AND c.agreement_enable='Y'
            LEFT JOIN agreements_employee AS e ON a.agreement_id=e.agreement_id 
                AND e.employee_id=" . intval($employee_id) . "
            WHERE a.group_id=" . intval($groupId) . " AND a.organization_id=" . intval($organizationId) . " 
            ORDER BY e.version DESC 
            LIMIT 1";
        $row = GetStatement()->FetchRow($query);

        if ($returnRow) {
            return $row;
        }

        if (!empty($row) && $row["version"] != null) {
            $productGroup = new ProductGroup($this->module);
            $productGroup->LoadByID($groupId);

            $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));
            if ($specificProductGroup == null) {
                return false;
            }
            $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

            $contract = new Contract($this->module);
            $contract->LoadContractForDate(
                OPTION_LEVEL_EMPLOYEE,
                $employee_id,
                $mainProductID,
                GetCurrentDate()
            );

            $where = [];
            if ($row["version"] > 0) {
                $where[] = "version > " . $row["version"];
            }
            $where[] = "agreement_id=" . $row["agreement_id"];
            $where[] = "created_at >=" . $contract->GetPropertyForSQL("start_date");
            $where[] = "new_only = 'N'";

            $stmtControl = GetStatement(DB_CONTROL);
            $query = "SELECT created_at, version, new_only FROM agreements_history
                   " . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "") . " ORDER BY version DESC";
            $listHistory = $stmtControl->FetchList($query);

            return count($listHistory) > 0;
        }

        return !empty($row) && $row["is_last_version"] == 0;
    }

    /**
     * @param AgreementsContract $contract
     * @param Employee $employee
     * @param CompanyUnit $company
     * @param string $filename
     * @param string $flag Output type
     *                                          I - Send to standard output
     *                                          D - Download file
     *                                          F - Save to local file. Return path
     *                                          S - Return as a string
     *
     * @return string|null
     */
    public function GenerateContractToPdf(
        AgreementsContract $contract,
        Employee $employee,
        CompanyUnit $company,
        $filename = "agreement.pdf",
        $flag = "F"
    ) {
        $adminPage = new PopupPage($this->module, true);
        $content = $adminPage->Load("mail_pdf.html");
        $content->LoadFromObject($contract);
        $content->LoadFromObject($employee);
        $content->LoadFromObject($company);
        $content->SetVar("Date", $contract->GetProperty("updated_at"));

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
            $mpdf->Output($pathToPdfFile, "F");

            return $pathToPdfFile;
        } else {
            $mpdf->Output($filename, $flag);
        }

        return null;
    }

    /**
     * Sending a contract of employment by company e-mail
     *
     * @param Employee $employee
     */
    public function SendContractToEmail(Employee $employee, $agreementId)
    {
        $contactList = new ContactList("company");
        $contactList->LoadContactList($employee->GetIntProperty("company_unit_id"));

        $emails = [];
        $salutationContact = [];
        $firstNameContact = [];
        $lastNameContact = [];
        foreach ($contactList->GetItems() as $item) {
            if ($item["contact_for_payroll_export"] != "Y") {
                continue;
            }

            $emails[] = $item["email"];
            $salutationContact[] = $item["salutation"];
            $firstNameContact[] = $item["first_name"];
            $lastNameContact[] = $item["last_name"];
        }

        foreach ($emails as $i => $email) {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $productGroup = new ProductGroup("product");
            $productGroup->LoadByID($this->GetIntProperty("group_id"));

            $popupPage = new PopupPage($this->module, true);
            $content = $popupPage->Load("contract_mail.html");

            $linkToAgreementList = GetUrlPrefix(DATA_LANGCODE, false) . ADMIN_FOLDER .
                "/module.php?load=" . $this->module . "&Section=Employees&OrganizationID=" .
                $employee->GetIntProperty("company_unit_id");
            $linkToAgreement = GetUrlPrefix(DATA_LANGCODE, false) . ADMIN_FOLDER .
                "/module.php?load=" . $this->module . "&Section=Employees&OrganizationID=" .
                $employee->GetIntProperty("company_unit_id") .
                "&Employee=" . $employee->GetIntProperty("employee_id") .
                "&PdfAgreementId=" . intval($agreementId);

            $message = GetTranslation(
                "send-email-contract-message",
                $this->module,
                [
                    "Salutation" => $salutationContact[$i],
                    "FirstName" => $firstNameContact[$i],
                    "LastName" => $lastNameContact[$i],
                    "EmployeeFirstName" => $employee->GetProperty("first_name"),
                    "EmployeeLastName" => $employee->GetProperty("last_name"),
                    "CompanyUnitTitle" => CompanyUnit::GetTitleByID($employee->GetProperty("company_unit_id")),
                    "ServiceName" => GetTranslation(
                        "product-group-" . $productGroup->GetProperty("code"),
                        "product"
                    ),
                    "LinkToTheAgreement" => "<a href='" . $linkToAgreement . "?disposition=inline' target='blank'>" .
                        GetTranslation(
                            "send-email-contract-link-agreement",
                            $this->module
                        ) . "</a>",
                    "LinkToTheAgreementList" => "<a href='" . $linkToAgreementList . "' target='blank'>" .
                        GetTranslation(
                            "send-email-contract-link-agreement-list",
                            $this->module
                        ) . "</a>",
                ]
            );
            $content->SetVar("Message", $message);
            $html = $popupPage->Grab($content);

            $subject = GetTranslation(
                "send-email-contract-subject",
                $this->module,
                [
                    "FirstName" => $employee->GetProperty("first_name"),
                    "LastName" => $employee->GetProperty("last_name"),
                    "CompanyUnitTitle" => CompanyUnit::GetTitleByID($employee->GetProperty("company_unit_id")),
                ]
            );
            $embeddedImages = [
                [
                    "CID" => "logo.png",
                    "Path" => ADMIN_PATH_ABSOLUTE . "template/images/email-footer-logo.png",
                ],
            ];

            SendMailFromAdminTask($email, $subject, $html, [], $embeddedImages);
        }
    }

    /**
     * Get change history
     *
     * @param int $agreementId
     * @param int $version
     * @param int $orderDesc
     *
     * @return array|bool|null
     */
    public function GetHistoryList($agreementId, $version = 0, $orderDesc = true)
    {
        $version = intval($version);
        $where = ["WHERE agreement_id=" . intval($agreementId)];

        if ($version > 0) {
            $where[] = "version <= " . $version;
        }

        $orderBy = $orderDesc ? " ORDER BY created_at DESC" : " ORDER BY created_at ASC";

        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT agreement_id, version, created_at, user_id, new_only
                FROM agreements_history
                " . implode(" AND ", $where) . $orderBy;
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
     * Load version in history
     *
     * @param int $agreementId
     * @param int $version
     */
    public function LoadHistoryVersion($agreementId, $version)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "SELECT * FROM agreements_history 
            WHERE agreement_id=" . intval($agreementId) . " AND version=" . intval($version);
        $this->LoadFromSQL($query, $stmt);
    }

    public function PrepareForPublic(Employee $employee)
    {
        $confirmMessage = trim($this->GetProperty("confirm_message"));
        if (empty($confirmMessage)) {
            $this->SetProperty("confirm_message", null);
        }

        $company = new CompanyUnit("company");
        $company->LoadByID($employee->GetIntProperty("company_unit_id"));

        $productGroup = new ProductGroup("product");
        $productGroup->LoadByID($this->GetProperty("group_id"));

        $specificProductGroup = SpecificProductGroupFactory::CreateByCode($productGroup->GetProperty("code"));
        if ($specificProductGroup == null) {
            return false;
        }
        $mainProductID = Product::GetProductIDByCode($specificProductGroup->GetMainProductCode());

        $contract = new Contract("product");
        $contract->LoadContractForDate(
            OPTION_LEVEL_EMPLOYEE,
            $employee->GetIntProperty("employee_id"),
            $mainProductID,
            GetCurrentDate()
        );

        $row = $this->IsAgreementMustBeAccepted(
            $this->GetProperty("group_id"),
            $employee->GetIntProperty("company_unit_id"),
            $employee->GetIntProperty("employee_id"),
            true
        );

        //if agreement was signed before, we need to get latest version for all, not "new contracts only"
        if (!empty($row) && $row["is_last_version"] != 1 && $row["version"] != null) {
            $stmtControl = GetStatement(DB_CONTROL);
            $query = "SELECT * FROM agreements_history
                WHERE agreement_id=" . $this->GetPropertyForSQL("agreement_id") . " AND 
                created_at >=" . $contract->GetPropertyForSQL("start_date") . " AND new_only='N' 
                ORDER BY version DESC";
            $listHistory = $stmtControl->FetchRow($query);
            if ($listHistory != null) {
                $this->AppendFromArray($listHistory);
            }
        }

        $replacementsTmp = $employee->GetReplacementsList();
        $replacements = $replacementsTmp["ValueList"];

        $replacementsTmp = $company->GetReplacementsList();
        $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

        $replacementsTmp = $contract->GetReplacementsList();
        $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

        $replacementsTmp = $specificProductGroup->GetReplacementsList(
            $employee->GetProperty("employee_id"),
            GetCurrentDate()
        );
        $replacements = array_merge($replacementsTmp["ValueList"], $replacements);

        //$newContent = str_replace(
        //array_keys($replacements),
        // array_values($replacements),
        // $this->GetProperty("content")
        //);

        $text = GetLanguage()->ReplacePairs($this->GetProperty("content"), $replacements);
        $this->SetProperty("content", $text);
    }

    /**
     * Save the modified fields.
     *
     * @return bool true if all modified fields are saved successfully or false on failure
     */
    public function SaveFieldHistory($currentPropertyList, $request)
    {
        $user = new User();
        $user->LoadBySession();

        $propertyList = ["confirm_message", "new_only"];
        if (!$currentPropertyList) {
            $currentPropertyList = array_fill_keys($propertyList, null);
        }

        foreach ($propertyList as $key) {
            if ($currentPropertyList[$key] == $request->GetProperty($key)) {
                continue;
            }

            if (
                !self::SaveFieldHistoryRow(
                    $user->GetProperty("user_id"),
                    $request->GetProperty("agreement_id"),
                    $key,
                    $request->GetProperty($key)
                )
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Puts the record about changed field to database
     *
     * @param int $userID user_id of user who makes the changes
     * @param int $agreementID agreement_id of changed agreement
     * @param string $key key of changed property
     * @param string $value new value
     *
     * @return bool|NULL true if inserted successfully or false|null otherwise
     */
    public static function SaveFieldHistoryRow($userID, $agreementID, $key, $value)
    {
        $stmt = GetStatement(DB_CONTROL);
        $query = "INSERT INTO agreements_field_history (agreement_id, property_name, value, created, user_id)
					VALUES (
						" . intval($agreementID) . ",
						" . Connection::GetSQLString($key) . ",
						" . Connection::GetSQLString($value) . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . intval($userID) . ")
					RETURNING value_id";

        return $stmt->Execute($query);
    }

    /**
     * Returns property value history
     *
     * @param string $property
     * @param int $agreementID
     *
     * @return array list of values
     */
    public static function GetPropertyValueListAgreement($property, $agreementID)
    {
        $stmt = GetStatement(DB_CONTROL);

        $query = "SELECT value_id, user_id, created, value, property_name
					FROM agreements_field_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND 
					agreement_id=" . intval($agreementID) . "
					ORDER BY created DESC";
        $valueList = $stmt->FetchList($query);

        if (!$valueList) {
            return $valueList;
        }

        $userIDs = array_column($valueList, "user_id");

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT user_id, 
					" . Connection::GetSQLDecryption("first_name") . " AS first_name, 
					" . Connection::GetSQLDecryption("last_name") . " AS last_name 
				FROM user_info 
				WHERE user_id IN(" . implode(",", $userIDs) . ")";
        $userInfo = $stmt->FetchIndexedList($query, "user_id");

        foreach ($valueList as $i => $value) {
            $valueList[$i]["first_name"] = $userInfo[$valueList[$i]["user_id"]]["first_name"];
            $valueList[$i]["last_name"] = $userInfo[$valueList[$i]["user_id"]]["last_name"];
        }

        return $valueList;
    }
}
