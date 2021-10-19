<?php

require_once(__DIR__ . '/../../agreements/include/employees_list.php');
require_once(__DIR__ . '/../../agreements/include/confirmation_list.php');

class StoredData extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of stored data properties to be loaded instantly
     */
    public function StoredData($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new stored data record in database. Object must be loaded before the method will be called.
     * Required properties are: company_unit_id, date_from, date_to, services, employees
     *
     * @return bool stored_data_id of new record on success or false on sql-failure
     */
    public function Create()
    {
        $stmt = GetStatement();

        $employees = $this->GetProperty("general") || count($this->GetProperty("employees")) == 0 ? "all" : implode(",", $this->GetProperty("employees"));

        $query = "INSERT INTO stored_data (company_unit_id, created, date_from, date_to, employees, status, cron) VALUES (
						" . $this->GetIntProperty("company_unit_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("date_from") . ",
						" . $this->GetPropertyForSQL("date_to") . ",
						" . Connection::GetSQLString($employees) . ",
						'new'" . ",
						" . $this->GetPropertyForSQL("cron") . ")
					RETURNING stored_data_id";

        if ($stmt->Execute($query)) {
            $this->SetProperty("stored_data_id", $stmt->GetLastInsertID());
            $createdFrom = 'admin';

            $storedDataId = $this->GetProperty("stored_data_id");

            if ($this->GetProperty("cron") == "N") {
                $user = new User();
                $user->LoadBySession();
                $userId = $user->GetProperty("user_id");
            } else {
                $userId = DATENSICHERUNG;
            }

            $values = "(" . $storedDataId . ",'archive','N','" . $userId . "','" . GetCurrentDateTime() . "'," . Connection::GetSQLString($createdFrom) . ")";

            $stmt = GetStatement(DB_CONTROL);
            $query = "INSERT INTO stored_data_history (stored_data_id, property_name, value, user_id, created, created_from) VALUES " . $values;

            $stmt->Execute($query);

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads stored data by its stored_data_id
     *
     * @param int $storedDatalID stored_data_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($storedDatalID)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT stored_data_id, company_unit_id, created, date_from, date_to, employees, status, cron
					FROM stored_data
					WHERE stored_data_id=" . intval($storedDatalID);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("stored_data_id") ? true : false;
    }

    /**
     * Sends email with generated stored data pdf to its company_unit email-address.
     * If email is sent successfully then stored data's status will be changed to 'sent'
     * Object must be loaded before the method will be called.
     */
    function Send()
    {
        $popupPage = new PopupPage($this->module);

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($this->GetProperty("company_unit_id"));

        $subject = $companyUnit->GetProperty("title") .
            ": Ihre Datensicherungsdatei für die Periode " .
            date("Y.m.d", strtotime($this->GetProperty("date_from"))) . "-" . date(
                "Y.m.d",
                strtotime($this->GetProperty("date_to"))
            );

        $stmt = GetStatement(DB_PERSONAL);
        $query = "SELECT salutation, first_name, last_name, email, contact_id 
                    FROM contact 
                    WHERE contact_for_stored_data='Y' AND company_unit_id=" . $this->GetIntProperty("company_unit_id");
        $contactList = $stmt->FetchList($query);

        $result = false;

        foreach ($contactList as $contact) {
            $content = $popupPage->Load("stored_data_email.html");
            $content->SetVar("company_unit_title", $companyUnit->GetProperty("title"));
            $content->SetVar("date_from", $this->GetProperty("date_from"));
            $content->SetVar("date_to", $this->GetProperty("date_to"));
            $content->LoadFromArray($contact);
            $html = $popupPage->Grab($content);

            $sendResult = SendMailFromAdminTask(
                $contact["email"],
                $subject,
                $html,
                array(),
                array(array("Path" => PROJECT_DIR . "admin/template/images/email-footer-logo.png", "CID" => "logo")),
                array(),
                "trebono - 2KS Cloud Services GmbH"
            );

            if (!$sendResult) {
                continue;
            }

            $result = true;
        }

        if ($result) {
            $this->UpdateField("status", "sent");

            return true;
        }

        $this->UpdateField("status", "error");
        $this->AddError($result);

        return false;
    }

    /**
     * Generated ZIP archive for stored data and outputs it to browser or to temporary local file.
     * Object must be loaded before the method will be called.
     * Required properties are: company_unit_id, date_from, date_to, services, employees
     *
     * @param int $operationID if is true then cron log is saved
     */
    function GenerateStoredDataZIP($operationID = false)
    {
        if ($operationID) {
            Operation::SaveCronStatus(
                $operationID,
                "ZIP start generating for Company Unit: " . $this->GetProperty("company_unit_id")
            );
        }

        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($this->GetProperty("company_unit_id"));

        $partOfName = $this->GetProperty("general") ? "" : "_" . implode("_", $this->GetProperty("employees"));

        $archiveLocalPath = PROJECT_DIR . "var/log/stored_data_" . $companyUnit->GetProperty("customer_guid") . $partOfName . ".zip";

        $fileSys = new FileSys();

        $zip = new ZipArchive();
        $resultOpen = $zip->open($archiveLocalPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($resultOpen) {
            $receiptIDs = [];
            $confirmationList = [];
            $agreementsList = [];

            if (count($this->GetProperty("employees")) > 0) {
                $receiptIDs = ReceiptList::GetReceiptIDsForStoredData(
                    $this->GetProperty("date_from"),
                    $this->GetProperty("date_to"),
                    $this->GetProperty("employees")
                );

                $confirmationList = RecreationConfirmationList::GetConfirmationListForStoredData(
                    $this->GetProperty("date_from"),
                    $this->GetProperty("date_to"),
                    $this->GetProperty("employees")
                );

                $agreementsList = AgreementsEmployeesList::GetAgreementsListForStoredData(
                    $this->GetProperty("date_from"),
                    $this->GetProperty("date_to"),
                    $this->GetProperty("employees")
                );
            }

            foreach ($receiptIDs as $receiptID) {
                $receipt = new Receipt('receipt');
                $receipt->LoadByID($receiptID);

                $specificProductGroup = SpecificProductGroupFactory::Create($receipt->GetProperty("group_id"));
                $container = $specificProductGroup->GetContainer();
                $fileStorage = GetFileStorage($container);

                $receiptFileList = new ReceiptFileList('receipt');
                $receiptFileList->LoadFileList($receiptID);

                //add transfer note
                $transferNoteFilePath = $receipt->GenerateTransferNotePDF();
                $zip->addFromString(
                    'beleg/' . $receipt->GetProperty("legal_receipt_id") . "/trebono_transfervermerk_" . $this->GetProperty("receipt_id") . ".pdf",
                    $fileSys->GetFileContent($transferNoteFilePath)
                );
                $fileSys->Remove($transferNoteFilePath);

                foreach ($receiptFileList->GetItems() as $receiptFile) {
                    //add photo
                    $zip->addFromString(
                        'beleg/' . $receipt->GetProperty("legal_receipt_id") . "/dateien/" . $receiptFile['receipt_file_id'] . '/' . $receiptFile["file_image"],
                        $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["file_image"])
                    );

                    //add signature_file
                    if ($receiptFile["signature_file"] != "") {
                        $zip->addFromString(
                            'beleg/' . $receipt->GetProperty("legal_receipt_id") . "/dateien/" . $receiptFile['receipt_file_id'] . '/' . $receiptFile["signature_file"],
                            $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_file"])
                        );
                    }

                    //add signature_report_file
                    if ($receiptFile["signature_report_file"]) {
                        $zip->addFromString(
                            'beleg/' . $receipt->GetProperty("legal_receipt_id") . "/dateien/" . $receiptFile['receipt_file_id'] . '/' . $receiptFile["signature_report_file"],
                            $fileStorage->GetFileContent(RECEIPT_IMAGE_DIR . "file/" . $receiptFile["signature_report_file"])
                        );
                    }

                    $fileLogPath = PROJECT_DIR . "var/log/stored_data_" . $receiptFile['receipt_file_id'] . '.log';
                    $log = ReceiptFile::GetLog($receiptFile['receipt_file_id']);
                    $fileSys->PutFileContent($fileLogPath, $log);

                    //add logs
                    $zip->addFromString(
                        'beleg/' . $receipt->GetProperty("legal_receipt_id") . "/dateien/" . $receiptFile['receipt_file_id'] . '/' . $receiptFile['receipt_file_id'] . '.log',
                        $fileSys->GetFileContent($fileLogPath)
                    );
                    $fileSys->Remove($fileLogPath);
                }
            }

            $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);

            foreach ($confirmationList as $confirmation) {
                if (!$confirmation["pdf_file"]) {
                    continue;
                }

                $zip->addFromString(
                    'erholungsurlaub/' . $confirmation["pdf_file"],
                    $fileStorage->GetFileContent(PAYROLL_DIR . $confirmation["pdf_file"])
                );
            }

            $fileStorage = GetFileStorage(CONTAINER__AGREEMENTS);

            foreach ($agreementsList as $agreement) {
                if (!$agreement["file"]) {
                    continue;
                }

                $zip->addFromString(
                    'EV/' . $agreement["file"],
                    $fileStorage->GetFileContent(AGREEMENTS_DIR . $agreement["file"])
                );
            }

            if ($this->GetProperty("general")) {
                $fileStorage = GetFileStorage(CONTAINER__BILLING__PAYROLL);

                //add payrolls
                $payrollIDs = PayrollList::GetPayrollListForStoredData(
                    $this->GetProperty("date_from"),
                    $this->GetProperty("date_to"),
                    $this->GetProperty("company_unit_id")
                );

                foreach ($payrollIDs as $payrollID) {
                    $payroll = new Payroll('billing');
                    $payroll->LoadByID($payrollID);

                    if ($payroll->GetProperty("pdf_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("pdf_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("pdf_file"))
                        );
                    }

                    if ($payroll->GetProperty("lodas_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("lodas_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("lodas_file"))
                        );
                    }

                    if ($payroll->GetProperty("lug_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("lug_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("lug_file"))
                        );
                    }

                    if ($payroll->GetProperty("logga_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("logga_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("logga_file"))
                        );
                    }

                    if ($payroll->GetProperty("topas_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("topas_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("topas_file"))
                        );
                    }

                    if ($payroll->GetProperty("perforce_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("perforce_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("perforce_file"))
                        );
                    }

                    if ($payroll->GetProperty("lohnabrechnungen")) {
                        $zip->addFromString(
                            'payrolls/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("addison_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("addison_file"))
                        );
                    }

                    if ($payroll->GetProperty("lexware_file")) {
                        $zip->addFromString(
                            'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("lexware_file"),
                            $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("lexware_file"))
                        );
                    }

                    if (!$payroll->GetProperty("sage_file")) {
                        continue;
                    }

                    $zip->addFromString(
                        'lohnabrechnungen/' . $payroll->GetProperty("payroll_month") . '/' . $payroll->GetProperty("sage_file"),
                        $fileStorage->GetFileContent(PAYROLL_DIR . $payroll->GetProperty("sage_file"))
                    );
                }
            }

            if ($zip->numFiles == 0) {
                $emptyFile = fopen(PROJECT_DIR . "var/log/empty.txt", "w");
                fclose($emptyFile);
                $zip->addFromString("empty.txt", PROJECT_DIR . "var/log/empty.txt");
            }

            $zip->close();
        }

        if ($fileSys->FileExists($archiveLocalPath)) {
            $fileStorage = GetFileStorage(CONTAINER__BILLING__STORED_DATA);

            $archiveSwiftName = 'stored_data_' .
                $companyUnit->GetProperty("customer_guid") . "_" .
                $this->GetProperty("date_from") . "_" .
                $this->GetProperty("date_to") . "_" .
                $this->GetProperty("stored_data_id") . $partOfName . ".zip";

            $fileStorage->MoveToStorage($archiveLocalPath, STORED_DATA_DIR, $archiveSwiftName);
            $fileSys->Remove($archiveLocalPath);

            if ($operationID) {
                Operation::SaveCronStatus(
                    $operationID,
                    "ZIP finish generating for Company Unit: " . $this->GetProperty("company_unit_id")
                );
            }

            return true;
        }

        return false;
    }

    /**
     * Output (download) stored data archive. Object must be loaded from request before the method will be called.
     */
    public function OutputZipFile()
    {
        $companyUnit = new CompanyUnit("company");
        $companyUnit->LoadByID($this->GetProperty("company_unit_id"));
        $partOfName = "";
        if ($this->GetProperty("employees") != "all") {
            $partOfName = "_" . $this->GetProperty("employees");
        }

        $fileName = 'stored_data_' .
            $companyUnit->GetProperty("customer_guid") . "_" .
            $this->GetProperty("date_from") . "_" .
            $this->GetProperty("date_to") . "_" .
            $this->GetProperty("stored_data_id") . $partOfName . ".zip";
        $filePath = STORED_DATA_DIR . $fileName;

        $fileStorage = GetFileStorage(CONTAINER__BILLING__STORED_DATA);

        if ($fileStorage->FileExists($filePath)) {
            header("Content-Type: application/zip");
            header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
            header("Cache-Control: public, must-revalidate, max-age=0");
            header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
            echo $fileStorage->GetFileContent($filePath);
            exit();
        }

        Send404();
    }

    /**
     * Returns properties value history for stored data
     *
     * @param array $property
     * @param int $storedDataID
     * @param bool $orderDesc
     *
     * @return array value properties
     */
    public static function GetPropertyValueListStoredData($property, $storedDataID, $orderDesc = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        $orderBy = $orderDesc ? " ORDER BY created DESC" : " ORDER BY created ASC";

        $query = "SELECT value_id, stored_data_id, created, value, property_name, created_from, user_id
					FROM stored_data_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND stored_data_id=" . intval($storedDataID) . " 
					" . $orderBy;

        $valueList = $stmt->FetchList($query);

        $stmt = GetStatement(DB_PERSONAL);
        for ($i = 0; $i < count($valueList); $i++) {
            $userInfo = $stmt->FetchRow("SELECT " . Connection::GetSQLDecryption("first_name") . " AS first_name, " . Connection::GetSQLDecryption("last_name") . " AS last_name FROM user_info WHERE user_id=" . intval($valueList[$i]["user_id"]));
            $valueList[$i]["first_name"] = $userInfo["first_name"];
            $valueList[$i]["last_name"] = $userInfo["last_name"];
        }

        for ($i = 0; $i < count($valueList); $i++) {
            if ($valueList[$i]["property_name"] != "archive") {
                continue;
            }

            $valueList[$i]["value_text"] = $valueList[$i]["value"] == "N"
                ? GetTranslation("Active")
                : GetTranslation("Cancel");
        }

        return $valueList;
    }

    /**
     * Checks if stored data for given date and company unit exists and isn't archive
     *
     * @param int $companyUnitId
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return bool true if stored data exists, false otherwise
     */
    public static function StoredDataExists($companyUnitId, $employees, $dateFrom, $dateTo)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT stored_data_id FROM stored_data 
					WHERE company_unit_id=" . intval($companyUnitId) .
            " AND archive='N'" .
            " AND employees=" . Connection::GetSQLString($employees) .
            " AND date_from=" . Connection::GetSQLString($dateFrom) .
            " AND date_to=" . Connection::GetSQLString($dateTo);

        return boolval($stmt->FetchRow($query));
    }

    /**
     * Validates user Role
     *
     * @param int $storedDataID stored_data_ID
     * @param int $companyID company_id
     * @param int $userID user_id
     *
     * @return true|false
     */
    public static function ValidateAccess($storedDataID, $userID = null, $companyUnitID = null)
    {
        if (!$storedDataID && $companyUnitID === null) {
            return true;
        }

        if ($companyUnitID === null) {
            $storedData = new StoredData("billing");
            $storedData->LoadByID($storedDataID);
            $companyUnitID = $storedData->GetProperty("company_unit_id");
        }

        $user = new User();
        if (intval($userID) > 0) {
            $user->LoadByID($userID);
        } else {
            $user->LoadBySession();
        }

        $permissionNames = array("stored_data");

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

    /**
     * Change stored data record in DB
     *
     * @param string $field Name of param to change
     * @param mixed $value New value of param
     */
    public function UpdateField($field, $value)
    {
        $availableFields = array("status");
        if (!in_array($field, $availableFields)) {
            return false;
        }

        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE stored_data SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE stored_data_id=" . $this->GetIntProperty("stored_data_id");

        return $stmt->Execute($query);
    }

    /**
     * Get periods for stored data manual generation
     *
     * @param string $frequency generation type (monthly/quarterly/yearly)
     * @param string $dateFrom date_from
     * @param string $dateTo date_to
     *
     * @return array|null
     */
    public function GetPeriodsForStoredDataManualGeneration($frequency, $dateFrom, $dateTo)
    {
        if (!in_array($frequency, ["monthly", "quarterly", "yearly"])) {
            return null;
        }

        $periods = [];

        $dateCreateFrom = date_create($dateFrom)->modify("first day of this month");
        $dateCreateTo = date_create($dateTo)->modify("last day of this month");

        switch ($frequency) {
            case 'monthly':
                while ($dateCreateFrom <= $dateCreateTo) {
                    $periods[] = [
                        'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                        'dateTo' => $dateCreateFrom->format("Y-m-t"),
                    ];
                    $dateCreateFrom->modify("next month");
                }
                break;
            case 'quarterly':
                $years = $dateCreateTo->format("Y") - $dateCreateFrom->format("Y");
                $quarterFrom = ceil($dateCreateFrom->format("n") / 3);
                $quarterTo = ceil($dateCreateTo->format("n") / 3);
                $quarterFirstMonths = [1 => "01", 2 => "04", 3 => "07", 4 => "10"];
                $quarterLastMonthsLastDays = [1 => "03-31", 2 => "06-30", 3 => "09-30", 4 => "12-31"];

                if ($years == 0) {
                    if ($quarterFrom < $quarterTo) {
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                            'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                        ];
                        $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                        $quarterFrom++;
                        while ($quarterFrom < $quarterTo) {
                            $periods[] = [
                                'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                                'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                            ];
                            $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                            $quarterFrom++;
                        }
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                            'dateTo' => $dateCreateTo->format("Y-m-t"),
                        ];
                    } else {
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                            'dateTo' => $dateCreateTo->format("Y-m-t"),
                        ];
                    }
                } else {
                    $periods[] = [
                        'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                        'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                    ];
                    $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                    $quarterFrom++;
                    while ($quarterFrom <= 4) {
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                            'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                        ];
                        $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                        $quarterFrom++;
                    }

                    while ($years > 1) {
                        $quarterFrom = 1;
                        while ($quarterFrom <= 4) {
                            $periods[] = [
                                'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                                'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                            ];
                            $quarterFrom++;
                        }
                        $dateCreateFrom->modify("next year");
                        $years--;
                    }

                    if ($quarterTo > 1) {
                        $quarterFrom = 1;
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-01-01"),
                            'dateTo' => $dateCreateFrom->format("Y-03-31"),
                        ];
                        $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                        $quarterFrom++;
                        while ($quarterFrom < $quarterTo) {
                            $periods[] = [
                                'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                                'dateTo' => $dateCreateFrom->format("Y-" . $quarterLastMonthsLastDays[$quarterFrom]),
                            ];
                            $dateCreateFrom->modify("next month")->modify("next month")->modify("next month");
                            $quarterFrom++;
                        }
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-" . $quarterFirstMonths[$quarterFrom] . "-01"),
                            'dateTo' => $dateCreateTo->format("Y-m-t"),
                        ];
                    } else {
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-01-01"),
                            'dateTo' => $dateCreateTo->format("Y-m-t"),
                        ];
                    }
                }
                break;
            case 'yearly':
                $years = $dateCreateTo->format("Y") - $dateCreateFrom->format("Y");
                if ($years > 0) {
                    $periods[] = [
                        'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                        'dateTo' => $dateCreateFrom->format("Y-12-31"),
                    ];

                    $dateCreateFrom->modify("next year");
                    while ($years > 1) {
                        $periods[] = [
                            'dateFrom' => $dateCreateFrom->format("Y-01-01"),
                            'dateTo' => $dateCreateFrom->format("Y-12-31"),
                        ];
                        $dateCreateFrom->modify("next year");
                        $years--;
                    }

                    $periods[] = [
                        'dateFrom' => $dateCreateFrom->format("Y-01-01"),
                        'dateTo' => $dateCreateTo->format("Y-m-t"),
                    ];
                } else {
                    $periods[] = [
                        'dateFrom' => $dateCreateFrom->format("Y-m-01"),
                        'dateTo' => $dateCreateTo->format("Y-m-t"),
                    ];
                }
                break;
        }

        return $periods;
    }

    /**
     * Get periods for stored data cron generation
     *
     * @param string $frequency generation type (monthly/quarterly/yearly)
     * @param string $payrollMonth generation type (last_month/current_month)
     * @param string $date cron generation date
     *
     * @return array|null
     */
    public function GetPeriodsForStoredDataCronGeneration(
        $frequency,
        $payrollMonth,
        $date
    )
    {
        if (
            !in_array($frequency, ["monthly", "quarterly", "yearly"]) ||
            !in_array($payrollMonth, ["last_month", "current_month"])
        ) {
            return null;
        }

        $dateFrom = date_create($date);
        $dateTo = date_create($date);

        switch ($frequency) {
            case 'monthly':
                $dateFrom = $payrollMonth == "last_month"
                    ? $dateFrom->modify('first day of last month')
                    : $dateFrom->modify('first day of this month');
                break;
            case 'quarterly':
                $dateFrom = $payrollMonth == "last_month"
                    ? $dateFrom->modify('first day of last month')->modify("last month")->modify("last month")
                    : $dateFrom->modify('first day of last month')->modify("last month");
                break;
            case 'yearly':
                $dateFrom = $payrollMonth == "last_month"
                    ? $dateFrom->modify('first day of january last year')
                    : $dateFrom->modify('first day of january this year');
                break;
        }

        $dateTo = $payrollMonth == "last_month"
            ? $dateTo->modify('last day of last month')
            : $dateTo->modify('last day of this month');

        return [['dateFrom' => $dateFrom->format("Y-m-d"), 'dateTo' => $dateTo->format("Y-m-d")]];
    }
}
