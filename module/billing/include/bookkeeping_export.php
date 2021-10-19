<?php

class BookkeepingExport extends LocalObject
{

    private $module;

    /**
     * Constructor
     *
     * @param string $module Name of context module
     * @param array $data Array of bookkeeping export properties to be loaded instantly
     */
    public function BookkeepingExport($module, $data = array())
    {
        parent::LocalObject($data);

        $this->module = $module;
    }

    /**
     * Created new export record in database. Object must be loaded before the method will be called.
     * Required properties are: company_unit_id, date_from, date_to, services, employees
     *
     * @return bool bookkeeping_export_id of new record on success or false on sql-failure
     */
    public function Create()
    {
        $stmt = GetStatement();

        $query = "INSERT INTO bookkeeping_export (company_unit_id, created, date, file, user_id) VALUES (
						" . $this->GetIntProperty("company_unit_id") . ",
						" . Connection::GetSQLString(GetCurrentDateTime()) . ",
						" . $this->GetPropertyForSQL("date") . ",
						" . $this->GetPropertyForSQL("file") . ",
						" . $this->GetPropertyForSQL("user_id") . ")
					RETURNING bookkeeping_export_id";

        if ($stmt->Execute($query)) {
            $this->SetProperty("bookkeeping_export_id", $stmt->GetLastInsertID());

            return true;
        }

        $this->AddError("sql-error");

        return false;
    }

    /**
     * Loads export by its bookkeeping_export_id
     *
     * @param int $id bookkeeping_export_id
     *
     * @return bool true if loaded successfully or false on failure
     */
    public function LoadByID($id)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "SELECT bookkeeping_export_id, company_unit_id, created, date, file, user_id
					FROM bookkeeping_export
					WHERE bookkeeping_export_id=" . intval($id);

        $this->LoadFromSQL($query, $stmt);

        return $this->GetProperty("bookkeeping_export_id") ? true : false;
    }

    /**
     * Generated ZIP archive for bookkeeping export and outputs it to browser or to temporary local file.
     * Object must be loaded before the method will be called.
     */
    public function GenerateExportZIP()
    {
        $date = date("Y-m-d", strtotime($this->GetProperty("date")));
        $this->SetProperty("date", $date);

        $groupID = ProductGroup::GetProductGroupIDByCode(PRODUCT_GROUP__TRAVEL);

        $companyUnitIDs = $this->GetProperty("company_unit_id");
        $employeeIDs = EmployeeList::GetFullHierarchyEmployeeIDsByCompanyUnitID($companyUnitIDs);

        $receiptList = [];
        if (!empty($employeeIDs)) {
            $stmt = GetStatement(DB_MAIN);
            $where = array();
            $where[] = "r.employee_id IN(" . implode(", ", $employeeIDs) . ")";
            $where[] = "r.group_id=" . intval($groupID);
            $where[] = "DATE(r.document_date) <= " . Connection::GetSQLDate($date);
            $where[] = "r.status='approved'";
            $where[] = "r.archive='N'";
            $where[] = "(r.receipt_from!='doc' OR r.receipt_from IS NULL)";
            $where[] = "r.booked='Y'";
            $where[] = "r.bookkeeping_export_id='0'";

            $query = "SELECT r.receipt_id, r.legal_receipt_id, r.employee_id, r.group_id, r.amount_approved, r.real_amount_approved, DATE(r.document_date) as document_date,
                        tr.trip_id, c.digit, r.vat, r.receipt_from, r.comment, r.acc_system, r.bookkeeping_export_id, r.days_amount_under_16, r.days_amount_over_16,
                        tr.trip_name, tr.start_date, tr.end_date, tr.purpose
						FROM receipt AS r
						LEFT JOIN trip AS tr ON r.trip_id = tr.trip_id
						LEFT JOIN currency AS c ON c.currency_id = r.currency_id"
                . (count($where) > 0 ? " WHERE " . implode(" AND ", $where) : "");

            $receiptList = $stmt->FetchList($query);
        }

        $employeeList = [];
        $tripList = [];
        if ($receiptList !== false && !empty($receiptList)) {
            foreach ($receiptList as $receipt) {
                $receiptFileList = new ReceiptFileList("receipt");
                $receiptFileList->LoadFileList($receipt["receipt_id"]);
                $receipt["FileList"] = $receiptFileList->GetItems();

                if (!isset($employeeList[$receipt["employee_id"]])) {
                    $employee = new Employee($this->module);
                    $employee->LoadByID($receipt["employee_id"]);
                    $employeeList[$receipt["employee_id"]] = $employee;
                }
                $companyUnitID = $employeeList[$receipt["employee_id"]]->GetProperty("company_unit_id");

                if (!isset($tripList[$companyUnitID][$receipt["trip_id"]])) {
                    $tripList[$companyUnitID][$receipt["trip_id"]]["receipt_list"] = array();
                    $tripList[$companyUnitID][$receipt["trip_id"]]["employee_id"] = $receipt["employee_id"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["trip_id"] = $receipt["trip_id"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["trip_name"] = $receipt["trip_name"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["start_date"] = $receipt["start_date"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["end_date"] = $receipt["end_date"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["purpose"] = $receipt["purpose"];
                    $tripList[$companyUnitID][$receipt["trip_id"]]["verification_info"] = Option::GetInheritableOptionValue(
                        OPTION_LEVEL_EMPLOYEE,
                        OPTION__TRAVEL__MAIN__INTERNAL_VERIFICATION_INFO,
                        $receipt["employee_id"],
                        $date
                    );
                    if ($employeeList[$receipt["employee_id"]]->GetProperty("acc_creditor") == null) {
                        $tripList[$companyUnitID][$receipt["trip_id"]]["acc_creditor"] = CompanyUnit::GetInheritablePropertyCompanyUnit(
                            $companyUnitID,
                            "acc_creditor"
                        );
                    } else {
                        $tripList[$companyUnitID][$receipt["trip_id"]]["acc_creditor"] = $employeeList[$receipt["employee_id"]]->GetProperty("acc_creditor");
                    }
                    $tripList[$companyUnitID][$receipt["trip_id"]]["acc_creditor"] = trim(
                        $tripList[$companyUnitID][$receipt["trip_id"]]["acc_creditor"],
                        "_"
                    );

                    $tripList[$companyUnitID][$receipt["trip_id"]]["days_amount_under_16"] = 0;
                    $tripList[$companyUnitID][$receipt["trip_id"]]["days_amount_over_16"] = 0;
                }

                $accType = "acc_" . $receipt["receipt_from"];
                if ($employeeList[$receipt["employee_id"]]->GetProperty($accType) == null) {
                    $receipt["acc_receipt_type"] = CompanyUnit::GetInheritablePropertyCompanyUnit(
                        $companyUnitID,
                        $accType
                    );
                } else {
                    $receipt["acc_receipt_type"] = $employeeList[$receipt["employee_id"]]->GetProperty($accType);
                }
                $receipt["acc_receipt_type"] = trim($receipt["acc_receipt_type"], "_");
                $employeeList[$receipt["employee_id"]]->SetProperty(
                    "acc_creditor",
                    $tripList[$companyUnitID][$receipt["trip_id"]]["acc_creditor"]
                );

                $tripList[$companyUnitID][$receipt["trip_id"]]["receipt_list"][] = $receipt;

                $tripList[$companyUnitID][$receipt["trip_id"]]["days_amount_under_16"] += $receipt["days_amount_under_16"];
                $tripList[$companyUnitID][$receipt["trip_id"]]["days_amount_over_16"] += $receipt["days_amount_over_16"];

                if (isset($tripList[$companyUnitID][$receipt["trip_id"]]["employee_name"])) {
                    continue;
                }

                $tripList[$companyUnitID][$receipt["trip_id"]]["employee_name"] = $employeeList[$receipt["employee_id"]]->GetProperty("first_name") . " " . $employeeList[$receipt["employee_id"]]->GetProperty("last_name");
            }
        }

        foreach ($tripList as $companyUnitID => $companyTripList) {
            $zipFilename = $companyUnitID . "_" . date("ymd", strtotime($date));
            $archiveLocalPath = PROJECT_DIR . "var/log/bookkeeping_export_" . $zipFilename . ".zip";
            $fileSys = new FileSys();

            $zip = new ZipArchive();
            $resultOpen = $zip->open($archiveLocalPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

            if ($resultOpen === false) {
                return false;
            }

            $exportedReceiptList = [];
            foreach ($companyTripList as $trip) {
                $employee = $employeeList[$trip["employee_id"]];

                $popupPage = new PopupPage($this->module);
                $content = $popupPage->Load("bookkeeping_export_pdf.html");

                $content->SetVar("Module", $this->module);
                $content->LoadFromArray($trip);
                $content->LoadFromObject($employee);

                $data = [
                    "user_name" => $this->GetProperty("user_name"),
                    "export_date" => GetCurrentDate(),
                    "employee_guid" => $employee->GetProperty("employee_guid"),
                    "cost_center_number" => $employee->GetProperty("cost_center_number"),
                ];

                $html = $popupPage->Grab($content);

                $pdf = new BookkeepingExportPDF($this->module, array_merge($trip, $data));
                $pdf->writeHTML($html, 2);

                $filename = date(
                    "ymd",
                    strtotime($date)
                ) . "_" . $trip["employee_id"] . "_" . $trip["trip_id"] . ".pdf";
                $path = PROJECT_DIR . "var/log/bookkeeping_export_" . $filename;

                //$pdf->Output("invoice.pdf", "I");
                //exit();
                $pdf->Output($path, "F");

                $zip->addFromString($filename, $fileSys->GetFileContent($path));
                $fileSys->Remove($path);

                $exportedReceiptList = array_merge(
                    $exportedReceiptList,
                    array_column($trip["receipt_list"], "receipt_id")
                );
            }

            $zip->close();

            if (!$fileSys->FileExists($archiveLocalPath)) {
                continue;
            }

            $this->SetProperty("company_unit_id", $companyUnitID);
            $this->SetProperty("file", $zipFilename . ".zip");
            if (!$this->Create()) {
                return false;
            }

            $zipFilename .= "_" . $this->GetProperty("bookkeeping_export_id") . ".zip";

            $fileStorage = GetFileStorage(CONTAINER__BILLING__BOOKKEEPING_EXPORT);

            $fileStorage->MoveToStorage($archiveLocalPath, BOOKKEEPING_EXPORT_DIR, $zipFilename);
            $fileSys->Remove($archiveLocalPath);
            $this->UpdateField("file", $zipFilename);

            $values = "(" . $this->GetProperty("bookkeeping_export_id") . ",'archive','N','" . $this->GetProperty("user_id") . "','" . GetCurrentDateTime() . "','admin')";
            $stmtControl = GetStatement(DB_CONTROL);
            $query = "INSERT INTO bookkeeping_export_history (bookkeeping_export_id, property_name, value, user_id, created, created_from) VALUES " . $values;
            $stmtControl->Execute($query);

            if (empty($exportedReceiptList)) {
                continue;
            }

            $query = "UPDATE receipt SET bookkeeping_export_id=" . $this->GetProperty("bookkeeping_export_id") . "
                        WHERE receipt_id IN (" . implode(", ", $exportedReceiptList) . ")";
            $stmt->Execute($query);
        }

        return true;
    }

    /**
     * Output (download) bookkeeping archive. Object must be loaded from request before the method will be called.
     */
    public function OutputZipFile()
    {
        $fileName = $this->GetProperty("file");
        $filePath = BOOKKEEPING_EXPORT_DIR . $this->GetProperty("file");

        $fileStorage = GetFileStorage(CONTAINER__BILLING__BOOKKEEPING_EXPORT);

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
     * Change bookkeeping export record in DB
     *
     * @param string $field Name of param to change
     * @param mixed $value New value of param
     */
    public function UpdateField($field, $value)
    {
        $stmt = GetStatement(DB_MAIN);
        $query = "UPDATE bookkeeping_export SET " . $field . "=" . Connection::GetSQLString($value) . " WHERE bookkeeping_export_id=" . $this->GetIntProperty("bookkeeping_export_id");

        return $stmt->Execute($query);
    }

    /**
     * Returns properties value history
     *
     * @param array $property
     * @param int $exportID
     * @param bool $orderDesc
     *
     * @return array value properties
     */
    public static function GetPropertyValueListBookkeepingExport($property, $exportID, $orderDesc = false)
    {
        $stmt = GetStatement(DB_CONTROL);

        $orderBy = $orderDesc ? " ORDER BY created DESC" : " ORDER BY created ASC";

        $query = "SELECT value_id, bookkeeping_export_id, created, value, property_name, created_from, user_id
					FROM bookkeeping_export_history
					WHERE property_name=" . Connection::GetSQLString($property) . " AND bookkeeping_export_id=" . intval($exportID) . " 
					" . $orderBy;
        $valueList = $stmt->FetchList($query);

        $usernameList = [];
        foreach ($valueList as $key => $item) {
            if (!isset($usernameList[$item["user_id"]])) {
                $usernameList[$item["user_id"]] = User::GetNameByID($item["user_id"]);
            }
            $valueList[$key]["user_name"] = $usernameList[$item["user_id"]];

            if ($valueList[$key]["property_name"] != "archive") {
                continue;
            }

            $valueList[$key]["value_text"] = $valueList[$key]["value"] == "N"
                ? GetTranslation("Active")
                : GetTranslation("Cancel");
        }

        return $valueList;
    }
}
